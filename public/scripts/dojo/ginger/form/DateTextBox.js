dojo.provide("ginger.form.DateTextBox");
dojo.require("dijit.form.DateTextBox");

dojo.declare("ginger.form.DateTextBox", [ginger.form.FormWidget, dijit.form.DateTextBox], {
	
	onClick: function(evt) {
		// summary:
		//		open the popup
		this._open();
		this.inherited(arguments);
	}
});