<?php

namespace Sicet7\Base\Server;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Base\Server\Events\BadRequest;
use Sicet7\Base\Server\Events\PostDispatch;
use Sicet7\Base\Server\Events\PreDispatch;
use Sicet7\Base\Server\Events\TerminateWorker;
use Sicet7\Base\Server\Events\UnhandledException;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;

final readonly class HttpWorker
{
    public function __construct(
        private RequestHandlerInterface $requestHandler,
        private PSR7WorkerInterface $PSR7Worker,
        private ResponseFactoryInterface $responseFactory,
        private ?LoggerInterface $logger = null,
        private ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function run(): void
    {
        do {
            try {
                $request = $this->PSR7Worker->waitRequest();

                if (!($request instanceof ServerRequestInterface)) {
                    $this->logger?->info('Termination request received');
                    $this->eventDispatcher?->dispatch(new TerminateWorker());
                    break;
                }

            } catch (\Throwable $throwable) {
                $this->logger?->notice(
                    'Malformed request received!',
                    $this->throwableToArray($throwable)
                );
                try {
                    $this->eventDispatcher?->dispatch(new BadRequest($throwable));
                    $this->PSR7Worker->respond(
                        $this->responseFactory->createResponse(400, 'Bad Request')
                    );
                } catch (\Throwable $badRequestException) {
                    $this->logger?->error('Failed to deliver bad request response, terminating worker.',
                        $this->throwableToArray($badRequestException)
                    );
                    break;
                }
                continue;
            }

            try {
                $this->eventDispatcher?->dispatch(new PreDispatch($request));
                $response = $this->requestHandler->handle($request);
                $this->eventDispatcher?->dispatch(new PostDispatch($response));
                $this->PSR7Worker->respond($response);
            } catch (\Throwable $throwable) {
                try {
                    $this->logger?->error(
                        'Request handler threw unhandled exception!',
                        $this->throwableToArray($throwable)
                    );
                    $this->eventDispatcher?->dispatch(new UnhandledException($throwable));
                    $this->PSR7Worker->respond(
                        $this->responseFactory->createResponse(500, 'Internal Server Error')
                    );
                } catch (\Throwable $internalServerError) {
                    $this->logger?->error('Failed to deliver internal server error response, terminating worker.',
                        $this->throwableToArray($internalServerError)
                    );
                    break;
                }
            }

        } while(true);
    }

    /**
     * @param \Throwable $throwable
     * @return array
     */
    protected function throwableToArray(\Throwable $throwable): array
    {
        $output = [
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ];
        if ($throwable->getPrevious() instanceof \Throwable) {
            $output['previous'] = $this->throwableToArray($throwable->getPrevious());
        }
        return $output;
    }
}