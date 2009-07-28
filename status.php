<?php

include 'Formatter.php';
include 'db.php';

$f = new Formatter;

$status = $_GET['status'];

$output = '';

$spec = 
	array(
		'status' => array(
			'PB' => 'published',
			'IP' => 'in press',
			'RR' => 'revise and resubmit',
			'SB' => 'submitted',
			'PR' => 'in prep',
		),
		'authorship' => array(
			'S' => 'sole author',
			'C' => 'co-author',
			'E' => 'editor',
			'T' => 'translator',
		),
		'type' => array(
			'AR' => 'article',
			'BK' => 'book',
			'BC' => 'book chapter',
			'MO' => 'monograph',
			'TR' => 'technical report',
			'AB' => 'abstract',
			'PR' => 'proceeding',
			'OP' => 'other publications ',
			'BR' => 'book review',
			'NP' => 'newspaper',
		),
	);


if ($status) {
	if ('NONE' == $status) {
		$sql = "SELECT * FROM publication where status NOT IN ('PB','IP','RR','SB','PR') LIMIT 1000";
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute();
		while ($row = $sth->fetch()) {
			$output .=  '<p>'.$f->getHtmlCitation($row)."</p>";
		}
	}
	$sql = "SELECT * FROM publication where status = ? LIMIT 500";
	$sth = $db->prepare($sql);
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$sth->execute(array($status));
	while ($row = $sth->fetch()) {
		$output .=  '<p>'.$f->getHtmlCitation($row)."</p>";
	}
} else {
	foreach ($spec['status'] as $status => $label) {
		$sql = "SELECT count(*) FROM publication where status = '$status'";
		$count = $db->query($sql)->fetchColumn();
		$output .= "<p><a href=\"status.php?status=$status\">$label ($count)</a></p>";
	}
	$sql = "SELECT count(*) FROM publication where status NOT IN ('PB','IP','RR','SB','PR')";
	$count = $db->query($sql)->fetchColumn();
	$output .= "<p><a href=\"status.php?status=NONE\">NO STATUS ($count)</a></p>";
}


print "<html><body>$output</body></html>";
