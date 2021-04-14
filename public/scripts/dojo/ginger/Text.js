dojo.provide("ginger.Text");

dojo.declare("ginger.Text", [dijit._Widget, dijit._Templated], {
	src: "",
	templatePath:  dojo.moduleUrl("ginger", "templates/Text.html"),
	isContainer: true,
	dependsOn: "",
	separator: "",
	attributeMap: dojo.delegate(dijit._Widget.prototype.attributeMap, {
		value: {node: "domNode", type: "innerHTML" }}),
		
	startup: function() {
		this.inherited(arguments);
		
		if (this.dependsOn != '') {
			this.dependsOn = dojo.fromJson(this.dependsOn);
			if (this.separator == undefined || this.separator == null)
				this.separator = "";
				
			this.recalculate(true);
		}
	},
	
	recalculate: function(connect) {
		val = ""; var _this = this;
		dojo.forEach(this.dependsOn, function(widgetId){
			// get current value
			widget = dijit.byId(widgetId);
			if (!widget) {
				console.error("Recalculate dependable value: widget " + widgetId + " is not found");
				return;
			}
			var isDropDown = widget.declaredClass == 'ginger.form.DropDown';
			
			var v = isDropDown ? widget.attr('displayedValue') : widget.attr('value');
			if (v != null && v != 'null'){
				if (val == "") val = v;
				else val += _this.separator + v;
			}
			
			// connect to any further changes of this widget
			if (connect) {
				if (isDropDown) {
					dojo.connect(widget.textbox, 'onblur', dojo.hitch(_this, 'recalculate', false));
				}
				else {
					dojo.connect(widget.focusNode, 'onchange', dojo.hitch(_this, 'recalculate', false));
				}
			}
		});
		this.attr('value', val);
	}
});