go.modules.community.addressbook.MainPanel = Ext.extend(Ext.Panel, {

	layout: "responsive",

	addAddressBookId: 1,

	initComponent: function () {

		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			tbar: [{
					xtype: "tbtitle",
					text: t("Address books")
				}, '->', {
					iconCls: 'ic-add'
				}]
		});

		this.filterPanel = new Ext.Panel({
			width: dp(300),
			region: "west",
			split: true,
			items: [
				this.addressBookTree
			]
		});

		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			width: dp(500),
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
//						this.westPanel.getLayout().setActiveItem(this.noteBookGrid);
//						this.noteBookGrid.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				},
				this.addButton = new Ext.Button({
					//disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (btn) {
						var dlg = new go.modules.community.addressbook.ContactDialog({
							formValues: {
								addressBookId: this.addAddressBookId
							}
						});
						dlg.show();
					},
					scope: this
				})

			],
			listeners: {
				viewready: function (grid) {
					//load note books and select the first
					this.grid.store.load();
				},

				rowdblclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex);
					if (record.get('permissionLevel') < GO.permissionLevels.write) {
						return;
					}

					var dlg = new go.modules.community.addressbook.ContactDialog();
					dlg.load(record.id).show();
				},

				scope: this
			}
		});

		this.grid.getSelectionModel().on('rowselect', function (sm, rowIndex, record) {
			go.Router.goto("contact/" + record.id);
		}, this);

		this.contactDetail = new go.modules.community.addressbook.ContactDetail({
			region: "center"
		});

		this.westPanel = new Ext.Panel({
			region: "west",
			layout: "responsive",
			//stateId: "go-addressbook-west",
			split: true,
			width: dp(700),
			narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.grid, //first is default in narrow mode
				this.filterPanel
			]
		});

		this.items = [this.westPanel, this.contactDetail];

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);
	}
});
