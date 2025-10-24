/**
 * IMPROVEMENTS.JS - JavaScript Utilities untuk Perbaikan UX
 * Include SETELAH script.js di footer.php
 */

// ===== 1. TOAST NOTIFICATION SYSTEM =====
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = 3000) {
        this.init();

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="${icons[type]} toast-icon"></i>
            <div class="toast-message">${message}</div>
            <button class="toast-close" aria-label="Tutup"><i class="fas fa-times"></i></button>
        `;

        this.container.appendChild(toast);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hide(toast);
        });

        // Auto hide
        if (duration > 0) {
            setTimeout(() => this.hide(toast), duration);
        }

        return toast;
    },

    hide(toast) {
        toast.classList.add('hiding');
        setTimeout(() => {
            toast.remove();
        }, 300);
    },

    success(message, duration) {
        return this.show(message, 'success', duration);
    },

    error(message, duration) {
        return this.show(message, 'error', duration);
    },

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

// ===== 2. LOADING OVERLAY =====
const Loading = {
    overlay: null,

    init() {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `
                <div>
                    <div class="spinner"></div>
                    <div class="loading-text">Memproses...</div>
                </div>
            `;
            document.body.appendChild(this.overlay);
        }
    },

    show(text = 'Memproses...') {
        this.init();
        const textElement = this.overlay.querySelector('.loading-text');
        if (textElement) textElement.textContent = text;
        this.overlay.classList.add('active');
    },

    hide() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
    }
};

// ===== 3. FORM SUBMISSION WITH LOADING =====
// DISABLED: Loading overlay memperlambat submit form
function setupFormWithLoading() {
    // DISABLED - Loading overlay menyebabkan form lambat
    // Jika ingin aktifkan lagi, uncomment code di bawah
    
    /*
    const forms = document.querySelectorAll('form[id]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Skip jika form punya data-no-loading attribute
            if (this.hasAttribute('data-no-loading')) return;

            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                // Disable button
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            }

            // Show loading overlay
            Loading.show('Menyimpan data...');

            // If form is AJAX, ensure to call Loading.hide() in success/error callbacks
            // If form is regular submit, loading will be hidden when page reloads
        });
    });
    */
}

// ===== 4. BACK TO TOP BUTTON =====
function setupBackToTop() {
    // Create button
    const btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    btn.setAttribute('aria-label', 'Kembali ke atas');
    document.body.appendChild(btn);

    // Show/hide based on scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });

    // Smooth scroll to top
    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ===== 5. FORM VALIDATION WITH ERROR MESSAGES =====
function setupFormValidation() {
    const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');

    inputs.forEach(input => {
        // Validation on blur
        input.addEventListener('blur', function() {
            validateInput(this);
        });

        // Clear error on input
        input.addEventListener('input', function() {
            if (this.parentElement.classList.contains('error')) {
                this.parentElement.classList.remove('error');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        });
    });
}

function validateInput(input) {
    const container = input.parentElement;
    
    // Remove existing error
    container.classList.remove('error');
    const existingError = container.querySelector('.error-message');
    if (existingError) existingError.remove();

    // Check validity
    if (!input.checkValidity()) {
        container.classList.add('error');
        
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        
        let message = input.validationMessage;
        
        // Custom messages
        if (input.validity.valueMissing) {
            message = 'Field ini wajib diisi';
        } else if (input.validity.typeMismatch) {
            if (input.type === 'email') message = 'Format email tidak valid';
            if (input.type === 'url') message = 'Format URL tidak valid';
        } else if (input.validity.tooShort) {
            message = `Minimal ${input.minLength} karakter`;
        } else if (input.validity.tooLong) {
            message = `Maksimal ${input.maxLength} karakter`;
        } else if (input.validity.rangeUnderflow) {
            message = `Nilai minimal ${input.min}`;
        } else if (input.validity.rangeOverflow) {
            message = `Nilai maksimal ${input.max}`;
        }
        
        errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        container.appendChild(errorMsg);
        
        return false;
    }
    
    return true;
}

// ===== 6. AUTO-SAVE FORM DRAFT =====
class FormAutoSave {
    constructor(formId, storageKey, interval = 5000) {
        this.form = document.getElementById(formId);
        this.storageKey = storageKey;
        this.interval = interval;
        this.timer = null;

        if (this.form) {
            this.init();
        }
    }

    init() {
        // Load saved draft
        this.loadDraft();

        // Setup auto-save
        this.form.addEventListener('input', () => {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.saveDraft(), this.interval);
        });

        // Clear draft on successful submit
        this.form.addEventListener('submit', () => {
            this.clearDraft();
        });

        // Show indicator if draft exists
        if (this.hasDraft()) {
            this.showDraftIndicator();
        }
    }

    saveDraft() {
        const formData = new FormData(this.form);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });

        localStorage.setItem(this.storageKey, JSON.stringify(data));
        
        // Show saved indicator
        Toast.info('Draft otomatis tersimpan', 2000);
    }

    loadDraft() {
        const saved = localStorage.getItem(this.storageKey);
        if (!saved) return;

        try {
            const data = JSON.parse(saved);
            
            Object.keys(data).forEach(key => {
                const input = this.form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        } catch (e) {
            console.error('Error loading draft:', e);
        }
    }

    clearDraft() {
        localStorage.removeItem(this.storageKey);
    }

    hasDraft() {
        return localStorage.getItem(this.storageKey) !== null;
    }

    showDraftIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'alert alert-info';
        indicator.style.marginBottom = '20px';
        indicator.innerHTML = `
            <i class="fas fa-info-circle"></i> 
            Draft tersimpan ditemukan. Data telah dimuat otomatis.
            <button type="button" onclick="clearFormDraft('${this.storageKey}')" 
                    class="btn btn-sm btn-secondary" style="margin-left: 10px;">
                Hapus Draft
            </button>
        `;
        
        this.form.insertBefore(indicator, this.form.firstChild);
    }
}

function clearFormDraft(storageKey) {
    if (confirm('Hapus draft tersimpan?')) {
        localStorage.removeItem(storageKey);
        Toast.success('Draft berhasil dihapus');
        location.reload();
    }
}

// ===== 7. MODAL UTILITY =====
const Modal = {
    create(title, content, buttons = []) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        
        const buttonsHtml = buttons.map(btn => 
            `<button class="btn btn-${btn.type || 'secondary'}" data-action="${btn.action}">${btn.text}</button>`
        ).join('');

        overlay.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="modal-close" aria-label="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">${content}</div>
                <div class="modal-footer">
                    ${buttonsHtml}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Close handlers
        overlay.querySelector('.modal-close').addEventListener('click', () => this.close(overlay));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) this.close(overlay);
        });

        // Button handlers
        buttons.forEach(btn => {
            const btnElement = overlay.querySelector(`[data-action="${btn.action}"]`);
            if (btnElement && btn.handler) {
                btnElement.addEventListener('click', () => {
                    btn.handler(overlay);
                });
            }
        });

        // Show modal
        setTimeout(() => overlay.classList.add('active'), 10);

        return overlay;
    },

    close(overlay) {
        overlay.classList.remove('active');
        setTimeout(() => overlay.remove(), 300);
    },

    confirm(title, message, onConfirm, onCancel) {
        return this.create(title, message, [
            {
                text: 'Batal',
                type: 'secondary',
                action: 'cancel',
                handler: (modal) => {
                    this.close(modal);
                    if (onCancel) onCancel();
                }
            },
            {
                text: 'Konfirmasi',
                type: 'primary',
                action: 'confirm',
                handler: (modal) => {
                    this.close(modal);
                    if (onConfirm) onConfirm();
                }
            }
        ]);
    }
};

// ===== 8. PREVENT DOUBLE SUBMIT =====
function preventDoubleSubmit() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (submitBtn && submitBtn.disabled) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ===== 9. PRINT FUNCTIONALITY =====
function setupPrintButton() {
    const printButtons = document.querySelectorAll('[data-print]');
    
    printButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            window.print();
        });
    });
}

// ===== 10. INITIALIZE ON DOM READY =====
document.addEventListener('DOMContentLoaded', function() {
    // Setup all improvements
    setupFormWithLoading();
    setupBackToTop();
    setupFormValidation();
    preventDoubleSubmit();
    setupPrintButton();

    // Setup auto-save for specific forms
    // Example usage:
    // new FormAutoSave('formTambahBooking', 'tambahBookingDraft');
    
    console.log('✓ Improvements.js loaded');
});

// ===== 11. GLOBAL ERROR HANDLER FOR AJAX =====
window.handleAjaxError = function(error) {
    Loading.hide();
    console.error('Ajax Error:', error);
    Toast.error('Terjadi kesalahan. Silakan coba lagi.');
};

// ===== 12. UTILITY FUNCTIONS =====
const Utils = {
    // Format tanggal Indonesia
    formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    },

    // Format waktu
    formatTime(timeString) {
        return timeString.substring(0, 5); // HH:MM
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Copy to clipboard
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Toast.success('Berhasil disalin ke clipboard');
        }).catch(() => {
            Toast.error('Gagal menyalin ke clipboard');
        });
    }
};

// Export untuk penggunaan global
window.Toast = Toast;
window.Loading = Loading;
window.Modal = Modal;
window.Utils = Utils;
window.FormAutoSave = FormAutoSave;
