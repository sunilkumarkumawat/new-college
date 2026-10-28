<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Agent extends Model
{
    use SoftDeletes;

    protected $table = 'agents';
    protected $guarded = [];

    public function Admissions()
    {
        return $this->hasMany('App\Models\Admission', 'agent_id');
    }

    public function Enquiries()
    {
        return $this->hasMany('App\Models\Enquiry', 'agent_id');
    }

    /*public static function getActiveAgents()
    {
        $branchId = Session::get('admin_branch_id') ?: Session::get('branch_id');
        $query = self::where('status', 1);
        if (!empty($branchId)) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        return $query->orderBy('name', 'ASC')->get();
    }*/
    
    public static function getActiveAgents()
{
    return self::where('status', 1)
        ->orderBy('name', 'ASC')
        ->get();
}
}
