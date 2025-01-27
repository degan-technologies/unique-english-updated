<?php

namespace App\Helper\Lang\Error;

use App\Helper\Lang\Lang;

class ErrorLangAmharic extends Lang {

    public static $key = 'am';
    public static $name = 'amharic';
    public static $icon = 'am.png';

    public static function lang() {
        return [
            'email_required' => 'ኢሜል መሞላት አለበት',
            'invalid_email' => 'ያልተገባ ኢሜል አስገብተዋል',
            'enter_your_password' => 'የሚስጥር ቁጥርዎን ያስገቡ',
            
            'resource_name_required' => 'የሪሶርስ ስም መሞላት አለበት',
            'resource_name_string' => 'የሪሶርስ ስም በጽሑፍ መሆን አለበት',
            'resource_name_max' => 'የሪሶርስ ስም በምስል 255 ቁምፊ ያልበለጠ መሆን አለበት',
            'resurce_url_required' => 'የሪሶርስ URL መሞላት አለበት',
            'resurce_url_url' => 'የሪሶርስ URL ታማኝ እና ትክክለኛ መሆን አለበት',
            'resurce_url_unique' => 'ይህ የሪሶርስ URL ተደጋጋሚ ነው',
            'live_session_id_required' => 'live_session_id መሞላት አለበት',
            'live_session_id_exists' => 'live_session_id አልተገኘም',
        ];
        
    }

    public static function translations() {
        return [
            static::$key => ['lang' => static::$name, 'icon' => static::$icon, static::Lang()]
        ];
    }
}
