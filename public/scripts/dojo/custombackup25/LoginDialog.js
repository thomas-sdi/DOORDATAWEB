dojo.provide("custom.LoginDialog");

dojo.declare("custom.LoginDialog", ginger.Dialog, {
	
	visible: false,
	
	_onKey: function(evt) {
		if (evt.charOrCode == dojo.keys.ENTER)
			dijit.byId('loginForm').submit(function(){changeBody('/index/home', false, true); loginDialog.hide();});
		else this.inherited(arguments); 
	},
	
	hide: function() {
		this.visible = false;
		this.inherited(arguments);
	},
	
	show: function() {
		this.visible = true;
		dijit.byId('login_login').attr('value', '');
		dijit.byId('login_password').attr('value', '');
		this.inherited(arguments);
	}
	
});
