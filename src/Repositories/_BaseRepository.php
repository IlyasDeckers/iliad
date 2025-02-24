<?php

namespace Clockwork\Core\Repositories;

use Clockwork\Core\Concerns\Transaction;
use Clockwork\Core\Http\QueryStringData;
//use Clockwork\Core\Http\Request;
use Clockwork\Core\Repositories\Concerns\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ReflectionException;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

/**
 * @template T
 * @template TDataObject
 */
abstract class _BaseRepository
{
    use Transaction,
        QueryBuilder;

    /**
     * An Eloquent model instance
     *
     * @var Model
     */
    protected Model $model;

    /**
     * A class reference to a Data Transfer Object
     *
     * @var string
     */
    protected string $dataClass;

    /**
     * @throws InvalidDataClass
     */
    public function __construct()
    {
        if (!is_a($this->dataClass, BaseData::class, true)) {
            throw InvalidDataClass::create($this->dataClass);
        }

        if (request()->getMethod() !== 'GET') {
            $this->startTransactions();
        }
    }

    /**
     * Get a collection from the database
     *
     * @return DataCollection<TDataObject>
     * @throws InvalidDataClass|ReflectionException
     */
    public function getAll(): DataCollection
    {
        return $this->createDataCollection(
            $this->parseQueryDataToQuery(
                $this->model, QueryStringData::from(request()->all())
            )
        );
    }

    /**
     * @param int $id
     * @return TDataObject
     * @throws InvalidDataClass|ReflectionException
     */
    public function find(int $id): Data
    {
        $model = $this->parseQueryDataToQuery(
            $this->model->where('id', $id),
            QueryStringData::from(request()->all())
        )->first();

        return $this->dataClass::from($model);
    }
}
