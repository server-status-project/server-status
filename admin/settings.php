<?php
if (isset($_GET['new']))
{
	Service::add();
}

if (isset($_GET['delete']))
{
	Service::delete();
}

Template::render_header(_("Settings"), true);
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
<h3 class="pull-left"><?php echo _("Services");?></h3>
<?php if ($user->get_rank() <= 1){?>
<form action="?do=settings&amp;new=service" method="post">
	<div class="input-group pull-right new-service">
		<input class="form-control" name="service" placeholder="Name" type="text" value="<?php echo ((isset($_POST['service']))?htmlspecialchars($_POST['service']):''); ?>" maxlength="50" required>
		<span class="input-group-btn">
			<button type="submit" class="btn btn-success pull-right"><?php echo _("Add service");?></button>
		</span>
	</div>
</form>
<?php }?>
<table class="table">
	
<thead><tr>
	<th scope="col"><?php echo _("ID");?></th>
	<th scope="col"><?php echo _("Name");?></th>
<?php if ($user->get_rank()<=1)
	{?>
		<th scope="col"><?php echo _("Delete");?></th>
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
		echo '<td><a href="'.WEB_URL.'/admin/?do=settings&amp;delete='.$result['id'].'" class="pull-right delete-service"><i class="fa fa-trash"></i></a></td>';
	}
	echo "</tr>";
}?>
</tbody>
</table>
</section>


<section>
<h3 class="pull-left"><?php echo _("Users");?></h3>
<?php if ($user->get_rank() == 0){?> <a href="<?php echo WEB_URL;?>/admin/?do=new-user" class="btn btn-success pull-right"><?php echo _("Add new user");?></a><?php }?>
<table class="table">
	
<thead><tr><th scope="col"><?php echo _("ID");?></th><th scope="col"><?php echo _("Username");?></th><th scope="col"><?php echo _("Name");?></th><th scope="col"><?php echo _("Surname");?></th><th scope="col"><?php echo _("Email");?></th><th scope="col"><?php echo _("Role");?></th><th scope="col">Active</th></tr></thead>
<tbody>
<?php 
$query = $mysqli->query("SELECT *  FROM users");
while($result = $query->fetch_assoc())
{
	echo "<tr>";
	echo "<td>".$result['id']."</td>";
	echo "<td><a href='".WEB_URL."/admin/?do=user&amp;id=".$result['id']."'>".$result['username']."</a></td>";
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