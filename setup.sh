#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

WITH_FRONTEND=true
RESET=false
NO_BUILD=false
RUN_SMOKE=false

usage() {
  cat <<'EOF'
Usage: ./setup.sh [options]

Options:
  --backend-only    Start backend + database only
  --with-frontend   Explicitly include frontend profile (default behavior)
  --reset           Reset environment first (down -v + remove named containers)
  --no-build        Skip image build during compose up
  --smoke           Run API smoke calls after setup (sync cars/quotes)
  -h, --help        Show this help
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --with-frontend) WITH_FRONTEND=true ;;
    --backend-only) WITH_FRONTEND=false ;;
    --reset) RESET=true ;;
    --no-build) NO_BUILD=true ;;
    --smoke) RUN_SMOKE=true ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      usage
      exit 1
      ;;
  esac
  shift
done

require_command() {
  local cmd="$1"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "Missing required command: $cmd"
    exit 1
  fi
}

require_command docker
require_command curl

if ! docker info >/dev/null 2>&1; then
  echo "Docker daemon is not running. Please start Docker first."
  exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
  echo "Docker Compose plugin is not available. Install Docker Compose v2."
  exit 1
fi

if [[ ! -f "$ROOT_DIR/php-test-car-app-be/.env" ]]; then
  cp "$ROOT_DIR/php-test-car-app-be/.env.example" "$ROOT_DIR/php-test-car-app-be/.env"
  echo "Created php-test-car-app-be/.env from .env.example"
fi

if [[ ! -f "$ROOT_DIR/php-test-car-app-fe/.env" ]]; then
  cp "$ROOT_DIR/php-test-car-app-fe/.env.example" "$ROOT_DIR/php-test-car-app-fe/.env"
  echo "Created php-test-car-app-fe/.env from .env.example"
fi

if [[ "$RESET" == true ]]; then
  echo "Resetting compose resources..."
  docker compose down -v --remove-orphans || true
  docker rm -f php-test-car-app-be php-test-car-app-mysql php-test-car-app-fe >/dev/null 2>&1 || true
fi

PROFILE_ARGS=()
if [[ "$WITH_FRONTEND" == true ]]; then
  PROFILE_ARGS+=(--profile frontend)
fi

UP_ARGS=(--build -d)
if [[ "$NO_BUILD" == true ]]; then
  UP_ARGS=(--no-build -d)
fi

echo "Starting services..."
docker compose "${PROFILE_ARGS[@]}" up "${UP_ARGS[@]}"

wait_for_url() {
  local url="$1"
  local retries="$2"
  local sleep_seconds="$3"

  for _ in $(seq 1 "$retries"); do
    if curl -fsS "$url" >/dev/null 2>&1; then
      return 0
    fi
    sleep "$sleep_seconds"
  done
  return 1
}

echo "Waiting for backend health..."
if ! wait_for_url "http://localhost:8080/health" 60 2; then
  echo "Backend health check failed at http://localhost:8080/health"
  exit 1
fi

if [[ "$WITH_FRONTEND" == true ]]; then
  echo "Waiting for frontend..."
  if ! wait_for_url "http://localhost:5173" 60 2; then
    echo "Frontend check failed at http://localhost:5173"
    exit 1
  fi
fi

if [[ "$RUN_SMOKE" == true ]]; then
  echo "Running API smoke tests..."
  curl -fsS "http://localhost:8080/health" >/dev/null
  curl -fsS -X POST "http://localhost:8080/api/sync/cars" >/dev/null
  curl -fsS -X POST "http://localhost:8080/api/sync/quotes" >/dev/null
fi

echo "Setup complete."
echo "- Backend:  http://localhost:8080"
if [[ "$WITH_FRONTEND" == true ]]; then
  echo "- Frontend: http://localhost:5173"
fi
