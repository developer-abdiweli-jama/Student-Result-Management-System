// assets/js/admin/reports.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Add export functionality
    addExportButtons();
});

function initializeCharts() {
    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        const gradeData = getGradeDistributionData();
        new Chart(gradeCtx, {
            type: 'bar',
            data: gradeData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Grade Distribution'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    }
                }
            }
        });
    }
}

function getGradeDistributionData() {
    // This would typically come from an API endpoint
    // For now, we'll extract from the existing HTML
    const grades = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'D', 'F'];
    const counts = [];
    
    grades.forEach(grade => {
        const element = document.querySelector(`[data-grade="${grade}"]`);
        if (element) {
            counts.push(parseInt(element.textContent) || 0);
        }
    });
    
    return {
        labels: grades,
        datasets: [{
            data: counts,
            backgroundColor: [
                '#10B981', '#34D399', '#6EE7B7',
                '#60A5FA', '#3B82F6', '#2563EB',
                '#F59E0B', '#F97316', '#EF4444'
            ],
            borderColor: [
                '#059669', '#10B981', '#34D399',
                '#3B82F6', '#2563EB', '#1D4ED8',
                '#D97706', '#EA580C', '#DC2626'
            ],
            borderWidth: 1
        }]
    };
}

function addExportButtons() {
    const exportSection = document.createElement('div');
    exportSection.className = 'flex justify-end space-x-4 mb-6';
    exportSection.innerHTML = `
        <button onclick="exportToCSV()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
            Export to CSV
        </button>
        <button onclick="exportToPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
            Export to PDF
        </button>
        <button onclick="printReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
            Print Report
        </button>
    `;
    
    const mainContent = document.querySelector('.ml-64.flex-1');
    if (mainContent) {
        const firstChild = mainContent.firstChild;
        mainContent.insertBefore(exportSection, firstChild);
    }
}

function exportToCSV() {
    showLoading();
    
    // Collect data from tables
    const tables = document.querySelectorAll('table');
    let csvContent = "data:text/csv;charset=utf-8,";
    
    tables.forEach((table, index) => {
        const title = table.previousElementSibling?.querySelector('h3')?.textContent || `Table ${index + 1}`;
        csvContent += title + "\r\n\r\n";
        
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('th, td');
            const rowData = Array.from(cols).map(col => `"${col.textContent.trim()}"`).join(',');
            csvContent += rowData + "\r\n";
        });
        
        csvContent += "\r\n\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "reports_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    hideLoading();
    showNotification('CSV export completed successfully', 'success');
}

function exportToPDF() {
    showLoading();

    // Build a printable document from the main content area
    const content = document.querySelector('.ml-64.flex-1');
    if (!content) {
        hideLoading();
        showNotification('No report content found to export', 'error');
        return;
    }

    // Clone content to avoid modifying the page
    const clone = content.cloneNode(true);

    // Use the dedicated print stylesheet so PDF resembles student transcript
    const printCssUrl = window.location.origin + '/assets/css/print.css';
    const win = window.open('', '_blank');
    win.document.open();
    win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Report - ${new Date().toLocaleDateString()}</title><link rel="stylesheet" href="${printCssUrl}"></head><body>`);
    win.document.write(clone.innerHTML);
    win.document.write('</body></html>');
    win.document.close();

    // Wait a short moment for resources to load then trigger print
    setTimeout(() => {
        try {
            win.print();
            win.onafterprint = function () { win.close(); };
            hideLoading();
            showNotification('Opened printable report — use your browser Print/Save as PDF', 'success');
        } catch (e) {
            hideLoading();
            showNotification('Failed to open print dialog: ' + e.message, 'error');
        }
    }, 700);
}

function printReport() {
    window.print();
}

// Add data attributes to grade distribution for charting
document.addEventListener('DOMContentLoaded', function() {
    const gradeItems = document.querySelectorAll('.space-y-4 > div');
    gradeItems.forEach((item, index) => {
        const gradeSpan = item.querySelector('span:first-child');
        if (gradeSpan) {
            const grade = gradeSpan.textContent.trim();
            item.setAttribute('data-grade', grade);
        }
    });
});