dojo.provide("ginger.form.ValidationForm");
dojo.require("dijit.form.Form");
dojo.require("ginger.form.SubmitButton");
dojo.require("ginger.form.CancelButton");
dojo.require("dijit._Container");

dojo.declare("ginger.form.ValidationForm", [dijit.form.Form, dijit._Container], {
	baseClass: 'ginger.form.ValidationForm',
	problems: [],
	recaptcha: false,
	resultsWidget: null,
	parent: null,
	submitButton: null,
	cancelButton: null,
	iframe: false,
	jsonp: false,
	afterSubmit: null,
	dialog: null,
	widgets: {},	// array of name => widget
	
	startup: function() {
		this.inherited(arguments);
		
		if (this.encType == 'multipart/form-data') this.iframe = true;
		
		// find parent (usually a dialog)
		this.parent = dijit.byNode(this.domNode.parentNode);
		if (!this.parent) this.parent = dijit.byNode(this.domNode.parentNode.parentNode);
		
		// find submitButton
		if (dijit.byId(this.id + '_submit'))
			this.submitButton = dijit.byId(this.id + '_submit');
		
		//if form has recaptcha
		if (dojo.byId('recaptcha'))
			this.recaptcha = true;
		
		var form = this;
		dojo.forEach(this.getDescendants(), function(widget) {
			widget.form = form;
			dojo.connect(widget, 'onKeyPress', dojo.hitch(widget, form.onWidgetKeyPress));
		});
		
		dojo.connect(this.domNode, 'onkeypress', dojo.hitch(this, this.keyPressed));
	},

	onWidgetKeyPress: function(e) {
		var widget = this;
		if (e.keyCode == dojo.keys.ENTER) {
			if (widget.focusNode && dojo.byId(e.target).tagName.toLowerCase() != 'textarea') //we are supporting multiline textareas
				widget.focusNode.blur();
		}
	},
	
	keyPressed: function(e) {
	//console.log('Key pressed ' + e.keyCode);
	/*if (e.keyCode == dojo.keys.ENTER) {
			this.submit(this.afterSubmit);
		}*/
	},
	
	_loadHandler: function(response, ioArgs){
		// hide in-progress button
		/*if (_this.submitBtn && _this.submitBtn.src && _this.parent == mainDialogFrame) {
			_this.submitBtn.src = baseUrl + '/public/images/submit_btn.png';
			_this.submitBtn.onclick = _this.submitBtn.oldOnClick;
			_this.submitBtn.oldOnClick = null;
		}*/
		if (this.submitButton) this.submitButton.setDisabled(false);
		
		response = response || {};
		
		if (response && typeof(response) != 'object')
			response = dojo.fromJson(response);
		/*if (this.iframe == true || this.jsonp == true) {
			if (response && !response.href) {
				return this._errorHandler(response, ioArgs);
			}
		}*/
		
		if (response && response.problems) {
			return this._errorHandler(response, ioArgs);
		} //else
		//	return this._errorHandler(response, ioArgs);
		
		// if we were told to be redirected, do it
		if (response && response.href) {
			var all = response.global;
			if (all)
				url = '/index/home?form=' + response.href;
			else
				url = response.href;
			
			return changeBody(url, false, all);
		} 
		else {
			if (dojo.isFunction(this.afterSubmit)) this.afterSubmit();
			
			// perform action if any is assigned
			if (this.parent && this.parent.action) {
				this.parent.action(response);
				this.parent.action = null;
			}
		}

		this.onSubmitSuccess(response);
		return response;
	},
	
	_errorHandler: function(response, ioArgs) {
		// hide in-progress button
		/*if (_this.submitBtn && _this.submitBtn.src && _this.parent == mainDialogFrame) {
			_this.submitBtn.src = baseUrl + '/public/images/submit_btn.png';
			if (_this.submitBtn.oldOnClick != null)
				_this.submitBtn.onclick = _this.submitBtn.oldOnClick;
			_this.submitBtn.oldOnClick = null;
		}*/
		if (this.submitButton) this.submitButton.setDisabled(false);
		
		if (response.responseText) response = response.responseText;
		if(typeof(response) != 'object'){
			try {
				response = dojo.fromJson(response);
			} catch(err) {
				console.log('Error: ' + dumpVar(err.message));
			}
		}
		if (response.problems && typeof(response.problems) != 'object') {
			response.problems = dojo.fromJson(response.problems);
			
		}
		this.problems = response.problems || response || [];
		var form = this;
		
		// force validation for all widgets
		dojo.forEach(this.getDescendants(), function(widget){
			widget._refreshState = function() {
				console.log('works!');
				var message = this.getErrorMessage ? this.getErrorMessage() : null;
				
				if (message) {
					dijit.showTooltip(message, this.domNode, ['above', 'below']);
				}
			};
			
			if (widget.name) {
				widget.invalidMessage = form.problems[widget.name];
			}
			if (widget.validate) {
				widget.validate(true);
			}
		});
        
		// display general problem (if any)
		if (this.submitButton) var node = this.submitButton.domNode;
		else var node = this.domNode;
		if (this.problems && this.problems.general) {
			dijit.showTooltip(this.problems.general, node, 'above');
		}
		else {
			if (dijit._masterTT)
				dijit.hideTooltip(dijit._masterTT.aroundNode);
		}
        
		if (this.recaptcha) {
			Recaptcha.reload();
		}
        
		this.onSubmitError(response);
		return response;
	},
	
	onSubmitError: function(response) {
	// saved for custom implementations 
	},
	
	onSubmitSuccess: function(response) {
	// saved for custom implementations 
	},
	
	// Presubmit validation. Must return true for submit
	beforeSubmit: function() {
		return true;
	},
	
	submit: function(afterSubmit) {
		
		// show "please wait" button
		/*if (this.submitBtn && this.submitBtn.src && this.parent == mainDialogFrame) {
			this.submitBtn.src = baseUrl + '/public/images/wait_btn.png';
			this.submitBtn.oldOnClick = this.submitBtn.onclick;
			this.submitBtn.onclick = function(){alert("Please wait");};
		}*/
		
		if (!this.beforeSubmit()) {
			console.log('Before submit returned false');
			this.onSubmitError({});
			return;
		};
		
		if (this.submitButton) this.submitButton.setDisabled(true);
		
		if (afterSubmit) this.afterSubmit = afterSubmit;
		
		if (dijit._masterTT)
			dijit.hideTooltip(dijit._masterTT.aroundNode);
		
		// define submit parameters
		var submitObj = {
			url:   this.action,
			form:  this.domNode,
			load:  dojo.hitch(this, this._loadHandler),
			error: dojo.hitch(this, this._errorHandler)
		//handle: dojo.hitch(this, this._loadHandler)//  - use this if something doesn't work and you want to see error messages
		};
		
		// submit the form
		if (this.iframe == true) {
			submitObj.timeout = 600000;
			dojo.io.iframe.send(submitObj);
		} else if (this.jsonp == true) {
			// collect form values and convert them into a query string
			dojo.io.script.get({
				callbackParamName : "mycallback", //provided by the jsonp service
				url: this.action + '?' + dojo.objectToQuery(this.attr('value')),
				load:  dojo.hitch(this, this._loadHandler),
				error: dojo.hitch(this, this._errorHandler)
			});
		} else {
			submitObj.handleAs = 'json';
			dojo.xhrPost(submitObj);
		}
	},
	
	cancel: function() {
		if (this.dialog) this.dialog.hide();
		this.onCancel();
	},
	
	onCancel: function() {
		
	}
	
});