/**
 * Laravel Generator CRUD Operations
 * JavaScript functionality for single-page CRUD interfaces
 */

// Global variables
let currentEditId = null;
let searchTimeout = null;
let currentPage = 1;

// API Configuration
const CRUD_CONFIG = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    debounceDelay: 300
};

/**
 * Initialize CRUD operations on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeCrudOperations();
});

/**
 * Setup CRUD event listeners and configurations
 */
function initializeCrudOperations() {
    setupCSRFToken();
    initializeEventListeners();
    setupModalAnimations();
}

/**
 * Setup CSRF token for AJAX requests
 */
function setupCSRFToken() {
    if (typeof $ !== 'undefined' && CRUD_CONFIG.csrfToken) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': CRUD_CONFIG.csrfToken
            }
        });
    }
}

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Form submission
    const crudForm = document.getElementById('crud-form');
    if (crudForm) {
        crudForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    
    // Close modals when clicking outside
    setupModalClickOutside();
}

/**
 * Handle form submission for create/update operations
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submit-btn');
    
    // Disable submit button during request
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    
    try {
        let url, method;
        
        if (currentEditId) {
            // Update existing record
            url = `${getApiBase()}/${currentEditId}`;
            method = 'PUT';
            formData.append('_method', 'PUT');
        } else {
            // Create new record
            url = getApiBase();
            method = 'POST';
        }
        
        const response = await fetch(url, {
            method: 'POST', // Always use POST for Laravel
            body: formData,
            headers: {
                'X-CSRF-TOKEN': CRUD_CONFIG.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert('success', result.message || 'Operation completed successfully');
            closeModal();
            refreshTable();
            resetForm();
        } else {
            handleFormErrors(result.errors || {});
            showAlert('error', result.message || 'An error occurred');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        showAlert('error', 'An unexpected error occurred');
    } finally {
        // Re-enable submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = currentEditId ? 'Update' : 'Create';
        }
    }
}

/**
 * Open create modal
 */
function openCreateModal() {
    currentEditId = null;
    resetForm();
    document.getElementById('modal-title').textContent = getResourceName('create');
    document.getElementById('submit-btn').textContent = 'Create';
    showModal('form-modal');
}

/**
 * Open edit modal with existing data
 */
async function editItem(id) {
    try {
        const response = await fetch(`${getApiBase()}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            currentEditId = id;
            populateForm(data);
            document.getElementById('modal-title').textContent = getResourceName('edit');
            document.getElementById('submit-btn').textContent = 'Update';
            showModal('form-modal');
        } else {
            showAlert('error', 'Failed to load item data');
        }
    } catch (error) {
        console.error('Edit item error:', error);
        showAlert('error', 'Failed to load item data');
    }
}

/**
 * View item details
 */
async function viewItem(id) {
    try {
        const response = await fetch(`${getApiBase()}/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            populateViewModal(data);
            document.getElementById('edit-from-view-btn').onclick = () => {
                closeViewModal();
                editItem(id);
            };
            showModal('view-modal');
        } else {
            showAlert('error', 'Failed to load item data');
        }
    } catch (error) {
        console.error('View item error:', error);
        showAlert('error', 'Failed to load item data');
    }
}

/**
 * Delete item confirmation
 */
function deleteItem(id) {
    document.getElementById('confirm-delete-btn').onclick = () => confirmDelete(id);
    showModal('delete-modal');
}

/**
 * Confirm delete operation
 */
async function confirmDelete(id) {
    try {
        const response = await fetch(`${getApiBase()}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CRUD_CONFIG.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showAlert('success', result.message || 'Item deleted successfully');
            closeDeleteModal();
            refreshTable();
        } else {
            showAlert('error', result.message || 'Failed to delete item');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showAlert('error', 'Failed to delete item');
    }
}

/**
 * Search functionality with debouncing
 */
function debounceSearch(query) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, CRUD_CONFIG.debounceDelay);
}

/**
 * Perform search operation
 */
async function performSearch(query) {
    try {
        const response = await fetch(`${getApiBase()}?search=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const html = await response.text();
        updateTableBody(html);
    } catch (error) {
        console.error('Search error:', error);
        showAlert('error', 'Search failed');
    }
}

/**
 * Refresh table data
 */
async function refreshTable() {
    try {
        const response = await fetch(getApiBase(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const html = await response.text();
            updateTableBody(html);
        }
    } catch (error) {
        console.error('Refresh table error:', error);
    }
}

/**
 * Reset search filters
 */
function resetFilters() {
    document.getElementById('search-input').value = '';
    refreshTable();
}

// Modal Management Functions

/**
 * Show modal with animation
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('animate-in');
        document.body.classList.add('overflow-hidden');
    }
}

/**
 * Close specific modal
 */
function closeModal() {
    const modal = document.getElementById('form-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('animate-in');
        document.body.classList.remove('overflow-hidden');
    }
}

/**
 * Close view modal
 */
function closeViewModal() {
    const modal = document.getElementById('view-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('animate-in');
        document.body.classList.remove('overflow-hidden');
    }
}

/**
 * Close delete modal
 */
function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('animate-in');
        document.body.classList.remove('overflow-hidden');
    }
}

/**
 * Close all modals
 */
function closeAllModals() {
    closeModal();
    closeViewModal();
    closeDeleteModal();
}

/**
 * Edit from view modal
 */
function editFromView() {
    closeViewModal();
    if (currentEditId) {
        editItem(currentEditId);
    }
}

// Utility Functions

/**
 * Get API base URL from current page
 */
function getApiBase() {
    return window.location.pathname.replace(/\/(create|edit\/\d+)?$/, '');
}

/**
 * Get resource name for titles
 */
function getResourceName(action) {
    const resourceName = getApiBase().split('/').pop().replace(/-/g, ' ');
    const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);
    return `${capitalize(action)} ${capitalize(resourceName)}`;
}

/**
 * Reset form to initial state
 */
function resetForm() {
    const form = document.getElementById('crud-form');
    if (form) {
        form.reset();
        clearFormErrors();
    }
    currentEditId = null;
}

/**
 * Populate form with data for editing
 */
function populateForm(data) {
    const form = document.getElementById('crud-form');
    if (!form) return;
    
    Object.keys(data).forEach(key => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            if (field.type === 'checkbox') {
                field.checked = !!data[key];
            } else {
                field.value = data[key] || '';
            }
        }
    });
}

/**
 * Populate view modal with data
 */
function populateViewModal(data) {
    const viewContent = document.getElementById('view-content');
    if (!viewContent) return;
    
    let html = '';
    Object.keys(data).forEach(key => {
        if (!['id', 'created_at', 'updated_at'].includes(key)) {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const value = data[key] || 'N/A';
            html += `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">${label}</label>
                    <p class="mt-1 text-sm text-gray-900">${value}</p>
                </div>
            `;
        }
    });
    
    viewContent.innerHTML = html;
}

/**
 * Handle form validation errors
 */
function handleFormErrors(errors) {
    clearFormErrors();
    
    Object.keys(errors).forEach(field => {
        const fieldElement = document.querySelector(`[name="${field}"]`);
        if (fieldElement) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-sm mt-1';
            errorDiv.textContent = errors[field][0];
            fieldElement.parentNode.appendChild(errorDiv);
            fieldElement.classList.add('border-red-500');
        }
    });
}

/**
 * Clear form validation errors
 */
function clearFormErrors() {
    const form = document.getElementById('crud-form');
    if (!form) return;
    
    // Remove error messages
    form.querySelectorAll('.text-red-500').forEach(el => el.remove());
    
    // Remove error classes
    form.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
    });
}

/**
 * Update table body with new HTML
 */
function updateTableBody(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newTableBody = doc.querySelector('#table-body');
    const currentTableBody = document.getElementById('table-body');
    
    if (newTableBody && currentTableBody) {
        currentTableBody.innerHTML = newTableBody.innerHTML;
    }
}

/**
 * Setup modal click outside to close
 */
function setupModalClickOutside() {
    ['form-modal', 'view-modal', 'delete-modal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }
    });
}

/**
 * Setup modal animations
 */
function setupModalAnimations() {
    // Animation styles are handled by CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in > div {
            animation: slideIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    // This function should be customized based on your alert system
    // For now, using browser alert
    if (type === 'success') {
        console.log('Success:', message);
    } else {
        console.error('Error:', message);
    }
    
    // You can integrate with your preferred alert/toast library here
}