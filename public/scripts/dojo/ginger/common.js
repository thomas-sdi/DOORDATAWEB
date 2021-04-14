var MAX_DUMP_DEPTH = 0;

function dumpObj(obj, name, indent, depth) {
	if (depth > MAX_DUMP_DEPTH ) {
		return indent + name + ": <Maximum Depth Reached>\n";
	}
	if (typeof obj == "object") {
		var child = null;
		var output = indent + name + "\n";
		indent += "\t";
		for (var item in obj)
		{
			try {
				child = obj[item];
			} catch (e) {
				child = "<Unable to Evaluate>";
			}
			if (child instanceof Function)
				continue;
			if (typeof child == "object") {
				output += dumpObj(child, item, indent, depth + 1);
			} else {
				output += indent + item + ": " + child + "\n";
			}
		}
		return output;
	} else {
		return obj;
	}
}

function isEmpty(obj) {
	return obj == null || obj == undefined || obj == "" || obj == "null"; 
}

function equal(a, b) {
	return a == b || isEmpty(a) && isEmpty(b);
}

function nvl(first, secondIfFirstNull) {
	return first ? first : secondIfFirstNull;
}
function dumpVar(obj){
	return dumpObj(obj, "Your object", "", 0);
}

// input must be in canonical format
function formatDate(dateValue){	
	if (dateValue == null || dateValue == "" || dateValue == "...") return null;
	dateValue = dateValue + ""; // value from grid cell sometimes can be a string-convertable object
	if (dateValue.indexOf('#') >=0) return null;
	
	var datePattern  = this.constraint ? this.constraint.dateFormat : 'yyyy-MM-dd';
	var format = {
		datePattern: datePattern, 
		selector: "date"
	};
	var formattedDate = dojo.date.locale.format(new Date(dateValue), format);
	return formattedDate;
}

//input must be in canonical format
function formatTime(dateValue){	
	if (dateValue == null || dateValue == "" || dateValue == "...") return null;
	dateValue = dateValue + ""; // value from grid cell sometimes can be a string-convertable object
	if (dateValue.indexOf('#') >=0) return null;
	
	var timePattern = this.constraint ? this.constraint.timeFormat : 'HH:mm';
	var format = {
		datePattern: timePattern,
		selector: "time"
	};
	var formattedDate = dojo.date.locale.format(new Date(dateValue), format);
	return formattedDate;
}

function formatReference(refValue){
	if (!refValue || !refValue.indexOf) return '';
	else return refValue.substr(refValue.indexOf('#') + 1);
}

function formatLink(linkValue){
	if (linkValue){
		linkValue = "<a href ='javascript: cmp_" + this.constraint.grid + ".openLink(\"" + this.constraint.link  +  "\")'>" + linkValue + "</a>";
		return linkValue;
	}else return '';
}

function formatCalculated(calculatedValue){
	if(calculatedValue){
		calculatedValue = calculatedValue.replace(/&lt;/g, '<');	
		calculatedValue = calculatedValue.replace(/&amp;/g, '&');		
		return calculatedValue;
	}else return '';
}

function openDialog(url, action, onComplete) {
	dojo.connect(mainDialogFrame, 'onLoad', function() {
		if (action) mainDialogFrame.action = action;
		if (dijit._masterTT) dijit.hideTooltip(dijit._masterTT.aroundNode);
		mainDialog._position();
	});
	if (onComplete) {
		var handler1 = dojo.connect(mainDialogFrame, 'onLoad', function() {
			onComplete();
			dojo.disconnect(handler1);
		});
	}
	var handler = dojo.connect(mainDialog, 'onHide', function() {
		mainDialogFrame.destroyDescendants();
		dojo.disconnect(handler);
		
	});
	mainDialog.show();
	mainDialogFrame.set('href', baseUrl + url);
}

function changeBody(url, isBackForward, all) {
    if (!isBackForward) {
		var state = {
			back: function() {
				console.log('Going back to ' + url);
				changeBody(url, true, all);
			},
			forward: function() {
				console.log('Going forward to ' + url);
				changeBody(url, true, all);
			},
			changeUrl: true
		};
		dojo.back.addToHistory(state);
	} 
	
	//bodyDiv.attr('href', baseUrl + url);
	//frameDiv.changeBody(baseUrl + url);
    
	if (dijit._masterTT && dijit._masterTT != undefined)
		dijit.hideTooltip(dijit._masterTT.aroundNode);
	
	dojo.forEach(findWidgetsRecursively(all ? bodyDiv : frameDiv), function(widget){
    	widget.destroy();  
    });
	
	if (all) {
		bodyDiv.changeBody ? bodyDiv.changeBody(baseUrl + url) : 
			bodyDiv.attr('href', baseUrl + url);
	} else {
		frameDiv.changeBody ? frameDiv.changeBody(baseUrl + url) :
			frameDiv.attr('href', baseUrl + url);
	}
	
	
	//else mainContentDiv.attr('href', baseUrl + '/index/changebody?url=' + url);
	
	// checkAuthStatus();
}

function findWidgetsRecursively(parentWidget) {
	var arr = new Array();
	if (parentWidget.containerNode) {
		dojo.forEach(dijit.findWidgets(parentWidget.containerNode), function(widget){
			arr.push(widget);
			arr = arr.concat(findWidgetsRecursively(widget));
		});
	}
	return arr;
}

function round_up(val, precision) {
	power = Math.pow (10, precision);
	poweredVal = Math.ceil (val * power);
	result = poweredVal / power;

	return result;
}

function checkAuthStatus() {
	if (dojo.byId('loginForm') || dojo.byId('signupForm')) return false;
	
	dojo.xhrGet({
		url: baseUrl + '/auth/loginjson',
		handleAs: 'json',
		sync: true,
		load: function(response){
			if (response && response.reload == 'true') {
				window.location.href = baseUrl + '/index';
			}
			if (!response) //loginDialog.show();
				window.location.href = baseUrl;
			else {
				if (response.login == 'false') //loginDialog.show();
					window.location.href = baseUrl + '/index';
			}
		}
	});
}

// concatenates 2 objects
function concat(obj1, obj2) {
	for (var key in obj2)
		obj1[key] = obj2[key];
	return obj1;
}

/*
* Date Format 1.2.3
* (c) 2007-2009 Steven Levithan <stevenlevithan.com>
* MIT license
*
* Includes enhancements by Scott Trenda <scott.trenda.net>
* and Kris Kowal <cixar.com/~kris.kowal/>
*
* Accepts a date, a mask, or a date and a mask.
* Returns a formatted version of the given date.
* The date defaults to the current date/time.
* The mask defaults to dateFormat.masks.default.
*/

var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
	timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
	timezoneClip = /[^-+\dA-Z]/g,
	pad = function (val, len) {
		val = String(val);
		len = len || 2;
		while (val.length < len) val = "0" + val;
		return val;
	};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
		d = date[_ + "Date"](),
		D = date[_ + "Day"](),
		m = date[_ + "Month"](),
		y = date[_ + "FullYear"](),
		H = date[_ + "Hours"](),
		M = date[_ + "Minutes"](),
		s = date[_ + "Seconds"](),
		L = date[_ + "Milliseconds"](),
		o = utc ? 0 : date.getTimezoneOffset(),
		flags = {
			d:    d,
			dd:   pad(d),
			ddd:  dF.i18n.dayNames[D],
			dddd: dF.i18n.dayNames[D + 7],
			m:    m + 1,
			mm:   pad(m + 1),
			mmm:  dF.i18n.monthNames[m],
			mmmm: dF.i18n.monthNames[m + 12],
			yy:   String(y).slice(2),
			yyyy: y,
			h:    H % 12 || 12,
			hh:   pad(H % 12 || 12),
			H:    H,
			HH:   pad(H),
			M:    M,
			MM:   pad(M),
			s:    s,
			ss:   pad(s),
			l:    pad(L, 3),
			L:    pad(L > 99 ? Math.round(L / 10) : L),
			t:    H < 12 ? "a"  : "p",
			tt:   H < 12 ? "am" : "pm",
			T:    H < 12 ? "A"  : "P",
			TT:   H < 12 ? "AM" : "PM",
			Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
			o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
			S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
		};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
	"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
	"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
	"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
	"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};