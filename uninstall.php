<?php 
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) die();
delete_option('sktnurc_privtkey');
delete_option('sktnurc_publkey');
delete_option('sktnurc_lang');
delete_option('sktnurc_lang_set');
delete_option('sktnurc_reclocales_lang');
delete_option('sktnurc_theme');
delete_option('sktnurc_count');
delete_option('sktnurc_keysset');
delete_option('sktnurc_regbutton');
?>