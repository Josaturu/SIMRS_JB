/**
 * Mark Required Fields
 * Automatically add asterisk (*) to labels of required fields
 */

(function() {
    'use strict';

    function markRequiredFields() {
        // Find all required inputs
        const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        
        requiredInputs.forEach(function(input) {
            // Skip hidden inputs
            if (input.type === 'hidden') {
                return;
            }

            // Find associated label
            let label = null;
            
            // Method 1: label with for attribute
            if (input.id) {
                label = document.querySelector(`label[for="${input.id}"]`);
            }
            
            // Method 2: label-floating inside same container
            if (!label) {
                const container = input.closest('.input-container');
                if (container) {
                    label = container.querySelector('.label-floating');
                }
            }
            
            // Method 3: parent label (for radio/checkbox)
            if (!label) {
                const parentLabel = input.closest('label');
                if (parentLabel) {
                    // For radio/checkbox, mark the group label instead
                    const formRow = input.closest('.form-row, .form-item');
                    if (formRow) {
                        const groupLabel = formRow.querySelector('label:not(.label-floating)');
                        if (groupLabel && !groupLabel.contains(input)) {
                            label = groupLabel;
                        }
                    }
                }
            }
            
            // Add required-field class to label
            if (label && !label.classList.contains('required-field')) {
                label.classList.add('required-field');
            }
            
            // Fix label-floating position if input has value
            if (label && label.classList.contains('label-floating')) {
                // Check if input has value
                const hasValue = input.value && input.value.trim() !== '';
                
                if (hasValue) {
                    // Force label to move up (outside the input border)
                    label.style.setProperty('top', '-22px', 'important');
                    label.style.setProperty('font-size', '12px', 'important');
                    label.style.setProperty('background', 'transparent', 'important');
                    label.style.setProperty('padding', '0', 'important');
                    label.style.setProperty('left', '0', 'important');
                    
                    // Also add a class for CSS targeting
                    label.classList.add('has-value');
                }
            }
        });
        
        console.log(`✅ Marked ${requiredInputs.length} required fields`);
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markRequiredFields);
    } else {
        markRequiredFields();
    }

    // Re-mark when new content is added
    const observer = new MutationObserver(function(mutations) {
        let shouldRemark = false;
        
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    const hasRequired = node.querySelectorAll ? 
                        node.querySelectorAll('input[required], select[required], textarea[required]').length > 0 : 
                        false;
                    
                    if (hasRequired) {
                        shouldRemark = true;
                    }
                }
            });
        });
        
        if (shouldRemark) {
            markRequiredFields();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Expose to global scope
    window.MarkRequiredFields = {
        mark: markRequiredFields
    };

})();
