<?php 
function render_footer($admin = false)
{
  global $lang_names;
  ?>
  </div>
  <div id="footerwrap">
    <div class="container">
      <div class="row centered">
        <div class="col-md-4 text-left">Copyright © <?php echo date("Y");?> Vojtěch Sajdl</div>
        <div class="col-md-4 text-center">
          <div class="btn-group dropup">
            <button type="button" class="btn btn-primary"><?php echo '<img src="'.WEB_URL.'/locale/'.$_SESSION['locale'].'/flag.png" alt="'.$lang_names[$_SESSION['locale']].'">'.$lang_names[$_SESSION['locale']];?></button>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="caret"></span>
              <span class="sr-only"><?php echo _("Toggle Dropdown");?></span>
            </button>
            <ul class="dropdown-menu">
                <?php 
                foreach ($lang_names as $key => $value) {
                  echo '<a href="?lang='.$key.'""><img src="'.WEB_URL.'/locale/'.$key.'/flag.png" alt="'.$value.'">'.$value.'</a>';
                }
                ?>
            </ul>
          </div>
        </div>
        <div class="col-md-4 text-right"><a href="https://github.com/Pryx/server-status/" target="_blank"><i class="fa fa-github" aria-hidden="true"></i></a></div>
      </div><!--/row -->
    </div><!--/container -->
  </div>
  <script src="/js/vendor/jquery-1.11.2.min.js"></script>
  <script src="/js/vendor/jquery.timeago.js"></script>
  <?php if ($admin){?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="/js/admin.js"></script>
    <script src="/js/vendor/jquery.growl.js"></script>
  <? }?>
  <script src="/js/vendor/bootstrap.min.js"></script>
  <script src="/js/main.js"></script>
</body>
</html>
<?
}
