<?php
declare(strict_types=1);
namespace Iliad\DataTransferObjects\Concerns;

use Spatie\LaravelData\DataCollection;

trait StaticCreateDataCollection
{
    public static function collect(array $values): DataCollection
    {
        return (new DataCollection(static::class, $values));
    }
}