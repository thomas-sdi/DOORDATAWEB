dojo.provide("ginger.GridCheckBox");

dojo.declare("ginger.GridCheckBox", [dojox.grid.cells.Bool], {
	readonly: false,

	formatEditing: function(inDatum, inRowIndex){
		if (this.readonly == 'false') this.readonly = false;
		else if (this.readonly == 'true') this.readonly = true;
		var html = '<input class="dojoxGridInput" type="checkbox"'
			  + (this.readonly ? ' disabled ' : '')
			  + (inDatum ? ' checked="checked"' : '')
			  + ' style="width: auto" />';
		console.log('html: ' + html);
		return html;
	}
});
ginger.GridCheckBox.markupFactory = function(node, cell){
	dojox.grid.cells.Bool.markupFactory(node, cell);
	cell.readonly = dojo.attr(node, "readonly");
}