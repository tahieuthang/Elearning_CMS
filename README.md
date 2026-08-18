# E-Learning CMS Backend

Laravel 11 backend for a full-stack e-learning platform. The project provides a role-based administration panel for course operations and a JWT-protected REST API consumed by the Vue.js learner application.

This repository is a portfolio/demo project focused on practical backend engineering: course commerce, media processing, learning progress, streak analytics, secure authentication, and Docker-based deployment.

## Demo

- Learner application: [app.viettech.click](https://app.viettech.click)
- Public API: [api.viettech.click](https://api.viettech.click)
- CMS administration: [cms.viettech.click](https://cms.viettech.click)

The payment flow currently uses the VNPay sandbox environment. Demo availability depends on the configured VPS, DNS, third-party credentials, and storage services.

## Highlights

### Course and content operations

- Course CRUD with active/private status management
- One-level categories and reusable tags
- Course thumbnail, banner, rich-text description, and ordered lessons
- Quiz and question management
- Blog posts and hot-content management
- Admin-side server-side DataTables, filtering, permissions, and PDF export

### Learner commerce

- Customer shopping cart and order history
- Coupon validation and course-specific coupon management
- VNPay sandbox payment URL generation, callback, and transaction tracking
- Review and rating aggregation for courses
- Free-course and purchased-course learning access flows

### Media and asynchronous processing

- Video upload pipeline backed by Laravel queue workers
- Cloudflare R2/S3-compatible object storage for uploaded video files
- Redis-backed queue processing
- Stable public R2 URLs for learner playback
- Separate persistent Docker volumes for MySQL data, uploaded files, and built assets

### Authentication and account security

- Session-based authentication for CMS administrators
- JWT authentication for customer APIs
- Short-lived access tokens kept in browser memory
- Refresh tokens issued through HttpOnly, Secure, SameSite cookies
- Six-digit email verification code with a 15-minute TTL
- Verification-code resend cooldown and hourly rate limit
- Password reset and customer profile management
- Spatie Permission-based roles and permissions for CMS access

### Learning progress and streaks

- Course-player-only learning activity tracking
- Video progress based on watched ranges to reduce seek/jump inflation
- Weekly per-video watched progress
- Weekly learning summaries and current streak calculation
- 30-minute watched-time target for a qualifying learning day
- Offline progress synchronization window for temporary network loss
- Dedicated unit tests for range merging and week-boundary behavior

## Technology

### Backend

- Laravel 11
- PHP 8.2
- MySQL 8
- Redis
- PHP-FPM and Nginx
- Eloquent ORM and Laravel migrations
- JWT Auth (`tymon/jwt-auth`)
- Spatie Laravel Permission
- Yajra Laravel DataTables
- Laravel Queue
- Sentry Laravel integration

### CMS frontend

- Blade templates
- AdminLTE 3
- Bootstrap and jQuery
- DataTables
- Select2
- CKEditor
- Bootstrap FileInput
- SweetAlert2
- Vite

### Infrastructure and integrations

- Docker and Docker Compose
- Cloudflare R2 / S3-compatible storage
- VNPay sandbox
- SMTP email delivery
- GitHub Actions deployment workflow
- VPS deployment with Nginx and HTTPS certificates

## Architecture

The main request path is:

```text
Route
  -> Middleware
  -> Controller
  -> Service
  -> Eloquent Model / Query
  -> MySQL
  -> JSON response
```

The backend keeps HTTP transport concerns in controllers and places reusable business operations in service classes. Queue jobs handle work that should not block a request, such as transferring uploaded media to R2.

Learning streak tracking is separated into focused services:

```text
Course player
  -> progress API
  -> watched-range merger
  -> weekly video progress
  -> weekly learning summary
  -> streak response for My Learning
```

Production Compose intentionally avoids mounting the application source into the runtime container. Each deployment builds an immutable application image while MySQL, uploaded files, and built assets live in persistent Docker volumes.

## Repository structure

```text
Elearning_CMS/
├── app/
│   ├── Http/Controllers/       # CMS and API adapters
│   ├── Jobs/                   # Queue jobs, including R2 upload jobs
│   ├── Models/                 # Eloquent models and relationships
│   ├── Notifications/          # Email notifications
│   ├── Observers/              # Model side effects, such as rating updates
│   └── Services/               # Application and domain operations
├── config/                     # Laravel and feature configuration
├── database/
│   ├── migrations/             # Versioned schema changes
│   └── seeders/                # Development/demo seed data
├── resources/views/            # CMS Blade views
├── routes/
│   ├── api.php                 # Customer API routes
│   └── web.php                 # CMS web routes
├── tests/
│   ├── Feature/                # HTTP and integration behavior
│   └── Unit/                   # Domain and deterministic service tests
├── docker-compose.dev.yml      # Local development services
├── docker-compose.yml          # Production Compose configuration
├── Dockerfile                  # Application image build
└── .github/workflows/          # CI/deployment workflow
```

## Local development

### Prerequisites

- Docker Engine 20.10+
- Docker Compose v2+
- Git
- PHP 8.2 and Composer if running Laravel directly on the host
- Node.js and pnpm if building frontend assets outside Docker

### Docker development setup

```bash
git clone https://github.com/Kamadee/Elearning_CMS.git
cd Elearning_CMS
cp .env.example .env

docker compose -f docker-compose.dev.yml up -d --build
docker compose -f docker-compose.dev.yml exec app php artisan key:generate
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

Development endpoints:

```text
CMS/API: http://localhost:8081
API base URL: http://localhost:8081/api
MySQL from the host: 127.0.0.1:3307
Redis from the host: 127.0.0.1:6380
```

The development Compose file bind-mounts the source tree for fast code iteration. Production uses a different Compose configuration and does not use this bind mount.

### Running without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
pnpm install
pnpm run build
php artisan serve
```

See [PRODUCTION-RUN.md](./PRODUCTION-RUN.md) for the deployment runbook.

## Production deployment

Production deployment uses:

- A Dockerized Laravel application and queue worker
- Nginx as the public entry point
- Persistent `mysql_data`, `public_uploads`, and `public_build` volumes
- HTTPS certificates mounted from the VPS
- Database and upload backups before deployment
- Laravel migrations and cache optimization after the image is updated
- GitHub Actions for automated test/deployment steps when repository secrets and branch rules are configured

The production image build requires a temporary Composer authentication file for GitHub package downloads. Do not commit `.env`, Composer credentials, SSH keys, or production tokens.

For the operational procedure, see [PRODUCTION-RUN.md](./PRODUCTION-RUN.md).

## API surface

The API is primarily protected with JWT customer authentication. Representative endpoints include:

```text
POST   /api/customer/register
POST   /api/customer/verify
POST   /api/customer/login
POST   /api/customer/refresh
POST   /api/customer/logout

GET    /api/course/list
GET    /api/course/detail/{id}
GET    /api/course/top
GET    /api/course/categories
GET    /api/course/tags

GET    /api/cart/content
POST   /api/cart/add
DELETE /api/cart/delete/{id}

POST   /api/payment/create
GET    /api/payment/result
GET    /api/payment/response

POST   /api/course/video/progress
GET    /api/course/streak
POST   /api/course/streak/visit
POST   /api/course/review/add/{id}
POST   /api/course/quiz/submit
```

The checked-in [swagger.json](./swagger.json) and route definitions are the source of truth for endpoint details. Environment-specific base URLs must be configured through environment variables; no production URL or secret belongs in frontend source code.

## Testing and quality checks

Run the backend test suite inside the application container:

```bash
docker compose exec -T app php artisan test
docker compose exec -T app vendor/bin/pint --test
git diff --check
```

The test suite covers cart price resolution, video URL handling, authentication cookie behavior, learning streak range/week logic, API health behavior, and rating aggregation regressions.

## Current scope and limitations

This is an intentionally scoped portfolio system, not a claim of production-scale readiness. The following are outside the current implemented scope:

- No microservices migration
- No GraphQL API
- No Elasticsearch search cluster
- No WebSocket notification system
- No Kubernetes or auto-scaling setup
- No production VNPay merchant approval; the integration is sandbox-oriented

These items should not be presented as implemented features without adding the corresponding code, tests, deployment configuration, and operational evidence.

## Related documentation

- [swagger.json](./swagger.json) — API reference

## License

This project is maintained as a portfolio/demo application.
