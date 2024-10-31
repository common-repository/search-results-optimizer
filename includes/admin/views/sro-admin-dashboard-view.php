<div class="wrap searchresultsoptimizer sro-dashboard">
  <div>
    <div class="sro-logo">
      <div class="sro-colour-one"></div>
      <div class="sro-colour-two"></div>
      <div class="sro-colour-three"></div>
      <div class="sro-colour-four"></div>
      <div class="sro-colour-five"></div>
    </div>
    <div class="dashboard-header">
      <h2><?php echo __( 'Search Results Optimizer', 'searchresultsoptimizer' ); ?></h2>
      <h3>Version: <?php echo get_option('searchresultsoptimizer_version'); ?></h3>
      <h3 class="attrib"><a href="http://www.gorvan.com/">Chris Gorvan</a></h3>
    </div>
    <div class="srocf"></div>
  </div>
  <div class="srocf"></div>
  <hr>
  <div id="poststuff">
    <div class="left">
      <h3><?php echo __('Latest Searches', 'searchresultsoptimizer'); ?></h3>
      <?php
          $table = new \SearchResultsOptimizer\includes\admin\reports\SROAdminReportsLatestSearchesTable();
          $table->prepare_items();
          $table->display();
        ?>
    </div>
    <div class="right">
      <h3><?php echo __('Latest Results Clicked', 'searchresultsoptimizer'); ?></h3>
      <?php
          $table = new \SearchResultsOptimizer\includes\admin\reports\SROAdminReportsLatestClicksTable();
          $table->prepare_items();
          $table->display();
        ?>
    </div>
    <div class="srocf"></div>
  </div>
</div>