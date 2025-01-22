<?php

namespace App\Helper\Lang\Error;

use App\Helper\Lang\Lang;

class ErrorLangEnglish extends Lang {

    public static $key = 'en';
    public static $name = 'english';
    public static $icon = 'en.png';

    public static function lang() {
        return [
            'email_required' => 'Email is required',
            'invalid_email' => 'Invalid email provided',
            'enter_your_password' => 'Please enter you password',
            'invalid_credentials' => 'Invalid credentials provided',
        ];
    }

    public static function translations() {
        return [
            static::$key => ['lang' => static::$name, 'icon' => static::$icon, static::Lang()]
        ];
    }
}
