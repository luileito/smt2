/**
 * Hypermedia player class.
 * @author  Luis Leiva
 * @date    13 Dec 2009
 */
package com.speedzinemedia.smt.display {

    import flash.display.DisplayObjectContainer;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.events.TimerEvent;
    import flash.geom.Point;
    import flash.text.TextField;
    import flash.text.TextFieldAutoSize;
    import flash.text.TextFormat;
    import flash.utils.Timer;
    import flash.external.ExternalInterface;
    
    import caurina.transitions.Tweener;
    import com.speedzinemedia.smt.display.Draggable;
    //import com.speedzinemedia.smt.display.Scrubber;
    import com.speedzinemedia.smt.display.ScrubberInteractive;    
    import com.speedzinemedia.smt.draw.DrawUtils;    
    import com.speedzinemedia.smt.events.PlayerEvent;
    import com.speedzinemedia.smt.interfaces.ITimelineControls;
    import com.speedzinemedia.smt.utils.Maths;
    import com.speedzinemedia.smt.utils.TimeUtils;

    public class Player extends Sprite implements ITimelineControls
    {        
        private var __player:Sprite = new Sprite();
        private var __scrubber:ScrubberInteractive;
        private var __play:Sprite;
        private var __pause:Sprite;
        private var __stop:Sprite;
        private var __timeLoop:Timer;
        private var __timeout:Timer;
        private var __timeStart:TextField;
        private var __timeEnd:TextField;
        private var __seconds:int = 0;
        private var __maxSeconds:int = 0;
        private const BUTTON_SIZE:int = 25;
        private var __scrollingOffset:Object = {x:0, y:0};        
        
        public function get seconds():int { return __seconds; };
        /**
         * Constructor.
         * @param prop: { time, width, height, color }
         */
        public function Player(parent:DisplayObjectContainer, prop:Object)
        {
            // launch timer
            __timeLoop = new Timer(1000);
            __timeLoop.addEventListener(TimerEvent.TIMER, onProgress);
            __timeLoop.start();
            
            var width:int = 600;
            var height:int = 50;
            // draw background
            __player.graphics.lineStyle(3, 0x555555);
            __player.graphics.beginFill(0x333333, .8);
            __player.graphics.drawRoundRect(0,0, width,height, height/3);
            __player.graphics.endFill();

            var scrubberContainer:Sprite = new Sprite();
            var scrubberContainerWidth:int = width/1.4;
            var scrubberContainerHeight:int = 5;
            var scrubberContainerBorderOffset:int = 3;
            
            __timeStart = createTimeTextField();
            __timeEnd = createTimeTextField();
            
            __timeStart.x = height/3; // note: relative to parent container (control Sprite)
            
            // scrubber itself
            __scrubber = new ScrubberInteractive({time:prop.time, fps:prop.fps, 
              width:scrubberContainerWidth, height:scrubberContainerHeight});
            __scrubber.x = __timeStart.x * 1.2 + __timeStart.width;
            __scrubber.y = (height - __timeStart.height)/4;
            __timeEnd.x = __scrubber.x * 1.1 + __scrubber.width;
            
            __play = createPlayButton(BUTTON_SIZE);
            __play.x = width - __play.width;        // ~ align right
            __play.y = (__play.height - height)/4;  // ~ valign center
            
            __pause = createPauseButton(BUTTON_SIZE);
            __pause.x = __play.x;
            __pause.y = __play.y;
            
            __stop = createReloadButton(BUTTON_SIZE); // createStopButton
            __stop.x = __play.x;
            __stop.y = __play.y;
            __stop.visible = false;
            
            __maxSeconds = prop.time;
              
            // scrubber boundaries decoration
            scrubberContainer.graphics.lineStyle(0, 0xEEEEEE);
            scrubberContainer.graphics.drawRect(__scrubber.x-scrubberContainerBorderOffset/2, 
                                        __scrubber.y-scrubberContainerBorderOffset/2,
                                        scrubberContainerWidth + scrubberContainerBorderOffset,
                                        scrubberContainerHeight + scrubberContainerBorderOffset);
            scrubberContainer.addChild(__scrubber);
            scrubberContainer.addEventListener(PlayerEvent.SEEK, onSeek);
            
            var controls:Sprite = new Sprite();                                        
            controls.x = 0;
            controls.y = height/2 - scrubberContainer.height;
            controls.addChild(scrubberContainer);
            controls.addChild(__timeStart);
            controls.addChild(__timeEnd);
            controls.addChild(__play);
            controls.addChild(__pause);
            controls.addChild(__stop);
            __player.addChild(controls);
            addChild(__player);
            
            new Draggable(__player);
            __player.alpha = 0;

            updatePlayerState();
            updateTime();

            this.addEventListener(Event.ADDED_TO_STAGE, onAddedToStage);
            parent.addChild(this);
        };
 
        private function onAddedToStage(e:Event):void
        {
            __player.x = 50; //(stage.stageWidth + __player.width)/2;
            __player.y = 50;

            __timeout = new Timer(3000, 1);
            __timeout.addEventListener(TimerEvent.TIMER_COMPLETE, onTimeout);
            __timeout.start();

            stage.addEventListener(MouseEvent.MOUSE_MOVE, onMouseMove);
        };

        private function onProgress(e:TimerEvent):void
        {
            if (__scrubber.paused) return;
            
            __seconds++;
            updateTime();
        };
        
        private function onSeek(e:PlayerEvent):void
        {
            var perc:Number = Number(e.data);
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.SEEK, perc));
            seek(perc);
        };
                
        private function onMouseMove(e:MouseEvent):void
        {
            Tweener.addTween(__player, {alpha:1, time:1, transition:"easeOutQuart", onComplete:resetTimeout});
        };
        
        private function resetTimeout():void
        {
            __timeout.reset();
            __timeout.start();
        };
        
        private function onTimeout(e:TimerEvent):void
        {
            // repos player according to viewport
            var offset:Object = ExternalInterface.call("window.smt2fn.getWindowOffset");
            Tweener.addTween(__player, {
              x:__player.x + offset.x - __scrollingOffset.x, 
              y:__player.y + offset.y - __scrollingOffset.y, 
              alpha:0, time:1, transition:"easeOutQuart"
            });
            // update
            __scrollingOffset = { x:offset.x, y:offset.y };
        };
        
        private function createTimeTextField():TextField
        {
            var fmtTime:TextFormat = new TextFormat();
            fmtTime.font = "_sans";
            fmtTime.color = 0xFFFFFF;
            var tf:TextField = new TextField();
            tf.defaultTextFormat = fmtTime;
            tf.maxChars = 5;
            tf.text = "00:00";
            tf.autoSize = TextFieldAutoSize.CENTER;
            tf.selectable = false;

            return tf;
        };
        
        private function createPlayButton(size:int):Sprite
        {
            var btn:Sprite = new Sprite();
            createButtonHitArea(btn,size);
            /*
            btn.graphics.beginFill(0x999999);
            btn.graphics.moveTo(0,0);
            btn.graphics.lineTo(size, size/2);
            btn.graphics.lineTo(0, size);
            btn.graphics.lineTo(0,0);
            btn.graphics.endFill();
            */
            var icon:Sprite = new Asset.ICON_PLAY();
            icon.width = icon.height = size;
            DrawUtils.changeInstanceColor(icon, 0x999999);
            btn.addChild(icon);
            btn.addEventListener(MouseEvent.CLICK, onClickPlayButton);
            
            return btn;
        };
        
        private function createPauseButton(size:int):Sprite
        {
            var btn:Sprite = new Sprite();
            createButtonHitArea(btn,size);
            /*
            btn.graphics.beginFill(0x999999);
            btn.graphics.drawRect(0,0, size/3,size);
            btn.graphics.drawRect(size*2/3,0, size/3,size);
            btn.graphics.endFill();
            */
            var icon:Sprite = new Asset.ICON_PAUSE();
            icon.width = icon.height = size;
            DrawUtils.changeInstanceColor(icon, 0x999999);
            btn.addChild(icon);            
            btn.addEventListener(MouseEvent.CLICK, onClickPauseButton);
            
            return btn;
        };
        
        private function createStopButton(size:int):Sprite
        {
            var btn:Sprite = new Sprite();
            createButtonHitArea(btn,size);
            btn.graphics.beginFill(0x999999);
            btn.graphics.drawRect(0,0, size,size);
            btn.graphics.endFill();
            btn.addEventListener(MouseEvent.CLICK, onClickStopButton);

            return btn;
        };

        private function createReloadButton(size:int):Sprite
        {
            var btn:Sprite = new Sprite();
            createButtonHitArea(btn,size);
            /*
            btn.graphics.beginFill(0x999999);
            DrawUtils.drawArc(btn, new Point(0,0), size/2, 45/360, -90/360, BUTTON_SIZE);
            btn.graphics.endFill();
            */
            var icon:Sprite = new Asset.ICON_RELOAD();
            icon.width = icon.height = size;
            DrawUtils.changeInstanceColor(icon, 0x999999);
            btn.addChild(icon);
            btn.addEventListener(MouseEvent.CLICK, onClickReloadButton);

            return btn;
        };
                
        private function createButtonHitArea(btn:Sprite, size:int, ds:Number = 1.5):void
        {
            var ha:Sprite = new Sprite();
            ha.graphics.beginFill(0xFF0000, 0);
            ha.graphics.drawRect(btn.x,btn.y,size*ds,size*ds);
            ha.graphics.endFill();
            ha.x -= (ha.width - size)/2;
            ha.y -= (ha.height - size)/2;
            btn.addChild(ha);
        };

        /** Toggles scrubber animation. */
        public function pause():void
        {
            __scrubber.pause();
            
            updatePlayerState();
        };

        /** Stops scrubber animation. */
        public function stop():void
        {
            __scrubber.stop();
            
            updatePlayerState();
        };

        /** Resumes scrubber animation. */
        public function resume():void
        {
            __scrubber.resume();
            
            updatePlayerState();
        };
                
        /** Finishes scrubber animation. */
        public function finish():void
        {
            __scrubber.finish();
            __seconds = __maxSeconds;
            __timeLoop.stop();

            updatePlayerState();
            updateTime();
        };

        /** Restarts scrubber animation. */
        public function restart():void
        {
            __seconds = 0;
            __stop.visible = false;
            __timeLoop.reset();
            __timeLoop.start();
            __scrubber.restart();
            
            updatePlayerState();
            updateTime();
        };

        /** Seeks to time percentage. */
        public function seek(perc:Number):void
        {
            __seconds = __maxSeconds * perc;
            __scrubber.seek(perc);
            updateTime();
        };
                
        private function updatePlayerState():void
        {
            if (__scrubber.finished) {
                __stop.visible = true;
                __play.visible = __pause.visible = false;
            } else {
                __play.visible = !__scrubber.paused;
                __pause.visible = __scrubber.paused;
            }
        };
        
        private function updateTime():void
        {
            if (__seconds >= __maxSeconds) {
              parent.dispatchEvent(new PlayerEvent(PlayerEvent.FINISH));
            } else {
              __timeStart.text = TimeUtils.timeToSMPTE(__seconds * __timeLoop.delay);
              __timeEnd.text = TimeUtils.timeToSMPTE( (__maxSeconds - __seconds) * __timeLoop.delay );
            }
        };
        
        private function onClickPlayButton(e:MouseEvent):void
        {
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.PLAY));
        };
        
        private function onClickPauseButton(e:MouseEvent):void
        {
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.PAUSE));
        };
        
        private function onClickStopButton(e:MouseEvent):void
        {
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.STOP));
        };
 
        private function onClickReloadButton(e:MouseEvent):void
        {
            parent.dispatchEvent(new PlayerEvent(PlayerEvent.RELOAD));
        };
                   
    } // end class
}
