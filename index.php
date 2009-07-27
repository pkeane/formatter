<?php

include 'Formatter.php';
include 'db.php';

$f = new Formatter;


$eid = $_GET['eid'];
$html = $_GET['html'];
$raw = $_GET['raw'];

$output = '';

if ($raw) {
	$sql = "SELECT * FROM publication where eid = ? ORDER BY date_published";
	$sth = $db->prepare($sql);
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$sth->execute(array($eid));
	while ($row = $sth->fetch()) {
		$output .=  '<p>'.$f->getData($row)."</p>";
	}
} else if ($eid) {
	$sql = "SELECT * FROM publication where eid = ? ORDER BY date_published";
	$sth = $db->prepare($sql);
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$sth->execute(array($eid));
	while ($row = $sth->fetch()) {
		if ($html) {
			$output .=  '<p>'.$f->getHtmlCitation($row)."</p>";
		} else {
			$output .=  '<p>'.$f->getCitation($row)."</p>";
		}
	}
} else {
	$output = "<h1>click an EID to see citations</h1>";
	$sql = "SELECT eid FROM publication GROUP BY eid ORDER BY eid";
	$sth = $db->prepare($sql);
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	$sth->execute();
	while ($row = $sth->fetch()) {
		$eid = $row['eid'];
		$output .= "<p><a href=\"index.php?eid=$eid\">$eid</a> (<a href=\"index.php?eid=$eid&html=1\">HTML</a>) (<a href=\"index.php?eid=$eid&raw=1\">raw</a>)</p>";
	}
}

print "<html><body>$output</body></html>";
