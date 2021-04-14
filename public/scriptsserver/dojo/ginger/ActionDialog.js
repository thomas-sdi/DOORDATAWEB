dojo.provide("ginger.ActionDialog");

dojo.declare("ginger.ActionDialog", null, {
	onSubmitHook: null,
	onCancelHook: null,
	dialog: null,
	draggable: null,
	message: '',
	submitTitle: '',
	cancelTitle: '',
	
	/* 
	 * args: {
	 *		onSubmit: Function, 
	 *		onCancel: Function,
	 *		message: String,
	 *		submitTitle: String (Default: 'OK'),
	 *		cancelTitle: String (Default: 'Cancel'),
	 *		title: String (Default),
	 *		hideSubmit: Boolean,
	 *		hideCancel: Boolean,
	 *		
	 * }
	 * */
	constructor: function(args) {
		this.onSubmitHook = args.onSubmit;
		this.onCancelHook = args.onCancel;
		this.draggable = args.hasOwnProperty('draggable') ? args.draggable : true;
		
		var caption = args.title || 'Please confirm your action';
		
		var titleDiv = dojo.create('div', {
			style:'', 
			innerHTML:'<h3 class="content-box-header bg-primary" style="text-align: left;"><i class="glyph-icon icon-question-circle"></i> ' + caption + '</h3>'
		});
		
		// create message div
		var messageDiv = dojo.create('div', {style: 'margin: 20px 5px 0px 5px; text-align: center; font-weight: bold;', innerHTML: args.message});
		var separateDiv = dojo.create('div', {style: "margin:10px 5px 10px 5px; min-height: 40px; text-align: center;"});
		var bottomDiv = dojo.create('div', {style: 'clear:both; margin-bottom: 10px; text-align: center;'});
		// create action buttons
		var submitTitle = args.submitTitle || 'OK';
		var cancelTitle = args.cancelTitle || 'Cancel';
		
		var hideSubmit = args.hideSubmit || false;
		var hideCancel = args.hideCancel || false;
		var buttonsOrder = args.buttonsOrder || ['ok', 'cancel'];
			
		// create dialog and attach buttons to it
		this.dialog = new ginger.Dialog({title: caption, draggable: this.draggable});
		
		this.dialog.containerNode.appendChild(titleDiv);
		this.dialog.containerNode.appendChild(messageDiv);
		
		for (var i=0; i < buttonsOrder.length; i++){
			if (buttonsOrder[i] == 'ok' && !hideSubmit){
				var submitButton = new ginger.form.SubmitButton(
        			{ text: submitTitle, onClick: dojo.hitch(this, "onSubmit"), style: i == 0 ? '': 'margin-left: 10px !important'});
				separateDiv.appendChild(submitButton.domNode);
			}
			if (buttonsOrder[i] == 'cancel' && !hideCancel){
				var cancelButton = new ginger.form.CancelButton(
        			{ text: cancelTitle, onClick: dojo.hitch(this, "onCancel"), style: i == 0 ? '' : 'margin-left: 10px !important'});
				separateDiv.appendChild(cancelButton.domNode);
			}
		}
		
		this.dialog.containerNode.appendChild(separateDiv);
		this.dialog.containerNode.appendChild(bottomDiv);
		
		// show dialog
		this.dialog.startup();
		this.dialog.show();
	},
	
	onSubmit: function(evt) {
		if (this.onSubmitHook) this.onSubmitHook(evt);
	},
	
	onCancel: function(evt) {
		if (this.onCancelHook) this.onCancelHook(evt);
		this.hide();
	},
	
	hide: function() {
		this.dialog.hide();
		this.dialog.destroyRecursive();
	}
});