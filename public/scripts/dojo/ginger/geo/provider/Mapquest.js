dojo.provide("ginger.geo.provider.Mapquest");
dojo.require("ginger.geo.provider.Leafletgeo");
dojo.require("dojo.io.script");
dojo.require("ginger.geo.AddressPoint");
dojo.require("ginger.geo.provider.Foursquare");

dojo.declare("ginger.geo.provider.Mapquest", ginger.geo.provider.Leafletgeo, {
	
	locale: 'EN',
	key: null,
	link: null,
	country: null,
	foursquare: null,
	tilesUrl: null,
	routeUrl: null,
	unitSystem: null,
	
	/*
	 * @param.locale, language to be used during the search, i.e. 'en', 'el', 'nl'
	 * @param.key, this is provided by cloudmade.com
	 */
	constructor: function(params){
		
		this.locale = params.locale ? params.locale : 'EN';
		this.key = params.key ? params.key : 'Fmjtd|luub2l6zl9%2C8w%3Do5-96twqw';
		this.link = 'https://open.mapquestapi.com/geocoding/v1/address';
		this.country = params.country || 'United States of America';
		
		this.tilesUrl = 'https://otile{s}-s.mqcdn.com/tiles/1.0.0/{type}/{z}/{x}/{y}.png';
		this.routeUrl = 'https://open.mapquestapi.com/directions/v2/route';
		
		this.attribution = 'Map server &copy; <a href="https://www.mapquest.com/" target="_blank">MapQuest</a> <img src="https://developer.mapquest.com/content/osm/mq_logo.png" />. Map data &copy; 2014 OSM';
		
		this.foursquare = new ginger.geo.provider.Foursquare({});
		
		if (params.unitSystem){
			this.unitSystem = (params.unitSystem == 'KM' ? 'k' : 'm');
		}
		else this.unitSystem = 'm';
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
				subdomains: '1234',
				type: 'osm'
			}).addTo(this.map);
		
		this.redrawMap();
		
		return this.map;
	},
	
	/*
	 * @param {string} city: city where the address should be searched, i.e. Charlotte
	 * @param {string} province (if available)
	 * @param {string} place: address to be found
	 * @param {object} onComplete
	 */
	searchAddress: function(params) {
		if (!params.onComplete) {
			console.error('MAPQUEST: Callback not provided to searchAddress function');
			return;
		}
		
		//this means the query was altered, but there is no difference between the original and altered query. So no point in running the request again
		if (params.altered = true && params.place == params.query){
			params.onComplete([]);
			return;
		}
		
		//cities are searched in the same request in mapquest, no need for separate request
		if (params.indexes == 'Geography') {
			params.onComplete([]);
			return; 
		}
		
		var _this = this;
		
		dojo.io.script.get({
			url: this.link,
			callbackParamName: 'callback',
			content: {
				outFormat: 	'json',
				key:		this.key,
				country:	this.country,
				state:		params.province,
				city:		params.city,
				street:		params.query || params.place,
				thumbMaps:	false
			},
			load: function(response) {
				params.onComplete(_this._formatPlaces(response.results));
			},
			error: function(error) {
				console.error('Search places returned error: ' + error);
				params.onError? params.onError() : params.onComplete([]);
			}
		});
	},
	
	/*
	 * @param {string} city: city where the address should be searched, i.e. Charlotte
	 * @param {string} centerLat
	 * @param {string} centerLong
	 * @param {string} place: address to be found
	 * @param {object} onComplete
	 */
	searchPlace: function(params){
		if (!params.onComplete) {
			console.error('MAPQUEST: Callback not provided to searchPlaceRest function');
			return;
		}
		
		this.foursquare.searchPlace({
			lat: params.centerLat,
			lng: params.centerLong,
			query: params.place,
			onComplete: function(fqResults){
				params.onComplete(fqResults);
			}
		});
	},
	
	/*
	 * We are going to extract data from the mapquest format and use it with our own format
	 */
	_formatPlaces: function(places){
		var _this = this;
		var result = [];
		var streetAddressFound = false;
		
		dojo.forEach(places, function(place){
			dojo.forEach(place.locations, function(loc){
				var freeformAddress = (loc.street || '') +' ' + (loc.adminArea5 || '') + ' ' + (loc.adminArea4 || '') + ' ' + (loc.adminArea3 || '') 
									+ ' ' + (loc.adminArea1 || '') + ' ' + (loc.postalCode || '');
				freeformAddress = freeformAddress.trim();
									
				var address = new ginger.geo.AddressPoint({
					lat:				loc.latLng['lat'],
					lng:				loc.latLng['lng'],
					freeformAddress:	freeformAddress,
					streetNumber:		'',
					streetName:			loc.street || '',
					city:				loc.adminArea5 || '',
					postalCode:			loc.postalCode || '',
					source:				'mapquest',
					level:				_this._getGeocodeQuality(loc.geocodeQuality)
				});
				
				//Here we are checking if there were at least one point or address type of point found
				if (loc.geocodeQuality == 'POINT' || loc.geocodeQuality == 'ADDRESS'){
					streetAddressFound = true;
				}
				
				result.push(address);
			});
		});
		
		//if the address was found correctly, we are happy and return it back
		if (streetAddressFound == true)
			return result;
		
		//if nothing was found, return
		if (places.length == 0)
			return result;
		
		
		//if the street address is not found, let's check if the street with the same name was found
		//places.providedLocation is the initial request, we are getting just the street name
		var streetName = this._removeHouseNumbers(places[0].providedLocation.street);
		streetName = streetName;
		
		//if the street name found is the same as typed street name, we are going to add house number to it, source then would change to approximation
		for (var i = 0; i < result.length; i++){
			if (result[i].streetName.toLowerCase() == streetName.toLowerCase()){
				result[i].streetName = places[0].providedLocation.street;
				result[i].freeformAddress = result[i].streetName + ' ' + result[i].city + ' ' + result[i].postalCode;
				result[i].source = 'approximation';
			}
		}
		return result;
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
	
	/*
	 * See http://open.mapquestapi.com/geocoding/geocodequality.html#granularity for available values of mqQuality
	 */
	_getGeocodeQuality: function(mqQuality){
		var result = '';
		switch(mqQuality){
			case 'POINT': result = 'POI'; break;
			case 'ADDRESS': result = 'House'; break;
			case 'INTERSECTION': result = 'Street'; break;
			case 'STREET': result = 'Street'; break;
			case 'COUNTRY': result = 'City'; break;
			case 'STATE': result = 'City'; break;
			case 'COUNTY': result = 'City'; break;
			case 'CITY': result = 'City'; break;
			case 'ZIP': result = 'City'; break;
			case 'ZIP_EXTENDED': result = 'City'; break;
			default: result = ''; break;
		}
		return result;
	},
	
	generateRoute: function(params){
		var self = this;
		
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

		//let's set all the request parameters that mapquest expects		
		var jsonParams={
			locations: [],
			options:{
				narrativeType: "none",
				doReverseGeocode: false,
				fullShape: true,
				shapeFormat: "raw",
				generalize: 0,
				unit: this.unitSystem
			}
		};
		
		//now let's add all waypoints to the request
		for (var i = 0; i < params.waypoints.length; i++){
			jsonParams.locations.push({
				"latLng":{"lat": params.waypoints[i].lat, "lng": params.waypoints[i].lng}
			});
		}
		
		//ask server to generate the route
		dojo.io.script.get({
			url: this.routeUrl,
			content: {
				key: this.key,
				outFormat: 'json',
				json: JSON.stringify(jsonParams)
			},
			callbackParamName: 'callback',
			load: function(response){
				
				if(!response || response.info.statuscode != 0){
					//error occurred :(
					console.log('Error when creating a route with mapquest: ' + response.info.messages.toString());
					params.onError ? params.onError() : params.onComplete();
					return;
				}
				
				//so, everything is ok, now continue and parse the route polygon
				var polygon = [];
				var points = response.route.shape.shapePoints;
				for (var i=0; i < points.length - 1; i=i+2 ){
					polygon.push({lat: points[i], lng: points[i+1]});
				}
				self.route = polygon; //remember the route
			
				var polyline = self.showRoute({polygon: polygon});
				params.onComplete({
					polyline: polyline,
					distance: { value: response.route.distance, uon: self.unitSystem == 'k' ? 'KM' : 'MI' }
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
		
		dojo.forEach(this.route, function(point){
			routeArray += '"(' + point.lat + ',' + point.lng + ')",';
		});
		// remove last comma
		if (routeArray.length > 2)
			routeArray = routeArray.substr(0, routeArray.length - 1);
		
		return routeArray + ']]';
	},
	
	getProviderName: function(){
		return 'mapquest';
	}
	
});