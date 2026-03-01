<?php

namespace Brikshya\LaravelGenerator\Services;

class AlertService
{
    /**
     * Generate alert component for different UI frameworks.
     */
    public static function generateAlert(string $framework = 'breeze', array $types = ['success', 'error', 'warning', 'info']): string
    {
        return match($framework) {
            'breeze' => self::generateBreezeAlerts($types),
            'jetstream' => self::generateJetstreamAlerts($types),
            'bootstrap' => self::generateBootstrapAlerts($types),
            default => self::generateTailwindAlerts($types),
        };
    }

    /**
     * Generate Breeze-compatible alerts.
     */
    protected static function generateBreezeAlerts(array $types): string
    {
        $alerts = [];

        foreach ($types as $type) {
            $alerts[] = self::getBreezeAlertTemplate($type);
        }

        return "<!-- Alerts -->\n<div id=\"alert-container\" class=\"space-y-4 mb-6\">\n    " . implode("\n\n    ", $alerts) . "\n</div>";
    }

    /**
     * Get Breeze alert template.
     */
    protected static function getBreezeAlertTemplate(string $type): string
    {
        $colors = [
            'success' => ['bg' => 'bg-green-50 dark:bg-green-900/20', 'border' => 'border-green-200 dark:border-green-800', 'text' => 'text-green-800 dark:text-green-200', 'icon' => '✓'],
            'error' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'border' => 'border-red-200 dark:border-red-800', 'text' => 'text-red-800 dark:text-red-200', 'icon' => '✕'],
            'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'border' => 'border-yellow-200 dark:border-yellow-800', 'text' => 'text-yellow-800 dark:text-yellow-200', 'icon' => '⚠'],
            'info' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'border' => 'border-blue-200 dark:border-blue-800', 'text' => 'text-blue-800 dark:text-blue-200', 'icon' => 'ℹ'],
        ];

        $color = $colors[$type];

        return "@if(session('{$type}'))
        <div class=\"{$color['bg']} {$color['border']} {$color['text']} border rounded-lg p-4 flex items-center justify-between\" role=\"alert\">
            <div class=\"flex items-center\">
                <span class=\"text-lg mr-3\">{$color['icon']}</span>
                <span class=\"font-medium\">{{ session('{$type}') }}</span>
            </div>
            <button type=\"button\" class=\"{$color['text']} hover:opacity-70\" onclick=\"this.parentElement.remove()\">
                <svg class=\"w-5 h-5\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                    <path fill-rule=\"evenodd\" d=\"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z\" clip-rule=\"evenodd\"></path>
                </svg>
            </button>
        </div>
    @endif";
    }

    /**
     * Generate Tailwind-only alerts.
     */
    protected static function generateTailwindAlerts(array $types): string
    {
        $alerts = [];

        foreach ($types as $type) {
            $alerts[] = self::getTailwindAlertTemplate($type);
        }

        return "<!-- Alerts -->\n<div id=\"alert-container\" class=\"space-y-4 mb-6\">\n    " . implode("\n\n    ", $alerts) . "\n</div>";
    }

    /**
     * Get Tailwind alert template.
     */
    protected static function getTailwindAlertTemplate(string $type): string
    {
        $colors = [
            'success' => ['bg' => 'bg-green-100', 'border' => 'border-green-400', 'text' => 'text-green-700'],
            'error' => ['bg' => 'bg-red-100', 'border' => 'border-red-400', 'text' => 'text-red-700'],
            'warning' => ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-400', 'text' => 'text-yellow-700'],
            'info' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-400', 'text' => 'text-blue-700'],
        ];

        $color = $colors[$type];

        return "@if(session('{$type}'))
        <div class=\"{$color['bg']} {$color['border']} {$color['text']} border px-4 py-3 rounded relative\" role=\"alert\">
            <span class=\"block sm:inline\">{{ session('{$type}') }}</span>
            <span class=\"absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer\" onclick=\"this.parentElement.remove()\">
                <svg class=\"fill-current h-6 w-6 {$color['text']}\" role=\"button\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 20 20\">
                    <title>Close</title>
                    <path d=\"M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z\"/>
                </svg>
            </span>
        </div>
    @endif";
    }

    /**
     * Generate Bootstrap alerts.
     */
    protected static function generateBootstrapAlerts(array $types): string
    {
        $alerts = [];

        foreach ($types as $type) {
            $bootType = match($type) {
                'error' => 'danger',
                'warning' => 'warning',
                'info' => 'info',
                default => 'success',
            };

            $alerts[] = "@if(session('{$type}'))
        <div class=\"alert alert-{$bootType} alert-dismissible fade show\" role=\"alert\">
            {{ session('{$type}') }}
            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
        </div>
    @endif";
        }

        return "<!-- Alerts -->\n<div id=\"alert-container\" class=\"mb-4\">\n    " . implode("\n\n    ", $alerts) . "\n</div>";
    }

    /**
     * Generate Jetstream alerts.
     */
    protected static function generateJetstreamAlerts(array $types): string
    {
        // Same as Breeze for now
        return self::generateBreezeAlerts($types);
    }

    /**
     * Generate modal dialog HTML container.
     */
    public static function generateDialogContainer(): string
    {
        return '<!-- Modal Dialog Container -->
<div id="dialog-overlay" class="dialog-overlay hidden">
    <div id="dialog-container" class="dialog-container">
        <div class="dialog-content">
            <div class="dialog-icon-container">
                <div id="dialog-icon" class="dialog-icon"></div>
            </div>
            <div class="dialog-text-container">
                <h3 id="dialog-title" class="dialog-title"></h3>
                <p id="dialog-message" class="dialog-message"></p>
                <div id="dialog-input-container" class="dialog-input-container hidden">
                    <input type="text" id="dialog-input" class="dialog-input" />
                    <span id="dialog-input-error" class="dialog-input-error"></span>
                </div>
            </div>
            <div id="dialog-buttons" class="dialog-buttons">
                <!-- Buttons will be inserted here -->
            </div>
        </div>
    </div>
</div>';
    }

    /**
     * Generate CSS for modal dialogs.
     */
    public static function generateDialogCSS(): string
    {
        return '<style>
/* Modal Dialog Styles */
.dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.dialog-overlay.show {
    opacity: 1;
}

.dialog-container {
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.8) translateY(20px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dialog-overlay.show .dialog-container {
    transform: scale(1) translateY(0);
}

.dialog-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    text-align: center;
}

.dark .dialog-content {
    background: rgb(31 41 55);
    border: 1px solid rgb(75 85 99);
}

.dialog-icon-container {
    margin-bottom: 24px;
}

.dialog-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
}

.dialog-icon.success {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
    animation: successPulse 0.6s ease-out;
}

.dialog-icon.error {
    background: linear-gradient(135deg, #EF4444, #DC2626);
    color: white;
    animation: errorShake 0.6s ease-out;
}

.dialog-icon.warning {
    background: linear-gradient(135deg, #F59E0B, #D97706);
    color: white;
    animation: warningBounce 0.6s ease-out;
}

.dialog-icon.info {
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
    animation: infoPulse 0.6s ease-out;
}

.dialog-icon.question {
    background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    color: white;
    animation: questionWiggle 0.6s ease-out;
}

.dialog-title {
    font-size: 24px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: rgb(17 24 39);
}

.dark .dialog-title {
    color: rgb(243 244 246);
}

.dialog-message {
    font-size: 16px;
    color: rgb(107 114 128);
    margin: 0 0 24px 0;
    line-height: 1.5;
}

.dark .dialog-message {
    color: rgb(156 163 175);
}

.dialog-input-container {
    margin: 24px 0;
}

.dialog-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgb(229 231 235);
    border-radius: 8px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.2s;
}

.dialog-input:focus {
    border-color: rgb(59 130 246);
}

.dark .dialog-input {
    background: rgb(55 65 81);
    border-color: rgb(75 85 99);
    color: white;
}

.dark .dialog-input:focus {
    border-color: rgb(59 130 246);
}

.dialog-input-error {
    display: block;
    color: rgb(239 68 68);
    font-size: 14px;
    margin-top: 8px;
    text-align: left;
}

.dialog-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.dialog-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 100px;
}

.dialog-btn-primary {
    background: linear-gradient(135deg, #3B82F6, #2563EB);
    color: white;
}

.dialog-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.dialog-btn-success {
    background: linear-gradient(135deg, #10B981, #059669);
    color: white;
}

.dialog-btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

.dialog-btn-danger {
    background: linear-gradient(135deg, #EF4444, #DC2626);
    color: white;
}

.dialog-btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
}

.dialog-btn-secondary {
    background: rgb(243 244 246);
    color: rgb(55 65 81);
    border: 1px solid rgb(209 213 219);
}

.dialog-btn-secondary:hover {
    background: rgb(229 231 235);
    transform: translateY(-1px);
}

.dark .dialog-btn-secondary {
    background: rgb(75 85 99);
    color: rgb(243 244 246);
    border-color: rgb(107 114 128);
}

.dark .dialog-btn-secondary:hover {
    background: rgb(107 114 128);
}

/* Loading Spinner */
.dialog-loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-radius: 50%;
    border-top: 2px solid #3498db;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

/* Animations */
@keyframes successPulse {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes errorShake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

@keyframes warningBounce {
    0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
    40%, 43% { transform: translateY(-16px); }
    70% { transform: translateY(-8px); }
    90% { transform: translateY(-2px); }
}

@keyframes infoPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes questionWiggle {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(5deg); }
    75% { transform: rotate(-5deg); }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 640px) {
    .dialog-content {
        padding: 24px 20px;
    }
    
    .dialog-icon {
        width: 48px;
        height: 48px;
        font-size: 24px;
    }
    
    .dialog-title {
        font-size: 20px;
    }
    
    .dialog-buttons {
        flex-direction: column;
    }
    
    .dialog-btn {
        width: 100%;
    }
}
</style>';
    }

    /**
     * Generate JavaScript for enhanced dialogs and alerts.
     */
    public static function generateAlertJavaScript(): string
    {
        $dialogContainer = str_replace(["\n", '"', "'"], ['\\n', '\\"', "\\'"], self::generateDialogContainer());
        
        return '<script>
// Enhanced Alert and Dialog System
let currentDialog = null;
let dialogQueue = [];
let isDialogOpen = false;

// Initialize dialog system
document.addEventListener("DOMContentLoaded", function() {
    initializeDialogSystem();
});

function initializeDialogSystem() {
    // Create dialog container if it doesn\'t exist
    if (!document.getElementById("dialog-overlay")) {
        const dialogHTML = `' . $dialogContainer . '`;
        document.body.insertAdjacentHTML("beforeend", dialogHTML);
    }
    
    // Add keyboard event listeners
    document.addEventListener("keydown", handleDialogKeyboard);
    
    // Add click outside to close
    document.getElementById("dialog-overlay").addEventListener("click", function(e) {
        if (e.target === this && currentDialog && currentDialog.allowOutsideClick !== false) {
            closeDialog();
        }
    });
}

function handleDialogKeyboard(e) {
    if (!isDialogOpen) return;
    
    if (e.key === "Escape" && currentDialog && currentDialog.allowEscapeKey !== false) {
        closeDialog();
    }
    
    if (e.key === "Enter") {
        const primaryButton = document.querySelector(".dialog-btn-primary, .dialog-btn-success");
        if (primaryButton && !primaryButton.disabled) {
            primaryButton.click();
        }
    }
}

// Main dialog function
function showDialog(options = {}) {
    const defaults = {
        type: "info",
        title: "Dialog",
        message: "",
        confirmButtonText: "OK",
        showCancelButton: false,
        cancelButtonText: "Cancel",
        showInput: false,
        inputPlaceholder: "",
        inputValidator: null,
        allowOutsideClick: true,
        allowEscapeKey: true,
        onConfirm: () => {},
        onCancel: () => {},
        onClose: () => {}
    };
    
    const config = { ...defaults, ...options };
    
    if (isDialogOpen) {
        dialogQueue.push(config);
        return Promise.resolve({ dismissed: true });
    }
    
    return new Promise((resolve) => {
        currentDialog = config;
        isDialogOpen = true;
        
        config.resolve = resolve;
        renderDialog(config);
        showDialogOverlay();
    });
}

function renderDialog(config) {
    // Set icon
    const iconElement = document.getElementById("dialog-icon");
    const iconIcons = {
        success: "✓",
        error: "✕",
        warning: "⚠",
        info: "ℹ",
        question: "?"
    };
    
    iconElement.textContent = iconIcons[config.type] || iconIcons.info;
    iconElement.className = "dialog-icon " + config.type;
    
    // Set title and message
    document.getElementById("dialog-title").textContent = config.title;
    document.getElementById("dialog-message").textContent = config.message;
    
    // Handle input
    const inputContainer = document.getElementById("dialog-input-container");
    const input = document.getElementById("dialog-input");
    
    if (config.showInput) {
        input.placeholder = config.inputPlaceholder;
        input.value = "";
        inputContainer.classList.remove("hidden");
        setTimeout(() => input.focus(), 300);
    } else {
        inputContainer.classList.add("hidden");
    }
    
    // Render buttons
    renderDialogButtons(config);
}

function renderDialogButtons(config) {
    const buttonsContainer = document.getElementById("dialog-buttons");
    buttonsContainer.innerHTML = "";
    
    if (config.showCancelButton) {
        const cancelButton = document.createElement("button");
        cancelButton.className = "dialog-btn dialog-btn-secondary";
        cancelButton.textContent = config.cancelButtonText;
        cancelButton.onclick = () => handleCancel(config);
        buttonsContainer.appendChild(cancelButton);
    }
    
    const confirmButton = document.createElement("button");
    const buttonClass = config.type === "error" ? "dialog-btn-danger" : 
                      config.type === "success" ? "dialog-btn-success" : "dialog-btn-primary";
    confirmButton.className = "dialog-btn " + buttonClass;
    confirmButton.textContent = config.confirmButtonText;
    confirmButton.onclick = () => handleConfirm(config);
    buttonsContainer.appendChild(confirmButton);
}

function handleConfirm(config) {
    if (config.showInput) {
        const input = document.getElementById("dialog-input");
        const value = input.value.trim();
        const errorElement = document.getElementById("dialog-input-error");
        
        if (config.inputValidator) {
            const validationResult = config.inputValidator(value);
            if (validationResult !== true) {
                errorElement.textContent = validationResult;
                input.focus();
                return;
            }
        }
        
        errorElement.textContent = "";
        config.resolve({ confirmed: true, value: value });
    } else {
        config.resolve({ confirmed: true });
    }
    
    config.onConfirm();
    closeDialog();
}

function handleCancel(config) {
    config.resolve({ confirmed: false, dismissed: true });
    config.onCancel();
    closeDialog();
}

function showDialogOverlay() {
    const overlay = document.getElementById("dialog-overlay");
    overlay.classList.remove("hidden");
    
    // Force reflow
    overlay.offsetHeight;
    
    overlay.classList.add("show");
}

function closeDialog() {
    const overlay = document.getElementById("dialog-overlay");
    overlay.classList.remove("show");
    
    setTimeout(() => {
        overlay.classList.add("hidden");
        isDialogOpen = false;
        
        if (currentDialog) {
            currentDialog.onClose();
            currentDialog = null;
        }
        
        // Process queue
        if (dialogQueue.length > 0) {
            const nextDialog = dialogQueue.shift();
            showDialog(nextDialog);
        }
    }, 300);
}

// Convenience functions
function successDialog(title, message = "") {
    return showDialog({
        type: "success",
        title: title,
        message: message,
        confirmButtonText: "Great!"
    });
}

function errorDialog(title, message = "") {
    return showDialog({
        type: "error",
        title: title,
        message: message,
        confirmButtonText: "OK"
    });
}

function warningDialog(title, message = "") {
    return showDialog({
        type: "warning",
        title: title,
        message: message,
        confirmButtonText: "OK"
    });
}

function infoDialog(title, message = "") {
    return showDialog({
        type: "info",
        title: title,
        message: message,
        confirmButtonText: "OK"
    });
}

function confirmDialog(title, message = "", options = {}) {
    return showDialog({
        type: "question",
        title: title,
        message: message,
        showCancelButton: true,
        confirmButtonText: options.confirmText || "Yes",
        cancelButtonText: options.cancelText || "No",
        ...options
    });
}

function promptDialog(title, message = "", placeholder = "", validator = null) {
    return showDialog({
        type: "question",
        title: title,
        message: message,
        showInput: true,
        inputPlaceholder: placeholder,
        inputValidator: validator,
        showCancelButton: true,
        confirmButtonText: "Submit",
        cancelButtonText: "Cancel"
    });
}

function loadingDialog(title, message = "") {
    return showDialog({
        type: "info",
        title: title,
        message: message,
        confirmButtonText: `<span class="dialog-loading"></span>Loading...`,
        allowOutsideClick: false,
        allowEscapeKey: false
    });
}

// Toast notifications (existing functionality)
function showAlert(message, type = "success", duration = 5000) {
    const container = document.getElementById("alert-container");
    if (!container) return;
    
    const alertId = "alert-" + Date.now();
    const alertHTML = createAlertHTML(message, type, alertId);
    
    container.insertAdjacentHTML("afterbegin", alertHTML);
    
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, duration);
    }
}

function createAlertHTML(message, type, id) {
    const colors = {
        success: { bg: "bg-green-50 dark:bg-green-900/20", border: "border-green-200 dark:border-green-800", text: "text-green-800 dark:text-green-200", icon: "✓" },
        error: { bg: "bg-red-50 dark:bg-red-900/20", border: "border-red-200 dark:border-red-800", text: "text-red-800 dark:text-red-200", icon: "✕" },
        warning: { bg: "bg-yellow-50 dark:bg-yellow-900/20", border: "border-yellow-200 dark:border-yellow-800", text: "text-yellow-800 dark:text-yellow-200", icon: "⚠" },
        info: { bg: "bg-blue-50 dark:bg-blue-900/20", border: "border-blue-200 dark:border-blue-800", text: "text-blue-800 dark:text-blue-200", icon: "ℹ" }
    };
    
    const color = colors[type] || colors.success;
    
    return `
        <div id="${id}" class="${color.bg} ${color.border} ${color.text} border rounded-lg p-4 flex items-center justify-between animate-in slide-in-from-top-2 duration-300" role="alert">
            <div class="flex items-center">
                <span class="text-lg mr-3">${color.icon}</span>
                <span class="font-medium">${message}</span>
            </div>
            <button type="button" class="${color.text} hover:opacity-70" onclick="document.getElementById(\'${id}\').remove()">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
}

// AJAX Helper Functions
function handleAjaxResponse(response, successMessage) {
    if (response.success) {
        showAlert(successMessage || response.message, "success");
        if (response.reload) {
            setTimeout(() => location.reload(), 1500);
        }
    } else {
        showAlert(response.message || "An error occurred", "error");
    }
}

function handleAjaxError(xhr) {
    let message = "An error occurred";
    
    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    } else if (xhr && xhr.responseJSON && xhr.responseJSON.errors) {
        const errors = Object.values(xhr.responseJSON.errors).flat();
        message = errors.join(", ");
    }
    
    errorDialog("Error", message);
}
</script>';
    }
}