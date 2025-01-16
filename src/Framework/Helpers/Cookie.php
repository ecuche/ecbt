<?php
declare(strict_types=1);
namespace Framework\Helpers;
class Cookie{

	public static function set(string $name, mixed $value, int $expiry = 60*60*24*1): bool
	{
		if(setcookie($name, $value, time() + $expiry, '/')){
			return true;
		}
		return false;
	}

	public static function exists($name): bool
	{
		return isset($_COOKIE[$name]) ? true : false;
	}

	public static function get($name): mixed
	{
		return $_COOKIE[$name] ?? false;
	}

	public static function delete($name): void
	{
		self::set($name, '', time() - 1);
	}

	public static function check($name, $value): bool
	{
		if(self::exists($name) && (self::get($name) == $value)){
			return true;
		}
		return false;
	}
	public static function flash($name): mixed
	{
		if(self::exists($name)){
			$cookie = self::get($name);
			self::delete($name);
			return $cookie;
		}
		return false;
	}
}