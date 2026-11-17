from fastapi.testclient import TestClient

from app import main
from app.main import app


def test_standalone_service_liveness_does_not_require_laravel_or_database():
    with TestClient(app) as client:
        response = client.get("/live")
    assert response.status_code == 200
    assert response.json() == {"status": "ok"}


def test_health_checks_existing_mobile_return_idempotency_columns(monkeypatch):
    class FakeConnection:
        def __init__(self):
            self.queries = []

        def __enter__(self):
            return self

        def __exit__(self, *_):
            return False

        def execute(self, query):
            self.queries.append(query)

    connection = FakeConnection()
    monkeypatch.setenv("AUDIT_HMAC_KEY", "test-audit-key")
    monkeypatch.setattr(main, "connect", lambda: connection)

    assert main.health() == {"status": "ok"}
    assert (
        "select employee_id,borrow_request_id,idempotency_key "
        "from mobile_return_idempotency limit 0"
    ) in connection.queries


def test_inventory_payload_exposes_qr_token_for_the_offline_catalog():
    payload = main.inventory_payload(
        {
            "id": 9,
            "item_code": "RGV-009",
            "name": "Handheld Jackhammer",
            "brand": "INGCO",
            "description": None,
            "category": "Tools",
            "quantity": 3,
            "unit": "unit",
            "status": "available",
            "condition": "good",
            "is_active": True,
            "qr_token": "QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",
        }
    )

    assert payload["qr_token"] == "QR-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"


def test_inventory_payload_tolerates_items_without_a_qr_token():
    payload = main.inventory_payload(
        {
            "id": 1,
            "item_code": "RGV-001",
            "name": "Item",
            "quantity": 1,
            "unit": "pcs",
            "status": "available",
            "condition": "good",
            "is_active": True,
        }
    )

    assert payload["qr_token"] is None
