<div class="wrap searchresultsoptimizer">
  <h2><?php echo __('Searches', 'searchresultsoptimizer' ); ?></h2>
  <?php
    $table = new \SearchResultsOptimizer\includes\admin\SROAdminSearchesTable();
    $table->prepare_items();
  ?>
  <form method="post">
    <input type="hidden" name="page" value="<?php echo $table->screen->id; ?>" />
    <?php $table->search_box(__('search', 'searchresultsoptimizer' ), 'searches_search'); ?>
    <?php
      $table->display();
    ?>
  </form>
</div>