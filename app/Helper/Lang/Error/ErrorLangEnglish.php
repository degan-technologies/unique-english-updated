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
            'unAuthorized' => 'unAuthorized',

            'user_successfully_registered' => 'User successfully registered',
            'user_successfully_deleted' => 'User successfully deleted',
            
            'profile_successfully_updated' => 'Profile succefully updated',
            'incorrect_old_password' => 'incorrect old password',
            'password_changed' => 'password succefully changed',
            'registration_failed' => 'Registration faild occure occurred',

            'course_successfully_added' => 'Course succefully added',
            'course_successfully_updated' => 'Course succefully updated',
            'course_successfully_deleted' => 'Course is trash succefully',
            'course_not_found' => 'Course could not be found',

            'course_content_successfully_added' => 'Course content succefully added',
            'course_content_successfully_updated' => 'Course content succefully updated',
            'course_content_successfully_deleted' => 'Course content is trash succefully',
            'course_content_not_found' => 'Course content could not be found',

            'registration' => [
                'email.required' => 'Email is required',
                'invalid.email' => 'Invalid email provided',
                'invalid.unique' => 'Email already exist',
                'password.required' => 'Please enter you password',
                'password.min' => 'Password contain atleast four characters',
                
                'first_name.required' => 'First Name is required',
                'first_name.not_regex' => 'First Name must have valid characters',
                'first_name.alpha_das' => 'First Name must have alphabetic  characters',
                'middle_name.not_regex' => 'Middle Name must have valid characters',
                'middle_name.alpha_das' => 'Middle Name must have alphabetic  characters',
                'last_name.not_regex' => 'Last Name must have valid characters',
                'last_name.alpha_das' => 'Last Name must have alphabetic  characters',
                'phone' => 'phone number already exists',
                'phone.regex' => 'add digit only',
                'profile.image' => 'It support only image',
                'bg_image.image' => 'It support only image',
            ],

            'password_reset' => [
                'old_password.required' => 'Old password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'New Password contain atleast four characters',
                'new_password.confirmed' => 'confirm new password',
                'new_password_confirmation.required' => 'password confirm is required',
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

            'courseContent' => [
                'title.required' => 'Title For Content is required ',
                'title.not_regex' => 'Title must be descriptive',
                'title.min' => 'Title must have minmum four characters',
                'description.min' => 'Description must have minmum ten characters',
                'content_type.in' => 'Please add required content type',
                'content_url.file' => 'Upload your file',
                'thumbnail_url.image' => 'Upload you thumbnail image',
                'hour.date_format' => 'Please choose hour',
                'status.in' => 'Do you want to publish',
                'note.min' => 'Note must have minmum ten characters',
            ],

            'enter_your_password' => 'Please enter your password',

            'resource_not_found' => 'resource could not be found',
            'resource_created_successfully' => 'resource created succefully',
            'resource_updated_successfully' => 'Resource succefully updated',
            'resource_deleted_successfully' => 'Resource is trash succefully',

            'session_not_found' => 'session could not be found',
            'session_created_successfully' => 'session created succefully',
            'session_updated_successfully' => 'session succefully updated',
            'session_deleted_successfully' => 'session is trash succefully',

            'participant_not_found' => 'participant could not be found',
            'participant_created_successfully' => 'participant created succefully',
            'participant_updated_successfully' => 'participant succefully updated',
            'participant_deleted_successfully' => 'participant is trash succefully',

            'VertualClassEnrollment_not_found' => 'VertualClassEnrollment could not be found',
            'VertualClassEnrollment_created_successfully' => 'VertualClassEnrollment created succefully',
            'VirtualClassEnrollment_deleted_successfully' => 'VertualClassEnrollment is trash succefully',
            'Unauthorized_to_Create_VirtualClassEnrollment' => 'Unauthorized to Create VirtualClassEnrollment.',
            
          'LiveResource'=>[
            'resurce_url_required' => 'Resource URL is required',
            'resurce_url_url' => 'Resource URL must be a valid URL',
            'resource_name_required' => 'Resource name is required',
            'resource_name_string' => 'Resource name must be a string',
            'live_session_id_required' => 'Live session ID is required',
            'resurce_url_unique' => 'This resource URL has already been taken',
            'resource_name_max' => 'Resource name may not be greater than 255 characters',
                    ],

         'LiveSession' =>[ 
             'title_required' => 'The title field is required.',
             'stream_url_unique' => 'The stream URL must be unique.',
             'stream_url_required' => 'The stream URL field is required.',
             'description_required' => 'The description field is required. ',
             'end_time_after' => 'The end time must be after the start time.',
             'instructor_id_required' => 'The instructor ID field is required.',
             'start_time_after' => 'The start time must be after the current time.',
             'max_participants_required' => 'The maximum participants field is required.',
                    ],

         'Participant' => [
             'status_required' => 'The status field is required.',
             'joined_at_required' => 'The joined at field is required.',
             'joined_at_date' => 'The joined at field must be a valid date.',
                     ],
                    
         'VertualClassEnrollment' => [
            'end_date_required' => 'End date is required',
            'price_plan_required' => 'Price plan is required',
            'enrolled_at_required' => 'Enrolled at date is required',
            'remaining_date_required' => 'Remaining date is required',
            'remaining_date_min' => 'Remaining date must be at least 1 day',
            'end_date_after_enrolled' => 'End date must be after the enrolled date',
                ],
            

        ];
        
    }

/*************  ✨ Codeium Command ⭐  *************/
    /**
     * Get the translations for the language.
     *
     * @return array The array containing language key, name, icon, and language strings.
     */

/******  21dbc386-a388-4d2a-879e-db9daaad10e6  *******/
    public static function translations() {
        return [
            static::$key => ['lang' => static::$name, 'icon' => static::$icon, static::Lang()]
        ];
    }
}
