<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
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
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\ActivityFeedController;



Route::post('/login', [AuthController::class, 'login']);

Route::post('/registration', [UserController::class, 'store']);

Route::middleware('auth:api')
    ->group(function () {
        Route::post('/add-instructor', [UserController::class, 'addInstructor']);
        Route::post('/add-student', [UserController::class, 'addStudent']);
        Route::delete('/delete-instructor/{id}', [UserController::class, 'destroy']);
        Route::post('/update-profile', [UserController::class, 'profileUpdate']);
        Route::post('/password-reset', [UserController::class, 'passwordReset']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/users', [UserController::class, 'index']);
        Route::resource('/delete-instructor', UserController::class );
        Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete']);
    });

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/current', [AuthController::class, 'currentUser']);
        Route::resource('live-sessions', LiveSessionController::class);
        Route::resource('live-resources', LiveResourceController::class);
        Route::resource('participants', ParticipantController::class);
        Route::resource('virtual-class-enrollments', VirtualClassEnrollmentController::class);
       
        
        
    Route::resource('quize',QuizController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::resource('QMetaData', QMetaDataController::class);  
    Route::get('/exams', [QMetaDataController::class,'fetchInstructorExam']); 
    Route::resource('tests', TestController::class);
    Route::resource('Results', ResultController::class);
    Route::resource('plans', PlanController::class);
    Route::resource('QASection', QASectionController::class);
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
        Route::get('/{course}/certificate-status', [CourseController::class, 'certificateStatus']);
        Route::post('/course-content-progress/{courseContentId}', [CourseController::class, 'updateProgress']);
        Route::get('/activities', [ActivityFeedController::class, 'index']);

    });

Route::middleware('auth:api')
    ->prefix('feedbacks')
    ->group(function () {
        // Standard resource endpoints
        Route::get('/', [FeedBackController::class, 'index']);
        Route::post('/', [FeedBackController::class, 'store']);
        Route::get('{id}', [FeedBackController::class, 'show']);
        Route::put('{id}', [FeedBackController::class, 'update']);
        Route::delete('{id}', [FeedBackController::class, 'destroy']);

        // Custom endpoints for additional feedback actions
        Route::post('{id}/like', [FeedBackController::class, 'like']);
        Route::post('{id}/dislike', [FeedBackController::class, 'dislike']);
        Route::post('{id}/report', [FeedBackController::class, 'report']);
    });

    // SMS endpoints added here
Route::middleware('auth:api')->group(function () {
    Route::post('/send-sms', [SMSController::class, 'sendSMS']);
    Route::post('/send-bulk-sms', [SMSController::class, 'sendBulkSMS']);
    });
    
Route::middleware('auth:api')
    ->prefix('books')
    ->group(function(){
        Route::resource('/books', BookController::class);
        Route::resource('/order-books', orderdController::class);
    });

Route::post('/initiate-payment', [TransactionController::class, 'initiatePayment'])->middleware('auth:api');

