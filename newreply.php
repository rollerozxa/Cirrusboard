<?php
require('lib/common.php');

needsLogin();

$id = $_GET['id'] ?? null;
$action = $_POST['action'] ?? null;
$message = trim($_POST['message'] ?? '');

$thread = fetch("SELECT t.*, f.title f_title, f.minreply f_minreply
	FROM threads t LEFT JOIN forums f ON f.id = t.forum
	WHERE t.id = ?", [$id]);

if (!$thread)
	error('404', "Thread does not exist.");
if ($thread['f_minreply'] > $userdata['powerlevel'])
	error('403', "You have no permissions to create posts in this forum.");
if ($thread['closed'] && $userdata['powerlevel'] < 2)
	error('403', "You can't post in closed threads!");

$error = [];

if ($action == "Submit") {
	$lastpost = fetch("SELECT id, user, date FROM posts WHERE thread = ? ORDER BY id DESC LIMIT 1", [$thread['id']]);
	if ($lastpost['user'] == $userdata['id'] && $lastpost['date'] >= (time() - 60*60*12))
		$error[] = "You can't double post until it's been at least 12 hours!";

	if (strlen($message) == 0)
		$error[] = "Your post is empty. Enter a message and try again.";

	if ($error == []) {
		query("UPDATE users SET posts = posts + 1, lastpost = ? WHERE id = ?", [time(), $userdata['id']]);
		query("INSERT INTO posts (user, thread, date, ip) VALUES (?,?,?,?)",
			[$userdata['id'], $id, time(), $ipaddr]);

		$postid = insertid();
		query("INSERT INTO poststext (id, text) VALUES (?,?)",
			[$postid, $message]);

		query("UPDATE threads SET posts = posts + 1, lastdate = ?, lastuser = ?, lastid = ? WHERE id = ?",
			[time(), $userdata['id'], $postid, $id]);

		query("UPDATE forums SET posts = posts + 1, lastdate = ?, lastuser = ?, lastid = ? WHERE id = ?",
			[time(), $userdata['id'], $postid, $thread['forum']]);

		redirect("thread.php?pid=$postid#$postid");
	}
} elseif ($action == 'Preview') {
	$post['date'] = time();
	$post['text'] = $message;
	foreach ($userdata as $field => $val)
		$post['u_'.$field] = $val;
	$post['headerbar'] = 'Post preview';
}

// Append quoted message to the newreply box, to reply to other messages.
$pid = $_GET['pid'] ?? 0;
if ($pid) {
	$qpost = fetch("SELECT u.name name, p.user, pt.text, f.id fid, p.thread, f.minread
				FROM posts p
				LEFT JOIN poststext pt ON p.id = pt.id AND p.revision = pt.revision
				LEFT JOIN users u ON p.user = u.id
				LEFT JOIN threads t ON t.id = p.thread
				LEFT JOIN forums f ON f.id = t.forum
				WHERE p.id = ?",
			[$pid]);

	//does the user have reading access to the quoted post?
	if ($userdata['powerlevel'] < $qpost['minread'])
		$qpost['name'] = $qpost['text'] = '[redacted]';

	$message = sprintf(
		'[quote="%s" id="%s"]%s[/quote]'.PHP_EOL.PHP_EOL,
	$qpost['name'], $pid, $qpost['text']);
}

$breadcrumb = [
	'forum.php?id='.$thread['forum'] => $thread['f_title'],
	'thread.php?id='.$thread['id'] => $thread['title']
];

echo twigloader()->render('newreply.twig', [
	'id' => $id,
	'breadcrumb' => $breadcrumb,
	'thread' => $thread,
	'message' => $message,
	'error' => $error,
	'post' => $post ?? null,
	'action' => $action
]);

