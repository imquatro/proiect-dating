<?php
if (!isset($activePage)) { $activePage = ''; }
if (!isset($content)) { $content = ''; }
if (!isset($pageTitle)) { $pageTitle = ''; }
if (!isset($pageCss)) { $pageCss = ''; }
if (!isset($extraJs)) { $extraJs = ''; }
if (!isset($extraCss)) { $extraCss = []; }
if (!isset($baseHref)) {
    $path = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $depth = $path === '' ? 0 : substr_count($path, '/');
    $baseHref = $depth ? str_repeat('../', $depth) : './';
}
if (!isset($hideNav)) { $hideNav = false; }
require_once __DIR__ . '/includes/cache_buster.php';
    $profilePhoto = 'default-avatar.png';
    $userLevel = 1;
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/includes/db.php';
        $stmt = $db->prepare('SELECT gallery, level FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
            if (!empty($gallery)) {
                $candidate = 'uploads/' . $_SESSION['user_id'] . '/' . $gallery[0];
                if (is_file($candidate)) {
                    $profilePhoto = $candidate;
                }
            }
            if (isset($user['level'])) {
                $userLevel = (int)$user['level'];
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
    if (sessionStorage.getItem('navFading')) {
        document.documentElement.classList.add('nav-fade');
    }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <base href="<?= htmlspecialchars($baseHref) ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/template.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/message-notification.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/nav-transition.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/loading-variants.css') ?>">
    <link rel="stylesheet" href="<?= asset('moneysistem/money.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/xp-float.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/level-up.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/user-level-card.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets_css/helper-buddy.css') ?>">
    <?php if ($pageCss): ?>
    <link rel="stylesheet" href="<?= asset($pageCss) ?>">
    <?php endif; ?>
    <?php foreach ((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= asset($css) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="app-frame">
    <div class="top-bar">
        <a href="diverse.php" class="nav-btn top-btn"><i class="fas fa-ellipsis-h"></i></a>
        <a href="mesaje.php" class="nav-btn top-btn center-btn"><i class="fas fa-envelope"></i><span id="messageIndicator" class="message-indicator"></span></a>
        <div class="top-right">
            <?php include __DIR__ . '/moneysistem/money.php'; ?>
            <div class="profile-box"><img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile"></div>
        </div>
    </div>
    <div class="content">
        <?= $content ?>
    </div>
    <?php if (!$hideNav): ?>
    <nav class="bottom-nav">
        <a href="welcome.php" class="nav-btn <?php if($activePage==='welcome') echo 'active';?>"><i class="fas fa-seedling"></i></a>
        <a href="barn.php" class="nav-btn <?php if($activePage==='barn') echo 'active';?>"><i class="fas fa-warehouse"></i></a>
        <a href="friends.php" class="nav-btn <?php if($activePage==='friends') echo 'active';?>"><i class="fas fa-user-friends"></i><span id="friendIndicator" class="friend-indicator"></span></a>
        <a href="pvp_battles.php" class="nav-btn <?php if($activePage==='pvp') echo 'active';?>"><i class="fas fa-trophy"></i></a>
        <a href="vip.php" class="nav-btn <?php if($activePage==='vip') echo 'active';?>"><i class="fas fa-crown"></i></a>
        <a href="settings.php" class="nav-btn <?php if($activePage==='settings') echo 'active';?>"><i class="fas fa-cog"></i></a>
    </nav>
    <?php endif; ?>
</div>
<div id="level-up-card"></div>
<div id="slot-panel-overlay"></div>

<?php 
// Initialize $noScroll if not set
if (!isset($noScroll)) {
    $noScroll = false;
}
if (!$noScroll): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.content');
    let isDown = false;
    let startY;
    let scrollTop;

    content.addEventListener('mousedown', function(e) {
        isDown = true;
        startY = e.pageY - content.offsetTop;
        scrollTop = content.scrollTop;
    });

    content.addEventListener('mouseleave', function() {
        isDown = false;
    });

    content.addEventListener('mouseup', function() {
        isDown = false;
    });

    content.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        const y = e.pageY - content.offsetTop;
        const walk = y - startY;
        content.scrollTop = scrollTop - walk;
    });

    content.addEventListener('touchstart', function(e) {
        startY = e.touches[0].pageY - content.offsetTop;
        scrollTop = content.scrollTop;
    }, { passive: true });

    content.addEventListener('touchmove', function(e) {
        const y = e.touches[0].pageY - content.offsetTop;
        const walk = y - startY;
        content.scrollTop = scrollTop - walk;
    }, { passive: true });
});
</script>
<?php endif; ?>
  <script src="<?= asset('assets_js/base-url.js') ?>"></script>
  <script src="<?= asset('assets_js/message-notification.js') ?>"></script>
  <script src="<?= asset('assets_js/friend-request-notification.js') ?>"></script>
  <script src="<?= asset('moneysistem/money.js') ?>"></script>
  <script src="<?= asset('assets_js/interaction-blocker.js') ?>"></script>
  <script src="<?= asset('assets_js/xp-float.js') ?>"></script>
  <script src="<?= asset('assets_js/level-up.js') ?>"></script>
  <script src="<?= asset('assets_js/helper-buddy.js') ?>"></script>
  <script src="<?= asset('assets_js/nav-transition.js') ?>"></script>
  <?php if (isset($_SESSION['user_id'])): ?>
  <?php
    // Load user's loading style preference from database
    $userLoadingStyle = 'variant-1'; // default
    try {
        $stmt = $db->prepare('SELECT loading_style FROM user_preferences WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $savedStyle = $stmt->fetchColumn();
        if ($savedStyle) {
            $userLoadingStyle = $savedStyle;
        }
    } catch (Exception $e) {
        // Use default if error
    }
  ?>
  <script>
    window.userId = <?= (int)$_SESSION['user_id']; ?>; 
    window.currentLevel = <?= $userLevel; ?>;
    // Set loading style preference from PHP (already loaded from database)
    localStorage.setItem('loadingStyle', '<?= $userLoadingStyle; ?>');
  </script>
  <?php endif; ?>
  <?php
  if ($extraJs) {
      if (is_array($extraJs)) {
          foreach ($extraJs as $js) {
              echo '<script src="' . asset($js) . '"></script>';
          }
      } else {
          $extraJs = preg_replace_callback(
              '/<script\\s+[^>]*src=["\\\']([^"\\\']+)["\\\'][^>]*><\\/script>/i',
              fn($m) => '<script src="' . asset($m[1]) . '"></script>',
              $extraJs
          );
          echo $extraJs;
      }
  }
  ?>
</body>
</html>
