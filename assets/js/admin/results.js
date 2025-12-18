// assets/js/admin/results.js
document.addEventListener('DOMContentLoaded', function() {
    // Grade preview functionality
    // Grade preview functionality
    const marksInput = document.getElementById('marks_obtained');
    const gradePreview = document.getElementById('gradePreview');
    const subjectSelect = document.getElementById('subject_id');
    const termSelect = document.getElementById('term');

    if (marksInput && gradePreview) {
        marksInput.addEventListener('input', updateGradePreview);
        updateGradePreview(); // Initial update
    }

    // Class level filter - Primary filter for subjects and students
    const classLevelFilter = document.getElementById('class_level_filter');
    if (classLevelFilter) {
        classLevelFilter.addEventListener('change', function() {
            filterSubjectsByClassLevel();
            filterStudentsByClassLevel();
        });
    }
    
    // Auto-set term based on subject selection AND filter students by class level
    if (subjectSelect && termSelect) {
        subjectSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.term) {
                termSelect.value = selectedOption.dataset.term;
            }
            
            // Filter students by class level (from subject's class level)
            filterStudentsBySubject();
        });
    }
    
    // Also enable filtering on page load if subject is preselected
    if (subjectSelect) {
        filterStudentsBySubject();
    }
    
    // Bulk form class level filter
    const bulkClassLevelFilter = document.getElementById('bulk_class_level');
    if (bulkClassLevelFilter) {
        bulkClassLevelFilter.addEventListener('change', function() {
            filterBulkSubjectsByClassLevel();
        });
        // Initialize on page load
        filterBulkSubjectsByClassLevel();
    }

    // Bulk upload form enhancements
    const bulkForm = document.querySelector('form');
    if (bulkForm && document.querySelector('input[name^="results"]')) {
        enhanceBulkForm();
    }
});

function updateGradePreview() {
    const marksInput = document.getElementById('marks_obtained');
    const gradePreview = document.getElementById('gradePreview');
    
    if (!marksInput || !gradePreview) return;

    const marks = parseFloat(marksInput.value);
    
    if (isNaN(marks) || marks < 0 || marks > 100) {
        gradePreview.innerHTML = '<span class="text-lg font-semibold text-gray-600">Enter valid marks (0-100)</span>';
        return;
    }

    const gradeInfo = calculateGradeFromMarks(marks);
    const gradeClass = gradeInfo.grade === 'F' ? 'text-red-600' : 'text-green-600';
    const bgClass = gradeInfo.grade === 'F' ? 'bg-red-100' : 'bg-green-100';
    
    gradePreview.innerHTML = `
        <div class="inline-flex items-center px-4 py-2 rounded-lg ${bgClass}">
            <span class="text-2xl font-bold ${gradeClass} mr-2">${gradeInfo.grade}</span>
            <span class="text-lg ${gradeClass}">Grade Point: ${gradeInfo.point}</span>
        </div>
    `;
}

function calculateGradeFromMarks(marks) {
    const gradeScale = {
        'A': { min: 90, max: 100, point: 4.0 },
        'A-': { min: 85, max: 89, point: 3.7 },
        'B+': { min: 80, max: 84, point: 3.3 },
        'B': { min: 75, max: 79, point: 3.0 },
        'B-': { min: 70, max: 74, point: 2.7 },
        'C+': { min: 65, max: 69, point: 2.3 },
        'C': { min: 60, max: 64, point: 2.0 },
        'D': { min: 50, max: 59, point: 1.0 },
        'F': { min: 0, max: 49, point: 0.0 }
    };

    for (const [grade, range] of Object.entries(gradeScale)) {
        if (marks >= range.min && marks <= range.max) {
            return { grade, point: range.point };
        }
    }
    
    return { grade: 'F', point: 0.0 };
}

function enhanceBulkForm() {
    const marksInputs = document.querySelectorAll('input[name^="results"]');
    
    marksInputs.forEach(input => {
        input.addEventListener('input', function() {
            const marks = parseFloat(this.value);
            
            if (!isNaN(marks)) {
                if (marks < 0) this.value = 0;
                if (marks > 100) this.value = 100;
                
                // Add visual feedback for valid/invalid marks
                if (marks >= 0 && marks <= 100) {
                    this.classList.remove('border-red-300', 'bg-red-50');
                    this.classList.add('border-green-300', 'bg-green-50');
                } else {
                    this.classList.remove('border-green-300', 'bg-green-50');
                    this.classList.add('border-red-300', 'bg-red-50');
                }
            } else {
                this.classList.remove('border-red-300', 'bg-red-50', 'border-green-300', 'bg-green-50');
            }
        });
    });

    // Add bulk actions
    addBulkActions();
}

function addBulkActions() {
    const actionBar = document.createElement('div');
    actionBar.className = 'flex justify-between items-center mb-4 p-4 bg-gray-50 rounded-lg';
    actionBar.innerHTML = `
        <div class="flex space-x-2">
            <button type="button" onclick="fillAllMarks(0)" class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded transition duration-200">
                Set All to 0
            </button>
            <button type="button" onclick="fillAllMarks(50)" class="px-3 py-1 text-sm bg-blue-200 hover:bg-blue-300 rounded transition duration-200">
                Set All to 50
            </button>
            <button type="button" onclick="fillAllMarks(75)" class="px-3 py-1 text-sm bg-green-200 hover:bg-green-300 rounded transition duration-200">
                Set All to 75
            </button>
            <button type="button" onclick="clearAllMarks()" class="px-3 py-1 text-sm bg-red-200 hover:bg-red-300 rounded transition duration-200">
                Clear All
            </button>
        </div>
        <div class="text-sm text-gray-600">
            <span id="filledCount">0</span> / <span id="totalCount">0</span> students filled
        </div>
    `;
    
    const form = document.querySelector('form');
    form.insertBefore(actionBar, form.firstChild);
    
    updateFilledCount();
    
    // Update count when inputs change
    const marksInputs = document.querySelectorAll('input[name^="results"]');
    marksInputs.forEach(input => {
        input.addEventListener('input', updateFilledCount);
    });
}

function fillAllMarks(marks) {
    const marksInputs = document.querySelectorAll('input[name^="results"]');
    marksInputs.forEach(input => {
        input.value = marks;
        input.dispatchEvent(new Event('input'));
    });
    showNotification(`All marks set to ${marks}`, 'success');
}

function clearAllMarks() {
    const marksInputs = document.querySelectorAll('input[name^="results"]');
    marksInputs.forEach(input => {
        input.value = '';
        input.classList.remove('border-red-300', 'bg-red-50', 'border-green-300', 'bg-green-50');
    });
    updateFilledCount();
    showNotification('All marks cleared', 'info');
}

function updateFilledCount() {
    const marksInputs = document.querySelectorAll('input[name^="results"]');
    const filledInputs = Array.from(marksInputs).filter(input => input.value !== '');
    
    const filledCount = document.getElementById('filledCount');
    const totalCount = document.getElementById('totalCount');
    
    if (filledCount && totalCount) {
        filledCount.textContent = filledInputs.length;
        totalCount.textContent = marksInputs.length;
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resultForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const marksInput = document.getElementById('marks_obtained');
            if (marksInput) {
                const marks = parseFloat(marksInput.value);
                if (isNaN(marks) || marks < 0 || marks > 100) {
                    e.preventDefault();
                    showNotification('Please enter valid marks between 0 and 100', 'error');
                    marksInput.focus();
                }
            }
        });
    }
});

// Filter subjects by class level (from class level dropdown)
function filterSubjectsByClassLevel() {
    const classLevelFilter = document.getElementById('class_level_filter');
    const subjectSelect = document.getElementById('subject_id');
    
    if (!classLevelFilter || !subjectSelect) return;
    
    const selectedClassLevel = classLevelFilter.value;
    const subjectOptions = subjectSelect.querySelectorAll('option');
    let visibleCount = 0;
    
    subjectOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }
        
        const subjectClassLevel = option.dataset.classLevel;
        
        if (!selectedClassLevel || subjectClassLevel === selectedClassLevel) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset subject selection if hidden
    const currentSubject = subjectSelect.options[subjectSelect.selectedIndex];
    if (currentSubject && currentSubject.style.display === 'none') {
        subjectSelect.value = '';
    }
}

// Filter students by class level (from subject's class level)
function filterStudentsBySubject() {
    const subjectSelect = document.getElementById('subject_id');
    const studentSelect = document.getElementById('student_id');
    
    if (!subjectSelect || !studentSelect) return;
    
    const selectedSubOption = subjectSelect.options[subjectSelect.selectedIndex];
    const selectedClassLevel = selectedSubOption ? selectedSubOption.dataset.classLevel : null;
    
    const studentOptions = studentSelect.querySelectorAll('option');
    let visibleCount = 0;
    
    studentOptions.forEach(option => {
        if (option.value === '') {
            // Keep the "Select Student" placeholder
            option.style.display = '';
            return;
        }
        
        const studentClassLevel = option.dataset.classLevel;
        
        if (!selectedClassLevel || selectedClassLevel === '' || studentClassLevel === selectedClassLevel) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset student selection if current selection is now hidden
    const currentlySelected = studentSelect.options[studentSelect.selectedIndex];
    if (currentlySelected && currentlySelected.style.display === 'none') {
        studentSelect.value = '';
    }
    
    // Show notification about filtering
    if (selectedClassLevel && visibleCount > 0) {
        console.log(`Filtered to ${visibleCount} students from ${selectedClassLevel}`);
    }
}

// Filter students by class level from class level dropdown
function filterStudentsByClassLevel() {
    const classLevelFilter = document.getElementById('class_level_filter');
    const studentSelect = document.getElementById('student_id');
    
    if (!classLevelFilter || !studentSelect) return;
    
    const selectedClassLevel = classLevelFilter.value;
    const studentOptions = studentSelect.querySelectorAll('option');
    let visibleCount = 0;
    
    studentOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }
        
        const studentClassLevel = option.dataset.classLevel;
        
        if (!selectedClassLevel || studentClassLevel === selectedClassLevel) {
            option.style.display = '';
            visibleCount++;
        } else {
            option.style.display = 'none';
        }
    });
    
    
    // Also filter student rows
    filterBulkStudentRows();
}

// Filter subjects in bulk form by class level
function filterBulkSubjectsByClassLevel() {
    const classLevelFilter = document.getElementById('bulk_class_level');
    const subjectSelect = document.getElementById('bulk_subject_id');
    
    if (!classLevelFilter || !subjectSelect) return;
    
    const selectedClassLevel = classLevelFilter.value;
    const subjectOptions = subjectSelect.querySelectorAll('option');
    
    subjectOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }
        
        const subjectClassLevel = option.dataset.classLevel;
        
        if (!selectedClassLevel || subjectClassLevel === selectedClassLevel) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset subject if hidden
    const currentSubject = subjectSelect.options[subjectSelect.selectedIndex];
    if (currentSubject && currentSubject.style.display === 'none') {
        subjectSelect.value = '';
    }
    
    // Also filter student rows
    filterBulkStudentRows();
}

// Filter student table rows in bulk form
function filterBulkStudentRows() {
    const classLevelFilter = document.getElementById('bulk_class_level');
    if (!classLevelFilter) return;
    
    const selectedClassLevel = classLevelFilter.value;
    const studentRows = document.querySelectorAll('tbody tr[data-class-level]');
    let visibleCount = 0;
    
    studentRows.forEach(row => {
        const rowClassLevel = row.dataset.classLevel;
        
        if (!selectedClassLevel || rowClassLevel === selectedClassLevel) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    console.log(`Bulk form: Showing ${visibleCount} students for ${selectedClassLevel || 'all classes'}`);
}