<?php
namespace Iliad\Http\Controllers;

use Iliad\Concerns\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Iliad\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, 
        DispatchesJobs, 
        ValidatesRequests,
        Validator;

    /**
     * Model being used by the controller
     *
     * @var object
     */
    protected $model;

    /**
     * Class reference to the API resource being used on the 
     * controller's methods.
     *
     * @var object
     */
    protected $resource;

    /**
     * Class references that contain the validation rules 
     * for the given request defined in an array that uses 
     * the key as method name and a Request class.
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * Display a listing of the resource.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        return $this->resource::collection(
            $this->model->getAll($request)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        return new $this->resource(
            $this->model->find($request)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return object
     * @throws ValidationException
     */
    public function store(Request $request) : object
    {
        // Validate the incomming request
        $this->validator(__FUNCTION__, $request);

        // Store the resource
        $result = $this->model->store($request);

        // Check if the result is an instance  of Collection. 
        if ($result instanceof Collection) {
            // Return a collection resource.
            return $this->resource::collection($result);
        }

        // Return an item.
        return new $this->resource($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return object
     * @throws ValidationException
     */
    public function update(Request $request) : object
    {
        // Validate the incoming request.
        $this->validator(__FUNCTION__, $request);

        // Update model and return the updated model.
        return new $this->resource(
            $this->model->update($request)
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy(int $id): void
    {
        $this->model->destroy($id);
    }
}