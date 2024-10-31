<?php if ($post->pinned):
      $pinnedId = $post->ID; ?>
      <div class='sro-pin dashicons dashicons-admin-post button button-secondary button-large' data-unpinned-text='<?php echo __('Pin this result','searchresultsoptimizer'); ?> ' data-pinned-text='<?php echo __('Unpin this result','searchresultsoptimizer'); ?>'><?php echo __('Unpin this result','searchresultsoptimizer'); ?></div>
    <?php else: ?>
      <div class='sro-pin dashicons dashicons-admin-post button button-secondary button-large' data-unpinned-text='<?php echo __('Pin this result','searchresultsoptimizer'); ?> ' data-pinned-text='<?php echo __('Unpin this result','searchresultsoptimizer'); ?>'><?php echo __('Pin this result','searchresultsoptimizer'); ?></div>
  <?php endif; ?>