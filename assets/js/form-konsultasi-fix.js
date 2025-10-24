/**
 * FIX FORM KONSULTASI ANESTESI
 * Mengatasi masalah data tidak tersimpan
 * Tanggal: 2025-10-20
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Form Konsultasi Anestesi - Fix Script Loaded');
    
    // ============================================
    // 1. SINKRONISASI FIELD DUPLIKAT
    // ============================================
    
    /**
     * Sinkronkan Tinggi Badan (tinggiBadan <-> tb)
     */
    const tinggiBadanTop = document.getElementById('tinggiBadan');
    const tinggiBadanBottom = document.getElementById('tb');
    
    if (tinggiBadanTop && tinggiBadanBottom) {
        // Sync dari atas ke bawah
        tinggiBadanTop.addEventListener('input', function() {
            tinggiBadanBottom.value = this.value;
            console.log('Sync TB: ' + this.value);
        });
        
        // Sync dari bawah ke atas
        tinggiBadanBottom.addEventListener('input', function() {
            tinggiBadanTop.value = this.value;
            console.log('Sync TB: ' + this.value);
        });
        
        // Sync saat page load jika ada nilai
        if (tinggiBadanTop.value && !tinggiBadanBottom.value) {
            tinggiBadanBottom.value = tinggiBadanTop.value;
        } else if (tinggiBadanBottom.value && !tinggiBadanTop.value) {
            tinggiBadanTop.value = tinggiBadanBottom.value;
        }
    }
    
    /**
     * Sinkronkan Berat Badan (beratBadan <-> bb)
     */
    const beratBadanTop = document.getElementById('beratBadan');
    const beratBadanBottom = document.getElementById('bb');
    
    if (beratBadanTop && beratBadanBottom) {
        // Sync dari atas ke bawah
        beratBadanTop.addEventListener('input', function() {
            beratBadanBottom.value = this.value;
            console.log('Sync BB: ' + this.value);
        });
        
        // Sync dari bawah ke atas
        beratBadanBottom.addEventListener('input', function() {
            beratBadanTop.value = this.value;
            console.log('Sync BB: ' + this.value);
        });
        
        // Sync saat page load jika ada nilai
        if (beratBadanTop.value && !beratBadanBottom.value) {
            beratBadanBottom.value = beratBadanTop.value;
        } else if (beratBadanBottom.value && !beratBadanTop.value) {
            beratBadanTop.value = beratBadanBottom.value;
        }
    }
    
    // ============================================
    // 2. VALIDASI SEBELUM SUBMIT
    // ============================================
    
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT TRIGGERED ===');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            
            // Pastikan field duplikat tersinkronisasi
            if (tinggiBadanTop && tinggiBadanBottom) {
                if (tinggiBadanTop.value && !tinggiBadanBottom.value) {
                    tinggiBadanBottom.value = tinggiBadanTop.value;
                }
            }
            
            if (beratBadanTop && beratBadanBottom) {
                if (beratBadanTop.value && !beratBadanBottom.value) {
                    beratBadanBottom.value = beratBadanTop.value;
                }
            }
            
            // Validasi field penting
            const requiredFields = [
                { id: 'kesadaran', label: 'Kesadaran' },
                { id: 'tb', label: 'Tinggi Badan (TB)' },
                { id: 'bb', label: 'Berat Badan (BB)' },
                { id: 'td', label: 'Tekanan Darah (TD)' },
                { id: 'nadi', label: 'Nadi' },
                { id: 'rr', label: 'Respiratory Rate (RR)' },
                { id: 'suhu', label: 'Suhu' }
            ];
            
            let missingFields = [];
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (element && !element.value.trim()) {
                    missingFields.push(field.label);
                }
            });
            
            // Cek Jenis Kelamin
            const jenisKelaminChecked = document.querySelector('input[name="jenis_kelamin"]:checked');
            if (!jenisKelaminChecked) {
                missingFields.push('Jenis Kelamin');
            }
            
            // Cek ASA Status
            const asaChecked = document.querySelector('input[name="asa"]:checked');
            if (!asaChecked) {
                missingFields.push('Klasifikasi ASA');
            }
            
            // Tampilkan peringatan jika ada field yang kosong
            if (missingFields.length > 0) {
                const confirmSubmit = confirm(
                    'Field berikut belum diisi:\n\n' + 
                    missingFields.join('\n') + 
                    '\n\nApakah Anda yakin ingin melanjutkan?'
                );
                
                if (!confirmSubmit) {
                    e.preventDefault();
                    return false;
                }
            }
            
            console.log('Form validation passed');
        });
    }
    
    // ============================================
    // 3. AUTO-FILL DEFAULT VALUES
    // ============================================
    
    /**
     * Set default value untuk field yang sering kosong
     */
    function setDefaultIfEmpty(fieldId, defaultValue) {
        const field = document.getElementById(fieldId);
        if (field && !field.value) {
            field.value = defaultValue;
        }
    }
    
    // Uncomment jika ingin set default value
    // setDefaultIfEmpty('kesadaran', 'Compos Mentis');
    // setDefaultIfEmpty('jalan_nafas', 'Normal');
    
    // ============================================
    // 4. TOGGLE FIELD PENGOBATAN
    // ============================================
    
    /**
     * Pastikan field pengobatan ter-submit meskipun hidden
     */
    const hasPengobatanRadios = document.querySelectorAll('input[name="has_pengobatan"]');
    const pengobatanDetail = document.getElementById('pengobatanDetail');
    const pengobatanField = document.getElementById('pengobatan');
    
    hasPengobatanRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Tidak' && pengobatanField) {
                // Kosongkan field pengobatan jika pilih "Tidak"
                pengobatanField.value = '';
            }
        });
    });
    
    // ============================================
    // 5. DEBUG HELPER
    // ============================================
    
    /**
     * Log semua field yang kosong saat submit
     */
    if (form) {
        form.addEventListener('submit', function() {
            const formData = new FormData(form);
            const emptyFields = [];
            
            for (let [key, value] of formData.entries()) {
                if (!value || value.trim() === '') {
                    emptyFields.push(key);
                }
            }
            
            if (emptyFields.length > 0) {
                console.warn('Empty fields:', emptyFields);
            }
            
            // Log field penting dengan highlight untuk jenis_kelamin
            console.log('%c=== FIELD PENTING ===', 'color: blue; font-weight: bold; font-size: 14px;');
            console.log('tinggiBadan:', formData.get('tinggiBadan'));
            console.log('beratBadan:', formData.get('beratBadan'));
            console.log('tb:', formData.get('tb'));
            console.log('bb:', formData.get('bb'));
            
            // Highlight jenis_kelamin
            const jenisKelamin = formData.get('jenis_kelamin');
            if (jenisKelamin) {
                console.log('%cjenis_kelamin: ' + jenisKelamin, 'background: yellow; color: black; font-weight: bold; padding: 2px 5px;');
            } else {
                console.log('%cjenis_kelamin: NULL/KOSONG', 'background: red; color: white; font-weight: bold; padding: 2px 5px;');
            }
            
            console.log('menikah:', formData.get('menikah'));
            console.log('kesadaran:', formData.get('kesadaran'));
            console.log('td:', formData.get('td'));
            console.log('nadi:', formData.get('nadi'));
            console.log('rr:', formData.get('rr'));
            console.log('suhu:', formData.get('suhu'));
            console.log('paruParu:', formData.get('paruParu'));
            console.log('jantung:', formData.get('jantung'));
            console.log('asa:', formData.get('asa'));
            console.log('anestesi_umum:', formData.get('anestesi_umum'));
            console.log('regional:', formData.get('regional'));
            console.log('combined:', formData.get('combined'));
            console.log('sedasi:', formData.get('sedasi'));
            console.log('%c=====================', 'color: blue; font-weight: bold;');
        });
    }
    
    // ============================================
    // 6. HIGHLIGHT FIELD YANG KOSONG
    // ============================================
    
    /**
     * Tambahkan visual indicator untuk field yang wajib diisi
     */
    function highlightEmptyRequiredFields() {
        const importantFields = [
            'kesadaran', 'tb', 'bb', 'td', 'nadi', 'rr', 'suhu',
            'paruParu', 'jantung', 'abdomen', 'ekstrimitas'
        ];
        
        importantFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.style.borderColor = '#ff6b6b';
                        this.style.backgroundColor = '#fff5f5';
                    } else {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                    }
                });
                
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#51cf66';
                        this.style.backgroundColor = '#f0fff4';
                    }
                });
            }
        });
    }
    
    highlightEmptyRequiredFields();
    
    // ============================================
    // 7. VISUAL FEEDBACK UNTUK JENIS KELAMIN
    // ============================================
    
    /**
     * Highlight jenis kelamin jika belum dipilih
     */
    const jenisKelaminRadios = document.querySelectorAll('input[name="jenis_kelamin"]');
    if (jenisKelaminRadios.length > 0) {
        // Cek apakah ada yang sudah dipilih
        const isChecked = Array.from(jenisKelaminRadios).some(radio => radio.checked);
        
        if (!isChecked) {
            // Tambahkan border merah pada container jika belum dipilih
            const container = jenisKelaminRadios[0].closest('.form-item');
            if (container) {
                container.style.border = '2px solid #ff6b6b';
                container.style.padding = '10px';
                container.style.borderRadius = '5px';
                container.style.backgroundColor = '#fff5f5';
            }
        }
        
        // Event listener untuk menghilangkan highlight saat dipilih
        jenisKelaminRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const container = this.closest('.form-item');
                if (container) {
                    container.style.border = '';
                    container.style.padding = '';
                    container.style.backgroundColor = '';
                }
                console.log('Jenis Kelamin dipilih:', this.value);
            });
        });
    }
    
    console.log('Form Konsultasi Anestesi - All fixes applied');
});
