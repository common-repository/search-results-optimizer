<div class='sro-result<?php echo $post->pinned ? ' pinned' : '';?> popular-tags hndle<?php echo $k%2 ? ' alternate' : ''; ?>' data-id='<?php echo $post->ID; ?>'>
  <h3><?php echo $post->post_title; ?><span class='timestamp'><?php echo __('Last modified','searchresultsoptimizer') . ': ' . \date_i18n(get_option('date_format'), strtotime($post->post_modified)); ?></span></h3>
  <div class='left'><?php if (\strlen($post->post_content) > self::EXCERPT_LENGTH): ?>
      <div><?php echo \substr(\strip_tags($post->post_content), 0, self::EXCERPT_LENGTH); ?>&hellip;</div>
    <?php else: ?>
      <div><?php echo \strip_tags($post->post_content); ?></div>
    <?php endif; ?>
    <?php if (empty($post->read_only)) { include __DIR__ . DIRECTORY_SEPARATOR . 'sro-admin_search_pin.php'; } ?>
  </div>
  <div class='right'>
    <?php if (isset($post->sroCtr)) { include __DIR__ . DIRECTORY_SEPARATOR . 'sro-admin_search_ctr.php'; } ?>
    <?php if (property_exists($post, 'clicked_position')) { include __DIR__ . DIRECTORY_SEPARATOR . 'sro-admin_search_position.php'; } ?>
  </div>
  <div class='clear'></div>
</div>