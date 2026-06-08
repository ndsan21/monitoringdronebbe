<?php


// Trik Jitu Bypass storage:link untuk cPanel
$targetFolder = __DIR__ . '/../storage/app/public';
$linkFolder = __DIR__ . '/storage';



if (symlink($targetFolder, $linkFolder)) {
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
    echo "<h1 style='color: #10B981;'>MANTAP BOS! Storage Link Berhasil Dibuat! 🚀</h1>";
    echo "<p style='color: #6B7280;'>Folder upload foto kamu sekarang sudah aktif sempurna.</p>";
    echo "</div>";
} else {
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
    echo "<h1 style='color: #EF4444;'>Gagal total Bos, cek permission atau jalurnya! ❌</h1>";
    echo "</div>";
}