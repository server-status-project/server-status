<?php
render_header("Lost password");
?>
  <div class="text-center">
    <h1>Lost password</h1>
  </div>
  <div id="login-form" class="center">
    
    <?php
    if (isset($_POST['id']))
    {
      $user = new User($_POST['id']);
      $user->change_password($_POST['token']);
      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <a href="/admin/?do=lost-password<?php echo "&id=".$_POST['id']."&token=".$_POST['token'];?>">Go back</a>
      <?php 
      }
        else{?>
        <p class="alert alert-success">Password changed successfully!</p>
        <a href="/admin/">Go back to login page</a>
        <?php 
      }
    }
    else if (isset($_POST['email']))
    {
      User::password_link();
      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <a href="/admin/?do=lost-password">Go back to start</a>
      <?php 
      }
        else{?>
        <p class="alert alert-success">Email with password reset link has been sent!</p>
        <a href="/admin/">Go back to login page</a>
        <?php 
      }
    }
    else{

      if (isset($message)){?>
      <p class="alert alert-danger"><?php echo $message?></p>
      <?php }?>
      <form action="/admin/?do=lost-password" method="post">
      <?php if (!isset($_GET['id'])||!isset($_GET['token'])){?>
        <label for="email">Email:</label>
          <div class="input-group pull-right">
            <input class="form-control" name="email" id="email" placeholder="Email" type="email" required>
            <span class="input-group-btn">
              <button type="submit" class="btn btn-success pull-right">Submit request</button>
            </span>
          </div>      
        <?php }
        else{
          $user = new User($_GET['id']);
          ?>
            <p class="alert alert-info">Reset password for <?php echo $user->get_name()." (".$user->get_username().")";?></p>
            <input type="hidden" name="id" value="<?php echo $_GET['id'];?>" >
            <input type="hidden" name="token" value="<?php echo $_GET['token'];?>" >
            <label for="new_password">New password</label>
            <input id="new_password" placeholder="New password" type="password" class="form-control" name="password">
            <label for="new_password_check">Repeat password</label>
            <input id="new_password_check" placeholder="Repeat password" type="password" class="form-control" name="password_repeat">
            <button type="submit" class="btn btn-primary pull-right margin-top">Change password</button>
          <?php 
        }
        ?>
        </form>
        <?php }?>
    </div>
<?php 
render_footer();