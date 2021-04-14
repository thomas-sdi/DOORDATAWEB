dojo.provide("ginger.Component");

dojo.declare("ginger.Component", null, {
    _children:      [],
    parent:		null,
    idColumnIndex: 'id',
    
    // adds a child grid to the list
    addChild: function(child) {
		for (var i in this._children) {
			if (this._children[i].getId() == child.getId()) return;
		}
		child.parent = this;
        this._children.push(child);
    },
    
    constructor: function() {
    	this._children = [];
    },
    
    getId: function() {
    	return this.id.substr(4); // remove cmp_ prefix
    },
    
    getChildren: function() {
    	return this._children;
    },
    
    // returns currently selected items
    getActiveItems: function() {
    	// user should overload it
    },
    
    getSelectedItemId: function() {
    	//
    }
});
