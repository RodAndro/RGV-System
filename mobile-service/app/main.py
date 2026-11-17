import hashlib
import hmac
import json
import os
import secrets
from datetime import date, datetime, timedelta, timezone
from email.message import EmailMessage
import smtplib
from zoneinfo import ZoneInfo

from fastapi import Depends, FastAPI, Header, Request
from fastapi.responses import JSONResponse

from .database import connect, transaction
from .security import hash_password, issue_token, totp_valid, verify_password

app = FastAPI(
    title="RGV Mobile Service",
    version="1.0.0",
    docs_url=None,
    redoc_url=None,
    openapi_url=None,
)
API = "/api/v1/mobile"
APP_TIMEZONE = ZoneInfo(os.environ.get("APP_TIMEZONE", "UTC"))


def business_today() -> date:
    return datetime.now(APP_TIMEZONE).date()


class ApiError(Exception):
    def __init__(self, status: int, message: str, errors: dict | None = None):
        self.status, self.message, self.errors = status, message, errors or {}


@app.exception_handler(ApiError)
async def api_error_handler(_: Request, exc: ApiError):
    body = {"message": exc.message}
    if exc.errors:
        body["errors"] = exc.errors
    return JSONResponse(body, status_code=exc.status)


@app.exception_handler(Exception)
async def unexpected_error_handler(_: Request, exc: Exception):
    # Avoid leaking SQL, hashes, or credentials in production responses.
    print(f"mobile-service request failed: {type(exc).__name__}", flush=True)
    return JSONResponse(
        {"message": "The request could not be completed."}, status_code=500
    )


def invalid(field: str, message: str):
    raise ApiError(422, "The given data was invalid.", {field: [message]})


async def json_body(request: Request) -> dict:
    try:
        value = await request.json()
    except Exception:
        invalid("request", "A valid JSON request is required.")
    if not isinstance(value, dict):
        invalid("request", "A JSON object is required.")
    return value


@app.middleware("http")
async def api_rate_limit(request: Request, call_next):
    if not request.url.path.startswith(API + "/"):
        return await call_next(request)
    remote = request.client.host if request.client else "unknown"
    authorization = request.headers.get("authorization", "")
    if request.url.path.endswith("/auth/login"):
        limit = int(os.getenv("LOGIN_REQUESTS_PER_MINUTE", "20"))
        identity = "login|" + remote
    elif request.url.path.endswith("/auth/mfa/verify"):
        limit = int(os.getenv("MFA_REQUESTS_PER_MINUTE", "5"))
        identity = "mfa|" + remote + "|" + authorization
    else:
        limit = int(os.getenv("API_REQUESTS_PER_MINUTE", "120"))
        identity = "api|" + remote + "|" + authorization
    key = hashlib.sha256(identity.encode()).hexdigest()
    with transaction() as conn:
        row = conn.execute(
            "insert into mobile_api_rate_limits(key,hits,window_started_at,updated_at) values (%s,1,now(),now()) "
            "on conflict(key) do update set hits=case when mobile_api_rate_limits.window_started_at < now()-interval '1 minute' then 1 else mobile_api_rate_limits.hits+1 end, "
            "window_started_at=case when mobile_api_rate_limits.window_started_at < now()-interval '1 minute' then now() else mobile_api_rate_limits.window_started_at end, updated_at=now() returning hits",
            (key,),
        ).fetchone()
        if secrets.randbelow(100) == 0:
            conn.execute(
                "delete from mobile_api_rate_limits where updated_at < now()-interval '1 day'"
            )
            conn.execute(
                "delete from mobile_login_attempts where window_started_at < now()-interval '1 day'"
            )
        if row["hits"] > limit:
            return JSONResponse(
                {"message": "Too many requests. Please try again shortly."},
                status_code=429,
            )
    return await call_next(request)


def audit(
    conn,
    user_id: int | None,
    event: str,
    subject_type: str | None = None,
    subject_id: int | None = None,
    properties: dict | None = None,
    old_values: dict | None = None,
    ip: str | None = None,
    url: str | None = None,
):
    key = os.environ.get("AUDIT_HMAC_KEY", "")
    if not key:
        raise RuntimeError(
            "AUDIT_HMAC_KEY must be configured with the Laravel audit key"
        )
    # Match AuditLog::makeChecksum's JSON shape and Laravel APP_KEY HMAC.
    conn.execute("select pg_advisory_xact_lock(708196311)")
    previous = conn.execute(
        "select checksum from audit_logs order by id desc limit 1"
    ).fetchone()
    payload = {
        "user_id": user_id,
        "event": event,
        "auditable_type": subject_type,
        "auditable_id": subject_id,
        "old_values": old_values or [],
        "new_values": properties or [],
        "ip_address": ip,
        "url": url,
        "previous_checksum": previous["checksum"] if previous else None,
    }
    canonical = json.dumps(payload, ensure_ascii=True, separators=(",", ":"))
    checksum = hmac.new(key.encode(), canonical.encode(), hashlib.sha256).hexdigest()
    conn.execute(
        "insert into audit_logs(user_id,event,auditable_type,auditable_id,old_values,new_values,ip_address,url,checksum,previous_checksum,created_at,updated_at) "
        "values (%s,%s,%s,%s,%s::json,%s::json,%s,%s,%s,%s,now(),now())",
        (
            user_id,
            event,
            subject_type,
            subject_id,
            json.dumps(old_values or []),
            json.dumps(properties or []),
            ip,
            url,
            checksum,
            payload["previous_checksum"],
        ),
    )
    conn.execute(
        "insert into activity_log(log_name, description, subject_type, subject_id, causer_type, causer_id, properties, created_at, updated_at) "
        "values ('mobile', %s, %s, %s, 'App\\Models\\User', %s, %s::json, now(), now())",
        (event, subject_type, subject_id, user_id, json.dumps(properties or {})),
    )


def audit_inventory_quantity(
    conn, user_id, inventory_id, old_quantity, new_quantity, request
):
    audit(
        conn,
        user_id,
        "Inventory updated",
        "App\\Models\\Inventory",
        inventory_id,
        {"quantity": new_quantity},
        old_values={"quantity": old_quantity},
        ip=request.client.host if request.client else None,
        url=str(request.url),
    )


def user_payload(row: dict):
    return {
        "id": row["id"],
        "name": row["name"],
        "email": row["email"],
        "phone": row.get("phone"),
        "avatar_path": row.get("avatar_path"),
        "is_active": bool(row["is_active"]),
        "mfa_enabled": bool(row.get("mfa_enabled")),
        "role": "employee",
    }


def new_session(
    conn, user_id: int, abilities: list[str], device: str, minutes: int | None
):
    secret, digest = issue_token()
    expires = (
        datetime.now(timezone.utc) + timedelta(minutes=minutes)
        if minutes
        else datetime.now(timezone.utc) + timedelta(days=30)
    )
    row = conn.execute(
        "insert into personal_access_tokens(tokenable_type, tokenable_id, name, token, abilities, expires_at, created_at, updated_at) "
        "values ('App\\Models\\User', %s, %s, %s, %s, %s, now(), now()) returning id",
        (
            user_id,
            (device or "RGV Employee App")[:255],
            digest,
            json.dumps(abilities),
            expires,
        ),
    ).fetchone()
    return f"{row['id']}|{secret}"


def current_user(authorization: str | None, required_ability: str = "employee"):
    if not authorization or not authorization.startswith("Bearer "):
        raise ApiError(401, "Unauthenticated.")
    raw = authorization[7:].strip()
    try:
        token_id, secret = raw.split("|", 1)
        token_id = int(token_id)
    except (ValueError, TypeError):
        raise ApiError(401, "Unauthenticated.")
    digest = hashlib.sha256(secret.encode()).hexdigest()
    with transaction() as conn:
        token = conn.execute(
            "select id, tokenable_id, abilities, expires_at from personal_access_tokens "
            "where id=%s and tokenable_type='App\\Models\\User' and token=%s for update",
            (token_id, digest),
        ).fetchone()
        if not token or (
            token["expires_at"] and token["expires_at"] <= datetime.now(timezone.utc)
        ):
            raise ApiError(401, "Unauthenticated.")
        abilities = (
            token["abilities"]
            if isinstance(token["abilities"], list)
            else json.loads(token["abilities"] or "[]")
        )
        if required_ability not in abilities and "*" not in abilities:
            raise ApiError(403, "This token cannot perform this action.")
        user = conn.execute(
            "select u.id,u.name,u.email,u.phone,u.avatar_path,u.is_active,u.mfa_enabled,exists(select 1 from model_has_roles mr join roles r on r.id=mr.role_id "
            "where mr.model_id=u.id and mr.model_type='App\\Models\\User' and r.name='employee' and r.guard_name='web') as is_employee "
            "from users u where u.id=%s",
            (token["tokenable_id"],),
        ).fetchone()
        conn.execute(
            "update personal_access_tokens set last_used_at=now(), updated_at=now() where id=%s",
            (token_id,),
        )
        if not user or not user["is_active"] or not user["is_employee"]:
            raise ApiError(403, "This account is not an active employee.")
        if required_ability == "employee" and user.get("mfa_enabled"):
            # The login MFA challenge only receives mfa-verify ability.
            # Access tokens are issued only after a valid factor check.
            pass
        return user


def auth_dependency(required_ability: str = "employee"):
    def dependency(authorization: str | None = Header(default=None)):
        return current_user(authorization, required_ability)

    return dependency


get_user = auth_dependency()


def ip_key(request: Request, email: str):
    # Store no raw email or IP in the throttling table.
    return hashlib.sha256(
        f"{email.lower()}|{request.client.host if request.client else ''}".encode()
    ).hexdigest()


def enforce_login_limit(conn, key: str):
    row = conn.execute(
        "select attempts, window_started_at from mobile_login_attempts where key=%s for update",
        (key,),
    ).fetchone()
    if (
        row
        and row["window_started_at"] > datetime.now(timezone.utc) - timedelta(minutes=1)
        and row["attempts"] >= 5
    ):
        raise ApiError(429, "Too many login attempts. Please try again in 1 minute.")


def record_login_failure(key: str, email: str, ip: str | None):
    # This is a separate committed transaction because the caller returns a
    # validation error, which must not roll back the failed-attempt counter.
    with transaction() as conn:
        conn.execute(
            "insert into mobile_login_attempts(key, attempts, window_started_at) values (%s,1,now()) "
            "on conflict(key) do update set attempts=case when mobile_login_attempts.window_started_at < now()-interval '1 minute' then 1 else mobile_login_attempts.attempts+1 end, "
            "window_started_at=case when mobile_login_attempts.window_started_at < now()-interval '1 minute' then now() else mobile_login_attempts.window_started_at end",
            (key,),
        )
        audit(
            conn,
            None,
            "failed login",
            properties={"email": email},
            ip=ip,
            url=API + "/auth/login",
        )


@app.get("/health")
@app.get(API + "/health")
def health():
    if not os.environ.get("AUDIT_HMAC_KEY"):
        return JSONResponse(
            {"status": "degraded", "reason": "audit key is not configured"},
            status_code=503,
        )
    with connect() as conn:
        conn.execute("select 1")
        for sql in (
            "select id from users limit 0",
            "select id from personal_access_tokens limit 0",
            "select id from inventories limit 0",
            "select id from borrow_requests limit 0",
            "select employee_id,borrow_request_id,idempotency_key from mobile_return_idempotency limit 0",
            "select employee_id,idempotency_key from mobile_borrow_idempotency limit 0",
            "select key from mobile_login_attempts limit 0",
            "select key from mobile_api_rate_limits limit 0",
            "select id,checksum from audit_logs limit 0",
        ):
            conn.execute(sql)
    return {"status": "ok"}


@app.get("/live")
def live():
    return {"status": "ok"}


@app.post(API + "/auth/login")
async def login(request: Request):
    data = await json_body(request)
    email, password = data.get("email"), data.get("password")
    if not isinstance(email, str) or "@" not in email or len(email) > 255:
        invalid("email", "Enter a valid email address.")
    if not isinstance(password, str) or not password:
        invalid("password", "The password field is required.")
    key = ip_key(request, email)
    with transaction() as conn:
        enforce_login_limit(conn, key)
        user = conn.execute(
            "select u.id,u.name,u.email,u.password,u.phone,u.avatar_path,u.is_active,u.mfa_enabled,u.mfa_type,u.mfa_secret,u.mfa_recovery_codes,exists(select 1 from model_has_roles mr join roles r on r.id=mr.role_id "
            "where mr.model_id=u.id and mr.model_type='App\\Models\\User' and r.name='employee' and r.guard_name='web') as is_employee "
            "from users u where lower(u.email)=lower(%s)",
            (email,),
        ).fetchone()
        if not user or not verify_password(password, user["password"]):
            record_login_failure(
                key, email, request.client.host if request.client else None
            )
            invalid("email", "These credentials do not match our records.")
        if not user["is_active"]:
            record_login_failure(
                key, email, request.client.host if request.client else None
            )
            invalid("email", "This account is inactive. Contact an administrator.")
        if not user["is_employee"]:
            record_login_failure(
                key, email, request.client.host if request.client else None
            )
            invalid("email", "This app is for employee accounts only.")
        conn.execute("delete from mobile_login_attempts where key=%s", (key,))
        conn.execute(
            "update users set last_login_at=now(),updated_at=now() where id=%s",
            (user["id"],),
        )
        conn.execute(
            "insert into login_history(user_id, ip_address, user_agent, logged_in_at, created_at, updated_at) values (%s,%s,%s,now(),now(),now())",
            (
                user["id"],
                request.client.host if request.client else None,
                request.headers.get("user-agent"),
            ),
        )
        audit(
            conn,
            user["id"],
            "login",
            "App\\Models\\User",
            user["id"],
            {},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
        if user.get("mfa_enabled"):
            token = new_session(
                conn,
                user["id"],
                ["mfa-verify"],
                data.get("device_name", "RGV Employee App"),
                10,
            )
            return {
                "mfa_required": True,
                "mfa_type": user.get("mfa_type") or "totp",
                "mfa_token": token,
            }
        token = new_session(
            conn,
            user["id"],
            ["employee"],
            data.get("device_name", "RGV Employee App"),
            None,
        )
        return {"token": token, "user": user_payload(user)}


@app.post(API + "/auth/mfa/verify")
async def verify_mfa(request: Request, pending=Depends(auth_dependency("mfa-verify"))):
    data = await json_body(request)
    code = data.get("code")
    if not isinstance(code, str) or not (
        code.isdigit()
        and len(code) == 6
        or len(code) == 17
        and code[8] == "-"
        and all(c in "0123456789abcdefABCDEF" for c in code[:8] + code[9:])
    ):
        invalid("code", "Enter a six-digit verification code or a recovery code.")
    with transaction() as conn:
        user = conn.execute(
            "select id,name,email,phone,avatar_path,is_active,mfa_enabled,mfa_secret,mfa_recovery_codes from users where id=%s for update",
            (pending["id"],),
        ).fetchone()
        recovery = user.get("mfa_recovery_codes") or []
        if isinstance(recovery, str):
            recovery = json.loads(recovery)
        submitted_code = code.lower()
        recovery_match = any(
            secrets.compare_digest(submitted_code, item.lower())
            for item in recovery
            if isinstance(item, str)
        )
        if not totp_valid(user.get("mfa_secret"), code) and not recovery_match:
            invalid("code", "Invalid verification code.")
        if recovery_match:
            # Recovery codes are single use across clients sharing this row.
            recovery.remove(
                next(
                    item
                    for item in recovery
                    if isinstance(item, str)
                    and secrets.compare_digest(submitted_code, item.lower())
                )
            )
            conn.execute(
                "update users set mfa_recovery_codes=%s::json, updated_at=now() where id=%s",
                (json.dumps(recovery), user["id"]),
            )
        conn.execute(
            "delete from personal_access_tokens where id=%s",
            (
                int(
                    request.headers.get("authorization", "")
                    .split("|")[0]
                    .removeprefix("Bearer ")
                ),
            ),
        )
        token = new_session(
            conn,
            user["id"],
            ["employee"],
            data.get("device_name", "RGV Employee App"),
            None,
        )
        audit(
            conn,
            user["id"],
            "Mobile MFA verified",
            properties={"method": "totp" if not recovery_match else "recovery"},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
        return {"token": token, "user": user_payload(user)}


@app.post(API + "/auth/logout")
def logout(request: Request, user=Depends(get_user), authorization: str = Header(...)):
    token_id = int(authorization[7:].split("|", 1)[0])
    with transaction() as conn:
        conn.execute(
            "delete from personal_access_tokens where id=%s and tokenable_id=%s",
            (token_id, user["id"]),
        )
        conn.execute(
            "update login_history set logged_out_at=now(),updated_at=now() where id=(select id from login_history where user_id=%s and logged_out_at is null order by logged_in_at desc limit 1)",
            (user["id"],),
        )
        audit(
            conn,
            user["id"],
            "logout",
            "App\\Models\\User",
            user["id"],
            {},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
    return {"message": "Logged out."}


@app.get(API + "/me")
def me(user=Depends(get_user)):
    return {"user": user_payload(user)}


def request_payload(conn, request_id: int):
    req = conn.execute(
        "select * from borrow_requests where id=%s", (request_id,)
    ).fetchone()
    if not req:
        return None
    items = conn.execute(
        "select bi.*, i.item_code,i.name,i.brand,i.unit from borrow_items bi left join inventories i on i.id=bi.inventory_id "
        "where bi.borrow_request_id=%s order by bi.id",
        (request_id,),
    ).fetchall()
    return {
        "id": req["id"],
        "request_number": req["request_number"],
        "status": req["status"],
        "reason": req["reason"],
        "borrow_date": str(req["borrow_date"]),
        "due_date": str(req["due_date"]),
        "return_date": str(req["return_date"]) if req["return_date"] else None,
        "return_location": req.get("return_location"),
        "admin_remarks": req.get("admin_remarks"),
        "approved_at": req["approved_at"].isoformat()
        if req.get("approved_at")
        else None,
        "rejected_at": req["rejected_at"].isoformat()
        if req.get("rejected_at")
        else None,
        "cancelled_at": req["cancelled_at"].isoformat()
        if req.get("cancelled_at")
        else None,
        "borrowed_at": req["borrowed_at"].isoformat()
        if req.get("borrowed_at")
        else None,
        "returned_at": req["returned_at"].isoformat()
        if req.get("returned_at")
        else None,
        "items": [
            {
                "id": it["id"],
                "inventory_id": it["inventory_id"],
                "item_code": it.get("item_code"),
                "name": it.get("name"),
                "brand": it.get("brand"),
                "unit": it.get("unit"),
                "quantity": it["quantity"],
                "condition_borrowed": it["condition_borrowed"],
                "condition_returned": it.get("condition_returned"),
                "is_returned": bool(it["is_returned"]),
                "returned_at": it["returned_at"].isoformat()
                if it.get("returned_at")
                else None,
                "damage_notes": it.get("damage_notes"),
                "return_evidence": None,
            }
            for it in items
        ],
    }


@app.get(API + "/dashboard")
def dashboard(user=Depends(get_user)):
    with connect() as conn:
        counts = conn.execute(
            "select count(*) total, count(*) filter(where status='pending') pending, count(*) filter(where status='approved') approved, count(*) filter(where status='borrowed') borrowed, count(*) filter(where status='returned') returned, count(*) filter(where status='borrowed' and due_date<current_date) overdue from borrow_requests where employee_id=%s and deleted_at is null",
            (user["id"],),
        ).fetchone()
        recent = conn.execute(
            "select id,request_number,status,borrow_date,due_date from borrow_requests where employee_id=%s and deleted_at is null order by created_at desc limit 5",
            (user["id"],),
        ).fetchall()
        for row in recent:
            row["items"] = [
                x["name"]
                for x in conn.execute(
                    "select i.name from borrow_items bi left join inventories i on i.id=bi.inventory_id where bi.borrow_request_id=%s order by bi.id",
                    (row["id"],),
                ).fetchall()
                if x["name"]
            ]
        return {
            "employee": {
                "id": user["id"],
                "name": user["name"],
                "email": user["email"],
            },
            "summary": dict(counts),
            "recent_requests": [
                {
                    **r,
                    "borrow_date": str(r["borrow_date"]),
                    "due_date": str(r["due_date"]),
                }
                for r in recent
            ],
        }


def inventory_payload(row):
    return {
        "id": row["id"],
        "item_code": row["item_code"],
        "name": row["name"],
        "brand": row.get("brand"),
        "description": row.get("description"),
        "category": row.get("category"),
        "quantity": row["quantity"],
        "unit": row["unit"],
        "status": row["status"],
        "condition": row["condition"],
        "is_active": bool(row["is_active"]),
        # Included so the app can resolve scanned QR codes from its offline
        # catalog after the device loses connectivity.
        "qr_token": row.get("qr_token"),
    }


@app.get(API + "/inventory/lookup")
def inventory_lookup(code: str, user=Depends(get_user)):
    code = code.strip()
    if len(code) > 1000:
        invalid("code", "The code may not exceed 1000 characters.")
    if code.startswith("{"):
        try:
            meta = json.loads(code)
            if isinstance(meta, dict):
                code = str(
                    next(
                        (
                            meta[k]
                            for k in ("qr_token", "qrToken", "token", "id", "item_code", "itemCode", "code")
                            if meta.get(k) not in (None, "")
                        ),
                        code,
                    )
                )
        except json.JSONDecodeError:
            pass
    if code.startswith("http"):
        parts = code.rstrip("/").split("/")
        if "qr" in parts and parts.index("qr") + 1 < len(parts):
            code = parts[parts.index("qr") + 1]
    with connect() as conn:
        row = conn.execute(
            "select i.id,i.item_code,i.name,i.brand,i.description,i.category_id,i.quantity,i.unit,i.status,i.condition,i.is_active,i.deleted_at,i.qr_token,i.location,c.name category from inventories i left join inventory_categories c on c.id=i.category_id where (i.qr_token=%s or (case when %s ~ '^[0-9]+$' then i.id=%s::bigint else false end) or i.item_code=%s) and i.deleted_at is null order by case when i.qr_token=%s then 0 when %s ~ '^[0-9]+$' and i.id=%s::bigint then 1 else 2 end limit 1",
            (code, code, code, code, code, code, code),
        ).fetchone()
        if not row:
            invalid("code", "No inventory item matches this code.")
        return {"item": inventory_payload(row)}


@app.get(API + "/inventory/search")
def inventory_search(q: str = "", limit: int = 50, user=Depends(get_user)):
    q = q.strip()
    if len(q) > 100:
        invalid("q", "Search text must be 100 characters or fewer.")
    # A bounded page size lets the app refresh its offline catalog in one call.
    limit = max(1, min(limit, 500))
    with connect() as conn:
        rows = conn.execute(
            "select i.id,i.item_code,i.name,i.brand,i.description,i.category_id,i.quantity,i.unit,i.status,i.condition,i.is_active,i.deleted_at,i.location,i.qr_token,c.name category from inventories i left join inventory_categories c on c.id=i.category_id where i.is_active=true and i.deleted_at is null and (%s='' or i.name ilike %s or i.item_code ilike %s or coalesce(i.brand,'') ilike %s) order by i.name limit %s",
            (q, f"%{q}%", f"%{q}%", f"%{q}%", limit),
        ).fetchall()
        return {"items": [inventory_payload(r) for r in rows]}


def notify_admins(conn, request_row):
    admins = conn.execute(
        "select u.id from users u join model_has_roles mr on mr.model_id=u.id and mr.model_type='App\\Models\\User' join roles r on r.id=mr.role_id and r.name='admin' and r.guard_name='web' where u.is_active=true"
    ).fetchall()
    for admin in admins:
        pref = conn.execute(
            "select in_app_enabled from notification_preferences where user_id=%s and type='borrow_request'",
            (admin["id"],),
        ).fetchone()
        if pref is None or pref["in_app_enabled"]:
            conn.execute(
                "insert into notifications(user_id,type,title,message,link,is_read,created_at,updated_at) values (%s,'borrow_request','New Borrow Request Received',%s,%s,false,now(),now())",
                (
                    admin["id"],
                    f"New borrow request {request_row['request_number']} from {request_row['employee_name']} for {request_row['borrow_date']}.",
                    f"/admin/borrow-requests/{request_row['id']}",
                ),
            )


def notify_admins_low_stock(conn, inventory):
    admins = conn.execute(
        "select u.id from users u join model_has_roles mr on mr.model_id=u.id and mr.model_type='App\\Models\\User' join roles r on r.id=mr.role_id and r.name='admin' and r.guard_name='web' where u.is_active=true"
    ).fetchall()
    for admin in admins:
        pref = conn.execute(
            "select in_app_enabled from notification_preferences where user_id=%s and type='inventory'",
            (admin["id"],),
        ).fetchone()
        if pref is None or pref["in_app_enabled"]:
            conn.execute(
                "insert into notifications(user_id,type,title,message,link,is_read,created_at,updated_at) values (%s,'inventory','Low Stock Alert',%s,%s,false,now(),now())",
                (
                    admin["id"],
                    f"{inventory['name']} is running low on stock. Current: {inventory['quantity']}",
                    f"/admin/inventories/{inventory['id']}",
                ),
            )


def send_email(recipient: str, subject: str, body: str):
    host = os.getenv("MAIL_HOST", "")
    if (
        os.getenv("NOTIFICATION_EMAIL_ENABLED", "true").lower()
        not in ("1", "true", "yes", "on")
        or not recipient
        or not host
    ):
        return
    message = EmailMessage()
    message["Subject"] = subject
    message["From"] = os.getenv("MAIL_FROM_ADDRESS", "noreply@example.invalid")
    message["To"] = recipient
    message.set_content(body)
    try:
        with smtplib.SMTP(host, int(os.getenv("MAIL_PORT", "587")), timeout=8) as smtp:
            smtp.starttls()
            username = os.getenv("MAIL_USERNAME")
            if username:
                smtp.login(username, os.getenv("MAIL_PASSWORD", ""))
            smtp.send_message(message)
    except Exception as exc:
        # Commit business data first; report delivery failure for operations.
        print(f"admin email delivery failed: {type(exc).__name__}", flush=True)


def email_admins_about_request(request_row):
    recipient = (
        os.getenv("ADMIN_NOTIFICATION_EMAIL")
        or os.getenv("BACKUP_NOTIFICATION_EMAIL")
        or os.getenv("SYSTEM_NOTIFICATION_EMAIL")
        or ""
    ).strip()
    send_email(
        recipient,
        "New Borrow Request Received - RGV Multi-Tech Services",
        f"A new borrow request has been received.\n\nRequest Number: {request_row['request_number']}\n"
        f"Employee: {request_row['employee_name']}\nBorrow Date: {request_row['borrow_date']}\n"
        f"Due Date: {request_row['due_date']}\nReason: {request_row['reason']}\n"
        f"Review: {os.getenv('APP_URL', '').rstrip('/')}/admin/borrow-requests/{request_row['id']}\n",
    )


@app.get(API + "/borrow-requests")
def list_requests(user=Depends(get_user)):
    with connect() as conn:
        rows = conn.execute(
            "select id from borrow_requests where employee_id=%s and deleted_at is null order by created_at desc",
            (user["id"],),
        ).fetchall()
        return {"requests": [request_payload(conn, r["id"]) for r in rows]}


@app.get(API + "/borrow-requests/returnable")
def returnable(user=Depends(get_user)):
    with connect() as conn:
        rows = conn.execute(
            "select bi.id borrow_item_id, br.request_number,br.id request_id,br.borrow_date,br.due_date,bi.quantity,bi.condition_borrowed,i.id inventory_id,i.item_code,i.name,i.brand,i.unit,i.location from borrow_requests br join borrow_items bi on bi.borrow_request_id=br.id left join inventories i on i.id=bi.inventory_id where br.employee_id=%s and br.status='borrowed' and br.deleted_at is null and bi.is_returned=false order by br.created_at desc,bi.id",
            (user["id"],),
        ).fetchall()
        return {
            "items": [
                {
                    "borrow_item_id": r["borrow_item_id"],
                    "request_number": r["request_number"],
                    "request_id": r["request_id"],
                    "borrow_date": str(r["borrow_date"]),
                    "due_date": str(r["due_date"]),
                    "quantity": r["quantity"],
                    "condition_borrowed": r["condition_borrowed"],
                    "inventory": {
                        "id": r["inventory_id"],
                        "item_code": r["item_code"],
                        "name": r["name"],
                        "brand": r["brand"],
                        "unit": r["unit"],
                        "location": r["location"],
                    },
                }
                for r in rows
            ]
        }


@app.get(API + "/borrow-requests/{request_id}")
def get_request(request_id: int, user=Depends(get_user)):
    with connect() as conn:
        row = conn.execute(
            "select id from borrow_requests where id=%s and employee_id=%s and deleted_at is null",
            (request_id, user["id"]),
        ).fetchone()
        if not row:
            raise ApiError(404, "Borrow request not found.")
        return {"request": request_payload(conn, request_id)}


@app.post(API + "/borrow-requests", status_code=201)
async def create_request(request: Request, user=Depends(get_user)):
    data = await json_body(request)
    idempotency_key = request.headers.get("Idempotency-Key") or data.get("idempotency_key")
    if not isinstance(idempotency_key, str) or not idempotency_key.strip() or len(idempotency_key) > 64:
        invalid("idempotency_key", "A valid idempotency key is required.")
    try:
        borrow_date = (
            date.fromisoformat(data.get("borrow_date", ""))
            if isinstance(data.get("borrow_date"), str)
            else None
        )
        due_date = (
            date.fromisoformat(data.get("due_date", ""))
            if isinstance(data.get("due_date"), str)
            else None
        )
    except ValueError:
        invalid("borrow_date", "Enter valid borrow and due dates.")
    reason, items = data.get("reason"), data.get("items")
    if not borrow_date or borrow_date < business_today():
        invalid("borrow_date", "Borrow date must be today or later.")
    if not due_date or due_date <= borrow_date:
        invalid("due_date", "Due date must be after the borrow date.")
    if not isinstance(reason, str) or not reason.strip() or len(reason) > 2000:
        invalid("reason", "A reason of up to 2000 characters is required.")
    if not isinstance(items, list) or not items:
        invalid("items", "At least one inventory item is required.")
    normalized = []
    for item in items:
        if (
            not isinstance(item, dict)
            or not isinstance(item.get("inventory_id"), int)
            or not isinstance(item.get("quantity"), int)
            or item["quantity"] < 1
        ):
            invalid(
                "items", "Each item needs a valid inventory id and positive quantity."
            )
        normalized.append((item["inventory_id"], item["quantity"]))
    if len({x[0] for x in normalized}) != len(normalized):
        invalid("items", "Each inventory item can only be requested once.")
    request_hash = hashlib.sha256(
        json.dumps(
            {
                "borrow_date": borrow_date.isoformat(),
                "due_date": due_date.isoformat(),
                "reason": reason.strip(),
                "items": sorted(normalized),
            },
            separators=(",", ":"),
        ).encode()
    ).hexdigest()
    try:
        with transaction() as conn:
            idempotency = conn.execute(
                "insert into mobile_borrow_idempotency(employee_id,idempotency_key,request_hash,created_at) values (%s,%s,%s,now()) on conflict(employee_id,idempotency_key) do nothing returning employee_id",
                (user["id"], idempotency_key.strip(), request_hash),
            ).fetchone()
            if not idempotency:
                previous = conn.execute(
                    "select request_hash,response_payload from mobile_borrow_idempotency where employee_id=%s and idempotency_key=%s for update",
                    (user["id"], idempotency_key.strip()),
                ).fetchone()
                if previous["request_hash"] != request_hash:
                    raise ApiError(409, "This idempotency key was already used with different request details.")
                if previous["response_payload"] is None:
                    raise ApiError(409, "This request is still being processed. Please retry shortly.")
                response = previous["response_payload"]
                if isinstance(response, str):
                    response = json.loads(response)
                return JSONResponse(response, status_code=200)
            ids = sorted(x[0] for x in normalized)
            rows = conn.execute(
                "select id,name,unit,quantity,status,is_active,deleted_at,low_stock_threshold from inventories where id=any(%s) order by id for update",
                (ids,),
            ).fetchall()
            by_id = {r["id"]: r for r in rows}
            for iid, qty in normalized:
                r = by_id.get(iid)
                if (
                    not r
                    or r["deleted_at"] is not None
                    or not r["is_active"]
                    or r["status"] != "available"
                ):
                    raise ApiError(
                        409, "One of the selected items is no longer available."
                    )
                if r["quantity"] < qty:
                    raise ApiError(409, f"Insufficient stock for {r['name']}.")
            req = conn.execute(
                "insert into borrow_requests(request_number,employee_id,status,reason,borrow_date,due_date,created_at,updated_at) values (%s,%s,'pending',%s,%s,%s,now(),now()) returning id,request_number,borrow_date",
                (
                    "BR-" + secrets.token_hex(16).upper(),
                    user["id"],
                    reason.strip(),
                    borrow_date,
                    due_date,
                ),
            ).fetchone()
            for iid, qty in normalized:
                before = by_id[iid]
                after_quantity = before["quantity"] - qty
                conn.execute(
                    "insert into borrow_items(borrow_request_id,inventory_id,quantity,reserved_quantity,condition_borrowed,created_at,updated_at) values (%s,%s,%s,%s,'good',now(),now())",
                    (req["id"], iid, qty, qty),
                )
                conn.execute(
                    "update inventories set quantity=quantity-%s,updated_at=now() where id=%s",
                    (qty, iid),
                )
                audit_inventory_quantity(
                    conn, user["id"], iid, before["quantity"], after_quantity, request
                )
                if (
                    before["quantity"] > before["low_stock_threshold"]
                    and after_quantity <= before["low_stock_threshold"]
                ):
                    notify_admins_low_stock(
                        conn,
                        {"id": iid, "name": before["name"], "quantity": after_quantity},
                    )
            audit(
                conn,
                user["id"],
                "BorrowRequest created",
                "App\\Models\\BorrowRequest",
                req["id"],
                {"status": "pending", "reason": reason.strip()},
                ip=request.client.host if request.client else None,
                url=str(request.url),
            )
            n = {
                **req,
                "employee_name": user["name"],
                "due_date": due_date,
                "reason": reason.strip(),
            }
            notify_admins(conn, n)
            response = {
                "message": "Borrow request submitted.",
                "request": request_payload(conn, req["id"]),
            }
            conn.execute(
                "update mobile_borrow_idempotency set response_payload=%s::jsonb where employee_id=%s and idempotency_key=%s",
                (json.dumps(response), user["id"], idempotency_key.strip()),
            )
        email_admins_about_request(n)
        return response
    except (ValueError, TypeError):
        invalid("borrow_date", "Enter valid borrow and due dates.")


@app.post(API + "/borrow-requests/{request_id}/cancel")
def cancel_request(request_id: int, request: Request, user=Depends(get_user)):
    with transaction() as conn:
        req = conn.execute(
            "select * from borrow_requests where id=%s and employee_id=%s and deleted_at is null for update",
            (request_id, user["id"]),
        ).fetchone()
        if not req:
            raise ApiError(404, "Borrow request not found.")
        if req["status"] != "pending":
            if req["status"] == "cancelled":
                return {"message": "Borrow request cancelled.", "request": request_payload(conn, request_id)}
            raise ApiError(409, "Only pending requests can be cancelled.")
        items = conn.execute(
            "select id,inventory_id,reserved_quantity from borrow_items where borrow_request_id=%s and reserved_quantity>0 for update",
            (request_id,),
        ).fetchall()
        for it in items:
            inventory = conn.execute(
                "select quantity from inventories where id=%s for update",
                (it["inventory_id"],),
            ).fetchone()
            conn.execute(
                "update inventories set quantity=quantity+%s,updated_at=now() where id=%s",
                (it["reserved_quantity"], it["inventory_id"]),
            )
            conn.execute(
                "update borrow_items set reserved_quantity=0,updated_at=now() where id=%s",
                (it["id"],),
            )
            audit_inventory_quantity(
                conn,
                user["id"],
                it["inventory_id"],
                inventory["quantity"],
                inventory["quantity"] + it["reserved_quantity"],
                request,
            )
        conn.execute(
            "update borrow_requests set status='cancelled',cancelled_at=now(),updated_at=now() where id=%s",
            (request_id,),
        )
        audit(
            conn,
            user["id"],
            "BorrowRequest updated",
            "App\\Models\\BorrowRequest",
            request_id,
            {"status": "cancelled"},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
        response = {
            "message": "Borrow request cancelled.",
            "request": request_payload(conn, request_id),
        }
        email_context = {
            "request_number": req["request_number"],
            "employee_name": user["name"],
        }
    recipient = (
        os.getenv("ADMIN_NOTIFICATION_EMAIL")
        or os.getenv("BACKUP_NOTIFICATION_EMAIL")
        or os.getenv("SYSTEM_NOTIFICATION_EMAIL")
        or ""
    ).strip()
    send_email(
        recipient,
        "Borrow Request Status Changed - RGV Multi-Tech Services",
        f"Borrow request {email_context['request_number']} was cancelled by {email_context['employee_name']}.\n"
        f"Review: {os.getenv('APP_URL', '').rstrip('/')}/admin/borrow-requests/{request_id}\n",
    )
    return response


@app.post(API + "/borrow-requests/{request_id}/return")
async def return_request(request_id: int, request: Request, user=Depends(get_user)):
    data = await json_body(request)
    idem = data.get("idempotency_key")
    location = data.get("return_location")
    items = data.get("items")
    if not isinstance(idem, str) or not idem.strip() or len(idem) > 64:
        invalid("idempotency_key", "A valid idempotency key is required.")
    if not isinstance(location, str) or not location.strip() or len(location) > 255:
        invalid("return_location", "A return location is required.")
    if not isinstance(items, list) or not items:
        invalid("items", "At least one item is required.")
    normalized = []
    for item in items:
        if (
            not isinstance(item, dict)
            or not isinstance(item.get("borrow_item_id"), int)
            or item.get("condition_returned")
            not in ("new", "good", "fair", "poor", "damaged")
        ):
            invalid("items", "Invalid return item or condition.")
        notes = item.get("damage_notes")
        if notes is not None and (not isinstance(notes, str) or len(notes) > 1000):
            invalid("items", "Damage notes may not exceed 1000 characters.")
        normalized.append(
            {
                "borrow_item_id": item["borrow_item_id"],
                "condition_returned": item["condition_returned"],
                "damage_notes": notes,
            }
        )
    if len({x["borrow_item_id"] for x in normalized}) != len(normalized):
        invalid("items", "Each borrowed item can only be returned once per request.")
    request_json = {"return_location": location.strip(), "items": normalized}
    # Match Laravel's shared idempotency representation so a retry can cross
    # between both services using the same key.
    request_hash = hashlib.sha256(
        json.dumps(request_json, ensure_ascii=False, separators=(",", ":")).encode()
    ).hexdigest()
    with transaction() as conn:
        req = conn.execute(
            "select * from borrow_requests where id=%s and employee_id=%s and deleted_at is null for update",
            (request_id, user["id"]),
        ).fetchone()
        if not req:
            raise ApiError(404, "Borrow request not found.")
        cached = conn.execute(
            "select request_payload,response_payload from mobile_return_idempotency where employee_id=%s and borrow_request_id=%s and idempotency_key=%s",
            (user["id"], request_id, idem.strip()),
        ).fetchone()
        if cached:
            previous = cached["request_payload"]
            if isinstance(previous, str):
                previous = json.loads(previous)
            if previous not in (request_hash, request_json):
                raise ApiError(
                    409,
                    "This idempotency key was already used with different return details.",
                )
            response = cached["response_payload"]
            if isinstance(response, str):
                response = json.loads(response)
            return response
        if req["status"] not in ("borrowed", "returned"):
            raise ApiError(409, "Only borrowed requests can be returned.")
        if req["status"] == "returned":
            payload = {
                "message": "Items returned successfully.",
                "request": request_payload(conn, request_id),
            }
            conn.execute(
                "insert into mobile_return_idempotency(employee_id,borrow_request_id,idempotency_key,request_payload,response_payload,created_at) values (%s,%s,%s,%s::jsonb,%s::jsonb,now())",
                (
                    user["id"],
                    request_id,
                    idem.strip(),
                    json.dumps(request_hash),
                    json.dumps(payload),
                ),
            )
            return payload
        for item in normalized:
            bi = conn.execute(
                "select * from borrow_items where id=%s and borrow_request_id=%s for update",
                (item["borrow_item_id"], request_id),
            ).fetchone()
            if not bi:
                invalid("items", "A selected item does not belong to this request.")
            if not bi["is_returned"]:
                restore = (
                    bi["reserved_quantity"]
                    if bi["reserved_quantity"] > 0
                    else bi["quantity"]
                )
                inventory = conn.execute(
                    "select quantity from inventories where id=%s for update",
                    (bi["inventory_id"],),
                ).fetchone()
                conn.execute(
                    "update borrow_items set condition_returned=%s,damage_notes=%s,is_returned=true,returned_at=now(),reserved_quantity=0,updated_at=now() where id=%s",
                    (item["condition_returned"], item["damage_notes"], bi["id"]),
                )
                conn.execute(
                    "update inventories set quantity=quantity+%s,updated_at=now() where id=%s",
                    (restore, bi["inventory_id"]),
                )
                audit_inventory_quantity(
                    conn,
                    user["id"],
                    bi["inventory_id"],
                    inventory["quantity"],
                    inventory["quantity"] + restore,
                    request,
                )
        conn.execute(
            "update borrow_requests set return_location=%s,updated_at=now() where id=%s",
            (location.strip(), request_id),
        )
        remaining = conn.execute(
            "select exists(select 1 from borrow_items where borrow_request_id=%s and is_returned=false) as remaining",
            (request_id,),
        ).fetchone()["remaining"]
        completed = not remaining
        if completed:
            conn.execute(
                "update borrow_requests set status='returned',return_date=current_date,returned_at=now(),updated_at=now() where id=%s",
                (request_id,),
            )
            pref = conn.execute(
                "select in_app_enabled,email_enabled from notification_preferences where user_id=%s and type='borrow_request'",
                (user["id"],),
            ).fetchone()
            if pref is None or pref["in_app_enabled"]:
                conn.execute(
                    "insert into notifications(user_id,type,title,message,link,is_read,created_at,updated_at) values (%s,'borrow_request','Items Returned','Your borrowed items have been successfully returned.',%s,false,now(),now())",
                    (user["id"], f"/employee/borrow-requests/{request_id}"),
                )
        audit(
            conn,
            user["id"],
            "BorrowRequest returned",
            "App\\Models\\BorrowRequest",
            request_id,
            {"return_location": location.strip(), "items": normalized},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
        payload = {
            "message": "Items returned successfully.",
            "request": request_payload(conn, request_id),
        }
        conn.execute(
            "insert into mobile_return_idempotency(employee_id,borrow_request_id,idempotency_key,request_payload,response_payload,created_at) values (%s,%s,%s,%s::jsonb,%s::jsonb,now())",
            (
                user["id"],
                request_id,
                idem.strip(),
                json.dumps(request_hash),
                json.dumps(payload),
            ),
        )
        return_email_enabled = completed and (pref is None or pref["email_enabled"])
    if return_email_enabled:
        send_email(
            user["email"],
            "Items Returned - RGV Multi-Tech Services",
            f"Your borrowed items have been successfully returned.\nRequest Number: {req['request_number']}\n"
            f"Return Date: {payload['request']['return_date']}\n"
            f"View: {os.getenv('APP_URL', '').rstrip('/')}/employee/borrow-requests/{request_id}\n",
        )
    return payload


@app.patch(API + "/profile")
async def update_profile(request: Request, user=Depends(get_user)):
    data = await json_body(request)
    name = data.get("name")
    if not isinstance(name, str) or not name.strip() or len(name) > 255:
        invalid("name", "Enter a valid name.")
    with transaction() as conn:
        conn.execute(
            "update users set name=%s,updated_at=now() where id=%s",
            (name.strip(), user["id"]),
        )
        audit(
            conn,
            user["id"],
            "User updated",
            "App\\Models\\User",
            user["id"],
            {"name": name.strip()},
            old_values={"name": user["name"]},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
        row = conn.execute(
            "select id,name,email,phone,avatar_path from users where id=%s",
            (user["id"],),
        ).fetchone()
        return {
            "message": "Profile updated.",
            "user": {
                "id": row["id"],
                "name": row["name"],
                "email": row["email"],
                "phone": row.get("phone"),
                "avatar_path": row.get("avatar_path"),
            },
        }


@app.put(API + "/password")
async def change_password(request: Request, user=Depends(get_user)):
    data = await json_body(request)
    current = data.get("current_password")
    new = data.get("new_password")
    if not isinstance(current, str) or not isinstance(new, str):
        invalid("new_password", "Enter the current and new password.")
    if data.get("new_password_confirmation") != new:
        invalid("new_password", "The password confirmation does not match.")
    if (
        len(new) < int(os.environ.get("PASSWORD_MIN_LENGTH", "8"))
        or not any(c.isalpha() for c in new)
        or not any(c.islower() for c in new)
        or not any(c.isupper() for c in new)
        or not any(c.isdigit() for c in new)
    ):
        invalid(
            "new_password",
            "The password must contain letters, mixed case, and numbers.",
        )
    with transaction() as conn:
        row = conn.execute(
            "select password from users where id=%s for update", (user["id"],)
        ).fetchone()
        if not verify_password(current, row["password"]):
            invalid("current_password", "The current password is incorrect.")
        conn.execute(
            "update users set password=%s,updated_at=now() where id=%s",
            (hash_password(new), user["id"]),
        )
        token_id = int(request.headers["authorization"][7:].split("|", 1)[0])
        conn.execute(
            "delete from personal_access_tokens where tokenable_id=%s and tokenable_type='App\\Models\\User' and id<>%s",
            (user["id"], token_id),
        )
        audit(
            conn,
            user["id"],
            "User updated",
            "App\\Models\\User",
            user["id"],
            {},
            ip=request.client.host if request.client else None,
            url=str(request.url),
        )
    return {"message": "Password updated."}
