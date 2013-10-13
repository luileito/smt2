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
    import com.speedzinemedia.smt.draw.LayoutType;
    import com.speedzinemedia.smt.utils.Utils;
    import com.speedzinemedia.smt.utils.Maths;
    import com.speedzinemedia.smt.events.TrackingEvent;
    
     
    public class MouseEventDispatcher extends Sprite
    {
        public var paused:Boolean;        
		    public var config:Object = {
		      realtime: true,  // realtime replay
		      heatmaps: false, // use heatmaps (shadowmaps, actually)
		      nopauses: false  // skip dwell times while replaying
		    };
		            
        private var $freq:int, $coords:Object, $numCoords:uint, _screen:Object, _cleans:Object, $intervalId:uint;
        private var _currPt:Point, $nextPt:Point, $iniClick:Point, $endClick:Point;
        private var _count:int, _stopSizes:Vector.<Number>, _varStopSize:int, _maxStopSize:int;
        
        // read-only
        public function get count():int {
            return _count;
        }
        public function get position():Point {
            return _currPt;
        }
		    public function get cleans():Object {
            return _cleans;
        }
		    public function get screen():Object {
            return _screen;
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
            _screen = screen;
			
			      preprocess();
        };
        
        protected function normalizeCoords(p:Point):Point
        {
            var px:Number = p.x, py:Number = p.y;
            
            switch(_screen.layoutType) {
              case LayoutType.LEFT:
                // do nothing, as content is ragged left
                break;            
              case LayoutType.CENTER:
                // in this case, only horizontal coordinates should be updated
                px += _screen.currWindow.width / _screen.prevWindow.width;
                break;
              case LayoutType.RIGHT:
                px += _screen.currWindow.width - _screen.prevWindow.width;              
                break;
              case LayoutType.LIQUID:
              default:
                px *= _screen.currWindow.width / _screen.prevWindow.width;
                py *= _screen.currWindow.height / _screen.prevWindow.height;
                break;                
            }
            
            return new Point(px,py);
        };
        
		    private function preprocess():void 
		    {
			      var xclean:Vector.<int> = new Vector.<int>();
			      var yclean:Vector.<int> = new Vector.<int>();
			      // user stops & clean coords: useful for time-depending circles (dwell times) and path centroid
            _stopSizes = new Vector.<Number>($numCoords, true);
            var size:int = 0;
            var stops:Vector.<Number> = new Vector.<Number>();
            
            for (var k:int = 0; k < $numCoords - 1; ++k)
            {
              if ($coords.x[k] == $coords.x[k+1] && $coords.y[k] == $coords.y[k+1]) {
                ++size;
              } else {
                // store all user stops (time) for drawing variable circles later
                if (size > 0) { stops.push(size); }
                // reset size
                size = 0;
                // store clean mouse coordinates
                xclean.push($coords.x[k]);
                yclean.push($coords.y[k]);
              }
              _stopSizes[k] = size;
            }
			      // save clean object (useful for clustering and centroid)
			      _cleans = { x: xclean, y: yclean };
            // set max size for variable circles (for later normalization)
            _maxStopSize = Maths.arrayMax(stops);
            //ExternalInterface.call('console.log', _stopSizes.length, xclean.length);
		    };
		  
        public function init(conf:Object):void
        {   
            for (var prop:String in conf) {
              if (config.hasOwnProperty(prop) && typeof conf[prop] !== null) {
                config[prop] = conf[prop];
              }
            }
            
			      paused = false;			      
			      _count = 0;
  			    _varStopSize = 0;
			                	
            if (config.realtime) {
                start();
            } else {
                finish();
            }
        };
		  
        public function start():void
        {
            if ($intervalId) clearInterval($intervalId);
            $intervalId = setInterval(loop, $freq);
        };
        
        public function finish():void
        {
			      config.realtime = false;
            for (var n:int = _count; n < $numCoords; ++n) { loop(); }
        };
        
		    public function pause():void
        {
			      paused = !paused;
        };

		    public function stop():void
        {
			      paused = true;
        };

		    public function resume():void
        {
			      paused = false;
        };
                		    
		    public function seekTo(perc:Number):void
        {
            var t:int = Math.floor($numCoords * perc);
            // draw skipped points
			      for (var n:int = _count; n <= t; ++n) { nextFrame(); }
            //_count = t;
        };
                		  
        protected function loop():void 
        {
            if (config.realtime && paused) { return; }
            
            if (config.realtime && config.nopauses) {
              // move when there is no pause
              if (_stopSizes[_count + 1] == _stopSizes[_count]) {
                nextFrame();
              } else {
                // find next index where there is no pause
                for (var n:int = _count; n < $numCoords - 1; ++n) {
                  if (_stopSizes[n + 1] < _stopSizes[n]) {
                    break;
                  }
                }
                // and draw skipped points
                for (var i:int = _count; i <= n; ++i) { nextFrame(); }
                parent.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_ADVANCE, n/$numCoords));
                if (_count == $numCoords - 1) finish();
              }
            } else {
              // otherwise, replay as ususal
              nextFrame();
            }
        };
        
        protected function nextFrame():void 
        {
            getPoints();

            if (_count == 0) {
                this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_INI, _currPt));
            }
            
            if (_count < $numCoords) 
            {
                var mouseDist:Number = Point.distance(_currPt, $nextPt);
                var o:Object = {ini:_currPt, end:$nextPt, distance:mouseDist};
                //this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_LOOP, o));
                                
                // dispatch movements and hesitations (pauses)
                if (mouseDist > 0)
                {
                    this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_MOVE, o));
					          if (_varStopSize > 0) {
					             this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_RESUME, _currPt));
					          }
					          _varStopSize = 0;
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
            } 
            else
            {
                clearInterval($intervalId);
                this.dispatchEvent(new TrackingEvent(TrackingEvent.MOUSE_END, _currPt));
            }
            
            ++_count;
        };
                
        private function getPoints():void 
        {
            if (_count >= $numCoords - 1) return;
            
            _currPt  = normalizeCoords(new Point($coords.x[_count], $coords.y[_count]));
            $nextPt  = normalizeCoords(new Point($coords.x[_count + 1], $coords.y[_count + 1]));

            var currClickType:int = $coords.type[ _count     ];
            var nextClickType:int = $coords.type[ _count + 1 ];
            var currClickX:int = currClickType > 0 ? $coords.x[ _count     ] : 0;
            var nextClickX:int = nextClickType > 0 ? $coords.x[ _count + 1 ] : 0;
            var currClickY:int = currClickType > 0 ? $coords.y[ _count     ] : 0;
            var nextClickY:int = nextClickType > 0 ? $coords.y[ _count + 1 ] : 0;

            $iniClick = normalizeCoords(new Point(currClickX, currClickY));
            $endClick = normalizeCoords(new Point(nextClickX, nextClickY));
        };
        
        
    } // end class
}
