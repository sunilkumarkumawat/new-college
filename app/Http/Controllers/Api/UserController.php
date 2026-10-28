<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\User;
use App\Models\NotificationToken;

class UserController extends BaseController
{
    public function saveDeviceToken(Request $request)
    {
        $userId      = $request->input("userId");
        $modelName   = $request->input("model");
        $deviceToken = $request->input("device_token");

        if (empty($userId) || empty($modelName) || empty($deviceToken)) {
            return response()->json([
                "status" => false,
                "error"  => "userId, model, and device_token are required"
            ], 400);
        }

        $idMap = [
            "Admission" => "admission_id",
            "Student"   => "admission_id",
            "User"      => "user_id",
            "AppUser"   => Admission::whereKey($userId)->exists() ? "admission_id" : "user_id",
        ];

        if (!isset($idMap[$modelName])) {
            return response()->json(["status" => false, "error" => "Invalid model name"], 400);
        }

        $token = NotificationToken::withTrashed()->where($idMap[$modelName], $userId)->first();
        $message = "Device token updated successfully";
        if (!$token) {
            $token = new NotificationToken();
            $message = "Device token saved successfully";
        }
        $token->{$idMap[$modelName]} = $userId;
        $tokenOwner = $idMap[$modelName] === "admission_id"
            ? Admission::find($userId)
            : User::find($userId);
        $token->attendance_unique_id = trim((string) ($tokenOwner->attendance_unique_id ?? "")) ?: null;
        $token->entity_type = $idMap[$modelName] === "admission_id" ? "student" : "teacher";
        $token->branch_id = $tokenOwner->branch_id ?? null;
        $token->session_id = $tokenOwner->session_id ?? null;
        $token->device_token = $deviceToken;
        $token->platform = "android";
        $token->deleted_at = null;
        $token->updated_at = now();
        $token->save();

        return response()->json([
            "status"  => true,
            "message" => $message
        ]);
    }
}
