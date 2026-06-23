<?php
/**
 * YashRaj Systems - SMTP Port Diagnostic Tool
 * Run this on your Hostinger server to check if outbound SMTP ports are blocked.
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== YashRaj Systems SMTP Port Diagnostic ===\n\n";
echo "Testing outgoing connection to smtp.gmail.com...\n\n";

$ports = [
    465 => 'SSL',
    587 => 'STARTTLS',
    25  => 'Plain Text'
];

foreach ($ports as $port => $encryption) {
    echo "Testing Port {$port} ({$encryption})... ";
    $start = microtime(true);
    $fp = @fsockopen("smtp.gmail.com", $port, $errno, $errstr, 6);
    $duration = round(microtime(true) - $start, 3);
    
    if ($fp) {
        echo "SUCCESS! (Connected in {$duration}s)\n";
        fclose($fp);
    } else {
        echo "FAILED! Error: {$errstr} ({$errno}) - took {$duration}s\n";
        if ($errno === 110 || $errno === 101 || strpos(strtolower($errstr), 'timeout') !== false) {
            echo "   -> Diagnostic: This indicates that Hostinger's firewall is likely blocking outbound traffic on port {$port}.\n";
        }
    }
    echo "\n";
}

echo "============================================\n";
echo "How to resolve:\n";
echo "1. If Port 465 FAILED but Port 587 is SUCCESS, let me know and we will switch the script to use Port 587.\n";
echo "2. If both FAILED, Hostinger shared hosting is blocking outbound third-party SMTP. You will need to contact Hostinger support and ask them to enable outbound connection to smtp.gmail.com on port 465 or 587, OR switch the mail handler to use your Hostinger cPanel email settings instead of Gmail.\n";
