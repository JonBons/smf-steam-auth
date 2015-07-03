<?php
// Version: 1.0: SteamAuth.php
// Licence: ISC

if (!defined('SMF')) die('Hacking attempt...');

function steam_auth_load_theme()
{
	global $context, $modSettings, $smcFunc, $sourcedir, $user_settings, $txt;
	
	if ($context['current_action'] == 'login' && !empty($modSettings['steam_auth_api_key']))
	{
		loadLanguage('SteamAuth');
		try
		{
			require_once ($sourcedir . '/openid.php');

			$returnURL = $_SERVER['SERVER_NAME'];
			$returnURL = $returnURL . ( ($_SERVER['SERVER_PORT'] == "80") ? "" : (":" . $_SERVER['SERVER_PORT']) );

			$openid = new LightOpenID($returnURL);
			if (!$openid->mode)
			{
				if (isset($_GET['steam']))
				{
					// This is forcing english because it has a weird habit of selecting a random language otherwise
					$openid->identity = 'http://steamcommunity.com/openid/?l=english';
					header('Location: ' . $openid->authUrl());
				}
			}
			elseif ($openid->mode == 'cancel') $context['login_errors'] = array(
				$txt['steam_auth_canceled']
			);
			else
			{
				if ($openid->validate())
				{
					$id = $openid->identity;

					$ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
					preg_match($ptn, $id, $matches);
					$steamid = $matches[1];

					$request = $smcFunc['db_query']('', '
						SELECT passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt,
							openid_uri, passwd_flood
						FROM {db_prefix}members
						WHERE steam_id = {string:steamid}
						LIMIT 1', array(
						'steamid' => $steamid,
					));

					$user_settings = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if (empty($user_settings))
					{

						$context['login_errors'] = array(
							$txt['steam_auth_invalid_user']
						);

					} else {

						require_once($sourcedir . '/LogInOut.php');

						// Check their activation status.
						if (!checkActivation())
							return;

						DoLogin();

					};
				}
				else 
				{  
					$context['login_errors'] = array(
						$txt['error_occured']
					);
				}
			}
		}

		catch(ErrorException $e)
		{
			$context['login_errors'] = array(
				$e->getMessage()
			);
		}
	} else if ($context['current_action'] == 'profile' && !empty($modSettings['steam_auth_api_key'])) {
		if (isset($_GET['area'])) {
			if ($_GET['area'] == "account" && $context['user']['is_logged']) {

				$member_id = "";

				if (!empty($_GET['u'])) {
					// Make sure the id is a number and not "I like trying to hack the database".
					$_GET['u'] = (int) $_GET['u'];

					$member_id = $_GET['u'];	

				} else {

					$member_id = $context['user']['id'];

				};

				$request = $smcFunc['db_query']('', '
						SELECT id_member, id_group, steam_id
						FROM {db_prefix}members
						WHERE id_member = {int:memberid}
						LIMIT 1', array(
						'memberid' => $member_id,
					));

				$user_settings = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				if (empty($user_settings['steam_id'])) {

					loadLanguage('SteamAuth');

					require_once ($sourcedir . '/openid.php');

					$returnURL = $_SERVER['SERVER_NAME'];
					$returnURL = $returnURL . ( ($_SERVER['SERVER_PORT'] == "80") ? "" : (":" . $_SERVER['SERVER_PORT']) );

					$openid = new LightOpenID($returnURL);
					if (!$openid->mode)
					{
						if (isset($_GET['steam']))
						{
							// This is forcing english because it has a weird habit of selecting a random language otherwise
							$openid->identity = 'http://steamcommunity.com/openid/?l=english';
							header('Location: ' . $openid->authUrl());
						}
					}
					elseif ($openid->mode == 'cancel') $context['auth_errors'] = array(
						$txt['steam_auth_canceled']
					);
					else
					{
						if ($openid->validate())
						{
							$id = $openid->identity;

							$ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
							preg_match($ptn, $id, $matches);
							$steamid = $matches[1];

							$request = $smcFunc['db_query']('', '
								SELECT id_member, id_group, steam_id
								FROM {db_prefix}members
								WHERE steam_id = {string:steamid}
								LIMIT 1', array(
								'steamid' => $steamid,
							));

							$user_settings = $smcFunc['db_fetch_assoc']($request);
							$smcFunc['db_free_result']($request);

							if (empty($user_settings))
							{

								require_once($sourcedir . '/Subs.php');

								updateMemberData($member_id, array('steam_id' => $steamid));

							} else {

								$context['auth_errors'] = array(
									$txt['steam_auth_existing_user']
								);

							};
						}
						else 
						{  
							$context['auth_errors'] = array(
								$txt['error_occured']
							);
						}
					};

				} else {

					if (isset($_GET['resetsteamid'])) {

						require_once($sourcedir . '/Subs.php');

						if (allowedTo('admin_forum'))
						{

							// You must input a valid user....
							if (empty($_GET['u']) || loadMemberData((int) $_GET['u']) === false)
								return;

							// Make sure the id is a number and not "I like trying to hack the database".
							$_GET['u'] = (int) $_GET['u'];
							// Load the member's contextual information!
							if (!loadMemberContext($_GET['u']))
								return;

							// Okay, I admit it, I'm lazy.  Stupid $_GET['u'] is long and hard to type.
							$profile = &$memberContext[$_GET['u']];

							updateMemberData($profile['id'], array('steam_id' => 'NULL'));

						};

					};

				};
			}
		}
	}
}

function steam_auth_general_mod_settings(&$config_vars)
{
	global $txt;
	loadLanguage('SteamAuth');
	$config_vars = array_merge($config_vars, array(
		'',
		array(
			'text',
			'steam_auth_api_key',
			80,
			'postinput' => $txt['steam_auth_api_key_link']
		) ,
	));
}

?>
