dojo.provide("ginger.geo.AddressPoint");

dojo.declare("ginger.geo.AddressPoint", null, {
	
	lat: '',
	lng: '',
	streetName: '',
	streetNumber: '',
	city: '',
	postalCode: '',
	freeformAddress: '',
	poiName: '',
	source: '',
	level: '',
	
	/*
	 * @param addressString 'lat,lng,streetName,streetNumber,city,postalCode'
	 * @param lat
	 * @param lng
	 * @param streetName
	 * @param streetNumber
	 * @param city
	 * @param postalCode
	 * @param poiName
	 * @param source
	 * @param level POI, House, Street, City
	 */
	constructor: function(params) {
		if (params.addressString) {
			var addressArray = params.addressString.split(',');
			this.lat             = addressArray.length > 0 ? addressArray[0] : '';
			this.lng             = addressArray.length > 1 ? addressArray[1] : '';
			this.freeformAddress = addressArray.length > 2 ? addressArray[2] : '';
			this.streetName      = addressArray.length > 3 ? addressArray[3] : '';
			this.streetNumber    = addressArray.length > 4 ? addressArray[4] : '';
			this.city            = addressArray.length > 5 ? addressArray[5] : '';
			this.postalCode      = addressArray.length > 6 ? addressArray[6] : '';
			this.poiName         = addressArray.length > 7 ? addressArray[7] : '';
			this.source			 = addressArray.length > 8 ? addressArray[8] : '';
			this.level			 = addressArray.length > 9 ? addressArray[9] : '';
		}
		else {
			this.lat = params.lat;
			this.lng = params.lng;
			this.streetName = params.streetName;
			this.streetNumber = params.streetNumber;
			this.city = params.city;
			this.postalCode = params.postalCode;
			this.freeformAddress = params.freeformAddress;
			this.poiName = params.poiName;
			this.source = params.source;
			this.level = params.level;
		}
		
	},
	
	toString: function() {
		return [
			this.lat, this.lng,
			this.freeformAddress ? this.freeformAddress.replace(/,/g, ' ') : '',
			this.streetName ? this.streetName.replace(/,/g, ' ') : '',
			this.streetNumber ? this.streetNumber.replace(/,/g, ' ') : '',
			this.city ? this.city.replace(/,/g, ' ') : '',
			this.postalCode ? this.postalCode.replace(/,/g, ' ') : '',
			this.poiName ? this.poiName.replace(/,/g, ' ') : '',
			this.source ? this.source : '',
			this.level ? this.level : ''
		].join();
	}
	
});
