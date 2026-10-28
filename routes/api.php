<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\SidebarController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\FeesController;
use App\Http\Controllers\Api\ClassController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::match(['get','post'],'login', [LoginController::class, 'login']);
Route::match(['get','post'],'forgotPassword', [LoginController::class,'forgotPassword']);
Route::match(['get','post'],'teacherLogin', [LoginController::class, 'teacherLogin']);
Route::match(['get','post'],'studentLogin', [LoginController::class, 'studentLogin']);
Route::match(['get','post'],'libraryHostelDetails', [LoginController::class, 'libraryHostelDetails']);
Route::match(['get','post'],'appData', [LoginController::class, 'appDataApi']);



Route::match(['get','post'],'sidebarPermission', [SidebarController::class, 'sidebarPermission']);
Route::match(['get','post'],'studentAdd', [AdmissionController::class, 'studentAdd']);
Route::match(['get','post'],'studentList', [AdmissionController::class, 'studentList']);
Route::match(['get','post'],'studentDelete', [AdmissionController::class, 'studentDelete']);
Route::match(['get', 'post'], 'studentEdit/{id}', 'Api\AdmissionController@studentEdit');
Route::match(['get', 'post'], 'getProfile', 'Api\ProfileController@getProfile');
Route::match(['get', 'post'], 'profileEdit', 'Api\ProfileController@profileEdit');
Route::match(['get', 'post'], 'getAllCount', 'Api\ProfileController@getAllCount');
Route::match(['get', 'post'], 'schoolDeskView', 'Api\ProfileController@schoolDeskView');
Route::match(['get', 'post'], 'schoolPrayer', 'Api\ProfileController@schoolPrayer');
Route::match(['get', 'post'], 'schoolRules', 'Api\ProfileController@schoolRules');
Route::match(['get', 'post'], 'schoolGallery', 'Api\ProfileController@schoolGallery');
Route::match(['get', 'post'], 'gatePassView', 'Api\ProfileController@gatePassView');
Route::match(['get', 'post'], 'noticeBoard', 'Api\ProfileController@noticeBoard');
Route::match(['get', 'post'], 'complainBox', 'Api\ProfileController@complainBox');
Route::match(['get', 'post'], 'addComplain', 'Api\ProfileController@addComplain');
Route::match(['get', 'post'], 'leaveBox', 'Api\ProfileController@leaveBox');
Route::match(['get', 'post'], 'addLeave', 'Api\ProfileController@addLeave');
Route::match(['get', 'post'], 'getCountry', 'Api\DependentController@getCountry');
Route::match(['get', 'post'], 'getState/{county_id}', 'Api\DependentController@getState');
Route::match(['get', 'post'], 'getCity/{state_id}', 'Api\DependentController@getCity');

Route::match(['get', 'post'], 'teacherList', 'Api\TeacherController@teacherList');
Route::match(['get', 'post'], 'teacherDelete', 'Api\TeacherController@teacherDelete');
Route::match(['get', 'post'], 'getAllTeacherCount', 'Api\TeacherController@getAllTeacherCount');
Route::match(['get', 'post'], 'teacherEdit/{id}', 'Api\TeacherController@teacherEdit');
Route::match(['get', 'post'], 'teacherAdd', 'Api\TeacherController@teacherAdd');


Route::match(['get', 'post'], 'attendanceview', 'Api\StaffAttendanceController@view');
Route::match(['get', 'post'], 'attendanceAdd', 'Api\StaffAttendanceController@add');
Route::match(['get', 'post'], 'monthWiseStaffAttendance', 'Api\StaffAttendanceController@monthWise');
Route::match(['get', 'post'], 'subSidebar', 'Api\SubSidebarController@subSidebar');
Route::match(['get', 'post'], 'getSubjectList', 'Api\QuestionController@getSubjectList');
Route::match(['get', 'post'], 'getQuestionList', 'Api\QuestionController@getQuestionList');
Route::match(['get', 'post'], 'getQuestionEdit/{id}', 'Api\QuestionController@getQuestionEdit');

//class controller
Route::match(['get','post'],'className', [ClassController::class, 'className']);

//Exmination
Route::match(['get', 'post'], 'addExam', 'Api\ExaminationController@addExam');
Route::match(['get', 'post'], 'viewExam', 'Api\ExaminationController@viewExam');

//fees controller
Route::match(['get', 'post'], 'counterAdd', 'Api\FeesController@counterAdd');
Route::match(['get', 'post'], 'counterAuth', 'Api\FeesController@counterAuth');
Route::match(['get', 'post'], 'counterList', 'Api\FeesController@counterList');
Route::match(['get', 'post'], 'counterDelete', 'Api\FeesController@counterDelete');

Route::match(['get', 'post'], 'counterEdit/{id}', 'Api\FeesController@counterEdit');
Route::match(['get', 'post'], 'paymentModes', 'Api\FeesController@paymentModes');
Route::match(['get', 'post'], 'feesDetails', 'Api\FeesController@feesDetails');
Route::match(['get', 'post'], 'studentFeesDetails', 'Api\FeesController@studentFeesDetails');

Route::match(['get', 'post'], 'school_fees_print/{id}', 'Api\FeesController@schoolFeesPrint');
Route::match(['get', 'post'], 'library_fees_print/{invoice_no}/{id}', 'Api\FeesController@libraryFeesPrint');

//Exmination
Route::match(['get', 'post'], 'examTerminal', 'Api\ExaminationController@examTerminal');
Route::match(['get', 'post'], 'startExam', 'Api\ExaminationController@startExam');
Route::match(['get', 'post'], 'resultExam', 'Api\ExaminationController@resultExam');
Route::match(['get', 'post'], 'getExamResult', 'Api\ExaminationController@getExamResult');
Route::match(['get', 'post'], 'analysisPanel', 'Api\ExaminationController@analysisPanel');
Route::match(['get', 'post'], 'examAnalysis', 'Api\ExaminationController@examAnalysis');

//Student Attendance View

Route::match(['get', 'post'], 'studentAttendanceView', 'Api\StudentAttendanceController@view');
Route::match(['get', 'post'], 'studentAttendanceAdd', 'Api\StudentAttendanceController@add');
Route::match(['get', 'post'], 'monthWiseStudentAttendance', 'Api\StudentAttendanceController@monthWise');


//subject Controller
Route::match(['get', 'post'], 'studentSubjectList', 'Api\SubjectController@studentSubjectList');

//certicates Controller
Route::match(['get', 'post'], 'getAllCertificates', 'Api\CertificateController@getAllCertificates');
Route::match(['get', 'post'], 'cc_print/{id}', 'Api\CertificateController@characterCertificate');
Route::match(['get', 'post'], 'evente_print/{id}', 'Api\CertificateController@eventCertificate');
Route::match(['get', 'post'], 'sport_print/{id}', 'Api\CertificateController@sportCertificate');
Route::match(['get', 'post'], 'tc_print/{id}', 'Api\CertificateController@tcCertificate');


//download Controller
Route::match(['get', 'post'], 'getDownloadCenter', 'Api\DownloadController@getDownloadCenter');
Route::match(['get', 'post'], 'getStudyMaterial', 'Api\DownloadController@getStudyMaterial');
//BooksUniform Controller
Route::match(['get', 'post'], 'booksUniformShops', 'Api\BooksUniformController@booksUniformShops');
//Bus Controller
Route::match(['get', 'post'], 'busAssigned', 'Api\BusController@busAssigned');

//Device Token
Route::match(['get', 'post'], 'saveDeviceToken', 'Api\UserController@saveDeviceToken');

