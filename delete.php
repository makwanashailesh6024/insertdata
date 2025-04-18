<?php 
$host="localhost";
$user="root";
$password="";
$database="newdatabase";
$con=mysqli_connect($host,$user,$password,$database);
$sql="delete from user where id=".$_GET['id'];
$data=$con->query($sql);