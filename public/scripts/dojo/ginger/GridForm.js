dojo.provide("ginger.GridForm");
dojo.require("ginger.form.ValidationForm");

dojo.declare("ginger.GridForm", ginger.form.ValidationForm, {
	store: null,
	actionPath: "",
	objectId: "",
	model: "",
	storeItem: null,
	displayNode: null,
	idColumnIndex: "",
	
	startup: function() {
		this.inherited(arguments);
		var _this = this;
		
		// if data store wasn't assigned, create it
		if (!this.store) {
			this.store = new ginger.GridStore({
				url: this.actionPath + '/fetch?_model=' + this.model + '&_id=' + this.objectId,
				saveUrl:      this.actionPath + '/save',
				idColumnIndex: this.idColumnIndex,
				model:         this.model
			});
		}
		
		// create data item
		if (!this.storeItem) {
			var item = {_ident: this.objectId};
			item[this.idColumnIndex] = this.objectId;
			item[this.store._storeRefPropName] = this.store;
			item[this.store._itemNumPropName] = 0;
			
			this.store._arrayOfTopLevelItems.push(item);
			this.store._arrayOfAllItems[0] = item;
			this.store._itemsByIdentity = [];
			this.store._itemsByIdentity[this.objectId] = item;
			
			this.storeItem = item;
		}
		
		// fill item with data and link edit widget to it 
		//dojo.forEach(dijit.findWidgets(this.containerNode), function(widget){
		prevwidget = firstwidget = contentPane = accordionContainer = null;
		dojo.forEach(findWidgetsRecursively(this), function(widget){
			if (widget.field && _this.storeItem[widget.field] === undefined) {
				_this.storeItem[widget.field] = [widget.attr('value')];
			}
			if (widget.inlineInput == true) {
				widget.storeItem = _this.storeItem;
				widget.contentPane = contentPane;
				widget.accordionContainer = accordionContainer;
				if (!prevwidget) firstwidget = widget;
				else {
					prevwidget.tabNext = widget;
					widget.tabPrev = prevwidget;
				}
				prevwidget = widget;
			}
			if (widget.declaredClass == 'dijit.layout.ContentPane') contentPane = widget;
			if (widget.declaredClass == 'dijit.layout.AccordionContainer') accordionContainer = widget;
			
		});
		if (firstwidget && prevwidget) {
			firstwidget.tabPrev = prevwidget;
			prevwidget.tabNext = firstwidget;
		}
		//this.findNestedWidgetsRecursively(this, this);
	},
	
	/*findNestedWidgetsRecursively: function(parentWidget, frm) {
		if (!parentWidget.containerNode) return;
		dojo.forEach(dijit.findWidgets(parentWidget.containerNode), function(widget){
			frm.findNestedWidgetsRecursively(widget, frm);
			if (widget.field && frm.storeItem[widget.field] === undefined) {
				frm.storeItem[widget.field] = [widget.attr('value')];
			}
			if (widget.declaredClass == 'ginger.InlineText') {
				widget.storeItem = frm.storeItem;
			}
		});
	},*/
	
	onInlineChange: function(widget) {
		console.log('Field: ' + widget.name + ', value: ' + widget.attr('value'));
	},
	
	_isNestedForm: function(element) {
		return !isEmpty(element) && typeof(element) == 'object'
			  && !(element instanceof Array) && !(element instanceof Date);
	},

	submit: function() {
    	this.updateStore();
    	this.store.save({
    		onComplete: dojo.hitch(this, this._loadHandler, null, dojo.hitch(this, this.onSaveCompleted)),
    		onError:    dojo.hitch(this, this._errorHandler)}
    	);
    },
	
	updateStore: function() {
		// iterate through the form values and update the data store
		var formValues = this.attr('value'); var _this = this;
    	dojo.forEach(this._getFormElements(), function(element){
			if (_this.storeItem[element.field] === undefined)
				_this.storeItem[element.field] = "";
			var value = element.value ? element.value + "" : "";
			if(dojo.isArray(value)) value = value[0];
			_this.store.setValue(_this.storeItem, element.field, value);
		});
	},
	
	_getFormElements: function() {
		var elements = [];
		var formValues = this.attr('value');
    	for (var field in formValues) {
			// get field value
			var value = formValues[field] + "";
			
			// ignore nested forms
			if (this._isNestedForm(value)) continue;

			// get form widget holding the value
			var widget = dijit.byId(this.model + '_' + field);
				
			// for checkbox we must convert '' into [false], otherwise the value does not appear in the store
			if (value == '' && widget && (
					widget.baseClass == 'dijitCheckBox' ||
			    	widget.declaredClass == "ginger.RadioGroup")) {
				value = [false];
			} else if (widget && widget.baseClass == 'dijitCheckBox'){
				console.log(field + ': ' + value);
			}
						
			// for dropdown we must store both ID and NAME so grid can display it properly
			if (widget && widget.baseClass == 'dijitComboBox') {
				value = value + '#' + widget.attr('displayedValue');
			}
			
			// add element to the list
			var element = {};
			element.widget = widget; element.value = value + ''; element.field = field;
			elements.push(element);
    	}
		return elements;
	},
	
	onSaveCompleted: function() {
		//this.refresh();
	},
	
	refresh: function() {
		this.store.close();
		this.store.fetch({
			onComplete: function(items, req){
				
			},
			onError: function(request, error){
				console.error('Error during refreshing the form: ' + error.message);
			}
		});
	}
});