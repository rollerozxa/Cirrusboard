<?php
$host = '127.0.0.1';
$db   = 'cool_forum';
$user = '';
$pass = '';

$tplCache = 'templates/cache';
//$tplCache = '/tmp/cirrusboard';
$tplNoCache = false; // **DO NOT SET AS TRUE IN PROD - DEV ONLY**

// Customise your forum
$config['title'] = "Cool Forum";
$config['description'] = "A very cool forum.";
$config['logo'] = 'assets/logo_placeholder.png';

$config['newsid'] = -1; // Designates the id for your announcements forum, the latest post will be shown at the top of the index page.

$config['defaulttheme'] = "voxelmanip_retro";
$config['defaulttimezone'] = "Europe/Stockholm"; // Default timezone if people do not select their own.

// Allow HTML in posts? A reasonable attempt is done to scrub JavaScript and other dangerous HTML elements, but tread with caution.
$config['html'] = false;

// Uncomment to replace the footer with your own thing. We won't mind if you remove the credits from the footer!
// (As long as it remains in the /credits.php page and the LICENSE file is kept)
//$config['customfooter'] = <<<HTML
// (put some stuff inside here)
//HTML;

// List of smilies, if you want them.
$smilies = [
	//'-_-' => 'assets/smilies/annoyed.png',
];

// Sample post that is shown on profile pages.
$samplepost = <<<HTML
[b]This[/b] is a [i]sample message.[/i] It shows how [u]your posts[/u] will look on the board.
[quote=Anonymous][spoiler]Hello![/spoiler][/quote]
[code]if (true) {\r
	print "The world isn't broken.";\r
} else {\r
	print "Something is very wrong.";\r
}[/code]
[url=]Test Link. Ooh![/url]
HTML;
