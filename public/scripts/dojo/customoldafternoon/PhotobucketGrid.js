dojo.provide("custom.PhotobucketGrid");
dojo.require("ginger.Grid");

dojo.declare("custom.PhotobucketGrid", ginger.Grid, {
	downloadAll: function() {
		if (!checkAuthStatus()) return;
		document.location.href = baseUrl + '/photobucket/download?_parent=' + this.getParentRowId();
		//window.open(baseUrl + '/photobucket/download?_parent=' + this.getParentRowId(), 'Download');
	}
});

custom.PhotobucketGrid.markupFactory = function(props, node, ctor, cellFunc) {
    return ginger.Grid.markupFactory(props, node, ctor, cellFunc);
};