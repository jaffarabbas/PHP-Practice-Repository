# Laravel Middleware Guide

## What is Middleware?

Middleware is a layer between the incoming HTTP request and your application logic. It acts as a filter that can:
- Inspect requests BEFORE they reach your controller
- Modify responses AFTER your controller processes them
- Block requests that don't meet certain criteria

Think of it like security guards at different checkpoints in a building.

## How Middleware Works

```
Request → Middleware 1 → Middleware 2 → Controller → Response
                ↓                              ↑
            Check API Key                 Return JSON
```

## Created Middleware Examples

### 1. CheckApiKey Middleware
**Location:** `app/Http/Middleware/CheckApiKey.php`

**Purpose:** Validates that incoming requests have a valid API key in headers

**How it works:**
1. Checks for `X-API-KEY` header
2. Compares it with the valid key (stored in `.env`)
3. Blocks request if key is missing or invalid
4. Allows request to proceed if valid

**Code:**
```php
public function handle(Request $request, Closure $next): Response
{
    $apiKey = $request->header('X-API-KEY');
    $validApiKey = env('API_KEY', 'your-secret-api-key');

    if (!$apiKey) {
        return response()->json(['error' => 'API key required'], 401);
    }

    if ($apiKey !== $validApiKey) {
        return response()->json(['error' => 'Invalid API key'], 403);
    }

    return $next($request); // Continue to controller
}
```

### 2. LogRequest Middleware
**Location:** `app/Http/Middleware/LogRequest.php`

**Purpose:** Logs all incoming API requests and responses

**How it works:**
1. Logs request details (method, URL, IP) BEFORE processing
2. Processes the request
3. Logs response details (status code) AFTER processing

**Code:**
```php
public function handle(Request $request, Closure $next): Response
{
    // Log BEFORE
    Log::info('API Request', ['method' => $request->method()]);

    // Process request
    $response = $next($request);

    // Log AFTER
    Log::info('API Response', ['status' => $response->getStatusCode()]);

    return $response;
}
```

## How to Register Middleware

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'api.key' => \App\Http\Middleware\CheckApiKey::class,
        'log.request' => \App\Http\Middleware\LogRequest::class,
    ]);
})
```

This gives your middleware a short alias name to use in routes.

## How to Apply Middleware to Routes

**File:** `routes/api.php`

### Method 1: Single Route, Single Middleware
```php
Route::get('/users', [UserController::class, 'getUsers'])
    ->middleware('log.request');
```

### Method 2: Single Route, Multiple Middleware
```php
Route::get('/users/{id}', [UserController::class, 'getUserByID'])
    ->middleware(['log.request', 'api.key']);
```
Middleware runs in order: `log.request` first, then `api.key`

### Method 3: Group Routes with Middleware
```php
Route::middleware(['log.request'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```
All routes in the group share the same middleware.

### Method 4: Prefix + Middleware Group
```php
Route::prefix('protected')->middleware('api.key')->group(function () {
    Route::get('/users', [UserController::class, 'getUsers']);
});
```
Accessible at `/api/protected/users` and requires API key.

## Testing Middleware

### Test WITHOUT API Key (will fail):
```bash
curl http://localhost:8000/api/users/1
```
**Response:**
```json
{
  "success": false,
  "message": "API key is required. Please provide X-API-KEY header."
}
```

### Test WITH Valid API Key (will succeed):
```bash
curl http://localhost:8000/api/users/1 \
  -H "X-API-KEY: your-secret-api-key"
```
**Response:**
```json
{
  "success": true,
  "data": { ... }
}
```

### Test WITH Invalid API Key (will fail):
```bash
curl http://localhost:8000/api/users/1 \
  -H "X-API-KEY: wrong-key"
```
**Response:**
```json
{
  "success": false,
  "message": "Invalid API key"
}
```

## Setting API Key in .env

Add this to your `.env` file:
```
API_KEY=your-secret-api-key-here
```

Change `your-secret-api-key-here` to a secure random string.

## Creating Your Own Middleware

### Step 1: Generate Middleware
```bash
php artisan make:middleware YourMiddlewareName
```

### Step 2: Edit the handle() method
```php
public function handle(Request $request, Closure $next): Response
{
    // Your logic BEFORE the request reaches the controller

    if (/* some condition */) {
        return response()->json(['error' => 'Blocked'], 403);
    }

    // Process the request
    $response = $next($request);

    // Your logic AFTER the controller processes the request

    return $response;
}
```

### Step 3: Register in bootstrap/app.php
```php
$middleware->alias([
    'your.alias' => \App\Http\Middleware\YourMiddlewareName::class,
]);
```

### Step 4: Apply to routes
```php
Route::get('/route', [Controller::class, 'method'])
    ->middleware('your.alias');
```

## Common Use Cases

1. **Authentication** - Check if user is logged in
2. **Authorization** - Check if user has permission
3. **Rate Limiting** - Limit requests per minute
4. **CORS** - Handle cross-origin requests
5. **Logging** - Track all requests
6. **API Key Validation** - Validate API keys
7. **Input Sanitization** - Clean user input
8. **Maintenance Mode** - Block access during maintenance

## Middleware Execution Order

Middleware runs in the order you specify:
```php
->middleware(['first', 'second', 'third'])
```

Flow:
```
Request → first → second → third → Controller → third → second → first → Response
```

Each middleware gets to process both the request (going in) and response (going out).

## Check Logs

Logs are stored in: `storage/logs/laravel.log`

You'll see entries like:
```
[2025-11-25 20:50:00] local.INFO: API Request {"method":"GET","url":"http://localhost:8000/api/users"}
[2025-11-25 20:50:01] local.INFO: API Response {"status_code":200}
```

## Summary

✅ Created 2 middleware: `CheckApiKey` and `LogRequest`
✅ Registered middleware aliases in `bootstrap/app.php`
✅ Applied middleware to routes in different ways
✅ Tested with and without API keys

Now you understand how middleware works in Laravel!
