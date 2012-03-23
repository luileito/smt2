package com.speedzinemedia.smt.display {

    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.Shape;
    import flash.display.StageQuality;
    import flash.display.Sprite;
    import flash.events.Event;
    import flash.events.MouseEvent;
    import flash.external.ExternalInterface;
    import flash.geom.ColorTransform;
    import flash.net.SharedObject;
    import flash.net.navigateToURL;
    import flash.net.URLRequest;
    import flash.net.URLVariables;
    import flash.text.TextField;
    import flash.text.TextFormat;
    import flash.text.TextFieldAutoSize;
    
    import com.bit101.components.CheckBox;
    import com.bit101.components.ColorChooser;
    import com.bit101.components.Label;
    import com.bit101.components.Panel;
    import com.bit101.components.PushButton;
    import com.bit101.components.RadioButton;
    import com.speedzinemedia.smt.events.*;
    import com.speedzinemedia.smt.charts.*;
    import com.speedzinemedia.smt.display.CustomSprite;
    import com.speedzinemedia.smt.display.Draggable;
    import com.speedzinemedia.smt.display.Layers;
    import com.speedzinemedia.smt.draw.AntsRectangle;
    import com.speedzinemedia.smt.draw.DrawUtils;
    import com.speedzinemedia.smt.modal.ModalConfirm;
    import com.speedzinemedia.smt.modal.ModalAlert;
    import com.speedzinemedia.smt.utils.*;
    import com.speedzinemedia.smt.Tracking;
    /**
     *  (smt) Control Panel
     *  @autor      Luis Leiva
     *  @version    2.0.1
     *  @date       12 Feb 2009
     */
    public class ControlPanel extends Sprite
    {
        private var $panel:Panel;                   // control panel
        private var $panelBoundary:AntsRectangle;   // control panel bounding box
        //private var $panelBoundary:Shape = new Shape(); // too basic
        
        private var $info:Array;                     // parent info (grouped data, coords, etc)
        private var $radioButtonId:String;           // radio button identifier
        private var $color:uint;                     // current layer color
        private var $cc:ColorChooser;                // change layer colors at runtime
        private var $showAllLayers:Boolean;          // flag to (de)select all layers
        private var $defIndex:int = 0;               // default layer index (0 is background)
        private var $tabIndexOrder:int = 1;          // tabulation index
        private var $savedSettings:SharedObject;     // remember user settings
        private var $isReplayFinished:Boolean;       // don't fire events twice
        private var $trails:Array;                   // user trail (visited pages)
        private var $currTrailId:int;                // current user trail id
        private var $currTrailPos:int;               // current user trail index position
        private var $trailUrl:String;                // full path to track PHP file
        private var $isNextTrailAvailable:Boolean;   // flag to load next user trail 
        
        private var MARGIN:int = 10;                 // panel margins
        private var COLUMNS:int = 4;                 // number of columns
        private var COLUMN_WIDTH:int = 130;          // panel column height
        private var PANEL_HEIGHT:int;                // panel width
        private var PANEL_WIDTH:int;                 // and height
        private var XPOS:int = 10;                   // drawing helpers
        private var YPOS:int = 35;
        private var ID:Object = {                    // panel ID references
            SEL: {
                INVERT:  "invert", 
                TOGGLE:  "toggle",
                SAVED:   "saved",
                DEFAULT: "default"
            },
            OUT: {
                REPLAYRT:  "replayRT",
                TOGGLEHQ:  "toggleHQ",
                LOADTRAIL: "loadTrail",
                HEATMAP:   "heatMap",
                REMEMBER:  "remember"
            },
            TIME: {
                X: "timeX",
                Y: "timeY"
            }
        };

        public function ControlPanel(parent:DisplayObjectContainer, options:Array)
        {
            // { activity: mouseInfo, screen: screenInfo, color: m.color, avg: u[i].avg }
            $info = options;
            parent.addChild(this);

            init();
        };

        private function init():void 
        {
            getUserTrails();
            // load custom selection, if available
            $savedSettings = SharedObject.getLocal("smt-controlPanel", "/");
            $showAllLayers = ($savedSettings.size > 0);
            // build panel
            createControlPanel();
            // update panel if needed
            checkSavedSettings();
            // on finish the mouse replay, check for more user trails
            addEventListener(ControlPanelEvent.REPLAY_COMPLETE, replayIsCompleted);
        };
        
        private function getUserTrails():void
        {
            var p:Object = parent.loaderInfo.parameters;
            $trailUrl = Utils.getFlashVar(p.trailurl, "string");

            if ($info.length > 1 || !$trailUrl) return;
            
            // cast all trails ids to real number array
            $trails = Maths.arrayCastNumbers(Utils.getFlashVar(p.trails, "array"));
            $currTrailId = Utils.getFlashVar(p.currtrail, "int");
            $currTrailPos = $trails.indexOf($currTrailId);
            $isNextTrailAvailable = ($currTrailPos < $trails.length - 1);
        };
                
        private function setPanelDimensions():void 
        {
            // compute panel height, assuming 23px of average height for each checkbox/radio button
            for (var c:int = 0, sum:int = 0; c < Layers.collectionLength; ++c, sum += 23) {}
            PANEL_HEIGHT = sum + MARGIN * 2;
            PANEL_WIDTH  = COLUMN_WIDTH * COLUMNS + MARGIN * 2;  // panel width: column width * number of columns + margins
        };
        
        private function createControlPanel():void 
        {   
            setPanelDimensions();
            createBasePanel();
            createPanelBoundary();
            createLayersColumn();
            createSelectionsColumn();
            //if ($info.length == 1) {
                createColorsColumn(); // changing colors is available for single user replays
            //}
            createVisualizationColumn();
        };
        
        private function createBasePanel():void 
        {
            // create panel @ center,top
            $panel = new Panel(this, (stage.stageWidth + PANEL_WIDTH)/2, MARGIN);
            $panel.setSize(PANEL_WIDTH, PANEL_HEIGHT);

            new Draggable($panel);
            
            // do not show panel by default, or load user choice
            $panel.visible = ($savedSettings.size > 0) ? $savedSettings.data.showControlPanel : false;
            // toggle control panel when clicking on parent stage (dummy layer)
            parent.addEventListener(MouseEvent.CLICK, togglePanel);
            parent.addEventListener(MouseEvent.MOUSE_MOVE, updatePanelPosition);  
        };
        
        private function createPanelBoundary():void 
        {
            // add ants rectangle as Panel boundary
            $panelBoundary = new AntsRectangle($panel);
            /*
            $panelBoundary = new Shape();
            $panelBoundary.graphics.lineStyle(0, 0xAAAAAA);
            $panelBoundary.graphics.drawRect(0, 0, PANEL_WIDTH, PANEL_HEIGHT);
            */
            $panelBoundary.visible = false;
            $panelBoundary.x = $panel.x;
            $panelBoundary.y = $panel.y;
            this.addChild($panelBoundary);
        };  
        
        private function createLayersColumn():void 
        {
            var label1:Label = new Label($panel.content, XPOS, MARGIN, "LAYERS");
            for (var i:int = 0; i < Layers.collectionLength; ++i) {
                var cb:CheckBox = new CheckBox($panel.content, label1.x + MARGIN, YPOS, Layers.collection[i].label, toggleVisualizationLayer);
                cb.name = Layers.collection[i].id;
                cb.selected = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].visible : Layers.collection[i].visible;
                setTabIndex(cb);
                YPOS += 20;
            }
            updateColumnHelpers();
        };

        private function createSelectionsColumn():void 
        {
            var label2:Label = new Label($panel.content, XPOS, MARGIN, "CUSTOM SELECTIONS");
            var custObj:Array = [
                { id: ID.SEL.INVERT,  label: "invert layers"     },
                { id: ID.SEL.TOGGLE,  label: "toggle all/none"   },
                { id: ID.SEL.DEFAULT, label: "default selection" }
            ];

            if ($savedSettings.data.remember) {
                custObj.push({ id: ID.SEL.SAVED, label: "my selection" });
			}
				
            for (var i:int = 0; i < custObj.length; ++i) {
                var pb1:PushButton = new PushButton($panel.content, label2.x + MARGIN, YPOS, custObj[i].label, loadCustomSelection);
                pb1.name = custObj[i].id;
                setTabIndex(pb1);
                YPOS += 25;
            }   
            
            var label3:Label = new Label($panel.content, XPOS, YPOS, "TIME CHARTS");
            
            var timeObj:Array = [
                { id: ID.TIME.X, label: "X coords vs. time" },
                { id: ID.TIME.Y, label: "Y coords vs. time" }
            ];
			
			YPOS = 25;
            for (var j:int = 0; j < timeObj.length; ++j) {
                var pb2:PushButton = new PushButton($panel.content, label3.x + MARGIN, label3.y + YPOS, timeObj[j].label, showTimeChart);
                pb2.name = timeObj[j].id;
                setTabIndex(pb2);
                YPOS += 25;
            }
            var pb3:PushButton = new PushButton($panel.content, label3.x + MARGIN, label3.y + YPOS, "3D (experimental)", show3D);
            
            updateColumnHelpers();    
        };
        
        private function createColorsColumn():void 
        {
            var label3:Label = new Label($panel.content, XPOS, MARGIN, "COLORS");
            for (var k:int = 0; k < Layers.collectionLength; ++k)
            {
                // color of interacted areas cannot be changed due to blend mode
                if (Layers.collection[k].id == Layers.id.MASK) continue;
                
                if (Layers.collection[k].color) {
                    var rb:RadioButton = new RadioButton($panel.content, label3.x + MARGIN, YPOS, Layers.collection[k].label, false, selectCurrentColor);
                    rb.name = Layers.collection[k].id;
                    setTabIndex(rb);
                    YPOS += 20;
                }
            }
            $cc = new ColorChooser($panel.content, label3.x + MARGIN, YPOS + 5, 0x000000, changeLayerColor);
            $cc.usePopup = true;
            setTabIndex($cc);
            // update ColorChooser here
            updateColorChooser();
            
            updateColumnHelpers();
        };
                
        private function showTimeChart(e:MouseEvent):void 
        {
            var chartType:String = (e.currentTarget.name == ID.TIME.X) ? TimeChart.TYPE_HORIZONTAL : TimeChart.TYPE_VERTICAL;

            var chartSettings:Object = {
                data:       $info,
                type:       chartType,
                label:      chartType + " mouse coordinates vs. time (normalized scales)"
            };
            
            new ChartContainer(parent, chartSettings, TimeChart);
        };

        private function show3D(e:MouseEvent):void
        {
            new ChartContainer(parent, {data: $info}, Plot3D);
        };
        
        private function createVisualizationColumn():void 
        {
            var label4:Label = new Label($panel.content, XPOS, MARGIN, "VISUALIZATION");
            var outObj:Array = [
                { id: ID.OUT.REPLAYRT,  label: "Replay in real time",  callback: toggleReplayMode  },
                { id: ID.OUT.HEATMAP,   label: "Use heatmaps",         callback: toggleHeatMap     },
                { id: ID.OUT.TOGGLEHQ,  label: "Toggle High Quality",  callback: toggleQuality     },
                { id: ID.OUT.REMEMBER,  label: "Remember settings",    callback: rememberSettings  }
                //{ id: ID.OUT.LOADTRAIL, label: "Autoplay next trail",  callback: loadTrails        }
            ];

            // add CheckBoxes
            for (var u:int = 0; u < outObj.length; ++u) {
                var out:CheckBox = new CheckBox($panel.content, XPOS + MARGIN, YPOS, outObj[u].label, outObj[u].callback);
                out.name = outObj[u].id;
                setTabIndex(out);
                YPOS += 20;
            }
            
			updateColumnHelpers();
        };
        
        private function updateColumnHelpers():void 
        {
            XPOS += COLUMN_WIDTH;
            YPOS = 35; 
        };
        
        private function checkSavedSettings():void 
        {
            // check custom saved settings
            if ($savedSettings.size > 0)
            {
                setCheckBoxState(ID.OUT.REPLAYRT,  $savedSettings.data.replayRT);
                setCheckBoxState(ID.OUT.TOGGLEHQ,  $savedSettings.data.toggleHQ);
                setCheckBoxState(ID.OUT.HEATMAP,   $savedSettings.data.heatMap);
                //setCheckBoxState(ID.OUT.LOADTRAIL, $savedSettings.data.loadTrail); // create it later
                setCheckBoxState(ID.OUT.REMEMBER,  true); // obviously ;)
            }
            else {
                // by default, if no saved settings, start in real-time mode
                setCheckBoxState(ID.OUT.REPLAYRT, true);
                setCheckBoxState(ID.OUT.TOGGLEHQ, true);
            }
            
            // loading next user trail is only available when replaying in real time
            if (getCheckBoxState(ID.OUT.REPLAYRT)) {
                // don't display interacted areas in real-time replay (it's processor-intensive)
                toggleVisiblePanelOption(Layers.id.MASK);
                createNextTrailOption();
            } else {
                toggleVisiblePanelOption(ID.OUT.LOADTRAIL);
            }

            // finally check stage quality
            toggleQuality();
        };
        
        private function createNextTrailOption():void 
        {
            if ($info.length > 1) return;
            
			// set this item under "remember settings"
			var cbRef:CheckBox = $panel.content.getChildByName(ID.OUT.REMEMBER) as CheckBox;
			
            createPanelOption(CheckBox, { x: cbRef.x, y: cbRef.y + 20, id: ID.OUT.LOADTRAIL, label: "Autoplay trails", callback: loadTrails });
            // check state (reset when toggling realtime mode)
            if ($savedSettings.size > 0) {
                setCheckBoxState(ID.OUT.LOADTRAIL, $savedSettings.data.loadTrail);
            }
        };
        
        /* Create any type of available content in $panel: PushButton, CheckBox, etc. */
        private function createPanelOption(baseClass:Object, prop:Object):void 
        {
            var newOpt:DisplayObject = new baseClass($panel.content, prop.x, prop.y, prop.label, prop.callback);
            newOpt.name = prop.id;
            setTabIndex(newOpt);
        };
        
        private function destroyPanelOption(oid:String):void 
        {   
            var instance:DisplayObject = $panel.content.getChildByName(oid);
            $panel.content.removeChild(instance);
        };
        
        private function toggleVisiblePanelOption(oid:String):void
        {
            var instance:DisplayObject = $panel.content.getChildByName(oid);
            // ensure that layer exists
            if (instance) {
                instance.visible = !instance.visible;
            }
        };
        
        private function replayIsCompleted(e:ControlPanelEvent):void
        {
            if ($isReplayFinished || !getCheckBoxState(ID.OUT.REPLAYRT)) { return; }

            // use browser dialog API for cross-look'n'feel
            loadNextUserTrail();
            /*
            if (getCheckBoxState(ID.OUT.LOADTRAIL)) {
                loadNextUserTrail();
            } else if ($isNextTrailAvailable) {
                showModalDialog();
            }
            */
            $isReplayFinished = true;
        };
        
        /** @deprecated */
        private function showModalDialog():void
        {
            var mc:ModalConfirm = new ModalConfirm(this, "This user also browsed more pages.\nDo you want to replay the next log?");
            mc.addEventListener(ModalEvent.MODAL_ACCEPT, loadNextUserTrail);
        };
        
        private function setTabIndex(o:*):void 
        {
            o.tabIndex = $tabIndexOrder; 
            $tabIndexOrder++;
        };
        
        private function togglePanel(e:MouseEvent):void 
        {
            if (e.currentTarget is Tracking && e.ctrlKey) {
                $panel.visible = !$panel.visible;
                $panelBoundary.visible = !$panel.visible;
                rememberSettings();
            }
        };
        
        private function updatePanelPosition(e:MouseEvent):void 
        {
            if (e.ctrlKey) {
                $panelBoundary.x = e.localX;
                $panelBoundary.y = e.localY;
            }
            if (!$panel.visible) {
                $panel.x = e.localX;
                $panel.y = e.localY;
                // show panel boundary while control key is pressed
                $panelBoundary.visible = e.ctrlKey;
                e.updateAfterEvent();
            }
        };
                
        private function toggleVisualizationLayer(e:MouseEvent):void
        {
            var selectedLayer:CustomSprite = parent.getChildByName(e.currentTarget.name) as CustomSprite;
            selectedLayer.visible = !selectedLayer.visible;
            
            rememberSettings();
        };

        private function loadCustomSelection(e:MouseEvent = null, myState:String = ""):void 
        {
            var choice:String = (e) ? e.currentTarget.name : myState;

            var layerState:Boolean;
            // reset 'toggle all' flag outside the loop
            $showAllLayers = !$showAllLayers;
            // update layers
            for (var i:int = 0; i < Layers.collectionLength; ++i) {
                var layer:CustomSprite = parent.getChildByName(Layers.collection[i].id) as CustomSprite;
                switch (choice) {
                    case ID.SEL.INVERT:
                        layerState = !layer.visible;
                        break;
                    case ID.SEL.TOGGLE:
                        layerState = $showAllLayers;
                        break;
                    case ID.SEL.SAVED:
                        layerState = $savedSettings.data.layers[i].visible;
                        break;
                    case ID.SEL.DEFAULT:
                    default:
                        layerState = Layers.collection[i].visible;
                        break;
                }
                layer.visible = layerState;
            }
            // now update CheckBoxes for selected layers
            var checkBoxState:Boolean;
            for (var j:int = 0; j < Layers.collectionLength; ++j) {
                var cb:CheckBox = $panel.content.getChildByName(Layers.collection[j].id) as CheckBox;
                switch (choice) {
                    case ID.SEL.INVERT:
                        checkBoxState = !cb.selected;
                        break;
                    case ID.SEL.TOGGLE:
                        checkBoxState = $showAllLayers;
                        break;
                    case ID.SEL.SAVED:
                        checkBoxState = $savedSettings.data.layers[j].visible;
                        break;
                    case ID.SEL.DEFAULT:
                    default:
                        checkBoxState = Layers.collection[j].visible;
                        break;
                }
                cb.selected = checkBoxState;
            }

            rememberSettings();
            
            if ($info.length > 1) return;
            
            // if user settings are set, update layers colors
            if ($savedSettings.size > 0 && choice == ID.SEL.SAVED) 
            {
                for (var k:int = 0; k < Layers.collectionLength; ++k) {
                    var my:CustomSprite = parent.getChildByName($savedSettings.data.layers[k].id) as CustomSprite;
                    var color:uint = $savedSettings.data.layers[k].color;
                    changeLayerColor(null, my.name, color);
                }
                // color column is optional
                if ($cc) { updateColorChooser(); }
                
                rememberSettings();
            }
        };

        private function toggleReplayMode(e:MouseEvent):void
        {
            var rt:Boolean = e.currentTarget.selected;
            // check also heatMap state
            var hm:Boolean = getCheckBoxState(ID.OUT.HEATMAP);
            // notify parent container
            parent.dispatchEvent( new ControlPanelEvent(ControlPanelEvent.TOGGLE_REPLAY_MODE, {realTime: rt, heatMap: hm}) );

            toggleVisiblePanelOption(Layers.id.MASK);
            //toggleVisiblePanelOption(ID.OUT.LOADTRAIL);
            if (rt)
            {
                createNextTrailOption(); 
            }
            else
            {
                try {
                    destroyPanelOption(ID.OUT.LOADTRAIL);
                } catch(e:Error) {} // ... this checkbox wasn't created
            }
            rememberSettings();
        };
        
        /* can be called on init */
        private function toggleQuality(e:MouseEvent = null):void
        {
            var state:Boolean = getCheckBoxState(ID.OUT.TOGGLEHQ);
            stage.quality = (state) ? StageQuality.HIGH : StageQuality.LOW;
            // save settings only if user checked state
            if (e) { rememberSettings(); }
        };
        
        private function toggleHeatMap(e:MouseEvent):void
        {
            var hm:Boolean = e.currentTarget.selected;
            // check also realTime state
            var rt:Boolean = getCheckBoxState(ID.OUT.REPLAYRT);
            // notify parent container
            parent.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.TOGGLE_REPLAY_MODE, {realTime: rt, heatMap: hm}));
            
            rememberSettings();
        };
        
        private function loadTrails(e:MouseEvent):void
        {
            rememberSettings();
            
            if (getCheckBoxState(ID.OUT.LOADTRAIL) && $isReplayFinished) {
                loadNextUserTrail();
            }
        };
        
        /* Called from Modal Alert and Push Button */
        private function loadNextUserTrail(e:ModalEvent = null):void
        {   
            if (!getCheckBoxState(ID.OUT.REPLAYRT)) { return; }
            
            var settings:Object = {
                api:        "swf", 
                trailurl:   $trailUrl, 
                trails:     $trails, 
                currtrail:  $currTrailId, 
                autoload:   getCheckBoxState(ID.OUT.LOADTRAIL)
            };
            // use browser dialog API for cross-look'n'feel
            ExternalInterface.call("window.smt2fn.loadNextMouseTrail", settings);
            /*
            if (!$isNextTrailAvailable) {
                var noMore:ModalAlert = new ModalAlert(this, "This user did not browse more pages.");
                return;
            }
            // if user agreed or checked the proper CheckBox, load next trail
            if (e || getCheckBoxState(ID.OUT.LOADTRAIL)) { // && Utils.allowDomainURI($trailUrl)
                var vars:URLVariables = new URLVariables();
                vars.id = $trails[ $currTrailPos + 1 ];
                vars.api = "swf";
                var request:URLRequest = new URLRequest($trailUrl);
                request.data = vars;
                try {
                    navigateToURL(request, "_self"); // allow popup in Firefox ...
                } catch (e:Error) {}
            }
            */
        };
        
        private function selectCurrentColor(e:MouseEvent):void
        {
            $radioButtonId = e.currentTarget.name;
            // check saved data
            if ($savedSettings.size > 0) {
                var i:int = Layers.getIndex($radioButtonId);
                $color = $savedSettings.data.layers[i].color;
            } else {
                var selectedLayer:CustomSprite = parent.getChildByName($radioButtonId) as CustomSprite;
                $color = selectedLayer.color;
            }
            
            updateColorChooser();
        };
        
        /** Updates the ColorChooser instance with the default global $color */
        private function updateColorChooser():void
        {
            if (!$color) $color = 0x000000;
            $cc.value = $color;
        };
        
        /** Gets the color from ColorChooser (e != null) or SharedObject */
        private function changeLayerColor(e:Event = null, id:String = "", color:uint = 0):void
        {
            var layerId:String, layerColor:uint;
            if (e) {
                layerId     = $radioButtonId;
                layerColor  = e.target.value;
            } else {
                layerId     = id;
                layerColor  = color;
            }

            var selectedLayer:CustomSprite = parent.getChildByName(layerId) as CustomSprite;
            // note that some Tracking layers do not have color asigned
            if (Layers.getColor(layerId)) {
                DrawUtils.changeInstanceColor(selectedLayer, layerColor);
                // update (populated to parent layer)
                selectedLayer.color = layerColor;
            }
            if (e) { rememberSettings(); }
        };
        
        // will listen to MouseEvent only when checking the CheckBox instance
        private function rememberSettings(e:MouseEvent = null):void 
        {
            var remember:CheckBox = $panel.content.getChildByName(ID.OUT.REMEMBER) as CheckBox;
            if (remember.selected) {
                // save layers state
                var layers:Array = []; 
                for (var i:int = 0; i < Layers.collectionLength; ++i) {
                    var layer:CustomSprite = parent.getChildByName(Layers.collection[i].id) as CustomSprite;
                    layers.push({ id: layer.name, color: layer.color, visible: layer.visible }); 
                }
                // save visualization states
                $savedSettings.data.replayRT = getCheckBoxState(ID.OUT.REPLAYRT);
                $savedSettings.data.toggleHQ = getCheckBoxState(ID.OUT.TOGGLEHQ);
                $savedSettings.data.heatMap = getCheckBoxState(ID.OUT.HEATMAP);
                $savedSettings.data.loadTrail = getCheckBoxState(ID.OUT.LOADTRAIL);
                $savedSettings.data.showControlPanel = $panel.visible;
                $savedSettings.data.remember = true;
                // store saved layers (ant their states)
                $savedSettings.data.layers = layers;
                $savedSettings.flush();
                //ExternalInterface.call("console.log", "saved data:" + $savedSettings.size + " bytes");
            } else if ($savedSettings.size > 0) {
                $savedSettings.clear();
            }
        };
        
        // make this function available outside the scope of this package
        private function getCheckBoxState(cid:String):Boolean
        {
            var cb:CheckBox = $panel.content.getChildByName(cid) as CheckBox;
            if (cb === null) { return false; }
            
            return cb.selected;
        };
        
        private function setCheckBoxState(cid:String, value:Boolean):void 
        {
            var cb:CheckBox = $panel.content.getChildByName(cid) as CheckBox;
            if (cb) {
                cb.selected = value;
            }
        };
        
    } // end class
}
