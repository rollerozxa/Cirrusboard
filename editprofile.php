<?php
require('lib/common.php');

needsLogin();

// TODO: only current user can be edited, make admins able to edit others
$user = fetch("SELECT * FROM users WHERE id = ?", [$userdata['id']]);

if (isset($_POST['action'])) {

	$error = [];

	// avatars
	$fname = $_FILES['avatar'] ?? null;
	if ($fname && $fname['size'] > 0) {
		$types = ['png','jpeg','jpg','gif'];
		$res = getimagesize($fname['tmp_name']);

		if (!in_array(str_replace('image/','',$res['mime']),$types))
			$error[] = "Avatar: Invalid file type.";

		if ($res[0] > 180 || $res[1] > 180)
			$error[] = "Avatar: The image is too big. Please resize it to be under 180x180.";

		if ($fname['size'] > 100*1024)
			$error[] = "Avatar: The image filesize is too big.";

		if ($error == []) {
			if (move_uploaded_file($fname['tmp_name'], 'userpic/'.$userdata['id']))
				$fields['avatar'] = 1;
			else
				trigger_error("Avatar uploading broken, check userpic/ permissions", E_USER_ERROR);
		}
	}
	if (isset($_POST['picturedel'])) $avatar = 0;

	//Validate birthday values.
	$bday = (int)($_POST['birthD'] ?? null);
	$bmonth = (int)($_POST['birthM'] ?? null);
	$byear = (int)($_POST['birthY'] ?? null);

	if ($bday > 0 && $bmonth > 0 && $byear > 0 && $bmonth <= 12 && $bday <= 31 && $byear <= 3000) // Y-m-d
		$birthday = $byear.'-'.str_pad($bmonth, 2, "0", STR_PAD_LEFT).'-'.str_pad($bday, 2, "0", STR_PAD_LEFT);

	// Password change
	$pass = $_POST['password'] ?? null;
	$pass2 = $_POST['password2'] ?? null;
	if ($pass) {
		if ($pass == $pass2) {
			if (strlen($pass) < 15) {
				$newtoken = bin2hex(random_bytes(32));
				setcookie('token', $newtoken, 2147483647);
			} else
				$error[] = "Password: Password is too short (needs to be at least 15 characters).";
		} else
			$error[] = "Password: The new passwords don't match.";
	}

	if ($error == []) {
		// Temp variables for dynamic query construction.
		$fieldquery = '';
		$placeholders = [];

		$fields = [
			'location'	=> $_POST['location'] ?: null,
			'bio'		=> $_POST['bio'] ?: null,
			'email'		=> $_POST['email'] ?: null,
			'showemail'	=> isset($_POST['showemail']) ? 1 : 0,
			'theme'		=> $_POST['theme'] != $config['defaulttheme'] ? $_POST['theme'] : null,
			'timezone'	=> $_POST['timezone'] != $config['defaulttimezone'] ? $_POST['timezone'] : null,
			'ppp'		=> $_POST['ppp'],
			'tpp'		=> $_POST['tpp'],
		];

		if (isset($avatar))
			$fields['avatar'] = $avatar;

		if (isset($birthday))
			$fields['birthday'] = $birthday;

		if ($pass) {
			$fields['password'] = password_hash($pass, PASSWORD_DEFAULT);
			$fields['token'] = $newtoken;
		}

		if ($userdata['powerlevel'] > 2)
			$fields['title'] = $_POST['title'];

		// Construct a query containing all fields.
		foreach ($fields as $fieldk => $fieldv) {
			if ($fieldquery) $fieldquery .= ',';
			$fieldquery .= $fieldk.'=?';
			$placeholders[] = $fieldv;
		}

		// 100% safe from SQL injection because no arbitrary user input is ever put directly
		// into the query, rather it is passed as a prepared statement placeholder.
		$placeholders[] = $user['id'];
		query("UPDATE users SET $fieldquery WHERE id = ?", $placeholders);

		redirect('profile.php?id='.$user['id']);
	} else {
		foreach ($_POST as $k => $v)
			$user[$k] = $v;
		$user['birthday'] = $birthday;
	}
}


$timezones = [];
foreach (timezone_identifiers_list() as $tz)
	$timezones[$tz] = $tz;

$user['timezone'] = $user['timezone'] ?: $config['defaulttimezone'];

if ($user['birthday']) // Y-m-d format
	$birthday = explode('-', $user['birthday']);

echo twigloader()->render('editprofile.twig', [
	'user' => $user,
	'timezones' => $timezones,
	'birthday' => $birthday ?? null,
	'error' => $error ?? null
]);
