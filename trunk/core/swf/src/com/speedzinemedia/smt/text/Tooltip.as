/**
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva
 *  @usage      package {
 *                  ... imports ...
 *                  class TtExample {
 *                      ... 
 *                      private var $tip:Tooltip;
 *                      ...
 *                      public function TtExample(){
 *                          ...
 *                          var s:Shape = new Shape();
 *                          s.name = "this is the text that will be shown";
 *                          s.addEventListener(MouseEvent.MOUSE_OVER, showTip);
 *                          s.addEventListener(MouseEvent.MOUSE_OUT, hideTip);   
 *                          ...
 *                          $tip = new Tooltip();
 *                          addChild($tip);
 *                      };
 *                      private function showTip(e:MouseEvent):void {
 *                          $tip.show(e.target.name); 
 *                      };
 *                      private function hideTip(e:MouseEvent):void {
 *                          $tip.hide(); 
 *                      };  
 *                  } 
 *              }    
 */
package com.speedzinemedia.smt.text {

    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.text.TextField;
    import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;
    
    public class Tooltip extends TextField 
    {
        private var $local:Boolean; // useful when scaling Sprite containers
        
        public function Tooltip(local2global:Boolean = false)
        {
            var fmt:TextFormat = new TextFormat("_sans", 10);
            this.defaultTextFormat = fmt;
            this.autoSize = TextFieldAutoSize.LEFT;
            this.selectable = false;
            this.background = true;
            this.backgroundColor = 0xFFFFFF;
            this.border = true;
            this.borderColor = 0x000000;
            this.visible = false;
            this.mouseEnabled = false; // avoid flickering
            
            $local = local2global;
        };
        
        public function show(str:String):void
        {
            this.text = str;
            this.visible = true;
            if (!$local) { 
                this.addEventListener(Event.ENTER_FRAME, repos); 
            } else {
                updatePos();
            }
        };
        
        public function hide():void
        {
            this.text = "";
            this.visible = false;
            if (!$local) { 
                this.removeEventListener(Event.ENTER_FRAME, repos); 
            } else {
                updatePos();
            }
        };

        private function repos(e:Event):void
        {
            var offset:int = 10;
            // compute boundaries
            this.x = (stage.mouseX + this.width + offset > stage.stageWidth) ?
                      stage.mouseX - this.width - offset :
                      stage.mouseX + offset;
            this.y = (stage.mouseY + this.height > stage.stageHeight) ?
                      stage.mouseY - this.height :
                      stage.mouseY;
        };
        
        private function updatePos():void
        {
            this.x = mouseX;
            this.y = mouseY;
        };
        
    } // end class
}