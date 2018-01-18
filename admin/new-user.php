<?php 
if (isset($_GET['new']))
{
	User::add();
}

Template::render_header(_("New user"), true); ?>
<div class="text-center">
    <h2>Add new user</h2>
</div>

<form action="<?php echo WEB_URL;?>/admin/?do=new-user&amp;new=user" method="POST" class="form-horizontal">
	<?php if (isset($message))
    {?>
      <p class="alert alert-danger"><?php echo $message?></p>
    <?php
    } ?>
	<div class="form-group">
		<div class="col-sm-6"><label for="name"><?php echo _("Name");?>: </label><input type="text" maxlength="50" name="name" value="<?php echo ((isset($_POST['name']))?htmlspecialchars($_POST['name'],ENT_QUOTES):'');?>" id="name" placeholder="<?php echo _("Name");?>" class="form-control" required></div>
		<div class="col-sm-6"><label for="surname"><?php echo _("Surname");?>: </label><input type="text" maxlength="50" name="surname" value="<?php echo ((isset($_POST['surname']))?htmlspecialchars($_POST['surname'],ENT_QUOTES):'');?>" id="surname" placeholder="<?php echo _("Surname");?>" class="form-control" required></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6"><label for="username"><?php echo _("Username");?>:</label><input type="text" maxlength="50" name="username" value="<?php echo ((isset($_POST['username']))?htmlspecialchars($_POST['username'],ENT_QUOTES):'');?>" id="username" placeholder="<?php echo _("Username");?>" class="form-control" required></div>
		<div class="col-sm-6"><label for="email"><?php echo _("Email");?>:</label><input type="email" maxlength="60" name="email" value="<?php echo ((isset($_POST['email']))?htmlspecialchars($_POST['email'],ENT_QUOTES):'');?>" id="email" placeholder="<?php echo _("Email");?>" class="form-control" required></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6"><label for="password"><?php echo _("Password");?>:</label><input type="password" name="password" value="<?php echo ((isset($_POST['password']))?htmlspecialchars($_POST['password'],ENT_QUOTES):'');?>" id="password" placeholder="<?php echo _("Password");?>" class="form-control" required></div>
		<div class="col-sm-6">
			<label for="permission"><?php echo _("Permission");?>: </label>
			<select name="permission" id="permission" class="form-control">
				<?php 
				if (!empty($_POST['permission']))
				{
					$permission = $_POST['permission'];
				}
				else
				{
					$permission = 2;
				}
				foreach ($permissions as $key => $value) {
					if ($permission == $key)
					{
						echo '<option value="'.$key.'" selected>'.$value.'</option>';
					}
					else{
						echo '<option value="'.$key.'">'.$value.'</option>';
					}	
				}
				?>
			</select>
		</div>
	</div>
	<button type="submit" class="btn btn-primary pull-right"><?php echo _("Submit");?></button>
</form>