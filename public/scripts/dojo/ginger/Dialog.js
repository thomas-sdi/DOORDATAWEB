dojo.provide("ginger.Dialog");
dojo.require("dijit.Dialog");

dojo.declare("ginger.Dialog", dijit.Dialog, {
	//the dialog is not draggable if on the mobile device
	draggable: null,
	
	constructor: function(args){
		this.draggable = args.hasOwnProperty('draggable') ? args.draggable : (typeof onMobile === 'undefined' ? true : (onMobile == true ? false : true));
	},
	
	hide: function() {
		if (dijit._masterTT) dijit.hideTooltip(dijit._masterTT.aroundNode);
		this.inherited(arguments);
		this.onHide();
	},
	
	// user overriden.
	onHide: function() {
		
	},
	
	_size: function() {
	    // parent function can lessen the height of the dialog if it doesn't fit in the viewport
	},
	
	_position: function(){
		/*
       	var node = this.domNode;
       	dojo.style(node,{
       		overflowX: "hidden",
       	    overflowY: "auto"
       	});*/
       	
       	console.log('position ginger dialog width: ' + window.innerWidth);
		
		if (!(typeof onMobile === 'undefined') && onMobile == true) return;
		
        if(!dojo.hasClass(dojo.body(),"dojoMove")){
        	
        	//TODO: figure out a better solution
        	
        	/*
        	var mD = false;
        	switch (navigator.platform){
        		case 'iPad': 		mD = true; break;
        		case 'iPhone':  	mD = true; break;
        		case 'iPod':    	mD = true; break;
        		case 'Android': 	mD = true; break;
        		case 'BlackBerry':  mD = true; break;
        		case 'Pocket PC': 	mD = true; break;
        		default: 			mD = false; break;
        	}
        	
        	*/
        			
            var node = this.domNode;
            
            /*
            var viewport = mD == true
                    ? {w: window.innerWidth, h: window.innerHeight, l: pageXOffset, t: pageYOffset}
                    : dojo.window.getBox(),
                p = this._relativePosition,
                bb = p ? null : dojo._getBorderBox(node),
                l = Math.floor(viewport.l + (p ? p.x : Math.max(viewport.w - bb.w, 0) / 2)),
                t = Math.floor(viewport.t + (p ? p.y : Math.max(viewport.h - bb.h, 0) / 2));
                
                if (t > 10) t = 10;
                
                if(viewport.w < 400) l = 0;
                
            
                    
       	    dojo.style(node,{
       	        left: l + "px",
       	        top: t + "px"
       	    });
       	    
       	    */
       	    
       	    dojo.style(node,{
       	    	left: "0px",
       	    	top: "0px",
       	    	width: "100%"
       	    });
       	}
    }
});