<?php
// includes/admin_sidebar.php
?>
<aside id="sidebar" class="bg-white shadow-lg fixed h-full w-64 z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out left-0 top-0 pt-16 lg:pt-0">
    <div class="p-6 border-b border-gray-200 hidden lg:block">
        <h2 class="text-xl font-bold text-gray-800">Admin Panel</h2>
    </div>
    <nav class="mt-6">
        <div class="px-4 space-y-2">
            <a href="dashboard.php" 
               class="flex items-center px-4 py-3 text-gray-700 bg-blue-50 border-l-4 border-blue-500 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-50 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="mx-4 font-medium">Dashboard</span>
            </a>
            
            <a href="students.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                <span class="mx-4 font-medium">Students</span>
            </a>
            
            <a href="results.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="mx-4 font-medium">Results</span>
            </a>
            
            <a href="teachers.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                </svg>
                <span class="mx-4 font-medium">Teachers</span>
            </a>

            <a href="subjects.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="mx-4 font-medium">Subjects</span>
            </a>

            <a href="assignments.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
                </svg>
                <span class="mx-4 font-medium">Assignments</span>
            </a>
            
            <a href="reports.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="mx-4 font-medium">Reports</span>
            </a>
            
            <a href="settings.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0 10c-4.418 0-8-1.79-8-4v-1c0-.667.895-1.333 2.5-1.833M20 14v1c0 2.21-3.582 4-8 4"/>
                </svg>
                <span class="mx-4 font-medium">Settings</span>
            </a>
        </div>
    </nav>
</aside>