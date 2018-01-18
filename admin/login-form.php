<?php
Template::render_header(_("Login"));
?>
  <div class="text-center">
    <h1><?php echo _("Login");?></h1>
  </div>
  <div id="login-form" class="center">
    <?php if (isset($message)){?>
    <p class="alert alert-danger"><?php echo $message?></p>
    <?php }else{?>
    <p class="alert alert-info"><?php echo _("Please login to continue.");?></p>
    <?php }?>
    <form action="<?php echo WEB_URL;?>/admin/" method="post">
          <div class="form-group">
            <label for="email"><?php echo _("Email");?></label>
            <input placeholder="<?php echo _("Email");?>" class="form-control" name="email" id="email" type="email" tabindex="1" value="<?php echo htmlspecialchars((isset($_POST['email'])?$_POST['email']:''),ENT_QUOTES);?>" required>
          </div>
          <div class="form-group" style="margin-bottom: 8px">
            <label for="pass"><?php echo _("Password");?></label>
            <input placeholder="<?php echo _("Password");?>" class="form-control" name="pass" id="pass" type="password" tabindex="2" required>
            <div style="padding-top: 8px; position: relative;">
              <a href="<?php echo WEB_URL;?>/admin/?do=lost-password" class="pull-right noselect" tabindex="5"><?php echo _("Forgotten password?");?></a>
              <input name="remember" id="remember" type="checkbox" tabindex="3"> <label class="lbl-login noselect" style="color: black;" for="remember"><?php echo _("Remember me");?></label>
            </div>
          </div>
          <div class="form-group clearfix">
            <button type="submit" class="btn btn-success pull-right" tabindex="4"><?php echo _("Login");?></button>
          </div>          
      </form>
    </div>
<?php
Template::render_footer();