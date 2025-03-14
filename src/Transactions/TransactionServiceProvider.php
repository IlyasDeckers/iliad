<?php
namespace Iliad\Transactions;

use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TransactionManager::class);
    }
}