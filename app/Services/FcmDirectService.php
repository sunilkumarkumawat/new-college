<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmDirectService
{
    private const ATTENDANCE_ANDROID_CHANNELS = [
        'attendance_in' => ['channel_id' => 'attendance_in', 'sound' => 'attendance_in'],
        'attendance_out' => ['channel_id' => 'attendance_out', 'sound' => 'attendance_out'],
        'attendance_absent_v2' => ['channel_id' => 'attendance_absent_v2', 'sound' => 'attendance_absent'],
        'attendance_absent' => ['channel_id' => 'attendance_absent_v2', 'sound' => 'attendance_absent'],
        'default' => ['channel_id' => 'default', 'sound' => 'default'],
    ];

    public function send(
        string $fcmToken,
        array $data,
        string $priority = 'high',
        ?string $title = null,
        ?string $body = null,
        ?string $image = null
    ): array {
        try {
            $serviceAccountPath = storage_path('app/firebase/service-account.json');
            if (!file_exists($serviceAccountPath)) {
                return ['success' => false, 'error' => 'JSON file missing at: ' . $serviceAccountPath];
            }

            $credentialsArray = json_decode(file_get_contents($serviceAccountPath), true);
            $projectId = trim((string) ($credentialsArray['project_id'] ?? ''));
            if ($projectId === '') {
                return ['success' => false, 'error' => 'Firebase project_id is missing in service-account.json.'];
            }

            $accessToken = Cache::remember('fcm_access_token_' . $projectId, 3000, function () use ($credentialsArray) {
                $credentials = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    $credentialsArray
                );
                $tokenData = $credentials->fetchAuthToken();
                if (isset($tokenData['error']) || empty($tokenData['access_token'])) {
                    throw new \Exception('Token error: ' . json_encode($tokenData));
                }
                return $tokenData['access_token'];
            });

            $payload = ['message' => ['token' => $fcmToken, 'data' => $data]];
            if ($title || $body || $image) {
                $payload['message']['notification'] = array_filter([
                    'title' => $title,
                    'body' => $body,
                    'image' => $image,
                ]);
            }

            $notificationType = $this->resolveNotificationType($data);
            $payload['message']['android'] = [
                'priority' => $priority === 'high' ? 'high' : 'normal',
                // Attendance alerts are time-sensitive. Do not let an outdated
                // check-in/out notification surface much later after device wake.
                'ttl' => '60s',
                'direct_boot_ok' => true,
            ];
            $payload['message']['apns'] = [
                'headers' => ['apns-priority' => $priority === 'high' ? '10' : '5'],
            ];

            $androidNotification = $this->buildAndroidNotification($notificationType);
            if (!empty($androidNotification)) {
                $payload['message']['android']['notification'] = $androidNotification;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            $responseBody = $response->body();
            $decoded = json_decode($responseBody, true);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'body' => $decoded,
                    'message_id' => $decoded['name'] ?? null,
                ];
            }

            $error = $decoded['error'] ?? null;
            $firebaseDetails = [
                'status' => $response->status(),
                'message' => $error['message'] ?? 'FCM Send Failed',
                'code' => $error['code'] ?? null,
                'details' => $error['details'] ?? [],
                'raw_body' => $responseBody,
            ];
            Log::error('FCM Error', $firebaseDetails);

            return [
                'success' => false,
                'error' => $firebaseDetails['message'],
                'firebase_details' => $firebaseDetails,
            ];
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function resolveNotificationType(array $data): ?string
    {
        $notificationType = $data['notification_type']
            ?? $data['notificationType']
            ?? $data['channel_id']
            ?? $data['channelId']
            ?? $data['type']
            ?? null;

        if (!is_string($notificationType)) {
            return null;
        }

        $notificationType = str_replace([' ', '-'], '_', strtolower(trim($notificationType)));
        return $notificationType !== '' ? $notificationType : null;
    }

    private function buildAndroidNotification(?string $notificationType): array
    {
        if (empty($notificationType) || !isset(self::ATTENDANCE_ANDROID_CHANNELS[$notificationType])) {
            return [];
        }
        return array_filter(self::ATTENDANCE_ANDROID_CHANNELS[$notificationType]);
    }
}
