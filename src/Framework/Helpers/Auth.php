<?php
declare(strict_types=1);
namespace Framework\Helpers;
use Framework\Helpers\Redirect;
use Framework\Helpers\Session;
use Framework\Helpers\Cookie;

class Auth
{
    public static function isLoggedIn(): bool
    {
        $id = Session::get('id') ?? false;
        $email = Session::get('email') ?? false;
        $username = Session::get('username') ?? false;
        if(!empty($id) && (!empty($email) || !empty($username))){
            return true;
        }
        return false;
    }

    public static function passRedirect(string $url = '/dashboard', string $message = ''): null|bool
    {
        $message ??= $message;
        if(self::isLoggedIn()){
            self::setIntendedPage();
            !empty($message) ? Session::set('warning', $message): false;
            Redirect::to($url);
        }
        return false;
    }

    public static function failRedirect($url = '', $message = ''): null|bool
    {
        $message ??= $message;
        if(!self::isLoggedIn()){
            self::setIntendedPage();
            !empty($message) ? Session::set('warning', $message): false;
            Redirect::to($url);
        }
        return false;
    }

    public static function login(object $user): bool
    {
        Session::regenerate();
        $id = Session::set('id', $user->id);
        $email = Session::set('email', $user->email);
        $name = Session::set('name', $user->name);
        $role = Session::set('role', $user->role);
        return $id && $email && $name ? true : false;
    }

    public static function logout(string $message = ''): bool
    {   
        Cookie::delete('remember_me');
        $result = Session::destroy();
        // $result = Session::delete('id');
        // Session::delete('email');
        // Session::delete('username');
        Session::set(['success' => $message]); 
        return $result;  
    }

    public static function setPermission(string $value): bool
    {
        return Session::set('permission', $value);
    }

    public static function permissionRedirect(string $value): bool|null
    {
        if(Session::get('permission') !=$value){
            self::setIntendedPage();
            Redirect::to('');
        }
        return false;
    }

    public static function setIntendedPage(): string
    {
        $_SESSION['intended_url'] = substr($_SERVER['REQUEST_URI'], strlen($_ENV["SITE_DIR"]));
        return  $_SESSION['intended_url'];
    }

    public static function returnPage(): string|bool
    {
        if(!empty($_SESSION['intended_url'])){
            $page = $_SESSION['intended_url'];
            Session::delete('intended_url');
            return $page;
        }
        return false;
    }
}