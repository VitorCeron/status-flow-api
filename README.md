# StatusFlow — API Health Monitoring

StatusFlow is a backend API built to monitor external endpoints and alert users when services go down. Think of it as a lightweight alternative to UptimeRobot, but built from scratch to demonstrate real-world backend engineering decisions.

---

## What it does

- Monitors API endpoints at configurable intervals (1, 5, or 10 minutes)
- Tracks response time and HTTP status codes on every check
- Detects failures and sends email alerts when a monitor crosses the failure threshold
- Stores check history with pagination for analytics
- Provides a dashboard with uptime stats, incident counts, and recent activity
- Prunes old logs automatically on a daily schedule

---

## The Challenges

### 1. Scheduling checks without overlap

The core challenge of a monitoring system is running checks reliably and at scale. A naive approach (a single loop checking every monitor in sequence) doesn't work when you have hundreds of monitors with different intervals.

**Solution:** A scheduled command (`monitors:run`) runs every minute and queries for monitors that are *due to run* — meaning their last check time plus interval is in the past. Each eligible monitor dispatches an independent `ExecuteMonitorCheckJob` to the queue, which means checks run concurrently and a slow or failing endpoint doesn't block others.

### 2. Avoiding false positives

A single failed HTTP request can be a network blip, not a real outage. Sending an alert for every failure would create noise and erode trust.

**Solution:** Each monitor has a configurable `fail_threshold`. The system counts consecutive failures using `countConsecutiveFailures()` in `MonitorLogService` before changing the monitor status to `DOWN` and sending an email. This means alerts only fire when the failure is sustained.

### 3. Keeping the architecture testable and clean

A service that directly instantiates its dependencies is hard to test and hard to change. Mixing HTTP calls, database writes, and email dispatch in a single class creates a maintenance burden.

**Solution:** The project uses Domain-Driven Design with a strict layered architecture: Controllers only validate and delegate, Services own business logic, Repositories handle persistence, and all dependencies are injected via interfaces. The IoC container binds everything in `AppServiceProvider`. Tests mock the infrastructure, not the logic.

### 4. Separating concerns across monitoring stages

The execution of a check (making the HTTP request) and the processing of its result (saving the log, updating status, sending mail) are different responsibilities that should be independently testable.

**Solution:** `MonitorExecutionService` handles the HTTP call with Guzzle and returns a result object. `MonitorLogService` saves the log. The job that glues them together (`ExecuteMonitorCheckJob`) is thin and orchestrates without owning logic.

---

## Architecture

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

---

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | — | Register a new user |
| POST | `/api/auth/login` | — | Login and receive token |
| POST | `/api/auth/logout` | ✓ | Invalidate token |
| GET | `/api/auth/me` | ✓ | Authenticated user profile |
| GET | `/api/dashboard` | ✓ | Summary stats |
| GET | `/api/monitors` | ✓ | Paginated list of monitors |
| POST | `/api/monitors` | ✓ | Create a monitor |
| GET | `/api/monitors/{id}` | ✓ | Show monitor details |
| PUT | `/api/monitors/{id}` | ✓ | Update a monitor |
| DELETE | `/api/monitors/{id}` | ✓ | Delete a monitor |
| GET | `/api/monitors/{id}/stats` | ✓ | Response time history and uptime % |

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
git clone https://github.com/your-username/status-flow.git
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
