<?php
Template::render_header(_("Lost password"));
?>
  <div class="text-center">
    <h1><?php echo _("Lost password");?></h1>
  </div>
  <div id="login-form" class="center">
    
    <?php
    if (isset($_POST['id']))
    {
      $user = new User($_POST['id']);
      $user->change_password($_POST['token']);
      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <a href="<?php echo WEB_URL;?>/admin/?do=lost-password<?php echo "&amp;id=".$_POST['id']."&amp;token=".$_POST['token'];?>"><?php echo _("Go back");?> </a>
      <?php 
      }
        else{?>
        <p class="alert alert-success"><?php echo _("Password changed successfully!");?></p>
        <a href="<?php echo WEB_URL;?>/admin/"><?php echo _("Go back to login page");?></a>
        <?php 
      }
    }
    else if (isset($_POST['email']))
    {
      User::password_link();
      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <a href="<?php echo WEB_URL;?>/admin/?do=lost-password"><?php echo _("Go back to start");?></a>
      <?php 
      }
        else{?>
        <p class="alert alert-success"><?php echo _("Email with password reset link has been sent!");?></p>
        <a href="<?php echo WEB_URL;?>/admin/"><?php echo _("Go back to login page");?></a>
        <?php 
      }
    }
    else{

      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <?php }?>
      <form action="<?php echo WEB_URL;?>/admin/?do=lost-password" method="post">
      <?php if (!isset($_GET['id'])||!isset($_GET['token'])){?>
        <label for="email"><?php echo _("Email");?>:</label>
          <div class="input-group pull-right">
            <input class="form-control" name="email" id="email" placeholder="<?php echo _("Email");?>" type="email" required>
            <span class="input-group-btn">
              <button type="submit" class="btn btn-success pull-right"><?php echo _("Submit request");?></button>
            </span>
          </div>      
        <?php }
        else{
          $user = new User($_GET['id']);
          ?>
            <p class="alert alert-info"><?php printf(_("Reset password for %s (%s)"),$user->get_name(), $user->get_username());?></p>
            <input type="hidden" name="id" value="<?php echo $_GET['id'];?>" >
            <input type="hidden" name="token" value="<?php echo $_GET['token'];?>" >
            <label for="new_password"><?php echo _("New password");?></label>
            <input id="new_password" placeholder="<?php echo _("New password");?>" type="password" class="form-control" name="password">
            <label for="new_password_check"><?php echo _("Repeat password");?></label>
            <input id="new_password_check" placeholder="<?php echo _("Repeat password");?>" type="password" class="form-control" name="password_repeat">
            <button type="submit" class="btn btn-primary pull-right margin-top"><?php echo _("Change password");?></button>
          <?php 
        }
        ?>
        </form>
        <?php }?>
    </div>
<?php 
Template::render_footer();