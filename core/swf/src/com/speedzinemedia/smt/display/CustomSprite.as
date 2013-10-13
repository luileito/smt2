/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {
	
	import flash.display.Sprite;
	
	public class CustomSprite extends Sprite {
	
    private var _color:uint;
          
    public function CustomSprite(){};
        
    // getter/setters
		public function set color(c:uint):void
		{
			_color = c;
		};
		
		public function get color():uint
		{
			return _color;
		};
        
  } // end class
}
