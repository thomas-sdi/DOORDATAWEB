dojo.require("ginger.NavigationItem");
dojo.provide("ginger.NavigationContainer");

dojo.declare("ginger.NavigationContainer", [ginger.Widget, dijit._Templated, dijit._Container], {
	
	templateString: "<div dojoAttachPoint='domNode,containerNode'></div>",
	contentPane: "", // must be derived from dijit.layout.ContentPane, runtime binding
	supportBackForward: true,
	
	/*
	 * Manipulates CSS classes on items to highlight selected
	 * Then changes contentNode to whatever the item points towards
	 * This method is invoked from NavigationItem widget
	 */
	selectItem: function(selectedItem, isBackForward) {
		// apply CSS changes to highlight the selected item
		dojo.forEach(this.getItems(), function(item){
			if (item == selectedItem) {
				dojo.removeClass(item.domNode, 'unselected');
				dojo.addClass(item.domNode, 'selected');
			}
			else {
				dojo.removeClass(item.domNode, 'selected');
				dojo.addClass(item.domNode, 'unselected');
			}
		}, this);
		
		// remember how the current state can be accomplished (for back/forward browser buttons)
		if (!isBackForward && this.supportBackForward) {
			var _this = this;
	    	var state = {
				back: function() {_this.selectItem(selectedItem, true);},
				forward: function() {_this.selectItem(selectedItem, true);},
				changeUrl: true
			};
			dojo.back.addToHistory(state);
	    }
		//if (dijit._masterTT) dijit.hideTooltip(dijit._masterTT.aroundNode);
		
		// resolve content pane
		if (typeof(this.contentPane) == 'string') {
			console.log('yes, string!');
			this.contentPane = dijit.byId(this.contentPane);
			if (!this.contentPane) {
				console.error('No content pane defined for this NavigationContainer');
				return;
			}
		}
		//alert(this.contentPane.containerNode.getDescendants().length);
		//dojo.forEach(this.contentPane.getDescendants(), function(wdg) {
		//	wdg.destroyRecursive();
		//});
		//this.contentPane.destroyDescendants();
		this.contentPane.attr('href', baseUrl + selectedItem.href);
		
	},
	
	getItems: function() {
		return dijit.findWidgets(this.containerNode);
	}
	
});
	    
