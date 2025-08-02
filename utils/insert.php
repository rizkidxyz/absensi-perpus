<?php
function insert($table, $data) {
    global $pdo;
    // Ambil nama kolom dan placeholder untuk prepared statement
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    // SQL insert-nya
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    // Prepare dan eksekusi
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data); // true jika berhasil
}
?>