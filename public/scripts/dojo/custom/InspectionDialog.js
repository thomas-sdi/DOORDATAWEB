dojo.provide("custom.InspectionDialog");
dojo.require("ginger.Dialog");

dojo.declare("custom.InspectionDialog", ginger.DetailedDialog, {
	submit: function() {
		if (this.searchMode) { this.inherited(arguments); return; };
		var select = dijit.byId('inspection_BUILDING');
		if (!select) select = dijit.byId('company_inspections_BUILDING');
		if (!select) select = dijit.byId('building_inspection_BUILDING');
		var oldValue = dojo.byId('buildingOldName').value;
		//alert(gridDialog.selectedItem._ident);
		var newValue = select.attr('value');
		var _this = this;
		if ((oldValue != newValue) && (gridDialogInspection.selectedItem._ident > -1)) {
			// create a confirmation dialog
			var dlg = new ginger.ActionDialog({
				message: 'The building name has been updated. Do you want to save these changes?',
				submitTitle: 'Yes',
				cancelTitle: 'No',
				onSubmit: function(evt) {
					_this.executeEdit();
					dlg.hide();
				},
				onCancel: function(evt){
					select.attr('value', oldValue);
					_this.executeEdit();
	            }
			});
			
		}
		else this.inherited(arguments);
		//this.inherited(arguments);
		
	}
});