<?php
define('UCP_ACCESS', true);
require_once 'config/config.php';
require_once 'php/classes/UCP.php';

$ucp = new UCP();
$lang = $ucp->getLanguage();
$currentUser = $ucp->getCurrentUser();
$recaptcha = new ReCaptcha();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('game_title'); ?> - User Control Panel</title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo SITE_KEYWORDS; ?>">
    <meta name="author" content="<?php echo SITE_NAME; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $lang->get('game_title'); ?> - UCP">
    <meta property="og:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/images/og-image.png">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $lang->get('game_title'); ?> - UCP">
    <meta name="twitter:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/images/twitter-card.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
    <link rel="manifest" href="images/site.webmanifest">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="js/main.js" as="script">
    <link rel="preload" href="css/main.css" as="style">
    
    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//www.google.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    
    <!-- reCAPTCHA -->
    <?php echo $recaptcha->generateScript(); ?>
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "<?php echo $lang->get('game_title'); ?> UCP",
        "description": "<?php echo SITE_DESCRIPTION; ?>",
        "url": "<?php echo SITE_URL; ?>",
        "applicationCategory": "GameApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR"
        },
        "author": {
            "@type": "Organization",
            "name": "<?php echo SITE_NAME; ?>"
        }
    }
    </script>
</head>
<body>
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
                    <a href="index.php" class="nav-link active" data-i18n="nav_home"><?php echo $lang->get('nav_home'); ?></a>
                    
                    <?php if ($currentUser): ?>
                        <a href="dashboard.php" class="nav-link" data-i18n="nav_dashboard"><?php echo $lang->get('nav_dashboard'); ?></a>
                        <a href="characters.php" class="nav-link" data-i18n="nav_characters"><?php echo $lang->get('nav_characters'); ?></a>
                        <a href="account.php" class="nav-link" data-i18n="nav_account"><?php echo $lang->get('nav_account'); ?></a>
                        <div class="user-menu">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <a href="logout.php" class="nav-link" data-i18n="nav_logout"><?php echo $lang->get('nav_logout'); ?></a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="nav-link" data-i18n="nav_login"><?php echo $lang->get('nav_login'); ?></a>
                        <a href="register.php" class="nav-link" data-i18n="nav_register"><?php echo $lang->get('nav_register'); ?></a>
                    <?php endif; ?>
                    
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
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" aria-label="Menu">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Hero Section -->
            <section class="hero">
                <div class="hero-content">
                    <h1 class="hero-title fade-in">
                        <?php echo $lang->get('welcome'); ?>
                        <span class="hero-title-accent"><?php echo $lang->get('game_title'); ?></span>
                    </h1>
                    <p class="hero-subtitle fade-in">
                        <?php echo $lang->get('game_description'); ?>
                    </p>
                    
                    <?php if (!$currentUser): ?>
                    <div class="hero-actions fade-in">
                        <a href="register.php" class="btn btn-primary btn-lg" data-i18n="nav_register">
                            <?php echo $lang->get('nav_register'); ?>
                        </a>
                        <a href="login.php" class="btn btn-secondary btn-lg" data-i18n="nav_login">
                            <?php echo $lang->get('nav_login'); ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="hero-actions fade-in">
                        <a href="dashboard.php" class="btn btn-primary btn-lg" data-i18n="nav_dashboard">
                            <?php echo $lang->get('nav_dashboard'); ?>
                        </a>
                        <a href="characters.php" class="btn btn-secondary btn-lg" data-i18n="nav_characters">
                            <?php echo $lang->get('nav_characters'); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="hero-image">
                    <img src="images/hero-character.png" alt="Game Character" class="hero-character">
                    <div class="hero-effects">
                        <div class="particle-effect"></div>
                        <div class="glow-effect"></div>
                    </div>
                </div>
            </section>
            
            <!-- Features Section -->
            <section class="features">
                <div class="section-header">
                    <h2 class="section-title"><?php echo $lang->get('features_title'); ?></h2>
                    <p class="section-subtitle"><?php echo $lang->get('features_subtitle'); ?></p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <h3 class="feature-title"><?php echo $lang->get('feature_secure_title'); ?></h3>
                        <p class="feature-description"><?php echo $lang->get('feature_secure_description'); ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <h3 class="feature-title"><?php echo $lang->get('feature_characters_title'); ?></h3>
                        <p class="feature-description"><?php echo $lang->get('feature_characters_description'); ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                        </div>
                        <h3 class="feature-title"><?php echo $lang->get('feature_community_title'); ?></h3>
                        <p class="feature-description"><?php echo $lang->get('feature_community_description'); ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                        </div>
                        <h3 class="feature-title"><?php echo $lang->get('feature_stats_title'); ?></h3>
                        <p class="feature-description"><?php echo $lang->get('feature_stats_description'); ?></p>
                    </div>
                </div>
            </section>
            
            <!-- Server Status Section -->
            <section class="server-status">
                <div class="section-header">
                    <h2 class="section-title"><?php echo $lang->get('server_status_title'); ?></h2>
                    <p class="section-subtitle"><?php echo $lang->get('server_status_subtitle'); ?></p>
                </div>
                
                <div class="server-info">
                    <div class="server-card">
                        <div class="server-indicator">
                            <span class="status-dot online"></span>
                            <span class="status-text" data-i18n="server_online"><?php echo $lang->get('server_online'); ?></span>
                        </div>
                        <div class="server-stats">
                            <div class="stat-item">
                                <span class="stat-label" data-i18n="server_players"><?php echo $lang->get('server_players'); ?></span>
                                <span class="stat-value" id="online-players">Loading...</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label" data-i18n="server_uptime"><?php echo $lang->get('server_uptime'); ?></span>
                                <span class="stat-value" id="server-uptime">Loading...</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label" data-i18n="server_version"><?php echo $lang->get('server_version'); ?></span>
                                <span class="stat-value" id="server-version">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- News Section -->
            <section class="news">
                <div class="section-header">
                    <h2 class="section-title"><?php echo $lang->get('news_title'); ?></h2>
                    <p class="section-subtitle"><?php echo $lang->get('news_subtitle'); ?></p>
                </div>
                
                <div class="news-grid">
                    <article class="news-card">
                        <div class="news-image">
                            <img src="images/news-1.jpg" alt="News 1">
                        </div>
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-date">2024-01-15</span>
                                <span class="news-category"><?php echo $lang->get('news_category_update'); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $lang->get('news_1_title'); ?></h3>
                            <p class="news-excerpt"><?php echo $lang->get('news_1_excerpt'); ?></p>
                            <a href="news-1.php" class="news-link"><?php echo $lang->get('read_more'); ?></a>
                        </div>
                    </article>
                    
                    <article class="news-card">
                        <div class="news-image">
                            <img src="images/news-2.jpg" alt="News 2">
                        </div>
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-date">2024-01-10</span>
                                <span class="news-category"><?php echo $lang->get('news_category_event'); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $lang->get('news_2_title'); ?></h3>
                            <p class="news-excerpt"><?php echo $lang->get('news_2_excerpt'); ?></p>
                            <a href="news-2.php" class="news-link"><?php echo $lang->get('read_more'); ?></a>
                        </div>
                    </article>
                    
                    <article class="news-card">
                        <div class="news-image">
                            <img src="images/news-3.jpg" alt="News 3">
                        </div>
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-date">2024-01-05</span>
                                <span class="news-category"><?php echo $lang->get('news_category_community'); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $lang->get('news_3_title'); ?></h3>
                            <p class="news-excerpt"><?php echo $lang->get('news_3_excerpt'); ?></p>
                            <a href="news-3.php" class="news-link"><?php echo $lang->get('read_more'); ?></a>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php echo $lang->get('game_title'); ?></h4>
                    <p><?php echo $lang->get('footer_description'); ?></p>
                    <div class="social-links">
                        <a href="<?php echo SOCIAL_FACEBOOK; ?>" class="social-link" aria-label="Facebook">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="<?php echo SOCIAL_TWITTER; ?>" class="social-link" aria-label="Twitter">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="<?php echo SOCIAL_DISCORD; ?>" class="social-link" aria-label="Discord">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419-.0003 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9554 2.4189-2.1568 2.4189Z"/>
                            </svg>
                        </a>
                        <a href="<?php echo SOCIAL_YOUTUBE; ?>" class="social-link" aria-label="YouTube">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4><?php echo $lang->get('footer_links_title'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="about.php" data-i18n="footer_about"><?php echo $lang->get('footer_about'); ?></a></li>
                        <li><a href="support.php" data-i18n="footer_support"><?php echo $lang->get('footer_support'); ?></a></li>
                        <li><a href="contact.php" data-i18n="footer_contact"><?php echo $lang->get('footer_contact'); ?></a></li>
                        <li><a href="changelog.php" data-i18n="changelog"><?php echo $lang->get('changelog'); ?></a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4><?php echo $lang->get('footer_legal_title'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="terms.php" data-i18n="footer_terms"><?php echo $lang->get('footer_terms'); ?></a></li>
                        <li><a href="privacy.php" data-i18n="footer_privacy"><?php echo $lang->get('footer_privacy'); ?></a></li>
                        <li><a href="cookies.php" data-i18n="footer_cookies"><?php echo $lang->get('footer_cookies'); ?></a></li>
                        <li><a href="imprint.php" data-i18n="footer_imprint"><?php echo $lang->get('footer_imprint'); ?></a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4><?php echo $lang->get('footer_download_title'); ?></h4>
                    <div class="download-links">
                        <a href="downloads/game-client.exe" class="download-link">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/>
                            </svg>
                            <?php echo $lang->get('download_client'); ?>
                        </a>
                        <a href="downloads/game-launcher.exe" class="download-link">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/>
                            </svg>
                            <?php echo $lang->get('download_launcher'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p data-i18n="footer_copyright"><?php echo $lang->get('footer_copyright'); ?></p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Pass PHP variables to JavaScript
        window.translations = <?php echo json_encode($lang->getAllTranslations()); ?>;
        window.ucpConfig = {
            siteUrl: '<?php echo SITE_URL; ?>',
            language: '<?php echo $lang->getCurrentLanguage(); ?>',
            recaptchaSiteKey: '<?php echo $recaptcha->getSiteKey(); ?>',
            isLoggedIn: <?php echo $currentUser ? 'true' : 'false'; ?>,
            user: <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>
        };
    </script>
    <script src="js/main.js"></script>
    <script src="js/server-status.js"></script>
    <script src="js/animations.js"></script>
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html> 