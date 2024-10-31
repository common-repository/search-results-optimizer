<?php
if ('1' === \get_option('searchresultsoptimizer_advanced_search_enabled')):
  $action = \esc_url(home_url('/'));
  $searchFor = __('Search for', 'searchresultsoptimizer');
  $search = __('Search', 'searchresultsoptimizer');
  $query = \get_search_query();
  global $sroPlugin; ?>
  <form role="search" method="get" class="searchresultsoptimizer search-form entry-content" action="<?php echo $action; ?>">
    <div class='searchresultsoptimizer_advanced_search'>
      <div class='searchresultsoptimizer_advanced_search_terms searchresultsoptimizer_advanced_option'>
        <span class='searchresultsoptimizer_advanced_search_label'><?php echo __('Search for', 'searchresultsoptimizer'); ?>:</span>
        <input type="search" class="sro-search-field" placeholder="<?php echo $search; ?> &hellip;" value="<?php echo $query; ?>" name="s" required="required">
        <input type="submit" class="search-submit" value="<?php echo $search; ?>" />
        <div class='srocf'></div>
      </div>
      <div class='searchresultsoptimizer_advanced_filters'>
      <?php if ('1' === \get_option('searchresultsoptimizer_metadata_filters_types')): ?>
        <div class='searchresultsoptimizer_advanced_search_types searchresultsoptimizer_advanced_option'>
          <span class='searchresultsoptimizer_advanced_search_label'><?php echo __('Types', 'searchresultsoptimizer'); ?>:</span>
          <select id='typelist' multiple>
          <?php foreach ($sroPlugin->sroBuilder->getEnabledTypes() as $name): ?>
            <option value='<?php echo $name; ?>' <?php echo ($sroPlugin->sroBuilder->typeWasChecked($name) ? 'selected="selected"' : ''); ?>><?php echo ucfirst($name); ?></option>
          <?php endforeach; ?>
          </select>
          <input type="hidden" name="typelist" value="">
          <div class='srocf'></div>
        </div>
      <?php endif;
      if ('1' === \get_option('searchresultsoptimizer_metadata_filters_tags')): ?>
        <div class='searchresultsoptimizer_advanced_search_tags searchresultsoptimizer_advanced_option'>
          <span class='searchresultsoptimizer_advanced_search_label'><?php echo __('Tags', 'searchresultsoptimizer'); ?>:</span>
          <select id='taglist' multiple>
          <?php foreach ($sroPlugin->sroBuilder->getEnabledTags() as $id => $name): ?>
            <option value='<?php echo $id; ?>'<?php echo ($sroPlugin->sroBuilder->tagWasChecked($id) ? 'selected="selected"' : ''); ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
          </select>
          <input type="hidden" name="taglist" value="">
          <div class='restrictor'>
            <label for='tags_combined'>
              <sup>
                <input name='tags_combined' type='checkbox' value='1' <?php echo ($sroPlugin->sroBuilder->tagWasRestricted() ? 'checked="checked"' : ''); ?>>
                <?php echo __('with all of these tags?', 'searchresultsoptimizer'); ?>
              </sup>
            </label>
          </div>
        </div>
      <?php endif;
      if ('1' === \get_option('searchresultsoptimizer_metadata_filters_categories')): ?>
        <div class='searchresultsoptimizer_advanced_search_categories searchresultsoptimizer_advanced_option'>
          <span class='searchresultsoptimizer_advanced_search_label'><?php echo __('Categories', 'searchresultsoptimizer'); ?>:</span>
          <select id='catlist' multiple>
          <?php foreach ($sroPlugin->sroBuilder->getEnabledCategories() as $id => $name): ?>
            <option value='<?php echo $id; ?>'<?php echo ($sroPlugin->sroBuilder->categoryWasChecked($id) ? 'selected="selected"' : ''); ?>><?php echo ucfirst($name); ?></option>
          <?php endforeach; ?>
          </select>
          <input type="hidden" name="catlist" value="">
          <div class='restrictor'>
            <label for='cats_combined'>
              <sup>
                <input name='cats_combined' type='checkbox' value='1' <?php echo ($sroPlugin->sroBuilder->categoryWasRestricted() ? 'checked="checked"' : ''); ?>>
                <?php echo __('with all of these categories?', 'searchresultsoptimizer'); ?>
              </sup>
            </label>
          </div>
        </div>
      <?php endif; ?>
      </div>
      <div class='srocf'></div>
    </div>
  </form>
<?php endif; ?>
<?php if (!empty($assitiveSearch)): ?>
  <div class="sro-related-searches">
    <h4>Related Searches</h4>
    <ul>
    <?php foreach($assitiveSearch as $search): ?>
      <li><a href="/?s=<?php echo urlencode($search->query); ?>"><?php echo $search->query; ?></a></li>
    <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>