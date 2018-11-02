
<?php 

include('config/database.php');

if(isset($_GET['id']) && (int)$_GET['id'])
{
   $id = (int)$_GET['id'];
   $delete_query = " DELETE FROM questions WHERE `id` = '$id' ";
   mysqli_query($connection, $delete_query);
}

header('location:new.php?action=deleted');
