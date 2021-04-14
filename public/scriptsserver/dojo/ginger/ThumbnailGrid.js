dojo.provide("ginger.ThumbnailGrid");
dojo.require("ginger.ThumbnailPicker");

dojo.declare("ginger.ThumbnailGrid", [ginger.Widget, dijit._Templated, dijit._Container], {
	
	templatePath:  dojo.moduleUrl("ginger", "templates/ThumbnailGrid.html"),
	
	caption: "",
	controller: "",
	idColumnIndex: "",
	id: "",
	jsid: "",
	maxRows: "",
	detailedDialog: "",
	detailedView: "",
	
	isContainer: true
	
});