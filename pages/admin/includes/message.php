<?php
if (!function_exists('show_flash_messages')) {
    function show_flash_messages()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $keys = ['user_msg', 'session_msg', 'subject_msg', 'message', 'msg', 'flash_msg'];
        foreach ($keys as $k) {
            if (!empty($_SESSION[$k])) {
                $m = $_SESSION[$k];
                unset($_SESSION[$k]);
                $type = htmlspecialchars($m['type'] ?? 'info');
                $text = htmlspecialchars($m['text'] ?? '');
                echo "<div class=\"alert alert-{$type} auto-dismiss\">{$text}</div>";
            }
        }
    }
}

?>
