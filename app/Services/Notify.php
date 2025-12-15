<?php

namespace App\Services;

use App\Models\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
// google fcm

use Illuminate\Support\Facades\Log;

class Notifiy
{
    // public function create(array $data): Notification
    // {
    //     return Notification::create($data);
    // }

    // public function markAsSeen(Notification $notification): bool
    // {
    //     $notification->seen = true;

    //     return $notification->save();
    // }

    // fcm token save

    public function token(array $data)
    {
        // dd($data);
        // $title = 'FCM';
        // $body = "Test notification from FCM";
        // $data = "Data from FCM";

        $accessToken = $this->getAccessToken();

        // First check if token exists in cache

        $tokens = [];
        if (! empty($data['web_token'])) {
            $tokens['web'] = $data['web_token'];
        }
        if (! empty($data['mob_token'])) {
            $tokens['mob'] = $data['mob_token'];
        }

        foreach ($tokens as $type => $token) {

            log::info('Preparing to send FCM notification', ['tokens' => $token, 'type' => $type]);

            // $payload = [
            //     'message' => [
            //         'token' => $token,
            //         'notification' => [
            //             'title' => $data['title'],
            //             'body' => $data['body'],
            //         ],
            //         'data' => [
            //             'job_id' => (string) $data['id'],
            //             'type' => 'job_update',
            //             'link' => $data['link'] ?? 'https://onstru.com/',
            //         ],
            //         'webpush' => [
            //             'notification' => [
            //                 'icon' => 'https://onstru.com/assets/images/Logo_Admin.png',
            //             ],
            //             'fcm_options' => [
            //                 'link' => $data['link'] ?? 'https://onstru.com/',
            //             ],
            //         ],
            //     ],
            // ];

            if ($type === 'web') {
                log::info('Preparing web notification payload');
                // Log::info("Sending web notification via FCM to token: {$token}");
                // $payload = [
                //     'message' => [
                //         'token' => $token,
                //         'data' => [   // Only send data
                //             'title' => $data['title'],
                //             'body' => $data['body'],
                //             'link' => $data['link'] ?? 'https://onstru.com/',
                //             'id' => $data['id'] ?? '',
                //         ],
                //         // 'notification' => [
                //         //     'title' => $data['title'],
                //         //     'body' => $data['body'],
                //         // ],
                //         // 'webpush' => [
                //         //     'notification' => [
                //         //         'icon' => 'https://onstru.com/assets/images/Logo_Admin.png',
                //         //     ],
                //         // ],
                //     ],
                // ];
                // $payload = [
                //     'message' => [
                //         'token' => $token,
                //         // 'notification' => [
                //         //     'title' => $data['title'],
                //         //     'body' => $data['body'],
                //         // ],
                //         'data' => [
                //             'title' => $data['title'],
                //             'body' => $data['body'],
                //             'link' => $data['link'] ?? 'https://onstru.com/',
                //             'id' => (string) ($data['id'] ?? ''),
                //         ],
                //         'webpush' => [
                //             'notification' => [
                //                 'icon' => 'https://onstru.com/assets/images/Logo_Admin.png',
                //                 'click_action' => $data['link'] ?? 'https://onstru.com/',
                //             ],
                //             'fcm_options' => [
                //                 'link' => $data['link'] ?? 'https://onstru.com/',
                //             ],
                //         ],
                //     ],
                // ];
                $payload = [
                    'message' => [
                        'token' => $token,
                        'data' => [
                            'title' => $data['title'],
                            'body' => $data['body'],
                            'link' => $data['link'] ?? 'https://onstru.com/',
                            'id' => (string) ($data['id'] ?? ''),
                        ],
                        'webpush' => [
                            // 'notification' => [
                            //     'icon' => 'https://onstru.com/assets/images/Logo_Admin.png',
                            //     'click_action' => $data['link'] ?? 'https://onstru.com/',
                            // ],
                            'fcm_options' => [
                                'link' => $data['link'] ?? 'https://onstru.com/',
                            ],
                        ],
                    ],
                ];

            } else {
                // Log::info("Sending mobile notification via FCM to token: {$token}");
                $payload = [
                    'message' => [
                        'token' => $token,
                        'data' => [   // Only send data
                            'title' => $data['title'],
                            'body' => $data['body'],
                            'link' => $data['link'] ?? 'https://onstru.com/',
                            'id' => (string) $data['id'] ?? '',
                        ],
                        'notification' => [
                            'title' => $data['title'],
                            'body' => $data['body'],
                        ],
                        // 'webpush' => [
                        //     'notification' => [
                        //         'icon' => 'https://onstru.com/assets/images/Logo_Admin.png',
                        //     ],
                        // ],
                    ],
                ];

            }

            $response = Http::withToken($accessToken)
                ->post('https://fcm.googleapis.com/v1/projects/cogosmart/messages:send', $payload);
        }

        // Log::info("FCM sent with payload: " . json_encode($payload, JSON_PRETTY_PRINT));
        // Log::info("FCM sent to token: {$token}");
        // Log::info('FCM v1 Status: ' . $response->status());
        // Log::info('FCM v1 Body: ' . $response->body());

        // log("Access Token: " . $accessToken);

     
        // Log::info($data);
        Log::info('📡 FCM Response Status: '.$response->status());
        Log::info('📡 FCM Response Body: '.$response->body());

        return $response->json();
    }

    public function getAccessToken()
    {
        return Cache::remember('fcm_access_token', 55 * 60, function () {
            // token lifetime = 3600s, we cache for 55 min (3300s) to be safe
            // $credentials = new ServiceAccountCredentials(
            //     ['https://www.googleapis.com/auth/firebase.messaging'],
            //     storage_path('app/cogosmart.json')
            // );

            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                json_decode(file_get_contents(storage_path('app/cogosmart.json')), true)
            );

            $token = $credentials->fetchAuthToken();
            $accessToken = $token['access_token'] ?? null;

            Log::debug('Generated new FCM Access Token: '.$accessToken);

            return $accessToken;
        });
        // $credentials = new ServiceAccountCredentials(
        //     ['https://www.googleapis.com/auth/firebase.messaging'],
        //     storage_path('app/firebase-service-account.json')
        // );

        // $token = $credentials->fetchAuthToken();

        // Log::debug("Generated FCM Access Token: " . $token['access_token']);

        // return $token['access_token'];
    }
}
