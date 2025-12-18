// assets/js/student/dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    initializeStudentDashboard();
    setupPrintFunctionality();
});

function initializeStudentDashboard() {
    // Add interactive elements to student dashboard
    addPerformanceVisualization();
    setupResultFilters();
}

function addPerformanceVisualization() {
    const performanceSection = document.querySelector('.bg-white.shadow.rounded-lg:last-child');
    if (!performanceSection) return;
    
    const visualizationHTML = `
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Performance Trend</h3>
        </div>
        <div class="p-6">
            <div id="performanceChart" class="h-64">
                <p class="text-gray-500 text-center py-8">Performance chart would show your GPA trend across terms</p>
            </div>
        </div>
    `;
    
    performanceSection.insertAdjacentHTML('beforeend', visualizationHTML);
}

function setupResultFilters() {
    const resultsSection = document.querySelector('.bg-white.shadow.rounded-lg');
    if (!resultsSection) return;
    
    const filterHTML = `
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <div class="flex space-x-4">
                <select id="termFilter" class="text-sm border border-gray-300 rounded px-3 py-1">
                    <option value="">All Terms</option>
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
                <button onclick="filterResults()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200">
                    Apply Filter
                </button>
            </div>
        </div>
    `;
    
    const table = resultsSection.querySelector('table');
    if (table) {
        table.insertAdjacentHTML('beforebegin', filterHTML);
    }
}

function filterResults() {
    const termFilter = document.getElementById('termFilter').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const termCell = row.querySelector('td:nth-child(3)'); // Adjust index based on your table structure
        if (termCell) {
            // Match "Term X" or "T X" (based on our PHP change earlier which output Term X)
            const term = termCell.textContent.match(/Term (\d+)/)?.[1];
            if (!termFilter || term === termFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    showNotification(`Filtered results for ${termFilter ? 'Term ' + termFilter : 'all terms'}`, 'success');
}

function setupPrintFunctionality() {
    const printButton = document.createElement('button');
    printButton.textContent = 'Print Dashboard';
    printButton.className = 'bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 ml-4';
    printButton.onclick = function() {
        window.print();
    };
    
    const headerActions = document.querySelector('.flex.justify-between.items-center.py-6 .text-right');
    if (headerActions) {
        headerActions.appendChild(printButton);
    }
}

// GPA Calculator
function calculateGPA(results) {
    if (!results || results.length === 0) return 0;
    
    const totalPoints = results.reduce((sum, result) => {
        return sum + (result.grade_point * result.credits);
    }, 0);
    
    const totalCredits = results.reduce((sum, result) => {
        return sum + result.credits;
    }, 0);
    
    return totalCredits > 0 ? totalPoints / totalCredits : 0;
}

// Export functions for global use
window.filterResults = filterResults;