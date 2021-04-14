dojo.provide("ginger.form.SubmitButton");
dojo.require("ginger.Widget");

dojo.declare("ginger.form.SubmitButton", [ginger.Widget, dijit._Templated, dijit._Contained], {
	text: "Submit",
	disabled: false,
	templatePath: dojo.moduleUrl("ginger.form", "templates/SubmitButton.html"),
	
	startup: function() {
		this.getParent().submitButton = this;
		this.inherited(arguments);
	},
	
	onClick: function(evt) {
		if (this.disabled) return;
		return this.getParent().submit();
	},
	
	setText: function(text) {
		this.text = text;
		dojo.byId(this.id+'_input').value = text;
	},
	
	setDisabled: function(disabled) {
		this.disabled = disabled;
		disabled ? dojo.addClass(this.domNode, 'disabledButton') : dojo.removeClass(this.domNode, 'disabledButton');
	}
});