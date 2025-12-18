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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS for Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Premium Home Styling & Scripts -->
    <link rel="stylesheet" href="assets/css/home.css">
    <script src="assets/js/home.js"></script>
    <!-- Tailwind CDN (Last to ensure config picks up) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-grid-pattern overflow-x-hidden">

    <!-- Scroll Progress Bar -->
    <div class="fixed top-0 left-0 w-full h-1 z-50 bg-gray-200">
        <div id="scroll-progress" class="h-full gradient-bg transition-all duration-300" style="width: 0%"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-1 left-1/2 -translate-x-1/2 z-40 w-[95%] max-w-6xl mt-2 rounded-2xl glass-effect shadow-xl">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <?php if ($siteLogo && file_exists('assets/uploads/logos/' . $siteLogo)): ?>
                        <div class="p-2 bg-white rounded-xl shadow-lg">
                            <img src="assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo" class="h-8 w-auto">
                        </div>
                    <?php else: ?>
                        <div class="gradient-bg p-2.5 rounded-xl shadow-lg">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-bold text-gray-900">
                        <span class="gradient-text"><?php echo htmlspecialchars($siteName); ?></span>
                    </span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-primary-600 font-medium transition flex items-center gap-2 group">
                        <i class="fas fa-star text-sm text-primary-500"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Features</span>
                    </a>
                    <a href="#stats" class="text-gray-600 hover:text-primary-600 font-medium transition flex items-center gap-2 group">
                        <i class="fas fa-chart-line text-sm text-primary-500"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Analytics</span>
                    </a>
                    <a href="#portals" class="text-gray-600 hover:text-primary-600 font-medium transition flex items-center gap-2 group">
                        <i class="fas fa-door-open text-sm text-primary-500"></i>
                        <span class="group-hover:translate-x-1 transition-transform">Portals</span>
                    </a>
                    <a href="login.php" class="gradient-bg text-white px-6 py-2.5 rounded-xl font-semibold hover:shadow-2xl hover:scale-105 transition-all shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Log In
                    </a>
                </div>
                <button id="mobile-menu-button" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden absolute top-full left-0 right-0 mt-2 rounded-2xl glass-effect shadow-2xl p-6">
            <div class="flex flex-col space-y-4">
                <a href="#features" class="text-gray-700 hover:text-primary-600 font-medium py-3 px-4 rounded-lg hover:bg-white/50 transition">
                    <i class="fas fa-star mr-3 text-primary-500"></i>Features
                </a>
                <a href="#stats" class="text-gray-700 hover:text-primary-600 font-medium py-3 px-4 rounded-lg hover:bg-white/50 transition">
                    <i class="fas fa-chart-line mr-3 text-primary-500"></i>Analytics
                </a>
                <a href="#portals" class="text-gray-700 hover:text-primary-600 font-medium py-3 px-4 rounded-lg hover:bg-white/50 transition">
                    <i class="fas fa-door-open mr-3 text-primary-500"></i>Portals
                </a>
                <a href="login.php" class="gradient-bg text-white font-semibold py-3 px-4 rounded-lg text-center mt-4 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Log In
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-32 pb-20 md:pt-40 md:pb-32 overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-glow"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-primary-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-glow animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-primary-400 to-primary-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="inline-flex items-center gap-2 mb-8 px-4 py-2 bg-white/80 rounded-full shadow-sm border border-gray-100">
                    <span class="flex h-2 w-2 rounded-full bg-green-500 animate-ping"></span>
                    <span class="text-sm font-semibold text-gray-700">System Status: <span class="text-green-600">Live & Operational</span></span>
                </div>
                
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-gray-900 mb-6 leading-tight">
                    <span class="block">Revolutionizing</span>
                    <span class="gradient-text">Education</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto mb-10 leading-relaxed font-light">
                    <?php echo htmlspecialchars($heroSubtitle); ?>
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                    <a href="#portals" class="group relative overflow-hidden gradient-bg text-white px-10 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300">
                        <span class="relative z-10 flex items-center justify-center">
                            Get Started
                            <i class="fas fa-arrow-right ml-3 group-hover:translate-x-2 transition-transform"></i>
                        </span>
                        <div class="absolute inset-0 bg-white/20 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    </a>
                    
                    <a href="#features" class="group bg-white text-gray-700 border-2 border-gray-200 px-10 py-4 rounded-xl font-bold text-lg hover:border-primary-300 hover:shadow-lg transition-all duration-300">
                        <span class="flex items-center justify-center">
                            <i class="fas fa-play-circle mr-3 text-primary-500"></i>
                            Watch Demo
                        </span>
                    </a>
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-16 flex flex-wrap justify-center items-center gap-8 opacity-70">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-check text-2xl text-green-500"></i>
                        <span class="text-gray-600 font-medium">Enterprise Security</span>
                    </div>
                    <div class="hidden sm:block w-px h-6 bg-gray-300"></div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-bolt text-2xl text-yellow-500"></i>
                        <span class="text-gray-600 font-medium">99.9% Uptime</span>
                    </div>
                    <div class="hidden sm:block w-px h-6 bg-gray-300"></div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-users text-2xl text-blue-500"></i>
                        <span class="text-gray-600 font-medium">500+ Institutions</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-1/4 left-10 floating-element hidden lg:block">
            <div class="w-12 h-12 rounded-2xl bg-primary-100 border-2 border-primary-200 flex items-center justify-center">
                <i class="fas fa-chart-bar text-primary-600"></i>
            </div>
        </div>
        <div class="absolute bottom-1/4 right-10 floating-element animation-delay-1000 hidden lg:block">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 border-2 border-purple-200 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600"></i>
            </div>
        </div>
    </header>

    <!-- Statistics Section with Modern Cards -->
    <section id="stats" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="glass-effect p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($totalStudents); ?></p>
                    <p class="text-gray-500 font-medium">Active Students</p>
                </div>
                
                <div class="glass-effect p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-100 rounded-xl">
                            <i class="fas fa-chalkboard-teacher text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">+8%</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($totalTeachers); ?></p>
                    <p class="text-gray-500 font-medium">Expert Teachers</p>
                </div>
                
                <div class="glass-effect p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-100 rounded-xl">
                            <i class="fas fa-file-alt text-green-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded-full">+24%</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mb-2"><?php echo number_format($totalResults); ?></p>
                    <p class="text-gray-500 font-medium">Published Results</p>
                </div>
                
                <div class="glass-effect p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="400">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-yellow-100 rounded-xl">
                            <i class="fas fa-server text-yellow-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">99.9%</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 mb-2">24/7</p>
                    <p class="text-gray-500 font-medium">System Uptime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portals Section with Modern Cards -->
    <section id="portals" class="py-24 relative">
        <div class="absolute inset-0 bg-gradient-to-b from-gray-50/50 to-transparent -z-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <div class="inline-block mb-4">
                    <span class="text-sm font-semibold text-primary-600 bg-primary-50 px-4 py-2 rounded-full">
                        <i class="fas fa-door-open mr-2"></i>ACCESS POINTS
                    </span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Choose Your Portal</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">Tailored experiences for every user role in your educational ecosystem</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Student Portal -->
                <div class="portal-hover group bg-white p-8 rounded-3xl shadow-xl gradient-border" data-aos="fade-right">
                    <div class="flex items-start justify-between mb-8">
                        <div class="p-4 bg-blue-50 rounded-2xl group-hover:bg-blue-100 transition-colors">
                            <i class="fas fa-user-graduate text-blue-600 text-2xl"></i>
                        </div>
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">POPULAR</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Student Portal</h3>
                    <p class="text-gray-500 mb-8 leading-relaxed">
                        Track grades, download result slips, manage academic progression, and access learning resources.
                    </p>
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Real-time grade tracking</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Interactive dashboards</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Mobile-friendly access</span>
                        </div>
                    </div>
                    <a href="login.php" class="block w-full text-center bg-blue-50 text-blue-700 font-semibold py-4 rounded-xl hover:bg-blue-100 transition-colors group-hover:shadow-lg">
                        Enter Portal <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </a>
                </div>

                <!-- Teacher Portal (Featured) -->
                <div class="portal-hover group relative md:scale-105 md:-translate-y-4" data-aos="fade-up">
                    <div class="absolute -inset-1 gradient-bg rounded-3xl blur opacity-30 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative bg-gradient-to-b from-primary-600 to-primary-800 p-8 rounded-3xl text-white shadow-2xl">
                        <div class="flex items-center justify-between mb-8">
                            <div class="p-4 bg-white/20 rounded-2xl">
                                <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                            </div>
                            <span class="text-xs font-bold bg-white/20 px-3 py-1 rounded-full">RECOMMENDED</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">Teacher Portal</h3>
                        <p class="text-primary-100 mb-8 leading-relaxed">
                            Manage classes, input results securely, handle assignments, and track student progress.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center text-primary-100">
                                <i class="fas fa-check-circle mr-3"></i>
                                <span>Bulk result entry</span>
                            </div>
                            <div class="flex items-center text-primary-100">
                                <i class="fas fa-check-circle mr-3"></i>
                                <span>Analytics dashboard</span>
                            </div>
                            <div class="flex items-center text-primary-100">
                                <i class="fas fa-check-circle mr-3"></i>
                                <span>Communication tools</span>
                            </div>
                        </div>
                        <a href="login.php" class="block w-full text-center bg-white text-primary-600 font-semibold py-4 rounded-xl hover:bg-gray-100 transition-colors shadow-lg">
                            Launch Dashboard <i class="fas fa-rocket ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Admin Portal -->
                <div class="portal-hover group bg-white p-8 rounded-3xl shadow-xl gradient-border" data-aos="fade-left">
                    <div class="flex items-start justify-between mb-8">
                        <div class="p-4 bg-indigo-50 rounded-2xl group-hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-user-shield text-indigo-600 text-2xl"></i>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">POWERFUL</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Admin Portal</h3>
                    <p class="text-gray-500 mb-8 leading-relaxed">
                        Full administrative control with advanced reporting, user management, and system configuration.
                    </p>
                    <div class="space-y-4 mb-8">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Advanced analytics</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Role management</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>System monitoring</span>
                        </div>
                    </div>
                    <a href="login.php" class="block w-full text-center bg-indigo-50 text-indigo-700 font-semibold py-4 rounded-xl hover:bg-indigo-100 transition-colors group-hover:shadow-lg">
                        Access Control Panel <i class="fas fa-cog ml-2 animate-spin-slow"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <div class="inline-block mb-6">
                        <span class="text-sm font-semibold text-primary-600 bg-primary-50 px-4 py-2 rounded-full">
                            <i class="fas fa-bolt mr-2"></i>KEY FEATURES
                        </span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-8">
                        Everything you need for
                        <span class="gradient-text">educational excellence</span>
                    </h2>
                    <p class="text-gray-600 text-lg mb-12">
                        Our platform combines cutting-edge technology with pedagogical insights to deliver an unparalleled educational management experience.
                    </p>
                    
                    <div class="space-y-8">
                        <div class="flex gap-6 group" data-aos="fade-up" data-aos-delay="100">
                            <div class="flex-shrink-0 w-14 h-14 gradient-bg rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                <i class="fas fa-chart-bar text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($feature1Title); ?></h4>
                                <p class="text-gray-500"><?php echo htmlspecialchars($feature1Desc); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-6 group" data-aos="fade-up" data-aos-delay="200">
                            <div class="flex-shrink-0 w-14 h-14 gradient-bg rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                <i class="fas fa-bolt text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($feature2Title); ?></h4>
                                <p class="text-gray-500"><?php echo htmlspecialchars($feature2Desc); ?></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-6 group" data-aos="fade-up" data-aos-delay="300">
                            <div class="flex-shrink-0 w-14 h-14 gradient-bg rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                <i class="fas fa-shield-halved text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($feature3Title); ?></h4>
                                <p class="text-gray-500"><?php echo htmlspecialchars($feature3Desc); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div data-aos="fade-left">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-1 shadow-2xl">
                            <div class="bg-gray-900 rounded-3xl p-8">
                                <!-- Mock Dashboard -->
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                        </div>
                                        <span class="text-gray-400 text-sm">Dashboard Preview</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="bg-gray-800 rounded-xl p-4">
                                            <div class="text-gray-400 text-sm mb-2">Students</div>
                                            <div class="text-2xl font-bold text-white"><?php echo number_format($totalStudents); ?></div>
                                        </div>
                                        <div class="bg-gray-800 rounded-xl p-4">
                                            <div class="text-gray-400 text-sm mb-2">Teachers</div>
                                            <div class="text-2xl font-bold text-white"><?php echo number_format($totalTeachers); ?></div>
                                        </div>
                                        <div class="bg-gray-800 rounded-xl p-4">
                                            <div class="text-gray-400 text-sm mb-2">Results</div>
                                            <div class="text-2xl font-bold text-white"><?php echo number_format($totalResults); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gray-800 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="text-gray-400 text-sm">Performance Trend</div>
                                            <div class="text-green-400 text-sm">↑ 12%</div>
                                        </div>
                                        <div class="h-32 flex items-end space-x-2">
                                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-t-lg" style="height: 70%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-t-lg" style="height: 85%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-t-lg" style="height: 60%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-t-lg" style="height: 90%"></div>
                                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-t-lg" style="height: 75%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Elements Around Dashboard -->
                        <div class="absolute -top-6 -right-6 w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                            <i class="fas fa-trophy text-yellow-500 text-xl"></i>
                        </div>
                        <div class="absolute -bottom-6 -left-6 w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-green-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 gradient-bg opacity-5 -z-10"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="zoom-in">
            <div class="bg-white rounded-3xl p-12 shadow-2xl">
                <div class="w-20 h-20 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-rocket text-white text-2xl"></i>
                </div>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">Ready to Transform Your Institution?</h2>
                <p class="text-gray-600 text-lg mb-10 max-w-2xl mx-auto">
                    Join thousands of educational institutions already experiencing the power of modern student management.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="login.php" class="gradient-bg text-white px-10 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition-all hover:scale-105">
                        Start Free Trial <i class="fas fa-arrow-right ml-3"></i>
                    </a>
                    <a href="#" class="bg-gray-50 text-gray-700 px-10 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 transition-all border-2 border-gray-100">
                        <i class="fas fa-calendar mr-3"></i>Schedule Demo
                    </a>
                </div>
                <p class="text-gray-500 text-sm mt-8">
                    <i class="fas fa-lock mr-2"></i>Secure onboarding · No credit card required · 30-day free trial
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 mb-16">
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="gradient-bg p-2 rounded-lg">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold"><?php echo htmlspecialchars($siteName); ?></span>
                    </div>
                    <p class="text-gray-400 mb-8 leading-relaxed max-w-md">
                        The ultimate student result management solution designed for modern educational institutions worldwide.
                    </p>
                    <div class="flex items-center space-x-5">
                        <a href="#" class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:bg-primary-600 transition-all">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:bg-primary-600 transition-all">
                            <i class="fab fa-linkedin-in text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:bg-primary-600 transition-all">
                            <i class="fab fa-github text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:bg-primary-600 transition-all">
                            <i class="fab fa-youtube text-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8 text-white">Platform</h4>
                    <ul class="space-y-4 font-light text-gray-400">
                        <li><a href="#features" class="hover:text-white transition hover:translate-x-2 inline-block">Features</a></li>
                        <li><a href="#portals" class="hover:text-white transition hover:translate-x-2 inline-block">Portals</a></li>
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">API</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8 text-white">Resources</h4>
                    <ul class="space-y-4 font-light text-gray-400">
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">Documentation</a></li>
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">Support</a></li>
                        <li><a href="#" class="hover:text-white transition hover:translate-x-2 inline-block">Community</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold mb-8 text-white">System Status</h4>
                    <div class="bg-gray-800/50 p-6 rounded-2xl border border-gray-700">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="w-16 h-16">
                                    <svg class="w-full h-full" viewBox="0 0 36 36">
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#1e3a8a" stroke-width="3" stroke-dasharray="100, 100"/>
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="99.9, 100"/>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold">99.9%</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center text-green-500 mb-1">
                                    <span class="flex h-2 w-2 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                                    <span class="text-sm font-bold">All Systems Go</span>
                                </div>
                                <p class="text-xs text-gray-500">Last updated: <?php echo date('M d, H:i'); ?></p>
                            </div>
                        </div>
                        <a href="#" class="text-sm text-primary-400 hover:text-primary-300 transition">
                            <i class="fas fa-external-link-alt mr-2"></i>View Status Page
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="pt-12 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-gray-500 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. All rights reserved.
                </p>
                <div class="flex space-x-8 text-gray-500 text-sm">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                    <a href="#" class="hover:text-white transition">Cookie Policy</a>
                    <a href="#" class="hover:text-white transition">GDPR</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-8 right-8 w-12 h-12 gradient-bg text-white rounded-xl shadow-2xl flex items-center justify-center hover:scale-110 transition-transform opacity-0">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>