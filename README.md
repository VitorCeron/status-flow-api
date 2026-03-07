# StatusFlow — API Health Monitoring

StatusFlowAPI is a backend API built to monitor external endpoints and alert users when services go down. Think of it as a lightweight alternative to UptimeRobot, but built from scratch to demonstrate real-world backend engineering decisions.

---

## What it does

- Monitors API endpoints at configurable intervals (1, 5, or 10 minutes)
- Tracks response time and HTTP status codes on every check
- Detects failures and sends email alerts when a monitor crosses the failure threshold
- Stores check
- Provides a dashboard with uptime stats, incident counts, and recent activity
- Prunes old logs automatically on a daily schedule

---

# Challenges

## Architecture

The main flow of a request within the application is:
```
api.php -> Requester -> Controller -> Service -> Repository - 
                                                            |
                         Resource <- Controller <- Service <- 
```

The architecture was designed following maintenance best practices to ensure it is easy to maintain and allows for scaling with new features.

The project follows a domain-driven structure with one domain per bounded context:

```
app/
├── Console/
│   └── Commands/
│       ├── RunMonitorChecksCommand.php    # Dispatches jobs every minute
│       └── PruneMonitorLogsCommand.php   # Daily log cleanup
├── Domains/
│   ├── Auth/                             # Register, login, logout, me
│   ├── Dashboard/                        # Stats aggregation
│   ├── Monitor/                          # CRUD for monitors
│   ├── MonitorExecution/                 # HTTP check execution
│   └── MonitorLog/                       # Log persistence and queries
├── Http/
│   ├── Controllers/                      # Thin controllers, no business logic
│   ├── Requests/                         # Form validation
│   └── Resources/                        # Response transformation
├── Jobs/
│   └── ExecuteMonitorCheckJob.php        # Queued check execution
└── Mail/
    └── MonitorDownMail.php               # Failure alert email
```

Each domain has its own `Services/`, `Repositories/`, and `Exceptions/` directories. Interfaces are bound in `AppServiceProvider`, keeping implementation details hidden from consumers.

### Controllers / Requesters / Resources

I usually keep controllers responsible only for redirecting the action to the corresponding `Service`. Ideally, there should be no conditionals, only the call to the `Service` and exception handling.

This is because, before reaching the controller, the data passes through the requester, which validates all information based on the rules. If everything is valid, the data reaches the controller already validated, so it only passes the data to the correct `Service`.

Regarding the try-catch block, it is because I leave the Controller responsible for knowing how to direct the response to the API. If successful, it will call a `Resource` to return the information; if not successful, it must know the best error response to provide to the endpoint.

### DDD

The goal of every application is to keep the architecture scalable as the project grows, ensuring it is easy to maintain and include new modules.

For this, Domain Driven Development was used, applied in this project with the central idea of keeping all business rules within the Service layer. Thus, whenever a business rule needs to be changed or incremented, this rule must be directly related to a `Service`.

Each service can contain one or more methods from the same context. For example, the Monitors CRUD is located in `app\Domains\Monitor\Services\MonitorService.php`. To change anything regarding the CRUD rules, this service is the one to be consulted. This is how the services were separated, so that each service serves an isolated context.

Another example is the application's `ExecuteMonitorCheckJob`. Inside the `handle` method, there are basically calls to `Services` in a simpler way. The `Service` will process the data as needed. The `Job` layer is responsible for having the correct extensibility to be executed in a queue—ensuring the process does not stall the rest of the queue, handling retry counts, and so on—without mixing business rules with application operational rules.

### Data Access

The challenge of the data layer is that being coupled with business rules can bring complications in the long run. Changes in services can cause problems in data writing, and updating versions related to the data access layer can generate bugs in business rules, and so forth.

That is why I like to isolate the data access layer using the `Repository Pattern`. This layer is used by services when it is necessary to retrieve or insert data into the database.

I try to keep `Repositories` as simple as possible and tied to the same context as the service. This organization facilitates maintenance; through the file name, it is easy to locate, for example, that the data access layer for `MonitorService` is the `MonitorRepository`.

### Security

`UUID` was used in tables where data is publicly exposed so that it is not possible to track the number of records directly in each database table and to prevent attacks.

Regarding the access token, Laravel Sanctum was used, with short-lived access tokens, token invalidation upon logout, and access revocation when deleting an account.

The API also has a `rate limit` of 60 requests per minute to avoid receiving a massive amount of requests from the same user or IP.

Routes were separated by middlewares that ensure correct access for each type of user: the admin user only has access to backoffice information, and each user has access to their own panel.

All API queries use `Eloquent`, which is natively protected against `SQL Injection`.

---

## API Endpoints

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | — | Register a new user |
| POST | `/api/auth/login` | — | Login and receive token |
| POST | `/api/auth/logout` | ✓ | Invalidate token |
| GET | `/api/auth/me` | ✓ | Authenticated user profile |
| POST | `/api/auth/change-password` | ✓ | Change password |
| DELETE | `/api/auth/account` | ✓ | Delete account |

### Profile

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/profile/timezones` | ✓ | List available timezones |
| PATCH | `/api/profile/settings` | ✓ | Update profile settings |

### Dashboard

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/dashboard` | ✓ | Summary stats |

### Monitors

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/monitors` | ✓ | Paginated list of monitors |
| POST | `/api/monitors` | ✓ | Create a monitor |
| GET | `/api/monitors/{id}` | ✓ | Show monitor details |
| PUT | `/api/monitors/{id}` | ✓ | Update a monitor |
| DELETE | `/api/monitors/{id}` | ✓ | Delete a monitor |
| GET | `/api/monitors/{id}/stats` | ✓ | Response time history and uptime % |

### Backoffice (admin only)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/backoffice/dashboard` | ✓ admin | Backoffice summary stats |
| GET | `/api/backoffice/users` | ✓ admin | List all users |
| GET | `/api/backoffice/users/{id}` | ✓ admin | Show user details |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.5.3 |
| Framework | Laravel 10 |
| Database | MySQL 8.4 |
| Authentication | Laravel Sanctum (API tokens) |
| HTTP Client | Guzzle 7 |
| Queue | Laravel Queue (database driver) |
| Cache | Redis |
| Email (local) | Mailpit |
| Testing | PHPUnit 10 |
| Containerization | Docker (Laravel Sail) |

---

## Installation

### Prerequisites

- Docker and Docker Compose
- PHP 8.5+ with Composer (only needed to install Sail initially)

### Steps

**1. Clone the repository**

```bash
git clone https://github.com/VitorCeron/status-flow-api.git
cd status-flow
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Copy and configure the environment file**

```bash
cp .env.example .env
```

Edit `.env` and set your desired `APP_KEY`, database credentials, and mail settings. For local development the defaults work with Sail.

**4. Generate the application key**

```bash
php artisan key:generate
```

**5. Start the Docker containers**

```bash
./vendor/bin/sail up -d
```

This starts:
- The Laravel application on port `80`
- MySQL on port `3306`
- Redis on port `6379`
- Mailpit (email dashboard) on port `8025`

**6. Run migrations**

```bash
./vendor/bin/sail artisan migrate
```

**7. Start the queue worker**

```bash
./vendor/bin/sail artisan queue:work --queue=local-default
```

**8. Start the scheduler (for automatic monitor checks)**

```bash
./vendor/bin/sail artisan schedule:work
```

The scheduler dispatches `monitors:run` every minute and `monitors:prune-logs` daily.

---

## Running Tests

```bash
# Run all tests
./vendor/bin/sail composer test

# Run a specific test file with readable output
./vendor/bin/sail artisan test --filter MonitorControllerTest --testdox
```

Tests use `RefreshDatabase` and factory-generated data. External HTTP calls in `MonitorExecutionService` are mocked, so no real network requests are made during testing.

---

## Local Email

Mailpit captures all outbound emails locally. Access the inbox at [http://localhost:8025](http://localhost:8025) after starting Sail.

---

## License

MIT
