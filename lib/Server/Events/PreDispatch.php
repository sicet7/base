<?php

namespace Sicet7\Base\Server\Events;

use Psr\Http\Message\ServerRequestInterface;

final readonly class PreDispatch
{
    public function __construct(
        public ServerRequestInterface $response,
    ) {
    }
}