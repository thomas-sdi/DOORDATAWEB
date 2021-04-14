dojo.provide("ginger.form.TimeTextBox");
dojo.require("dijit.form.TimeTextBox");

dojo.declare("ginger.form.TimeTextBox", [dijit.form.TimeTextBox, ginger.form.FormWidget],  {
	popupClass	: "",
	openOnClick: false,
	maxLength	: 5,
	oldValue	: null,
	amPm: false, // use AM/PM or 24 format
	
	//_onFocus: function(/*Event*/ evt){
	//	if (this.emptyBox)
	//		this.emptyBox.style.display = 'none';
	//},

	parse: function(/*String*/ value, /*dojo.date.locale.__FormatOptions*/ constraints){
		if (value && value.length == 2) {
			if (value.indexOf(':') <0) {
				if ((this.amPm && !(value.match(/^[0-1]$/) || 
					value.match(/^[0-1]?[0-2]$/) ||
					value.match(/^0?[0-9]$/)) ||
				(!this.amPm && !(value.match(/^[0-2]$/) || 
					value.match(/^[0-1][0-9]$/) ||
					value.match(/^2[0-3]$/))))) {
					value = value[0] + ':' + value[1];
				}
			} 
		}
		else if (value && value.length == 3) {
			if (value.indexOf(':') <0)
				value = value.substr(0,2) + ':' + value.substr(2);
			else if (value[2] == ':')
				value = value.substr(0,2);	
		}
		if (!value || 
			// for am pm time
			this.amPm && (
				value.match(/^[0]?[0-9]$/) || 
				value.match(/^[0-1]?[0-2]$/) ||
				value.match(/^[0]?[0-9]:$/) || 
				value.match(/^[0-1]?[0-2]:$/) ||
				value.match(/^[0-1]?[0-2]:[0-5]$/) ||
				value.match(/^[0]?[0-9]:[0-5]$/) || 
				value.match(/^[0-1]?[0-2]:[0-5][0-9]$/)) ||	
				value.match(/^[0]?[0-9]:[0-5][0-9]$/) || 
			// for 24 hour time
			!this.amPm && (
				value.match(/^[0-2]$/) || 
				value.match(/^[0-1]?[0-9]$/) ||
				value.match(/^2?[0-3]$/) ||
				value.match(/^[0-1]?[0-9]:$/) ||
				value.match(/^2?[0-3]:$/) ||
				value.match(/^[0-1]?[0-9]:[0-5]$/) ||
				value.match(/^2?[0-3]:[0-5]$/) ||
				value.match(/^[0-1]?[0-9]:[0-5][0-9]$/) ||
				value.match(/^2?[0-3]:[0-5][0-9]$/)
				)) {
			this.oldValue = value;
		} else {
			value = this.oldValue;
		}
		this.textbox.value = value;
		
		return dojo.date.locale.parse(value, constraints) || (this._isEmpty(value) ? null : undefined);	 // Date
	},
	
	_onKey: function(evt){
	//this.inherited(arguments);
	}
});