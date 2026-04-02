# cartest

Monorepo for the Dinggo PHP test solution with three components:

- `db` - MySQL schema/init scripts
- `php-test-car-app-be` - Lumen backend API
- `php-test-car-app-fe` - React + Tailwind frontend

## Repository structure

- `docker-compose.yml` (root): main orchestration for MySQL + BE (+ optional FE profile)
- `db/docker-entrypoint-initdb.d/schema.sql`: auto-init SQL for MySQL
- `php-test-car-app-be/`: backend source, tests, Dockerfile
- `php-test-car-app-fe/`: frontend source, `.nvmrc` (Node 24), Dockerfile
- `k8s/`: Kubernetes manifests (`base` + `overlays`)

## Prerequisites

- Docker installed and running

## Local setup (full project)

### Recommended: one-command setup script

Install Docker first, make sure Docker is running, then execute:

```bash
cd /Applications/php_projs/cartest
./setup.sh
```

Default behavior: starts MySQL + backend + frontend.

Useful flags:

```bash
./setup.sh --reset            # reset containers/volume, then setup
./setup.sh --backend-only     # start only mysql + backend
./setup.sh --smoke            # run API smoke calls after health check
```

### Manual option: Docker Compose only

From repo root:

```bash
cd /Applications/php_projs/cartest
docker compose --profile frontend up --build -d
docker compose ps
```

Backend-only manual startup:

```bash
cd /Applications/php_projs/cartest
docker compose up --build -d
docker compose ps
```

Notes:

- MySQL schema is auto-applied on first startup from:
  - `db/docker-entrypoint-initdb.d/schema.sql`
- Backend API runs at:
  - `http://localhost:8080`
- Frontend runs at:
  - `http://localhost:5173`

### Validate backend endpoints

```bash
curl -sS "http://localhost:8080/health"
curl -sS -X POST "http://localhost:8080/api/sync/cars"
curl -sS -X POST "http://localhost:8080/api/sync/quotes"
curl -sS "http://localhost:8080/api/cars"
curl -sS "http://localhost:8080/api/cars/1/quotes"
```

### Run backend unit tests

```bash
cd /Applications/php_projs/cartest
docker compose exec app composer install
docker compose exec app ./vendor/bin/phpunit tests/
```

## Kubernetes setup

Kubernetes manifests are available under `k8s/` with `kustomize` layout:

- `k8s/base`: shared namespace, mysql, db init job, backend, frontend, ingress
- `k8s/overlays/dev|staging|prod`: environment-specific image tags and patches

Quick start:

```bash
cd /Applications/php_projs/cartest
kubectl apply -f k8s/base/namespace.yaml
kubectl create configmap cartest-db-schema -n cartest --from-file=schema.sql=db/docker-entrypoint-initdb.d/schema.sql --dry-run=client -o yaml | kubectl apply -f -
kubectl apply -k k8s/overlays/dev
```

Before applying, update image names/tags in the overlay (`your-registry/cartest-be` and `your-registry/cartest-fe`) and set real secret values in `k8s/base/secret.example.yaml`.

## Common operations

Recreate app only:

```bash
docker compose up -d --force-recreate app
```

Reset DB volume (re-run init SQL on next up):

```bash
docker compose down -v
docker compose up --build -d
```

Stop stack:

```bash
docker compose down
```
