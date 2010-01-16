/**
 *  (smt) Control Panel
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt {

    import flash.display.Bitmap;
    import flash.display.DisplayObject;
    import flash.display.DisplayObjectContainer;
    import flash.display.Shape;
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
    import flash.ui.Mouse;
    
    import com.bit101.components.CheckBox;
    import com.bit101.components.ColorChooser;
    import com.bit101.components.Label;
    import com.bit101.components.Panel;
    import com.bit101.components.PushButton;
    import com.bit101.components.RadioButton;
    
    import com.speedzinemedia.smt.events.*;
    import com.speedzinemedia.smt.modal.ModalConfirm;
    import com.speedzinemedia.smt.modal.ModalAlert;
    import com.speedzinemedia.smt.draw.AntsRectangle;
    import com.speedzinemedia.smt.Layers;
    
    public class ControlPanel extends Sprite
    {
        private var $panel:Panel;                   // control panel
        private var $panelBoundary:AntsRectangle;   // control panel bounding box
        //private var $panelBoundary:Shape = new Shape(); // too basic
        
        private var $windowHeight:int;               // viewport height
        private var $radioButtonId:String;           // radio button identifier
        private var $color:uint;                     // current layer color
        private var $cc:ColorChooser;                // change layer colors at runtime
        private var $showAllLayers:Boolean;          // flag to (de)select all layers
        private var $move:Bitmap;                    // cursor move icon for control panel
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
                REMEMBER:  "remember"
            }
        };

        public function ControlPanel(parent:DisplayObjectContainer, viewPortHeight:int)
        {
            $windowHeight = viewPortHeight;
            parent.addChild(this);

            init();
        };

        private function init():void 
        {
            getUserTrails();
            // load custom selection, if available
            $savedSettings = SharedObject.getLocal("smtControlPanel");
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
            // cast all trails ids to real number array
            $trails = Maths.arrayCastNumbers(Utils.getFlashVar(p.trails, "array"));
            $currTrailId = Utils.getFlashVar(p.currtrail, "int");
            $currTrailPos = $trails.indexOf($currTrailId);
            $isNextTrailAvailable = ($currTrailPos < $trails.length - 1);
        };
                
        private function setPanelDimensions():void 
        {
            // compute panel height, assuming 23px of average height for each checkbox/radio button
            for (var c:int = 0, sum:int = 0; c < Layers.collectionLength; ++c, sum += 23);                             
            PANEL_HEIGHT = sum + MARGIN * 2;                    
            PANEL_WIDTH = COLUMN_WIDTH * COLUMNS + MARGIN * 2;  // panel width: column width * number of columns + margins
        };
        
        private function createControlPanel():void 
        {   
            setPanelDimensions();
            createBasePanel();
            createPanelBoundary();
            createLayersColumn();
            createCustomSelectionColumn();
            createColorsColumn();
            createVisualizationColumn();
        };
        
        private function createBasePanel():void 
        {
            // create panel at bottom 
            $panel = new Panel(this, MARGIN, $windowHeight - PANEL_HEIGHT - MARGIN*2);
            $panel.setSize(PANEL_WIDTH, PANEL_HEIGHT);
            $panel.addEventListener(MouseEvent.MOUSE_DOWN, dragPanel);
            $panel.addEventListener(MouseEvent.MOUSE_UP, dropPanel);
            $panel.addEventListener(MouseEvent.MOUSE_OVER, showMoveCursor);
            $panel.addEventListener(MouseEvent.MOUSE_OUT, hideMoveCursor);
            $panel.addEventListener(MouseEvent.MOUSE_MOVE, moveMoveCursor, false, 10); // higher priority on move
            // show panel by default, or load user choice
            $panel.visible = (!$savedSettings.size) ? false : $savedSettings.data.showControlPanel;
            // add cursor move
            $move = new Asset.cursorMove();
            $move.visible = false;
            this.addChild($move);
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
            var label1:Label = new Label($panel, XPOS, MARGIN, "LAYERS");            
            for (var i:int = 0; i < Layers.collectionLength; ++i) {
                var cb:CheckBox = new CheckBox($panel, label1.x + MARGIN, YPOS, Layers.collection[i].label, toggleVisualizationLayer);
                cb.name = Layers.collection[i].id;
                cb.selected = ($savedSettings.size > 0) ? $savedSettings.data.layers[i].visible : Layers.collection[i].visible;
                setTabIndex(cb);
                YPOS += 20;
            }
            updateColumnHelpers();
        };
    
        private function createCustomSelectionColumn():void 
        {
            var label2:Label = new Label($panel, XPOS, MARGIN, "CUSTOM SELECTIONS");
            var custObj:Array = [
                { id: ID.SEL.INVERT,  label: "invert layers"     },
                { id: ID.SEL.TOGGLE,  label: "toggle all/none"   },
                { id: ID.SEL.SAVED,   label: "my selection"      },
                { id: ID.SEL.DEFAULT, label: "default selection" }
            ];
            for (var j:int = 0; j < custObj.length; ++j) {
                var pb:PushButton = new PushButton($panel, label2.x + MARGIN, YPOS, custObj[j].label, loadCustomSelection);
                pb.name = custObj[j].id;
                setTabIndex(pb);
                YPOS += 25;
            }   
            updateColumnHelpers();    
        };
        
        private function createColorsColumn():void 
        {
            var label3:Label = new Label($panel, XPOS, MARGIN, "COLORS");
            // set a radio button by default ($defIndex)
            var defRadio:Object = ($savedSettings.size > 0) ? $savedSettings.data.layers[$defIndex] : Layers.collection[$defIndex];
            for (var k:int = 0; k < Layers.collectionLength; ++k) {
                if (Layers.collection[k].color !== null) {
                    // select the default radio button
                    var selected:Boolean = (k == $defIndex);
                    var rb:RadioButton = new RadioButton($panel, label3.x + MARGIN, YPOS, Layers.collection[k].label, selected, selectCurrentColor);
                    rb.name = Layers.collection[k].id;
                    setTabIndex(rb);
                    YPOS += 20;
                }
            }
            // add the color chooser at the end of this column
            $color = Utils.parseColor(defRadio.color);
            $cc = new ColorChooser($panel, label3.x + MARGIN, YPOS + 5, $color, changeLayerColor);
            setTabIndex($cc);
            // update ColorChooser here
            updateColorChooser();
            
            updateColumnHelpers();
        };
        
        private function createVisualizationColumn():void 
        {
            var label4:Label = new Label($panel, XPOS, MARGIN, "VISUALIZATION");
            var outObj:Array = [
                { id: ID.OUT.REPLAYRT,  label: "Replay in real time",  callback: toggleReplayMode  },
                { id: ID.OUT.TOGGLEHQ,  label: "Toggle High Quality",  callback: toggleQuality     },
                { id: ID.OUT.REMEMBER,  label: "Remember my settings", callback: rememberSettings  }
            ];

            // add CheckBoxes
            for (var u:int = 0; u < outObj.length; ++u) {
                var out:CheckBox = new CheckBox($panel, XPOS + MARGIN, YPOS, outObj[u].label, outObj[u].callback);
                out.name = outObj[u].id;
                setTabIndex(out);
                YPOS += 20;
            }
            // column helpers should not be reset, because later we'll add some new
        };
        
        private function updateColumnHelpers():void 
        {
            XPOS += COLUMN_WIDTH;
            YPOS = 35; 
        };
        
        private function checkSavedSettings():void 
        {
            // check custom saved settings
            if ($savedSettings.size > 0) {
                setCheckBoxState(ID.OUT.REPLAYRT,  $savedSettings.data.replayRT);
                setCheckBoxState(ID.OUT.TOGGLEHQ,  $savedSettings.data.toggleHQ);
                setCheckBoxState(ID.OUT.REMEMBER,  true); // obviously ;)
                // fire these functions passing null events
                loadCustomSelection(null, ID.SEL.SAVED);
                toggleQuality();
            } else {
                // by default, if no saved settings, start in real-time mode
                setCheckBoxState(ID.OUT.REPLAYRT, true);
            }
            // loading next user trail is only available when replaying in real time
            if (getCheckBoxState(ID.OUT.REPLAYRT)) {
                createNextTrailOpt();
            }
        };
        
        /* This function is called at runtime */
        private function createNextTrailOpt():void 
        {
            createPanelOption(CheckBox, { x: XPOS + MARGIN, y: YPOS, id: ID.OUT.LOADTRAIL, label: "Load next user trail", callback: loadTrails });
            // check state (reset when toggling realtime mode)
            if ($savedSettings.size > 0 && $savedSettings.data.loadTrail) {
                setCheckBoxState(ID.OUT.LOADTRAIL, true);
            }
        };
        
        /* Create any type of available content in $panel: PushButton, CheckBox, etc. */
        private function createPanelOption(baseClass:Object, prop:Object):void 
        {
            var newOpt:DisplayObject = new baseClass($panel, prop.x, prop.y, prop.label, prop.callback);
            newOpt.name = prop.id;
            setTabIndex(newOpt);
        };
        
        private function destroyPanelOption(oid:String):void 
        {   
            var instance:DisplayObject = $panel.getChildByName(oid);
            $panel.removeChild(instance);
        };
        
        private function replayIsCompleted(e:ControlPanelEvent):void
        {   
            if (!getCheckBoxState(ID.OUT.REPLAYRT) || $isReplayFinished) { return; }
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
        
        private function showMoveCursor(e:MouseEvent):void 
        {
            if (e.target is Panel) {
                Mouse.hide();
                $move.visible = true;
            }
        };
        private function hideMoveCursor(e:MouseEvent):void 
        {
            if (e.target is Panel) {
                Mouse.show();
                $move.visible = false;
            }
        };
        private function moveMoveCursor(e:MouseEvent):void 
        {
            if (e.target is Panel) {
                // substract to set the registration point at center
                $move.x = e.stageX - $move.width/2; 
                $move.y = e.stageY - $move.height/2;
            }
        };

		private function dragPanel(e:MouseEvent):void 
        {
            if (e.target is Panel) {
                e.target.startDrag();
            }
        };
        private function dropPanel(e:MouseEvent):void 
        {
            e.target.stopDrag();   
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
                var cb:CheckBox = $panel.getChildByName(Layers.collection[j].id) as CheckBox;
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
            
            // if user settings are set, update layers colors
            if ($savedSettings.size > 0 && choice == ID.SEL.SAVED) {
                for (var k:int = 0; k < Layers.collectionLength; ++k) {
                    var my:CustomSprite = parent.getChildByName($savedSettings.data.layers[k].id) as CustomSprite;
                    var color:String = $savedSettings.data.layers[k].color;
                        
                    changeLayerColor(null, my.name, color);
                }
                updateColorChooser();
            }
            // rememberSettings(); // turned off because each time a custom selection is set, the saved seletion is overwrite
        };

        private function toggleReplayMode(e:MouseEvent):void
        {
            var realtime:Boolean = e.currentTarget.selected;
            // notify parent container with the Boolean value of the checkBox (stored on 'params' object)
            parent.dispatchEvent(new ControlPanelEvent(ControlPanelEvent.TOGGLE_REPLAY_MODE, realtime));
            
            if (realtime) {
                createNextTrailOpt(); 
            } else {
                destroyPanelOption(ID.OUT.LOADTRAIL); 
            }
            
            rememberSettings();
        };
        
        /* can be called on init */
        private function toggleQuality(e:MouseEvent = null):void
        {            
            var state:Boolean = (e) ? e.currentTarget.selected : $savedSettings.data.toggleHQ;
            stage.quality = (state) ? "HIGH" : "LOW";
            // save settings only if user checked state
            if (e) { rememberSettings(); }
        };
        
        /* Called from Push Button */
        private function loadTrails(e:MouseEvent):void
        {
            if (getCheckBoxState(ID.OUT.LOADTRAIL) && $isReplayFinished) {
                loadNextUserTrail();
            }
            
            rememberSettings();
        };
        
        /* Called from Modal Alert and Push Button */
        private function loadNextUserTrail(e:ModalEvent = null):void
        {   
            if (!getCheckBoxState(ID.OUT.REPLAYRT)) { return; }
            // use browser dialog API for cross-look'n'feel
            ExternalInterface.call("window.smtAuxFn.loadNextMouseTrail", {api:"swf", trailurl:$trailUrl, trails:$trails, currtrail:$currTrailId, autoload:getCheckBoxState(ID.OUT.LOADTRAIL)});
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
            var i:int = Layers.getIndex($radioButtonId);
            var layer:Object = ($savedSettings.size > 0) ? $savedSettings.data.layers[i] : Layers.select($radioButtonId);
            $color = Utils.parseColor(layer.color);
            
            updateColorChooser();
        };
        
        /** Updates the ColorChooser instance with the default global $color */
        private function updateColorChooser():void
        {
            $cc.value = $color;
        };
        
        /** Gets the color from ColorChooser (e != null) or SharedObject */
        private function changeLayerColor(e:Event = null, id:String = "", color:String = ""):void 
        {
            var layerId:String = (e) ? $radioButtonId : id;
            var layerColor:String = (e) ? e.target.text : color;
            // check if user types a color for the first time (no radio is selected)
            if (layerId == null) { 
                layerId = Layers.collection[$defIndex].id; 
            }
            var selectedLayer:CustomSprite = parent.getChildByName(layerId) as CustomSprite;
            // some Tracking layers do not have color
            if (selectedLayer.color !== null) {
                var ct:ColorTransform = selectedLayer.transform.colorTransform;
                ct.color = Utils.parseColor(layerColor);
                selectedLayer.transform.colorTransform = ct;
                // update CustomSprite (parent layer)
                selectedLayer.color = layerColor;
            }
            
            if (e) { rememberSettings(); }
        };
        
        // use MouseEvent only when checking the CheckBox instance!
        private function rememberSettings(e:MouseEvent = null):void 
        {
            var remember:CheckBox = $panel.getChildByName(ID.OUT.REMEMBER) as CheckBox;
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
                $savedSettings.data.loadTrail = getCheckBoxState(ID.OUT.LOADTRAIL);
                $savedSettings.data.showControlPanel = $panel.visible;
                $savedSettings.data.remember = true;
                // store saved layers
                $savedSettings.data.layers = layers;
                $savedSettings.flush();
                //ExternalInterface.call("console.log", "saved data:" + $savedSettings.size + " bytes");
            } else if ($savedSettings.size > 0) {
                $savedSettings.clear();
                //ExternalInterface.call("console.log", "data deleted!");
            }
        };
        
        private function getCheckBoxState(cid:String):Boolean 
        {
            var cb:CheckBox = $panel.getChildByName(cid) as CheckBox;
            if (cb === null) { return false; }
            
            return cb.selected;
        };
        
        private function setCheckBoxState(cid:String, value:Boolean):void 
        {
            var cb:CheckBox = $panel.getChildByName(cid) as CheckBox;
            if (cb === null) { return; }
            
            cb.selected = value;
        };
        
    } // end class
}