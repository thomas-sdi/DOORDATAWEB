dojo.provide("ginger.GridLink");

dojo.declare("ginger.GridLink", [dojox.grid.cells._Widget], {
	link: ""
});

ginger.GridLink.markupFactory = function(node, cell){
	dojox.grid.cells._Widget.markupFactory(node, cell);
};