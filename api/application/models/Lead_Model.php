<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_Model extends CI_Model {


    function __construct() {
        parent::__construct();

    }
	
    public function getLoanRepaymentDetails($lead_id) {

        $result_array = array("status" => 0, "repayment_data" => array());
        $update_loan_array = array();

        $data = array();
		$this->db->select('LD.lead_id, LD.user_type, LD.customer_id, LD.lead_status_id, MS.status_name as status, MS.status_stage as stage, 
						   LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, LD.lead_final_disbursed_date,
						   CAM.cam_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, 
						   CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, 
						   CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.cam_advance_interest_amount');
		$this->db->from('leads LD');
		$this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id');
		$this->db->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id');
		$this->db->join('master_status MS', 'MS.status_id = LD.lead_status_id');
		$this->db->where('LD.lead_id', $lead_id);
		$this->db->where('LD.lead_active', 1);
		$this->db->where('LD.lead_deleted', 0);

		$query = $this->db->get();
		$tempData = $query->result_array();


        if (!empty($tempData) && count($tempData) > 0) {
            // $status = 1;
            $leadDetails = $tempData[0];
            $lead_status_id = $leadDetails["lead_status_id"];
//            $user_type = $leadDetails["user_type"];
            $status = $leadDetails["status"];
//            $stage = $leadDetails["stage"];
            $loan_no = $leadDetails["loan_no"];
            $loan_recommended = ($leadDetails["loan_recommended"]) ? $leadDetails["loan_recommended"] : 0;
        }

        $disbursal_date = "-";
        $repayment_date = "-";
        $roi = 0;
        $penal_roi = 0;
        $tenure = 0;
        $ptenure = 0;
        $realIntrest = 0;
        $penaltyIntrest = 0;
        $recovered_interest_amount_deducted = 0;
        $advance_interest_amount_deducted = 0;
        $repayment_amount = 0;
        $total_repayment_amount = 0;
        $total_received_amount = 0;
        $total_due_amount = 0;

        $total_interest_amount = 0;
//        $total_principle_amount = 0;
//        $total_penalty_interest = 0;
        //discount was calculated on collection verify time.
        $principle_discount_amount = 0;
        $interest_discount_amount = 0;
        $penalty_discount_amount = 0;
        $total_discount_amount = 0;

			$this->db->select('LD.lead_id, LD.user_type, LD.customer_id, MS.status_name as status, MS.status_stage as stage, 
                   LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, LD.lead_final_disbursed_date, 
                   CAM.cam_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, 
                   CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, 
                   CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.cam_advance_interest_amount, 
                   L.loan_principle_discount_amount, L.loan_interest_discount_amount, L.loan_penalty_discount_amount, 
                   L.loan_total_discount_amount, L.loan_noc_settlement_letter, L.loan_noc_settled_letter_datetime, 
                   L.loan_noc_closed_letter_datetime, L.loan_noc_closing_letter');
			$this->db->from('leads LD');
			$this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id');
			$this->db->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id');
			$this->db->join('loan L', 'L.lead_id = LD.lead_id');
			$this->db->join('master_status MS', 'MS.status_id = LD.lead_status_id');
			$this->db->where('LD.lead_id', $lead_id);
			$this->db->where('LD.lead_active', 1);
			$this->db->where('LD.lead_deleted', 0);
			$this->db->where('L.loan_status_id', 14);

			$query = $this->db->get();
			$tempDetails = $query->result_array();


        if (!empty($tempDetails) && count($tempDetails) > 0) {

            $lead_details = $tempDetails[0];

            $roi = !empty($lead_details["roi"]) ? $lead_details["roi"] : 0;
            $loan_noc_settlement_letter = !empty($lead_details["loan_noc_settlement_letter"]) ? $lead_details["loan_noc_settlement_letter"] : 0;
            $loan_settled_date = !empty($lead_details["loan_noc_settled_letter_datetime"]) ? date('d-m-Y H:i:s', strtotime($lead_details["loan_noc_settled_letter_datetime"])) : 0;
            $loan_closure_date = !empty($lead_details["loan_noc_closed_letter_datetime"]) ? date('d-m-Y H:i:s', strtotime($lead_details["loan_noc_closed_letter_datetime"])) : 0;
            $loan_noc_closing_letter = !empty($lead_details["loan_noc_closing_letter"]) ? $lead_details["loan_noc_closing_letter"] : 0;
            $penal_roi = $roi * 2;
            $disbursal_date = !empty($lead_details["lead_final_disbursed_date"]) ? date('d-m-Y', strtotime($lead_details["lead_final_disbursed_date"])) : '';
            $repayment_date = !empty($lead_details["repayment_date"]) ? date('d-m-Y', strtotime($lead_details["repayment_date"])) : '';
            $tenure = !empty($lead_details["tenure"]) ? $lead_details["tenure"] : 0;
            $repayment_amount = !empty($lead_details["repayment_amount"]) ? $lead_details["repayment_amount"] : 0;
//            $processing_fee_percetage = !empty($lead_details["processing_fee_percent"]) ? $lead_details["processing_fee_percent"] : 0;
            $advance_interest_amount_deducted = !empty($lead_details["cam_advance_interest_amount"]) ? $lead_details["cam_advance_interest_amount"] : 0;
            $principle_discount_amount = !empty($lead_details["loan_principle_discount_amount"]) ? $lead_details["loan_principle_discount_amount"] : 0;
            $interest_discount_amount = !empty($lead_details["loan_interest_discount_amount"]) ? $lead_details["loan_interest_discount_amount"] : 0;
            $penalty_discount_amount = !empty($lead_details["loan_penalty_discount_amount"]) ? $lead_details["loan_penalty_discount_amount"] : 0;
            $total_discount_amount = !empty($lead_details["loan_total_discount_amount"]) ? $lead_details["loan_total_discount_amount"] : 0;

            $rtenure = 0;
            $ptenure = 0;

            $date_of_receive = strtotime(date('d-m-Y'));
            $date_of_receive_payment_verified = strtotime(date('d-m-Y'));
            $disbursal_date_to_time = strtotime($disbursal_date);
            $repayment_date_to_time = strtotime($repayment_date);

            $date_of_receive_flag = 0;

            //First get the date of received of settle or close case so that interest and panelty will be freez
           $this->db->select('CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on');
			$this->db->from('collection CO');
			$this->db->where('CO.lead_id', $lead_id);
			$this->db->where('CO.repayment_type', 17);
			$this->db->where('CO.payment_verification', 1);
			$this->db->where('CO.collection_active', 1);
			$this->db->where('CO.collection_deleted', 0);
			$this->db->order_by('CO.id', 'ASC');
			$this->db->limit(1);

			$query = $this->db->get();
			$tempDetails = $query->result_array();

            if (!empty($tempDetails) && count($tempDetails) > 0) {

                $first_settle_data = $tempDetails[0];

                if ($first_settle_data['repayment_type'] == 17 && !empty($first_settle_data["date_of_recived"]) && $first_settle_data["date_of_recived"] != '0000-00-00') {
                    $settle_date_of_receive = strtotime(date('d-m-Y', strtotime($first_settle_data["date_of_recived"])));
                    $settle_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_settle_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_settled_date'] = date('Y-m-d', strtotime($first_settle_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 1;
                    }
                }
            }

           $this->db->select('CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on');
			$this->db->from('collection CO');
			$this->db->where('CO.lead_id', $lead_id);
			$this->db->where('CO.repayment_type', 16);
			$this->db->where('CO.payment_verification', 1);
			$this->db->where('CO.collection_active', 1);
			$this->db->where('CO.collection_deleted', 0);
			$this->db->order_by('CO.id', 'ASC');
			$this->db->limit(1);

			$query = $this->db->get();
			$tempDetails = $query->result_array();


            if (!empty($tempDetails) && count($tempDetails) > 0) {
                $first_close_data = $tempDetails[0];
                if ($first_close_data['repayment_type'] == 16 && !empty($first_close_data["date_of_recived"]) && $first_close_data["date_of_recived"] != '0000-00-00') {
                    $close_date_of_receive = strtotime(date('d-m-Y', strtotime($first_close_data["date_of_recived"])));
                    $close_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_close_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_closure_date'] = date('Y-m-d', strtotime($first_close_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 2;
                    }
                }
            }

            $this->db->select('CO.repayment_type, CO.date_of_recived, CO.recovery_status, CO.closure_payment_updated_on');
			$this->db->from('collection CO');
			$this->db->where('CO.lead_id', $lead_id);
			$this->db->where('CO.repayment_type', 18);
			$this->db->where('CO.payment_verification', 1);
			$this->db->where('CO.collection_active', 1);
			$this->db->where('CO.collection_deleted', 0);
			$this->db->order_by('CO.id', 'ASC');
			$this->db->limit(1);

			$query = $this->db->get();
			$tempDetails = $query->result_array();


            if (!empty($tempDetails) && count($tempDetails) > 0) {
                $first_writeoff_data = $tempDetails[0];
                if ($first_writeoff_data['repayment_type'] == 18 && !empty($first_writeoff_data["date_of_recived"]) && $first_writeoff_data["date_of_recived"] != '0000-00-00') {
                    $writeoff_date_of_receive = strtotime(date('d-m-Y', strtotime($first_writeoff_data["date_of_recived"])));
                    $writeoff_date_of_receive_payment_verified = strtotime(date('d-m-Y', strtotime($first_writeoff_data["closure_payment_updated_on"])));
                    $update_loan_array['loan_writeoff_date'] = date('Y-m-d', strtotime($first_writeoff_data["date_of_recived"]));
                    if (empty($date_of_receive_flag)) {
                        $date_of_receive_flag = 3;
                    }
                }
            }

            if ($date_of_receive_flag == 1) {
                $date_of_receive = $settle_date_of_receive;
                $date_of_receive_payment_verified = $settle_date_of_receive_payment_verified;
            } else if ($date_of_receive_flag == 3) {
                $date_of_receive = $writeoff_date_of_receive;
                $date_of_receive_payment_verified = $writeoff_date_of_receive_payment_verified;
            } else if ($date_of_receive_flag == 2) {
                $date_of_receive = $close_date_of_receive;
                $date_of_receive_payment_verified = $close_date_of_receive_payment_verified;
            }

            $this->db->select_sum('CO.received_amount', 'total_paid');
			$this->db->from('collection CO');
			$this->db->where('CO.lead_id', $lead_id);
			$this->db->where('CO.payment_verification', 1);
			$this->db->where('CO.collection_active', 1);
			$this->db->where('CO.collection_deleted', 0);

			$query = $this->db->get();
			$tempDetails = $query->result_array();


            if (!empty($tempDetails) && count($tempDetails) > 0) {
                $total_received_amount = !empty($tempDetails[0]["total_paid"]) ? $tempDetails[0]["total_paid"] : 0;
            }


            if ($date_of_receive <= $repayment_date_to_time) {
                $realdays = $date_of_receive - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            } else {
                $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            }

            if ($date_of_receive_payment_verified <= $repayment_date_to_time) {
//                $realdays = $date_of_receive - $disbursal_date_to_time;
            } else {
                $endDate = $date_of_receive_payment_verified - $repayment_date_to_time;
                $oneDay = (60 * 60 * 24);
                $dateDays60 = ($oneDay * 60);

                if ($endDate <= $dateDays60) {
                    $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                    $rtenure = ($realdays / 60 / 60 / 24);
                    $paneldays = $date_of_receive_payment_verified - $repayment_date_to_time;
                    $ptenure = ($paneldays / 60 / 60 / 24);
                } else {
                    $ptenure = 60;
                }
            }

            $tenure = ($repayment_date_to_time - $disbursal_date_to_time) / (60 * 60 * 24);

            $interest_amount = round(($loan_recommended * $roi * $tenure) / 100);

            $realIntrest = round(($loan_recommended * $roi * $rtenure) / 100);

            $repayment_with_real_interest = $loan_recommended + $realIntrest;

            $total_interest_amount = $repayment_amount - $loan_recommended;

            if ($total_received_amount < $interest_amount) { // 700 < 1000
                $total_interest_amount_received = $total_received_amount;
                $total_interest_amount_pending = $interest_amount - ($total_interest_amount_received + $interest_discount_amount);
            } else if (($total_received_amount >= $interest_amount)) {
                $total_interest_amount_received = $interest_amount - $interest_discount_amount;
                $total_interest_amount_pending = 0;
            } else {
                $total_interest_amount_received = 0;
                $total_interest_amount_pending = $interest_amount;
            }

            if (($total_received_amount >= $interest_amount) && ($total_received_amount < $repayment_amount)) {
                $total_principle_amount_received = ($total_received_amount + $advance_interest_amount_deducted + $interest_discount_amount) - $interest_amount;
                $total_principle_amount_pending = $loan_recommended - $total_principle_amount_received - $principle_discount_amount;
            } else if (($total_received_amount >= $loan_recommended)) {
                $total_principle_amount_received = $loan_recommended - $principle_discount_amount;
                $total_principle_amount_pending = 0;
            } else {
                $total_principle_amount_received = 0;
                $total_principle_amount_pending = $loan_recommended - $principle_discount_amount;
            }


            /* if ($advance_interest_amount_deducted > 0) {
                $total_interest_amount = $advance_interest_amount_deducted;
                $total_interest_amount_received = $advance_interest_amount_deducted - $interest_discount_amount;
                $total_interest_amount_pending = 0;
                $total_received_amount = $total_received_amount + $interest_discount_amount + $total_interest_amount_received;
            } */

			if(!in_array($lead_status_id,[16,17,18])){
				$penaltyIntrest = ($loan_recommended * ($penal_roi) * $ptenure) / 100;
			}            
            $total_repayment_amount = ($repayment_amount + $penaltyIntrest + $advance_interest_amount_deducted);
            $total_due_amount = $total_repayment_amount - $total_received_amount - $total_discount_amount;
            if ($penaltyIntrest > 0) {
                if (($total_received_amount > $repayment_amount) && ($total_received_amount < $total_repayment_amount)) {
                    $total_penalty_interest_received = $total_received_amount - $repayment_amount - $advance_interest_amount_deducted;
                    $total_penalty_interest_pending = $penaltyIntrest - $total_penalty_interest_received - $penalty_discount_amount;
                } else if ($total_received_amount >= $total_repayment_amount) {
                    $total_penalty_interest_received = $penaltyIntrest - $penalty_discount_amount;
                    $total_penalty_interest_pending = 0;
                } else {
                    $total_penalty_interest_received = 0;
                    $total_penalty_interest_pending = $penaltyIntrest - $penalty_discount_amount;
                }
            } else {
                $total_penalty_interest_received = 0;
                $total_penalty_interest_pending = 0;
            }
			if($status == 'CLOSED'){
				$total_due_amount = 0;
			}
        }

        $data['loan_no'] = $loan_no;
        $data['lead_black_list_flag'] = !empty($lead_details["lead_black_list_flag"]) ? $lead_details["lead_black_list_flag"] : '';
        $data['status'] = $status;
        $data['disbursal_date'] = $disbursal_date;
        $data['repayment_date'] = $repayment_date;
        $data['repayment_interest_date'] = ($tenure > $rtenure) ? date("d-m-Y", $date_of_receive) : $repayment_date;
        $data['roi'] = round($roi, 2);
        $data['penal_roi'] = round($penal_roi, 2);
        $data['tenure'] = $tenure;
        $data['realdays'] = $rtenure;
        $data['penalty_days'] = $ptenure;
        $data['recovered_interest_amount_deducted'] = round($recovered_interest_amount_deducted, 0);
        $data['advance_interest_amount_deducted'] = round($advance_interest_amount_deducted, 0);
        $data['repayment_amount'] = round($repayment_amount, 0);

        $data['real_interest'] = round($realIntrest, 0);
        $data['repayment_with_real_interest'] = round($repayment_with_real_interest, 0);

        $data['total_interest_amount'] = round($total_interest_amount);
        $data['interest_discount_amount'] = round($interest_discount_amount, 0);
        $data['total_interest_amount_received'] = round($total_interest_amount_received, 0);
        $data['total_interest_amount_pending'] = round($total_interest_amount_pending, 0);

        $data['loan_recommended'] = round($loan_recommended, 0);
        $data['principle_discount_amount'] = round($principle_discount_amount, 0);
        $data['total_principle_amount_received'] = round($total_principle_amount_received, 0);
        $data['total_principle_amount_pending'] = round($total_principle_amount_pending, 0);

        $data['penalty_interest'] = round($penaltyIntrest, 0);
        $data['penalty_discount_amount'] = round($penalty_discount_amount, 0);
        $data['total_penalty_interest_received'] = round($total_penalty_interest_received, 0);
        $data['total_penalty_interest_pending'] = round($total_penalty_interest_pending, 0);

        $data['total_repayment_amount'] = round($total_repayment_amount, 0);
        $data['total_received_amount'] = round($total_received_amount, 0);
        $data['total_due_amount'] = round($total_due_amount, 0);
        $data['total_discount_amount'] = round($total_discount_amount, 0);
        $data['loan_noc_settled_letter_datetime'] = $loan_settled_date;
        $data['loan_noc_settlement_letter'] = $loan_noc_settlement_letter;
        $data['loan_noc_closed_letter_datetime'] = $loan_closure_date;
        $data['loan_noc_closing_letter'] = $loan_noc_closing_letter;

        if (!empty($loan_no) && !in_array($lead_status_id,[16,17,18])) {

            $update_loan_array['loan_principle_payable_amount'] = $loan_recommended;
            $update_loan_array['loan_interest_payable_amount'] = $total_interest_amount;
            $update_loan_array['loan_penalty_payable_amount'] = $penaltyIntrest;
            $update_loan_array['loan_principle_received_amount'] = $total_principle_amount_received;
            $update_loan_array['loan_interest_received_amount'] = $total_interest_amount_received;
            $update_loan_array['loan_penalty_received_amount'] = $total_penalty_interest_received;
            $update_loan_array['loan_principle_outstanding_amount'] = $total_principle_amount_pending;
            $update_loan_array['loan_interest_outstanding_amount'] = $total_interest_amount_pending;
            $update_loan_array['loan_penalty_outstanding_amount'] = $total_penalty_interest_pending;
            $update_loan_array['loan_total_payable_amount'] = $total_repayment_amount;
            $update_loan_array['loan_total_received_amount'] = $total_received_amount;
            $update_loan_array['loan_total_outstanding_amount'] = $total_due_amount;

           // $this->updateLoanTable($lead_id, $update_loan_array);
        }

        $result_array = array("status" => 1, "repayment_data" => $data);

        return $result_array;
    }

}

?>
