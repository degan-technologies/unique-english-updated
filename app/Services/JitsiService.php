<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;

class JitsiService
{
    public function generateToken(string $roomName, array $user): string
    {
        $cfg = config('services.jitsi');
        $fullpath = $cfg['private_key_fullpath'];

        if (! file_exists($fullpath) || ! is_readable($fullpath)) {
            throw new \RuntimeException("Cannot read JaaS key at: {$fullpath}");
        }

        $privateKey = file_get_contents($fullpath);

        // JWT Header: include your key ID here
        $headers = [
            'kid' => "{$cfg['app_id']}/{$cfg['kid']}"
        ];

        $now = Carbon::now();

        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',  // JaaS standard value
            'sub' => $cfg['app_id'], // e.g. 'vpaas-magic-cookie-xxxxxx'
            'room' => $roomName, // or '*' for all rooms
            'context' => [
                'user' => [
                    'id' => $user['id'] ?? 'guest',
                    'name' => $user['name'] ?? 'Guest User',
                    'email' => $user['email'] ?? '',
                    'avatar' => $user['avatar'] ?? '',
                    'moderator' => true,
                    'hidden-from-recorder' => false,
                ],
                'features' => [
                    'livestreaming' => true,
                    'recording' => true,
                    'transcription' => true,
                    'outbound-call' => true,
                    'sip-outbound-call' => false,
                ]
            ],
            'iat' => $now->timestamp,
            'nbf' => $now->timestamp,
            'exp' => $now->addHours(2)->timestamp,
        ];


        $data = JWT::encode(
            $payload,
            $privateKey,
            $cfg['algorithm'] ?? 'RS256',
            null,
            $headers
        ); 

        
 
        return $data;
    }
}
