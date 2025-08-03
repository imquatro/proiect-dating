<?php
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare('UPDATE users SET last_active = NOW() WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Ignorăm erorile de actualizare a activității
    }
}