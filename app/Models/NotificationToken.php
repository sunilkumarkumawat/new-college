<?php

namespace App\Models;

use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationToken extends Model
{
    use SoftDeletes;
    protected $table = "notification_tokens";
}
