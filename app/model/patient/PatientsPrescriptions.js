/**
 GaiaEHR (Electronic Health Records)
 Copyright (C) 2013 Certun, inc.

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

Ext.define('App.model.patient.PatientsPrescriptions', {
	extend: 'Ext.data.Model',
	table: {
		name:'patientsprescriptions',
		comment:'Patients Prescriptions'
	},
	fields: [
        {name: 'id', type: 'int', dataType: 'bigint', len: 20, primaryKey : true, autoIncrement : true, allowNull : false, store: true, comment: 'Patient Prescription ID'},
		{ name: 'pid', type:'int' },
		{ name: 'eid', type:'int' },
		{ name: 'uid', type:'int' },
		{ name: 'created_date', type:'date', dateFormat:'Y-m-d H:i:s'} ,
		{ name: 'note', type:'string' },
		{ name: 'document_id', type: 'int' },
		{ name: 'docUrl', type: 'string' },
		{ name: 'medications'}
	],
	proxy : {
		type: 'direct',
		api : {
            read  : Prescriptions.getPrescriptions,
            create: Prescriptions.addPrescription,
            update: Prescriptions.updatePrescription
		}
	}
});