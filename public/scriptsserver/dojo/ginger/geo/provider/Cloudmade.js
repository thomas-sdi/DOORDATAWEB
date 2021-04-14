dojo.provide("ginger.geo.provider.Cloudmade");
dojo.require("ginger.geo.provider.Leafletgeo");
dojo.require("dojo.io.script");
dojo.require("ginger.geo.AddressPoint");

dojo.declare("ginger.geo.provider.Cloudmade", ginger.geo.provider.Leafletgeo, {
	
	locale: 'EN',
	key: null,
	link: null,
	country: null,
	tilesUrl: null,
	attribution: null,
	routeUrl: null,
	unitSystem: null,
	
	/*
	 * @param.locale, language to be used during the search, i.e. 'en', 'el', 'nl'
	 * @param.key, this is provided by cloudmade.com
	 */
	constructor: function(params){
		this.locale = params.locale ? params.locale : 'EN';
		this.key = params.key ? params.key : '1dfb34dde854414e8a543d8300b15931';
		this.link = 'http://beta.geocoding.cloudmade.com/v3/';
		this.country = params.country || 'United States of America';
		this.tilesUrl = 'http://{s}.tile.cloudmade.com/' + this.key + '/997/256/{z}/{x}/{y}.png';
		this.attribution = 'Map Server CloudMade. Map data &copy; 2014 OSM';
		this.routeUrl = 'http://routes.cloudmade.com/' + this.key + '/api/0.3/';
		
		if (params.unitSystem){
			this.unitSystem = (params.unitSystem == 'KM' ? 'km' : 'miles');
		}
		else this.unitSystem = 'miles';
	},
	
	/**
	 * No address search supported - return null
	 */
	searchAddress: function(params) {
		if (!params.onComplete) {
			console.error('CLOUDMADE: Callback not provided to searchAddress function');
			return;
		}
		
		console.log('CLOUDMADE: search address is not implemented');
		
		params.onComplete([]);
	},
	
	/*
	 * @param {string} city: city where the address should be searched, i.e. Charlotte
	 * @param {string} province (if available)
	 * @param {string} place: address to be found
	 * @param {object} onComplete
	 */
	searchPlace: function(params){
		if (!params.onComplete) {
			console.error('CLOUDMADE: Callback not provided to searchPlace function');
			return;
		}
		
		var _this = this;
		
		var province = '';
		if (params.province){
			province = '[state=' + params.province + ']';
		}
		var searchQuery = '[country=' + this.country + '][city=' + params.city + ']' + province + params.place;
		
		dojo.io.script.get({
			url: this.link + this.key + '/api/geo.location.search.2',
			callbackParamName: 'callback',
			content: {
				format: 'json',
				source: 'OSM',
				enc:	'UTF-8',
				limit: '10',
				locale: this.locale,
				q: searchQuery
			},
			load: function(results) {
				params.onComplete(_this._formatPlaces(results.places));
			},
			error: function(error) {
				console.error('Search places returned error: ' + error);
				params.onError? params.onError() : params.onComplete([]);
			}
		});
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
				attribution: this.attribution
			}).addTo(this.map);
		
		this.redrawMap();
		
		return this.map;
	},
	
	/*
	 * We are going to extract data from the cloudmade format and use it with our own format
	 */
	_formatPlaces: function(places){
		var result = [];
		dojo.forEach(places, function(place){
			var freeformAddress = (place.streetNumber || '') + ' ' + (place.street || '') + ' '
								+ (place.city || '') + ' ' + (place.state || '') + ' ' + (place.country || '') + ' ' + (place.zip || '');
			freeformAddress = freeformAddress.trim();
			
			var address = new ginger.geo.AddressPoint({
				lat:             place.position.lat,
				lng:             place.position.lon,
				freeformAddress: freeformAddress,
				streetNumber:    place.streetNumber || '',
				streetName:      place.street || '',
				city:            place.city || '',
				postalCode:      place.zip || ''
			});
			result.push(address);
		});
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
		
		//now let's add all waypoints to the request
		var l = params.waypoints.length;
		var waypoints = params.waypoints[0].lat +',' + params.waypoints[0].lng;
		if (l == 2){
			//this means there is only pick up and drop off points
			waypoints += ',' + params.waypoints[1].lat + ',' + params.waypoints[1].lng;
		}
		else{
			//these means there are more points, all the interim points need to be surrounded by [] brackets
			waypoints += ',[';
			for (var i = 1; i < l - 1; i++){
				waypoints += params.waypoints[i].lat + ',' + params.waypoints[i].lng + ',';
			}
			waypoints = waypoints.substr(0, waypoints.length - 1) + '],' + params.waypoints[l-1].lat + ',' + params.waypoints[l-1].lng;
		}
		
		var url = this.routeUrl + waypoints + '/car/shortest.js';
		
		//ask server to generate the route
		dojo.io.script.get({
			url: url,
			callbackParamName: 'callback',
			units: this.unitSystem,
			load: function(route){
				self.route = route;
				
				//get distance in meters and then convert it to miles or kilometers depending on the company UOM
				var distance = route.route_summary['total_distance']; 
				if (self.unitSystem == 'km')
					distance = Math.round(distance * 0.001 * 100) / 100;
				else if(self.unitSystem == 'miles'){
					distance = Math.round(distance * 0.000621371 * 100) / 100;
				}
				
				// parse the route polygon
				var polygon = [];
				dojo.forEach(route.route_geometry, function(point){
					polygon.push({lat: point[0], lng: point[1]});
				});
				
				var polyline  = self.showRoute({polygon: polygon});
				params.onComplete({
					polyline: polyline,
					distance: {value: distance, uom: self.unitSystem}
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
		
		dojo.forEach(this.route.route_geometry, function(point){
			routeArray += '"(' + point[0] + ',' + point[1] + ')",';
		});
		// remove last comma
		if (routeArray.length > 2)
			routeArray = routeArray.substr(0, routeArray.length - 1);
		
		return routeArray + ']]';
	},
	
	getProviderName: function(){
		return 'cloudmade';
	}
	
});