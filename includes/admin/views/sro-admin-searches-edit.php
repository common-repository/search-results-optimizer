<div class="wrap searchresultsoptimizer sro-searches-edit">
  <h2><?php echo __('Searches', 'searchresultsoptimizer'); ?></h2>
  <form method="post">
    <input type="hidden" name="nonce" value="<?php echo $this->getSearch()->getUpdateNonce(); ?>">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
              <input type="text" readonly="readonly "size="64" value="<?php echo $this->getSearch()->query; ?>" id="title" autocomplete="off">
            </div>
          </div>
          <h2><?php echo __('Most popular results', 'searchresultsoptimizer'); ?></h2>
          <div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap">
            <div id="wp-content-editor-container" class="sro-searches wp-editor-container ui-sortable pinning<?php echo \get_option('searchresultsoptimizer_result_pinning_enabled'); ?>">
            <?php $this->displaySearches(); ?>
            </div>
          </div>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div id="side-sortables" class="meta-box-sortables">
            <div id="submitdiv" class="postbox">
              <h3><span><?php echo __('Associated Themes','searchresultsoptimizer'); ?></span></h3>
              <div class="inside">
                <div class="submitbox" id="submitpost">
                  <div id="minor-publishing">
                    <div class="misc-pub-section misc-pub-post-status tagchecklist sro">
                      <?php foreach ($this->getSearch()->getThemes() as $theme): ?>
                        <span><a data-id="<?php echo $theme->id; ?>" data-nonce="<?php echo $theme->getUpdateNonce(); ?>" data-hash="<?php echo $this->getSearch()->normalized; ?>" class="sro-unlink-search ntdelbutton">X</a>&nbsp;<?php echo $theme->name; ?></span>
                      <?php endforeach; ?>
                    </div>
                    <div class="ajaxtag hide-if-no-js">
                      <label class="screen-reader-text" for="new-theme">Themes</label>
                      <p>
                        <input type="text" id="new-theme" name="newtheme" class="newtag form-input-tip" size="24" autocomplete="off" value="">
                        <input type="hidden" name="hash" value="<?php echo $this->getSearch()->normalized; ?>">
                        <input type="hidden" name="themenonce" value="<?php $theme = new \SearchResultsOptimizer\includes\classes\SROTheme(); echo $theme->getAddNonce(); ?>">
                        <input type="button" class="button themeadd" value="<?php echo __('Add', 'searchresultsoptimizer'); ?>">
                      </p>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div id="major-publishing-actions">
                  <div class="submitbox" id="delete-action">
                    <a class="submitdelete deletion" href="?page=searchresultsoptimizer_searches&action=delete&nonce=<?php echo $this->getSearch()->getDeleteNonce(); ?>&id=<?php echo $this->getSearch()->id; ?>"><?php echo __('Delete Search','searchresultsoptimizer'); ?></a></div>
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