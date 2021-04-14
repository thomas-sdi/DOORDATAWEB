dojo.provide("ginger.form.FormWidget");
dojo.require("dijit.form._FormWidget");

dojo.declare("ginger.form.FormWidget", [dijit._Contained, dijit.form._FormWidget], {
	form: null,
	messageBox: null,
	emptyText: '',	// text to show when input is empty
	emptyBox: null,	// box to show empty text in
	invalidMessage: '',
	
	startup: function() {
		this.inherited(arguments);	
		if (!this.form)
			this.form = this.getParent();
		
		var messageBox = '<div class="messageBox"></div>';
		//this.messageBox = dojo.place(messageBox, this.focusNode.parentNode, 'after');
		
		/*var emptyBox= '<div class="emptyBox">'+this.emptyText+'</div>';
		this.emptyBox = dojo.place(emptyBox, this.domNode, 'first');
		
		this.showEmptyText();*/
	},
			
	validator: function(value, constraints) {
		var valid = ''; //this.invalidMessage = '';
		
		// ignore disabled attributes
		if (this.attr('disabled')) return valid;
		
		// if there are any errors with this widget, store invalid message
//		if (this.form && this.form.problems && this.form.problems[this.name]) {
//			console.log('Validating');
//			this.invalidMessage = this.form.problems[this.name];
//			valid = 'Error';
//			return valid;
//		}
		if (this.invalidMessage && this.invalidMessage != '') {
			return 'Error';
		}
		
		// if attribute is required and the value is empty
		/*if(this.attr('required') && (this._isEmpty(value))) {
			valid = 'Error';
		}*/
	
		// restore default invalidMessage if needed
		//if (this.invalidMessage == '' && this.messages)
		//	this.invalidMessage = this.messages.invalidMessage;
	
		// can be overriden by custom widgets
		valid = this.customValidator(value, constraints);
		
		return valid;
	},
			
	validate: function(isFocused) {
		// support for Date widgets
		if(this.valueNode && this.toString) this.valueNode.value = this.toString();
		
		this.state = this.textbox ? this.validator(this.textbox.value) : this.validator(this.value);
		if (this._setStateClass) this._setStateClass();
		if(this.focusNode) dijit.setWaiState(this.focusNode, "invalid", this.state != '');
		if (this.state != '') {
			this.displayMessage(this.getErrorMessage());
		} else
			this.displayMessage('');
		return this.state == '';
	},
	
	getErrorMessage: function() {
		return this.invalidMessage;
	},
	
	customValidator: function(value, constraints) {
		return '';
	},
	
	onChange: function() {
		//if (this.form) this.form.problems[this.name] = null;
		//this.validate(false);
		
		//this.showEmptyText();
	},
	
	displayMessage: function(message) {
		if (this.messageBox) {
			this.messageBox.innerHTML = message;
			var self = this;
			dojo.style(this.messageBox, 'top', dojo.style(this.domNode, 'height'));
		} else {
			this.inherited(arguments);
		}
	},
	
	isValid: function() {
		return true;
	}
	
	/*onFocus: function() {
		this.emptyBox.style.display = 'none';
	},
	
	onBlur: function() {
		this.showEmptyText();
	},
	
	showEmptyText: function() {
		var value = this.Value;
		if (this.textbox)
			value = this.textbox.value;
		if (value == '' || !value) {
			dojo.style(this.emptyBox, 'display', 'block');
		}
		else {
			dojo.style(this.emptyBox, 'display', 'none');
		}
	}*/
});
	    
