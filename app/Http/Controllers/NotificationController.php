<?php

namespace App\Http\Controllers;

use App\Models\NotificationToken;
use Illuminate\Http\Request;
use Session;
use Helper;

class NotificationController extends Controller
{
    public function notification(Request $request)
    {
        $tokens = NotificationToken::where("platform", "android")
            ->pluck("device_token")
            ->filter()
            ->unique()
            ->values()
            ->all();

        $result = Helper::sendNotification(
            "Hello World!",
            "This is a notification with image and icon.",
            "firebase",
            $tokens,
            "https://demo3.rusoft.in/schoolimage/setting/left_logo/17502424226852947652a461748931241_xbv2FRwzun.jpeg",
            "https://rukmanisoftware.com/public/assets/img/header-logo.png",
            ["customKey" => "customValue"]
        );

        return response()->json($result, !empty($result["success"]) ? 200 : 502);
    }
}
