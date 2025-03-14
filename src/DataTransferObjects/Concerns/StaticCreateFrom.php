<?php
declare(strict_types=1);
namespace Iliad\DataTransferObjects\Concerns;

use Iliad\DataTransferObjects\Dto;
use ReflectionClass;

trait StaticCreateFrom
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function from(array $values): static
    {
        $dto = new static();

        $dtoProperties = (new ReflectionClass($dto))->getProperties();
        foreach ($dtoProperties as $dtoProperty) {
            $dtoPropertyName = $dtoProperty->getName();
            $type = (new \ReflectionProperty(static::class, $dtoPropertyName))->getType();
            if (array_key_exists($dtoPropertyName, $values)) {
                $dto->{$dtoPropertyName} = $values[$dtoPropertyName];
            } else {
                if (!$type->allowsNull()) {
                    $className = static::class;
                    throw new \Exception("Property {$dtoPropertyName} not nullable on {$className}");
                }

                $dto->{$dtoPropertyName} = null;
            }
        }

        return $dto;
    }
}