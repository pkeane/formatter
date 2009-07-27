<?php

include 'Formatter.php';
include 'db.php';

$f = new Formatter;

$spec = 
	array(
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
		'status' => array(
			'PB' => 'published',
			'IP' => 'in press',
			'RR' => 'revise and resubmit',
			'SB' => 'submitted',
			'PR' => 'in prep',
		)
	);

foreach ($spec as $col => $set) {
	print "\n-----------------------------\n";
	foreach ($set as $key => $val) {
		$sql = "SELECT count(*) FROM publication where $col = '$key'";
		print $db->query($sql)->fetchColumn()." $col of $val\n";
		//print $sth->fetchColumn()." $val\n";
	}
}
