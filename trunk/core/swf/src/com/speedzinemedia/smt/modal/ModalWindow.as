/**
 *  Modal Window.  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.Sprite;
    import flash.external.ExternalInterface;
    import flash.events.MouseEvent;
    import flash.events.KeyboardEvent;
    import flash.ui.Keyboard;
    
    import caurina.transitions.Tweener;
    import com.speedzinemedia.smt.display.CustomSprite;
    
    public class ModalWindow extends Sprite
    {
        private static var $root:DisplayObjectContainer;
        private static var $container:Sprite;
        private static var $content:DisplayObject;
        private static var $bg:Sprite;
        
        protected function show(stage:DisplayObjectContainer, object:DisplayObject):void
        {
            // init
            $root = DisplayObjectContainer(stage.root);
            $content = object;
            $container = new Sprite();
            $bg = new CustomSprite();
            // draw background
            $bg.graphics.beginFill(0x000000);
            $bg.graphics.drawRect($root.x, $root.y, $root.width, $root.height);
            $bg.graphics.endFill();
            $bg.alpha = 0;
            Tweener.addTween($bg, {alpha:1, time:0.5, transition:"easeOutQuad"});
            
            // update display list
            $container.addChild($bg);
            $container.addChild(object);
            $root.addChild($container);
            $root.setChildIndex($container, $root.numChildren - 1);
            
            // center object on viewport
            var offset:Object = ExternalInterface.call("window.smt2fn.getWindowOffset");
            var window:Object = ExternalInterface.call("window.smt2fn.getWindowSize");
            object.x = offset.x + window.width/2 - object.width/2;
            object.y = offset.y + window.height/2 - object.height/2;
            /*
            object.x = $root.width/2 - object.width/2;
            object.y = $root.height/2 - object.height/2;
            */
            // listen
            $container.addEventListener(MouseEvent.CLICK, onClick, true);
        };
        
        protected function close():void
        {
            $root.removeChild($container);        
        };
        
        private function onClick(e:*):void 
        {
            if (e.target is CustomSprite) {
                $container.removeChild($content);
                Tweener.addTween($bg, {alpha:0, time:0.5, transition:"easeOutQuart", onComplete:close});
            }
        };
    }
}