/**
 *  Draggable Sprite.  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.Bitmap;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.ui.Mouse;
    import flash.external.ExternalInterface;
    
    import com.speedzinemedia.smt.display.Asset;
    
    public class ModalDraggable extends Sprite
    {
        private static var $content:*;
        private static var $move:Bitmap;
        private static var $drag:Sprite;
        
        /**
         *  Constructor.        
         *  @param object instance to allow dragging
         */
        public function ModalDraggable(object:*):void
        {
            $content = object;
            
            $drag = new Sprite();
            $drag.graphics.beginFill(0xAAAAAA);
            $drag.graphics.drawRect(0,-15, $content.width, 15);
            $drag.graphics.endFill();
            
            addListeners();
            object.addEventListener(Event.REMOVED_FROM_STAGE, removeListeners);
            
            $content.addChild($drag);
            
            $move = new Asset.cursorMove();
            $move.visible = false;
            $content.addChild($move);
        };
        
        private function addListeners():void 
        {
            $drag.addEventListener(MouseEvent.MOUSE_DOWN, dragContent);
            $drag.addEventListener(MouseEvent.MOUSE_UP,   dropContent);
            $drag.addEventListener(MouseEvent.MOUSE_OVER, showMoveCursor);
            $drag.addEventListener(MouseEvent.MOUSE_OUT,  hideMoveCursor);
            $drag.addEventListener(MouseEvent.MOUSE_MOVE, moveMoveCursor);
        };
        
        private function removeListeners(e:Event):void 
        {   
            $drag.removeEventListener(MouseEvent.MOUSE_DOWN, dragContent);
            $drag.removeEventListener(MouseEvent.MOUSE_UP,   dropContent);
            $drag.removeEventListener(MouseEvent.MOUSE_OVER, showMoveCursor);
            $drag.removeEventListener(MouseEvent.MOUSE_OUT,  hideMoveCursor);
            $drag.removeEventListener(MouseEvent.MOUSE_MOVE, moveMoveCursor);
        };
        
        protected function dragContent(e:MouseEvent):void 
        {
            $content.startDrag();
            e.stopImmediatePropagation();
        };
        
        protected function dropContent(e:MouseEvent):void 
        {
            $content.stopDrag();
            e.stopImmediatePropagation();
        };
        
        private function showMoveCursor(e:MouseEvent):void 
        {
            Mouse.hide();
            $move.visible = true;
        };
        
        private function hideMoveCursor(e:MouseEvent):void 
        {
            Mouse.show();
            $move.visible = false;
        };
        
        private function moveMoveCursor(e:MouseEvent):void 
        {
            // substract to set the registration point at center
            $move.x = e.localX - $move.width/2; 
            $move.y = e.localY - $move.height/2;
        };
        
    }
}