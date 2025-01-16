<?php
declare(strict_types=1);
namespace Framework\Helpers;
class Media{
    public static function base64($file){
        return base64_encode(file_get_contents($file));
    }

    public static function audio($audio): string
    {
        $audio_path = "{$_ENV['MEDIA_PATH']}/audios/";
        $audioData = file_exists("{$audio_path}{$audio}") ? self::base64("{$audio_path}{$audio}") : self::base64("{$audio_path}default.mp3");
        return "data:audio/mpeg;base64, {$audioData}"; 
    }

    public static function avatar($avatar): string
    {
        $avatar_path = "{$_ENV['MEDIA_PATH']}/avatars/";
        $imageData = file_exists("{$avatar_path}{$avatar}") ? self::base64("{$avatar_path}{$avatar}") :  self::base64("{$avatar_path}default.jpg");
        return "data:image/jpeg;base64,{$imageData}";
    }

    public static function cover($cover): string
    {
        $cover_path = "{$_ENV['MEDIA_PATH']}/covers/";
        $imageData = file_exists("{$cover_path}{$cover}") ? self::base64("{$cover_path}{$cover}") : self::base64("{$cover_path}default.jpg");
        return "data:image/jpeg;base64,{$imageData}";
    }

    public static function video($video): string
    {
        $video_path = "{$_ENV['MEDIA_PATH']}/videos/";
        $videoData = file_exists("{$video_path}{$video}") ? self::base64("{$video_path}{$video}") : self::base64("{$video_path}default.mp4");
        return "data:video/mp4;base64,{$videoData}";   
    }

    public static function questionImage($image): string
    {
        $image_path = "{$_ENV['CSV_PATH']}/papers/images/";
        $imageData = file_exists("{$image_path}{$image}") ? self::base64("{$image_path}{$image}"): null;
        return "data:image/jpeg;base64,{$imageData}";
    }
}