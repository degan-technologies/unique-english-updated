<?php
use App\Http\Controllers\Auth\SocialController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Bank\BankInfoController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\Book\BookVideoController;
use App\Http\Controllers\Book\orderdController;
use App\Http\Controllers\Course\CourseContentController;
use App\Http\Controllers\Course\CourseModuleController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\FeedBackController; 
use App\Http\Controllers\Quiz\QuizController;
use App\Http\Controllers\Quiz\QMetaDataController;
use App\Http\Controllers\Quiz\ResultController;
use App\Http\Controllers\Quiz\QASectionController; 
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
use App\Http\Controllers\JitsiController;
use App\Http\Controllers\Live\AttendanceController;
use App\Http\Controllers\Live\LiveController;
use App\Http\Controllers\Quiz\AnswerController;
use App\Http\Controllers\Logo\LogoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Notifications\EmailNotificationController;
use App\Http\Controllers\System\HeroController;
use App\Http\Middleware\EnsureSignature;

Route::get('/all-couses', [CourseController::class, 'allCourses']);
Route::get('/all-books', [BookController::class, 'allBooks']);
Route::resource('test', TestController::class);
Route::post('/verify-otp', [UserController::class, 'verifyEmailOTP']);
Route::post('/resend-otp', [UserController::class, 'resendOTP']);

Route::get('/hero-section', [HeroController::class, 'index']);
Route::get('/get-plans',[ PlanController::class, 'index']);
Route::get('/courses/stream/video/{filename}', [CourseVideoController::class, 'stream']); 
Route::get('/books/stream/video/{filename}', [BookVideoController::class, 'stream']);
  
Route::get('/stream/video/{filename}', [CourseContentVideoController::class, 'stream'])
    ->name('stream.video')
    ->middleware(EnsureSignature::class);
 
Route::middleware('auth:api')
->group(function () { 
        Route::post('/coursecontent/stream/pdf-stream/{filename}', [BookVideoController::class, 'contentPdfStream'])
            ->name('stream.pdf')
            ->middleware(EnsureSignature::class);
 
        Route::post('/book/pdf-stream/{filename}', [BookVideoController::class, 'bookPdfStream'])
        ->name('book.pdf')
        ->middleware(EnsureSignature::class);

        Route::post('/log-out', [AuthController::class, 'logout']);
        Route::post('/add-instructor', [UserController::class, 'addInstructor']);
        Route::post('/add-student', [UserController::class, 'addStudent']);
        Route::delete('/delete-instructor/{id}', [UserController::class, 'destroy']);
        Route::post('/update-profile', [UserController::class, 'profileUpdate']);
        Route::post('/password-reset', [UserController::class, 'passwordReset']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/get-user/statistics', [UserController::class, 'getUserStatistics']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::resource('/delete-instructor', UserController::class );
        Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete']);
        Route::post('/profile-image/update', [UserController::class, 'profileImageUpdate']);
        Route::post('/remove-image', [UserController::class, 'profileImageRemove']);

        Route::get('/show-course/{slug}', [CourseController::class, 'showCourse']);
        Route::get('/get-course-modules/{slug}', [CourseModuleController::class, 'getCourseModules']);
        Route::get('/get-course-qa/{slug}', [CourseModuleController::class, 'getCourseQandA']);
        Route::get('/get-book/{slug}', [BookController::class, 'getBook']);
        Route::get('/book-pdf/{filename}',[BookController::class, 'streamPdf']);

        Route::post('/coursecontent/progress', [CourseContentProgressController::class, 'store']);
        Route::get('/coursecontent/progress', [CourseContentProgressController::class, 'index']);
        Route::get('/contniue/progress/{slug}', [CourseContentProgressController::class, 'currentProgress']);
        Route::get('/coursecontent/progress/{courseContentId}', [CourseContentProgressController::class, 'show']);
        Route::get('/completed-progress/{courseContentId}', [CourseContentProgressController::class, 'completeProgress']);

        Route::post('/answer/quiz', [ResultController::class, 'answerQuiz']);
        Route::get('/check/answer/{qmId}', [QMetaDataController::class, 'checkAnswer']);
 
        Route::get('/get-rooms', [LiveController::class, 'getAllRooms']);
        Route::get('/get-my-rooms', [LiveController::class, 'getMyRooms']);
        Route::post('/rooms', [LiveController::class, 'store']);
        Route::put('/rooms/{id}', [LiveController::class, 'update']);
        Route::post('/assign-class/{id}', [LiveController::class, 'assignClass']);
        Route::post('/assign-private-instructor/{id}', [LiveController::class, 'assignPrivateInstructor']);

        Route::post('/assign-instructor/{id}', [LiveController::class, 'AssignInstructors']);
        Route::get('/my-instructors', [LiveController::class, 'getMyInstructors']); 
        Route::post('/courses-status/{id}', [CourseController::class, 'updateStatus']);

        Route::post('/update-visiter-attendance/{scheduleId}', [AttendanceController::class, 'updateVisiterAttendance']);
        Route::post('/update-instractor-attendance/{scheduleId}', [AttendanceController::class, 'updateInstractorAttendance']);
        Route::post('/store-instractor-attendance/{scheduleId}', [AttendanceController::class, 'storeInstractorAttendance']);

        Route::post('/update-student-attendance/{scheduleId}', [AttendanceController::class, 'updateStudentAttendance']);
        Route::post('/store-student-attendance/{scheduleId}', [AttendanceController::class, 'storeStudentAttendance']);

        Route::get('/get-instractor-attendance', [AttendanceController::class, 'instractorAttendance']);
        Route::get('/detail-instractor-attendance/{id}', [AttendanceController::class, 'detailInstractorAttendance']);
});

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/current', [AuthController::class, 'currentUser']);       
        Route::resource('quize',QuizController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('QMetaData', QMetaDataController::class);
        Route::get('/get-student-module-exam', [QMetaDataController::class,'fetchStudentExam']); 
        Route::get('/get-module-quizes', [QMetaDataController::class,'fetchInstructorExam']); 
        Route::resource('tests', TestController::class);
        Route::resource('/results', ResultController::class);
        Route::resource('plans', PlanController::class);
        Route::resource('QASection', QASectionController::class);
        Route::resource('answers', AnswerController::class);
        Route::get('/my-schedule', [ScheduleController::class, 'getMySchedules']);  
        Route::get('/my-participants', [LiveController::class, 'getParticipants']);   
        Route::get('/private-participants', [LiveController::class, 'getPrivateParticipants']);   
        Route::get('/student-schedule', [ScheduleController::class, 'getStudentSchedules']); 
        Route::get('/my-get-students', [LiveController::class, 'getMyStudents']);
        Route::get('/my-private-students-schedule', [LiveController::class, 'getMyPrivateStudentsAndSchedules']); 
        
        Route::get('get-my-plans', [PlanController::class, 'getMyPlans']);
         Route::get('/private-student-schedule', [ScheduleController::class, 'getStudentPrivateSchedules']); 
        
        Route::post('update-private-schedules/{id}', [ScheduleController::class, 'updatePrivateSchedule']);
        Route::post('add-private-schedules', [ScheduleController::class, 'addPrivateSchedule']);
        Route::get('/today-schedules', [ScheduleController::class, 'todaySchedules']);   
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
        Route::get('/my-courses', [CourseController::class, 'myCourse']);
        Route::get('/my-books', [BookController::class, 'myBooks']);
        Route::get('/module-contents/{moduleId}', [CourseContentController::class, 'getModuleContents']);
        Route::post('/upload-intro-video', [CourseController::class, 'uploadIntroVideo']);
        Route::post('/upload-thumbnail', [CourseController::class, 'uploadThumbnail']);
        Route::post('/upload-lesson-file', [CourseContentController::class, 'uploadLessonFile']);
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
    
Route::get('/feedbacks/course/{slug}', [FeedBackController::class, 'getFeedbacksByCourse']);

// SMS endpoints added here 
Route::middleware('auth:api')->group(function () {
        Route::post('/send-sms', [SMSController::class, 'sendSMS']);
        Route::post('/send-bulk-sms', [SMSController::class, 'sendBulkSMS']);
        Route::post('/send-otp', [SMSController::class, 'sendOTP']);
        Route::post('/verify-otp-sms', [SMSController::class, 'verifyOTP']);

        Route::post('/email-notification', [EmailNotificationController::class, 'sendEmailNotification']);
        Route::get('/created-announcements', [EmailNotificationController::class, 'getAnnouncements']);
        });

Route::middleware('auth:api')
    ->prefix('books')
    ->group(function(){
        Route::resource('/books', BookController::class);
        Route::resource('/order-books', orderdController::class);
        Route::post('/update-books/{id}', [BookController::class, 'update']);

        Route::post('/upload-pdf', [BookController::class, 'uploadPdf']);
        Route::post('/upload-thumbnail', [BookController::class, 'uploadCoverImage']);
        Route::post('/upload-intro-video', [BookController::class, 'uploadIntroVideo']);
    });

Route::middleware('auth:api')
->group(function () {
    Route::post('/initiate-payment', [TransactionController::class, 'initiatePayment']);
    Route::get('/transaction', [TransactionController::class, 'transactions']);
    Route::get('/top-sellers', [TransactionController::class, 'getTopSeller']);
    Route::get('/top-sold-books', [TransactionController::class, 'topSoldBooks']);
    Route::get('/top-sold-courses', [TransactionController::class, 'topSoldCourses']);
    Route::get('/system-transaction', [TransactionController::class, 'systemTransaction']);
    Route::get('/transaction-info/{txRef}', [TransactionController::class, 'transactionInvoice']);
    Route::get('/refend-transaction/{txRef}', [TransactionController::class, 'refundTransaction']);
    Route::get('/bank-lists', [TransactionController::class, 'getBankList']);
    Route::resource('/bank-info', BankInfoController::class);
    Route::get('/my-bank-info', [BankInfoController::class, 'myBankInfo']);
    Route::post('/withdrawals', [TransactionController::class, 'transferToBank']); 
    Route::get('/get-transfer-history', [TransactionController::class, 'getTransferHistory']);
    Route::get('/get-balance', [TransactionController::class, 'getBalance']);
    Route::post('/chapa/transfer/approval', [TransactionController::class, 'handleTransferApproval'])
    ->name('chapa.transfer.callback');
    
    Route::get('/get-comission', [PlatformComissionController::class, 'getComission']);
    Route::post('/change-comission', [PlatformComissionController::class, 'store']);
}); 

Route::post('/chapa/withdrawal-approval', [TransactionController::class, 'handleWithdrawalApproval'])->name('transfer.approval'); 
Route::post('/chapa/approve-transfer', [TransactionController::class, 'handleTransferApproval']); 

Route::middleware(['auth:api'])->group(function () {

    // Only system admins should be able to store new logos
    Route::post('/logos', [LogoController::class, 'store']);

    // All authenticated users can view logos
    Route::get('/logos', [LogoController::class, 'index']);
    Route::get('/logos/{logo}', [LogoController::class, 'show']);
    Route::get('/get-notifications', [NotificationController::class, 'index']); 
    Route::post('/read-notification/{id}', [NotificationController::class, 'markAsRead']); 

    //system information
    Route::post('/hero-section', [HeroController::class, 'stroreOrUpdate']);
    Route::get('/activity-logs', [AuthController::class, 'getActivityLogs']);
    
});

Route::post('/jitsi/token', [JitsiController::class, 'generateToken']);
 