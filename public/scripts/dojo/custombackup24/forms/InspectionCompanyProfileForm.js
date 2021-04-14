dojo.provide("custom.forms.InspectionCompanyProfileForm");
dojo.require("custom.forms.CompanyProfileForm");

dojo.declare("custom.forms.InspectionCompanyProfileForm", custom.forms.CompanyProfileForm, {
	
	onSubmitSuccess: function(response) {
		
		console.log('the form was submitted');
		
		// refresh the company profile pane
		dijit.byId('inspection_company_profile').refresh();
		
		// update the logo url
		var logoUrl = response.logo_file;
		if (logoUrl) {
		    dojo.byId('companyLogoHeader').src = logoUrl;
		
		    // set the cookie for the logo
		    dojo.cookie('companyLogo', logoUrl);
		}
		
		// remember the color schema
		var theme = response.theme * 1; // to make it integer
		dojo.cookie('theme', theme);
		this.changeTheme(theme);
	},
	
	changeTheme: function(theme) {
	    // get the body element
        var body = dojo.body();
        
        // remove all possible classes
        if (dojo.hasClass(body, 'themeBlue'))   dojo.removeClass(body, 'themeBlue');
        if (dojo.hasClass(body, 'themeChrome')) dojo.removeClass(body, 'themeChrome');
        if (dojo.hasClass(body, 'themeRed'))    dojo.removeClass(body, 'themeRed');
        if (dojo.hasClass(body, 'themeGreen'))  dojo.removeClass(body, 'themeGreen');
        if (dojo.hasClass(body, 'themeBrown'))  dojo.removeClass(body, 'themeBrown');
        
        // add the correct class
        switch (theme) {
            case 0: dojo.addClass(body, 'themeBlue');   break;
            case 1: dojo.addClass(body, 'themeChrome'); break;
            case 2: dojo.addClass(body, 'themeRed');    break;
            case 3: dojo.addClass(body, 'themeGreen');  break;
            case 4: dojo.addClass(body, 'themeBrown');  break; 
        }
        
	},
	
	removeLogo: function() {
	    dojo.byId('formLogoDisplay').style.display='none';
	    dojo.byId('formRemoveLogo').value = 1;
	}
	
});