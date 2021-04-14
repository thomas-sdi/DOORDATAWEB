dojo.provide("ginger.Tree");

dojo.declare("ginger.Tree", [dijit.Tree, ginger.Component], {
	
	selectedNode: null,
	
	onClick: function(item) {
		selectedNode = item;
    	dojo.forEach(this.getChildren(), function(grid) {
    		grid.lastSelected = [];
    		grid.filter({_parent: item.id}, false);
    	});
	},
	
	// returns all selected items
    getActiveItems: function() {
    	return [selectedNode];
    },

	getAllChildrenIds: function(item) {
		var children = new Array();
		if (!item.children) {
			return null;
		}
		
		getAllChildrenIds = this.getAllChildrenIds;
		dojo.forEach(item.children, function(child) {
			children[children.length] = child.id + '';
			children.concat(getAllChildrenIds(child));
		});
		
		return children;
	},
	
	refresh: function() {
		// refresh the store
		this.model.store.close();
		
		// recreate tree
		this._itemNodeMap = {};
		this.rootNode.destroyRecursive();
		this.model.root.children = null; // relevant for ForestTree only
		this._load();
    }
});