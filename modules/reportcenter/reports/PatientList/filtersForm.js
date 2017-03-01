/**
 * GaiaEHR (Electronic Health Records)
 * Copyright (C) 2015 TRA NextGen, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

Ext.define('Modules.reportcenter.reports.PatientList.filtersForm', {
    extend: 'Ext.form.Panel',
    requires: [
        'Ext.form.field.Date',
        'App.ux.combo.ActiveProviders',
        'App.ux.LiveSnomedProblemMultipleSearch',
        'Modules.reportcenter.reports.PatientList.ux.LiveRXNORMSearchReport',
        'Modules.reportcenter.reports.PatientList.ux.ComboOptionListReport',
        'Modules.reportcenter.reports.PatientList.ux.LabResultValuesFilter',
        'Modules.reportcenter.reports.PatientList.ux.LiveRXNORMAllergySearchReport'
    ],
    xtype: 'reportFilter',
    region: 'west',
    title: _('filters'),
    itemId: 'PatientListFilters',
    collapsible: true,
    border: true,
    split: true,
    defaults: {
        xtype: 'fieldset',
        layout: 'anchor',
        defaults: {anchor: '100%'},
        border: false,
        frame: false,
        margin: 2
    },
    items: [
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'datefield',
                            name: 'begin_date',
                            columnWidth: 1,
                            fieldLabel: _('begin_date'),
                            labelWidth: 100,
                            allowBlank: false,
                            format: g('date_display_format'),
                            submitFormat: 'Y-m-d'
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'datefield',
                            name: 'end_date',
                            columnWidth: 1,
                            fieldLabel: _('end_date'),
                            labelWidth: 100,
                            allowBlank: false,
                            format: g('date_display_format'),
                            submitFormat: 'Y-m-d'
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'activeproviderscombo',
                            name: 'provider',
                            displayField: 'option_name',
                            itemId: 'option_name',
                            valueField: 'id',
                            editable: true,
                            columnWidth: 1,
                            hideLabel: true,
                            emptyText: _('select_provider'),
                            allowOnlyWhitespace: true,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #provider_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var option_name = Ext.ComponentQuery.query('reportFilter #option_name')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #provider_name')[0];
                                    option_name.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'provider_name',
                            name: 'provider_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'rxnormallergylivetsearchreport',
                            hideLabel: true,
                            name: 'allergy_code',
                            itemId: 'allergy_code',
                            emptyText: 'Makarena...',
                            columnWidth: 1,
                            displayField: 'STR',
                            valueField: 'RXCUI',
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #allergy_name')[0];
                                    field.setValue(records[0].data.STR);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var allergy_code = Ext.ComponentQuery.query('reportFilter #allergy_code')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #allergy_name')[0];
                                    allergy_code.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'allergy_name',
                            name: 'allergy_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'livesnomedproblemmultiple',
                            hideLabel: true,
                            name: 'problem_code',
                            itemId: 'problem_code',
                            columnWidth: 1,
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var problem_list = '',
                                        field = Ext.ComponentQuery.query('reportFilter #problem_name')[0];
                                    for (var i = 0; i < records.length; i++) {
                                        problem_list = records[i].data.FullySpecifiedName + ', ' + problem_list;
                                    }
                                    field.setValue(problem_list);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var problem_code = Ext.ComponentQuery.query('reportFilter #problem_code')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #problem_name')[0];
                                    problem_code.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'problem_name',
                            name: 'problem_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'rxnormlivetsearchreport',
                            hideLabel: true,
                            columnWidth: 1,
                            name: 'medication_code',
                            itemId: 'medication_code',
                            emptyText: _('medication_search')+'...',
                            enableKeyEvents: true,
                            value: null,
                            displayField: 'STR',
                            valueField: 'RXCUI',
                            listeners: {
                                select: function(combo, records, eOpts){
                                    var field = Ext.ComponentQuery.query('reportFilter #medication_name')[0];
                                    field.setValue(records[0].data.STR);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var medication_code = Ext.ComponentQuery.query('reportFilter #medication_code')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #medication_name')[0];
                                    medication_code.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'medication_name',
                            name: 'medication_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'gaiaehr.ethnicitycombo',
                            hideLabel: true,
                            columnWidth: 1,
                            itemId: 'race',
                            name: 'race',
                            displayField: 'option_name',
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #race_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var ethnicitycombo = Ext.ComponentQuery.query('reportFilter #race')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #race_name')[0];
                                    ethnicitycombo.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'race_name',
                            name: 'race_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'gaiaehr.racecombo',
                            hideLabel: true,
                            columnWidth: 1,
                            name: 'ethnicity',
                            itemId: 'ethnicity',
                            displayField: 'option_name',
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #ethnicity_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var racecombo = Ext.ComponentQuery.query('reportFilter #ethnicity')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #ethnicity_name')[0];
                                    racecombo.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'ethnicity_name',
                            name: 'ethnicity_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'gaiaehr.sexcombo',
                            hideLabel: true,
                            columnWidth: 1,
                            name: 'sex',
                            itemId: 'sex',
                            displayField: 'sex',
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #sex_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var sexcombo = Ext.ComponentQuery.query('reportFilter #sex')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #sex_name')[0];
                                    sexcombo.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'sex_name',
                            name: 'sex_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'gaiaehr.combo',
                            hideLabel: true,
                            list: 132,
                            columnWidth: 1,
                            name: 'phone_publicity',
                            itemId: 'phone_publicity',
                            emptyText: _('communication'),
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #phone_publicity_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var phone_publicity = Ext.ComponentQuery.query('reportFilter #phone_publicity')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #phone_publicity_name')[0];
                                    phone_publicity.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'phone_publicity_name',
                            name: 'phone_publicity_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'numberfield',
                            hideLabel: true,
                            columnWidth: 0.50,
                            name: 'ageFrom',
                            enableKeyEvents: true,
                            value: null,
                            emptyText: _('ageFrom')
                        },
                        {
                            xtype: 'numberfield',
                            hideLabel: true,
                            columnWidth: 0.50,
                            name: 'ageTo',
                            enableKeyEvents: true,
                            value: null,
                            emptyText: _('ageTo')
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'listcomboreport',
                            list: 12,
                            id: 'marital',
                            hideLabel: true,
                            emptyText: _('patient_marital_status'),
                            columnWidth: 1,
                            name: 'marital',
                            itemId: 'marital',
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #marital_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var marital = Ext.ComponentQuery.query('reportFilter #marital')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #marital_name')[0];
                                    marital.reset();
                                    field.setValue('');

                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'marital_name',
                            name: 'marital_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'listcomboreport',
                            list: 10,
                            id: 'language',
                            hideLabel: true,
                            emptyText: _('language'),
                            columnWidth: 1,
                            name: 'language',
                            itemId: 'language',
                            enableKeyEvents: true,
                            value: null,
                            listeners: {
                                select: function (combo, records, eOpts) {
                                    var field = Ext.ComponentQuery.query('reportFilter #language_name')[0];
                                    field.setValue(records[0].data.option_name);
                                }
                            }
                        },
                        {
                            xtype: 'button',
                            text: 'X',
                            listeners:{
                                click: function(btn){
                                    var language = Ext.ComponentQuery.query('reportFilter #language')[0],
                                        field = Ext.ComponentQuery.query('reportFilter #language_name')[0];
                                    language.reset();
                                    field.setValue('');
                                }
                            }
                        },
                        {
                            xtype: 'hiddenfield',
                            itemId: 'language_name',
                            name: 'language_name',
                            value: ''
                        }
                    ]
                }
            ]
        },
        {
            items: [
                {
                    xtype: 'panel',
                    layout: 'column',
                    border: false,
                    frame: false,
                    items: [
                        {
                            xtype: 'labresultvalues',
                            id: 'labResult',
                            columnWidth: 1
                        }
                    ]
                }
            ]
        }
    ]
});
