<?php
declare(strict_types=1);
namespace Framework\Helpers;

use Framework\Helpers\Session;
use Framework\Exceptions\PageNotFoundException;

class CSRF{

	public static function generate(): string
	{
		$tokenName = 'csrf_token';
		$token = md5(uniqid());
		Session::set($tokenName, $token);
		$CSRF = "<input type='hidden' name='{$tokenName}' value='{$token}'>";
		$_ENV['CSRF'] = $CSRF;
		return $CSRF;
	}

	public static function check($token): bool
	{
		$tokenName = 'csrf_token';
		if($token === Session::get($tokenName)){
			Session::delete($tokenName);
			unset($_ENV['CSRF']);
			return true;
		}
		throw new PageNotFoundException("visit page properly please");
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
			unset($_ENV['CSRF']);
			return true;
		}
		Redirect::to('/500');
		return false;
	}

	public static function autoCheck(): bool
	{
		if(isset($_POST['csrf_token'])){
			return self::check($_POST['csrf_token']);
		}
		return false;
	}
}

