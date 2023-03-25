<?php

namespace Sicet7\Base\Server;

final class WorkerParams
{
    /**
     * @param bool $interceptSideEffects
     */
    public function __construct(public bool $interceptSideEffects = true)
    {
    }
}