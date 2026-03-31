# php-test-car-app-be

Backend API component (Lumen) for the `cartest` repository.

## Stack

- PHP `>=8.2`
- Lumen framework (`laravel/lumen-framework`)
- MySQL 8
- Guzzle HTTP client
- PHPUnit + Mockery (service-layer unit tests)
- Docker

## API endpoints

- `GET /health`
- `POST /api/sync/cars`
- `POST /api/sync/quotes`
- `POST /api/sync/quotes/{carId}`
- `GET /api/cars`
- `GET /api/cars/{carId}`
- `GET /api/cars/{carId}/quotes`

## Run / setup

Use the root guide for full setup and commands:

- `../README.md`

