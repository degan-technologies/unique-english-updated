<?php

namespace App\Helper\Lang\Error;

use App\Helper\Lang\Lang;

class ErrorLangAmharic extends Lang {

    public static $key = 'am';
    public static $name = 'amharic';
    public static $icon = 'am.png';

    public static function lang() {
        return [
            'email_required' => 'ኢሜል መሞላት አለበትአለበት',
            'invalid_email' => 'ያልተገባ ኢሜል አስገብተዋልአስገብተዋል',
            'enter_your_password' => 'የሚስጥር ቁጥርዎን ያስገቡ',
        ];
    }

    public static function translations() {
        return [
            static::$key => ['lang' => static::$name, 'icon' => static::$icon, static::Lang()]
        ];
    }
}
