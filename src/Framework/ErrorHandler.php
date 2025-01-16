<?php
declare(strict_types=1);
namespace Framework;
use ErrorException;
use Framework\Exceptions\PageNotFoundException;
use Framework\Controller;
use Throwable; 
class ErrorHandler extends Controller
{

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool 
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleException(Throwable $exception)
    {
        if($exception instanceof PageNotFoundException){
            http_response_code(404);
            $template = "404";
        }else{
            http_response_code(500);
            $template = "500";
        }
    
        if($_ENV["SHOW_ERRORS"]){
            ini_set("display_errors", "1");
        }else{
            ini_set("display_errors", "0");
            ini_set("log_errors", "1");
            require_once dirname(__DIR__, 2)."/views/$template.cav";
        }
        throw $exception;
    }
}