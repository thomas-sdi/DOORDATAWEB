dojo.provide("ginger.ThumbnailPicker");
dojo.require('dojox.image.ThumbnailPicker');
dojo.require("ginger.Thumbnail");

dojo.declare("ginger.ThumbnailPicker", [ginger.Component, dojox.image.ThumbnailPicker], {
	
	parent:         '',
	parentId:       "",
	store: null,
	imageThumbAttr: "THUMBNAIL",
	titleAttr: "BANKNAME",
	identAttr: "_ident",
	controller: "",
	idColumnIndex: "",
	thumbHeight: '72',
	thumbWidth: '72',
	selectedId: null,
	selectedItem: null,
	rowCount: null,
	maxRows: "",
	isAutosave: false,
	
	lastSelected:   [],
	detailedDialog: null,
	detailedView: 	"",
	searchView: 	"",
	detailedTitle:	"",
	shortTitle:		false,
	//inline:         "",
	//selector:       "",
	buttons:        [],
	numbered:       "",
    searchFilter:   [],
	
	postCreate: function() {		
		// create a new store and attach the widget to it
		//var store = new ginger.GridStore({grid: this});
		this.store = new ginger.GridStore({grid: this});
		this.setDataStore(this.store, {count: 25, start: 0}, {imageThumbAttr: this.imageThumbAttr});
		
	    this.inherited(arguments);
	    
		if (this.detailedDialog) this.detailedDialog.grid = this;
	},
	
	_loadImage: function(data, index, callback){	
    	// get thumbnail URL and title
    	//alert('_loadImage '+index);
    	var url = this.imageStore.getValue(data, this.imageThumbAttr);
    	var title = this.imageStore.getValue(data, this.titleAttr);
    	var id = this.imageStore.getValue(data, this.identAttr);
		var thumbnail = new ginger.Thumbnail({imageSrc: url, title: title});
		//var thumbnail = new ginger.Thumbnail();
		var imgContainer = thumbnail.domNode;
		var img = thumbnail.imgNode;
		
		/*var img = document.createElement("img");
		var imgContainer = document.createElement("div");
		imgContainer.setAttribute("id","img_" + this.widgetid+"_"+index);
		imgContainer.appendChild(img);
		img._index = index;
		img._data = data;*/
	
		this._thumbs[index] = imgContainer;
		var loadingDiv;
		if(this.useLoadNotifier){
			loadingDiv = document.createElement("div");
			loadingDiv.setAttribute("id","loadingDiv_" + this.widgetid+"_"+index);
	
			//If this widget was previously told that the main image for this
			//thumb has been loaded, make the loading indicator transparent.
			this._setThumbClass(loadingDiv,
				this._loadedImages[index] ? "thumbLoaded":"thumbNotifier");
	
			imgContainer.appendChild(loadingDiv);
		}
		var size = dojo.marginBox(this.thumbsNode);
		var defaultSize;
		var sizeParam;
		if(this.isHorizontal){
			defaultSize = this.thumbWidth;
			sizeParam = 'w';
		} else{
			defaultSize = this.thumbHeight;
			sizeParam = 'h';
		}
		size = size[sizeParam];
		var sl = this.thumbScroller.scrollLeft, st = this.thumbScroller.scrollTop;
		dojo.style(this.thumbsNode, this._sizeProperty, (size + defaultSize + 20) + "px");
		//Remember the scroll values, as changing the size can alter them
		this.thumbScroller.scrollLeft = sl;
		this.thumbScroller.scrollTop = st;
		this.thumbsNode.appendChild(imgContainer);		
		
		dojo.connect(imgContainer, 'onclick', this, function() {
			dojo.forEach(this._thumbs, function(thumb) {
				if (thumb) dojo.removeClass(thumb, 'thumbSelected');
			});
			dojo.addClass(imgContainer, 'thumbSelected');
			this.selectedId = id;
			dojo.forEach (this.getDependent(), function(child){
				var g = dijit.byId(child.id);
				if (g && g.domNode) g.refresh();
			});
		});
	
		dojo.connect(img, "onload", this, function(){
			var realSize = dojo.marginBox(img)[sizeParam];
			this._totalSize += (Number(realSize) + 4);
			dojo.style(this.thumbsNode, this._sizeProperty, this._totalSize + "px");
	
			if(this.useLoadNotifier){
				dojo.style(loadingDiv, "width", (img.width - 4) + "px"); 
			}
			dojo.style(imgContainer, "width", img.width + "px");
			callback();
			return false;
		});
	
		dojo.connect(img, "onclick", this, function(evt){
			dojo.publish(this.getClickTopicName(),	[{
				index: evt.target._index,
				data: evt.target._data,
				url: img.getAttribute("src"), 
				largeUrl: this.imageStore.getValue(data,this.imageLargeAttr),
				title: this.imageStore.getValue(data,this.titleAttr),
				link: this.imageStore.getValue(data,this.linkAttr)
			}]);
			return false;
		});
		dojo.addClass(img, "imageGalleryThumb");
		img.setAttribute("src", url);
		var title = this.imageStore.getValue(data, this.titleAttr);
		if(title){ img.setAttribute("title",title); }
		this._updateNavControls();
	
	},
	
	getSelectedItemId: function() {
		return this.selectedId;
	},
	
	getFirstSelectedItem: function() {
		return this.store._itemsByIdentity[this.selectedId];
	},
	
	getParentRowId: function () {
		if (this.parentId) 	  return this.parentId;
		else if (this.parent) return this.parent.getSelectedItemId();
	},
	
	save: function(force) {
		//this.edit.apply();
    	this.store.save({
    		onComplete: dojo.hitch(this, "onSaveComplete"),
    		onError:    dojo.hitch(this, "onSaveFailed")}
    	);
    },
    
    onSaveComplete: function() {
		// hide dialog (if it was shown)
		if (this.detailedDialog && this.detailedDialog.open) this.detailedDialog.hide();
		this.refresh();
    },
    
    onSaveFailed: function() {
    	// show the edit dialog with the error
    	if (this.detailedDialog && this.detailedDialog.open) {
			this.detailedDialog.showProblems();
		} 
    },
    
    refresh: function(isRender, onComplete) {
    	// update grid
    	this.store = new ginger.GridStore({grid: this});
    	this.setDataStore(this.store, {count: 25, start: 0}, {imageThumbAttr: this.imageThumbAttr});
		
		// unselect all items
    	dojo.forEach(this._thumbs, function(thumb) {
    		if (thumb) dojo.removeClass(thumb, 'thumbSelected');
		});
    	this.selectedId = null;
		
    	// update children
		dojo.forEach (this.getDependent(), function(child){
			var g = dijit.byId(child.id);
			if (g && g.domNode) g.refresh();
			//if (child) child.refresh();
		});
		
		// call callback function if specified
		if (onComplete) onComplete();
    },
    
    revert: function() {
    	this.store.revert();
    },
	
	addNewItem: function() {
		//this.maxRows			 = '<?= $grid->getMaxRows() ?>';
		var idColumnIndex = this.idColumnIndex;
		if( !this.newItemsCount ) this.newItemsCount = 0;
		
        // add new item to the store populating fields with designated parent values
        var newItem = {_ident: (-1)*(++this.newItemsCount)};
        
        this.selectedId = newItem._ident;
        
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
        /*dojo.forEach(this.layout.cells, function(cell){
        	//if (cell.hidden == true) return;
        	newItem[cell.field] = (cell.defaultValue == '...' ? '' : cell.defaultValue);
        	//newItem[cell.field] = cell.defaultValue || '...';
        	
        	// fix empty date representation  
        	if (cell.formatter == formatDate && newItem[cell.field] == "")
        		newItem[cell.field] = null;
        });*/
		
        // ID column must be added always
        if (!newItem[idColumnIndex])
            newItem[idColumnIndex] = (-1)*(this.newItemsCount);
        
        if (!this.store._arrayOfAllItems) 	  this.store._arrayOfAllItems = [];
		if (!this.store._arrayOfTopLevelItems) this.store._arrayOfTopLevelItems = [];
		if (!this.store._itemsByIdentity) 	  this.store._itemsByIdentity = [];
        
		if (!this.rowCount) this.rowCount = 1;
		else ++this.rowCount;
		
        // create new record in datastore
        this.store._arrayOfAllItems.length = this.rowCount;
        
        this.store.newItem(newItem);
        
        // scroll to show this row
        //this.scrollToRow(this.rowCount);
        
        // select the row and open edit dialog
        //this.selection.deselectAll();
        //this.selection.addToSelection(this.rowCount-1);
		
        //if number of rows >= maxRows, then hide the new button
        if (this.maxRows > 0) {
        	if (this.rowCount >= this.maxRows) {
        		this.buttons['new'].hide();
        	} 
        }
        
       	if (!this.inline) this.showDetailed();
    },
    
    /**
     * Shows dialog to view/edit the selected record
     */
    showDetailed: function() {
    	if (this.detailedDialog) {
			this.detailedDialog.grid = this;
    		this.detailedDialog.showEdit();
    	}
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
        
    
    /**
     * Deletes all selected items
     */
    deleteItems: function() {
		var _this = this;
		// create a confirmation dialog
		var dlg = new ginger.ActionDialog({
			message: 'Пожалуйcта, подтвердите удаление этой записи',
			onSubmit: function(evt){
				_this.store.deleteItem(_this.getFirstSelectedItem());
				_this.store.save({
					onComplete: function() {
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
    }
});