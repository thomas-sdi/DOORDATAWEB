dojo.provide("custom.PDFExportForm");
dojo.require("dijit.form.Form");

dojo.declare("custom.PDFExportForm", dijit.form.Form, {
	
	btnGen: null,
	btnGenAgain: null,
	btnClose: null,
	
	startup: function() {
		this.inherited(arguments);
		
		this.btnGen      = dijit.byId('gen_pdf');
		this.btnGenAgain = dijit.byId('btn_gen_again');
		this.btnClose    = dijit.byId('btn_close');
		
		dojo.connect(this.btnGen, 	   'onClick', dojo.hitch(this, 'genPDF'));
		dojo.connect(this.btnGenAgain, 'onClick', dojo.hitch(this, 'generatePdf'));
		dojo.connect(this.btnClose,    'onClick', dojo.hitch(this, 'closeDlg'));

	},
	
	genPDF: function () {
		if ((this.domNode.innerHTML.indexOf("Generate PDF")   != -1) ||
			(this.domNode.innerHTML.indexOf("Generate again") != -1) ) {
			this.generatePdf();
		} else {
			this.retrievePdf();
		}
	},
	
	generatePdf: function() {
		
		var format = 'Legacy';
		
		if ($('#legacy_tab').hasClass('active')) format = 'Legacy';
		if($('#doordata_report_tab').hasClass('active')) format = 'DoorData';
		
		//set the value of the hidden field depending on the tab opened
		$('#reportFormat').val(format);
		
		dojo.byId('status').innerHTML = 'Generating PDF...';
		var _this = this;
		dojo.xhrPost ({
        	url: baseUrl + '/pdf/generatejava',
        	handleAs: "text", 
        	form: "forms",
       	 	load: function(response, ioArgs) {
				//console.log(response, '\n', ioArgs);
				var responseObj = dojo.fromJson(response);
            	if (responseObj.status == 'succeeded') {
					dojo.byId('report_location').value = responseObj.location;
					dojo.byId('status').innerHTML = 'Request succeeded.';
					_this.retrievePdf();
				} else if (responseObj.code == "400") {
					dojo.byId('report_location').value = '';
					if (responseObj.error.length > 150) { 
						dojo.byId('status').innerHTML = responseObj.error.substring(0, 150) + "...";
					} else {
						dojo.byId('status').innerHTML = responseObj.error;
					}
				} else {
					dojo.byId('status').innerHTML = "Server error occured!";
				}
				return response;
			},
			error: function(response, ioArgs) {  
				//console.error(response, '\n', ioArgs);
           		var responseObj = dojo.fromJson(response);
				if (responseObj.error.length > 150) { 
					dojo.byId('status').innerHTML = responseObj.error.substring(0, 150) + "...";
				} else {
					dojo.byId('status').innerHTML = responseObj.error;
				}
				return response;  
       		} 
		});
	},
	
	closeDlg: function() {
		mainDialog.hide();
	},
	
	retrievePdf: function() {
		var report_location = dojo.byId("report_location").value;
		var inspection_id = dojo.byId("inspection_id").value;
		dojo.byId('status').innerHTML = 'Retrieving PDF...';
		
		dojo.byId('gen_pdf').setAttribute('label', "Generate PDF");
		dojo.byId('status').innerHTML = "Report was succesfully retrieved";
		dojo.byId('previous_report').innerHTML = '<a href="' + report_location + '" target="_blank">See previous report</a>';
		
		window.open(report_location);
		
		/*
		dojo.xhrGet ({
			url: baseUrl + '/pdf/retrieve',
			handleAs: "text", 
			content: {report_location: report_location, inspection_id: inspection_id},
			load: function(response, ioArgs) {
				//console.log(response, '\n', ioArgs);
				var responseObj = dojo.fromJson(response);
				if (responseObj.status == 'succeeded') {
					dojo.byId('gen_pdf').setAttribute('label', "Generate PDF");
					dojo.byId('status').innerHTML = "Report was succesfully retrieved";
					dojo.byId('previous_report').innerHTML = '<a href="' + responseObj.report_location + '" target=_self>See previous report</a>';
					window.location.href = responseObj.report_location;
					//window.open(responseObj.report_location, 'PDF');
					dojo.byId('gen_again').setAttribute('style', 'visibility: hidden');
				} else if (responseObj.code == '400') {
					if (responseObj.error.length > 150) { 
						dojo.byId('status').innerHTML = responseObj.error.substring(0, 150) + "...";
					} else {
						dojo.byId('status').innerHTML = responseObj.error;
					}
				} else {
					dojo.byId('gen_pdf').setAttribute('label', 'Try again');
					dojo.byId('status').innerHTML = "Busy, try again later";
				}
   				return response;
			},
			error: function(response, ioArgs) { 
				//console.log(response, '\n', ioArgs);
    			var responseObj = dojo.fromJson(response);
				//console.error("HTTP status code: ", ioArgs.xhr.status); 
				if (responseObj.error.length > 150) { 
					dojo.byId('status').innerHTML = responseObj.error.substring(0, 150) + "...";
				} else {
					dojo.byId('status').innerHTML = responseObj.error;
				}
    			return response;  
			} 
		});
		
		*/
	}
	
});