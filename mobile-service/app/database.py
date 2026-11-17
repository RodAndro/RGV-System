import os
from contextlib import contextmanager

import psycopg
from psycopg.rows import dict_row


def connect():
    dsn = os.environ.get("DATABASE_URL")
    if dsn:
        return psycopg.connect(dsn, row_factory=dict_row, connect_timeout=8)
    required = ("DB_HOST", "DB_NAME", "DB_USER", "DB_PASSWORD")
    missing = [key for key in required if not os.environ.get(key)]
    if missing:
        raise RuntimeError("Missing database configuration: " + ", ".join(missing))
    return psycopg.connect(
        host=os.environ["DB_HOST"],
        port=os.environ.get("DB_PORT", "5432"),
        dbname=os.environ["DB_NAME"],
        user=os.environ["DB_USER"],
        password=os.environ["DB_PASSWORD"],
        sslmode=os.environ.get("DB_SSLMODE", "verify-full"),
        sslrootcert=os.environ.get("DB_SSLROOTCERT") or None,
        connect_timeout=8,
        row_factory=dict_row,
    )


@contextmanager
def transaction():
    conn = connect()
    try:
        with conn.transaction():
            yield conn
    finally:
        conn.close()


def query_one(sql: str, params=()):
    with connect() as conn:
        return conn.execute(sql, params).fetchone()
