<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$ssi = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');

$smcFunc['db_add_column'](
  '{db_prefix}members',
  array(
    'name' => 'steam_id',
    'size' => 64,
    'type' => 'varchar',
    'null' => true
  )
);

$smcFunc['db_add_index'](
  '{db_prefix}members',
  array(
    'type' => 'unique',
    'columns' => array('steam_id')
  )
);

add_integration_function('integrate_pre_include', '$sourcedir/SteamAuth.php');
add_integration_function('integrate_load_theme', 'steam_auth_load_theme');
add_integration_function('integrate_general_mod_settings', 'steam_auth_general_mod_settings');

if (!empty($ssi))
	echo 'Database installation complete!';

?>