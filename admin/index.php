<?php
/**
 * Redirect old /admin/ to /wp-admin/
 * This file provides backward compatibility for old bookmarks/links
 */
header('Location: /ae-admin/', true, 301);
exit;

