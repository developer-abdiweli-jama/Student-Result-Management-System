<?php
// includes/teacher_sidebar.php
?>
<aside id="sidebar" class="bg-white shadow-lg fixed h-full w-64 z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out left-0 top-0 pt-16 lg:pt-0">
    <div class="p-6 border-b border-gray-200 hidden lg:block">
        <h2 class="text-xl font-bold text-gray-800">Teacher Portal</h2>
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
            
            <a href="enter_result.php" 
               class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition duration-200 <?php echo basename($_SERVER['PHP_SELF']) == 'enter_result.php' ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="mx-4 font-medium">Enter Results</span>
            </a>
            
            <!-- Add more links if needed -->
        </div>
    </nav>
</aside>
