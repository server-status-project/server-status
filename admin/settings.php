<?php
if (isset($_GET['delete']) && isset($_GET['type']))
{
	if ( $_GET['type'] == 'service') {
		Service::delete();
	}
	elseif ( $_GET['type'] == 'groups') {
		ServiceGroup::delete();
	}
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
	<form action="?do=settings&new=service" method="post">
		<div class="input-group pull-right new-service">
			<a href="<?php echo WEB_URL;?>/admin/?do=new-service" class="btn btn-success pull-right"><?php echo _("Add new service");?></a>
		</div>
	</form>
	<?php }?>
	<div class="table-responsive">
		<table class="table">

			<thead><tr>
				<!--<th scope="col"><?php echo _("ID");?></th>-->
				<th scope="col"><?php echo _("Name");?></th>
				<th scope="col"><?php echo _("Description");?></th>
				<th scope="col"><?php echo _("Group");?></th>
				<?php if ($user->get_rank()<=1)
				{?>
					<th scope="col"><?php echo _("Delete");?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$query = $mysqli->query("SELECT services.*, services_groups.name AS group_name FROM `services` LEFT JOIN services_groups ON services.group_id = services_groups.id ORDER BY services.name ASC");
				while($result = $query->fetch_assoc())
				{
					echo "<tr>";
					//echo "<td>".$result['id']."</td>";
					echo '<td><a href="'.WEB_URL.'/admin?do=edit-service&id='.$result['id'].'">'.$result['name'].'</a></th>';
					echo "<td>".$result['description']."</td>";
					echo "<td>".$result['group_name']."</td>";

					if ($user->get_rank()<=1)
					{
						echo '<td><a href="'.WEB_URL.'/admin/?do=settings&type=service&delete='.$result['id'].'" class="pull-right delete-service"><i class="fa fa-trash"></i></a></td>';
					}
					echo "</tr>";
				}?>
			</tbody>
		</table>
	</div>
</section>

<section>
	<h3 class="pull-left"><?php echo _("Services Groups");?></h3>
	<?php if ($user->get_rank() <= 1){?>
	<form action="?do=settings&new=service-group" method="post">
		<div class="input-group pull-right new-service">
			<a href="<?php echo WEB_URL;?>/admin/?do=new-service-group" class="btn btn-success pull-right"><?php echo _("Add new service group");?></a>
		</div>
	</form>
	<?php }?>
	<div class="table-responsive">
		<table class="table">

			<thead><tr>
				<!--<th scope="col"><?php echo _("ID");?></th>-->
				<th scope="col"><?php echo _("Group Name");?></th>
				<th scope="col"><?php echo _("In use by");?></th>
				<th scope="col"><?php echo _("Description");?></th>
				<th scope="col"><?php echo _("Visibility");?></th>
				<?php if ($user->get_rank()<=1)
				{?>
					<th scope="col"><?php echo _("Delete");?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$query = $mysqli->query("SELECT sg.* , (SELECT COUNT(*) FROM services WHERE services.group_id = sg.id) AS counter FROM services_groups AS sg ORDER BY sg.id ASC");
				while($result = $query->fetch_assoc())
				{
					echo "<tr>";
					//echo "<td>".$result['id']."</td>";
					echo '<td><a href="'.WEB_URL.'/admin?do=edit-service-group&id='.$result['id'].'">'.$result['name'].'</a></th>';
					echo '<td> <span class="badge badge-danger ml-2">'.$result['counter'].'</span>';
					echo "<td>".$result['description']."</td>";
					echo "<td>".$visibility[$result['visibility']]."</td>";

					if ($user->get_rank()<=1)
					{
						echo '<td><a href="'.WEB_URL.'/admin/?do=settings&type=groups&delete='.$result['id'].'" class="pull-right delete-service"><i class="fa fa-trash"></i></a></td>';
					}
					echo "</tr>";
				}?>
			</tbody>
		</table>
	</div>
</section>


<section>
	<h3 class="pull-left"><?php echo _("Users");?></h3>
	<?php if ($user->get_rank() == 0){?> <a href="<?php echo WEB_URL;?>/admin/?do=new-user" class="btn btn-success pull-right"><?php echo _("Add new user");?></a><?php }?>
	<div class="table-responsive">
		<table class="table">

			<thead><tr><th scope="col"><?php echo _("ID");?></th><th scope="col"><?php echo _("Username");?></th><th scope="col"><?php echo _("Name");?></th><th scope="col"><?php echo _("Surname");?></th><th scope="col"><?php echo _("Email");?></th><th scope="col"><?php echo _("Role");?></th><th scope="col">Active</th></tr></thead>
			<tbody>
				<?php
				$query = $mysqli->query("SELECT *  FROM users");
				while($result = $query->fetch_assoc())
				{
					echo "<tr>";
					echo "<td>".$result['id']."</td>";
					echo "<td><a href='".WEB_URL."/admin/?do=user&id=".$result['id']."'>".$result['username']."</a></td>";
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
	</div>
</section>
