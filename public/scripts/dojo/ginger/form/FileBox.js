dojo.provide("ginger.form.FileBox");
dojo.require("ginger.form.TextBox");

dojo.declare("ginger.form.FileBox", ginger.form.TextBox, {
	templateString: dojo.cache("ginger.form", "templates/FileBox.html"),
	
	startup: function() {
		this.inherited(arguments);	
		
		dojo.connect(this.fileNode, 'onchange', this, function(evt){
			this.textbox.value = this.fileNode.value; 
			this.onChange();
		});
		
	},
	
	_onInput: function(e) {
		this.textbox.value = this.fileNode.value;
	}
});