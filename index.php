<?php

include 'Formatter.php';


$f = new Formatter;

$eid = $_GET['eid'];

print $f->getByEid($eid);

