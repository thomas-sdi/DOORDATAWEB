dojo.provide("ginger.geo.provider.Foursquare");
dojo.require("dojo.io.script");
dojo.require("ginger.geo.AddressPoint");

dojo.declare("ginger.geo.provider.Foursquare", null, {
	
	link: null,
	client_id: null, 		//obtained from foursquare
	client_secret: null,	//obtained from foursquare
	
	constructor: function(params){
		this.link = 'https://api.foursquare.com/v2/venues/search/';
		this.client_id = params.client_id || 'T3SGEYQTXLRUJMCXHV4DXLBTFW5GXJIR4XHYORV5QBAFPBGQ';
		this.client_secret = params.client_secret || '1YFKGPN2JP1LO5S2RW4SPRHJIPTI0UYKF5E2E2X1ZIGRNARC';
	},
	
	/**
	 * No address search supported - return null
	 */
	searchAddress: function(params) {
		if (!params.onComplete) {
			console.error('FOURSQUARE: Callback not provided to searchAddress function');
			return;
		}
		
		params.onComplete();
	},
	
	/*
	 * @param {number} lat - latitude
	 * @param {number} lng - longitude
	 * @param {string} query: place to be found, as typed by user
	 * @param {object} onComplete
	 */
	searchPlace: function(params){
		if (!params.onComplete) {
			console.error('FOURSQUARE: Callback not provided to searchPlaceRest function');
			return;
		}
		
		var _this = this;
		
		dojo.io.script.get({
			url: this.link,
			callbackParamName: 'callback',
			content: {
				ll: params.lat + ',' + params.lng,
				radius: 50000, //in meters, 100'000 m is the maximum value foursquare accepts
				query: params.query,
				limit: 10,
				intent: 'browse',
				client_id: this.client_id,
				client_secret: this.client_secret,
				v: '20130217' //using api version as of Feb 17. 2013
			},
			load: function(results) {
				if(!results.response || !results.response.venues)
					params.onComplete([]);
				else {
					params.onComplete(_this._formatPlaces(results.response.venues));
				}
			},
			error: function(error) {
				console.error('Search places returned error: ' + error);
				params.onError? params.onError() : params.onComplete([]);
			}
		});
	},
	
	/*
	 * We are going to reformat Forsquare places to our own format
	 */
	_formatPlaces: function(places){
		var result = [];
		dojo.forEach(places, function(place){
			var address = new ginger.geo.AddressPoint({
				lat:             place.location.lat || '',
				lng:             place.location.lng || '',
				freeformAddress: (place.name || '') + ' ' + (place.location.address || '') + ' ' + (place.location.city || ''),
				streetNumber:	 '',
				streetName:      place.location.address || '',
				city:            place.location.city || '',
				postalCode:      place.location.postalCode || '',
				source:			 'foursquare',
				level:			'POI'
			});
			result.push(address);
		});
		
		return result;
	},
	
	getProviderName: function(){
		return 'foursquare';
	}
});