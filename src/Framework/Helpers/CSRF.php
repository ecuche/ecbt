<?php
declare(strict_types=1);
namespace Framework\Helpers;

use Framework\Helpers\Session;
class CSRF{

	public static function generate(): string
	{
		$tokenName = 'csrf_token';
		$token = md5(uniqid());
		Session::set($tokenName, $token);
		return "<input type='hidden' name='{$tokenName}' value='{$token}'>";
	}

	public static function check($token): bool
	{
		$tokenName = 'csrf_token';
		if($token === Session::get($tokenName)){
			Session::delete($tokenName);
			return true;
		}
		Redirect::to('/500');
		return false;
	}

	public static function generateMethod(): string
	{
		self::generate();
		Session::set('method_csrf_token', Session::get('csrf_token'));
		return Session::get('method_csrf_token');
	}

	public static function checkMethod($method_csrf): bool
	{
		if($method_csrf === Session::get('method_csrf_token')){
			Session::delete('method_csrf_token');
			return true;
		}
		Redirect::to('/500');
		return false;
	}
}

