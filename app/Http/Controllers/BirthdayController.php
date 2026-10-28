<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator; 
use App\Models\Setting;
use App\Models\Admission;
use App\Models\User;
use App\Models\BirthdayWishes;
use App\Models\Master\MessageTemplate;
use Session;
use Hash;
use Str;
use Redirect;
use Helper;
use Auth;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BirthdayController extends Controller
{
    public function happy_birthday(Request $request){
        $targetDate = !empty($request->date) ? Carbon::parse($request->date) : now();
        $branchId = Session::get('branch_id');
        $sessionId = Session::get('session_id');

        $studentQuery = Admission::select('admissions.*', 'types.name as class_name')
            ->leftJoin('class_types as types', 'types.id', 'admissions.class_type_id')
            ->where('admissions.branch_id', $branchId)
            ->when(!empty($sessionId), function($q) use ($sessionId) {
                $q->where('admissions.session_id', $sessionId);
            })
            ->where('admissions.status', 1)
            ->whereMonth('admissions.dob', $targetDate->month)
            ->whereDay('admissions.dob', $targetDate->day)
            ->whereNull('admissions.deleted_at')
            ->orderBy('admissions.first_name', 'ASC');

        $userQuery = User::select('users.*', 'role.name as role_name')
            ->leftJoin('role', 'role.id', 'users.role_id')
            ->where('users.branch_id', $branchId)
            ->where('users.status', 1)
            ->whereMonth('users.dob', $targetDate->month)
            ->whereDay('users.dob', $targetDate->day)
            ->whereNull('users.deleted_at')
            ->orderBy('users.first_name', 'ASC');

        $student = $studentQuery->get();
        $user = $userQuery->get();

        // High-Performance Bulk Eager Loading of Birthday Wishes (Eliminating all N+1 blade queries)
        $studentIds = $student->pluck('id')->filter()->toArray();
        $studentWishes = [];
        if (!empty($studentIds)) {
            $studentWishes = BirthdayWishes::whereIn('admission_id', $studentIds)
                ->where('branch_id', $branchId)
                ->whereNull('deleted_at')
                ->where(function($q) use ($targetDate) {
                    $q->whereYear('created_at', $targetDate->year)
                      ->orWhereNull('created_at');
                })
                ->orderBy('id', 'desc')
                ->get()
                ->keyBy('admission_id');
        }

        $userIds = $user->pluck('id')->filter()->toArray();
        $userWishes = [];
        if (!empty($userIds)) {
            $userWishes = BirthdayWishes::whereIn('user_id_sender', $userIds)
                ->where('branch_id', $branchId)
                ->whereNull('deleted_at')
                ->where(function($q) use ($targetDate) {
                    $q->whereYear('created_at', $targetDate->year)
                      ->orWhereNull('created_at');
                })
                ->orderBy('id', 'desc')
                ->get()
                ->keyBy('user_id_sender');
        }

        $studentPendingCount = 0;
        $studentSentCount = 0;
        $studentFailedCount = 0;
        foreach ($student as $s) {
            $s->wish_record = $studentWishes[$s->id] ?? null;
            if (!empty($s->wish_record)) {
                if ($s->wish_record->status == 0) {
                    $studentSentCount++;
                } else {
                    $studentFailedCount++;
                }
            } else {
                $studentPendingCount++;
            }
        }

        $userPendingCount = 0;
        $userSentCount = 0;
        $userFailedCount = 0;
        foreach ($user as $u) {
            $u->wish_record = $userWishes[$u->id] ?? null;
            if (!empty($u->wish_record)) {
                if ($u->wish_record->status == 0) {
                    $userSentCount++;
                } else {
                    $userFailedCount++;
                }
            } else {
                $userPendingCount++;
            }
        }

        $template = MessageTemplate::select('message_templates.*', 'message_types.slug')
            ->leftJoin('message_types', 'message_types.id', 'message_templates.message_type_id')
            ->where('message_types.status', 1)
            ->where('message_types.slug', 'birthday-wishes')
            ->first();

        return view('birthday/view', [
            'data' => $student,
            'data2' => $user,
            'targetDate' => $targetDate,
            'isToday' => $targetDate->isToday(),
            'studentPendingCount' => $studentPendingCount,
            'studentSentCount' => $studentSentCount,
            'studentFailedCount' => $studentFailedCount,
            'userPendingCount' => $userPendingCount,
            'userSentCount' => $userSentCount,
            'userFailedCount' => $userFailedCount,
            'template' => $template
        ]);
    }

    public function send_wishes(Request $request){
        $template = MessageTemplate::select('message_templates.*', 'message_types.slug')
            ->leftJoin('message_types', 'message_types.id', 'message_templates.message_type_id')
            ->where('message_types.status', 1)
            ->where('message_types.slug', 'birthday-wishes')
            ->first();
                
        $setting = Setting::where('branch_id', Session::get('branch_id'))->first();

        if ($request->isMethod('post')) {
            $error = 0;
            $success = 0;

            // 1. Process Users (Staff) Wishes
            if (!empty($request->checkbox_user)) {
                foreach ($request->checkbox_user as $key => $item) {
                    $userObj = User::find($item);
                    $userName = $userObj ? ($userObj->first_name . ($userObj->last_name ? ' ' . $userObj->last_name : '')) : ($request->first_name_user[$key] ?? '');
                    $userMobile = $userObj ? $userObj->mobile : ($request->mobile_user[$key] ?? '');
                    $userRole = $userObj ? $userObj->role_id : ($request->role_id_user[$key] ?? 1);

                    $arrey1 = array('{#name#}', '{#school_name#}');
                    $arrey2 = array($userName, $setting->name ?? '');

                    $rawMsg = !empty($template->whatsapp_content) ? $template->whatsapp_content : "🎉 Happy Birthday {#name#}! Wishing you a wonderful year ahead.";
                    $message = str_replace($arrey1, $arrey2, $rawMsg);

                    if (!empty($setting) && $setting->firebase_notification == 1) {
                        Helper::sendNotification(
                            $template->title ?? 'Happy Birthday',
                            $message,
                            'user',
                            $item
                        ); 
                    }

                    $response = Helper::sendWhatsappMessage($userMobile, $message, null);

                    $value = new BirthdayWishes;
                    $value->user_id = Session::get('user_id');
                    $value->session_id = Session::get('session_id');
                    $value->branch_id = Session::get('branch_id');
                    $value->user_id_sender = $item;
                    $value->role_id = $userRole;

                    $decode = is_string($response) ? json_decode($response) : (is_array($response) ? (object)$response : $response);

                    if (!isset($decode->status) || $decode->status === false || $decode->status == 'error' || empty($decode)) {
                        $value->status = 1;
                        $value->failed_message = isset($decode->message) ? $decode->message : 'Error sending wish';
                        $value->save();
                        $error++;
                    } else {
                        $value->status = 0;
                        $value->save();
                        $success++;
                    }
                }
            }

            // 2. Process Students Wishes
            if (!empty($request->checkbox_student)) {
                foreach ($request->checkbox_student as $key => $item) {
                    $studentObj = Admission::find($item);
                    $studentName = $studentObj ? ($studentObj->first_name . ($studentObj->last_name ? ' ' . $studentObj->last_name : '')) : ($request->first_name_student[$key] ?? '');
                    $studentMobile = $studentObj ? $studentObj->mobile : ($request->mobile_student[$key] ?? '');
                    $studentRole = $studentObj ? ($studentObj->role_id ?? 3) : ($request->role_id_student[$key] ?? 3);

                    $arrey1 = array('{#name#}', '{#school_name#}');
                    $arrey2 = array($studentName, $setting->name ?? '');

                    $rawMsg = !empty($template->whatsapp_content) ? $template->whatsapp_content : "🎉 Happy Birthday {#name#}! Wishing you a wonderful year ahead.";
                    $message = str_replace($arrey1, $arrey2, $rawMsg);

                    if (!empty($setting) && $setting->firebase_notification == 1) {
                        Helper::sendNotification(
                            $template->title ?? 'Happy Birthday',
                            $message,
                            'student',
                            $item
                        ); 
                    }

                    $response = Helper::sendWhatsappMessage($studentMobile, $message, null);

                    $value = new BirthdayWishes;
                    $value->user_id = Session::get('user_id');
                    $value->session_id = Session::get('session_id');
                    $value->branch_id = Session::get('branch_id');
                    $value->admission_id = $item;
                    $value->role_id = $studentRole;

                    $decode = is_string($response) ? json_decode($response) : (is_array($response) ? (object)$response : $response);

                    if (!isset($decode->status) || $decode->status === false || $decode->status == 'error' || empty($decode)) {
                        $value->status = 1;
                        $value->failed_message = isset($decode->message) ? $decode->message : 'Error sending wish';
                        $value->save();
                        $error++;
                    } else {
                        $value->status = 0;
                        $value->save();
                        $success++;
                    }
                }
            }

            if ($error > 0 && $success == 0) {
                return redirect::to('happy_birthday')->with('error', 'WhatsApp service is experiencing downtime. Some messages failed to send.');
            } elseif ($error > 0 && $success > 0) {
                return redirect::to('happy_birthday')->with('message', "Sent {$success} wishes successfully. {$error} wishes encountered delivery issues.");
            } else {
                return redirect::to('happy_birthday')->with('message', 'Birthday Wishes Sent Successfully.');
            }
        }

        return redirect::to('happy_birthday');
    }
}