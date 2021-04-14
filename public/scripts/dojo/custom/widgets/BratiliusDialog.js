dojo.provide("custom.widgets.BratiliusDialog");
dojo.require("ginger.Dialog");

dojo.declare("custom.widgets.BratiliusDialog", ginger.Dialog, {
	grid: null,
	parentPane: null,
	selectedItem: null,
	frame: null,
	problems: {
		errors: [], 
		warnings: []
	},
	isChanged: false,
	parentUrl: '/index/home',
	searchMode: false,
	
	
	startup: function() {
		this.inherited(arguments);
		this.draggable = false;
		
		this.frame = dijit.byId(this.id + 'Frame');
	},
	
	setParentUrl: function(url){
		if (url) this.parentUrl = url;
		else this.parentUrl = '/index/home';
	},
	
	getParentUrl: function(){
		return this.parentUrl;
	},
	
	setGrid: function(gridId){
		this.grid = dijit.byId(gridId);
	},
	
	
	// shows the dialog in 'Edit' mode
	showEdit: function(detailedViewUrl) {
		this.frame.attr('href', detailedViewUrl);
		// open the dialog
		this.show();
	},
	
	// shows the dialog in 'Edit' mode
	showSearch: function(searchViewUrl, gridId) {
		this.grid = dijit.byId(gridId);
		if (!this.grid) {
			console.error('Cannot open search window: grid "' + gridId + '" is not found');
			return;
		}
		this.searchMode = true;
		this.attr('title', 'Search');
		
		this.frame.onLoad = dojo.hitch(this, "onLoad");
 		this.frame.attr('href', searchViewUrl);
		// open the dialog
		this.show();
	},
	
	onLoad: function() {
		if (this.searchMode) {
			this.onShowSearch();
		}
		this.inherited(arguments);
	},
	
	executeSearch: function(qparam=null) {
		// get the current grid query params
		if(qparam == null)
		{
		var query = this.grid.getQuery();
		var gridId = this.grid.id.replace('cmp_', '');
		// update with the params from the form
		var formValues = this.attr('value');
		for (var element in formValues) {

			// get form widget holding the value
			var widget = dijit.byId(gridId + '_' + element);
			
			if (!widget) {
				console.log(gridId + '_' + element + ' is undefined');
				continue;
			}
			
			//skip checkboxes, which used to select empty values
			if (element.indexOf('_empty') > 0) {
				continue;
			}
			
			// get field value
			var filterValue = formValues[element] + '*';

			// for dropdown we search by displayed value
			if (widget && widget.declaredClass == 'ginger.form.DropDown') {
				filterValue = widget.attr('displayedValue');
			} else // format date
			if (widget && widget.declaredClass == 'ginger.form.DateTextBox'){
				filterValue = formatDate(widget.attr('value'));
			}
			
			if (widget && widget.declaredClass == 'dijit.form.TimeTextBox'){
				filterValue = formatTime(widget.attr('value'));
			}
			
			if (widget && widget.declaredClass == 'ginger.RadioGroup') {
				filterValue = widget._getValueAttr();
			}
			
			if (dijit.byId(widget.id + '_empty')) {
				if (dijit.byId(widget.id + '_empty').checked) {
					query[element] = '';
					continue;
				} 
			}

			if(filterValue == '' || filterValue == '*') {
				query[element] = null;
				continue;
			}

				// add filter to the query
				query[element] = filterValue;
			}

		}
		else{
			query = qparam;
		}
		// apply the new filter
		console.log("this.grid==",this.grid);
		console.log(query);

		this.grid.setQuery(query);
		this.hideSearch();
	},
	
	clearSearch: function() {
		dojo.forEach(this.getDescendants(), function(widget){
			if (widget.declaredClass == 'ginger.form.DropDown') {
				widget.value = null;
				widget.valueNode.value = null;
				widget.textbox.value = '';
			}
			else if (widget.baseClass == 'dijitCheckBox') {
				widget.attr('value', null);
				widget.checked = false;
			} 
			else if (widget.declaredClass == 'ginger.RadioGroup') {
				widget._setChecked(null);
			}
			else
				widget.attr('value', null);
		});
	},
	
	hideSearch: function() {
		this.searchMode = false;
		dojo.forEach(this.frame.getDescendants(), function(widget){
			widget.destroyRecursive();
		});
		this.hide(true);
	},
	
	hide: function(nativeHide){
		// call for a native hide method
		if (nativeHide) {
			this.inherited(arguments);
			return;
		}
		
		//mainDialog.hide();
		
		this.hide(true);
		this.refreshGrid();

		
		//changeBody(this.parentUrl, true, true);
		/*
		window.history.back();
		
		//we need this for the case user was navigating through the hash tags
		while (isNaN(dojo.back.getHash())){
			window.history.back();
		}*/
	},
	
	refreshGrid: function(){
		if (this.parentPane){
			this.parentPane.attr('href', this.parentPane.attr('href'));
		}
	},
	
	onShowSearch: function() {
		var formValues = this.grid.getQuery();
		var gridId = this.grid.id.replace('cmp_', '');
		
		for (var element in formValues) {
			// get form widget holding the value
			var widget = dijit.byId(gridId + '_' + element);
			
			if (!widget) {
				//console.log('widget ' + this.grid.getId() + '_' + element + ' not found');
				continue;
			}
			
			if (value == "") {
				var emptyWidget = dijit.byId(gridId + '_' + element + '_empty');
				emptyWidget.attr('checked', true);
				continue;
			}
			
			// get field value
			var value = formValues[element];
			value = value.replace(/\*/g, '');
			
			//console.log(element + ' declaredClass = ' + widget.declaredClass);
			
			if (widget.declaredClass == 'ginger.form.DropDown') {
				var delimiter = value.indexOf('#');
				widget.value = value.substr(0, delimiter);
				widget.textbox.value = value.substr(delimiter + 1);
			} 
			else if (widget.declaredClass == 'ginger.form.DateTextBox'){
				// because of IE we have to parse the date
				var result = value.match(/(\d{4})-(\d{2})-(\d{2})/);
				if (result != null) {
					value = new Date(/* Year */result[1], /* Month - 1 */result[2]-1, /* Day */result[3]);
					widget.attr('value', value);
				}
			} 
			else if (widget.declaredClass == 'ginger.form.TimeTextBox'){
				value = widget.parse(value.substr(0,5));
				widget.attr('value', value);
			} 
			else if ((widget.baseClass == 'dijitCheckBox') && value == 'checked') {
				widget.checked = true;
				widget.setAttribute('value', '1');
			} else if (widget.declaredClass == 'ginger.RadioGroup') {
				widget.setAttribute('value', value);
				widget._setChecked(value);
			} else	if (widget) {
				widget.setAttribute('value', value);
			} else {
				console.log('widget ' + this.grid.getId() + '_' + element + ' not found');
			}
		}	
	}

});
