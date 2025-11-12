/**
 * Nullable Field Warning
 * Menampilkan peringatan jika ada field nullable yang masih kosong sebelum submit atau keluar
 */

(function() {
    'use strict';

    // Daftar field yang nullable tapi sebaiknya diisi
    const nullableFields = [
        { name: 'prognosis', label: 'Prognosis' },
        { name: 'alternatif', label: 'Alternatif dan Resiko' },
        { name: 'lainLain', label: 'Lain-lain' },
        { name: 'data_masuk', label: 'Data Masuk' },
        { name: 'kondisi_pasien', label: 'Kondisi Pasien' }
    ];

    // Check if any nullable fields are empty
    function checkNullableFields() {
        const emptyFields = [];
        
        nullableFields.forEach(function(field) {
            const input = document.getElementById(field.name) || 
                         document.querySelector(`[name="${field.name}"]`);
            
            if (input) {
                const value = input.value ? input.value.trim() : '';
                if (value === '') {
                    emptyFields.push(field.label);
                }
            }
        });
        
        return emptyFields;
    }

    // Show warning modal
    function showWarningModal(emptyFields, callback) {
        // Create modal if not exists
        let modal = document.getElementById('nullableFieldWarningModal');
        if (!modal) {
            modal = createWarningModal();
        }

        // Update empty fields list
        const fieldList = modal.querySelector('#emptyFieldsList');
        fieldList.innerHTML = '';
        emptyFields.forEach(function(field) {
            const li = document.createElement('li');
            li.textContent = field;
            li.style.marginBottom = '5px';
            fieldList.appendChild(li);
        });

        // Show modal
        modal.classList.add('show');

        // Button handlers
        const btnContinue = document.getElementById('btnContinueAnyway');
        const btnGoBack = document.getElementById('btnGoBackToFill');

        // Remove old listeners
        const newBtnContinue = btnContinue.cloneNode(true);
        const newBtnGoBack = btnGoBack.cloneNode(true);
        
        btnContinue.parentNode.replaceChild(newBtnContinue, btnContinue);
        btnGoBack.parentNode.replaceChild(newBtnGoBack, btnGoBack);

        // Continue anyway
        newBtnContinue.addEventListener('click', function() {
            modal.classList.remove('show');
            callback('continue');
        });

        // Go back to fill
        newBtnGoBack.addEventListener('click', function() {
            modal.classList.remove('show');
            callback('goback');
        });

        // Close on overlay click
        modal.querySelector('.warning-modal-overlay').addEventListener('click', function() {
            modal.classList.remove('show');
            callback('goback');
        });
    }

    // Create warning modal HTML
    function createWarningModal() {
        const modal = document.createElement('div');
        modal.id = 'nullableFieldWarningModal';
        modal.className = 'warning-modal';
        modal.innerHTML = `
            <div class="warning-modal-overlay"></div>
            <div class="warning-modal-content">
                <div class="warning-modal-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Peringatan: Data Belum Lengkap</h3>
                </div>
                <div class="warning-modal-body">
                    <p style="margin-bottom: 15px;">Anda akan menyimpan data tanpa mengisi:</p>
                    <ul id="emptyFieldsList" style="margin: 10px 0; padding-left: 20px; color: #dc3545; font-weight: 500;"></ul>
                    <p style="margin-top: 15px;"><strong>Apakah Anda yakin ingin menyimpan?</strong></p>
                </div>
                <div class="warning-modal-footer">
                    <button type="button" class="btn-modal btn-goback" id="btnGoBackToFill">
                        <i class="fas fa-arrow-left"></i> Kembali Isi Data
                    </button>
                    <button type="button" class="btn-modal btn-continue" id="btnContinueAnyway">
                        <i class="fas fa-check"></i> Lanjutkan Saja
                    </button>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .warning-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            }

            .warning-modal.show {
                display: block;
            }

            .warning-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(3px);
            }

            .warning-modal-content {
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

            .warning-modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                gap: 12px;
                background: #fff3cd;
                border-top-left-radius: 12px;
                border-top-right-radius: 12px;
            }

            .warning-modal-header i {
                font-size: 28px;
                color: #ff9800;
            }

            .warning-modal-header h3 {
                margin: 0;
                font-size: 18px;
                color: #856404;
            }

            .warning-modal-body {
                padding: 24px;
                color: #333;
            }

            .warning-modal-body p {
                margin: 0 0 10px 0;
                line-height: 1.6;
            }

            .warning-modal-footer {
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

            .btn-goback {
                background: #6c757d;
                color: white;
            }

            .btn-goback:hover {
                background: #5a6268;
            }

            .btn-continue {
                background: #ffc107;
                color: #333;
            }

            .btn-continue:hover {
                background: #e0a800;
            }

            @media (max-width: 600px) {
                .warning-modal-content {
                    width: 95%;
                }

                .warning-modal-footer {
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

    // Intercept form submit
    function interceptFormSubmit() {
        const form = document.querySelector('form[id*="Informed"], form[id*="informed"]');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const emptyFields = checkNullableFields();
            
            if (emptyFields.length > 0) {
                e.preventDefault();
                e.stopPropagation();

                showWarningModal(emptyFields, function(action) {
                    if (action === 'continue') {
                        // Submit form without validation
                        const newForm = form.cloneNode(true);
                        form.parentNode.replaceChild(newForm, form);
                        newForm.submit();
                    }
                    // action === 'goback' - do nothing, stay on form
                });
            }
        });
    }

    // Intercept back button
    function interceptBackButton() {
        const backButtons = document.querySelectorAll('.btn-back-to-detail, a[href*="detail-pasien"], button[onclick*="detail-pasien"]');

        backButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                const emptyFields = checkNullableFields();
                
                if (emptyFields.length > 0) {
                    e.preventDefault();
                    e.stopPropagation();

                    const originalHref = this.href || this.getAttribute('onclick');

                    showWarningModal(emptyFields, function(action) {
                        if (action === 'continue') {
                            // Navigate away
                            if (button.href) {
                                window.location.href = button.href;
                            } else if (button.getAttribute('onclick')) {
                                eval(button.getAttribute('onclick'));
                            }
                        }
                        // action === 'goback' - do nothing
                    });
                }
            });
        });
    }

    // Initialize
    function init() {
        interceptFormSubmit();
        interceptBackButton();
        console.log('✅ Nullable Field Warning initialized');
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose to global scope
    window.NullableFieldWarning = {
        init: init,
        check: checkNullableFields
    };

})();
