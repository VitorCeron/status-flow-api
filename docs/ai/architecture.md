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


