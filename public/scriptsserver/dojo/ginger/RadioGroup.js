dojo.provide("ginger.RadioGroup");
dojo.require("dijit.form.RadioButton");

dojo.declare("ginger.RadioGroup", [dijit._Widget, dijit._Templated], {
	templatePath:  dojo.moduleUrl("ginger", "templates/RadioGroup.html"),
	isContainer: true,
	attributeMap: {value: ""},
	
	startup: function() {
		this.inherited(arguments);
		var _this = this;
		dojo.forEach(this.getDescendants(), function(widget){
			dojo.connect(widget, 'onChange', dojo.hitch(_this, _this.onChange));
		});
	},
	
	// User override
	onChange: function() {
	
	},
	
	_getValueAttr: function(/*String*/ value){
		var returnValue = null; var otherValue = null; var otherSelected = false;
		dojo.forEach(this.getDescendants(), function(widget){
			if (widget.declaredClass == "dijit.form.ValidationTextBox" || widget.declaredClass == "ginger.form.TextBox"){
				otherValue = widget.attr('value');
				
			}
			else if (widget.attr('value') !== false) {
				// find a label for this radio
				var label = dojo.byId('label_' + widget.id);
				if (label == null) otherSelected = true;
				else returnValue = label.innerHTML;
			}
		});
		if (otherSelected) return otherValue;
		else return returnValue;
	},
	
	// Return id of checked radio
	_getSelectedId: function() {
		var returnValue = null; var otherValue = null;
		dojo.forEach(this.getDescendants(), function(widget){
			if (widget.declaredClass == "dijit.form.ValidationTextBox" || widget.declaredClass == "ginger.form.TextBox"){
				otherValue = widget.attr('value');
			}
			else if (widget.attr('value') !== false) {
				// return its id
				returnValue = widget.id;
			}
		});
		if (otherValue) return otherValue;
		else return returnValue;
	},
	
	_setChecked: function(/*String*/ id){
		var widget = dijit.byId(id);
		if (widget) { //if this is checkbox or radio
			widget.setAttribute('checked', true);
			//console.log('Widget ' + widget + ' was checked: ' + widget.checked);
		} else { //id is a value for textbox
			dojo.forEach(this.getDescendants(), function(widget){
				if (widget.declaredClass == "dijit.form.ValidationTextBox" || widget.declaredClass == "dijit.form.TextBox"){
					widget.setAttribute('value', id);
				} else if (widget.baseClass == 'dijitCheckBox' || widget.baseClass == 'dijitRadioButton')
					widget.setAttribute('value', false);
					widget.setAttribute('checked', false);
					//console.log('Widget ' + widget + ' was checked: ' + widget.checked);
			})
		}
	},
	
	_setDisabled: function(/*Boolean*/ isDisabled){
		if (isDisabled === null) isDisabled = false;
		dojo.forEach(this.getDescendants(), function(widget){
			widget.setAttribute('disabled', isDisabled);
			//console.log('Widget ' + widget + ' set disabled to ' + widget.disabled);
		})
	}
});