dojo.provide("ginger.geo.provider.Decarta");
dojo.require("ginger.geo.provider.Leafletgeo");
dojo.require("dojo.io.script");
dojo.require("ginger.geo.AddressPoint");
dojo.require("ginger.geo.provider.Foursquare");

dojo.declare("ginger.geo.provider.Decarta", ginger.geo.provider.Leafletgeo, {
	
	defaultLanguage: 'EN',
	defaultCountry: 'US',  
	defaultCountryName: 'United States of America',
	locale: null,
	searchUrl: '',
	mapMarkers: [],
	unitSystem: null, 
	tilesUrl: '',
	attribution: '',
	foursquare: null,
	route: null,
	
	constructor: function(params) {
		if (!params) params = {};
		
		// initializing decarta configuration
		deCarta.Core.Configuration.url = window.deCartaUrl;
		deCarta.Core.Configuration.AppKey = window.deCartaKey;
		
		deCarta.Core.Configuration.language = params.language || this.defaultLanguage;
		deCarta.Core.Configuration.country = params.country || this.defaultCountry;
		
		this.tilesUrl = deCarta.Core.Configuration.url + '/v1/{key}/tile/{x}/{y}/{z}.png';
		this.attribution = 'Map Server deCarta. Map data &copy; 2014 TomTom';
		
		this.locale = new deCarta.Core.Locale(
    			deCarta.Core.Configuration.language,
    			deCarta.Core.Configuration.country
    	);
    	
    	this.baseUrl = window.deCartaUrl + '/' + window.deCartaVersion + '/' + window.deCartaKey + '/'; 
    	
    	this.unitSystem = params.unitSystem ? params.unitSystem : 'MI';
    	
    	this.foursquare = new ginger.geo.provider.Foursquare({});
	},
	
	/**
 	 * @param {string}	place: address to be searched, as typed by user
 	 * @param {string}  city
 	 * @param {string}  province
 	 * @param {number}  centerLat
 	 * @param {number}  centerLong
 	 * @param {Object}  onComplete 
 	 * @param {bool}	altered - shows if the search term was altered (house number removed)
	 */
	searchAddress: function(params) {
		if (!params.onComplete) {
			console.error('DECARTA: Callback not provided to searchAddress function');
			return;
		}
		
		// search an exact address
		this.searchPlace({
			query: 	    params.query || null, //if the query was altered, this is original one
			place:		params.place || null, //if the query was altered, this is the altered one
			altered:	params.altered || false,
			city: 		params.city || '',
			province: 	params.province || '',
			place: 		params.place || '',
			lat: 		params.centerLat,
			lon: 		params.centerLon,
			indexes:	params.indexes || 'Address',
			onComplete: function(dcResult){
				params.onComplete(dcResult);
			}
		});
	},
	
	/*
	 * This function talks to the deCarta GeoCoding API and sends back the results
	 * @param indexes List what level of search do we need: POI or a particular street address 
	 */
	searchPlace: function(params){
		if (params.indexes != 'POI'){
		
			var _this = this;
		
			var province = ''; // params.province ? ' ' + params.province : ''; <<< by some reason when province is appended, the search in decarta stops working 
		
			var searchTerm = (params.place || params.query) + ' ' + params.city + province;
			var indexes = params.indexes || 'POI,PAD,Street';
			
			dojo.io.script.get({
				url: this.baseUrl + 'search/' + encodeURIComponent(searchTerm) + '.json',
				callbackParamName: 'callback',
				timeout: 2000,
				content: {
					typeahead: 'true',
					limit: 10,
					indexes: indexes,
					lat: params.centerLat, 
					lon: params.centerLong,
					countrySet: deCarta.Core.Configuration.country
				},
				load: function(results) {
					var dcResult = _this._formatPlaces({
						places: results.results,
						searchCity: params.city,
						searchQuery: params.query,
						searchPlace: params.place,
						indexes: indexes,
						altered: params.altered || false,
					});
					params.onComplete(dcResult);
				},
				error: function(error) {
					console.error('Search places returned error: ' + error);
					params.onError? params.onError() : params.onComplete();
				}
			});
		}
		else {
			if (!params.onComplete) {
				console.error('DECARTA: Callback not provided to searchPlaceRest function');
				return;
			}
		
			this.foursquare.searchPlace({
				lat: params.centerLat,
				lng: params.centerLong,
				query: params.place || params.query,
				onComplete: function(fqResults){
					params.onComplete(fqResults);
				}
			});
		}
	},
	
	/*
	 * @param places
	 * @param searchCity
	 * @param searchQuery //if query was altered, this is the original one
	 * @param searchPlace //if query was altered, this is the altered one
	 * @param indexes
	 * @param altered - shows if the initial term was altered
	 */
	_formatPlaces: function(params) {
		var result = [];
		var _this = this;
		var includeResult = true;
		dojo.forEach(params.places, function(place){
			if (params.indexes == 'Geography' && place.type != 'Geography'){
				return;
			}
			
			// filter out addresses which aren't within the city of search
			var placeCity = _this._extractLocalLanguage(place.address.municipality || '', 'Foreign');
			
			// figure out the level of match
			var query = params.searchQuery || params.searchPlace;
			var matchLevel = '';
			switch(place.type){
				case 'POI': matchLevel = 'POI'; break;
				case 'Address Range': matchLevel = 'House'; break;
				case 'Street': matchLevel = 'Street'; break;
				case 'Geography': matchLavel = 'City'; break;
				default: matchLevel = (params.searchPlace == query) ? 'House' : 'Street'; break;
			}
			
			//decarta's freeformAddress field street name is returned in English, we would want it in native language, if possible
			var freeformAddress = place.address.freeformAddress;

			
			// if the match is Street, then the result address should contain the original search query
			var address = place.type == 'POI' ?
				place.poi.name + ', ' + freeformAddress : 
				((matchLevel == 'Street' && params.altered == true) ? 
					params.searchQuery + ' ' + placeCity + ' ' + (place.address.postalCode || '')
					: freeformAddress);
			
			//try to figure out what the house number should be in case if the query was altered	
			var houseNumber = '';
			if (matchLevel == 'Street' && params.altered) {
				houseNumber = params.searchQuery.replace(params.searchPlace, ''); 
				houseNumber = houseNumber.trim();
			}
			
			
			if (includeResult || (matchLevel != 'Street' || params.altered != true)) {
				var address = new ginger.geo.AddressPoint({
					lat:             place.position.lat,
					lng:             place.position.lon,
					freeformAddress: address,
					streetNumber:    place.address.streetNumber || houseNumber, //here, in case no street number was returned but the query was altered, insert the removed piece
					streetName:      _this._extractLocalLanguage(place.address.streetName || '', 0),
					city:            placeCity,
					postalCode:      _this._extractLocalLanguage(place.address.postalCode || '', 0),
					source:			 'decarta',
					level:			 matchLevel
				});
				result.push(address);
			}
			
			//this is needed to avoid multiple results with the only difference in the ZIP code
			if (matchLevel == 'Street' && params.altered == true){
				includeResult = false;
			}
		});
		return result;
	},
	
	
	/*
	 * we are assuming that STR has the following structure: "Language 0, Language 1". 
	 * The function returns the part of the string in the specific language (index parameter is used to say which)
	 * sometimes
	 * @param.str
	 * @param.index (either 0 or 1)
	 */
	_extractLocalLanguage: function(str, index){	
		
		if (deCarta.Core.Configuration.language == 'EN'){
			return str;
		}

		if (str.length == 0) {return str;}
			
		var trns = str.split(',');
		
		//if we are just looking for a specific index
		if (typeof index == 'number'){
			if (trns.length > index){
				return trns[index];
			}
			else return trns[0];
		}		
		
		//means we need to specifically look for foreign characters
		if (trns.length == 1) {
			return trns[0];
		}
		
		var nonlatin = /[^\u0000-\u007F]+/g;
		for (var i = 0; i < trns.length; i++){
			if (nonlatin.test(trns[i])){ //we are only interested in non-latin characters, if they exist
				return trns[i];
				break;
			}
		}
		
		//if nothing was found, return the first element
		return trns[0];
	},
	
	/** 
 	 * @param centerLat
     * @param centerLong
     * @param container 	What is the div container name (map_canvas by default) 
	 */
	createMap: function(params) {
		var self = this;
		var mapContainer = params.container || 'map_canvas';
		
		this.map = L.map(mapContainer, {drawControl : params.drawControl ? params.drawControl : false}).setView([params.centerLat, params.centerLong], 12);
		
		// add attribution to the provider
		L.tileLayer(this.tilesUrl, {maxZoom: 18,
				attribution: this.attribution,
				key: deCarta.Core.Configuration.AppKey
			}).addTo(this.map);
		
		this.redrawMap();
		
		return this.map;
	},
	
	
	/**
	 * @param waypoints [{lat, lng}, {lat, lng}, ...]
	 * @param onComplete
	 */
	generateRoute: function(params) {
		var self = this;
		self.map = this.map;
		
		// check if a callback function was provided
		if (!params.onComplete || !dojo.isFunction(params.onComplete)) {
			console.error('Cannot generate a route as no callback function was provided');
			return;
		}
		
		// check if there are at least 2 waypoints in the route
		if (!params || !params.waypoints || params.waypoints.length < 2) {
			console.error('Cannot show a route with less than 2 waypoints');
			params.onComplete();
		}
		
		//destination point
		var l = params.waypoints.length - 1;
		
		//waypoints, including start and finish (as required by decarta api)
		var routeCriteria = new deCarta.Core.RouteCriteria();
		routeCriteria.waypoints = [];
		routeCriteria.distanceUnit = this.unitSystem;
		for (i = 0; i <= l; i++){
			routeCriteria.waypoints.push(params.waypoints[i].lat + ',' +params.waypoints[i].lng);
		}
		
		//ask server to generate the route
		dojo.io.script.get({
			url: this.baseUrl + 'route' +
				'/from/' + params.waypoints[0].lat + ',' + params.waypoints[0].lng +
				'/to/'   + params.waypoints[l].lat + ',' + params.waypoints[l].lng +
				'.json',
			callbackParamName: 'callback',
			content: {
				criteria: dojo.toJson(routeCriteria),
			},
			load: function(route) {
				self.route = route;
				
				// parse the route polygon
				var polygon = [];
				dojo.forEach(route.routeGeometry, function(point){
					var pointParsed = point.split(',');
					polygon.push({lat: pointParsed[0], lng: pointParsed[1].trim()});
				});
				var polyline  = self.showRoute({polygon: polygon});
				params.onComplete({
					polyline: polyline,
					distance: route.routeSummary['totalDistance']
				});
			},
			error: function(error) {
				console.error('Route generation returned error: ' + error);
				params.onError ? params.onError() : params.onComplete();
			}
		});
	},
	
	getRouteString: function(params) {
		if (!this.route) {
			console.error('Cannot convert route to string: no route exists');
			return null;
		}
		var routeArray = '[[';
		
		dojo.forEach(this.route.routeGeometry, function(point){
			routeArray += '"(' + point + ')",';
		});
		// remove last comma
		if (routeArray.length > 2)
			routeArray = routeArray.substr(0, routeArray.length - 1);
		
		return routeArray + ']]';
	},
	
	getProviderName: function(){
		return 'decarta';
	}
});