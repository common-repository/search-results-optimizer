<div class="wrap searchresultsoptimizer">
  <h2><?php echo __('Reports', 'searchresultsoptimizer'); ?></h2>
  <div id="poststuff">
      <div id="post-body" class="metabox-holder">
        <div id="post-body-content">
          <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap">
            <div id="wp-content-editor-container" class="sro-report wp-editor-container">
            <?php $this->getLastTenResults(); ?>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>