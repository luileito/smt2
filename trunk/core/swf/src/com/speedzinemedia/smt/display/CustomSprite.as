/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {
	
	import flash.display.Sprite;
	
	public class CustomSprite extends Sprite
    {
        //private var _id:String;
        private var _color:String;
        
        public function CustomSprite()
        {
            //super();
        };
        
        // getter/setters
		public function set color(str:String):void
		{
			_color = str;
		};
		
		public function get color():String
		{
			return _color;
		};
		/*
		public function set id(str:String):void
		{
			_id = str;
		};
		
		public function get id():String
		{
			return _id;
		};
        */
        
    } // end class
}