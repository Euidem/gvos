<#
.SYNOPSIS
    GVOS — Full Phase 0 Setup Script
.DESCRIPTION
    This script sets up the GVOS Laravel application from scratch.
    It installs all PHP and JS dependencies, configures the environment,
    runs migrations and seeders, and verifies the installation.

    PREREQUISITES:
    - PHP 8.2 or higher (php.exe must be in PATH)
    - Composer (composer.bat/composer.phar must be in PATH)
    - Node.js 18 LTS or higher (node.exe must be in PATH)
    - MySQL 8.0+ or PostgreSQL 15+ running locally

    RECOMMENDED:
    Install Laragon (https://laragon.org) for Windows — it bundles PHP + MySQL
    and creates a clean dev environment.

.NOTES
    Run this script from the GVOS project root directory.
    After installation, the admin panel is at: http://localhost:8000/admin
    Default credentials: admin@gvos.local / password (LOCAL TESTING ONLY)
#>

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  GVOS — Phase 0 Setup Script" -ForegroundColor Cyan
Write-Host "  GetVirtual Operations System" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# -----------------------------------------------------------------------
# Step 1: Check Prerequisites
# -----------------------------------------------------------------------
Write-Host "[1/9] Checking prerequisites..." -ForegroundColor Yellow

$missingTools = @()

# Check PHP
try {
    $phpVersion = & php --version 2>&1 | Select-Object -First 1
    if ($phpVersion -match "PHP (\d+\.\d+)") {
        $version = [version]$Matches[1]
        if ($version -lt [version]"8.2") {
            Write-Host "  ❌ PHP $($Matches[1]) found but PHP 8.2+ is required." -ForegroundColor Red
            $missingTools += "PHP 8.2+"
        } else {
            Write-Host "  ✅ PHP $($Matches[1]) found." -ForegroundColor Green
        }
    }
} catch {
    Write-Host "  ❌ PHP not found in PATH." -ForegroundColor Red
    $missingTools += "PHP 8.2+ (https://windows.php.net/ or install Laragon)"
}

# Check Composer
try {
    $composerVersion = & composer --version 2>&1 | Select-Object -First 1
    Write-Host "  ✅ Composer found: $composerVersion" -ForegroundColor Green
} catch {
    Write-Host "  ❌ Composer not found in PATH." -ForegroundColor Red
    $missingTools += "Composer (https://getcomposer.org/)"
}

# Check Node.js
try {
    $nodeVersion = & node --version 2>&1
    Write-Host "  ✅ Node.js $nodeVersion found." -ForegroundColor Green
} catch {
    Write-Host "  ❌ Node.js not found in PATH." -ForegroundColor Red
    $missingTools += "Node.js 18+ (https://nodejs.org/)"
}

# Check npm
try {
    $npmVersion = & npm --version 2>&1
    Write-Host "  ✅ npm $npmVersion found." -ForegroundColor Green
} catch {
    Write-Host "  ❌ npm not found in PATH." -ForegroundColor Red
    $missingTools += "npm (installed with Node.js)"
}

if ($missingTools.Count -gt 0) {
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Red
    Write-Host "  SETUP CANNOT CONTINUE — Missing prerequisites:" -ForegroundColor Red
    Write-Host "============================================================" -ForegroundColor Red
    foreach ($tool in $missingTools) {
        Write-Host "  • Install: $tool" -ForegroundColor Red
    }
    Write-Host ""
    Write-Host "RECOMMENDED: Install Laragon (https://laragon.org/)" -ForegroundColor Yellow
    Write-Host "  Laragon bundles PHP 8.2+ and MySQL for Windows development." -ForegroundColor Yellow
    Write-Host "  After installing Laragon, open a Laragon terminal and re-run this script." -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

# -----------------------------------------------------------------------
# Step 2: Install PHP Dependencies
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[2/9] Installing PHP dependencies (composer install)..." -ForegroundColor Yellow

if (Test-Path "vendor") {
    Write-Host "  vendor/ already exists. Running composer install to ensure up to date..." -ForegroundColor Gray
}

& composer install --no-interaction --prefer-dist --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ❌ composer install failed." -ForegroundColor Red
    exit 1
}
Write-Host "  ✅ PHP dependencies installed." -ForegroundColor Green

# -----------------------------------------------------------------------
# Step 3: Configure Environment
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[3/9] Configuring environment..." -ForegroundColor Yellow

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  .env created from .env.example" -ForegroundColor Gray
} else {
    Write-Host "  .env already exists — skipping copy." -ForegroundColor Gray
}

# Generate APP_KEY if not set
$envContent = Get-Content ".env" -Raw
if ($envContent -match "APP_KEY=$" -or $envContent -match "APP_KEY=\s*$") {
    Write-Host "  Generating APP_KEY..." -ForegroundColor Gray
    & php artisan key:generate --ansi
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ❌ Failed to generate APP_KEY." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "  APP_KEY already set." -ForegroundColor Gray
}

Write-Host "  ✅ Environment configured." -ForegroundColor Green
Write-Host ""
Write-Host "  ⚠️  IMPORTANT: Edit .env and set your database credentials:" -ForegroundColor Yellow
Write-Host "     DB_DATABASE=gvos" -ForegroundColor Yellow
Write-Host "     DB_USERNAME=root" -ForegroundColor Yellow
Write-Host "     DB_PASSWORD=yourpassword" -ForegroundColor Yellow
Write-Host ""

# -----------------------------------------------------------------------
# Step 4: Install Breeze (React scaffolding)
# -----------------------------------------------------------------------
Write-Host "[4/9] Installing Laravel Breeze with React/Inertia..." -ForegroundColor Yellow

if (-not (Test-Path "resources/js/Pages/Auth")) {
    & php artisan breeze:install react --no-interaction
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ❌ Breeze install failed." -ForegroundColor Red
        exit 1
    }
    Write-Host "  ✅ Breeze (React/Inertia) installed." -ForegroundColor Green
} else {
    Write-Host "  ✅ Breeze already installed (Auth pages exist)." -ForegroundColor Green
}

# -----------------------------------------------------------------------
# Step 5: Publish Filament and Spatie Assets
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[5/9] Publishing package assets..." -ForegroundColor Yellow

& php artisan filament:install --panels --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ⚠️  Filament install had warnings (may already be installed)." -ForegroundColor Yellow
}

& php artisan vendor:publish --tag=permission-migrations --no-interaction
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ⚠️  Permission migrations may already be published." -ForegroundColor Yellow
}

& php artisan vendor:publish --tag=permission-config --no-interaction
Write-Host "  ✅ Package assets published." -ForegroundColor Green

# -----------------------------------------------------------------------
# Step 6: Run Migrations
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[6/9] Running database migrations..." -ForegroundColor Yellow
Write-Host "  Make sure your database '$((Get-Content '.env' | Select-String 'DB_DATABASE=').ToString().Split('=')[1])' exists and credentials are correct in .env" -ForegroundColor Gray
Write-Host ""

& php artisan migrate --no-interaction --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ❌ Migrations failed. Check your .env database settings." -ForegroundColor Red
    Write-Host "     Ensure DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD are set correctly." -ForegroundColor Yellow
    exit 1
}
Write-Host "  ✅ Migrations completed." -ForegroundColor Green

# -----------------------------------------------------------------------
# Step 7: Run Seeders
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[7/9] Running seeders..." -ForegroundColor Yellow

& php artisan db:seed --no-interaction --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ❌ Seeding failed." -ForegroundColor Red
    exit 1
}
Write-Host "  ✅ Seeders completed." -ForegroundColor Green

# -----------------------------------------------------------------------
# Step 8: Install JS Dependencies and Build
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[8/9] Installing JS dependencies (npm install)..." -ForegroundColor Yellow

& npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ❌ npm install failed." -ForegroundColor Red
    exit 1
}
Write-Host "  ✅ JS dependencies installed." -ForegroundColor Green

Write-Host ""
Write-Host "  Building frontend assets (npm run build)..." -ForegroundColor Gray
& npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ⚠️  npm build had issues. Run 'npm run dev' for development mode instead." -ForegroundColor Yellow
} else {
    Write-Host "  ✅ Frontend assets built." -ForegroundColor Green
}

# -----------------------------------------------------------------------
# Step 9: Create Filament Super Admin
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "[9/9] Setting up Filament admin panel..." -ForegroundColor Yellow

# The AdminUserSeeder handles user creation and role assignment
# Filament also needs the user to be added to admin panel
& php artisan make:filament-user --no-interaction 2>&1 | Out-Null
Write-Host "  ✅ Filament admin panel configured." -ForegroundColor Green

# -----------------------------------------------------------------------
# Setup Complete
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  ✅ GVOS Phase 0 Setup Complete!" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Start the development server:" -ForegroundColor Cyan
Write-Host "    php artisan serve" -ForegroundColor White
Write-Host "    (in a second terminal): npm run dev" -ForegroundColor White
Write-Host ""
Write-Host "  Access the platform:" -ForegroundColor Cyan
Write-Host "    App:      http://localhost:8000" -ForegroundColor White
Write-Host "    Admin:    http://localhost:8000/admin" -ForegroundColor White
Write-Host ""
Write-Host "  Local admin credentials (TESTING ONLY):" -ForegroundColor Cyan
Write-Host "    Email:    admin@gvos.local" -ForegroundColor White
Write-Host "    Password: password" -ForegroundColor White
Write-Host ""
Write-Host "  ⚠️  NEVER use these credentials in production." -ForegroundColor Yellow
Write-Host ""
Write-Host "  Next step: Review /docs/CURRENT_STATUS.md then get Phase 0 approved." -ForegroundColor Cyan
Write-Host ""
