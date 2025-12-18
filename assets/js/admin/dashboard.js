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
                        '#10B981', // green
                        '#F59E0B', // amber
                        '#EF4444', // red
                        '#8B5CF6', // violet
                        '#6366F1', // indigo
                        '#EC4899', // pink
                        '#F472B6', // rose
                        '#2DD4BF', // teal
                    ],
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1F2937',
                        bodyColor: '#1F2937',
                        borderColor: '#E5E7EB',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        boxPadding: 6
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Student Enrollment Chart (Bar)
    const enrollmentCtx = document.getElementById('enrollmentChart');
    if (enrollmentCtx) {
        new Chart(enrollmentCtx, {
            type: 'bar',
            data: {
                labels: data.enrollmentLabels || [],
                datasets: [{
                    label: 'Students',
                    data: data.enrollmentData || [],
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    borderColor: '#6366F1',
                    borderWidth: 2,
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        padding: 12
                    }
                }
            }
        });
    }
});
