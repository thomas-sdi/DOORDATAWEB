dojo.provide("custom.forms.InspectionImportForm");
dojo.require("ginger.form.ValidationForm");

dojo.declare("custom.forms.InspectionImportForm", ginger.form.ValidationForm, {
	importButton: null,
	statusDiv: null,
	
	startup: function() {
		this.importButton = dijit.byId('import');
		this.statusDiv = dojo.byId('status');
	},
	
	importInspection: function() {
		this.statusDiv.innerHTML = 'Importing data...';
		this.importButton.attr('label', 'Import in progress...');
		this.importButton.attr('disabled', true);
		this.importButton.attr('class', 'long');
		var self = this;
		
		dojo.io.iframe.send ({
			url: baseUrl + '/dataimport/',
			handleAs: "text", 
			form: "main_form",
			load: function(response, ioArgs) {
				console.log('Successful response received: ' + response);
				self.importButton.attr('label', 'Import');
				self.importButton.attr('disabled', false);
				self.importButton.attr('class', 'short');
				var responseObj = dojo.fromJson(response);
				if (responseObj.status == 'succeeded') {
					self.statusDiv.innerHTML = 'Data was successfully imported';
					mainDialog.hide();
					cmp_inspection_door.refresh();							
				} else if (responseObj.code == "400") {
					if (responseObj.error.length > 150) { 
						self.statusDiv.innerHTML = responseObj.error.substring(0, 150) + "...";
					} else {
						self.statusDiv.innerHTML = responseObj.error;
					}
				} else {
					this.statusDiv.innerHTML = "Server error occurred!";
				}
				return response;
			},
			error: function(response, ioArgs) {  
				console.log('Failure received: ' + response);
				var responseObj = dojo.fromJson(response);
				console.error("HTTP status code: ", ioArgs.xhr.status);  
				if (responseObj.error.length > 150) { 
					self.statusDiv.innerHTML = responseObj.error.substring(0, 150) + "...";
				} else {
					self.statusDiv.innerHTML = responseObj.error;
				}
				self.importButton.attr('label', 'Import');
				self.importButton.attr('disabled', false);
				self.importButton.attr('class', 'short');
				return response;  
			},
			handle: function(response) {
				console.log('Response: ' + dumpVar(response));
			}
			
		});			
	}
});