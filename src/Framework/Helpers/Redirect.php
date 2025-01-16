<?php
declare(strict_types= 1);
namespace Framework\Helpers;
use Framework\Helpers\Session;

class Redirect{
	public static function to($location = 'index', $params = []): never
        {
                if(($location == 'index') || ($location == 'home') || ($location == '')){
                        $location = '';
                }elseif($location == '404'){
                        $location = 'home/error404';
                }
                $url = '';
                $params = (array)$params;
                if(count($params) > 0){
                        foreach($params as $param){
                                if(!empty($param)){
                                        $url .='/'.str_replace(' ','-', $param);
                                }else{
                                        $url = '';
                                }
                        }
                }
                header('location: '.$_ENV['URL_ROOT'].$location.$url);
                exit();
	}

        public static function link($location = '/index', $params = []){

                if(($location == 'index') || ($location == 'home') || ($location == '') ){
                        $location = '';
                }elseif($location == '404'){
                        $location = 'home/error404';
                }	

                $url = '';
                $params = (array)$params;
                if(count($params) > 0){
                        foreach($params as $param){
                                if(!empty($param)){
                                        $url .='/'.str_replace(' ','-', $param);
                                }else{
                                        $url = '';
                                }
                        }
                }
                return $_ENV['URL_ROOT'].$location.$url;
        }

        public static function param($param = null): void
        {
                if(empty($param)){
                        self::to('error404');
                }
        }

        public static function post($url = ''): void  
        {
                if(empty($_POST)){
                        self::to($url);
                }
        }

        public static function with($key, $value): void
        {
                Session::set($key, $value);
        }
}