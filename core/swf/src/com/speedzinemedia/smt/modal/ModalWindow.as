/**
 *  (smt) Modal Window  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.events.MouseEvent;
    import flash.geom.Rectangle;
    import flash.text.TextField;
    
    import caurina.transitions.Tweener;
    
    public class ModalWindow extends Sprite
    {
        private static var $root:DisplayObjectContainer;
        private static var $container:Sprite;
        private static var $bg:Shape;
        
        protected function show(stage:DisplayObjectContainer, object:DisplayObject):void
        {
            // init
            $root = DisplayObjectContainer(stage.root);
            $container = new Sprite();
            $bg = new Shape();
            // draw background
            $bg.graphics.beginFill(0x000000);
            $bg.graphics.drawRect($root.x, $root.y, $root.width, $root.height);
            $bg.graphics.endFill();
            $bg.alpha = 0.9;
            // update display list
            $container.addChild($bg);
            $container.addChild(object);
            $root.addChild($container);
            $root.setChildIndex($container, $root.numChildren - 1);
            // center object
            object.x = $root.width/2 - object.width/2;
            object.y = $root.height/2 - object.height/2;
            // the stage size could be quite bigger thatn the viewport, so use mouse position
            var endY:int = Math.round($root.mouseY - object.height);
            // check vertical boundary
            if (endY < object.height/2) { endY = object.height/2; }
            // animate
            Tweener.addTween(object, {y:endY, time:1, transition:"easeOutQuad"});
            // listen
            object.addEventListener(MouseEvent.MOUSE_DOWN, dragContent);
            object.addEventListener(MouseEvent.MOUSE_UP, dropContent);
            $container.addEventListener(MouseEvent.CLICK, hideBackground);
        };
        
        protected function close():void
        {
            $root.removeChild($container);        
        };
         
        private function dragContent(e:MouseEvent):void 
        {
            if (e.target is TextField) {
                e.currentTarget.startDrag();
            }
        };
        
        private function dropContent(e:MouseEvent):void 
        {
            if (e.target is TextField) {
                e.currentTarget.stopDrag();
            }   
        };
        
        private function hideBackground(e:MouseEvent):void 
        {
            if (e.target is Sprite) {
                $container.removeChild($bg);
            }   
        };
        
    }
}