<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\Admission;
use App\Models\ClassType;
use App\Models\hostel\Hostel;
use App\Models\hostel\HostelBuilding;
use App\Models\BillCounter;
use App\Models\hostel\HostelFloor;
use App\Models\hostel\HostelRoom;
use App\Models\hostel\HostelBed;
use App\Models\hostel\HostelAssign;
use App\Models\Master\MessageTemplate;
use App\Models\Master\Branch;
use App\Models\Setting;
use App\Models\Expense;
use App\Models\Remark;
use Session;
use Hash;
use Helper;
use File;
use Str;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{

            public function expenseAdd(Request $request){
                if ($request->isMethod('post')) {
                    $names = $request->name ?? [];
                    if (empty($names)) {
                        return redirect()->back()->with('error', 'Please add at least one expense item.');
                    }

                    $template = MessageTemplate::select('message_templates.*', 'message_types.slug')
                        ->leftJoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                        ->where('message_types.status', 1)->where('message_types.slug', 'expences')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->first();
                    $admin = User::where('id', Session::get('id'))->first();
                    $lastExpense = null;

                    foreach ($names as $count => $expenseName) {
                        if (!empty($expenseName)) {
                            $attachment = '';
                            if ($request->hasFile('attachment') && isset($request->file('attachment')[$count])) {
                                $image = $request->file('attachment')[$count];
                                $attachment = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                                $destinationPath = env('IMAGE_UPLOAD_PATH') . 'expense';
                                $image->move($destinationPath, $attachment);
                            }

                            $expense = new Expense;
                            $expense->user_id = $request->role[$count] ?? Session::get('id');
                            $expense->session_id = Session::get('session_id');
                            $expense->branch_id = Session::get('branch_id');
                            $expense->name = $expenseName;
                            $expense->date = $request->date[$count] ?? date('Y-m-d');
                            $expense->quantity = $request->quantity[$count] ?? 1;
                            $expense->rate = $request->rate[$count] ?? 0;
                            $expense->amount = $request->amount[$count] ?? ($expense->quantity * $expense->rate);
                            $expense->payment_mode_id = $request->payment_mode_id[$count] ?? 1;
                            $expense->total_amt = $request->total_amt ?? $expense->amount;
                            $expense->attachment = $attachment;
                            $expense->description = $request->description[$count] ?? '';
                            $expense->save();
                            $lastExpense = $expense;

                            if (!empty($template) && $template->status != 1 && $admin) {
                                $arrey1 = array(
                                    '{#name#}',
                                    '{#expenses_name#}',
                                    '{#description#}',
                                    '{#quantity#}',
                                    '{#amount#}',
                                    '{#total_amount#}',
                                    '{#date#}',
                                    '{#support_no#}',
                                    '{#school_name#}'
                                );
                                $arrey2 = array(
                                    $admin->first_name . " " . $admin->last_name,
                                    $expenseName,
                                    $request->description[$count] ?? '',
                                    $request->quantity[$count] ?? 1,
                                    $request->rate[$count] ?? 0,
                                    $request->amount[$count] ?? 0,
                                    date('d-m-Y', strtotime($request->date[$count] ?? date('Y-m-d'))),
                                    $setting->mobile ?? '',
                                    $setting->name ?? ''
                                );

                                if ($admin->email != "" && $branch && $branch->email_srvc != 0 && $template->email_status != 0) {
                                    $message = str_replace($arrey1, $arrey2, $template->email_content);
                                    $emailData = ['email' => $admin->email, 'data' => $message, 'subject' => $template->title];
                                    Helper::sendMail('email_print.template_print', $emailData);
                                }
                                if ($admin->mobile != "" && $branch && $branch->whatsapp_srvc != 0 && $template->whatsapp_status != 0) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                                    Helper::sendWhatsappMessage($admin->mobile, $whatsapp);
                                }
                                if ($admin->mobile != "" && $branch && $branch->sms_srvc != 0 && $template->sms_status != 0) {
                                    $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                                    Helper::SendMessage($admin->mobile, $sms);
                                }
                            }
                        }
                    }

                    if ($lastExpense) {
                        $url = '/expensePrint/' . $lastExpense->id;
                        ?>
                        <script type="text/javascript">
                            window.open("<?= $url ?>", "_blank");
                        </script>
                        <?php
                    }

                    return redirect::to('expenseView')->with('message', 'Expense Added Successfully !');
                }
                return view('expense.add');
            }
        
            public function expenseView(Request $request){
                $search = [
                    'id' => $request->id ?? '',
                    'session_id' => $request->has('session_id') ? $request->session_id : Session::get('session_id'),
                    'name' => $request->name ?? '',
                    'user_name' => $request->user_name ?? '',
                    'from_date' => $request->from_date ?? '',
                    'to_date' => $request->to_date ?? '',
                    'payment_mode_id' => $request->payment_mode_id ?? '',
                    'amount' => $request->amount ?? '',
                    'per_page' => $request->per_page ?? '25',
                ];

                $query = Expense::select(
                        'expenses.*',
                        'users.first_name as user_first_name',
                        'users.last_name as user_last_name',
                        'payment_modes.name as payment_mode_name'
                    )
                    ->leftJoin('users', 'users.id', '=', 'expenses.user_id')
                    ->leftJoin('payment_modes', 'payment_modes.id', '=', 'expenses.payment_mode_id')
                    ->whereNull('expenses.deleted_at');

                if (!empty($search['id'])) {
                    $query->where('expenses.id', $search['id']);
                }

                if (!empty($search['session_id'])) {
                    $query->where('expenses.session_id', $search['session_id']);
                }

                if (Session::get('role_id') > 1) {
                    $query->where('expenses.branch_id', Session::get('branch_id'));
                }

                if (!empty(Session::get('admin_branch_id'))) {
                    $query->where('expenses.branch_id', Session::get('admin_branch_id'));
                }

                if (!empty($search['name'])) {
                    $val = trim($search['name']);
                    $query->where(function($q) use ($val) {
                        $q->where('expenses.name', 'LIKE', '%' . $val . '%')
                          ->orWhere('expenses.description', 'LIKE', '%' . $val . '%');
                    });
                }

                if (!empty($search['user_name'])) {
                    $uVal = trim($search['user_name']);
                    $query->where(function($q) use ($uVal) {
                        $q->where('users.first_name', 'LIKE', '%' . $uVal . '%')
                          ->orWhere('users.last_name', 'LIKE', '%' . $uVal . '%');
                    });
                }

                if (!empty($search['from_date']) && !empty($search['to_date'])) {
                    $query->whereBetween('expenses.date', [$search['from_date'], $search['to_date']]);
                } elseif (!empty($search['from_date'])) {
                    $query->whereDate('expenses.date', '>=', $search['from_date']);
                } elseif (!empty($search['to_date'])) {
                    $query->whereDate('expenses.date', '<=', $search['to_date']);
                }

                if (!empty($search['payment_mode_id'])) {
                    $query->where('expenses.payment_mode_id', $search['payment_mode_id']);
                }

                if (!empty($search['amount'])) {
                    $query->where('expenses.amount', 'LIKE', '%' . trim($search['amount']) . '%');
                }

                // Metric aggregates
                $totalAmount = (clone $query)->sum('expenses.amount');
                $totalCount = (clone $query)->count();
                $cashAmount = (clone $query)->where('expenses.payment_mode_id', 1)->sum('expenses.amount');
                $digitalAmount = (clone $query)->where('expenses.payment_mode_id', '!=', 1)->sum('expenses.amount');

                $perPage = $request->get('per_page', 25);
                if ($perPage === 'all' || $perPage == -1) {
                    $perPage = 1000;
                } else {
                    $perPage = (int) $perPage;
                    if ($perPage <= 0) $perPage = 25;
                }

                $data = $query->orderBy('expenses.id', 'DESC')->paginate($perPage)->appends($request->all());
                $paymentModes = \DB::table('payment_modes')->whereNull('deleted_at')->get();

                if ($request->ajax()) {
                    $tbodyHtml = view('expense.table_rows', [
                        'data' => $data,
                        'search' => $search
                    ])->render();

                    $paginationHtml = view('expense.pagination', [
                        'data' => $data
                    ])->render();

                    return response()->json([
                        'status' => 'success',
                        'html' => $tbodyHtml,
                        'pagination' => $paginationHtml,
                        'total' => $data->total(),
                        'from' => $data->firstItem() ?? 0,
                        'to' => $data->lastItem() ?? 0,
                        'count' => $data->count(),
                        'total_amount' => number_format($totalAmount, 2),
                        'total_count' => number_format($totalCount),
                        'cash_amount' => number_format($cashAmount, 2),
                        'digital_amount' => number_format($digitalAmount, 2)
                    ]);
                }

                return view('expense.view', [
                    'data' => $data,
                    'search' => $search,
                    'paymentModes' => $paymentModes,
                    'totalAmount' => $totalAmount,
                    'totalCount' => $totalCount,
                    'cashAmount' => $cashAmount,
                    'digitalAmount' => $digitalAmount
                ]);
            }

            public function expenseEdit(Request $request, $id)
            {
                $data = Expense::find($id);
                if ($request->isMethod('post')) {
                    $request->validate([
                        //  'name' => 'required',    
                        //  'date' => 'required',    
                        //  'amount' => 'required',    
                        //  'total_amt' => 'required',    
        
                    ]);
                    if ($request->file('attachment')) {
                        $image = $request->file('attachment');
                        $path = $image->getRealPath();
                        $attachment = time() . uniqid() . $image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH') . 'expense';
                        $image->move($destinationPath, $attachment);
                        if (File::exists(env('IMAGE_UPLOAD_PATH') . 'expense/' . $data->attachment)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'expense/' . $data->attachment);
                        }
                        $data->attachment = $attachment;
                    }
                    $data->user_id = $request->role ?? '';
                    $data->session_id = Session::get('session_id');
                    $data->branch_id = Session::get('branch_id');
                    // $data->role_id = $request->role;
                    $data->name = $request->name ?? '';
                    $data->date = $request->date;
                    $data->quantity = $request->quantity;
                    $data->rate = $request->rate;
                    $data->amount = $request->amount;
                    $data->payment_mode_id = $request->payment_mode_id;
                    $data->total_amt = $request->total_amt;
                    $data->description = $request->description;
                    $data->save();
                    return redirect::to('expenseView')->with('message', 'Expense Updated Successfully !');
                }
                return view('expense.edit', ['data' => $data]);
            }
            public function expenseDelete(Request $request){
                $id = $request->delete_id;
                $bed = Expense::find($id);
                if (File::exists(env('IMAGE_UPLOAD_PATH') . 'expense/' . $bed->attachment)) {
                    File::delete(env('IMAGE_UPLOAD_PATH') . 'expense/' . $bed->attachment);
                }
                $bed->delete();
                return redirect::to('expenseView')->with('message', 'Expense Deleted Successfully !');
            }
            public function expensePrint($id){
                $data = Expense::find($id);
                return view('print_file.expense.expense_print', ['data' => $data]);
            }
            
            public function remarkMail(Request $request){
                $data = Remark::select('registration_remarks.*', 'Student.*', 'ClassTypes.name as class')
                    ->leftjoin('students as Student', 'Student.id', 'registration_remarks.student_id')
                    ->leftjoin('class_types as ClassTypes', 'Student.class_type_id', 'ClassTypes.id')
                    ->where('registration_remarks.date', date('Y-m-d'))->get();
                $admin = User::find('1');
                if ($data->count() > 0) {
                    $emailData = ['email' => $admin->gmail, 'data' => $data, 'subject' => 'Remark Recole.'];
                    Helper::sendmail('print_file.remark_mail', $emailData);
                }
                return view('print_file.remark_mail', ['data' => $data]);
            }


}
