# Script: fix_paths.ps1
# Memperbaiki semua path di file-file dalam subfolder anggota

function Fix-Paths {
    param(
        [string]$FilePath,
        [string[]]$Replacements
    )
    $content = Get-Content $FilePath -Raw -Encoding UTF8
    for ($i = 0; $i -lt $Replacements.Length; $i += 2) {
        $from = $Replacements[$i]
        $to   = $Replacements[$i+1]
        $content = $content.Replace($from, $to)
    }
    Set-Content $FilePath $content -Encoding UTF8 -NoNewline
    Write-Host "✅ Fixed: $FilePath"
}

# ──────────────────────────────────────────────────────────────
# Sidebar nav links yang SAMA di semua halaman admin
# ──────────────────────────────────────────────────────────────
$sidebarFixes = @(
    # config
    'include "config/koneksi.php";',        'include "../config/koneksi.php";'
    "include 'config/koneksi.php';",        "include '../config/koneksi.php';"
    'require "config/koneksi.php";',        'require "../config/koneksi.php";'
    "require 'config/koneksi.php';",        "require '../config/koneksi.php';"
    'require_once "config/koneksi.php";',   'require_once "../config/koneksi.php";'
    "require_once 'config/koneksi.php';",   "require_once '../config/koneksi.php';"

    # manifest & sw
    'href="manifest.json"',                 'href="../manifest.json"'
    "register('sw.js')",                    "register('../sw.js')"
    'src="sw.js"',                          'src="../sw.js"'

    # Login/Logout redirect
    'header("Location: login.php")',        'header("Location: ../desta/login.php")'
    "header('Location: login.php')",        "header('Location: ../desta/login.php')"
    'header("Location: logout.php")',       'header("Location: ../desta/logout.php")'
    "header('Location: logout.php')",       "header('Location: ../desta/logout.php')"

    # Dashboard
    'header("Location: index.php")',        'header("Location: ../veve/index.php")'
    "header('Location: index.php')",        "header('Location: ../veve/index.php')"
    'header("Location: siswa_dashboard.php")', 'header("Location: ../veve/siswa_dashboard.php")'
    "header('Location: siswa_dashboard.php')", "header('Location: ../veve/siswa_dashboard.php')"

    # Sidebar links (href)
    'href="index.php"',                     'href="../veve/index.php"'
    'href="kelas.php"',                     'href="../dwi/kelas.php"'
    'href="siswa.php"',                     'href="../dwi/siswa.php"'
    'href="rekap.php"',                     'href="../hasbi/rekap.php"'
    'href="database_console.php"',          'href="../aldi/database_console.php"'
    'href="absensi.php"',                   'href="../fiis/absensi.php"'
    'href="register.php"',                  'href="../fiis/register.php"'
    'href="logout.php"',                    'href="../desta/logout.php"'
    'href="login.php"',                     'href="../desta/login.php"'
    'href="kelas_detail.php',               'href="../dwi/kelas_detail.php'
    'href="siswa_dashboard.php"',           'href="../veve/siswa_dashboard.php"'
    'href="register_admin.php"',            'href="../desta/register_admin.php"'
    'href="tescamera.php"',                 'href="../fiis/tescamera.php"'

    # Action links
    "action='login.php'",                   "action='../desta/login.php'"
    'action="login.php"',                   'action="../desta/login.php"'
    "action='register.php'",                "action='../fiis/register.php'"
    'action="register.php"',                'action="../fiis/register.php"'
    "action='register_admin.php'",          "action='../desta/register_admin.php'"
    'action="register_admin.php"',          'action="../desta/register_admin.php"'

    # JS fetch/XHR paths
    "'api/run_migration.php'",              "'../api/run_migration.php'"
    '"api/run_migration.php"',              '"../api/run_migration.php"'
    "'api/absensi_proses.php'",             "'../api/absensi_proses.php'"
    '"api/absensi_proses.php"',             '"../api/absensi_proses.php"'
    "'api/register_face.php'",              "'../api/register_face.php'"
    '"api/register_face.php"',              '"../api/register_face.php"'
    "'api/get_kelas.php'",                  "'../api/get_kelas.php'"
    '"api/get_kelas.php"',                  '"../api/get_kelas.php"'
    "'api/",                                "'../api/"
    '"api/',                                '"../api/'

    # JS Models path
    'models/',                              '../models/'
    'js/',                                  '../js/'
    'icons/',                               '../icons/'
)

# ──────────────────────────────────────────────────────────────
# Terapkan ke semua file di masing-masing folder
# ──────────────────────────────────────────────────────────────
$folders = @('aldi', 'desta', 'dwi', 'fiis', 'veve', 'hasbi')
foreach ($folder in $folders) {
    $files = Get-ChildItem -Path ".\$folder" -Filter "*.php"
    foreach ($file in $files) {
        Fix-Paths -FilePath $file.FullName -Replacements $sidebarFixes
    }
}

Write-Host ""
Write-Host "✅ SEMUA PATH SUDAH DIPERBARUI!" -ForegroundColor Green
