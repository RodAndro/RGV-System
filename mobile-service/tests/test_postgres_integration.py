"""Integration tests against an explicitly disposable PostgreSQL *_test database.

Set TEST_DATABASE_URL to a local test-only PostgreSQL database. A random schema
is created and dropped; the test refuses database names without a `_test` suffix.
No Laravel process or endpoint is used by these tests.
"""

import os
import secrets
from urllib.parse import quote

import psycopg
import pytest
from fastapi.testclient import TestClient


TEST_URL = os.getenv("TEST_DATABASE_URL")
if TEST_URL and not psycopg.conninfo.conninfo_to_dict(TEST_URL).get(
    "dbname", ""
).endswith("_test"):
    raise RuntimeError(
        "TEST_DATABASE_URL must point to a database whose name ends with _test"
    )
pytestmark = pytest.mark.skipif(
    not TEST_URL, reason="TEST_DATABASE_URL is not configured for disposable PostgreSQL"
)


@pytest.fixture
def client(monkeypatch):
    schema = "mobile_test_" + secrets.token_hex(5)
    with psycopg.connect(TEST_URL, autocommit=True) as admin:
        admin.execute(f'create schema "{schema}"')
        ddl = open(
            os.path.join(os.path.dirname(__file__), "schema.sql"), encoding="utf-8"
        ).read()
        admin.execute(ddl)
    monkeypatch.setenv(
        "DATABASE_URL",
        TEST_URL
        + ("&" if "?" in TEST_URL else "?")
        + "options="
        + quote(f"-csearch_path={schema}"),
    )
    monkeypatch.setenv("AUDIT_HMAC_KEY", "test-only-mobile-service-audit-key")
    from app.main import app

    with TestClient(app) as test_client:
        yield test_client
    with psycopg.connect(TEST_URL, autocommit=True) as admin:
        admin.execute(f'drop schema "{schema}" cascade')


def seed_employee(client, email="employee@example.test", password="Shared-Pass-123"):
    from app.database import transaction
    from app.security import hash_password

    with transaction() as conn:
        row = conn.execute(
            "insert into users(name,email,password,is_active,mfa_enabled) values (%s,%s,%s,true,false) returning id",
            ("Test Employee", email, hash_password(password)),
        ).fetchone()
        role = conn.execute(
            "insert into roles(name,guard_name) values ('employee','web') returning id"
        ).fetchone()
        conn.execute(
            "insert into model_has_roles(role_id,model_type,model_id) values (%s,'App\\Models\\User',%s)",
            (role["id"], row["id"]),
        )
    return email, password, row["id"]


def test_laravel_format_shared_account_auth_and_employee_authorization(client):
    email, password, user_id = seed_employee(client)
    result = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    )
    assert result.status_code == 200
    token = result.json()["token"]
    assert result.json()["user"]["id"] == user_id
    assert (
        client.get(
            "/api/v1/mobile/me", headers={"Authorization": f"Bearer {token}"}
        ).json()["user"]["email"]
        == email
    )

    with __import__("app.database", fromlist=["transaction"]).transaction() as conn:
        conn.execute("update users set is_active=false where id=%s", (user_id,))
    assert (
        client.get(
            "/api/v1/mobile/me", headers={"Authorization": f"Bearer {token}"}
        ).status_code
        == 403
    )


def test_admin_role_is_not_authorized_for_mobile_workflows(client):
    from app.database import transaction
    from app.security import hash_password

    with transaction() as conn:
        user = conn.execute(
            "insert into users(name,email,password,is_active,mfa_enabled) values ('Admin','admin@example.test',%s,true,false) returning id",
            (hash_password("Admin-Pass-123"),),
        ).fetchone()
        role = conn.execute(
            "insert into roles(name,guard_name) values ('admin','web') returning id"
        ).fetchone()
        conn.execute(
            "insert into model_has_roles(role_id,model_type,model_id) values (%s,'App\\Models\\User',%s)",
            (role["id"], user["id"]),
        )
    response = client.post(
        "/api/v1/mobile/auth/login",
        json={"email": "admin@example.test", "password": "Admin-Pass-123"},
    )
    assert response.status_code == 422


def test_qr_lookup_prefers_the_exact_immutable_token_and_reports_unknown_codes(client):
    from app.database import transaction

    email, password, _ = seed_employee(client)
    with transaction() as conn:
        category = conn.execute(
            "insert into inventory_categories(name) values ('Tools') returning id"
        ).fetchone()["id"]
        item_id = conn.execute(
            "insert into inventories(item_code,name,category_id,quantity,low_stock_threshold,status,is_active,qr_token) values ('OLD-CODE','Updated drill',%s,2,1,'available',true,'QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA') returning id",
            (category,),
        ).fetchone()["id"]
    token = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()["token"]
    headers = {"Authorization": f"Bearer {token}"}
    code = '{"item_code":"OLD-CODE","qr_token":"QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"}'
    found = client.get("/api/v1/mobile/inventory/lookup", headers=headers, params={"code": code})
    assert found.status_code == 200 and found.json()["item"]["id"] == item_id
    unknown = client.get("/api/v1/mobile/inventory/lookup", headers=headers, params={"code": "QR-UNKNOWN"})
    assert unknown.status_code == 422


def test_mfa_challenge_token_cannot_read_data_until_totp_verified(client):
    from app.database import transaction

    email, password, user_id = seed_employee(client)
    secret = "JBSWY3DPEHPK3PXP"
    with transaction() as conn:
        conn.execute(
            "update users set mfa_enabled=true,mfa_type='totp',mfa_secret=%s where id=%s",
            (secret, user_id),
        )
    login = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()
    pending = login["mfa_token"]
    assert (
        client.get(
            "/api/v1/mobile/me", headers={"Authorization": f"Bearer {pending}"}
        ).status_code
        == 403
    )
    import base64
    import hashlib
    import hmac
    import struct
    import time

    counter = int(time.time() // 30)
    digest = hmac.new(
        base64.b32decode(secret), struct.pack(">Q", counter), hashlib.sha1
    ).digest()
    offset = digest[-1] & 15
    code = f"{((struct.unpack('>I', digest[offset : offset + 4])[0] & 0x7FFFFFFF) % 1000000):06d}"
    verified = client.post(
        "/api/v1/mobile/auth/mfa/verify",
        headers={"Authorization": f"Bearer {pending}"},
        json={"code": code},
    )
    assert verified.status_code == 200
    assert (
        client.get(
            "/api/v1/mobile/me",
            headers={"Authorization": f"Bearer {verified.json()['token']}"},
        ).status_code
        == 200
    )


def test_wrong_mfa_code_is_rejected(client):
    from app.database import transaction

    email, password, user_id = seed_employee(client)
    with transaction() as conn:
        conn.execute(
            "update users set mfa_enabled=true,mfa_type='totp',mfa_secret='JBSWY3DPEHPK3PXP' where id=%s",
            (user_id,),
        )
    pending = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()["mfa_token"]
    response = client.post(
        "/api/v1/mobile/auth/mfa/verify",
        headers={"Authorization": f"Bearer {pending}"},
        json={"code": "000000"},
    )
    assert response.status_code == 422


def test_mfa_recovery_code_is_consumed_once_in_the_shared_user_row(client):
    from app.database import transaction

    email, password, user_id = seed_employee(client)
    with transaction() as conn:
        conn.execute(
            "update users set mfa_enabled=true,mfa_type='totp',mfa_secret='JBSWY3DPEHPK3PXP',mfa_recovery_codes=%s::json where id=%s",
            ('["deadbeef-12345678"]', user_id),
        )
    pending = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()["mfa_token"]
    verify = client.post(
        "/api/v1/mobile/auth/mfa/verify",
        headers={"Authorization": f"Bearer {pending}"},
        json={"code": "DEADBEEF-12345678"},
    )
    assert verify.status_code == 200
    with transaction() as conn:
        assert (
            conn.execute(
                "select mfa_recovery_codes from users where id=%s", (user_id,)
            ).fetchone()["mfa_recovery_codes"]
            == []
        )


def test_borrow_reservation_cancel_and_return_are_transactional_and_idempotent(client):
    from app.database import transaction

    email, password, user_id = seed_employee(client)
    with transaction() as conn:
        admin = conn.execute(
            "insert into users(name,email,password,is_active,mfa_enabled) values ('Admin','admin@example.test','unused',true,false) returning id"
        ).fetchone()
        admin_role = conn.execute(
            "insert into roles(name,guard_name) values ('admin','web') returning id"
        ).fetchone()
        conn.execute(
            "insert into model_has_roles(role_id,model_type,model_id) values (%s,'App\\Models\\User',%s)",
            (admin_role["id"], admin["id"]),
        )
        category = conn.execute(
            "insert into inventory_categories(name) values ('Tools') returning id"
        ).fetchone()["id"]
        inventory = conn.execute(
            "insert into inventories(item_code,name,category_id,quantity,low_stock_threshold,status,is_active) values ('RGV-01','Drill',%s,3,1,'available',true) returning id",
            (category,),
        ).fetchone()["id"]
    token = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()["token"]
    headers = {"Authorization": f"Bearer {token}"}
    req = {
        "borrow_date": __import__("datetime").date.today().isoformat(),
        "due_date": (
            __import__("datetime").date.today()
            + __import__("datetime").timedelta(days=7)
        ).isoformat(),
        "reason": "Site work",
        "items": [{"inventory_id": inventory, "quantity": 2}],
    }
    created = client.post(
        "/api/v1/mobile/borrow-requests",
        headers={**headers, "Idempotency-Key": "borrow-first"},
        json=req,
    )
    assert created.status_code == 201
    repeated = client.post(
        "/api/v1/mobile/borrow-requests",
        headers={**headers, "Idempotency-Key": "borrow-first"},
        json=req,
    )
    assert repeated.status_code == 200 and repeated.json() == created.json()
    assert client.post(
        "/api/v1/mobile/borrow-requests",
        headers={**headers, "Idempotency-Key": "borrow-first"},
        json={**req, "reason": "Different request"},
    ).status_code == 409
    request_id = created.json()["request"]["id"]
    with transaction() as conn:
        assert (
            conn.execute(
                "select quantity from inventories where id=%s", (inventory,)
            ).fetchone()["quantity"]
            == 1
        )
        titles = {
            r["title"]
            for r in conn.execute("select title from notifications").fetchall()
        }
        assert {"Low Stock Alert", "New Borrow Request Received"} <= titles
    # A second pending request can be cancelled and its reservation is restored.
    second = client.post(
        "/api/v1/mobile/borrow-requests",
        headers={**headers, "Idempotency-Key": "borrow-second"},
        json={**req, "items": [{"inventory_id": inventory, "quantity": 1}]},
    )
    assert second.status_code == 201
    cancelled = client.post(
        f"/api/v1/mobile/borrow-requests/{second.json()['request']['id']}/cancel",
        headers=headers,
    )
    assert (
        cancelled.status_code == 200
        and cancelled.json()["request"]["status"] == "cancelled"
    )
    assert client.post(
        f"/api/v1/mobile/borrow-requests/{second.json()['request']['id']}/cancel",
        headers=headers,
    ).status_code == 200
    with transaction() as conn:
        assert (
            conn.execute(
                "select quantity from inventories where id=%s", (inventory,)
            ).fetchone()["quantity"]
            == 1
        )
        conn.execute(
            "update borrow_requests set status='borrowed' where id=%s", (request_id,)
        )
    ret = {
        "idempotency_key": "test-key-0001",
        "return_location": "Main office",
        "items": [
            {
                "borrow_item_id": created.json()["request"]["items"][0]["id"],
                "condition_returned": "good",
            }
        ],
    }
    first = client.post(
        f"/api/v1/mobile/borrow-requests/{request_id}/return", headers=headers, json=ret
    )
    again = client.post(
        f"/api/v1/mobile/borrow-requests/{request_id}/return", headers=headers, json=ret
    )
    assert first.status_code == 200 and again.json() == first.json()
    with transaction() as conn:
        assert (
            conn.execute(
                "select quantity from inventories where id=%s", (inventory,)
            ).fetchone()["quantity"]
            == 3
        )
        stored_payload = conn.execute(
            "select request_payload from mobile_return_idempotency"
        ).fetchone()["request_payload"]
        assert isinstance(stored_payload, str) and len(stored_payload) == 64
        assert (
            conn.execute("select count(*) from mobile_return_idempotency").fetchone()[
                "count"
            ]
            == 1
        )
        assert (
            conn.execute(
                "select count(*) from notifications where user_id=%s and title='Items Returned'",
                (user_id,),
            ).fetchone()["count"]
            == 1
        )
    conflicting = {**ret, "return_location": "Different location"}
    assert (
        client.post(
            f"/api/v1/mobile/borrow-requests/{request_id}/return",
            headers=headers,
            json=conflicting,
        ).status_code
        == 409
    )


def test_profile_and_password_changes_update_the_shared_user_record(client):
    from app.database import transaction

    email, password, user_id = seed_employee(client)
    token = client.post(
        "/api/v1/mobile/auth/login", json={"email": email, "password": password}
    ).json()["token"]
    headers = {"Authorization": f"Bearer {token}"}
    updated = client.patch(
        "/api/v1/mobile/profile", headers=headers, json={"name": "Updated Employee"}
    )
    assert (
        updated.status_code == 200
        and updated.json()["user"]["name"] == "Updated Employee"
    )
    changed = client.put(
        "/api/v1/mobile/password",
        headers=headers,
        json={
            "current_password": password,
            "new_password": "New-Shared-456",
            "new_password_confirmation": "New-Shared-456",
        },
    )
    assert changed.status_code == 200
    assert (
        client.post(
            "/api/v1/mobile/auth/login",
            json={"email": email, "password": "New-Shared-456"},
        ).status_code
        == 200
    )
    with transaction() as conn:
        assert (
            conn.execute("select name from users where id=%s", (user_id,)).fetchone()[
                "name"
            ]
            == "Updated Employee"
        )
