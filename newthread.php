<?php
require('lib/common.php');

needsLogin();

$id = $_GET['id'] ?? null;
$action = $_POST['action'] ?? null;
$title = $_POST['title'] ?? '';
$message = $_POST['message'] ?? '';

$forum = fetch("SELECT id, title, minthread FROM forums WHERE id = ?", [$id]);

if (!$forum)
	error('404', "Forum does not exist.");
if ($forum['minthread'] > $userdata['powerlevel'])
	error('403', "You have no permissions to create threads in this forum.");

$error = [];

if ($action == 'Submit') {
	if (strlen(trim($title)) < 15)
		$error[] = "You need to enter a longer title.";

	if (strlen(trim($message)) == 0)
		$error[] = "You need to enter a message to your thread.";

	if ($userdata['lastpost'] > time() - 30 && $userdata['powerlevel'] < 4)
		$error[] = "Please wait at least 30 seconds before starting a new thread.";

	if ($error == []) {
		query("INSERT INTO threads (forum, title, user, lastdate, lastuser) VALUES (?,?,?,?,?)",
			[$id, $title, $userdata['id'], time(), $userdata['id']]);

		$tid = insertId();
		query("INSERT INTO posts (user, thread, date, ip) VALUES (?,?,?,?)",
			[$userdata['id'], $tid, time(), $ipaddr]);

		$pid = insertId();
		query("INSERT INTO poststext (id, text) VALUES (?,?)", [$pid, $message]);

		query("UPDATE threads SET lastid = ? WHERE id = ?", [$pid, $tid]);

		query("UPDATE forums SET
					threads = threads + 1, posts = posts + 1,
					lastdate = ?, lastuser = ?, lastid = ?
				WHERE id = ?",
			[time(), $userdata['id'], $pid, $id]);

		query("UPDATE users SET posts = posts + 1, threads = threads + 1, lastpost = ? WHERE id = ?",
			[time(), $userdata['id']]);

		redirect("thread.php?id=$tid");
	}
} elseif ($action == 'Preview') {
	$post['date'] = time();
	$post['text'] = $message;
	foreach ($userdata as $field => $val)
		$post['u_'.$field] = $val;
	$post['headerbar'] = 'Post preview';
}

$breadcrumb = [
	'forum.php?id='.$forum['id'] => $forum['title']];

echo twigloader()->render('newthread.twig', [
	'breadcrumb' => $breadcrumb,
	'threadtitle' => $title,
	'message' => $message,
	'error' => $error,
	'post' => $post ?? null,
	'action' => $action
]);
