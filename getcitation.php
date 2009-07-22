<?php


$dbh = new PDO('mysql:host=mysql.laits.utexas.edu;dbname=pkeane_bibcite','bibcite_user','bibcite_user');
$sql = "SELECT * FROM publication";
$sth = $dbh->prepare($sql);
$sth->setFetchMode(PDO::FETCH_ASSOC);
$sth->execute();

/**
id
eid
name
department
title
book_title
type
status
journal_or_publisher_name
city_of_publication
referee_tier
date_published
authorship
volume
number
month_season
page_from
page_to
pub_url
co_author_1
co_author_2
co_author_3
co_author_4
co_author_5
co_author_6
co_author_7
co_author_8
co_author_9
co_author_10
editor_1
editor_2
editor_3
editor_4
notes
station
assembled_citation
display_citation
file
_updated
_updatedby

Authorship
	a.       S = sole author
	b.      C = co-author
	c.       E = editor
	d.      T = translator

	Type
	a.       AR = article
	b.      BK = book
	c.       BC = book chapter
	d.      MO = monograph
	e.      TR = technical report
	f.        AB = abstract
	g.       PR = proceeding
	h.      OP = other publications (museum catalogs, commentaries [unless
	refereed], columns, conference papers [unless refereed], external
	reviews, editing of film scripts, abstracts)
	i.         BR = book review
	j.        NP = newspaper

	Status
	a.       PB = published
	b.      IP = in press
	c.       RR = revise and resubmit
	d.      SB = submitted
	e.      PR = in prep

 */

function dirify($str)
{
	$str = strtolower(preg_replace('/[^a-zA-Z0-9_-]/','_',trim($str)));
	return preg_replace('/__*/','_',$str);
}

function getAuthorList($row) {
	$authors[] = $row['name'];
	$coauthors = array(
		'co_author_1',
		'co_author_2',
		'co_author_3',
		'co_author_4',
		'co_author_5',
		'co_author_6',
		'co_author_7',
		'co_author_8',
		'co_author_9',
		'co_author_10',
	);

	foreach ($coauthors as $ca_key) {
		if ($row[$ca_key]) {
			$authors[] = $row[$ca_key];
		}
	}

	$formatted_auths = array();
	foreach ($authors as $auth) {
		$initials = array();
		$set = explode(', ',$auth);
		$last = trim(array_shift($set));
		print "LAST: ".$last."\n";
		$names = explode(' ',$set[0]);

		foreach ($names as $name) {
			$initial = substr($name,0,1);
			$initials[] = $initial.'.';
		}
		$new_auth = $last.','.join('',$initials);
		$formatted_auths[] = $new_auth;
	}
	$last_author = array_pop($formatted_auths);
	return join(', ',$formatted_auths)." & ".$last_author;
}

$fields = array();

$row = $sth->fetch();
foreach ($row as $k => $v) {
	$fields[] = $k;
}

$sets = array();

while ($row = $sth->fetch()) {
	if (!isset($row['eid']) || !$row['eid']) {
		$row['eid'] = dirify($row['name']);
	}

	print "\n\n CITATION ({$row['eid']}):\n\n";
	print getAuthorList($row);
	unset($cos);
}

