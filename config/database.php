<?php

define('HOST','localhost'); // Database host name
define('USERNAME','root'); //Database username
define('PASSWORD',''); //Database password
define('DATABASE','survey'); //Database name

//--------Make sure we have MySQL on the server--------
if(!function_exists('mysqli_connect'))
{
	echo 'Unable to connect to MYSQL database, please check MySQL settings';
	exit;		
}

//--------Make a connection to the database using above credentials--------
$connection = mysqli_connect(HOST,USERNAME,PASSWORD);
if(!$connection)
{
	echo 'Database connection failed';
	exit;
}

//---------Select the specified database---------
$db_selection = mysqli_select_db($connection, DATABASE);
if(!$db_selection)
{
	echo 'Database selection failed';
	exit;
}

?>