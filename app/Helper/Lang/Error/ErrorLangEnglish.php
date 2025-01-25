<?php

namespace App\Helper\Lang\Error;

use App\Helper\Lang\Lang;
use App\Models\Course\Course;

class ErrorLangEnglish extends Lang {

    public static $key = 'en';
    public static $name = 'english';
    public static $icon = 'en.png';

    public static function lang() {
        return [
            'email_required' => 'Email is required',
            'invalid_email' => 'Invalid email provided',
            'enter_your_password' => 'Please enter you password',
            'invalid_credentials' => 'Invalid credentials',

            'user_successfully_registered' => 'User successfully registered',
            'course_successfully_added' => 'Course succefully added',
            'course_successfully_updated' => 'Course succefully updated',
            'course_successfully_deleted' => 'Course is trash succefully',
            'course_not_found' => 'Course could not be found',

            'registration' => [
                'email.required' => 'Email is required',
                'invalid.email' => 'Invalid email provided',
                'invalid.unique' => 'Email already exist',
                'password.required' => 'Please enter you password',
                'password.min' => 'Password contain atleast four characters',

                'first_name.required' => 'Name is required',
                'first_name.not_regex' => 'Name must have valid characters',
                'first_name.alpha_das' => 'Name must have alphabetic  characters',
                'middle_name.not_regex' => 'Name must have valid characters',
                'middle_name.alpha_das' => 'Name must have alphabetic  characters',
            ],

            'courses' => [
                'course_name.required' => 'Course name is required ',
                'course_name.not_regex' => 'Course name must be alphabet only',
                'overview.min' => 'Overview must have minmum ten characters',
                'tag.min' => 'Tag must have minmum three characters',
                'skill_level.numeric' => 'please send valid skill level',
                'price.numeric' => 'please insert number only',
                'discount.numeric' => 'please insert number only',
                'credit_hour.numeric' => 'please insert number only',
            ],
        ];
    }

    public static function translations() {
        return [
            static::$key => ['lang' => static::$name, 'icon' => static::$icon, static::Lang()]
        ];
    }
}
