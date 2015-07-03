# Steam Authentication
Login using your Steam account

Fork of live627's smf-steam-auth modifcation made into working order and changed behaviour to no longer create users if user with steam id was not found.

## Changes to fork
- Added unique constrained steam_id column to members table for tracking data.
- Made the loading of the Steam login button be handled by Profile and Login templates via modification.xml.
- Added support for allowing an admin to reset (remove) the Steam account linked to a user.

## Reqiurements
SMF 2.0.x

This mod will allow users to login via the Steam OpenID integration (OpenID is a core SMF feature, btw, though it's buggy).

##Foreword

Thanks goes to:
- JTX for the original steam openid script (http://pastebin.com/6vZT4RhD)
- The LightopenID library (http://gitorious.org/lightopenid)
 * The library requires PHP >= 5.1.2 with curl or http/https stream wrappers enabled.
 * @author Mewp
 * @copyright Copyright (c) 2010, Mewp
 * @license http://www.opensource.org/licenses/mit-license.php MIT


## License
[ISC](http://opensource.org/licenses/ISC)
