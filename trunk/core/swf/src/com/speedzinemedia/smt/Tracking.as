/**
 *  (smt) Simple Mouse Tracking application
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt {

    import flash.display.Bitmap;
    import flash.display.BitmapData;
    import flash.display.DisplayObject;
    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.display.StageQuality;
    import flash.events.Event;
    import flash.events.KeyboardEvent;
    import flash.events.MouseEvent;
    import flash.external.ExternalInterface;
    import flash.filters.BitmapFilter;
    import flash.filters.BitmapFilterQuality;
    import flash.filters.BlurFilter;
    import flash.geom.Matrix;
    import flash.geom.ColorTransform;
    import flash.geom.Point;
    import flash.net.SharedObject;
    import flash.ui.Keyboard;
    import flash.ui.Mouse;
    import flash.utils.*;

    import caurina.transitions.Tweener;
    
    import com.speedzinemedia.smt.events.ControlPanelEvent;
    import com.speedzinemedia.smt.text.Tooltip;
    import com.speedzinemedia.smt.text.DebugText;
     
    public class Tracking extends Sprite 
    {
        private var $FPS:int;                           // user data ...
        private var $xcoords:Array, $ycoords:Array;
        private var $xclicks:Array, $yclicks:Array;
        private var $xclusters:Array, $yclusters:Array, $clustSize:Array;
        private var $prevWindowWidth:int, $prevWindowHeight:int;
        private var $currWindowWidth:int, $currWindowHeight:int;
        private var $stageWidth:int, $stageHeight:int;
        
        private var $num:int;                           // tracking points
        private var $count:int;                         // counter for mouse tracking loop
        private var $varCount:int;                      // counter for registration points size
        private var $varCountMax:int;                   // counter for registration points size
        private var $varCircles:Array;
        private var $minStarRadius:int;
        private var $maxStarRadius:int;
        private var $xclean:Array, $yclean:Array;       // clean mouse coordinates
        private var $iniMouse:Point;                    // previous mouse coordinate
        private var $endMouse:Point;                    // next mouse coordinate
        private var $iniClick:Point;                    // previous user click
        private var $endClick:Point;                    // next user click
        
        private var $waitCursor:Bitmap ;                // exit mouse pointer
        private var $normalCursor:Bitmap;               // normal mouse pointer 
        private var $intervalId:uint;                   // mouse tracking identifier
        private var $tip:Tooltip;                       // display info at runtime
        private var $debug:DebugText;                   // debugging info
        private var $drH:Number;                        // discrepance ratios
        private var $drV:Number;
        private var $paused:Boolean;                    // pause the visualization

        private var $cp:ControlPanel;
        private var $savedSettings:SharedObject;        // get saved visualization settings
        
        public function Tracking() 
        {            
            Utils.initStage(stage, this);
            // get user data
            var p:Object = this.loaderInfo.parameters;
            try {
                $FPS              = Utils.getFlashVar(p.fps,       "int");
                $xcoords          = Utils.getFlashVar(p.xcoords,   "array");
                $ycoords          = Utils.getFlashVar(p.ycoords,   "array");
                $xclicks          = Utils.getFlashVar(p.xclicks,   "array");
                $yclicks          = Utils.getFlashVar(p.yclicks,   "array");
                $xclusters        = Utils.getFlashVar(p.xclusters, "array");
                $yclusters        = Utils.getFlashVar(p.yclusters, "array");
                $clustSize        = Utils.getFlashVar(p.clustsize, "array");
                $prevWindowWidth  = Utils.getFlashVar(p.wprev,     "int");
                $prevWindowHeight = Utils.getFlashVar(p.hprev,     "int");
                $currWindowWidth  = Utils.getFlashVar(p.wcurr,     "int");
                $currWindowHeight = Utils.getFlashVar(p.hcurr,     "int");
                $stageWidth       = Utils.getFlashVar(p.wview,     "int");
                $stageHeight      = Utils.getFlashVar(p.hview,     "int");
            } catch (e:Error) {
                $debug = new DebugText(this, true, true, true); // display: backround, label, and selectable
                $debug.msg(e + " Check flash vars!\nSee http://livedocs.adobe.com/flex/3/langref/runtimeErrors.html");
                return;
            }
            
            // check previous saved settings
            $savedSettings = SharedObject.getLocal("smtControlPanel"); 
            // if everything went OK, start
            precalculate();
            init();            
        };
        
        private function precalculate():void 
        {
            // set initial values
            $num = $xcoords.length - 1;
            $varCount = 1;
            $varCountMax = 0;
            $minStarRadius = 3;
            $maxStarRadius = 20;
            $varCircles = [];
            $xclean = []; 
            $yclean = [];
            $drH = 1;
            $drV = 1;
            
            // user stops & clean coords: useful for time-depending circles and path centroid
            var stops:Array = [];
            var size:int = 1;
            for (var k:int = 0; k < $num; ++k) {
                if ($xcoords[k] == $xcoords[k+1] && $ycoords[k] == $ycoords[k+1]) {
                  ++size;
                } else {
                  // store all user stops (time) for drawing variable circles later
                  if (size > 1) { stops.push(size); }
                  // reset size
                  size = 1;
                  // store clean mouse coordinates
                  $xclean.push($xcoords[k]);
                  $yclean.push($ycoords[k]);
                }
            }
            // set max size for variable circles
            $varCountMax = Maths.arrayMax(stops);
        };
        
        private function init():void 
        {
            createLayers();
            fadeBackground(0.75);
            // set stage frame rate from user data
            stage.frameRate = $FPS;
            // compute discrepance ratios
            if ($prevWindowWidth && $prevWindowHeight) { 
                $drH = $currWindowWidth / $prevWindowWidth;
                $drV = $currWindowHeight / $prevWindowHeight; 
            }
            // create an empty tooltip instance
            $tip = new Tooltip();
            addChild($tip);   
            // listen to changes from the control panel
            addEventListener(ControlPanelEvent.TOGGLE_REPLAY_MODE, toggleReplay);
            // allow pausing the mouse visualization
            stage.addEventListener(KeyboardEvent.KEY_UP, keyUpHandler);
            // start replay
            if ($savedSettings.size > 0) {
                // use saved settings (realtime can be true or false)
                toggleReplay();
            } else {
                // begin real-time replay
                $intervalId = setInterval(playMouse, Math.round(1000/$FPS), true);
            }
        };
   
        private function createLayers():void
        {
            // control panel dummy layer
            var panelHitTest:Sprite = new Sprite();
            panelHitTest.graphics.beginFill(0xFF0000, 0);
            panelHitTest.graphics.drawRect(0,0, $stageWidth, $stageHeight);
            panelHitTest.graphics.endFill();
            addChild(panelHitTest);
            // visualization layers
            for (var i:int = 0; i < Layers.collectionLength; ++i) {
                var layer:CustomSprite = new CustomSprite();
                // set a name to reference them later
                layer.name = Layers.collection[i].id;
                layer.color = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].color : Layers.collection[i].color;
                // the background layer is a special case
                if (layer.name == "bgLay") {
                    var bgColor:uint = Utils.parseColor(layer.color);
                    layer.graphics.beginFill(bgColor, 1);
                    layer.graphics.drawRect(0,0, $stageWidth,$stageHeight);
                    layer.graphics.endFill();
                    // transparency will change later in 3D graph
                    layer.alpha = 0.5; 
                }
                addChild(layer);
                // hide some layers, as set in Layers.as file or set by user
                layer.visible = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].visible : Layers.collection[i].visible;
            }
            // control panel
            $cp = new ControlPanel(this, $currWindowHeight);
        };
        
        private function fadeBackground(opacity:Number, seconds:Number = 2):void
        {
            var bg:Sprite = getChildByName("bgLay") as Sprite;
            Tweener.addTween(bg, {alpha:opacity, time:seconds, transition:"easeOutQuart"});
        };
        
        private function getLayerColor(layerName:String):uint 
        {
            var layerColor:String;
            if ($savedSettings.size > 0) {
                var index:int = Layers.getIndex(layerName);
                var layer:Object = $savedSettings.data.layers[index];
                layerColor = layer.color;
            } else {
                layerColor = Layers.select(layerName).color;
            }
            
            return Utils.parseColor(layerColor);
        };
        
        private function drawMousePointer(p:Point, type:String):void 
        {
            var ee:Sprite = new Sprite();
            ee.name = type + " point @ (" + Math.round($iniMouse.x) +", "+ Math.round($iniMouse.y) + ")"; 
            addTooltipListeners(ee, false);
            
            var cursor:Bitmap, color:uint; 
            if (type == "entry") {
                cursor = new Asset.cursorEntry();
                color = 0x33FF33;
            } else if (type == "exit") {
                cursor = new Asset.cursorExit();
                color = 0xFF3333;
            }
            cursor.x = p.x;
            cursor.y = p.y;
            
            // add a nice background glow
            addGlowPoint(ee, p, color, 60);

            ee.addChild(cursor);
            var cnv4cursor:Sprite = getChildByName("eeCur") as Sprite;
            cnv4cursor.addChild(ee);
        };
        
        private function addGlowPoint(layer:Sprite, p:Point, color:uint = 0xFFFFFF, size:int = 30, alpha:Number = 0.5):void 
        {
            var bg:Shape = new Shape();
            bg.graphics.beginFill(color, alpha);
            bg.graphics.drawCircle(p.x, p.y, size/2);
            bg.graphics.endFill();
            var filter:BitmapFilter = new BlurFilter(size, size);
            var myFilters:Array = new Array();
            myFilters.push(filter);
            bg.filters = myFilters;
            // update display list
            layer.addChild(bg);
        };
                  
        private function playMouse(realtime:Boolean, showArrowsOnMouseLines:Boolean = false):void 
        {
            if ($paused) { return; }

            // create previous coordinate
            $iniMouse = new Point($xcoords[$count] * $drH, $ycoords[$count] * $drV);
            $iniClick = new Point($xclicks[$count] * $drH, $yclicks[$count] * $drV);
            // this Sprite is used to move the normal cursor on screen
            var cnv4cursor:Sprite = getChildByName("eeCur") as Sprite;            
            // 1. enter the loop for the first time
            if ($count == 0) {
                // 1.1. display entry cursor
                drawMousePointer($iniMouse, "entry");
                // 1.2. *add* both normal and wait cursors
                $normalCursor = new Asset.cursorNormal();
                cnv4cursor.addChild($normalCursor);
                $waitCursor = new Asset.cursorWait();
                $waitCursor.visible = false; // hide initially
                cnv4cursor.addChild($waitCursor);
            }
            // 2. main loop to draw mouse trail
            if ($count < $num) {
                // create next coordinate
                $endMouse = new Point($xcoords[$count+1] * $drH, $ycoords[$count+1] * $drV);
                $endClick = new Point($xclicks[$count+1] * $drH, $yclicks[$count+1] * $drV);
                // 2.1. *move* normal cursor to the current position
                $normalCursor.x = $endMouse.x; 
                $normalCursor.y = $endMouse.y;
                // 2.2. compute euclidean distance
                var dist:Number = Point.distance($iniMouse, $endMouse);
                if (dist > 0) {
                    // 2.3. this function is self-explanatory ;)
                    drawRegistrationPoint($iniMouse);
                    // 2.4. draw mouse stops
                    if ($varCount > 1) {
                        drawVariableCircle($iniMouse, $varCount);
                        // hide wait cursor
                        if (realtime) { $waitCursor.visible = false; }
                    }
                    // 2.5. draw direction vector bitmaps
                    if (dist > 50) { drawDirectionArrow($endMouse, dist); }
                    // reset count for the next iteration
                    $varCount = 1;
                } else {
                    // store variable circles size
                    ++$varCount;
                    // display wait cursor
                    if (realtime) {
                        $waitCursor.x = $endMouse.x; 
                        $waitCursor.y = $endMouse.y;
                        $waitCursor.visible = true;
                    }
                }
                
                // 2.6. draw mouse path
                drawMousePath($iniMouse, $endMouse);
                // 2.6. draw mouse clicks
                if ($iniClick.x != 0) {
                    // check distance to next click point
                    var clickDist:int = Math.floor( Point.distance($iniClick, $endClick) );
//var isSingleClick:Boolean = !Boolean(clickDist);
//ExternalInterface.call("console.log", "isSingleClick: " + isSingleClick);
//if (clickDist > 0 ) { if ($endClick.x != 0) { drawMouseClick($iniClick, true); } else { drawMouseClick($iniClick, false); } }
                    if (clickDist == 0) {
                        // draw single click
                        drawMouseClick($iniClick, false);
                    } else if ($endClick.x != 0) {
                        // the mouse is pressed while moving
                        drawMouseClick($iniClick, true);
                    }
                }
                // 2.7. update mouse coordinates
                ++$count;
                // auto-scroll fancy function
                if (realtime) {
                    ExternalInterface.call("window.smtAuxFn.doScroll", {xpos:$endMouse.x, ypos:$endMouse.y, width:$currWindowWidth, height:$currWindowHeight});
                }
                
            } else {
                // 2.8. exit the loop
                clearInterval($intervalId);
                // draw last registration point
                drawRegistrationPoint($iniMouse);
                // display exit cursor and remove both normal and wait cursors
                drawMousePointer($iniMouse, "exit");
                cnv4cursor.removeChild($normalCursor);
                cnv4cursor.removeChild($waitCursor);
                // compute path centroid
                var centroid:Point = new Point( Maths.arrayAvg($xclean) * $drH, Maths.arrayAvg($yclean) * $drV );
                drawCentroid(centroid);
                // k-means clustering
                for (var i:int = 0, cLength:int = $xclusters.length; i < cLength; ++i) {
                    var clusterPt:Point = new Point($xclusters[i] * $drH, $yclusters[i] * $drV);
                    drawCluster(clusterPt, $clustSize[i]);
                }
                // check if there are more pages
                $cp.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.REPLAY_COMPLETE));
            }
        };
        
        private function drawMousePath(p:Point, q:Point):void
        {
            var cnv4path:Sprite = getChildByName("mPath") as Sprite;
            cnv4path.graphics.lineStyle(0, getLayerColor("mPath"));
            cnv4path.graphics.moveTo(p.x, p.y);
            cnv4path.graphics.lineTo(q.x, q.y);
            /*
            if (showArrowsOnMouseLines && Point.distance(p, q) > 8) {
                var a:Arrow = new Arrow(p, q, 0, 4, pathColor, false);
                cnv4path.addChild(a);
            }
            */
        };
                
        private function drawRegistrationPoint(p:Point):void
        {
            var rp:Sprite = new Sprite();
            rp.name = "#" + $count +"; time: "+Maths.roundTo($count/$FPS, 2);//"count: "+$count+"/"+$num;
            addTooltipListeners(rp, false);
            // draw square
            const SIZE:int = 3;
            rp.graphics.beginFill(getLayerColor("regPt"));
            rp.graphics.drawRect(p.x - SIZE/2, p.y - SIZE/2, SIZE, SIZE);
            rp.graphics.endFill();
            // update display list
            var cnv4reg:Sprite = getChildByName("regPt") as Sprite;
            cnv4reg.addChild(rp);
        };
        
        private function drawVariableCircle(p:Point, size:int):void
        {
            // use multiplier to normalize all circles: 0 < norm < 1
            var norm:Number = Maths.roundTo(size/$varCountMax, 2);
            if (size * norm == 0 ) { return; }
            var vc:Sprite = new Sprite();
            vc.name = "stop: "+Maths.roundTo(size/$FPS, 2)+" seconds @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            // limit size to 1/2 of current window width (px)
            if (size > $currWindowWidth/2) { size = Math.round($currWindowWidth/2 * norm); }
            // draw circle
            vc.graphics.beginFill(getLayerColor("stops"), 0.4);
            vc.graphics.drawCircle(p.x, p.y, size/2);
            vc.graphics.endFill();
            //drawCircleCenter(p, size, vc);
            addTooltipListeners(vc);
            // update display list
            var cnv4stop:Sprite = getChildByName("stops") as Sprite;
            cnv4stop.addChild(vc);
            
            // now check previous variable circles
            $varCircles.push(size);
            for (var i:int = 0; i < cnv4stop.numChildren; ++i) {
                // swap smaller circles with bigger ones
                if ($varCircles.length > 1 && $varCircles[i] > $varCircles[i-1]) {
                    cnv4stop.swapChildrenAt(i, i-1);    
                }
            }
        };
        
        private function drawDirectionArrow(p:Point, distance:Number):void
        {
            // dvc: direction vector container
            var dvc:Sprite = new Sprite();
            dvc.name = "distance: " + Maths.roundTo(distance,2) + "px"; // + "(from " + $count + " to " + ($count+1) + ")";
            addTooltipListeners(dvc);
            // draw direction arrow
            var dirVect:Bitmap = new Asset.cursorDir();
            // rotate arrow and scale it
            var alpha:Number = Utils.checkAngle($iniMouse, $endMouse);
            var m:Matrix = dirVect.transform.matrix;
            m.rotate(alpha + Math.PI/2);        // add 90º because bitmap is a vertical image
            m.scale(distance/80, distance/80);  // proportional to the distance
            dirVect.transform.matrix = m;
            dirVect.x = p.x;
            dirVect.y = p.y;
            // update display list
            dvc.addChild(dirVect);
            var cnv4dir:Sprite = getChildByName("dDist") as Sprite;
            cnv4dir.addChild(dvc);
        };
        
        private function drawMouseClick(p:Point, isDragAndDrop:Boolean):void
        {
            const SIZE:int = 6;
            if (!isDragAndDrop) {
                var cnv4clicks:Sprite = getChildByName("click") as Sprite;
                cnv4clicks.graphics.lineStyle(0, getLayerColor("click"));
                Utils.drawStar(cnv4clicks, p.x, p.y, SIZE*2, SIZE/2); // offset: SIZE/2
                //addGlowPoint(cnv4clicks, p);
            } else {
                var cnv4ddrop:Sprite = getChildByName("dDrop") as Sprite;
                var color:uint = getLayerColor("dDrop");
                /*
                cnv4ddrop.graphics.lineStyle(0, color);
                cnv4ddrop.graphics.drawRect(p.x - SIZE/2, p.y - SIZE/2, SIZE, SIZE);
                */
                addGlowPoint(cnv4ddrop, p, color, 20);
            }
        };
        
        private function drawCentroid(p:Point):void 
        {
            var c:Sprite = new Sprite();
            c.name = "centroid @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            addTooltipListeners(c);
            
            c.graphics.lineStyle(6, getLayerColor("centr"));
            Utils.drawStar(c, p.x, p.y, $maxStarRadius, 0); // offset: 0px
            var cnv4centr:Sprite = getChildByName("centr") as Sprite;
            cnv4centr.addChild(c);
        };
        
        private function drawCluster(p:Point, size:int):void 
        {
            // reject one-point clusters
            if (size < 2) { return; } 
            
            var c:Sprite = new Sprite();
            c.name = size + " points in cluster @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            addTooltipListeners(c);
            /*
            // draw cluster star
            c.graphics.lineStyle(3, getLayerColor("clust"));
            Utils.drawStar(c, p.x, p.y, size, 0); // offset: 0px
            */
            // draw cluster circle
            c.graphics.beginFill(getLayerColor("clust"), 0.4);
            c.graphics.drawCircle(p.x, p.y, size/2);
            c.graphics.endFill();
            //drawCircleCenter(p, size, c);
            // update display list
            var cnv4clust:Sprite = getChildByName("clust") as Sprite;
            cnv4clust.addChild(c);   
        };
        
        private function drawCircleCenter(p:Point, circleSize:int, canvas:Sprite):void 
        {
            // draw center as a star
            var centerSize:Number = circleSize/10;
            if (centerSize > $minStarRadius) {
                // limit max size
                if (centerSize > $maxStarRadius) { centerSize = $maxStarRadius; }
                // draw
                canvas.graphics.lineStyle(0, 0x000000);
                Utils.drawStar(canvas, p.x, p.y, centerSize);
            }
        };
            
        private function toggleReplay(e:ControlPanelEvent = null):void
        {
            // remove previous trails (i starts at 1 because 0 is bgLayer)
            for (var i:int = 1; i < Layers.collectionLength; ++i) {
                var layer:Sprite = getChildByName(Layers.collection[i].id) as Sprite;
                while (layer.numChildren > 0) { layer.removeChildAt(0); }
                layer.graphics.clear();
            }
            // reset counters
            $count = 0; $varCount = 1;
            // replay modes
            var realtime:Boolean = (e) ? e.params : $savedSettings.data.replayRT;
            if (realtime) {
                $intervalId = setInterval(playMouse, Math.round(1000/$FPS), realtime);
            } else {
                // static mouse tracking visualization (retook from last $count)
                $paused = false;
                for (var n:int = $count; n <= $num ; ++n) { playMouse(realtime); }
            }
        };
        
        private function keyUpHandler(e:KeyboardEvent):void
        {
            if (e.keyCode == Keyboard.SPACE) {
                $paused = !$paused; 
            }
            if (e.keyCode == Keyboard.ESCAPE) {
                dispatchEvent(new ControlPanelEvent(ControlPanelEvent.TOGGLE_REPLAY_MODE, false)); 
            }
        };
        
        private function addTooltipListeners(s:Sprite, button:Boolean = true):void
        {
            s.buttonMode = button;
            s.addEventListener(MouseEvent.MOUSE_OVER, showTip);
            s.addEventListener(MouseEvent.MOUSE_OUT, hideTip);
        };
            
        private function showTip(e:MouseEvent):void
        {
            $tip.show(e.target.name);
        };
        
        private function hideTip(e:MouseEvent):void
        {
            $tip.hide();
        };      
        
    } // end class
}