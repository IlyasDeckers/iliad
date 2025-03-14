<?php
namespace Iliad\Transactions;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionManager
{
    private static bool $handlerRegistered = false;
    private static int $activeTransactions = 0;

    public function beginTransaction(): void
    {
        if (self::$activeTransactions === 0) {
            $this->registerExceptionHandler();
        }

        self::$activeTransactions++;
        DB::beginTransaction();
    }

    public function commit(): void
    {
        if (self::$activeTransactions > 0) {
            DB::commit();
            self::$activeTransactions--;
        }
    }

    public function rollback(): void
    {
        if (self::$activeTransactions > 0) {
            DB::rollBack();
            self::$activeTransactions--;
        }
    }

    private function registerExceptionHandler(): void
    {
        if (self::$handlerRegistered) {
            return;
        }

        app(ExceptionHandler::class)->renderer(function (Throwable $e) {
            $this->rollbackAll();
            // Log the exception
            Log::error('Transaction rolled back due to exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        });

        self::$handlerRegistered = true;
    }

    private function rollbackAll(): void
    {
        while (self::$activeTransactions > 0) {
            $this->rollback();
        }
    }

    /**
     * Run a closure within a transaction
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}