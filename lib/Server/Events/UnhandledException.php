<?php

namespace Sicet7\Base\Server\Events;

final readonly class UnhandledException
{
    public function __construct(
        public \Throwable $throwable
    ) {
    }
}