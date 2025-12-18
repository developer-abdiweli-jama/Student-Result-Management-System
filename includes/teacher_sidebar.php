<?php
// includes/teacher_sidebar.php
?>
<aside id="sidebar" class="bg-slate-900 border-r border-slate-800 fixed h-full w-64 z-30 transform -translate-x-full lg:translate-x-0 transition-all duration-500 ease-in-out left-0 top-0 pt-16 lg:pt-0">
    <div class="p-8 border-b border-slate-800 hidden lg:flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
        </div>
        <div>
            <h2 class="text-sm font-black text-white uppercase tracking-widest">Teacher</h2>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Portal Access</p>
        </div>
    </div>
    <nav class="mt-8 px-4">
        <div class="space-y-4">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Core Navigation</p>
            <a href="dashboard.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-th-large text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Dashboard</span>
            </a>
            
            <a href="enter_result.php" 
               class="flex items-center px-4 py-3 rounded-2xl transition-all duration-300 group <?php echo basename($_SERVER['PHP_SELF']) == 'enter_result.php' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'enter_result.php' ? 'bg-white/20' : 'bg-slate-800 group-hover:bg-slate-700'; ?>">
                    <i class="fas fa-edit text-sm"></i>
                </div>
                <span class="mx-4 font-bold text-sm tracking-wide">Result Entry</span>
            </a>

            <div class="pt-8 px-4">
                <div class="p-4 rounded-2xl bg-slate-800/50 border border-slate-800">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Support</p>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Need help with results? Contact the admin.</p>
                </div>
            </div>
        </div>
    </nav>
</aside>
