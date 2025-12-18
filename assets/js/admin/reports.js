// assets/js/admin/reports.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if Chart.js and data are available
    if (typeof Chart !== 'undefined' && window.chartData) {
        initializeCharts();
    }
});

function initializeCharts() {
    const data = window.chartData;

    // Yearly Trends Chart
    const yearlyCtx = document.getElementById('yearlyChart');
    if (yearlyCtx) {
        new Chart(yearlyCtx, {
            type: 'line',
            data: {
                labels: data.yearlyLabels,
                datasets: [{
                    label: 'Mean GPA Score',
                    data: data.yearlyGPA,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 12,
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4.0,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { font: { weight: '600' } }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: data.gradeLabels,
                datasets: [{
                    data: data.gradeCounts,
                    backgroundColor: [
                        '#10B981', // A
                        '#34D399', // A-
                        '#6EE7B7', // B+
                        '#60A5FA', // B
                        '#3B82F6', // B-
                        '#2563EB', // C+
                        '#F59E0B', // C
                        '#F97316', // D
                        '#EF4444'  // F
                    ],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { weight: '600', size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
}

function exportToExcel() {
    // Basic CSV export for tables
    const tables = document.querySelectorAll('table.table-modern');
    let csv = [];
    
    tables.forEach((table, index) => {
        const rows = table.querySelectorAll('tr');
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                // Remove buttons or hidden elements if any
                if (cols[j].querySelector('button')) continue;
                row.push(cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim());
            }
            csv.push(row.join(","));
        }
        csv.push("\n"); // Space between tables
    });

    downloadCSV(csv.join("\n"), 'academic_report.csv');
}

function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: "text/csv" });
    const downloadLink = document.createElement("a");
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function exportToPDF() {
    // Uses the browser print function but optimized with @media print
    window.print();
}