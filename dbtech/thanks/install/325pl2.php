<?php
self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "dbtech_thanks_entrycache` CHANGE `data` `data` MEDIUMBLOB NULL DEFAULT NULL");
self::report('Altered Table', 'dbtech_thanks_entrycache');

define('CP_REDIRECT', 'thanks.php?do=finalise&version=325');
define('DISABLE_PRODUCT_REDIRECT', true);