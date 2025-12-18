/**
 * Admin Dashboard - Analytics Initialization
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }

    const data = window.chartData || {};

    // 1. Grade Distribution Chart (Doughnut)
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: data.gradeLabels || [],
                datasets: [{
                    data: data.gradeData || [],
                    backgroundColor: [
                        '#3B82F6', // blue
                        '#6366F1', // indigo
                        '#8B5CF6', // violet
                        '#10B981', // green
                        '#F59E0B', // amber
                        '#F43F5E', // rose
                    ],
                    borderWidth: 0,
                    hoverOffset: 20,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 30,
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 11,
                                weight: '700'
                            },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#0f172a',
                        titleFont: { family: "'Poppins', sans-serif", size: 13, weight: '700' },
                        bodyFont: { family: "'Poppins', sans-serif", size: 12 },
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: false
                    }
                }
            }
        });
    }

    // 2. Student Enrollment Chart (Bar)
    const enrollmentCtx = document.getElementById('enrollmentChart');
    if (enrollmentCtx) {
        const gradient = enrollmentCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 1)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 1)');

        new Chart(enrollmentCtx, {
            type: 'bar',
            data: {
                labels: data.enrollmentLabels || [],
                datasets: [{
                    label: 'Students Registered',
                    data: data.enrollmentData || [],
                    backgroundColor: gradient,
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: "'Poppins', sans-serif", size: 11, weight: '600' },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: "'Poppins', sans-serif", size: 11, weight: '600' },
                            color: '#94a3b8'
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 16,
                        cornerRadius: 12
                    }
                }
            }
        });
    }
});
