<?php

class Formatter 
{
	public static $specs = 
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


	private function sortByYear($b,$a)
	{
		$year_a = substr($a['date_published'],0,4);
		$year_b = substr($b['date_published'],0,4);
		if ($year_a == $year_b) {
			return 0;
		}
		return ($year_a < $year_b) ? -1 : 1;
	}

	private function dirify($str)
	{
		$str = strtolower(preg_replace('/[^a-zA-Z0-9_-]/','_',trim($str)));
		return preg_replace('/__*/','_',$str);
	}

	private function getAuthorList($row) {
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

	private function getDateString($row) {
		if ('0000-00-00' == $row['date_published']) {
			return '0000';
		}
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
			$disp = date('Y, F',$ts);
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

	private function getEditorList($row) {
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

	private function getWorkTitle($row) {
		switch ($row['type']) {
		case 'AR': //article
			if (strlen($this->getPubInfo($row)) < 2) {
				return $row['journal_or_publisher_name'];
			} else {
				return $row['journal_or_publisher_name'].',';
			}
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
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'AB': //abstract
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'PR': //proceeding
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'OP': //other publication
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'BR': //book review
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'NP': //newspaper
		default:
			return $row['journal_or_publisher_name'];
		}
	}

	private function getHtmlWorkTitle($row) {
		$row['book_title'] = '<em>'.$row['book_title'].'</em>';
		$row['journal_or_publisher_name'] = '<em>'.$row['journal_or_publisher_name'].'</em>';
		switch ($row['type']) {
		case 'AR': //article
			if (strlen($this->getPubInfo($row)) < 2) {
				return $row['journal_or_publisher_name'];
			} else {
				return $row['journal_or_publisher_name'].',';
			}
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
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'AB': //abstract
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'PR': //proceeding
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'OP': //other publication
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'BR': //book review
			return $row['journal_or_publisher_name'] ? $row['journal_or_publisher_name'] : $row['book_title'];
		case 'NP': //newspaper
		default:
			return $row['journal_or_publisher_name'];
		}
	}

	private function getPubInfo($row) {
		if ($row['city_of_publication']) {
			$row['city_of_publication'] .= ':';
		}
		switch ($row['type']) {
		case 'AR': //article
			$vol = $row['volume'];
			$num = $row['number'];
			if ($num) {
				$vol = "$vol($num)";
			}
			if ($this->getPages($row)) {
				return $vol.', '.$this->getPages($row).'.';
			} else {
				return $vol.'.';
			}
		case 'BK': //book
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'BC': //book chapter
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'MO': //monograph
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'TR': //technical report
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'AB': //abstract
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'PR': //proceeding
			return $row['city_of_publication'].' '.$row['journal_or_publisher_name'].'.';
		case 'OP': //other publication
			$vol = $row['volume'];
			$num = $row['number'];
			if ($num) {
				$vol = "$vol($num)";
			}
			return $vol.', '.$this->getPages($row).'.';
		case 'BR': //book review
		case 'NP': //newspaper
		default:
			return $row['journal_or_publisher_name'];
		}
	}

	private function getTitle($row) {
		return $row['title'];
	}

	private function getPages($row) {
		if ($row['page_to'] && $row['page_from']) {
			return $row['page_from'].'-'.$row['page_to'];
		} else {
			return '';
		}
	}

	private function isInvalid($row) {
		if ('0000-00-00' == $row['date_published']) {
			return true;
		}
		//if (in_array($row['status'],array('IP','RR','SB','PR'))) {
		if (in_array($row['status'],array('RR','SB','PR'))) {
			return true;
		}
		if (!$row['title']) {
			return true;
		}
		if (!$row['name']) {
			return true;
		}
		return false;
	}

	public function getCitation($row,$check=true) {
		$fmt = '';
		if ($check && $this->isInvalid($row)) {
			$fmt .= "[INVALID] ";
		}
		$fmt .= $this->getAuthorList($row);
		$fmt .= ' ';
		$fmt .= $this->getDateString($row);
		$fmt .= ' ';
		$fmt .= $this->getTitle($row); 
		$fmt .= '. ';
		$fmt .= $this->getWorkTitle($row);
		$fmt .= ' ';
		$fmt .= $this->getPubInfo($row);
		$fmt = str_replace(' ,',',',$fmt);
		$fmt = str_replace(' .','.',$fmt);
		$fmt = str_replace(',,',',',$fmt);
		$fmt = str_replace(',.','.',$fmt);
		return $fmt;
	}

	public function getHtmlCitation($row,$check=true) {
		$fmt = '';
		if ($check && $this->isInvalid($row)) {
			$fmt .= "[INVALID] ";
		}
		$fmt .= $this->getAuthorList($row);
		$fmt .= ' ';
		$fmt .= $this->getDateString($row);
		$fmt .= ' ';
		$fmt .= '<strong>'.$this->getTitle($row).'</strong>'; 
		$fmt .= '. ';
		$fmt .= $this->getHtmlWorkTitle($row);
		$fmt .= ' ';
		$fmt .= $this->getPubInfo($row);
		$fmt = str_replace(' ,',',',$fmt);
		$fmt = str_replace(' .','.',$fmt);
		$fmt = str_replace(',,',',',$fmt);
		$fmt = str_replace(',.','.',$fmt);
		return $fmt;
	}

	public function getData($row) {
		$str = '';
		foreach ($row as $key => $val) {
			$str .= $key.' : '.$val.' | ';
		}
		return $str;
	}
}
