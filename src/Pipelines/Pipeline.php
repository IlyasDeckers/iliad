<?php
namespace Iliad\Pipelines;

use Closure;
use InvalidArgumentException;

class Pipeline
{
    protected mixed $passable;

    /**
     * @var array<int, callable|object|string>
     */
    protected array $pipes = [];

    protected string $method = 'handle';

    /**
     * @template T
     * @param T $passable
     * @return static
     */
    public static function send(mixed $passable): static
    {
        $pipeline = new static;
        $pipeline->passable = $passable;
        return $pipeline;
    }

    /**
     * @param array<int, callable|object|string> $pipes
     * @return $this
     */
    public function through(array $pipes): static
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * @template T
     * @param Closure(mixed): T $destination
     * @return T
     */
    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            function (mixed $passable) use ($destination) {
                return $destination($passable);
            }
        );

        return $pipeline($this->passable);
    }

    /**
     * @return mixed
     */
    public function thenReturn(): mixed
    {
        return $this->then(fn(mixed $passable) => $passable);
    }

    protected function carry(): Closure
    {
        return function (callable $stack, mixed $pipe): Closure {
            return function (mixed $passable) use ($stack, $pipe): mixed {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (is_object($pipe)) {
                    return $pipe->{$this->method}($passable, $stack);
                } elseif (is_string($pipe) && class_exists($pipe)) {
                    $pipeInstance = new $pipe;
                    return $pipeInstance->{$this->method}($passable, $stack);
                } else {
                    throw new InvalidArgumentException('Invalid pipe type.');
                }
            };
        };
    }
}