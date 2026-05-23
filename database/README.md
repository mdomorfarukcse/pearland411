# Database

This project did not previously ship its schema in git. As we make
schema changes for security/feature work, the DDL lands here as
numbered SQL files (`0001_*.sql`, `0002_*.sql`, ...).

## Running migrations

Each file is safe to run **once** against the live MySQL database
and (where MySQL allows) idempotent on re-run:

```bash
mysql -u <user> -p <db_name> < database/migrations/0001_auth_flows.sql
```

## Outstanding follow-up

A complete schema dump still needs to be added (see audit
recommendation D1). Suggested:

```bash
mysqldump --no-data --routines --triggers <db_name> > database/schema.sql
mysqldump --no-create-info --skip-extended-insert <db_name> \
    listing_categories listing_sub_categories plans plan_durations \
    > database/seed_reference.sql
```

This keeps the live schema and reference data reproducible.
