dojo.provide("custom.GridButton");
dojo.require("dijit.form._FormWidget");
dojo.require("dijit._Container");

dojo.declare("custom.GridButton", [dijit._Widget, dijit._Templated], {
	image: "",
	action: "",
	tooltip: "",
	caption: "",
	grid: "",
	top: "",
	className: "",
	tdNode: null,
	templatePath:  dojo.moduleUrl("custom", "templates/GridButton.html"),
	actions: {
		'new': 'addNewItem', 
		'search': 'search'
	},
	
	postMixInProperties: function() {
		this.inherited(arguments);
		var imageDir = dojoDir + '/custom/themes/images/';
		switch (this.action) {
			case 'new':
				this.title = 'NEW';
				this.tooltip = 'NEW ' + this.caption;
				if (this.image == "") this.image = imageDir + 'window_new.png';
				break;
			case 'search':
				this.title = 'SEARCH'
				this.tooltip = 'SEARCH ' + this.caption;
				this.image = imageDir + 'Search Icon.png';
				break;
			case 'delete':
				this.title = 'DELETE';
				this.tooltip = 'DELETE ' + this.caption ;
				this.image = imageDir + 'icon_delete.png';
				break;
			case 'excel':
				this.title = 'EXPORT';
				this.tooltip = 'EXPORT TO EXCEL';
				this.image = imageDir + 'Excel Export.png';
				break;
			case 'download':
				this.title = 'DOWNLOAD';
				this.tooltip = 'DOWNLOAD ' + this.caption;
				this.image = imageDir + 'icon_download.png';
				break;
		}
	},
	
	startup: function() {
		// we need this because buttons are created before the grid	
		if (this.grid != "" && typeof(this.grid) != 'object') this.grid = window[this.grid];
		
		// connect a button and the grid
		this.grid.buttons[this.action] = this;
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