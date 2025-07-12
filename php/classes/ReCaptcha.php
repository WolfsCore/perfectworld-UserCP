<?php
/**
 * reCAPTCHA v3 Handler Class
 * Handles server-side reCAPTCHA verification
 */

class ReCaptcha {
    
    private $secretKey;
    private $verifyUrl;
    private $minScore;
    private $timeout;
    
    public function __construct() {
        $this->secretKey = getConfig('recaptcha.secret_key');
        $this->verifyUrl = getConfig('recaptcha.verify_url');
        $this->minScore = getConfig('recaptcha.min_score');
        $this->timeout = getConfig('recaptcha.timeout');
    }
    
    /**
     * Verify reCAPTCHA token
     */
    public function verify($token, $action = null) {
        try {
            // Validate inputs
            if (empty($token)) {
                throw new Exception('reCAPTCHA token is required');
            }
            
            if (empty($this->secretKey)) {
                throw new Exception('reCAPTCHA secret key not configured');
            }
            
            // Prepare request data
            $data = [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];
            
            // Make request to Google reCAPTCHA API
            $response = $this->makeRequest($data);
            
            // Parse response
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid reCAPTCHA response format');
            }
            
            // Check if verification was successful
            if (!$result['success']) {
                $this->logError('reCAPTCHA verification failed', [
                    'token' => substr($token, 0, 20) . '...',
                    'action' => $action,
                    'errors' => $result['error-codes'] ?? []
                ]);
                return false;
            }
            
            // Check score (for v3)
            if (isset($result['score']) && $result['score'] < $this->minScore) {
                $this->logWarning('reCAPTCHA score too low', [
                    'score' => $result['score'],
                    'min_score' => $this->minScore,
                    'action' => $action
                ]);
                return false;
            }
            
            // Check action (for v3)
            if ($action && isset($result['action']) && $result['action'] !== $action) {
                $this->logWarning('reCAPTCHA action mismatch', [
                    'expected' => $action,
                    'received' => $result['action']
                ]);
                return false;
            }
            
            // Check hostname (optional)
            if (isset($result['hostname'])) {
                $expectedHostname = parse_url(SITE_URL, PHP_URL_HOST);
                if ($result['hostname'] !== $expectedHostname) {
                    $this->logWarning('reCAPTCHA hostname mismatch', [
                        'expected' => $expectedHostname,
                        'received' => $result['hostname']
                    ]);
                }
            }
            
            // Log successful verification
            $this->logInfo('reCAPTCHA verification successful', [
                'score' => $result['score'] ?? 'N/A',
                'action' => $result['action'] ?? 'N/A',
                'hostname' => $result['hostname'] ?? 'N/A'
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'action' => $action
            ]);
            return false;
        }
    }
    
    /**
     * Make HTTP request to reCAPTCHA API
     */
    private function makeRequest($data) {
        // Use cURL if available
        if (function_exists('curl_init')) {
            return $this->makeRequestCurl($data);
        }
        
        // Fallback to file_get_contents
        return $this->makeRequestFileGetContents($data);
    }
    
    /**
     * Make request using cURL
     */
    private function makeRequestCurl($data) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->verifyUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'UCP-reCAPTCHA-Client/1.0',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('HTTP error: ' . $httpCode);
        }
        
        return $response;
    }
    
    /**
     * Make request using file_get_contents
     */
    private function makeRequestFileGetContents($data) {
        $postData = http_build_query($data);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: ' . strlen($postData),
                    'User-Agent: UCP-reCAPTCHA-Client/1.0'
                ],
                'content' => $postData,
                'timeout' => $this->timeout
            ]
        ]);
        
        $response = file_get_contents($this->verifyUrl, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to make HTTP request');
        }
        
        return $response;
    }
    
    /**
     * Get reCAPTCHA site key for client-side
     */
    public function getSiteKey() {
        return getConfig('recaptcha.site_key');
    }
    
    /**
     * Generate reCAPTCHA HTML for forms
     */
    public function generateHtml($action = 'submit') {
        $siteKey = $this->getSiteKey();
        
        if (empty($siteKey)) {
            return '<!-- reCAPTCHA not configured -->';
        }
        
        return sprintf(
            '<div class="g-recaptcha" data-sitekey="%s" data-action="%s" data-callback="onRecaptchaCallback"></div>',
            htmlspecialchars($siteKey),
            htmlspecialchars($action)
        );
    }
    
    /**
     * Generate reCAPTCHA JavaScript
     */
    public function generateScript() {
        $siteKey = $this->getSiteKey();
        
        if (empty($siteKey)) {
            return '<!-- reCAPTCHA not configured -->';
        }
        
        return sprintf(
            '<script src="https://www.google.com/recaptcha/api.js?render=%s"></script>',
            htmlspecialchars($siteKey)
        );
    }
    
    /**
     * Get verification statistics
     */
    public function getStats($days = 7) {
        $stats = [
            'total_verifications' => 0,
            'successful_verifications' => 0,
            'failed_verifications' => 0,
            'average_score' => 0,
            'actions' => []
        ];
        
        // This would typically query your logging system
        // For now, return empty stats
        return $stats;
    }
    
    /**
     * Check if reCAPTCHA is properly configured
     */
    public function isConfigured() {
        return !empty($this->secretKey) && !empty(getConfig('recaptcha.site_key'));
    }
    
    /**
     * Test reCAPTCHA configuration
     */
    public function testConfiguration() {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'reCAPTCHA keys not configured'
            ];
        }
        
        // Test with a dummy token (this will fail, but we can check the error)
        try {
            $testToken = 'test-token';
            $response = $this->makeRequest([
                'secret' => $this->secretKey,
                'response' => $testToken,
                'remoteip' => '127.0.0.1'
            ]);
            
            $result = json_decode($response, true);
            
            if (isset($result['error-codes'])) {
                // Check for specific error codes
                if (in_array('invalid-input-secret', $result['error-codes'])) {
                    return [
                        'success' => false,
                        'message' => 'Invalid reCAPTCHA secret key'
                    ];
                }
            }
            
            return [
                'success' => true,
                'message' => 'reCAPTCHA configuration appears valid'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'reCAPTCHA API communication error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log info message
     */
    private function logInfo($message, $context = []) {
        if (class_exists('Logger')) {
            $logger = new Logger();
            $logger->info($message, $context);
        }
    }
    
    /**
     * Log warning message
     */
    private function logWarning($message, $context = []) {
        if (class_exists('Logger')) {
            $logger = new Logger();
            $logger->warning($message, $context);
        }
    }
    
    /**
     * Log error message
     */
    private function logError($message, $context = []) {
        if (class_exists('Logger')) {
            $logger = new Logger();
            $logger->error($message, $context);
        }
    }
    
    /**
     * Rate limiting for reCAPTCHA requests
     */
    private function checkRateLimit($ip) {
        $cacheKey = 'recaptcha_rate_limit_' . md5($ip);
        $cache = new Cache();
        
        $attempts = $cache->get($cacheKey, 0);
        
        if ($attempts >= 60) { // 60 requests per hour
            return false;
        }
        
        $cache->set($cacheKey, $attempts + 1, 3600); // 1 hour
        return true;
    }
    
    /**
     * Clean up old verification logs
     */
    public function cleanup($days = 30) {
        // This would typically clean up old logs
        // Implementation depends on your logging system
    }
}
?> 