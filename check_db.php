<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>PHP Environment Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>Database Drivers Check</h3>";
if (extension_loaded('pdo_pgsql')) {
    echo "<span style='color: green; font-weight: bold;'>✔ pdo_pgsql (PostgreSQL PDO) is ENABLED!</span><br>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ pdo_pgsql (PostgreSQL PDO) is DISABLED/NOT INSTALLED!</span><br>";
}

if (extension_loaded('pgsql')) {
    echo "<span style='color: green; font-weight: bold;'>✔ pgsql (PostgreSQL) is ENABLED!</span><br>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ pgsql (PostgreSQL) is DISABLED/NOT INSTALLED!</span><br>";
}

echo "<br>Available PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "<br>";
