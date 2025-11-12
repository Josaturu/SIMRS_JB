/**
 * DateTime Now Button - Add "Now" button to all date, time, and datetime inputs
 * Automatically fills input with current date/time based on input type
 */

(function() {
    'use strict';

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Format time to HH:MM
    function formatTime(date) {
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Format datetime-local to YYYY-MM-DDTHH:MM
    function formatDateTime(date) {
        return `${formatDate(date)}T${formatTime(date)}`;
    }

    // Set current date/time to input
    function setNow(input) {
        const now = new Date();
        const type = input.type;

        switch(type) {
            case 'date':
                input.value = formatDate(now);
                break;
            case 'time':
                input.value = formatTime(now);
                break;
            case 'datetime-local':
                input.value = formatDateTime(now);
                break;
        }

        // Trigger change event for any listeners
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Create "Now" button
    function createNowButton(input) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn-now';
        button.innerHTML = '<i class="fas fa-clock"></i> Now';
        button.title = 'Set to current date/time';
        
        // Style the button
        button.style.cssText = `
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        `;

        // Hover effect
        button.addEventListener('mouseenter', function() {
            this.style.background = '#0056b3';
            this.style.transform = 'translateY(-50%) scale(1.05)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.background = '#007bff';
            this.style.transform = 'translateY(-50%) scale(1)';
        });

        // Click handler
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            setNow(input);
            
            // Visual feedback
            this.style.background = '#28a745';
            setTimeout(() => {
                this.style.background = '#007bff';
            }, 300);
        });

        return button;
    }

    // Wrap input with relative container
    function wrapInput(input) {
        // Skip if already wrapped
        if (input.parentElement.classList.contains('datetime-now-wrapper')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'datetime-now-wrapper';
        wrapper.style.cssText = `
            position: relative;
            display: inline-block;
            width: 100%;
        `;

        // If input is inside input-container, wrap the input-container
        const inputContainer = input.closest('.input-container');
        if (inputContainer) {
            // Don't wrap, just add button to input-container
            inputContainer.style.position = 'relative';
            const button = createNowButton(input);
            inputContainer.appendChild(button);
        } else {
            // Wrap the input
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            const button = createNowButton(input);
            wrapper.appendChild(button);
        }

        // Adjust input padding to make room for button
        const currentPadding = window.getComputedStyle(input).paddingRight;
        const currentPaddingValue = parseInt(currentPadding) || 10;
        input.style.paddingRight = (currentPaddingValue + 70) + 'px';
    }

    // Initialize all date/time inputs
    function initDateTimeNow() {
        // Find all date, time, and datetime-local inputs
        const inputs = document.querySelectorAll('input[type="date"], input[type="time"], input[type="datetime-local"]');
        
        inputs.forEach(function(input) {
            // Skip readonly inputs
            if (input.readOnly || input.disabled) {
                return;
            }

            wrapInput(input);
        });

        console.log(`✅ DateTime Now initialized for ${inputs.length} inputs`);
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDateTimeNow);
    } else {
        initDateTimeNow();
    }

    // Re-initialize when new content is added (for dynamic forms)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const inputs = node.querySelectorAll ? 
                        node.querySelectorAll('input[type="date"], input[type="time"], input[type="datetime-local"]') : 
                        [];
                    
                    inputs.forEach(function(input) {
                        if (!input.readOnly && !input.disabled) {
                            wrapInput(input);
                        }
                    });
                }
            });
        });
    });

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Expose to global scope if needed
    window.DateTimeNow = {
        init: initDateTimeNow,
        setNow: setNow
    };

})();
