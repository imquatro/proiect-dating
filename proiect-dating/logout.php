<?php
// logout.php

require_once 'includes_core/session.php';

// Dacă utilizatorul a folosit autentificare GitHub, eliminăm tokenul
if (isset($_SESSION['github_access_token'])) {
    unset($_SESSION['github_access_token']);
    // Aici poți adăuga un redirect către GitHub pentru a gestiona logout-ul complet dacă este necesar
}

// Distrugem toate datele sesiunii
session_unset();
session_destroy();

// Redirectăm utilizatorul către pagina de login
header('Location: login.php');
exit();
?>
