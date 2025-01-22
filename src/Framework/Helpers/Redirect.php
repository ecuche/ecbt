<?php
declare(strict_types= 1);
namespace Framework\Helpers;
use Framework\Helpers\Session;

class Redirect{
	public static function to($location = ''): never
        {
                if(($location == 'index') || ($location == 'home') || ($location == '')){
                        $location = '';
                }elseif($location == '404'){
                        $location = '404';
                }
                header('location: '.$_ENV['URL_ROOT'].$location);
                exit();
	}

        public static function link($location = ''){
                if(($location == 'index') || ($location == 'home') || ($location == '') ){
                        $location = '';
                }elseif($location == '404'){
                        $location = '404';
                }	
                return $_ENV['URL_ROOT'].$location;
        }

        public static function notPost($url = '')  
        {
                if(empty($_POST)){
                        self::to($url);
                }
        }
}