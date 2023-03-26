<?php

namespace Sicet7\Base\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Controller
{
    public function __construct(public string $prefix = '')
    {
    }
}