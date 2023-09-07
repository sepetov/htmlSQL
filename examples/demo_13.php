<?php

/*
** htmlSQL - Example 1
**
** Shows a simple query
*/

include_once("../snoopy.php");
include_once("../htmlsql.php");

$wsql = new htmlsql();

// connect to a URL
if (!$wsql->connect('url', 'https://www.jonasjohn.de/contact.htm'))
{
    print 'Error while connecting: ' . $wsql->error;
    exit;
}

/* execute a query:

   This query extracts all h2
*/
if (!$wsql->query('SELECT * FROM h2'))
{
    print ":-( Query error: " . $wsql->error;
    exit;
}

// show results:
foreach($wsql->fetch_array() as $row)
{
    print_r($row);

    /*
    $row is an array and looks like this:
    Array
    (
        [tagname] => h2
        [text] => Contact me
    )
    */
}
