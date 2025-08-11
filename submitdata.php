	<table>
	<tr>
	<th>name</th>
	<th>email</th>
	<th>password</th>
	<th>gender</th>
	<th>hobby</th>
	</tr>
	
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
$hobby=json_encode($_POST['hobby']); 

 $sql="INSERT INTO `user` (`name`, `email`, `password`, `gender`, `hobby`) VALUES ('$name', '$email', '$password', '$gender', '$hobby')";
$con->query($sql);

$sql="select * from user";
$data=$con->query($sql);
$result=$data->fetch_all(MYSQLI_ASSOC);//11/08
foreach($result as $val){

?>
<tr>
<td><?php echo $val['name']?></td>
<td><?php echo $val['email']?></td>
<td><?php echo $val['password']?></td>
<td><?php echo $val['gender']?></td>
<td><?php echo $val['hobby']?></td>
<td><a href="http://localhost:81/update.php?id=<?php echo $val['id']?>">edit</a></td>
<td><a href="http://localhost:81/delete.php?id=<?php echo $val['id']?>">delete</a></td>
</tr>
<?php } ?>


	</table>
