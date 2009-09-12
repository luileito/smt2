/**
 *  Base layers
 *  @version    2.0 - 12 Feb 2009    
 *  @autor      Luis Leiva   
 */
package com.speedzinemedia.smt {

    import flash.display.Shape;
    import flash.display.Sprite;
        
    public class Layers
    {
        /**
         *  bgLay: background layer (must be ALWAYS at index 0) 
         *  mPath: mouse path
         *  stops: mouse pauses
         *  dDrop: drag&drop, selections, etc.
         *  click: mouse clicks                           
         *  regPt: registration points
         *  clust: k-means clustering
         *  centr: mouse path centroid                  
         *  dDist: direction arrows and distances
         *  eeCur: mouse pointers, and entry/exit cursors                                                               
         */
        public static const collection:Array = [
            { id: "bgLay", label: "background overlay",    color: "555555", visible: true  },
            { id: "mPath", label: "mouse path",            color: "00CCCC", visible: true  },
            { id: "stops", label: "hesitations",           color: "FFFF99", visible: false },
            { id: "dDrop", label: "drag&drop/selections",  color: "AABBCC", visible: true  },
            { id: "click", label: "clicks",                color: "FF0000", visible: true  },
            { id: "regPt", label: "registration points",   color: "FF00FF", visible: false },
            { id: "dDist", label: "direction & distances", color: null,     visible: false },
            { id: "clust", label: "clustering",            color: "0000FF", visible: true  },
            { id: "centr", label: "path centroid",         color: "DDDDDD", visible: true  },
            { id: "eeCur", label: "mouse pointers",        color: null,     visible: true  }
        ];
        
        // precalculate the length of layers collection 
        public static const collectionLength:int = collection.length;
        
        public static function select(strId:String):Object
        {
            return collection[ getIndex(strId) ];
        };
        
        public static function getIndex(strId:String):int
        {
            var i:int;
            for (i = 0; i < collectionLength; ++i) {
                if (collection[i].id === strId) { break; }
            }
            return i;
        };

    } // end class
}