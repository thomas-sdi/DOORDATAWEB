dojo.provide("ginger.helper.AddressHelper");

dojo.declare("ginger.helper.AddressHelper", null, {
    
	map: null,
	geocoder: null,
	placesService: null,
	foundAddresses: [],
	geoProvider: null,
	
	constructor: function(params) {
		//this.map = params.map;
		
		// create new geocoder object
		//this.geocoder = new google.maps.Geocoder();
		//this.placesService = new google.maps.places.PlacesService(this.map);
		
		//var deCartaCenter = new deCarta.Core.Position(window.companyCityCenter.lat +", " + window.companyCityCenter.lng);
		
		
	},
   
	searchPlace: function(place, center, onPlaceFound) {
		/*
		var _this = this;
		console.log('Searching for place: ' + place);
		if (!_this.foundAddresses) _this.foundAddresses = [];
	    
		this.placesService.search({
			location: center, 
			radius: 50000, 
			keyword: place
		}, function(results, status){
			console.log('place results received');
			if (status == google.maps.GeocoderStatus.ZERO_RESULTS || status != google.maps.GeocoderStatus.OK) {
				console.error('Place search failed: ' + status);
			}
			else {            
				for (var i in results) {
					var r = results[i];
					console.log('Found place: ' + r.name);
					_this.foundAddresses.push({
						googleAddress: r, 
						streetAddress: r.name, 
						location: r.geometry.location, 
						vicinity: r.vicinity, 
						place: true
					});
				}
			}
                
			onPlaceFound(_this.foundAddresses);
			_this.foundAddresses = [];
		});*/
	},
	
	searchPlaceByLocation: function(location, onPlaceFound) {
		/*
		var _this = this;
		// if (!_this.foundAddresses) _this.foundAddresses = [];
		_this.foundAddresses = [];
						
		this.placesService.search({
			location: location, 
			radius: 50
		}, function(results, status){
			console.log('place results received');
			if (status == google.maps.GeocoderStatus.ZERO_RESULTS || status != google.maps.GeocoderStatus.OK) {
				console.error('Place search failed: ' + status);
			}
			else {            
				for (var i in results) {
					var r = results[i];
					console.log('Found place: ' + r.name);
					_this.foundAddresses.push({
						googleAddress: r, 
						streetAddress: r.name, 
						location: r.geometry.location, 
						vicinity: r.vicinity, 
						place: true
					});
				}
			}
                
			onPlaceFound(_this.foundAddresses);
			_this.foundAddresses = [];
		});
		*/
	},

	searchAddress: function(address, center, onAddressFound) {
		console.log('Searching for address: ' + address);
		
		var myAddress = new deCarta.Core.FreeFormAddress(address, new deCarta.Core.Locale('EN', 'US'));
		 
		deCarta.Core.Geocoder.geocode(myAddress,function(addressResults){
			if (addressResults.err){
				console.log('Error!' + addressResults.err);
				_this.foundAddresses.length = 0;
				_this.foundAddresses = [];
			}
			else{
				console.log('results: ' + JSON.stringify(addressResults));
				_this.foundAddresses = [_this.parseGoogleAddress(addressResults)];
			}
           		
		});
		
		
		/*var _this = this;
		this.geocoder.geocode({
			address: address, 
			location: center
		}, function(results, status){
			if (status != google.maps.GeocoderStatus.OK) {
				console.error('Address search failed: ' + status);
				_this.foundAddresses = [];
			}
			else {
				_this.foundAddresses = [_this.parseGoogleAddress(results)];
			}
             
			_this.searchPlace(address, center, onAddressFound);
		});*/
	},
	
	parseGoogleAddress: function(addressResults) {
		/*
		var streetAddress, vicinity, location;
        
		// resolve the street address and the locality
		for(var i in addressResults) {
			if (i == 0) { // street address
				streetAddress = addressResults[i].formatted_address.split(',')[0];
				location = addressResults[i].geometry.location;
				continue;
			}
            
			if (addressResults[i].types.join().indexOf('locality') >= 0) { // city/province/country
				vicinity = addressResults[i].formatted_address;
				break;
			}
		}
		return {
			streetAddress: streetAddress, 
			vicinity: vicinity, 
			location: location, 
			place: false
		};
		*/
	},
	
	searchAddressByLocation: function(lat, lng, onSuccess) {
		/*
		var latlng = new google.maps.LatLng(lat, lng);
		var _this = this;
			
		this.geocoder.geocode({
			'latLng': latlng
		}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				onSuccess(_this.parseGoogleAddress(results));
			} else {
				console.log("Geocoder failed due to: " + status);
			}
		});
		*/
	},
	
	getPlaceDetails: function(request, callback) {
		/*
		this.placesService.getDetails(request, callback);
		*/
	},
	
	getAddressComponents: function(place) {
		/*
		var components = {
			street_number: null, 
			street_address: place.name, 
			city: null, 
			postal_code: null
		};
		for (var i in place.address_components) {
			var comp = place.address_components[i];
			for (var j in comp.types) {
				var type = comp.types[j];
				if (type == 'street_number')
					components.street_number = comp.short_name;
				else if (type == 'street_address' || type == 'route' || type == 'airport')
					components.street_address = comp.short_name;
				else if (type == 'locality')
					components.city = comp.short_name;
				else if (type == 'postal_code')
					components.postal_code = comp.short_name;
			}
		}
		return components;
		*/
	},
    
	createMarker: function(position) {
		/*
		var self = this;

		var marker = new L.marker(position);
		marker.addTo(self.map);
		return marker;
		*/
	}
});