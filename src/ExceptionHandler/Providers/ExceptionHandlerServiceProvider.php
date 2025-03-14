<?php

namespace Iliad\ExceptionHandler\Providers;

use Iliad\ExceptionHandler\HandlerDecorator;
use Iliad\ExceptionHandler\HandlersRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

/**
 * The exception handler service provider.
 */
class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerExceptionHandlersRepository();
        $this->extendExceptionHandler();
    }

    /**
     * Register the custom exception handlers repository.
     */
    private function registerExceptionHandlersRepository(): void
    {
        $this->app->singleton(HandlersRepository::class);
    }

    /**
     * Extend the Laravel default exception handler.
     */
    private function extendExceptionHandler(): void
    {
        $this->app->extend(ExceptionHandler::class, function (ExceptionHandler $handler, $app) {
            return new HandlerDecorator($handler, $app[HandlersRepository::class]);
        });
    }
}