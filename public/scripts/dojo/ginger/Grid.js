dojo.provide("ginger.Grid");

dojo.require("dojox.grid.DataGrid");
dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dojox.data.QueryReadStore");
dojo.require("ginger.GridStore");
dojo.require("ginger.Component");
dojo.require("ginger.GridButton");

dojo.require("dojox.grid.cells.dijit");

dojo.declare("ginger.Grid", [dojox.grid.DataGrid, ginger.Component], {
    parent:         '',
    parentId:       "",
    lastSelected:   [],
    idColumnIndex:  "",
    controller:     "",
    detailedDialog: null,
    detailedView: 	"",
	searchView: 	"",
	detailedTitle:	"",
	shortTitle:		false,
	inline:         "",
	selector:       "",
	maxRows:        "",
	buttons:        [],
	numbered:       "",
    searchFilter:   [],
    isAutosave:		false,
	doubleClickEdit: true,
	paginator:		null,
	usePaginator:   false,
	currentPageIndex: 0,
	class:			"",
	
    // overrides postCreate function to setup parent grid
    postCreate: function() {
		this.store = new ginger.GridStore({grid: this});
		
        this.inherited(arguments);
        this.store.grid = this;
        
        if (this.parent != '') this.parent = dijit.byId(this.parent);
        
        if (this.parent && this.parent.addChild) {
            this.parent.addChild(this);
        }
		
        if (this.detailedDialog && !this.detailedDialog.grid) this.detailedDialog.grid = this;
		
		if (this.inline == '' || this.inline == 'false') this.inline = false;
		else this.inline = true;
		
		if (this.numbered == '' || this.numbered == 'false') this.numbered = false;
		else this.numbered = true;
    },
    
    // returns all selected items
    getActiveItems: function() {
    	// user should overload it
    	return this.selection.getSelected();
    },
    
    addNewItem: function() {
    	var idColumnIndex = this.idColumnIndex;
        if( !this.newItemsCount ) this.newItemsCount = 0;
		
        // add new item to the store populating fields with designated parent values
        var newItem = {_ident: (-1)*(++this.newItemsCount)};
        
		// if this grid is a child, add information about parent row
		if (this.parent) {
			var parentId = this.parent.getSelectedItemId();
			if(parentId == undefined || parentId == null) {
				alert('Please select a row in parent grid');
				return;
			}else if (parentId < 0 ) {
				alert('Please save changes in parent grid first');
				return;
			}
			
			newItem._parent = parentId;
		} else if (this.parentId) newItem._parent = this.parentId;

        // set values of columns
        dojo.forEach(this.layout.cells, function(cell){
        	//if (cell.hidden == true) return;
        	newItem[cell.field] = (cell.defaultValue == '...' ? '' : cell.defaultValue);
        	//newItem[cell.field] = cell.defaultValue || '...';
        	
        	// fix empty date representation  
        	if (cell.formatter == formatDate && newItem[cell.field] == "")
        		newItem[cell.field] = null;
        });
        // ID column must be added always
        if (!newItem[idColumnIndex])
            newItem[idColumnIndex] = (-1)*(this.newItemsCount);
        
        // create new record in datastore
        this.store._arrayOfAllItems.length = this.get('rowCount');
        
        this.store.newItem(newItem);
        
        // scroll to show this row
        //this.scrollToRow(this.rowCount);
        
        
    },
    
    _onNew: function(item, parentInfo){
    	this.inherited(arguments);
		
		// select the row and open edit dialog
    	var rowCount = this.get('rowCount');
        this.selection.deselectAll();
        this.selection.addToSelection(rowCount-1);
		
        //if number of rows >= maxRows, then hide the new button
        if (this.maxRows > 0) {
        	if (rowCount + 1 >= this.maxRows) {
        		this.buttons['new'].hide();
        	} 
        }
        
       	if (!this.inline) this.showDetailed();
	},
    
    save: function(force) {
		this.edit.apply();
    	this.store.save({
    		onComplete: dojo.hitch(this, "onSaveComplete"),
    		onError:    dojo.hitch(this, "onSaveFailed")}
    	);
    },
    
    onSaveFailed: function() {
    	// show the edit dialog with the error
    	if (this.detailedDialog && this.detailedDialog.open) {
			this.detailedDialog.showProblems();
		} 
    },
    
    onSaveComplete: function() {
    	// hide dialog (if it was shown)
		if (this.detailedDialog && this.detailedDialog.open) this.detailedDialog.hide();
		var grid = this;
		this.refresh(false, function(){
			grid.scrollToRow(grid.getFirstSelectedRow());
			grid.resize();
			grid.selection.deselectAll();
		});
    },

    // saveToExcel
    fetchExcel: function() {
    	window.open(this.store.getBaseUrl() + '/excel?' + dojo.objectToQuery(this.store.getQuery()), 'Excel');
    	//window.open(this.store._jsonFileUrl + '&_excel=true', 'Excel');
    },
    
    filter: function(filters, renew, onComplete) {
    	//if(!this._isLoaded) { // grid was not fully loaded - cannot work with it
       //     console.error('Grid is not loaded yet. Can\' filter');
		//	return;
       // }
	   
        // first empty current store data
        this.store.close();            
        
        // get current fetch query constraints as an object
        var currentFilter = this.store.getQuery();
        
        // renew filter either completely or just delta
        var newFilter = renew ? new Object() : currentFilter;
        for (var filt in filters) {
        	newFilter[filt] = filters[filt];
        }
        this.store.setQuery(newFilter);
                                           
        // renew the store
        this.refresh(true, onComplete);
    },
    
    _fetch: function(start, isRender, onComplete) {
    	//if pagination is used
    	if (this.usePaginator) {
			return this._fetchPage(-1, onComplete);
    	}
    	
    	var row = this.scroller.firstVisibleRow;
    	//if (!this.assertPageDirty()) return;
    	
    	for (var i in this.store._pending._newItems)
    	if (start >= this.rowCount + parseInt(i)) {
    		return;
    	}
    	
    	// forward a page number from which to start fetch
    	// first grid render (isRender=true) must always redirect to the last page
    	var query = this.store.getQuery();
    	if (!isRender) query['start'] = start;
    	
    	// set sorting parameters (if any)
    	var sortSpec = this.getSortProps();
    	if (sortSpec) {
    		query._sort = sortSpec[0].attribute;
    		query._sortDir = sortSpec[0].descending ? 'desc' : 'asc';
    	}

    	// request next page of data 
    	var store = new ginger.GridStore(
    			{url: this.store.getFetchBaseUrl() + '?' + dojo.objectToQuery(query)});
    	var mainStore = this.store;var grid = this;
    	
		// abort previous request
		if (this.request) this.request.abort();
    	
		this._isLoading = true;this._isLoaded = false;
		this.showMessage(this.loadingMessage);
		
		this.request = store.fetch({
			start: 0,
			count: this.rowsPerPage,
			onComplete: function(items, req) {
    			// initialize our main store if needed
    			mainStore._features['dojo.data.api.Identity'] = store._features['dojo.data.api.Identity'];
    			mainStore._loadFinished = true;
    			if (!mainStore._arrayOfAllItems) 	  mainStore._arrayOfAllItems = [];
    			if (!mainStore._arrayOfTopLevelItems) mainStore._arrayOfTopLevelItems = [];
    			if (!mainStore._itemsByIdentity) 	  mainStore._itemsByIdentity = [];
    			
    			// if grid is rendered for the first time, show last page
    			/*if (isRender)
    				start = store.totalrowscount - 1 - (store.totalrowscount - 1)%grid.rowsPerPage;*/
    			
    			mainStore.setQuery(store.getQuery());
    			grid.searchFilter = store.getQuery();
    			
    			var req = {start: start, count: mainStore.rowsPerPage,isRender: isRender};
    			
    		    // update main store item arrays with just fetched items
    		    var firstIndex = -1; 
    		    dojo.forEach(items, function(item, idx){
    		    	item[mainStore._storeRefPropName] = mainStore;
    		    	item[mainStore._itemNumPropName]  = start+idx;
    				
    		    	// array of top level items is quite complex:
    		    	// it must be contigous, meaning no gaps between numbers
    		    	// therefore we must find a first place where our item can fit it
    		    	var index = firstIndex + idx;
    		    	if (firstIndex == -1) {
    		    		var index = firstIndex = mainStore._arrayOfTopLevelItems.length;
    		    		if (start + idx < index ) { // must be somewhere before
    		    			for (var i = 0; i < mainStore._arrayOfTopLevelItems.length; i++) {
    		    				if (mainStore._arrayOfTopLevelItems[i]._itemNumPropName >= start + idx) {
    		    					firstIndex = index = i;break;
    		    	}}}}
    		    	
    		    	// add item in toplevelitems so contingency is not broken
    		    	if (start + idx < mainStore._arrayOfTopLevelItems.length)
    		    		mainStore._arrayOfTopLevelItems.splice(index, item);
    		    	else
    		    		mainStore._arrayOfTopLevelItems.push(item);
    		    	
    		    	// add item at its exact offset position into the array of all items
    		    	mainStore._arrayOfAllItems[start+idx]      = item;
    		    	
    		    	var identity = item._ident[0];
    		    	mainStore._itemsByIdentity[identity] = item;
    			});
    		    grid.scroller.invalidateNodes();
    		    grid._onFetchBegin(store.totalrowscount, req);
    		    grid._onFetchComplete(items, req);
				grid.sizeChange();
    		    grid.restoreSelection();
    		    grid.scroller.resize();
    		    
    		    if (isRender) {
    		    	/*grid.scrollToRow(start);
    		    	grid._bop = start;
    				grid._eop = grid._bop + grid.rowsPerPage;
    				if (start > 0) { // remove obsolete info about 0 page
    					grid._pages[0] = undefined;
    					grid.scroller.destroyPage(0);
    					grid._pages[Math.floor(store.totalrowscount/grid.rowsPerPage)] = true;
    					grid.scroller.popPage();
    				}*/
    		    }
    		    else {
    		    	//grid.scrollToRow(row);
    		    }
				
				if (dojo.isFunction(onComplete)) onComplete();
    		    
    		},
			onError: dojo.hitch(this, "_onFetchError")
		});
    },
    
    _fetchPage: function(pageIndex, onComplete) {
    	var isRender = false;
		pageIndex = pageIndex >= 0 ? pageIndex : this.currentPageIndex;
		this.currentPageIndex = pageIndex;
    	var start = this.rowsPerPage * pageIndex;
    	
    	// first grid render (isRender=true) must always redirect to the last page
    	var query = this.store.getQuery();
    	if (!isRender && start >= 0) query['start'] = start;
    	
    	// set sorting parameters (if any)
    	var sortSpec = this.getSortProps();
    	if (sortSpec) {
    		query._sort = sortSpec[0].attribute;
    		query._sortDir = sortSpec[0].descending ? 'desc' : 'asc';
    	}

    	// request next page of data 
    	var store = new ginger.GridStore(
    			{url: this.store.getFetchBaseUrl() + '?' + dojo.objectToQuery(query)});
    	var mainStore = this.store;var grid = this;
    	
		// abort previous request
		if (this.request) this.request.abort();
		
		this._isLoading = true;this._isLoaded = false;
		this.showMessage(this.loadingMessage);
		
		this.request = store.fetch({
			start: 0,
			count: grid.rowsPerPage,
			onComplete: function(items, req) {
    			// initialize our main store if needed
    			mainStore._features['dojo.data.api.Identity'] = store._features['dojo.data.api.Identity'];
    			mainStore._loadFinished = true;
    			mainStore._arrayOfAllItems = [];
    			mainStore._arrayOfTopLevelItems = [];
    			mainStore._itemsByIdentity = [];
    			grid._clearData();
    			
    			console.log('Query ' + dojo.toJson(store.getQuery()));
    			mainStore.setQuery(store.getQuery());
    			grid.searchFilter = store.getQuery();
    			
    			req = {start: 0, count: grid.rowsPerPage,isRender: isRender};
    			// update main store item arrays with just fetched items
    		    dojo.forEach(items, function(item, idx){
    		    	item[mainStore._storeRefPropName] = mainStore;
    		    	item[mainStore._itemNumPropName]  = idx;
    				
    		    	mainStore._arrayOfTopLevelItems.push(item);
    		    	mainStore._arrayOfAllItems[idx]      = item;
    		    	
    		    	var identity = item._ident[0];
    		    	mainStore._itemsByIdentity[identity] = item;
    			});
    		    
    		    grid.rowCount = items.length;
    		    //grid._onFetchBegin(store.totalrowscount, req);
    		    grid.showMessage("");
    		    grid._onFetchComplete(items, req);
    		    grid.scroller.updateRowCount(items.length);
    		    //grid.scroller.invalidateNode();
    		    grid.sizeChange();
    		    grid.restoreSelection();
    		    
    		    var pageCount = Math.ceil(store.totalrowscount / grid.rowsPerPage);
    		    grid.paginator.updatePageCount(pageCount);
    		    
    		    pageIndex = Math.floor(grid.searchFilter['start'] / grid.rowsPerPage);
    		    if (isNaN(pageIndex)) pageIndex = 0;
    		    grid.paginator.selectPage(pageIndex, false);
    		   
    		    if (dojo.isFunction(onComplete)) onComplete();
    		    
    		},
			onError: dojo.hitch(this, "_onFetchError")
		});
		
	},
    
	onFetchError: function(err, req) {
	//	console.error('Fetch error: ' + err.responseText);
    	if (err.status == '401') // not authorized
    		this.showMessage(err.responseText);//TODO: show login dialog
    },
    
    _onFetchComplete: function(items, req) {
    	var firstLoad = !this._isLoaded;
		this.inherited(arguments);
		//if(firstLoad) {
			
		//}
    	this.selection.clear();
		
        //if number of rows >= maxRows, then hide the new button
        if (this.maxRows > 0) {
        	if (this.get('rowCount') >= this.maxRows) {
        		this.buttons['new'].hide();
        	} else {
        		this.buttons['new'].show();
        	}
        }
    },
    
    /*_clearData: function(){
		//this.updateRowCount(0); // otherwise troubles with refreshing -  it will refresh both 0 and last pages
		this._by_idty = {};
		this._by_idx = [];
		this._pages = [];
		this._bop = this._eop = -1;
		this._isLoaded = false;
		this._isLoading = false;
	},*/
    
    /**
     * Deletes all selected items
     */
    deleteItems: function() {
		var item = this.getSelectedItemId();
		if (isEmpty(item)) {
    		alert('Please, select the record for deletion');
			return;
    	}
		var _this = this;
		// create a confirmation dialog
		var dlg = new ginger.ActionDialog({
			message: 'Please, confirm the deletion of this record(s). Deleted record(s) cannot be recovered.',
			onSubmit: function(evt){
				var toDelete = _this.selection.getSelected();
				if (toDelete){
					for (i=0; i< toDelete.length; i++){
						_this.store.deleteItem(toDelete[i]);
					}
				}
				_this.store.save({
					onComplete: function() {
						_this.selection.selected = _this.lastSelected = [];
						_this.refresh();
						dlg.hide();
					},
    				onError: function () {
						console.log('error during saving of record deletion');
						dlg.hide();
					}
				});
            }
		});
    },
    
    getFirstSelectedItem: function() {
    	var items = this.selection.getSelected();
    	return items ? items[0] : null;
    },
    
    getFirstSelectedRow: function() {
    	for (var i in this.selection.selected) {
    		if (this.selection.selected[i])
    			return i;
    	}
    	return null;
    },

    /**
     * Shows dialog to view/edit the selected record
     */
    showDetailed: function() {
    	// check how many rows are selected
    	var selectedRows = 0;
    	for (var i in this.selection.selected) {
    		if (this.selection.selected[i]) selectedRows++; 
    	}
    	
    	// check if there is more than one element selected
    	if (selectedRows > 1) {
    		alert('Please select only one row for editing');
    		return;
    	}
    	
    	// check if there is at least one row selected
    	if (selectedRows == 0) {
    		alert('Please select one row for editing');
    		return;
    	}
    	
    	var item = this.getSelectedItemId();
		if (isEmpty(item)) {
    		alert('Please, select the record for editing');
			return;
    	}
    	if (this.detailedDialog) {
			this.detailedDialog.grid = this;
    		this.detailedDialog.showEdit();
    	}
    },
    
    /**
     * Shows search dialog - creates stores for FilteringSelect items
     */
    showSearch: function() {
		if (this.detailedDialog) {
    		this.detailedDialog.grid = this;
    		this.detailedDialog.showSearch();
    	}
    },
    
    refresh: function(isRender, onComplete) {
    	if (!this.assertPageDirty()) return;
    	if(!isRender) isRender = false;
    	// remember which row where we were on
    	if (!this.scroller) this.createScroller();
    	if (!this.focus) this.createManagers();
    	var page    = this.scroller ? this.scroller.page : null;
    		//var pageTop = this.scroller.pageTop;
    	
    	var row = this.getSelectedItemId();
    	
    	this.store.close();
    	//this._clearData(); - cannot use that otherwise 0 page will be always shown first
		
		this._by_idty = {};
		this._by_idx = [];
		this._pages = [];
		this._bop = this._eop = -1;
		this._isLoaded = false;
		this._isLoading = false;
		
		
    	if (page !== null && page !== undefined) this._pages[page] = true;
    	//if (this.scroller) 
    		this.scroller.rowsPerPage = this.rowsPerPage;
    	//	this.scroller.page = page;
    	//this.scroller.pageTop = pageTop;
    	var start = page ? page * this.scroller.rowsPerPage : null;
    	//alert(start);
    	
    	// refresh parent filter
    	if (this.parent || this.parentId) {
    		var filter = this.store.getQuery();
    		filter._parent = this.parent ? this.parent.getSelectedItemId() : this.parentId;
    		this.store.setQuery(filter);
    	}
    	this._fetch(start, isRender, onComplete);
    	
    	//refresh child grids
    	//console.log('Children : ' + this.getChildren());
    	//this.onSelectionChanged(this.getFirstSelectedRow());
    	dojo.forEach(this.getChildren(), function(grid) {
    		var g = dijit.byId(grid.id);
			if (g && g.domNode) {
	    		if(!g.assertPageDirty()) return;
	    		g.filter({_parent: row}, false);
			}
      	});
    },
    
    sort: function(){
		this.refresh();
	},
    
    assertPageDirty: function() {
    	// if there are any unsaved new/deleted rows, save them first
    	// otherwise grid paging would be corrupted
    	if (!this.store._isEmpty(this.store._pending._newItems) || 
			!this.store._isEmpty(this.store._pending._deletedItems)) {
    		actionDialog.show(
    				'You have unsaved changes in the grid. Please choose one of the options below:',
    				'Save',   dojo.hitch(this, this.save),
    				'Revert', dojo.hitch(this, this.revert));
        	return false;
        }
    	return true;
    },
    
    onStyleRow: function(inRow) {
    	// get grid row by id
    	var row = this._by_idx[inRow.index];
    	if (!row) { // this is because selection.selected keeps empty rows sometimes
    		this.inherited(arguments); 
    		return;
    	}
    	var ident = row.idty;
    	// color red/yellow if errors/warnings
    	if (this.store.getError(ident)) {
    		inRow.customClasses += ' error';
    	}
    	else if (this.store.hasWarnings(ident)) {
    		inRow.customClasses += ' warning';
    	}
    	// color green if the row has unsaved changes 
    	else if (this.store._pending._newItems[ident] ||
    			 this.store._pending._modifiedItems[ident]) {
    		inRow.customClasses += ' unsaved';
    	}
        else inRow.customClasses += '';
        
    	this.inherited(arguments);
    },
    
    revert: function() {
    	this.store.revert();
    },
    
    restoreSelection: function() {
    	this.selection.selected = dojo.clone(this.lastSelected);
    },
    
    selectionChanged: function() {
    	// check if all rows from lastSelected are presented in newSelection 
    	for (var i in this.lastSelected) {
    		if (this.selection.selected[i] == null) return true;
    	}
    	
    	// now check if all rows from newSelection are presented in lastSelection 
    	for (var i in this.selection.selected) {
    		if (this.lastSelected[i] == null) return true;
    	}
    	return false;
    },
    
    onSelectionChanged: function(rowIndex) {
    	if (!this.selectionChanged()) return;
    	
    	//this.lastSelected = dojo.clone(this.selection.selected);
    	var last = [];
    	
    	// get ID's to filter by from selected items
    	var parentFilter = [];
        for (var i in this.selection.selected) {
        	if (this.selection.selected[i] == null) continue;
        	last[i] = true;
        	
        	if (!this._by_idx[i]) continue;
        	
        	var id = this._by_idx[i].item[this.idColumnIndex];
        	
        	if (id < 0) continue; // this is a just created row, not interested
        	parentFilter.push(id);
        }
        
        // if no rows are selected, don't bother with child grids refresh
        if (!last.length) return;
        
        this.lastSelected = last;
       
        // filter child grids
        if (parentFilter.length) {
        	dojo.forEach(this.getChildren(), function(grid) {
        		// make sure there are no unsaved changes in this grid
            	if(!grid.assertPageDirty()) return;
            	grid.filter({_parent: parentFilter}, false);
        	});
        }
    },
    
    getFieldByTitle: function(title) {
    	dojo.forEach(this.grid.layout.cells, function(cell) {
    		if (cell.name == title)
    			return cell.field;
    	});
    	return null;
    },
    
    getSelectedItemId: function(){
    	var selectedItem = this.getFirstSelectedItem();
    	if (selectedItem){
    		return selectedItem[this.idColumnIndex];
    	}
    },
    
    openLink: function(url) {
    	changeBody(url + '?_parent=' + this.getSelectedItemId());
    },
	
	getParentRowId: function () {
		if (this.parentId) 
			return this.parentId;
		else 
			if (this.parent) {
				parentId = this.parent.getSelectedItemId();
				return parentId[0];
			}
	},
	
	checkAll: function() {
		for (var ident in this._by_idty) {
			var storeItem = this._by_idty[ident].item;
			this.store.setValue(storeItem, this.selector, '1');
		}
	},
	
	clearAll: function() {
		for (var ident in this._by_idty) {
			var storeItem = this._by_idty[ident].item;
			this.store.setValue(storeItem, this.selector, null);
		}
	},
	
	_onSet: function(item, attribute, oldValue, newValue){
		/*var idx = this.getItemIndex(item);
		if(idx>-1){
			this.updateRow(idx);
		}*/
	},
	
	onRowDblClick: function(e){
		//if (this.doubleClickEdit)
		//	this.showDetailed();
		var cell;
		 for(var i = 0; i < this.layout.cellCount; i++ ) {
			cell = this.getCell(i);
			if (cell.hidden == true) continue;
			var node = cell.getNode(this.focus.rowIndex);
			var elems = node.childNodes;
			len = elems.length;
			for (var j = 0; j < len; j++) {	
				if (elems[j].tagName == 'A') {
					if (elems[j].innerHTML == 'Edit') {
						var element = elems[j];
						if (document.createEventObject) { // for IE 
							element.click();
						} else if (document.createEvent) { // for W3C-compatible
							setTimeout(unescape(element.href), 1);
						}  
						return;
					}
				}
			}
		 }
	},
	
	onApplyCellEdit: function(inValue, inRowIndex, inFieldIndex){
		var storeItem = this.getItem(inRowIndex);
		this.store.setValue(storeItem, inFieldIndex, inValue);
		this.store.save();
	}
});
        
ginger.Grid.markupFactory = function(props, node, ctor, cellFunc) {
    return dojox.grid.DataGrid.markupFactory(props, node, ctor, cellFunc);
};