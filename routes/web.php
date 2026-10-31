<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;
use App\Http\Controllers\PayUMoneyController;
//use Redirect;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::match(['get', 'post'], 'payUSuccess/{id}', 'PayUMoneyController@payUSuccess');
Route::match(['get', 'post'], 'payUCancel/{id}', 'PayUMoneyController@payUCancel');
Route::match(['get', 'post'], 'checkTransactionStatus/{id}', 'PayUMoneyController@checkTransactionStatus');
Route::match(['get', 'post'], 'checkPayUTxnStatus', 'PayUMoneyController@checkPayUTxnStatus');
Route::match(['get', 'post'], 'checkTransactionStatusUniversal/{id}', 'PayUMoneyController@checkTransactionStatusUniversal');
Route::match(['get', 'post'], 'payUSuccessUniversal', 'PayUMoneyController@payUSuccessUniversal');
Route::match(['get', 'post'], 'payUCancelUniversal', 'PayUMoneyController@payUCancelUniversal');

Route::get('/clear-cache', function () {
	Artisan::call('cache:clear');
	Artisan::call('route:clear');
	Artisan::call('view:clear');

	return redirect('/')->with('message', 'Cache clear successfully.');
});

//Queue mail
Route::get('/start-queue-worker', 'Sms_ServiceController@startQueueWorker');

Route::match(['get', 'post'], 'folderCompressor', 'StudentsAdmissionController@folderCompressor');

//Auth 
//Route::match(['get','post'],'attendanceMessageStatus', 'StudentAttendanceController@attendanceMessageStatus');
//Route::get('/our_backup_database', 'Auth\AuthController@our_backup_database');    

Route::get('/access-denied', function () {
    return view('access.denied');
})->name('access.denied');



//Auth 
Route::match(['get','post'],'attendanceSendMassage', 'CronJobController@attendanceSendMassage');
Route::match(['get','post'],'birthday_auto_massage', 'CronJobController@birthday_auto_massage');




Route::match(['get', 'post'], 'cronJobs', 'CronJobController@cronJobs');

Route::get('softwareTokenStatus', function(){ session()->put('softwareTokenStatus',1); });
Route::match(['get', 'post'], 'login', 'Auth\AuthController@getLogin');
Route::match(['get', 'post'], 'forgot_password', 'Auth\AuthController@forgotPassword');
Route::match(['get', 'post'], 'whatsappOtpRequest', 'Auth\AuthController@whatsappOtpRequest');
Route::match(['get', 'post'], 'logout', 'Auth\AuthController@logout');
Route::match(['get', 'post'], 'getregister', 'Auth\AuthController@getregister');
Route::match(['get', 'post'], 'register', 'Auth\AuthController@register');
Route::match(['get', 'post'], 'qr_code', 'EnquiryController@qrCode');

Route::any('newStudentRegistration', 'Auth\AuthController@newStudentRegistration');
Route::match(['get', 'post'], 'storeReceipt/{receipt_no}/{admission_id?}', 'StoreController@storeReceipt');
Route::match(['get', 'post'], 'releaseStaleStoreReservations', 'StoreController@releaseStaleStoreReservations');
Route::match(['get', 'post'], 'feesReceipt/{receipt_no}/{admission_id?}', 'fees\FeesController@feesReceipt');

Route::group(['middleware' => 'islogin'], function () {
Route::match(['get','post'],'qrcode_Dashboard', 'QrCodeAttendanceController@qrcode_Dashboard');
Route::match(['get','post'],'qrcode_user', 'QrCodeAttendanceController@qrcode_user');
Route::match(['get','post'],'qrcode_student', 'QrCodeAttendanceController@qrcode_student');
Route::match(['get','post'],'qrcode_attendance', 'QrCodeAttendanceController@qrcode_attendance');
Route::match(['get','post'],'qrcode_attendance_save', 'QrCodeAttendanceController@qrcode_attendance_save');
Route::match(['get','post'],'user_attendence_qr_download', 'QrCodeAttendanceController@user_attendence_qr_download');
Route::match(['get','post'],'student_attendence_qr_download', 'QrCodeAttendanceController@student_attendence_qr_download');
    
Route::match(['get', 'post'], 'setWhatsappPermission', 'WhatsappController@setWhatsappPermission');
Route::match(['get', 'post'], 'whatsapp_api_response', 'WhatsappController@whatsappApiResponse');
Route::match(['get', 'post'], 'whatsappSendFeesRemainder', 'WhatsappController@whatsappSendFeesRemainder');
Route::match(['get', 'post'], 'messangerButtons', 'WhatsappController@messangerButtons');
Route::match(['get', 'post'], 'sendWhatsapp', 'WhatsappController@sendWhatsapp');
Route::match(['get', 'post'], 'todayWhatsappMessages', 'WhatsappController@todayWhatsappMessages');
Route::match(['get', 'post'], 'validateOtpWhatsapp', 'WhatsappController@validateOtpWhatsapp');
Route::match(['get', 'post'], 'WhatsAppMessageHistory', 'WhatsappController@WhatsAppMessageHistory');
Route::match(['get', 'post'], 'firebase-notification', 'NotificationController@notification');

// Chat Panel
Route::match(['get', 'post'], 'chat/test', 'chat\ChatController@chatTest');
Route::match(['get', 'post'], 'chat/compose', 'chat\ChatController@compose');
Route::match(['get', 'post'], 'search/chat/user', 'chat\ChatController@searchChat');
Route::match(['get', 'post'], 'chat/user/show/click', 'chat\ChatController@chatUserShowClick');
Route::match(['get', 'post'], 'chat/last/message', 'chat\ChatController@chatLastMessage');
Route::match(['get', 'post'], 'send/chat', 'chat\ChatController@sendChat');
Route::match(['get', 'post'], 'download/chat/document', 'chat\ChatController@downloadChatDocument');
Route::match(['get', 'post'], 'delete/chat/message', 'chat\ChatController@deleteChatMessage');
Route::match(['get', 'post'], 'chat/message/seen', 'chat\ChatController@chatMessageSeen');

//Settings
Route::match(['get', 'post'], 'addSetting', 'SettingsController@addSetting');
Route::match(['get', 'post'], 'viewSetting', 'SettingsController@viewSetting');
Route::match(['get', 'post'], 'editSetting/{id}', 'SettingsController@editSetting');
Route::match(['get', 'post'], 'deleteSetting', 'SettingsController@deleteSetting');
Route::match(['get', 'post'], 'addVillageList', 'SettingsController@addVillageList');
Route::match(['get', 'post'], 'deleteVillageList', 'SettingsController@deleteVillageList');
//User
Route::match(['get', 'post'], 'addUser', 'UserController@addUser'); 
Route::match(['get', 'post'], 'viewUser', 'UserController@viewUser');
Route::match(['get', 'post'], 'editUser/{id}', 'UserController@editUser');
Route::match(['get', 'post'], 'deleteUser', 'UserController@deleteUser');
Route::match(['get', 'post'], 'userStatus', 'UserController@userStatus');
Route::match(['get', 'post'], 'user/Attendance/view', 'UserController@userAttendanceView');
Route::match(['get', 'post'], 'user_side_per', 'UserController@userSidePer');
Route::post('quick_add_role', 'UserController@quickAddRole');

//ReportController

 Route::match(['get', 'post'], 'reporting_dashboard', 'Report\ReportingDashboard@reportingDashboard');
 Route::match(['get', 'post'], 'fees_reporting', 'Report\ReportingDashboard@feesReporting');
 Route::match(['get', 'post'], 'reporting', 'Report\ReportingDashboard@reporting');
 Route::match(['get', 'post'], 'hostel_reporting', 'Report\HostelControllers@hostel_reporting');
 Route::match(['get', 'post'], 'hostel_pending_fees', 'Report\HostelControllers@hostel_pending_fees');

 
//EnquiryController 
    Route::match(['get', 'post'], 'studentsDashboard', 'EnquiryController@studentsDashboard');
    Route::match(['get', 'post'], 'enquiryAdd', 'EnquiryController@enquiryAdd');
    Route::match(['get', 'post'], 'enquiryView', 'EnquiryController@enquiryView');
    // Route::match(['get', 'post'], 'enquiry_status_update', 'EnquiryController@enquiryStatusUpdate');
    Route::match(['get', 'post'], 'enquiryEdit/{id}', 'EnquiryController@enquiryEdit');
    Route::match(['get', 'post'], 'enquiryDelete', 'EnquiryController@enquiryDelete');
    Route::get('registrationPrint/{id}', 'EnquiryController@registrationPrint');
    Route::match(['get', 'post'], 'studentRegistrationDetail/{id}', 'EnquiryController@studentRegistrationDetail');
    Route::match(['get', 'post'], 'enquiryRemarkAdd', 'EnquiryController@enquiryRemarkAdd');
    Route::match(['get', 'post'], 'enquiryRemarkEdit', 'EnquiryController@enquiryRemarkEdit');
    Route::match(['get', 'post'], 'action_add', 'EnquiryController@action_add');
    Route::match(['get', 'post'], 'student_action_index', 'EnquiryController@student_action_index');
    Route::get('personal_details', 'EnquiryController@personal_details');
    Route::post('student_id_print_multiple', 'EnquiryController@studentIdPrintMultiple');
    Route::match(['get', 'post'], 'class_type_search', 'EnquiryController@class_type_search');
    Route::match(['get', 'post'], 'getClassesByCourse', 'StudentsAdmissionController@getClassesByCourse');
    Route::match(['get', 'post'], 'student_details', 'EnquiryController@studentDetails');
    Route::match(['get', 'post'], 'student/promote_add', 'EnquiryController@promoteAdd');
    Route::match(['get', 'post'], 'getPromotionProgressionInfo', 'EnquiryController@getPromotionProgressionInfo');
   
    Route::match(['get', 'post'], 'studentsPromoteAdd', 'EnquiryController@studentsPromoteAdd');
    Route::match(['get', 'post'], 'enquiry_qr_generate', 'EnquiryController@enquiry_qr_generate');
Route::get('enquiry_qr_generate', function () {
  
    \QrCode::size(500)
            ->format('png')
            ->generate('ItSolutionStuff.com');
    
  return view('qrCode');
    
});
// Agents Management
Route::match(['get', 'post'], 'agents', 'AgentController@index');
Route::match(['get', 'post'], 'agents/store', 'AgentController@store');
Route::post('agents/quick-add', 'AgentController@quickAddAgent');
Route::match(['get', 'post'], 'agents/delete', 'AgentController@delete');
Route::match(['get', 'post'], 'agents/status', 'AgentController@status');

//students Admission
// Route::match(['get', 'post'], 'unique_system_id', 'StudentsAdmissionController@unique_system_id');
Route::match(['get', 'post'], 'bulkIdPrint', 'StudentsAdmissionController@bulkIdPrint');
Route::match(['get', 'post'], 'studentUserNameCreate', 'StudentsAdmissionController@studentUserNameCreate');
Route::match(['get', 'post'], 'admissionAdd', 'StudentsAdmissionController@admissionAdd');
Route::match(['get', 'post'], 'getNextAdmissionNo', 'StudentsAdmissionController@getNextAdmissionNo');
Route::match(['get', 'post'], 'getStreamSubjects', 'StudentsAdmissionController@getStreamSubjects');
Route::match(['get', 'post'], 'admissionView', 'StudentsAdmissionController@admissionView');
Route::match(['get', 'post'], 'saveAdmissionDatatableFields', 'StudentsAdmissionController@saveAdmissionDatatableFields');
Route::match(['get', 'post'], 'admissionEdit/{id}', 'StudentsAdmissionController@admissionEdit');
Route::match(['get', 'post'], 'admissionDelete', 'StudentsAdmissionController@admissionDelete');
Route::match(['get', 'post'], 'admissionStudentSearch', 'StudentsAdmissionController@admissionStudentSearch');
Route::match(['get', 'post'], 'admissionStudentOnClick', 'StudentsAdmissionController@admissionStudentOnClick');
Route::get('admissionStudentPrint/{id}', 'StudentsAdmissionController@admissionStudentPrint');
Route::get('admissionStudentIdPrint/{id}', 'StudentsAdmissionController@admissionStudentIdPrint');
Route::get('studentId_generator/{id}', 'StudentsAdmissionController@studentIdGenerator');
Route::match(['get', 'post'], 'studentDetail/{id}', 'StudentsAdmissionController@studentDetail');
Route::match(['get', 'post'], 'studentExcelAdd', 'StudentsAdmissionController@studentExcelAdd');
Route::match(['get', 'post'], 'student_profile/{id}', 'StudentsAdmissionController@studentProfile');
//Route::match(['get', 'post'], 'show_fees_detail', 'StudentsAdmissionController@showFeesDetail');
Route::match(['get', 'post'], 'multiAdmissionEdit', 'StudentsAdmissionController@multiAdmissionEdit');

Route::match(['get', 'post'], 'verify_admission', 'StudentsAdmissionController@verify_admission');

Route::match(['get', 'post'], 'updateByExcel', 'StudentsAdmissionController@updateByExcel');
Route::match(['get', 'post'], 'studentBulkImageUpload', 'StudentsAdmissionController@studentBulkImageUpload');
Route::match(['get', 'post'], 'stream_update', 'StudentsAdmissionController@streamUpdate');
Route::match(['get', 'post'], 'stream_update_save', 'StudentsAdmissionController@streamUpdateSave');
Route::match(['get', 'post'], 'stream_remove/{admission_id}/{subject_id}', 'StudentsAdmissionController@streamRemove');
Route::match(['get', 'post'], 'category_wise_report', 'StudentsAdmissionController@category_wise_report');
Route::match(['get', 'post'], 'imageDelete', 'StudentsAdmissionController@imageDelete');
Route::match(['get', 'post'], 'login_credential_reports', 'StudentsAdmissionController@login_credential_reports');
Route::match(['get', 'post'], 'updateSingleStudentCredential', 'StudentsAdmissionController@updateSingleStudentCredential');

 
//student attendance
Route::match(['get', 'post'], 'studentsAttendanceAdd', 'StudentAttendanceController@add');
Route::match(['get', 'post'], 'studentsAttendancdDelete', 'StudentAttendanceController@attendancedelete');
Route::match(['get', 'post'], 'getAttendanceDates', 'StudentAttendanceController@getAttendanceDates');
Route::match(['get', 'post'], 'sundayAutoSubmitAttendance', 'StudentAttendanceController@sundayAutoSubmitAttendance');  //Need to start cron...
Route::match(['get', 'post'], 'autoStudentAttendance', 'StudentAttendanceController@autoStudentAttendance');
Route::match(['get', 'post'], 'studentsAttendanceView', 'StudentAttendanceController@view');
Route::match(['get', 'post'], 'studentsAttendanceViewTable', 'StudentAttendanceController@viewTable');
Route::match(['get', 'post'], 'studentPanelAttendanceView', 'StudentAttendanceController@studentPanelAttendanceView');
Route::match(['get', 'post'], 'SearchValueAtten', 'StudentAttendanceController@SearchValueAtten');

//Invantory Start...//

	Route::match(['get', 'post'], 'invantory_dashboard', 'inventory\InvantoryController@invantoryDashboard');
	Route::match(['get', 'post'], 'invantory_view', 'inventory\InvantoryController@viewInvantory');
	Route::match(['get', 'post'], 'invantory_add', 'inventory\InvantoryController@addInvantory');
	Route::match(['get', 'post'], 'invantory_edit/{id}', 'inventory\InvantoryController@editInvantory');
	Route::match(['get', 'post'], 'invantory_delete', 'inventory\InvantoryController@deleteInvantory');
	Route::match(['get', 'post'], 'delete_inventory_detail', 'inventory\InvantoryController@deleteInvantoryDetail');


	Route::match(['get', 'post'], 'invantory_item_add', 'inventory\InvantoryController@addInvantoryItem');
	Route::match(['get', 'post'], 'invantory_item_edit/{id}', 'inventory\InvantoryController@editInvantoryItem');
	Route::match(['get', 'post'], 'invantory_item_delete', 'inventory\InvantoryController@deleteInvantoryItem');
	Route::match(['get', 'post'], 'getAutoCompleteInvantoryItem', 'inventory\InvantoryController@getAutoCompleteInvantoryItem');
    
    Route::match(['get', 'post'], 'sale_inventory_view', 'inventory\SalesInvantoryController@SalesViewInvantory');
	Route::match(['get', 'post'], 'sales_invantory_add', 'inventory\SalesInvantoryController@SalesAddInvantory');
	Route::match(['get', 'post'], 'sales_invantory_edit/{id}', 'inventory\SalesInvantoryController@SalesEditInvantory');
	Route::match(['get', 'post'], 'sales_invantory_delete', 'inventory\SalesInvantoryController@SalesDeleteInvantory');
	Route::match(['get', 'post'], 'getAutoCompleteStudent', 'inventory\SalesInvantoryController@getAutoCompleteStudent');
	Route::match(['get', 'post'], 'getInvantoryItemQtyCheck', 'inventory\SalesInvantoryController@getInvantoryItemQtyCheck');
	Route::match(['get', 'post'], 'sale_inventory_print/{id}', 'inventory\SalesInvantoryController@sale_inventory_print');
	Route::match(['get', 'post'], 'get-student-data', 'inventory\SalesInvantoryController@getStudentData');

	//Invantory End...//
	
	
// Expenses Start... //
Route::match(['get', 'post'], 'expenseAdd', 'ExpenseController@expenseAdd');
Route::match(['get', 'post'], 'expenseView', 'ExpenseController@expenseView');
Route::match(['get', 'post'], 'expenseEdit/{id}', 'ExpenseController@expenseEdit');
Route::match(['get', 'post'], 'expenseDelete', 'ExpenseController@expenseDelete');
Route::match(['get', 'post'], 'expensePrint/{id}', 'ExpenseController@expensePrint');
// Expenses End... //

//Feed Group
Route::match(['get', 'post'], 'feesGroup', 'fees\FeesController@feesGroup');
Route::match(['get', 'post'], 'saveSemesterFeeHead', 'fees\FeesController@saveSemesterFeeHead');
Route::match(['get', 'post'], 'updateSemesterFeeHead', 'fees\FeesController@updateSemesterFeeHead');
Route::match(['get', 'post'], 'deleteSemesterFeeHead', 'fees\FeesController@deleteSemesterFeeHead');
Route::match(['get', 'post'], 'copySemesterFees', 'fees\FeesController@copySemesterFees');
Route::match(['get', 'post'], 'saveMasterFeeHead', 'fees\FeesController@saveMasterFeeHead');
Route::match(['get', 'post'], 'deleteMasterFeeHead', 'fees\FeesController@deleteMasterFeeHead');
Route::match(['get', 'post'], 'getSemesterFeeStatus', 'fees\FeesController@getSemesterFeeStatus');
Route::match(['get', 'post'], 'getFeesGroup', 'fees\FeesController@getFeesGroup');
Route::match(['get', 'post'], 'getFeesStructureSessionSummary', 'fees\FeesController@getFeesStructureSessionSummary');
Route::match(['get', 'post'], 'cloneFeesStructure', 'fees\FeesController@cloneFeesStructure');
Route::match(['get', 'post'], 'feesGroupEdit/{id}', 'fees\FeesController@feesGroupEdit');
Route::match(['get', 'post'], 'feesGroupDelete', 'fees\FeesController@feesGroupDelete');
Route::match(['get', 'post'], 'assignFeeHeadToClasses', 'fees\FeesController@assignFeeHeadToClasses');
Route::match(['get', 'post'], 'createFeesInstallment', 'fees\FeesController@createFeesInstallment');
Route::match(['get', 'post'], 'createFeesInstallmentClassWise', 'fees\FeesController@createFeesInstallmentClassWise');
Route::match(['get', 'post'], 'assignFeesMultipleStudents', 'fees\FeesController@assignFeesMultipleStudents');
Route::match(['get', 'post'], 'fees_assign', 'fees\FeesController@feesAssign');
Route::match(['get', 'post'], 'toggleStudentFeeHead', 'fees\FeesController@toggleStudentFeeHead');
Route::match(['get', 'post'], 'bulkAssignCourseFees', 'fees\FeesController@bulkAssignCourseFees');
Route::match(['get', 'post'], 'clearStudentFeeHeads', 'fees\FeesController@clearStudentFeeHeads');
Route::match(['get', 'post'], 'getStudentFeeDetailsModal', 'fees\FeesController@getStudentFeeDetailsModal');
Route::match(['get', 'post'], 'updateStudentFeeDetailInline', 'fees\FeesController@updateStudentFeeDetailInline');
Route::match(['get', 'post'], 'updateStudentFeeDetailsBatch', 'fees\FeesController@updateStudentFeeDetailsBatch');
Route::match(['get', 'post'], 'updateStudentFeeRemark', 'fees\FeesController@updateStudentFeeRemark');
Route::match(['get', 'post'], 'checkStudentCollectedFees', 'fees\FeesController@checkStudentCollectedFees');
Route::post('getStudentOverallLedger', 'fees\FeesController@getStudentOverallLedger');
Route::match(['get', 'post'], 'getMasterData', 'fees\FeesController@getMasterData');
Route::match(['get', 'post'], 'feesModification', 'fees\FeesController@feesModification');
Route::match(['get', 'post'], 'deleteAssignedFees', 'fees\FeesController@deleteAssignedFees');
Route::match(['get', 'post'], 'updateAssignedFees', 'fees\FeesController@updateAssignedFees');
Route::match(['get', 'post'], 'getStudentsList', 'fees\FeesController@getStudentsList');
Route::match(['get', 'post'], 'ca_report', 'fees\FeesController@caReport'); 
Route::match(['get', 'post'], 'temp_fees_collect', 'fees\FeesController@temp_fees_collect'); 

//Fees Group End..//
 

//Fees Master
Route::match(['get', 'post'], 'feesMasterAdd', function() {
    return redirect('feesGroup');
});
Route::match(['get', 'post'], 'feesMasterEdit/{id}', 'fees\FeesMasterController@feesMasterEdit');
Route::match(['get', 'post'], 'feesMasterDelete', 'fees\FeesMasterController@feesMasterDelete');
Route::match(['get', 'post'], 'mesterClassAmt', 'fees\FeesMasterController@mesterClassAmt');

Route::match(['get', 'post'], 'specialFeesMaster', 'fees\FeesMasterController@specialFeesMaster');
Route::match(['get', 'post'], 'specialFeesMastercourseData', 'fees\FeesMasterController@specialFeesMastercourseData');
//RefundFees
Route::match(['get', 'post'], 'refund_fees', 'fees\RefundFeesController@RefundFees');
Route::match(['get', 'post'], 'AddrefundFees', 'fees\RefundFeesController@AddRefundFees');
Route::match(['get', 'post'], 'fees_refund_view', 'fees\RefundFeesController@feesRefundView');
Route::match(['get', 'post'], 'fees_refund_save', 'fees\RefundFeesController@feesRefundSave');

//Emi
Route::match(['get', 'post'], 'emi_add', 'fees\EmiController@emiAdd');
Route::match(['get', 'post'], 'emi_edit/{id}', 'fees\EmiController@emiEdit');
Route::match(['get', 'post'], 'emi_delete', 'fees\EmiController@emiDelete');

//Fees counter
Route::match(['get', 'post'], 'feesCounterAdd', 'fees\FeesCounterController@feesCounter');
Route::match(['get', 'post'], 'check_authentication', 'fees\FeesCounterController@checkAuthentication');
Route::match(['get', 'post'], 'feesCounterEdit/{id}', 'fees\FeesCounterController@feesCounterEdit');
Route::match(['get', 'post'], 'feesCounterDelete', 'fees\FeesCounterController@feesCounterDelete');
Route::match(['get', 'post'], 'feesCounterView', 'fees\FeesCounterController@feesCounterview');

//certificate_dashboard
Route::match(['get', 'post'], 'certificate_dashboard', 'CertificateController@certificate_dashboard');
Route::match(['get', 'post'], 'certificate_student_dashboard', 'CertificateController@certificate_student_dashboard');
Route::match(['get', 'post'], 'CC_Form_std_data', 'CertificateController@CCFormStdData');
Route::match(['get', 'post'], 'cc/form/add', 'CertificateController@add');
Route::match(['get', 'post'], 'cc/form/index', 'CertificateController@ccFormIndex');
Route::match(['get', 'post'], 'cc/form/edit/{id}', 'CertificateController@formEdit');
Route::match(['get', 'post'], 'student_search_certificate', 'CertificateController@certificateSearch');
Route::match(['get', 'post'], 'certificate_delete', 'CertificateController@delete');
Route::match(['get', 'post'], 'certificate_add_click', 'CertificateController@certificateAddClick');
Route::get('cc_print/{id}', 'CertificateController@ccPrint');

Route::match(['get', 'post'], 'certificate_editor', 'CertificateController@certificateEditor');

Route::match(['get', 'post'], 'evente/certificate/add', 'CertificateController@eventeCertificateAdd');
Route::match(['get', 'post'], 'evente/certificate/index', 'CertificateController@certificateIndex');
Route::match(['get', 'post'], 'evente/certificate/edit/{id}', 'CertificateController@certificateEdit');
Route::match(['get', 'post'], 'search_evente', 'CertificateController@eventeSearch');
Route::match(['get', 'post'], 'evente_add_click', 'CertificateController@eventeAddClick');
Route::match(['get', 'post'], 'evente_delete', 'CertificateController@evente_delete');
Route::get('evente_print/{id}', 'CertificateController@eventePrint');

Route::match(['get', 'post'], 'sport/certificate/add', 'CertificateController@sportAdd');
Route::match(['get', 'post'], 'sport/certificate/index', 'CertificateController@sportndex');
Route::match(['get', 'post'], 'sport/certificate/edit/{id}', 'CertificateController@sportEdit');
Route::match(['get', 'post'], 'search_sport', 'CertificateController@sportSearch');
Route::match(['get', 'post'], 'sport_add_click', 'CertificateController@sportAddClick');
Route::match(['get', 'post'], 'sport_delete', 'CertificateController@sport_delete');
Route::match(['get', 'post'], 'CCFormStdData', 'CertificateController@CCFormStdData');
Route::get('sport_print/{id}', 'CertificateController@sportPrint');

Route::match(['get', 'post'], 'tc/certificate/add', 'CertificateController@tcCertificateAdd');
Route::match(['get', 'post'], 'tc/certificate/index', 'CertificateController@tcIndex');
Route::match(['get', 'post'], 'tc/certificate/edit/{id}', 'CertificateController@tcEdit');
Route::match(['get', 'post'], 'search_tc', 'CertificateController@tcSearch');
Route::match(['get', 'post'], 'tc_add_click', 'CertificateController@tcAddClick');
Route::match(['get', 'post'], 'tc_delete', 'CertificateController@tc_delete');
Route::get('tc_print/{id}', 'CertificateController@tcPrint');
Route::get('tc_print_formate', 'CertificateController@tcPrintFormate');
Route::match(['get', 'post'], 'noc_print/{id}', 'CertificateController@nocPrint');

//Student Dashboard

    Route::match(['get', 'post'], 'student_gate_pass_view', 'student_login\DashboardController@gatePassView');
    Route::match(['get', 'post'], 'student_uniform_view', 'student_login\DashboardController@uniformView');
    Route::match(['get', 'post'], 'prayer', 'student_login\DashboardController@prayerView');
    Route::match(['get', 'post'], 'rule_view', 'student_login\DashboardController@ruleView');
    //	Route::match(['get', 'post'], 'duedate/{id}', 'DashboardController@duedate');


// Examination offline
  Route::match(['get', 'post'], 'add_offline_exam', 'offline_exam\offline_examExaminationOfflineController@addExam');
  Route::match(['get', 'post'], 'view_offline_exam', 'offline_exam\ExaminationOfflineController@viewExam');
  Route::match(['get', 'post'], 'assign_offline_exam/{id}', 'offline_exam\ExaminationOfflineController@assignExam');
  Route::match(['get', 'post'], 'assign_offline_exam', 'offline_exam\ExaminationOfflineController@addAssignExam');
  Route::match(['get', 'post'], 'assign_delete_offline_exam', 'offline_exam\ExaminationOfflineController@deleteOfflineAssignExam');
  Route::match(['get', 'post'], 'edit_offline_exam/{id}', 'offline_exam\ExaminationOfflineController@editExam');
  Route::match(['get', 'post'], 'delete_offline_exam', 'offline_exam\ExaminationOfflineController@deleteOfflineExam');
  
  Route::match(['get', 'post'], 'add_offline_examination_schedule', 'offline_exam\ScheduleController@addOfflineExaminationSchedule');
	Route::match(['get', 'post'], 'SubmitSchedule', 'offline_exam\ExamScheduleController@SubmitSchedule');
  Route::match(['get', 'post'], 'examResultGraph', 'offline_exam\ExamResultController@examResultGraph');

// Examination

    Route::match(['get', 'post'], 'admit_card_notes', 'ExaminationController@AdmitCardNotes');
   

//FeesCollect
	Route::match(['get', 'post'], 'fee_dashboard', 'fees\FeesController@feeDashboard');
	Route::match(['get', 'post'], 'Fees/add', 'fees\FeesController@addFees');
	Route::match(['get', 'post'], 'quick_add_assign_fees_group', 'fees\FeesController@quickAddAssignFeesGroup');
	Route::match(['get', 'post'], 'student_fees_onclick', 'fees\FeesController@studentFeesOnclick');
	Route::match(['get', 'post'], 'student_pay_submit', 'fees\FeesController@studentPaySubmit');
	Route::match(['get', 'post'], 'inventoryPaySubmit', 'fees\FeesController@inventoryPaySubmit');
	Route::match(['get', 'post'], 'fees_search_data', 'fees\FeesController@feesSearchData');
	Route::match(['get', 'post'], 'print_payement/{id}', 'fees\FeesController@printPayement');
	Route::match(['get', 'post'], 'printFeesInvoice', 'fees\FeesController@printFeesInvoice');
    
	Route::match(['get', 'post'], 'print_payement_generate/{id}', 'fees\FeesController@printPayementGenerate');
	Route::match(['get', 'post'], 'getSessionwiseFeesDetails', 'fees\FeesController@getSessionwiseFeesDetails');

//fees Detail
	Route::match(['get', 'post'], 'collect_fees_delete', 'fees\FeesController@collectFeesDelete');


//Fees Ledger
	Route::match(['get', 'post'], 'fees/ledger', 'fees\FeesController@feesLedger');
	Route::get('fees/ledger/export/{format}', 'fees\FeesController@feesLedgerExport')->where('format', 'xlsx|csv');
	Route::match(['get', 'post'], 'ledger_update', 'fees\FeesController@ledgerUpdate');
	Route::match(['get', 'post'], 'ledger_save', 'fees\FeesController@ledgerSave');
	Route::match(['get', 'post'], 'fees/ledger/collect', 'fees\FeesController@feesLedgerCollect');
	Route::match(['get', 'post'], 'fees_ledger_view', 'fees\FeesController@fees_ledger_view');
Route::match(['get', 'post'], 'fees_cheque', 'fees\FeesController@fees_cheque');
Route::match(['get', 'post'], 'fees_ledger_print/{id}', 'fees\FeesController@feesLedgerPrint');

	


//no idea
	Route::match(['get', 'post'], 'fees/getFeesDetail', 'fees\FeesController@getFeesDetail');
	Route::match(['get', 'post'], 'fees_add_click', 'fees\FeesController@feesAddClick');
	Route::match(['get', 'post'], 'fees_masterData', 'fees\FeesController@feesMasterData');
//







 // Store Management
    Route::match(['get', 'post'], 'update-store-txn-status', 'StoreController@updateStoreTxnStatus');
    Route::match(['get', 'post'], 'storeDashboard', 'StoreController@storeDashboard');
    Route::match(['get', 'post'], 'addStoreItem', 'StoreController@addStoreItem');
    Route::match(['get', 'post'], 'viewStoreRequest', 'StoreController@viewStoreRequest');
    Route::match(['get', 'post'], 'deleteStoreItem', 'StoreController@deleteStoreItem');

    Route::match(['get', 'post'], 'showSoldOutItems', 'StoreController@showSoldOutItems');
    Route::match(['get', 'post'], 'searchStudentsForStationaryRequest', 'StoreController@searchStudentsForStationaryRequest');

    Route::match(['get', 'post'], 'addStationaryRequest', 'StoreController@addStationaryRequest');
	Route::match(['get', 'post'], 'createStationaryRequest', 'StoreController@createStationaryRequest');
    Route::match(['get', 'post'], 'updateStationaryRequest', 'StoreController@updateStationaryRequest');
    Route::match(['get', 'post'], 'cancelStorePayment', 'StoreController@cancelStorePayment');
    
   
    Route::match(['get', 'post'], 'editInvoiceInventory/{receipt_no}', 'StoreController@editInvoiceInventory');
    Route::match(['get', 'post'], 'deleteInvoiceInventory', 'StoreController@deleteInvoiceInventory');
    Route::match(['get', 'post'], 'deleteReceiptInventory', 'StoreController@deleteReceiptInventory');
	Route::match(['get', 'post'], 'store-daily-collection', 'StoreController@storeDailyCollection');
    






























//User Dashboard
Route::match(['get', 'post'], 'user_dashboard', 'UserController@user_dashboard');
Route::match(['get', 'post'], 'user_side_per', 'UserController@userSidePer');



//Generate Slary Slip...//
Route::match(['get', 'post'], 'salary_details', 'SalaryController@salaryDetails');
Route::match(['get', 'post'], 'staff_salary_view', 'SalaryController@staff_salary_view');
Route::match(['get', 'post'], 'assign/salary', 'SalaryController@assignSalary');
Route::match(['get', 'post'], 'assignSalaryDetail', 'SalaryController@assignSalaryDetail');
Route::match(['get', 'post'], 'generate/salary/slip', 'SalaryController@generateSalarySlip');
Route::match(['get', 'post'], 'find/staff', 'SalaryController@findStaff');
Route::match(['get', 'post'], 'salary_print/{id}/{month_id}', 'SalaryController@salaryPrint');
Route::match(['get', 'post'], 'download/salary/slip/{id}/{month_id}', 'SalaryController@downloadSalarySlip');
Route::match(['get', 'post'], 'generate/salary', 'SalaryController@generateSalary');


Route::match(['get', 'post'], 'countryData', 'Auth\AuthController@getLogin');
Route::match(['get', 'post'], 'change_password', 'Auth\AuthController@changePassword');

Route::match(['get', 'post'], 'updateSingleField', 'HomeController@updateSingleField');

Route::match(['get', 'post'], 'studentData/{id}', 'HomeController@studentData');
Route::match(['get', 'post'], 'streamData/{id}/{classType}', 'HomeController@streamData');
Route::match(['get', 'post'], 'subjectGetData/{id}', 'HomeController@subjectGetData');
Route::match(['get', 'post'], 'calendarElement', 'HomeController@calendarElement');

Route::match(['get', 'post'], 'attendanceEmail', 'DashboardController@sendAttendanceStatus');
Route::match(['get', 'post'], 'remark_mail', 'ExpenseController@remarkMail');

Route::match(['get', 'post'], 'birthday_mail', 'StudentsAdmissionController@birthdatEmail');
Route::match(['get', 'post'], 'send_wishes', 'BirthdayController@send_wishes');
Route::match(['get', 'post'], 'happy_birthday', 'BirthdayController@happy_birthday');



Route::match(['get', 'post'], 'student_report_card/{id}', 'ExaminationController@StudentReportCard');
Route::match(['get', 'post'], 'student_admit_card/{id}', 'ExaminationController@StudentAdmitCard');






	//dashboard
	Route::match(['get'], '/', 'DashboardController@dashboard');
	Route::match(['get'], 'dashboard', 'DashboardController@dashboard');
	Route::match(['get', 'post'], 'minidashboard', 'DashboardController@minidashboard');
   // Route::match(['get'], 'test', 'DashboardController@test');

	Route::match(['get', 'post'], 'profile', 'ProfileController@profile');
	Route::match(['get', 'post'], 'profile/edit/{id}', 'ProfileController@profileEdit');
	Route::match(['get', 'post'], 'all_students_search', 'DashboardController@allStudentsSearch');
	Route::match(['get', 'post'], 'get_modules', 'DashboardController@getModules');
	Route::match(['get', 'post'], 'stu_status', 'DashboardController@stuStatus');
	Route::match(['get', 'post'], 'header/student/search', 'DashboardController@headerStudentSearch');


	/*Route::match(['get', 'post'], 'add/task', 'ToDoListController@addTask');
	Route::match(['get', 'post'], 'status/task', 'ToDoListController@statusTask');
	Route::match(['get', 'post'], 'delete/task', 'ToDoListController@deleteTask');*/
	
	Route::match(['get', 'post'], 'add/task', 'ToDoListController@addTask');
	Route::match(['get', 'post'], 'taskStatusChange', 'ToDoListController@taskStatusChange');
	Route::match(['get', 'post'], 'to_do_assign', 'ToDoListController@toDoAssign');
	Route::match(['get', 'post'], 'to_do_assign_view', 'ToDoListController@toDoAssignView');
	Route::match(['get', 'post'], 'to_do_assign_edit/{id}', 'ToDoListController@toDoAssignEdit');
	Route::match(['get', 'post'], 'to_do_assign_delete', 'ToDoListController@toDoAssignDelete');
	Route::match(['get', 'post'], 'status/task', 'ToDoListController@statusTask');
	Route::match(['get', 'post'], 'delete/task', 'ToDoListController@deleteTask');
	Route::match(['get', 'post'], 'task_list', 'ToDoListController@taskList');

	//user
	Route::get('create-user', 'UserController@createuser');

	







	//students Id
	Route::match(['get', 'post'], 'students_id_data', 'StudentsIdController@studentsIdData');
	Route::match(['get', 'post'], 'student/id/index', 'StudentsIdController@studentIdIndex');

    Route::match(['get', 'post'], 'customIdCard', 'StudentsIdController@customIdCard');
    Route::match(['get', 'post'], 'customIdCatdImageUpload', 'StudentsIdController@customIdCatdImageUpload');
    Route::match(['get', 'post'], 'customIdCardExcelUpload', 'StudentsIdController@customIdCardExcelUpload');
    Route::match(['get', 'post'], 'customIdPrint/{id}', 'StudentsIdController@customIdPrint');
    Route::match(['get', 'post'], 'customIdPrintMultiple', 'StudentsIdController@customIdPrintMultiple');
    Route::match(['get', 'post'], 'visitorPass', 'StudentsIdController@visitorPass');
    Route::match(['get', 'post'], 'updateSingleFieldCustomIdCard', 'StudentsIdController@updateSingleFieldCustomIdCard');
    Route::match(['get', 'post'], 'deleteCustomIdCard', 'StudentsIdController@deleteCustomIdCard');

	//account_dashboard 
	Route::match(['get', 'post'], 'account_dashboard', 'AccountController@account_dashboard');
	Route::match(['get', 'post'], 'bank/account/add', 'AccountController@add');
	Route::match(['get', 'post'], 'bank/account/index', 'AccountController@accountList');
	Route::match(['get', 'post'], 'bank/account/edit/{id}', 'AccountController@editBank');
	Route::match(['get', 'post'], 'account_delete', 'AccountController@delete');


	// Staff Attendance

	Route::get('salary_generate', 'SalaryController@salaryGenerate');

	Route::match(['get', 'post'], 'staff_attendance_add', 'StaffAttendanceController@add');
	Route::match(['get', 'post'], 'SearchValueStaffAtten', 'StaffAttendanceController@searchValueStaffAtten');
	Route::match(['get', 'post'], 'staff_attendance_save', 'StaffAttendanceController@staff_attendance_save');
	Route::match(['get', 'post'], 'staff/Attendance/view', 'StaffAttendanceController@view');
	Route::match(['get', 'post'], 'autoStaffAttendance', 'StaffAttendanceController@autoStaffAttendance');
	
	Route::post('Staff_categiry_data/{id}', 'StaffAttendanceController@StaffCategiryData');


	//Settings
	Route::match(['get', 'post'], 'settings_dashboard', 'SettingsController@settings_dashboard');


	Route::match(['get', 'post'], 'add_ip_setting', 'SettingsController@addIP');
	Route::match(['get', 'post'], 'view_ip_setting', 'SettingsController@viewIP');
	Route::match(['get', 'post'], 'edit_ip_setting/{id}', 'SettingsController@editIP');
	Route::match(['get', 'post'], 'delete_ip_setting', 'SettingsController@deleteIP');
	Route::match(['get', 'post'], 'ip_status', 'SettingsController@ipStatus');

	
	Route::match(['get', 'post'], 'discountdata/{id}', 'DashboardController@discountdata');
	/*Route::match(['get', 'post'], 'task', 'DashboardController@task');
	Route::match(['get', 'post'], 'task_list', 'DashboardController@taskList');*/




	






	// Download Center
	Route::match(['get', 'post'], 'download_center', 'DownloadController@downloadCenter');
	Route::match(['get', 'post'], 'upload/content', 'DownloadController@upload');
	Route::match(['get', 'post'], 'upload/content_edit/{id}', 'DownloadController@upload_edit');
	Route::match(['get', 'post'], 'download/{id}', 'DownloadController@download');

	Route::match(['get', 'post'], 'upload_delete', 'DownloadController@uploadDelete');
	Route::match(['get', 'post'], 'assignments', 'DownloadController@assignments');
	Route::match(['get', 'post'], 'study_material', 'DownloadController@studyMaterials');
	Route::match(['get', 'post'], 'syllabus', 'DownloadController@syllabus');
	Route::match(['get', 'post'], 'other_downloads', 'DownloadController@otherDownloads');
	Route::match(['get', 'post'], 'studentAdmitCard', 'DownloadController@studentAdmitCard');


	// SMS Service
	Route::match(['get', 'post'], 'send_message_terminal', 'Sms_ServiceController@sendMessageTerminal');
	Route::match(['get', 'post'], 'send_message', 'Sms_ServiceController@sendMessage');
	Route::match(['get', 'post'], 'resend_message', 'Sms_ServiceController@resendMessage');
	Route::match(['get', 'post'], 'sms_search_data', 'Sms_ServiceController@smsSearchData');
	Route::match(['get', 'post'], 'group_view', 'Sms_ServiceController@groupView');
	Route::match(['get', 'post'], 'group_add', 'Sms_ServiceController@groupAdd');
	Route::match(['get', 'post'], 'whatsapp_group_status', 'Sms_ServiceController@groupStatus');
	Route::match(['get', 'post'], 'group_delete', 'Sms_ServiceController@groupDelete');
	Route::match(['get', 'post'], 'group_edit/{id}', 'Sms_ServiceController@groupEdit');
	Route::match(['get', 'post'], 'group_messages_send', 'Sms_ServiceController@groupMessagesSend');
	Route::match(['get', 'post'], 'getGroupData/{id}', 'Sms_ServiceController@getGroupData');
	Route::match(['get', 'post'], 'failed_messages_delete', 'Sms_ServiceController@failedMessagesDelete');
    

	//student dashboard
	Route::match(['get', 'post'], 'timetable', 'student_login\DashboardController@timetableView');
    Route::match(['get', 'post'], 'homework', 'student_login\DashboardController@homeworkView');
	Route::match(['get', 'post'], 'downloadHomework/{id}', 'master\HomeworkController@downloadHomework');
	Route::match(['get', 'post'], 'student_download_center', 'student_login\DownloadCenterController@studentDownloadCenter');
	Route::match(['get', 'post'], 'studentAssignments', 'student_login\DownloadCenterController@studentAssignments');
	Route::match(['get', 'post'], 'student_study_material', 'student_login\DownloadCenterController@studentStudyMaterials');
	Route::match(['get', 'post'], 'student_syllabus', 'student_login\DownloadCenterController@studentSyllabus');
	Route::match(['get', 'post'], 'student_other_downloads', 'student_login\DownloadCenterController@studentOtherDownloads');

Route::match(['get', 'post'], 'parent_teacher_conversation', 'student_login\ComplaintController@parent_teacherConversation');

// Route::match(['get', 'post'], 'student_complaints', 'student_login\StudentComplaintController@studentComplaints');




// Student Complaints (student: apni complaints, admin: sabhi students ki)
Route::match(['get', 'post'], 'student_complaints', 'student_login\StudentComplaintController@studentComplaints');
Route::post('student_complaints_update', 'student_login\StudentComplaintController@update');
Route::post('student_complaints_reply', 'student_login\StudentComplaintController@reply');
Route::post('student_complaints_delete', 'student_login\StudentComplaintController@delete');



	// Library Management Start... //

//	Route::match(['get', 'post'], 'books_library', 'LibraryController@view');


	

	Route::match(['get', 'post'], 'dataCabin/{id}', 'library\LibraryClickDataController@dataCabin');
	Route::match(['get', 'post'], 'libraryData/{id}', 'HomeController@libraryData');

	



















	Route::match(['get', 'post'], 'school_calender_add', 'SchoolCalenderController@schoolCalenderAdd');
	Route::match(['get', 'post'], 'calender_view', 'SchoolCalenderController@calendarView');
	Route::match(['get', 'post'], 'getEvents', 'SchoolCalenderController@getEvents');
	//examination panel start//
	Route::match(['get', 'post'], 'examination_dashboard', 'ExaminationController@examinationDashboard');
	Route::match(['get', 'post'], 'view/question', 'offline_exam\QuestionController@viewQuestion');	
	Route::match(['get', 'post'], 'add/question', 'offline_exam\QuestionController@addQuestion');
	Route::match(['get', 'post'], 'edit/question/{id}', 'offline_exam\QuestionController@editQuestion');
	Route::match(['get', 'post'], 'delete/question', 'offline_exam\QuestionController@deleteQuestion');

	Route::match(['get', 'post'], 'view/exam', 'offline_exam\ExamController@viewExam');
	Route::match(['get', 'post'], 'add/exam', 'offline_exam\ExamController@addExam');
	Route::match(['get', 'post'], 'edit/exam/{id}', 'offline_exam\ExamController@editExam');
	Route::match(['get', 'post'], 'assign/exam/{id}', 'offline_exam\ExamController@assignExam');
	Route::match(['get', 'post'], 'assign/delete/exam', 'offline_exam\ExamController@deleteAssignExam');

	Route::match(['get', 'post'], 'add/examination_schedule', 'offline_exam\ExamScheduleController@addExaminationSchedule');
    Route::match(['get', 'post'], 'examData/{class_type_id}', 'HomeController@examData');
	Route::match(['get', 'post'], 'SubmitExaminationSchedule', 'offline_exam\ExamScheduleController@SubmitSchedule');


	Route::match(['get', 'post'], 'my_exams', 'offline_exam\FillMarkController@myExams');
	Route::match(['get', 'post'], 'fill_marks', 'offline_exam\FillMarkController@fillMarks');
    Route::match(['get', 'post'], 'fill_marks_submit', 'offline_exam\FillMarkController@fillMarksSubmit');
	Route::match(['get', 'post'], 'download_marksheet', 'offline_exam\FillMarkController@download_marksheet');
	Route::match(['get', 'post'], 'print_report_card', 'offline_exam\FillMarkController@printReportCard');
	Route::match(['get', 'post'], 'bulk_marksheet', 'offline_exam\FillMarkController@bulk_marksheet');
    Route::match(['get', 'post'], 'bulk_marksheet_generate', 'offline_exam\FillMarkController@bulk_marksheet_generate');
    Route::match(['get', 'post'], 'performance_marks', 'offline_exam\FillMarkController@performanceMarks');
    Route::match(['get', 'post'], 'performance_marks_submit', 'offline_exam\FillMarkController@performanceMarksSubmit');
    
	Route::match(['get', 'post'], 'add/admit_card', 'offline_exam\AdmitCardController@addAdmitCard');
	Route::match(['get', 'post'], 'download_admit_card', 'offline_exam\AdmitCardController@download_admit_card');
	Route::match(['get', 'post'], 'SubmitAdmitCard', 'offline_exam\AdmitCardController@SubmitAdmitCard');
	Route::match(['get', 'post'], 'single_exam_admit_card/{class_type_id}/{exam_id}/{admission_id}', 'offline_exam\AdmitCardController@singleDownloadAdmitCardPdf');
    Route::match(['get', 'post'], 'exam_admit_card/{exam_id}/{class_type_id}/{admission_id}', 'offline_exam\AdmitCardController@downloadAdmitCard');
     Route::match(['get', 'post'], 'without_subject_admit_card/{exam_id}/{class_type_id}/{admission_id}', 'offline_exam\AdmitCardController@without_subject_admit_card');

	
	
	
	
	
	
	
	Route::match(['get', 'post'], 'student_performance', 'StudentPerformanceController@studentPerformance');
	Route::match(['get', 'post'], 'studentParticularPerformance/{id}', 'StudentPerformanceController@studentParticularPerformance');
	
	
	
	Route::match(['get', 'post'], 'message_queue', 'MessageQueueController@message_queue');
	
	
	
	
	
	
	
	
	
	
	
	

	Route::match(['get', 'post'], 'get_question_ajax', 'ExaminationController@getQuestionAjax');
	Route::match(['get', 'post'], 'add_exam_result', 'ExaminationController@addExamStudents');
	Route::match(['get', 'post'], 'view_exam_result', 'ExaminationController@viewExamStudents');
	Route::match(['get', 'post'], 'exam_print', 'ExaminationController@examPrint');
	

	// My Examinations ...//
	Route::match(['get', 'post'], 'view_marksheet1', 'ExaminationController@marksheet1');
	Route::match(['get', 'post'], 'view_marksheet2', 'ExaminationController@marksheet2');
	Route::match(['get', 'post'], 'view_marksheet3', 'ExaminationController@marksheet3');
	Route::match(['get', 'post'], 'view_marksheet4', 'ExaminationController@marksheet4');
	Route::match(['get', 'post'], 'view_marksheet5', 'ExaminationController@marksheet5');



	Route::match(['get', 'post'], 'view/marksheet1', 'ExaminationController@viewMarksheet1');


	Route::match(['get', 'post'], 'print_marksheet', 'ExaminationController@print_marksheet');
	
	Route::match(['get', 'post'], 'teacher/view/exam/{id}', 'ExaminationController@viewExamTeacher');
	Route::match(['get', 'post'], 'delete/exam', 'ExaminationController@deleteExam');




	
	Route::match(['get', 'post'], 'exam_admit_card', 'ExaminationController@downloadAdmitCard');

	Route::match(['get', 'post'], 'assign/question', 'ExaminationController@assignQuestion');
	Route::match(['get', 'post'], 'search/assigned/question', 'ExaminationController@searchkAssignedQuestion');
	Route::match(['get', 'post'], 'search/already/assigned/question', 'ExaminationController@searchkAlreadyAssignedQuestion');

	Route::match(['get', 'post'], 'online/exam/{id}', 'ExaminationController@onlineExam');
	Route::match(['get', 'post'], 'submit_exam', 'ExaminationController@addExamResult');

	Route::match(['get', 'post'], 'fetchQuestion', 'ExaminationController@fetchQuestion');

	Route::match(['get', 'post'], 'view/result', 'ExaminationController@viewResult');
	Route::match(['get', 'post'], 'view/exam/result/{id}', 'ExaminationController@viewExamResult');
	Route::match(['get', 'post'], 'download/marksheet/{admission_id}/{exam_id}', 'ExaminationController@downloadMarksheet');
	Route::match(['get', 'post'], 'view/marksheet/{admission_id}/{exam_id}', 'ExaminationController@viewMarksheet');
	Route::match(['get', 'post'], 'answer/key/{id}', 'ExaminationController@answerKey');
	Route::match(['get', 'post'], 'answer/key/{exam_id}/{student_id}', 'ExaminationController@answerKeyTeacher');
	//examination panel end//


	Route::match(['get', 'post'], 'changeLang', 'HomeController@change');
    Route::match(['get', 'post'], 'sectionDataId', 'HomeController@sectionDataId');

// ONLINE EXAMINATION ADMIN SIDE START

    Route::match(['get', 'post'], 'examChapterData', 'exam\LiveExaminationController@examChapterData');
    Route::match(['get', 'post'], 'examTopicData', 'exam\LiveExaminationController@examTopicData');
    Route::match(['get', 'post'], 'checkQuestionCount', 'exam\LiveExaminationController@checkQuestionCount');
    Route::match(['get', 'post'], 'checkQuestionAvailability', 'exam\LiveExaminationController@checkQuestionAvailability');
    Route::match(['get', 'post'], 'digital/add/exam', 'exam\LiveExaminationController@addExam');
    Route::match(['get', 'post'], 'digital/view/exam', 'exam\LiveExaminationController@viewExam');
    Route::match(['get', 'post'], 'digital/view/deleted/exam', 'exam\LiveExaminationController@viewDeletedExam');
    Route::match(['get', 'post'], 'digital/restore/exam', 'exam\LiveExaminationController@restoreExam');
    Route::match(['get', 'post'], 'digital/edit/exam/{id}', 'exam\LiveExaminationController@editExam');
	Route::match(['get', 'post'], 'digital/delete/exam', 'exam\LiveExaminationController@deleteExam');
	Route::match(['get', 'post'], 'print/exam/{id}', 'exam\LiveExaminationController@printExam');
	Route::match(['get', 'post'], 'answerKey/{id}', 'exam\LiveExaminationController@answerKey');
    Route::match(['get', 'post'], 'digital/add/question', 'exam\LiveExaminationController@addQuestion');
    Route::match(['get', 'post'], 'digital/view/question', 'exam\LiveExaminationController@viewQuestion');
    Route::match(['get', 'post'], 'digital/edit/question/{id}', 'exam\LiveExaminationController@editQuestion');
    Route::match(['get', 'post'], 'digital/delete/question', 'exam\LiveExaminationController@deleteQuestion');
    Route::match(['get', 'post'], 'digitalExamTerminal', 'exam\LiveExaminationController@digitalExamTerminal');
    Route::match(['get', 'post'], 'getChapters', 'exam\LiveExaminationController@getChapters');
    Route::match(['get', 'post'], 'examTerminal', 'ExaminationController@examTerminal');
    Route::match(['get', 'post'], 'examTerminal2', 'ExaminationController@examTerminal2');
    Route::match(['get', 'post'], 'digitalStartExam', 'exam\LiveExaminationController@digitalStartExam');
    Route::match(['get', 'post'], 'digitalResultExam', 'exam\LiveExaminationController@digitalResultExam');
    Route::match(['get', 'post'], 'examResultStudentList/{id}', 'exam\LiveExaminationController@examResultStudentList');
    Route::match(['get', 'post'], 'digitalAnalysisPanel', 'exam\LiveExaminationController@digitalAnalysisPanel');
    Route::match(['get', 'post'], 'digitalDownloadAnswerKey', 'exam\LiveExaminationController@digitalDownloadAnswerKey');
    Route::match(['get', 'post'], 'digitalQuestionKey', 'exam\LiveExaminationController@digitalQuestionKey');
    Route::match(['get', 'post'], 'digitalExamAnalysis/{id}', 'exam\LiveExaminationController@digitalExamAnalysis');



// Print file controller

    Route::match(['get', 'post'], 'printFilePanel', 'PrintFileController@printFilePanel');
    Route::match(['get', 'post'], 'template/{id}', 'PrintFileController@template');
    Route::match(['get', 'post'], 'printFilePanel', 'PrintFileController@printFilePanel');

    Route::match(['get', 'post'], 'printFileModuleWiseView/{id}', 'PrintFileController@printFileModuleWiseView');
    
    Route::match(['get', 'post'], 'feesRemainderCron', 'fees\FeesController@feesRemainderCron');
    
    


//Registration
Route::match(['get', 'post'], 'registrationDashboard', 'registration\RegistrationController@registrationDashboard');
Route::match(['get', 'post'], 'registrationList', 'registration\RegistrationController@registrationList');
Route::match(['get', 'post'], 'counselling', 'registration\RegistrationController@counselling');
Route::match(['get', 'post'], 'registrationListGroup', 'registration\RegistrationController@registrationListGroup');


Route::match(['get', 'post'], 'registration_fees_ledger_view', 'registration\RegistrationController@registrationFeesLedgerView');

    // ==========================================
    // MODERN COLLEGE FEES MODULE (ISOLATED)
    // ==========================================
    Route::prefix('college/fees')->group(function () {
        Route::get('/dashboard', 'CollegeFees\CollegeFeeController@dashboard')->name('college.fees.dashboard');
        Route::get('/structures', 'CollegeFees\CollegeFeeController@structures')->name('college.fees.structures');
        Route::post('/structures/save', 'CollegeFees\CollegeFeeController@saveStructure')->name('college.fees.structures.save');
        Route::match(['get', 'post'], '/structures/delete/{id}', 'CollegeFees\CollegeFeeController@deleteStructure')->name('college.fees.structures.delete');
        Route::post('/structures/copy', 'CollegeFees\CollegeFeeController@copyStructure')->name('college.fees.structures.copy');
        Route::post('/structures/save-common', 'CollegeFees\CollegeFeeController@saveCommonStructure')->name('college.fees.structures.save_common');
        Route::get('/structures/common-heads', 'CollegeFees\CollegeFeeController@getCommonStructures')->name('college.fees.structures.common_heads');
        Route::match(['get', 'post'], '/structures/delete-common/{id}', 'CollegeFees\CollegeFeeController@deleteCommonHead')->name('college.fees.structures.delete_common_head');
        Route::post('/structures/auto-assign', 'CollegeFees\CollegeFeeController@autoAssignStructure')->name('college.fees.structures.auto_assign');

        // Student Fee Ledger Interactive Matrix Endpoints
        Route::get('/ledger-matrix-data', 'CollegeFees\CollegeFeeController@getLedgerMatrixData')->name('college.fees.ledger_matrix_data');
        Route::post('/update-cell-fee', 'CollegeFees\CollegeFeeController@updateCellFee')->name('college.fees.update_cell_fee');
        Route::post('/add-head-to-student', 'CollegeFees\CollegeFeeController@addHeadToStudent')->name('college.fees.add_head_to_student');
        Route::post('/remove-student-head', 'CollegeFees\CollegeFeeController@removeStudentHead')->name('college.fees.remove_student_head');
        Route::post('/update-discount', 'CollegeFees\CollegeFeeController@updateDiscount')->name('college.fees.update_discount');
        Route::post('/apply-bulk-discount', 'CollegeFees\CollegeFeeController@applyBulkDiscount')->name('college.fees.apply_bulk_discount');
        Route::post('/save-batch-standard-fees', 'CollegeFees\CollegeFeeController@saveBatchStandardFees')->name('college.fees.save_batch_standard_fees');
        Route::post('/assign-heads-to-selected', 'CollegeFees\CollegeFeeController@assignHeadsToSelectedStudents')->name('college.fees.assign_heads_to_selected');
        Route::post('/bulk-collect-payment', 'CollegeFees\CollegeFeeController@bulkCollectPayment')->name('college.fees.bulk_collect_payment');
        Route::get('/bulk-receipt-pdf/{bulkBatchId}', 'CollegeFees\CollegeFeeController@streamBulkReceiptPdf')->name('college.fees.bulk_receipt_pdf');
        Route::match(['get', 'post'], '/selected-receipts-pdf', 'CollegeFees\CollegeFeeController@streamSelectedReceiptsPdf')->name('college.fees.selected_receipts_pdf');
        Route::get('/bulk-collections-history', 'CollegeFees\CollegeFeeController@getBulkCollectionsHistory')->name('college.fees.bulk_collections_history');

        Route::get('/student-ledger/{studentId}', 'CollegeFees\CollegeFeeController@getStudentLedger')->name('college.fees.student_ledger');
        Route::get('/get-student-collect-data', 'CollegeFees\CollegeFeeController@getStudentCollectData')->name('college.fees.get_student_collect_data');
        Route::post('/collect-payment', 'CollegeFees\CollegeFeeController@collectPayment')->name('college.fees.collect_payment');
        Route::get('/receipt/{id}', 'CollegeFees\CollegeFeeController@printReceipt')->name('college.fees.receipt');
        Route::get('/receipt/{id}/download', 'CollegeFees\CollegeFeeController@downloadReceipt')->name('college.fees.receipt_download');
        Route::post('/cancel-receipt', 'CollegeFees\CollegeFeeController@cancelReceipt')->name('college.fees.cancel_receipt');
        Route::get('/receipt/{id}/edit-data', 'CollegeFees\CollegeFeeController@getReceiptEditData')->name('college.fees.receipt_edit_data');
        Route::post('/update-receipt', 'CollegeFees\CollegeFeeController@updateReceipt')->name('college.fees.update_receipt');

        Route::get('/progression', 'CollegeFees\CollegeFeeController@progression')->name('college.fees.progression');
        Route::get('/progression/detect-batch-sem', 'CollegeFees\CollegeFeeController@detectBatchActiveSemester')->name('college.fees.progression.detect_batch_sem');
        Route::post('/progression/execute', 'CollegeFees\CollegeFeeController@executeProgression')->name('college.fees.progression.execute');

        Route::get('/reports', 'CollegeFees\CollegeFeeController@reports')->name('college.fees.reports');

        // Consolidated Course Fees Ledger
        Route::get('/course-ledger', 'CollegeFees\CollegeFeeController@courseLedger')->name('college.fees.course_ledger');
        Route::get('/course-ledger-data', 'CollegeFees\CollegeFeeController@getCourseLedgerData')->name('college.fees.course_ledger_data');
    });

});



//Registration
Route::match(['get', 'post'], 'registration', 'registration\RegistrationController@registration');
Route::match(['get', 'post'], 'registration/saveFees', 'registration\RegistrationController@saveFees');
Route::post('/get-fees-groups', 'registration\RegistrationController@getFeesGroupByType');
Route::match(['get', 'post'], 'registration/get-by-neet', 'registration\RegistrationController@getByNeet');
Route::match(['get', 'post'], 'get_duplicate_registration', 'registration\RegistrationController@duplicateRegistration');
Route::match(['get', 'post'], 'registration/print/{id}', 'registration\RegistrationController@print');
Route::match(['get', 'post'], 'registrationList/print/{id}/{payment_date}/{registration_invoice_no}', 'registration\RegistrationController@listGroupprint');


Route::match(['get', 'post'], 'countryData/{id}', 'HomeController@countryData');
Route::match(['get', 'post'], 'stateData/{id}', 'HomeController@stateData');


Route::match(['get', 'post'], 'test', 'TestController@test');
Route::match(['get', 'post'], 'testExcel', 'TestController@testExcel');

Route::match(['get', 'post'], 'newRegistration', 'Auth\AuthController@newRegistration');
Route::match(['get', 'post'], 'redirection', 'Auth\AuthController@redirection');
Route::match(['get', 'post'], 'clearTable', 'Auth\AuthController@clearTable');
Route::match(['get', 'post'], 'setSidebar', 'Auth\AuthController@setSidebar');
Route::match(['get', 'post'], 'allowSidebar', 'Auth\AuthController@allowSidebar');
Route::match(['get', 'post'], 'validateOtp', 'Auth\AuthController@validateOtp');
