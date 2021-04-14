dojo.provide("ginger.geo.provider.Factory");
dojo.require("ginger.geo.provider.Decarta");
dojo.require("ginger.geo.provider.Mapquest");
dojo.require("ginger.geo.provider.Cloudmade");
dojo.require("ginger.geo.provider.Foursquare");


var MAP_PROVIDER_GOOGLE  = 'google';
var MAP_PROVIDER_DECARTA = 'decarta';
var MAP_PROVIDER_FOURSQUARE = 'foursquare';
var MAP_PROVIDER_CLOUDMATE = 'cloudmade';
var MAP_PROVIDER_MAPQUEST = 'mapquest';
var MAP_PROVIDER_DEFAULT = MAP_PROVIDER_DECARTA;

dojo.declare("ginger.geo.provider.Factory", null, {
	
	getProvider: function(params) {
		if (!params) params = {};
		
		var provider = params.mapProvider || MAP_PROVIDER_DEFAULT;
		var language = window.companyMapLanguage || 'EN';
		var countryCode = window.companyCountryCode || 'US';
		var countryName = window.companyCountryName || 'United States of America';
		var unitSystem = window.unitSystem || 'MI';
		
		// initialize the provider		
		switch(provider) {
			case MAP_PROVIDER_DECARTA:
				return new ginger.geo.provider.Decarta({
					language: 	 language,
					country: 	 countryCode,
					countryName: countryName,
					unitSystem:  unitSystem
				});
				break;			
			case MAP_PROVIDER_GOOGLE:
				console.error('Google is not yet supported');
				break;
			case MAP_PROVIDER_FOURSQUARE:
				return new ginger.geo.provider.Foursquare({});
				break;
			case MAP_PROVIDER_CLOUDMATE:
				return new ginger.geo.provider.Cloudmade({
					locale:  language,
					country: countryName,
					unitSystem: unitSystem
				});
				break;
			case MAP_PROVIDER_MAPQUEST:
				return new ginger.geo.provider.Mapquest({country: countryName, locale: language});
				break;
			default: 
				console.error('Specifed map provider ' + provider + ' is not supported');
				break;
		}
	},
	
	/*
	 * Function returns which exactly geo provider should be used for this company (i.e. decarta, mapquest, cloudmade, etc)
	 * type may be: 0) 'geocoder', 1) 'maptiles', 3) 'routing'
	 */
	getCompanyProvider: function(type) {
    	if (!window.companyGeoProviders) {
    		console.error('Company geocoder is not defined');
    		return null;
    	}
    	var providers = window.companyGeoProviders.split(';');
    	if (!providers.length) {
    		console.error('Company GeoProviders are malformatted');
    		return null;
    	}
    	
    	switch(type){
    		case 'geocoder': return providers[0]; break;
    		case 'maptiles': return providers[1] || providers[0]; break;
    		case 'routing':  return providers[2] || providers[0]; break;
    		default: return providers[0];
    	}
    }
});