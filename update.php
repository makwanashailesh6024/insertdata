
<?php 
$host="localhost";
$user="root";
$password="";
$database="newdatabase";
$con=mysqli_connect($host,$user,$password,$database);
$sql="select * from user where id=".$_GET['id'];
$data=$con->query($sql);
$result=$data->fetch_all(MYSQLI_ASSOC);
foreach($result as $val){
	
	?>
<html>
<form action="http://localhost:81/updatedata.php" method="post"> 
<label>Name</label><input type="text" name="name" value="<?php echo $val['name']?>">
<label>Id</label><input type="text" name="id" value="<?php echo $val['id']?>">
<label>Email</label><input type="text" name="email" value="<?php echo $val['email']?>">
<label>Password</label><input type="text" name="password" value="<?php echo $val['password']?>">
<label>gender</label> male <input value="male" type="radio" name="gender" >
female <input value="female" type="radio" name="gender">
<label>hobby</label>one<input value="one" type="checkbox" name="hobby[]">
two<input value="two" type="checkbox" name="hobby[]">
<input type="submit">
</form>
</html>

<?php } ?>