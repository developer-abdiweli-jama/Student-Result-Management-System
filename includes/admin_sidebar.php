<?php
// includes/admin_sidebar.php
?>
<aside id="sidebar" class="bg-slate-900 border-r border-slate-800 fixed h-full w-64 z-30 transform -translate-x-full lg:translate-x-0 transition-all duration-500 ease-in-out left-0 top-0 pt-16 lg:pt-0 flex flex-col">
    <div class="p-8 border-b border-slate-800 hidden lg:flex items-center gap-3 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20 text-white">
            <i class="fas fa-user-shield text-lg"></i>
        </div>
        <div>
            <h2 class="text-sm font-black text-white uppercase tracking-widest">Admin</h2>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">System Authority</p>
        </div>
    </div>
    <nav class="mt-8 overflow-y-auto flex-1 custom-scrollbar">
        <div class="px-4 space-y-4 pb-8">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Core Management</p>
            
            <a href="dashboard.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-th-large text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Dashboard</span>
            </a>
            
            <a href="students.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-user-graduate text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Students</span>
            </a>
            
            <a href="results.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-file-invoice text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Results</span>
            </a>
            
            <a href="teachers.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-chalkboard-teacher text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Teachers</span>
            </a>

            <a href="subjects.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-book-open text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Subjects</span>
            </a>

            <a href="assignments.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-tasks text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Assignments</span>
            </a>
            
            <a href="reports.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-chart-line text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Reports</span>
            </a>
            
            <a href="settings.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-cog text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Settings</span>
            </a>
        </div>
    </nav>
</aside>