package com.speedzinemedia.smt.display {

    import flash.display.Sprite;
    import flash.events.MouseEvent;
	
	/**
	 * Class for panning (dragging) Sprites.
	 * @autor Luis Leiva
	 * @date 14-Ene-2010
	 */
    public class Pannable extends Control
    {
        public function Pannable(obj:Sprite)
        {
            super(obj);

			target.addEventListener(MouseEvent.MOUSE_DOWN,  drag);
        };
		
        protected function drag(e:MouseEvent):void
		{
			if (e.target !== target) return;

			target.startDrag();
			target.addEventListener(MouseEvent.MOUSE_UP, drop);
		};
		
		protected function drop(e:MouseEvent):void 
		{
			target.stopDrag();
			target.removeEventListener(MouseEvent.MOUSE_UP, drop);
		};
			
	} // end class
}