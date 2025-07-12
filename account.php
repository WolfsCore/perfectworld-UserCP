<?php
define('UCP_ACCESS', true);
require_once 'config/config.php';
require_once 'php/classes/UCP.php';

$ucp = new UCP();
$lang = $ucp->getLanguage();
$currentUser = $ucp->getCurrentUser();

// Redirect if not logged in
if (!$ucp->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'change_password':
                $result = $ucp->changePassword($_POST);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'update_profile':
                $result = $ucp->updateProfile($_POST);
                if ($result['success']) {
                    $success = $result['message'];
                    $currentUser = $ucp->getCurrentUser(); // Refresh user data
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'update_security':
                $result = $ucp->updateSecuritySettings($_POST);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get user statistics and activity
$userStats = $ucp->getUserStats($currentUser['id']);
$loginHistory = $ucp->getLoginHistory($currentUser['id'], 10);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('account_title'); ?> - <?php echo $lang->get('game_title'); ?></title>
    <meta name="description" content="<?php echo $lang->get('account_subtitle'); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/account.css">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="js/main.js" as="script">
    <link rel="preload" href="js/account.js" as="script">
</head>
<body class="account-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <a href="index.php">
                        <img src="images/logo.png" alt="<?php echo $lang->get('game_title'); ?>" class="logo">
                        <?php echo $lang->get('game_title'); ?>
                    </a>
                </div>
                
                <div class="navbar-nav">
                    <a href="dashboard.php" class="nav-link" data-i18n="nav_dashboard"><?php echo $lang->get('nav_dashboard'); ?></a>
                    <a href="characters.php" class="nav-link" data-i18n="nav_characters"><?php echo $lang->get('nav_characters'); ?></a>
                    <a href="players.php" class="nav-link" data-i18n="nav_players"><?php echo $lang->get('nav_players'); ?></a>
                    <a href="account.php" class="nav-link active" data-i18n="nav_account"><?php echo $lang->get('nav_account'); ?></a>
                    
                    <!-- User Menu -->
                    <div class="user-menu">
                        <div class="user-menu-toggle">
                            <div class="user-avatar">
                                <img src="images/default-avatar.png" alt="<?php echo htmlspecialchars($currentUser['username']); ?>">
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <svg class="icon dropdown-arrow" viewBox="0 0 24 24">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </div>
                        <div class="user-menu-dropdown">
                            <a href="account.php" class="user-menu-item">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                                <span data-i18n="nav_account"><?php echo $lang->get('nav_account'); ?></span>
                            </a>
                            <a href="logout.php" class="user-menu-item">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                                </svg>
                                <span data-i18n="nav_logout"><?php echo $lang->get('nav_logout'); ?></span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Language Selector -->
                    <div class="language-selector">
                        <button class="language-selector-toggle" aria-label="<?php echo $lang->get('nav_language'); ?>">
                            <span class="language-flag"></span>
                            <?php echo strtoupper($lang->getCurrentLanguage()); ?>
                        </button>
                        <div class="language-dropdown">
                            <a href="?lang=de" class="language-option" data-lang="de">
                                <span class="language-flag flag-de"></span>
                                Deutsch
                            </a>
                            <a href="?lang=en" class="language-option" data-lang="en">
                                <span class="language-flag flag-en"></span>
                                English
                            </a>
                            <a href="?lang=br" class="language-option" data-lang="br">
                                <span class="language-flag flag-br"></span>
                                Português
                            </a>
                            <a href="?lang=cn" class="language-option" data-lang="cn">
                                <span class="language-flag flag-cn"></span>
                                中文
                            </a>
                            <a href="?lang=vm" class="language-option" data-lang="vm">
                                <span class="language-flag flag-vm"></span>
                                Tiếng Việt
                            </a>
                            <a href="?lang=ru" class="language-option" data-lang="ru">
                                <span class="language-flag flag-ru"></span>
                                Русский
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <section class="page-header">
                <div class="header-content">
                    <h1 class="page-title" data-i18n="account_title"><?php echo $lang->get('account_title'); ?></h1>
                    <p class="page-subtitle" data-i18n="account_subtitle"><?php echo $lang->get('account_subtitle'); ?></p>
                </div>
            </section>
            
            <!-- Alerts -->
            <?php if ($error): ?>
            <div class="alert alert-error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <!-- Account Content -->
            <div class="account-grid">
                <!-- Profile Section -->
                <section class="account-section profile-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="account_profile"><?php echo $lang->get('account_profile'); ?></h2>
                    </div>
                    
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <img src="images/default-avatar.png" alt="<?php echo htmlspecialchars($currentUser['username']); ?>">
                            <button class="avatar-upload" type="button" title="<?php echo $lang->get('account_avatar_upload'); ?>">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="profile-info">
                            <h3 class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></h3>
                            <p class="profile-email"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-label" data-i18n="account_member_since"><?php echo $lang->get('account_member_since'); ?></span>
                                    <span class="stat-value"><?php echo date('d.m.Y', strtotime($currentUser['created_at'])); ?></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label" data-i18n="account_last_login"><?php echo $lang->get('account_last_login'); ?></span>
                                    <span class="stat-value"><?php echo date('d.m.Y H:i', strtotime($currentUser['last_login'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form class="profile-form" method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="email" class="form-label" data-i18n="account_email"><?php echo $lang->get('account_email'); ?></label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="language" class="form-label" data-i18n="account_language"><?php echo $lang->get('account_language'); ?></label>
                            <select id="language" name="language" class="form-control">
                                <option value="de" <?php echo $currentUser['language'] === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                                <option value="en" <?php echo $currentUser['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="br" <?php echo $currentUser['language'] === 'br' ? 'selected' : ''; ?>>Português</option>
                                <option value="cn" <?php echo $currentUser['language'] === 'cn' ? 'selected' : ''; ?>>中文</option>
                                <option value="vm" <?php echo $currentUser['language'] === 'vm' ? 'selected' : ''; ?>>Tiếng Việt</option>
                                <option value="ru" <?php echo $currentUser['language'] === 'ru' ? 'selected' : ''; ?>>Русский</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="newsletter" name="newsletter" <?php echo $currentUser['newsletter'] ? 'checked' : ''; ?>>
                                <label for="newsletter" class="checkbox-label">
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text" data-i18n="account_newsletter"><?php echo $lang->get('account_newsletter'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" data-i18n="account_update_profile">
                            <?php echo $lang->get('account_update_profile'); ?>
                        </button>
                    </form>
                </section>
                
                <!-- Security Section -->
                <section class="account-section security-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="account_security"><?php echo $lang->get('account_security'); ?></h2>
                    </div>
                    
                    <!-- Password Change -->
                    <div class="security-card">
                        <div class="security-header">
                            <h3 class="security-title" data-i18n="account_password_change"><?php echo $lang->get('account_password_change'); ?></h3>
                            <p class="security-subtitle" data-i18n="account_password_change_subtitle"><?php echo $lang->get('account_password_change_subtitle'); ?></p>
                        </div>
                        
                        <form class="security-form" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label" data-i18n="account_current_password"><?php echo $lang->get('account_current_password'); ?></label>
                                <div class="password-input-container">
                                    <input type="password" 
                                           id="current_password" 
                                           name="current_password" 
                                           class="form-control" 
                                           placeholder="<?php echo $lang->get('account_current_password'); ?>"
                                           required
                                           autocomplete="current-password">
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon icon-eye" viewBox="0 0 24 24">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label" data-i18n="account_new_password"><?php echo $lang->get('account_new_password'); ?></label>
                                <div class="password-input-container">
                                    <input type="password" 
                                           id="new_password" 
                                           name="new_password" 
                                           class="form-control" 
                                           placeholder="<?php echo $lang->get('account_new_password'); ?>"
                                           required
                                           minlength="8"
                                           autocomplete="new-password">
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon icon-eye" viewBox="0 0 24 24">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill"></div>
                                    </div>
                                    <span class="strength-text" data-i18n="password_strength_weak"><?php echo $lang->get('password_strength_weak'); ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label" data-i18n="account_confirm_password"><?php echo $lang->get('account_confirm_password'); ?></label>
                                <div class="password-input-container">
                                    <input type="password" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           class="form-control" 
                                           placeholder="<?php echo $lang->get('account_confirm_password'); ?>"
                                           required
                                           autocomplete="new-password">
                                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                        <svg class="icon icon-eye" viewBox="0 0 24 24">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" data-i18n="account_change_password">
                                <?php echo $lang->get('account_change_password'); ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Two-Factor Authentication -->
                    <div class="security-card">
                        <div class="security-header">
                            <h3 class="security-title" data-i18n="account_2fa"><?php echo $lang->get('account_2fa'); ?></h3>
                            <p class="security-subtitle" data-i18n="account_2fa_subtitle"><?php echo $lang->get('account_2fa_subtitle'); ?></p>
                        </div>
                        
                        <div class="security-status">
                            <div class="status-indicator <?php echo $currentUser['two_factor_enabled'] ? 'enabled' : 'disabled'; ?>">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <?php if ($currentUser['two_factor_enabled']): ?>
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                                    <?php else: ?>
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 6h2v2h-2V7zm0 4h2v6h-2v-6z"/>
                                    <?php endif; ?>
                                </svg>
                                <span class="status-text">
                                    <?php echo $currentUser['two_factor_enabled'] ? $lang->get('account_2fa_enabled') : $lang->get('account_2fa_disabled'); ?>
                                </span>
                            </div>
                            
                            <button class="btn btn-secondary" id="toggle-2fa-btn" data-i18n="<?php echo $currentUser['two_factor_enabled'] ? 'account_2fa_disable' : 'account_2fa_enable'; ?>">
                                <?php echo $currentUser['two_factor_enabled'] ? $lang->get('account_2fa_disable') : $lang->get('account_2fa_enable'); ?>
                            </button>
                        </div>
                    </div>
                </section>
                
                <!-- Login History Section -->
                <section class="account-section history-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="account_login_history"><?php echo $lang->get('account_login_history'); ?></h2>
                    </div>
                    
                    <div class="history-list">
                        <?php if (empty($loginHistory)): ?>
                        <div class="empty-state">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                            </svg>
                            <p data-i18n="account_no_login_history"><?php echo $lang->get('account_no_login_history'); ?></p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($loginHistory as $login): ?>
                        <div class="history-item">
                            <div class="history-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                                </svg>
                            </div>
                            <div class="history-content">
                                <div class="history-info">
                                    <span class="history-date"><?php echo date('d.m.Y H:i', strtotime($login['login_time'])); ?></span>
                                    <span class="history-ip"><?php echo htmlspecialchars($login['ip_address']); ?></span>
                                </div>
                                <div class="history-details">
                                    <span class="history-location"><?php echo htmlspecialchars($login['location']); ?></span>
                                    <span class="history-device"><?php echo htmlspecialchars($login['user_agent']); ?></span>
                                </div>
                            </div>
                            <div class="history-status">
                                <span class="status-badge <?php echo $login['status']; ?>">
                                    <?php echo $lang->get('login_status_' . $login['status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Account Stats Section -->
                <section class="account-section stats-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="account_statistics"><?php echo $lang->get('account_statistics'); ?></h2>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $userStats['total_characters'] ?? 0; ?></span>
                                <span class="stat-label" data-i18n="account_characters"><?php echo $lang->get('account_characters'); ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $userStats['total_playtime_hours'] ?? 0; ?>h</span>
                                <span class="stat-label" data-i18n="account_playtime"><?php echo $lang->get('account_playtime'); ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $userStats['total_logins'] ?? 0; ?></span>
                                <span class="stat-label" data-i18n="account_logins"><?php echo $lang->get('account_logins'); ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $userStats['max_level'] ?? 1; ?></span>
                                <span class="stat-label" data-i18n="account_max_level"><?php echo $lang->get('account_max_level'); ?></span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
    
    <!-- Scripts -->
    <script>
        // Pass PHP variables to JavaScript
        window.translations = <?php echo json_encode($lang->getAllTranslations()); ?>;
        window.ucpConfig = {
            siteUrl: '<?php echo SITE_URL; ?>',
            language: '<?php echo $lang->getCurrentLanguage(); ?>',
            user: <?php echo json_encode($currentUser); ?>,
            userStats: <?php echo json_encode($userStats); ?>
        };
    </script>
    <script src="js/main.js"></script>
    <script src="js/account.js"></script>
</body>
</html> 