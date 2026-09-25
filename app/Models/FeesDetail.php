<?php

namespace App\Models;
use Session;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FeesDetail extends Model
{
        use SoftDeletes;
	protected $table = "fees_detail"; //table name

	public function Admission()
    {
        return $this->belongsTo('App\Models\Admission','admission_id');
    }
    public function ClassTypes()
    {
        return $this->belongsTo('App\Models\ClassType','class_type_id');
    }
     public function Month()
    {
        return $this->belongsTo('App\Models\Month','month_id');
    }
    
     public function PaymentMode()
    {
        return $this->belongsTo('App\Models\Master\PaymentMode','payment_mode_id');
    }
    public function FeesCollect()
    {
        return $this->belongsTo('App\Models\FeesCollect','fees_collect_id');
    }   
 
    public function feesGroup()
    {
        return $this->belongsTo('App\Models\FeesGroup', 'fees_group_id');
    }

    public function FeesGroup()
    {
        return $this->belongsTo('App\Models\FeesGroup', 'fees_group_id');
    }

    public function HostelAssign()
    {
        return $this->belongsTo('App\Models\hostel\HostelAssign','hostel_assign_id');
    }  
     
    public static function totalCollection(){
        $data = FeesDetail::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('fees_type',0)->whereIn('status', [0, 1])
        ->selectRaw('SUM(paid_amount + installment_fine) as total')
        ->value('total');
        return $data;
    }
    
    public static function todayCollection(){
       $data = FeesDetail::where('session_id', Session::get('session_id'))
        ->where('branch_id', Session::get('branch_id'))
        ->where('fees_type', 0)
        ->where('date', date('Y-m-d'))
        ->whereIn('status', [0, 1])
        ->selectRaw('SUM(paid_amount + installment_fine) as total')
        ->value('total'); 
        return $data;
    }

    public static function thisMonthCollection(){
        $data=FeesDetail::where('session_id',Session::get('session_id'))
		 ->where('branch_id',Session::get('branch_id'))->whereMonth('date',date('m'))->whereIn('status',[0,1])->sum('paid_amount');
        return $data;
    }

    public static function thisSessionCollection(){
        $data=FeesDetail::where('session_id',Session::get('session_id'))
		 ->where('branch_id',Session::get('branch_id'))->sum('paid_amount');
        return $data;
    }
    
    public static function countHostelTotelCollection(){
        $data=FeesDetail::where('session_id',Session::get('session_id'))
		 ->where('branch_id',Session::get('branch_id'))->where('fees_type',1)->sum('total_amount');
        if($data){
            return $data;
        }
        return 0;
    } 
    public static function countHostelTodayCollection(){
        $data=FeesDetail::whereDate('date',date('Y-m-d'))->where('session_id',Session::get('session_id'))
		 ->where('branch_id',Session::get('branch_id'))->where('fees_type',1)->sum('paid_amount');
        if($data){
            return $data;
        }
        return 0;
    }    

    public static function LibraryCollection(){
        $data=FeesDetail::where('session_id',Session::get('session_id'))
		                  ->where('branch_id',Session::get('branch_id'))->where('fees_type',2);
            $data1 = clone  $data;   
            $data2 = clone $data;   
            $data3 = clone $data;   
            $data4 = clone $data;   
            $data5 = clone $data;   
            $data6 = clone $data;   
            $count['today'] = $data1->whereDate('date','=',date('Y-m-d'))->get()->sum('paid_amount');
            $count['yesterday'] = $data2->whereDate('date','=',date('Y-m-d', strtotime("-1 day")))->get()->sum('paid_amount');
            $count['this_month'] = $data3->whereYear('date',date('Y-m-d'))->whereMonth('date',date('m'))->get()->sum('paid_amount');
            $count['last_month'] = $data4->whereYear('date', '=',date('Y-m-d'))->whereMonth('date', '=',date('m', strtotime("-1 month")))->get()->sum('paid_amount');
            $count['collection'] = $data5->get()->sum('paid_amount');
            $count['due_amount'] = Invoice::where('invoice_type',0)->sum('due_amount');
         return $count;
    } 
    public static function HostelCollection(){
        $data=FeesDetail::where('session_id',Session::get('session_id'))
		                  ->where('branch_id',Session::get('branch_id'))->where('fees_type',1);
            $data1 = clone  $data;   
            $data2 = clone $data;   
            $data3 = clone $data;   
            $data4 = clone $data;   
            $data5 = clone $data;   
            $data6 = clone $data;   
            $count['today'] = $data1->whereDate('date','=',date('Y-m-d'))->get()->sum('paid_amount');
            $count['yesterday'] = $data2->whereDate('date','=',date('Y-m-d', strtotime("-1 day")))->get()->sum('paid_amount');
            $count['this_month'] = $data3->whereYear('date',date('Y-m-d'))->whereMonth('date',date('m'))->get()->sum('paid_amount');
            $count['last_month'] = $data4->whereYear('date', '=',date('Y-m-d'))->whereMonth('date', '=',date('m', strtotime("-1 month")))->get()->sum('paid_amount');
            $count['collection'] = $data5->get()->sum('paid_amount');
            $count['due_amount'] = Invoice::where('invoice_type',1)->sum('due_amount');
         return $count;
    } 
}