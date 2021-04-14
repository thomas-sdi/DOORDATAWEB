dojo.provide("ginger.form.AddressBox");

dojo.require("dijit.form.FilteringSelect");
dojo.require("ginger.form.FormWidget");
dojo.require('dojox.timing');
dojo.require('ginger.helper.AddressHelper');
dojo.require('ginger.geo.provider.Factory');

dojo.declare("ginger.form.AddressBox", [dijit.form.FilteringSelect, ginger.form.FormWidget],  {
	hasDownArrow: false,
	geocoder: null,	//google.maps.Geocoder
	map: null,
	queryExpr: "*",
	searchDelay: 0,
	cityName: "", // Full name of city with province and country
	cityProvince: "",
	city: null,	// google.maps.GeocoderGeometry
	timeout: null,
	searchAttempt: 0,
	placesService: null,
	mapCenterMarker: null,
	mapCenter: null,
	helper: null,
	googleAddress: null, // google.maps.Address
	mapProvider: '', // can specify MAP_PROVIDER_GOOGLE or MAP_PROVIDER_DECARTA, or leave blank for a default provider configured in app.ini
	geoFactory: null,
	geoProvider: null,
	companyGeocoder: null,
	defaultPlaceHolder : '',

	postMixInProperties: function() {
		this.inherited(arguments);
		
		var store = [];
		var item = {};
		item[this.value] = this.displayedValue;
		store.push(item);
		this.store = new dojox.data.KeyValueStore({
			dataVar: store
		});
		
		this.geoFactory = new ginger.geo.provider.Factory();
		this.geoProvider = this.geoFactory.getProvider({mapProvider: this.mapProvider});
		this.defaultPlaceholder = this.placeHolder;
	},
	
	startup: function() {
		// stop bubbling onEnter event
		dojo.connect(this.domNode, "onkeypress", function(e) {
			if (e.keyCode == dojo.keys.ENTER) {
				dojo.stopEvent(e);
			}
		});
		
		this.cityName = window.companyCityName;
		this.cityProvince = window.companyProvince || '';
		this.companyGeocoder = this._getCompanyGeocoder();
		
		//in decarta, we are going to pass country name coded in as 2-letter
		//this.cityName = window.companyCityName + ' ' + window.companyProvince + ' ' + window.companyCountry;
		
		//if (!this.map) this.createMap();
		this.helper = new ginger.helper.AddressHelper({
			map: this.map,
			mapProvider: this.mapProvider
		});
		
		this.inherited(arguments);
		
	},
	
	_getCompanyGeocoderFallback: function(currentGeocoder){
		var fallbackArray = {
			'decarta': 'mapquest',
			'mapquest': 'foursquare'
		};
		//cloudmade does not provide service any more...
		return fallbackArray[currentGeocoder];
	},
	
	createMap: function() {
        // create map canvas, if it doesn't exist yet
        var canvas = dojo.byId('map_canvas');
        if (!canvas) {
            canvas = document.createElement("div");
            canvas.id = "map_canvas";
            dojo.body().appendChild(canvas);
        }
        
        this.map = L.map('map_canvas').setView(window.companyCityCenter, 12);
	},
	
	/*
	 * @param.currentGeocoder
	 * @param.place
	 * @param.city
	 * @param.province
	 * @param.centerLat
	 * @param.centerLong
	 */
	searchAddress: function(params){
		var _this = this;
		var place = params.place;
		var city =  params.city;
		var province = params.province;
		var centerLat = params.centerLat;
		var centerLong = params.centerLong;
		
		var geocoder = this.geoFactory.getProvider({mapProvider: params.currentGeocoder}); 
		var geocoderFallback = this._getCompanyGeocoderFallback(this.companyGeocoder);
		
		geocoder.searchAddress({
			query: place,
			city: city,
			province: province,
			centerLat: centerLat,
			centerLong: centerLong,
			onError: function(){
				//if the geocoder couldn't return the results, let's ask this same query from the fallback geocoder
				geocoderFallback.searchAddress({
					place: place,
					city:  city,
					province: province,
					centerLat: centerLat,
					centerLong: centerLong,
					onComplete: function(results){
						_this.processSearchResults({
							results: results,
							geocoder: geocoderFallback,
							place: place,
							city: city,
							province: province
						});
					} 
				});
			},
			onComplete: function(results){
				_this.processSearchResults({
					results: results,
					geocoder: geocoder,
					place: place,
					city: city,
					province: province
				});
			} 
		});
	},
    
    searchPlaces: function() {
    	if (this.timer) {
			this.timer.stop();
		}
    	
    	// append vicinity information if the place doesn't contain "," in it already
    	var place = this.textbox.value;
    	
    	// get the city name (either default or, if there is a comma in the user query, take it from there)
    	var city  = this.cityName;
    	var cityDelPos = place.indexOf(',');
    	if (cityDelPos >= 0){
    		city = place.substr(cityDelPos + 1, place.length - cityDelPos - 1);
    		if (city.trim() == '')
    			city = this.cityName;
    		place = place.substr(0, cityDelPos); // only consider everything before the comma
    	}
    	
    	// only call for geo provider when the search string is not empty
		if (place.length > 0){			
			this.searchAddress({
				currentGeocoder: this.companyGeocoder,
				place:		place,
				city: 		city,
				province:	this.cityProvince,
				centerLat:  window.companyCityCenter.lat,
				centerLong: window.companyCityCenter.lng,
			});
		}
    },
    
    /*
     * @param.geocoder
     * @param.place
     * @param.city
     * @param.province
     */
    processSearchResults: function(params) {
    	var _this = this;
    	
    	var geocoder = params.geocoder || this.geoFactory.getProvider({mapProvider: this.companyGeocoder});
    	var place = params.place;
    	var city = params.city;
    	var province = params.province;
    	var results = params.results;
    	
    	//looking for city results, if there are city level results, let's compliment the original results with those
    	if (city == this.cityName){
    		geocoder.searchAddress({
    			query: place,
    			indexes: 'Geography',
    			centerLat: window.companyCityCenter.lat,
    			centerLong: window.companyCityCenter.lng,
    			onComplete: function(cityResults){
					if (results && results.length)
    					results = results.concat(cityResults);
    				else results = cityResults;
    				
    				if (results && results.length)
    					return _this.onPlacesSearchResultsReceived(results);
    				else {
    					// if no results received, try searching by street name only
						console.log('No exact address match, trying removing house number now...');
						geocoder.searchAddress({
							query:	    place,
							place:		_this._removeHouseNumbers(place),
							altered:	true,
							city: 		city,
							province:	province,
							indexes:	'Street',
							centerLat:  window.companyCityCenter.lat,
							centerLong: window.companyCityCenter.lng,  
							onComplete: function(streetResults) {
								if (streetResults && streetResults.length){
									return _this.onPlacesSearchResultsReceived(streetResults);
								}
								else {
									// if not needed, search for places
									console.log('Still no match, trying places search');
									geocoder.searchPlace({
										place:		place,
										city: 		city,
										province:	province,
										indexes:	'POI',
										centerLat:  window.companyCityCenter.lat,
										centerLong: window.companyCityCenter.lng,  
										onComplete: function(placeResults) {
											var cityCenter = new ginger.geo.AddressPoint({
												lat:             window.companyCityCenter.lat,
												lng:             window.companyCityCenter.lng,
												freeformAddress: place,
												streetNumber:    '',
												streetName:      place,
												city:            city,
												postalCode:      '',
												source:			 'approximation',
												level:			 'City'
											});
											if (placeResults && placeResults.length){
												return _this.onPlacesSearchResultsReceived(placeResults.concat([cityCenter]));
											}
											else {
												// no places found either, returning city center
												_this.onPlacesSearchResultsReceived([cityCenter]);
											}
										}
									});
								}
							}
						});
    				}
    			}
    		});
    	}
    	else {
    		//the search was done with mentioning of the city name
    		if (results && results.length){
				return this.onPlacesSearchResultsReceived(results);
			}
			else {
				// if no results received, try searching by street name only
				console.log('No exact address match, trying removing house number now...');
				geocoder.searchAddress({
					query:	    place,
					place:		_this._removeHouseNumbers(place),
					altered:	true,
					city: 		city,
					province:	province,
					indexes:	'Street',
					centerLat:  window.companyCityCenter.lat,
					centerLong: window.companyCityCenter.lng,  
					onComplete: function(streetResults) {
						if (streetResults && streetResults.length){
							return _this.onPlacesSearchResultsReceived(streetResults);
						}
						else {
							// if not needed, search for places
							console.log('Still no match, trying places search');
							geocoder.searchPlace({
								place:		place,
								city: 		city,
								province:	province,
								indexes:	'POI',
								centerLat:  window.companyCityCenter.lat,
								centerLong: window.companyCityCenter.lng,  
								onComplete: function(placeResults) {
									var cityCenter = new ginger.geo.AddressPoint({
										lat:             window.companyCityCenter.lat,
										lng:             window.companyCityCenter.lng,
										freeformAddress: place,
										streetNumber:    '',
										streetName:      place,
										city:            city,
										postalCode:      '',
										source:			 'approximation',
										level:			 'City'
									});
									if (placeResults && placeResults.length){
										return _this.onPlacesSearchResultsReceived(placeResults.concat([cityCenter]));
									}
									else {
										// no places found either, returning city center
										_this.onPlacesSearchResultsReceived([cityCenter]);
									}
								}
							});
						}
					}
				});
			}
    	}
		
	},
    
    /*
     * Goes through all address elements and removes all that start with a digit
     */
    _removeHouseNumbers: function(place) {
    	var addressElements = place.replace(',', ' ').split(' ');
    	var newElements = [];
    	var reg = new RegExp("^[0-9]");
    	for (var i in addressElements) {
    		if (reg.test(addressElements[i])) {
    			continue;
    		}
    		else {
    			newElements.push(addressElements[i]);
    		}
    	}
    	var result = newElements.join(' ');
    	console.log('Result of removing house number: ' + result);
    	return result;
    },
    
    _getCompanyGeocoder: function() {
    	if (!window.companyGeoProviders) {
    		console.error('Company geocoder is not defined');
    		return null;
    	}
    	var companyGeocoder = window.companyGeoProviders.split(';');
    	if (!companyGeocoder.length) {
    		console.error('Company geocoder is malformatted');
    		return null;
    	}
		return companyGeocoder[0];
    },
		
	onPlacesSearchResultsReceived: function(results) {
		var store = this._getPredefinedLocations();
		
		dojo.forEach(results, function(place) {
		    var item = {};
		    item[place.toString()] = place.freeformAddress;
		    store.push(item);
		});
		
		// display the search results
		this.store = new dojox.data.KeyValueStore({
			dataVar: store
		});
		this._startSearch(this.focusNode.value.replace(/([\\\*\?])/g, "\\$1"));
	},
	
	_startSearchFromInput: function(){
		this._showSearchedCity(); //need to decide, if we the city name where the search is done should be shown as a place holder, or not
		
		if (this.timer) {
			this.timer.stop();
		}
		else {
			this.timer = new dojox.timing.Timer(1000);
			this.timer.onTick = dojo.hitch(this, this.searchPlaces);
		}
		
		this.timer.start();
	},
	
	_showSearchedCity: function(){
		var query = this.textbox.value;
		
		var cityDelPos = query.indexOf(',');
		if (query.trim() == '' || cityDelPos >= 0) { //we already have comma in the input or there is no input yet
			this.placeHolder = this.defaultPlaceholder;
		}
		else { //we do not have comma in the input, lets show user that they are searching in the default city
			this.placeHolder = ', ' + this.cityName;
		}
		this._setPlaceHolderAttr(this.placeHolder);
		
	},
	
	_updatePlaceHolder: function(){
		this.inherited(arguments);
		
		var ruler = dojo.byId(this.id + '_ruler');
		ruler.innerHTML = this.textbox.value;
		var query = this.textbox.value.trim();

		var selectedValue = this.attr('value');
		if (selectedValue){
			selectedValue = selectedValue.split(',');
			selectedValue = selectedValue[2]; //freeformAddress field
		}

		//if no value in dropdown was selected, or the selected value does not matches when user sees in the textbox
		var check = (!selectedValue || selectedValue != this.textbox.value); 

		if (this._phspan){
			this._phspan.style.display = (this.placeHolder && 
				(
					(check && !query && !this._focused) || 
					(check && query && query.indexOf(',') < 0)
				)) ? "" : "none";
			this._phspan.style.color   = this._focused ? '#FFFFFF' : '#C3C3C3';
			this._phspan.style.left    = ruler.offsetWidth ? (ruler.offsetWidth + 2) + 'px' : '0';
			this._phspan.style.top     = '3px';
		}
		ruler = null;
	},
	
	_startSearch: function(key){
		this.inherited(arguments);
	},
	
	/*_getValueAttr: function() {
		return this.textbox.value+'#'+this.value;
	},*/
	
	_setValueAttr: function(value, priorityChange){
		var parts = value.split(',');
		this.value = this.valueNode.value = value;
		this._lastDisplayedValue = this.textbox.value = parts[2];
		//this.inherited(arguments);
	},
	
	_setDisplayedValueAttr: function(/*String*/ value){
		this.inherited(arguments);
		this._updatePlaceHolder();
	},
	
	_getPredefinedLocations: function() {
		var item = {};
		var store = [];
		var newItem = {};
		var self = this;
		
		if (this.form.lastLocationsStore) {
			var llStore = this.form.lastLocationsStore;
			llStore.fetch({
				query: {
					value: self.textbox.value+'*'
				},
				queryOptions: {
					ignoreCase: true
				},
				onItem: function(item, request) {
					newItem = {};
					newItem[llStore.getValue(item, 'key')] = llStore.getValue(item, 'value');
					store.push(newItem);
				}
			});
		}
		
		return store;
	},
});