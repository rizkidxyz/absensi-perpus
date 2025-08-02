<?php
function update($tabel, $data, $where) {
    global $pdo; // Pastikan $pdo adalah koneksi PDO aktif

    if (empty($data) || empty($where)) return false;

    $set = [];
    $params = [];

    foreach ($data as $key => $value) {
        $set[] = "`$key` = :set_$key";
        $params["set_$key"] = $value;
    }

    $conditions = [];
    foreach ($where as $key => $value) {
        $conditions[] = "`$key` = :where_$key";
        $params["where_$key"] = $value;
    }

    $sql = "UPDATE `$tabel` SET " . implode(", ", $set) . " WHERE " . implode(" AND ", $conditions);

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}