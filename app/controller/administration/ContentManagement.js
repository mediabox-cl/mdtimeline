/**
 * GaiaEHR (Electronic Health Records)
 * Copyright (C) 2013 Certun, LLC.
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

Ext.define('App.controller.administration.ContentManagement', {
    extend: 'Ext.app.Controller',
    refs: [
        {
            ref: 'ContentManagementGrid',
            selector: '#ContentManagementGrid'
        },
        {
            ref: 'ContentManagementWindow',
            selector: '#ContentManagementWindow'
        },
        {
            ref: 'ContentManagementWindowForm',
            selector: '#ContentManagementWindowForm'
        },
        {
            ref: 'ContentManagementWindowSaveBtn',
            selector: '#ContentManagementWindowSaveBtn'
        },
        {
            ref: 'ContentManagementWindowCancelBtn',
            selector: '#ContentManagementWindowCancelBtn'
        },
        {
            ref: 'ContentManagementWindowTokensTextArea',
            selector: '#ContentManagementWindowTokensTextArea'
        }
    ],

    init: function () {
        var me = this;

        me.control({
            '#ContentManagementGrid': {
                beforerender: me.onContentManagementGridBeforeRender,
                itemdblclick: me.onContentManagementGridItemDblClick
            },
            '#ContentManagementWindow': {
                close: me.onContentManagementWindowClose
            },
            '#ContentManagementWindowSaveBtn': {
                click: me.onContentManagementWindowSaveBtnClick
            },
            '#ContentManagementWindowCancelBtn': {
                click: me.onContentManagementWindowCancelBtnClick
            }
        });
    },

    onContentManagementWindowSaveBtnClick: function () {
        var me = this,
            form = me.getContentManagementWindowForm().getForm(),
            record = form.getRecord(),
            values = form.getValues();

        if (form.isValid()) {

            record.set(values);
            record.store.sync({
                success: function () {
                    app.msg(_('sweet'), _('record_saved'));
                    record.store.load();
                },
                failure: function () {
                    app.msg(_('oops'), _('record_error'), true);
                }
            });

            me.getContentManagementWindow().close();
        }
    },

    onContentManagementWindowCancelBtnClick: function () {
        this.getContentManagementWindow().close();
    },

    onContentManagementWindowClose: function () {
        this.getContentManagementWindowForm().getForm().reset();
    },

    onContentManagementGridBeforeRender: function (grid) {
        grid.getStore().load();
    },

    onContentManagementGridItemDblClick: function (grid, record) {

        this.showContentWindow();

        var me = this,
            content_type = record.get('content_type'),
            form = me.getContentManagementWindowForm().getForm();


        form.reset();
        form.loadRecord(record);
        me.setTokensTextAreaFieldByContentType(content_type);
    },

    showContentWindow: function () {
        if (!this.getContentManagementWindow()) {
            Ext.create('App.view.administration.ContentManagementWindow');
        }
        this.getContentManagementWindow().show();
    },

    setTokensTextAreaFieldByContentType: function (content_type) {
        var me = this,
            tokens = [],
            tokenTextAreaField = me.getContentManagementWindowTokensTextArea();

        if (content_type === 'disclosure') {
            tokens = tokens.concat(this.disclosureTokens(), this.patientTokens(),  this.formatTokens());
        }

        if (content_type === 'reminder_mammography_one_year' || content_type === 'reminder_mammography_six_months' || content_type === 'reminder_mammography_pathology') {
            tokens = tokens.concat(this.breastImagingReminderTokens());
        }

        if (content_type === 'sms_worklist_report_ready') {
            tokens = tokens.concat(this.smsWorklistReporReadyTokens());
        }

        if (content_type == null || content_type === '' || tokens.length <= 0) {
            tokens = tokens.concat(this.defaultTokens());
        }

        tokenTextAreaField.setValue(tokens.join("\r\n"));
    },

    patientTokens: function () {
        return [
            '[PATIENT_NAME]',
            '[PATIENT_ID]',
            '[PATIENT_RECORD_NUMBER]',
            '[PATIENT_FULL_NAME]',
            '[PATIENT_LAST_NAME]',
            '[PATIENT_SEX]',
            '[PATIENT_BIRTHDATE]',
            '[PATIENT_MARITAL_STATUS]',
            '[PATIENT_SOCIAL_SECURITY]',
            '[PATIENT_EXTERNAL_ID]',
            '[PATIENT_DRIVERS_LICENSE]',
            '[PATIENT_POSTAL_ADDRESS_LINE_ONE]',
            '[PATIENT_POSTAL_ADDRESS_LINE_TWO]',
            '[PATIENT_POSTAL_CITY]',
            '[PATIENT_POSTAL_STATE]',
            '[PATIENT_POSTAL_ZIP]',
            '[PATIENT_POSTAL_COUNTRY]',
            '[PATIENT_PHYSICAL_ADDRESS_LINE_ONE]',
            '[PATIENT_PHYSICAL_ADDRESS_LINE_TWO]',
            '[PATIENT_PHYSICAL_CITY]',
            '[PATIENT_PHYSICAL_STATE]',
            '[PATIENT_PHYSICAL_ZIP]',
            '[PATIENT_PHYSICAL_COUNTRY]',
            '[PATIENT_HOME_PHONE]',
            '[PATIENT_MOBILE_PHONE]',
            '[PATIENT_WORK_PHONE]',
            '[PATIENT_EMAIL]',
            '[PATIENT_MOTHERS_NAME]',
            '[PATIENT_GUARDIANS_NAME]',
            '[PATIENT_EMERGENCY_CONTACT]',
            '[PATIENT_EMERGENCY_PHONE]',
            '[PATIENT_PROVIDER]',
            '[PATIENT_PHARMACY]',
            '[PATIENT_AGE]',
            '[PATIENT_OCCUPATION]',
            '[PATIENT_EMPLOYEER]',
            '[PATIENT_RACE]',
            '[PATIENT_ETHNICITY]',
            '[PATIENT_LENGUAGE]',
            '[PATIENT_PICTURE]',
            '[PATIENT_QRCODE]',
        ];
    },

    disclosureTokens: function () {
        return [
            '[DISCLOSURE_DOCUMENTS]',
            '[DISCLOSURE_RECIPIENT]',
            '[DISCLOSURE_DESCRIPTION]',
            '[DISCLOSURE_REQUEST_DATE]',
            '[DISCLOSURE_FULFIL_DATE]',
            '[DISCLOSURE_PICKUP_DATE]',
            '[DISCLOSURE_DOCUMENT_COUNT]'
        ];
    },

    breastImagingReminderTokens: function () {
        return [
            '[PATIENT_NAME]',
            '[PATIENT_ADDRESS]',
            '[PATIENT_ADDRESS_CONT]',
            '[PATIENT_CITY]',
            '[PATIENT_STATE]',
            '[PATIENT_ZIP]',
            '[PATIENT_CITY_STATE_ZIP]',
            '[PATIENT_RECORD_NUMBER]',
            '[FACILITY_NAME]',
            '[FACILITY_ADDRESS]',
            '[FACILITY_ADDRESS_CONT]',
            '[FACILITY_CITY_STATE_ZIP]',
            '[SERVICE_DATE]',
            '[TODAY]',
            '[B]',
            '[/B]',
            '[U]',
            '[/U]',
            '[I]',
            '[/I]',
            '[TAB]'
        ];
    },

    smsWorklistReporReadyTokens: function () {
        return [
            '[FACILITY_NAME]',
            '[SERVICE_DATE]',
            '[ACCESSION_NUMBER]'
        ];
    },

    formatTokens: function () {
        return [
            '[TODAY]',
            '[B]',
            '[/B]',
            '[U]',
            '[/U]',
            '[I]',
            '[/I]',
            '[TAB]'
        ];
    },

    defaultTokens: function () {
        return [
            '[PATIENT_NAME]',
            '[PATIENT_RECORD_NUMBER]',
            '[SERVICE_DATE]',
            '[SIGNED_DATE]',
            '[TITLE]',
            '[RADIOLOGIST_SIGNATURE]',
            '[ORDERING_PHYSICIAN]',
            '[FACILITY_NAME]',
            '[FACILITY_PHONE]',
            '[DENSE_BREST]',
            '[NORMAL_BENIGN]',
            '[PROBABLY_BENIGN]',
            '[ADDITIONAL]',
            '[PREVIOUS]',
            '[ABNORMAL]',
            '[RECOMENDATIONS_LIST]',
            '[B]',
            '[/B]',
            '[U]',
            '[/U]',
            '[I]',
            '[/I]'
        ];
    }


});