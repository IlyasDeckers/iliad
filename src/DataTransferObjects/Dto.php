<?php
declare(strict_types=1);
namespace Iliad\DataTransferObjects;

use Iliad\DataTransferObjects\Concerns\StaticCreateDataCollection;
use Iliad\DataTransferObjects\Concerns\StaticCreateFrom;
use Iliad\DataTransferObjects\Concerns\ToArray;

class Dto
{
    use StaticCreateFrom,
        StaticCreateDataCollection,
        ToArray;
}