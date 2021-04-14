dojo.provide("ginger.GridPaginator");
dojo.provide("ginger.GridPaginatorPage");

dojo.declare("ginger.GridPaginator", [dijit._Widget, dijit._Templated, dijit._Container], {
	templatePath: dojo.moduleUrl("ginger", "templates/GridPaginator.html"),
	grid		: "",
	pageCount	: 0,
	curPage		: null,
    curPageIndex: 0,
    maxPageCount: 16,

    
    startup: function() {
		this.inherited(arguments);
		
		// we need this because paginator created before the grid	
		if (this.grid != "" && typeof(this.grid) != 'object') this.grid = window[this.grid];
		
		console.log('Paginator ' + this.grid.paginator);
		
		// connect a paginator and the grid
		this.grid.paginator = this;
		
	},
    
	// Sets the number of pages and redraws paginator
	updatePageCount: function(newPageCount) {
		if (newPageCount < 0) {
			return;
		}
		
		var countDiff = newPageCount - this.pageCount;
		
		if (countDiff == 0)
			return;
		
		if (countDiff > 0) {
			//add extra pages
			for (var i = this.pageCount; i < newPageCount; i++) {
				var page = new ginger.GridPaginatorPage({text: i+1, index: i});
				this.addChild(page, i);
			}
		} 
		
		if (countDiff < 0) {
			//remove pages
			var pages = this.getChildren();
			for (var i = this.pageCount-1; i >= newPageCount; i--) {
				var page = pages[i];
				this.removeChild(page);
				page.destroyRecursive();
			}
		}
		
		this.pageCount = newPageCount;
		if (this.pageCount == 0) {
			this.prevNode.style.visibility = 'hidden';
			this.nextNode.style.visibility = 'hidden';
		}
	},
	
	//Select and load page if refresh=true
	selectPage: function(/*Integer*/pageIndex, /*Boolean*/refresh) {
		if (pageIndex < 0 || pageIndex >= this.pageCount || isNaN(pageIndex))
			return;
		
		var pages = this.getChildren();
		this.curPage = pages[this.curPageIndex];
		if (this.curPage != undefined && this.curPage != null)
		dojo.removeClass(this.curPage.domNode, 'selected');
		
		this.curPageIndex = pageIndex;
		this.curPage = pages[pageIndex];
		dojo.addClass(this.curPage.domNode, 'selected');
		
		var leftBorder = this.curPageIndex -  this.maxPageCount / 2;
		var rightBorder = this.curPageIndex +  this.maxPageCount / 2;
		
		if (leftBorder < 0)
			rightBorder = this.maxPageCount;
		if (rightBorder >= this.pageCount)
			leftBorder = this.pageCount - this.maxPageCount;
		
		//if number of pages too big, show only pages, close to currently selected
		var parent = this;
		dojo.forEach(this.getChildren(), function(page) {
			if (page.index < leftBorder ||
				page.index > rightBorder)
				page.domNode.style.display = 'none';
			else
				page.domNode.style.display = '';
		});
		
		// hide prev and next buttons if needed
		if (pageIndex == 0)
			this.prevNode.style.visibility = 'hidden';
		else
			this.prevNode.style.visibility = 'visible';
		
		if (pageIndex == this.pageCount - 1)
			this.nextNode.style.visibility = 'hidden';
		else
			this.nextNode.style.visibility = 'visible';
		
		if (refresh) this.grid._fetchPage(pageIndex);
			
	},
	
	_onMouseOver: function(evt) {
		dojo.addClass(evt.target, 'over');
	},
	
	_onMouseOut: function(evt) {
		dojo.removeClass(evt.target, 'over');
	},
	
	prevClick: function(evt) {
		this.selectPage(this.curPageIndex - 1, true);
	},
	
	nextClick: function(evt) {
		this.selectPage(this.curPageIndex + 1, true);
	}
});

dojo.declare("ginger.GridPaginatorPage", [dijit._Widget, dijit._Templated, dijit._Contained], {
	templatePath: dojo.moduleUrl("ginger", "templates/GridPaginatorPage.html"),
	text	: 'null',
	index	: 0,
	
	_onMouseOver: function(evt) {
		dojo.addClass(this.domNode, 'over');
	},
	
	_onMouseOut: function(evt) {
		dojo.removeClass(this.domNode, 'over');
	},
	
	_onClick: function(evt) {
		var parent = this.getParent();
		parent.selectPage(this.index, true);
	}
});