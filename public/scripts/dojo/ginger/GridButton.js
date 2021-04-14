dojo.provide("ginger.GridButton");
dojo.require("dijit.form._FormWidget");
dojo.require("dijit._Container");

dojo.declare("ginger.GridButton", [dijit._Widget, dijit._Templated], {
	image: "",
	title: "",
	templatePath:  dojo.moduleUrl("ginger", "templates/GridButton.html"),
	isContainer: true,
	action: "",
	caption: "",
	tooltip: "",
	grid: "",
	top: "",
	
	postMixInProperties: function() {
		var imageDir = dojoDir + '/custom/themes/images/';
		switch (this.action) {
			case 'new':
				this.title = 'Add';
				this.tooltip = this.tooltip || 'add'; //'Add ' + this.caption;
				if (this.image == "") this.image = imageDir + 'icon_addnew.png';
				break;
			case 'edit':
				this.title = 'Edit';
				this.tooltip = this.tooltip || 'edit';
				if (this.image == "") this.image = '/public/images/btn_grey.png';
				break;
			case 'search':
				this.title = 'Search';
				this.tooltip = this.tooltip || 'search'; // 'Search ' + this.caption;
				this.image = imageDir + 'icon_search.png';
				break;
			case 'delete':
				this.title = 'Delete';
				this.tooltip = this.tooltip || 'delete'; //'Delete ' + this.caption ;
				this.image = imageDir + 'icon_delete.png';
				break;
			case 'excel':
				this.title = 'Export';
				this.tooltip = 'EXPORT TO EXCEL';
				this.image = imageDir + 'Excel Export.png';
				break;
			case 'download':
				this.title = 'Download';
				this.tooltip = 'download'; // 'Download ' + this.caption;
				this.image = imageDir + 'icon_download.png';
				break;
		}
		
		this.inherited(arguments);
	},
	
	startup: function() {
		// we need this because buttons are created before the grid	
		if (this.grid != "" && typeof(this.grid) != 'object') this.grid = window[this.grid];
		
		// connect a button and the grid
		this.grid.buttons[this.action] = this;
		
		this.inherited(arguments);
	},
	
	hide: function() {
		dojo.style(this.domNode, {
			visibility:"hidden",
			position:"absolute",
			display:"",
			top:"-9999px"
		});
	},
	
	show: function() {
		dojo.style(this.domNode, {
			visibility:"visible",
			position:"relative",
			display:"block",
			top:"0"
		});
	},
	
	onClick: function(evt) {
		switch (this.action) {
			case 'new':
				this.grid.addNewItem();
				break;
			case 'edit':
				this.grid.showDetailed();
				break;
			case 'search':
				this.grid.showSearch();
				break;
			case 'delete':
				this.grid.deleteItems();
				break;
			case 'excel':
				this.grid.fetchExcel();
				break;
			case 'download':
				this.grid.downloadAll();
				break;
		}
	}
});