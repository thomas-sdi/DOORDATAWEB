dojo.provide("ginger.GridStore");

dojo.declare("ginger.GridStore", dojo.data.ItemFileWriteStore, {
    
    saveUrl:       "",
    model:         "",
    idColumnIndex: "",
    grid:          null,
    
    constructor: function(args) {
		if (args) {
			this.grid = args.grid;
			if (this.grid) {
				var gridId = this.grid.getId();
				
				// save and fetch URL's
				this._jsonFileUrl = this.grid.controller + '/fetch?_model=' + gridId;
				this.saveUrl      = this.grid.controller + '/save';
				if (this.grid.parentId) {
					this._jsonFileUrl += '&_parent=' + this.grid.parentId;
				}
				
				this.model = gridId;
				this.clearOnClose = true;
				this.idColumnIndex = this.grid.idColumnIndex;
				this.id = 'store_' + gridId;
			}
			else {
				this.saveUrl       = args.saveUrl;
				this._jsonFileUrl  = args.url;
				this.model         = args.model;
				this.idColumnIndex = args.idColumnIndex;
				this._features['dojo.data.api.Identity'] = '_ident';
			}
			
			this.problems = {errors: null, warnings: null};
		}
    },
    
    getQuery: function() {
    	var url = this._jsonFileUrl;
        var queryMark = url.indexOf('?');
        return (queryMark == -1 ) ? new Object() : dojo.queryToObject(url.substr(queryMark + 1));
    },
    
    getFetchBaseUrl: function() {
    	var url = this._jsonFileUrl;
        var queryMark = url.indexOf('?');
        return (queryMark == -1 ) ? url : url.substr(0, queryMark);
    },
    
    getBaseUrl: function() {
    	var url = this.getFetchBaseUrl();
    	return url.substr(0, url.length - 6); // cuts off /_fetch
    },
    
    setQuery: function(query) {
    	// remove all null attributes from query
    	var newQuery=[];
    	for(var attr in query) {
    		if (query[attr] == null)
    			continue;
    		newQuery[attr] = query[attr];
    	}
    	
    	var url = this._jsonFileUrl;
    	var queryMark = url.indexOf('?');
    	var queryStr = dojo.objectToQuery(newQuery);
    	this._jsonFileUrl = this.getFetchBaseUrl() + '?' + queryStr;
    },
    
    getWarning: function(itemId) {
    	if (this.problems.warnings && this.problems.warnings[itemId])
    		return this.problems.warnings[itemId];
    	else
    		return null;
    },
    
    hasWarnings: function(itemId, countIgnored) {
    	// check if there are any problems at all
    	if (!this.problems.warnings || !this.problems.warnings[itemId])
    		return false;
    	// there are some problems, check if we're concerned with ignored ones
    	else if (countIgnored) return true;
    	
    	// check if at least one warning was not ignored
    	for (var warning in this.problems.warnings[itemId]) {
    		if (!this.isIgnored(itemId, warning))
    			return true;
    	}
    	
    	return false;
    },
    
    getError: function(itemId) {
    	if (this.problems.errors && this.problems.errors[itemId]) {
    		return this.problems.errors[itemId];
    	}
    	else
    		return null;
    },

    _saveCustom: function(saveCompleteCallback, saveFailedCallback) {
        var changeSet = new Object();
        changeSet.newItems = new Array();
        changeSet.changedItems = new Array();
        changeSet.deletedItems = new Array();
		
		// get parent id (if any)
		var parentId = this.grid ? this.grid.getParentRowId() : null;
		
        // collect new items
        if (!this._isEmpty(this._pending._newItems)) {
            for (var identity in this._pending._newItems) {
                var added = new Object();
                var item = this._itemsByIdentity[identity];
                var ident = this._pending._newItems[identity];
                if (!item) // this means a row was first added, then deleted
                    continue;
                var attrs = this.getAttributes(ident);
                for (attrId in attrs) {
                	if (attrs[attrId] == this.idColumnIndex && // no need to report id
						this.getValue(ident, attrs[attrId]) < 0 ) // unless it is an existing record hacked here
                        continue;
                	
                    var newVal = this.getValue(ident, attrs[attrId]);
                    
                    // ignore empty values
                    if (newVal == null || newVal == "") continue;
                    
                    // format dates to ISO
                    if(newVal instanceof Date) {
                        newVal = dojo.date.stamp.toISOString(newVal, {selector: "date"});
                    }
                    
                    added[attrs[attrId]] = newVal;
                }
                if (this.problems.ignored && this.problems.ignored[identity])
                	added._ignored = this.problems.ignored[identity];
					
				if (parentId) added._parent = parentId;
                changeSet.newItems.push( added );
            }
        }
        
        // collect modified items
        if (!this._isEmpty(this._pending._modifiedItems)) {
            for (var identity in this._pending._modifiedItems) {
            	if(identity < 0) // this means the row was just created, all data is alredy in _newItems
            		continue;
                var changed = new Object();
                var empty = true; // check if there were actually any changes except to ID
                
                var item = this._itemsByIdentity[identity];
                if (!item ) // this means a row was first modified, then deleted
                    continue;
                var attrs = this.getAttributes(item);
                if (this.getValue(item, this.idColumnIndex) < 0 ) continue; // ignores case when default values are modified
                for (attrId in attrs) {
                    if (attrs[attrId] == this.idColumnIndex) // this is internal ID update process, no need to save anything on server
                        continue;
                    var newVal = this.getValue(item, attrs[attrId]);
                    var ident = this._pending._modifiedItems[identity];
                    var oldVal = ident[attrs[attrId]];
					
                    // format dates to ISO
                    if(newVal instanceof Date) {
                        newVal = dojo.date.stamp.toISOString(newVal, {selector: "date"});
                        oldVal = dojo.date.stamp.toISOString(new Date(oldVal+""), {selector: "date"});
                    }
                    /*else if(newVal === false || newVal === true || newVal == 'false' || newVal == 'true'){
                    	if (oldVal == 'false') oldVal = false;
                    	if (oldVal == 'true')  oldVal = true;
                    	if (newVal == 'false') newVal = false;
                    	if (newVal == 'true')  newVal = true;
                    }*/
                    
                    // compare for differences
					//console.log('Attr: ' + attrs[attrId] + ', old val: ' + oldVal + ', new val: ' + newVal);
                    if (!equal(newVal,oldVal)) {
                        changed[attrs[attrId]] = newVal;
                        empty = false;
                    }
                }
                if (empty) // no attributes have been changed (only ID - for new rows)
                    continue;
                    
                // add ID and ident attributes - required for server processing
                changed._ID    = this.getValue(item, this.idColumnIndex);
                changed._ident = identity;
                if (this.problems.ignored && this.problems.ignored[identity])
                	changed._ignored = this.problems.ignored[identity];
				if (parentId) changed._parent = parentId;
                changeSet.changedItems.push( changed );
            }
        }
        
        // collect deleted items
        if (!this._isEmpty(this._pending._deletedItems)) {
            for (var identity in this._pending._deletedItems) {
            	var id = this._pending._deletedItems[identity][this.idColumnIndex][0];
                if (id < 0) // newly added row was deleted, ignore
                    continue;
                deleted = {_ID: id, _ident: identity};
                if (this.problems.ignored && this.problems.ignored[identity])
                   	deleted._ignored = this.problems.ignored[identity];
				if (parentId) deleted._parent = parentId;
                changeSet.deletedItems.push(deleted);
                console.log('pushing item: ' + dojo.toJson(deleted));
            }
        }
        if(changeSet.newItems.length     == 0 &&
           changeSet.changedItems.length == 0 &&
           changeSet.deletedItems.length == 0) { // no changes made
           console.log('no changes made');
            saveCompleteCallback();
        }
        else {
            console.log('changeSet: ' + dojo.toJson(changeSet));
            
            // define local variables as 'this' will be nonsense within function being defined below
            var myStore       = this;
            var idColumnIndex = this.idColumnIndex;
            
            // post resulted changeset to the server and wait for response
            var cont = {json: dojo.toJson(changeSet), _model: this.model};
			if (parentId) cont._parent = parentId;
            if (this.force) cont._force = true;
            dojo.xhrPost({
                url: this.saveUrl,
                content: cont,
                handle: function (data, args) {
                    console.log(data);
                    
                    var response = dojo.fromJson(data);
                    
                    // check if any problems
                    if (response && (response.errors||response.warnings)) {
                        myStore.problems.errors   = response.errors;
                        myStore.problems.warnings = response.warnings;
                        myStore.revertDeleted();
                        saveFailedCallback();
                        return;
                    }
                    // no problems, proceed
                    myStore.problems = {errors: null, warnings: null};

                    // now refresh the grid
                    //myStore.grid.refresh();
                    saveCompleteCallback();
                    
                    // check for new ID's for just created rows and update store
/*
				** not relevant - grid is updated anyway ** 
                      if (response && response.created) {
                        for (var id in response.created) {
                            var item = myStore._itemsByIdentity[id];
                            myStore.setValue(item, idColumnIndex, response.created[id]);
                        }
                        myStore.save();
                    }*/
                }
            });
        }
    },
    
    // reverts back all pendingDeleted items (taken 100% from ItemFileWriteStore.revert)
    // it is important because if a delete was unsuccessful, a user should see an error and the item 
    revertDeleted: function() {
    	var deletedItem;
		for(identity in this._pending._deletedItems){
			deletedItem = this._pending._deletedItems[identity];
			deletedItem[this._storeRefPropName] = this;
			var index = deletedItem[this._itemNumPropName];

			//Restore the reverse refererence map, if any.
			if(deletedItem["backup_" + this._reverseRefMap]){
				deletedItem[this._reverseRefMap] = deletedItem["backup_" + this._reverseRefMap];
				delete deletedItem["backup_" + this._reverseRefMap];
			}
			this._arrayOfAllItems[index] = deletedItem;
			
			if (this._itemsByIdentity) {
				this._itemsByIdentity[identity] = deletedItem;
			}
			if(deletedItem[this._rootItemPropName]){
				this._arrayOfTopLevelItems.push(deletedItem);
			}	  
		}
		//We have to pass through it again and restore the reference maps after all the
		//undeletes have occurred.
		for(identity in this._pending._deletedItems){
			deletedItem = this._pending._deletedItems[identity];
			if(deletedItem["backupRefs_" + this._reverseRefMap]){
				dojo.forEach(deletedItem["backupRefs_" + this._reverseRefMap], function(reference){
					var refItem;
					if(this._itemsByIdentity){
						refItem = this._itemsByIdentity[reference.id];
					}else{
						refItem = this._arrayOfAllItems[reference.id];
					}
					this._addReferenceToMap(refItem, deletedItem, reference.attr);
				}, this);
				delete deletedItem["backupRefs_" + this._reverseRefMap]; 
			}
			this.onNew(deletedItem);
		}
    },
    
    ignoreWarning: function(ident, warningId) {
    	if (!this.problems.ignored) this.problems.ignored = [];
    	if (!this.problems.ignored[ident]) this.problems.ignored[ident] = [];
    	
    	var l = this.problems.ignored[ident].length; 
    	this.problems.ignored[ident][l] = warningId;
    },
    
    isIgnored: function(ident, warningId) {
    	if (this.problems.ignored ) {
    		for (var i in this.problems.ignored[ident]) {
    			if (this.problems.ignored[ident][i] == warningId)
    				return true;
    		}
    	}
    	return false;
    },
    
    revert: function() {
    	this.problems = {errors: null, warnings: null};
    	this.inherited(arguments);
    },
    
    // called when any item is modified 
    onSet: function(item, attribute, oldValueOrValues, newValueOrValues) {
    	// we don't need to keep _modifiedItems info if _newItem already contains it
    	// if we keep them together, revert does not work correctly
    	if (this._pending._newItems && this._pending._newItems[item._ident]) {
    		var mod = new Object();
    		for (var i in this._pending._modifiedItems) {
    			if (i == item._ident) continue;
    			mod[i] = this._pending._modifiedItems[mod];
    		}
    		this._pending._modifiedItems = mod;
    	}
    },
    
    _getItemsFromLoadedData: function(data) {
    	this.totalrowscount = data._count;
    	this.inherited(arguments);
    }
});