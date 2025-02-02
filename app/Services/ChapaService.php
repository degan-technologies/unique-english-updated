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
}
