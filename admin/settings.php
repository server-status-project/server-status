<?php
if (isset($_GET['new']))
{
	Service::add();
}

if (isset($_GET['delete']))
{
	Service::delete();
}

render_header("Settings", true);
?>
<div class="text-center">
    <h2>Settings</h2>
</div>
<?php 
if (isset($message)){
?>
<p class="alert alert-danger"><?php echo $message; ?></p>
<?php }?>
<section>
<h3 class="pull-left">Services</h3>
<?php if ($user->get_rank() <= 1){?>
<form action="?do=settings&new=service" method="post">
	<div class="input-group pull-right new-service">
		<input class="form-control" name="service" placeholder="Name" type="text" value="<?php echo htmlspecialchars($_POST['service']); ?>" maxlength="50" required>
		<span class="input-group-btn">
			<button type="submit" class="btn btn-success pull-right">Add service</button>
		</span>
	</div>
</form>
<?php }?>
<table class="table">
	
<thead><tr>
	<th scope="col">ID</th>
	<th scope="col">Name</th>
<?php if ($user->get_rank()<=1)
	{?>
		<th scope="col">Delete</th>
<?php } ?>
	</tr>
</thead>
<tbody>
<?php 
$query = $mysqli->query("SELECT *  FROM services");
while($result = $query->fetch_assoc())
{
	echo "<tr>";
	echo "<td>".$result['id']."</td>";
	echo "<td>".$result['name']."</td>";
	if ($user->get_rank()<=1)
	{
		echo '<td><a href="?do=settings&delete='.$result['id'].'" class="pull-right delete-service"><i class="fa fa-trash"></i></a></td>';
	}
	echo "</tr>";
}?>
</tbody>
</table>
</section>


<section>
<h3 class="pull-left">Users</h3>
<?php if ($user->get_rank() == 0){?> <a href="?do=new-user" class="btn btn-success pull-right">Add new user</a><?php }?>
<table class="table">
	
<thead><tr><th scope="col">ID</th><th scope="col">Username</th><th scope="col">Name</th><th scope="col">Surname</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Active</th></tr></thead>
<tbody>
<?php 
$query = $mysqli->query("SELECT *  FROM users");
while($result = $query->fetch_assoc())
{
	echo "<tr>";
	echo "<td>".$result['id']."</td>";
	echo "<td><a href='/admin/?do=user&id=".$result['id']."'>".$result['username']."</a></td>";
	echo "<td>".$result['name']."</td>";
	echo "<td>".$result['surname']."</td>";
	echo "<td><a href=\"mailto:".$result['email']."\">".$result['email']."</a></td>";
	echo "<td>".$permissions[$result['permission']]."</td><td>";
	echo "<i class='fa fa-".($result['active']?"check success":"times danger")."'></i>";
	echo "</td>";
	echo "</tr>";
}?>
</tbody>
</table>
</section>