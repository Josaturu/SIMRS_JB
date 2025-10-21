/**
 * Global Notification System
 * Unified notification system for all pages
 * Features: Success, Error, Warning, Info notifications with animations
 */

const Notification = {
    /**
     * Show notification
     * @param {string} message - Message to display
     * @param {string} type - Type: 'success', 'error', 'warning', 'info'
     * @param {number} duration - Duration in milliseconds (default: 4000)
     */
    show: function(message, type = 'info', duration = 4000) {
        // Remove existing notification if any
        this.remove();
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.id = 'global-notification';
        
        // Icon based on type
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        // Titles based on type
        const titles = {
            success: 'Berhasil!',
            error: 'Gagal!',
            warning: 'Peringatan!',
            info: 'Informasi'
        };
        
        notification.innerHTML = `
            <div class="notification-icon">${icons[type]}</div>
            <div class="notification-content">
                <div class="notification-title">${titles[type]}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" onclick="Notification.remove()">×</button>
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove();
            }, duration);
        }
    },
    
    /**
     * Show success notification
     */
    success: function(message, duration = 4000) {
        this.show(message, 'success', duration);
    },
    
    /**
     * Show error notification
     */
    error: function(message, duration = 5000) {
        this.show(message, 'error', duration);
    },
    
    /**
     * Show warning notification
     */
    warning: function(message, duration = 4000) {
        this.show(message, 'warning', duration);
    },
    
    /**
     * Show info notification
     */
    info: function(message, duration = 4000) {
        this.show(message, 'info', duration);
    },
    
    /**
     * Remove notification
     */
    remove: function() {
        const notification = document.getElementById('global-notification');
        if (notification) {
            notification.classList.remove('show');
            notification.classList.add('hide');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    },
    
    /**
     * Check URL parameters and show notification
     */
    checkUrlParams: function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const action = urlParams.get('action');
        const error = urlParams.get('error');
        const msg = urlParams.get('msg');
        const success = urlParams.get('success');
        
        console.log('[Notification] URL Params:', {
            status: status,
            action: action,
            error: error,
            msg: msg,
            success: success
        });
        
        if (status === 'sukses') {
            let message = 'Data berhasil disimpan.';
            if (action === 'updated') {
                message = 'Data berhasil diperbarui.';
            } else if (action === 'saved') {
                message = 'Data berhasil disimpan.';
            }
            console.log('[Notification] Showing success:', message);
            this.success(message);
        } else if (status === 'gagal') {
            const errorMsg = error || msg || 'Terjadi kesalahan saat menyimpan data.';
            console.log('[Notification] Showing error:', errorMsg);
            this.error(errorMsg);
        } else if (status === 'error') {
            const errorMsg = msg || error || 'Terjadi kesalahan sistem.';
            console.log('[Notification] Showing error:', errorMsg);
            this.error(errorMsg);
        } else if (success) {
            // Support for ?success=saved or ?success=updated
            const message = success === 'updated' ? 'Data berhasil diperbarui.' : 'Data berhasil disimpan.';
            console.log('[Notification] Showing success from ?success param:', message);
            this.success(message);
        } else if (error) {
            // Support for ?error=message
            console.log('[Notification] Showing error from ?error param:', error);
            this.error(error);
        } else {
            console.log('[Notification] No notification params found');
        }
    }
};

// Make it globally available FIRST
window.Notification = Notification;

// Auto-check URL params on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Notification] DOMContentLoaded - checking URL params');
        Notification.checkUrlParams();
    });
} else {
    // DOM already loaded, run immediately
    console.log('[Notification] DOM already loaded - checking URL params');
    Notification.checkUrlParams();
}
