#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE ROLE replicator WITH REPLICATION LOGIN PASSWORD 'replicatorpass';
    SELECT pg_create_physical_replication_slot('slave_slot');
EOSQL

echo "Master initialization complete."
