/**
 *  Populates mouse trail information
 *  @version    1.0 - 26 Sep 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.mouse {
        
    import flash.display.Sprite;
    import flash.external.ExternalInterface;
    import flash.geom.Point;
    import flash.utils.clearInterval;
    import flash.utils.setInterval;
        
    import com.speedzinemedia.smt.display.Asset;
    import com.speedzinemedia.smt.utils.Utils;
    import com.speedzinemedia.smt.utils.Maths;
    import com.speedzinemedia.smt.events.TrackingEvent;
     
    public class MouseEventDispatcher extends Sprite
    {
        private var $coords:Object, _cleans:Object, $numCoords:uint;
        private var $freq:int;
        private var $intervalId:uint;
        private var _currPt:Point, $nextPt:Point, $iniClick:Point, $endClick:Point;
        private var _dr:Object;
        private var _count:int;
		private var _varStopSize:int, _maxStopSize:int;
		private var _realTime:Boolean = true; // for autoScrolling
		private var _heatMap:Boolean;
		private var _paused:Boolean;
        
        public function set paused(value:Boolean):void {
            _paused = value;
        }
		  
		public function get realTime():Boolean {
            return _realTime;
        }
        public function get heatMap():Boolean {
            return _heatMap;
        }
        public function get paused():Boolean {
            return _paused;
        }
        public function get count():int {
            return _count;
        }
        public function get position():Point {
            return _currPt;
        }
        public function get discrepance():Object {
            return _dr;
        }
		public function get cleans():Object {
            return _cleans;
        }
		public function get maxStopSize():int {
            return _maxStopSize;
        }
		public function get varStopSize():int {
            return _varStopSize;
        }
		  
        public function MouseEventDispatcher(mouse:Object, screen:Object)
        {
            $coords = mouse.coords;
            $freq = Math.round(1000/mouse.fps);
            $numCoords = $coords.x.length;
            _dr = computeDiscrepances(screen);
			
			preprocess();
        };
        
        private function computeDiscrepances(dim:Object):Object
        {
            var w:Number, h:Number;
            try {
                w = dim.currWindow.width / dim.prevWindow.width;
                h = dim.currWindow.height / dim.prevWindow.height;
            } catch(e:Error) {
                w = 1;
                h = 1;
            }
            
            return {x:w, y:h}
        };
        
		private function preprocess():void 
		{
			var xclean:Array = [];
			var yclean:Array = [];
			
			// user stops & clean coords: useful for time-depending circles and path centroid
            var stops:Array = [];
            var size:int = 1;
				
            for (var k:int = 0; k < $numCoords; ++k)
            {
                if ($coords.x[k] == $coords.x[k+1] && $coords.y[k] == $coords.y[k+1]) {
                  ++size;
                } else {
                  // store all user stops (time) for drawing variable circles later
                  if (size > 1) { stops.push(size); }
                  // reset size
                  size = 1;
                  // store clean mouse coordinates
                  xclean.push($coords.x[k]);
                  yclean.push($coords.y[k]);
                }
            }
			// save clean object (useful for clustering and centroid)
			_cleans = { x: xclean, y: yclean };
            // set max size for variable circles (for later normalization)
            _maxStopSize = Maths.arrayMax(stops);
		  };
		  
        public function init(isRealtime:Boolean = true, isHeatmap:Boolean = false):void
        {   
			_count = 0;
			_varStopSize = 1;
			_paused = false;
			_realTime = isRealtime;
			_heatMap = isHeatmap;
            	
            if (isRealtime) {
                start();
            } else {
                finish();
            }
        };
		  
        public function start():void
        {
            if ($intervalId) {
                clearInterval($intervalId);
            }
            
            $intervalId = setInterval(loop, $freq);
        };
        
        public function finish():void
        {
			_realTime = false;
			
            for (var n:int = _count; n <= $numCoords; ++n) { loop(); }
        };
        
		public function pause():void
        {
			_paused = !_paused;
        };
		  
        protected function loop():void 
        {
            if (_realTime && _paused) return;
            
            getPoints();

            if (_count == 0) {
                this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_INI, _currPt));
            }
            
            if (_count < $numCoords)
            {
                var mouseDist:Number = Point.distance(_currPt, $nextPt);
                
                var o:Object = {ini:_currPt, end:$nextPt, distance:mouseDist, count:_count, steps:$numCoords - 2};
                //this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_LOOP, o));
                
                // dispatch movements and hesitations
                if (mouseDist > 0)
                {
                    this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_MOVE, o));
					if (_varStopSize) {
					   this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_RESUME, _currPt));
					}
					_varStopSize = 1;
                }
                else
                {
                    this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_STOP, _currPt));
					++_varStopSize;
                }
                
                // dispatch click events
                if ($iniClick.x)
                {
                    // check distance to next click point
                    var clickDist:int = Math.floor( Point.distance($iniClick, $endClick) );
                    if (clickDist > 0 && !$endClick.x) {
                        // single click
                        this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_CLICK, _currPt));
                    } else if ($endClick.x) {
                        // the mouse is pressed while moving
                        this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_DRAG, _currPt));
                    }
                }
                
                ++_count;
            } 
            else
            {
                clearInterval($intervalId);
                // rewind one step to get the last coordinate
                --_count;
                getPoints();
                
                this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_END, _currPt));
            }
        };
        
        private function getPoints():void 
        {
            _currPt  = new Point($coords.x[ _count     ] * _dr.x, $coords.y[ _count     ] * _dr.y);
            $nextPt  = new Point($coords.x[ _count + 1 ] * _dr.x, $coords.y[ _count + 1 ] * _dr.y);
            
            var currClickType:int = $coords.type[ _count     ];
            var nextClickType:int = $coords.type[ _count + 1 ];
            var currClickX:int = currClickType > 0 ? $coords.x[ _count     ] : 0;
            var nextClickX:int = nextClickType > 0 ? $coords.x[ _count + 1 ] : 0;
            var currClickY:int = currClickType > 0 ? $coords.y[ _count     ] : 0;
            var nextClickY:int = nextClickType > 0 ? $coords.y[ _count + 1 ] : 0;

            $iniClick = new Point(currClickX * _dr.x, currClickY * _dr.y);
            $endClick = new Point(nextClickX * _dr.x, nextClickY * _dr.y);
        };
        
        
    } // end class
}