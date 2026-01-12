/**
 * TaskFlow - JavaScript File
 * UAS Pemrograman Web Dasar
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('TaskFlow Application Loaded');
    
    // Initialize components
    initFormValidation();
    initTaskInteractions();
    initFilters();
    initModal();
    initKeyboardShortcuts();
    
    // Set minimum date for deadline input
    const deadlineInput = document.getElementById('deadline');
    if (deadlineInput) {
        const today = new Date().toISOString().split('T')[0];
        deadlineInput.min = today;
        
        // Set default deadline to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        deadlineInput.value = tomorrow.toISOString().split('T')[0];
    }
});

/**
 * Form Validation
 */
function initFormValidation() {
    const taskForm = document.getElementById('taskForm');
    if (!taskForm) return;
    
    taskForm.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const deadline = document.getElementById('deadline').value;
        
        if (!title) {
            e.preventDefault();
            showToast('Judul tugas harus diisi!', 'error');
            document.getElementById('title').focus();
            return;
        }
        
        if (!deadline) {
            e.preventDefault();
            showToast('Deadline harus ditentukan!', 'error');
            document.getElementById('deadline').focus();
            return;
        }
        
        // Show loading state
        const submitBtn = taskForm.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
        
        // Re-enable after 2 seconds (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
}

/**
 * Task Interactions
 */
function initTaskInteractions() {
    // Add animation to status toggle
    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const taskCard = this.closest('.task-card');
            taskCard.classList.add('updating');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                taskCard.classList.remove('updating');
            }, 300);
        });
    });
    
    // Add confirmation for delete buttons
    document.querySelectorAll('form[action="delete_task.php"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
                e.preventDefault();
            }
        });
    });
    
    // Add hover effects to task cards
    document.querySelectorAll('.task-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

/**
 * Filter and Search Functionality
 */
function initFilters() {
    const searchInput = document.querySelector('.search-box input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length > 2 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
    
    // Add active state to filter buttons
    document.querySelectorAll('.filter-btn, .category-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            // Add loading indicator
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            
            // Restore after 1 second
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 1000);
        });
    });
}

/**
 * Modal Functions
 */
function initModal() {
    const modal = document.getElementById('dbModal');
    if (!modal) return;
    
    const closeBtn = modal.querySelector('.close');
    
    // Close modal when clicking X
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

/**
 * Show Database Info Modal
 */
function showDatabaseInfo() {
    const modal = document.getElementById('dbModal');
    if (modal) {
        modal.style.display = 'block';
    }
    return false;
}

/**
 * Export Tasks to CSV
 */
function exportToCSV() {
    // In a real implementation, this would fetch data from server
    const tasks = [];
    document.querySelectorAll('.task-card').forEach(card => {
        const task = {
            title: card.querySelector('.task-title').textContent,
            category: card.querySelector('.task-category').textContent,
            priority: card.querySelector('.task-priority').textContent,
            deadline: card.querySelector('.task-deadline').textContent.trim(),
            completed: card.classList.contains('completed') ? 'Ya' : 'Tidak'
        };
        tasks.push(task);
    });
    
    // Create CSV content
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Judul,Kategori,Prioritas,Deadline,Selesai\n";
    
    tasks.forEach(task => {
        csvContent += `"${task.title}","${task.category}","${task.priority}","${task.deadline}","${task.completed}"\n`;
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "taskflow_tasks.csv");
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    document.body.removeChild(link);
    
    showToast('Data berhasil diekspor ke CSV!', 'success');
}

/**
 * Print Tasks
 */
function printTasks() {
    window.print();
}

/**
 * Keyboard Shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: Focus on new task form
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const titleInput = document.getElementById('title');
            if (titleInput) {
                titleInput.focus();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        }
        
        // Escape: Clear search
        if (e.key === 'Escape') {
            const searchInput = document.querySelector('.search-box input[name="search"]');
            if (searchInput && searchInput.value) {
                window.location.href = window.location.pathname;
            }
        }
        
        // Ctrl/Cmd + F: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-box input[name="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }, 3000);
}

/**
 * Add CSS for Toast
 */
const toastCSS = `
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10000;
    transform: translateX(150%);
    transition: transform 0.3s ease;
    min-width: 300px;
    border-left: 5px solid #4f46e5;
}

.toast.show {
    transform: translateX(0);
}

.toast-success {
    border-left-color: #10b981;
}

.toast-error {
    border-left-color: #ef4444;
}

.toast i {
    font-size: 1.2rem;
}

.toast-success i {
    color: #10b981;
}

.toast-error i {
    color: #ef4444;
}
`;

// Add toast styles to document
const style = document.createElement('style');
style.textContent = toastCSS;
document.head.appendChild(style);

/**
 * Initialize Task Countdown
 */
function updateTaskCountdown() {
    document.querySelectorAll('.days-left').forEach(element => {
        const days = parseInt(element.textContent.match(/\d+/)[0]);
        if (days === 0) {
            element.innerHTML = '(Hari ini!)';
            element.style.color = '#ef4444';
            element.style.fontWeight = 'bold';
        } else if (days < 0) {
            element.innerHTML = '(Terlambat!)';
            element.style.color = '#ef4444';
            element.style.fontWeight = 'bold';
        } else if (days <= 3) {
            element.style.color = '#f59e0b';
            element.style.fontWeight = 'bold';
        }
    });
}

// Initialize countdown on load
updateTaskCountdown();

/**
 * Auto-save form data (using localStorage)
 */
function initAutoSave() {
    const form = document.getElementById('taskForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    // Load saved data
    inputs.forEach(input => {
        const savedValue = localStorage.getItem(`taskform_${input.name}`);
        if (savedValue !== null && !input.value) {
            input.value = savedValue;
        }
    });
    
    // Save on input change
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            localStorage.setItem(`taskform_${this.name}`, this.value);
        });
    });
    
    // Clear saved data on form submit
    form.addEventListener('submit', function() {
        inputs.forEach(input => {
            localStorage.removeItem(`taskform_${input.name}`);
        });
    });
}

// Initialize auto-save
initAutoSave();