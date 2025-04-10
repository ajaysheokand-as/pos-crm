<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CibilController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
    }

    public function index() {
        // ini_set('display_errors', '1');
        // ini_set('display_startup_errors', '1');
        // error_reporting(E_ALL);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $lead_id = $this->encrypt->decode($this->input->post('lead_id'));

            if (!empty($lead_id)) {

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $cibil_call_return = $CommonComponent->call_bureau_api($lead_id);

                if ($cibil_call_return['status'] == 1) {
                    $json['lead_id'] = $lead_id;
                    $json['msg'] = 'Cibil generated successfully.';
                    echo json_encode($json);
                } else {
                    $json['err'] = trim($cibil_call_return['errors']);
                    echo json_encode($json);
                    return false;
                }
            } else {
                $json['err'] = "Lead Id is Required";
                echo json_encode($json);
                return false;
            }
        }
    }

    public function old_index() {

        // ini_set('display_errors', '1');
        // ini_set('display_startup_errors', '1');
        // error_reporting(E_ALL);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $lead_id = $this->input->post('lead_id');
            $fetch = 'LD.customer_id, C.first_name, C.middle_name, C.sur_name, C.mobile, C.dob, C.pancard, C.gender, LD.state_id, LD.city_id, LD.pincode, LD.loan_amount'; //  LD.check_cibil_status,
            $conditions = ['LD.lead_id' => $lead_id];
            $table1 = 'leads LD';
            $table2 = 'lead_customer C';
            $join2 = 'C.customer_lead_id = LD.lead_id';
            $query = $this->Tasks->join_two_table_with_where($conditions, $fetch, $table1, $table2, $join2);
            $leadDetails = $query->row();

            // if($leadDetails->check_cibil_status == 0)
            // {
            if (!empty($lead_id)) {
                $m_name = str_replace("-", "", $leadDetails->middle_name);
                $m_name = ($m_name ? $m_name : '');
                $s_name = str_replace("-", "", $leadDetails->sur_name);
                $s_name = ($s_name ? $s_name : '');
                $customer_id = $leadDetails->customer_id;
                $name = $leadDetails->first_name;
                $mobile = $leadDetails->mobile;
                $pancard = $leadDetails->pancard;
                $gender = $leadDetails->gender;
                $dob = $leadDetails->dob;
                $state_id = $leadDetails->state_id;
                $cityQuery = $this->db->select("m_city_name as city")->where('m_city_id', $leadDetails->city_id)->from('master_city')->get()->row();
                $city = $cityQuery->city;

                $address = $city;
                $pincode = $leadDetails->pincode;
                $loan_amount = ($leadDetails->loan_amount) ? round($leadDetails->loan_amount) : 5000;

                if (empty($name) || empty($mobile) || empty($pancard) || empty($gender) || empty($dob) || empty($state_id) || empty($city) || empty($pincode)) {
                    $requiredData = [
                        'name' => $name . '' . $m_name . ' ' . $s_name,
                        'mobile' => $mobile,
                        'pancard' => $pancard,
                        'gender' => $gender,
                        'dob' => $dob,
                        'state_id' => $state_id,
                        'city' => $city,
                        'pincode' => $pincode
                    ];

                    foreach ($requiredData as $key => $value) {
                        if (empty($value)) {
                            $error .= $key . ", ";
                        }
                    }
                    $json['err'] = "Please update all required fields - " . $error;
                    echo json_encode($json);
                    return false;
                } else {
                    $day = date('d', strtotime($dob));
                    $month = date("m", strtotime($dob));
                    $year = date("Y", strtotime($dob));
                    $dateOfBirth = $day . '' . $month . '' . $year;

                    $scoreType = '08';
                    $purpose = '06';  // 01 - 06
                    $solutionSetId = '140';

                    $query_state = $this->db->select("m_state_name as state")->where("m_state_id", $state_id)->get('master_state')->row_array();
                    $stateName = ucwords(strtolower($query_state['state']));

                    $stateNameData = array(
                        '01' => 'Jammu & Kashmir',
                        '02' => 'Himachal Pradesh',
                        '03' => 'Punjab',
                        '04' => 'Chandigarh',
                        '05' => 'Uttaranchal',
                        '06' => 'Haryana',
                        '07' => 'Delhi',
                        '08' => 'Rajasthan',
                        '09' => 'Uttar Pradesh',
                        '10' => 'Bihar',
                        '11' => 'Sikkim',
                        '12' => 'Arunachal Pradesh',
                        '13' => 'Nagaland',
                        '14' => 'Manipur',
                        '15' => 'Mizoram',
                        '16' => 'Tripura',
                        '17' => 'Meghalaya',
                        '18' => 'Assam',
                        '19' => 'West Bengal',
                        '20' => 'Jharkhand',
                        '21' => 'Orissa',
                        '22' => 'Chhattisgarh',
                        '23' => 'Madhya Pradesh',
                        '24' => 'Gujarat',
                        '25' => 'Daman & Diu',
                        '26' => 'Dadra & Nagar Haveli',
                        '27' => 'Maharashtra',
                        '28' => 'Andhra Pradesh',
                        '29' => 'Karnataka',
                        '30' => 'Goa',
                        '31' => 'Lakshadweep',
                        '32' => 'Kerala',
                        '33' => 'Tamil Nadu',
                        '34' => 'Pondicherry',
                        '35' => 'Andaman & Nicobar Islands',
                        '36' => 'Telangana'
                    );

                    $stateKey = array_search($stateName, $stateNameData);

                    if (ENVIRONMENT != "production") {
                        define("userId", "NB4235DC01_UAT001");
                        define("password", "TempPass@cibil2");
                        define("memberId", "NB42358888_UATC2C");
                        define("memberPass", "2iqzapqOkcqgmf@qnvni");
                        define("api_url", "https://www.test.transuniondecisioncentre.co.in/DC/TU/TU.IDS.ExternalServices/SolutionExecution/ExternalSolutionExecution.svc");

                        $day = date('dd', strtotime($dob));
                        $month = date("mm", strtotime($dob));
                        $year = date("YYYY", strtotime($dob));
                        $dateOfBirth = $day . '' . $month . '' . $year;

                        $name = "BHIMSERI SHYAM";
                        $mobile = 9823511152;
                        $pancard = "ACYPB6874G";
                        $gender = "Male";
                        $dateOfBirth = "05151963";
                        $address = "SHASHI INVESTMENT";
                        $stateKey = 07;
                        $city = "Delhi";
                        $pincode = 110001;
                    } else {
                        define("userId", "NB4235DC01_PROD002");
                        define("password", "Lo@anwalle15Dec2020");
                        define("memberId", "NB42358899_CIRC2C");
                        define("memberPass", "Ce8#Yh8@Py8@Dh");
                        define("api_url", "https://www.dc.transuniondecisioncentre.co.in/DE/TU.IDS.ExternalServices/SolutionExecution/ExternalSolutionExecution.svc");
                    }

                    $input_xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                                    <soapenv:Header />
                                    <soapenv:Body>
                                      <tem:ExecuteXMLString>
                                        <tem:request>
                                          <![CDATA[

                                  <DCRequest xmlns="http://transunion.com/dc/extsvc">
                                    <Authentication type="OnDemand">
                                        <UserId>' . userId . '</UserId>
                                        <Password>' . password . '</Password>
                                     </Authentication>
                                     <RequestInfo>
                                          <SolutionSetId>140</SolutionSetId>
                                          <ExecuteLatestVersion>true</ExecuteLatestVersion>
                                          <ExecutionMode>NewWithContext</ExecutionMode>
                                    </RequestInfo>
                                    <Fields>
                                      <Field key="Applicants">

                                            &lt;Applicants&gt;
                                    &lt;Applicant&gt;
                                      &lt;ApplicantType&gt;Main&lt;/ApplicantType&gt;
                                      &lt;ApplicantFirstName&gt;' . $name . '&lt;/ApplicantFirstName&gt;
                                      &lt;ApplicantMiddleName&gt;' . $m_name . '&lt;/ApplicantMiddleName&gt;
                                      &lt;ApplicantLastName&gt;' . $s_name . '&lt;/ApplicantLastName&gt;
                                      &lt;DateOfBirth&gt;' . $dateOfBirth . '&lt;/DateOfBirth&gt;
                                      &lt;Gender&gt;' . $gender . '&lt;/Gender&gt;
                                      &lt;EmailAddress&gt;&lt;/EmailAddress&gt;
                                      &lt;CompanyName&gt;&lt;/CompanyName&gt;
                                      &lt;Identifiers&gt;
                                        &lt;Identifier&gt;
                                          &lt;IdNumber&gt;' . $pancard . '&lt;/IdNumber&gt;
                                          &lt;IdType&gt;01&lt;/IdType&gt;
                                        &lt;/Identifier&gt;
                                        &lt;Identifier&gt;
                                          &lt;IdNumber&gt;&lt;/IdNumber&gt;
                                          &lt;IdType&gt;06&lt;/IdType&gt;
                                        &lt;/Identifier&gt;
                                      &lt;/Identifiers&gt;
                                      &lt;Telephones&gt;
                                        &lt;Telephone&gt;
                                          &lt;TelephoneExtension&gt;&lt;/TelephoneExtension&gt;
                                          &lt;TelephoneNumber&gt;' . $mobile . '&lt;/TelephoneNumber&gt;
                                          &lt;TelephoneType&gt;01&lt;/TelephoneType&gt;
                                        &lt;/Telephone&gt;
                                         &lt;Telephone&gt;
                                          &lt;TelephoneExtension&gt;&lt;/TelephoneExtension&gt;
                                          &lt;TelephoneNumber&gt;&lt;/TelephoneNumber&gt;
                                          &lt;TelephoneType&gt;01&lt;/TelephoneType&gt;
                                        &lt;/Telephone&gt;
                                      &lt;/Telephones&gt;
                                      &lt;Addresses&gt;
                                        &lt;Address&gt;
                                          &lt;AddressLine1&gt;' . $address . '&lt;/AddressLine1&gt;
                                          &lt;AddressLine2&gt;&lt;/AddressLine2&gt;
                                          &lt;AddressLine3&gt;&lt;/AddressLine3&gt;
                                          &lt;AddressLine4&gt;&lt;/AddressLine4&gt;
                                          &lt;AddressLine5&gt;&lt;/AddressLine5&gt;
                                          &lt;AddressType&gt;01&lt;/AddressType&gt;
                                          &lt;City&gt;' . $city . '&lt;/City&gt;
                                          &lt;PinCode&gt;' . $pincode . '&lt;/PinCode&gt;
                                          &lt;ResidenceType&gt;01&lt;/ResidenceType&gt;
                                          &lt;StateCode&gt;' . $stateKey . '&lt;/StateCode&gt;
                                        &lt;/Address&gt;
                                      &lt;/Addresses&gt;
                                      &lt;NomineeRelation&gt;&lt;/NomineeRelation&gt;
                                      &lt;NomineeName&gt;&lt;/NomineeName&gt;
                                      &lt;MemberRelationType4&gt;&lt;/MemberRelationType4&gt;
                                      &lt;MemberRelationName4&gt;&lt;/MemberRelationName4&gt;
                                      &lt;MemberRelationType3&gt;&lt;/MemberRelationType3&gt;
                                      &lt;MemberRelationName3&gt;&lt;/MemberRelationName3&gt;
                                      &lt;MemberRelationType2&gt;&lt;/MemberRelationType2&gt;
                                      &lt;MemberRelationName2&gt;&lt;/MemberRelationName2&gt;
                                      &lt;MemberRelationType1&gt;&lt;/MemberRelationType1&gt;
                                      &lt;MemberRelationName1&gt;&lt;/MemberRelationName1&gt;
                                      &lt;KeyPersonRelation&gt;&lt;/KeyPersonRelation&gt;
                                      &lt;KeyPersonName&gt;&lt;/KeyPersonName&gt;
                                      &lt;MemberOtherId3&gt;&lt;/MemberOtherId3&gt;
                                      &lt;MemberOtherId3Type&gt;&lt;/MemberOtherId3Type&gt;
                                      &lt;MemberOtherId2&gt;&lt;/MemberOtherId2&gt;
                                      &lt;MemberOtherId2Type&gt;&lt;/MemberOtherId2Type&gt;
                                      &lt;MemberOtherId1&gt;&lt;/MemberOtherId1&gt;
                                      &lt;MemberOtherId1Type&gt;&lt;/MemberOtherId1Type&gt;
                                      &lt;Accounts&gt;
                                        &lt;Account&gt;
                                          &lt;AccountNumber&gt;&lt;/AccountNumber&gt;
                                        &lt;/Account&gt;
                                      &lt;/Accounts&gt;
                                    &lt;/Applicant&gt;
                                  &lt;/Applicants&gt;

                                  </Field>
                                  <Field key="ApplicationData">
                                   &lt;ApplicationData&gt;
                                  &lt;Purpose&gt;10&lt;/Purpose&gt;
                                  &lt;Amount&gt;' . $loan_amount . '&lt;/Amount&gt;
                                  &lt;ScoreType&gt;08&lt;/ScoreType&gt;
                                  &lt;GSTStateCode&gt;07&lt;/GSTStateCode&gt;


                                  &lt;MemberCode&gt;' . memberId . '&lt;/MemberCode&gt;
                                  &lt;Password&gt;' . memberPass . '&lt;/Password&gt;


                                  &lt;CibilBureauFlag&gt;False&lt;/CibilBureauFlag&gt;
                                    &lt;DSTuNtcFlag&gt;True&lt;/DSTuNtcFlag&gt;
                                    &lt;IDVerificationFlag&gt;False&lt;/IDVerificationFlag&gt;
                                    &lt;MFIBureauFlag&gt;True&lt;/MFIBureauFlag&gt;
                                    &lt;NTCProductType&gt;PL&lt;/NTCProductType&gt;
                                    &lt;ConsumerConsentForUIDAIAuthentication&gt;N&lt;/ConsumerConsentForUIDAIAuthentication&gt;
                                    &lt;MFIEnquiryAmount&gt;&lt;/MFIEnquiryAmount&gt;
                                    &lt;MFILoanPurpose&gt;&lt;/MFILoanPurpose&gt;
                                    &lt;MFICenterReferenceNo&gt;&lt;/MFICenterReferenceNo&gt;
                                    &lt;MFIBranchReferenceNo&gt;&lt;/MFIBranchReferenceNo&gt;
                                    &lt;FormattedReport&gt;True&lt;/FormattedReport&gt;
                                &lt;/ApplicationData&gt;


                                            </Field>
                                            <Field key="FinalTraceLevel">2</Field>
                                            </Fields>
                                            </DCRequest>
                                       ]]>
                                    </tem:request>
                                  </tem:ExecuteXMLString>
                                </soapenv:Body>
                              </soapenv:Envelope>';

                    $url = api_url;

                    $headers = [
                        'Content-Type: text/xml',
                        'soapAction: http://tempuri.org/IExternalSolutionExecution/ExecuteXMLString'
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $dataResponse = curl_exec($ch);

                    $soap = simplexml_load_string($dataResponse);
                    $response = $soap->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->ExecuteXMLStringResponse;
                    $xx = $response->ExecuteXMLStringResult;
                    $xx = simplexml_load_string($xx);
                    $ApplicationId = (string) $xx->ResponseInfo->ApplicationId;
                    if (!empty($ApplicationId) && $ApplicationId != null) {
                        $insertCibilData = [
                            'lead_id' => $lead_id,
                            'customer_id' => $customer_id,
                            'applicationId' => $ApplicationId,
                            'cibil_created_by' => $_SESSION['isUserSession']['user_id'],
                            'created_at' => date("Y-m-d H:i:s"),
                            'cibil_pancard' => $pancard,
                        ];

                        $this->db->insert('tbl_cibil', $insertCibilData); // INSERT DATA INTO CIBIL
                        $cibil_id = $this->db->insert_id(); // GET LAST INSERTED ID FROM CIBIL

                        $insertCibilLogData = [
                            'lead_id' => $lead_id,
                            'customer_id' => $customer_id,
                            'customer_name' => $name,
                            'customer_mobile' => $mobile,
                            'pancard' => $pancard,
                            'loan_amount' => $loan_amount,
                            'dob' => $dateOfBirth,
                            'gender' => $gender,
                            'city' => $city,
                            'state_id' => $state_id,
                            'pincode' => $pincode,
                            'api1_request' => $input_xml,
                            'api1_response' => $dataResponse,
                            'applicationId' => $ApplicationId,
                        ];
                        $this->db->insert('tbl_cibil_log', $insertCibilLogData); // INSERT DATA INTO CIBIL LOG
                        $cibil_log_id = $this->db->insert_id(); // GET LAST INSERTED ID FROM COBIL LOGS

                        if (!empty($cibil_id) && !empty($cibil_log_id)) {
                            $this->getApplication($cibil_id, $cibil_log_id, $lead_id, $ApplicationId, $customer_id); // APPLICATION GET API CALLED
                        }
                        curl_close($ch);
                    } else {
                        $insertCibilLogData = [
                            'lead_id' => $lead_id,
                            'customer_id' => $customer_id,
                            'customer_name' => $name,
                            'customer_mobile' => $mobile,
                            'pancard' => $pancard,
                            'loan_amount' => $loan_amount,
                            'dob' => $dateOfBirth,
                            'gender' => $gender,
                            'city' => $city,
                            'state_id' => $state_id,
                            'pincode' => $pincode,
                            'api1_request' => $input_xml,
                            'api1_response' => $dataResponse,
                            'applicationId' => (($ApplicationId) ? $ApplicationId : 0),
                        ];
                        $this->db->insert('tbl_cibil_log', $insertCibilLogData); // INSERT DATA INTO CIBIL LOG
                        // $json['err'] = "Failed to check Internal dedupe. Please check valid customer details";

                        $json['err'] = "CIBIL API Authentication Failed.";
                        echo json_encode($json);
                    }
                }
            } else {
                $json['err'] = "Lead Id is Required";
                echo json_encode($json);
            }
        }
    }

    public function getApplication($cibil_id, $cibil_log_id, $lead_id, $ApplicationId, $customer_id) {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
        $xml2 = '
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Header />
                    <soapenv:Body>
                      <tem:RetrieveDocumentMetaDataXMLString>
                        <tem:request>
                          <![CDATA[
                  <DCRequest xmlns="http://transunion.com/dc/extsvc">
                <Authentication type="Token">
                    <UserId>' . userId . '</UserId>
                    <Password>' . password . '</Password>
                </Authentication>
                <RetrieveDocumentMetaData>
                <ApplicationId>' . $ApplicationId . '</ApplicationId>
                </RetrieveDocumentMetaData>
                </DCRequest>
                ]]>
                    </tem:request>
                  </tem:RetrieveDocumentMetaDataXMLString>
                </soapenv:Body>
                </soapenv:Envelope>
            ';

        $url2 = api_url;

        $ch2 = curl_init();
        $headers2 = [
            'Content-Type: text/xml',
            'soapAction: http://tempuri.org/IExternalSolutionExecution/RetrieveDocumentMetaDataXMLString'
        ];

        curl_setopt($ch2, CURLOPT_URL, $url2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $xml2);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers2);

        $data2 = curl_exec($ch2);
        $soap = simplexml_load_string($data2);
        file_put_contents('text.txt', $data2);
        $response = $soap->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->RetrieveDocumentMetaDataXMLStringResponse;

        $xx = $response->RetrieveDocumentMetaDataXMLStringResult;
        $xx = simplexml_load_string($xx);
        $documentId = (string) $xx->ResponseInfo->DocumentDetails->DocumentMetaData->DocumentId;

        if (!empty($documentId) && $documentId != null) {

            $updateCibilData = ['document_Id' => $documentId];
            $this->db->where('cibil_id', $cibil_id)->update('tbl_cibil', $updateCibilData); // UPDATE DOCUMENT ID IN CIBIL

            $updateCibilLogData = [
                'api2_request' => $xml2,
                'api2_response' => $data2,
                'document_Id' => $documentId,
            ];
            $this->db->where('cibil_id', $cibil_log_id)->update('tbl_cibil_log', $updateCibilLogData); // UPDATE DATA INTO CIBIL LOG
            $this->getDocument($cibil_id, $cibil_log_id, $lead_id, $ApplicationId, $documentId, $customer_id); // Document API CALLED
        } else {
            $json['err'] = "Document Id is Required. Failed request Document API.";
            echo json_encode($json);
        }
    }

    public function getDocument($cibil_id, $cibil_log_id, $lead_id, $ApplicationId, $documentId, $customer_id) {
        $xml3 = '
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                        <soapenv:Header />
                        <soapenv:Body>
                          <tem:DownloadDocument>
                            <tem:request>
                              <![CDATA[
                    <DCRequest xmlns="http://transunion.com/dc/extsvc">
                          <Authentication type="Token">
                                <UserId>' . userId . '</UserId>
                                <Password>' . password . '</Password>
                          </Authentication>
                          <DownloadDocument>
                            <ApplicationId>' . $ApplicationId . '</ApplicationId>
                            <DocumentId>' . $documentId . '</DocumentId>
                          </DownloadDocument>
                        </DCRequest>
                           ]]>
                        </tem:request>
                      </tem:DownloadDocument>
                    </soapenv:Body>
                  </soapenv:Envelope>
            ';

        $url3 = api_url;

        $ch3 = curl_init();
        $headers3 = [
            'Content-Type: text/xml',
            'soapAction: http://tempuri.org/IExternalSolutionExecution/DownloadDocument'
        ];

        curl_setopt($ch3, CURLOPT_URL, $url3);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch3, CURLOPT_POST, true);
        curl_setopt($ch3, CURLOPT_POSTFIELDS, $xml3);
        curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers3);

        $data3 = curl_exec($ch3);
        $filename = base64_decode($data3);

        $newFile = strstr($filename, "<?xml");
        $file = substr($newFile, 0, strpos($newFile, "</html>"));
        $file .= "</html>";
        $htmlResult = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);

        $result = mb_convert_encoding($htmlResult, 'UTF-16', 'UTF-8');
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($result); //or simplexml_load_file
        //        foreach (libxml_get_errors() as $error) {
        //            print_r($error);
        //        }

        if (false === $result) {
            throw new Exception('Input string could not be converted.');
        }
        $xml = simplexml_load_string($result) or die("xml not loading");
        $cibilScore = $xml->body->table->tr[8]->td->table->tr->td[1];

        $summary = $xml->body->table->tr[29]->td->table->tr[3]; //->td[1]
        $overdue = $xml->body->table->tr[29]->td->table->tr[4]; //->td[1]
        $zerobalanceAcccount = $xml->body->table->tr[29]->td->table->tr[5]; //->td[1]
        $totalAccount = 0;
        $totalBalance = 0;
        $overDueAccount = 0;
        $overDueAmount = 0;
        $zeroBalance = 0;
        $higestcrSecAmt = 0;
        $i = 0;

        foreach ($summary->children() as $key => $child) {
            if ($i == 1) {
                $totalAccount = $child;
            }
            if ($i == 2) {
                $higestcrSecAmt = $child;
            } else if ($i == 3) {
                $totalBalance = $child;
            }
            $i++;
        }

        foreach ($overdue->children() as $key => $child) {
            if ($i == 6) {
                $overDueAccount = $child;
            } else if ($i == 8) {
                $overDueAmount = $child;
            }

            $i++;
        }

        foreach ($zerobalanceAcccount->children() as $key => $child) {
            if ($i == 11) {
                $zeroBalance = $child;
            }
            $i++;
        }

        $data = [
            'memberCode' => $xml->body->table->tr[1]->td->table->tr[1]->td[0]->table->tr[1]->td[1],
            'cibilScore' => ($cibilScore) ? $cibilScore : 0,
            'totalAccount' => $totalAccount,
            'totalBalance' => $totalBalance,
            'highCrSanAmt' => $higestcrSecAmt,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance,
            'cibil_file' => $htmlResult
        ];

        $data2 = [
            'api3_request' => $xml3,
            'api3_response' => $data3,
            'cibil_file' => $htmlResult,
            'memberCode' => $xml->body->table->tr[1]->td->table->tr[1]->td[0]->table->tr[1]->td[1],
            'cibilScore' => ($cibilScore) ? $cibilScore : 0,
            'totalAccount' => $totalAccount,
            'highCrSanAmt' => $higestcrSecAmt,
            'totalBalance' => $totalBalance,
            'overDueAccount' => $overDueAccount,
            'overDueAmount' => $overDueAmount,
            'zeroBalance' => $zeroBalance
        ];
        $this->db->where('cibil_id', $cibil_id)->update('tbl_cibil', $data);
        $this->db->where('cibil_id', $cibil_log_id)->update('tbl_cibil_log', $data2);
        $this->db->where('lead_id', $lead_id)->update('leads', ['check_cibil_status' => 1, 'cibil' => $cibilScore]);
        $json['customer_id'] = $customer_id;
        $json['lead_id'] = $lead_id;
        $json['msg'] = 'Cibil generated successfully.';
        echo json_encode($json);
    }

    public function viewCibilScore($cibil_id) {
        //   ini_set('display_errors', '1');
        // ini_set('display_startup_errors', '1');
        // error_reporting(E_ALL);
        $data = $this->getCibilFile($cibil_id);
        $filename = $data['cibil_file'];

        if ($filename) {
            $file1 = file_get_contents('readdata.xml', $filename);
            $newFile = strstr($filename, "<?xml");
            $file = substr($newFile, 0, strpos($newFile, "</html>"));
            $file .= "</html>";
            $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
            // echo "<pre>"; print_r($temp); exit;
            $result = mb_convert_encoding($temp, 'UTF-16', 'UTF-8');

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($result); //or simplexml_load_file
            // echo "<pre>"; print_r($result); exit;
            foreach (libxml_get_errors() as $error) {
                print_r($error);
            }

            if (false === $result) {
                throw new Exception('Input string could not be converted.');
            }
            $xml = simplexml_load_string($result) or die("xml not loading");
            $summary = $xml->body->table->tr[29]->td->table->tr[3]; //->td[1]
            $overdue = $xml->body->table->tr[29]->td->table->tr[4]; //->td[1]
            $zerobalanceAcccount = $xml->body->table->tr[29]->td->table->tr[5]; //->td[1]
            $totalAccount = 0;
            $totalBalance = 0;
            $overDueAccount = 0;
            $overDueAmount = 0;
            $zeroBalance = 0;
            $i = 0;
            foreach ($overdue->children() as $key => $child) {
                if ($i == 1) {
                    $overDueAmount = $child;
                }
                $i++;
            }
            foreach ($summary->children() as $key => $child) {
                if ($i == 6) {
                    echo $totalAccount = $child;
                    // echo $i. "totalAccount node: " . $child->getName(). " = ". $key ." val : " . $totalAccount . "</br>";
                } else if ($i == 7) {
                    echo $totalBalance = $child;
                    // echo $i. "totalBalance node: " . $child->getName(). " = ". $key ." val : " . $totalBalance . "</br>";
                } else if ($i == 8) {
                    echo $overDueAccount = $child;
                    // echo $i. "overDueAccount node: " . $child->getName(). " = ". $key ." val : " . $overDueAccount . "</br>";
                }
                $i++;
            }

            foreach ($zerobalanceAcccount->children() as $key => $child) {
                if ($i == 11) {
                    $zeroBalance = $child;
                }
                $i++;
            }

            print_r($summary->children());
            exit;

            $data = [
                'memberCode' => $xml->body->table->tr[1]->td->table->tr[1]->td[0]->table->tr[1]->td[1],
                'cibilScore' => $xml->body->table->tr[8]->td->table->tr->td[1],
                'totalAccount' => $totalAccount,
                'totalBalance' => $totalBalance,
                'overDueAccount' => $overDueAccount,
                'overDueAmount' => $overDueAmount,
                'zeroBalance' => $zeroBalance
            ];
        } else {
            exit('Failed to open readdata.xml.');
        }
    }

    public function ViewCivilStatement() {
        $json = '';
        if (!empty($_POST['lead_id'])) {
            $lead_id = intval($this->encrypt->decode($_POST['lead_id']));
            $pancard = strval($_POST['pancard']);
            $json = $this->Tasks->ViewCivilStatement($lead_id);
        }
        echo json_encode($json);
    }

    public function getCibilFile($cibil_id) {
        if (!empty($cibil_id)) {

            $sql = "SELECT cibil_file FROM tbl_cibil WHERE cibil_id = ? AND s3_flag = 1";
            $result = $this->db->query($sql, [$cibil_id])->row();

            if (!empty($result)) {
                require_once(COMPONENT_PATH . "CommonComponent.php");
                $CommonComponent = new CommonComponent();

                $request_array['file'] = $result->cibil_file;
                $result_array = $CommonComponent->download_document($lead_id, $request_array);

                $documentBody = $result_array['document_body'];
                $content = $documentBody->__toString();
                return ['cibil_file' => $content];
            } else {
                $result = $this->db->select('tbl_cibil.cibil_file')->where('cibil_id', $cibil_id)->get('tbl_cibil')->row();
                $data = ['cibil_file' => $result->cibil_file];
                return $data;
            }
        }
    }

    public function viewCustomerCibilPDF($cibil_id) {

        $data = $this->getCibilFile($cibil_id);
        $filename = $data['cibil_file'];
        print_r($filename);

        $data = file_get_contents(APPPATH . "/views/cibil_score_pdf.php", $filename);

        $html = $this->load->view("cibil_score_pdf", $data, true);

        // $mpdf = new \Mpdf\Mpdf();
        require_once __DIR__ . '/../../vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($data);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->defaultfooterline = 1;
        $mpdf->default_font_size = 110000;
        $mpdf->Output();
    }

    public function downloadCibilPDF($cibil_id) {
        // exit;
        $data = $this->getCibilFile($cibil_id);
        $filename = $data['cibil_file'];

        // $dom = new DOMDocument;
        // $dom->preserveWhiteSpace = FALSE;
        // $dom->loadXML($filename);
        // if($dom->save(APPPATH.'cibil.xml')){
        //     echo "<h2>Site Map Created SuccessFully</h2>";
        // }else{
        //     echo "<h2>Site Map Created Failed</h2>";
        // }
        // $this->output->set_content_type('text/xml');
        // $this->output->set_output($filename);

        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="text.xml"');

        echo $xml_contents;
    }

    public function downloadcibil($cibil_id) {

        $data = $this->getCibilFile($cibil_id);
        $filename = $data['cibil_file'];
        file_put_contents(APPPATH . "/views/cibil.php", $filename);

        $html = $this->load->view(utf8_encode("cibil"));

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteText($html);
        $mpdf->defaultfooterline = 1;
        $mpdf->Output('MyPDF.pdf', 'D');
    }

    public function checkpdf() {
        $result = $this->db->select('tbl_cibil.cibil_file, tbl_cibil.customer_name')->where('cibil_id', "1718")->get('tbl_cibil')->row();
        $mpdf = new \Mpdf\Mpdf();
        $html = $this->load->view('Tasks/cibilpdfview', $data, true);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

    public function cibilpdfView($cibil_id) {
        $result = $this->db->select('tbl_cibil.cibil_file, tbl_cibil.customer_name')->where('cibil_id', $cibil_id)->get('tbl_cibil')->row();
        $data = [
            'customer_name' => $result->customer_name,
            'cibil_file' => $result->cibil_file
        ];
        return $this->load->view('Tasks/cibilpdfview', $data);
    }

    public function viewDownloadCibilPDF($cibil_id) {
        $data = $this->getCibilFile($cibil_id);
        $filename = $data['cibil_file'];

        // $pth    =   file_get_contents($filename);
        // $nme    =   $customer_name. "cibil_". todayDate .".pdf";
        // $nme    =   file_get_contents('cibil.pdf', $filename);
        // force_download($nme, $pth);
        // force_download($nme, $filename);

        $data = file_get_contents('cibil.pdf', $filename);
        // $data = file_get_contents($filename.".pdf");
        // force_download('file.pdf', $data);

        $mpdf = new \Mpdf\Mpdf();
        // $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '']);
        $mpdf->SetProtection(array());
        // $html = $this->load->view('pdf', $data, true);
        $mpdf->WriteHTML($data);
        $mpdf->defaultfooterline = 1;
        $mpdf->Output();
    }
}
