<?php

namespace NwApi\Middlewares;

use Slim\Middleware;
use NwApi\Di;

class Json extends Middleware
{
    public function call()
    {
        $this->next->call();
        if ($this->app->response->headers->get('Content-Type') === 'application/json') {
            $this->handleJsonResponse();
        }
    }

    protected function handleJsonResponse()
    {
        $acceptContentType = $this->app->request->headers->get('Accept');
        $body = $this->app->response->getBody();

        if (strpos($acceptContentType, 'text/html') !== false) {
            $di = Di::getInstance();
            $this->app->response->headers->set('Content-Type', 'text/html');
            $this->app->response->setBody('');
            $responseStatus = $this->app->response->getStatus();
            $this->app->render('JsonMiddleware.php', [
                'requestUri' => $this->app->request->getResourceUri(),
                'requestHeaders' => $this->app->request->headers->all(),
                'requestBody' => $this->app->request->getBody(),
                'responseHeaders' => $this->app->response->headers->all(),
                'responseBody' => $body,
                'responseStatusMessage' => $this->app->response->getMessageForCode($responseStatus),
                'sqlQueries' => $di->sqlLogger->queries,
            ]);
        }
    }
}
