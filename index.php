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
    <header class="relative pt-32 pb-24 md:pt-48 md:pb-40 overflow-hidden aurora-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center" data-aos="zoom-out" data-aos-duration="1200">
                <div class="inline-flex items-center gap-2 mb-10 px-6 py-2.5 bg-white/60 backdrop-blur-md rounded-full shadow-sm border border-white/40">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-blue-900 tracking-widest uppercase">Experience the Future of Education</span>
                </div>
                
                <h1 class="text-6xl md:text-8xl lg:text-9xl font-black text-slate-900 mb-8 leading-[0.9] tracking-tighter">
                    Empowering <br/>
                    <span class="gradient-text">Excellence.</span>
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

    <!-- Statistics Section -->
    <section id="stats" class="py-24 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="glass-effect p-10 rounded-[2.5rem] text-center group hover:bg-white transition-all duration-500" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-graduate text-blue-600 text-2xl"></i>
                    </div>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-black text-slate-900 stat-counter" data-target="<?php echo $totalStudents; ?>">0</span>
                        <span class="text-xl font-bold text-blue-600">+</span>
                    </div>
                    <p class="text-slate-500 font-semibold mt-4">Active Learners</p>
                </div>
                
                <div class="glass-effect p-10 rounded-[2.5rem] text-center group hover:bg-white transition-all duration-500" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-chalkboard-teacher text-indigo-600 text-2xl"></i>
                    </div>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-black text-slate-900 stat-counter" data-target="<?php echo $totalTeachers; ?>">0</span>
                    </div>
                    <p class="text-slate-500 font-semibold mt-4">Expert Faculty</p>
                </div>
                
                <div class="glass-effect p-10 rounded-[2.5rem] text-center group hover:bg-white transition-all duration-500" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-invoice text-purple-600 text-2xl"></i>
                    </div>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-black text-slate-900 stat-counter" data-target="<?php echo $totalResults; ?>">0</span>
                        <span class="text-xl font-bold text-purple-600">k</span>
                    </div>
                    <p class="text-slate-500 font-semibold mt-4">Results Published</p>
                </div>
                
                <div class="glass-effect p-10 rounded-[2.5rem] text-center group hover:bg-white transition-all duration-500" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-check text-emerald-600 text-2xl"></i>
                    </div>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-5xl font-black text-slate-900">99.9</span>
                        <span class="text-xl font-bold text-emerald-600">%</span>
                    </div>
                    <p class="text-slate-500 font-semibold mt-4">System Reliability</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By Marquee -->
    <section class="py-12 border-y border-slate-100 bg-white/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-center text-xs font-bold text-slate-400 uppercase tracking-[0.3em] mb-8">Trusted by Leading Institutions</p>
            <div class="marquee-container">
                <div class="marquee-content">
                    <!-- Placeholder logos using Font Awesome icons as stylized logos -->
                    <div class="inline-flex items-center gap-12 mx-8">
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-university text-2xl"></i>
                            <span class="font-bold tracking-tighter">OXFORD ACADEMY</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-school text-2xl"></i>
                            <span class="font-bold tracking-tighter">ELITE PREP</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-microchip text-2xl"></i>
                            <span class="font-bold tracking-tighter">TECH INSTITUTE</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                            <span class="font-bold tracking-tighter">GLOBAL SCHOOLS</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-book-reader text-2xl"></i>
                            <span class="font-bold tracking-tighter">OPEN UNIVERSITY</span>
                        </div>
                    </div>
                    <!-- Repeat for seamless scroll -->
                    <div class="inline-flex items-center gap-12 mx-8">
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-university text-2xl"></i>
                            <span class="font-bold tracking-tighter">OXFORD ACADEMY</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-school text-2xl"></i>
                            <span class="font-bold tracking-tighter">ELITE PREP</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-microchip text-2xl"></i>
                            <span class="font-bold tracking-tighter">TECH INSTITUTE</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                            <span class="font-bold tracking-tighter">GLOBAL SCHOOLS</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 grayscale filter">
                            <i class="fas fa-book-reader text-2xl"></i>
                            <span class="font-bold tracking-tighter">OPEN UNIVERSITY</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portals Section -->
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

    <!-- Bento Features Section -->
    <section id="features" class="py-32 bg-slate-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20" data-aos="fade-up">
                <h2 class="text-5xl font-black text-slate-900 mb-6">Built for the <span class="gradient-text">Next Generation</span></h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto italic">"Design is not just what it looks like and feels like. Design is how it works."</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Large Bento Item -->
                <div class="md:col-span-8 bg-white p-12 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group" data-aos="fade-right">
                    <div class="flex flex-col h-full justify-between">
                        <div>
                            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-blue-600 transition-colors">
                                <i class="fas fa-chart-line text-blue-600 group-hover:text-white transition-colors"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-slate-900 mb-4"><?php echo htmlspecialchars($feature1Title); ?></h3>
                            <p class="text-slate-500 text-lg leading-relaxed max-w-md"><?php echo htmlspecialchars($feature1Desc); ?></p>
                        </div>
                        <div class="mt-12 bg-slate-50 rounded-2xl p-6 border border-slate-100">
                             <div class="flex items-center justify-between mb-4">
                                 <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Live Preview</span>
                                 <span class="text-xs font-bold text-green-500">Processing...</span>
                             </div>
                             <div class="space-y-3">
                                 <div class="h-2 w-full bg-slate-200 rounded-full overflow-hidden">
                                     <div class="h-full bg-blue-500 w-3/4 animate-pulse"></div>
                                 </div>
                                 <div class="h-2 w-1/2 bg-slate-200 rounded-full"></div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Small Bento Item -->
                <div class="md:col-span-4 bg-gradient-to-br from-indigo-600 to-blue-700 p-12 rounded-[3rem] text-white shadow-xl group" data-aos="fade-left">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-8">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($feature2Title); ?></h3>
                    <p class="text-indigo-100 leading-relaxed"><?php echo htmlspecialchars($feature2Desc); ?></p>
                    <div class="mt-12">
                        <i class="fas fa-microchip text-6xl opacity-20"></i>
                    </div>
                </div>

                <!-- Three Bottom Items -->
                <div class="md:col-span-4 bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-halved text-purple-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3"><?php echo htmlspecialchars($feature3Title); ?></h4>
                    <p class="text-slate-500"><?php echo htmlspecialchars($feature3Desc); ?></p>
                </div>

                <div class="md:col-span-4 bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-amber-50 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-screen text-amber-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Mobile Results</h4>
                    <p class="text-slate-500">Access grades and reports on any device with our fully responsive student portal.</p>
                </div>

                <div class="md:col-span-4 bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 bg-rose-50 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-pdf text-rose-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Instant Export</h4>
                    <p class="text-slate-500">One-click PDF generation for result slips, transcripts, and school-wide performance charts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-32 bg-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_10%_20%,rgba(59,130,246,0.03)_0%,transparent_50%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-20" data-aos="fade-up">
                <span class="text-blue-600 font-bold tracking-widest uppercase text-xs">Testimonials</span>
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 mt-4 mb-6">Voices of Innovation</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white transition-colors group" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex gap-1 text-amber-400 mb-6">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-slate-600 italic leading-relaxed mb-8">"The transition to SRMIS was the best technical decision our school made this decade. The automation is flawless."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">JD</div>
                        <div>
                            <p class="font-bold text-slate-900">Dr. James Dalton</p>
                            <p class="text-xs text-slate-400">Principal, St. Andrews</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 rounded-[2.5rem] bg-blue-600 border border-blue-500 text-white shadow-2xl group" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex gap-1 text-blue-200 mb-6">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="italic leading-relaxed mb-8">"Teachers actually enjoy inputting results now. The bulk tools and intuitive UI saved our staff hundreds of hours annually."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white font-bold">SM</div>
                        <div>
                            <p class="font-bold">Sarah Miller</p>
                            <p class="text-xs text-blue-200">Head of Department</p>
                        </div>
                    </div>
                </div>

                <div class="p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white transition-colors group" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex gap-1 text-amber-400 mb-6">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-slate-600 italic leading-relaxed mb-8">"As a student, having my results available instantly on my phone has changed how I track my own academic progress."</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold">AK</div>
                        <div>
                            <p class="font-bold text-slate-900">Ahmed Khan</p>
                            <p class="text-xs text-slate-400">Student Representative</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-32 bg-slate-50/30">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-black text-slate-900 mb-4">Frequently Asked Questions</h2>
                <p class="text-slate-500">Everything you need to know about the SRMIS ecosystem.</p>
            </div>

            <div class="space-y-4" data-aos="fade-up">
                <div class="faq-item active bg-white rounded-3xl p-6 border border-slate-100 px-8">
                    <button class="faq-trigger">
                        <span>How secure is the student data?</span>
                        <i class="fas fa-chevron-down faq-icon text-blue-600"></i>
                    </button>
                    <div class="faq-content">
                        We use enterprise-grade encryption and role-based access control to ensure that only authorized personnel can view or modify sensitive academic records.
                    </div>
                </div>

                <div class="faq-item bg-white rounded-3xl p-6 border border-slate-100 px-8">
                    <button class="faq-trigger">
                        <span>Can we migrate data from our old system?</span>
                        <i class="fas fa-chevron-down faq-icon text-blue-600"></i>
                    </button>
                    <div class="faq-content">
                        Yes, our system supports CSV and Excel imports, making it easy to bring over your existing student and result databases seamlessly.
                    </div>
                </div>

                <div class="faq-item bg-white rounded-3xl p-6 border border-slate-100 px-8">
                    <button class="faq-trigger">
                        <span>Is there a mobile app?</span>
                        <i class="fas fa-chevron-down faq-icon text-blue-600"></i>
                    </button>
                    <div class="faq-content">
                        The SRMIS is a progressive web application, meaning it works perfectly on all mobile browsers and can be added to your home screen for an app-like experience.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-900 rounded-[4rem] p-12 md:p-24 relative overflow-hidden shadow-2xl" data-aos="zoom-in">
                <!-- Decorative Circle -->
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 text-center">
                    <h2 class="text-4xl md:text-6xl font-black text-white mb-8 leading-tight">Ready to Elevate Your <br/>School's Potential?</h2>
                    <p class="text-slate-400 text-xl mb-12 max-w-2xl mx-auto font-light">Join 100+ institutions that have revolutionized their result management systems with SRMIS.</p>
                    <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                        <a href="login.php" class="bg-white text-slate-900 px-12 py-5 rounded-2xl font-black text-lg hover:bg-slate-100 transition shadow-2xl">
                            Get Started Now
                        </a>
                        <a href="#" class="text-white font-bold border-b-2 border-white/20 hover:border-white transition pb-1">
                            Contact Sales Team
                        </a>
                    </div>
                </div>
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
                        The ultimate student result management solution. <br/>Designed & Developed with excellence by <a href="#" class="text-white font-bold hover:text-blue-400 transition">Hirgal Nexus</a>.
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
                <p class="text-gray-500 text-sm italic">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. Crafted by <span class="text-gray-300 font-semibold">Hirgal Nexus</span>.
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