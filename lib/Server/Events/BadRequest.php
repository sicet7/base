<?php

namespace Sicet7\Base\Server\Events;

final readonly class BadRequest
{
    public function __construct(
        public \Throwable $throwable
    ) {
    }
}