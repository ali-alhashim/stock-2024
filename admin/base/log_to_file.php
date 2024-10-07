<?php
function logToFile($message) {
    // Define the path to the log file
    $logFile = __DIR__ . '/logs/log.txt'; // You can change this path

    // Prepare the log message with a timestamp
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;

    // Write the log message to the file
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
?>