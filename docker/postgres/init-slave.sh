#!/bin/bash
set -e

echo "Waiting for master to be ready..."
until pg_isready -h pg-master -U postgres -d zita_rutas; do
    sleep 2
done

sleep 5

if [ ! -f /var/lib/postgresql/data/PG_VERSION ]; then
    echo "Initializing slave from master..."
    rm -rf /var/lib/postgresql/data/*
    pg_basebackup -h pg-master -U replicator -D /var/lib/postgresql/data -Fp -Xs -P -R
    chmod 0700 /var/lib/postgresql/data
    echo "Slave initialization complete."
fi
