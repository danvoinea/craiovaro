# Contributing Guidelines

## 1. Architecture

### High-level structure
- **Controllers stay thin.** They validate input and delegate to Actions/Services. They should not contain business logic.
- **Actions (`app/Actions`)** represent a single use case / intention (e.g. `GetSystemStatus`, `CreateOrder`, `SendWelcomeEmail`).  
  - Public entrypoint is always `execute(...)`.
  - Actions may orchestrate one or more Services.
- **Services (`app/Services`)** hold domain/business logic and can be reused by multiple Actions.
  - Services should not know about HTTP (no `Request`, no `Response` objects).
  - Services may talk to models, repositories, events, queues, etc.

> This `Actions` + `Services` layering is our convention and overrides the default “fat controller” approach from generic Laravel tutorials. All new behavior must follow this pattern.

### Controllers
- Use tuple notation: `[Controller::class, 'method']` when defining routes.
- Controllers should mostly be very small methods like:
  - validate request
  - call an Action
  - return a response (JSON / view)

### Models
- Keep Eloquent models focused on persistence (relationships, casts, scopes).
- Avoid putting business rules directly on models unless it’s clearly an Eloquent concern (e.g. scopes, accessors).

---

## 2. Naming & HTTP Conventions

### Routes
- **URLs**: kebab-case  
  Example: `/user-profile`, `/error-occurrences`
- **Route names**: camelCase  
  Example: `->name('userProfile.show')`
- **Route parameters**: camelCase  
  Example: `/users/{userId}`
- **Resource naming**: use plural resource names for endpoints  
  Example: `/posts`, `/error-occurrences`

Add routes in `routes/api.php` or `routes/web.php` using:

```php
Route::get('/status', [StatusController::class, 'show'])
    ->name('status.show');

