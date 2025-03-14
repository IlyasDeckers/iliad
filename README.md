# Iliad Documentation

## Overview

Iliad is a PHP package built on top of Laravel's Eloquent ORM that provides a structured approach to developing REST APIs. It implements the repository pattern to create an abstraction layer between controllers and models, making your codebase more maintainable and testable.

## Core Components

### Repositories

Repositories are the heart of Iliad, providing a clean interface for data access and manipulation. They encapsulate the logic required to interact with data sources.

#### BaseRepository (Deprecated)

> **DEPRECATED**: This class is deprecated and should not be used in new code. Use `_BaseRepository` instead.

The original base repository implementation that provides common methods for interacting with Eloquent models.

```php
namespace Iliad\Repositories;

abstract class BaseRepository
{
    // Common repository methods
    public function find(object $request): object;
    public function getAll(object $request): object;
    // ...
}
```

#### _BaseRepository

The current implementation of the repository pattern that leverages PHP 8 features and provides a more robust implementation.

```php
namespace Iliad\Repositories;

abstract class _BaseRepository
{
    use Transaction, QueryBuilder;

    protected Model $model;
    protected string $dataClass;
    
    // Core methods
    public function getAll(): DataCollection;
    public function find(int $id): Data;
    // ...
}
```

Key features:
- Strong typing with PHP 8 features
- Better integration with Laravel's ecosystem
- Enhanced transaction handling via `TransactionManager`
- Integration with Spatie's Laravel Data package

#### Implementing a Repository

When implementing a repository, you should extend `_BaseRepository` and implement the necessary methods:

```php
namespace App\Repositories;

use App\Dto\UserData;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Iliad\Transactions\Transaction;
use Iliad\Repositories\_BaseRepository;
use Iliad\Repositories\Concerns\QueryBuilder;
use Iliad\Transactions\TransactionManager;

class UserRepository extends _BaseRepository implements UserRepositoryInterface
{
    use Transaction,
        QueryBuilder;

    protected string $dataClass = UserData::class;

    public function __construct(
        User $userModel,
        TransactionManager $transactionManager
    )
    {
        $this->transactionManager = $transactionManager;
        parent::__construct();
        $this->model = $userModel;
    }

    public function store(UserData $userData): UserData
    {
        $this->model->create($userData);

        $this->transactionManager->commit();

        return $results['model']->refresh()->getData();
    }

    public function update(UserData $userData): UserData
    {
        $user = $this->model->findOrFail($userData->id);

        // Update logic here

        $this->transactionManager->commit();

        return $user->refresh()->getData();
    }

    public function destroy(int $id): void
    {
        // Delete logic here
    }
}
```

### Transaction Management

Iliad provides robust transaction handling to ensure database integrity.

#### Transaction Trait

The `Transaction` trait provides methods for managing database transactions:

```php
namespace Iliad\Transactions;

trait Transaction
{
    public ?TransactionManager $transactionManager = null;

    private function getTransactionManager(): TransactionManager;
    private function startTransactions(): void;
    private function commitTransactions(): void;
    private function flush(): void; // Deprecated, use commitTransactions() instead
    // ...
}
```

Key features:
- Automatic transaction start for non-GET requests
- Integration with the `TransactionManager` class
- Automatic rollback on exceptions

#### TransactionManager

The transaction management system that provides control and error handling:

```php
namespace Iliad\Transactions;

class TransactionManager
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
    public function transaction(callable $callback): mixed;
    // ...
}
```

Key features:
- Tracks active transactions
- Provides a clean API for transaction management
- Supports nested transactions
- Includes a `transaction()` method for executing code within a transaction

### Query Builder Concern

The `QueryBuilder` trait provides methods for building and executing queries:

```php
namespace Iliad\Repositories\Concerns;

trait QueryBuilder
{
    public function collectionResponse(Builder|Model $query): DataCollection;
    public function createCollection(Builder|Model $query): DataCollection;
    public function createDataCollection(Builder|Model $query): DataCollection;
    private function parseQueryDataToQuery(Builder|Model $query, QueryStringData $queryData): Builder;
    public function sort(Builder $query, Request $request): Builder;
    public function noSort(Builder $query): Builder;
    // ...
}
```

Key features:
- Transform queries into data collections
- Parse query string parameters
- Apply sorting, filtering, and other operations

### Data Transfer Objects

Iliad uses Data Transfer Objects (DTOs) to encapsulate data and provide a consistent interface.

#### Dto

The base DTO class that provides common functionality:

```php
namespace Iliad\DataTransferObjects;

class Dto
{
    use StaticCreateFrom, StaticCreateDataCollection, ToArray;
}
```

Key features:
- Convert to and from arrays
- Create collections of DTOs
- Static factory methods

### Controllers

Iliad provides base controllers that integrate with repositories to handle HTTP requests.

#### BaseController (Deprecated)

> **DEPRECATED**: This class is deprecated and should not be used in new code.

The base controller that provides RESTful endpoints:

```php
namespace Iliad\Http\Controllers;

abstract class BaseController extends Controller
{
    protected $model;
    protected $resource;
    protected array $rules = [];

    public function index(Request $request);
    public function show(Request $request);
    public function store(Request $request): object;
    public function update(Request $request): object;
    public function destroy(int $id): void;
    // ...
}
```

#### _BaseController

The recommended controller base class to extend for new controllers:

```php
namespace Iliad\Http\Controllers;

abstract class _BaseController extends Controller
{
    // Base controller functionality
}
```

#### Controller

The base controller that provides the foundation for all controllers:

```php
namespace Iliad\Http\Controllers;

abstract class Controller
{
    protected array $middleware = [];

    public function middleware($middleware, array $options = []): ControllerMiddlewareOptions;
    public function getMiddleware();
    public function callAction($method, $parameters);
    // ...
}
```

#### Implementing a Controller

Here's an example of implementing a controller that uses route attributes and integrates with a repository:

```php
<?php
namespace App\Http\Controllers;

use App\Dto\UserData;
use App\Repositories\UserRepository;
use Exception;
use Iliad\Http\Controllers\_BaseController;
use Iliad\RouteAttributes\Attributes\Prefix;
use Iliad\RouteAttributes\Attributes\Resource;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

#[Prefix('api/v3')]
#[Resource(
    resource: 'users',
    apiResource: true,
    except: ['destroy'],
    names: 'api.v3.users',
    parameters: ['users' => 'id'],
    shallow: true,
)]
class UserController extends _BaseController
{
    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    /**
     * @return DataCollection<UserData>
     * @throws InvalidDataClass|\ReflectionException
     */
    public function index(): DataCollection
    {
        return $this->userRepository->getAll();
    }

    /**
     * @param int $id
     * @return UserData
     * @throws InvalidDataClass|\ReflectionException
     */
    public function show(int $id): UserData
    {
        return $this->userRepository->find($id);
    }

    /**
     * @param UserData $userData
     * @return UserData
     * @throws Exception
     */
    public function store(UserData $userData): UserData
    {
        return $this->userRepository->store($userData);
    }

    /**
     * @param UserData $userData
     * @return UserData
     */
    public function update(UserData $userData): UserData
    {
        return $this->userRepository->update($userData);
    }
}
```

Key features:
- Route attributes for defining API routes (`#[Prefix]`, `#[Resource]`)
- Type-hinted repository injection
- Strong typing with return types and parameter types
- Integration with Data objects for request and response handling

## Query String Parameters

Iliad supports various query string parameters to customize API responses:

### with

Load relationships with the `with` parameter:

```
GET /api/users/1?with=posts,comments
```

This will return a user with their posts and comments:

```json
{
  "id": 1,
  "name": "John Doe",
  "posts": [
    { "id": 1, "title": "First Post" }
  ],
  "comments": [
    { "id": 1, "body": "Great article!" }
  ]
}
```

You can also load nested relationships:

```
GET /api/users/1?with=posts.comments
```

### scopes

Apply model scopes with the `scopes` parameter:

```
GET /api/users?scopes=active,premium
```

This will apply the `active` and `premium` scopes to the query.

### paginate

Enable pagination with the `paginate` parameter:

```
GET /api/users?paginate=true&per_page=10
```

This will return paginated results with 10 items per page.

### sort

Sort results with the `sort` parameter:

```
GET /api/users?sort=name|asc,created_at|desc
```

This will sort users by name in ascending order, then by creation date in descending order.

### groupBy

Group results with the `groupBy` parameter:

```
GET /api/users?groupBy=role
```

This will group users by their role:

```json
{
  "admin": [
    { "id": 1, "name": "John" }
  ],
  "user": [
    { "id": 2, "name": "Jane" }
  ]
}
```

### exclude

Exclude specific relationships or fields:

```
GET /api/users/1?with=posts&except=posts.body
```

This will load user posts but exclude the post body content.

## Exception Handling

Iliad includes a custom exception handler that integrates with the transaction system.

### HandlerDecorator

Decorates Laravel's exception handler to provide additional functionality:

```php
namespace Iliad\ExceptionHandler;

class HandlerDecorator implements ExceptionHandler
{
    public function report(Throwable $e);
    public function render($request, Throwable $e);
    public function renderForConsole($output, Throwable $e);
    public function reporter(callable $reporter): int;
    public function renderer(callable $renderer): int;
    public function consoleRenderer(callable $renderer);
    // ...
}
```

Key features:
- Custom exception reporting
- Custom exception rendering
- Integration with the transaction system for automatic rollback

## Validation

Iliad provides validation support through the `Validator` trait.

### Validator Trait

```php
namespace Iliad\Concerns;

trait Validator
{
    private function validator(string $function, Request $request): void;
    public function enforce($rules, int $status = 412): void;
    // ...
}
```

Key features:
- Apply validation rules to requests
- Custom error handling
- Support for Laravel's validation system

## Utility Traits

Iliad includes several utility traits that provide additional functionality.

### ResolveId

Resolves the currently authenticated user's ID:

```php
namespace Iliad\Concerns;

trait ResolveId
{
    public static function resolveId(): mixed;
}
```

### WithData

Provides a method to get a data object from a model:

```php
namespace Iliad\Concerns;

trait WithData
{
    public function getData();
}
```

### Editable

Provides methods for formatting messages with variable replacement:

```php
namespace Iliad\Concerns;

trait Editable
{
    public function formatMessage(): void;
    private function replace($fields, $variable): void;
    // ...
}
```

## Helper Functions

Iliad includes several helper functions in `helpers.php`:

- `decimalTime($value)` - Converts decimal time to hours:minutes format
- `timeToDecimal($value)` - Converts hours:minutes format to decimal
- `carbon($value)` - Parses a value to a Carbon instance
- `format($value, $format)` - Formats a date using Carbon
- `generatePassword()` - Generates a secure random password

## Best Practices

### Using Repositories

1. Always extend `_BaseRepository` for new repositories
2. Implement specific interfaces for each repository
3. Define the required `$model` and `$dataClass` properties
4. Inject `TransactionManager` in the constructor
5. Call `$this->transactionManager->commit()` at the end of methods that modify data

### Using Controllers

1. Always extend `_BaseController` for new controllers
2. Use route attributes (`#[Prefix]`, `#[Resource]`, etc.) to define routes
3. Type-hint repository dependencies in the constructor
4. Use strong typing for parameters and return values
5. Use data objects (`UserData`, etc.) for request and response handling

### Transaction Management

1. Let Iliad handle transactions automatically for non-GET requests
2. Use `$this->transactionManager->commit()` to commit transactions
3. Don't worry about rollbacks - they happen automatically on exceptions
4. For complex operations, use the `transaction()` method of the `TransactionManager`

### Data Transfer Objects

1. Create a specific DTO for each model
2. Use `YourData::from($model)` to create DTOs from models
3. Define the `allowedRequestExcept()` method to control what can be excluded

### Query Parameters

1. Use `with` to load relationships efficiently
2. Use `scopes` to apply model scopes
3. Use `sort` to control sorting order
4. Use `paginate` for large result sets

## Conclusion

Iliad provides a structured approach to developing REST APIs with Laravel. By following the repository pattern and using the provided base classes, you can create maintainable and testable APIs with minimal boilerplate code.

Remember to always use the most recent implementations (`_BaseRepository`) and avoid deprecated classes (`BaseRepository` and `BaseController`).