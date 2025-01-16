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
}