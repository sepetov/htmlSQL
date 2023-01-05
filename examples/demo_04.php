<?php

/*
** htmlSQL - Example 4
**
** Shows a advanced query with preg_match
*/

include_once("../snoopy.php");
include_once("../htmlsql.php");

$wsql = new htmlsql();

// connect to a URL
if (!$wsql->connect('url', 'http://codedump.jonasjohn.de/links.htm'))
{
    print 'Error while connecting: ' . $wsql->error;
    exit;
}

/* execute a query:

   This query returns all links of an document that start with https://
*/
if (!$wsql->query('SELECT * FROM a WHERE preg_match("/^https:\/\//", $href)'))
{
    print "Query error: " . $wsql->error;
    exit;
}

// show results:
foreach($wsql->fetch_array() as $row)
{
    print_r($row);
}
