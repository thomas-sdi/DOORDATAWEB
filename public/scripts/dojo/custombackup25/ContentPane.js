dojo.provide("custom.ContentPane");
dojo.require("dojox.layout.ContentPane");

dojo.declare(
		"custom.ContentPane",
		dojox.layout.ContentPane, {
			
	onFocus: function() {
		//checkAuthStatus();
		this.inherited(arguments);
	}
	
});
	    
