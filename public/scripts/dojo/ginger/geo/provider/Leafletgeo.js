dojo.provide("ginger.geo.provider.Leafletgeo");

dojo.declare("ginger.geo.provider.Leafletgeo", null, {
	
	map: null,
	route: null,
	
	constructor: function(params){
		
	},
	
	/**
	 * @param lat
	 * @param lng
	 * @param draggable
	 * @param widget_id
	 * @param icon {iconLetter, iconColor}
	 * @param popupMessage
	 * @param name
	 */
	updateMapMarker: function(params) {
		// validations
		if (!this.map) {
			console.error('Cannot add marker as no map was created');
			return;
		}
		
		var marker = params.marker;
		
		// check if the marker already exists 
		if (marker) {
			if (!params.lat || !params.lng){
				//means we can not place marker
				if (this.map.hasLayer(marker)) this.map.removeLayer(marker);
				return null;
			}
			
			marker.setLatLng(L.latLng(params.lat, params.lng));
			
			if (params.icon) {
				marker.setIcon(this.createIcon(params.icon));
			}
			marker.widget_id = params.widget_id ? params.widget_id : '';
		}
		else {
			if (!params.lat || !params.lng){
				//not enough parameters, we are not creating the marker
				console.log('not enough parameters, we are not creating the marker for ' + params.widget_id + ', ' + params.popupMessage);
				return null;
			}
			marker = L.marker([params.lat, params.lng], {
				draggable: params.hasOwnProperty('draggable') ? params.draggable: false,
				icon: this.createIcon(params.icon)
			});
			marker.widget_id = params.widget_id ? params.widget_id : '';
			marker.addTo(this.map);
		}
		
		// add popup message
		if (params.popupMessage) {
			marker.bindPopup(params.popupMessage);
		}
		
		return marker;
	},
	
	/*
	 * @param polygon [{lat,lng}]
	 * @param options see http://leafletjs.com/reference.html#polyline-options
	 */
	showRoute: function(params) {
		if (!params || !params.polygon) {
			console.error('Cannot display route with no polygon provided');
			return;
		}
		
		// create the polygon array
		var geometry = [];
		try{
			dojo.forEach(params.polygon, function(point){
				geometry.push(L.latLng(point.lat*1, point.lng*1));
			});
		}
		catch(err){
			console.error('Cannot display route with polygon data provided: ' + dumpVar(err.message));
			return;
		}
		
		var polyline = L.polyline(geometry);

		//this.map.addLayer(polyline);
		//// ensure the route is fit on the map
		//this.fitBounds({polygon: polyline});
		
		return polyline;
	},
	
	redrawMap: function() {
		var self = this;
		// required to properly draw the map
		setTimeout(function(){
			self.map.invalidateSize(false);
		}, 500);
	},
	
	createIcon: function(params) {
		var iconUrl = null;
		var icon = null;
		
		if (params.iconColor && params.iconLetter){
			var iconUrl = "https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=" + params.iconLetter +"|" + params.iconColor + "|000000";
			
			icon = L.icon({
        		iconUrl: iconUrl,
        		iconSize: [21,34]
        	});
		}
		else
			icon = new L.Icon.Default; 
        
        return icon;
	},
	
	/*
	 * @param lat
	 * @param lng
	 */
	panTo: function(params) {
		if (!this.map) {
			console.error('Cannot pan to a point as no map is created yet');
			return;
		}
		
		this.map.panTo(L.latLng(params.lat, params.lng));
	},
	
	/*
	 * @param polygon
	 */
	fitBounds: function(params) {
		if (!this.map) {
			console.error('Cannot fit bounds as no map is created yet');
			return;
		}
		
		if (!params || !params.polygon) {
			console.error('Cannot fit bounds as no polygon is provided');
			return;
		}
		
		// the delay is required as otherwise map freezes sometimes
		var self = this;
		setTimeout(function(){self.map.fitBounds(params.polygon, {animate: false});}, 1000);
	}
});