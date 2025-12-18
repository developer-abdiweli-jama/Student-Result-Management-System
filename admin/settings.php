<?php
// admin/settings.php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../includes/settings.php';
$page_scripts = ['admin/settings.js'];
require_once __DIR__ . '/../includes/header.php';

$siteName = getSetting('site_name', SITE_NAME);
$siteLogo = getSetting('site_logo', null);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
    <!-- Glass Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Configuration</h1>
                <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                    <span>Admin</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600 font-bold">Preferences & Settings</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl border border-blue-100 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Global Admin Access</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-8">
        <?php if ($flashSuccess): ?>
            <div class="mb-8 p-4 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center gap-3 animate-fade-in-up">
                <i class="fas fa-check-circle"></i>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($flashSuccess); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="mb-8 p-4 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-3 animate-fade-in-up">
                <i class="fas fa-exclamation-circle"></i>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($flashError); ?></span>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="flex bg-white p-1 rounded-2xl shadow-sm border border-slate-100 mb-8 w-fit">
            <button onclick="switchTab('general')" id="tab-btn-general" class="tab-btn active px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                General
            </button>
            <button onclick="switchTab('branding')" id="tab-btn-branding" class="tab-btn px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600">
                Branding
            </button>
            <button onclick="switchTab('system')" id="tab-btn-system" class="tab-btn px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600">
                System
            </button>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <!-- General Tab -->
            <div id="tab-general" class="tab-content">
                <div class="dashboard-card pb-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Main Configuration</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Core institution information</p>
                        </div>
                    </div>

                    <form method="post" action="settings_save.php" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Institution Name</label>
                                <div class="relative">
                                    <i class="fas fa-university absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>" 
                                           class="w-full pl-11 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Current Academic Year</label>
                                <div class="relative">
                                    <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="current_academic_year" value="<?php echo htmlspecialchars(getSetting('current_academic_year', '2024/2025')); ?>" 
                                           class="w-full pl-11 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-bold focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-8 border-t border-slate-50">
                            <button type="submit" class="premium-btn px-10 py-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-1 transition-all">
                                Update Information
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Branding Tab -->
            <div id="tab-branding" class="tab-content hidden">
                <div class="dashboard-card">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Branding & Identity</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Custom visual presentation</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-10 items-start">
                        <div class="md:col-span-12 lg:col-span-5 relative group">
                            <div class="aspect-square bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center p-8 transition-all group-hover:bg-slate-100/50">
                                <?php if ($siteLogo): ?>
                                    <img id="logo_preview" src="../assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" 
                                         class="max-h-56 w-auto object-contain drop-shadow-2xl animate-fade-in" />
                                    <form method="post" action="../includes/delete_logo.php" class="absolute top-4 right-4 group-hover:scale-110 transition-transform">
                                        <button type="submit" class="w-10 h-10 rounded-full bg-rose-500 text-white shadow-xl hover:bg-rose-600 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <img id="logo_preview" src="" class="hidden max-h-56 w-auto object-contain drop-shadow-2xl animate-fade-in" />
                                    <div id="logo_placeholder" class="text-center">
                                        <div class="w-20 h-20 rounded-3xl bg-slate-200 flex items-center justify-center text-slate-400 mx-auto mb-4 border-4 border-white shadow-inner">
                                            <i class="fas fa-image text-3xl"></i>
                                        </div>
                                        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No Logo Set</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="md:col-span-12 lg:col-span-7 space-y-6">
                            <form method="post" action="../includes/upload_logo.php" enctype="multipart/form-data" class="space-y-6">
                                <div class="relative">
                                    <input type="file" name="site_logo" id="logo_input" accept="image/png,image/jpeg,image/svg+xml" class="hidden" />
                                    <label for="logo_input" class="block w-full cursor-pointer">
                                        <div class="p-8 bg-blue-50/30 border-2 border-dashed border-blue-200 rounded-3xl text-center group hover:bg-blue-50 transition-all">
                                            <div class="w-12 h-12 bg-blue-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/30">
                                                <i class="fas fa-cloud-upload-alt text-lg"></i>
                                            </div>
                                            <h4 class="text-sm font-black text-blue-900 mb-1">Upload New Logo</h4>
                                            <p id="fileNameDisplay" class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">PNG, JPG or SVG up to 2MB</p>
                                        </div>
                                    </label>
                                </div>
                                <button type="submit" class="w-full bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest py-5 rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/10 active:scale-95">
                                    Finalize Branding Update
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Tab -->
            <div id="tab-system" class="tab-content hidden">
                <div class="dashboard-card">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">System Infrastructure</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Advanced maintenance controls</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-4 group hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl group-hover:bg-rose-500 group-hover:text-white transition-all">
                                <i class="fas fa-power-off"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase">Maintenance Mode</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Status: <span class="text-emerald-500">Online</span></p>
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-4 group hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:bg-emerald-500 group-hover:text-white transition-all">
                                <i class="fas fa-database"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase">System Backup</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Last Backup: 2h ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn.active {
    background: #0f172a;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
