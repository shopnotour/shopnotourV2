# Shopno Tour V2 — Local Setup Guide

## Requirements
- PHP 8.1+ (XAMPP recommended)
- Composer
- MySQL
- Git

---

## Setup Steps

### 1. Clone the repository
```bash
git clone https://github.com/shopnotour/shopnotourV2.git
cd shopnotourV2
```

### 2. Set PHP path (if needed)
If `php --version` shows error, run this in PowerShell:
```powershell
$env:PATH = "C:\xampp\php;" + $env:PATH
php --version
```

### 3. Install PHP dependencies
```powershell
composer install
```

### 4. Create storage directories
```powershell
mkdir storage\framework\sessions, storage\framework\cache\data, storage\framework\views, storage\logs, storage\app\public
```

### 5. Environment setup
```powershell
cp .env.example .env
php artisan key:generate
```

### 6. Configure `.env` file
Edit `.env` and set your database credentials:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=

BC_ACTIVE_THEME=GoTrip
```

### 7. Import the database
Import the provided `.sql` file into your MySQL using phpMyAdmin or terminal:
```bash
mysql -u root -p your_database_name < database.sql
```

### 8. Extract uploads
Download `uploads.zip` from shared Google Drive and extract into `public/` folder:
```powershell
Expand-Archive -Path public\uploads.zip -DestinationPath public\
```

### 9. Mark as installed
```powershell
echo $null > storage\installed
```

### 10. Set storage permissions
```powershell
$folders = @("storage", "bootstrap\cache")
foreach ($folder in $folders) {
    $acl = Get-Acl $folder
    $rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
        "Everyone", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
    )
    $acl.SetAccessRule($rule)
    Set-Acl $folder $acl
}
```

### 11. Storage link
```powershell
php artisan storage:link
```

### 12. Clear cache
```powershell
php artisan optimize:clear
```

### 13. Run the project
```powershell
php artisan serve
```

Visit: http://127.0.0.1:8000

---

## Notes
- Never commit your `.env` file
- `vendor/` folder is not in git — always run `composer install` after cloning
- `public/uploads/` is not in git — download from shared Google Drive link
- `storage/installed` file must exist for the app to work properly
- `BC_ACTIVE_THEME=GoTrip` must be set in `.env`
- If images are not showing, make sure `uploads.zip` is extracted properly
