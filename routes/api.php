<?php
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\Live\ChatController;
use App\Http\Controllers\Live\MeetingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Bank\BankInfoController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\Book\orderdController;
use App\Http\Controllers\Course\CourseContentController;
use App\Http\Controllers\Course\CourseModuleController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\FeedBackController;
use App\Http\Controllers\Live\ParticipantController;
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\Quiz\QMetaDataController;
use App\Http\Controllers\Quiz\ResultController;
use App\Http\Controllers\Quiz\QASectionController;
use App\Http\Controllers\Live\LiveSessionController;
use App\Http\Controllers\Live\LiveResourceController;
use App\Http\Controllers\Live\VirtualClassEnrollmentController;
use App\Http\Controllers\Message\SMSController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Plan\PlanController;
use App\Http\Controllers\Schedule\ScheduleController;
use App\Http\Controllers\System\PlatformComissionController;
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\Course\CourseVideoController;
use App\Http\Controllers\Course\CourseContentVideoController;
use App\Http\Controllers\Course\CourseContentProgressController;
use App\Http\Controllers\Quiz\AnswerController;


Route::post('/login', [AuthController::class, 'login']);

Route::post('/registration', [UserController::class, 'store']);

Route::middleware('auth:api')
    ->group(function () {
        Route::post('/add-instructor', [UserController::class, 'addInstructor']);
        Route::post('/add-student', [UserController::class, 'addStudent']);
        Route::post('/register', [UserController::class, 'store']);
        Route::delete('/delete-instructor/{id}', [UserController::class, 'destroy']);
        Route::post('/update-profile', [UserController::class, 'profileUpdate']);
        Route::post('/password-reset', [UserController::class, 'passwordReset']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::resource('/delete-instructor', UserController::class );
        Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete']);
        Route::post('/verify-otp', [UserController::class, 'verifyEmailOTP']);
        Route::post('/resend-otp', [UserController::class, 'resendOTP']);
    });
// Social Login Routes
Route::middleware(['web'])->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);
});


Route::middleware('auth:api')
    ->group(function () {
        Route::get('/current', [AuthController::class, 'currentUser']);

        // Existing resource routes
        Route::resource('live-sessions', LiveSessionController::class);
        Route::resource('live-resources', LiveResourceController::class);
        Route::resource('participants', ParticipantController::class);
       
    Route::resource('quize',QuizController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::resource('QMetaData', QMetaDataController::class);  
    Route::get('/exams', [QMetaDataController::class,'fetchInstructorExam']); 
    Route::resource('tests', TestController::class);
    Route::resource('results', ResultController::class);
    Route::resource('plans', PlanController::class);
    Route::resource('QASection', QASectionController::class);
    Route::resource('answers', AnswerController::class);


     // New progress endpoints for course content
     Route::post('/coursecontent/progress', [CourseContentProgressController::class, 'store']);
     Route::get('/coursecontent/progress', [CourseContentProgressController::class, 'index']);

     Route::get('/coursecontent/progress/{courseContentId}', [CourseContentProgressController::class, 'show']);

    });


Route::middleware('auth:api')
    ->prefix('courses')
    ->group(function () {
        Route::resource('/course', CourseController::class);
        Route::post('/update/{id}', [CourseController::class, 'update']);
        Route::post('/update-content/{id}', [CourseContentController::class, 'update']);
        Route::post('/search', [CourseController::class, 'search']);
        Route::resource('/content', CourseContentController::class);
        Route::resource('/module', CourseModuleController::class);
        Route::get('/transactions/{type}', [TransactionController::class, 'index'])
            ->where('type', 'course|book|live');
        Route::get('/courses/stream/video/{filename}', [CourseVideoController::class, 'stream']);
        Route::get('/coursecontent/stream/video/{filename}', [CourseContentVideoController::class, 'stream']);
        // Route::get('/stream/video/{filename}', [CourseVideoController::class, 'stream']);
    });

Route::middleware('auth:api')
    ->prefix('feedbacks')
    ->group(function () {
        // Standard resource endpoints
        Route::get('/', [FeedBackController::class, 'index']);
        Route::get('/feedback/{slug}', [FeedBackController::class, 'courseFeedBack']);
        Route::post('/', [FeedBackController::class, 'store']);
        Route::get('{id}', [FeedBackController::class, 'show']);
        Route::put('{id}', [FeedBackController::class, 'update']);
        Route::delete('{id}', [FeedBackController::class, 'destroy']);

        // Custom endpoints for additional feedback actions
        Route::post('favorite/{id}', [FeedBackController::class, 'addFavorite']);
        Route::post('{id}/report', [FeedBackController::class, 'report']);
    });

    // SMS endpoints added here
Route::middleware('auth:api')->group(function () {
        Route::post('/send-sms', [SMSController::class, 'sendSMS']);
        Route::post('/send-bulk-sms', [SMSController::class, 'sendBulkSMS']);
        Route::post('/send-otp', [SMSController::class, 'sendOTP']);
        Route::post('/verify-otp-sms', [SMSController::class, 'verifyOTP']);
        });

Route::middleware('auth:api')->group(function () {
        Route::get('/chat/{meetingId}', [ChatController::class, 'index']);
        Route::post('/chat', [ChatController::class, 'store']);
        });

Route::middleware('auth:api')->group(function () {
        Route::post('/meetings', [MeetingController::class, 'create']);
        Route::post('/meetings/join', [MeetingController::class, 'join']);
        Route::post('/meetings/end', [MeetingController::class, 'end']);
    });


Route::middleware('auth:api')
    ->prefix('books')
    ->group(function(){
        Route::resource('/books', BookController::class);
        Route::resource('/order-books', orderdController::class);
    });

Route::middleware('auth:api')
    ->group(function () {
    Route::post('/initiate-payment', [TransactionController::class, 'initiatePayment']);
    Route::get('/transaction', [TransactionController::class, 'transactions']);
    Route::get('/transaction-info/{txRef}', [TransactionController::class, 'transactionInvoce']);
    Route::get('/refend-transaction/{txRef}', [TransactionController::class, 'refundTransaction']);
    Route::get('/bank-lists', [TransactionController::class, 'getBankList']);
    Route::resource('/bank-info', BankInfoController::class);
    Route::get('//my-bank-info', [BankInfoController::class, 'myBankInfo']);
    Route::post('/transfer', [TransactionController::class, 'transferToBank']);
    Route::get('/get-transfer-history', [TransactionController::class, 'getTransferHistory']);
    Route::get('/get-balance', [TransactionController::class, 'getBalance']);

    Route::get('/get-comission', [PlatformComissionController::class, 'getComission']);
    Route::post('/change-comission', [PlatformComissionController::class, 'store']);
});
