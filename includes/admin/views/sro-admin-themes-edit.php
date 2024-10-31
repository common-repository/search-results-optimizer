<div class="wrap searchresultsoptimizer">
  <h2><?php echo __('Search Themes', 'searchresultsoptimizer'); ?></h2>
  <form method="post">
    <input type="hidden" name="nonce" value="<?php echo $this->getTheme()->getUpdateNonce(); ?>">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
		<label class="screen-reader-text" id="title-prompt-text" for="title">Enter name here</label>
                <input type="text" name="theme_name" size="64" value="<?php echo $this->getTheme()->name; ?>" id="title" autocomplete="off">
            </div>
          </div>
          <h2><?php echo __('Most popular results', 'searchresultsoptimizer'); ?></h2>
          <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap">
            <div id="wp-content-editor-container" class="sro-searches wp-editor-container ui-sortable">
            <?php $this->displaySearches(); ?>
            </div>
          </div>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div id="side-sortables" class="meta-box-sortables">
            <div id="submitdiv" class="postbox">
              <h3><span><?php echo __('Tagged Searches','searchresultsoptimizer'); ?></span></h3>
              <div class="inside">
                <div class="submitbox" id="submitpost">
                  <div id="minor-publishing">
                    <div class="misc-pub-section misc-pub-post-status tagchecklist sro">
                      <?php foreach ($this->getTheme()->getSearches() as $search): ?>
                        <span><a data-id="<?php echo $this->getTheme()->id; ?>" data-nonce="<?php echo $this->getTheme()->getUpdateNonce(); ?>" data-hash="<?php echo $search->normalized; ?>" class="sro-unlink-search ntdelbutton">X</a>&nbsp;<?php echo $search->query; ?></span>
                      <?php endforeach; ?>
                    </div>
                    <div class="misc-pub-section">
                      <p><?php echo __('Tag searches from the','searchresultsoptimizer'); ?> <a href="?page=searchresultsoptimizer_searches"><?php echo __('searches page','searchresultsoptimizer'); ?></a>.</p>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div id="major-publishing-actions">
                  <div class="submitbox" id="delete-action">
                    <a class="submitdelete deletion" href="?page=searchresultsoptimizer_themes&action=delete&nonce=<?php echo $this->getTheme()->getDeleteNonce(); ?>&id=<?php echo $this->getTheme()->id; ?>"><?php echo __('Delete Theme','searchresultsoptimizer'); ?></a></div>
                    <div id="publishing-action">
                      <span class="spinner"></span>
                      <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="Update">
                    </div>
                    <div class="clear"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
      <br class="clear">
    </div>
  </form>
</div>