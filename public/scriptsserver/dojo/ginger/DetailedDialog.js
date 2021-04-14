dojo.provide("ginger.DetailedDialog");
dojo.require("ginger.Dialog");

dojo.declare("ginger.DetailedDialog", ginger.Dialog, {
	searchMode: false,
	grid: null,
	selectedItem: null,
	frame: null,
	problems: {
		errors: [], 
		warnings: []
	},
	isChanged: false,
	
	startup: function() {
		this.inherited(arguments);
		
		this.frame = dijit.byId(this.id + 'Frame');
	//if (!this.selectedItem) this.selectedItem = this.grid.getFirstSelectedItem();
	},
	
	_onKey: function(evt) {
		this.inherited(arguments);
		//console.log('onkey = '+evt.charOrCode);
		if (evt.charOrCode == dojo.keys.ENTER){
			var node = evt.target;
			//if this is a memo field, we won't be closing the details dialog, otherwise will submit
			if (dojo.byId(node).tagName.toLowerCase() != 'textarea')
				this.submit();
		}
	},
	
	// shows the dialog in 'Edit' mode
	showEdit: function(showMode) {
		this.searchMode = false;
		this.isChanged = false;
		
		// only first item from selection counts for now
		var selectedRow = this.grid.getSelectedItemId();
		if (selectedRow === null) return;
		this.selectedItem = this.grid.getFirstSelectedItem();
		
		// update dialog title
		this.attr('title', selectedRow < 0 ? 'New record' : 'Edit record');
		
		// get existing query
		var href = this.grid.detailedView;
		var queryMark = href.indexOf('?');
		var query = (queryMark == -1 ) ?
		new Object() : dojo.queryToObject(href.substr(queryMark + 1));
				
		// update query in dialog href
		query._parent = selectedRow;
		var gridParentId = this.grid.getParentRowId();
		if (gridParentId) query._super = gridParentId;
		
		// update dialog frame href
		var host = queryMark == -1 ? href : href.substr(0, queryMark);
		this.frame.onLoad = dojo.hitch(this, "onLoad");
		this.frame.attr('href', host + '?' + dojo.objectToQuery(query));
		// open the dialog
		this.show();
	},
	
	// returns HTML with ignore button
	_getIgnoreButton: function(warningId) {
		return "<button dojoType='dijit.form.Button' onclick='dialog_"
		+ this.grid.getId() + ".ignoreWarning(" + this.selectedItem._ident
		+ ", " + warningId + ")'>Ignore</button>";
	},
	
	showProblems: function() {
		//this.problems = {"warnings": [], "errors": []};
		this.problems = {};
		
		var severity = false;
		do { // repeat for errors and problems
			severity = !severity;
			var kind = severity ? 'warnings' : 'errors';
			// get problems from the data store
			if (this.grid.store.problems && this.grid.store.problems[kind])
				var problems = this.grid.store.problems[kind][this.selectedItem._ident];
			else continue; // no problems yet
			
			console.log("Problems for " + this.selectedItem._ident);
			console.log(problems);
			
			// iterate all problems of current record and assign to either dialog or specific widget
			var generalMessage = '';
			var onlyWarnings = true;
			var problemFields = [];
			for (var problem in problems) {
				if (problem == -1) { // this is a general problem message
					for (var id in problems[problem]) {
						if (this.grid.store.isIgnored(this.selectedItem._ident, id))
							continue;
						
						generalMessage += '<b>General ' + (severity ? 'Warning' : 'Error') + ': </b>' +
						problems[problem][id] + '<br>';
						
						if (!severity) onlyWarnings = false;
						else // this is a warning message - display "ignore" button
							generalMessage += this._getIgnoreButton(problem);
					}
				} else { // if it's specific, then highlight problematic fields
					//					var fields = problems[problem];
					//					for(var field in fields) {
					//						this.problems[fields[field]] = fields.Rule || fields;
					//					}
					var fields = problems[problem];
					for(var field in fields) {
						this.problems[fields[field]] = problem.Rule || problem;
					}
				//	console.log(this.problems);
				}
			}
		} while(severity != false);
		
		var self = this;
		
		// validate all widgets
		dojo.forEach(this.getDescendants(), function(widget){
			if (widget.name) widget.invalidMessage = self.problems[widget.name];
			if (widget.validate) widget.validate();
		});
		
		if (generalMessage != '') {
			if (!this.errorToolip) {
				this.errorTooltip = new dijit._MasterTooltip();
				this.errorTooltip.containerNode.className += (' ' + (onlyWarnings ? 'dijitWarning' : 'dijitError'));
			}
			this.errorTooltip.show(generalMessage, this.domNode, 'above');
		}
	},
	
	рide: function() {
		if (this.errorTooltip) this.errorTooltip.hide(this.domNode);		
		if (!this.searchMode && (this.selectedItem._ident > 0) && this.grid.isAutosave) this.grid.refresh();
		this.inherited(arguments);
	},
	
	onLoad: function() {
		if (!this.searchMode) {
			// refresh grids if any
			var _this = this;
			dojo.forEach(this.getDescendants(), function(widget){
				if (widget.declaredClass == 'ginger.Grid') 
					widget.refresh();
				
				if (widget.dialog == null) widget.dialog = _this;
			});
		} else {
			this.onShowSearch();
		}
		
		this.inherited(arguments);
	},
	
	onHide: function() {
		this.frame.destroyDescendants();
	},
	
	ignoreWarning: function(ident, warningId) {
		this.grid.store.ignoreWarning(ident, warningId);
		// revalidate all fields
		dojo.forEach(this.getDescendants(), function(widget){
			if(!widget.name) return;
			widget.validate(false);
		});
		// refresh the grid
		this.grid.scroller.resize();
	},
	
	// show dialog
	executeEdit: function(){
		if (this.errorTooltip)
			this.errorTooltip.hide(this.domNode);
		
		// get respective row in datastore
		var storeItem = this.grid.store._getItemByIdentity(this.selectedItem._ident);
		
		// modify the row
		var store = this.grid.store;
		var gridId = this.grid.getId();
    	
		var formValues = this.attr('value');
		for (var element in formValues) {
			// ignore nested forms
			if (!isEmpty(formValues[element]) && typeof(formValues[element]) == 'object'
				&& !(formValues[element] instanceof Array) && !(formValues[element] instanceof Date)) {
				//console.log('My element: ' + element + ', value:***' + formValues[element] + '###') ;
				continue;
			}
			
			// get field name (backward compatibility)
			//var field = element.indexOf(gridId) != -1 ? 
			//	element.substr(gridId.length+1) : element;
			var field = element;
			
			// get form widget holding the value
			var widget = dijit.byId(this.grid.getId() + '_' + field);
			
			// get field value
			var value = formValues[element];
			
			// for checkbox we must convert '' into [false], otherwise the value does not appear in the store
			if (value == '' && widget && (
				widget.baseClass == 'dijitCheckBox' ||
				widget.declaredClass == "ginger.RadioGroup" ||
				widget.declaredClass == "ginger.InlineRadioGroup")) {
				value = [false];
			} 
			
			// for dropdown we must store both ID and NAME so grid can display it properly
			if (widget && (widget.baseClass == 'dijitComboBox' ||  widget.declaredClass == "ginger.form.DropDown") && !dojo.hasClass(widget.domNode, 'onlyText')) {
				value = value + '#' + widget.attr('displayedValue');
			}
			console.log(value);
			
			if (widget && widget.declaredClass == 'ginger.form.NumberTextBox' && (value === undefined || isNaN(value))) value = null;
			
			// update data store
			store.setValue(storeItem, field, value);
		}
		// autosave changes
		this.grid.save();
	},
	
	onCancel: function() {
		if (this.errorTooltip)
			this.errorTooltip.hide(this.domNode);
		
		if (!this.searchMode && this.grid.store.isDirty())
			this.grid.revert();
		if (!this.searchMode && this.isChanged) {
			this.grid.refresh();
		}
		this.inherited(arguments);
	},
	
	/**
     * Shows search dialog - creates stores for FilteringSelect items
     */
	showSearch: function() {
		this.searchMode = true; //this.attr('title', 'Search');
		
		// get existing query
		this.frame.onLoad = dojo.hitch(this, "onLoad");
		this.frame.attr('href', this.grid.searchView);
		this.show();
	},
	
	onShowSearch: function() {
		var formValues = this.grid.searchFilter;
		
		for (var element in formValues) {
			// get form widget holding the value
			var widget = dijit.byId(this.grid.getId() + '_' + element);
			
			if (!widget) {
				//console.log('widget ' + this.grid.getId() + '_' + element + ' not found');
				continue;
			}
			
			if (value == "") {
				var emptyWidget = dijit.byId(this.grid.getId() + '_' + element + '_empty');
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
	},
	
	executeSearch: function() {
		var query = this.grid.store.getQuery();

		// empty all arguments first
		var gridId = this.grid.getId();
        
		var formValues = this.attr('value');
			
		for (var element in formValues) {
		
			// get form widget holding the value
			var widget = dijit.byId(this.grid.getId() + '_' + element);
			
			if (!widget) {
				console.log(this.grid.getId() + '_' + element + ' is undefined');
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
		
		// apply filter
		this.grid.store.setQuery(query);
		
		var searchFilter = [];
		var searchCount = 0;
		//this is for search filter
		for (var element in formValues) {
			var widget = dijit.byId(this.grid.getId() + '_' + element);
			
			if (widget.declaredClass == 'ginger.RadioGroup') {
				var filterValue = widget._getSelectedId();
				if (filterValue) {
					searchFilter[element] = filterValue;
					searchCount++;
				}
			} else if (widget.baseClass == 'dijitCheckBox' && widget.checked) {
				searchFilter[element] = 'checked';
				searchCount++;
			} else if (widget.attr('value') != '' && widget.attr('value') !== null) { 
				searchFilter[element] = widget.attr('value');
				searchCount++;
			}
		}
    	
		//save search values to grid.searchFilter
		this.grid.searchFilter = searchFilter;
		
		//console.log('searchFilter length=' + searchCount);
		if (dojo.byId(this.grid.getId() + '_search_mark')) {
			if (searchCount > 0) 
				dojo.byId(this.grid.getId() + '_search_mark').setAttribute('style', 'visibility: visible; display: block;');
			else 
				dojo.byId(this.grid.getId() + '_search_mark').setAttribute('style', 'visibility: hidden; display: none;');
		}
						
		// refresh the grid
		//this.grid.scroller.page = null;
		//this.grid.scroller.scrollboxNode=null;
		this.lastSelected=null;
		this.grid.refresh(true);
		this.hide();
		
	/*
        // update filter values in query
        dojo.forEach(this.getDescendants(), function(widget) {
        	if (!widget.name) return;
        	
        	// get name of the corresponding field
        	var field = widget.name.substr(gridId.length + 1);
        	
        	// get filter value
        	var filterValue = widget.attr('displayedValue') || widget.attr('value');

        	// if empty, remove from query
        	if(filterValue === null || filterValue === "" ||
        	      (filterValue === 'null' && widget.attr('displayedValue') === "")) {
        		query[field] = null;
        		return;
        	}
        	        		
        	
        });*/
	},
    
	execute: function() {
		if (dijit._masterTT)
			dijit.hideTooltip(dijit._masterTT.aroundNode);
		this.searchMode ? this.executeSearch() :this.executeEdit(); 
	},
	
	submit: function() {
		this.searchMode ? this.executeSearch() :this.executeEdit();
	},
    
	clear: function() {
		dojo.forEach(this.getDescendants(), function(widget){
			//console.log(widget.id + ' baseClass=' + widget.baseClass + ' declaredClass=' + widget.declaredClass + ' value=' + widget.attr('value'));
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
	}

});
