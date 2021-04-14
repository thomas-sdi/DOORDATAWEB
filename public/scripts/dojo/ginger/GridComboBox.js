dojo.provide("ginger.GridComboBox");
dojo.require("dojox.grid.cells._Widget");	

dojo.declare("ginger.GridComboBox", [dojox.grid.cells._Widget], {
		widgetClass: "dijit.form.FilteringSelect",
		getWidgetProps: function(inDatum){
			// construct fetch url and create a new data store
			var urlString = this.grid.store.getBaseUrl() + 
					'/dropdown?_model=' + this.grid.getId() + '&_column=' + this.field;
			
			// if the grid has parent, add filter by parent
			if (this.grid.parent) {
				urlString += '&_parent=' + this.grid.parent.getFirstSelectedItem()._ident;
			}
		    var store = new dojox.data.QueryReadStore({url: urlString});
		    
		    var newValue = inDatum.substr(0, inDatum.indexOf('#'));
			return dojo.mixin({}, this.widgetProps||{}, {
				value: newValue,
				store: store
			});
		},
		
		getValue: function(){
			//console.log('getting value: ' + this.widget.attr('value') + ', disp: ' + this.widget.attr('displayedValue'));
			
			// we should find our item in the store by its value
			for (var ident in this.widget.store._itemsByIdentity) {
				if (this.widget.store._itemsByIdentity[ident].name == this.widget.attr('displayedValue')) {
					this.widget.attr('value', ident);
					break;
				}
			}
            return this.widget.attr('value') + '#' + this.widget.attr('displayedValue');
		},
		
		setValue: function(inRowIndex, inValue) {
			if(this.widget&&this.widget.setValue){
				var newValue = inValue != null ? inValue.substr(0, inValue.indexOf('#')) : null;
				this.widget.attr('value', newValue);
			}else{
				this.inherited(arguments);
			}
		}

	});
	ginger.GridComboBox.markupFactory = function(node, cell){
		dojox.grid.cells._Widget.markupFactory(node, cell);
	};