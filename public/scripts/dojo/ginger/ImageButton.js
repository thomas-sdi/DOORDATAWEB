dojo.provide("ginger.ImageButton");
dojo.require("dijit.form._FormWidget");
dojo.require("dijit._Container");

dojo.declare("ginger.ImageButton", [dijit._Widget, dijit._Templated], {
	image: "",
	title: "",
	templatePath:  dojo.moduleUrl("ginger", "templates/ImageButton.html"),
	isContainer: true
});