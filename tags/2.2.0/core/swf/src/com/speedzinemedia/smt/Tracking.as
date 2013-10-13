package com.speedzinemedia.smt {

    import flash.display.Bitmap;
    import flash.display.BlendMode;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.events.KeyboardEvent;
    import flash.external.ExternalInterface;
	  import flash.geom.Point;
    import flash.net.SharedObject;
    import flash.ui.Keyboard;

	  import caurina.transitions.Tweener;
	  //import de.polygonal.math.PM_PRNG;
	  import com.speedzinemedia.smt.events.TrackingEvent;
    import com.speedzinemedia.smt.events.ControlPanelEvent;
    import com.speedzinemedia.smt.events.PlayerEvent;
    import com.speedzinemedia.smt.display.Asset;
    import com.speedzinemedia.smt.display.ControlPanel;
    import com.speedzinemedia.smt.display.CustomSprite;
    import com.speedzinemedia.smt.display.Layers;
    import com.speedzinemedia.smt.display.Player;
    import com.speedzinemedia.smt.display.Scrubber;
    import com.speedzinemedia.smt.display.ScrubberInteractive;    
    import com.speedzinemedia.smt.draw.DrawUtils;
    import com.speedzinemedia.smt.mouse.MouseView;
	  import com.speedzinemedia.smt.mouse.MouseManager;
	  import com.speedzinemedia.smt.text.DebugText;
	  import com.speedzinemedia.smt.text.Hypernote;
    import com.speedzinemedia.smt.utils.FPSMeter;
	  import com.speedzinemedia.smt.utils.TimeUtils;
	  import com.speedzinemedia.smt.utils.Utils;
	
    /**
     *  (smt) Simple Mouse Tracking application
     *  @autor      Luis Leiva
     *  @version    2.0.1
     *  @date       16 Jan 2009
     */
    public class Tracking extends Sprite 
    {
		    private var $FPS:int, $users:Object, $login:String, $layoutType:String;
        private var $currWindowWidth:int, $currWindowHeight:int, $stageWidth:int, $stageHeight:int;        
        private var $replayTime:int = 0;          // browsing time > interaction time
		    private var $fragStart:String, $fragEnd:String; // hyperfragments
        private var $cp:ControlPanel;             // control panel instance
        private var $savedSettings:SharedObject;  // visualization settings
		    private var $MOUSE:MouseManager;          // mouse track(s) manager
		    private var $scrubber:Scrubber;           // timeline scrubber
		    private var $player:Player;               // timeline player (w/ scrubber)
		    private var $endQueue:int;                // count finished mouse tracks

        private var $isPaused:Boolean = false;    // vis. defaults
        private var $isRealtime:Boolean = true;
        private var $useHeatMap:Boolean = false;
        private var $skipPauses:Boolean = false;
        
        public function Tracking() 
        {
            Utils.initStage(this);
            // get user data
            var p:Object = this.loaderInfo.parameters;
            try {
                $login            = Utils.getFlashVar(p.login);
                $layoutType       = Utils.getFlashVar(p.layout);
                $currWindowWidth  = Utils.getFlashVar(p.wcurr,  "int");
                $currWindowHeight = Utils.getFlashVar(p.hcurr,  "int");
                $stageWidth       = Utils.getFlashVar(p.wpage,  "int");
                $stageHeight      = Utils.getFlashVar(p.hpage,  "int");
                $FPS              = Utils.getFlashVar(p.fps,    "int");
                $users            = Utils.getFlashVar(p.users,  "json");
                $fragStart        = Utils.getFlashVar(p.start);
                $fragEnd          = Utils.getFlashVar(p.end);
                // external bindings JS->AS
                ExternalInterface.addCallback("displayHyperNote", displayHyperNote);
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
            var link:String = "http://livedocs.adobe.com/flex/3/langref/runtimeErrors.html";
            if (e.errorID > 0) link += "#" + e.errorID;
            reason += "\nSee <a href='" + link + "'>" + link + "</a>";
            // Flash Player debugger provides more info
            var stack:String = e.getStackTrace();
            if (stack) reason += "\n" + stack;
            
            debug.msg(reason);
        };
        
        private function init():void 
        {                 
            stageConf();
            resetQueue();
            createLayers();
            createFPSMeter();
            
            var userInfo:Array = loadUserInfo();
            // check whether video should finish at a specific time
            var fe:Number = smpte2perc($fragEnd);
            if (fe > 0 && fe < $replayTime) $replayTime *= fe;
            // build hyperplayer
            playerConf();
            // pass userInfo to control panel
            $cp = new ControlPanel(this, userInfo);
			      // load Mouse Manager
			      $MOUSE = new MouseManager(this);
			      // start replay
            if ($savedSettings.size > 0) {
              // use saved settings (realtime can be true or false)
              $isRealtime = $savedSettings.data.replayRT;
              $useHeatMap = $savedSettings.data.heatMap;
              $skipPauses = $savedSettings.data.noPauses;
              toggleReplay();
            } else {
              // begin with default settings (real-time replay, no heatmap visualizations)
				      resetMouseManager();
            }
            // check whether video should start at a specific time
            var fs:Number = smpte2perc($fragStart);
            if (fs > 0) {
              seekReplay(fs); // fn uses percentage of viewed video
              togglePause();
            }
        };
        
        private function stageConf():void
        {        
            // set stage frame rate from user data
            stage.frameRate = $FPS;
            // allow pausing the mouse visualization
            stage.addEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
            // listen to changes from other classess
            addEventListener(ControlPanelEvent.TOGGLE_REPLAY_MODE, toggleReplayFromPanel);
            addEventListener(ControlPanelEvent.CREATE_HYPERNOTE, createHyperNoteFromPanel);
            addEventListener(ControlPanelEvent.TOGGLE_HYPERNOTE, toggleHyperNoteFromPanel);
            addEventListener(ControlPanelEvent.REQUEST_CUEPOINT, getCuepointFromPanel);
            addEventListener(TrackingEvent.MOUSE_ADVANCE, advanceReplay);            
            addEventListener(TrackingEvent.MOUSE_END, endReplay);
            addEventListener(PlayerEvent.PLAY, onPlayerPlay);
            addEventListener(PlayerEvent.PAUSE, onPlayerPause);
            addEventListener(PlayerEvent.STOP, onPlayerStop);
            addEventListener(PlayerEvent.SEEK, onPlayerSeek);
            addEventListener(PlayerEvent.RELOAD, onPlayerReload);
            addEventListener(PlayerEvent.FINISH, onPlayerFinish);
        };
        
        private function createFPSMeter():void 
        {
            var fpsm:FPSMeter = new FPSMeter();
            addChild(fpsm);
            fpsm.x = $currWindowWidth - fpsm.width;
            fpsm.y = 3;
        };
                
        private function playerConf():void
        {            
            $scrubber = new Scrubber({
              width:  $stageWidth,
              time:   $replayTime,
              fps:    $FPS,
              color:  0x33CCFF
            });
            addChild($scrubber);
            
            $player = new Player(this, {
              width:  $stageWidth,              
              time:   $replayTime,
              fps:    $FPS,
              color:  0xFFCC33
            });
        };

        private function smpte2perc(smpte:String):Number
        {
            var t:Number = TimeUtils.SMPTEToTime(smpte)/1000; // seconds
            // some silly checks
            if (t > $replayTime) t = $replayTime;
            else if (t < 0) t = 0;
            
            return t / $replayTime;
        };
                            
        private function loadUserInfo():Array
        {
            /*
            var rnd:PM_PRNG = new PM_PRNG();
            rnd.seed = Math.random() * 0x7FFFFFFE;
            */
            var drawCanvas:Array = buildCanvas();
            var userInfo:Array = [];
            var vectorSize:uint, xc:Vector.<int>, yc:Vector.<int>, tc:Vector.<int>;
            //var trackLength:int = 0; // interaction time ~ Math.ceil(trackLength/$FPS)
            const NUM_USERS:uint = $users.length;
            for (var i:int = 0; i < NUM_USERS; ++i)
            {
                // use fixed-length vectors instead of arrays
                vectorSize = $users[i].xcoords.length;
                xc = new Vector.<int>(vectorSize, true);
                yc = new Vector.<int>(vectorSize, true);
                tc = new Vector.<int>(vectorSize, true);
                for (var j:int = 0; j < vectorSize; ++j) {
                  xc[j] = $users[i].xcoords[j];
                  yc[j] = $users[i].ycoords[j];
                  tc[j] = $users[i].clicks[j];
                }
                // create info objects
				        var mouseInfo:Object = {
					        coords: { x: xc, y: yc, type: tc },
					        fps:    $FPS
				        };
				        var screenInfo:Object = {
				          layoutType: $layoutType,
                  viewport:   { width: $currWindowWidth, height: $currWindowHeight },
				          currWindow: { width: $stageWidth,      height: $stageHeight      },
					        prevWindow: { width: $users[i].wprev,  height: $users[i].hprev   }
				        };
                // create mouse view instance
                var m:MouseView = new MouseView(mouseInfo, screenInfo, drawCanvas);
                if (NUM_USERS > 1) 
                {
                    if ($users[i].avg) {
                        // hilite average path
                        m.thick = 3;
                        m.color = Layers.getColor(Layers.id.PATH);
                    } else {
                        // distinguish users
                        m.color = Math.random() * 0xFFFFFF; //rnd.nextInt()
                        m.label = $users[i].timestamp;
                    }                   
                } 
                else 
                {
                    // follow mouse on scroll
					          m.leader = true;
				        }
				        
				        //if (trackLength < $users[i].xcoords.length) trackLength = $users[i].xcoords.length;
				        if ($replayTime < $users[i].time) $replayTime = $users[i].time;
                
				        addChild(m);
				        // save mouse info
                userInfo.push({ activity: mouseInfo, screen: screenInfo, color: m.color, avg: $users[i].avg });
                // load hypernotes
                const NUM_NOTES:uint = $users[i].hypernotes.length;
                if ($users[i].hypernotes) for (j = 0; j < NUM_NOTES; ++j)
                { 
                  var hn:Object = $users[i].hypernotes[j];
                  displayHyperNote(hn.uid, hn.pos);
                }
            }
                        
            return userInfo;
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
            // put hypernotes in an independent layer
            var hyperlayer:CustomSprite = new CustomSprite();
            hyperlayer.name = "hypernotes";
            addChild(hyperlayer);
            hyperlayer.visible = ($savedSettings.size > 0) ? $savedSettings.data.toggleHyper : true;
        };
        
        private function drawMask(layer:CustomSprite):void
        {
            layer.blendMode = BlendMode.SUBTRACT;
            // color of interacted areas shouldn't be changed
            layer.graphics.beginFill(0xFFFFFF);
            layer.graphics.drawRect(0,0, $stageWidth,$stageHeight);
            layer.graphics.endFill();
        };
        
        private function resetQueue():void
        { 
            $endQueue = $users.length;
        };

        private function resetMouseManager():void
        {
            // reset mouse manager
			      $MOUSE.init({
			        realtime: $isRealtime,
			        heatmaps: $useHeatMap,
			        nopauses: $skipPauses			      
			      });        
        };

        public function displayHyperNote(user:String, smpte:String):void
        {
            var perc:Number = smpte2perc(smpte);
            var xpos:Number = $stageWidth * perc;
            var hyper:Sprite = new Sprite();
            hyper.name = user + xpos;
            hyper.addEventListener(MouseEvent.CLICK, function(e:MouseEvent):void {
              readHyperNote(smpte);
              seekReplay(perc);
            });
            var icon:Bitmap = new Asset.ICON_NOTE();
            icon.x = xpos - icon.width/2;
            icon.y = 2;
            hyper.addChild(icon);
            var hyperlayer:CustomSprite = getChildByName("hypernotes") as CustomSprite;
            hyperlayer.addChild(hyper);
        };
        
        private function createHyperNoteFromPanel(e:ControlPanelEvent):void
        {
            //displayHyperNote($login, $scrubber.position, String(e.data));
            var id:int = Utils.getFlashVar(this.loaderInfo.parameters.currtrail, "int");
            Hypernote.create(id, $login, TimeUtils.timeToSMPTE($player.seconds * 1000)); //$scrubber.position/$stageWidth
        };
                
        private function toggleHyperNoteFromPanel(e:ControlPanelEvent):void
        {
            var hyperlayer:CustomSprite = getChildByName("hypernotes") as CustomSprite;
            hyperlayer.visible = e.data;
        };
                
        private function readHyperNote(smpte:String):void
        {
            //ExternalInterface.call('console.log', e.currentTarget.name);
            var id:int = Utils.getFlashVar(this.loaderInfo.parameters.currtrail, "int");
            Hypernote.read(id, $login, smpte);
        };
        
        private function getCuepointFromPanel(e:ControlPanelEvent):void 
        {
            $cp.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.UPDATED_CUEPOINT, $scrubber.position));
        };
        
        private function keyUpHandler(e:KeyboardEvent):void
        {
            switch(e.keyCode) 
			      {
				      case Keyboard.SPACE:
					      togglePause();
					      break;
				      case Keyboard.ESCAPE:
					      cancelReplay();
					      break;
				      default:
					      break;
			      }
        };
        
        private function toggleReplayFromPanel(e:ControlPanelEvent):void
        {
            $isRealtime = e.data.realTime;
            $useHeatMap = e.data.heatMap;
            $skipPauses = e.data.noPauses;
            
            resetQueue();
            toggleReplay();
        };
        
        private function toggleReplay():void
        {            
            clearAllLayers();
            resetMouseManager();
			
			      if ($player) {
                if ($isRealtime) {
                    $player.restart();
                } else {
                    $player.finish();
                }
            }
			      if ($scrubber) {
                if ($isRealtime) {
                    $scrubber.restart();
                } else {
                    $scrubber.finish();
                }
            }
			      if ($isRealtime && !stage.hasEventListener(KeyboardEvent.KEY_UP)) {
                stage.addEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
            }
        };
        
        private function clearAllLayers():void
        {
            for (var i:int = 0; i < Layers.collectionLength; ++i)
            {
                var layer:CustomSprite = getChildByName(Layers.collection[i].id) as CustomSprite;
                // do not clear bgLayer
                if (layer.name == Layers.id.BACKGROUND) continue;
                // remove previous trails
                while (layer.numChildren > 0) { layer.removeChildAt(0); }
                layer.graphics.clear();
                // refill mask layer
                if (layer.name == Layers.id.MASK && !$isRealtime) {
                    drawMask(layer);
                }
            }
        };
                
		    private function resumeReplay():void
        {
            $MOUSE.resume();
            if ($player) $player.resume();
            if ($scrubber) $scrubber.resume();
        };

		    private function stopReplay():void
        {		    
            $MOUSE.stop();
            if ($player) $player.stop();
            if ($scrubber) $scrubber.stop();
        };
        
		    private function seekReplay(perc:Number):void
        {	
            //toggleReplay();      
            clearAllLayers();
            resetMouseManager();
            
            $MOUSE.seek(perc);
            if ($scrubber) $scrubber.seek(perc);
            if ($player) $player.seek(perc);
            
            if ($isPaused) {
              stopReplay();
            } else {
              resumeReplay();
            }
        };
        
        private function finish():void 
        {		    
            $MOUSE.finish();
            if ($player) $player.finish();
            if ($scrubber) $scrubber.finish();
        };
                
		    private function togglePause():void 
        {
            $isPaused = !$isPaused;
            
            $MOUSE.pause();
            if ($player) $player.pause();
            if ($scrubber) $scrubber.pause();
            
            if ($endQueue > 0) {
              var icon:Sprite = ($isPaused) ? new Asset.ICON_PAUSE() : new Asset.ICON_PLAY();			
              displayStageIcon(icon);
            }
		    };
        		    
		    private function cancelReplay():void
        {
            finish();
			      endReplay();
		    };
                
        private function endReplay(e:TrackingEvent = null):void 
        {
            $cp.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.REPLAY_COMPLETE));
            --$endQueue;

            if ($endQueue <= 0) {
                //stage.removeEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
                finish();
            }
        };
        
        private function advanceReplay(e:TrackingEvent):void 
        {
            var perc:Number = Number(e.data);
            //ExternalInterface.call('console.log', "advance", perc, perc*$replayTime);
            // player imposes a race condition, so this cannot be updated
            //seekReplay(perc);
        };

        // Player buttons indicate replay status, so clicking on play must stop and vice versa
        private function onPlayerPlay(e:PlayerEvent):void
        {
            togglePause();
        };
        
        private function onPlayerPause(e:PlayerEvent):void
        {
            togglePause();
        };
        
        private function onPlayerStop(e:PlayerEvent):void
        {
            resumeReplay();
        };
        
        private function onPlayerSeek(e:PlayerEvent):void
        {
            seekReplay( Number(e.data) );
        };
         
        private function onPlayerReload(e:PlayerEvent):void
        {
            resetQueue();
            toggleReplay();
            
            var icon:Sprite = new Asset.ICON_RELOAD();
            displayStageIcon(icon);
        };
        
        private function onPlayerFinish(e:PlayerEvent):void
        {
           finish();
        };
        
 		    private function displayStageIcon(icon:Sprite):void
        {
            icon.alpha = 0.5;
            icon.name = "stageIcon";
            var offset:Object = ExternalInterface.call("window.smt2fn.getWindowOffset");
            icon.x = $currWindowWidth/2 + offset.x - icon.width/2;
            icon.y = $currWindowHeight/2 + offset.y - icon.height/2;
            addChild(icon);
            
            Tweener.addTween(icon, {alpha:0, time:2, transition:"easeOutQuart", onComplete:removeStageIcon});
        };
        
        private function removeStageIcon():void 
        {
            removeChild(getChildByName("stageIcon"));
        };
               
    } // end class
}
