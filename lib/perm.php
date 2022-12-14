<?php

$powerlevels = [
	-1 => 'Banned',
	//0  => 'Guest',
	1  => 'Member',
	2  => 'Moderator',
	3  => 'Administrator',
	4  => 'Root',
];

function powIdToName($id) {
	return match ($id) {
		-1 => 'Banned',
		0  => 'Guest',
		1  => 'Member',
		2  => 'Moderator',
		3  => 'Administrator',
		4  => 'Root',
		default => 'N/A'
	};
}

function powIdToColour($id) {
	return match ($id) {
		-1 => '858585',
		0  => 'ffffff',
		1  => '839ef9',
		2  => '4fe840',
		3  => 'aa3c3c',
		4  => 'ffd21b',
		default => ''
	};
}

function powNameToId($id) {
	return match ($id) {
		'Banned'		=> -1,
		'Guest'			=> 0,
		'Member'		=> 1,
		'Moderator'		=> 2,
		'Administrator'	=> 3,
		'Root'			=> 4
	};
}

function needsLogin() {
	global $log;
	if (!$log) {
		error('403', 'This page requires login. <p><a href="login.php">Login</a></p>');
		die();
	}
}
