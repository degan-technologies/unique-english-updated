<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChapaService {
    private $secretKey;
    private $baseUrl;

    public function __construct()
    {
        $this->secretKey = env('CHAPA_SECRET_KEY');
        $this->baseUrl = env('CHAPA_BASE_URL');
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

    public function transfer($data) {
        $response = Http::withToken($this->secretKey)
            ->post("$this->baseUrl/transfers", $data);

        return $response->json();
    }
}
