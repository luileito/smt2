package com.speedzinemedia.smt.display {

    import flash.display.Sprite;
	import flash.events.MouseEvent;
	
	import com.earthbrowser.ebutils.MacMouseWheelHandler;
	import com.speedzinemedia.smt.utils.PlayerInfo;
	/**
	 * Class for zooming Sprites with the mouse wheel.
	 * Fixes the Flash Player bug on Mac (http://bugs.adobe.com/jira/browse/FP-503).
	 * @autor Luis Leiva
	 * @date 14-Ene-2010
	 */
    public class Zoomable extends Control
    {
        public function Zoomable(obj:Sprite)
        {
            super(obj);
			
			// fix mouse wheel support on Mac (http://bugs.adobe.com/jira/browse/FP-503)
			if ( PlayerInfo.isMac() ) {
				MacMouseWheelHandler.init(target.stage);
			}
			
			target.addEventListener(MouseEvent.MOUSE_WHEEL, zoom);
        };
		
		private function zoom(e:MouseEvent):void 
		{
			//if (e.target !== target) return; // too strict for zooming!
			
			var allowed:Boolean = e.target is Sprite;
			if (!allowed) return;
			
			var mod:Number = 5;

			var sX:Number = target.scaleX + (e.delta / mod);
			var sY:Number = target.scaleY + (e.delta / mod);
			
			if (sX < 0.1) return;
			
			target.scaleX = sX;
			target.scaleY = sY;
			
			target.x = ((2 * target.stage.mouseX) - (2 * (e.localX * target.scaleX))) / 2;
			target.y = ((2 * target.stage.mouseY) - (2 * (e.localY * target.scaleY))) / 2;
		};
			
	} // end class
}