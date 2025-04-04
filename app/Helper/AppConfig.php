<?php
namespace App\Helper;

define('DB_CONNECTION', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_DATABASE', 'unique');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'qlarapass');

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
define('STUDENT_ROLE', 'student');
define('INSTRUCTOR_ROLE', 'instructor');
define('SYSTEM_ADMIN_ROLE', 'system_admin');

define('AMAHARIC', 'am');
define('ENGLISH', 'en');

define('VIDEO', 1);
define('PDF', 2);
define('IMAGE', 3);

define('CONTENT_TYPE',[
    VIDEO, PDF, IMAGE
]);

define('VIDEO_EXTENTION',[
    'mp4', 'avi', 'mpeg', 'mov', 'wmv', 'webm', 'ogg', 'flv', 'mkv'
]);

define('PDF_EXTENTION',[
    'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'
]);

define('IMAGE_EXTENTION',[
    'jpg', 'jpeg', 'png', 'gif'
]);

define('PUBLISHED', 'published');
define('DRAFT', 'draft');
define('ARCHIVED', 'archived'); 

define('COURSE_STATUS', [
    PUBLISHED, DRAFT, ARCHIVED
]);

define('SCHEDULED', 1);
define('LIVE', 2);
define('COMPLETED', 3);
define('CANCELLED', 4);
define('LiVESESSION_STATUS',[
    SCHEDULED,LIVE,COMPLETED,CANCELLED
]);

define('STARTED', 1);
define('COURSE_CONTENT_STATUS', [
    STARTED, COMPLETED
]);

define('REGISTERED', 1);
define('JOINED', 2);
define('LEFT', 3);
define('PARTICIPANT_STATUS', [
    REGISTERED, JOINED, LEFT
]);

define('BOOK', 'book');
define('COURSE', 'course');
define('LIVE_CLASS', 'live');
define('ORDER_TYPES',[
    BOOK, COURSE, LIVE_CLASS,
]);

define('DEBIT', 'debit');
define('CREDIT', 'credit');
define('TRANSACTION_TYPES',[
    DEBIT, CREDIT
]);

define('PAYPAL', 'payPal');
define('STRIPE', 'stripe');
define('CHAPA', 'chapa');
define('BANK', 'bank');
define('TELEBIRR', 'teleBirr');
define('CASH', 'cash');


define('TRUE_FALSE', 'true_false');
define('CHOICE', 'choice');
define('SHORT_ANSWER', 'short_answer');
define('QUESTION_TYPES',[
    TRUE_FALSE, CHOICE,SHORT_ANSWER
]);


define('TRANSACTION_METHODS', [
    PAYPAL, STRIPE, CHAPA, BANK, TELEBIRR, CASH,
]);

define('BIGINNER', 1);
define('INTERMIDIATE', 2);
define('ADVANCE', 3);
define('FULL_PACKAGE', 4);

define('SKILL_LEVEL', [
    BIGINNER, INTERMIDIATE, ADVANCE, FULL_PACKAGE
]);

define('TRANSACTION_PENDING', 'pending');
define('TRANSACTION_SUCCESS', 'success');
define('TRANSACTION_FAILED', 'failed');
define('TRANSACTION_REFUNDED', 'refunded');

define('TRANSACTION_STATUS', [
    TRANSACTION_PENDING, TRANSACTION_SUCCESS, 
    TRANSACTION_FAILED, TRANSACTION_REFUNDED,
]);

define('WITHDRAWAL', 'withdrawal');
define('DEPOSIT', 'deposit');
define('TRANSFER', 'transfer');

define('TRANSACTION_CATEGORY', [
    WITHDRAWAL, DEPOSIT, TRANSFER
]);

define('LIKED', 'liked');
define('DISLIKED', 'disliked');

define('FEEDBACK_ACTIONS', [
    LIKED,
    DISLIKED
]);

define('CORRECT', 'correct');
define('INCORRECT', 'incorrect');

define('TODAY', 'today');
define('THIS_MONTH', 'month');
define('ALL_TIME', 'allTime');
