dojo.provide("custom.ValidationTextBox");
dojo.require("dijit.form.ValidationTextBox");

dojo.declare("custom.ValidationTextBox",dijit.form.ValidationTextBox, {
	
	oldValue: null,
	
	startup: function() {
		this.oldValue = this.attr('value');
	},
	
	onBlur: function() {
		if ((this.oldValue != this.attr('value')) && !isEmpty(this.oldValue) ) {
			var _this = this;
			
			this.errorTooltip = new dijit._MasterTooltip();
			this.errorTooltip.containerNode.className += ' dijitWarning';
			this.errorTooltip.show('Caution! Editing this field effects all previous and subsequent inspection forms.', this.domNode, 'above');
			
			this.oldValue = this.attr('value');
			
			setTimeout(function() { _this.errorTooltip.hide(_this.domNode);}, 3000);
			
			/*
				var dlg = new ginger.ActionDialog({
				message: 'Caution! Editing this field effects all previous and subsequent inspection forms. Save changes?',
				submitTitle: 'Yes',
				cancelTitle: 'No',
				onSubmit: function(evt) {
					_this.oldValue = _this.attr('value');
					dlg.hide();
				},
				onCancel: function(evt){
					_this.attr('value', _this.oldValue);
					dlg.hide();
	            }
			});*/
		}
	}
	
});