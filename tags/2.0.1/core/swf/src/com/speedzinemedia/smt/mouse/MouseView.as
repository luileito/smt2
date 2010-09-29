/**
 *  Visualizes the mouse data
 *  @version    1.0 - 26 Sep 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.mouse {

    import flash.display.Bitmap;
    import flash.display.Shape;
	import flash.display.Sprite;
	import flash.display.BlendMode;
	import flash.display.GradientType;
    import flash.external.ExternalInterface;
    import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.Point;
	import flash.geom.Matrix;
    import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
    import flash.text.TextField;
    import flash.text.TextFormat;
    import flash.text.TextFieldAutoSize;
	 
    import com.adobe.serialization.json.*;
    import caurina.transitions.Tweener;
    //import de.polygonal.math.PM_PRNG;
    
	import com.speedzinemedia.smt.display.Asset;
	import com.speedzinemedia.smt.display.Layers;
    import com.speedzinemedia.smt.events.TrackingEvent;
    import com.speedzinemedia.smt.draw.DrawUtils;
    import com.speedzinemedia.smt.draw.HeatMap;
	import com.speedzinemedia.smt.text.Tooltip;
	import com.speedzinemedia.smt.text.Singleton;
	import com.speedzinemedia.smt.utils.*
    
    public class MouseView extends MouseEventDispatcher
    {   
        private var $cursor:Bitmap = new Asset.cursorNormal();
		private var $cursorWait:Bitmap = new Asset.cursorWait();
		private var _leader:Boolean;			// follow this instance (if multiuser)
		private var _color:uint;				// strokes color (if multiuser)
        private var _thick:Number = 0;			// strokes thickness (if multiuser)
        private var _label:String;				// display user text
        private var _avg:Boolean;			    // strokes thickness (if multiuser)
        private var $userLabel:TextField;       // private label
        private var $canvas:Array = [];		    // drawing areas
		private var $varCircles:Array = [];	    // drawing areas
		private var $tip:Tooltip;         	    // display info at runtime
		private var $mouse:Object;			    // user info
		private var $screen:Object;			    // additional user info
		
		// compute heatmaps for single-point items
		private var $heatMapLayers:Array = [
            Layers.id.REGISTRATION,
            Layers.id.DRAG,
            Layers.id.STOP, // hesitations, as active areas, are clustered items
            Layers.id.CLICK
        ];
		private var $heatMapSize:int = 15;      // diameter of heatmap's sphere of influence

		public function set leader(value:Boolean):void   { _leader = value; }
		public function set color(value:uint):void 	     { _color  = value; }
        public function set thick(value:Number):void 	 { _thick  = value; }
        public function set label(value:String):void 	 { _label  = value; }
        public function set avg(value:Boolean):void 	 { _avg    = value; }
        
        public function get avg():Boolean { return _avg; }
        public function get color():uint { return _color; }
		  
        public function MouseView(mouseData:Object, screenInfo:Object, canvas:Array) 
        {
			// pass settings to controller
            super(mouseData, screenInfo);
			// save references for drawing
			$mouse  = mouseData;
			$screen = screenInfo;
			$canvas = canvas;
			
			//Tweener.addTween($canvas[Layers.id.BACKGROUND], {alpha:0, time:2, transition:"easeOutQuart"});
			
			// create an empty tooltip instance
            $tip = Tooltip.instance();
            $tip.border = false;
            $tip.backgroundColor = 0xFFFFBB;
            addChild($tip);
            
            // listen to recorded mouse events
            addEventListener(TrackingEvent.MOUSE_INI, 	 onIni);
            addEventListener(TrackingEvent.MOUSE_END, 	 onEnd);
            addEventListener(TrackingEvent.MOUSE_CLICK,  onClick);
            addEventListener(TrackingEvent.MOUSE_DRAG, 	 onDrag);
			addEventListener(TrackingEvent.MOUSE_MOVE, 	 onMove);
            addEventListener(TrackingEvent.MOUSE_STOP,   onStop);
			addEventListener(TrackingEvent.MOUSE_RESUME, onResume);
        };

		protected function onIni(e:TrackingEvent):void 
        {
			drawMousePointer(e.data as Point, "entry");
			addLabels();
			addCursors();
			
            if (super.heatMap)
            {
                $heatMapLayers.forEach(function(elm:*, idx:int, arr:Array):void {
                    $canvas[elm].cacheAsBitmap = true;
                    //$canvas[elm].blendMode = BlendMode.SCREEN;
                    $canvas[elm].filters = DrawUtils.applyBlurFilter($heatMapSize);
                });
            }
            else
            {
                $heatMapLayers.forEach(function(elm:*, idx:int, arr:Array):void {
                    $canvas[elm].filters = [];
                });
            }
        };
		  
		protected function onMove(e:TrackingEvent):void 
        {   
			var p:Point = new Point(e.data.ini.x, e.data.ini.y);
            var q:Point = new Point(e.data.end.x, e.data.end.y);

            if (_label) {
                // add client id and login time
                $userLabel.x = q.x + 15;
                $userLabel.y = q.y - 5;
            }

            $cursor.x = q.x;
            $cursor.y = q.y;
				
			drawRegistrationPoint(p);
				
			drawMousePath(p,q);
				
			if (super.realTime && _leader) {
				ExternalInterface.call("window.smt2fn.doScroll",
                    {   xpos:   q.x,
                        ypos:   q.y,
                        width:  $screen.viewport.width,
                        height: $screen.viewport.height
                    });
			}

			if (e.data.distance > 50) {
				var a:Number = Utils.angle(p, q);
				drawDistanceArrow(q, a, e.data.distance);
			}
        };
		  
		protected function onStop(e:TrackingEvent):void 
        {
			if (super.realTime) {
				$cursorWait.x = e.data.x;
				$cursorWait.y = e.data.y;
				$cursorWait.visible = true;
			}
        };
		  
		protected function onResume(e:TrackingEvent):void 
		{
			drawHesitation(e.data as Point, super.varStopSize);
				
			if (super.realTime) { 
				$cursorWait.visible = false; 
			}
		};
		  
		//protected function onLoop(e:TrackingEvent):void {};
		  
        protected function onClick(e:TrackingEvent):void 
        {
            var p:Point = e.data as Point;

            if (super.heatMap) {
                drawGaussian(Layers.id.CLICK, p);
                return;
            }
            
            var size:int = 8;
            //var clickColor:uint = (_color) ? _color : Layers.getColor(Layers.id.CLICK);
            /*
            $canvas[Layers.id.CLICK].graphics.lineStyle(0, Layers.getColor(Layers.id.CLICK));
            DrawUtils.drawStar($canvas[Layers.id.CLICK], p, size, size/4);
            */
            var c:Sprite = new Sprite();
            c.name = "click @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            // draw hit area for tooltip
            DrawUtils.drawCircle(c, p, size*2, 0x000000, 0);
            
            $tip.addItem(c);
            
            $canvas[Layers.id.CLICK].addChild(c);

            $canvas[Layers.id.CLICK].graphics.lineStyle(6, Layers.getColor(Layers.id.CLICK));
            DrawUtils.drawStar($canvas[Layers.id.CLICK], p, size);
        };
        
        protected function onDrag(e:TrackingEvent):void 
        {
            var p:Point = e.data as Point;
            
            if (super.heatMap) {
                drawGaussian(Layers.id.DRAG, p);
                return;
            }
            
            var dragColor:uint = (_color) ? _color : Layers.getColor(Layers.id.DRAG);
            var size:int = 4;

            var c:Sprite = new Sprite();
            c.name = "drag @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            // draw hit area for tooltip
            DrawUtils.drawCircle(c, p, size*2, 0x000000, 0);

            $tip.addItem(c);
            
            $canvas[Layers.id.DRAG].addChild(c);

            $canvas[Layers.id.DRAG].graphics.lineStyle(3, Layers.getColor(Layers.id.DRAG));
            DrawUtils.drawStar($canvas[Layers.id.DRAG], p, size);
        };
		  
		protected function onEnd(e:TrackingEvent):void 
        {   
            $canvas[Layers.id.CURSOR].removeChild($cursorWait);
            $canvas[Layers.id.CURSOR].removeChild($cursor);

            if (_label) {
                $canvas[Layers.id.CURSOR].removeChild($userLabel);
            }
				
			drawMousePointer(e.data as Point, "exit");

			// compute path centroid
            var centroid:Point = new Point( Maths.arrayAvg(super.cleans.x) * super.discrepance.x, Maths.arrayAvg(super.cleans.y) * super.discrepance.y );
            drawCentroid(centroid);
			
			requestClusters();
			
			/*
			// replace gaussians with colorized Heat Maps (processor-intensive!)
			if (super.heatMap)
            {
                var map:HeatMap;
                $heatMapLayers.forEach(function(elm:*, idx:int, arr:Array):void {
                    map = new HeatMap($canvas[elm], $screen);
                });
            }
            */
			
			// notify parent container
            parent.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_END));
        };
      
		protected function requestClusters():void
		{ 
            var vars:URLVariables = new URLVariables();
			vars.xdata = JSON.encode(super.cleans.x);
			vars.ydata = JSON.encode(super.cleans.y);
			vars.xhr = true; // identify Ajax request
			// prepare the PHP request
			var basePath:String = ExternalInterface.call("window.smt2fn.getBase");
			var request:URLRequest = new URLRequest(basePath + "includes/kmeans.php");
			request.method = URLRequestMethod.POST;
			request.data = vars;
			// call the URL loader
			var loader:URLLoader = new URLLoader();
			loader.addEventListener(Event.COMPLETE, buildClusters);
			loader.load(request);
		};
		
		private function buildClusters(e:Event):void 
        { 
			var loaded:URLLoader = URLLoader(e.target);
    		var km:Object = JSON.decode(loaded.data);
			//ExternalInterface.call("console.log", typeof km + " <-- should be Object! Decoded response: " + km)
			if (typeof km !== 'object') {
				// PHP json_encode or Adobe bug?
				km = JSON.decode(km as String);
			}
			
			// draw clustering (already filtered)
            for (var i:int = 0, cLength:int = km.xclusters.length; i < cLength; ++i) {
				var clusterPt:Point = new Point(km.xclusters[i], km.yclusters[i]);
				var clusterVar:Point = new Point(km.xvariances[i], km.yvariances[i]);
                 
				drawCluster(clusterPt, km.sizes[i], clusterVar);
			}
		};
		
		
		private function addLabels():void
        {   
			if (_label) {
                $userLabel = new TextField();
                var fmt:TextFormat = new TextFormat("_sans");
                $userLabel.defaultTextFormat = fmt;
                $userLabel.text = _label;
                $userLabel.textColor = _color;
                $userLabel.autoSize = TextFieldAutoSize.LEFT;
                $userLabel.selectable = false;
                $canvas["eeCur"].addChild($userLabel);
            }
		};
		  
        private function addCursors():void 
        {       
            if (_color) {
                DrawUtils.changeInstanceColor($cursor, _color);
            }
            
            $canvas[Layers.id.CURSOR].addChild($cursor);
				
            // initially hide the wait cursor
			$cursorWait.visible = false;
			$canvas[Layers.id.CURSOR].addChild($cursorWait);
        };
                 
		private function drawMousePointer(p:Point, type:String):void 
        {
            var cursor:Bitmap, color:uint; 
            if (type == "entry") {
                cursor = new Asset.cursorEntry();
                color = 0x33FF33;
            } else if (type == "exit") {
                cursor = new Asset.cursorExit();
                color = 0xFF3333;
            }
            
            var ee:Sprite = new Sprite();
            ee.name = type + " point @ (" + Math.round(p.x) +", "+ Math.round(p.y) + ")";

            $tip.addItem(ee);
            
            ee.addChild(cursor);
			cursor.x = p.x;
			cursor.y = p.y;
            /*
            // do not add cursors when data is grouped
            if (!_color) {
                ee.addChild(cursor);
				//DrawUtils.changeInstanceColor(cursor, _color);
				cursor.x = p.x;
				cursor.y = p.y;
            }
            */
            $canvas[Layers.id.CURSOR].addChild(ee);
        };

		private function drawRegistrationPoint(p:Point):void
        {
            // draw mask, if allowed
            if (!super.realTime) {
                DrawUtils.drawCircle($canvas[Layers.id.MASK], p, $heatMapSize*2, 0x000000, 1);
            }
            
            if (super.heatMap) {
                drawGaussian(Layers.id.REGISTRATION, p);
                return;
            }

            // else... no heatmap
            var rp:Sprite = new Sprite();
            rp.name = "coord #" + super.count +"; time: "+Maths.roundTo(super.count/stage.frameRate, 2);
            
            $tip.addItem(rp);
            
            // draw square
            var rpColor:uint = (_color) ? _color : Layers.getColor(Layers.id.REGISTRATION);
            const SIZE:int = 3;
            rp.graphics.beginFill(rpColor);
            rp.graphics.drawRect(p.x - SIZE/2, p.y - SIZE/2, SIZE, SIZE);
            rp.graphics.endFill();
            //rp.cacheAsBitmap = true;
            $canvas[Layers.id.REGISTRATION].addChild(rp);
        };
		  
		private function drawMousePath(p:Point, q:Point, showArrowsOnMouseLines:Boolean = false):void
        {
            var pathColor:uint = (_color) ? _color : Layers.getColor(Layers.id.PATH);
            
            $canvas[Layers.id.PATH].graphics.lineStyle(_thick, pathColor);
            $canvas[Layers.id.PATH].graphics.moveTo(p.x, p.y);
            $canvas[Layers.id.PATH].graphics.lineTo(q.x, q.y);
            /*
            if (showArrowsOnMouseLines && Point.distance(p, q) > 8) {
                var a:Arrow = new Arrow(p,q);
                $canvas[Layers.id.PATH].addChild(a);
            }
            */
        };
		  
		private function drawHesitation(p:Point, size:int):void
        {
            var duration:Number = Maths.roundTo(size/stage.frameRate, 2);
            // use multiplier to normalize all circles: 0 < norm < 1
            var norm:Number = Maths.roundTo(size/super.maxStopSize, 2);
            if (size * norm == 0 || duration < 0.05) { return; }

            /*if (super.heatMap) {
                drawGaussian(Layers.id.STOP, p);
                return;
            }*/
            
            var h:Sprite = new Sprite();
            h.name = "stop <b>" + duration + "</b> seconds @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";
            // limit size to 1/2 of current window width (px)
            if (size > $screen.viewport.width/2) { size = Math.round($screen.viewport.width/2 * norm); }

            //var hColor:uint = (_color) ? _color : Layers.getColor(Layers.id.STOP);
            DrawUtils.drawCircle(h, p, size/2, Layers.getColor(Layers.id.STOP), 0.5);
            //DrawUtils.drawCircleCenter(h, p, size);
            $tip.addItem(h);
            
            $canvas[Layers.id.STOP].addChild(h);
            
            // now check previous drawn hesitations
            $varCircles.push(size);
            for (var i:int = 0; i < $canvas[Layers.id.STOP].numChildren; ++i) {
                // swap smaller circles with bigger ones
                if ($varCircles.length > 1 && $varCircles[i] > $varCircles[i-1]) {
                    $canvas[Layers.id.STOP].swapChildrenAt(i, i-1);    
                }
            }
        };
		
        /** @deprecated */
		private function drawDistanceArrow(p:Point, angle:Number, distance:Number):void
        {
            // dvc: direction vector container
            var dvc:Sprite = new Sprite();
            dvc.name = "distance: " + Maths.roundTo(distance,2) + "px";

            $tip.addItem(dvc);
            
            // draw direction arrow
            var dirVect:Bitmap = new Asset.cursorDir();
            // rotate arrow and scale it
            var m:Matrix = dirVect.transform.matrix;
            m.rotate(angle + Math.PI/2);        // add 90º because bitmap is a vertical image
            m.scale(distance/80, distance/80);  // proportional to the distance
            dirVect.transform.matrix = m;
            dirVect.x = p.x;
            dirVect.y = p.y;
            //if (_color) { DrawUtils.changeInstanceColor(dvc, _color); }
            dvc.addChild(dirVect);
            $canvas[Layers.id.DISTANCE].addChild(dvc);
        };
		  
		private function drawCentroid(p:Point):void 
        {
            /*if (super.heatMap) {
                drawGaussian(Layers.id.CENTROID, p);
                return;
            }*/
            
            const SIZE:int = 20;
            var c:Sprite = new Sprite();
            c.name = "centroid @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";

            $tip.addItem(c);
            
            var cColor:uint = (_color) ? _color : Layers.getColor(Layers.id.CENTROID);
            c.graphics.lineStyle(6, cColor);
            DrawUtils.drawStar(c, p, SIZE);
            //DrawUtils.drawCircle(c, p, SIZE, cColor, 0.7);
            
            $canvas[Layers.id.CENTROID].addChild(c);
        };
		  
		private function drawCluster(p:Point, size:int, variance:Point = null):void 
        {
            // do not draw singleton clusters
            if (size < 2) { return; }
            
            var c:Sprite = new Sprite();
            c.name = "<b>" + size + "</b> points in cluster @ (" + Math.round(p.x) + ", " + Math.round(p.y) + ")";

            $tip.addItem(c);
            
            // compute discrepances, since cluster points are retrieved via XHR
            p = new Point(p.x * super.discrepance.x, p.y * super.discrepance.y);
            
            DrawUtils.drawCircle(c, p, size/2, Layers.getColor(Layers.id.CLUSTER), 0.5);
            //DrawUtils.drawCircleCenter(c, p, size/2);
            
            $canvas[Layers.id.CLUSTER].addChild(c);   
        };

        /** Draws a sphere of (global) constant size $heatMapSize */
        private function drawGaussian(id:String, p:Point):void
        {
            DrawUtils.drawCircle($canvas[id], p, $heatMapSize, Layers.getColor(id), 0.5);
            /*
            var c:Sprite = new Sprite();
            DrawUtils.drawCircle(c, p, $heatMapSize, Layers.getColor(id), 1);
            $canvas[id].addChild(c);

            $canvas[Layers.id.BACKGROUND].mask = c;
            */
        };
		  
    } // end class
}