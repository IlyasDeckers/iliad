<?php

namespace Iliad\Repositories\Concerns;

use Iliad\Http\QueryStringData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

/**
 * @template T
 * @template TDataObject
 */
trait QueryBuilder
{
    /**
     * Get a collection with relations,
     * scopes, sorting and filtering.
     *
     * @return DataCollection<TDataObject>
     */
    public function collectionResponse(Builder|Model $query): DataCollection
    {
        $collection = $query->get()->when(
            (new Request)->query->has('groupBy'),
            fn($query) => $this->groupBy($query, (new Request)->query->get('groupBy'))
        );

        return $this->dataClass::collect($collection, DataCollection::class);
    }

    /**
     * @return DataCollection<T>
     */
    public function createCollection(Builder|Model $query): DataCollection
    {
        return $query->get();
    }

    /**
     * Transforms a query into a data collection.
     *
     * @return DataCollection<TDataObject>
     * @throws InvalidDataClass
     */
    public function createDataCollection(Builder|Model $query): DataCollection
    {
        if (!is_a($this->dataClass, BaseData::class, true)) {
            throw InvalidDataClass::create($this->dataClass);
        }

        return $this->dataClass::collect(
            $query->get(), DataCollection::class
        );
    }

    /**
     * Transforms a request query parameter bag to a query that is executed on a model.
     * A request can have `with`, `count` & `groupBy` `scopes` params.
     *
     * `with` loads relations
     * `scopes` queries defined scopes on a model eg. byMonth
     * `count` returns the amount of records a defined relation has
     * `groupBy` groups the results by the specified column name
     */
    private function parseQueryDataToQuery(Builder|Model $query, QueryStringData $queryData): Builder
    {
        return $query->when(
            $queryData->has('with'),
            fn(Builder $query) => $query->with($queryData->parseToArray('with'))
        )->when(
            $queryData->has('count'),
            fn(Builder $query) => $query->withCount($queryData->parseToArray('count'))
        )->when(
            $queryData->has('scopes'),
            fn(Builder $query) => $query->scopes($queryData->parseToArray('scopes'))
        )->when(
            $queryData->has('paginate'),
            fn(Builder $query) => null // $query->paginate($queryData->per_page) TODO: Implement pagination
        );
    }

    /**
     * Sort the query
     */
    public function sort(Builder $query, Request $request): Builder
    {
        foreach (explode(',', $request->query->get('sort')) as $sort) {
            [$sortCol, $sortDir] = explode('|', $sort);
            $query = $query->orderBy($sortCol, $sortDir);
        }

        return $query;
    }

    /**
     * Default sort method
     */
    public function noSort(Builder $query): Builder
    {
        return $query->orderBy('id', 'asc');
    }
}
