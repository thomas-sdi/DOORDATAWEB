dojo.provide("ginger.form.NumberTextBox");

dojo.declare("ginger.form.NumberTextBox", [dijit.form.NumberTextBox, ginger.form.FormWidget], {
	oldValue: null,
	
	parse: function(/*String*/ value, /*dojo.date.locale.__FormatOptions*/ constraints){
		if (!value.match(/^-?\d*\.?\d*$/)) {
			value = this.oldValue;
		} else {
			this.oldValue = value;
		}
		this.textbox.value = value;
		return parseFloat(value);
	}
});