<?php
declare(strict_types=1);
namespace Framework\Helpers;

class Functions{

	public static function generateRandomCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    public static function createFolder($location): bool
    {
        if (!file_exists($location)) {
            $result = mkdir($location, 0777, true);
            return $result;
        }
        return false;
    }
}