<?php

class Formatter 
{

	public $citations_by_eid;

	public function __construct() 
	{
		$data_array = unserialize(file_get_contents('data'));

		$citations_by_eid = array();
		foreach ($data_array as $citation) {
			if (!isset($citation['eid'])) {
				$citation['eid'] = dirify($citation['name']);
			}
			if (!isset($citations_by_eid[$citation['eid']])) {
				$citations_by_eid[$citation['eid']] = array();
				$citations_by_eid[$citation['eid']][] = $citation;
			} else {
				$citations_by_eid[$citation['eid']][] = $citation;
			}
		}
		$this->citations_by_eid = $citations_by_eid;
	}

	public static function sortByYear($b,$a)
	{
		$year_a = substr($a['date_published'],0,4);
		$year_b = substr($b['date_published'],0,4);
		if ($year_a == $year_b) {
			return 0;
		}
		return ($year_a < $year_b) ? -1 : 1;
	}


/*
$dbh = new PDO('mysql:host=x;dbname=x','x','x');
$sql = "SELECT * FROM publication";
$sth = $dbh->prepare($sql);
$sth->setFetchMode(PDO::FETCH_ASSOC);
$sth->execute();
$data_array = $sth->fetchAll();
 */


//	print getByEid($citations_by_eid,'ja8294');

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
		$keys = array(
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

		foreach ($keys as $ca_key) {
			if ($row[$ca_key]) {
				$authors[] = $row[$ca_key];
			}
		}

		$formatted_auths = array();
		foreach ($authors as $auth) {
			$initials = array();
			$set = explode(', ',$auth);
			$last = trim(array_shift($set));
			$names = explode(' ',$set[0]);

			foreach ($names as $name) {
				$initial = substr($name,0,1);
				$initials[] = $initial.'.';
			}
			$new_auth = $last.', '.join('',$initials);
			$formatted_auths[] = $new_auth;
		}
		$last_author = array_pop($formatted_auths);
		if (count($formatted_auths)) {
			return join(', ',$formatted_auths)." & ".$last_author;
		} else {
			//only one author
			return $last_author;
		}
	}

	function getDateString($row) {
		$ts = strtotime($row['date_published']);

		switch ($row['type']) {
		case 'AR': //article
			$disp = date('Y, F',$ts);
			break;
		case 'BK': //book
			$disp = date('Y',$ts);
			break;
		case 'BC': //book chapter
			$disp = date('Y',$ts);
			break;
		case 'MO': //monograph
			$disp = date('Y',$ts);
			break;
		case 'TR': //technical report
			$disp = date('Y',$ts);
			break;
		case 'AB': //abstract
			$disp = date('Y',$ts);
			break;
		case 'PR': //proceeding
			$disp = date('Y',$ts);
			break;
		case 'OP': //other publication
			$disp = date('Y',$ts);
			break;
		case 'BR': //book review
			$disp = date('Y',$ts);
			break;
		case 'NP': //newspaper
			$disp = date('Y',$ts);
			break;
		default:
			$disp = date('Y',$ts);
		}
		return "($disp)"; 
	}

	function getEditorList($row) {
		$editors = array();
		$keys = array(
			'editor_1',
			'editor_2',
			'editor_3',
			'editor_4',
		);

		foreach ($keys as $ekey) {
			if ($row[$ekey]) {
				$editors[] = $row[$ekey];
			}
		}

		$formatted = array();
		foreach ($editors as $ed) {
			$initials = array();
			$set = explode(', ',$ed);
			$last = trim(array_shift($set));
			if (count($set)) {
				$names = explode(' ',$set[0]);

				foreach ($names as $name) {
					$initial = substr($name,0,1);
					$initials[] = $initial.'.';
				}
			}
			$new_auth = join('',$initials).' '.$last;
			$formatted[] = $new_auth;
		}
		$last_ed = array_pop($formatted);
		if (count($formatted)) {
			return join(', ',$formatted)." & ".$last_ed;
		} else {
			//only one author
			return $last_ed;
		}
	}

	function getWorkTitle($row) {
		switch ($row['type']) {
		case 'AR': //article
			return $row['journal_or_publisher_name'].',';
		case 'BK': //book
			return $row['book_title'];
		case 'BC': //book chapter
			$pp = $this->getPages($row);
			if ($pp) {
				$pp = ' (pp.'.$pp.')';
			}
			$ed = $this->getEditorList($row);
			if ($ed) {
				if (strpos($ed,'&')) {
					return "In ".$this->getEditorList($row).' (Eds.), '.$row['book_title'].$pp.'.';
				} else {
					return "In ".$this->getEditorList($row).' (Ed.), '.$row['book_title'].$pp.'.';
				}
			} else {
				return "In ".$row['book_title'].$pp.'.';
			}
		case 'MO': //monograph
			return $row['book_title'];
		case 'TR': //technical report
		case 'AB': //abstract
		case 'PR': //proceeding
		case 'OP': //other publication
		case 'BR': //book review
		case 'NP': //newspaper
		default:
			return $row['journal_or_publisher_name'];
		}
	}

	function getPubInfo($row) {
		switch ($row['type']) {
		case 'AR': //article
			$vol = $row['volume'];
			$num = $row['number'];
			if ($num) {
				$vol = "$vol($num)";
			}
			return $vol.', '.$this->getPages($row).'.';
		case 'BK': //book
		case 'BC': //book chapter
			return $row['city_of_publication'].': '.$row['journal_or_publisher_name'].'.';
		case 'MO': //monograph
		case 'TR': //technical report
		case 'AB': //abstract
		case 'PR': //proceeding
		case 'OP': //other publication
		case 'BR': //book review
		case 'NP': //newspaper
		default:
			return $row['journal_or_publisher_name'];
		}
	}

	function getTitle($row) {
		return $row['title'];
	}

	function getPages($row) {
		if ($row['page_to'] && $row['page_from']) {
			return $row['page_from'].'-'.$row['page_to'];
		} else {
			return '';
		}
	}

	function getFormatted($raw) {
		$fmt = $this->getAuthorList($raw);
		$fmt .= ' ';
		$fmt .= $this->getDateString($raw);
		$fmt .= ' ';
		$fmt .= $this->getTitle($raw); 
		$fmt .= '. ';
		$fmt .= $this->getWorkTitle($raw);
		$fmt .= ' ';
		$fmt .= $this->getPubInfo($raw);
		return $fmt;
	}

	function getEids()
	{
		$res = array_keys($this->citations_by_eid);
		sort($res);
		return $res;
	}

	function getByEid($eid) {
		$data = $this->citations_by_eid[$eid];
		uasort($data,array('Formatter','sortByYear'));
		foreach ($data as $raw) {
			$res[] = $this->getFormatted($raw);
		}
		return join("<p>",$res);
	}

	function getRawByEid($eid) {
		$data = $this->citations_by_eid[$eid];
		uasort($data,array('Formatter','sortByYear'));
		$headers = $data[0];
		foreach ($headers as $k => $v) {
			$table .= "<th>".$k."</th>";
		}
		foreach ($data as $raw) {
			$table .= "<tr>";
			foreach ($raw as $k => $v) {
				$table .= "<td>&nbsp;".$v."</td>";
			}
			$table .= "</tr>";
		}
		return "<table border=\"1\" cellpadding=\"4\">$table</table>";
	}
}
