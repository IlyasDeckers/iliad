<?php
namespace Clockwork\Core\Concerns;

use App;
use DB;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Request;
use Throwable;

trait Transaction
{
    private function startTransactions (): void
    {
        DB::beginTransaction();
        $this->registerExceptionHandler();
    }

    private function registerExceptionHandler(): void
    {
        App::make(ExceptionHandler::class)->renderer(function (\Exception $e) {
            // TODO: add error logging
            DB::rollBack();
        });
    }

    private function commitTransactions(): void
    {
        DB::commit();
    }

    private function flush(): void
    {
        DB::commit();
    }

    /**
     * Kept for backwards compatability.
     *
     * Call the private function with database transactions
     * for the methods store, update and delete.
     *
     * When the called function is set to public, __call will
     * be omitted and no transactions are performed.
     *
     * @param string $method
     * @param array $args
     * @return void
     * @throws Throwable
     */
    public function __call(string $method, array $args)
    {
        if (!Request::isMethod('get')) {
            return $this->useTransactions($method, $args);
        }

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * @throws Throwable
     */
    private function useTransactions(string $method, array $args)
    {
        try {
            DB::beginTransaction();
            // Check if the method exists on the class this trait
            // has been implemented in. Next we call this function.
            if (!method_exists($this, $method)) {
                throw new Exception("Method '{$method}' doesn't exist");
            }

            $response = call_user_func_array([$this, $method], $args);
            DB::commit();
        } catch (Exception $e) {
            // If the method call throws an exception rollback the
            // database queries and format the exception.
            DB::rollback();
            throw new Exception($e->getMessage());
        }

        return $response;
    }
}
