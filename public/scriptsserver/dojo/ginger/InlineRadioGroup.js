dojo.provide("ginger.InlineRadioGroup");
dojo.require("ginger.RadioGroup");

dojo.declare("ginger.InlineRadioGroup", [ginger.RadioGroup/*, ginger.InlineText*/], {
	templatePath:  dojo.moduleUrl("ginger", "templates/InlineRadioGroup.html"),
	
	inlineInput: true, // need this in order to confirm that this object is inline (when probed from outside)
	storeItem: null,
	field: "",
	progressImageSrc: "",
	progressNode: null,
	mainNode: null,
	tabPressed: false, 
	keyDownHandle: null,
	tabNext: null,
	tabPrev: null,
	contentPane: null,
	accordionContainer: null,
	
	constructor: function() {
		this.progressImageSrc = baseUrl + '/public/images/inprogress.gif';
	},
	
	startup: function() {
		dojo.addClass(this.domNode, "inlineControl");
		var _this = this;
		dojo.forEach(this.getDescendants(), function(widget) {
			if (widget.declaredClass == 'dijit.form.RadioButton') {
				//dojo.connect(widget, 'onChange', dojo.hitch(_this, _this.onChange));
				dojo.connect(widget, 'onChange', function() {_this.onChange(widget);});
				dojo.connect(widget, 'onKeyDown', dojo.hitch(_this, _this.onTab));
			}
		});
		this.inherited(arguments); 
	},
	
	onChange: function(radioWidget) {
		if (radioWidget) {
			// if there is more than 1 radiobutton turned on, just ignore this event
			// and wait for another one which will turn another button off
			var count = 0;
			dojo.forEach(this.getChildren(), function(widget) {
				if (widget.attr('value')) count++;
				if (count > 1) return;
			});
		}
		
		this.showProgress();
		var store = this.storeItem._S;
		//store.setValue(this.storeItem, this.field, this.attr('value'));
		store.setValue(this.storeItem, this.field, radioWidget.attr('value'));
		store.save({
			onComplete: dojo.hitch(this, dojo.hitch(this, this.hideProgress)),
			onError:    dojo.hitch(this, dojo.hitch(this, this.hideProgress))
		});
	},
	
	revert: function () {
		var oldValue = this.storeItem[this.field];
		this.attr('value', oldValue);
	},
	
	showProgress: function() {
		this.progressNode.style.visibility = 'visible';
		this.progressNode.style.display = 'block';
	},
	
	hideProgress: function() {
		this.progressNode.style.visibility = 'hidden';
		this.progressNode.style.display = 'none';
		if (this.tabPressed) {
			this.doTabbing();
		}
	},
	
	_onClick: function() {
		this.inherited(arguments);
		if (this.editor) {
			this.edit();
			this.keyDownHandle = dojo.connect(this.editWidget, 'onKeyDown', dojo.hitch(this, this.onTab));
		}
		else this.keyDownHandle = dojo.connect(this.domNode, 'onkeydown', dojo.hitch(this, this.onTab));
		
	},
	
	_onMouseOver: function(){
		this.inherited(arguments);
		dojo.addClass(this.mainNode, 'hover');
	},

	_onMouseOut: function(){
		this.inherited(arguments);
		dojo.removeClass(this.mainNode, 'hover');
	},
	
	onTab: function(evt) {
		if (evt.keyCode == dojo.keys.TAB) {
			//console.log('tab pressed');
			if (this.keyDownHandle) dojo.disconnect(this.keyDownHandle);
			if (!this.tabPressed) {
				if (dojo.hasClass(this.domNode, 'radioGroup')) {
					this.tabPressed = false;
					this.doTabbing();
				}
				else this.tabPressed = true;
			}
			dojo.stopEvent(evt);
		}
	},
	
	doTabbing: function() {
		var stop = false;
		if (this.tabNext) {
			if (this.contentPane != this.tabNext.contentPane) {
				this.tabNext.accordionContainer.selectChild(this.tabNext.contentPane);
			}
			try {
				if (this.tabNext.editor) {
					this.tabNext.edit();
					this.tabNext.keyDownHandle = dojo.connect(this.tabNext.editWidget, 'onKeyDown', dojo.hitch(this.tabNext, this.tabNext.onTab));
					stop = true;
				}
				else if (dojo.hasClass(this.tabNext.domNode, 'radioGroup')) {
					dojo.forEach(this.tabNext.getChildren(), function(widget) {
						if (widget.attr('checked') == true) widget.focus();
						this.tabNext.keyDownHandle = dojo.connect(this.tabNext.domNode, 'onKeyDown', dojo.hitch(this.tabNext, this.tabNext.onTab));
						stop = true;
					});
				}
			}
			finally {}
		}
		if (stop) return;
		return;
		var list = dojo.query('.inlineControl');
		var _this = this;
		var key = false;
		//console.log('starting list...');
		list.forEach(function (item, index, list) {
			if (stop) return; 
			var w = dijit.byNode(item);
			if (key) {
				try {
					if (w.editor) {
						w.edit();
						w.keyDownHandle = dojo.connect(w.editWidget, 'onKeyDown', dojo.hitch(w, w.onTab));
						stop = true;
					}
					else if (dojo.hasClass(w.domNode, 'radioGroup')) {
						dojo.forEach(w.getChildren(), function(widget) {
							if (widget.attr('checked') == true) widget.focus();
							w.keyDownHandle = dojo.connect(w.domNode, 'onKeyDown', dojo.hitch(w, w.onTab));
							stop = true;
						});
					}
				}
				finally {}
			}
			else if (w == _this) key = true;
		});
		
		if (key && !stop) { // the next control is not found
			
		}
		//console.log('end list');
		this.tabPressed = false;
	}
});