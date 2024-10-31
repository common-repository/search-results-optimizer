jQuery('document').ready(function() {
  function unlinkSearch() {
    var search = jQuery(this);
    var nonce = jQuery(this).data('nonce');
    var id = jQuery(this).data('id');
    var hash = jQuery(this).data('hash');
    if (nonce && hash) {
      var data = {
        action: 'unlinkSearch',
        id: id,
        nonce: nonce,
        hash: hash
      };
      jQuery.post(ajaxurl, data, function(response) {
        response = jQuery.parseJSON(response);
        if (1 === response['error']) {
          if (undefined !== response['message']) {
            alert(response['message']);
          }
        } else {
          jQuery('a.sro-unlink-search[data-id="' + data.id + '"]').parent().fadeOut();
        }
      });
    }
  }
  jQuery('a.sro-unlink-search', '.sro').on('click', unlinkSearch);
  
  jQuery('a.sro.add-theme').on('click', function() {
    jQuery('tr.highlight').removeClass('highlight').find('.ajaxtag,.column-themes span.delete').remove();
    if (jQuery(this).siblings('.ajaxtag').length == 0) {
      var hash = jQuery(this).data('hash');
      var html = [];
      html.push(
        '<span class="delete"> | <a href="#cancel" class="cancel-add-theme">Cancel</a></span>',
        '<div class="ajaxtag hide-if-no-js">',
        '<label class="screen-reader-text" for="new-theme">Themes</label>',
        '<div class="taghint" style="">Add New Theme</div>',
        '<p><input type="text" id="new-theme" name="newtheme" class="newtag form-input-tip" size="24" autocomplete="off" value="">',
        '<input type="hidden" name="hash" value="' + hash + '">',
        '<input type="button" class="button themeadd" value="Add"></p>',
        '</div>'
      );
      jQuery('a[data-hash=' + hash + ']').parents('tr').addClass('highlight');
      var nonce = jQuery(this).data('nonce');
      jQuery(this).after(html.join(''));
      var addButton = jQuery(this).siblings('div').find('input.button.themeadd');
      jQuery('a.cancel-add-theme').on('click', function() {
        jQuery(this).parent().siblings('div.ajaxtag').remove();
        jQuery('.highlight').removeClass('highlight');
        jQuery(this).parent().remove();
      });
      jQuery(addButton).click(function() {
        var data = {
          action: 'linkSearch',
          nonce: nonce,
          hash: hash,
          name: jQuery(this).siblings('input[name="newtheme"]').val()
        };
        if ('' != data.name) {
          jQuery.post(ajaxurl, data, function(response) {
            response = jQuery.parseJSON(response);
            if (1 === response['error']) {
              if (undefined !== response['message']) {
                alert(response['message']);
              }
            } else {
              jQuery('.highlight').find('div.tagchecklist.sro').append("<span><a data-id='" + response['id'] + "' data-nonce='" + response['nonce'] + "' data-hash='" + data.hash + "' class='sro-unlink-search ntdelbutton'>X</a>&nbsp;" + data.name + "</span>");
              jQuery('a[data-id="' + response['id'] + '"]', '.highlight').on('click', unlinkSearch);
              jQuery('.highlight').find('a.sro.add-theme').html(jQuery('.highlight').find('a.sro.add-theme').data('alternate'));
              jQuery('.highlight').find('span.delete').remove();
            }
            jQuery(addButton).parents('.ajaxtag').remove();
            jQuery('.highlight').removeClass('highlight');
          });
        }
      });
    }
    jQuery(this).siblings('div').find('.newtag').focus();
  });
  
  jQuery('input.themeadd', '.sro-searches-edit').click(function() {
    var data = {
      action: 'linkSearch',
      nonce: jQuery(this).siblings('input[name="themenonce"]').val(),
      hash: jQuery(this).siblings('input[name="hash"]').val(),
      name: jQuery(this).siblings('input#new-theme').val()
    };
    if ('' != data.name) {
      jQuery.post(ajaxurl, data, function(response) {
        response = jQuery.parseJSON(response);
        if (1 === response['error']) {
          if (undefined !== response['message']) {
            alert(response['message']);
          }
        } else {
          jQuery('div.tagchecklist.sro').append('<span><a data-id="' + response['id'] + '" data-nonce="' + response['nonce'] + '" data-hash="' + data.hash + '" class="sro-unlink-search ntdelbutton">X</a>&nbsp;' + data.name + '</span>');
          jQuery('a[data-id="' + response['id'] + '"]', '.sro').on('click', unlinkSearch);
          jQuery('input#new-theme').val('');
        }
      });
    }
  });
  
  jQuery('div.sro-searches').sortable({
    opacity: 0.6,
    revert: true,
    cursor: 'move',
    items: '.sro-result',
    stop: function(event, ui) {
      jQuery('input[name="order"]').val('');
      jQuery('div.sro-result').each(function() {
        var result = jQuery(this);
        jQuery('input[name="order"]').val(function(i,val) {
          return val + (!val ? '' : ',') + jQuery(result).data('id');
        });
      });
      jQuery('div.sro-pin', 'div.sro-result.pinned:not(:first-child)').each(function() {
        jQuery(this).html(jQuery(this).data('unpinned-text')).parents('div.sro-result').removeClass('pinned');
      });
      if (jQuery('div.sro-result.pinned:first-child').length) {
        jQuery('input[name="pin"]').val(jQuery('div.sro-result.pinned:first-child').data('id'));
      } else {
        jQuery('input[name="pin"]').val('');
      }
    }
  });
  
  jQuery('div.sro-pin').click(function() {
    jQuery('input[name="pin"]').val('');
    if (jQuery(this).parents('div.sro-result').hasClass('pinned')) {
      jQuery(this).parents('div.sro-result').removeClass('pinned');
      jQuery(this).html(jQuery(this).data('unpinned-text'));
    } else {
      jQuery(this).parents('div.sro-result').addClass('pinned').siblings('div.sro-result').removeClass('pinned');
      jQuery(this).html(jQuery(this).data('pinned-text'));
      if (jQuery('div.sro-result.pinned:first-child').length) {
        jQuery('input[name="pin"]').val(jQuery(this).parents('div.sro-result').data('id'));
      }
    }
  });
  
  jQuery('#searchresultsoptimizer_result_highlighting_colour').wpColorPicker();
  
  jQuery('input#searchresultsoptimizer_sorting_popularity').change(function() {
    if ('checked' == jQuery(this).attr('checked')) {
      jQuery('select#searchresultsoptimizer_sorting_secondary,input#searchresultsoptimizer_result_pinning_enabled').removeAttr('disabled');
    } else {
      jQuery('select#searchresultsoptimizer_sorting_secondary,input#searchresultsoptimizer_result_pinning_enabled').attr('disabled','disabled');
    }
  });
  
  jQuery('input#searchresultsoptimizer_advanced_search_enabled').change(function() {
    if ('checked' == jQuery(this).attr('checked')) {
      jQuery('input#searchresultsoptimizer_metadata_filters_tags,input#searchresultsoptimizer_metadata_filters_categories,input#searchresultsoptimizer_metadata_filters_types').removeAttr('disabled');
    } else {
      jQuery('input#searchresultsoptimizer_metadata_filters_tags,input#searchresultsoptimizer_metadata_filters_categories,input#searchresultsoptimizer_metadata_filters_types').attr('disabled','disabled');
    }
  });

});

google.load('visualization', '1.0', {'packages':['corechart']});
if (jQuery('.sro-report').length) {
  google.setOnLoadCallback(drawCharts);
}

function drawCharts() {
  jQuery('div.sro-report').each(function(c) {
    var data = new google.visualization.DataTable();

    jQuery("th",jQuery('thead:first-child', jQuery(this))).each(function(i) { 
      data.addColumn((0 === i ? 'string' : 'number'), this.innerHTML);
    });
    var largest = 0;
    var rows = jQuery("tbody tr",jQuery(this)).map(function() { 
      return [jQuery("td",this).map(function() {
        if (!isNaN(this.innerHTML)) {
          largest = (Number(this.innerHTML) > largest) ? Number(this.innerHTML) : largest;
          return Number(this.innerHTML);
        } else {
          return this.innerHTML;
        }
      }).get()];
    }).get();
    
    if (1 === rows[0].length) {
      return;
    }
    
    data.addRows(rows);

    var options = {
      colors: ['#E8D790','#D98372','#C76274','#7F616F','#7F9799'],
      'vAxis': {
        'minValue': 0,
        'maxValue': largest
      }
    };
    var id = 'chart_' + (c+1);
    var canvas = jQuery(this).find('.chart').attr('id',id);
    var chart = new google.visualization.ColumnChart(document.getElementById(id));
    chart.draw(data, options);

  });
}