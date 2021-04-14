dojo.provide("ginger.form.Image");
dojo.require("ginger.form.FormWidget");

dojo.declare("ginger.form.Image", [ginger.Widget, dijit._Templated, dijit._Contained], {
	src: "",
	style: "",
	name: "",
	width: "0px",
	height: "0px",
	id: "",
	value: "",
	previewSrc: "",
	showImage: true,
	templateString: dojo.cache("ginger.form", "templates/Image.html"),
	
	postMixinProperties: function() {
		this.inherited(arguments);
		
		if (!this.value || !this.showImage) {
			this.imageNode.style.display = none;
		}
	}
});