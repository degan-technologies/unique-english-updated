<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SMSService
{
    protected $apiUrl;
    protected $apiToken;
    protected $identifierId;
    protected $senderName;

    public function __construct()
    {
        $this->apiToken = config('services.afromessage.api_token');
        $this->apiUrl = config('services.afromessage.api_url');
        $this->identifierId = config('services.afromessage.identifier_id');
        $this->senderName = config('services.afromessage.sender_name');
       
    }

    public function sendSMS($to, $message, $callback = null)
    {
        // Prepare the parameters for the GET request
        $params = [
            'from'     => $this->identifierId,
            'sender'   => $this->senderName,
            'to'       => $to,
            'message'  => $message,
            'callback' => $callback ?? '',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get($this->apiUrl, $params);

       

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }
        return ['success' => false, 'error' => $response->body()];
    }
}
