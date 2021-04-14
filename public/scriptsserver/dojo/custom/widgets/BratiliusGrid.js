dojo.provide("custom.widgets.BratiliusGrid");
dojo.require("ginger.Widget");

dojo.declare("custom.widgets.BratiliusGrid", ginger.Widget, {
	id: "",
	parentPaneId: "",
	parentPane: null,
	detailedView: "",
	formActionUrl: "",
	parentUrl: "",
	selectAll: null,
	selectedRowId: null,
	childGridIds: null,
	superGridId: "",
	
	startup: function() {
		
		this.selectAll = dijit.byId(this.id + '_select_all');
		
		this.inherited(arguments);
		this.parentPane = dijit.byId(this.parentPaneId);
		if (!this.parentPane) {
			console.error('Parent pane for the grid ' + this.id + ' by name ' + this.parentPaneId + ' is not found');
			return;
		}
		
		//every time the user clicks on the "select all" checkbox, the other records will be marked as checked or unchecked, respectively
		dojo.connect(this.selectAll, 'onChange', dojo.hitch(this, this.onSelectAllClicked));
		
	},
	
	getId: function(){
		var gridId = this.id.substring(4);
		return gridId;
	},
	
	showNewDialog: function(parent){
		var hasParams = this.detailedView.indexOf('?') !== -1;
		
		if(!parent && parent != -1 && this.superGridId){
			var parentSelectedRowId  = dijit.byId(this.superGridId).getSelectedRowId();
			if (parentSelectedRowId) parent = parentSelectedRowId;
			else{
				alert('Please select a row in the parent grid.');
				return;
			}
		}
		
		var url = baseUrl + this.detailedView + (hasParams ? '&' : '?') + '_super=' + parent + '&_parent=-1';
		
		bratiliusDialog.frame.attr('href', url);
		bratiliusDialog.parentPane = this.parentPane;
		bratiliusDialog.show();
	},
	
	showEditDialog: function(id){
		var hasParams = this.detailedView.indexOf('?') !== -1;
		
		
		var url = baseUrl + this.detailedView + (hasParams ? '&' : '?') + '_parent=' + id;	
		
		bratiliusDialog.frame.attr('href', url);
		bratiliusDialog.parentPane = this.parentPane;
		bratiliusDialog.show();
		

	},
	
	showDoorEditDialog: function(id, showMode){
		var hasParams = this.detailedView.indexOf('?') !== -1;
		
		
		var url = baseUrl + this.detailedView + (hasParams ? '&' : '?') + '_parent=' + id + '&_showMode=' + showMode;	
		
		gridDialogDoor.frame.attr('href', url);
		gridDialogDoor.parentPane = this.parentPane;
		gridDialogDoor.showEdit();
	},
	
	onSaveComplete: function() {
		
		//changeBody(this.parentUrl, false, true);
		
		/*window.history.back();
		
		//we need this for the case user was navigating through the hash tags
		while (isNaN(dojo.back.getHash())){
			window.history.back();
		}*/
	},
	
	deleteItem: function(id){
		
		if (!id) {
    		alert('Please, select the record for deletion');
			return;
    	}
    	
		var _this = this;
		// create a confirmation dialog
		var dlg = new ginger.ActionDialog({
			message: 'Please, confirm the deletion of this record. Deleted record cannot be recovered.',
			submitTitle: 'Delete',
			cancelTitle: 'Cancel',
			draggable: false,
			onSubmit: function(evt){
				dojo.xhrDelete({
					url: _this.formActionUrl,
					handleAs: 'json',
					content: {
						id: id
					},
					load: function(response, ioArgs) {
						_this.refresh();
						dlg.hide();
						dlg = null;
					},
					error: function(response, ioArgs) {
						console.log('Errors: ' + response);
					}
				});
				
				
            }
		});
		
		
	},
	
	deleteItems: function(){
		var _this = this;
		
		
		//get the ids of the items that should be deleted
		
		var ids = [];
		
		dojo.query('input[id^="'+ this.id + '_"]').forEach(function(node, index, array){
			if (node.checked) {// the element is not checked, so we don't have to do anything
				
				//get the id of the element and put it into the list
				var recordId = node.id;
				recordId = recordId.replace((_this.id + '_'), '');
				
				if (!isNaN(recordId)) //if this is a number
					ids.push(recordId);
			}
		});
		
		//now, when we have created a list of records to be deleted. if the list is empty, let's show it
		if (ids.length == 0) {
			alert('Please select at least one record to delete');
			return;
		}
		
		//if there is at least one record, show the confirmation and send the request to the server
		var dlg = new ginger.ActionDialog({
			message: 'Please confirm the deletion of these records. Deleted records cannot be recovered.',
			submitTitle: 'Delete',
			cancelTitle: 'Cancel',
			graggable: false,
			onSubmit: function(evt){
				
				for (var i = 0; i < ids.length; i++){
					dojo.xhrDelete({
						url: _this.formActionUrl,
						handleAs: 'json',
						content: {
							id: ids[i]
						},
						load: function(response, ioArgs) {
						},
						error: function(response, ioArgs) {
							console.log('Errors: ' + response);
						}
					});
				}
				
				dlg.hide();
				dlg = null;
				_this.refresh();
				
			}
		});
	},
	
	onSelectAllClicked: function(){
		
		console.log('checkbox clicked');
		var _this = this;
		
		//get all checkboxes from this grid and mark them checked or unchecked based on the main grid checkbox
		dojo.query('input[id^="'+ this.id + '_"]').forEach(function(node, index, array){
			
			dojo.byId(node.id).checked = _this.selectAll.checked;
		});
	},
	
	refresh: function() {
		this.parentPane.attr('href', this.parentPane.attr('href'));
	},
	
	fetchSuper: function(_super){
		var url = this.parentPane.attr('href');
		
		var hasParams = url.indexOf('?') !== -1;
		
  		if (!hasParams){
  			url = url + '?_super=' + _super;
  		}
  		else{
  			url = url.substring(0, url.indexOf('?')) + '?_super=' + _super;
  		}
		this.parentPane.attr('href', url);
	},
	
	sortByColumn: function(columnId, direction) {
		var query = this.getQuery();
		query.sort_by = columnId;
		query.sort_direction = direction;
		this.setQuery(query);
	},
	
	getQuery: function() {
		var href = this.parentPane.attr('href');
		var paramsIndex = href.indexOf('?');
		var params = paramsIndex < 0 ? '' : href.substr(paramsIndex + 1);
		var query = dojo.queryToObject(params);
		return query;
	},
	
	setQuery: function(query) {
		var href = this.parentPane.attr('href');
		var paramsIndex = href.indexOf('?');
		var base = paramsIndex < 0 ? href : href.substring(0, paramsIndex);
		this.parentPane.attr('href', base + '?' + dojo.objectToQuery(query));
	},
	
	goToPage: function(pageIndex) {
		if (!this.parentPane) {
			console.error('Grid parent pane not found, cannot perform the operation');
			return;
		}
		var href = this.parentPane.attr('href');
		var qPos = href.indexOf('?');
		if (qPos < 0)
			href += '?page=' + pageIndex;
		else {
			var pagePos = href.indexOf('page=');
			if (pagePos < 0)
				href += '&page=' + pageIndex;
			else {
				endPos = href.indexOf(';');
				if (endPos < 0)
					href = href.substr(0,pagePos) + 'page=' + pageIndex;
				else
					href = href.substr(0,pagePos) + 'page=' + pageIndex + href.substr(endPos);
			}
		}
		this.parentPane.attr('href', href);
	},
	
	selectRow: function(id){
		var oldSelectedRowId = this.getSelectedRowId();
		if (id == oldSelectedRowId) return; //we do not need to do the refresh if the id is the same
		
		this.setSelectedRowId(id);
		
		//Load child grids		
		for (i = 0; i < this.childGridIds.length; i++){
			console.log(this.childGridIds[i]);
			
			var childGrid = dijit.byId(this.childGridIds[i]);
  			childGrid.fetchSuper(id);
		}
	},
	
	setSelectedRowId: function(id){
		
		//if another row was previously selected, unselect it
		if (this.selectedRowId && dojo.byId(this.id + '_row_' + this.selectedRowId)){
			dojo.removeClass(this.id + '_row_' + this.selectedRowId, "selectedRow");
		}
		
		//remember new selection
		this.selectedRowId = id;
				
		//mark new selection in the grid
		if(this.selectedRowId){
			if (dojo.byId(this.id + '_row_' + this.selectedRowId))
				dojo.addClass(this.id + '_row_' + this.selectedRowId, "selectedRow");
			else{
				this.selectedRowId = null;
			}
		}
	},
	
	getSelectedRowId: function(id){
		return this.selectedRowId;
	},

});
