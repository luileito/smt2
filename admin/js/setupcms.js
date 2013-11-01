var SetupCMS = {

    // use this function instead of .conf class because deletions are asynchronous
    deleteTrackingButtons: function()
    {
        // delete records link
        $('a.del').one("click", function(e){
          var self = $(this);
          var deleteLink = self.attr("href");
          var content = self.parent();
          var question = "Are you sure? This operation cannot be undone.";
          if (confirm(question)) {
            // send fake data on POST to prevent GET accesses to delete.php (the script itself adds the required query string)
            $.post(deleteLink, {'allowed':true}, function(data){
                if (data.success) {
                  content.html('<em>'+data.response+'</em>');
                  // visual response
                  var placeholder = content.parent();
                  placeholder.addClass("deleted");
                  setTimeout(function(){
                    placeholder.fadeOut("fast");
                  }, 2000);
                } else {
                  // wrap link for displaying server response error
                  self.wrap('<span />');
                  // then replace the whole link for the error text
                  content.find('span').html(data.response);
                }
            }, "json");
          }
         e.preventDefault();
        });
    },

    // use this function to replace JS visualizations if user have Flash Player
    viewTrackingButtons: function ()
    {
        // API type
        var api = (FlashDetect.installed) ? "swf" : "js";
        // anchors list
        var viewAnchor = $('a.view');
        $.each(viewAnchor, function(){
          var trackLink = $(this).attr("href");
          if (trackLink.indexOf("&api=") == -1) {
            // append API
            trackLink += "&api=" + api;
            $(this).attr("href", trackLink);
          }
        });
    },
    
    // open external links in a new browser tab
    externalLinks: function()
    {
        $('a[rel=external]').attr("target", "_blank");
    },

    // confirm critical actions on synch requests (e.g. posting a form)
    confirmActions: function()
    {
        $('.conf').click(function(e){
          return confirm("Are you sure?");
        });
    },
    
    // select all text when clicking on an input field
    inpuTexts: function()
    {
        $('input[type=text]').focus(function(){
          $(this).select();
        });
    },

    // fire all options (used in admin-logs section)
    all: function()
    {
        this.deleteTrackingButtons();
        //this.viewTrackingButtons(); // now using Flash API always
        this.externalLinks();
        this.confirmActions();
        this.inpuTexts();
    }
};

// apply common options for all CMS sections
$(function(){
  SetupCMS.all();
});
