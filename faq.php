<?php
require('lib/common.php');

// Powerlevel colours
$nctable = '';
foreach ($powerlevels as $id => $title)
	$nctable .= sprintf('<td class="b n1" width="140"><b><span style="color:#%s">%s</span></b></td>', powIdToColour($id), $title);

if (file_exists('conf/faq.php'))
	require('conf/faq.php');
else
	require('conf/faq.sample.php');

$twig = twigloader();

echo $twig->render('faq.twig', [
	'faq' => $faq
]);