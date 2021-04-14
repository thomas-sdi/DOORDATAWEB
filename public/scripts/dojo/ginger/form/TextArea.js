dojo.provide("ginger.form.TextArea");
dojo.require("dijit.form.SimpleTextarea");

dojo.declare("ginger.form.TextArea", [dijit.form.SimpleTextarea, ginger.form.FormWidget], {
	templateString: dojo.cache("ginger.form", "templates/TextArea.html")
});