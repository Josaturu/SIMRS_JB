document.addEventListener('DOMContentLoaded', function () {
    const dialWrapper = document.querySelector('[data-dial-init]');
    if (!dialWrapper) return;

    const toggleButton = document.querySelector('[data-dial-toggle]');
    const menu = dialWrapper.querySelector('.speed-dial-menu');

    // Tombol Aksi
    const saveButton = document.getElementById('speed-dial-save');
    const printButton = document.getElementById('speed-dial-print');
    const backButton = document.getElementById('speed-dial-back');
    const logoutButton = document.getElementById('speed-dial-logout');

    // Logika Kontekstual (menampilkan/menyembunyikan tombol)
    const isFormPage = window.location.href.includes('form-');
    const isDashboardPage = window.location.href.endsWith('dashboard.php');

    if (saveButton && printButton) {
        if (!isFormPage) {
            saveButton.style.display = 'none';
            printButton.style.display = 'none';
        } else {
            saveButton.style.display = 'flex';
            printButton.style.display = 'flex';
        }
    }

    if (backButton && isDashboardPage) {
        backButton.style.display = 'none';
    }

    // Event Listener untuk Tombol Utama
    toggleButton.addEventListener('click', function () {
        menu.classList.toggle('hidden');
        const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
        toggleButton.setAttribute('aria-expanded', !isExpanded);
    });

    // Event Listener untuk menutup menu jika klik di luar
    document.addEventListener('click', function (event) {
        if (!dialWrapper.contains(event.target)) {
            menu.classList.add('hidden');
            toggleButton.setAttribute('aria-expanded', 'false');
        }
    });

    // --- Fungsionalitas Tombol Aksi ---

    // 1. Tombol Simpan
    if (saveButton) {
        saveButton.addEventListener('click', function (e) {
            e.preventDefault();
            // Cari form utama di halaman dan submit
            const mainForm = document.querySelector('form.needs-validation, form');
            if (mainForm) {
                // Memicu submit handler asli, bukan hanya submit()
                if (typeof mainForm.requestSubmit === 'function') {
                    mainForm.requestSubmit();
                } else {
                    mainForm.submit();
                }
            } else {
                alert('Tidak ada form untuk disimpan.');
            }
        });
    }

    // 2. Tombol Cetak
    if (printButton) {
        printButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });
    }

    // 3. Tombol Kembali
    if (backButton) {
        backButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.history.back();
        });
    }

    // 4. Tombol Logout
    if (logoutButton) {
        logoutButton.addEventListener('click', function (e) {
            e.preventDefault();
            // Tentukan base path untuk logout
            const basePath = (window.location.href.includes('/views/')) ? '../' : '';
            window.location.href = `${basePath}process/logout_process.php`;
        });
    }
});
