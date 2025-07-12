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

// Get user data and statistics
$userStats = $ucp->getUserStats($currentUser['id']);
$characters = $ucp->getUserCharacters($currentUser['id']);
$recentActivities = $ucp->getRecentActivities($currentUser['id'], 10);
$serverStatus = $ucp->getServerStatus();
$onlinePlayers = $ucp->getOnlinePlayersCount();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('dashboard_title'); ?> - <?php echo $lang->get('game_title'); ?></title>
    <meta name="description" content="<?php echo $lang->get('dashboard_subtitle'); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="js/main.js" as="script">
    <link rel="preload" href="js/dashboard.js" as="script">
</head>
<body class="dashboard-page">
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
                    <a href="dashboard.php" class="nav-link active" data-i18n="nav_dashboard"><?php echo $lang->get('nav_dashboard'); ?></a>
                    <a href="characters.php" class="nav-link" data-i18n="nav_characters"><?php echo $lang->get('nav_characters'); ?></a>
                    <a href="players.php" class="nav-link" data-i18n="nav_players"><?php echo $lang->get('nav_players'); ?></a>
                    <a href="account.php" class="nav-link" data-i18n="nav_account"><?php echo $lang->get('nav_account'); ?></a>
                    
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
            <!-- Welcome Section -->
            <section class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">
                        <?php echo sprintf($lang->get('dashboard_welcome'), htmlspecialchars($currentUser['username'])); ?>
                    </h1>
                    <p class="welcome-subtitle" data-i18n="dashboard_subtitle">
                        <?php echo $lang->get('dashboard_subtitle'); ?>
                    </p>
                    <div class="welcome-stats">
                        <div class="welcome-stat">
                            <span class="stat-label" data-i18n="dashboard_last_login"><?php echo $lang->get('dashboard_last_login'); ?></span>
                            <span class="stat-value"><?php echo date('d.m.Y H:i', strtotime($currentUser['last_login'])); ?></span>
                        </div>
                        <div class="welcome-stat">
                            <span class="stat-label" data-i18n="dashboard_member_since"><?php echo $lang->get('dashboard_member_since'); ?></span>
                            <span class="stat-value"><?php echo date('d.m.Y', strtotime($currentUser['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="welcome-image">
                    <img src="images/dashboard-hero.png" alt="Dashboard Hero" class="hero-image">
                </div>
            </section>
            
            <!-- Quick Stats -->
            <section class="quick-stats">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo count($characters); ?></span>
                            <span class="stat-label" data-i18n="dashboard_characters"><?php echo $lang->get('dashboard_characters'); ?></span>
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
                            <span class="stat-label" data-i18n="dashboard_playtime"><?php echo $lang->get('dashboard_playtime'); ?></span>
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
                            <span class="stat-label" data-i18n="dashboard_max_level"><?php echo $lang->get('dashboard_max_level'); ?></span>
                        </div>
                    </div>
                    
                    <div class="stat-card server-status">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number online"><?php echo $onlinePlayers; ?></span>
                            <span class="stat-label" data-i18n="dashboard_online_players"><?php echo $lang->get('dashboard_online_players'); ?></span>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Main Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Characters Section -->
                <section class="dashboard-section characters-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="dashboard_characters"><?php echo $lang->get('dashboard_characters'); ?></h2>
                        <a href="characters.php" class="section-link" data-i18n="view_all"><?php echo $lang->get('view_all'); ?></a>
                    </div>
                    
                    <div class="characters-preview">
                        <?php if (empty($characters)): ?>
                        <div class="empty-state">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                            <h3 data-i18n="no_characters_title"><?php echo $lang->get('no_characters_title'); ?></h3>
                            <p data-i18n="no_characters_description"><?php echo $lang->get('no_characters_description'); ?></p>
                            <a href="characters.php" class="btn btn-primary" data-i18n="characters_create"><?php echo $lang->get('characters_create'); ?></a>
                        </div>
                        <?php else: ?>
                        <div class="character-cards">
                            <?php foreach (array_slice($characters, 0, 3) as $character): ?>
                            <div class="character-card">
                                <div class="character-avatar">
                                    <img src="images/class-<?php echo strtolower($character['class']); ?>.png" 
                                         alt="<?php echo htmlspecialchars($character['name']); ?>"
                                         onerror="this.src='images/default-character.png'">
                                    <div class="character-status <?php echo $character['online'] ? 'online' : 'offline'; ?>">
                                        <span class="status-dot"></span>
                                    </div>
                                </div>
                                <div class="character-info">
                                    <h3 class="character-name"><?php echo htmlspecialchars($character['name']); ?></h3>
                                    <p class="character-class"><?php echo $lang->get('class_' . strtolower($character['class'])); ?></p>
                                    <div class="character-level">
                                        <span data-i18n="level"><?php echo $lang->get('level'); ?></span> <?php echo $character['level']; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Server Status Section -->
                <section class="dashboard-section server-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="dashboard_server_status"><?php echo $lang->get('dashboard_server_status'); ?></h2>
                        <span class="server-indicator <?php echo $serverStatus['status']; ?>">
                            <span class="status-dot"></span>
                            <span class="status-text"><?php echo $lang->get('server_' . $serverStatus['status']); ?></span>
                        </span>
                    </div>
                    
                    <div class="server-info">
                        <div class="server-stats">
                            <div class="server-stat">
                                <span class="stat-label" data-i18n="server_players"><?php echo $lang->get('server_players'); ?></span>
                                <span class="stat-value"><?php echo $onlinePlayers; ?>/<?php echo $serverStatus['max_players']; ?></span>
                            </div>
                            <div class="server-stat">
                                <span class="stat-label" data-i18n="server_uptime"><?php echo $lang->get('server_uptime'); ?></span>
                                <span class="stat-value"><?php echo $serverStatus['uptime']; ?></span>
                            </div>
                            <div class="server-stat">
                                <span class="stat-label" data-i18n="server_version"><?php echo $lang->get('server_version'); ?></span>
                                <span class="stat-value"><?php echo $serverStatus['version']; ?></span>
                            </div>
                        </div>
                        
                        <div class="quick-actions">
                            <a href="players.php" class="btn btn-secondary btn-sm" data-i18n="view_players">
                                <?php echo $lang->get('view_players'); ?>
                            </a>
                            <a href="downloads.php" class="btn btn-secondary btn-sm" data-i18n="download_client">
                                <?php echo $lang->get('download_client'); ?>
                            </a>
                        </div>
                    </div>
                </section>
                
                <!-- Recent Activities Section -->
                <section class="dashboard-section activities-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="dashboard_recent_activities"><?php echo $lang->get('dashboard_recent_activities'); ?></h2>
                    </div>
                    
                    <div class="activities-list">
                        <?php if (empty($recentActivities)): ?>
                        <div class="empty-state small">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                            </svg>
                            <p data-i18n="no_recent_activities"><?php echo $lang->get('no_recent_activities'); ?></p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <svg class="icon" viewBox="0 0 24 24">
                                    <?php echo $activity['icon']; ?>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?php echo $activity['description']; ?></div>
                                <div class="activity-time"><?php echo $activity['time_ago']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- News Section -->
                <section class="dashboard-section news-section">
                    <div class="section-header">
                        <h2 class="section-title" data-i18n="dashboard_news"><?php echo $lang->get('dashboard_news'); ?></h2>
                        <a href="news.php" class="section-link" data-i18n="view_all"><?php echo $lang->get('view_all'); ?></a>
                    </div>
                    
                    <div class="news-preview">
                        <article class="news-item">
                            <div class="news-meta">
                                <span class="news-date">2024-01-15</span>
                                <span class="news-category"><?php echo $lang->get('news_category_update'); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $lang->get('news_1_title'); ?></h3>
                            <p class="news-excerpt"><?php echo $lang->get('news_1_excerpt'); ?></p>
                        </article>
                        
                        <article class="news-item">
                            <div class="news-meta">
                                <span class="news-date">2024-01-10</span>
                                <span class="news-category"><?php echo $lang->get('news_category_event'); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $lang->get('news_2_title'); ?></h3>
                            <p class="news-excerpt"><?php echo $lang->get('news_2_excerpt'); ?></p>
                        </article>
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
            serverStatus: <?php echo json_encode($serverStatus); ?>
        };
    </script>
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html> 