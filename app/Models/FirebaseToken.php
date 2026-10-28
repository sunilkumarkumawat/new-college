<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirebaseToken extends Model
{
    use SoftDeletes;

    protected $table = "notification_tokens";

    protected $fillable = [
        "branch_id",
        "session_id",
        "user_id",
        "admission_id",
        "attendance_unique_id",
        "entity_type",
        "device_token",
        "platform",
    ];
}
