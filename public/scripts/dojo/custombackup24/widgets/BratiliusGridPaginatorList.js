dojo.provide("custom.widgets.BratiliusGridPaginatorList");


dojo.declare("custom.widgets.BratiliusGridPaginatorList", [dijit._Widget, dijit._Templated, dijit._Container], {
	templatePath: dojo.moduleUrl("custom", "templates/BratiliusGridPaginatorList.html"),
	grid		: "",
	pageCount	: 0,
	curPage		: null,
    curPageIndex: 0,
    maxPageCount: 6,
    prevNode	: null,
    nextNode	: null,

    startup: function() {
		this.inherited(arguments);
		
		this.maxPageCount = onMobile ? 4 : 6;
		
		// we need this because paginator created before the grid	
		if (this.grid != "" && typeof(this.grid) != 'object') this.grid = window[this.grid];
		
		// connect a paginator and the grid
		this.grid.paginator = this;
		
		// create the pages
		var cnt = this.pageCount; 
		this.pageCount = 0;
		this.updatePageCount(cnt);
		
		if (!this.curPageIndex) {
			this.curPageIndex = 0;
		}
		
		this.refreshPaginatorStyle(this.curPageIndex);
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
		
		var pages = this.getChildren();
		
		//mark currently selected page as active
		for (var i=0; i < this.pageCount; i++){
			var page = pages[i];
			if (i == this.curPageIndex){
				dojo.addClass(page.domNode, 'active');
				break;
			}
		}
	},
	
	refreshPaginatorStyle: function(pageIndex){
		if (pageIndex < 0 || pageIndex >= this.pageCount || isNaN(pageIndex))
			return;
		
		var pages = this.getChildren();

		this.curPageIndex = pageIndex;
		this.curPage = pages[pageIndex];
		
		dojo.addClass(this.curPage.domNode, 'active');
		
		var leftBorder = this.curPageIndex -  this.maxPageCount / 2;
		var rightBorder = this.curPageIndex +  this.maxPageCount / 2;
		
		if (leftBorder < 0){
			leftBorder = 0;
		}
			
		if (rightBorder >= this.pageCount){
			rightBorder = this.pageCount - 1;
		}
			
		//if number of pages too big, show only pages, close to currently selected
		var parent = this;
		dojo.forEach(this.getChildren(), function(page) {
			if (page.index < leftBorder || page.index > rightBorder){			
				pages[page.index].domNode.style.display = 'none';	
			}		
			else{
				pages[page.index].domNode.style.display = 'inline';
			}
				
		});
		
		// hide prev and next buttons if needed
		if (pageIndex == 0)
			this.prevNode.style.display = 'none';
		else
			this.prevNode.style.display = 'inline';
		
		if (pageIndex == this.pageCount - 1)
			this.nextNode.style.display = 'none';
		else
			this.nextNode.style.display = 'inline';
	},
	

	//Load page if refresh=true
	selectPage: function(/*Integer*/pageIndex, /*Boolean*/refresh) {
		if (pageIndex < 0 || pageIndex >= this.pageCount || isNaN(pageIndex))
			return;
		
		if (refresh) this.grid.goToPage(pageIndex);
			
	},
	
	click10: function(evt) {
		this.selectPage(this.curPageIndex, true);
	},
	
	click20: function(evt) {
		this.selectPage(this.curPageIndex, true);
	},
	click50: function(evt) {
		this.selectPage(this.curPageIndex, true);
	},
	
	click100: function(evt) {
		this.selectPage(this.curPageIndex, true);
	}
});