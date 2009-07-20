/** 
 * (smt)2 simple mouse tracking - replay mode (smt-replay-2.0.0.js)
 * Copyleft (cc) 2006-2009 Luis Leiva
 * Release date: July 19th 2009
 * http://smt.speedzinemedia.com  
 * @class smt2-record
 * @requires smt2-aux Auxiliary (smt)2 functions  
 * @version 2.0.0
 * @author Luis Leiva 
 * @license Dual licensed under the MIT (MIT-LICENSE.txt) and GPL (GPL-LICENSE.txt) licenses. 
 * @see smtAuxFn
 * @deprecated in favor of the new SWF visualization API 
 */
 
//var smtReplay = {}; // used for documenting code only, as this file content is unobtrusively self-encapsulated
(function(){
  /** 
   * (smt)2 default replaying options.
   * This Object should be overriden from the (smt)2 CMS.
   */ 
  var smtOpt = {
    /** 
     * Entry point color
     * @type string 
     */  
    entryPt:  "#9F6",   
    /** 
     * Exit point color
     * @type string     
     */
    exitPt:   "#F66",   
    /** 
     * Registration points color
     * @type string      
     */
    regPt:    "#F0F",   
    /** 
     * Lines color
     * @type string      
     */ 
    regLn:    "#0CC",   
    /** 
     * Clicks color
     * @type string      
     */
    click:    "#F00",   
    /** 
     * Drag and drop color
     * @type string      
     */
    dDrop:    "#ABC",   
    /** 
     * User stops: time-depending circles color
     * @type string      
     */
    varCir:   "#F99",   
    /** 
     * Centroid color
     * @type string      
     */
    cenPt:    "#DDD",   
    /** 
     * Clusters color
     * @type string      
     */
    clust:    "#00F",   
    /** 
     * Static (false) or dynamic mouse replay (true)
     * @type boolean      
     */
    realTime: true,     
    /** 
     * Draw background layer (true) or not (false)
     * @type boolean      
     */
    bgLayer:  true,
    /** 
     * Background layer color
     * @type string
     */
    bgColor:  "#000",     
    /** 
     * Show direction vector (useful if realTime: false)
     * @type boolean      
     */
    dirVect:  false
  };
  
  
  /* do not edit below this line -------------------------------------------- */
  
  
  // get globals
  var smtData     = window.smtData;
  var jsGraphics  = window.jsGraphics;
  var aux         = window.smtAuxFn;
  // check globals
  if (typeof smtData === 'undefined') {     throw("user data is malformed or not found"); } 
  if (typeof jsGraphics === 'undefined') {  throw("jsGraphics library not found");        } 
  if (typeof aux === 'undefined') {         throw("auxiliar (smt) functions not found");  }

  // get user personalized settings
  var custom = window.smtReplayOptions;
  if (typeof custom !== "undefined") { aux.overrideTrackingOptions(smtOpt, custom); }
  
  // remove null distances to compute the mouse path centroid
  var xclean = [];
  var yclean = [];
  
  /* (smt) replay system definition ----------------------------------------- */
  
  
  /** 
   * (smt)2 replaying object.
   * This Object is private. Methods are cited but not documented.
   */
  var smtRep = {
    i:       0,                           // mouse tracking global counter var
    j:       1,                           // registration points size global counter var
    jMax:    0,                           // registration points size limit
    play:    null,                        // mouse tracking identifier
    jg:      null,                        // canvas area for drawing
    jgClust: null,                        // layer for clustering
    viewport: { width:0, height:0 },      // data normalization
    discrepance: {x:1, y:1 },             // discrepance ratios
    paused:  false,                       // pause the visualization
    
    /** 
     * Create drawing canvas layer.
     */
    createCanvas: function(layerName) 
    {
      // canvas layer for mouse trackig
      var jg = document.createElement("div");
          jg.id             = layerName;
          jg.style.position = "absolute";
          jg.style.top      = 0;
          jg.style.left     = 0;
          jg.style.width    = 100 + '%';
          jg.style.height   = 100 + '%';
          jg.style.zIndex   = aux.getNextHighestDepth();
      
      // layer for clustering
      var opacity = 40;
      var jgClust = document.createElement("div");
          jgClust.id              = layerName + "Clust";
          jgClust.style.position  = "absolute";
          jgClust.style.top       = 0;
          jgClust.style.left      = 0;
          jgClust.style.width     = 100 + '%';
          jgClust.style.height    = 100 + '%';
          jgClust.style.opacity   = opacity/100; // for W3C browsers
          jgClust.style.filter    = "alpha(opacity="+opacity+")"; // only for IE
          jgClust.style.zIndex    = jg.style.zIndex + 1;
          
      var body  = document.getElementsByTagName("body")[0];
          body.appendChild(jg);
          body.appendChild(jgClust);
          
      // set the canvas areas for drawing both mouse tracking and clustering
      smtRep.jg = new jsGraphics(jg.id);
      smtRep.jgClust = new jsGraphics(jgClust.id);
    },
    /** 
     * Create background layer.
     */
    setBgCanvas: function(layerName) 
    {
      var opacity = 50, // background layer opacity (%)
          // set layer above the mouse tracking one
          jg = document.getElementById(layerName),
          doc = aux.getPageSize();
          
      var bg = document.createElement("div");
          bg.id                     = layerName + "Bg";
          bg.style.position         = "absolute";
          bg.style.top              = 0;
          bg.style.left             = 0;
          bg.style.width            = doc.width + 'px'; 
          bg.style.height           = doc.height + 'px';
          bg.style.overflow         = "hidden";
          bg.style.backgroundColor  = smtOpt.bgColor;
          bg.style.opacity          = opacity/100; // for W3C browsers
          bg.style.filter           = "alpha(opacity="+opacity+")"; // only for IE
          bg.style.zIndex           = jg.style.zIndex - 1;
      
      var body  = document.getElementsByTagName("body")[0];
          body.appendChild(bg);
    },
    /** 
     * Draw line.
     */
    drawLine: function(ini,end) 
    {
        smtRep.jg.setColor(smtOpt.regLn);
        smtRep.jg.drawLine(ini.x,ini.y, end.x,end.y);
        smtRep.jg.paint();
    },
    /** 
     * Draw mouse click.
     */
    drawClick: function(x,y, isDragAndDrop) 
    {
      var size;
      if (!isDragAndDrop) {
        size = 12;
        var offset = 3;
        smtRep.jg.setColor(smtOpt.click);
        smtRep.jg.drawLine(x-size,y-size, x-offset,y-offset);
        smtRep.jg.drawLine(x-size,y+size, x-offset,y+offset);
        smtRep.jg.drawLine(x+size,y-size, x+offset,y-offset);
        smtRep.jg.drawLine(x+size,y+size, x+offset,y+offset);
      } else {
        size = 6;
        smtRep.jg.setColor(smtOpt.dDrop);
        smtRep.jg.drawRect(x-size/2,y-size/2, size,size);
      }
      smtRep.jg.paint();
    },
    /** 
     * Draw direction arrow in a line.
     */
    drawDirectionArrow: function(ini,end)
    {
      var a = ini.x - end.x,
          b = ini.y - end.y,
          s = 4;
      if (a>0 && b>0) {
        smtRep.jg.drawPolyline([end.x-s,end.x,end.x+s], [end.y+s,end.y,end.y+s]);
      } else if (a<0 && b>0) {
        smtRep.jg.drawPolyline([end.x-s,end.x,end.x-s], [end.y-s,end.y,end.y+s]);
      } else if (a<0 && b<0) {
        smtRep.jg.drawPolyline([end.x-s,end.x,end.x+s], [end.y-s,end.y,end.y-s]);
      } else if (a>0 && b<0) {
        smtRep.jg.drawPolyline([end.x+s,end.x,end.x+s], [end.y-s,end.y,end.y+s]);
      }
      smtRep.jg.paint();
    },
    /** 
     * Draw mouse cursor.
     */
    drawCursor: function(x,y, color) 
    {
      smtRep.jg.setColor(color);
      smtRep.jg.fillPolygon([x,x,   x+4, x+6, x+9, x+7, x+15], 
                            [y,y+15,y+15,y+23,y+23,y+15,y+15]);
      smtRep.jg.paint();
    },
    /** 
     * Draw registration point.
     */
    drawRegistrationPoint: function(x,y) 
    {
      smtRep.jg.setColor(smtOpt.regPt);
      smtRep.jg.fillRect(x-1, y-1, 3, 3);
      smtRep.jg.paint();
    },
    /** 
     * Draw time-depending circle.
     */
    drawVariableCircle: function(x,y, size) 
    {
      // use multiplier to normalize all circles: 0 < norm < 1
      var norm = aux.roundTo(size/smtRep.jMax); 
      if (size * norm === 0 ) { return; }
      // limit size to 1/2 of current window width (px)
      if (size > smtData.wcurr/2) { size = Math.round(smtData.wcurr/2 * norm); }
      // draw
      smtRep.jg.setColor(smtOpt.varCir);
      smtRep.jg.drawEllipse(x - size/2, y - size/2, size, size);
      smtRep.jg.paint();
    },
    /** 
     * Draw centroid as a star.
     */
    drawCentroid: function()
    {
      smtRep.jg.setColor(smtOpt.cenPt);
      // the centroid is computed discarding null distances
      var u = Math.round(aux.array.sum(xclean) * smtRep.discrepance.x / xclean.length),
          v = Math.round(aux.array.sum(yclean) * smtRep.discrepance.y / yclean.length),
          l = 20; // centroid line length
      smtRep.jg.setStroke(5);
    	smtRep.jg.drawLine(u, v, u+l, v-l); // 1st quadrant
    	smtRep.jg.drawLine(u, v, u-l, v-l); // 2nd quadrant
    	smtRep.jg.drawLine(u, v, u-l, v+l); // 3rd quadrant
    	smtRep.jg.drawLine(u, v, u+l, v+l); // 4th quadrant
    	smtRep.jg.setStroke(0); // reset strokes
    	smtRep.jg.paint();
    },
    /** 
     * Draw cluster as a circle.
     */
    drawClusters: function() 
    {
      smtRep.jgClust.setColor(smtOpt.clust);
      var size;
      for (var i = 0, clusters = smtData.clustsize.length; i < clusters; ++i) {
        size = smtData.clustsize[i];
        smtRep.jgClust.fillEllipse(smtData.xclusters[i] * smtRep.discrepance.x - size/2, smtData.yclusters[i] * smtRep.discrepance.y - size/2, size, size);
      }
      smtRep.jgClust.paint();
    },
    /** 
     * Get euclidean distance from point a to point b.
     */
    distance: function(a,b) 
    {
      return Math.sqrt( Math.pow(a.x - b.x,2) + Math.pow(a.y - b.y,2) );
    },
    /** 
     * (smt)2 realtime drawing algorithm.
     */
    playMouse: function() 
    {
      if (smtRep.paused) { return; }

      // mouse coords normalization
      var iniMouse = { 
                        x: smtData.xcoords[smtRep.i] * smtRep.discrepance.x,
                        y: smtData.ycoords[smtRep.i] * smtRep.discrepance.y 
                     };
      var endMouse = { 
                        x: smtData.xcoords[smtRep.i+1] * smtRep.discrepance.x,
                        y: smtData.ycoords[smtRep.i+1] * smtRep.discrepance.y 
                     };
      var iniClick = {
                        x: smtData.xclicks[smtRep.i] * smtRep.discrepance.x, 
                        y: smtData.yclicks[smtRep.i] * smtRep.discrepance.y
                     };
      var endClick = {
                        x: smtData.xclicks[smtRep.i+1] * smtRep.discrepance.x, 
                        y: smtData.yclicks[smtRep.i+1] * smtRep.discrepance.y
                     };
      
      // draw entry point
      if (smtRep.i === 0) {
        smtRep.drawCursor(iniMouse.x,iniMouse.y, smtOpt.entryPt);
      }
      
      // main loop to draw mouse trail
      if (smtRep.i < smtData.xcoords.length) 
      {
        var mouseDistance = smtRep.distance(iniMouse,endMouse);
        // draw registration points
        if (mouseDistance) {
          // there is mouse movement
          if (!smtOpt.dirVect) {
            // show static squares
            smtRep.drawRegistrationPoint(iniMouse.x,iniMouse.y);
          } else {
            // show direction pseudo-arrows
            smtRep.drawDirectionArrow(iniMouse,endMouse);
          }
          // variable circles
          if (smtRep.j > 1) {
            smtRep.drawVariableCircle(iniMouse.x, iniMouse.y, smtRep.j);
          }
          // reset variable circles size
          smtRep.j = 1;
        } else {
          // mouse stop: store variable size (circles)
          ++smtRep.j;
          // give a visual clue while replaying in real time
        }
        // draw lines
        smtRep.drawLine(iniMouse,endMouse);
        // draw clicks
        if (iniClick.x) {
          var clickDistance = smtRep.distance(iniClick,endClick);
          if (!clickDistance) {
            // is a single click
            smtRep.drawClick(endClick.x,endClick.y, false);
          } else if (endClick.x !== 0) {
            // is drag and drop
            smtRep.drawClick(iniClick.x,iniClick.y, true);
          }
        }
        // update mouse coordinates
        ++smtRep.i;
    	}
      
      // draw exit point
      else {
    	  // rewind count 1 step to access the last mouse coordinate
    	  --smtRep.i;
    	  iniMouse.x = smtData.xcoords[smtRep.i] * smtRep.discrepance.x;
        iniMouse.y = smtData.ycoords[smtRep.i] * smtRep.discrepance.y;
        // draw exit point
    	  smtRep.drawCursor(iniMouse.x,iniMouse.y, smtOpt.exitPt);
    	  // draw clusters
    	  smtRep.drawClusters();
        // draw centroid (average mouse position) 
        smtRep.drawCentroid();
        // clear mouse tracking
        clearInterval(smtRep.play);
        smtRep.play = null;
    	}
    },
    /** 
     * Replay method: static or dynamic.
     */
    replay: function(realtime) 
    {
      if (realtime) {
        // fps are stored in smtData object, so we can use that value here
        var interval = Math.round(1000/smtData.fps);
        smtRep.play = setInterval(smtRep.playMouse, interval);
      } else {
        // static mouse tracking visualization 
        for (var k = 0, total = smtData.xcoords.length; k <= total; ++k) {
          smtRep.playMouse();
        }
      }
    },
    /** 
     * Reload method: mouse tracking layers are redrawn.
     * @deprecated     
     */
    reset: function() 
    {
      clearInterval(smtRep.play);
      smtRep.paused = false;
      // reset counters
      smtRep.i = 0;
      smtRep.j = 1;    
      // clear canvas  
      smtRep.jg.clear();
      smtRep.jgClust.clear();
    },
    /** 
     * User can pause the mouse replay by pressing the SPACE key, 
     * or toggle replay mode by pressing the ESC key.
     */
    helpKeys: function(e) 
    {
      // use helpKeys only in realtime replaying
      if (!smtOpt.realTime) { return; }
      
      if (!e) { e = window.event; }
      var code = e.keyCode || e.which;
      // on press ESC key finish drawing
      if (code == 27) {
        // clear main loop
        clearInterval(smtRep.play);
        smtRep.paused = false;
        // end drawing from the current position
        for (var k = smtRep.i, total = smtData.xcoords.length; k <= total; ++k) {
          smtRep.playMouse();
        }
        // set this flag
        smtOpt.realTime = false;
      } else if (code == 32) {
        // on press space bar toggle drawing
        smtRep.paused = !smtRep.paused;
      }
    },
    /** 
     * System initialization.
     */
    init: function() 
    {
      var vp = aux.getWindowSize();
      smtRep.viewport.width = vp.width;
      smtRep.viewport.height = vp.height;
      
      // compute the discrepance ratio
      if (smtData.wprev && smtData.hprev) {
        smtRep.discrepance.x = aux.roundTo(smtData.wcurr / smtData.wprev);
        smtRep.discrepance.y = aux.roundTo(smtData.hcurr / smtData.hprev);  
      }
      //aux.trace('info', smtRep.discrepance.x+" x "+smtRep.discrepance.y+" | "+smtRep.viewport.width+" x "+smtRep.viewport.height);
          
      // precalculate the user stops: useful for time-depending circles and path centroid
      var stops = [];      
      var size = 1;
      for (var k = 0, len = smtData.xcoords.length; k < len; ++k) {
        if (smtData.xcoords[k] == smtData.xcoords[k+1] && smtData.ycoords[k] == smtData.ycoords[k+1]) {
          ++size;
        } else {
          // store all user stops (time) for drawing variable circles later
          if (size > 1) { stops.push(size); }
          size = 1;
          // store clean mouse coordinates
          xclean.push(smtData.xcoords[k]);
          yclean.push(smtData.ycoords[k]);
        }
      }
      // set max size for variable circles
      smtRep.jMax = aux.array.max(stops);       
      // common suffix for tracking canvas and background layers
      var smtName = "smtCanvas";
      // set the canvas layer
      smtRep.createCanvas(smtName);
      // draw the background layer
      if (smtOpt.bgLayer) { smtRep.setBgCanvas(smtName); }
      // allow mouse replay over Flash animations
      aux.allowTrackingOnFlashObjects();
      // init
      smtRep.replay(smtOpt.realTime);
    }
  };
  
  /* (smt)2 replay initialization ------------------------------------------- */
  aux.addEvent(document, "keyup", smtRep.helpKeys);
  //aux.addEvent(window, "resize", smtRep.reset);
  aux.onDOMload(smtRep.init);

})();