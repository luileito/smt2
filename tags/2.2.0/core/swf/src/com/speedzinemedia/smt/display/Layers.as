/**
 *  Base layers.
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt.display {

    import flash.display.Shape;
    import flash.display.Sprite;
    import flash.net.SharedObject;
        
    import com.speedzinemedia.smt.draw.DrawUtils;
        
    public class Layers
    {
        /**
         *  Layers identifiers:
         *      background layer
         *      mouse path
         *      mouse clicks
         *      mouse pauses
         *      drag&drop, selections, etc.
         *      registration points
         *      direction arrows and distances
         *      k-means clustering
         *      mouse path centroid
         *      mouse pointers, and entry/exit cursors
         */
        public static const id:Object = {
            BACKGROUND:     "bgLay",
            MASK:           "masks",
            PATH:           "mPath",
            REGISTRATION:   "regPt",
            STOP:           "stops",
            DRAG:           "dDrop",
            CLICK:          "click",
            DISTANCE:       "dDist",
            CLUSTER:        "clust",
            CENTROID:       "centr",
            CURSOR:         "eeCur"
        };
        
        /** Layers definition. */
        public static const collection:Array = [
            { id: id.BACKGROUND,    label: "Background overlay",    color: 0x555555, visible: true  },  // must be always at index 0
            { id: id.PATH,          label: "Cursor trail",          color: 0X99CCEE, visible: true  },
            { id: id.REGISTRATION,  label: "Coordinates",           color: 0xFFFFFF, visible: false },
            { id: id.STOP,          label: "Pauses",                color: 0xFFFF99, visible: false },
            { id: id.DRAG,          label: "Drag&drop/selections",  color: 0xAABBCC, visible: true  },
            { id: id.CLICK,         label: "Clicks",                color: 0x990000, visible: true  },
            { id: id.DISTANCE,      label: "Direction arrows",      color: null,     visible: false },  // no color because of images
            { id: id.CLUSTER,       label: "Active areas",          color: 0x0000FF, visible: true  },
            { id: id.CENTROID,      label: "Centroids",             color: 0xFF99FF, visible: true  },
            { id: id.CURSOR,        label: "Cursors",               color: null,     visible: true  },  // no color because of images
            { id: id.MASK,          label: "Interacted areas",      color: null,     visible: false }   // special blend mode
        ];
        
        /** Precalculates the length of layers collection. */
        public static const collectionLength:int = collection.length;
        
        /** Gets saved visualization settings. */
        public static const $savedSettings:SharedObject = SharedObject.getLocal("smtControlPanel", "/");
        
        /** @private */
        public static function getIndex(strId:String):int
        {
            var i:int;
            for (i = 0; i < collectionLength; ++i) {
                if (collection[i].id === strId) { break; }
            }
            return i;
        };
        
        /** Selects a layer by its name. */
        public static function getLayer(strId:String):Object
        {
            return collection[ getIndex(strId) ];
        };
        
        /** Gets layer color. */
        public static function getColor(strId:String):uint 
        {
            var layerColor:uint;
            if ($savedSettings.size > 0) {
                var layer:Object = $savedSettings.data.layers[ getIndex(strId) ];
                layerColor = layer.color;
            } else {
                layerColor = getLayer(strId).color;
            }
            
            return layerColor;
        };

    } // end class
}
