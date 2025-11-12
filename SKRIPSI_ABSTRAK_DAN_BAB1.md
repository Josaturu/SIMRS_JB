# SKRIPSI
# SISTEM INFORMASI MANAJEMEN REKAM MEDIS ANESTESI BERBASIS WEB DI RUMAH SAKIT JOSATURU BEDAH

---

## ABSTRAK

**Judul:** Sistem Informasi Manajemen Rekam Medis Anestesi Berbasis Web di Rumah Sakit Josaturu Bedah

**Nama:** [Nama Mahasiswa]  
**NIM:** [NIM Mahasiswa]  
**Program Studi:** [Program Studi]  
**Pembimbing:** [Nama Pembimbing]

---

### ABSTRAK

Rekam medis anestesi merupakan dokumentasi penting dalam setiap tindakan operasi yang memuat informasi lengkap mengenai persiapan, pelaksanaan, dan pemulihan pasien. Proses pencatatan rekam medis anestesi yang masih dilakukan secara manual di Rumah Sakit Josaturu Bedah menimbulkan berbagai permasalahan, seperti kesulitan dalam penyimpanan, pencarian data, risiko kehilangan dokumen, dan ketidakefisienan waktu dalam pengisian formulir. Penelitian ini bertujuan untuk merancang dan membangun Sistem Informasi Manajemen Rekam Medis Anestesi berbasis web yang dapat membantu tenaga medis dalam mengelola data pasien operasi secara digital, efisien, dan terintegrasi.

Metode pengembangan sistem yang digunakan adalah metode waterfall yang terdiri dari tahap analisis kebutuhan, desain sistem, implementasi, pengujian, dan pemeliharaan. Sistem ini dibangun menggunakan teknologi web dengan bahasa pemrograman PHP, database MySQL, dan framework CSS untuk tampilan antarmuka yang responsif dan user-friendly. Sistem ini memiliki fitur-fitur utama meliputi: manajemen data pasien, booking operasi, konsultasi anestesi, informed consent, checklist keselamatan operasi, checklist persiapan operasi, catatan sedasi dan anestesi dengan monitoring vital sign, catatan kamar pemulihan, serta fitur export laporan dalam format PDF.

Hasil pengujian sistem menunjukkan bahwa sistem dapat berfungsi dengan baik dalam mengelola seluruh proses rekam medis anestesi mulai dari pendaftaran pasien hingga pemulihan pasca operasi. Sistem ini berhasil mengurangi waktu pengisian formulir, meminimalisir kesalahan pencatatan, memudahkan pencarian dan akses data, serta meningkatkan keamanan dan integritas data rekam medis. Sistem juga dilengkapi dengan fitur autosave untuk mencegah kehilangan data dan validasi input untuk memastikan kelengkapan data yang dimasukkan.

Kesimpulan dari penelitian ini adalah Sistem Informasi Manajemen Rekam Medis Anestesi berbasis web dapat menjadi solusi efektif untuk meningkatkan efisiensi dan kualitas pelayanan kesehatan di Rumah Sakit Josaturu Bedah, khususnya dalam pengelolaan dokumentasi anestesi dan operasi. Sistem ini diharapkan dapat membantu tenaga medis dalam memberikan pelayanan yang lebih baik dan aman kepada pasien.

**Kata Kunci:** Sistem Informasi, Rekam Medis, Anestesi, Operasi, Web-Based, PHP, MySQL, Rumah Sakit

---

## BAB I  
## PENDAHULUAN

### 1.1 Latar Belakang

Rumah sakit sebagai institusi pelayanan kesehatan memiliki peran vital dalam memberikan pelayanan medis kepada masyarakat. Salah satu aspek penting dalam pelayanan rumah sakit adalah tindakan operasi yang memerlukan prosedur anestesi. Anestesi merupakan tindakan medis yang bertujuan untuk menghilangkan rasa sakit dan kesadaran pasien selama proses operasi berlangsung. Dalam setiap tindakan anestesi, dokumentasi yang lengkap dan akurat sangat diperlukan untuk memastikan keselamatan pasien dan kualitas pelayanan medis.

Rekam medis anestesi mencakup berbagai informasi penting seperti data identitas pasien, riwayat kesehatan, hasil pemeriksaan pra-operasi, jenis anestesi yang digunakan, obat-obatan yang diberikan, monitoring tanda-tanda vital selama operasi, serta kondisi pasien pasca operasi. Dokumentasi ini tidak hanya berfungsi sebagai bukti pelayanan medis, tetapi juga sebagai alat komunikasi antar tenaga kesehatan, bahan evaluasi kualitas pelayanan, dan dokumen legal yang dapat digunakan untuk keperluan hukum.

Rumah Sakit Josaturu Bedah sebagai rumah sakit yang fokus pada tindakan bedah, melakukan berbagai jenis operasi setiap harinya. Proses pencatatan rekam medis anestesi yang masih dilakukan secara manual menggunakan formulir kertas menimbulkan berbagai permasalahan operasional. Beberapa permasalahan yang sering dihadapi antara lain:

1. **Kesulitan dalam Penyimpanan dan Pencarian Data**  
   Dokumen rekam medis dalam bentuk kertas memerlukan ruang penyimpanan yang besar dan sistem pengarsipan yang terorganisir. Pencarian data pasien lama membutuhkan waktu yang lama karena harus mencari secara manual di antara tumpukan dokumen.

2. **Risiko Kehilangan dan Kerusakan Dokumen**  
   Dokumen kertas rentan terhadap kerusakan fisik seperti robek, luntur, atau hilang. Kehilangan dokumen rekam medis dapat berdampak serius pada kontinuitas pelayanan pasien dan berpotensi menimbulkan masalah hukum.

3. **Ketidakefisienan Waktu Pengisian**  
   Pengisian formulir secara manual membutuhkan waktu yang cukup lama, terutama untuk formulir yang kompleks seperti catatan sedasi dan anestesi yang memerlukan pencatatan vital sign secara berkala. Hal ini dapat mengurangi waktu tenaga medis untuk fokus pada pelayanan pasien.

4. **Kesulitan dalam Monitoring dan Evaluasi**  
   Data dalam bentuk kertas sulit untuk dianalisis secara agregat. Proses evaluasi kualitas pelayanan dan pembuatan laporan statistik memerlukan waktu dan tenaga yang besar karena harus mengumpulkan dan mengolah data secara manual.

5. **Risiko Kesalahan Pencatatan**  
   Tulisan tangan yang tidak jelas atau kesalahan dalam pengisian formulir dapat menyebabkan kesalahan interpretasi data yang berpotensi membahayakan keselamatan pasien.

6. **Keterbatasan Akses Data**  
   Dokumen kertas hanya dapat diakses oleh satu orang pada satu waktu dan hanya dapat diakses di lokasi penyimpanan dokumen. Hal ini menyulitkan koordinasi antar tim medis yang memerlukan akses simultan terhadap data pasien.

Perkembangan teknologi informasi dan komunikasi saat ini memberikan solusi untuk mengatasi permasalahan-permasalahan tersebut melalui digitalisasi sistem rekam medis. Sistem informasi berbasis web memungkinkan pengelolaan data rekam medis secara digital dengan berbagai keunggulan seperti kemudahan akses, keamanan data yang lebih baik, efisiensi waktu, dan kemampuan untuk mengintegrasikan berbagai proses bisnis rumah sakit.

Berdasarkan permasalahan yang ada, penelitian ini bertujuan untuk merancang dan membangun Sistem Informasi Manajemen Rekam Medis Anestesi berbasis web di Rumah Sakit Josaturu Bedah. Sistem ini diharapkan dapat membantu tenaga medis dalam mengelola seluruh proses dokumentasi anestesi mulai dari konsultasi pra-operasi, persiapan operasi, pelaksanaan anestesi dengan monitoring vital sign, hingga pemulihan pasca operasi secara digital, terintegrasi, dan efisien.

### 1.2 Rumusan Masalah

Berdasarkan latar belakang yang telah diuraikan, rumusan masalah dalam penelitian ini adalah:

1. Bagaimana merancang sistem informasi manajemen rekam medis anestesi yang sesuai dengan kebutuhan dan alur kerja di Rumah Sakit Josaturu Bedah?

2. Bagaimana membangun sistem informasi berbasis web yang dapat mengelola seluruh proses dokumentasi anestesi mulai dari konsultasi pra-operasi hingga pemulihan pasca operasi?

3. Bagaimana mengimplementasikan fitur-fitur yang mendukung efisiensi kerja tenaga medis seperti autosave, validasi data, dan export laporan PDF?

4. Bagaimana menguji dan mengevaluasi fungsionalitas sistem untuk memastikan sistem dapat berjalan dengan baik dan memenuhi kebutuhan pengguna?

### 1.3 Batasan Masalah

Agar penelitian ini lebih fokus dan terarah, maka ditetapkan batasan masalah sebagai berikut:

1. Sistem yang dibangun fokus pada pengelolaan rekam medis anestesi dan tidak mencakup sistem informasi rumah sakit secara keseluruhan seperti sistem farmasi, laboratorium, atau radiologi.

2. Sistem mencakup modul-modul: manajemen data pasien, booking operasi, konsultasi anestesi, informed consent anestesi, checklist keselamatan operasi, checklist persiapan operasi, catatan sedasi dan anestesi (termasuk vital sign), dan catatan kamar pemulihan.

3. Sistem dibangun menggunakan teknologi web dengan bahasa pemrograman PHP, database MySQL, dan tidak menggunakan framework PHP seperti Laravel atau CodeIgniter.

4. Sistem tidak mencakup integrasi dengan perangkat medis (medical devices) untuk pengambilan data vital sign secara otomatis, melainkan input data dilakukan secara manual oleh tenaga medis.

5. Pengujian sistem dilakukan dengan metode black box testing untuk menguji fungsionalitas sistem dan tidak mencakup pengujian performa atau load testing.

6. Sistem dikembangkan untuk digunakan di lingkungan internal rumah sakit (intranet) dan tidak mencakup fitur akses untuk pasien atau keluarga pasien.

### 1.4 Tujuan Penelitian

Tujuan dari penelitian ini adalah:

1. Merancang sistem informasi manajemen rekam medis anestesi yang sesuai dengan kebutuhan dan alur kerja di Rumah Sakit Josaturu Bedah.

2. Membangun sistem informasi berbasis web yang dapat mengelola seluruh proses dokumentasi anestesi secara digital dan terintegrasi.

3. Mengimplementasikan fitur-fitur yang mendukung efisiensi kerja tenaga medis seperti autosave, validasi data, pencarian data, dan export laporan dalam format PDF.

4. Menguji dan mengevaluasi fungsionalitas sistem untuk memastikan sistem dapat berjalan dengan baik dan memenuhi kebutuhan pengguna.

5. Menghasilkan sistem yang dapat meningkatkan efisiensi, akurasi, dan keamanan dalam pengelolaan rekam medis anestesi di Rumah Sakit Josaturu Bedah.

### 1.5 Manfaat Penelitian

Penelitian ini diharapkan dapat memberikan manfaat sebagai berikut:

#### 1.5.1 Manfaat Teoritis

1. Memberikan kontribusi dalam pengembangan ilmu pengetahuan di bidang sistem informasi kesehatan, khususnya sistem informasi rekam medis anestesi.

2. Menjadi referensi bagi penelitian selanjutnya yang berkaitan dengan pengembangan sistem informasi rumah sakit atau sistem informasi kesehatan.

3. Memberikan gambaran implementasi teknologi informasi dalam meningkatkan kualitas pelayanan kesehatan di rumah sakit.

#### 1.5.2 Manfaat Praktis

**Bagi Rumah Sakit Josaturu Bedah:**

1. Meningkatkan efisiensi operasional dalam pengelolaan rekam medis anestesi dengan mengurangi penggunaan dokumen kertas.

2. Meningkatkan keamanan dan integritas data rekam medis pasien melalui sistem penyimpanan digital yang terstruktur.

3. Memudahkan proses pencarian dan akses data pasien sehingga dapat meningkatkan kecepatan pelayanan.

4. Memudahkan proses monitoring, evaluasi, dan pembuatan laporan untuk keperluan manajemen dan akreditasi rumah sakit.

**Bagi Tenaga Medis (Dokter dan Perawat):**

1. Mengurangi beban kerja administratif dalam pengisian formulir rekam medis secara manual.

2. Meminimalisir kesalahan pencatatan melalui fitur validasi dan autosave.

3. Memudahkan akses terhadap data pasien dari berbagai lokasi di rumah sakit.

4. Meningkatkan koordinasi antar tim medis melalui sistem yang terintegrasi.

**Bagi Pasien:**

1. Meningkatkan keselamatan pasien melalui dokumentasi yang lebih akurat dan lengkap.

2. Meningkatkan kualitas pelayanan melalui proses yang lebih efisien dan terorganisir.

3. Mengurangi waktu tunggu karena proses administrasi yang lebih cepat.

**Bagi Peneliti:**

1. Menambah pengalaman dan pengetahuan dalam merancang dan membangun sistem informasi kesehatan.

2. Mengaplikasikan teori dan konsep yang telah dipelajari dalam perkuliahan ke dalam proyek nyata.

3. Mengembangkan kemampuan problem solving dalam mengatasi permasalahan teknis selama pengembangan sistem.

### 1.6 Metodologi Penelitian

Metodologi penelitian yang digunakan dalam pengembangan sistem ini adalah metode waterfall (air terjun) yang terdiri dari beberapa tahapan sebagai berikut:

#### 1.6.1 Analisis Kebutuhan (Requirements Analysis)

Pada tahap ini dilakukan pengumpulan data dan analisis kebutuhan sistem melalui:

1. **Observasi:** Melakukan pengamatan langsung terhadap proses pencatatan rekam medis anestesi yang sedang berjalan di Rumah Sakit Josaturu Bedah.

2. **Wawancara:** Melakukan wawancara dengan dokter anestesi, perawat anestesi, dan staf administrasi untuk memahami kebutuhan dan permasalahan yang dihadapi.

3. **Studi Literatur:** Mempelajari literatur, jurnal, dan standar rekam medis anestesi untuk memahami best practices dalam dokumentasi anestesi.

4. **Analisis Dokumen:** Menganalisis formulir-formulir rekam medis anestesi yang sudah ada untuk dijadikan acuan dalam perancangan sistem.

#### 1.6.2 Desain Sistem (System Design)

Pada tahap ini dilakukan perancangan sistem yang meliputi:

1. **Perancangan Database:** Merancang struktur database dengan membuat Entity Relationship Diagram (ERD) dan normalisasi tabel.

2. **Perancangan Antarmuka (Interface):** Merancang tampilan antarmuka pengguna (user interface) yang user-friendly dan sesuai dengan alur kerja tenaga medis.

3. **Perancangan Proses Bisnis:** Merancang alur proses bisnis sistem dengan membuat flowchart dan Data Flow Diagram (DFD).

4. **Perancangan Arsitektur Sistem:** Merancang arsitektur sistem secara keseluruhan termasuk struktur folder, modul-modul, dan integrasi antar modul.

#### 1.6.3 Implementasi (Implementation)

Pada tahap ini dilakukan pembangunan sistem berdasarkan desain yang telah dibuat dengan menggunakan:

1. **Bahasa Pemrograman:** PHP untuk server-side programming dan JavaScript untuk client-side programming.

2. **Database:** MySQL untuk penyimpanan data.

3. **Framework CSS:** Bootstrap dan custom CSS untuk tampilan antarmuka yang responsif.

4. **Library:** TCPDF untuk generate laporan PDF, Font Awesome untuk icon, dan library JavaScript lainnya untuk meningkatkan user experience.

#### 1.6.4 Pengujian (Testing)

Pada tahap ini dilakukan pengujian sistem untuk memastikan sistem berfungsi dengan baik. Metode pengujian yang digunakan adalah:

1. **Black Box Testing:** Menguji fungsionalitas sistem tanpa melihat struktur internal kode program.

2. **User Acceptance Testing (UAT):** Melibatkan pengguna akhir (dokter dan perawat) untuk menguji sistem dan memberikan feedback.

#### 1.6.5 Pemeliharaan (Maintenance)

Pada tahap ini dilakukan pemeliharaan sistem berupa perbaikan bug, penambahan fitur, dan optimasi performa berdasarkan feedback dari pengguna.

### 1.7 Sistematika Penulisan

Sistematika penulisan skripsi ini disusun sebagai berikut:

**BAB I PENDAHULUAN**

Bab ini berisi latar belakang masalah, rumusan masalah, batasan masalah, tujuan penelitian, manfaat penelitian, metodologi penelitian, dan sistematika penulisan.

**BAB II LANDASAN TEORI**

Bab ini berisi teori-teori yang menjadi dasar dalam penelitian dan pengembangan sistem, meliputi: konsep sistem informasi, rekam medis, anestesi, metodologi pengembangan sistem, teknologi yang digunakan (PHP, MySQL, HTML, CSS, JavaScript), dan penelitian terkait.

**BAB III ANALISIS DAN PERANCANGAN SISTEM**

Bab ini berisi analisis sistem yang sedang berjalan, analisis kebutuhan sistem, perancangan database (ERD, normalisasi), perancangan proses bisnis (flowchart, DFD), perancangan antarmuka pengguna, dan perancangan arsitektur sistem.

**BAB IV IMPLEMENTASI DAN PENGUJIAN**

Bab ini berisi implementasi sistem berdasarkan perancangan yang telah dibuat, penjelasan fitur-fitur sistem, pengujian sistem dengan metode black box testing, dan analisis hasil pengujian.

**BAB V PENUTUP**

Bab ini berisi kesimpulan dari hasil penelitian dan saran untuk pengembangan sistem di masa mendatang.

---

**DAFTAR PUSTAKA**

**LAMPIRAN**

---

**Catatan:**
- Dokumen ini merupakan draft awal untuk Abstrak dan BAB I
- Silakan sesuaikan dengan format dan ketentuan dari institusi pendidikan Anda
- Lengkapi bagian yang masih kosong seperti nama, NIM, pembimbing, dll.
- Tambahkan referensi dan kutipan yang sesuai dengan standar penulisan ilmiah
