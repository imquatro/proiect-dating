<?php
function ensureBarnSchema(PDO $db)
{
    $tableExists = $db->query("SHOW TABLES LIKE 'user_barn'")->rowCount() > 0;
    if ($tableExists) {
        $colExists = $db->query("SHOW COLUMNS FROM user_barn LIKE 'slot_number'")->rowCount() > 0;
        if (!$colExists) {
            $db->exec("CREATE TABLE user_barn_new (
                user_id INT NOT NULL,
                slot_number INT NOT NULL,
                item_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 0,
                PRIMARY KEY (user_id, slot_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $rows = $db->query("SELECT user_id, item_id, quantity FROM user_barn")->fetchAll(PDO::FETCH_ASSOC);
            $ins = $db->prepare("INSERT INTO user_barn_new (user_id, slot_number, item_id, quantity) VALUES (?, ?, ?, ?)");
            foreach ($rows as $r) {
                $qty = (int)$r['quantity'];
                $prodStmt = $db->prepare("SELECT production FROM farm_items WHERE id = ?");
                $prodStmt->execute([$r['item_id']]);
                $prod = (int)$prodStmt->fetchColumn();
                $cap = ($prod === 1) ? 1 : 1000;
                $slot = 1;
                while ($qty > 0) {
                    $add = min($cap, $qty);
                    $ins->execute([$r['user_id'], $slot, $r['item_id'], $add]);
                    $qty -= $add;
                    $slot++;
                }
            }
            $db->exec("DROP TABLE user_barn");
            $db->exec("RENAME TABLE user_barn_new TO user_barn");
        }
    } else {
        $db->exec("CREATE TABLE user_barn (
            user_id INT NOT NULL,
            slot_number INT NOT NULL,
            item_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            PRIMARY KEY (user_id, slot_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }
}