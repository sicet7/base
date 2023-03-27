<?php

namespace Sicet7\Base\Factories;

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory as SymfonyToPsrHttpFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseFactory
{
    public function __construct(
        public readonly SymfonyToPsrHttpFactory $symfonyToPsrFactory
    ) {
    }

    /**
     * @param array|\JsonSerializable $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function fromData(
        array|\JsonSerializable $data,
        int $status = 200,
        array $headers = [],
    ): ResponseInterface {
        return $this->symfonyToPsrFactory->createResponse(new JsonResponse(
            $data,
            $status,
            $headers,
        ));
    }

    /**
     * @param string $json
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function fromString(
        string $json,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        return $this->symfonyToPsrFactory->createResponse(new JsonResponse(
            $json,
            $status,
            $headers,
            true
        ));
    }
}