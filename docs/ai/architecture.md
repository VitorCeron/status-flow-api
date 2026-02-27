# Architecture patterns: Domains and layers

This document defines folder strucure and responsability of each layer. Follow these rules strictly when suggesting or creating code.

## Folder structure

- app
    - Domains
        - [Module]
            - Repositories
                - Interfaces
                    - ModuleRepositoryInterface.php  
                - ModuleRepository.php
            - Services
                - Interfaces
                        - ModuleServiceInterface.php  
                    - ModuleService.php
    - Enums
    - Exceptions
    - Http
        - Controllers
        - Middleware
        - Requests
        - Resources
    - Models
    - Providers

## Request flow

**Route -> Requester -> Controller -> Service -> Repository -> Service -> Controller -> Resource**.

## Folder structure explain

### Requester (FormRequest)
- **Local:** `app/Http/Requests/`
- **Responsability:** Validate and sanitiza data.
- **Rules:** 
    - Check user is owner from register if possible.
    - Validate data.
    - Return formatted errors if exists.
    - For date fields, always convert date from user timezone to UTC

### Controller
- **Local:** `app/Http/Controllers/`
- **Responsability:** Orchestration of incoming requests, redirecting them to the correct module.
- **Rules:** 
    - It should not contain conditional statements, business rules, or database queries.
    - Only receive `Requester`, call correct `Service`, returns `Resource`.
    - Must be try catch to return correct error message to user.

### Service (Core)
- **Local:** `app/Domains/{Domain}/Services/`
- **Responsability:** All the business logic, event triggering, and calculations..
- **Rules:** 
    - Can call repositories.
    - Can call other services and inject.
    - Should not use models directly to get data on database.

### Repository
- **Local:** `app/Domains/{Domain}/Repositories/`
- **Responsability:** Abstração de persistência.
- **Rules:** 
    - Only read and write methods.
    - Should not contain business rules.
    - Methods can be agnostic.

### Resource (API Resources)
- **Local:** `app/Http/Resources/`
- **Responsability:** Formatted response correctly.
- **Rules:** 
    - Convert data if is necessary.
    - Format data to return.
    - For date fields, always convert to UTC from user timezone.

## Other important repositories

### Enums
- **Local:** `app/Enums`
- **Responsability:** To list all the application's enums, where there is a finite number of possible options, create the corresponding enum.
- **Rules:** 
    - Only base enums for application.

### Jobs
- **Local:** `app/Jobs`
- **Responsability:** All the process which can be executed on background, set on that folder.
- **Rules:** 
    - Use `ShouldQueue`.
    - Define important variables for laravel jobs like `timeout`, `tries`, `backoff`.
    - Define `failed` method with error treatment.
    - Use queue name to define where queue is executed using `$this->onQueue(QueueEnum::DEFAULT->getQueueName());`.

### Providers
- **Local:** `app/Providers`
- **Responsability:** Register implementations for interfaces and create bind.
- **Rules:** Make bind of interface with real implementation.

### Commands

- **Local:** `app/Console/Commands`
- **Responsability:** Register schedule operations.
- **Rules:**
    - Define schedule time on `app/Console/Kernel.php`.
    - Should not contain business rules.
    - Return `return Command::SUCCESS;` or `return Command::FAILURE;`  
    - Use `$this->info` to add execution informations

### Exceptions
Criar todos os tipos de erro customizados da aplicação, preferir sempre criar um erro personalizado no local de usar uma exception genérica com mensagem de erro.
- **Local:** `app/Exceptions`
- **Responsability:** Create all customized error types for application, business errors.
- **Rules:**
    - Use `use Exception;` to extend default Exception
    - Group many exceptions for specific context on one folder like `Exceptions/Auth/...`

## Pagination (standard pattern for list endpoints)

All list endpoints (index actions) must return paginated data using `LengthAwarePaginator`. This is the standard for all CRUD modules in the application.

### Layer responsibilities

- **Repository:** Add a `paginateBy{Field}` method (e.g. `paginateByUserId`) returning `LengthAwarePaginator`:
  ```php
  public function paginateByUserId(string $userId, int $perPage): LengthAwarePaginator
  {
      return $this->model->where('user_id', $userId)->paginate($perPage);
  }
  ```

- **Service:** Accept `int $perPage` and delegate to the repository:
  ```php
  public function index(User $user, int $perPage): LengthAwarePaginator
  {
      return $this->repository->paginateByUserId($user->id, $perPage);
  }
  ```

- **Controller:** Extract `per_page` from the query string with a default of 15 and a max cap of 100:
  ```php
  $perPage = min((int) $request->query('per_page', 15), 100);
  $items   = $this->service->index($request->user(), $perPage);
  return ItemResource::collection($items);
  ```
  No custom ResourceCollection is needed — Laravel's `ResourceCollection` automatically detects a paginator and wraps the response with `data`, `links`, and `meta`.

### Response format

```json
GET /api/monitors?per_page=15&page=2
{
  "data": [ { ... } ],
  "links": { "first": "...", "last": "...", "prev": "...", "next": "..." },
  "meta":  { "current_page": 2, "last_page": 4, "per_page": 15, "total": 55 }
}
```

## General code rules and best practices

- Create docblock comments on methods or variables to keep better organized the code, it's easier to read.
