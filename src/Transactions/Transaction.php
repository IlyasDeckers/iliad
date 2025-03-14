<?php
namespace Iliad\Transactions;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Throwable;

trait Transaction
{
    public ?TransactionManager $transactionManager = null;

    private function getTransactionManager(): TransactionManager
    {
        if ($this->transactionManager === null) {
            $this->transactionManager = App::make(TransactionManager::class);
        }

        return $this->transactionManager;
    }

    private function startTransactions(): void
    {
        if (!Request::isMethod('get')) {
            $this->getTransactionManager()->beginTransaction();
        }
    }

    private function commitTransactions(): void
    {
        $this->getTransactionManager()->commit();
    }

    /**
     * @deprecated Use commitTransactions() instead
     */
    private function flush(): void
    {
        $this->commitTransactions();
    }

    /**
     * @deprecated Use TransactionManager::transaction() instead
     * @param array<int, mixed> $args
     * @throws Throwable
     */
    public function __call(string $method, array $args): mixed
    {
        if (!Request::isMethod('get')) {
            return $this->useTransactions($method, $args);
        }

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * @deprecated Use TransactionManager::transaction() instead
     * @param array<int, mixed> $args
     * @throws Throwable
     */
    private function useTransactions(string $method, array $args): mixed
    {
        return $this->getTransactionManager()->transaction(function() use ($method, $args) {
            if (!method_exists($this, $method)) {
                throw new \Exception("Method '{$method}' doesn't exist");
            }

            return call_user_func_array([$this, $method], $args);
        });
    }
}