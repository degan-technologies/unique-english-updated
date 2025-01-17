<?php

namespace App\Helper\Lang\Back;

use App\Helper\Lang\Lang;

class English extends Lang {

    public static $key = 'en';
    public static $name = 'english';
    public static $icon = 'en.png';

    public static function lang() {
        return [
            'name' => 'Name',
            'age' => 'Age',
        ];
    }

    public static function translations() {
        return [
            static::$key => ['name' => static::$name, 'icon' => static::$icon,'lang' => static::Lang()]
        ];
    }
}