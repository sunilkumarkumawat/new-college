<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\Master\Role;
use App\Models\PermissionManagement;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\AttendanceStatus;
use App\Models\Setting;
use App\Models\WhatsappSetting;
use App\Models\TeacherAttendance;
use App\Models\Master\MessageTemplate;
use App\Models\City;
use App\Models\Master\Branch;
use App\Models\Master\MessageContent;
use App\Jobs\Job;
use Session;
use Hash;
use Str;
use Helper;
use File;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user_dashboard()
    {
        return view('user/usre_dashboard');
    }

    public function userAttendanceView(Request $request)
    {
        $search['name'] = $request->name;
        $search['date'] = !empty($request->date) ? $request->date : date("m");
        $current_date = !empty($request->date) ? $request->date : date("m");
        $curr_yrs = date('Y');
        $curr_mnt = $current_date;
        $data['monthDate'] = date('t', mktime(0, 0, 0, $curr_mnt, 1, $curr_yrs));
        $totel_month_day = $data['monthDate'];

        $all_teachers = User::where('status', 1)
            ->where('role_id', '!=', 3) // Exclude students
            ->orderBy('first_name')
            ->get()
            ->toArray();

        if ($request->isMethod('post')) {
            if (!empty($request->name)) {
                $value = $request->name;
                $all_staff = User::where('status', 1)->where(function ($query) use ($value) {
                    $query->where("first_name", 'like', '%' . $value . '%')
                        ->orWhere("last_name", 'like', '%' . $value . '%')
                        ->orWhere("father_name", 'like', '%' . $value . '%')
                        ->orWhere("mobile", 'like', '%' . $value . '%')
                        ->orWhere("email", 'like', '%' . $value . '%');
                })->get()->toArray();
            }
        }

        $atnrecord = array();
        if (!empty($all_teachers)) {
            foreach ($all_teachers as $key => $staff_record) {
                $atnrecord[$staff_record['id']] = TeacherAttendance::where('teacher_attendance.staff_id', $staff_record['id'])
                    ->whereMonth('teacher_attendance.date', $curr_mnt)
                    ->whereYear('teacher_attendance.date', $curr_yrs)
                    ->groupby('teacher_attendance.date')
                    ->get(['date', 'staff_id', 'attendance_status_id'])
                    ->keyBy('date')
                    ->toArray();
            }
        }
        $AttStatus = AttendanceStatus::get()->keyBy('id')->toArray();
        return view('user/users/attendence', [
            'data' => $atnrecord,
            'all_teachers' => $all_teachers,
            'AttStatus' => $AttStatus,
            'curr_yrs' => $curr_yrs,
            'curr_mnt' => $curr_mnt,
            'totel_month_day' => $totel_month_day,
            'search' => $search
        ]);
    }

    public function addUser(Request $request)
    {
        $BillCounter = BillCounter::where('type', 'User')
            ->where('branch_id', Session::get('branch_id'))
            ->where('session_id', Session::get('session_id'))
            ->first();

        if (empty($BillCounter)) {
            $BillCounter = BillCounter::where('type', 'Teacher')
                ->where('branch_id', Session::get('branch_id'))
                ->where('session_id', Session::get('session_id'))
                ->first();
        }

        $counter = !empty($BillCounter->counter) ? (int)$BillCounter->counter : 0;
        $autoEmployeeId = 'EMP-' . sprintf('%04d', $counter + 1);

        $users = User::all();
        $total_user = count($users);
        $branch = Branch::all();

        if ($request->isMethod('post')) {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile' => 'required',
                'role_id' => 'required',
                'userName' => 'required|unique:users,userName',
                'password' => 'required|same:confirm_password|min:4',
                'confirm_password' => 'required|same:password|min:4',
                'sidebar_id' => 'required',
            ]);

            // Update Bill Counter
            if (!empty($BillCounter)) {
                $BillCounter->counter = $counter + 1;
                $BillCounter->save();
            } else {
                $newCounter = new BillCounter();
                $newCounter->user_id = Session::get('id') ?? 1;
                $newCounter->session_id = Session::get('session_id') ?? 1;
                $newCounter->branch_id = Session::get('branch_id') ?? 1;
                $newCounter->type = 'User';
                $newCounter->counter = $counter + 1;
                $newCounter->save();
            }

            $destinationPath = env('IMAGE_UPLOAD_PATH') . 'profile/';
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $photo = '';
            if ($request->file('photo')) {
                $image = $request->file('photo');
                $photo = time() . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $photo);
            }

            $id_proof_doc = '';
            if ($request->file('id_proof_doc')) {
                $idDoc = $request->file('id_proof_doc');
                $id_proof_doc = 'id_' . time() . uniqid() . '.' . $idDoc->getClientOriginalExtension();
                $idDoc->move($destinationPath, $id_proof_doc);
            }

            $qualification_doc = '';
            if ($request->file('qualification_doc')) {
                $qualDoc = $request->file('qualification_doc');
                $qualification_doc = 'qual_' . time() . uniqid() . '.' . $qualDoc->getClientOriginalExtension();
                $qualDoc->move($destinationPath, $qualification_doc);
            }

            $resume_doc = '';
            if ($request->file('resume_doc')) {
                $resDoc = $request->file('resume_doc');
                $resume_doc = 'res_' . time() . uniqid() . '.' . $resDoc->getClientOriginalExtension();
                $resDoc->move($destinationPath, $resume_doc);
            }

            $other_doc = '';
            if ($request->file('other_doc')) {
                $othDoc = $request->file('other_doc');
                $other_doc = 'oth_' . time() . uniqid() . '.' . $othDoc->getClientOriginalExtension();
                $othDoc->move($destinationPath, $other_doc);
            }

            $add_user = new User;
            $add_user->session_id = Session::get('session_id') ?? 1;
            $add_user->branch_id = !empty($request->branch_id) ? $request->branch_id : (Session::get('branch_id') ?? 1);

            // Staff / Employee Identifiers
            $add_user->UniqueId = !empty($request->UniqueId) ? $request->UniqueId : $autoEmployeeId;
            $add_user->role_id = $request->role_id;
            $add_user->designation = $request->designation;
            $add_user->department = $request->department;
            $add_user->qualification = $request->qualification;
            $add_user->work_experience = $request->work_experience;
            $add_user->joining_date = !empty($request->joining_date) ? $request->joining_date : date('Y-m-d');
            $add_user->contract_type = $request->contract_type;
            $add_user->shift = $request->shift;

            // Personal & Demographic Details
            $add_user->first_name = $request->first_name;
            $add_user->last_name = $request->last_name;
            $add_user->father_name = $request->father_name;
            $add_user->mother_name = $request->mother_name;
            $add_user->spouse_name = $request->spouse_name;
            $add_user->gender = $request->gender;
            $add_user->gender_id = $request->gender_id;
            $add_user->dob = $request->dob;
            $add_user->marital_status = $request->marital_status;
            $add_user->blood_group = $request->blood_group;
            $add_user->aadhaar_no = $request->aadhaar_no;
            $add_user->pan_number = $request->pan_number;
            $add_user->emergency_contact_no = $request->emergency_contact_no;
            $add_user->father_mobile = $request->father_mobile;

            // Contact & Address Details
            $add_user->mobile = $request->mobile;
            $add_user->email = $request->email;
            $add_user->country_id = $request->country_id ?? 1;
            $add_user->state_id = $request->state ?? $request->state_id;
            $add_user->city_id = $request->city ?? $request->city_id;
            $add_user->pincode = $request->pincode;
            $add_user->address = $request->address;
            $add_user->permanent_address = $request->permanent_address;

            // Payroll & Bank Details
            $add_user->salary = $request->salary ?? $request->basic_salary;
            $add_user->basic_salary = $request->basic_salary ?? $request->salary;
            $add_user->bank_name = $request->bank_name;
            $add_user->account_no = $request->account_no;
            $add_user->ifsc_code = $request->ifsc_code;
            $add_user->bank_branch = $request->bank_branch;
            $add_user->epf_no = $request->epf_no;
            $add_user->esi_no = $request->esi_no;

            // Credentials & Documents
            $add_user->userName = $request->userName;
            $add_user->password = Hash::make($request->password);
            $add_user->confirm_password = $request->confirm_password;
            $add_user->image = $photo;
            $add_user->id_proof_doc = $id_proof_doc;
            $add_user->qualification_doc = $qualification_doc;
            $add_user->resume_doc = $resume_doc;
            $add_user->other_doc = $other_doc;
            $add_user->status = 1;

            $add_user->save();
            $user_id = $add_user->id;

            // Permissions
            $sidebar_id = '';
            if (!empty($request->sidebar_id)) {
                $sidebar_id = implode(',', $request->sidebar_id);
            }

            $sidebar_sub_id = null;
            if (!empty($request->sidebar_sub_id)) {
                $sidebar_sub_id = implode(',', $request->sidebar_sub_id);
            }

            $add_pr = new PermissionManagement;
            $add_pr->reg_user_id = $user_id;
            $add_pr->sidebar_id = $sidebar_id;
            $add_pr->sidebar_sub_id = $sidebar_sub_id;
            $add_pr->edit = $request->has('edit') ? 1 : 0;
            $add_pr->deletes = $request->has('delete') ? 1 : 0;
            $add_pr->download = $request->has('download') ? 1 : 0;
            $add_pr->save();

            // Notification / Welcome messaging
            $template = MessageTemplate::Select('message_templates.*', 'message_types.slug')
                ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                ->where('message_types.status', 1)->where('message_types.slug', 'User')->first();
            $branch = Branch::find(Session::get('branch_id'));
            $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
            $role_name = Role::where('id', $request->role_id)->first();

            $arrey1 = array(
                '{#name#}',
                '{#user_name#}',
                '{#password#}',
                '{#email#}',
                '{#role#}',
                '{#address#}',
                '{#mobile_no#}',
                '{#support_email#}',
                '{#school_name#}',
                '{#support_no#}'
            );
            $arrey2 = array(
                $request->first_name . ' ' . $request->last_name,
                $request->userName,
                $request->password,
                $request->email,
                $role_name->name ?? 'Staff',
                $request->address,
                $request->mobile,
                $setting->gmail ?? '',
                $setting->name ?? '',
                $setting->mobile ?? ''
            );

            if (!empty($template) && $template->status != 1) {
                if ($request->email != "") {
                    if (!empty($branch) && $branch->email_srvc != 0) {
                        if ($template->email_status != 0) {
                            $message = str_replace($arrey1, $arrey2, $template->email_content);
                            $emailData = ['email' => $request->email, 'data' => $message, 'subject' => $template->title];
                            Helper::sendMail('email_print.template_print', $emailData);
                        }
                    }
                }
                if (!empty($branch) && $branch->whatsapp_srvc != 0) {
                    if ($request->mobile != "") {
                        if ($template->whatsapp_status != 0) {
                            $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                            Helper::sendWhatsappMessage($request->mobile, $whatsapp);
                        }
                    }
                }
                if (!empty($branch) && $branch->sms_srvc != 0) {
                    if ($request->mobile != "") {
                        if ($template->sms_status != 0) {
                            $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                            Helper::SendMessage($request->mobile, $sms);
                        }
                    }
                }
            }

            return redirect::to('viewUser')->with('message', 'User created successfully with role ' . ($role_name->name ?? ''));
        }

        return view('user.users.add', [
            'branch' => $branch,
            'total_user' => $total_user,
            'autoEmployeeId' => $autoEmployeeId
        ]);
    }

    public function viewUser(Request $request)
    {
        $search['id'] = $request->id ?? '';
        $search['UniqueId'] = $request->UniqueId ?? '';
        $search['name'] = $request->name ?? '';
        $search['role_id'] = $request->role_id ?? '';
        $search['department'] = $request->department ?? '';
        $search['designation'] = $request->designation ?? '';
        $search['mobile'] = $request->mobile ?? '';
        $search['email'] = $request->email ?? '';
        $search['joining_date'] = $request->joining_date ?? '';
        $search['status'] = $request->status ?? '1';
        $search['per_page'] = $request->per_page ?? '25';
        $search['branch_id'] = $request->branch_id ?? '';

        $query = User::with(['roleName:id,name', 'Branch:id,branch_name']);

        if (Session::get('role_id') > 1) {
            $query->where('branch_id', Session::get('branch_id'));
        } elseif (!empty(Session::get('admin_branch_id'))) {
            $query->where('branch_id', Session::get('admin_branch_id'));
        }

        if (!empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        if (!empty($request->id)) {
            $query->where('id', $request->id);
        }

        if (!empty($request->UniqueId)) {
            $query->where('UniqueId', 'like', '%' . trim($request->UniqueId) . '%');
        }

        if (!empty($request->name)) {
            $val = trim($request->name);
            $query->where(function ($q) use ($val) {
                $q->where('first_name', 'like', '%' . $val . '%')
                    ->orWhere('last_name', 'like', '%' . $val . '%')
                    ->orWhere('userName', 'like', '%' . $val . '%')
                    ->orWhere('father_name', 'like', '%' . $val . '%');
            });
        }

        if (!empty($request->role_id)) {
            $query->where('role_id', $request->role_id);
        }

        if (!empty($request->department)) {
            $query->where('department', 'like', '%' . trim($request->department) . '%');
        }

        if (!empty($request->designation)) {
            $query->where('designation', 'like', '%' . trim($request->designation) . '%');
        }

        if (!empty($request->mobile)) {
            $query->where('mobile', 'like', '%' . trim($request->mobile) . '%');
        }

        if (!empty($request->email)) {
            $query->where('email', 'like', '%' . trim($request->email) . '%');
        }

        if (!empty($request->joining_date)) {
            $query->where('joining_date', 'like', '%' . trim($request->joining_date) . '%');
        }

        if ($request->has('status')) {
            if ($request->status !== '' && $request->status !== 'all') {
                $query->where('status', (int)$request->status);
            }
        } else {
            $query->where('status', 1);
        }

        $perPage = $request->get('per_page', 25);
        if ($perPage === 'all' || $perPage == -1) {
            $perPage = 1000;
        } else {
            $perPage = (int) $perPage;
            if ($perPage <= 0) $perPage = 25;
        }

        $all_user = $query->orderBy('id', 'DESC')
            ->paginate($perPage)
            ->appends($request->all());

        if ($request->ajax()) {
            $tbodyHtml = view('user.users.table_rows', [
                'data' => $all_user,
                'search' => $search
            ])->render();

            $paginationHtml = view('user.users.pagination', [
                'data' => $all_user
            ])->render();

            return response()->json([
                'status' => 'success',
                'html' => $tbodyHtml,
                'pagination' => $paginationHtml,
                'total' => $all_user->total(),
                'from' => $all_user->firstItem() ?? 0,
                'to' => $all_user->lastItem() ?? 0,
                'count' => $all_user->count()
            ]);
        }

        $roles = Role::orderBy('id', 'ASC')->get(['id', 'name']);
        $departments = User::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');

        return view('user.users.view', [
            'data' => $all_user,
            'search' => $search,
            'roles' => $roles,
            'departments' => $departments
        ]);
    }

    public function editUser(Request $request, $id)
    {
        $branch = Branch::all();
        $data = User::find($id);
        $getcitie = City::where('state_id', $data['state_id'])->get();
        $add_pr = PermissionManagement::where('reg_user_id', $id)->first();

        if ($request->isMethod('post')) {
            $destinationPath = env('IMAGE_UPLOAD_PATH') . 'profile/';
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            if ($request->file('photo')) {
                $image = $request->file('photo');
                $photo = time() . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($destinationPath, $photo);
                if (!empty($data->image) && File::exists($destinationPath . $data->image)) {
                    File::delete($destinationPath . $data->image);
                }
                $data->image = $photo;
            }

            if ($request->file('id_proof_doc')) {
                $idDoc = $request->file('id_proof_doc');
                $id_proof_doc = 'id_' . time() . uniqid() . '.' . $idDoc->getClientOriginalExtension();
                $idDoc->move($destinationPath, $id_proof_doc);
                $data->id_proof_doc = $id_proof_doc;
            }

            if ($request->file('qualification_doc')) {
                $qualDoc = $request->file('qualification_doc');
                $qualification_doc = 'qual_' . time() . uniqid() . '.' . $qualDoc->getClientOriginalExtension();
                $qualDoc->move($destinationPath, $qualification_doc);
                $data->qualification_doc = $qualification_doc;
            }

            if ($request->file('resume_doc')) {
                $resDoc = $request->file('resume_doc');
                $resume_doc = 'res_' . time() . uniqid() . '.' . $resDoc->getClientOriginalExtension();
                $resDoc->move($destinationPath, $resume_doc);
                $data->resume_doc = $resume_doc;
            }

            $data->session_id = Session::get('session_id') ?? 1;
            if (!empty($request->branch_id)) {
                $data->branch_id = $request->branch_id;
            } else {
                $data->branch_id = Session::get('branch_id') ?? 1;
            }

            if (!empty($request->UniqueId)) {
                $data->UniqueId = $request->UniqueId;
            }
            if (!empty($request->role_id)) {
                $data->role_id = $request->role_id;
            }

            $data->designation = $request->designation;
            $data->department = $request->department;
            $data->qualification = $request->qualification;
            $data->work_experience = $request->work_experience;
            if (!empty($request->joining_date)) {
                $data->joining_date = $request->joining_date;
            }
            $data->contract_type = $request->contract_type;
            $data->shift = $request->shift;

            $data->userName = $request->userName;
            $data->first_name = $request->first_name;
            $data->last_name = $request->last_name;
            $data->father_name = $request->father_name;
            $data->mother_name = $request->mother_name;
            $data->spouse_name = $request->spouse_name;
            $data->gender = $request->gender;
            $data->gender_id = $request->gender_id;
            $data->dob = $request->dob;
            $data->marital_status = $request->marital_status;
            $data->blood_group = $request->blood_group;
            $data->aadhaar_no = $request->aadhaar_no;
            $data->pan_number = $request->pan_number;
            $data->emergency_contact_no = $request->emergency_contact_no;
            $data->father_mobile = $request->father_mobile;

            $data->city_id = $request->city ?? $request->city_id;
            $data->country_id = $request->country_id ?? 1;
            $data->state_id = $request->state ?? $request->state_id;
            $data->pincode = $request->pincode;
            $data->mobile = $request->mobile;
            $data->address = $request->address;
            $data->permanent_address = $request->permanent_address;
            $data->email = $request->email;

            if (!empty($request->password)) {
                $data->password = Hash::make($request->password);
                $data->confirm_password = $request->confirm_password ?? $request->password;
            }

            $data->salary = $request->salary ?? $request->basic_salary;
            $data->basic_salary = $request->basic_salary ?? $request->salary;
            $data->bank_name = $request->bank_name;
            $data->account_no = $request->account_no;
            $data->ifsc_code = $request->ifsc_code;
            $data->bank_branch = $request->bank_branch;
            $data->epf_no = $request->epf_no;
            $data->esi_no = $request->esi_no;

            $data->save();

            $sidebar_id = '';
            if (!empty($request->sidebar_id)) {
                $sidebar_id = implode(',', $request->sidebar_id);
            }

            $sidebar_sub_id = null;
            if (!empty($request->sidebar_sub_id)) {
                $sidebar_sub_id = implode(',', $request->sidebar_sub_id);
            }

            if ($add_pr == null) {
                $add_pr = new PermissionManagement;
            }
            $add_pr->reg_user_id = $id;
            $add_pr->sidebar_id = $sidebar_id;
            $add_pr->sidebar_sub_id = $sidebar_sub_id;
            $add_pr->edit = $request->has('edit') ? 1 : 0;
            $add_pr->deletes = $request->has('delete') ? 1 : 0;
            $add_pr->download = $request->has('download') ? 1 : 0;
            $add_pr->save();

            return redirect::to('viewUser')->with('message', 'User Updated Successfully.');
        }

        return view('user.users.edit', [
            'data' => $data,
            'add_pr' => $add_pr,
            'branch' => $branch,
            'getcitie' => $getcitie
        ]);
    }

    public function deleteUser(Request $request)
    {
        $id = $request->delete_id;
        $user = User::find($id);
        if (!empty($user)) {
            $destinationPath = env('IMAGE_UPLOAD_PATH') . 'profile/';
            if (!empty($user->image) && File::exists($destinationPath . $user->image)) {
                File::delete($destinationPath . $user->image);
            }
            $user->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully.'
            ]);
        }
        return redirect::to('viewUser')->with('message', 'User Deleted Successfully.');
    }

    public function userStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status_id;
        User::where('id', $id)->update(['status' => $status]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Status changed successfully.',
                'new_status' => $status
            ]);
        }
        return redirect::to('viewUser')->with('message', 'Status Changed Successfully.');
    }

    public function userSidePer(Request $request)
    {
        $sidebar_id = Role::where('id', $request->role_id)->pluck('sidebar_id')->first();
        echo $sidebar_id;
    }

    public function quickAddRole(Request $request)
    {
        $request->validate(['name' => 'required']);
        $role = new Role();
        $role->user_id = Session::get('id') ?? 1;
        $role->session_id = Session::get('session_id') ?? 1;
        $role->branch_id = Session::get('branch_id') ?? 1;
        $role->name = $request->name;
        $role->save();
        return response()->json(['status' => 'success', 'id' => $role->id, 'name' => $role->name]);
    }
}