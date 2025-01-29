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
define('SECONDARY_ADMIN', 2);


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

define('REGISTERED', 1);
define('JOINED', 2);
define('LEFT', 3);

define('SCHEDULED', 1);
define('LIVE', 2);
define('COMPLETED', 3);
define('CANCELLED', 4);
define('LiVESESSION_STATUS',[SCHEDULED,LIVE,COMPLETED,CANCELLED]);

define('REGISTERED', 1);
define('JOINED', 2);
define('LEFT', 3);
define('PARTICIPANT_STATUS', [REGISTERED, JOINED, LEFT]);

