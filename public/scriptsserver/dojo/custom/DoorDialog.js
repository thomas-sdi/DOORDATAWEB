dojo.provide("custom.DoorDialog");
dojo.require("custom.widgets.BratiliusDialog");

dojo.declare("custom.DoorDialog", custom.widgets.BratiliusDialog, {
	
	dojoConnectors: [],
	
	doorTypes: ['1009', '1008', '1007', '1010', '1011', '1012', '1013', '1006'],
	
	executeEdit: function (parent){
		
		if (parent) {
			//gather information about all the fields
			var gridId = this.grid.getId();
			
			//pass the fields to the server
			var data = {};
			
			//Fire DOORDATA tab
			data.ID = dojo.byId('inspection_door_id').value; 			
			data.NUMBER = dojo.byId(gridId + '_NUMBER').value;
			data.DOOR_BARCODE = dojo.byId(gridId + '_DOOR_BARCODE').value;
			data.HANDING = this.getDropdownValue('HANDING');
			data.STYLE = this.getRadioValue('STYLE');
			data.DOOR_TYPE = this.getDoorTypes();
			data.TYPE_OTHER = dojo.byId(gridId + '_TYPE_OTHER').value;
			data.MATERIAL = this.getRadioValue('MATERIAL');
			data.MATERIAL_OTHER = dojo.byId(gridId + '_MATERIAL_OTHER').value;
			data.ELEVATION = this.getRadioValue('ELEVATION');
			data.ELEVATION_OTHER = dojo.byId(gridId + '_ELEVATION_OTHER').value;
			data.FRAME_MATERIAL = this.getRadioValue('FRAME_MATERIAL');
			data.FRAME_MATERIAL_OTHER = dojo.byId(gridId + '_FRAME_MATERIAL_OTHER').value;
			data.FRAME_ELEVATION = this.getRadioValue('FRAME_ELEVATION');
			data.FRAME_ELEVATION_OTHER = dojo.byId(gridId + '_FRAME_ELEVATION_OTHER').value;
			data.LOCATION = dojo.byId(gridId + '_LOCATION').value;
			data.FIRE_RATING_1 = this.getRadioValue('FIRE_RATING_1');
			data.FIRE_RATING_2 = this.getRadioValue('FIRE_RATING_2');
			data.FIRE_RATING_3 = this.getRadioValue('FIRE_RATING_3');
			data.FIRE_RATING_4 = this.getRadioValue('FIRE_RATING_4');
			data.TEMP_RISE = this.getRadioValue('TEMP_RISE');
			data.LISTING_AGENCY = this.getRadioValue('LISTING_AGENCY');
			data.LISTING_AGENCY_OTHER = dojo.byId(gridId + '_LISTING_AGENCY_OTHER').value;
			data.BARCODE = dojo.byId(gridId + '_BARCODE').value;
			data.GAUGE = dojo.byId(gridId + '_GAUGE').value;
			data.MANUFACTURER = dojo.byId(gridId + '_MANUFACTURER').value;
			data.MODEL = dojo.byId(gridId + '_MODEL').value;
			data.REMARKS = dojo.byId(gridId + '_REMARKS').value;
			
			//Door Detail tab
			data.HINGE_HEIGHT = this.getDropdownValue('HINGE_HEIGHT');
			data.HINGE_THICKNESS = this.getDropdownValue('HINGE_THICKNESS');
			data.HINGE_HEIGHT1 = this.getTextValue('HINGE_HEIGHT1');
			data.HINGE_FRACTION1 = this.getDropdownValue('HINGE_FRACTION1');
			data.HINGE_HEIGHT2 = this.getTextValue('HINGE_HEIGHT2');
			data.HINGE_FRACTION2 = this.getDropdownValue('HINGE_FRACTION2');
			data.HINGE_HEIGHT3 = this.getTextValue('HINGE_HEIGHT3');
			data.HINGE_FRACTION3 = this.getDropdownValue('HINGE_FRACTION3');
			data.HINGE_HEIGHT4 = this.getTextValue('HINGE_HEIGHT4');
			data.HINGE_FRACTION4 = this.getDropdownValue('HINGE_FRACTION4');
			data.HINGE_BACKSET = this.getDropdownValue('HINGE_BACKSET');
			
			data.HINGE_MANUFACTURER = this.getTextValue('HINGE_MANUFACTURER');
			data.HINGE_MANUFACTURER_NO = this.getTextValue('HINGE_MANUFACTURER_NO');
			data.TOP_TO_CENTERLINE = this.getTextValue('TOP_TO_CENTERLINE');
			data.TOP_TO_CENTERLINE_FRACTION = this.getDropdownValue('TOP_TO_CENTERLINE_FRACTION');
			data.LOCK_BACKSET = this.getDropdownValue('LOCK_BACKSET');
			data.FRAME_BOTTOM_TO_CENTER = this.getTextValue('FRAME_BOTTOM_TO_CENTER');
			data.STRIKE_HEIGHT = this.getDropdownValue('STRIKE_HEIGHT');
			
			data.PREFIT_DOOR_SIZE_X = this.getTextValue('PREFIT_DOOR_SIZE_X');
			data.PREFIT_FRACTION_X = this.getDropdownValue('PREFIT_FRACTION_X');
			data.PREFIT_DOOR_SIZE_Y = this.getTextValue('PREFIT_DOOR_SIZE_Y');
			data.PREFIT_FRACTION_Y = this.getDropdownValue('PREFIT_FRACTION_Y');
			data.FRAME_OPENING_SIZE_X = this.getTextValue('FRAME_OPENING_SIZE_X');
			data.FRAME_OPENING_FRACTION_X = this.getDropdownValue('FRAME_OPENING_FRACTION_X');
			data.FRAME_OPENING_SIZE_Y = this.getTextValue('FRAME_OPENING_SIZE_Y');
			data.FRAME_OPENING_FRACTION_Y = this.getDropdownValue('FRAME_OPENING_FRACTION_Y');
			data.LITE_CUTOUT_SIZE_X = this.getTextValue('LITE_CUTOUT_SIZE_X');
			data.LITE_CUTOUT_FRACTION_X = this.getDropdownValue('LITE_CUTOUT_FRACTION_X');
			data.LITE_CUTOUT_SIZE_Y = this.getTextValue('LITE_CUTOUT_SIZE_Y');
			data.LITE_CUTOUT_FRACTION_Y = this.getDropdownValue('LITE_CUTOUT_FRACTION_Y');
			data.LOCKSTILE_SIZE = this.getTextValue('LOCKSTILE_SIZE');
			data.LOCKSTILE_FRACTION = this.getDropdownValue('LOCKSTILE_FRACTION');
			data.TOPRAIL_SIZE = this.getTextValue('TOPRAIL_SIZE');
			data.TOPRAIL_FRACTION = this.getDropdownValue('TOPRAIL_FRACTION');
			
			
			//Frame Detail Tab
			data.A = this.getTextValue('A');
			data.A_FRACTION = this.getDropdownValue('A_FRACTION');
			data.B = this.getTextValue('B');
			data.B_FRACTION = this.getDropdownValue('B_FRACTION');
			data.C = this.getTextValue('C');
			data.C_FRACTION = this.getDropdownValue('C_FRACTION');
			data.D = this.getTextValue('D');
			data.D_FRACTION = this.getDropdownValue('D_FRACTION');
			data.E = this.getTextValue('E');
			data.E_FRACTION = this.getDropdownValue('E_FRACTION');
			data.F = this.getTextValue('F');
			data.F_FRACTION = this.getDropdownValue('F_FRACTION');
			data.G = this.getTextValue('G');
			data.G_FRACTION = this.getDropdownValue('G_FRACTION');
			data.H = this.getTextValue('H');
			data.H_FRACTION = this.getDropdownValue('H_FRACTION');
			data.I = this.getTextValue('I');
			data.I_FRACTION = this.getDropdownValue('I_FRACTION');
			data.J = this.getTextValue('J');
			data.J_FRACTION = this.getDropdownValue('J_FRACTION');
			data.K = this.getTextValue('K');
			data.K_FRACTION = this.getDropdownValue('K_FRACTION');
			data.L = this.getTextValue('L');
			data.L_FRACTION = this.getDropdownValue('L_FRACTION');
			data.M = this.getTextValue('M');
			data.M_FRACTION = this.getDropdownValue('M_FRACTION');
			data.N = this.getTextValue('N');
			data.N_FRACTION = this.getDropdownValue('N_FRACTION');
			data.O = this.getTextValue('O');
			data.O_FRACTION = this.getDropdownValue('O_FRACTION');
			data.P = this.getTextValue('P');
			data.P_FRACTION = this.getDropdownValue('P_FRACTION');
			data.Q = this.getTextValue('Q');
			data.Q_FRACTION = this.getDropdownValue('Q_FRACTION');
			data.R = this.getTextValue('R');
			data.R_FRACTION = this.getDropdownValue('R_FRACTION');
			data.S = this.getTextValue('S');
			data.S_FRACTION = this.getDropdownValue('S_FRACTION');
			data.T = this.getTextValue('T');
			data.T_FRACTION = this.getDropdownValue('T_FRACTION');
			data.U = this.getTextValue('U');
			data.U_FRACTION = this.getDropdownValue('U_FRACTION');
			data.V = this.getTextValue('V');
			data.V_FRACTION = this.getDropdownValue('V_FRACTION');
			
			
			//Hardware tab
			data.HARDWARE_GROUP = this.getTextValue('HARDWARE_GROUP');
			data.HARDWARE_SET = this.getTextValue('HARDWARE_SET');
			
			
			//Inspection Checklist tab
			data.DOOR_CODES = this.getActiveDoorCodes();
			data.INSPECTION_OTHER = this.getOtherCodes();
			data.COMPLIANT = this.getRadioValue('compliant');
			
			var _this = this;
			
			dojo.xhrPost({
                url: baseUrl + '/door/savedoor',
                content: {json: dojo.toJson(data)},
                handleAs: 'json',
                load: function (response) {
                    console.log('response: ' + response);
                    
                    _this.hide();
                    bratiliusDialog.hide();
                    _this.cleanDojoConnectors();
                    
                    
                    
                    
                }
            });
			
			return;
		}
		var _this = this;
		
		
		// save picture1
		_this.savePicture(1, function(){
			console.log('saving picture 1');
			// save picture2
			_this.savePicture(2, function(){
				
				console.log('saving picture 2');
				
					//TODO:save hardware grid
					/*cmp_hardware.edit.apply(); //###
					if (cmp_hardware.store.isDirty()) { //###
						cmp_hardware.store.save({ //###
							
							onComplete: function() {
								// save inherited
								_this.executeEdit(true);
							},
							onError: dojo.hitch(cmp_hardware, "onSaveFailed") //###
						});
					}
					else _this.executeEdit(true);*/
					
					console.log('saving the door');
					
					_this.executeEdit(true); //TODO: the hardware grid logic above is commented out
				
			});
		});
		
		
	},
	
	getActiveDoorCodes: function(){
		var codes = [];
		
		//frame
		for (var i = 1; i <= 16; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//door 
		for (var i = 17; i <= 35; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//operational test
		for (var i = 36; i <= 47; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//hinges / pivots
		for (var i = 48; i <= 56; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//door bolts
		for (var i = 57; i <= 69; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//locks
		for (var i = 70; i <= 84; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//fire exit software
		for (var i = 85; i <= 97; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//door closers
		for (var i = 98; i <= 115; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		//misc
		for (var i = 116; i <= 134; i++){
			var code = dojo.byId('CODE_' + i);
			if (code.checked == true){
				codes.push(i);
			}
		}
		
		return codes;
	},
	
	getOtherCodes: function(){
		var otherCodes = [];
		
		for (var i = 13; i <= 16; i++){
			var code = dojo.byId('CODE_' + i + '_OTHER').value;
			if (code){
				otherCodes.push({
					'OTHER_ID': i,
					'OTHER_VALUE': code
				});
			}
		}
		
		return otherCodes;
	},
	
	getTextValue: function(fieldName){
		var gridId = this.grid.getId();
		return dojo.byId(gridId + '_' + fieldName).value;
	},
	
	getDropdownValue: function(fieldName){
		return document.getElementsByName(fieldName + '_id')[0].value;
	},
	
	getRadioValue: function(fieldName){
		var field = dojo.query('input[type=radio][name=' + fieldName + ']:checked')[0];
		
		//if user had selected a value
		if (field)
			return field.value;
		else return ''; //if none of the values are selected
	},
	
	getDoorTypes: function(){
		//all possible door types
		var types = this.doorTypes;
		
		var selectedTypes = [];	
		for (var i = 0; i < types.length; i++){
			if(dojo.byId('doorType_' + types[i]).checked == true){
				selectedTypes.push(types[i]);
			}
		}
		return selectedTypes;	
	},
	
	submit: function() {
		this.executeEdit(true);
	},
	
	savePicture: function(number, onComplete){
		// check if the picture has actually been modified
		var form = dojo.byId('door_main_form_' + number);
		var inputElement;
		//for (var n in form.elements) {
		for (var n = 0; n < form.elements.length; n++) {
			if (form.elements[n].name == 'picture_file') {
				inputElement = form.elements[n];
				if (isEmpty(form.elements[n].value)) {
					if (onComplete) {
						onComplete();
					}
					return '';
				}
			}
		}
				
		//alert('savepicture'+number+'1');
		dojo.byId('picture' + number + '_status').innerHTML = 'In Progress...';
		dojo.io.iframe.send({
			url: baseUrl + '/inspection/savepicture',
			handleAs: "text",
			form: 'door_main_form_' + number,
			load: function(response, ioArgs){
				console.log(response);
				inputElement.value = null;
				var responseObj = dojo.fromJson(response);
				if (responseObj.status && responseObj.status == 'failed') {
					dojo.byId('picture' + number + '_status').innerHTML = 'Problem during saving the file';
					return;
				} else if (responseObj.picture) {
					dojo.byId('picture_' + number).src = responseObj.picture;
					dojo.byId('picture_' + number + '_id').value = responseObj.picture_id;
					dojo.byId('picture' + number + '_status').innerHTML = "&nbsp";
				} else {
					dojo.byId('picture' + number + '_status').innerHTML = "&nbsp";
				}
				if (onComplete) onComplete();
				return response;
			},
			error: function(response, ioArgs){
				console.log(response, ioArgs);
				return response;
			}
		});
	},
	
	saveAudio: function(onComplete){
		var inputElement;
		// check if the audio has actually been modified
		var form = dojo.byId('door_main_form');
		//for (var n in form.elements) {
		for (var n = 0; n < form.elements.length; n++) {
			if (form.elements[n].name == 'audio_file') {
				inputElement = form.elements[n];
				if (isEmpty(form.elements[n].value)) {
					if (onComplete) 
						onComplete();
					return '';
				}
			}
		}
		
		dojo.byId('audio_status').innerHTML = 'In Progress...';
		dojo.io.iframe.send({
			url: baseUrl + '/inspection/saveaudio',
			handleAs: "text",
			form: 'door_main_form',
			load: function(response, ioArgs){
				console.log(response);
				inputElement.value = null;
				var responseObj = dojo.fromJson(response);
				if (responseObj.status && responseObj.status == 'failed') {
					dojo.byId('picture' + number + '_status').innerHTML = 'Problem during saving the file';
					return;
				} else if (responseObj.audio) {
					niftyplayer('player').load(responseObj.audio);
					dojo.byId('audio_id').value = responseObj.audio_id;
					dojo.byId('audio_status').innerHTML = "&nbsp";
				} else {
					dojo.byId('audio_status').innerHTML = "&nbsp";
				}
				if (onComplete) onComplete();
				return response;
			},
			error: function(response, ioArgs){
				console.log(response, ioArgs);
				return response;
			}
		});
	},
	
	deletePicture: function(number, onComplete){
		dojo.byId('picture' + number + '_status').innerHTML = 'In Progress...';
		dojo.xhrGet({
			url: baseUrl + '/inspection/deletepicture',
			content: {picture: dojo.byId('picture_' + number + '_id').value},
			handleAs: "text",
			load: function(response, ioArgs){
				console.log(response);
				var responseObj = dojo.fromJson(response);
				if (responseObj.status && responseObj.status == 'ok') {
					dojo.byId('picture_' + number).src = "";
					dojo.byId('picture_' + number + '_id').value = "";
					dojo.byId('picture_' + number + '_notes').innerHTML = "";
					dojo.byId('picture' + number + '_status').innerHTML = "&nbsp";
				} else {
					dojo.byId('picture' + number + '_status').innerHTML = 'Problem during deleting the file';
				}
				if (onComplete) onComplete();
				return response;
			},
			error: function(response, ioArgs){
				console.log(response, ioArgs);
				return response;
			}
		});
	},
	
	deleteAudio: function(onComplete){
		dojo.byId('audio_status').innerHTML = 'In Progress...';
		dojo.xhrGet({
			url: baseUrl + '/inspection/deleteaudio',
			content: {audio: dojo.byId('audio_id').value},
			handleAs: "text",
			load: function(response, ioArgs){
				console.log(response);
				var responseObj = dojo.fromJson(response);
				if (responseObj.status && responseObj.status == 'ok') {
					niftyplayer('player').load("");
					dojo.byId('audio_id').value = "";
					dojo.byId('audio_notes').innerHTML = "";
					dojo.byId('audio_status').innerHTML = "&nbsp";
				} else {
					dojo.byId('audio_status').innerHTML = 'Problem during deleting the file';
				}
				if (onComplete) onComplete();
				return response;
			},
			error: function(response, ioArgs){
				console.log(response, ioArgs);
				return response;
			}
		});
	},
	
	rotatePicture: function(number, onComplete){
		dojo.byId('picture' + number + '_status').innerHTML = 'In Progress...';
		dojo.xhrGet({
			url: baseUrl + '/inspection/rotatepicture',
			content: {picture: dojo.byId('picture_' + number + '_id').value},
			handleAs: "text",
			load: function(response, ioArgs){
				console.log(response);
				var responseObj = dojo.fromJson(response);
				if (responseObj.picture) {
					dojo.byId('picture_' + number).src = responseObj.picture;
					dojo.byId('picture' + number + '_status').innerHTML = "&nbsp";
				} else {
					dojo.byId('picture' + number + '_status').innerHTML = 'Problem during rotating picture';
				}
				if (onComplete) onComplete();
				return response;
			},
			error: function(response, ioArgs){
				console.log(response, ioArgs);
				return response;
			}
		});
	},

	
	
	/*
	showEdit: function(showMode){
		this.searchMode = false;
		
		// only first item from selection counts for now
		var selectedRow = this.grid.getSelectedItemId();
		if (this.selectedRow === null) return;
		this.selectedItem = this.grid.getFirstSelectedItem();

		// get existing query
		var href = this.grid.detailedView;
		var queryMark = href.indexOf('?');
		var query = (queryMark == -1 ) ?
				new Object() : dojo.queryToObject(href.substr(queryMark + 1));
		// update query in dialog href
		query._parent = selectedRow;
		query._showMode = showMode;
		var gridParentId = this.grid.getParentRowId();
		if (gridParentId) query._super = gridParentId;

		// update dialog frame href
		var host = queryMark == -1 ? href : href.substr(0, queryMark);
		this.frame.onLoad = dojo.hitch(this, "onLoad");
		this.frame.attr('href', host + '?' + dojo.objectToQuery(query));
		
		console.log('Href: ' + this.frame.attr('href'));
		
		// open the dialog
		this.show();
	},*/
	
	showEdit: function(){
		
		this.frame.onLoad = dojo.hitch(this, "onLoad");
		
		this.show();
	},
	
	rememberCheckedValue: function(widget) {
       widget.checkedBeforeClicked = widget.checked;  
    },
    
    uncheckBox: function(widget) {
        if (widget.checkedBeforeClicked) {
           widget.setAttribute('value', false);
           widget.setAttribute('checked', false);
       }
    },
	
	onLoad: function() {
	    this.inherited(arguments);
	    
	    
	    
	    var _this = this;
	    
	    // ability to uncheck Yes/No for Compliant checkbox
	    
	    var gridId = this.grid.getId();
	    
        var compliantWidget = dijit.byId(gridId + '_compliant');
        var checkWidgetYes = dijit.byId(gridId + '_compliant_0');
        var checkWidgetNo = dijit.byId(gridId + '_compliant_1');
         
        dojo.forEach([checkWidgetYes, checkWidgetNo], function(widget){
            _this.dojoConnectors.push(dojo.connect(widget, 'onMouseDown', function() {
                _this.rememberCheckedValue(widget);
            }));
            
            _this.dojoConnectors.push(dojo.connect(widget, 'onClick', function() {
                _this.uncheckBox(widget);
				
				//it should not be possible to mark the door as non-compliant if no violation codes were checked
                if(this.id == _this.grid.getId() + '_compliant_1'){
            		_this.onNotCompliantClick();
            	};
            }));
        });
        
        console.log(gridId);

		//door material radio button
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_MATERIAL_5'), 'onClick', dojo.hitch(this, this.onRadioClick)));

        //door elevation radio button
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_5'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_6'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_7'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_ELEVATION_8'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //frame material radio button
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_MATERIAL_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_MATERIAL_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_MATERIAL_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_MATERIAL_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_MATERIAL_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //frame elevation radio button
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_ELEVATION_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_ELEVATION_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_ELEVATION_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_ELEVATION_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FRAME_ELEVATION_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //fire rating minutes
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_1_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_1_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_1_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_1_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_1_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //fire rating code
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_2_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_2_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_2_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_2_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_2_4'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //temp rise
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_TEMP_RISE_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_TEMP_RISE_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_TEMP_RISE_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_TEMP_RISE_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //listing agency
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_LISTING_AGENCY_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_LISTING_AGENCY_1'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_LISTING_AGENCY_2'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_LISTING_AGENCY_3'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //positive pressure 1
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_3_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        
        //positive pressure 2
        this.dojoConnectors.push(dojo.connect(dijit.byId(gridId + '_FIRE_RATING_4_0'), 'onClick', dojo.hitch(this, this.onRadioClick)));
        

        
	},
	
	onRadioClick: function(event, oldValueContainerId){
		
		console.log('onRadioClick');

		//get the element that triggered the function
		var element = dijit.byId(event.target.id);
		
		//find the id of the container storing the previous value of the radiogroup
		var oldContainerId = '';
		console.log(element.name);
		
		switch(element.name){
			case 'MATERIAL':
				oldContainerId = 'door_material_internal';
				break;
			case 'ELEVATION':
				oldContainerId = 'door_elevation_internal';
				break;
			case 'FRAME_MATERIAL':
				oldContainerId = 'frame_material_internal';
				break;
			case 'FRAME_ELEVATION':
				oldContainerId = 'frame_elevation_internal';
				break;
			case 'FIRE_RATING_1':
				oldContainerId = 'fire_rating_minutes_internal';
				break;
			case 'FIRE_RATING_2':
				oldContainerId = 'fire_rating_code_internal';
				break;
			case 'TEMP_RISE':
				oldContainerId = 'temp_rise_internal';
				break;
			case 'LISTING_AGENCY':
				oldContainerId = 'listing_agency_internal';
				break;
			case 'FIRE_RATING_3':
				oldContainerId = 'pressure_1_internal';
				break;
			case 'FIRE_RATING_4':
				oldContainerId = 'pressure_2_internal';
				break;
			default: break;
		}

		
		//if the container id is not found, we can not continue
		if(oldContainerId == '') return;
		
		//now check if the user wants to uncheck the radiobutton and do so, if that's the case
		var oldValueContainer = dojo.byId(oldContainerId);
		if (element.value == oldValueContainer.value){
			element.set('checked', false);
			oldValueContainer.value = '';
		}
		else {
			oldValueContainer.value = element.value;
		}
	},
	
	clearFireRating1: function(){
		var gridId = this.grid.getId();
		
		dijit.byId(gridId + '_FIRE_RATING_1_0').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_1_1').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_1_2').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_1_3').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_1_4').set('checked', false);
	},
	
	clearFireRating2: function(){
		dijit.byId(gridId + '_FIRE_RATING_2_0').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_2_1').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_2_2').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_2_3').set('checked', false);
		dijit.byId(gridId + '_FIRE_RATING_2_4').set('checked', false);
	},
	
	onCancel: function(/* Bool */isNew) {
		console.log('IsNew: ' + isNew);
		
		var _this = this;
		
		if (isNew) {
			dojo.xhrGet({
				url: baseUrl + '/inspection/deletedoor',
				content: {_id: dojo.byId('inspection_door_id').value},
				handleAs: "text",
				load: function(response, ioArgs){
					console.log(response);
					return response;
				},
				error: function(response, ioArgs){
					console.log(response, ioArgs);
					return response;
				}
			});
		} 
		
		bratiliusDialog.hide();
		this.cleanDojoConnectors();
		
		
		
	},
	
	onNotCompliantClick: function(){
		var notCompliant = this.isDoorCompliant();

		//if the door is compliant, it should not be possible to mark it as not compliant
		if (notCompliant == false){
			console.log('the door is compliant');
			widget = dijit.byId(this.grid.getId() + '_compliant_1');
			widget.setAttribute('value', false);
           	widget.setAttribute('checked', false);
		}
	},
	
	// check if at least one violation code is checked off
	isDoorCompliant: function() {
        for (var element in this.attr('value')) {
            if (element.indexOf('CODE_') == 0) {
                // get form widget holding the value
                var widget = dijit.byId(element);
                                
                if (widget && widget.baseClass == 'dijitCheckBox') {
                    if (widget.checked) {
                        return true;
                    }
                } 
            }
        }
        return false;
	},
	
	
	//this function needs to be called every time the door dialog is closed
	cleanDojoConnectors: function(){
		console.log('cleaning dojo connectors');
		
		dojo.forEach(this.dojoConnectors, function(connector){
			dojo.disconnect(connector);
        });
        
        
        //clean widgets
        var gridId = this.grid.getId();
        dijit.byId(gridId + '_NUMBER').destroy();
        dijit.byId(gridId + '_DOOR_BARCODE').destroy();
        dijit.byId(gridId + '_HANDING').destroy();
        dijit.byId(gridId + '_STYLE').destroyRecursive();
        
        for(var i=0; i < this.doorTypes.length; i++){
  			dijit.byId('doorType_' + this.doorTypes[i]).destroy();
		}
		
		dijit.byId(gridId + '_TYPE_OTHER').destroy();
		dijit.byId(gridId + '_MATERIAL').destroyRecursive();
		dijit.byId(gridId + '_ELEVATION').destroyRecursive();
		dijit.byId(gridId + '_FRAME_MATERIAL').destroyRecursive();
		dijit.byId(gridId + '_FRAME_ELEVATION').destroyRecursive();
		dijit.byId(gridId + '_LOCATION').destroy();
		
		dijit.byId(gridId + '_FIRE_RATING_1').destroyRecursive();
		dijit.byId(gridId + '_FIRE_RATING_2').destroyRecursive();
		dijit.byId(gridId + '_FIRE_RATING_3').destroyRecursive();
		dijit.byId(gridId + '_FIRE_RATING_4').destroyRecursive();
		dijit.byId(gridId + '_TEMP_RISE').destroyRecursive();
		dijit.byId(gridId + '_LISTING_AGENCY').destroyRecursive();
		
		dijit.byId(gridId + '_BARCODE').destroy();
		dijit.byId(gridId + '_GAUGE').destroy();
		dijit.byId(gridId + '_MANUFACTURER').destroy();
		dijit.byId(gridId + '_MODEL').destroy();
		dijit.byId(gridId + '_REMARKS').destroy();
		
		dijit.byId('door_main_form_1').destroyRecursive();
		dijit.byId('door_main_form_2').destroyRecursive();
		
		
		var widgets = ['HINGE_HEIGHT', 'HINGE_THICKNESS', 'HINGE_HEIGHT1', 'HINGE_HEIGHT2', 'HINGE_HEIGHT3', 'HINGE_HEIGHT4',
			'HINGE_FRACTION1', 'HINGE_FRACTION2', 'HINGE_FRACTION3', 'HINGE_FRACTION4', 'HINGE_BACKSET',
			'HINGE_MANUFACTURER', 'HINGE_MANUFACTURER_NO', 'TOP_TO_CENTERLINE', 'TOP_TO_CENTERLINE_FRACTION', 'LOCK_BACKSET', 
			'FRAME_BOTTOM_TO_CENTER', 'STRIKE_HEIGHT',
			'PREFIT_DOOR_SIZE_X', 'PREFIT_FRACTION_X', 'PREFIT_DOOR_SIZE_Y', 'PREFIT_FRACTION_Y', 'FRAME_OPENING_SIZE_X', 'FRAME_OPENING_FRACTION_X',
			'FRAME_OPENING_SIZE_Y', 'FRAME_OPENING_FRACTION_Y', 'LITE_CUTOUT_SIZE_X', 'LITE_CUTOUT_FRACTION_X', 'LITE_CUTOUT_SIZE_Y', 'LITE_CUTOUT_FRACTION_Y',
			'LOCKSTILE_SIZE', 'LOCKSTILE_FRACTION', 'TOPRAIL_SIZE', 'TOPRAIL_FRACTION',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'A_FRACTION', 'B_FRACTION', 'C_FRACTION', 'D_FRACTION', 'E_FRACTION', 'F_FRACTION', 'G_FRACTION', 'H_FRACTION', 'I_FRACTION', 'J_FRACTION', 'K_FRACTION', 
			'L_FRACTION', 'M_FRACTION', 'N_FRACTION', 'O_FRACTION', 'P_FRACTION', 'Q_FRACTION', 'R_FRACTION', 'S_FRACTION', 'T_FRACTION', 'U_FRACTION', 'V_FRACTION',
			'HARDWARE_GROUP', 'HARDWARE_SET'];
		
		for(var i=0; i < widgets.length; i++){
  			this.destroyWidget(widgets[i]);
		}
		
		widgets = ['compliant'];
		for(var i=0; i < widgets.length; i++){
  			this.destroyWidgetRecursive(widgets[i]);
		}
		
		this.destroyCodeWidgets();
		
	},
	
	destroyCodeWidgets: function(){
		for (var i = 1; i <= 134; i++){
			dijit.byId('CODE_' + i).destroy();
		}
		
		var others = [13, 14, 15, 16, 32, 33, 34, 35, 44, 45, 46, 47, 53, 54, 55, 56, 66, 67, 68, 69, 81, 82, 83, 84, 94, 95, 96, 97, 112, 113, 114, 115, 131, 132, 133, 134];
		
		for (var i = 0; i < others.length; i++){
			dijit.byId('CODE_' + others[i] + '_OTHER').destroy();
		}
	},
	
	destroyWidgetRecursive: function(widgetId){
		if (!widgetId) return;
		var gridId = this.grid.getId();
		dijit.byId(gridId + '_' + widgetId).destroyRecursive();
		
	},
	
	destroyWidget: function(widgetId){
		if (!widgetId) return;
		var gridId = this.grid.getId();
		dijit.byId(gridId + '_' + widgetId).destroy();
	},
	
	checkCompliant: function() {
    	
    	var isChecked = this.isDoorCompliant();
    	
    	//if any of codes checked set compliant disabled, else enabled
    	var compliantWidget = dijit.byId(this.grid.getId() + '_compliant');
    	
		if (compliantWidget) {
    		if (isChecked) {
				compliantWidget._setDisabled(true);
				compliantWidget._setChecked(this.grid.getId() + '_compliant_1');
	    	} else {
	    		compliantWidget._setDisabled(false);
	    		compliantWidget._setChecked(null);
		    }
    	}   	
	}
});