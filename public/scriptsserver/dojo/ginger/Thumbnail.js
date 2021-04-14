dojo.provide("ginger.Thumbnail");
dojo.declare("ginger.Thumbnail", [dijit._Widget, dijit._Templated], {
	imageSrc: "",
	title:    "",
	ident: 	  "",
	parent:  null,
	templatePath:  dojo.moduleUrl("ginger", "templates/Thumbnail.html"),
	imgNode: null,
	_skipNodeCache: true,
	onLoadCallback: null,
	
	_onClick: function(evt) {
		return this.parent._onThumbnailClick(evt, this);
	},
	
	getIdent: function() {
		return this.ident;
	},
	
	_onImageLoad: function(evt) {
		this.parent._onThumbnailLoad(evt, this);
		this.onLoadCallback();
	}
});