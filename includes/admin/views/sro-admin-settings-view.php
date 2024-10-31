<div class="wrap searchresultsoptimizer">
  <h2><?php echo __('Settings', 'searchresultsoptimizer'); ?></h2>
  <form method="post" action="options.php"> 
    <?php settings_fields('searchresultsoptimizer_fields'); ?>
    <?php do_settings_sections('searchresultsoptimizer_settings'); ?>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save Changes', 'searchresultsoptimizer'); ?>">
    </p>
  </form>
</div>