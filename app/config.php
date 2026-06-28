<?php
$host = 'bsfqntekdjqnvdoah3qc-mysql.services.clever-cloud.com'; 
$db   = 'bsfqntekdjqnvdoah3qc';                                 
$user = 'uw51gprlhmkkw8r8';                                 
$pass = 'LUEGjpHB8XoVOkhvi9EH';                            
$port = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Koneksi berhasil!
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
