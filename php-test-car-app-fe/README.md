# php-test-car-app-fe

Frontend app for the Dinggo challenge using React + Tailwind + Vite.

## Stack

- React 19
- Vite 8
- Tailwind CSS 4
- React Router
- Node `24.x` (via `.nvmrc`)
- Yarn 4
- Docker (optional profile run)

## FE structure

- `src/routers/` - route config (`/` default page)
- `src/services/` - backend API client
- `src/components/` - reusable UI components
- `src/pages/` - page containers
- `src/hooks/` - reserved for custom hooks

## UI scope

- Default route: `/`
- Cars table display
- Car-specific quotes display
- Sync action buttons for cars/quotes

## Run / setup

Use the root guide for complete local and docker setup:

- `../README.md`
