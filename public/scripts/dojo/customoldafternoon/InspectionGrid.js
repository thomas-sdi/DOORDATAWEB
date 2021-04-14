dojo.provide("custom.InspectionGrid");
dojo.require("ginger.Grid");
dojo.require("custom.widgets.BratiliusGrid");

dojo.declare("custom.InspectionGrid", custom.widgets.BratiliusGrid, {
	

	
	onSelectionChanged: function(rowIndex) {
		this.inherited(arguments);
		this.refreshButtons();
	},
	
	restoreSelection: function() {
		this.inherited(arguments);
		this.refreshButtons();
	},
	
	refreshButtons: function() {
		// get inspection status
		var item = this.getFirstSelectedItem();
		if (!item) return; // new inspection just created
		var s = item.status_ID;
		
		// get 'new' button from door grid - first child
		var doorGrid = this.getDoorGrid();
		

		// if new or incomplete - allow creating new doors
		if (s == '1080' || s == '1383') {
			doorGrid.buttons['new'].show();
		}
		else {
			doorGrid.buttons['new'].hide();
		}
	},
	
	showNewDialog: function(parent){
		var hasParams = this.detailedView.indexOf('?') !== -1;
		
		var url = baseUrl + this.detailedView + (hasParams ? '&' : '?') + '_parent=-1';
		if (parent){
			url += '&_super=' + parent;
		}
		//changeBody(url, false, true);
		
		bratiliusDialog.frame.attr('href', url);
		bratiliusDialog.parentPane = this.parentPane;
		bratiliusDialog.show();
	},
	
	getDoorGrid: function() {
		var children = this.getChildren();
		return children[0];
	},
	
	assignInspection: function() {	    
	    	var grid   = this;
	    	//var filter = grid.store.getQuery();
	        dojo.xhrGet({
	        	url: baseUrl + '/inspection/xml?_id=' + grid.getSelectedItemId(),
	            //content: filter,
				load: function(response){
					if (response == 'ok')
						document.getElementById("lblInfo").innerHTML = 'Inspection successfully assigned';
					else
						document.getElementById("lblInfo").innerHTML = response;
					},
	            handle: function(data, args) {
	            	grid.refresh();
	            }
	        });
			document.getElementById("lblInfo").innerHTML = 'Assign in progress, please wait....';
			dijit.byId("dialogSubmit").show();
	},

	unlockInspection: function(id) {
	    	var grid   = this;
	    	//var filter = grid.store.getQuery();
	        dojo.xhrGet({
	        	url: baseUrl + '/inspection/unlock?_id=' + id,
	            //content: filter,
				load: function(response){
					if (response == 'ok')
						document.getElementById("lblInfo").innerHTML = 'Inspection successfully assigned';
					else
						document.getElementById("lblInfo").innerHTML = response;
					},
	            handle: function(data, args) {
	            	grid.refresh();
	            }
	        });
			document.getElementById("lblInfo").innerHTML = 'Unlock in progress, please wait....';
			dijit.byId("dialogSubmit").show();
	}
	
});

custom.InspectionGrid.markupFactory = function(props, node, ctor, cellFunc) {
    return ginger.Grid.markupFactory(props, node, ctor, cellFunc);
};