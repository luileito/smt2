package com.speedzinemedia.smt.text {

    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.display.InteractiveObject;
    import flash.text.TextField;
    import flash.text.TextFieldAutoSize;
	  import flash.text.TextFormat;
    /**
     *  Tooltip class.
     *  This is a singleton class, and can only be accessed through Tooltip.instance() method.
     *  The basic idea is to reuse a single tooltip instance for all interactive objects on the stage.
     *  Basic html markup (those tags supported by Flash Player) may be used in the tooltip text as well.
     *  @version    2.0 - 12 Feb 2009
     *  @autor      Luis Leiva
     *  @usage      package {
     *                  class TtExample {
     *                      private var tip:Tooltip;
     *                      public function TtExample() {
     *                          tip = Tooltip.getInstance();
     *                          addChild(tip);
     *
     *                          var s:Shape = new Shape();
     *                          s.name = "this is a <b>tooltip</b> example";
     *
     *                          tip.addItem(s);
     *                      };
     *                  }
     *              }
     */
    public class Tooltip extends TextField 
    {
        private static var _instance:Tooltip = new Tooltip();
        //private static var _tabIndex:int = 0;
        
        public static function getInstance():Tooltip
        {
            return _instance;
        };

        /** @private */
        public function Tooltip()
        {
            if (_instance) {
                throw new Error("Tooltip is a singleton class, and can only be accessed through Tooltip.getInstance() method");
            } else init();
        };

        private function init():void
        {
            this.defaultTextFormat  = new TextFormat("_sans", 11);
            
            // customizable properties
            this.background         = true;
            this.backgroundColor    = 0xFFFFFF;
            this.border             = true;
            this.borderColor        = 0x000000;
            
            // non-customizable properties (actually they can be overridden)
            this.autoSize           = TextFieldAutoSize.LEFT;
            this.selectable         = false;
            // avoid flickering between tiny objects
            this.mouseEnabled = false;
            this.visible      = false;
        };
        
        /**
         * Registers an element to be "tooltiped"
         * @param   elem    InteractiveObject (e.g. Sprite, Shape, DisplayObjectContainer...)
         */
        public function addItem(elem:InteractiveObject):void
        {
            elem.addEventListener(MouseEvent.MOUSE_OVER, show);
            elem.addEventListener(MouseEvent.MOUSE_OUT, hide);
            //elem.tabIndex = _tabIndex++;
        };

        private function show(e:MouseEvent):void
        {
            this.htmlText = e.target.name;
            this.visible = true;
            
            this.addEventListener(Event.ENTER_FRAME, repos);
            // check
            if (stage.hasEventListener(MouseEvent.MOUSE_MOVE)) {
                stage.removeEventListener(MouseEvent.MOUSE_MOVE, updateCoords);
            }
        };

        private function hide(e:MouseEvent):void
        {
            this.htmlText = "";
            this.visible = false;
            
            this.removeEventListener(Event.ENTER_FRAME, repos);
            // meanwhile update Tooltip coordinates
            stage.addEventListener(MouseEvent.MOUSE_MOVE, updateCoords);
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

        private function updateCoords(e:MouseEvent):void
        {
            this.x = stage.mouseX;
            this.y = stage.mouseY;
        };
        
    } // end class
}
