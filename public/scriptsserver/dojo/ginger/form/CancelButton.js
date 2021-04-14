dojo.provide("ginger.form.CancelButton");
dojo.require("ginger.Widget");

dojo.declare("ginger.form.CancelButton", [ginger.Widget, dijit._Templated, dijit._Contained], {
	text: "Cancel",
	
	templatePath: dojo.moduleUrl("ginger.form", "templates/CancelButton.html"),
	
	form: null,
	
	startup: function() {
		this.form = this.getParent();
		if (this.form) this.form.cancelButton = this;
		
		this.inherited(arguments);
	},
	
	onClick: function(evt) {
		return this.form.cancel();
	}
	
});