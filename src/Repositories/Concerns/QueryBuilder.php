<?php

namespace Iliad\Repositories\Concerns;

use Iliad\Http\QueryStringData;
use Iliad\Http\Request;
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
     * @param Builder|Model $query
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

    public function createCollection(Builder|Model $query): DataCollection
    {
        return $query->get();
    }

    /**
     * Transforms a query into a data collection.
     *
     * @param Builder|Model $query
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
     *
     * @param Builder|Model $query
     * @param QueryStringData $queryData
     * @return Builder
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
            $queryData->has('groupBy'),
            fn(Builder $query) => $query->groupBy($queryData->groupBy)
        )->when(
            $queryData->has('paginate'),
            fn(Builder $query) => null // $query->paginate($queryData->per_page) TODO: Implement pagination
        );
    }

    /**
     * Sort the query
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function sort(Builder $query, Request $request): Builder
    {
        foreach (explode(',', $request->query->get('sort')) as $sort) {
            list($sortCol, $sortDir) = explode('|', $sort);
            $query = $query->orderBy($sortCol, $sortDir);
        }

        return $query;
    }

    /**
     * Default sort method
     *
     * @param Builder $query
     * @return Builder
     */
    public function noSort(Builder $query): Builder
    {
        return $query->orderBy('id', 'asc');
    }
}