<?php
namespace Iliad\ExceptionHandler;

use ReflectionFunction;
use Throwable;

/**
 * The handlers' repository.
 */
class HandlersRepository
{
    /**
     * The custom handlers reporting exceptions.
     *
     * @var array<int, callable>
     */
    protected array $reporters = [];

    /**
     * The custom handlers rendering exceptions.
     *
     * @var array<int, callable>
     */
    protected array $renderers = [];

    /**
     * The custom handlers rendering exceptions in console.
     *
     * @var array<int, callable>
     */
    protected array $consoleRenderers = [];

    /**
     * Register a custom handler to report exceptions
     */
    public function addReporter(callable $reporter): int
    {
        return array_unshift($this->reporters, $reporter);
    }

    /**
     * Register a custom handler to render exceptions
     */
    public function addRenderer(callable $renderer): int
    {
        return array_unshift($this->renderers, $renderer);
    }

    /**
     * Register a custom handler to render exceptions in console
     */
    public function addConsoleRenderer(callable $renderer): int
    {
        return array_unshift($this->consoleRenderers, $renderer);
    }

    /**
     * Retrieve all reporters handling the given exception
     *
     * @return array<int, callable>
     */
    public function getReportersByException(Throwable $e): array
    {
        return array_filter($this->reporters, function (callable $handler) use ($e) {
            return $this->handlesException($handler, $e);
        });
    }

    /**
     * Determine whether the given handler can handle the provided exception
     */
    protected function handlesException(callable $handler, Throwable $e): bool
    {
        $reflection = new ReflectionFunction($handler);

        if (!$params = $reflection->getParameters()) {
            return false;
        }

        return $params[0]->getType()?->getName()
            ? is_a($e, $params[0]->getType()->getName())
            : true;
    }

    /**
     * Retrieve all renderers handling the given exception
     *
     * @return array<int, callable>
     */
    public function getRenderersByException(Throwable $e): array
    {
        return array_filter($this->renderers, function (callable $handler) use ($e) {
            return $this->handlesException($handler, $e);
        });
    }

    /**
     * Retrieve all console renderers handling the given exception
     *
     * @return array<int, callable>
     */
    public function getConsoleRenderersByException(Throwable $e): array
    {
        return array_filter($this->consoleRenderers, function (callable $handler) use ($e) {
            return $this->handlesException($handler, $e);
        });
    }
}