<?php
function select($table, $where = [], $fetchAll = true, $orderBy = null, $limit = null, $customWhere = []) {
    global $pdo;

    $sql = "SELECT * FROM `$table`";
    $bindings = [];
    $conditions = [];
    $i = 0;

    // Handle kondisi normal (dengan atau tanpa operator)
    foreach ($where as $key => $value) {
        if (preg_match('/^(.+?)\s*(=|!=|<>|<|<=|>|>=|LIKE|NOT LIKE|IN|NOT IN|BETWEEN)$/i', $key, $matches)) {
            $column = $matches[1];
            $operator = strtoupper($matches[2]);

            if (in_array($operator, ['IN', 'NOT IN']) && is_array($value)) {
                $placeholders = [];
                foreach ($value as $val) {
                    $param = ":w{$i}";
                    $placeholders[] = $param;
                    $bindings[$param] = $val;
                    $i++;
                }
                $conditions[] = "`$column` $operator (" . implode(", ", $placeholders) . ")";
            } elseif ($operator === "BETWEEN" && is_array($value) && count($value) === 2) {
                $param1 = ":w{$i}"; $bindings[$param1] = $value[0]; $i++;
                $param2 = ":w{$i}"; $bindings[$param2] = $value[1]; $i++;
                $conditions[] = "`$column` BETWEEN $param1 AND $param2";
            } else {
                $param = ":w{$i}";
                $conditions[] = "`$column` $operator $param";
                $bindings[$param] = $value;
                $i++;
            }
        } else {
            $param = ":w{$i}";
            $conditions[] = "`$key` = $param";
            $bindings[$param] = $value;
            $i++;
        }
    }

    // Tambahan untuk kondisi WHERE manual dengan SQL fungsi (e.g. DATE(waktu_kunjung) = :tgl)
    foreach ($customWhere as $expr => $val) {
        $param = ":w{$i}";
        $conditions[] = "$expr = $param";
        $bindings[$param] = $val;
        $i++;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }

    if ($limit) {
        $sql .= " LIMIT $limit";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);

    return $fetchAll ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
}
