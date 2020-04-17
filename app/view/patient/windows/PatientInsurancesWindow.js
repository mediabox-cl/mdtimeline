/**
 * mdTimeLine (Billing Module)
 * Copyright (C) 2018 mdTimeLine.
 *
 */

Ext.define('App.view.patient.windows.PatientInsurancesWindow', {
    extend: 'Ext.window.Window',
    requires: [
        'Ext.grid.Panel',
        'App.view.patient.InsurancesPanel'
    ],

    title: _('insurance'),
    itemId: 'PatientInsurancesWindow',
    modal: true,
    width: 1155,
    height: 800,
    layout: {
        type: 'fit'
    },
    bodyPadding: 5,
    items: [
        {
            xtype: 'insurancestabpanel',
            title: '',
            itemId: 'PatientInsurancesPanel'
        }
    ]
});