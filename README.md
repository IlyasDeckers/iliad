# Iliad
WIP

## Repositories

### Introduction
Repositories are classes or components that encapsulate the logic required to access data sources. With the implementation of repositories we create an abstraction layer between our Controllers and Models, to create business logic to interact with our (eloquent) models.

The `BaseRepository` is used as a starting point for all repositories. This enables us to create and reuse methods shared by all repositories in our application.
```
+--------------+           +----------------+
|              |  extends  |                |
|  Repository  +-----------> BaseRepository |
|              |           |                |
+--------------+           +----------------+
```

## Using repositories
To use a repository you can inject the repository into you controller's constructor. By doing this the 
controller can access the repository and therefore the appropriate model to interact with the database.

```php
    public function __construct(
        protected ModuleRepository $moduleRepository
    )
```
The repository that is being used extends `BaseRepository.php` to gain access to a set of methods. These methods get 
called by providing query strings to your API calls. [read more](/docs/clockwork/backend/requests/)

{{< callout type="warning" >}}
**Attention** There currently are two BaseRepositories, an old implementation and a refactored implementation prefixed by an
underscore. When writing a new repository always user `_BaseRepository.php`
{{< /callout >}}

### Interfaces
A repository interface defines the required methods needed to perform the basic actions a repository needs to perform.

Required methods in an interface:

* `find()`
* `getAll()`
* `update()`
* `store()`
* `destroy()`

Example:
```php
interface ContractRepositoryInterface
{
    /**
     * @param Request $request
     * @return DataCollection<ContractData>
     */
    public function getAll(Request $request): DataCollection;

    /**
     * @param int $id
     * @return ContractData
     */
    public function find(int $id): Data;

    /**
     * @param ContractData $data
     * @return ContractData
     */
    public function store(ContractData $data): ContractData;

    /**
     * @param ContractData $data
     * @return ContractData
     */
    public function update(ContractData $data): ContractData;
}
```

#### Required properties

A repository needs two properties to function properly.

* `$model`: An instance of an Eloquent model
* `$dataClass`: A string reference to a DataObject

### Transactions
On the `_BaseReposity` there is a `Transaction` trait included. This trait contains a couple of methods to enable 
database transactions in a repository.

Transactions are started automatically for `PUT`, `PATCH`, `POST` and `DELETE` requests. To commit to the database you
need to call `$this->commitTransactions()` or `$this->flush()`. Both methods do exactly the same; Committing transactions 
to the database.

When an Exception is encountered before the transactions are committed to the database they are automatically rolled back.
This gets handled by a custom exception handler that extends the default Laravel exception handler, it is registered upon
starting transactions. 

You can start a transaction manually by calling `$this->startTransactions()` in a class that implements the Transaction 
trait.

#### Old implementation
On repositories that still extend the old `BaseRepository` the `update()`, `store()` and `delete()` methods are private 
methods. These get called through a `__call()` method which starts the transaction and rolls it back when encountering 
an exception. It commits to the database automatically when needed.

### Repository example
```php
<?php

namespace Clockwork\Contracts\Repositories;

use Clockwork\Base\_BaseRepository;
use Clockwork\Base\Traits\Transaction;
use Clockwork\Contracts\Actions\CreateContract;
use Clockwork\Contracts\Actions\CreateContractExtension;
use Clockwork\Contracts\Actions\GetActiveExtensionQuery;
use Clockwork\Contracts\Actions\UpdateContract;
use Clockwork\Contracts\Interfaces\ContractRepositoryInterface;
use Clockwork\Contracts\Models\Contract;
use Clockwork\Contracts\Services\ContractService;
use Clockwork\DataObjects\ContractData;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

class ContractRepository extends _BaseRepository implements ContractRepositoryInterface
{
    use Transaction;

    protected string $dataClass = ContractData::class;

    /**
     * @param Contract $contract
     * @param ContractService $contractService
     * @param GetActiveExtensionQuery $getActiveExtensionQuery
     * @param CreateContract $createContract
     * @param CreateContractExtension $createContractExtension
     * @param UpdateContract $updateContract
     * @throws InvalidDataClass
     */
    public function __construct(
        Contract                                   $contract,
        protected ContractService                  $contractService,
        protected readonly GetActiveExtensionQuery $getActiveExtensionQuery,
        protected readonly CreateContract          $createContract,
        protected readonly CreateContractExtension $createContractExtension,
        protected readonly UpdateContract          $updateContract,
    )
    {
        parent::__construct();
        $this->model = $contract;
    }

    /**
     * @param ContractData $data
     * @return ContractData
     */
    public function store(ContractData $data): ContractData
    {
        $contract = $this->createContract->execute(
            $data
        );

        $this->commitTransactions(); // or $this->flush();

        return ContractData::from($contract->refresh());
    }

    /**
     * @param ContractData $data
     * @return ContractData
     */
    public function update(ContractData $data): ContractData
    {
        $request = request();
        $contract = $this->updateContract->execute($data);
        
        $this->commitTransactions(); // or $this->flush();

        return ContractData::from($contract->refresh());
    }

    /**
     * Delete a contract
     *
     * @param integer $id
     * @return void
     */
    private function delete(int $id)
    {
        //
    }
}

```
### BaseRepository API references
#### Properties

| access modifiers | property    |                                               | 
|------------------|-------------|-----------------------------------------------|
| protected object | $model      | The Model used in the class                   |
| protected Data   | $dataObject | A string representation of a DataObject class |

#### Methods
* `find()`
* `getAll()`
* `update()`
* `store()`
* `destroy()`


## Requests

Through HTTP requests, in the query parameters, we can specify different parameters that each have their own function.
We can eager load relationships, sort, exclude properties,...

### Request query parameters
Example HTTP request:  
`/api/v1/users?with=invoices,purchases&scopes=management`

The following parameters can be used in a query string.
* `with`: loads in relationships
* `scopes`: applies model scopes
* `paginate`: paginates the response
* `sort`: sorts the response
* `groupBy`: groups the response

#### with
Relationships and are defined in the query string of the API call by passing a `with` param. It is possible to eager load
relationships of models passed into this query string. Eg. `contracts.extensions.docusign`

An example:

`/api/users/1?with=vehicle,supplier,contracts.extensions`

This API call will return a user with id 1, his vehicle, supplier and all contracts with it's extensions. The repsonse
will look like this;

```json
{
  "id": 1,
  // ...
  "vehicle": {},
  "supplier": {},
  "contracts": [
    {
      "id": 1,
      "extensions": [
        {}
      ]
    }
  ],
  // ...
}
```

#### scopes
Scopes on models allow you to define common sets of constraints that you may easily re-use throughout your application. 
For example, you may need to frequently retrieve all users that are "active". To define a scope, prefix an Eloquent 
model method with scope.

```php
    /**
     * Scope to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }
```

Our HTTP request will look like this: `GET /api/v1/users?scopes=active`

```json

[
    {
        "id": 1,
        "name": "John Doe",
        "active": true
    }
]
```

#### Exclude
Sometimes when calling relationships we retrieve a lot of extra information that is eager loaded by Eloquent that we do
not need. We can exclude these properties by using the `exclude` param.

Not all properties are excludable, they need to be defined on the DataObject of the corresponding model in a
`allowedRequestExcept()` method.

An example of ContractData's allowed excepts.
```php
public static function allowedRequestExcept(): ?array
{
    return [
        'extensions',
        'definitions',
        'tariffs',
        'audits',
        'extension',
        'user'
    ];
}
```

`/api/contracts/1?with=extensions&except=definitions,extensions.text`

This API request will return a contract without the definitions and the extensions text property.

#### paginate
Pagination is disabled by default. You can add the `paginate` and `per_page` query parameters.

```
GET /api/v1/users?paginate=true&per_page=5
```
This will paginate the results returned and group them per 5 items in a collection.

#### sort
WIP

#### groupBy
Group your results by a given key.

```
GET /api/v1/users?groupBy=type

[
    "employee": [
        ...
    ],
    "management": [
        ...
    ]
]
```
