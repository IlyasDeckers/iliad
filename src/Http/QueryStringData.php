<?php
declare(strict_types=1);
namespace Iliad\Http;

use Iliad\DataTransferObjects\Dto;

final class QueryStringData extends Dto
{
    public string|array|null $with;

    public ?string $scopes;

    public ?string $count;

    public ?string $orderBy;

    public ?string $groupBy;

    public ?string $paginate;

    public ?string $exclude;

    public function has(string $key): bool
    {
        return !is_null($this->{$key});
    }

    public function parseToArray(string|array $key): array
    {
        if (is_array($this->{$key})) {
            return $this->{$key};
        }

        return explode(',', $this->{$key});
    }
}

