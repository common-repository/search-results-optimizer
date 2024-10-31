<div class="wrap searchresultsoptimizer">
  <h2><?php echo __('Reports', 'searchresultsoptimizer'); ?></h2>
  <div id="poststuff">
    <h3><?php echo __('Last 7 Days Most Popular Results', 'searchresultsoptimizer'); ?></h3>
    <div class="sro-report recent-clicks">
      <div class="chart postbox"></div>
      <div class="table">
        <?php
          $table = new \SearchResultsOptimizer\includes\admin\reports\SROAdminReportsClicksTable();
          $table->prepare_items();
          $table->display();
        ?>
      </div>
    </div>
  </div>
</div>