dojo.provide("ginger.InlineDropDown");

dojo.declare("ginger.InlineDropDown", ginger.InlineText, {

	startup: function() {
		this.inherited(arguments);
		
		var displayValue = formatReference(this.value) || this.noValueIndicator;
		this.displayNode.innerHTML = this.displayNode.value = displayValue;
	},
	
	edit: function(){
		var value = this.attr('value');
		this.value = formatReference(value);
		this.inherited(arguments);
		var widget = this.editWidget;
		
		var id = value.substr(0, value.indexOf('#'));
		//widget.editWidget.attr('value', id);
		widget.editWidget.value = id;
		widget.editWidget.textbox.value = this.value;
		
		this.value = value;
		
		this.editWidget.getValue = function() {
			var ew = widget.editWidget;
			/*var _this = this;
			var fetch = {
				query: {name: ew.attr('displayedValue')},
				onComplete: function(result, dataObject){
					if (result.length) {
						//console.log('Item ' + result[0]);
						ew.attr('item', result[0]);
					} else {
						//console.log('Item null');
						ew.attr('item', null);
					}
				}
			};
			ew.store.fetch(fetch);
			if (ew.attr('item') == null) {
				console.log('Result #');
				return '#';
			} else {
				console.log('Result ' + ew.store.getValue(ew.attr("item"), 'ID') + '#' + ew.store.getValue(ew.attr("item"), 'name'));
				return ew.store.getValue(ew.attr("item"), 'ID') + '#' + ew.store.getValue(ew.attr("item"), 'name');
			}*/
			if (ew.attr("item")) {
				console.log('Result item ' + ew.store.getValue(ew.attr("item"), 'ID') + '#' + ew.store.getValue(ew.attr("item"), 'name'));
				return ew.store.getValue(ew.attr("item"), 'ID') + '#' + ew.store.getValue(ew.attr("item"), 'name');
			} else {
				console.log('Result ' + ew.valueNode.value + '#' + ew.displayedValue);
				return '#' + ew.displayedValue;
			}
		}
	},
	
	_setValueAttr: function(/*String*/ val){
		//console.log('Val ' + val);
		var value = formatReference(val);
		this.value = val;
		var displayValue = formatReference(val) || this.noValueIndicator;
	
		// in the original version only innerHTML was set which doesn't allow using normal input boxes as edit 
		this.displayNode.innerHTML = this.displayNode.value = displayValue;
	}
});