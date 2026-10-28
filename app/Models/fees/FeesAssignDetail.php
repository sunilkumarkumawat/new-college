<?php

namespace App\Models\fees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FeesAssignDetail extends Model
{
        use SoftDeletes;
	protected $table = "fees_assign_details"; //table name
	protected $guarded = [];

    public function feesGroup()
    {
        return $this->belongsTo('App\Models\FeesGroup', 'fees_group_id');
    }
}
