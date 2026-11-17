import base64
import hmac
import hashlib
import struct
import time

from app.security import hash_password, issue_token, totp_valid, verify_password


def test_laravel_bcrypt_hashes_are_verified_without_replacement():
    stored = hash_password("shared-account-password")
    assert stored.startswith("$2b$")
    assert verify_password("shared-account-password", stored)
    assert not verify_password("wrong", stored)


def test_laravel_2y_hash_marker_is_compatible():
    stored = hash_password("shared-account-password")
    laravel_hash = "$2y$" + stored[4:]
    assert verify_password("shared-account-password", laravel_hash)


def test_totp_accepts_current_six_digit_code_and_rejects_wrong_code():
    secret = "JBSWY3DPEHPK3PXP"
    counter = int(time.time() // 30)
    key = base64.b32decode(secret)
    digest = hmac.new(key, struct.pack(">Q", counter), hashlib.sha1).digest()
    offset = digest[-1] & 15
    number = (
        struct.unpack(">I", digest[offset : offset + 4])[0] & 0x7FFFFFFF
    ) % 1_000_000
    code = f"{number:06d}"
    assert totp_valid(secret, code)
    assert not totp_valid(secret, "000000" if code != "000000" else "000001")


def test_tokens_are_random_and_only_the_digest_is_stored():
    secret, digest = issue_token()
    assert secret not in digest
    assert len(digest) == 64
    assert issue_token()[0] != secret
