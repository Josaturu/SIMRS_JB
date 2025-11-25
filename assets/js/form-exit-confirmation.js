/**
 * Form Exit Confirmation
 * Menampilkan konfirmasi sebelum keluar dari form jika ada perubahan yang belum disimpan
 */

(function() {
    'use strict';

    let formChanged = false;
    let isSubmitting = false;
    let trackedForms = new Set();

    // Track form changes
    function trackFormChanges(form) {
        if (trackedForms.has(form)) {
            return; // Already tracked
        }
        trackedForms.add(form);

        // Get initial form data
        const initialData = new FormData(form);
        const initialValues = {};
        for (let [key, value] of initialData.entries()) {
            initialValues[key] = value;
        }

        // Track all input changes
        form.addEventListener('input', function(e) {
            if (!isSubmitting) {
                formChanged = true;
                console.log('Form changed detected');
            }
        });

        form.addEventListener('change', function(e) {
            if (!isSubmitting) {
                formChanged = true;
                console.log('Form changed detected');
            }
        });

        // Reset flag on submit
        form.addEventListener('submit', function(e) {
            isSubmitting = true;
            formChanged = false;
            console.log('Form submitting - disabling exit confirmation');
        });
    }

    // Create modal HTML
    function createModal() {
        const modal = document.createElement('div');
        modal.id = 'exitConfirmationModal';
        modal.className = 'exit-modal';
        modal.innerHTML = `
            <div class="exit-modal-overlay"></div>
            <div class="exit-modal-content">
                <div class="exit-modal-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Konfirmasi Keluar</h3>
                </div>
                <div class="exit-modal-body">
                    <p>Anda memiliki perubahan yang belum disimpan.</p>
                    <p><strong>Apakah Anda ingin menyimpan perubahan sebelum keluar?</strong></p>
                </div>
                <div class="exit-modal-footer">
                    <button type="button" class="btn-modal btn-save" id="btnSaveAndExit">
                        <i class="fas fa-save"></i> Simpan & Keluar
                    </button>
                    <button type="button" class="btn-modal btn-discard" id="btnDiscardAndExit">
                        <i class="fas fa-trash"></i> Buang & Keluar
                    </button>
                    <button type="button" class="btn-modal btn-cancel" id="btnCancelExit">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .exit-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                animation: fadeIn 0.3s ease;
            }

            .exit-modal.show {
                display: block;
            }

            .exit-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(3px);
            }

            .exit-modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 90%;
                animation: slideDown 0.3s ease;
            }

            .exit-modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .exit-modal-header i {
                font-size: 24px;
                color: #ff9800;
            }

            .exit-modal-header h3 {
                margin: 0;
                font-size: 20px;
                color: #333;
            }

            .exit-modal-body {
                padding: 24px;
            }

            .exit-modal-body p {
                margin: 0 0 12px 0;
                color: #555;
                line-height: 1.6;
            }

            .exit-modal-body p:last-child {
                margin-bottom: 0;
            }

            .exit-modal-footer {
                padding: 16px 24px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            .btn-modal {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .btn-modal:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .btn-modal:active {
                transform: translateY(0);
            }

            .btn-save {
                background: #28a745;
                color: white;
            }

            .btn-save:hover {
                background: #218838;
            }

            .btn-discard {
                background: #dc3545;
                color: white;
            }

            .btn-discard:hover {
                background: #c82333;
            }

            .btn-cancel {
                background: #6c757d;
                color: white;
            }

            .btn-cancel:hover {
                background: #5a6268;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            @keyframes slideDown {
                from {
                    transform: translate(-50%, -60%);
                    opacity: 0;
                }
                to {
                    transform: translate(-50%, -50%);
                    opacity: 1;
                }
            }

            @media (max-width: 600px) {
                .exit-modal-content {
                    width: 95%;
                }

                .exit-modal-footer {
                    flex-direction: column;
                }

                .btn-modal {
                    width: 100%;
                    justify-content: center;
                }
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(modal);

        return modal;
    }

    // Show modal
    function showModal(callback) {
        let modal = document.getElementById('exitConfirmationModal');
        if (!modal) {
            modal = createModal();
        }

        modal.classList.add('show');

        // Button handlers
        const btnSave = document.getElementById('btnSaveAndExit');
        const btnDiscard = document.getElementById('btnDiscardAndExit');
        const btnCancel = document.getElementById('btnCancelExit');

        // Remove old listeners
        const newBtnSave = btnSave.cloneNode(true);
        const newBtnDiscard = btnDiscard.cloneNode(true);
        const newBtnCancel = btnCancel.cloneNode(true);
        
        btnSave.parentNode.replaceChild(newBtnSave, btnSave);
        btnDiscard.parentNode.replaceChild(newBtnDiscard, btnDiscard);
        btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);

        // Save & Exit
        newBtnSave.addEventListener('click', function() {
            modal.classList.remove('show');
            callback('save');
        });

        // Discard & Exit
        newBtnDiscard.addEventListener('click', function() {
            modal.classList.remove('show');
            formChanged = false;
            callback('discard');
        });

        // Cancel
        newBtnCancel.addEventListener('click', function() {
            modal.classList.remove('show');
            callback('cancel');
        });

        // Close on overlay click
        modal.querySelector('.exit-modal-overlay').addEventListener('click', function() {
            modal.classList.remove('show');
            callback('cancel');
        });
    }

    // Intercept back button clicks
    function interceptBackButton() {
        // Find all back buttons (floating and regular)
        const backButtons = document.querySelectorAll('.btn-back-to-detail, a[href*="detail-pasien"], button[onclick*="detail-pasien"]');

        backButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (formChanged && !isSubmitting) {
                    e.preventDefault();
                    e.stopPropagation();

                    const originalHref = this.href || this.getAttribute('onclick');

                    showModal(function(action) {
                        if (action === 'save') {
                            // Submit form
                            const form = document.querySelector('form[id*="form"]');
                            if (form) {
                                isSubmitting = true;
                                formChanged = false;
                                form.submit();
                            }
                        } else if (action === 'discard') {
                            // Navigate away
                            if (button.href) {
                                window.location.href = button.href;
                            } else if (button.getAttribute('onclick')) {
                                eval(button.getAttribute('onclick'));
                            }
                        }
                        // action === 'cancel' - do nothing
                    });
                }
            });
        });
    }

    // Warn before page unload
    function setupBeforeUnload() {
        window.addEventListener('beforeunload', function(e) {
            if (formChanged && !isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin keluar?';
                return e.returnValue;
            }
        });
    }

    // Initialize
    function init() {
        // Track all forms
        const forms = document.querySelectorAll('form');
        forms.forEach(trackFormChanges);

        // Intercept back buttons
        interceptBackButton();

        // Setup beforeunload
        setupBeforeUnload();

        console.log('✅ Form Exit Confirmation initialized');
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialize for dynamic content
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    // Track new forms
                    if (node.tagName === 'FORM') {
                        trackFormChanges(node);
                    }
                    const forms = node.querySelectorAll ? node.querySelectorAll('form') : [];
                    forms.forEach(trackFormChanges);

                    // Re-intercept back buttons
                    interceptBackButton();
                }
            });
        });
    });

    const startObserver = function() {
        if (!document.body) {
            return;
        }
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };

    if (document.body) {
        startObserver();
    } else {
        window.addEventListener('load', startObserver);
    }

    // Expose to global scope
    window.FormExitConfirmation = {
        init: init,
        reset: function() {
            formChanged = false;
            isSubmitting = false;
        }
    };

})();
