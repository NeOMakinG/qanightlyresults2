<?php
use \DI\Bridge\Slim\Bridge;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = Bridge::create();

/*
 * Load routes
 */
require __DIR__ . '/../src/routes.php';

$app->addRoutingMiddleware();

/*
 * Load settings
 */
require __DIR__ . '/../src/settings.php';

$app->add(function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('Content-Type', 'application/json');
});

$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $payload = ['error' => $exception->getMessage()];
    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );
    return $response->withHeader('Content-Type', 'application/json');
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->run();
