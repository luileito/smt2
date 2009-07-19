/**
 *  (smt) Modal Window  
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.modal {

    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.Sprite;
    import flash.display.Graphics;
    import flash.geom.Rectangle;

    public class ModalWindow extends Sprite
    {
        private static var $sprite:Sprite;
        private static var $container:DisplayObjectContainer;
        
        protected function show(stage:DisplayObjectContainer, object:DisplayObject):void
        {
            $container = DisplayObjectContainer(stage.root);

            $sprite = new Sprite();
            $sprite.graphics.beginFill(0x000000);
            $sprite.graphics.drawRect($container.x, $container.y, $container.width, $container.height);
            $sprite.graphics.endFill();
            $sprite.addChild(object);
            $sprite.alpha = 0.9;                     

            $container.addChild($sprite);                    
            $container.setChildIndex($sprite, $container.numChildren - 1);
            
            object.x = $container.width/2 - object.width/2;
            object.y = $container.height/2 - object.height/2;
        };
        
        protected function close():void
        {
            $container.removeChild($sprite);        
        };

    }
}
