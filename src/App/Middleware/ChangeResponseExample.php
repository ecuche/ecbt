<?php
declare(strict_types=1);
namespace App\Middleware;

use Framework\Response;
use Framework\Request;
use Framework\RequestHandlerInterface;
use Framework\MiddlewareInterface;


class ChangeResponseExample implements MiddlewareInterface
{
   public function process(Request $request, RequestHandlerInterface $next): Response
   {
       $response = $next->handle($request);
       $response->setBody($response->getBody());
       return $response;
   }
}