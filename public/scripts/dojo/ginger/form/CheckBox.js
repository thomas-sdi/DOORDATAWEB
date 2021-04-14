dojo.provide("ginger.form.CheckBox");
dojo.require("dijit.form.CheckBox");
dojo.require("ginger.form.FormWidget");

dojo.declare("ginger.form.CheckBox", [dijit.form.CheckBox, ginger.form.FormWidget], {
	templateString: dojo.cache("ginger.form", "templates/CheckBox.html")
});