<?php
declare(strict_types=1);
namespace Clockwork\Core\DataTransferObjects;

use Clockwork\Core\DataTransferObjects\Concerns\StaticCreateDataCollection;
use Clockwork\Core\DataTransferObjects\Concerns\StaticCreateFrom;
use Clockwork\Core\DataTransferObjects\Concerns\ToArray;

class Dto
{
    use StaticCreateFrom,
        StaticCreateDataCollection,
        ToArray;
}