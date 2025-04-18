<?php 
$host="localhost";
$user="root";
$password="";
$database="newdatabase";
$con=mysqli_connect($host,$user,$password,$database);
$name=$_POST['name'];
$email=$_POST['email'];
$password=$_POST['password'];
$gender=$_POST['gender'];
$id=$_POST['id'];
$hobby=json_encode($_POST['hobby']);   
$sql="UPDATE `user` SET `name` = '$name', `email` = '$email', `password` = '$password', `gender` = '$gender', `hobby` = '$hobby' WHERE `user`.`id` = '$id'";
$data=$con->query($sql);
?>