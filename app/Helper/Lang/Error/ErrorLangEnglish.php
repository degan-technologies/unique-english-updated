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
            'plan_created' => 'Plan created successfully',
            'plan_updated' => 'Plan updated successfully',
            'plan_deleted' => 'Plan deleted successfully',
            'plan_not_found' => 'Plan not found',
            'test_created_successfully' => 'Test created successfully.',
            'test_updated_successfully' => 'Test updated successfully.',
            'test_deleted_successfully' => 'Test deleted successfully.',
            'test_not_found' => 'Test not found.',
            'validation_failed' => 'Validation failed. Please check your input.',
            'schedule_exists' => 'This time slot already exists for the selected day.',
            'email_required' => 'Email is required',
            'invalid_email' => 'Invalid email provided',

            'enter_your_password' => 'Please enter you password',
            'invalid_credentials' => 'Invalid credentials',
            'unAuthorized' => 'unAuthorized',
            'user_not_found'=>'user could not be found',
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

            'unique_english_payment_transaction' => 'Unique english',
            'my_payment_description' => 'I pay for for unique english with amount of',
            'payment_initiated' => 'Payment initiated',

            'cart_empty' => 'Yor cart is empty',
            'order_not_found' => 'Order could not be found',

            'enter_your_password' => 'Please enter your password',
            'unauthorized' => 'Unauthorized',

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
            'video_camera_status_updated' => 'playing_status_updated',

            'book_created_successfully' => 'book created successfully',
            'book_updated_successfully' => 'book updated successfully',
            'book_deleted_successfully' => 'book deleted successfully',
            'book_not_found' => 'book could not found',

            'ordered_book_deleted' => 'ordered book deleted successfully',
            'ordered_created_successfully' => 'oredered created succesfully',

            'feedback_created_successfully' => 'Feedback created successfully',
            'feedback_updated_successfully' => 'Feedback updated successfully',
            'feedback_deleted_successfully' => 'Feedback deleted successfully',
            'feedback_not_found' => 'Feedback not found',

            'quiz_not_found' => 'quiz could not be found',
            'quiz_created_successfully' => 'quiz created succefully',
            'quiz_updated_successfully' => 'quiz succefully updated',
            'quiz_deleted_successfully' => 'quiz is trash succefully',

            'result_not_found' => 'result could not be found',
            'result_created_successfully' => 'result created succefully',
            'result_updated_successfully' => 'result succefully updated',
            'result_deleted_successfully' => 'result is trash succefully',

            'q_meta_data_not_found' => 'q_meta_data could not be found',
            'q_meta_data_created_successfully' => 'q_meta_data created succefully',
            'q_meta_data_updated_successfully' => 'q_meta_data succefully updated',
            'q_meta_data_deleted_successfully' => 'q_meta_data is trash succefully',

            'qa_section_not_found'=>'qa_section could not be found',
            'qa_section_created_successfully'=>'qa_section created succesfully',
            'qa_section_updated_successfully'=>'qa_section updated succesfully',
            'qa_section_deleted_successfully'=>'qa_section deleted succesfully',
    
            'schedule_not_found' => 'Schedule not found.',
            'schedule_created' => 'Schedule added successfully.',
            'schedule_updated' => 'Schedule updated successfully.',
            'schedule_deleted' => 'Schedule deleted successfully.',

            'meeting_created_successfully'=>'Meeting created successfully',
            'bank_info_created' => 'Bank information created successfully',
            'bank_info_updated' => 'Bank information updated successfully',
            'bankInfo_not_found' => 'Bank information not found',
            
            'incorrect_password' => 'Incorrect password',
            'comission_updated' => 'Comission updated successfully',
            'comission_not_found' => 'Comission not found',
            'comission_added' => 'platform comission is added',
            'comission_error' => 'comission error',

            'registration' => [
                'email.required' => 'Email is required',
                'phone.required' => 'phone number is required',
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
                // 'phone.regex' => 'add digit only',
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
            'modules' => [
                'title.required' => 'Module title is required',
                'title.string'   => 'Module title must be a valid string',
                'title.min'      => 'Module title must have at least 3 characters',
                'description.required' => 'Module description is required',
                'description.string'   => 'Module description must be a valid string',
                'description.min'      => 'Module description must have at least 10 characters',
                'course_id.required'   => 'Course ID is required',
                'course_id.exists'     => 'The selected course does not exist',
            ],

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

            'quiz' => [
                'choice.array' => 'The choice must be an array.',
                'choice.required' => 'The choice field is required.',
                'answer.required' => 'The answer field is required.',
                'ansewer.string' => 'The answer must be a string.',
                'question.string' => 'The question must be a string.',
                'question.required' => 'The question field is required.',
                'question_type.in' => 'The selected question type is invalid.',
                'question_type.required' => 'The question type field is required.',
                 'hint.string'=> 'The hint must be a string',
                 'hint.min' => 'The hint must be at least 10 characters.',
            ],

            'Result' => [
               'result_required' => 'The result field is required.',
               'result_integer' => 'The result must be an integer.',
                ],

            'QASection'=>[
                'question_required'=> 'The question answer field is required.',
                'question_string' => 'The result must be an string.',

                ],

            'QMetaData' => [
                'title_max' => 'The title may not be greater than 255 characters.',
                'instraction_string' => 'The instruction must be a string.',
                'question_type_required' => 'The question type field is required.',
                'question_type_string' => 'The question type must be a string.',
                'question_type_in' => 'The selected question type is invalid.',
            ],

            'transactions' => [
                'enrolled_at.required' => 'Enrolled date is required',
                'enrolled_at.date' => 'Enrolled date should be a valid date',
                'orders.required' => 'Orders are required',
                'orders.array' => 'Orders should be an array',
                'orders.*.type.required' => 'Order is required',
                'orders.*.type.in' => 'Please choose your order',
                'orders.*.slug.required' => 'Order unique id is required',
                'orders.*.slug.string' => 'Order should be valid',
            ],

            'books' => [
                'title.required' => 'The title is required.',
                'price.required' => 'The price is required.',
                'title.string' => 'The title must be a string.',
                'auther.required' => 'The author is required.',
                'auther.string' => 'The author must be a string.',
                'eddition.required' => 'The edition is required.',
                'price.integer' => 'The price must be an integer.',
                'language.required' => 'The language is required.',
                'file_url.required' => 'The file URL is required.',
                'file_url.unique' => 'The file URL must be unique.',
                'file_url.url' => 'The file URL must be a valid URL.',
                'tag.min' => 'Tag must have minmum three characters',
                'language.string' => 'The language must be a string.',
                'title.max' => 'The title cannot exceed 255 characters.',
                'page.number_required' => 'The page number is required.',
                'publish_date.required' => 'The publish date is required.',
                'eddition.integer' => 'The edition must be an integer.',
                'discount.integer' => 'The discount must be an integer.',
                'description.required' => 'The description is required.',
                'auther.max' => 'The author cannot exceed 255 characters.',
                'description.string' => 'The description must be a string.',
                'file_format.required' => 'The file format is required.',
                'file_format.string' => 'The file format must be a string.',
                'cover_page_url.required' => 'The cover page URL is required.',
                'page.number_integer' => 'The page number must be an integer.',
                'cover_page_url.unique' => 'The cover page URL must be unique.',
                'not_deleted.required' => 'The not_deleted field is required.',
                'publish_date.date' => 'The publish date must be a valid date.',
                'cover_page_url.url' => 'The cover page URL must be a valid URL.',
                'not_deleted.boolean' => 'The not_deleted field must be a boolean.',
                'is_downloadable.required' => 'The isDownloadable field is required.',
                'is_downloadable.boolean' => 'The isDownloadable field must be a boolean.',
            ],

            'ordereds' => [
                'enrolled_at.date' > 'The enrolled_at is Date',
                'enrolled_at.required' => 'The enrolled_at  is required.',
            ],

            'feedbacks' => [
                'rate.required' => 'The rate is required.',
                'rate.numeric' => 'The rate must be an integer.',
                'rate.between' => 'The rate must be between 1 and 5.',
                'comment.required' => 'The comment is required.',
                'comment.string' => 'The comment must be a string.',
                'comment.max' => 'The comment cannot exceed 1000 characters.',
                'course_id.required' => 'The course ID is required.',
                'course_id.exists' => 'The selected course does not exist.',
                'favorite.boolean' => 'The favorite field must be a boolean.',
                'issue_type.required' => 'The issue type is required.',
                'issue_details.required' => 'The issue details is required minimum 10 characters',
            ],

            'messages' => [
                'user_id.required' => 'The user ID is required.',
                'user_id.exists' => 'The selected user does not exist.',
                'message.required' => 'The message is required.',
                'message.string' => 'The message must be a string.',

            ],
            'otp_verification' => [
                'phone.required' => 'Phone number is required',
                'phone.regex' => 'Phone number is invalid',
                'otp.required' => 'OTP is required',
                'otp.numeric' => 'OTP must be a number',
                'otp.min' => 'OTP must be 6 digits',
                'otp.max' => 'OTP must be 6 digits',
                'verification_id.required' => 'Verification ID is required',
                'verification_id.string' => 'Verification ID must be a string',
            ],

            'email_otp_verification' => [
                'email.required' => 'Email is required',
                'email.email' => 'Email is invalid',
                'otp.required' => 'OTP is required',
                'otp.numeric' => 'OTP must be a number',
                'otp.min' => 'OTP must be 6 digits',
                'otp.max' => 'OTP must be 6 digits',
                'verification_id.required' => 'Verification ID is required',
                'verification_id.string' => 'Verification ID must be a string',
            ],
            'email_otpResend_verification' => [
                'email.required' => 'Email is required',
                'email.email' => 'Email is invalid',
                'verification_id.required' => 'Verification ID is required',
                'verification_id.string' => 'Verification ID must be a string',
            ],
            'ChatMessage'=> [
                'meeting_id.required' => 'Meeting ID is required',
                'message.required' => 'Message is required',
            ],
            'Meeting'=> [
                'meeting_id.required' => 'Meeting ID is required',
            ],

            'banksInfos' => [
                'full_name.required' => 'Full name is required',
                'bank_name.required' => 'Bank name is required',
                'bank_code.required' => 'Bank code is required',
                'account_number.required' => 'Account number is required',
            ],

            'transfers' => [
                'amount.required' => 'amount is required',
                'amount.numeric' => 'amount must be a number',
                'currency.required' => 'currency is required',
                'currency.in' => 'currency must be ETB or USD',
            ],

            'comission' => [
                'fee.required' => 'fee is required',
                'fee.numeric' => 'fee must be a number',
            ],

            'heroMessages' => [
                'title.string' => 'Title must be a string',
                'title.max' => 'Title cannot exceed 255 characters',
                'description.string' => 'Description must be a string',
                'description.max' => 'Description cannot exceed 1000 characters',
                'logo.image' => 'Image must be an image file',
                'banner.image' => 'Banner must be an image file', 
            ],

            'email_notification' => [
                'subject.required' => 'Subject is required',
                'subject.string' => 'Subject must be a string',
                'message.required' => 'Message is required',
                'message.string' => 'Message must be a string', 
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
