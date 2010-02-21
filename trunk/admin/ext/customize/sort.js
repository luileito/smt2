$(function(){
    // load results in this element
    var infoDiv = $("#info");
    // this layer was hidden via CSS because the interaction is JavaScript-dependent
    $('#cms-sortables').show();
    // set sortable list
    init();
    
    function init()
    {
      $('div.groupWrapper').Sortable({
			  opacity:      0.6,
				accept:       'groupItem',
				helperclass:  'sortHelper',
				handle:       'div.itemHeader',
				tolerance:    'pointer',
				onChange: function() {
				  serialize();
				},
				onStart: function() {
					$.iAutoscroller.start(this, document.getElementsByTagName('body'));
				},
				onStop: function() {
					$.iAutoscroller.stop();
				}
		  });
    };
		
		function serialize(s)
    {
    	var serial = $.SortSerialize(s); // array name is layer id ("sort" in this case)
    	infoDiv.load("sort.php?"+serial.hash, delayCallback);
    };
    
    function delayCallback()
    {
      setTimeout(function(){
        infoDiv.slideUp("slow", resetInfoDiv);
      }, 1000);
    };
    
    function resetInfoDiv()
    {
      infoDiv.text("").show();
      $('#sort').load("reset.php", init);
    };
    
    // set the reset link behaviour
    $('a#resetorder').click(function(e){
      infoDiv.load("sort.php?reset", delayCallback);
      e.preventDefault();
    });
});