<?php
Template::render_header(_("Login"), "login");
?>
<div class="text-center">
  <h1><?php echo _("Login"); ?></h1>
</div>
<div class="wrapper">
  <?php if (isset($message)) { ?>
    <p class="alert alert-danger"><?php echo $message ?></p>
  <?php } else { ?>
    <p class="alert alert-info"><?php echo _("Please login to continue."); ?></p>
  <?php } ?>
  <form action="<?php echo WEB_URL; ?>/admin/" method="post">
    <div class="card">
      <div class="card-header">
        <h1><?php echo _("Login"); ?></h1>
      </div>
      <div class="card-body">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="fas fa-at"></i></span>
          <input type="email" id="email" name="email" class="form-control" placeholder="<?php echo _("Email"); ?>" value="<?php echo htmlspecialchars((isset($_POST['email']) ? $_POST['email'] : ''), ENT_QUOTES); ?>" required>
        </div>
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="fas fa-key"></i></span>
          <input type="password" id="pass" name="pass" class="form-control" placeholder="<?php echo _("Password"); ?>" required>
        </div>
        <a href="<?php echo WEB_URL; ?>/admin/?do=lost-password" class="float-end" tabindex="5"><?php echo _("Forgotten password?"); ?></a>
        <div class="input-group mb-3">
          <div class="input-group-text nrbr">
            <input type="checkbox" name="remember" id="remember">
          </div>
          <label class="input-group-append input-group-text nlbr nobg" for="remember"><?php echo _("Remember me"); ?></label>
        </div>
        <div class="form-group">
          <input type="submit" value="<?php echo _("Login"); ?>" class="btn btn-success float-end">
        </div>
      </div>
    </div>
  </form>
</div>
<?php
Template::render_footer();
