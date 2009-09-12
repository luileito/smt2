$(function(){
  
  // open external links in a new browser tab
  $('a[rel=external]').attr("target", "_blank");
  
  // confirm critical actions
  $('.conf').click(function(e){
    return confirm("Are you sure?");
  });
  
});