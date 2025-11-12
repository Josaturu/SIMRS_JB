/**
 * Persiapan Operasi - Custom Validation
 * Menghapus peringatan HTML5 dan mengganti dengan konfirmasi custom
 */

(function() {
    'use strict';

    // Field nullable yang perlu di-check
    const nullableFieldsConfig = [
        { id: 'tglOperasi', label: 'Tanggal Operasi' },
        { id: 'macamOperasi', label: 'Macam Operasi' },
        { id: 'dpjp', label: 'DPJP (Dokter Penanggung Jawab Pelayanan)' },
        { id: 'tinggiBadan', label: 'Tinggi Badan' },
        { id: 'riwayatAlergi', label: 'Riwayat Alergi' },
        { id: 'beratBadan', label: 'Berat Badan' }
    ];

    // Check empty nullable fields
    function checkEmptyNullableFields() {
        const emptyFields = [];
        
        nullableFieldsConfig.forEach(function(field) {
            const input = document.getElementById(field.id);
            if (input) {
                const value = input.value ? input.value.trim() : '';
                if (value === '') {
                    emptyFields.push(field.label);
                }
            }
        });
        
        return emptyFields;
    }

    // Show confirmation modal
    function showConfirmationModal(emptyFields, callback) {
        // Create modal if not exists
        let modal = document.getElementById('persiapanOperasiConfirmModal');
        if (!modal) {
            modal = createConfirmModal();
        }

        // Update empty fields list
        const fieldList = modal.querySelector('#emptyFieldsListPersiapan');
        fieldList.innerHTML = '';
        emptyFields.forEach(function(field) {
            const li = document.createElement('li');
            li.textContent = field;
            fieldList.appendChild(li);
        });

        // Show modal
        modal.classList.add('show');

        // Button handlers
        const btnBack = document.getElementById('btnBackToFillPersiapan');
        const btnContinue = document.getElementById('btnContinueAnywayPersiapan');

        // Remove old listeners
        const newBtnBack = btnBack.cloneNode(true);
        const newBtnContinue = btnContinue.cloneNode(true);
        
        btnBack.parentNode.replaceChild(newBtnBack, btnBack);
        btnContinue.parentNode.replaceChild(newBtnContinue, btnContinue);

        // Back to fill
        newBtnBack.addEventListener('click', function() {
            modal.classList.remove('show');
            callback(false);
        });

        // Continue anyway
        newBtnContinue.addEventListener('click', function() {
            modal.classList.remove('show');
            callback(true);
        });

        // Close on overlay click
        modal.querySelector('.confirm-modal-overlay').addEventListener('click', function() {
            modal.classList.remove('show');
            callback(false);
        });
    }

    // Create confirmation modal
    function createConfirmModal() {
        const modal = document.createElement('div');
        modal.id = 'persiapanOperasiConfirmModal';
        modal.className = 'confirm-modal';
        modal.innerHTML = `
            <div class="confirm-modal-overlay"></div>
            <div class="confirm-modal-content">
                <div class="confirm-modal-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Konfirmasi Penyimpanan</h3>
                </div>
                <div class="confirm-modal-body">
                    <p style="margin-bottom: 15px;">Anda akan menyimpan data tanpa mengisi:</p>
                    <ul id="emptyFieldsListPersiapan" style="margin: 10px 0; padding-left: 20px; color: #dc3545; font-weight: 500;"></ul>
                    <p style="margin-top: 15px;"><strong>Apakah Anda yakin ingin menyimpan?</strong></p>
                </div>
                <div class="confirm-modal-footer">
                    <button type="button" class="btn-modal-confirm btn-back" id="btnBackToFillPersiapan">
                        <i class="fas fa-arrow-left"></i> Kembali Isi Data
                    </button>
                    <button type="button" class="btn-modal-confirm btn-continue" id="btnContinueAnywayPersiapan">
                        <i class="fas fa-check"></i> Ya, Simpan
                    </button>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .confirm-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            }

            .confirm-modal.show {
                display: block;
            }

            .confirm-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(3px);
            }

            .confirm-modal-content {
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

            .confirm-modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                align-items: center;
                gap: 12px;
                background: #fff3cd;
                border-top-left-radius: 12px;
                border-top-right-radius: 12px;
            }

            .confirm-modal-header i {
                font-size: 28px;
                color: #ff9800;
            }

            .confirm-modal-header h3 {
                margin: 0;
                font-size: 18px;
                color: #856404;
            }

            .confirm-modal-body {
                padding: 24px;
                color: #333;
            }

            .confirm-modal-body p {
                margin: 0 0 10px 0;
                line-height: 1.6;
            }

            .confirm-modal-body ul {
                list-style: disc;
            }

            .confirm-modal-body ul li {
                margin-bottom: 5px;
            }

            .confirm-modal-footer {
                padding: 16px 24px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                flex-wrap: wrap;
            }

            .btn-modal-confirm {
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

            .btn-modal-confirm:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .btn-modal-confirm:active {
                transform: translateY(0);
            }

            .btn-modal-confirm.btn-back {
                background: #6c757d;
                color: white;
            }

            .btn-modal-confirm.btn-back:hover {
                background: #5a6268;
            }

            .btn-modal-confirm.btn-continue {
                background: #28a745;
                color: white;
            }

            .btn-modal-confirm.btn-continue:hover {
                background: #218838;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
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
                .confirm-modal-content {
                    width: 95%;
                }

                .confirm-modal-footer {
                    flex-direction: column;
                }

                .btn-modal-confirm {
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
        const form = document.querySelector('form[action*="persiapan-operasi"]');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const emptyFields = checkEmptyNullableFields();
            
            if (emptyFields.length > 0) {
                e.preventDefault();
                e.stopPropagation();

                showConfirmationModal(emptyFields, function(shouldContinue) {
                    if (shouldContinue) {
                        // Remove event listener and submit
                        const newForm = form.cloneNode(true);
                        form.parentNode.replaceChild(newForm, form);
                        newForm.submit();
                    }
                });
            }
        });
    }

    // Initialize
    function init() {
        interceptFormSubmit();
        console.log('✅ Persiapan Operasi Validation initialized');
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose to global scope
    window.PersiapanOperasiValidation = {
        init: init,
        check: checkEmptyNullableFields
    };

})();
