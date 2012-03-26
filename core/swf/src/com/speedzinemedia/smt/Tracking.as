package com.speedzinemedia.smt {

    import flash.display.BlendMode;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.KeyboardEvent;
    import flash.external.ExternalInterface;
	  import flash.geom.Point;
    import flash.net.SharedObject;
    import flash.ui.Keyboard;

	  import caurina.transitions.Tweener;
	  import com.adobe.serialization.json.*;
	  //import de.polygonal.math.PM_PRNG;
	  import com.speedzinemedia.smt.events.TrackingEvent;
    import com.speedzinemedia.smt.events.ControlPanelEvent;
    import com.speedzinemedia.smt.events.PlayerEvent;
    import com.speedzinemedia.smt.display.Asset;
    import com.speedzinemedia.smt.display.ControlPanel;
    import com.speedzinemedia.smt.display.CustomSprite;
    import com.speedzinemedia.smt.display.Layers;
    import com.speedzinemedia.smt.display.Player;
    //import com.speedzinemedia.smt.display.Scrubber;
    import com.speedzinemedia.smt.draw.DrawUtils;
    import com.speedzinemedia.smt.mouse.MouseView;
	  import com.speedzinemedia.smt.mouse.MouseManager;
	  import com.speedzinemedia.smt.text.DebugText;
	  import com.speedzinemedia.smt.utils.Utils;
	
    /**
     *  (smt) Simple Mouse Tracking application
     *  @autor      Luis Leiva
     *  @version    2.0.1
     *  @date       16 Jan 2009
     */
    public class Tracking extends Sprite 
    {
        private var $FPS:int;                           // user data ...
        private var $currWindowWidth:int, $currWindowHeight:int;  
        private var $stageWidth:int, $stageHeight:int;
		    private var $users:String;
        
        private var $cp:ControlPanel;                   // control panel instance
        private var $savedSettings:SharedObject;        // get saved visualization settings
		    private var $MOUSE:MouseManager;                // mouse track(s) manager
		    //private var $scrubber:Scrubber;               // timeline scrubber
		    private var $player:Player;                     // timeline player (w/ scrubber)
		    private var $endQueue:int;                      // count finished mouse tracks
        
        public function Tracking() 
        {
            Utils.initStage(this);
            // get user data
            var p:Object = this.loaderInfo.parameters;
            try {
                $currWindowWidth  = Utils.getFlashVar(p.wcurr,     "int");
                $currWindowHeight = Utils.getFlashVar(p.hcurr,     "int");
                $stageWidth       = Utils.getFlashVar(p.wpage,     "int");
                $stageHeight      = Utils.getFlashVar(p.hpage,     "int");
                $FPS              = Utils.getFlashVar(p.fps,       "int");
                $users            = Utils.getFlashVar(p.users); // JSON string
            } catch (e:Error) {
                displayDebugText(e);
                return;
            }
            // check previous saved settings
            $savedSettings = SharedObject.getLocal("smt-controlPanel", "/");
            // if everything went OK, start
            init();            
        };
        
        private function displayDebugText(e:Error):void
        {
            var debug:DebugText = new DebugText(this, true, true); // background and selectable
            var reason:String = e.toString();
            // error info explained
            var link:String = "http://livedocs.adobe.com/flex/3/langref/runtimeErrors.html#" + e.errorID;
            reason += "\nSee <a href='" + link + "'>" + link + "</a>";
            // Flash Player debugger provides more info
            var stack:String = e.getStackTrace();
            if (stack) reason += "\n" + stack;
            
            debug.msg(reason);
        };
        
        private function init():void 
        {
            createLayers();
			      // set stage frame rate from user data
            stage.frameRate = $FPS;
            // allow pausing the mouse visualization
            stage.addEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
            // listen to changes from other classess
            addEventListener(ControlPanelEvent.TOGGLE_REPLAY_MODE, toggleReplay);
            addEventListener(TrackingEvent.MOUSE_END, endReplay);
            addEventListener(PlayerEvent.PLAY, onPlayerPlay);
            addEventListener(PlayerEvent.PAUSE, onPlayerPause);
            addEventListener(PlayerEvent.STOP, onPlayerStop);

            var userInfo:Array = loadUserInfo();
            $endQueue = userInfo.length;
            // create control panel
            $cp = new ControlPanel(this, userInfo);
			      // load Mouse Manager
			      $MOUSE = new MouseManager(this);
            
            // start replay
            if ($savedSettings.size > 0) {
              // use saved settings (realtime can be true or false)
              toggleReplay();
            } else {
              // begin with default settings (real-time replay, no heatmap visualizations)
				      $MOUSE.init();
            }
        };
        
        private function loadUserInfo():Array
        {
            var user:Object = JSON.decode(unescape($users));
            /*
            var rnd:PM_PRNG = new PM_PRNG();
            rnd.seed = Math.random() * 0x7FFFFFFE;
            */
            
            var drawCanvas:Array = buildCanvas();

            var arrOpts:Array = [];

            for (var i:int = 0, numUsers:int = user.length; i < numUsers; ++i)
            {
                // create info objects
				        var mouseInfo:Object = {
					        coords: { x: user[i].xcoords, y: user[i].ycoords, type: user[i].clicks },
					        fps:    $FPS
				        };
				        var screenInfo:Object = {
                  viewport:   { width: $currWindowWidth, height: $currWindowHeight },
				          currWindow: { width: $stageWidth,      height: $stageHeight      },
					        prevWindow: { width: user[i].wprev,    height: user[i].hprev     }
				        };
                // create mouse view instance
                var m:MouseView = new MouseView(mouseInfo, screenInfo, drawCanvas);

                if (numUsers > 1)
                {
                    if (user[i].avg) {
                        // hilite average path
                        m.thick = 3;
                        m.color = Layers.getColor(Layers.id.PATH);
                    } else {
                        // distinguish users
                        m.color = Math.random() * 0xFFFFFF; //rnd.nextInt()
                        m.label = user[i].timestamp;
                    }
                } else {
                    // follow mouse on scroll
					          m.leader = true;
					
                    $player = new Player(this, {
                        width:  $stageWidth,
                        time:   user[i].xcoords.length / $FPS,
                        color:  0xFFCC33
                    });
                    /*
                    // add scrubber
                    $scrubber = new Scrubber({
                        width:  $stageWidth,
                        time:   user[i].xcoords.length / $FPS,
                        color:  0xFFCC33
                    });
                    addChild($scrubber);
                    */
				        }
				        addChild(m);
				        // save mouse info
                arrOpts.push({ activity: mouseInfo, screen: screenInfo, color: m.color, avg: user[i].avg });
            }

            return arrOpts;
        };

        private function buildCanvas():Array
        {
            var c:Array = [];
            // do not include the background layer
			      for (var i:int = 1; i < Layers.collectionLength; ++i) {
                var name:String = Layers.collection[i].id;
                c[name] = getChildByName(name) as Sprite;
            }
				
			      return c;
		    };
		
        private function createLayers():void
        {
            // visualization layers
            for (var i:int = 0; i < Layers.collectionLength; ++i)
            {
                var layer:CustomSprite = new CustomSprite();
                // set a name to reference them later
                layer.name = Layers.collection[i].id;
                layer.color = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].color : Layers.collection[i].color;
                // the background layer is a special case (common to all mouse views)
                if (layer.name == Layers.id.BACKGROUND) {
                    var bgColor:uint = layer.color;
                    layer.graphics.beginFill(bgColor, .8);
                    layer.graphics.drawRect(0,0, $stageWidth,$stageHeight);
                    layer.graphics.endFill();
                }
                // this layer will be composited with mouse coordinates
                if (layer.name == Layers.id.MASK) {
                    drawMask(layer);
                }
                addChild(layer);
                // hide some layers, as set in Layers class or as user defined
                layer.visible = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].visible : Layers.collection[i].visible;
            }
        };
        
        private function drawMask(layer:CustomSprite):void
        {
            layer.blendMode = BlendMode.SUBTRACT;
            // color of interacted areas shouldn't be changed
            layer.graphics.beginFill(0xFFFFFF);
            layer.graphics.drawRect(0,0, $stageWidth,$stageHeight);
            layer.graphics.endFill();
        };
        
        private function toggleReplay(e:ControlPanelEvent = null):void
        {
            var isRealtime:Boolean = (e) ? e.data.realTime : $savedSettings.data.replayRT;
            
            for (var i:int = 0; i < Layers.collectionLength; ++i)
            {
                var layer:CustomSprite = getChildByName(Layers.collection[i].id) as CustomSprite;
                // do not clear bgLayer
                if (layer.name == Layers.id.BACKGROUND) continue;
                // remove previous trails
                while (layer.numChildren > 0) { layer.removeChildAt(0); }
                layer.graphics.clear();
                // refill mask layer
                if (layer.name == Layers.id.MASK && !isRealtime) {
                    drawMask(layer);
                }
            }
            
            // reset mouse manager
            var useHeatMap:Boolean = (e) ? e.data.heatMap : $savedSettings.data.heatMap;
            
			      $MOUSE.dynamic = isRealtime;
			      $MOUSE.heatmap = useHeatMap;
			      $MOUSE.init();
			
			      if ($player)
            {
                if (isRealtime) {
                    $player.restart();
                } else {
                    $player.finish();
                }
            }
			
			      if (isRealtime) {
                stage.addEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
            }
        };
        
        private function keyUpHandler(e:KeyboardEvent):void
        {
            switch(e.keyCode) 
			      {
				      case Keyboard.SPACE:
					      pauseReplay();
					      break;
				      case Keyboard.ESCAPE:
					      cancelReplay();
					      break;
				      default:
					      break;
			      }
        };
        
		    public function pauseReplay():void 
        {
			      $MOUSE.pause();
			      if ($player) $player.pause();
			
            // display icon
            var icon:Sprite = ($MOUSE.paused) ? new Asset.iconPause() : new Asset.iconPlay();
            icon.alpha = 0.5;
            icon.name = "resumeIcon";
            var offset:Object = ExternalInterface.call("window.smt2fn.getWindowOffset");
            
            icon.x = $currWindowWidth/2 + offset.x - icon.width/2;
            icon.y = $currWindowHeight/2 + offset.y - icon.height/2;
            addChild(icon);
            
            Tweener.addTween(icon, {alpha:0, time:2, transition:"easeOutQuart", onComplete:removePlayPauseIcon});
		    };

		    private function cancelReplay():void
        {
            $MOUSE.finish();
            if ($player) $player.finish();

			      endReplay();
		    };
		
        private function endReplay(e:TrackingEvent = null):void 
        {
            $cp.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.REPLAY_COMPLETE));
            --$endQueue;

            if ($endQueue <= 0) {
                stage.removeEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
                if ($player) $player.finish();
            }
        };
        
        private function removePlayPauseIcon():void 
        {
            removeChild(getChildByName("resumeIcon"));
        };
        
        private function onPlayerPlay(e:PlayerEvent):void
        {
            pauseReplay();
        };
        private function onPlayerPause(e:PlayerEvent):void
        {
            pauseReplay();
        };
        private function onPlayerStop(e:PlayerEvent):void
        {
            toggleReplay();
        };
        
    } // end class
}
