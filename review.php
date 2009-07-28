<?php

include 'Formatter.php';
include 'db.php';

$f = new Formatter;

$type = $_GET['type'];

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


if ($type) {
	$sql = "SELECT * FROM publication where type = ? LIMIT 500";
	$sth = $db->prepare($sql);
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$sth->execute(array($type));
	while ($row = $sth->fetch()) {
		$output .=  '<p>'.$f->getHtmlCitation($row)."</p>";
	}
} else {
	foreach ($spec['type'] as $type => $label) {
		$sql = "SELECT count(*) FROM publication where type = '$type'";
		$count = $db->query($sql)->fetchColumn();
		$output .= "<p><a href=\"review.php?type=$type\">$label ($count)</a></p>";
	}
}


print "<html><body>$output</body></html>";
