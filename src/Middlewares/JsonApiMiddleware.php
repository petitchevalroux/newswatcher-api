<?php

namespace NwApi\Middlewares;

use Slim\Middleware;
use NwApi\Di;
use NwApi\Exceptions\Http as HttpException;
use NwApi\Exceptions\Api as ApiException;
use Exception;
use NwApi\Libraries\RestDoctrineRouter as JsonApiRouter;
use NwApi\Controllers\JsonApiController;

class JsonApiMiddleware extends Middleware
{
    public function call()
    {
        try {
            try {
                // Append route to the app
                $this->app = JsonApiRouter::getInstance()->addRoutes($this->app, JsonApiController::getInstance());
                $this->next->call();
                if ($this->app->response->headers->get('Content-Type') === 'application/json') {
                    $this->handleJsonResponse();
                }
            } catch (HttpException $ex) {
                $this->app->response->setStatus($ex->getCode());
                throw $ex;
            }
        } catch (\Exception $ex) {
            $acceptContentType = $this->app->request->headers->get('Accept');
            // If response is not marked as error
            if ($this->app->response->getStatus() < 400) {
                $this->app->response->setStatus(503);
            }
            $di = Di::getInstance();
            // We display message only if it is a valid API exception
            if ($ex instanceof ApiException || $di->env === ENV_DEVELOPMENT) {
                $exceptionMessage = $ex->getMessage();
            } else {
                $exceptionMessage = 'Internal server error';
            }

            // If client does not support html, we send json
            if (strpos($acceptContentType, 'text/html') === false) {
                $this->app->response->headers->set('Content-Type', 'application/json');
                $data = ['error' => $exceptionMessage];

                if ($di->env === ENV_DEVELOPMENT) {
                    $data['file'] = $ex->getFile();
                    $data['line'] = $ex->getLine();
                }
                $this->app->response->setBody(json_encode($data));
            } else {
                // If we are in dev, pass to PrettyException
                if ($di->env === ENV_DEVELOPMENT) {
                    throw $ex;
                }
                $this->app->response->headers->set('Content-Type', 'text/html');
                $this->app->response->setBody('<!DOCTYPE html><html><head><title></title></head><body><p>'.htmlspecialchars($exceptionMessage).'</p></body></html>');
            }
        }
    }

    /**
     * handler Json Response when everything is ok.
     */
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
