<?php
/**
 * Etsy OAuth Callback Handler
 */

// 1. NUR HTTPS erlauben (in Production)
if (empty($_SERVER['HTTPS']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Automatisch zu HTTPS umleiten
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        http_response_code(400);
        echo 'HTTPS required';
        exit;
    }
}

// 2. Nur GET requests von Etsy erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}

// 3. Query parameters von Etsy
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

// 4. Deine WooCommerce Plugin URL (MUSS in deinem Plugin konfiguriert werden)
$woocommerce_callback_url = 'https://deine-woocommerce-site.com/wp-admin/admin.php?page=easy-etsy-auth';

// 5. Bei Fehler von Etsy
if (!empty($error)) {
    $error_description = $_GET['error_description'] ?? 'Unknown error';
    
    // Einfache Weiterleitung mit Fehler
    $redirect_url = $woocommerce_callback_url . '&error=' . urlencode($error) . 
                   '&error_description=' . urlencode($error_description);
    
    header('Location: ' . $redirect_url);
    exit;
}

// 6. Erfolg: Code von Etsy erhalten
if (!empty($code)) {
    // STATE Parameter validieren (wichtig für Sicherheit!)
    // Dieser State sollte von deinem WooCommerce Plugin kommen
    if (empty($state)) {
        // Ohne State ist es unsicher
        header('Location: ' . $woocommerce_callback_url . '&error=invalid_state');
        exit;
    }
    
    // Einfache Weiterleitung an dein WooCommerce Plugin
    $redirect_url = $woocommerce_callback_url . '&code=' . urlencode($code) . 
                   '&state=' . urlencode($state);
    
    header('Location: ' . $redirect_url);
    exit;
}

// 7. Keine gültigen Parameter
header('Location: ' . $woocommerce_callback_url . '&error=invalid_request');
exit;
?>
