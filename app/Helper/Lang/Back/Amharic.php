<?php

namespace App\Helper\Lang\Back;

use App\Helper\Lang\Lang;

class Amharic extends Lang {

    public static $key = 'am';
    public static $name = 'amharic';
    public static $icon = 'am.png';

    public static function lang() {
        return [
            'name' => 'ስም',
            'age' => 'እድሜ',
        ];
    }

    public static function translations() {
        return [
            static::$key => ['name' => static::$name, 'icon' => static::$icon, 'lang' => static::Lang()]
        ];
    }
}
