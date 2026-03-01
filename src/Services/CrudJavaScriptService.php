<?php

namespace Brikshya\LaravelGenerator\Services;

class CrudJavaScriptService
{
    /**
     * Generate comprehensive CRUD JavaScript for single-page interface.
     */
    public static function generateCrudScript(string $resourceName, array $fields = []): string
    {
        $kebabName = strtolower($resourceName);
        $pluralKebab = $kebabName;
        
        return "
<script>
// CRUD Operations for {{ class }}
let currentEditId = null;
let searchTimeout = null;
let currentPage = 1;

// API Endpoints
const API_BASE = '/{{ kebabName }}';
const CSRF_TOKEN = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    setupCSRFToken();
});

function initializeEventListeners() {
    // Form submission
    document.getElementById('crud-form').addEventListener('submit', handleFormSubmit);
    
    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

function setupCSRFToken() {
    // Set CSRF token for all AJAX requests
    \$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    });
}

// =============================================================================
// CREATE OPERATIONS
// =============================================================================

function openCreateModal() {
    currentEditId = null;
    document.getElementById('modal-title').textContent = '{{ __('Create {{ class }}') }}';
    document.getElementById('submit-btn').textContent = '{{ __('Create') }}';
    
    // Clear form
    clearForm();
    
    // Show modal
    showModal('form-modal');
}

function clearForm() {
    const form = document.getElementById('crud-form');
    form.reset();
    
    // Clear any validation errors
    document.querySelectorAll('.text-red-500').forEach(error => {
        error.textContent = '';
    });
    
    // Reset form method
    const methodInput = form.querySelector('input[name=\"_method\"]');
    if (methodInput) {
        methodInput.remove();
    }
}

// =============================================================================
// READ OPERATIONS
// =============================================================================

function viewItem(id) {
    showLoader('view-content');
    
    fetch(`\${API_BASE}/\${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateViewModal(data.data);
                document.getElementById('edit-from-view-btn').onclick = () => editItem(id);
                showModal('view-modal');
            } else {
                errorDialog('{{ __('Error') }}', data.message || '{{ __('Error loading data') }}');
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: 'Error loading item details' } });
        });
}

function populateViewModal(data) {
    const viewContent = document.getElementById('view-content');
    let html = '';
    
    // Generate view fields dynamically
    " . self::generateViewFields($fields) . "
    
    // Add timestamps
    html += `
        <div class=\"border-t border-gray-200 dark:border-gray-700 pt-4 mt-4\">
            <div class=\"grid grid-cols-2 gap-4\">
                <div>
                    <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300\">Created At</label>
                    <p class=\"mt-1 text-sm text-gray-600 dark:text-gray-400\">\${formatDate(data.created_at)}</p>
                </div>
                <div>
                    <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300\">Updated At</label>
                    <p class=\"mt-1 text-sm text-gray-600 dark:text-gray-400\">\${formatDate(data.updated_at)}</p>
                </div>
            </div>
        </div>
    `;
    
    viewContent.innerHTML = html;
}

function refreshTable() {
    location.reload();
}

// =============================================================================
// UPDATE OPERATIONS
// =============================================================================

function editItem(id) {
    currentEditId = id;
    
    // Close view modal if open
    closeViewModal();
    
    // Set modal title and button
    document.getElementById('modal-title').textContent = '{{ __('Edit {{ class }}') }}';
    document.getElementById('submit-btn').textContent = '{{ __('Update') }}';
    
    // Load existing data
    showLoader('form-fields');
    
    fetch(`\${API_BASE}/\${id}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.data);
                showModal('form-modal');
            } else {
                errorDialog('{{ __('Error') }}', data.message || '{{ __('Error loading data') }}');
            }
        })
        .catch(error => {
            handleAjaxError({ responseJSON: { message: 'Error loading item for editing' } });
        });
}

function populateEditForm(data) {
    " . self::generateFormPopulation($fields) . "
    
    // Add method override for update
    const form = document.getElementById('crud-form');
    let methodInput = form.querySelector('input[name=\"_method\"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Restore form fields display
    document.getElementById('form-fields').innerHTML = `{{ formFields }}`;
}

function editFromView() {
    closeViewModal();
    if (currentViewId) {
        editItem(currentViewId);
    }
}

// =============================================================================
// DELETE OPERATIONS
// =============================================================================

let currentDeleteId = null;

function deleteItem(id) {
    confirmDialog(
        '{{ __('Delete {{ class }}') }}',
        '{{ __('Are you sure you want to delete this {{ singularName }}? This action cannot be undone.') }}',
        {
            confirmText: '{{ __('Delete') }}',
            cancelText: '{{ __('Cancel') }}'
        }
    ).then(result => {
        if (result.confirmed) {
            performDelete(id);
        }
    });
}

function performDelete(id) {
    // Show loading dialog
    const loadingPromise = loadingDialog('{{ __('Deleting...') }}', '{{ __('Please wait while we delete this item.') }}');
    
    fetch(`\${API_BASE}/\${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Close loading dialog
        closeDialog();
        
        if (data.success) {
            // Remove row from table
            const row = document.getElementById(`row-\${id}`);
            if (row) {
                row.remove();
            }
            
            successDialog('{{ __('Success') }}', data.message || '{{ __('Item deleted successfully') }}');
            
            // Check if table is empty
            checkEmptyTable();
        } else {
            errorDialog('{{ __('Error') }}', data.message || '{{ __('Error deleting item') }}');
        }
    })
    .catch(error => {
        // Close loading dialog
        closeDialog();
        handleAjaxError({ responseJSON: { message: 'Error deleting item' } });
    });
}

function checkEmptyTable() {
    const tableBody = document.getElementById('table-body');
    if (tableBody.children.length === 0 || (tableBody.children.length === 1 && tableBody.children[0].id === 'empty-row')) {
        // Show empty state
        tableBody.innerHTML = `
            <tr id=\"empty-row\">
                <td colspan=\"100%\" class=\"px-6 py-12 text-center text-gray-500 dark:text-gray-400\">
                    <div class=\"text-center\">
                        <svg class=\"mx-auto h-12 w-12 text-gray-400\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2\" />
                        </svg>
                        <h3 class=\"mt-2 text-sm font-medium text-gray-900 dark:text-gray-100\">{{ __('No {{ pluralName }}') }}</h3>
                        <p class=\"mt-1 text-sm text-gray-500 dark:text-gray-400\">{{ __('Get started by creating a new {{ singularName }}.') }}</p>
                        <div class=\"mt-6\">
                            <button onclick=\"openCreateModal()\" class=\"inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150\">
                                {{ __('Create First {{ class }}') }}
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }
}

// =============================================================================
// FORM HANDLING
// =============================================================================

function handleFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.textContent = '{{ __('Saving...') }}';
    submitBtn.disabled = true;
    
    // Clear previous errors
    clearFormErrors();
    
    const url = currentEditId ? `\${API_BASE}/\${currentEditId}` : API_BASE;
    const method = currentEditId ? 'POST' : 'POST'; // We use POST with _method for PUT
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            successDialog(
                '{{ __('Success') }}', 
                data.message || (currentEditId ? '{{ __('Updated successfully') }}' : '{{ __('Created successfully') }}')
            );
            
            // Refresh table or add new row
            if (currentEditId) {
                updateTableRow(currentEditId, data.data);
            } else {
                addNewTableRow(data.data);
            }
        } else {
            if (data.errors) {
                displayFormErrors(data.errors);
            } else {
                errorDialog('{{ __('Validation Error') }}', data.message || '{{ __('Please check your input and try again.') }}');
            }
        }
    })
    .catch(error => {
        if (error.responseJSON && error.responseJSON.errors) {
            displayFormErrors(error.responseJSON.errors);
        } else {
            handleAjaxError({ responseJSON: { message: 'Error saving data' } });
        }
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function clearFormErrors() {
    document.querySelectorAll('.text-red-500').forEach(error => {
        error.textContent = '';
    });
    
    document.querySelectorAll('.border-red-500').forEach(field => {
        field.classList.remove('border-red-500');
    });
}

function displayFormErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.querySelector(`[data-error=\"\${field}\"]`);
        const inputElement = document.querySelector(`[name=\"\${field}\"]`);
        
        if (errorElement) {
            errorElement.textContent = errors[field][0];
        }
        
        if (inputElement) {
            inputElement.classList.add('border-red-500');
        }
    });
}

// =============================================================================
// SEARCH AND FILTERING
// =============================================================================

function debounceSearch(searchTerm) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch(searchTerm);
    }, 500);
}

function performSearch(searchTerm) {
    const url = new URL(window.location.href);
    
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    
    // Update URL without page reload
    window.history.pushState({}, '', url);
    
    // Perform AJAX search
    fetch(url.href, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.html) {
            document.getElementById('table-body').innerHTML = data.html;
        }
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    const url = new URL(window.location.href);
    url.search = '';
    window.location.href = url.href;
}

// =============================================================================
// MODAL MANAGEMENT
// =============================================================================

function showModal(modalName) {
    const modal = document.querySelector(`[x-data][x-show]`);
    if (modal && modal.__x) {
        modal.__x.\$data.show = true;
    } else {
        // Fallback for manual modal handling
        const modalElement = document.querySelector(`[name=\"\${modalName}\"]`);
        if (modalElement) {
            modalElement.style.display = 'block';
        }
    }
}

function closeModal() {
    const modal = document.querySelector('[name=\"form-modal\"]');
    if (modal && modal.__x) {
        modal.__x.\$data.show = false;
    } else {
        // Fallback
        const modalElement = document.querySelector('[name=\"form-modal\"]');
        if (modalElement) {
            modalElement.style.display = 'none';
        }
    }
    currentEditId = null;
}

function closeViewModal() {
    const modal = document.querySelector('[name=\"view-modal\"]');
    if (modal && modal.__x) {
        modal.__x.\$data.show = false;
    }
    currentViewId = null;
}

function closeDeleteModal() {
    const modal = document.querySelector('[name=\"delete-modal\"]');
    if (modal && modal.__x) {
        modal.__x.\$data.show = false;
    }
    currentDeleteId = null;
}

function closeAllModals() {
    closeModal();
    closeViewModal();
    closeDeleteModal();
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

function showLoader(containerId) {
    document.getElementById(containerId).innerHTML = `
        <div class=\"flex justify-center items-center py-8\">
            <div class=\"animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600\"></div>
        </div>
    `;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function updateTableRow(id, data) {
    // This would update the specific row with new data
    // Implementation depends on your table structure
    location.reload(); // Temporary solution
}

function addNewTableRow(data) {
    // This would add a new row to the table
    // Implementation depends on your table structure
    location.reload(); // Temporary solution
}

// Set current view ID for edit from view functionality
let currentViewId = null;

// Override view function to set current ID
const originalViewItem = viewItem;
viewItem = function(id) {
    currentViewId = id;
    originalViewItem(id);
};
</script>";
    }

    /**
     * Generate view fields for modal population.
     */
    protected static function generateViewFields(array $fields): string
    {
        $viewFields = [];
        
        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $fieldName = $field['name'];
                $label = ucwords(str_replace('_', ' ', $fieldName));
                
                if ($field['type'] === 'enum') {
                    $viewFields[] = "
    html += `
        <div>
            <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300\">{$label}</label>
            <p class=\"mt-1 text-sm text-gray-600 dark:text-gray-400\">\${data.{$fieldName}_label || 'N/A'}</p>
        </div>
    `;";
                } else {
                    $viewFields[] = "
    html += `
        <div>
            <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300\">{$label}</label>
            <p class=\"mt-1 text-sm text-gray-600 dark:text-gray-400\">\${data.{$fieldName} || 'N/A'}</p>
        </div>
    `;";
                }
            }
        }

        return implode("\n    ", $viewFields);
    }

    /**
     * Generate form population JavaScript.
     */
    protected static function generateFormPopulation(array $fields): string
    {
        $population = [];
        
        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $fieldName = $field['name'];
                
                $population[] = "
    const {$fieldName}Field = document.querySelector('[name=\"{$fieldName}\"]');
    if ({$fieldName}Field) {
        " . ($field['type'] === 'boolean' 
            ? "{$fieldName}Field.checked = data.{$fieldName};" 
            : "{$fieldName}Field.value = data.{$fieldName} || '';") . "
    }";
            }
        }

        return implode("\n    ", $population);
    }
}