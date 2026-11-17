<?php
/**
 * Prints <script>PL.viewer = {...}</script> so the placeholder API knows who is logged in.
 * Only the sample-data placeholders read it; once the real API is connected the server
 * takes the user from the session, so this tag can stay (harmless) or be removed.
 * Call after includes/auth.php, before the pl-api.js script tag.
 */
function render_viewer_script(): void
{
    $user = current_user();
    $viewer = [
        'user_id'    => (int) ($user['user_id'] ?? 0),
        'role'       => (string) ($user['role'] ?? ''),
        'first_name' => (string) ($user['first_name'] ?? ''),
    ];
    echo '<script>window.PL = window.PL || {}; PL.viewer = '
        . json_encode($viewer, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        . ";</script>\n";
}
