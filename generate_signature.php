<?php

$secret = 'qwertyuiopasdfghjk';

$payload = json_encode([
    'event' => 'payment.failed',
    'data' => [
        'tx_ref' => 'test123',
        'status' => 'failed',
        'amount' => 100,
        'currency' => 'ETB',
        'failure_reason' => 'insufficient_funds'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);  // ensures no spaces or escapes

$signature = hash_hmac('sha256', $payload, $secret);

echo "Payload: $payload\n";
echo "Chapa-Signature: $signature\n";
