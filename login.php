<?php
define('UCP_ACCESS', true);
require_once 'config/config.php';
require_once 'php/classes/UCP.php';

$ucp = new UCP();
$lang = $ucp->getLanguage();
$recaptcha = new ReCaptcha();

// Redirect if already logged in
if ($ucp->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$resetSent = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $result = $ucp->login($_POST);
            
            if ($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        } elseif ($_POST['action'] === 'forgot_password') {
            $result = $ucp->sendPasswordReset($_POST['email']);
            
            if ($result['success']) {
                $success = $result['message'];
                $resetSent = true;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('login_title'); ?> - <?php echo $lang->get('game_title'); ?></title>
    <meta name="description" content="<?php echo $lang->get('login_subtitle'); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/forms.css">
    
    <!-- reCAPTCHA -->
    <?php echo $recaptcha->generateScript(); ?>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="js/main.js" as="script">
    <link rel="preload" href="js/form-validation.js" as="script">
</head>
<body class="auth-page">
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
                    <a href="index.php" class="nav-link" data-i18n="nav_home"><?php echo $lang->get('nav_home'); ?></a>
                    <a href="login.php" class="nav-link active" data-i18n="nav_login"><?php echo $lang->get('nav_login'); ?></a>
                    <a href="register.php" class="nav-link" data-i18n="nav_register"><?php echo $lang->get('nav_register'); ?></a>
                    
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
            <div class="auth-container">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <h1 class="auth-title" data-i18n="login_title"><?php echo $lang->get('login_title'); ?></h1>
                        <p class="auth-subtitle" data-i18n="login_subtitle"><?php echo $lang->get('login_subtitle'); ?></p>
                    </div>
                    
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
                    
                    <!-- Login Form -->
                    <form class="auth-form" id="login-form" method="POST" style="<?php echo $resetSent ? 'display: none;' : ''; ?>">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="form-group">
                            <label for="username" class="form-label" data-i18n="login_username"><?php echo $lang->get('login_username'); ?></label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="<?php echo $lang->get('login_username'); ?>"
                                   required
                                   autocomplete="username"
                                   autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label" data-i18n="login_password"><?php echo $lang->get('login_password'); ?></label>
                            <div class="password-input-container">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control" 
                                       placeholder="<?php echo $lang->get('login_password'); ?>"
                                       required
                                       autocomplete="current-password">
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    <svg class="icon icon-eye" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                    <svg class="icon icon-eye-off" viewBox="0 0 24 24" style="display: none;">
                                        <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" class="checkbox-label">
                                    <span class="checkbox-custom"></span>
                                    <span class="checkbox-text" data-i18n="login_remember"><?php echo $lang->get('login_remember'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- reCAPTCHA -->
                        <div class="form-group">
                            <div class="recaptcha-container">
                                <div class="recaptcha-notice">
                                    <svg class="icon" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    <span>reCAPTCHA protected</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg" data-i18n="login_submit">
                            <?php echo $lang->get('login_submit'); ?>
                        </button>
                        
                        <div class="auth-links">
                            <button type="button" class="link-button" id="forgot-password-btn" data-i18n="login_forgot">
                                <?php echo $lang->get('login_forgot'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Forgot Password Form -->
                    <form class="auth-form" id="forgot-password-form" method="POST" style="<?php echo $resetSent ? '' : 'display: none;'; ?>">
                        <input type="hidden" name="action" value="forgot_password">
                        
                        <div class="forgot-password-header">
                            <h2 class="form-title" data-i18n="forgot_password_title"><?php echo $lang->get('forgot_password_title'); ?></h2>
                            <p class="form-subtitle" data-i18n="forgot_password_subtitle"><?php echo $lang->get('forgot_password_subtitle'); ?></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="reset_email" class="form-label" data-i18n="register_email"><?php echo $lang->get('register_email'); ?></label>
                            <input type="email" 
                                   id="reset_email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="<?php echo $lang->get('register_email'); ?>"
                                   required
                                   autocomplete="email">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg" data-i18n="forgot_password_submit">
                            <?php echo $lang->get('forgot_password_submit'); ?>
                        </button>
                        
                        <div class="auth-links">
                            <button type="button" class="link-button" id="back-to-login-btn" data-i18n="back_to_login">
                                <?php echo $lang->get('back_to_login'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!$resetSent): ?>
                    <div class="auth-footer">
                        <p class="auth-switch">
                            <span data-i18n="register_login_text"><?php echo $lang->get('register_login_text'); ?></span>
                            <a href="register.php" class="auth-link" data-i18n="nav_register"><?php echo $lang->get('nav_register'); ?></a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="auth-image-container">
                    <div class="auth-image">
                        <img src="images/auth-character.png" alt="Game Character" class="auth-character">
                        <div class="auth-effects">
                            <div class="particle-effect"></div>
                            <div class="glow-effect"></div>
                        </div>
                    </div>
                    
                    <div class="auth-info">
                        <h3><?php echo $lang->get('login_features_title'); ?></h3>
                        <ul class="benefits-list">
                            <li>
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span><?php echo $lang->get('login_feature_1'); ?></span>
                            </li>
                            <li>
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <span><?php echo $lang->get('login_feature_2'); ?></span>
                            </li>
                            <li>
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                                <span><?php echo $lang->get('login_feature_3'); ?></span>
                            </li>
                            <li>
                                <svg class="icon" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span><?php echo $lang->get('login_feature_4'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
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
            recaptchaSiteKey: '<?php echo $recaptcha->getSiteKey(); ?>',
            resetSent: <?php echo $resetSent ? 'true' : 'false'; ?>
        };
        
        // Toggle between login and forgot password forms
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const forgotForm = document.getElementById('forgot-password-form');
            const forgotBtn = document.getElementById('forgot-password-btn');
            const backBtn = document.getElementById('back-to-login-btn');
            
            if (forgotBtn) {
                forgotBtn.addEventListener('click', function() {
                    loginForm.style.display = 'none';
                    forgotForm.style.display = 'block';
                    document.getElementById('reset_email').focus();
                });
            }
            
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    forgotForm.style.display = 'none';
                    loginForm.style.display = 'block';
                    document.getElementById('username').focus();
                });
            }
        });
    </script>
    <script src="js/main.js"></script>
    <script src="js/form-validation.js"></script>
</body>
</html> 