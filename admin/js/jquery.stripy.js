jQuery.fn.stripy = function(options) {

  // default settings
  var options = jQuery.extend({
    background_even:  "#DDD", // background color for even rows
    background_odd:   "#EEE", // background color for odd rows
    font_even:        "#000", // font color for even rows
    font_odd:         "#000", // font color for odd rows
    background_hover: "#FFC", // hover background color
    font_hover:       "#000"  // hover font color
  }, options);

  return this.each(function() {
    // even rows
    jQuery(this).find('tr:even')
            .css('background-color', options.background_even)
            .css('color', options.font_even)
            .hover(
              function() {
        	       jQuery(this)
                 .css('background-color', options.background_hover)
                 .css('color', options.font_hover);
              },
             function() {
                jQuery(this)
                .css('background-color', options.background_even)
                .css('color', options.font_even);
             }
            );

    // odd rows
    jQuery(this).find('tr:odd')
            .css('background-color', options.background_odd)
            .css('color', options.font_odd)
            .hover(
              function() {
        	       jQuery(this)
                 .css('background-color', options.background_hover)
                 .css('color', options.font_hover);
              },
             function() {
                jQuery(this)
                .css('background-color', options.background_odd)
                .css('color', options.font_odd);
             }
            );

  });

};