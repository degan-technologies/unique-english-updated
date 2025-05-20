<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChapaService {
    private $secretKey;
    private $baseUrl;
    private $approvalSecret;
    private $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.chapa.secret_key');
        $this->baseUrl = config('services.chapa.base_url');
        $this->approvalSecret = config('services.chapa.approval_secret');
        $this->webhookSecret = config('services.chapa.webhook_secret');
    }

    public function initializePayment($data)
    {
        $response = Http::withToken($this->secretKey)
            ->post("$this->baseUrl/transaction/initialize", $data);

        return $response->json();
    }

    public function verifyPayment($tx_ref) {
        $response = Http::withToken($this->secretKey)
            ->get("$this->baseUrl/transaction/verify/$tx_ref");

        return $response->json();
    }

    public function refundPayment($txRef) {
        $response = Http::withToken($this->secretKey)
            ->get("$this->baseUrl/refund/$txRef" );

        return $response->json();
    }

    public function getBankList() {
        $response = Http::withToken($this->secretKey)
            ->get("$this->baseUrl/banks");

        return $response->json();
    }

    // app/Services/ChapaService.php
public function transfer(array $data)
{
    try {
        $response = Http::withToken($this->secretKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache',
            ])
            ->post($this->baseUrl.'/transfers', $data);

             $jsonResponse = $response->json();

            if (isset($jsonResponse['data']) && is_string($jsonResponse['data'])) {
                    $jsonResponse['data'] = [
                        'reference' => $jsonResponse['data'],
                        'status' => 'pending'
                    ];
                } 

            return $jsonResponse;
        }  catch (\Exception $e) {
            $errorResponse['raw_response'] = $response->body();
            return $errorResponse;
        }


    }  


    /**
     * Generate signature for transfer verification
     */
    public function generateSignature(array $data): string {
        return hash_hmac('sha256', json_encode($data), $this->approvalSecret);
    }

    /**
     * Verify incoming webhook signature
     */
    // Add this to your ChapaService class
  public function verifyWebhook(Request $request) 
{ 
    $incomingSignature = $request->header('x-chapa-signature');

    if (!$incomingSignature) {
        return false;
    }

    $payload = $request->getContent();

    $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

    return hash_equals($expectedSignature, $incomingSignature);
}


}
