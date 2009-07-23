<?php

include 'Formatter.php';


$f = new Formatter;

$eid = $_GET['eid'];
$raw = $_GET['raw'];

if ($eid) {
	print $f->getByEid($eid);
} else if ($raw) {
	print $f->getRawByEid($raw);
} else {
	print "<html><body><h1>click an EID to see citations</h1>";
	foreach ($f->getEids() as $eid) {
		print "<p><a href=\"index.php?eid=$eid\">$eid</a> (<a href=\"index.php?raw=$eid\">raw</a>)</p>";
	}
		print "</body></html>";
}
