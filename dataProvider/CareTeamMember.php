<?php
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
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class CareTeamMember {
	/**
	 * @var MatchaCUP
	 */
	private $c;

	function __construct(){
        if($this->c == NULL)
            $this->c = MatchaModel::setSenchaModel('App.model.patient.CareTeamMember');
	}

	public function getCareTeamMembers($params){
		unset($params->sort);
		return $this->c->load($params)->leftJoin([
			'title' => 'title',
			'fname' => 'fname',
			'lname' => 'lname',
			'mname' => 'mname',
		],'referring_providers', 'npi','npi')->group('npi')->all();
	}

	public function getCareTeamMember($params){
		unset($params->sort);
		return $this->c->load($params)->leftJoin([
			'title' => 'title',
			'fname' => 'fname',
			'lname' => 'lname',
			'mname' => 'mname',
		],'referring_providers', 'npi','npi')->group('npi')->one();
	}

	public function addCareTeamMember($params){
		return $this->c->save($params);
	}

	public function updateCareTeamMember($params){
		return $this->c->save($params);
	}

	public function destroyCareTeamMember($params){
		return $this->c->destroy($params);
	}
}