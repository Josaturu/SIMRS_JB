/**
 * HEADER STICKY - Popover Toggle & Scroll Detection
 * Handles patient info popover interaction and sticky header scroll effects
 */

(function() {
    'use strict';

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initPopover();
        initScrollDetection();
    });

    /**
     * Initialize popover toggle functionality
     */
    function initPopover() {
        const toggleBtn = document.getElementById('patientInfoToggle');
        const popover = document.getElementById('patientInfoPopover');
        const closeBtn = document.getElementById('popoverClose');

        if (!toggleBtn || !popover) return;

        // Toggle popover on button click
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isActive = popover.classList.contains('active');
            
            if (isActive) {
                closePopover();
            } else {
                openPopover();
            }
        });

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closePopover();
            });
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (popover.classList.contains('active')) {
                if (!popover.contains(e.target) && e.target !== toggleBtn) {
                    closePopover();
                }
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popover.classList.contains('active')) {
                closePopover();
            }
        });

        function openPopover() {
            popover.classList.add('active');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }

        function closePopover() {
            popover.classList.remove('active');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }

    /**
     * Detect scroll and add shadow to header
     */
    function initScrollDetection() {
        const header = document.querySelector('.header-sticky');
        if (!header) return;

        let lastScrollY = window.scrollY;
        let ticking = false;

        window.addEventListener('scroll', function() {
            lastScrollY = window.scrollY;

            if (!ticking) {
                window.requestAnimationFrame(function() {
                    updateHeaderState(lastScrollY);
                    ticking = false;
                });

                ticking = true;
            }
        });

        function updateHeaderState(scrollY) {
            if (scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
    }

})();
