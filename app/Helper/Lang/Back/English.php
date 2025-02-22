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
            'email' => 'Email',
            'password' => 'Password',
            'login' => 'Login',
            'termsAndConditions' => 'You must agree to the Terms & Conditions.',
            'wellcomeToPlatform' => 'Welcome to Our Platform',
            'joinUsAndSignUp'=>'Join us and experience the best services tailored just for you. Sign up now to begin your journey!',
            'createAccount' => 'Create Your Account',
            'startjourneywithus' => 'Start your journey with us today!',
            'signupwithfacebook' => 'Sign up with Facebook',
            'signupwithgoogle' => 'Sign up with Google',
            'signup' => 'Sign Up',
            'CreatingAccount' => 'Creating Account...',
            'haveAccount' => ' Already have an account?',
            'agreeto' => 'I agree to the',
            'termsAndConditions' => 'Terms & Conditions',
            'or' => 'Or',

            'updateProfile' => 'Update Profile',
            'profile' => 'Profile Image',
            'backgroundImage'=>'Background Image',
            'firstName'=>'First Name',
            'middleName'=>'Middle Name',
            'lastName'=>'Last Name',
            'email'=>'Email',
            'phone'=>'Phone',
            'gender'=>'Gender',
            'selectGender'=>'Select Gender',
            'male'=>'Male',
            'female'=>'Female',
            'UpdateProfile' => 'Update Profile',

            'changePassword'=>'Change Password',
            'currentPassword'=>'Current Password',
            'newPassword'=>'New Password',
            'confirmPassword'=>'Confirm Password',
            'updatePassword'=>'Update Password',

            'uniquee'=>'UNIQUE ENGLISH',
            'fullname'=>'FULLNAME',
            'logout'=>'Logout',
      
            'PasswordChangeForm' => 'Password Change', 
            'ProfileForm' => 'Profile', 
            'AboutUs' => 'About Us',
            'SignUp' => 'Sign Up',
            'ContactUs' => 'Contact Us',
            'FAQPage' => 'FAQ Page',
            'PrivacyPolicy' => 'Privacy Policy',
            'TermsOfService' => 'Terms Of Service',

            'register_student' => 'Register Student',

            'course_modules' => 'Course Modules',
            // You can also add other keys as needed, e.g.:
            'module_successfully_added' => 'Module successfully added.',
            'module_successfully_updated' => 'Module successfully updated.',
            'module_successfully_deleted' => 'Module successfully deleted.',
            'module_not_found' => 'Module not found.',
            'unauthorized_action' => 'Unauthorized action.',
        ];
    }

    public static function translations() {
        return ['key' => static::$key, 'name' => static::$name, 'icon' => static::$icon, 'lang' => static::Lang()];
    }
}