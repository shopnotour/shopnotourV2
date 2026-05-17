# Shopno Tour V2 — Local Setup Guide

## Requirements
- PHP 8.1+
- Composer
- MySQL
- Node.js & NPM

---

## Quick Setup (Windows)

Clone করার পর PowerShell এ run করুন:

```powershell
.\setup.ps1
```

তারপর:
1. `.env` file এ database credentials দিন
2. Database import করুন
3. `php artisan serve` চালান

---

## Manual Setup (Step by Step)

**1. Clone the repository**
```bash
git clone <repository-url>
cd shopnotourV2
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure `.env` file**

`.env` file খুলুন এবং database credentials দিন:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**5. Import the database**

phpMyAdmin দিয়ে অথবা terminal এ:
```bash
mysql -u root -p your_database_name < database.sql
```

**6. Storage permission & link**

PowerShell এ run করুন:
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

php artisan storage:link
```

**7. Install frontend dependencies**
```bash
npm install
npm run dev
```

**8. Run the project**
```bash
php artisan serve
```

Visit: http://127.0.0.1:8000

---

## Notes
- `.env` file কখনো git এ push করবেন না
- Clone করার পর সবসময় `composer install` চালাতে হবে (`vendor/` folder git এ নেই)
- Database `.sql` file আলাদাভাবে share করা হবে
- Storage folder এ write permission থাকতে হবে
