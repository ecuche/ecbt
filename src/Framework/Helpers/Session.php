<?php
declare(strict_types= 1);
namespace Framework\Helpers;

session_status() === PHP_SESSION_ACTIVE ?: session_start();
class Session{

	public static function exists($name): bool
	{
		return isset($_SESSION[$name]) ? true : false;
	}

	public static function set($name, $value = ''): bool
	{
		if(is_object($name) || is_array($name)){
			foreach($name as $key => $value){
				$_SESSION[$key] = $value;
			}
			return true;
		}elseif(is_string($name)){
			$_SESSION[$name] = $value;
			return true;
		}
		return false;
	}

	public static function get($name): mixed
	{
		if(is_array($name)){
			$result = [];
			foreach($name as $value){
				if(self::exists($value)){
					$session = [$value => $_SESSION[$value]];
					array_push($result, $session);
				}
			}
			return $result;
		}elseif(is_string($name)){
			if(self::exists($name)){
				$session = $_SESSION[$name];
				return $session;
			}else{
				return false;
			}
		}
		return false;
	}

	public static function delete($name):bool
	{
		if(is_array($name)){
			foreach($name as $key=>$value){
				if(self::exists($value)){
					unset($_SESSION[$value]);
					return true;
				}
			}
		}elseif(is_string($name)){
			if(self::exists($name)){
				unset($_SESSION[$name]);
				return true;
			}
		}
		return false;
	}

	public static function destroy(): bool
	{
		$_SESSION = [];
		// if (ini_get("session.use_cookies")) {
		// 	$params = session_get_cookie_params();
		// 	setcookie(session_name(), '', time() - 42000,
		// 		$params["path"], $params["domain"],
		// 		$params["secure"], $params["httponly"]
		// 	);
		// }
		// $result = session_destroy();
		// session_start();
		// return $result;
		return true;
	}

	public static function regenerate(): bool
	{
		return session_regenerate_id(true);
	}

	public static function flash($name): mixed
	{
		if(is_array($name)){
			$result = [];
			foreach($name as $key){
				if(self::exists($key)){
					$session = self::get($key);
					self::delete($key);
					$result[$key] = $session;
				}
			}
			return $result;
		}elseif(is_string($name)){
			if(self::exists($name)){
				$session = self::get($name);
				self::delete($name);
				return $session;
			}
		}
		return false;
	}

	public static function compare($name, $value): bool
	{
		if(self::exists($name) && (self::get($name) == $value)){
			return true;
		}
		return false;
	}
}