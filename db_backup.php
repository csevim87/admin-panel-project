<?php
session_start();
require_once '../../include/config.php';
require_once '../../include/db.php';
require_once '../../include/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_yetki'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// Veritabanı yedekleme fonksiyonu
function backup_database($db, $tables = '*') {
    // Yedekleme için kullanılacak SQL
    $return = '';
    
    // Tüm tablolari al
    if ($tables == '*') {
        $tables = [];
        $tables_sorgu = $db->query('SHOW TABLES');
        while ($row = $tables_sorgu->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }
    
    // Her tablo için
    foreach ($tables as $table) {
        $num_rows = $db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        
        $return .= "DROP TABLE IF EXISTS {$table};\n";
        
        $create_table_sorgu = $db->query("SHOW CREATE TABLE {$table}");
        $create_table = $create_table_sorgu->fetch(PDO::FETCH_NUM);
        $return .= $create_table[1] . ";\n\n";
        
        if ($num_rows > 0) {
            $columns_sorgu = $db->query("SHOW COLUMNS FROM {$table}");
            $columns = [];
            while ($column = $columns_sorgu->fetch(PDO::FETCH_NUM)) {
                $columns[] = $column[0];
            }
            
            $data_sorgu = $db->query("SELECT * FROM {$table}");
            while ($data = $data_sorgu->fetch(PDO::FETCH_NUM)) {
                $return .= "INSERT INTO {$table} VALUES (";
                $i = 0;
                foreach ($data as $value) {
                    if (isset($value)) {
                        $value = addslashes($value);
                        $value = str_replace("\n", "\\n", $value);
                        $return .= '"' . $value . '"';
                    } else {
                        $return .= 'NULL';
                    }
                    
                    if ($i < (count($data) - 1)) {
                        $return .= ',';
                    }
                    $i++;
                }
                $return .= ");\n";
            }
        }
        
        $return .= "\n\n";
    }
    
    return $return;
}

// Yedekleme işlemi
$backup = backup_database($db);

// Dosya adını oluştur
$filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';

// İndirme başlıklarını gönder
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($backup));
header('Connection: close');

// Yedeklemeyi gönder
echo $backup;
exit;
?>