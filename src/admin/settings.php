<?php
if (isset($_GET['delete']) && isset($_GET['type'])) {
  if ($_GET['type'] == 'service') {
    Service::delete();
  } elseif ($_GET['type'] == 'groups') {
    ServiceGroup::delete();
  }
}

Template::render_header(_('Settings'), 'settings', true);
?>
<div class="text-center">
  <h2>Settings</h2>
</div>
<?php
if (isset($message)) {
?>
  <p class="alert alert-danger"><?php echo $message; ?></p>
<?php } ?>
<section>
  <div class="settings-header">
    <div class="float-end">
      <?php if ($user->get_rank() <= 1) { ?>
        <a href="<?php echo WEB_URL; ?>/admin/?do=new-service" class="btn btn-success" role="button"><?php echo _("Add new service"); ?></a>
      <?php } ?>
    </div>
    <div class="float-start">
      <h3><?php echo _("Services"); ?></h3>
    </div>
    <div class="clearfix"></div>
  </div>
  <div>
    <div class="tables services">
      <div><?php echo _("Name"); ?></div>
      <div><?php echo _("Description"); ?></div>
      <div><?php echo _("Group"); ?></div>
      <div>
        <?php if ($user->get_rank() <= 1) {
          echo _("Delete");
        } ?>
      </div>
      <?php
      $query = $mysqli->query("SELECT services.*, services_groups.name AS group_name FROM `services` LEFT JOIN services_groups ON services.group_id = services_groups.id ORDER BY services.name ASC");
      while ($result = $query->fetch_assoc()) {
        echo '<div><a href="' . WEB_URL . '/admin?do=edit-service&id=' . $result['id'] . '">' . $result['name'] . '</a></div>';
        echo "<div>" . $result['description'] . "</div>";
        echo "<div>" . $result['group_name'] . "</div>";
        if ($user->get_rank() <= 1) {
          echo '<div class="centered"><a href="' . WEB_URL . '/admin/?do=settings&type=service&delete=' . $result['id'] . '" class="link-danger"><i class="fa fa-trash"></i></a></div>';
        }
      } ?>
    </div>
  </div>
</section>

<section>
  <div class="settings-header">
    <div class="float-end">
      <?php if ($user->get_rank() <= 1) { ?>
        <a href="<?php echo WEB_URL; ?>/admin/?do=new-service-group" class="btn btn-success" role="button"><?php echo _("Add new service group"); ?></a>
      <?php } ?>
    </div>
    <div class="float-start">
      <h3><?php echo _("Services Groups"); ?></h3>
    </div>
    <div class="clearfix"></div>
  </div>
  <div>
    <div>
      <div class="tables servicesgroups">
        <div><?php echo _("Group Name"); ?></div>
        <div class="centered"><?php echo _("In use by"); ?></div>
        <div><?php echo _("Description"); ?></div>
        <div><?php echo _("Visibility"); ?></div>
        <div>
          <?php if ($user->get_rank() <= 1) {
            echo _("Delete");
          } ?>
        </div>
        <?php
        $query = $mysqli->query("SELECT sg.* , (SELECT COUNT(*) FROM services WHERE services.group_id = sg.id) AS counter FROM services_groups AS sg ORDER BY sg.id ASC");
        while ($result = $query->fetch_assoc()) {
          echo '<div><a href="' . WEB_URL . '/admin?do=edit-service-group&id=' . $result['id'] . '">' . $result['name'] . '</a></div>';
          echo '<div class="centered">' . $result['counter'] . '</div>';
          echo "<div>" . $result['description'] . "</div>";
          echo "<div>" . $visibility[$result['visibility']] . "</div>";
          if ($user->get_rank() <= 1) {
            echo '<div class="centered"><a href="' . WEB_URL . '/admin/?do=settings&type=groups&delete=' . $result['id'] . '" class=" link-danger"><i class="fa fa-trash"></i></a></div>';
          }
        } ?>
      </div>
    </div>
</section>

<section>
  <div class="settings-header">
    <div class="float-end">
      <?php if ($user->get_rank() == 0) { ?>
        <a href="<?php echo WEB_URL; ?>/admin/?do=new-user" class="btn btn-success" role="button"><?php echo _("Add new user"); ?></a>
      <?php } ?>
    </div>
    <div class="float-start">
      <h3><?php echo _("Users"); ?></h3>
    </div>
    <div class="clearfix"></div>
  </div>
  <div>
    <div>
      <div class="tables users">
        <div><?php echo _("ID"); ?></div>
        <div><?php echo _("Username"); ?></div>
        <div><?php echo _("Name"); ?></div>
        <div><?php echo _("Surname"); ?></div>
        <div><?php echo _("Email"); ?></div>
        <div><?php echo _("Role"); ?></div>
        <div class="text-center">Active</div>
        <?php
        $query = $mysqli->query("SELECT *  FROM users");
        while ($result = $query->fetch_assoc()) {
          echo "<div>" . $result['id'] . "</div>";
          echo "<div><a href='" . WEB_URL . "/admin/?do=user&id=" . $result['id'] . "'>" . $result['username'] . "</a></div>";
          echo "<div>" . $result['name'] . "</div>";
          echo "<div>" . $result['surname'] . "</div>";
          echo "<div><a href=\"mailto:" . $result['email'] . "\">" . $result['email'] . "</a></div>";
          echo "<div>" . $permissions[$result['permission']] . "</div>";
          echo "<div class=\"text-center\"><i class='fa fa-" . ($result['active'] ? "check success" : "times danger") . "'></i></div>";
        } ?>
      </div>
    </div>
</section>