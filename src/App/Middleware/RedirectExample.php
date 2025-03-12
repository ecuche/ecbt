<?php
declare(strict_types=1);
namespace App\Middleware;

use Framework\Response;
use Framework\Request;
use Framework\RequestHandlerInterface;
use Framework\MiddlewareInterface;


class RedirectExample implements MiddlewareInterface
{
    public function __construct(private Response $response)
    {
    }
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $this->response->redirect("users/index");
        return $this->response;
    }
}