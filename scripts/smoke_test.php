<?php
// Simple smoke test for Assistant and CheckIn endpoints
$base = 'http://127.0.0.1:8000';

function post($url, $data) {
    $payload = json_encode($data);
    $opts = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
        'ignore_errors' => true,
    ]];
    $ctx = stream_context_create($opts);
    $res = @file_get_contents($url, false, $ctx);
    $status = null;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('#^HTTP/\d\.\d\s+(\d+)#', $h, $m)) { $status = (int)$m[1]; break; }
        }
    }
    return [$status, $res];
}

list($s1, $r1) = post($base . '/api/assistant', ['input' => 'I feel tired', 'mode' => 'reflect']);
echo "ASSISTANT STATUS: " . ($s1 ?? 'N/A') . "\n";
echo "ASSISTANT BODY:\n" . ($r1 ?? '') . "\n\n";

list($s2, $r2) = post($base . '/api/check-ins', ['mood' => 'tired', 'intensity' => 4, 'note' => 'Feeling drained today.']);
echo "CHECK-IN STATUS: " . ($s2 ?? 'N/A') . "\n";
echo "CHECK-IN BODY:\n" . ($r2 ?? '') . "\n";
