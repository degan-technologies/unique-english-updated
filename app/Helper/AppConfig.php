<?php
namespace App\Helper;

define('DB_CONNECTION', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_DATABASE', 'unique');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1234');

define('MALE', 1);
define('FEMALE', 2);
define('GENDER', [
    MALE,
    FEMALE
]);

define('SYSTEM_ADMIN', 1);
define('INSTRUCTOR', 2);
define('STUDENT', 3);

define('AMAHARIC', 'am');
define('ENGLISH', 'en');

define('VIDEO', 1);
define('PDF', 2);
define('IMAGE', 3);

define('CONTENT_TYPE',[
    VIDEO, PDF, IMAGE
]);

define('PUBLISHED', 1);
define('DRAFT', 2);
define('ARCHIVED', 3);

define('COURSE_STATUS', [
    PUBLISHED, DRAFT, ARCHIVED
]);