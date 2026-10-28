<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Admission;
use App\Models\Salary;
use App\Models\SmsSetting;
use App\Models\WhatsappSetting;
use App\Models\StaffAttendance;
use App\Models\TeacherCategory;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\Master\MessageTemplate;
use App\Models\AttendanceStatus;
use App\Models\Setting;
use App\Models\Master\Branch;
use App\Models\CronJobs;
use App\Models\MessageQueue;
use App\Models\StoreBillingDetail;
use App\Models\StoreItemRequest;
use App\Models\StoreItem;
use Session;
use Hash;
use Helper;
use Str;
use Redirect;
use Carbon\Carbon;
use Auth;
use Log;
use DateTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;


class CronJobController extends Controller

{

 
    public function cronJobs(NotificationService $notif): void
    {

        $notif->processQueue();
        //$this->releaseStaleStoreReservations();
        //rescue(fn () => app(\App\Payments\PaymentService::class)->reconcile());
    }   

    // private function releaseStaleStoreReservations(): void
    // {
    //     $staleBillings = StoreBillingDetail::where('transaction_status', 'pending')
    //         ->where('created_at', '<', now()->subMinutes(1))
    //         ->get();

    //     foreach ($staleBillings as $billing) {
    //         StoreItemRequest::where('receipt_no',  $billing->receipt_no)
    //             ->where('session_id', $billing->session_id)
    //             ->where('branch_id',  $billing->branch_id)
    //             ->get()
    //             ->each(function ($item) {
    //                 StoreItem::find($item->store_item_id)?->decrement(
    //                     'reserved_qty',
    //                     $item->qty
    //                 );
    //             });

    //         $billing->update(['transaction_status' => 'cancelled']);
    //     }
    // }
   
    

  

  

    
   
}
