<?php
/**
 * Main UCP Class
 * Handles core UCP functionality
 */

class UCP {
    
    protected $db;
    protected $config;
    protected $session;
    protected $language;
    protected $logger;
    protected $cache;
    
    public function __construct() {
        $this->initializeDatabase();
        $this->initializeSession();
        $this->initializeLanguage();
        $this->initializeLogger();
        $this->initializeCache();
        $this->checkMaintenanceMode();
    }
    
    /**
     * Initialize database connection
     */
    private function initializeDatabase() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->db = new PDO($dsn, DB_USER, DB_PASS, getConfig('database.options'));
        } catch (PDOException $e) {
            $this->handleError('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize session
     */
    private function initializeSession() {
        session_start();
        $this->session = new Session();
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Initialize language system
     */
    private function initializeLanguage() {
        $lang = $_GET['lang'] ?? $_SESSION['language'] ?? DEFAULT_LANGUAGE;
        $this->language = new Language($lang);
        $_SESSION['language'] = $lang;
    }
    
    /**
     * Initialize logger
     */
    private function initializeLogger() {
        $this->logger = new Logger();
    }
    
    /**
     * Initialize cache system
     */
    private function initializeCache() {
        $this->cache = new Cache();
    }
    
    /**
     * Check maintenance mode
     */
    private function checkMaintenanceMode() {
        if (MAINTENANCE_MODE && !$this->isMaintenanceAllowed()) {
            $this->showMaintenancePage();
        }
    }
    
    /**
     * Check if current IP is allowed during maintenance
     */
    private function isMaintenanceAllowed() {
        $allowedIps = explode(',', MAINTENANCE_ALLOWED_IPS);
        $clientIp = $_SERVER['REMOTE_ADDR'];
        return in_array($clientIp, $allowedIps);
    }
    
    /**
     * Show maintenance page
     */
    private function showMaintenancePage() {
        http_response_code(503);
        include 'templates/maintenance.php';
        exit;
    }
    
    /**
     * Handle user registration
     */
    public function register($data) {
        try {
            // Validate reCAPTCHA
            if (!$this->validateRecaptcha($data['recaptcha_token'], 'register')) {
                throw new Exception($this->language->get('validation_captcha'));
            }
            
            // Validate input data
            $this->validateRegistrationData($data);
            
            // Check if user already exists
            if ($this->userExists($data['username'], $data['email'])) {
                throw new Exception($this->language->get('register_exists'));
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID, getConfig('security.password_hash_options'));
            
            // Generate verification token
            $verificationToken = $this->generateToken();
            
            // Insert user into database
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, verification_token, created_at, last_login_ip) 
                VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $verificationToken,
                $_SERVER['REMOTE_ADDR']
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Send verification email
            if (isFeatureEnabled('email_verification')) {
                $this->sendVerificationEmail($data['email'], $verificationToken);
            }
            
            // Log registration
            $this->logger->info('User registered', [
                'user_id' => $userId,
                'username' => $data['username'],
                'email' => $data['email'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            return [
                'success' => true,
                'message' => $this->language->get('register_success'),
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Registration failed', [
                'error' => $e->getMessage(),
                'data' => $data,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle user login
     */
    public function login($data) {
        try {
            // Validate reCAPTCHA
            if (!$this->validateRecaptcha($data['recaptcha_token'], 'login')) {
                throw new Exception($this->language->get('validation_captcha'));
            }
            
            // Check login attempts
            if ($this->isLoginLocked($data['username'])) {
                throw new Exception($this->language->get('login_locked'));
            }
            
            // Get user from database
            $user = $this->getUserByUsernameOrEmail($data['username']);
            
            if (!$user) {
                $this->recordFailedLogin($data['username']);
                throw new Exception($this->language->get('login_invalid'));
            }
            
            // Verify password
            if (!password_verify($data['password'], $user['password'])) {
                $this->recordFailedLogin($data['username']);
                throw new Exception($this->language->get('login_invalid'));
            }
            
            // Check if account is banned
            if ($user['banned']) {
                throw new Exception($this->language->get('login_banned'));
            }
            
            // Check if email is verified
            if (isFeatureEnabled('email_verification') && !$user['email_verified']) {
                throw new Exception($this->language->get('login_not_activated'));
            }
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Start session
            $this->session->start($user);
            
            // Clear failed login attempts
            $this->clearFailedLogins($data['username']);
            
            // Log successful login
            $this->logger->info('User logged in', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            return [
                'success' => true,
                'message' => $this->language->get('login_success'),
                'user' => $this->sanitizeUserData($user)
            ];
            
        } catch (Exception $e) {
            $this->logger->warning('Login failed', [
                'error' => $e->getMessage(),
                'username' => $data['username'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle user logout
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        // Log logout
        if ($userId) {
            $this->logger->info('User logged out', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
        }
        
        // End session
        $this->session->end();
        
        return [
            'success' => true,
            'message' => $this->language->get('logout_success')
        ];
    }
    
    /**
     * Validate reCAPTCHA token
     */
    private function validateRecaptcha($token, $action) {
        $recaptcha = new ReCaptcha();
        return $recaptcha->verify($token, $action);
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        $validator = new Validator();
        
        // Username validation
        if (!$validator->validateUsername($data['username'])) {
            throw new Exception($this->language->get('validation_username_format'));
        }
        
        // Email validation
        if (!$validator->validateEmail($data['email'])) {
            throw new Exception($this->language->get('validation_email'));
        }
        
        // Password validation
        if (!$validator->validatePassword($data['password'])) {
            throw new Exception($this->language->get('validation_password_strength'));
        }
        
        // Password confirmation
        if ($data['password'] !== $data['password_confirm']) {
            throw new Exception($this->language->get('validation_passwords_match'));
        }
        
        // Terms acceptance
        if (!isset($data['terms']) || !$data['terms']) {
            throw new Exception($this->language->get('register_terms_required'));
        }
    }
    
    /**
     * Check if user exists
     */
    private function userExists($username, $email) {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $email]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get user by username or email
     */
    private function getUserByUsernameOrEmail($identifier) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }
    
    /**
     * Check if login is locked due to failed attempts
     */
    private function isLoginLocked($username) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE username = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$username, LOGIN_LOCKOUT_TIME]);
        $result = $stmt->fetch();
        
        return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedLogin($username) {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (username, ip_address, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
    }
    
    /**
     * Clear failed login attempts
     */
    private function clearFailedLogins($username) {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET last_login = NOW(), last_login_ip = ? 
            WHERE id = ?
        ");
        $stmt->execute([$_SERVER['REMOTE_ADDR'], $userId]);
    }
    
    /**
     * Send verification email
     */
    private function sendVerificationEmail($email, $token) {
        $mailer = new Mailer();
        $verificationUrl = SITE_URL . "/verify-email.php?token=" . $token;
        
        $mailer->sendTemplate('email_verification', $email, [
            'verification_url' => $verificationUrl,
            'site_name' => SITE_NAME
        ]);
    }
    
    /**
     * Sanitize user data for client
     */
    private function sanitizeUserData($user) {
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'email_verified' => $user['email_verified'],
            'created_at' => $user['created_at'],
            'last_login' => $user['last_login']
        ];
    }
    
    /**
     * Generate secure token
     */
    private function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Handle errors
     */
    private function handleError($message) {
        $this->logger->error($message);
        
        if (DEBUG_MODE) {
            throw new Exception($message);
        } else {
            throw new Exception($this->language->get('error_500'));
        }
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        return $this->session->getUser();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->session->isLoggedIn();
    }
    
    /**
     * Get database connection
     */
    public function getDatabase() {
        return $this->db;
    }
    
    /**
     * Get language instance
     */
    public function getLanguage() {
        return $this->language;
    }
    
    /**
     * Get logger instance
     */
    public function getLogger() {
        return $this->logger;
    }
    
    /**
     * Get cache instance
     */
    public function getCache() {
        return $this->cache;
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($email) {
        try {
            // Check if email exists
            $stmt = $this->db->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => $this->language->get('email_not_found')];
            }
            
            // Generate reset token
            $token = $this->generateToken();
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            
            // Save token to database
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
            ");
            $stmt->execute([$user['id'], $token, $expires, $token, $expires]);
            
            // Send email (placeholder - implement actual email sending)
            $this->logger->info('Password reset requested', ['user_id' => $user['id']]);
            
            return ['success' => true, 'message' => $this->language->get('password_reset_sent')];
        } catch (Exception $e) {
            $this->logger->error('Password reset failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $this->language->get('error_500')];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(c.id) as total_characters,
                    COALESCE(SUM(c.playtime_hours), 0) as total_playtime_hours,
                    COALESCE(MAX(c.level), 1) as max_level,
                    COUNT(DISTINCT l.id) as total_logins
                FROM users u
                LEFT JOIN characters c ON u.id = c.user_id
                LEFT JOIN login_history l ON u.id = l.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch() ?: [
                'total_characters' => 0,
                'total_playtime_hours' => 0,
                'max_level' => 1,
                'total_logins' => 0
            ];
        } catch (Exception $e) {
            $this->logger->error('Error getting user stats: ' . $e->getMessage());
            return ['total_characters' => 0, 'total_playtime_hours' => 0, 'max_level' => 1, 'total_logins' => 0];
        }
    }
    
    /**
     * Get user characters
     */
    public function getUserCharacters($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.name,
                    c.class,
                    c.level,
                    c.location,
                    c.playtime_hours,
                    c.experience,
                    c.experience_needed,
                    c.online,
                    c.created_at
                FROM characters c
                WHERE c.user_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->logger->error('Error getting user characters: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.id,
                    a.type,
                    a.description,
                    a.icon,
                    a.created_at,
                    TIMESTAMPDIFF(MINUTE, a.created_at, NOW()) as minutes_ago
                FROM activities a
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            $activities = $stmt->fetchAll() ?: [];
            
            // Format time_ago
            foreach ($activities as &$activity) {
                $minutes = $activity['minutes_ago'];
                if ($minutes < 60) {
                    $activity['time_ago'] = $minutes . ' ' . $this->language->get('minutes_ago');
                } elseif ($minutes < 1440) {
                    $hours = floor($minutes / 60);
                    $activity['time_ago'] = $hours . ' ' . $this->language->get('hours_ago');
                } else {
                    $days = floor($minutes / 1440);
                    $activity['time_ago'] = $days . ' ' . $this->language->get('days_ago');
                }
            }
            
            return $activities;
        } catch (Exception $e) {
            $this->logger->error('Error getting recent activities: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get server status
     */
    public function getServerStatus() {
        try {
            $stmt = $this->db->prepare("
                SELECT status, max_players, version, uptime, last_update
                FROM server_status
                ORDER BY last_update DESC
                LIMIT 1
            ");
            $stmt->execute();
            
            $status = $stmt->fetch();
            
            return $status ?: [
                'status' => 'offline',
                'max_players' => 100,
                'version' => '1.0.0',
                'uptime' => '0m',
                'last_update' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error('Error getting server status: ' . $e->getMessage());
            return [
                'status' => 'offline',
                'max_players' => 100,
                'version' => '1.0.0',
                'uptime' => '0m',
                'last_update' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Get online players count
     */
    public function getOnlinePlayersCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM characters WHERE online = 1");
            $stmt->execute();
            $result = $stmt->fetch();
            return (int) $result['count'];
        } catch (Exception $e) {
            $this->logger->error('Error getting online players count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create a new character
     */
    public function createCharacter($data) {
        try {
            $name = trim($data['character_name']);
            $class = $data['character_class'];
            $userId = $_SESSION['user_id'];
            
            // Validate character name
            if (strlen($name) < 3 || strlen($name) > 20) {
                return ['success' => false, 'message' => $this->language->get('character_name_length')];
            }
            
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                return ['success' => false, 'message' => $this->language->get('character_name_invalid')];
            }
            
            // Check if character name exists
            $stmt = $this->db->prepare("SELECT id FROM characters WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => $this->language->get('character_name_exists')];
            }
            
            // Check character limit
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM characters WHERE user_id = ?");
            $stmt->execute([$userId]);
            $count = $stmt->fetch()['count'];
            
            if ($count >= MAX_CHARACTERS_PER_ACCOUNT) {
                return ['success' => false, 'message' => $this->language->get('character_limit_reached')];
            }
            
            // Create character
            $stmt = $this->db->prepare("
                INSERT INTO characters (user_id, name, class, level, location, experience, experience_needed, created_at) 
                VALUES (?, ?, ?, 1, 'Starting Area', 0, 1000, NOW())
            ");
            $stmt->execute([$userId, $name, $class]);
            
            return ['success' => true, 'message' => $this->language->get('character_created')];
        } catch (Exception $e) {
            $this->logger->error('Error creating character: ' . $e->getMessage());
            return ['success' => false, 'message' => $this->language->get('error_500')];
        }
    }
    
    /**
     * Delete a character
     */
    public function deleteCharacter($characterId) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Check if character belongs to user
            $stmt = $this->db->prepare("SELECT id FROM characters WHERE id = ? AND user_id = ?");
            $stmt->execute([$characterId, $userId]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => $this->language->get('character_not_found')];
            }
            
            // Delete character
            $stmt = $this->db->prepare("DELETE FROM characters WHERE id = ? AND user_id = ?");
            $stmt->execute([$characterId, $userId]);
            
            return ['success' => true, 'message' => $this->language->get('character_deleted')];
        } catch (Exception $e) {
            $this->logger->error('Error deleting character: ' . $e->getMessage());
            return ['success' => false, 'message' => $this->language->get('error_500')];
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($data) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get current password hash
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => $this->language->get('user_not_found')];
            }
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                return ['success' => false, 'message' => $this->language->get('current_password_incorrect')];
            }
            
            // Validate new password
            if (strlen($data['new_password']) < 8) {
                return ['success' => false, 'message' => $this->language->get('password_too_short')];
            }
            
            if ($data['new_password'] !== $data['confirm_password']) {
                return ['success' => false, 'message' => $this->language->get('passwords_dont_match')];
            }
            
            // Update password
            $newPasswordHash = password_hash($data['new_password'], PASSWORD_ARGON2ID);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?");
            $stmt->execute([$newPasswordHash, $userId]);
            
            return ['success' => true, 'message' => $this->language->get('password_changed')];
        } catch (Exception $e) {
            $this->logger->error('Error changing password: ' . $e->getMessage());
            return ['success' => false, 'message' => $this->language->get('error_500')];
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($data) {
        try {
            $userId = $_SESSION['user_id'];
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => $this->language->get('invalid_email')];
            }
            
            // Check if email is already used
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => $this->language->get('email_already_used')];
            }
            
            // Update profile
            $stmt = $this->db->prepare("
                UPDATE users SET 
                    email = ?,
                    language = ?,
                    newsletter = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['email'],
                $data['language'],
                isset($data['newsletter']) ? 1 : 0,
                $userId
            ]);
            
            return ['success' => true, 'message' => $this->language->get('profile_updated')];
        } catch (Exception $e) {
            $this->logger->error('Error updating profile: ' . $e->getMessage());
            return ['success' => false, 'message' => $this->language->get('error_500')];
        }
    }
    
    /**
     * Get login history
     */
    public function getLoginHistory($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    login_time,
                    ip_address,
                    user_agent,
                    location,
                    status
                FROM login_history
                WHERE user_id = ?
                ORDER BY login_time DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->logger->error('Error getting login history: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get players list with filters
     */
    public function getPlayersList($filters = [], $page = 1, $perPage = 20) {
        try {
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "c.name LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($filters['class'])) {
                $whereConditions[] = "c.class = ?";
                $params[] = $filters['class'];
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "c.online = ?";
                $params[] = $filters['status'] === 'online' ? 1 : 0;
            }
            
            if (!empty($filters['level_min'])) {
                $whereConditions[] = "c.level >= ?";
                $params[] = (int) $filters['level_min'];
            }
            
            if (!empty($filters['level_max'])) {
                $whereConditions[] = "c.level <= ?";
                $params[] = (int) $filters['level_max'];
            }
            
            $whereSQL = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            $offset = ($page - 1) * $perPage;
            
            $sql = "
                SELECT 
                    c.id,
                    c.name,
                    c.class,
                    c.level,
                    c.location,
                    c.playtime_hours,
                    c.experience,
                    c.experience_needed,
                    c.online,
                    c.last_login,
                    g.name as guild
                FROM characters c
                LEFT JOIN guilds g ON c.guild_id = g.id
                $whereSQL
                ORDER BY c.online DESC, c.level DESC, c.name ASC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->logger->error('Error getting players list: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total players count
     */
    public function getTotalPlayersCount($filters = []) {
        try {
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "c.name LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($filters['class'])) {
                $whereConditions[] = "c.class = ?";
                $params[] = $filters['class'];
            }
            
            if (!empty($filters['status'])) {
                $whereConditions[] = "c.online = ?";
                $params[] = $filters['status'] === 'online' ? 1 : 0;
            }
            
            if (!empty($filters['level_min'])) {
                $whereConditions[] = "c.level >= ?";
                $params[] = (int) $filters['level_min'];
            }
            
            if (!empty($filters['level_max'])) {
                $whereConditions[] = "c.level <= ?";
                $params[] = (int) $filters['level_max'];
            }
            
            $whereSQL = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT COUNT(*) as count FROM characters c $whereSQL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return (int) $result['count'];
        } catch (Exception $e) {
            $this->logger->error('Error getting total players count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get player statistics
     */
    public function getPlayerStatistics() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    MAX(level) as max_level,
                    AVG(level) as average_level,
                    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today
                FROM characters
            ");
            $stmt->execute();
            
            return $stmt->fetch() ?: ['max_level' => 0, 'average_level' => 0, 'new_today' => 0];
        } catch (Exception $e) {
            $this->logger->error('Error getting player statistics: ' . $e->getMessage());
            return ['max_level' => 0, 'average_level' => 0, 'new_today' => 0];
        }
    }
}
?> 