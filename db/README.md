# Database Design (MySQL 8)

This folder contains the SQL schema for the Dinggo PHP challenge.

## Files

- `schema.sql`: full schema with database creation, tables, keys, and constraints.

## Tables

- `car`: stores vehicles from `POST /phptest/cars`.
- `quote`: stores quote results from `POST /phptest/quotes` linked to a car.
- `sync_run`: optional operational tracking for each ingestion run.

## Why this schema

- Keeps `car` aligned with the API payload and ready for ingestion use.
- Keeps `quote` aligned strictly with current API payload fields:
  - `price`
  - `repairer`
  - `overview_of_work`
- Supports simple FE pages:
  - cars list page
  - car detail page with quotes
- Adds indexes for likely query paths (`car_id`, `make/model`, `fetched_at`).

## Apply schema

From workspace root:

```bash
docker exec -i test-car-mysql mysql -uroot -proot < db/schema.sql
```

If your MySQL container is not running yet, create it first:

```bash
docker rm -f test-car-mysql >/dev/null 2>&1 || true
docker run -d \
  --name test-car-mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=dinggo_challenge \
  -p 3307:3306 \
  mysql:8
```
