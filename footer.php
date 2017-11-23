<?php 
function render_footer($admin = false)
{?>
  </div>
  <div id="footerwrap">
    <div class="container">
      <div class="row centered">
        <div class="col-md-8">Copyright © <?php echo date("Y");?> Vojtěch Sajdl</div>
        <div class="col-md-4"><a href="https://github.com/Pryx/server-status/" target="_blank"><i class="fa fa-github" aria-hidden="true"></i></a></div>
      </div><!--/row -->
    </div><!--/container -->
  </div>
  <script src="/js/vendor/jquery-1.11.2.min.js"></script>
	<script src="/js/vendor/jquery.timeago.js"></script>
	<?php if ($admin){?>
		<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
		<script src="/js/admin.js"></script>
	<? }?>
	<script src="/js/main.js"></script>
</body>
</html>
<?
}
