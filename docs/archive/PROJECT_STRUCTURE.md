# 📁 SIMRS Josaturu Bedah - Project Structure

## 🏗️ Struktur Folder

```
SIMRS_JB/
├── assets/              # Asset files (CSS, JS, images)
├── config/              # Configuration files
├── docs/                # 📚 Documentation
│   ├── fixes/          # Bug fixes & solutions documentation
│   ├── debug/          # Debug guides & testing checklists
│   └── archive/        # Old documentation (archived)
├── includes/            # PHP includes (header, footer, etc.)
├── models/              # Database models
├── process/             # Form processing scripts
├── scripts/             # 🔧 Utility scripts for debugging
├── sql/                 # 🗄️ SQL scripts & database management
└── views/               # View files (forms, pages)
```

---

## 📂 Folder Details

### **`/assets/`** - Asset Files
- CSS stylesheets
- JavaScript files
- Images & icons
- Static resources

### **`/config/`** - Configuration
- Database configuration
- Application settings
- Environment variables

### **`/docs/`** - Documentation 📚
**Organized into 3 sub-folders:**

1. **`/docs/fixes/`** - Bug Fixes & Solutions
   - `PROBLEM_SOLVED.md` - Main reference untuk semua fix
   - `FINAL_FIX_SUMMARY.md` - Latest fix summary
   - Fix documentation untuk specific issues

2. **`/docs/debug/`** - Debug & Testing
   - `QUICK_TEST_CHECKLIST.md` - Testing checklist
   - Debug guides & troubleshooting
   - Step-by-step debugging instructions

3. **`/docs/archive/`** - Archived Documentation
   - Old documentation
   - Historical reference
   - Not for daily use

**See:** `/docs/README.md` for detailed documentation guide

### **`/includes/`** - PHP Includes
- `header.php` - Page header & navigation
- `footer.php` - Page footer
- Common PHP includes

### **`/models/`** - Database Models
- Database connection
- Data models
- Database utilities

### **`/process/`** - Form Processing
- `process-konsultasi-anestesi.php` - Konsultasi Anestesi form handler
- `process-informed-consent.php` - Informed Consent form handler
- Other form processors

### **`/scripts/`** - Utility Scripts 🔧
**Debugging & maintenance scripts:**
- `count_parameters.php` - Count query parameters
- `count_placeholders.php` - Count SQL placeholders
- `verify_execute_params.php` - Verify execute() array
- `compare_columns.php` - Compare columns

**See:** `/scripts/README.md` for usage guide

### **`/sql/`** - SQL Scripts 🗄️
**Database management:**
- `add_jenis_diagnosa_column.sql` - Add jenis_diagnosa column
- `check_column_exists.sql` - Check if column exists

**See:** `/sql/README.md` for SQL documentation

### **`/views/`** - View Files
- `form-konsultasi-anestesi.php` - Konsultasi Anestesi form
- `form-informed-consent-anestesi.php` - Informed Consent form
- Other view files

---

## 🔍 Quick Navigation

### **For Developers:**
- **New bug?** → Check `/docs/fixes/PROBLEM_SOLVED.md`
- **Need to debug?** → Use `/docs/debug/` guides
- **Need scripts?** → Check `/scripts/` folder
- **Database changes?** → Check `/sql/` folder

### **For Testing:**
- **Testing checklist** → `/docs/debug/QUICK_TEST_CHECKLIST.md`
- **Debug guide** → `/docs/debug/DEBUG_*.md`

### **For Reference:**
- **All fixes** → `/docs/fixes/`
- **Old docs** → `/docs/archive/`
- **SQL scripts** → `/sql/`

---

## 🎯 Common Tasks

### **1. Debugging Parameter Mismatch:**
```bash
cd c:\FOLDER RIZKI\SIMRS_JB
php scripts/count_parameters.php
php scripts/count_placeholders.php
php scripts/verify_execute_params.php
```

### **2. Adding New Column:**
1. Check `/sql/check_column_exists.sql`
2. Create ALTER TABLE script
3. Test in local database
4. Document in `/docs/fixes/`

### **3. Testing Before Deploy:**
1. Follow `/docs/debug/QUICK_TEST_CHECKLIST.md`
2. Test all forms
3. Verify database
4. Check error logs

### **4. Finding Solutions:**
1. Check `/docs/fixes/PROBLEM_SOLVED.md`
2. Search in `/docs/fixes/` for similar issues
3. Use `/docs/debug/` guides for troubleshooting

---

## 📝 File Organization Rules

### **Documentation:**
- ✅ Bug fixes → `/docs/fixes/`
- ✅ Debug guides → `/docs/debug/`
- ✅ Old docs → `/docs/archive/`
- ❌ Don't put docs in root folder

### **Scripts:**
- ✅ Utility scripts → `/scripts/`
- ✅ Add README entry for new scripts
- ❌ Don't put scripts in root folder

### **SQL:**
- ✅ SQL scripts → `/sql/`
- ✅ Document purpose in README
- ❌ Don't put SQL in root folder

### **Code:**
- ✅ Views → `/views/`
- ✅ Processors → `/process/`
- ✅ Models → `/models/`
- ✅ Includes → `/includes/`

---

## 🧹 Cleanup Guidelines

### **Monthly:**
- Review `/docs/archive/` - Remove outdated docs
- Review `/scripts/` - Remove unused scripts
- Update README files

### **Before Deploy:**
- Remove debug files
- Remove test scripts
- Clean up temporary files

### **After Major Changes:**
- Update documentation in `/docs/fixes/`
- Update README files
- Archive old documentation

---

## 🚀 Getting Started

### **For New Developers:**
1. Read this file (PROJECT_STRUCTURE.md)
2. Read `/docs/README.md`
3. Read `/docs/fixes/PROBLEM_SOLVED.md`
4. Explore `/scripts/` and `/sql/` folders

### **For Maintenance:**
1. Check `/docs/debug/QUICK_TEST_CHECKLIST.md`
2. Use `/scripts/` for debugging
3. Reference `/docs/fixes/` for solutions

### **For Troubleshooting:**
1. Check error logs
2. Use `/docs/debug/` guides
3. Run `/scripts/` for verification
4. Check `/docs/fixes/` for similar issues

---

## 📊 Project Statistics

**Total Folders:** 13  
**Documentation Files:** 24  
**Utility Scripts:** 7  
**SQL Scripts:** 2  

**Last Cleanup:** 2025-10-14  
**Last Updated:** 2025-10-14  

---

## 🔗 Important Links

- **Main Documentation:** `/docs/README.md`
- **Fix Reference:** `/docs/fixes/PROBLEM_SOLVED.md`
- **Testing Guide:** `/docs/debug/QUICK_TEST_CHECKLIST.md`
- **Scripts Guide:** `/scripts/README.md`
- **SQL Guide:** `/sql/README.md`

---

**Maintained By:** Development Team  
**Project:** SIMRS Josaturu Bedah  
**Version:** 1.0
