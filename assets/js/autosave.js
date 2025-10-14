/**
 * AutoSave LocalStorage Library
 * Automatically saves form data to localStorage and restores on page load
 * 
 * Usage:
 *   AutoSave.init('formId', {
 *     debounce: 500,
 *     exclude: ['password', 'token'],
 *     onSave: function(data) { console.log('Saved:', data); },
 *     onRestore: function(data) { console.log('Restored:', data); }
 *   });
 */

const AutoSave = (function() {
    'use strict';
    
    // Default configuration
    const defaults = {
        debounce: 500,           // Debounce delay in ms
        exclude: [],             // Array of field names to exclude
        storagePrefix: 'form_',  // LocalStorage key prefix
        onSave: null,            // Callback after save
        onRestore: null,         // Callback after restore
        clearOnSubmit: true,     // Clear localStorage on successful submit
        showNotification: true   // Show save notification
    };
    
    let config = {};
    let formElement = null;
    let storageKey = '';
    let debounceTimer = null;
    let saveNotification = null;
    
    /**
     * Initialize AutoSave for a form
     * @param {string} formId - Form element ID
     * @param {object} options - Configuration options
     */
    function init(formId, options = {}) {
        // Merge config
        config = Object.assign({}, defaults, options);
        
        // Get form element
        formElement = document.getElementById(formId);
        if (!formElement) {
            console.error('AutoSave: Form not found:', formId);
            return;
        }
        
        // Set storage key
        storageKey = config.storagePrefix + formId;
        
        // Create save notification element
        if (config.showNotification) {
            createNotification();
        }
        
        // Restore saved data
        restoreData();
        
        // Attach event listeners
        attachListeners();
        
        // Clear on submit
        if (config.clearOnSubmit) {
            formElement.addEventListener('submit', function() {
                clearStorage();
            });
        }
        
        console.log('AutoSave initialized for:', formId);
    }
    
    /**
     * Create save notification element
     */
    function createNotification() {
        saveNotification = document.createElement('div');
        saveNotification.id = 'autosave-notification';
        saveNotification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        `;
        saveNotification.innerHTML = '<i class="fas fa-check-circle"></i> Data tersimpan otomatis';
        document.body.appendChild(saveNotification);
        
        // Add animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateY(50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Show save notification
     */
    function showNotification() {
        if (!saveNotification) return;
        
        saveNotification.style.display = 'block';
        setTimeout(() => {
            saveNotification.style.display = 'none';
        }, 2000);
    }
    
    /**
     * Attach event listeners to form elements
     */
    function attachListeners() {
        // Get all form inputs
        const inputs = formElement.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Skip excluded fields
            if (shouldExclude(input)) return;
            
            // Skip readonly/disabled fields
            if (input.readOnly || input.disabled) return;
            
            // Skip hidden fields (except hidden inputs with values)
            if (input.type === 'hidden') return;
            
            // Attach appropriate event
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.addEventListener('change', handleInput);
            } else {
                input.addEventListener('input', handleInput);
            }
        });
    }
    
    /**
     * Check if field should be excluded
     */
    function shouldExclude(input) {
        return config.exclude.includes(input.name) || 
               config.exclude.includes(input.id);
    }
    
    /**
     * Handle input change (debounced)
     */
    function handleInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            saveData();
        }, config.debounce);
    }
    
    /**
     * Save form data to localStorage
     */
    function saveData() {
        const formData = {};
        const inputs = formElement.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (shouldExclude(input) || input.readOnly || input.disabled || input.type === 'hidden') {
                return;
            }
            
            const name = input.name || input.id;
            if (!name) return;
            
            if (input.type === 'checkbox') {
                // Handle checkbox arrays
                if (input.name.endsWith('[]')) {
                    if (!formData[name]) formData[name] = [];
                    if (input.checked) {
                        formData[name].push(input.value);
                    }
                } else {
                    formData[name] = input.checked;
                }
            } else if (input.type === 'radio') {
                if (input.checked) {
                    formData[name] = input.value;
                }
            } else {
                formData[name] = input.value;
            }
        });
        
        // Save to localStorage
        try {
            localStorage.setItem(storageKey, JSON.stringify(formData));
            if (config.showNotification) {
                showNotification();
            }
            if (config.onSave) {
                config.onSave(formData);
            }
        } catch (e) {
            console.error('AutoSave: Failed to save data', e);
        }
    }
    
    /**
     * Restore form data from localStorage
     */
    function restoreData() {
        try {
            const savedData = localStorage.getItem(storageKey);
            if (!savedData) return;
            
            const formData = JSON.parse(savedData);
            const inputs = formElement.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                const name = input.name || input.id;
                if (!name || !formData.hasOwnProperty(name)) return;
                
                if (input.type === 'checkbox') {
                    if (input.name.endsWith('[]')) {
                        // Checkbox array
                        if (Array.isArray(formData[name]) && formData[name].includes(input.value)) {
                            input.checked = true;
                        }
                    } else {
                        input.checked = formData[name];
                    }
                } else if (input.type === 'radio') {
                    if (input.value === formData[name]) {
                        input.checked = true;
                    }
                } else {
                    input.value = formData[name];
                }
                
                // Trigger change event to update UI (e.g., floating labels)
                input.dispatchEvent(new Event('change'));
            });
            
            if (config.onRestore) {
                config.onRestore(formData);
            }
            
            console.log('AutoSave: Data restored from localStorage');
        } catch (e) {
            console.error('AutoSave: Failed to restore data', e);
        }
    }
    
    /**
     * Clear saved data from localStorage
     */
    function clearStorage() {
        try {
            localStorage.removeItem(storageKey);
            console.log('AutoSave: Data cleared from localStorage');
        } catch (e) {
            console.error('AutoSave: Failed to clear data', e);
        }
    }
    
    /**
     * Manually trigger save
     */
    function save() {
        saveData();
    }
    
    /**
     * Manually clear storage
     */
    function clear() {
        clearStorage();
    }
    
    // Public API
    return {
        init: init,
        save: save,
        clear: clear
    };
})();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoSave;
}
