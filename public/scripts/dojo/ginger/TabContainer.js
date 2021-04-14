dojo.provide("ginger.TabContainer");
dojo.require("dijit.layout.ContentPane");

dojo.declare(
		"ginger.TabContainer",
		dijit.layout.TabContainer, {
	selectChild: function(/*Widget*/ page){
		this.inherited(arguments);
		
		// we need this hack in order to redraw tabcontainer
		// otherwise it will show gray rect if window size
		// was changed before
		dojo.forEach(page.getDescendants(), function(widget){
			if(widget.resize) widget.resize();
	       });
	}
});
	    
