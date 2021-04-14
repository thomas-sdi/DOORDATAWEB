dojo.provide("ginger.geo.provider.Nokiahere");
dojo.require("ginger.geo.provider.Leafletgeo");
dojo.require("dojo.io.script");
dojo.require("ginger.geo.AddressPoint");

dojo.declare("ginger.geo.provider.Nokiahere", ginger.geo.provider.Leafletgeo, {
	
	locale: 'EN',
	country: null,
	
	app_id: null,
	app_code: null,
	tilesUrl: null,
	
	platform: null,
	
	
	/*
	 * @param.locale, language to be used during the search, i.e. 'en', 'el', 'nl'
	 * @param.app_id - from nokia here site
	 * @param.authenticationToken = from nokia here ste
	 */
	constructor: function(params){
		
		this.app_id = params.app_id ? params.app_id : 'elZI3QRynykK7ciZmRJ9';
		this.app_code = params.app_code ? params.app_code : 'U8TWTvDV-judyAIOBynVHw';
		this.tilesUrl = 'http://{s}.{base}.maps.cit.api.here.com/maptile/2.1/maptile/{mapID}/normal.day/{z}/{x}/{y}/256/png8?app_id={app_id}&app_code={app_code}';
		this.attribution = 'Map &copy; 1987-2014 <a href="http://developer.here.com">HERE</a>';
		
		this.locale = params.locale ? params.locale : 'EN';
		this.country = params.country || 'United States of America';
		
		this.platform = new H.service.Platform({
			app_id: this.app_id,
			app_code: this.app_code,
			useCIT: true,
			useHTTPS: true
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

		this.map = L.map(mapContainer, {drawControl : params.drawControl ? params.drawControl : false}).setView([params.cityLat, params.cityLong], 12);
		
		// add attribution to the provider
		L.tileLayer(this.tilesUrl, 
			{	
				minZoom: 0,
				maxZoom: 18,
				scrollWheelZoom: params.hasOwnProperty('scrollWheelZoom') ? params.scrollWheelZoom : true,
				touchZoom: params.hasOwnProperty('touchZoom') ? params.touchZoom : true,
				base: 'base',
				attribution: this.attribution,
				subdomains: '1234',
				mapID: 'newest',
				app_id: this.app_id,
				app_code: this.app_code
				
			}).addTo(this.map);
		
		this.redrawMap();
		
		return this.map;
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
	searchAddress: function(searchParams) {
		if (!searchParams.onComplete) {
			console.error('NOKIA HERE: Callback not provided to searchAddress function');
			return;
		}
		
		var self = this;
		
		var geocoder = this.platform.getGeocodingService();
		
		var geocodingParams = {
			street: searchParams.address,
			city: searchParams.city,
			state: searchParams.state,
			country: searchParams.country,
			jsonattributes: 1 //this juse indicates the naming convention for the returned json
		};
		
		geocoder.geocode(
			geocodingParams,
			function(result){ //this is the onSuccess function
				searchParams.onComplete(self._formatPlaces(result.response.view));
			},
			function(error){ //this is the onError function
				searchParams.onError ? searchParams.onError() : searchParams.onComplete([]);
			}
			
		);
	},

	_formatPlaces: function(places){
		var result = [];
		
		dojo.forEach(places, function(place){
			
			var p = place.result[0];
			
			var level = '';
			
			switch (p.MatchLevel){
				case 'country':  case 'state': case 'county': case 'city':
				case 'district':
					level = 'City'; break;
				case 'street': case 'intersection':
					level = 'Street'; break;
				case 'houseNumber':
					level = 'House'; break;
				case 'postalCode':
					level = 'City'; break;
				case 'landmark':
					level = 'POI'; break;
				default: break;
			}
			
			var address = new ginger.geo.AddressPoint({
				lat:				p.location.displayPosition.latitude,
				lng:				p.location.displayPosition.longitude,
				freeformAddress:	p.location.address.label,
				streetNumber:		p.location.address.houseNumber,
				streetName:			p.location.address.street,
				city:				p.location.address.city,
				postalCode:			p.location.address.postalCode,
				source:				'nokiahere',
				level:				level
			});
			result.push(address);
			
		});
		return result;
	}
	
});