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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Excellence in Education</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        .portal-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 overflow-x-hidden">

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <?php if ($siteLogo && file_exists('assets/uploads/logos/' . $siteLogo)): ?>
                        <img src="assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo" class="h-8 w-auto">
                    <?php else: ?>
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($siteName); ?></span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 font-medium transition">Features</a>
                    <a href="#portals" class="text-gray-600 hover:text-blue-600 font-medium transition">Portals</a>
                    <a href="login.php" class="bg-blue-600 text-white px-5 py-2 rounded-full font-semibold hover:bg-blue-700 transition shadow-md shadow-blue-200">
                        Login Now
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(45%_45%_at_50%_50%,rgba(59,130,246,0.05)_0%,rgba(255,255,255,0)_100%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-blue-50 text-blue-600 text-sm font-semibold mb-6">Version 2.0 Now Live</span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 tracking-tight lg:leading-[1.1] mb-6">
                Revolutionizing <span class="text-blue-600">Student Success</span> One Grade at a Time.
            </h1>
            <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                Empower your institution with a high-performance management system for tracking results, managing students, and delivering academic excellence.
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#portals" class="w-full sm:w-auto bg-blue-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-blue-700 transition transform hover:scale-105 shadow-xl shadow-blue-200">
                    Get Started <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
                <a href="#features" class="w-full sm:w-auto bg-white text-gray-700 border border-gray-200 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-50 transition">
                    Learn More
                </a>
            </div>
        </div>
    </header>

    <!-- Portals Section -->
    <section id="portals" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Dedicated Portals</h2>
                <div class="w-20 h-1.5 bg-blue-600 mx-auto rounded-full"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Student Portal -->
                <div class="portal-card bg-gray-50 p-8 rounded-3xl border border-gray-100 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-user-graduate text-blue-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Student Portal</h3>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Access your academic performance, view results, and track your progression journey with ease.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-3 bg-white border-2 border-blue-600 text-blue-600 rounded-xl font-bold hover:bg-blue-600 hover:text-white transition">
                        Student Login
                    </a>
                </div>

                <!-- Teacher Portal -->
                <div class="portal-card bg-blue-600 p-8 rounded-3xl text-white flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-white">Teacher Portal</h3>
                    <p class="text-blue-50 mb-8 leading-relaxed">
                        Input grades quickly with bulk entry tools, manage class lists, and request new subjects easily.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-3 bg-white text-blue-600 rounded-xl font-bold hover:bg-blue-50 transition">
                        Teacher Login
                    </a>
                </div>

                <!-- Admin Portal -->
                <div class="portal-card bg-gray-50 p-8 rounded-3xl border border-gray-100 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-user-shield text-indigo-600 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Admin Portal</h3>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Complete oversight of students, teachers, results, and system configuration from a powerful dashboard.
                    </p>
                    <a href="login.php" class="mt-auto w-full py-3 bg-white border-2 border-indigo-600 text-indigo-600 rounded-xl font-bold hover:bg-indigo-600 hover:text-white transition">
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2">
                    <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-8">
                        The smarter way to <span class="text-blue-600">manage results</span>
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">Dynamic Reporting</h4>
                                <p class="text-gray-600">Generate instantly readable result slips and progress reports for every student.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-bolt text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">Bulk Data Entry</h4>
                                <p class="text-gray-600">Save hours with our optimized bulk resulting tools for teachers and staff.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-halved text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">Secure by Design</h4>
                                <p class="text-gray-600">Role-based access control and modern security practices protect your data.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 relative">
                    <div class="bg-blue-600 rounded-3xl w-full aspect-video flex items-center justify-center shadow-2xl relative overflow-hidden group">
                        <div class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-10 transition"></div>
                        <i class="fas fa-play text-white text-6xl drop-shadow-lg"></i>
                        <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Dashboard Preview" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-10">
                    </div>
                    <!-- Stats badge -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Uptime Guarantee</p>
                            <p class="text-xl font-bold">99.9% Reliable</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-600 p-2 rounded-lg">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold"><?php echo htmlspecialchars($siteName); ?></span>
                </div>
                <p class="text-gray-400 text-center">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. Designed for excellence.
                </p>
                <div class="flex items-center space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>