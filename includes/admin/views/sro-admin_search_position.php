<?php if (!empty($post->clicked_position)): ?>
<div><?php echo __('This result was clicked whilst in position ','searchresultsoptimizer') . ': ' . $post->clicked_position; ?></div>
<?php else: ?>
<div><?php echo __('This result was not clicked','searchresultsoptimizer'); ?></div>
<?php endif; ?>