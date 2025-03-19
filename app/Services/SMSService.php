<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class SMSService
{
    protected $apiBaseUrl;
    protected $verifyUrl;
    protected $apiToken;
    protected $identifierId;
    protected $senderName;
    protected $otpUrl;

    public function __construct()
    {
        $this->apiToken = config('services.afromessage.api_token');
        $this->apiBaseUrl = config('services.afromessage.api_url'); // Base URL: https://api.afromessage.com/api
        $this->verifyUrl = config('services.afromessage.verifyUrl');
        $this->identifierId = config('services.afromessage.identifier_id');
        $this->senderName = config('services.afromessage.sender_name');
        $this->otpUrl = config('services.afromessage.security_url');
    }

    /**
     * Send a security code (OTP) using the AfroMessage /api/challenge endpoint.
     *
     * @param string $to Recipient phone number
     * @param array $options Optional parameters for the security code
     * @return array Response with success status and data/error
     */
    public function sendSecurityCode($to, $options = [])
{
    // Ensure phone number is in international format
    $to = $this->formatPhoneNumber($to);

    // Default parameters
    $defaultOptions = [
        'from' => $this->identifierId,       // System identifier ID
        // 'sender' => $this->senderName,         // Sender name
        'len' => 6,                          // Code length (default to 6 for OTP)
        't' => 0,                            // Code type: 0 = numeric
        'ttl' => 600,                        // Time to live: 10 minutes (600 seconds)
        'callback' => '',                    // Callback URL
        'pr' => 'Your verification code is: ', // Prefix
        'ps' => '. Valid for 10 minutes.',    // Postfix
        'sb' => 1,                           // Spaces before code
        'sa' => 1,                           // Spaces after code
    ];

    // Merge provided options with defaults
    $params = array_merge($defaultOptions, $options);
    $params['to'] = $to;

    // Build the query string (ensure to include '?' before query parameters)
    $url = $this->otpUrl . '?' . http_build_query($params);

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['acknowledge'] === 'success') {
                return [
                    'success' => true,
                    'data' => $data['response'], // Contains code, verificationId, etc.
                ];
            }
            return [
                'success' => false,
                'error' => $data['response'] ?? 'API returned failure',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json() ?? 'HTTP request failed',
        ];
    } catch (RequestException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

public function verifySecurityCode($phone, $otp)
{
    // Ensure the phone number is in international format
    $phone = $this->formatPhoneNumber($phone);

    // Prepare the request parameters for verifying the OTP
    $params = [
        'from' => $this->identifierId,
        'to' => $phone,
        'otp' => $otp,
    ];

    // Build the verification URL
    $url = $this->verifyUrl . '?' . http_build_query($params);

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            if ($data['acknowledge'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'OTP successfully verified',
                ];
            }

            return [
                'success' => false,
                'error' => $data['response'] ?? 'OTP verification failed',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json() ?? 'HTTP request failed',
        ];
    } catch (RequestException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}



    /**
     * Verify a security code using the verification endpoint.
     *
     * @param string|null $to Recipient phone number (optional if verificationId is provided)
     * @param string|null $verificationId Verification ID from the sendSecurityCode response
     * @param string $code The code to verify
     * @return array Response with success status and data/error
     */
    public function verifyCode($to = null, $verificationId = null, $code)
    {
        if (!$to && !$verificationId) {
            return ['success' => false, 'error' => 'Either "to" or "verificationId" is required'];
        }

        // Build query parameters
        $queryParams = [
            'to' => $to ? $this->formatPhoneNumber($to) : null,
            'vc' => $verificationId,
            'code' => $code,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get($this->verifyUrl, array_filter($queryParams)); // Remove null values

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json()];
        } catch (RequestException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Format phone number to international format (e.g., +251912345678).
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phone)
    {
        if (!preg_match('/^\+/', $phone)) {
            $phone = '+251' . ltrim($phone, '0');
        }
        return $phone;
    }

    /**
     * Legacy method for sending SMS (kept for compatibility).
     */
    public function sendSMS($to, $message, $callback = null)
    {
        $to = $this->formatPhoneNumber($to);

        $payload = [
            'from' => $this->identifierId,
            'to' => $to,
            'message' => $message,
            'callback' => $callback ?? '',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiBaseUrl, $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json()];
        } catch (RequestException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}