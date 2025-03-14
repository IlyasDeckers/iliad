<?php
namespace Iliad\Repositories;

use Iliad\Transactions\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    use Transaction;

    protected $request;

    protected $model;

    /**
     * Get a item from the database
     *
     * @param object $request
     * @return object
     */
    public function find(object $request): object
    {
        return $this->itemResponse(
            $request,
            $this->model->where('id', $request->id)
        );
    }

    /**
     * Get a collection from the database
     *
     * @param object $request
     * @return object
     */
    public function getAll(object $request): object
    {
        return $this->collectionResponse(
            $request,
            $this->model
        );
    }

    /**
     * Get an item and it's relations
     *
     * @param Request $request
     * @param object $query
     * @return object
     */
    public function itemResponse(object $request, object $query): object
    {
        $this->request = $request;

        return $query
            ->when($request->has('with'), [$this, 'with'])
            ->first();
    }

    /**
     * Get a collection with relations,
     * scopes, sorting and filtering.
     *
     * @param object $request
     * @param object $query
     * @return Collection
     */
    public function collectionResponse(object $request, object $query): Collection
    {
        $this->request = $request;

        $query = $query
            ->when($request->has('filter'), [$this, 'filter'])
            ->when($request->has('with'), [$this, 'with'])
            ->when($request->has('scopes'), [$this, 'scopes'])
            ->when(
                $request->has('sort') && $request->sort !== null,
                /* if */   [$this, 'sort'],
                /* else */ [$this, 'noSort']
            );

        if ($request->paginate == 'true') {
            return $query->paginate($request->per_page);
        }

        return $query->get()
            ->when($request->has('groupBy'), [$this, 'groupBy']);
    }

    /**
     * Filter a query
     *
     * @param Builder $query
     * @return Builder
     */
    public function filter(Builder $query): Builder
    {
        return $query->search($this->request->filter);
    }

    /**
     * Load the relationships
     *
     * @param Builder $query
     * @return Builder
     */
    public function with(Builder $query): Builder
    {
        return $query->with(
            explode(',', $this->request->with)
        );
    }

    /**
     * Get the query scopes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopes(Builder $query) 
    {
        $decoded = json_decode($this->request->scopes, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            $scopes = $decoded;
        } elseif (!is_object($decoded) && !is_array($decoded)) {
            $scopes = explode(',', $this->request->scopes);
        } else {
            $scopes = [];
        }

        return $query->scopes($scopes);
    }

    /**
     * Sort the query
     *
     * @param Builder $query
     * @return Builder
     */
    public function sort(Builder $query) 
    {
        foreach (explode(',', $this->request->sort) as $sort) {
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
    public function noSort(Builder $query) 
    {
        return $query->orderBy('id', 'asc');
    }

    public function groupBy($query) 
    {
        return $query->groupBy($this->request->groupBy);
    }
}
