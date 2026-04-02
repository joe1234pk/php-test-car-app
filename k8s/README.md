# Kubernetes deployment

This directory provides a `kustomize`-based deployment setup for the current monorepo structure.

## Structure

- `base/`: reusable manifests for namespace, mysql, db init, backend, frontend, and ingress
- `overlays/dev`: dev-specific host and image tags
- `overlays/staging`: staging-specific host/app config and image tags
- `overlays/prod`: production-specific host/app config/HPA and image tags

## Prerequisites

- Kubernetes cluster access (`kubectl` configured)
- Ingress controller (default manifests assume `nginx` class)
- Container registry with pushed images for:
  - `cartest-be`
  - `cartest-fe`

## 1) Prepare images

Build and push images from repo root:

```bash
docker build -t your-registry/cartest-be:dev ./php-test-car-app-be
docker build -t your-registry/cartest-fe:dev ./php-test-car-app-fe
docker push your-registry/cartest-be:dev
docker push your-registry/cartest-fe:dev
```

Then update the matching overlay image mapping.

## 2) Configure secrets

Edit `base/secret.example.yaml` with real values:

- `MYSQL_ROOT_PASSWORD`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DINGGO_USERNAME`
- `DINGGO_KEY`

## 3) Deploy

Create/update DB schema ConfigMap from the single source of truth (`db/docker-entrypoint-initdb.d/schema.sql`):

```bash
kubectl create configmap cartest-db-schema \
  -n cartest \
  --from-file=schema.sql=db/docker-entrypoint-initdb.d/schema.sql \
  --dry-run=client -o yaml | kubectl apply -f -
```

Then apply manifests:

```bash
kubectl apply -k k8s/overlays/dev
```

For other environments:

```bash
kubectl create configmap cartest-db-schema -n cartest --from-file=schema.sql=db/docker-entrypoint-initdb.d/schema.sql --dry-run=client -o yaml | kubectl apply -f -
kubectl apply -k k8s/overlays/staging
kubectl apply -k k8s/overlays/prod
```

## 4) Validate

```bash
kubectl get pods -n cartest
kubectl get svc -n cartest
kubectl get ingress -n cartest
kubectl logs job/mysql-schema-init -n cartest
```

The DB schema is loaded by `mysql-schema-init` job from ConfigMap `cartest-db-schema`, sourced from `db/docker-entrypoint-initdb.d/schema.sql`.
