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

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$class_filter = $_GET['class'] ?? '';
$status_filter = $_GET['status'] ?? '';
$level_min = $_GET['level_min'] ?? '';
$level_max = $_GET['level_max'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// Get players data
$filters = [
    'search' => $search,
    'class' => $class_filter,
    'status' => $status_filter,
    'level_min' => $level_min,
    'level_max' => $level_max
];

$players = $ucp->getPlayersList($filters, $page, $per_page);
$total_players = $ucp->getTotalPlayersCount($filters);
$total_pages = ceil($total_players / $per_page);

// Get statistics
$online_count = $ucp->getOnlinePlayersCount();
$player_stats = $ucp->getPlayerStatistics();
$character_classes = getConfig('character.classes');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('players_title'); ?> - <?php echo $lang->get('game_title'); ?></title>
    <meta name="description" content="<?php echo $lang->get('players_subtitle'); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/players.css">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="js/main.js" as="script">
    <link rel="preload" href="js/players.js" as="script">
</head>
<body class="players-page">
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
                    <a href="players.php" class="nav-link active" data-i18n="nav_players"><?php echo $lang->get('nav_players'); ?></a>
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
            <!-- Page Header -->
            <section class="page-header">
                <div class="header-content">
                    <h1 class="page-title" data-i18n="players_title"><?php echo $lang->get('players_title'); ?></h1>
                    <p class="page-subtitle" data-i18n="players_subtitle"><?php echo $lang->get('players_subtitle'); ?></p>
                </div>
                
                <div class="header-stats">
                    <div class="stat-item">
                        <span class="stat-number online"><?php echo $online_count; ?></span>
                        <span class="stat-label" data-i18n="players_online"><?php echo $lang->get('players_online'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $total_players; ?></span>
                        <span class="stat-label" data-i18n="players_total"><?php echo $lang->get('players_total'); ?></span>
                    </div>
                </div>
            </section>
            
            <!-- Player Statistics -->
            <section class="players-stats">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number online"><?php echo $online_count; ?></span>
                            <span class="stat-label" data-i18n="players_online"><?php echo $lang->get('players_online'); ?></span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $player_stats['max_level']; ?></span>
                            <span class="stat-label" data-i18n="players_max_level"><?php echo $lang->get('players_max_level'); ?></span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo round($player_stats['average_level'], 1); ?></span>
                            <span class="stat-label" data-i18n="players_avg_level"><?php echo $lang->get('players_avg_level'); ?></span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg class="icon" viewBox="0 0 24 24">
                                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo $player_stats['new_today']; ?></span>
                            <span class="stat-label" data-i18n="players_new_today"><?php echo $lang->get('players_new_today'); ?></span>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Search and Filters -->
            <section class="players-filters">
                <form class="filters-form" method="GET" action="">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search" class="filter-label" data-i18n="players_search"><?php echo $lang->get('players_search'); ?></label>
                            <div class="search-input-container">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="<?php echo $lang->get('players_search_placeholder'); ?>"
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <svg class="icon search-icon" viewBox="0 0 24 24">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label for="class" class="filter-label" data-i18n="players_class"><?php echo $lang->get('players_class'); ?></label>
                            <select id="class" name="class" class="form-control">
                                <option value="" data-i18n="players_all_classes"><?php echo $lang->get('players_all_classes'); ?></option>
                                <?php foreach ($character_classes as $class): ?>
                                <option value="<?php echo $class; ?>" <?php echo $class_filter === $class ? 'selected' : ''; ?>>
                                    <?php echo $lang->get('class_' . strtolower($class)); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="status" class="filter-label" data-i18n="players_status"><?php echo $lang->get('players_status'); ?></label>
                            <select id="status" name="status" class="form-control">
                                <option value="" data-i18n="players_all_status"><?php echo $lang->get('players_all_status'); ?></option>
                                <option value="online" <?php echo $status_filter === 'online' ? 'selected' : ''; ?> data-i18n="players_online"><?php echo $lang->get('players_online'); ?></option>
                                <option value="offline" <?php echo $status_filter === 'offline' ? 'selected' : ''; ?> data-i18n="players_offline"><?php echo $lang->get('players_offline'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group level-range">
                            <label class="filter-label" data-i18n="players_level_range"><?php echo $lang->get('players_level_range'); ?></label>
                            <div class="level-inputs">
                                <input type="number" 
                                       name="level_min" 
                                       class="form-control" 
                                       placeholder="<?php echo $lang->get('players_level_min'); ?>"
                                       value="<?php echo htmlspecialchars($level_min); ?>"
                                       min="1"
                                       max="200">
                                <span class="level-separator">-</span>
                                <input type="number" 
                                       name="level_max" 
                                       class="form-control" 
                                       placeholder="<?php echo $lang->get('players_level_max'); ?>"
                                       value="<?php echo htmlspecialchars($level_max); ?>"
                                       min="1"
                                       max="200">
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary" data-i18n="players_filter">
                                <?php echo $lang->get('players_filter'); ?>
                            </button>
                            <a href="players.php" class="btn btn-secondary" data-i18n="players_reset_filters">
                                <?php echo $lang->get('players_reset_filters'); ?>
                            </a>
                        </div>
                    </div>
                </form>
            </section>
            
            <!-- Players List -->
            <section class="players-list">
                <?php if (empty($players)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                        </svg>
                    </div>
                    <h3 class="empty-title" data-i18n="players_no_results"><?php echo $lang->get('players_no_results'); ?></h3>
                    <p class="empty-description" data-i18n="players_no_results_description"><?php echo $lang->get('players_no_results_description'); ?></p>
                </div>
                <?php else: ?>
                <div class="players-grid">
                    <?php foreach ($players as $player): ?>
                    <div class="player-card" data-player-id="<?php echo $player['id']; ?>">
                        <div class="player-header">
                            <div class="player-avatar">
                                <img src="images/class-<?php echo strtolower($player['class']); ?>.png" 
                                     alt="<?php echo htmlspecialchars($player['name']); ?>"
                                     onerror="this.src='images/default-character.png'">
                                <div class="player-status <?php echo $player['online'] ? 'online' : 'offline'; ?>">
                                    <span class="status-dot"></span>
                                </div>
                            </div>
                            
                            <div class="player-info">
                                <h3 class="player-name"><?php echo htmlspecialchars($player['name']); ?></h3>
                                <p class="player-class"><?php echo $lang->get('class_' . strtolower($player['class'])); ?></p>
                                <div class="player-level">
                                    <span data-i18n="level"><?php echo $lang->get('level'); ?></span> <?php echo $player['level']; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="player-stats">
                            <div class="stat-row">
                                <span class="stat-label" data-i18n="players_location"><?php echo $lang->get('players_location'); ?></span>
                                <span class="stat-value"><?php echo htmlspecialchars($player['location']); ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label" data-i18n="players_guild"><?php echo $lang->get('players_guild'); ?></span>
                                <span class="stat-value"><?php echo $player['guild'] ? htmlspecialchars($player['guild']) : '-'; ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label" data-i18n="players_playtime"><?php echo $lang->get('players_playtime'); ?></span>
                                <span class="stat-value"><?php echo $player['playtime_hours']; ?>h</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label" data-i18n="players_last_seen"><?php echo $lang->get('players_last_seen'); ?></span>
                                <span class="stat-value">
                                    <?php 
                                    if ($player['online']) {
                                        echo '<span class="online-indicator">' . $lang->get('players_online_now') . '</span>';
                                    } else {
                                        echo date('d.m.Y H:i', strtotime($player['last_login']));
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="player-footer">
                            <div class="player-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($player['experience'] / $player['experience_needed']) * 100; ?>%"></div>
                                </div>
                                <span class="progress-text">
                                    <?php echo number_format($player['experience']); ?> / <?php echo number_format($player['experience_needed']); ?> XP
                                </span>
                            </div>
                            
                            <div class="player-actions">
                                <button class="btn btn-sm btn-secondary" onclick="viewPlayer(<?php echo $player['id']; ?>)" data-i18n="players_view_profile">
                                    <?php echo $lang->get('players_view_profile'); ?>
                                </button>
                                <?php if ($player['online']): ?>
                                <button class="btn btn-sm btn-primary" onclick="sendMessage(<?php echo $player['id']; ?>)" data-i18n="players_send_message">
                                    <?php echo $lang->get('players_send_message'); ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $current_url = $_SERVER['PHP_SELF'];
                    $get_params = $_GET;
                    
                    // Previous page
                    if ($page > 1):
                        $get_params['page'] = $page - 1;
                        $prev_url = $current_url . '?' . http_build_query($get_params);
                    ?>
                    <a href="<?php echo $prev_url; ?>" class="pagination-btn prev" data-i18n="pagination_prev">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M15.41 16.09l-4.58-4.59 4.58-4.59L14 5.5l-6 6 6 6z"/>
                        </svg>
                        <?php echo $lang->get('pagination_prev'); ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Page numbers -->
                    <div class="pagination-numbers">
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                            $get_params['page'] = $i;
                            $page_url = $current_url . '?' . http_build_query($get_params);
                        ?>
                        <a href="<?php echo $page_url; ?>" class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Next page -->
                    <?php if ($page < $total_pages):
                        $get_params['page'] = $page + 1;
                        $next_url = $current_url . '?' . http_build_query($get_params);
                    ?>
                    <a href="<?php echo $next_url; ?>" class="pagination-btn next" data-i18n="pagination_next">
                        <?php echo $lang->get('pagination_next'); ?>
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M8.59 16.34l4.58-4.59-4.58-4.59L10 5.75l6 6-6 6z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <!-- Player Profile Modal -->
    <div class="modal" id="player-profile-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" data-i18n="players_player_profile"><?php echo $lang->get('players_player_profile'); ?></h2>
                <button class="modal-close" type="button" aria-label="Close">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body" id="player-profile-content">
                <!-- Content will be loaded via JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Send Message Modal -->
    <div class="modal" id="send-message-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" data-i18n="players_send_message"><?php echo $lang->get('players_send_message'); ?></h2>
                <button class="modal-close" type="button" aria-label="Close">
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
            
            <form class="modal-body" id="send-message-form">
                <div class="form-group">
                    <label for="message_text" class="form-label" data-i18n="players_message"><?php echo $lang->get('players_message'); ?></label>
                    <textarea id="message_text" 
                              name="message" 
                              class="form-control" 
                              rows="5" 
                              placeholder="<?php echo $lang->get('players_message_placeholder'); ?>"
                              required></textarea>
                </div>
            </form>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-i18n="cancel">
                    <?php echo $lang->get('cancel'); ?>
                </button>
                <button type="submit" form="send-message-form" class="btn btn-primary" data-i18n="players_send">
                    <?php echo $lang->get('players_send'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Pass PHP variables to JavaScript
        window.translations = <?php echo json_encode($lang->getAllTranslations()); ?>;
        window.ucpConfig = {
            siteUrl: '<?php echo SITE_URL; ?>',
            language: '<?php echo $lang->getCurrentLanguage(); ?>',
            user: <?php echo json_encode($currentUser); ?>,
            players: <?php echo json_encode($players); ?>,
            pagination: {
                currentPage: <?php echo $page; ?>,
                totalPages: <?php echo $total_pages; ?>,
                totalPlayers: <?php echo $total_players; ?>
            }
        };
        
        // Player interaction functions
        function viewPlayer(playerId) {
            // Load player profile via AJAX
            fetch('api/player-profile.php?id=' + playerId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('player-profile-content').innerHTML = data.html;
                        openModal('player-profile-modal');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error loading player profile', 'error');
                });
        }
        
        function sendMessage(playerId) {
            // Open send message modal
            document.getElementById('send-message-form').dataset.playerId = playerId;
            openModal('send-message-modal');
        }
    </script>
    <script src="js/main.js"></script>
    <script src="js/players.js"></script>
</body>
</html> 