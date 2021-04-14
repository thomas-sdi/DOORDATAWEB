dojo.provide("custom.forms.CompanyProfileForm");
dojo.require("ginger.form.ValidationForm");

dojo.declare("custom.forms.CompanyProfileForm", ginger.form.ValidationForm, {
	countryWidget:	null,
	stateWidget:	null,
	all_states:		null, //passed from the form
	state_id:		null, //current state for the company, passed from php form
	store:			null,
	
	startup: function(){
		this.inherited(arguments);
		
		//populate the list of the states with one passed from the php form
		var data = [];
		
		dojo.forEach(this.all_states, function(state){
			data.push({id: state.ID, name: state.ITEM, country: state.PARENT_ID});
		});
		this.store = {
			identifier:		'id',
			label:			'text',
			items:  		data
		};

		//when country is changed, the list of states should be filtered as well
		this.countryWidget 	= dijit.byId('country');		
		dojo.connect(this.countryWidget, 'onChange', dojo.hitch(this, this.onCountrySelected));
		
		//create the state widget out of the field with id = 'state'
		this.stateWidget = new dijit.form.FilteringSelect({
			id: 'state',
			name: 'state',
			store: new dojo.data.ItemFileReadStore({
                data: this.store
            }),
            query: {country: this.countryWidget.value || '*'},
			autoComplete: false,
		}, 'state');
		
		if (this.state_id && this.state_id != '') this.stateWidget.attr('value', this.state_id);
		else this.stateWidget.attr('value', '');

	},
	
	onCountrySelected: function(e){
		this.stateWidget.attr('value', '');
		this.stateWidget.query.country = e || '*'; //apply the filter again
	}
	
});