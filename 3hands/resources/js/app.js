
// Theme management
function toggleTheme() {
    console.log('Toggle theme called');
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcon(isDark ? 'dark' : 'light');
}

function updateThemeIcon(theme) {
    console.log('Update theme icon:', theme);
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;
    
    const icons = themeToggle.querySelectorAll('svg');
    console.log('Icons found:', icons.length);
    
    if (icons.length >= 2) {
        icons[0].classList.toggle('hidden', theme === 'dark');
        icons[1].classList.toggle('hidden', theme !== 'dark');
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    console.log('Show toast:', message, type);
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    
    if (!toast || !toastMessage) {
        console.error('Toast elements not found');
        // Fallback: use alert for important messages
        if (type === 'error') {
            alert('Error: ' + message);
        }
        return;
    }
    
    // Reset classes and set the type
    toast.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50';
    toast.classList.add(`toast-${type}`);
    
    toastMessage.textContent = message;
    toast.classList.remove('hidden', 'translate-x-full');
    toast.classList.add('translate-x-0');
    
    setTimeout(hideToast, 5000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }
}

// Get CSRF Token safely
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (!metaTag) {
        console.error('CSRF token meta tag not found!');
        return null;
    }
    return metaTag.getAttribute('content');
}

// User management functions
let currentEditingIndex = null;
let currentEditingId = null;

function handleAddUser() {
    console.log('handleAddUser called');
    currentEditingIndex = null;
    currentEditingId = null;
    document.getElementById('modal-title').textContent = 'Add New User';
    document.getElementById('user-form').reset();
    document.getElementById('user-modal').classList.remove('hidden');
}

function handleEditUser(index) {
    console.log('handleEditUser called with index:', index);
    const rows = document.querySelectorAll('tbody tr');
    
    if (rows[index]) {
        const id = rows[index].querySelector('.user-checkbox').value;
        const nameCell = rows[index].querySelector('td:nth-child(2)');
        const emailCell = rows[index].querySelector('td:nth-child(3)');
        
        if (nameCell && emailCell) {
            const name = nameCell.textContent.trim();
            const email = emailCell.textContent.trim();
            
            currentEditingIndex = index;
            currentEditingId = id;
            
            document.getElementById('modal-title').textContent = 'Edit User';
            document.getElementById('name').value = name;
            document.getElementById('email').value = email;
            document.getElementById('user-modal').classList.remove('hidden');
        }
    }
}

function handleDeleteUser(index) {
    console.log('handleDeleteUser called with index:', index);
    const rows = document.querySelectorAll('tbody tr');
    
    if (rows[index]) {
        const id = rows[index].querySelector('.user-checkbox').value;
        const name = rows[index].querySelector('td:nth-child(2)').textContent.trim();
        
        if (confirm(`Are you sure you want to delete user "${name}"?`)) {
            deleteUser(id, index);
        }
    }
}

function handleBulkDelete() {
    console.log('handleBulkDelete called');
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const selectedUsers = Array.from(selectedCheckboxes).map(checkbox => ({
        id: checkbox.value,
        index: Array.from(document.querySelectorAll('.user-checkbox')).indexOf(checkbox)
    }));
    
    console.log('Selected users:', selectedUsers);
    
    if (selectedUsers.length === 0) {
        showToast('Please select at least one user to delete.', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedUsers.length} user(s)?`)) {
        bulkDeleteUsers(selectedUsers);
    }
}

function closeModal() {
    console.log('closeModal called');
    document.getElementById('user-modal').classList.add('hidden');
    currentEditingIndex = null;
    currentEditingId = null;
}

// ===== API FUNCTIONS =====

async function addUser(userData) {
    try {
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }

        const response = await fetch('/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            closeModal();
            // Refresh the page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.message || 'Error adding user', 'error');
        }
    } catch (error) {
        console.error('Error adding user:', error);
        showToast('Error adding user: ' + error.message, 'error');
    }
}

async function updateUser(id, userData) {
    try {
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }

        const response = await fetch(`/users/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            closeModal();
            // Refresh the page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.message || 'Error updating user', 'error');
        }
    } catch (error) {
        console.error('Error updating user:', error);
        showToast('Error updating user: ' + error.message, 'error');
    }
}

async function deleteUser(id, index) {
    try {
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }

        const response = await fetch(`/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            // Remove row from table immediately
            const row = document.querySelectorAll('tbody tr')[index];
            if (row) {
                row.remove();
            }
            // Update indices for remaining checkboxes
            updateCheckboxIndices();
            
            // If no users left, show empty state
            if (document.querySelectorAll('tbody tr').length === 0) {
                showEmptyState();
            }
        } else {
            showToast(result.message || 'Error deleting user', 'error');
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showToast('Error deleting user: ' + error.message, 'error');
    }
}

async function bulkDeleteUsers(selectedUsers) {
    try {
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }

        const userIds = selectedUsers.map(user => user.id);
        
        const response = await fetch('/users/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ user_ids: userIds })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            
            // Remove selected rows from table immediately
            selectedUsers.forEach(user => {
                const row = document.querySelectorAll('tbody tr')[user.index];
                if (row) {
                    row.remove();
                }
            });
            
            // Reset selection
            document.getElementById('select-all').checked = false;
            document.getElementById('bulk-delete-btn').classList.add('hidden');
            
            // Update indices for remaining checkboxes
            updateCheckboxIndices();
            
            // If no users left, show empty state
            if (document.querySelectorAll('tbody tr').length === 0) {
                showEmptyState();
            }
        } else {
            showToast(result.message || 'Error deleting users', 'error');
        }
    } catch (error) {
        console.error('Error bulk deleting users:', error);
        showToast('Error deleting users: ' + error.message, 'error');
    }
}

function updateCheckboxIndices() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach((checkbox, index) => {
        checkbox.value = index;
    });
}

function showEmptyState() {
    const tableBody = document.querySelector('tbody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No users</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new user.</p>
                    <div class="mt-6">
                        <button onclick="handleAddUser()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add User
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// ===== USERS PAGE SPECIFIC FUNCTIONS =====

function initCheckboxHandlers() {
    console.log('Initializing checkbox handlers');
    
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    
    console.log('Select all found:', !!selectAll);
    console.log('User checkboxes found:', userCheckboxes.length);
    console.log('Bulk delete button found:', !!bulkDeleteBtn);
    
    if (selectAll && userCheckboxes.length > 0) {
        selectAll.addEventListener('change', function() {
            console.log('Select all changed:', this.checked);
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    userCheckboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            console.log(`Checkbox ${index} changed:`, this.checked);
            updateBulkDeleteButton();
        });
    });
    
    function updateBulkDeleteButton() {
        const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
        console.log('Selected count:', selectedCount);
        
        if (bulkDeleteBtn) {
            if (selectedCount > 0) {
                bulkDeleteBtn.classList.remove('hidden');
                bulkDeleteBtn.innerHTML = `
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Selected (${selectedCount})
                `;
            } else {
                bulkDeleteBtn.classList.add('hidden');
            }
        }
        
        // Update select all checkbox state
        if (selectAll) {
            selectAll.checked = selectedCount === userCheckboxes.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < userCheckboxes.length;
        }
    }
}

function initFormHandlers() {
    console.log('Initializing form handlers');
    
    const userForm = document.getElementById('user-form');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            
            console.log('Form data:', { name, email });
            
            if (!name || !email) {
                showToast('Please fill in all fields.', 'error');
                return;
            }
            
            if (!validateEmail(email)) {
                showToast('Please enter a valid email address.', 'error');
                return;
            }
            
            const userData = { name, email };
            
            if (currentEditingId) {
                // Update existing user
                updateUser(currentEditingId, userData);
            } else {
                // Add new user
                addUser(userData);
            }
        });
    }
}

function initModalHandlers() {
    console.log('Initializing modal handlers');
    
    const modal = document.getElementById('user-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
}


document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    
    // Check for CSRF token
    const csrfToken = getCsrfToken();
    console.log('CSRF Token available:', !!csrfToken);
    
    if (!csrfToken) {
        console.warn('CSRF token not found. API calls will fail.');
    }
    
    // Initialize theme
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.classList.toggle('dark', theme === 'dark');
    updateThemeIcon(theme);
    
    // Theme toggle event listener
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Toast close button
    const toastClose = document.getElementById('toast-close');
    if (toastClose) {
        toastClose.addEventListener('click', hideToast);
    }
    
    // Language selector
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            showToast(`Language changed to ${this.options[this.selectedIndex].text}`, 'info');
        });
    }
    
    // Check if we're on the users page
    if (window.location.pathname.includes('/users')) {
        console.log('On users page - initializing user handlers');
        initUserPage();
    }
});

function initUserPage() {
    console.log('Initializing users page functionality');
    
    // Initialize all user-related handlers
    initCheckboxHandlers();
    initFormHandlers();
    initModalHandlers();
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
        
        if (e.altKey) {
            switch(e.key.toLowerCase()) {
                case 'n':
                    e.preventDefault();
                    handleAddUser();
                    break;
                case 'escape':
                    e.preventDefault();
                    closeModal();
                    hideToast();
                    break;
            }
        }
    });
}

// ===== EXPOSE FUNCTIONS TO GLOBAL SCOPE =====

window.toggleTheme = toggleTheme;
window.showToast = showToast;
window.hideToast = hideToast;
window.handleAddUser = handleAddUser;
window.handleEditUser = handleEditUser;
window.handleDeleteUser = handleDeleteUser;
window.handleBulkDelete = handleBulkDelete;
window.closeModal = closeModal;

console.log('=== APP.JS LOADED - ALL FUNCTIONS ARE GLOBAL ===');