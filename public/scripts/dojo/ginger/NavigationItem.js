dojo.provide("ginger.NavigationItem");

dojo.declare("ginger.NavigationItem", [ginger.Widget, dijit._Templated, dijit._Contained], {
	
	href: "",
	templatePath: dojo.moduleUrl("ginger", "templates/NavigationItem.html"),
	onSelect: function(evt) {
		return this.getParent().selectItem(this);
	}
	
});
	    
