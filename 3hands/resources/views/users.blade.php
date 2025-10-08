@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                    Users Management
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage your application users with ease
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <!-- Bulk Delete Button -->
                <button id="bulk-delete-btn" 
                        class="items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 hidden"
                        onclick="handleBulkDelete()">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Selected
                </button>
                
                <!-- Add User Button -->
                <button onclick="handleAddUser()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <!-- Desktop Table (hidden on mobile) -->
        <div class="hidden md:block bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="tableHeader bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-8">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Users will be loaded by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards (shown on mobile) -->
        <div id="mobile-users-list" class="md:hidden space-y-4">
            <!-- Mobile users cards will be loaded here -->
        </div>

        <!-- Empty State (initially hidden) -->
        <div id="empty-state" class="text-center py-12 hidden">
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
        </div>
    </div>
</div>

<!-- User Modal -->
<div id="user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add New User</h3>
            <form id="user-form" class="space-y-4">
                <input type="hidden" id="user-id">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" 
                            onclick="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentEditingId = null;
    let allUsers = [];

    // Load users from backend
    async function loadUsers() {
        try {
            const response = await fetch('/users/api');
            const result = await response.json();
            
            if (result.success) {
                allUsers = result.users;
                renderUsers(allUsers);
            } else {
                showToast('Failed to load users', 'error');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            showToast('Error loading users', 'error');
        }
    }

    // Render users in both desktop table and mobile cards
    function renderUsers(users) {
        const mobileMediaQuery = window.matchMedia('(max-width: 767px)');
        const tableHeader = document.querySelector('thead') ; 
        const desktopTable = document.querySelector('.desktop') 
        const mobileTable = document.querySelector('.mobile') 
       
        // Check initially
    if (mobileMediaQuery.matches) {
        console.log('Currently in mobile view');
    } else {
        console.log('Currently in desktop view');
    }

    // Listen for changes
        mobileMediaQuery.addEventListener('change', (event) => {
            if (event.matches) {
                console.log('Changed to mobile view');
                // Execute mobile-specific functions
                    
                tableHeader.classList.add('hidden');
                // desktopTable.classList.add('hidden');
                
                renderMobileCards(users);
                
            } else {
                console.log('Changed to desktop view');
                // Execute desktop-specific functions       
                tableHeader.classList.remove('hidden');
                renderDesktopTable(users);
                
            }
        });
        updateEmptyState(users.length === 0);
    }

    // Render desktop table
    function renderDesktopTable(users) {
        
        const tbody = document.getElementById('users-table-body');
        
        tbody.innerHTML = users.map(user => `
            <tr class="desktop hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" 
                           class="user-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 desktop-checkbox" 
                           value="${user.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                        ${user.name}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                        ${user.email}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                        ${user.status}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="handleEditUser(${user.id})" 
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                        Edit
                    </button>
                    <button onclick="handleDeleteUser(${user.id})" 
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                        Delete
                    </button>
                </td>
            </tr>
        `).join('');
        
        initCheckboxHandlers();
    }

    // Render mobile cards
    function renderMobileCards(users) {
        const mobileList = document.getElementById('mobile-users-list');
        
        mobileList.innerHTML = users.map(user => `
            <div class="mobile bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <!-- Header with checkbox and name -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   class="mobile-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                   value="${user.id}">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">${user.name}</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    ${user.status}
                                </span>
                            </div>
                        </div>
                        <button onclick="toggleMobileDetails(${user.id})" 
                                class="text-gray-400 hover:text-gray-500 transition-colors">
                            <svg id="chevron-${user.id}" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Collapsible details -->
                    <div id="details-${user.id}" class="hidden border-t border-gray-200 dark:border-gray-600 pt-3 mt-3">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Email:</span>
                                <span class="text-sm text-gray-900 dark:text-white">${user.email}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="text-sm text-gray-900 dark:text-white">${user.status}</span>
                            </div>
                            <div class="flex space-x-3 pt-2">
                                <button onclick="handleEditUser(${user.id})" 
                                        class="flex-1 bg-indigo-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-indigo-700 transition-colors">
                                    Edit
                                </button>
                                <button onclick="handleDeleteUser(${user.id})" 
                                        class="flex-1 bg-red-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-red-700 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        initMobileCheckboxHandlers();
    }

    // Toggle mobile details
    function toggleMobileDetails(userId) {
        const details = document.getElementById(`details-${userId}`);
        const chevron = document.getElementById(`chevron-${userId}`);
        
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            details.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    // Update empty state
    function updateEmptyState(isEmpty) {
        const emptyState = document.getElementById('empty-state');
        const desktopTable = document.querySelector('.hidden.md\\:block');
        const mobileList = document.getElementById('mobile-users-list');
        
        if (isEmpty) {
            emptyState.classList.remove('hidden');
            desktopTable?.classList.add('hidden');
            mobileList.classList.add('hidden');
        } else {
            emptyState.classList.add('hidden');
            desktopTable?.classList.remove('hidden');
            mobileList.classList.remove('hidden');
        }
    }

    // User management functions
    function handleAddUser() {
        currentEditingId = null;
        document.getElementById('modal-title').textContent = 'Add New User';
        document.getElementById('user-form').reset();
        document.getElementById('user-id').value = '';
        document.getElementById('user-modal').classList.remove('hidden');
    }

    async function handleEditUser(id) {
        const user = allUsers.find(u => u.id == id);
        if (user) {
            currentEditingId = id;
            document.getElementById('modal-title').textContent = 'Edit User';
            document.getElementById('user-id').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('user-modal').classList.remove('hidden');
        }
    }

    async function handleDeleteUser(id) {
        const user = allUsers.find(u => u.id == id);
        if (user && confirm(`Are you sure you want to delete user "${user.name}"?`)) {
            try {
                const response = await fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    await loadUsers(); // Reload users from backend
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                showToast('Error deleting user', 'error');
            }
        }
    }

    async function handleBulkDelete() {
        const desktopCheckboxes = document.querySelectorAll('.desktop-checkbox:checked');
        const mobileCheckboxes = document.querySelectorAll('.mobile-checkbox:checked');
        
        const selectedIds = [
            ...Array.from(desktopCheckboxes).map(cb => parseInt(cb.value)),
            ...Array.from(mobileCheckboxes).map(cb => parseInt(cb.value))
        ];
        
        if (selectedIds.length === 0) {
            showToast('Please select at least one user to delete.', 'warning');
            return;
        }
        
        if (confirm(`Are you sure you want to delete ${selectedIds.length} user(s)?`)) {
            try {
                const response = await fetch('/users/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ user_ids: selectedIds })
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    await loadUsers(); // Reload users from backend
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error bulk deleting users:', error);
                showToast('Error deleting users', 'error');
            }
        }
    }

    function closeModal() {
        document.getElementById('user-modal').classList.add('hidden');
        currentEditingId = null;
    }

    // Form submission
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        
        if (!name || !email) {
            showToast('Please fill in all fields.', 'error');
            return;
        }
        
        if (!validateEmail(email)) {
            showToast('Please enter a valid email address.', 'error');
            return;
        }
        
        const userData = { name, email };
        const url = currentEditingId ? `/users/${currentEditingId}` : '/users';
        const method = currentEditingId ? 'PUT' : 'POST';
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                closeModal();
                await loadUsers(); // Reload users from backend
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            showToast('Error saving user', 'error');
        }
    }

    // Checkbox handlers for desktop
    function initCheckboxHandlers() {
        const selectAll = document.getElementById('select-all');
        const desktopCheckboxes = document.querySelectorAll('.desktop-checkbox');
        
        if (selectAll && desktopCheckboxes.length > 0) {
            // Remove existing event listeners
            selectAll.replaceWith(selectAll.cloneNode(true));
            const newSelectAll = document.getElementById('select-all');
            
            newSelectAll.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.desktop-checkbox, .mobile-checkbox');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkDeleteButton();
            });
        }
        
        desktopCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkDeleteButton();
            });
        });
    }

    // Checkbox handlers for mobile
    function initMobileCheckboxHandlers() {
        const mobileCheckboxes = document.querySelectorAll('.mobile-checkbox');
        
        mobileCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkDeleteButton();
            });
        });
    }

    // Update select all checkbox state
    function updateSelectAllState() {
        const selectAll = document.getElementById('select-all');
        if (!selectAll) return;
        
        const allCheckboxes = document.querySelectorAll('.desktop-checkbox, .mobile-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.desktop-checkbox:checked, .mobile-checkbox:checked');
        
        selectAll.checked = checkedCheckboxes.length === allCheckboxes.length;
        selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    }

    // Update bulk delete button
    function updateBulkDeleteButton() {
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const allCheckboxes = document.querySelectorAll('.desktop-checkbox:checked, .mobile-checkbox:checked');
        const selectedCount = allCheckboxes.length;
        
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
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Initialize users page
    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
        
        const userForm = document.getElementById('user-form');
        if (userForm) {
            userForm.addEventListener('submit', handleFormSubmit);
        }
        
        const modal = document.getElementById('user-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }
    });

    // Make functions global
    window.handleAddUser = handleAddUser;
    window.handleEditUser = handleEditUser;
    window.handleDeleteUser = handleDeleteUser;
    window.handleBulkDelete = handleBulkDelete;
    window.closeModal = closeModal;
    window.toggleMobileDetails = toggleMobileDetails;
</script>

<style>
    /* Custom styles for better mobile experience */
    @media (max-width: 767px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    
    /* Smooth transitions for mobile details */
    #mobile-users-list [id^="details-"] {
        transition: all 0.3s ease-in-out;
    }
    
    /* Rotate animation for chevron */
    .rotate-180 {
        transform: rotate(180deg);
    }
</style>
@endsection