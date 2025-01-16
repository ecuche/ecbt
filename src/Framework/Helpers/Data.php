<?php
declare(strict_types= 1);
namespace Framework\Helpers;

use DateTime;
class Data{

    public static function date($date): string
    {
        return date('dS M, Y',strtotime($date));
    }

    public static function cap($text): string
    {
        return ucwords($text);  
    }

    public static function is_multi_dim($data): bool
    {
        if(is_array($data)){
            $is_array = array_filter($data, 'is_array');
            if(count($is_array)){
                return true;
            }
            return false;
        }
        return false;
    }

    public static function is_email($email){
        if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        }
            return false;
    }

    public static function timeAgo($timestamp): string 
    {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        
        // Calculate the time ago in different units
        $minutes      = round($seconds / 60);           // value 60 is seconds
        $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
        $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
        $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
        $months       = round($seconds / 2629440);      // value 2629440 is ((365+365+365+365)/4/12/30) days * 24 hours * 60 minutes * 60 sec
        $years        = round($seconds / 31553280);     // value 31553280 is (365+365+365+365)/4 * 24 hours * 60 minutes * 60 sec
        
        // Now determine the appropriate time string
        if ($seconds <= 60) {
            return "Just Now";
        } else if ($minutes <= 60) {
            return ($minutes == 1) ? "one minute ago" : "$minutes minutes ago";
        } else if ($hours <= 24) {
            return ($hours == 1) ? "an hour ago" : "$hours hours ago";
        } else if ($days <= 7) {
            return ($days == 1) ? "yesterday" : "$days days ago";
        } else if ($weeks <= 4.3) {  // 4.3 == 30/7
            return ($weeks == 1) ? "a week ago" : "$weeks weeks ago";
        } else if ($months <= 12) {
            return ($months == 1) ? "a month ago" : "$months months ago";
        } else {
            return ($years == 1) ? "one year ago" : "$years years ago";
        }
    }
    
}