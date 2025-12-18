<?php
// includes/header.php
// Ensure session is started so $_SESSION is available in the header
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (!isset($hide_header)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Load site name from settings if available
    require_once __DIR__ . '/settings.php';
    $siteName = getSetting('site_name', defined('SITE_NAME') ? SITE_NAME : 'SRMIS');
    $siteLogo = getSetting('site_logo', null);
    ?>
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/styles.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php if (!isset($hide_navigation)): ?>
    <header class="bg-white shadow-sm sticky top-0 z-20 <?php echo isset($_SESSION['user_id']) ? 'lg:ml-64 w-full lg:w-[calc(100%-16rem)]' : ''; ?>">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-3 items-center py-4">
                <div class="flex items-center">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="lg:hidden mr-4 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <?php if (!empty($siteLogo) && file_exists(__DIR__ . '/../assets/uploads/logos/' . $siteLogo)): ?>
                        <img src="../assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" alt="logo" class="h-10 mr-3 object-contain" />
                    <?php endif; ?>
                    <div class="md:hidden">
                        <h1 class="text-xl font-bold text-blue-600 truncate max-w-[150px]"><?php echo htmlspecialchars($siteName); ?></h1>
                    </div>
                </div>

                <div class="hidden md:flex justify-center text-center">
                    <h1 class="text-2xl font-bold text-blue-600 truncate"><?php echo htmlspecialchars($siteName); ?></h1>
                </div>
                <!-- Sidebar Toggle Script -->
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const btn = document.getElementById('mobile-menu-btn');
                        const sidebar = document.getElementById('sidebar');
                        if(btn && sidebar) {
                            btn.addEventListener('click', () => {
                                sidebar.classList.toggle('-translate-x-full');
                            });
                        }
                    });
                </script>
                <div class="flex items-center justify-end space-x-4">
                    <?php
                        // Avoid undefined array key notices by checking existence
                        if (isset($_SESSION['name'])) {
                            $displayName = $_SESSION['name'];
                        } elseif (isset($_SESSION['username'])) {
                            $displayName = $_SESSION['username'];
                        } else {
                            $displayName = 'Guest';
                        }

                        // Try to fetch avatar filename if logged in
                        $avatarUrl = null;
                        if (isset($_SESSION['user_id'])) {
                            // attempt quick DB lookup (silent failures)
                            try {
                                require_once __DIR__ . '/../config/database.php';
                                $db = getDBConnection();
                                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN) {
                                    $stmt = $db->prepare('SELECT avatar FROM admins WHERE id = ?');
                                } else {
                                    $stmt = $db->prepare('SELECT avatar FROM students WHERE id = ?');
                                }
                                $stmt->bind_param('i', $_SESSION['user_id']);
                                $stmt->execute();
                                $res = $stmt->get_result()->fetch_assoc();
                                $stmt->close();
                                $db->close();
                                if (!empty($res['avatar'])) {
                                    $avatarUrl = '../assets/uploads/avatars/' . $res['avatar'];
                                }
                            } catch (Exception $e) {
                                // ignore DB errors in header
                                $avatarUrl = null;
                            }
                        }
                    ?>
                    <?php if ($avatarUrl): ?>
                        <img src="<?php echo $avatarUrl; ?>" alt="avatar" class="w-8 h-8 rounded-full object-cover" />
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm text-gray-700">
                            <?php echo htmlspecialchars(substr($displayName, 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>
<?php } ?>