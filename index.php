<?php
/**
 * index.php - Modern Landing Page for SRMIS
 * This file serves as the main entry point for the application,
 * providing a professional overview and quick access to various portals.
 */
require_once 'config/environment.php';
require_once 'config/security.php';
require_once 'includes/settings.php';

// Set security headers
setSecurityHeaders();

// Check if system is installed
if (!file_exists('config/database.php')) {
    header('Location: setup/install.php');
    exit;
}

$siteName = getSetting('site_name', 'SRMIS');
$siteLogo = getSetting('site_logo', null);

// Dynamic Hero Content
$heroTitle = getSetting('hero_title', 'Revolutionizing Student Success One Grade at a Time.');
$heroSubtitle = getSetting('hero_subtitle', 'Empower your institution with a high-performance management system for tracking results, managing students, and delivering academic excellence.');

// Dynamic Features
$feature1Title = getSetting('feature_1_title', 'Dynamic Reporting');
$feature1Desc = getSetting('feature_1_desc', 'Generate instantly readable result slips and progress reports for every student.');
$feature2Title = getSetting('feature_2_title', 'Bulk Data Entry');
$feature2Desc = getSetting('feature_2_desc', 'Save hours with our optimized bulk resulting tools for teachers and staff.');
$feature3Title = getSetting('feature_3_title', 'Secure by Design');
$feature3Desc = getSetting('feature_3_desc', 'Role-based access control and modern security practices protect your data.');

// Fetch Real-time Stats
$totalStudents = 0;
$totalTeachers = 0;
$totalResults = 0;

try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    if ($conn) {
        $totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0;
        $totalTeachers = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'] ?? 0;
        $totalResults = $conn->query("SELECT COUNT(*) as count FROM results")->fetch_assoc()['count'] ?? 0;
        $conn->close();
    }
} catch (Exception $e) {
    // Falls back to 0 if DB connection fails
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Premium Education Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS for Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        .portal-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .portal-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .stat-card {
            background: rgba(255, 255, 255, 1);
            border: 1px solid rgba(243, 244, 246, 1);
        }
        .exclusive-badge {
            background: linear-gradient(90deg, #3b82f6, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
    </style>
</head>
<body class="bg-[#fcfdff] text-gray-900 overflow-x-hidden">

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <?php if ($siteLogo && file_exists('assets/uploads/logos/' . $siteLogo)): ?>
                        <img src="assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo" class="h-10 w-auto">
                    <?php else: ?>
                        <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-200">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-2xl font-bold tracking-tight text-gray-900">
                        <span class="text-blue-600"><?php echo substr($siteName, 0, 1); ?></span><?php echo substr($siteName, 1); ?>
                    </span>
                </div>
                <div class="hidden md:flex items-center space-x-10">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 font-semibold transition tracking-wide">Features</a>
                    <a href="#stats" class="text-gray-600 hover:text-blue-600 font-semibold transition tracking-wide">Impact</a>
                    <a href="#portals" class="text-gray-600 hover:text-blue-600 font-semibold transition tracking-wide">Portals</a>
                    <a href="login.php" class="bg-blue-600 text-white px-7 py-3 rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 transform active:scale-95">
                        Log In
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-40 pb-24 md:pt-56 md:pb-40 overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_center,rgba(59,130,246,0.08)_0%,rgba(255,255,255,0)_70%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
            <span class="inline-flex items-center py-1.5 px-4 rounded-full bg-blue-50 text-blue-700 text-xs font-bold mb-8 tracking-widest uppercase border border-blue-100 shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-blue-600 mr-2"></span> System Online
            </span>
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight lg:leading-[1.1] mb-8">
                <?php 
                    $titleParts = explode(' ', $heroTitle);
                    if (count($titleParts) > 2) {
                        $lastWord = array_pop($titleParts);
                        $secondLastWord = array_pop($titleParts);
                        echo htmlspecialchars(implode(' ', $titleParts)) . ' <span class="exclusive-badge">' . htmlspecialchars($secondLastWord . ' ' . $lastWord) . '</span>';
                    } else {
                        echo htmlspecialchars($heroTitle);
                    }
                ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-500 max-w-3xl mx-auto mb-12 leading-relaxed font-light">
                <?php echo htmlspecialchars($heroSubtitle); ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                <a href="#portals" class="w-full sm:w-auto bg-blue-600 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-blue-700 transition transform hover:scale-105 shadow-2xl shadow-blue-200">
                    Enter Portal <i class="fas fa-arrow-right ml-3 text-sm"></i>
                </a>
                <a href="#features" class="w-full sm:w-auto bg-white text-gray-700 border border-gray-200 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-gray-50 transition shadow-sm">
                    Discover More
                </a>
            </div>
        </div>
    </header>

    <!-- Statistics Section -->
    <section id="stats" class="py-12 bg-white border-y border-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div data-aos="zoom-in" data-aos-delay="100">
                    <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo number_format($totalStudents); ?>+</p>
                    <p class="text-gray-500 font-medium">Active Students</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="200">
                    <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo number_format($totalTeachers); ?></p>
                    <p class="text-gray-500 font-medium">Expert Teachers</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="300">
                    <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo number_format($totalResults); ?>+</p>
                    <p class="text-gray-500 font-medium">Published Results</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="400">
                    <p class="text-4xl font-bold text-gray-900 mb-1">99.9%</p>
                    <p class="text-gray-500 font-medium">System Uptime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="py-24 bg-[#fcfdff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Why SRMIS Excellence?</h2>
                <div class="w-24 h-2 bg-blue-600 mx-auto rounded-full mb-6"></div>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">Built with modern tech and pedagogical insights to serve your educational ecosystem.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-50 hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-8">
                        <i class="fas fa-microchip text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Automation</h3>
                    <p class="text-gray-500 leading-relaxed font-light">Reduce manual grading errors by up to 90% with our automated calculation engines.</p>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-50 hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mb-8">
                        <i class="fas fa-chart-area text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Deep Analytics</h3>
                    <p class="text-gray-500 leading-relaxed font-light">Identify struggling students early with intuitive performance tracking and heatmaps.</p>
                </div>
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-50 hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mb-8">
                        <i class="fas fa-fingerprint text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Rock Solid Security</h3>
                    <p class="text-gray-500 leading-relaxed font-light">Encrypted data storage and multi-level authentication ensuring your records are safe.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portals Section -->
    <section id="portals" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Portal</h2>
                <div class="w-20 h-1.5 bg-blue-600 mx-auto rounded-full"></div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Student Portal -->
                <div class="portal-card bg-gray-50/50 p-10 rounded-[3rem] border border-gray-100 flex flex-col items-center text-center" data-aos="fade-right">
                    <div class="w-20 h-20 bg-blue-100 rounded-3xl flex items-center justify-center mb-8 shadow-inner">
                        <i class="fas fa-user-graduate text-blue-600 text-4xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-4">Students</h3>
                    <p class="text-gray-500 mb-10 leading-relaxed font-light">
                        Track your grades, download result slips, and manage your academic progression records.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-4 bg-white border-2 border-blue-600 text-blue-600 rounded-2xl font-bold hover:bg-blue-600 hover:text-white transition shadow-lg shadow-blue-50/50">
                        Login as Student
                    </a>
                </div>

                <!-- Teacher Portal -->
                <div class="portal-card bg-blue-600 p-10 rounded-[3rem] text-white flex flex-col items-center text-center shadow-2xl shadow-blue-200" data-aos="fade-up">
                    <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center mb-8">
                        <i class="fas fa-chalkboard-teacher text-white text-4xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold mb-4 text-white">Teachers</h3>
                    <p class="text-blue-100 mb-10 leading-relaxed font-light text-lg">
                        Manage your classes, input results securely, and handle subject assignments with precision.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-4 bg-white text-blue-600 rounded-2xl font-bold hover:bg-blue-50 transition transform hover:scale-105">
                        Log In Now
                    </a>
                </div>

                <!-- Admin Portal -->
                <div class="portal-card bg-gray-50/50 p-10 rounded-[3rem] border border-gray-100 flex flex-col items-center text-center" data-aos="fade-left">
                    <div class="w-20 h-20 bg-indigo-100 rounded-3xl flex items-center justify-center mb-8 shadow-inner">
                        <i class="fas fa-user-shield text-indigo-600 text-4xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-4">Admin</h3>
                    <p class="text-gray-500 mb-10 leading-relaxed font-light">
                        Full administrative control over students, staff, system settings, and school-wide reporting.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-4 bg-white border-2 border-indigo-600 text-indigo-600 rounded-2xl font-bold hover:bg-indigo-600 hover:text-white transition shadow-lg shadow-indigo-50/50">
                        Administrator Entry
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-32 bg-[#fcfdff]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-24">
                <div class="lg:w-1/2" data-aos="fade-right">
                    <h2 class="text-5xl font-extrabold text-gray-900 mb-10 leading-tight">
                        A smarter ecosystem for <br/><span class="text-blue-600">Educational Growth</span>
                    </h2>
                    
                    <div class="space-y-10">
                        <div class="flex gap-6 group">
                            <div class="flex-shrink-0 w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center group-hover:bg-green-600 transition duration-300">
                                <i class="fas fa-check text-green-600 group-hover:text-white transition"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($feature1Title); ?></h4>
                                <p class="text-gray-500 font-light text-lg"><?php echo htmlspecialchars($feature1Desc); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-6 group">
                            <div class="flex-shrink-0 w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-600 transition duration-300">
                                <i class="fas fa-bolt text-blue-600 group-hover:text-white transition"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($feature2Title); ?></h4>
                                <p class="text-gray-500 font-light text-lg"><?php echo htmlspecialchars($feature2Desc); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-6 group">
                            <div class="flex-shrink-0 w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center group-hover:bg-purple-600 transition duration-300">
                                <i class="fas fa-shield-halved text-purple-600 group-hover:text-white transition"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($feature3Title); ?></h4>
                                <p class="text-gray-500 font-light text-lg"><?php echo htmlspecialchars($feature3Desc); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 relative" data-aos="fade-left">
                    <div class="bg-gray-200 rounded-[3rem] w-full aspect-video flex items-center justify-center shadow-2xl relative overflow-hidden group border-8 border-white">
                        <i class="fas fa-play text-white text-7xl drop-shadow-2xl z-20 cursor-pointer hover:scale-110 transition"></i>
                        <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Dashboard Preview" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-110 transition duration-700">
                    </div>
                    <!-- Overlay Badge -->
                    <div class="absolute -bottom-8 -right-8 bg-white p-8 rounded-[2rem] shadow-2xl border border-gray-100 flex items-center space-x-5" data-aos="zoom-in" data-aos-delay="500">
                        <div class="w-14 h-14 bg-yellow-400 rounded-full flex items-center justify-center text-white text-2xl">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Top Rated</p>
                            <p class="text-xl font-bold text-gray-900">#1 System 2024</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 text-white pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-20">
                <div class="col-span-1 lg:col-span-1">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold tracking-tight"><?php echo htmlspecialchars($siteName); ?></span>
                    </div>
                    <p class="text-gray-500 leading-relaxed font-light mb-8">
                        The ultimate student result management solution designed for modern educational institutions worldwide.
                    </p>
                    <div class="flex items-center space-x-5">
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8">Quick Links</h4>
                    <ul class="space-y-4 font-light text-gray-500">
                        <li><a href="#" class="hover:text-blue-500 transition">About System</a></li>
                        <li><a href="#features" class="hover:text-blue-500 transition">Core Features</a></li>
                        <li><a href="#portals" class="hover:text-blue-500 transition">Access Portals</a></li>
                        <li><a href="#" class="hover:text-blue-500 transition">Release Notes</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8">For Users</h4>
                    <ul class="space-y-4 font-light text-gray-500">
                        <li><a href="login.php" class="hover:text-blue-500 transition">Student Login</a></li>
                        <li><a href="login.php" class="hover:text-blue-500 transition">Teacher Dashboard</a></li>
                        <li><a href="login.php" class="hover:text-blue-500 transition">Admin Access</a></li>
                        <li><a href="#" class="hover:text-blue-500 transition">User Manual</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8">System Status</h4>
                    <div class="bg-gray-900/50 p-6 rounded-2xl border border-gray-800">
                        <div class="flex items-center text-green-500 mb-2">
                            <span class="flex h-2 w-2 rounded-full bg-green-500 mr-2"></span>
                            <span class="text-sm font-bold uppercase tracking-widest">All Systems Operational</span>
                        </div>
                        <p class="text-xs text-gray-600">Last updated: <?php echo date('M d, H:i'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="pt-12 border-t border-gray-900 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-gray-600 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?> Global. All rights reserved.
                </p>
                <div class="flex space-x-12 text-gray-600 text-sm">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Init AOS
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-in-out'
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-xl', 'bg-white/95', 'h-16');
                nav.classList.remove('h-20');
            } else {
                nav.classList.remove('shadow-xl', 'bg-white/95', 'h-16');
                nav.classList.add('h-20');
            }
        });
    </script>
</body>
</html>
