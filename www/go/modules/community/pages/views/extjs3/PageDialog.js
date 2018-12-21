go.modules.community.pages.PageDialog = Ext.extend(go.form.Dialog, {
    title: t("Page"),
    entityStore: go.Stores.get("Page"),
    width: dp(1200),
    height: dp(600),
    //maximized: true,
    maximizable: true,
    siteId: '',
    sortOrder: '',
    redirectOnSave: false,
    newPage: false,
    initFormItems: function () {
	var items = [{
		xtype: 'fieldset',
		autoHeight: true,
		items: [
		    {
			xtype: 'textfield',
			name: 'pageName',
			fieldLabel: t("Page name"),
			anchor: '100%',
			allowBlank: false
		    },
		    {
			xtype: 'phtmleditor',
			name: 'content',
			fieldLabel: "",
			hideLabel: true,
			anchor: '100%',
			allowBlank: true,
			enableColors: false,
			enableFont: false,
			enableFontSize: false,
			//enableSourceEdit: false

		    },
		    {
			xtype: 'hidden',
			name: 'siteId',
			value: this.siteId
		    }, {
			xtype: 'hidden',
			name: 'sortOrder',
			value: this.sortOrder
		    }]
	    }
	]
	return items;
    },

    submit: function () {
	if (this.formPanel.getForm().isDirty()) {
	    go.modules.community.pages.PageDialog.superclass.submit.call(this);
	} else {
	    this.close();
	}
    },
    onSubmit: function (success, serverId) {
	//if a page is newly created the site will have to navigate to it.
	if (success && this.newPage) {
	    //get the page slug
	    this.entityStore.get([serverId], function (result) {
		pageSlug = result[0]['slug'];
		//get the site slug
		go.Stores.get("Site").get([this.siteId], function (result) {
		    //navigate to the new page
		    go.Router.goto(result[0]['slug'] + '\/view\/' + pageSlug);
		}, this);
	    }, this);
	}
    }
});