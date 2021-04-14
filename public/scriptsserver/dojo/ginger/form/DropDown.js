dojo.provide("ginger.form.DropDown");
dojo.require("dijit.form.FilteringSelect");

dojo.declare("ginger.form.DropDown", [dijit.form.FilteringSelect, ginger.form.FormWidget], {
	model:    "",
	field:    "",
	grid:     "",
	gridId:     "",
	dialog:   null,
	dependsOn: "",
	actionPath: "",
	hiddenValue: "",
	emptyCaption: "",
	urlString: "",
	autoComplete: true,
	allowNewValues: false,	// allow adding new values to dropdown
	parents: '',		// 'parent1, parent2, ...' - columnIds, this widget depends on 
	parentColumns: [],	// columns this widget depends on.
	childColumns: [],		// child columns
	started: false,
	
	constructor: function() {
		this.progressImageSrc = baseUrl + '/public/images/inprogress.gif';
	},
	
	startup: function() {
		console.log('startup');
	    // sometimes the startup is called two times, not sure why. This is to prevent this from happening.
	    if (this.started) return;
	    this.started = true;
	    
		this.inherited(arguments);
		
		// switch off autoComplete if allow adding new values
		if (this.allowNewValues == true || this.allowNewValues == 'true') {
			this.allowNewValues = true;
			this.autoComplete = false;
		} else {
			this.allowNewValues = false;
		}
		
		if (this.grid) this.grid = dijit.byId(this.grid);
		if (this.grid && !this.gridId) this.gridId = this.grid.getId();
		if (!this.actionPath && this.grid) this.actionPath = this.grid.store.getBaseUrl();
		
		// consistency checks
		if (this.model == "" && this.grid == null && this.actionPath == null) {
			console.error('Either grid, model or actionPath must be specified for the dropdown');
			return;
		} else if (this.model != "" && this.field =="") {
			console.error('Cannot fetch dropdown values: column is not specified');
			return;
		}
		
		// construct store URL	
		if (this.urlString.length == 0)
			this.urlString = this.model == "" ?
			this.actionPath + '/dropdown?_model=' + this.gridId + '&_column=' + this.field	: 
			baseUrl + '/dropdown/fetch?_model=' + this.model + '&_column=' + this.field;
		else {
			this.urlString = baseUrl + this.urlString;
			this.urlString += this.urlString.indexOf('?') > 0 ? '' : '?';
		}
			
		this.store = new dojox.data.QueryReadStore({url: this.urlString});		
			
		dojo.attr(this.textbox, 'name', this.name);
		dojo.attr(this.valueNode, 'name', this.name+'_id');
		
		/*if (this.dependsOn != '') {
			widget = dijit.byId(this.dependsOn);
			var dom = dojo.byId(this.dependsOn);
			
			// first get current value
			this.focusNode.value = dom.value;
			
			// then connect to any further changes
			var _this = this;
			dojo.connect(widget.textbox, 'onblur', function(evt){;
				_this.textbox.value = dom.value;
			});	
		}*/
		
		// convert dependent columns from ids to widgets
		this.childColumns.lenght = 0;
		this.childColumns = [];
		this.parentColumns.lenght = 0;
		this.parentColumns = [];
		if (this.parents) {
			var parentColumns = this.parents.split(',');
			for (var i in parentColumns) {
				var columnName = parentColumns[i];
				var parentWidget = dijit.byId(this.gridId+'_'+ columnName);
				var self = this;
				if (parentWidget) {
					if (!parentWidget.childColumns)
						parentWidget.childColumns = [];
					parentWidget.childColumns.push(this.id);
					this.parentColumns.push(parentWidget.id);
				} else {
					console.warn('Column ' + this.name + ' depends on column "' + columnName + '", but widget "' + this.gridId+'_'+columnName + '" is not found');
				}
			}
		}
	},
	
	_applyAttributes: function() {
		this.inherited(arguments);
		if (this.displayedValue) {
			this._lastDisplayedValue = this.textbox.value = this.displayedValue;
			this.value = this.valueNode.value = this.hiddenValue;
		}
		else this._lastDisplayedValue = this.textbox.value = this.emptyCaption;
	},
	
	_setValueAttr: function(/*String*/ value, /*Boolean?*/ priorityChange){
		if (value.indexOf('#') >=0) {
			var parts = value.split('#');
			this.value = this.valueNode.value = parts[0];
			this._lastDisplayedValue = this.textbox.value = parts[1];
			return;
		}
		this.inherited(arguments);
	},
	
	/*buildRendering: function() {
		this.inherited(arguments);
		if (this.displayedValue) {
			this._lastDisplayedValue = this.textbox.value = this.displayedValue;
			this.value = this.valueNode.value = this.hiddenValue;
		}
		else this._lastDisplayedValue = this.textbox.value = this.emptyCaption;
	},*/
	
	_startSearch: function(/*String*/ key){
	    console.log('Searching: ' + this.store.url);
	    
		// get current query of the store
		var url = this.store.url;
		
		var queryMark = url.indexOf('?');
        var query = dojo.queryToObject(url.substr(queryMark + 1));
        
        for (var i in this.parentColumns) {
			//var parentWidget = this.parentColumns[i];
        	var parentWidget = dijit.byId(this.parentColumns[i]);
			var columnName = parentWidget.attr('name');
			
			if (parentWidget) {
				query[columnName] = parentWidget.attr('value');
			} else {
				console.warn('Column ' + this.name + ' depends on column "' + columnName + '", but widget "' + this.gridId+'_'+columnName + '" is not found');
			}
		}
		
		// filter by parent
		if (this.grid != null && this.grid != '' && this.grid.getParentRowId) {
			var parentId = this.grid.getParentRowId();
			if (parentId !== null) query._parent = parentId;
		}
		else query._parent = null;
		
		// always show empty row when in Search mode
		if (this.dialog && this.dialog.searchMode)
			query._required = false;
			
		// no empty row when it's a specific search
		if (key != '') query._required = true;
		
		// set query
    	this.store.url = this.store.url.substr(0, queryMark) + '?' + dojo.objectToQuery(query);
    	
    	// parent method
		this.inherited(arguments);
	},
	
	_setBlurValue: function() {
		// we need this in order to prevent unnecessary lookups to the database
		var newvalue = this.attr('displayedValue');
		if (this._lastDisplayedValue != newvalue) 
			this.inherited(arguments);
	},
	
	_setDisplayedValueAttr: function(/*String*/ label, /*Boolean?*/ priorityChange){
		if (label == this._lastDisplayedValue || isEmpty(label)) {
			return;
		}
		this.inherited(arguments);
	},
	
	_getDisplayedValueAttr: function() {
		if (this.textbox.value == this.emptyCaption)
			return "";
		else return this.inherited(arguments);
	},
	
	//_getValueAttr: function() {
	//	return this.valueNode.value + '#' + this.textbox.value;
	//},
	
	isValid: function(){
		return true;
	},
	
	_callbackSetLabel: function(result, dataObject, priorityChange){
		if((dataObject && dataObject.query[this.searchAttr] != this._lastQuery) || (!dataObject && result.length && this.store.getIdentity(result[0]) != this._lastQuery)){
			return;
		}
		if(!result.length){
			this.valueNode.value = "";
			dijit.form.TextBox.superclass._setValueAttr.call(this, "", priorityChange || (priorityChange === undefined && !this._focused));
			this._isvalid = false;
			this.validate(this._focused);
			this.item = null;
		}else{
			var item = result[0];
			// if item is empty, fill it with new entered value
			if (this.allowNewValues) {
				if (item.i['ID'] == null) {
					item.i['label'] = this.textbox.value;
					item.i['name'] = this.textbox.value;
				}
			}
			this.attr('item', item, priorityChange);
		}
	},
	
	labelFunc: function(item, store){
		value = store.getValue(item, this.searchAttr);
		if (value) return value.toString();
		else return '';
	},
	
	onChange: function() {
		this.inherited(arguments);
		
		// empty child columns
		var self = this;
		for (var i in this.childColumns) {
			var child = dijit.byId(this.childColumns[i]);
			child.value = child.valueNode.value = '';
			child.displayedValue = child.textbox.value = child.emptyCaption || '';
		};
	},
	
	updateValueLocalOnly: function(value, displayedValue) {
	    this._lastDisplayedValue = this.displayedValue = this.textbox.value = displayedValue;
        this.value = this.valueNode.value = value;
        
        // hides the placeholder
        this._phspan.style.display = "none";
	}
});