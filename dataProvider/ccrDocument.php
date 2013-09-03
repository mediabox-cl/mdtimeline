<?php
/**
 * GaiaEHR (Electronic Health Records)
 * Copyright (C) 2013 Certun, inc.
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

if (!isset($_SESSION))
{
    session_name('GaiaEHR');
    session_start();
    session_cache_limiter('private');
}

$_SESSION['url'] = 'http://localhost/gaiaehr';
$_SESSION['root'] = '/var/www/gaiaehr';

include_once ($_SESSION['root'] . '/classes/MatchaHelper.php');
include_once ($_SESSION['root'] . '/classes/UUID.php');
include_once ($_SESSION['root'] . '/classes/Array2XML.php');

include_once ($_SESSION['root'] . '/dataProvider/Patient.php');
include_once ($_SESSION['root'] . '/dataProvider/User.php');
include_once ($_SESSION['root'] . '/dataProvider/Rxnorm.php');
include_once ($_SESSION['root'] . '/dataProvider/Encounter.php');
include_once ($_SESSION['root'] . '/dataProvider/PoolArea.php');
include_once ($_SESSION['root'] . '/dataProvider/Medical.php');
include_once ($_SESSION['root'] . '/dataProvider/PreventiveCare.php');
include_once ($_SESSION['root'] . '/dataProvider/Services.php');
include_once ($_SESSION['root'] . '/dataProvider/DiagnosisCodes.php');


/**
 * Load all the data for the CCR XML data and loops
 */
$patientGUID = UUID::v4();
$softwareGUID = UUID::v4();
$pdfDocumentGUID = UUID::v4();
$family1GUID = UUID::v4();
$family2GUID = UUID::v4();
$familyMemberLink1 = UUID::v4();
$familyMemberLink2 = UUID::v4();
$manufacturerGUID = UUID::v4();
$healthProviderGUID = UUID::v4();

$Patient = new Patient();
$Encounter = new Encounter();
$Medical = new Medical();

/**
 * Actors
 * ------
 * Used as a container to define all of the individuals, organizations, locations, and systems associated with
 * data in the summary document. Within the CCR data set, an Actor is a <Person>, <Organization> or
 * <Device>. These correspond to the HL7 RIM Entity classes: LivingSubject, Person, Organization or
 * Device, and are mapped accordingly to these classes as exposed in a CDA document. Whereas ASTM CCR
 * enumerates all Actors in the CCR Footer and references those Actors from within the CCR Body with the
 * <ActorLink> element, CCD defines many participants within the document header and body.
 */
$patientData = $Patient->getPatientDemographicDataByPid($_REQUEST['pid']);
$actors = array(
    'Actor' => array(
        array( // Actor 1 (This should be a loop with all the persons related to the CCR document
            'ActorObjectID' => $patientGUID,
            'Person' => array(
                'Name' => array(
                    'CurrentName' => array(
                        'Given' => $patientData['fname'],
                        'Family' => $patientData['lname'],
                        'Suffix' => $patientData['title']
                    )
                ),
                'DateOfBirth' => array(
                    'ExactDateTime' => $patientData['DOB']
                ),
                'Gender' => array(
                    'Text' => $patientData['sex'],
                    'Code' => array(
                        'Value' => ($patientData['sex'] == 'Male' ? 'M' : 'F'),
                        'CodingSystem' => '2.16.840.1.113883.5.1' // TODO: Where this GUID came from
                    )
                )
            ),
            'IDs' => array(
                array( // IDs 1
                    'Type' => array(
                        'Text' => $patientData['pid']
                    ),
                    'ID' => '2-16-840-1-113883-19-5-996756495', // TODO: Where this come from
                    'IssuedBy' => array(
                        'ActorID' => '2.16.840.1.113883.19.5' // TODO: Where this come from
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                array( // IDs 2
                    'Type' => array(
                        'Text' => 'Covered party ID'
                    ),
                    'ID' => '14d4a520-7aae-11db-9fe1-0800200c9a66', // TODO: Where this come from
                    'IssuedBy' => array(
                        'ActorID' => '329fcdf0-7ab3-11db-9fe1-0800200c9a66' // TODO: Where this come from
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                )
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            )
        )
    )
);

/**
 * References
 * ----------
 * Used to list the details concerning references to external data sources. Corresponds to the CDA R2
 * <reference> element. Whereas ASTM CCR enumerates all references in the CCR Footer, CCD defines the
 * reference within the section where it occurs.
 */
$references = array(
    'Reference' => array(
        array( // Reference 1
            'ReferenceObjectID' => UUID::v4(),
            'Description' => array(
                'Text' => 'Advance directive',
                'Code' => array(
                    'Value' => '371538006',
                    'CodingSystem' => 'SNOMED CT'
                )
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Locations' => array(
                'Location' => array(
                    array( // Location 1
                        'Actor' => array(
                            'ActorID' => 'b50b7910-7ffb-4f4c-bbe4-177ed68cbbf3'
                        )
                    )
                )
            )
        )
    )
);

/**
 * Healthcare providers
 * --------------------
 * Represents the healthcare providers involved in the current or pertinent historical care of the patient. At a
 * minimum, the patient’s key healthcare providers should be listed, particularly their primary physician and
 * any active consulting physicians, therapists, and counselors.
 */
$healthProviders = array(
    'Provider' => array(
        array( // Provider 1
            'ActorID' => $healthProviderGUID,
            'ActorRole' => array(
                'Text' => 'Gino Clinic',
                'Code' => array(
                    'Value' => 'PCP',
                    'CodingSystem' => '2.16.840.1.113883.5.88' // TODO: Find out from where this ID came from
                )
            )
        )
    )
);

/**
 * Plan of Care section
 * --------------------
 * The plan of care section contains data defining pending orders, interventions, encounters, services, and
 * procedures for the patient. It is limited to prospective, unfulfilled, or incomplete orders and requests only.
 * All active, incomplete, or pending orders, appointments, referrals, procedures, services, or any other
 * pending event of clinical significance to the current and ongoing care of the patient should be listed, unless
 * constrained due to issues of privacy.
 */
$planOfCare = array(
    'Plan' => array(
        array( // Plan 1
            'CCRDocumentObjectID' => UUID::v4(),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'OrderRequest' => array(
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'Type' => array(
                        'Text' => 'Requested date'
                    ),
                    'ExactDateTime' => '2000-04-21'
                ),
                'Status' => array(
                    'Text' => 'Ordered'
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                ),
                'Procedures' => array(
                    'Procedure' => array(
                        array( // Procedure 1
                            'CCRDocumentObjectID' => UUID::v4(),
                            'Type' => array(
                                'Text' => 'Pulmonary function test',
                                'Code' => array(
                                    'Value' => '23426006',
                                    'CodingSystem' => 'SNOMED CT'
                                )
                            ),
                            'Source' => array(
                                'Actor' => array(
                                    'ActorID' => $softwareGUID
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);

/**
 * Encounters section
 * ------------------
 * This section is used to list and describe any healthcare encounters pertinent to the patient’s current health
 * status or historical health history. An Encounter is an interaction, regardless of the setting, between a
 * patient and a practitioner who is vested with primary responsibility for diagnosing, evaluating, or treating
 * the patient’s condition. It may include visits, appointments, as well as non face-to-face interactions. It is
 * also a contact between a patient and a practitioner who has primary responsibility for assessing and treating
 * the patient at a given contact, exercising independent judgment. This section may contain all encounters for
 * the time period being summarized, but should include notable encounters.
 */
$encounters = array(
    'Encounter' => array(
        array( // Encounter 1
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'ExactDateTime' => '2000-04-07'
            ),
            'Type' => array(
                'Text' => 'Checkup Examination',
                'Code' => array(
                    'Value' => 'GENRL',
                    'CodingSystem' => '2.16.840.1.113883.5.4'
                )
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Locations' => array(
                'Location' => array(
                    'Actor' => array(
                        'ActorID' => '2.16.840.1.113883.19.5' // TODO: Find out what this ID came from
                    )
                )
            )
        )
    )
);

/**
 * Procedures section
 * ------------------
 * This section defines all interventional, surgical, diagnostic, or therapeutic procedures or treatments
 * pertinent to the patient historically at the time the document is generated. The section may contain all
 * procedures for the period of time being summarized, but should include notable procedures.
 */
$procedures = array(
    'Procedure' => array(
        array( // Procedure 1
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'Type' => array(
                    'Text' => 'Procedure Date'
                ),
                'ExactDateTime' => '1998'
            ),
            'Description' => array(
                'Text' => 'Total hip replacement, left',
                'ObjectAttribute' => array(
                    'Attribute' => 'Laterality',
                    'AttributeValue' => array(
                        'Value' => 'Left',
                        'Code' => array(
                            'Value' => '7771000',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    )
                ),
                'Code' => array(
                    'Value' => '52734007',
                    'CodingSystem' => 'SNOMED CT'
                )
            ),
            'Status' => array(
                'Text' => 'Final Results'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'InternalCCRLink' => array(
                'LinkID' => UUID::v4(), // TODO: Find out for what this GUID came from
                'LinkRelationship' => 'Implanted equipment'
            )
        )
    )
);

/**
 * Results section
 * ---------------
 * This section contains the results of observations generated by laboratories, imaging procedures, and other
 * procedures. The scope includes hematology, chemistry, serology, virology, toxicology, microbiology, plain
 * x-ray, ultrasound, CT, MRI, angiography, cardiac echo, nuclear medicine, pathology, and procedure
 * observations. The section may contain all results for the period of time being summarized, but should
 * include notable results such as abnormal values or relevant trends.
 */
$results = array(
    'Result' => array(
        array( // Result 1
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'Type' => array(
                    'Text' => 'Assessment Time'
                ),
                'ExactDateTime' => '2000-04-07T14:30Z'
            ),
            'Description' => array(
                'Text' => 'CBC WO DIFFERENTIAL',
                'Code' => array(
                    'Value' => '43789009',
                    'CodingSystem' => 'SNOMED CT'
                )
            ),
            'Status' => array(
                'Text' => 'Final Results'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Test' => array(
                array( // Test 1
                    'CCRDocumentObjectID' => UUID::v4(),
                    'DateTime' => array(
                        'Type' => array(
                            'Text' => 'Assessment Time'
                        ),
                        'ExactDateTime' => '2000-04-07T14:30Z'
                    ),
                    'Description' => array(
                        'Text' => 'HGB',
                        'Code' => array(
                            'Value' => '30313-1',
                            'CodingSystem' => 'LOINC'
                        )
                    ),
                    'Status' => array(
                        'Text' => 'Final Results'
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    ),
                    'TestResult' => array(
                        'Value' => '13.2',
                        'Units' => array(
                            'Unit' => 'g/dl'
                        )
                    ),
                    'NormalResult' => array(
                        'Normal' => array(
                            array( // Normal 1
                                'Description' => array(
                                    'Text' => 'M 13-18 g/dl; F 12-16 g/dl',
                                    'Source' => array(
                                        'Actor' => array(
                                            'ActorID' => $softwareGUID
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    )
);

/**
 * Vital Signs section
 * -------------------
 * This section contains current and historically relevant vital signs, such as blood pressure, heart rate,
 * respiratory rate, height, weight, body mass index, head circumference, crown-to-rump length, and pulse
 * oximetry. The section may contain all vital signs for the period of time being summarized, but at a
 * minimum should include notable vital signs such as the most recent, maximum and/or minimum, or both,
 * baseline, or relevant trends.
 */
$vitalSigns = $Encounter->getVitalsByPid($_REQUEST['pid']);
foreach($vitalSigns as $item)
{
    $vitals[] = array(
        'Result' => array(
            array( // Result 1
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'Type' => array(
                        'Text' => 'Assessment Time'
                    ),
                    'ExactDateTime' => $item['date']
                ),
                'Type' => array(
                    'Text' => 'Vital Signs',
                    'Code' => array(
                        'Value' => '8716-3',
                        'CodingSystem' => 'LOINC'
                    )
                ),
                'Status' => array(
                    'Text' => 'Final Results'
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                ),
                'Test' => array(
                    array( // Body height
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Body height',
                            'Code' => array(
                                'Value' => '3138-5',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['height_in'],
                            'Units' => array(
                                'Unit' => 'in'
                            )
                        )
                    ),
                    array( // Body height
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Body height',
                            'Code' => array(
                                'Value' => '3138-5',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['height_cm'],
                            'Units' => array(
                                'Unit' => 'cm'
                            )
                        )
                    ),
                    array( // Systolic BP
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Systolic BP',
                            'Code' => array(
                                'Value' => '8480-6',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['bp_systolic'],
                            'Units' => array(
                                'Unit' => 'mm Hg'
                            )
                        )
                    ),
                    array( // Diastolic BP
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Diastolic BP',
                            'Code' => array(
                                'Value' => '20184-8',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['bp_diastolic'],
                            'Units' => array(
                                'Unit' => 'mm Hg'
                            )
                        )
                    ),
                    array( // Body weight
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Body weight',
                            'Code' => array(
                                'Value' => '3141-9',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['weight_lbs'],
                            'Units' => array(
                                'Unit' => 'Lbs'
                            )
                        )
                    ),
                    array( // Body weight
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Body weight',
                            'Code' => array(
                                'Value' => '3141-9',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['weight_kg'],
                            'Units' => array(
                                'Unit' => 'Kg'
                            )
                        )
                    ),
                    array( // Pulse
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Pulse',
                            'Code' => array(
                                'Value' => '44974-4',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['pulse'],
                            'Units' => array(
                                'Unit' => 'Kg'
                            )
                        )
                    ),
                    array( // Respiration
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Respiration',
                            'Code' => array(
                                'Value' => '28333-3',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['respiration'],
                            'Units' => array(
                                'Unit' => ''
                            )
                        )
                    ),
                    array( // Temperature (Fahrenheit)
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Temperature',
                            'Code' => array(
                                'Value' => '8310-5',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['temp_f'],
                            'Units' => array(
                                'Unit' => 'F'
                            )
                        )
                    ),
                    array( // Temperature (Celcius)
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Temperature',
                            'Code' => array(
                                'Value' => '8310-5',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['temp_c'],
                            'Units' => array(
                                'Unit' => 'C'
                            )
                        )
                    ),
                    array( // Oxygen saturation
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Oxygen saturation',
                            'Code' => array(
                                'Value' => '20564-1',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['oxygen_saturation'],
                            'Units' => array(
                                'Unit' => '%'
                            )
                        )
                    ),
                    array( // Head circumference
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Head circumference',
                            'Code' => array(
                                'Value' => '11986-7',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['head_circumference_cm'],
                            'Units' => array(
                                'Unit' => 'cm'
                            )
                        )
                    ),
                    array( // Waist circumference
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'Waist circumference',
                            'Code' => array(
                                'Value' => '8280-0',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['waist_circumference_cm'],
                            'Units' => array(
                                'Unit' => 'cm'
                            )
                        )
                    ),
                    array( // BMI
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Assessment Time'
                            ),
                            'ExactDateTime' => $item['date']
                        ),
                        'Description' => array(
                            'Text' => 'BMI',
                            'Code' => array(
                                'Value' => '59574-4',
                                'CodingSystem' => 'LOINC'
                            )
                        ),
                        'Status' => array(
                            'Text' => 'Final Results'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        ),
                        'TestResult' => array(
                            'Value' => $item['bmi'],
                            'Units' => array(
                                'Unit' => '%'
                            )
                        )
                    )
                )
            )
        )
    );
}

/**
 * Immunizations section
 * ---------------------
 * The Immunizations section defines a patient’s current immunization status and pertinent immunization
 * history. The primary use case for the Immunization section is to enable communication of a patient’s
 * immunization status. The section should include current immunization status, and may contain the entire
 * immunization history that is relevant to the period of time being summarized.
 */
$patientImmunizations = $Medical->getPatientImmunizations($_REQUEST['pid']);
foreach($patientImmunizations as $item)
{
    $immunizations[] = array(
        'Immunization' => array(
            array(
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'Type' => array(
                        'Text' => 'Immunization Date'
                    ),
                    'ExactDateTime' => $item['administered_date']
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                ),
                'Product' => array(
                    'ProductName' => array(
                        'Text' => $item['vaccine_name'],
                        'Code' => array(
                            'Value' => $item['code'],
                            'CodingSystem' => $item['code_type']
                        )
                    )
                )
            )
        )
    );
}

/**
 * Medical Equipment section
 * -------------------------
 * The Medical Equipment section defines a patient’s implanted and external medical devices and equipment
 * that their health status depends on, as well as any pertinent equipment or device history. This section is also
 * used to itemize any pertinent current or historical durable medical equipment (DME) used to help maintain
 * the patient’s health status. All pertinent equipment relevant to the diagnosis, care, and treatment of a patient
 * should be included.
 */
$medicalEquipment = array(
    'Equipment' => array(
        array( // Equipment 1
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'ExactDateTime' => '1950'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Product' => array(
                'ProductName' => array(
                    'Text' => 'Automatic implantable cardioverter/defibrillator',
                    'Code' => array(
                        'Value' => '72506001',
                        'CodingSystem' => 'SNOMED CT'
                    )
                )
            )
        ),
        array( // Equipment 2
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'ExactDateTime' => '1998'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Product' => array(
                'ProductName' => array(
                    'Text' => 'Total hip replacement prosthesis',
                    'Code' => array(
                        'Value' => '304120007',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Manufacturer' => array(
                    'ActorID' => $manufacturerGUID
                ),
                'IDs' => array(
                    'ID' => UUID::v4(), // TODO: Find out for what is this ID
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                )
            )
        ),
        array( // Equipment 3
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'ExactDateTime' => '1999'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Product' => array(
                'ProductName' => array(
                    'Text' => 'Wheelchair',
                    'Code' => array(
                        'Value' => '58938008',
                        'CodingSystem' => 'SNOMED CT'
                    )
                )
            )
        )
    )
);


/**
 * Medications section
 * -------------------
 * The Medications section defines a patient’s current medications and pertinent medication history. At a
 * minimum, the currently active medications should be listed, with an entire medication history as an option,
 * particularly when the summary document is used for comprehensive data export. The section may also
 * include a patient’s prescription history, and enables the determination of the source of a medication list
 * (e.g. from a pharmacy system vs. from the patient).
 */
$patientMedications = $Medical->getPatientMedications($_REQUEST['pid']);
foreach($patientMedications as $item)
{
    $medications[] = array(
        'Medication' => array(
            array( // Medication 1
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'DateTimeRange' => array(
                        'BeginRange' => array(
                            'ExactDateTime' => $item['begin_date']
                        ),
                        'EndRange' => array(
                            'ExactDateTime' => $item['end_date']
                        )
                    )
                ),
                'Status' => array(
                    'Text' => 'Active'
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                ),
                'Product' => array(
                    'ProductName' => array(
                        'Text' => 'Albuterol inhalant',
                        'Code' => array(
                            'Value' => '307782',
                            'CodingSystem' => 'RxNorm'
                        )
                    ),
                    'Form' => array(
                        'Text' => ($item['take_pills'] ? 'TABLET' : '')
                    )
                ),
                'Directions' => array(
                    'Direction' => array(
                        array( // Direction 1
                            'Dose' => array(
                                'Value' => $item['dose']
                            ),
                            'Route' => array(
                                'Code' => array(
                                    'Value' => $item['route'],
                                    'CodingSystem' => 'RouteOfAdministration'
                                )
                            ),
                            'Frequency' => array(
                                'Value' => $item['prescription_often']
                            ),
                            'Indication' => array(
                                'PRNFlag' => array(
                                    'Text' => 'Wheezing',
                                    'Code' => array(
                                        'Value' => '56018004',
                                        'CodingSystem' => 'SNOMED CT'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    );
}
/**
 * Alerts section
 * --------------
 * This section is used to list and describe any allergies, adverse reactions, and alerts that are pertinent to the
 * patient’s current or past medical history. At a minimum, currently active and any relevant historical
 * allergies and adverse reactions should be listed.
 */
$alerts = array(
  'Alert' => array(
      array( // Alert 1
          'CCRDocumentObjectID' => UUID::v4(),
          'Description' => array(
              'Text' => 'Adverse reaction to substance',
              'Code' => array(
                  'Value' => '282100009',
                  'CodingSystem' => 'SNOMED CT'
              )
          ),
          'Status' => array(
              'Text' => 'Active'
          ),
          'Source' => array(
              'Actor' => array(
                  'ActorID' => $softwareGUID
              )
          ),
          'Agent' => array(
              'Products' => array(
                  'Product' => array(
                      array( // Product 1
                          'CCRDocumentObjectID' => UUID::v4(),
                          'Source' => array(
                              'Actor' => array(
                                  'ActorID' => $softwareGUID
                              )
                          ),
                          'Product' => array(
                              'ProductName' => array(
                                  'Text' => 'Penicillin',
                                  'Code' => array(
                                      'Value' => '70618',
                                      'CodingSystem' => 'RxNorm'
                                  )
                              )
                          )
                      )
                  )
              )
          ),
          'Reaction' => array(
              'Description' => array(
                  'Text' => 'Hives',
                  'Code' => array(
                      'Value' => '247472004',
                      'CodingSystem' => 'SNOMED CT'
                  )
              )
          )
      ),
      array( // Alert 2
          'CCRDocumentObjectID' => UUID::v4(),
          'Description' => array(
              'Text' => 'Adverse reaction to substance',
              'Code' => array(
                  'Value' => '282100009',
                  'CodingSystem' => 'SNOMED CT'
              )
          ),
          'Status' => array(
              'Text' => 'Active'
          ),
          'Source' => array(
              'Actor' => array(
                  'ActorID' => $softwareGUID
              )
          ),
          'Agent' => array(
              'Products' => array(
                  'Product' => array(
                      array( // Product 1
                          'CCRDocumentObjectID' => UUID::v4(),
                          'Source' => array(
                              'Actor' => array(
                                  'ActorID' => $softwareGUID
                              )
                          ),
                          'Product' => array(
                              'ProductName' => array(
                                  'Text' => 'Aspirin',
                                  'Code' => array(
                                      'Value' => '1191',
                                      'CodingSystem' => 'RxNorm'
                                  )
                              )
                          )
                      )
                  )
              )
          ),
          'Reaction' => array(
              'Description' => array(
                  'Text' => 'Wheezing',
                  'Code' => array(
                      'Value' => '56018004',
                      'CodingSystem' => 'SNOMED CT'
                  )
              )
          )
      ),
      array( // Alert 3
          'CCRDocumentObjectID' => UUID::v4(),
          'Description' => array(
              'Text' => 'Adverse reaction to substance',
              'Code' => array(
                  'Value' => '282100009',
                  'CodingSystem' => 'SNOMED CT'
              )
          ),
          'Status' => array(
              'Text' => 'Active'
          ),
          'Source' => array(
              'Actor' => array(
                  'ActorID' => $softwareGUID
              )
          ),
          'Agent' => array(
              'Products' => array(
                  'Product' => array(
                      array( // Product 1
                          'CCRDocumentObjectID' => UUID::v4(),
                          'Source' => array(
                              'Actor' => array(
                                  'ActorID' => $softwareGUID
                              )
                          ),
                          'Product' => array(
                              'ProductName' => array(
                                  'Text' => 'Codeine',
                                  'Code' => array(
                                      'Value' => '2670',
                                      'CodingSystem' => 'RxNorm'
                                  )
                              )
                          )
                      )
                  )
              )
          ),
          'Reaction' => array(
              'Description' => array(
                  'Text' => 'Nausea',
                  'Code' => array(
                      'Value' => '73879007',
                      'CodingSystem' => 'SNOMED CT'
                  )
              )
          )
      )
  )
);

/**
 * Social History section
 * ----------------------
 * This section contains data defining the patient’s occupational, personal (e.g. lifestyle), social, and
 * environmental history and health risk factors, as well as administrative data such as marital status, race,
 * ethnicity and religious affiliation. Social history can have significant influence on a patient’s physical,
 * psychological and emotional health and wellbeing so should be considered in the development of a
 * complete record.
 */
$socialHistory = array(
    'SocialHistoryElement' => array(
        array( // Social History 1
            'CCRDocumentObjectID' => UUID::v4(),
            'Type' => array(
                'Text' => 'Smoking',
                'Code' => array(
                    'Value' => '230056004',
                    'CodingSystem' => 'SNOMED CT'
                )
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Episodes' => array(
                'Episode' => array(
                    array(
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'DateTimeRange' => array(
                                'BeginRange' => array(
                                    'ExactDateTime' => '1947'
                                ),
                                'EndRange' => array(
                                    'ExactDateTime' => '1972'
                                )
                            )
                        ),
                        'Description' => array(
                            'Text' => '1 pack per day'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        )
                    ),
                    array(
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'DateTimeRange' => array(
                                'BeginRange' => array(
                                    'ExactDateTime' => '1973'
                                )
                            )
                        ),
                        'Description' => array(
                            'Text' => 'None'
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                )
            )
        ),
        array(
            'CCRDocumentObjectID' => UUID::v4(),
            'DateTime' => array(
                'DateTimeRange' => array(
                    'BeginRange' => array(
                        'ExactDateTime' => '1973'
                    )
                )
            ),
            'Type' => array(
                'Text' => 'ETOH Use',
                'Code' => array(
                    'Value' => '160573003',
                    'CodingSystem' => 'SNOMED CT'
                )
            ),
            'Description' => array(
                'Text' => 'None'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            )
        )
    )
);

/**
 * Family History section
 * ----------------------
 * This section contains data defining the patient’s genetic relatives in terms of possible or relevant health risk
 * factors that have a potential impact on the patient’s healthcare risk profile.
 */
$familyHistoryProblems = array(
    'FamilyProblemHistory' => array(
        array( // Fam. Problem 1
            'CCRDocumentObjectID' => UUID::v4(),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'FamilyMember' => array(
                'ActorID' =>$familyMemberLink1,
                'ActorRole' => array(
                    'Text' => 'Father'
                ),
                'HealthStatus' => array(
                    'Description' => array(
                        'Text' => 'Deceased'
                    ),
                    'CauseOfDeath' => 'Yes',
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            ),
            'Problem' => array(
                'Description' => array(
                    'Text' => 'Myocardial Infarction',
                    'Code' => array(
                        'Value' => '22298006',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Episodes' => array(
                    'Number' => '1',
                    'Episode' => array(
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Age At Onset'
                            ),
                            'Age' => array(
                                'Value' => '57',
                                'Units' => array(
                                    'Unit' => 'Years'
                                )
                            )
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            )
        ),
        array(
            'CCRDocumentObjectID' => UUID::v4(),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'FamilyMember' => array(
                'ActorID' =>$familyMemberLink1,
                'ActorRole' => array(
                    'Text' => 'Father'
                ),
                'HealthStatus' => array(
                    'Description' => array(
                        'Text' => 'Deceased'
                    ),
                    'CauseOfDeath' => 'No',
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            ),
            'Problem' => array(
                'Description' => array(
                    'Text' => 'Hypertension',
                    'Code' => array(
                        'Value' => '59621000',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Episodes' => array(
                    'Number' => '1',
                    'Episode' => array(
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Age At Onset'
                            ),
                            'Age' => array(
                                'Value' => '40',
                                'Units' => array(
                                    'Unit' => 'Years'
                                )
                            )
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            )
        ),
        array(
            'CCRDocumentObjectID' => UUID::v4(),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'FamilyMember' => array(
                'ActorID' =>$familyMemberLink2,
                'ActorRole' => array(
                    'Text' => 'Mother'
                ),
                'HealthStatus' => array(
                    'Description' => array(
                        'Text' => 'Alive And Well'
                    ),
                    'CauseOfDeath' => 'No',
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            ),
            'Problem' => array(
                'Description' => array(
                    'Text' => 'Asthma',
                    'Code' => array(
                        'Value' => '195967001',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Episodes' => array(
                    'Number' => '1',
                    'Episode' => array(
                        'CCRDocumentObjectID' => UUID::v4(),
                        'DateTime' => array(
                            'Type' => array(
                                'Text' => 'Age At Onset'
                            ),
                            'Age' => array(
                                'Value' => '30',
                                'Units' => array(
                                    'Unit' => 'Years'
                                )
                            )
                        ),
                        'Source' => array(
                            'Actor' => array(
                                'ActorID' => $softwareGUID
                            )
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            )
        )
    )
);

/**
 * Problems section
 * ----------------
 * This section lists and describes all relevant clinical problems at the time the summary is generated. At a
 * minimum, all pertinent current and historical problems should be listed. CDA R2 represents problems as
 * Observations.
 */
$patientProblems = $Medical->getPatientProblemsByPid($_REQUEST['pid']);
foreach($patientProblems as $item)
{
    $problems[] = array(
        'Problem' => array(
            array( // Problem 1
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'ExactDateTime' => $item['begin_date']
                ),
                'Description' => array(
                    'Text' => $item['code_text'],
                    'Code' => array(
                        'Value' => $item['diagnosis_code'],
                        'CodingSystem' => $item['code']
                    )
                ),
                'Status' => array(
                    'Text' => 'Active',
                    'Code' => array(
                        'Value' => '55561003',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                )
            )
        )
    );
}

/**
 * Functional Status section
 * -------------------------
 * Functional Status describes the patient’s status of normal functioning at the time the Care Record was
 * created. Functional statuses include information regarding the patient relative to:
 * • Ambulatory ability
 * • Mental status or competency
 * • Activities of Daily Living (ADLs), including bathing, dressing, feeding, grooming
 * • Home / living situation having an effect on the health status of the patient
 * • Ability to care for self
 * • Social activity, including issues with social cognition, participation with friends and acquaintances other than family members
 * • Occupation activity, including activities partly or directly related to working, housework or
 * • volunteering, family and home responsibilities or activities related to home and family
 * • Communication ability, including issues with speech, writing or cognition required for communication
 * Perception, including sight, hearing, taste, skin sensation, kinesthetic sense, proprioception, or balance
 * Any deviation from normal function that the patient displays and is recorded in the record should be
 * included. Of particular interest are those limitations that would in any way interfere with self care or the
 * medical therapeutic process. In addition, an improvement, any change in or noting that the patient has
 * normal functioning status is also valid for inclusion.
 */
$functional = array(
    'Function' => array(
        array( // Function 1
            'CCRDocumentObjectID' => UUID::v4(),
            'Type' => array(
                'Text' => 'Ambulatory Status'
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'Problem' => array(
                array(
                    'CCRDocumentObjectID' => UUID::v4(),
                    'DateTime' => array(
                        'ExactDateTime' => '1999-11-07'
                    ),
                    'Description' => array(
                        'Text' => 'Dependence on cane',
                        'Code' => array(
                            'Value' => '105504002',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    ),
                    'Status' => array(
                        'Text' => 'Active',
                        'Code' => array(
                            'Value' => '55561003',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                ),
                array(
                    'CCRDocumentObjectID' => UUID::v4(),
                    'DateTime' => array(
                        'ExactDateTime' => '1999'
                    ),
                    'Description' => array(
                        'Text' => 'Memory impairment',
                        'Code' => array(
                            'Value' => '386807006',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    ),
                    'Status' => array(
                        'Text' => 'Active',
                        'Code' => array(
                            'Value' => '55561003',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                )
            )
        )
    )
);

/**
 * Supporters
 * ----------
 * Represents the patient’s sources of support such as immediate family, relatives, and guardian at the time the
 * summarization is generated. Support information also includes next of kin, caregivers, and support
 * organizations. At a minimum, key support contacts relative to healthcare decisions, including next of kin,
 * should be included.
 */
$supporters = array(
    'SupportProvider' => array(
        array( // Supporter 1
            'ActorID' => $family1GUID,
            'ActorRole' => array(
                'Text' => 'Guardian'
            ),
        ),
        array( // Supporter 2
            'ActorID' => $family2GUID,
            'ActorRole' => array(
                'Text' => 'Next of Kin'
            )
        )
    )
);

/**
 *   Advance Directives section
 * ----------------------------
 * This section contains data defining the patient’s advance directives and any reference to supporting
 * documentation. The most recent and up-to-date directives are required, if known, and should be listed in as
 * much detail as possible. This section contains data such as the existence of living wills, healthcare proxies,
 * and CPR and resuscitation status. If referenced documents are available, they can be included in the CCD
 * exchange package.
 */
$advanceDirectives = array(
        'AdvanceDirective' => array(
            array( // Advanced Directive 1
                'CCRDocumentObjectID' => UUID::v4(),
                'DateTime' => array(
                    'Type' => array(
                        'Text' => 'Verified With Patient'
                    ),
                    'ExactDateTime' => '1999-11-07'
                ),
                'Type' => array(
                    'Text' => 'Resuscitation status',
                    'Code' => array(
                        'Value' => '304251008',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Description' => array(
                    'Text' => 'Do not resuscitate',
                    'Code' => array(
                        'Value' => '304253006',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Status' => array(
                    'Text' => 'Current and verified',
                    'Code' => array(
                        'Value' => '15240007',
                        'CodingSystem' => 'SNOMED CT'
                    )
                ),
                'Source' => array(
                    'Actor' => array(
                        'ActorID' => $softwareGUID
                    )
                ),
                'ReferenceID' => $pdfDocumentGUID
            )
        )
);

/**
 * Payers Section
 * --------------
 * Payers contains data on the patient’s payers, whether a ‘third party’ insurance, self-pay, other payer or
 * guarantor, or some combination of payers, and is used to define which entity is the responsible fiduciary for
 * the financial aspects of a patient’s care.
 */
$payerProviderGUID = UUID::v4();
$subscriberGUID = UUID::v4();
$insuranceData = $Patient->getPatientPrimaryInsuranceByPid($_REQUEST['pid']);
$payers = array(
    'Payer' => array(
        array( // Payer 1
            'CCRDocumentObjectID' => UUID::v4(),
            'Type' => array(
                'Text' => $insuranceData['provider'],
                'Code' => array(
                    'Value' => 'EHCPOL',
                    'CodingSystem' => 'ActCode'
                )
            ),
            'Source' => array(
                'Actor' => array(
                    'ActorID' => $softwareGUID
                )
            ),
            'PaymentProvider' => array(
                'ActorID' => $payerProviderGUID
            ),
            'Subscriber' => array(
                'ActorID' => $subscriberGUID,
                'ActorRole' => array(
                    'Text' => 'Covered party'
                )
            ),
            'Authorizations' => array(
                'Authorization' => array(
                    'CCRDocumentObjectID' => UUID::v4(),
                    'Description' => array(
                        'Text' => 'Colonoscopy',
                        'Code' => array(
                            'Value' => '73761001',
                            'CodingSystem' => 'SNOMED CT'
                        )
                    ),
                    'Source' => array(
                        'Actor' => array(
                            'ActorID' => $softwareGUID
                        )
                    )
                )
            )
        )
    )
);

/**
 * The header and body render
 */
$ccrArray = array(
    '@attributes' => array(
        'xmlns' => 'urn:astm-org:CCR'
    ),
    'CCRDocumentObjectID' => UUID::v4(),
    'Language' => array(
        'Text' => 'English',
        'Code' => array(
            'Value' => 'en-US',
            'CodingSystem' => 'IETF1766'
        )
    ),
    'Version' => 'V1.0',
    'DateTime' => array(
        'ExactDateTime' => date('Y-m-d TH:i:s')
    ),
    'Patient' => array(
        'ActorID' => $patientGUID
    ),
    'From' => array(
        'ActorLink' => array(
            'ActorID' => $softwareGUID,
            'ActorRole' => array(
                'Text' => 'author'
            )
        )
    ),
    'Purpose' => array(
        'Description' => array(
            'Text' => 'Transfer of care',
            'Code' => array(
                'Value' => '308292007',
                'CodingSystem' => 'SNOMED CT'
            )
        )
    ),
    'Body' => array(
        'Payers' => $payers,
        // 'AdvanceDirectives' => $advanceDirectives,
        // 'Support' => $supporters,
        // 'FunctionalStatus' => $functional,
        'Problems' => $problems,
        // 'FamilyHistory' => $familyHistoryProblems,
        // 'SocialHistory' => $socialHistory,
        'Alerts' => $alerts,
        'Medications' => $medications,
        // 'MedicalEquipment' => $medicalEquipment,
        'Immunizations' => $immunizations, // DONE
        'VitalSigns' => $vitals, // DONE
        'Results' => $results,
        'Procedures' => $procedures,
        'Encounters' => $encounters,
        'PlanOfCare' => $planOfCare,
        'HealthCareProviders' => $healthProviders
    ),
    'Actors' => $actors,
    'References' => $references
);

/**
 * Build the CCR XML Object
 */
Array2XML::init('1.0', 'UTF-8', true, array('xml-stylesheet' => 'type="text/xsl" href="'.$_SESSION['url'].'/lib/CCRCDA/schema/ccr.xsl"'));
$xml = Array2XML::createXML('ContinuityOfCareRecord', $ccrArray);

if($_REQUEST['action'] == 'viewccr')
{
    header('Content-type: application/xml');
    echo $xml->saveXML();
}