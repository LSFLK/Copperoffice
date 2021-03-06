go.users.UserCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("User"),
	hiddenName: 'userId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'displayName',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	store: {
		xtype: 'gostore',
		fields: ['id', 'displayName', 'username', 'avatarId'],
		entityStore: "User"
	},
	
	initComponent: function() {
		this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="x-combo-list-item"><div class="user">\
					 <tpl if="!photoBlobId"><div class="avatar"></div></tpl>\\n\
					 <tpl if="photoBlobId"><div class="avatar" style="background-image:url({[go.Jmap.downloadUrl(values.avatarId)]})"></div></tpl>\
					 <div class="wrap">\
						 <div>{displayName}</div><small style="color:#333;">{username}</small>\
					 </div>\
				 </div></div>',
				'</tpl>'
			 );
		
		go.users.UserCombo.superclass.initComponent.call(this);
	}
});


Ext.reg("usercombo", go.users.UserCombo);
