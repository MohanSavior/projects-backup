<?php
class Logger {
    private $logFile;

    public function __construct($logFilePath) {
        $this->logFile = fopen($logFilePath, 'a'); // 'a' mode appends to the file
        if (!$this->logFile) {
            $this->logFile = fopen($logFilePath, 'w');
        }
    }

    public function log($message) {
        $timestamp = date('[Y-m-d H:i:s]');
        $logMessage = $timestamp . ' ' . $message . PHP_EOL;
        fwrite($this->logFile, $logMessage);
    }

    public function close() {
        fclose($this->logFile);
    }

    public function logInfo($message) {
        $this->log("[INFO] " . $message);
    }

    public function logWarning($message) {
        $this->log("[WARNING] " . $message);
    }

    public function logError($message) {
        $this->log("[ERROR] " . $message);
    }

    public static function init($logFilePath) {
        return new self($logFilePath);
    }
}

