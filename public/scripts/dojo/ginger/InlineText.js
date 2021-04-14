dojo.provide("ginger.InlineText");

dojo.declare("ginger.InlineText", [dijit.InlineEditBox, dijit._Templated], {
	inlineInput: true, // need this in order to confirm that this object is inline (when probed from outside)
	storeItem: null,
	field: "",
	progressImageSrc: "",
	templatePath: dojo.moduleUrl("ginger", "templates/InlineText.html"),
	progressNode: null,
	mainNode: null,
	tabPressed: false, 
	keyDownHandle: null,
	tabNext: null,
	tabPrev: null,
	contentPane: null,
	accordionContainer: null,
	dialog: null,
	
	constructor: function() {
		this.progressImageSrc = baseUrl + '/public/images/inprogress.gif';
	},
	
	startup: function() {
		dojo.addClass(this.domNode, "inlineControl");
		this.inherited(arguments); 
	},
	
	postMixInProperties: function() {
		if (this.editor == 'dijit.form.DateTextBox' && this.value.length > 0) {
    		this.value = dojo.date.stamp.fromISOString(this.value);
			this.value = dojo.date.locale.format(this.value, {selector: "date"});
		}
		this.inherited(arguments);
	},
		
	onChange: function(value) {
		if (this.editor == 'dijit.form.DateTextBox') {
			value = dojo.date.locale.parse(value, {selector: "date"});
		}
		
		if ((this.editor == 'ginger.DropDown' || 
			 this.editor == 'custom.widgets.AddressDropDown') 
			 && this.value == '#') {
			return false;
		}
		
		this.showProgress();
		var store = this.storeItem._S;
		this.storeItem._S.setValue(this.storeItem, this.field, value);
		this.storeItem._S.save({
    		onComplete: dojo.hitch(this, dojo.hitch(this, this.onSaveCompleted)),
    		onError:    dojo.hitch(this, dojo.hitch(this, this.onSaveFailed))}
    	);
	},
	
	onSaveCompleted: function() {
		//console.log('save successfull');
		if (this.dialog) this.dialog.isChanged = true;
		this.hideProgress();
	},
	
	edit: function(){
		// this is needed in order for inline editor to be placed correctly
		// unfortunately we cannot do this from the beginning as otherwise the entire widget won't be rendered correctly
		this.domNode = this.displayNode;
		
		// alter css class of the entire widget
		dojo.addClass(this.mainNode, 'edit');
		
		this.inherited(arguments);
	},
	
	_showText: function(/*Boolean*/ focus) {
		// alter css class of the entire widget
		dojo.removeClass(this.mainNode, 'edit');
		this.inherited(arguments);
	},
	
	onSaveFailed: function() {
		console.log('Save failed...');
		this.hideProgress();
	},
	
	_setValueAttr: function(/*String*/ val){
		this.value = val;
		var displayValue = dojo.trim(val) || this.noValueIndicator;
		
		// in the original version only innerHTML was set which doesn't allow using normal input boxes as edit 
		this.displayNode.innerHTML = this.displayNode.value = displayValue;
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