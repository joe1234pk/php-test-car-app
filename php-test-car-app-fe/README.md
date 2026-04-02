# php-test-car-app-fe

Frontend app for the Dinggo challenge using React + Tailwind + Vite.

## Stack

- React 19
- Vite 8
- Tailwind CSS 4
- React Router
- Node `24.x` (via `.nvmrc`)
- Yarn 4
- Docker

## FE structure

- `src/routers/` - route config (`/` redirects to `/cars`)
- `src/services/` - backend API client
- `src/components/` - reusable UI components
- `src/pages/` - page containers
- `src/hooks/` - reserved for custom hooks

## UI scope

- `/cars` page: car operations and car list
- `/cars/:carId` page: quotes for a specific car
- Sync/reload action buttons on both pages

## Run / setup

Use the root guide for complete local and docker setup:

- `../README.md`
