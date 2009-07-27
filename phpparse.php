<?php
include 'db.php';
$sql = "SELECT INSERT INTO publication VALUES ";
$stmt = $db->prepare("INSERT INTO publication ( id, eid, name, department, title, book_title, type, status, journal_or_publisher_name, city_of_publication, referee_tier, date_published, authorship, volume, number, month_season, page_from, page_to, pub_url, co_author_1, co_author_2, co_author_3, co_author_4, co_author_5, co_author_6, co_author_7, co_author_8, co_author_9, co_author_10, editor_1, editor_2, editor_3, editor_4, notes, station, assembled_citation, display_citation, file, _updated, _updatedby
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");	

$row = 1;
$handle = fopen("publication.csv", "r");
$lookup = array(
	'id', 'eid', 'name', 'department', 'title', 'book_title', 'type', 'status', 'journal_or_publisher_name', 'city_of_publication', 'referee_tier', 'date_published', 'authorship', 'volume', 'number', 'month_season', 'page_from', 'page_to', 'pub_url', 'co_author_1', 'co_author_2', 'co_author_3', 'co_author_4', 'co_author_5', 'co_author_6', 'co_author_7', 'co_author_8', 'co_author_9', 'co_author_10', 'editor_1', 'editor_2', 'editor_3', 'editor_4', 'notes', 'station', 'assembled_citation', 'display_citation', 'file', '_updated', '_updatedby'
);	
$all = array();
while (($data = fgetcsv($handle,3000,";")) !== FALSE) {
	$assoc = array();
	$num = count($data);
	for ($c=0; $c < $num; $c++) {
		$assoc[$lookup[$c]] = $data[$c];
		$stmt->bindParam($c+1, $data[$c]);
	}
	$stmt->execute();
	print "inserted row $row\n";
	$row++;
}
fclose($handle);

//print serialize($all);

