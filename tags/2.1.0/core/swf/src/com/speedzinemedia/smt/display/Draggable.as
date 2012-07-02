package com.speedzinemedia.smt.display {

    import flash.display.Bitmap;
    import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.ui.Mouse;
	import flash.ui.MouseCursor;

	import com.speedzinemedia.smt.display.Asset;
	import com.speedzinemedia.smt.display.Pannable;
	import com.speedzinemedia.smt.utils.PlayerInfo;

	/**
	 * Class for dragging Sprites. 
	 * Extends the functionality of Panable class, adding a nice cursor while moving.
	 * @autor Luis Leiva
	 * @date 14-Ene-2010
	 */
    public class Draggable extends Pannable
    {
        // decorative (but usability-friendly) cursor
		private var $move:Bitmap 	= new Asset.cursorMove();
		private var $fp10:Boolean 	= (PlayerInfo.getPlayerVersion() >= 10);
		
        public function Draggable(obj:Sprite)
        {
            super(obj);
        };
		
		/** Adds the cursor Asset. */
		override protected function drag(e:MouseEvent):void 
		{
			if (e.target !== target) return;
			
			super.drag(e);

			if ($fp10) {
				Mouse.cursor = MouseCursor.HAND;
			} else {
				Mouse.hide();
    // attach cursor to stage to make it scale-independent
				target.stage.addChild($move);
				target.addEventListener(MouseEvent.MOUSE_MOVE, pos);
				// update cursor position
				pos(e);
			}
		};
		
		/** Removes the cursor Asset. */
		override protected function drop(e:MouseEvent):void 
		{
			super.drop(e);
			
			if ($fp10) {
				Mouse.cursor = MouseCursor.AUTO;
			} else {
				Mouse.show();
    // detach cursor
				target.stage.removeChild($move);
				target.removeEventListener(MouseEvent.MOUSE_MOVE, pos);
			}
		};

  private function pos(e:MouseEvent):void
		{
			$move.x = e.stageX - $move.width/2;
			$move.y = e.stageY - $move.height/2;

			e.updateAfterEvent();
		};
			
	} // end class
}