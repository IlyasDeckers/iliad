<?php

namespace Clockwork\Core\RouteAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Fallback
{
    public function __construct()
    {
    }
}
