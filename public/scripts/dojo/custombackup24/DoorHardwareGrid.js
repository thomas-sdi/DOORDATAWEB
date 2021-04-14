dojo.provide("custom.DoorHardwareGrid");
dojo.require("ginger.Grid");

dojo.declare("custom.DoorHardwareGrid", ginger.Grid, {
	postCreate: function() {
		this.inherited(arguments);
		
		// filter by door id
		var query = this.store.getQuery();
		query._parent = this.getParentRowId();
		this.store.setQuery(query);
	},
	
	checkAll: function() {
		for (var ident in this._by_idty) {
			var storeItem = this._by_idty[ident].item;
			for (var i = 1; i < 6; i++) {
				if (this.store.getValue(storeItem, 'column_'+i, '') != '') {
					this.store.setValue(storeItem, this.selector, '1');
					break;
				}
			}
		}
		this.update();
	},
	
	onApplyCellEdit: function(inValue, inRowIndex, inFieldIndex) {
		this.updateRow(inRowIndex);
	}

});

custom.DoorHardwareGrid.markupFactory = function(props, node, ctor, cellFunc) {
	return ginger.Grid.markupFactory(props, node, ctor, cellFunc);
};