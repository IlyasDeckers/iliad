<?php

namespace Clockwork\Core\Http;

use Clockwork\Core\DataTransferObjects\Dto;

class QueryStringData extends Dto
{
    public ?string $with;

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

    public function parseToArray(string $key): array
    {
        return explode(',', $this->{$key});
    }
}
