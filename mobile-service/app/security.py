import base64
import hashlib
import hmac
import secrets
import struct
import time

import bcrypt


def verify_password(plain: str, stored: str) -> bool:
    """Verify Laravel bcrypt hashes; never re-hash or replace on login."""
    if not stored.startswith(("$2y$", "$2a$", "$2b$")):
        return False
    compatible = "$2b$" + stored[4:] if stored.startswith(("$2y$", "$2a$")) else stored
    try:
        return bcrypt.checkpw(plain.encode(), compatible.encode())
    except (ValueError, TypeError):
        return False


def hash_password(plain: str) -> str:
    # Laravel accepts the standard bcrypt $2b$ marker.
    return bcrypt.hashpw(plain.encode(), bcrypt.gensalt(rounds=12)).decode()


def issue_token() -> tuple[str, str]:
    secret = secrets.token_urlsafe(48)
    return secret, hashlib.sha256(secret.encode()).hexdigest()


def totp_valid(secret: str | None, code: str, now: int | None = None) -> bool:
    if not secret or len(code) != 6 or not code.isdigit():
        return False
    try:
        key = base64.b32decode(secret.upper() + "=" * ((8 - len(secret) % 8) % 8))
    except (ValueError, base64.binascii.Error):
        return False
    counter = int((now if now is not None else time.time()) // 30)
    for offset in (-1, 0, 1):
        digest = hmac.new(
            key, struct.pack(">Q", counter + offset), hashlib.sha1
        ).digest()
        index = digest[-1] & 0x0F
        value = struct.unpack(">I", digest[index : index + 4])[0] & 0x7FFFFFFF
        expected = f"{value % 1_000_000:06d}"
        if hmac.compare_digest(expected, code):
            return True
    return False
