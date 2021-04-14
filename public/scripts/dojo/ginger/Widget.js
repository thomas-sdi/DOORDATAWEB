dojo.provide("ginger.Widget");
dojo.require("dijit.layout.ContentPane");

dojo.declare(
		"ginger.Widget",
		dijit._Widget, {
	prefix: "",
	
	buildRendering: function(){
		// Overrides Widget.buildRendering().
		// Since we have no template we need to set this.containerNode ourselves.
		// For subclasses of ContentPane do have a template, does nothing.
		this.inherited(arguments);
		if(!this.containerNode){
			// make getDescendants() work
			this.containerNode = this.domNode;
		}
	}
});
	    
