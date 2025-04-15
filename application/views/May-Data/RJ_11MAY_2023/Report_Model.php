<?php

class Report_Model extends CI_Model
{

    public function ExportMaster()
    {

        $sql = "SELECT m_report_id, m_report_heading, m_report_active, m_report_is_live FROM master_mis_report WHERE m_report_active = 1 ";

        $user_id = $_SESSION['isUserSession']['user_id'];

        // if (agent != 'CA' && $user_id != 222) {
        //     $sql .= "AND m_report_is_live = 1 ";
        // }

        if (agent != 'CA' && !in_array($user_id, [222])) {
            if ($user_id == 265) {
                $sql .= "AND m_report_id=33 ";
            } else {
                $sql .= "AND m_report_is_live = 1 ";
            }
        }

        $sql .= "ORDER BY m_report_heading ASC;";

        $result = $this->db->query($sql)->result_array();

        // if ($user_id == 265) { //265    66
        //     $result = '';
        // }

        return $result;
    }

    public function ReportName($fname)
    {
        if (!empty($fname)) {
            $q = $this->db->select('m_report_heading')
                ->from('master_report')
                ->where('m_report_id', $fname)
                ->get();
            $result = $q->result_array();

            return $result;
        }
    }

    public function PreCollectionCalculationModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT report.lead_id, report.lead_status_id, report.roi, report.tenure, report.loan_recommended, report.disbursal_date, report.repayment_date, report.pre_status_id, report.pre_collection_amount, report.pre_last_payment_date, report.pre_collection_amount_discount, report.collection_status_id, report.collection_amount, report.collection_amount_discount ,report.collection_last_payment_date, report.recovery_status_id, report.recovery_amount, report.recovery_amount_discount ,report.recovery_last_payment_date, report.loan_interest_discount_amount, report.loan_closure_date, report.loan_principle_discount_amount, report.loan_penalty_discount_amount, report.loan_settled_date ";

            // pre-collection

            $q .= "FROM (SELECT LD.lead_id, LD.lead_status_id, CAM.disbursal_date, CAM.repayment_date, CAM.loan_recommended, CAM.roi, CAM.tenure, (SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived <= CAM.repayment_date) as pre_collection_amount, (SELECT SUM(COL.discount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived <= CAM.repayment_date) as pre_collection_amount_discount, (SELECT MAX(COL.date_of_recived) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived <= CAM.repayment_date) as pre_last_payment_date, (SELECT MIN(COL.repayment_type) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived <= CAM.repayment_date) as pre_status_id, L.loan_interest_discount_amount, L.loan_closure_date, L.loan_principle_discount_amount, L.loan_penalty_discount_amount, L.loan_settled_date, ";

            /// collection

            $q .= "(SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > CAM.repayment_date AND COL.date_of_recived <= (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as collection_amount, (SELECT SUM(COL.discount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > CAM.repayment_date AND COL.date_of_recived <= (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as collection_amount_discount, (SELECT MAX(COL.date_of_recived) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > CAM.repayment_date AND COL.date_of_recived <= (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as collection_last_payment_date, (SELECT MIN(COL.repayment_type) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > CAM.repayment_date AND COL.date_of_recived <= (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as collection_status_id, ";

            // recovery

            $q .= "(SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as recovery_amount, (SELECT SUM(COL.discount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as recovery_amount_discount, (SELECT MAX(COL.date_of_recived) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as recovery_last_payment_date, (SELECT MIN(COL.repayment_type) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.collection_active=1 AND COL.payment_verification=1 AND COL.date_of_recived > (DATE_ADD(CAM.repayment_date, INTERVAL 10 DAY))) as recovery_status_id ";

            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE LD.lead_id=CAM.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND LD.lead_active=1 AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate') as report;";

            // CAM.repayment_date >= '2022-09-01' AND CAM.repayment_date <= '2022-09-05' CAM.lead_id=543472 

            $result = $this->db->query($q)->result_array();

            if (!empty($result)) {

                $result_array = array();
                $status_array = array(14 => 'DISBURSED', 16 => 'CLOSED', 17 => 'SETTLED', 18 => 'WRITE-OFF', 19 => 'PART-PAYMENT');

                foreach ($result as $row) {
                    $lead_id = $row['lead_id'];
                    $lead_status_id = $row['lead_status_id'];
                    $loan_recommended = $row['loan_recommended'];
                    $roi = $row['roi'];
                    $tenure = $row['tenure'];
                    $disbursal_date = $row['disbursal_date'];
                    $repayment_date = $row['repayment_date'];
                    $daydifferent = (strtotime(date('Y-m-d')) - strtotime($repayment_date)) / 86400;

                    $result_array[$row['lead_id']]['disbursal']['lead_id'] = $lead_id;
                    $result_array[$row['lead_id']]['disbursal']['lead_status_id'] = $lead_status_id;
                    $result_array[$row['lead_id']]['disbursal']['loan_recommended'] = $loan_recommended;
                    $result_array[$row['lead_id']]['disbursal']['roi'] = $roi;
                    $result_array[$row['lead_id']]['disbursal']['tenure'] = $tenure;
                    $result_array[$row['lead_id']]['disbursal']['disbursal_date'] = $disbursal_date;
                    $result_array[$row['lead_id']]['disbursal']['repayment_date'] = $repayment_date;

                    $pre_status_id = $row['pre_status_id'];
                    $pre_total_pre_collection = $row['pre_collection_amount'];
                    // $pre_collection_amount_discount = $row['pre_collection_amount_discount'];
                    $pre_received_date = $row['pre_last_payment_date'];

                    // Discount  start
                    $loan_interest_discount_amount = 0;
                    $loan_closed_date = $row['loan_closure_date'];
                    $loan_settled_date = $row['loan_settled_date'];
                    $loan_interest_discount_amount = $row['loan_interest_discount_amount'];
                    $loan_principle_discount_amount = $row['loan_principle_discount_amount'];

                    $principal_discount = 0;
                    $total_discount = 0;

                    if (empty($loan_closed_date)) {
                        $loan_closed_date = $loan_settled_date;
                    }
                    if (strtotime($loan_closed_date) <= strtotime($repayment_date)) {
                        $principal_discount = $loan_principle_discount_amount;
                        $total_discount = $loan_interest_discount_amount + $principal_discount;
                    }
                    // Discount  end

                    $payable_int = ($loan_recommended * ($roi / 100) * $tenure);

                    $int_rcvd = 0;
                    $pre_int_outstanding = 0;
                    if ($pre_total_pre_collection >= $payable_int) {
                        $int_rcvd = $payable_int - $loan_interest_discount_amount;
                        $pre_int_outstanding = 0;
                    } elseif ($payable_int > $pre_total_pre_collection) {
                        $int_rcvd = $pre_total_pre_collection;
                        $pre_int_outstanding = $payable_int - $pre_total_pre_collection;
                    }

                    $prnl_rcvd = 0;
                    $pre_prnl_outstanding = 0;
                    if (($pre_total_pre_collection - $int_rcvd) >= $loan_recommended) {
                        $prnl_rcvd = $loan_recommended - $principal_discount;
                        $pre_prnl_outstanding = 0;
                    } elseif ($loan_recommended > ($pre_total_pre_collection - $int_rcvd)) {
                        $prnl_rcvd = ($pre_total_pre_collection - $int_rcvd);
                        $pre_prnl_outstanding = $loan_recommended - ($pre_total_pre_collection - $int_rcvd);
                    }

                    if (in_array($pre_status_id, [16, 17, 18])) {
                        $pre_int_outstanding = 0;
                        $pre_prnl_outstanding = 0;
                    }

                    $status = '';
                    if (isset($status_array[$pre_status_id])) {
                        $status = $status_array[$pre_status_id];
                    }

                    $result_array[$row['lead_id']]['pre_collection']['lead_id'] = $lead_id;
                    $result_array[$row['lead_id']]['pre_collection']['lead_status_id'] = $pre_status_id;
                    $result_array[$row['lead_id']]['pre_collection']['loan_recommended'] = $loan_recommended;
                    $result_array[$row['lead_id']]['pre_collection']['roi'] = $roi;
                    $result_array[$row['lead_id']]['pre_collection']['tenure'] = $tenure;
                    $result_array[$row['lead_id']]['pre_collection']['total_collection'] = $pre_total_pre_collection;
                    $result_array[$row['lead_id']]['pre_collection']['payable_int'] = $payable_int;
                    $result_array[$row['lead_id']]['pre_collection']['int_rcvd'] = $int_rcvd;
                    $result_array[$row['lead_id']]['pre_collection']['discount_amount'] = $total_discount;
                    $result_array[$row['lead_id']]['pre_collection']['int_outstanding'] = $pre_int_outstanding;
                    $result_array[$row['lead_id']]['pre_collection']['prnl_rcvd'] = $prnl_rcvd;
                    $result_array[$row['lead_id']]['pre_collection']['prnl_outstanding'] = $pre_prnl_outstanding;
                    $result_array[$row['lead_id']]['pre_collection']['received_date'] = $pre_received_date;
                    $result_array[$row['lead_id']]['pre_collection']['status'] = $status;

                    if (!in_array($pre_status_id, [16, 17, 18]) && $daydifferent > 0) { // Collection Report
                        $collection_status_id = $row['collection_status_id'];
                        $collection_amount = $row['collection_amount'];
                        // $collection_amount_discount = $row['collection_amount_discount'];
                        $collection_last_payment_date = $row['collection_last_payment_date'];
                        $transfer_prnl_amount = $pre_prnl_outstanding;

                        if (!empty($collection_last_payment_date) && date('Y-m-d', strtotime($repayment_date)) >= date('Y-m-d', strtotime($collection_last_payment_date))) {
                            $repayment_date = $collection_last_payment_date;
                            $tenure = date('d', (strtotime($collection_last_payment_date) - strtotime($disbursal_date)));
                        }

                        $payable_int = (($loan_recommended * ($roi / 100) * $tenure) - $int_rcvd);

                        // Discount  start
                        $loan_penalty_discount_amount = $row['loan_penalty_discount_amount'];

                        $principal_discount = 0;
                        $total_discount = 0;
                        if (date('Y-m-d', strtotime($loan_closed_date)) > $repayment_date && date('Y-m-d', strtotime($loan_closed_date)) <= date('Y-m-d', strtotime('+10 days', strtotime($repayment_date)))) {
                            $principal_discount = $loan_principle_discount_amount;
                            $total_discount = $loan_interest_discount_amount + $principal_discount + $loan_penalty_discount_amount;
                        }
                        // Discount  end

                        $int_rcvd = 0;
                        $coll_int_outstanding = $pre_int_outstanding;
                        if ($coll_int_outstanding > 0) {
                            if ($collection_amount >= $coll_int_outstanding) {
                                $int_rcvd = $coll_int_outstanding - $loan_interest_discount_amount;
                                $coll_int_outstanding = 0;
                            } elseif ($coll_int_outstanding > $collection_amount) {
                                $int_rcvd = $collection_amount;
                                $coll_int_outstanding = $coll_int_outstanding - $collection_amount;
                            }
                        }


                        $prnl_rcvd = 0;
                        $coll_prnl_outstanding = 0;
                        if ($pre_prnl_outstanding > 0) {
                            if (($collection_amount - $int_rcvd) >= $pre_prnl_outstanding) {
                                $prnl_rcvd = $pre_prnl_outstanding - $principal_discount;
                                $pre_prnl_outstanding = 0;
                            } elseif ($pre_prnl_outstanding > ($collection_amount - $int_rcvd)) {
                                $prnl_rcvd = ($collection_amount - $int_rcvd);
                                $coll_prnl_outstanding = $pre_prnl_outstanding - ($collection_amount - $int_rcvd);
                            }
                        }

                        $coll_dpd = 0;
                        if (in_array($collection_status_id, [16, 17, 18])) {
                            $coll_dpd = round((strtotime($collection_last_payment_date) - strtotime($repayment_date)) / (60 * 60 * 24));
                        } elseif ((round((strtotime(date('Y-m-d')) - strtotime($repayment_date)) / (60 * 60 * 24))) <= 10) {
                            $coll_dpd = round((strtotime(date('Y-m-d')) - strtotime($repayment_date)) / (60 * 60 * 24));
                        } else {
                            $coll_dpd = 10;
                        }

                        $payable_penal_int = ((($roi * 2) / 100) * $coll_dpd * $loan_recommended);

                        $penal_rcvd = 0;
                        $coll_penal_outstanding = 0;
                        if (($collection_amount - $int_rcvd - $prnl_rcvd) >= $payable_penal_int) {
                            $penal_rcvd = $payable_penal_int;
                            $coll_penal_outstanding = 0;
                        } elseif ($payable_penal_int > ($collection_amount - $int_rcvd - $prnl_rcvd)) {
                            $penal_rcvd = ($collection_amount - $int_rcvd - $prnl_rcvd);
                            $coll_penal_outstanding = $payable_penal_int - ($collection_amount - $int_rcvd - $prnl_rcvd);
                        }

                        if (in_array($collection_status_id, [16, 17, 18])) {
                            $coll_int_outstanding = 0;
                            $coll_prnl_outstanding = 0;
                            $coll_penal_outstanding = 0;
                        }

                        $coll_case_forwarded = '';
                        if (in_array($collection_status_id, [16, 17, 18])) {
                            $coll_case_forwarded = date("Y-m-d", (strtotime($collection_last_payment_date) - strtotime($repayment_date)));
                        } elseif (round((time() - strtotime($repayment_date)) / (60 * 60 * 24)) <= 10) {
                            $coll_case_forwarded = date("Y-m-d", (time() - strtotime($repayment_date)));
                        } else {
                            $coll_case_forwarded = date("Y-m-d", strtotime("+1 days", strtotime($repayment_date)));
                        }

                        $status = '';
                        if (isset($status_array[$collection_status_id])) {
                            $status = $status_array[$collection_status_id];
                        }

                        $result_array[$row['lead_id']]['collection']['lead_id'] = $lead_id;
                        $result_array[$row['lead_id']]['collection']['lead_status_id'] = $collection_status_id;
                        $result_array[$row['lead_id']]['collection']['case_forwarded_on'] = $coll_case_forwarded;
                        $result_array[$row['lead_id']]['collection']['loan_recommended'] = $loan_recommended;
                        $result_array[$row['lead_id']]['collection']['roi'] = $roi;
                        $result_array[$row['lead_id']]['collection']['collection_payable'] = $transfer_prnl_amount;
                        $result_array[$row['lead_id']]['collection']['tenure'] = $tenure;
                        $result_array[$row['lead_id']]['collection']['dpd'] = $coll_dpd;
                        $result_array[$row['lead_id']]['collection']['total_collection'] = $collection_amount;
                        $result_array[$row['lead_id']]['collection']['payable_int'] = $pre_int_outstanding;
                        $result_array[$row['lead_id']]['collection']['int_rcvd'] = $int_rcvd;
                        $result_array[$row['lead_id']]['collection']['discount_amount'] = $total_discount;
                        $result_array[$row['lead_id']]['collection']['int_outstanding'] = $coll_int_outstanding;
                        $result_array[$row['lead_id']]['collection']['prnl_rcvd'] = $prnl_rcvd;
                        $result_array[$row['lead_id']]['collection']['prnl_outstanding'] = $coll_prnl_outstanding;
                        $result_array[$row['lead_id']]['collection']['penal_payable'] = $payable_penal_int;
                        $result_array[$row['lead_id']]['collection']['penal_rcvd'] = $penal_rcvd;
                        $result_array[$row['lead_id']]['collection']['penal_outstanding'] = $coll_penal_outstanding;
                        $result_array[$row['lead_id']]['collection']['received_date'] = $collection_last_payment_date;
                        $result_array[$row['lead_id']]['collection']['status'] = $status;

                        if (!in_array($collection_status_id, [16, 17, 18]) && $daydifferent > 10) { // Recovery Report
                            $recovery_status_id = $row['recovery_status_id'];
                            $recovery_amount = $row['recovery_amount'];
                            // $recovery_amount_discount = $row['recovery_amount_discount'];
                            $recovery_last_payment_date = $row['recovery_last_payment_date'];
                            $transfer_recovery_prnl = $coll_prnl_outstanding;

                            // Discount  start
                            $loan_penalty_discount_amount = $row['loan_penalty_discount_amount'];

                            $principal_discount = 0;
                            $total_discount = 0;
                            if (date('Y-m-d', strtotime($loan_closed_date)) > date('Y-m-d', strtotime('+10 days', strtotime($repayment_date)))) {
                                $principal_discount = $loan_principle_discount_amount;
                                $total_discount = $loan_interest_discount_amount + $principal_discount + $loan_penalty_discount_amount;
                            }
                            // Discount  end

                            $int_rcvd = 0;
                            $int_outstanding = $coll_int_outstanding;
                            if ($recovery_amount >= $int_outstanding) {
                                $int_rcvd = $int_outstanding - $loan_interest_discount_amount;
                                $int_outstanding = 0;
                            } elseif ($int_outstanding > $recovery_amount) {
                                $int_rcvd = $recovery_amount;
                                $int_outstanding = $int_outstanding - $recovery_amount;
                            }

                            $prnl_rcvd = 0;
                            $recovery_prnl_outstanding = 0;
                            if (($recovery_amount - $int_rcvd) >= $coll_prnl_outstanding) {
                                $prnl_rcvd = $coll_prnl_outstanding - $principal_discount;
                                $recovery_prnl_outstanding = 0;
                            } elseif ($coll_prnl_outstanding > ($recovery_amount - $int_rcvd)) {
                                $prnl_rcvd = ($recovery_amount - $int_rcvd);
                                $recovery_prnl_outstanding = $coll_prnl_outstanding - ($recovery_amount - $int_rcvd);
                            }

                            $dpd = 0;
                            if (in_array($recovery_status_id, [16, 17, 18])) {
                                $dpd = round((strtotime($recovery_last_payment_date) - strtotime($repayment_date)) / (60 * 60 * 24)) - $coll_dpd;
                            } else {
                                $dpd = round((strtotime(date('Y-m-d')) - strtotime($repayment_date)) / (60 * 60 * 24)) - $coll_dpd;
                            }

                            if (($dpd + $coll_dpd) <= 60) {
                                $dpd = $dpd;
                            } elseif (($dpd + $coll_dpd) >= 60) {
                                $dpd = 50;
                            }

                            $recovery_payable_penal_int = ((($roi * 2) / 100) * $dpd * $loan_recommended);

                            $recovery_penal_outstanding = ($recovery_payable_penal_int + $coll_penal_outstanding);

                            $penal_rcvd = 0;
                            if (($recovery_amount - ($int_rcvd + $prnl_rcvd)) >= $recovery_penal_outstanding) {
                                $penal_rcvd = $recovery_penal_outstanding - $loan_penalty_discount_amount;
                                $recovery_penal_outstanding = 0;
                            } else {   // ($recovery_penal_outstanding < ($recovery_amount - ($int_rcvd + $prnl_rcvd)))
                                $penal_rcvd = ($recovery_amount - ($int_rcvd + $prnl_rcvd));
                                $recovery_penal_outstanding = $recovery_penal_outstanding - ($recovery_amount - ($int_rcvd + $prnl_rcvd));
                            }

                            if (in_array($recovery_status_id, [16, 17, 18])) {
                                $int_outstanding = 0;
                                $recovery_prnl_outstanding = 0;
                                $recovery_penal_outstanding = 0;
                            }

                            $status = '';
                            if (isset($status_array[$recovery_status_id])) {
                                $status = $status_array[$recovery_status_id];
                            }

                            $result_array[$row['lead_id']]['recovery']['lead_id'] = $lead_id;
                            $result_array[$row['lead_id']]['recovery']['lead_status_id'] = $recovery_status_id;
                            $result_array[$row['lead_id']]['recovery']['case_forwarded_on'] = date('Y-m-d', strtotime('+10 days', strtotime($coll_case_forwarded)));
                            $result_array[$row['lead_id']]['recovery']['loan_recommended'] = $loan_recommended;
                            $result_array[$row['lead_id']]['recovery']['roi'] = $roi;
                            $result_array[$row['lead_id']]['recovery']['recovery_payable'] = $transfer_recovery_prnl;
                            $result_array[$row['lead_id']]['recovery']['tenure'] = $tenure;
                            $result_array[$row['lead_id']]['recovery']['dpd'] = $dpd;
                            $result_array[$row['lead_id']]['recovery']['total_recovery'] = $recovery_amount;
                            $result_array[$row['lead_id']]['recovery']['payable_int'] = $coll_int_outstanding;
                            $result_array[$row['lead_id']]['recovery']['int_rcvd'] = $int_rcvd;
                            $result_array[$row['lead_id']]['recovery']['discount_amount'] = $total_discount;
                            $result_array[$row['lead_id']]['recovery']['int_outstanding'] = $int_outstanding;
                            $result_array[$row['lead_id']]['recovery']['prnl_rcvd'] = $prnl_rcvd;
                            $result_array[$row['lead_id']]['recovery']['prnl_outstanding'] = $recovery_prnl_outstanding;
                            $result_array[$row['lead_id']]['recovery']['penal_payable'] = $recovery_payable_penal_int;
                            $result_array[$row['lead_id']]['recovery']['penal_rcvd'] = $penal_rcvd;
                            $result_array[$row['lead_id']]['recovery']['penal_outstanding'] = $recovery_penal_outstanding;
                            $result_array[$row['lead_id']]['recovery']['received_date'] = $recovery_last_payment_date;
                            $result_array[$row['lead_id']]['recovery']['status'] = $status;
                        }
                    }
                }
            }

            // echo "<pre>";
            // print_r($result_array);
            // print_r($result);
            // exit;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $result_array;
    }

    public function leadTotalReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $query1 = $this->db->select("LD.status 'Lead Source', count(*) '#Count'")
                ->from('leads LD')
                ->join('users U1', 'LD.lead_credit_assign_user_id = U1.user_id', 'RIGHT')
                ->join('master_status', 'LD.lead_status_id = master_status.status_id', 'LEFT')
                ->where("LD.lead_active = 1 AND LD.lead_entry_date!='' AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate'")
                // ->Where("U1.labels='CR2'")
                ->group_by('LD.status')
                ->get();
            // print_r($this->db->last_query()); exit;
            $q1 = $query1->result_array();
            $query2 = $this->db->select("'Total', count(*)")
                ->from('leads LD')
                ->join('users U1', 'LD.lead_credit_assign_user_id = U1.user_id', 'RIGHT')
                ->join('master_status', 'LD.lead_status_id = master_status.status_id', 'LEFT')
                ->where("LD.lead_active = 1 AND LD.lead_entry_date!='' AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate'")
                // ->Where("U1.labels='CR2'")
                ->limit(1)
                ->get();
            // print_r($this->db->last_query()); exit;
            $q2 = $query2->result_array();
            $query = array_merge($q1, $q2);
            if ($query) {
                foreach ($query as $row) {
                    $report_data[] = $row;
                }
                $report_header_array = array_keys($report_data[0]);
                $data = '<div class="table-responsive"><table class="table table-bordered table-hover" style="margin-top: 10px;margin-left: 30px" id="customers"><thead><tr>';
                $i = 0;
                foreach ($report_header_array as $key) {
                    $data .= '<th><strong>' . $report_header_array[$i] . '</strong></th>';
                    $i++;
                }
                $data .= '</tr></thead><tbody>';
                foreach ($report_data as $key) {
                    $data .= '<tr>';
                    foreach ($report_header_array as $key2) {
                        $data .= '<td>' . $key[$key2] . '</td>';
                    }
                    $data .= '</tr>';
                }
                $data .= '</tbody></table></div>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function DisbursedReport($month_data)
    {
        if (!empty($month_data)) {
            $fromDate = date('Y-m-d', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));
            $pre_month = date('d-m-Y', strtotime('-1 months', strtotime($fromDate)));
            $start_month = date("Y-m-01", strtotime($pre_month));
            $end_month = date("Y-m-t", strtotime($pre_month));

            //                $q = 'SET sql_mode = " "; ';
            $q = "SELECT report.disbursal_date as Month_year, count(*) counts, SUM(report.admin_fee) as admin_fee, SUM(report.loan_recommended) loan_amount ";
            $q .= "FROM (SELECT LD.lead_id, CAM.disbursal_date, CAM.admin_fee, CAM.loan_recommended loan_recommended, CAM.repayment_amount repayment_amount FROM `leads` LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_status_id IN(14,16,17,18,19) AND LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND L.loan_status_id=14  AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate') as report GROUP BY report.disbursal_date ORDER BY disbursal_date ASC;"; // AND LD.lead_data_source_id NOT IN(21, 27)

            $result = $this->db->query($q)->result_array();

            $q1 = "SELECT report.disbursal_date as pre_Month_year, count(*) pre_counts, SUM(report.admin_fee) as pre_admin_fee, SUM(report.loan_recommended) pre_loan_amount ";
            $q1 .= "FROM (SELECT LD.lead_id, CAM.disbursal_date, CAM.admin_fee, CAM.loan_recommended loan_recommended, CAM.repayment_amount repayment_amount FROM `leads` LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) ";
            $q1 .= "INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND L.loan_status_id=14 AND LD.lead_data_source_id NOT IN(21, 27) AND CAM.disbursal_date >= '$start_month' AND CAM.disbursal_date <= '$end_month') as report GROUP BY report.disbursal_date ORDER BY disbursal_date ASC;";
            $pre_result = $this->db->query($q1)->result_array();

            $q2 = "SELECT COL.lead_id, COL.date_of_recived, SUM(COL.received_amount) as collection_amount FROM collection COL WHERE COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived >= '$fromDate' AND COL.date_of_recived <= '$toDate' GROUP BY COL.date_of_recived;";
            $collection_current_month_result = $this->db->query($q2)->result_array();

            if ($result && $pre_result) {

                $report_array = array();
                $i = 0;
                $current = $fromDate;
                $pre_date = $start_month;

                $total_count = 0;
                $total_loan_amount = 0;
                $total_pre_count = 0;
                $total_pre_loan_amount = 0;
                $total_collection = 0;
                $total_admin_fee = 0;

                $pre_result_final_array = array();
                $post_result_final_array = array();
                $collection_result_final_array = array();
                $collection_result_last_month_final_array = array();
                $collection_data = array();

                if (!empty($pre_result)) {
                    foreach ($pre_result as $pre_data) {
                        $total_pre_count += $pre_data['pre_counts'];
                        $total_pre_loan_amount += $pre_data['pre_loan_amount'];

                        $pre_result_final_array[$pre_data['pre_Month_year']]['pre_counts'] = $pre_data['pre_counts'];
                        $pre_result_final_array[$pre_data['pre_Month_year']]['pre_loan_amount'] = $pre_data['pre_loan_amount'];
                    }
                }

                if (!empty($result)) {
                    foreach ($result as $post_data) {
                        $total_count += $post_data['counts'];
                        $total_loan_amount += $post_data['loan_amount'];
                        $total_admin_fee += $post_data['admin_fee'];

                        $post_result_final_array[$post_data['Month_year']]['counts'] = $post_data['counts'];
                        $post_result_final_array[$post_data['Month_year']]['loan_amount'] = $post_data['loan_amount'];
                        $post_result_final_array[$post_data['Month_year']]['admin_fee'] = $post_data['admin_fee'];
                    }
                }

                if (!empty($total_collection_last_month_result)) {
                    foreach ($total_collection_last_month_result as $total_col_data) {
                        $collection_result_last_month_final_array[$total_col_data['lead_id']]['date_of_recived'] = $total_col_data['date_of_recived'];
                        $collection_result_last_month_final_array[$total_col_data['lead_id']]['loan_amount'] = $total_col_data['loan_recommended'];
                        $collection_result_last_month_final_array[$total_col_data['lead_id']]['received_amount_as_on_last_month'] = $total_col_data['received_amount_as_on_last_month'];
                    }
                }


                if (!empty($collection_current_month_result)) {
                    foreach ($collection_current_month_result as $col_data) {
                        $total_collection += $col_data['collection_amount'];

                        $collection_result_final_array[$col_data['date_of_recived']]['date_of_recived'] = $col_data['date_of_recived'];
                        $collection_result_final_array[$col_data['date_of_recived']]['lead_id'] = $col_data['lead_id'];
                        $collection_result_final_array[$col_data['date_of_recived']]['collection_amount'] = $col_data['collection_amount'];
                        if (isset($collection_result_last_month_final_array[$col_data['lead_id']])) {
                            $part_payment = $collection_result_last_month_final_array[$col_data['lead_id']]['received_amount_as_on_last_month'];
                            $collection_result_final_array[$col_data['date_of_recived']]['received_amount_as_on_last_month'] = $collection_result_final_array[$col_data['lead_id']]['received_amount_as_on_last_month'] + $part_payment;
                        }
                    }
                }


                for (
                    $dt = 1;
                    $dt <= 31;
                    $dt++
                ) {

                    $report_array[$i]['Month_year'] = $current;
                    $report_array[$i]['pre_Month_year'] = $pre_date;

                    if (isset($pre_result_final_array[$pre_date])) {
                        $report_array[$i]['pre_counts'] = $pre_result_final_array[$pre_date]['pre_counts'];
                        $report_array[$i]['pre_loan_amount'] = $pre_result_final_array[$pre_date]['pre_loan_amount'];
                    }

                    if (isset($post_result_final_array[$current])) {
                        $report_array[$i]['counts'] = $post_result_final_array[$current]['counts'];
                        $report_array[$i]['loan_amount'] = $post_result_final_array[$current]['loan_amount'];
                        $report_array[$i]['admin_fee'] = $post_result_final_array[$current]['admin_fee'];
                    }

                    if (isset($collection_result_final_array[$current])) {
                        $report_array[$i]['collection_amount'] = $collection_result_final_array[$current]['collection_amount'];
                    }


                    $pre_date = date("Y-m-d", strtotime("+1 day", strtotime($pre_date)));
                    $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
                    $i++;
                }

                $fromDate = date('d-M-Y', strtotime($fromDate));
                $toDate = date('d-M-Y', strtotime($toDate));

                $data = '<table class="bordered"><tbody><tr class="fir-header">';
                $data .= '<th colspan="3" align="center" class="footer-tabels-text">Previous Month Disbursment</th>';
                $data .= '<th colspan="7" align="center" class="footer-tabels-text">Month Wise Disbursal/Collection Summary Report ' . $fromDate . ' to ' . $toDate . ' Generated at : ' . date('d-M-Y h:i:s') . '</th>';
                $data .= '</tr><tr class="sec-header">';
                $data .= '<th width="4%" rowspan="2" align="center" valign="middle" class="no-of-case" style="border-left:none !important;">Date</th>';
                $data .= '<th colspan="2" align="center" class="disbu">Disbursed Loans</th>';
                $data .= '<th width = "6%" rowspan = "2" align = "center" valign = "middle" class = "datess">Date</th>';
                $data .= '<th colspan = "2" align = "center" style = "color:#0363a3;  font-weight:bold;">Disbursed Loans&nbsp;</th >';
                $data .= '<th width = "6%" rowspan = "2" align = "center" valign = "middle" class = "no-of-case" title="' . date('M-Y', strtotime($fromDate)) . ' Collection Amount" style="cursor: pointer;">Collection Amount</th>';
                //                    $data .= '<th width = "5%" rowspan = "2" align = "center" class = "no-of-case">Income</th>';
                $data .= '<th width = "3%" rowspan = "2" align = "center" class = "no-of-case" title="Processing Fee" style="cursor: pointer;">Admin Fee</th>';
                $data .= '<th width = "3%" rowspan = "2" align = "center" class = "no-of-case">Total Income</th></tr><tr class="thr-header">';
                $data .= '<th width = "4%" align = "center" class = "no-of-case">No. of Cases</th>';
                $data .= '<th width = "4%" align = "center" class = "amounts">Loan Amount</th>';
                $data .= '<th width = "5%" align = "center" class = "no-of-case" style = "border-left:none !important;">No. of Cases</th>';
                $data .= '<th width = "8%" align = "center" class = "no-of-case">Loan Amount</th></tr>';

                foreach ($report_array as $row_data) {
                    $pre_month_date = date('d-m-Y', strtotime($row_data['pre_Month_year']));
                    $pre_count = number_format($row_data['pre_counts'], 0);
                    $pre_loan_amount = number_format($row_data['pre_loan_amount'], 2);
                    $month_date = date('d-m-Y', strtotime($row_data['Month_year']));
                    $count = number_format($row_data['counts'], 0);
                    $loan_amount = number_format($row_data['loan_amount'], 2);
                    $collection = number_format($row_data['collection_amount'], 2);
                    $admin_fee = number_format($row_data['admin_fee'], 2);
                    $total = (($row_data['admin_fee'] + $row_data['collection_amount']) - $row_data['loan_amount']);

                    $data .= '<tr>';
                    $data .= '<td>' . $pre_month_date . '</td>';
                    $data .= '<td>' . $pre_count . '</td>';
                    $data .= '<td>' . $pre_loan_amount . '</td>';
                    $data .= '<td>' . $month_date . '</td>';
                    $data .= '<td>' . $count . '</td>';
                    $data .= '<td>' . $loan_amount . '</td>';
                    $data .= '<td>' . $collection . '</td>';
                    //                        $data .= '<td>0</td>';
                    $data .= '<td>' . $admin_fee . '</td>';
                    $data .= '<td style = "' . ($total < 0 ? "color: #f10000; font-weight: bold" : "color: #076a0a; font-weight: bold") . '" >' . number_format($total, 2) . '</td>';
                    $data .= '</tr>';
                }



                $data .= '<tr><td class = "footer-tabels-text">Total</td>';
                $data .= '<td class = "footer-tabels-text">' . $total_pre_count . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_pre_loan_amount, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">&nbsp;</td>';
                $data .= '<td class = "footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_amount, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_collection, 2) . '</td>';
                //                    $data .= '<td class = "footer-tabels-text">0</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_admin_fee, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format((($total_admin_fee + $total_collection) - $total_loan_amount), 2) . '</td></tr>';
                $data .= '</tbody></table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }
    public function CollectionMonthwiseReport($fromDate, $toDate)
    {    
        if (!empty($fromDate)) {
        $fromDate = date('Y-d-m', strtotime($fromDate));   
        $q = "SELECT LD.lead_id, LD.lead_status_id, CAM.disbursal_date, L.loan_principle_outstanding_amount, CO.received_amount, CO.date_of_recived  FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN collection CO ON(CO.lead_id = LD.lead_id)  ";
        $q .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id  AND LD.lead_status_id IN(14,16,17,18,19) AND CO.collection_active=1 AND CO.collection_deleted=0 AND CO.payment_verification=1 AND CO.date_of_recived <= $fromDate "; // AND LD.lead_data_source_id NOT IN(21, 27)
    
        $result = $this->db->query($q)->result_array();
    
      
            // return $return;
            echo '<pre>';
            print_r($result);
            die;
            // return $result;

                $data = '<table class="bordered" style="width:40%"><thead><tr><th colspan="7" class="footer-tabels-text">MONTH WISE SEGREGATION OF PAYMENT RECEIVED <br> ON ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . '  Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<th width="24%" class="no-of-case">MONTH</th>';
                $data .= '<th width="39%"  class="no-of-case">OUTSTANDING AS ON 04.05.2023</th>';
                $data .= '<th width="25%" class="no-of-case">AMOUNT RECEIEVED</th></tr><tr>';
                $data .= '</th></tr></thead>';
                $data .= '<tr><td>FEB</td> <td>---</td> <td>---</td></tr>';
                $data .= '<tr><td>MAR</td> <td>---</td> <td class="text-right">1,45,987</td></tr>';
                $data .= '<tr><td>APR</td> <td class="text-right">3,08,609</td> <td class="text-right">6,45,987</td></tr>';
                $data .= '<tr><td>MAY 06</td> <td class="text-right">3,08,609</td> <td>6,45,987</td></tr>';
                $data .= '<tr><td>JUN </td> <td class="text-right">7,08,609</td> <td class="text-right">6,45,987</td></tr>';
                $data .= '<tr><td>JOL</td> <td>---</td> <td class="text-right">1,45,987</td></tr>';
                $data .= '<tr><td>JOL</td> <td class="text-right">3,08,609</td> <td class="text-right">1,45,987</td></tr>';
                $data .= '<tr><td>JOL</td> <td class="text-right">3,08,609</td> <td class="text-right">---</td></tr>';
                $data .= '<tr><th colspan="7" class="footer-tabels-text"></th>';
                $data .= '<tr><td colspan="2" style="text-align:left;font-weight:bold;">Advance Payment Recd. for Due <br> Date 04.05.2023 </td> <td>8,45,987</td></tr>';
                $data .= '<tr><td colspan="2" style="text-align:left;font-weight:bold;">Total Payment Recd. (A + B)</td> <td>12,45,987</td></tr>';
                $data .= '</tbody></table>';

        return $data;

    }
}

    public function TAT_model($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('LD.lead_id, LD.first_name, LD.loan_no, LD.mobile, LD.status, LD.source, LD.created_on, U5.name rejected_by, LD.lead_rejected_datetime, U.name screener_name, LD.lead_screener_assign_datetime, U1.name credit_name, LD.lead_credit_assign_datetime, LD.lead_credit_approve_datetime, U2.name credit_head, LD.lead_credithead_assign_datetime, U8.name disburse_assign_name, LD.lead_disbursal_assign_datetime, U7.name disburse_approve_name, LD.lead_disbursal_approve_datetime, LD.lead_final_disbursed_date, RR.reason, CAM.loan_recommended loan_amount, CAM.disbursal_date, CAM.repayment_date, CAM.repayment_amount, MS.m_state_name, MC.m_city_name, LD.user_type, LD.pancard')
                ->from('leads LD')
                ->join('users U', 'LD.lead_screener_assign_user_id =U.user_id', 'LEFT')
                ->join('users U1', 'LD.lead_credit_assign_user_id=U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credithead_assign_user_id=U2.user_id', 'LEFT')
                ->join('users U5', 'LD.lead_rejected_user_id=U5.user_id', 'LEFT')
                ->join('users U7', 'LD.lead_disbursal_approve_user_id=U7.user_id', 'LEFT')
                ->join('users U8', 'LD.lead_disbursal_assign_user_id=U8.user_id', 'LEFT')
                ->join('tbl_rejection_master RR', 'LD.lead_rejected_reason_id=RR.id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'LEFT')
                ->join('master_state MS', 'LD.state_id=MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id=MC.m_city_id', 'LEFT')
                ->where("LD.lead_active=1 AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportScreenerTAT($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('LD.lead_id, U.name screener, LD.lead_screener_assign_datetime')
                ->from('leads LD')
                ->join('users U', 'LD.lead_screener_assign_user_id=U.user_id', 'INNER')
                ->where("LD.lead_active=1 AND DATE(LD.lead_screener_assign_datetime)>= '$fromDate' AND DATE(LD.lead_screener_assign_datetime)<= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportCreditTAT($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('LD.lead_id, U.name credit, LD.lead_credit_assign_datetime')
                ->from('leads LD')
                ->join('users U', 'LD.lead_credit_assign_user_id=U.user_id', 'INNER')
                ->where("LD.lead_active=1 AND DATE(LD.lead_credit_assign_datetime)>= '$fromDate' AND DATE(LD.lead_credit_assign_datetime)<= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    ///////// Lead Source Report   ////////////////////

    public function LeadSourceReport($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $master_status = $this->db->select("MS.status_id, MS.status_name")
                ->from('master_status MS')
                ->where("MS.status_active = 1")
                ->order_by('MS.status_order ASC')
                ->get()->result_array();

            $query_result = $this->db->select("LD.lead_status_id , count(*) as lead_count")
                ->from('leads LD')
                ->join('master_status', 'LD.lead_status_id = master_status.status_id', 'LEFT')
                ->where("LD.lead_active = 1 AND LD.lead_entry_date!='' AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate'")
                ->group_by('LD.lead_status_id')
                ->get()->result_array();

            if ($master_status && $query_result) {
                $master_status_array = array();
                $i = 0;
                $total_status_leads = 0;
                foreach ($master_status as $row) {

                    $master_status_array[$i]['status_id'] = $row['status_id'];
                    $master_status_array[$i]['status_name'] = $row['status_name'];
                    $master_status_array[$i]['status_lead_count'] = 0;

                    if (!empty($query_result)) {

                        foreach ($query_result as $result_data) {

                            if ($result_data['lead_status_id'] == $row['status_id']) {
                                $master_status_array[$i]['status_lead_count'] = $result_data['lead_count'];
                            }
                        }
                    }

                    $total_status_leads = $total_status_leads + $master_status_array[$i]['status_lead_count'];
                    $i++;
                }
                $master_status_array[$i]['status_id'] = 99999;
                $master_status_array[$i]['status_name'] = "Total Leads";
                $master_status_array[$i]['status_lead_count'] = $total_status_leads;

                $data = '<div class="table-responsive"><table class="table table-bordered table-hover" style="margin-top: 10px;margin-left: 30px" id="customers"><thead><tr>';
                $data .= '<th><strong>' . 'Lead Status' . '</strong></th>';
                $data .= '<th><strong>' . '#Count' . '</strong></th>';
                $data .= '</tr></thead><tbody>';
                foreach ($master_status_array as $result_data) {
                    $data .= '<tr><td>' . $result_data['status_name'] . '</td>';
                    $data .= '<td>' . $result_data['status_lead_count'] . '</td></tr>';
                }
                $data .= '</tbody></table></div>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function TotalSanctionModel($fromDate, $toDate)
    {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $master_status = $this->db->select("U.name AS `Name`, SUM(IF(LD.user_type='NEW',1,0)) AS `NEW`, SUM(IF(LD.user_type='NEW',CAM.loan_recommended,0)) AS `NEW_AMOUNT`, SUM(IF(LD.user_type='REPEAT',1,0)) AS `REPEAT`, SUM(IF(LD.user_type='REPEAT',CAM.loan_recommended,0)) AS `REPEAT_AMOUNT`")
                ->from('leads LD')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'INNER')
                ->join('users U', 'LD.lead_credit_assign_user_id=U.user_id', 'INNER')
                ->where("LD.lead_active=1 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate'")
                ->group_by('U.user_id')
                ->order_by('Name')
                ->get()->result_array();

            //                print_r($this->db->last_query());
            //                exit;

            if (!empty($master_status)) {
                $master_status_array = array();
                $i = 0;
                $total_New = 0;
                $total_amount_New = 0;
                $total_Repeat = 0;
                $total_amount_Repeat = 0;
                $total_SUM = 0;
                $total_amount_SUM = 0;
                foreach ($master_status as $row) {
                    $master_status_array[$i]['Name'] = $row['Name'];
                    $master_status_array[$i]['NEW'] = $row['NEW'];
                    $master_status_array[$i]['NEW_AMOUNT'] = $row['NEW_AMOUNT'];
                    $master_status_array[$i]['REPEAT'] = $row['REPEAT'];
                    $master_status_array[$i]['REPEAT_AMOUNT'] = $row['REPEAT_AMOUNT'];
                    $master_status_array[$i]['TOTAL'] = $row['NEW'] + $row['REPEAT'];
                    $master_status_array[$i]['TOTAL_AMOUNT'] = $row['NEW_AMOUNT'] + $row['REPEAT_AMOUNT'];

                    $total_New = $total_New + $row['NEW'];
                    $total_amount_New = $total_amount_New + $row['NEW_AMOUNT'];
                    $total_Repeat = $total_Repeat + $row['REPEAT'];
                    $total_amount_Repeat = $total_amount_Repeat + $row['REPEAT_AMOUNT'];
                    $total_SUM = $total_SUM + $row['REPEAT'] + $row['NEW'];
                    $total_amount_SUM = $total_amount_SUM + $row['REPEAT_AMOUNT'] + $row['NEW_AMOUNT'];
                    $i++;
                }


                $data = '<table class="bordered"><thead><tr><th colspan="7" class="footer-tabels-text">Sanction Case Type Report ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . '  Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr><th width="20%" rowspan="2" align="center" class="no-of-case">Sanction Executive</th>';
                $data .= '<th width="24%" colspan="2" class="no-of-case">New</th>';
                $data .= '<th width="25%" colspan="2" class="no-of-case">Repeat</th>';
                $data .= '<th width="39%" style="width: 137px" colspan="2" class="no-of-case">Total</th></tr><tr>';
                $data .= '<th width="12%" class="no-of-case"><span style="color:#0363a3;">Cases</span></th>';
                $data .= '<th width="17%" class="no-of-case"><span style="color:#0363a3;">Amount</span></th>';
                $data .= '<th width="15%" class="no-of-case"><span style="color:#0363a3;">Cases</span></th>';
                $data .= '<th width="14%" class="no-of-case"><span style="color:#0363a3;">Amount</span></th>';
                $data .= '<th width="15%" class="no-of-case"><span style="color:#0363a3;">Total Cases</span></th>';
                $data .= '<th width="100%" style="width:200px" class="no-of-case"><span style="color:#0363a3;">Total Amount</span>';
                $data .= '</th></tr></thead>';

                foreach ($master_status_array as $result_data) {
                    $data .= '<tr><td>' . $result_data['Name'] . '</td>';
                    $data .= '<td>' . $result_data['NEW'] . '</td>';
                    $data .= '<td>' . number_format($result_data['NEW_AMOUNT'], 2) . '</td>';
                    $data .= '<td>' . $result_data['REPEAT'] . '</td>';
                    $data .= '<td>' . number_format($result_data['REPEAT_AMOUNT'], 2) . '</td>';
                    $data .= '<td>' . $result_data['TOTAL'] . '</td>';
                    $data .= '<td>' . number_format($result_data['TOTAL_AMOUNT'], 2) . '</td></tr>';
                }

                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total_New . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_amount_New, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_Repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_amount_Repeat, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_SUM . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_amount_SUM, 2) . '</td>';
                $data .= '</tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function BranchWiseReport($fromDate, $toDate)
    {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $master_status = $this->db->select("MB.m_branch_name AS `Branch`, SUM(IF(LD.user_type='NEW',1,0)) AS `NEW`, SUM(IF(LD.user_type='REPEAT',1,0)) AS `REPEAT`")
                ->from('leads LD')
                ->join('master_city MC', 'LD.city_id =MC.m_city_id', 'INNER')
                ->join('master_branch MB', 'MC.m_branch_id =MB.m_branch_id', 'INNER')
                ->where("LD.lead_active=1 AND LD.lead_data_source_id != 21 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate'")
                ->group_by('Branch')
                ->get()->result_array();

            if (!empty($master_status)) {
                $master_status_array = array();
                $i = 0;
                $total_New = 0;
                $total_Repeat = 0;
                $total_SUM = 0;
                foreach ($master_status as $row) {
                    // $master_status_array[$i]['status_id'] = $row['status_id'];
                    $master_status_array[$i]['Name'] = $row['Branch'];
                    $master_status_array[$i]['NEW'] = $row['NEW'];
                    $master_status_array[$i]['REPEAT'] = $row['REPEAT'];
                    $master_status_array[$i]['TOTAL'] = $row['NEW'] + $row['REPEAT'];

                    $total_New = $total_New + $row['NEW'];
                    $total_Repeat = $total_Repeat + $row['REPEAT'];
                    $total_SUM = $total_SUM + $row['REPEAT'] + $row['NEW'];
                    $i++;
                }
                $master_status_array[$i]['Name'] = 'TOTAL';
                $master_status_array[$i]['NEW'] = $total_New;
                $master_status_array[$i]['REPEAT'] = $total_Repeat;
                $master_status_array[$i]['TOTAL'] = $total_SUM;

                $data = '<div class="table-responsive"><table class="table table-bordered table-hover" style="margin-top: 10px;margin-left: 30px" id="customers"><thead><tr>';
                $data .= '<th><strong>' . 'Sanction Executive' . '</strong></th>';
                $data .= '<th><strong>' . 'NEW' . '</strong></th>';
                $data .= '<th><strong>' . 'REPEAT' . '</strong></th>';
                $data .= '<th><strong>' . 'TOTAL' . '</strong></th>';
                $data .= '</tr></thead><tbody>';
                foreach ($master_status_array as $result_data) {
                    $data .= '<tr><td>' . $result_data['Name'] . '</td>';
                    $data .= '<td>' . $result_data['NEW'] . '</td>';
                    $data .= '<td>' . $result_data['REPEAT'] . '</td>';
                    $data .= '<td>' . $result_data['TOTAL'] . '</td></tr>';
                }
                $data .= '</tbody></table></div>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function SanctionKPIModel($report_id, $month_data)
    {

        if (!empty($report_id) && !empty($month_data)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));

            $collection_to_date = date('Y-m-d', strtotime($toDate));
            if (strtotime($collection_to_date) >= strtotime(date('Y-m-d'))) {
                $collection_to_date = date("Y-m-d");
            }

            $sql = "SELECT LD.lead_id, LD.user_type, LD.lead_credit_assign_user_id, U.name, CAM.loan_recommended, CAM.disbursal_date FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";
            $sql .= "WHERE LD.lead_id=CAM.lead_id AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_credit_assign_user_id=U.user_id AND LD.lead_status_id IN(14,16,17,18,19) order by name asc;";

            $sql1 = $this->db->query($sql);
            $sanction_result = $sql1->result_array();

            $sql2 = "SELECT LD.lead_id, LD.user_type, LD.lead_credit_assign_user_id, U.name, CAM.loan_recommended, CAM.repayment_date, L.loan_principle_received_amount FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) INNER JOIN loan L ON(L.lead_id=LD.lead_id) ";
            $sql2 .= "WHERE L.lead_id=LD.lead_id AND L.loan_status_id=14 AND LD.lead_id=CAM.lead_id AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$collection_to_date' AND LD.lead_credit_assign_user_id=U.user_id AND LD.lead_status_id IN(14,16,17,18,19);";

            $sql3 = $this->db->query($sql2);
            $collection_result = $sql3->result_array();

            // echo "<pre>";
            // print_r($sanction_result);
            // exit;

            if (!empty($sanction_result) || !empty($collection_result)) {

                $sanction_report = array();
                $total = 0;
                $total_new = 0;
                $total_repeat = 0;
                $total_disburse_loan_new = 0;
                $total_disburse_loan_repeat = 0;
                $total_collection_loan = 0;
                $loan_principle_received_amount = 0;

                foreach ($sanction_result as $row) {
                    $sanction_report[$row['lead_credit_assign_user_id']]['name'] = $row['name'];
                }

                foreach ($collection_result as $row) {
                    if (isset($sanction_report[$row['lead_credit_assign_user_id']])) {
                        continue;
                    } else {
                        $sanction_report[$row['lead_credit_assign_user_id']]['name'] = $row['name'];
                    }
                }

                foreach ($sanction_result as $row) {
                    if (isset($sanction_report[$row['lead_credit_assign_user_id']])) {
                        $total += 1;
                        if ($row['user_type'] == 'NEW') {
                            $total_disburse_loan_new += $row['loan_recommended'];
                        } else {
                            $total_disburse_loan_repeat += $row['loan_recommended'];
                        }
                        if ($row['user_type'] == 'NEW') {
                            $total_new += 1;
                        } elseif ($row['user_type'] == 'REPEAT') {
                            $total_repeat += 1;
                        }
                        $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['user_type'] += 1;
                        $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];

                        if (date('d', strtotime($row['disbursal_date'])) > 0 && date('d', strtotime($row['disbursal_date'])) <= 7) {
                            if ($row['user_type'] == "NEW") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['sanction_new'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            } elseif ($row['user_type'] == "REPEAT") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['sanction_repeat'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_1'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            }
                        } elseif (date('d', strtotime($row['disbursal_date'])) > 7 && date('d', strtotime($row['disbursal_date'])) <= 14) {
                            if ($row['user_type'] == "NEW") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['sanction_new'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            } elseif ($row['user_type'] == "REPEAT") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['sanction_repeat'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_2'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            }
                        } elseif (date('d', strtotime($row['disbursal_date'])) > 14 && date('d', strtotime($row['disbursal_date'])) <= 21) {
                            if ($row['user_type'] == "NEW") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['sanction_new'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            } elseif ($row['user_type'] == "REPEAT") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['sanction_repeat'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_3'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            }
                        } elseif (date('d', strtotime($row['disbursal_date'])) > 21 && date('d', strtotime($row['disbursal_date'])) <= 28) {
                            if ($row['user_type'] == "NEW") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['sanction_new'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            } elseif ($row['user_type'] == "REPEAT") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['sanction_repeat'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_4'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            }
                        } elseif (date('d', strtotime($row['disbursal_date'])) > 28 && date('d', strtotime($row['disbursal_date'])) <= 31) {
                            if ($row['user_type'] == "NEW") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['sanction_new'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            } elseif ($row['user_type'] == "REPEAT") {
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['name'] = $row['name'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['sanction_repeat'] += 1;
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                                $sanction_report[$row['lead_credit_assign_user_id']]['Week_5'][$row['user_type']]['disbursal_date'] = $row['disbursal_date'];
                            }
                        }
                    }
                }

                foreach ($collection_result as $row) {
                    if (isset($sanction_report[$row['lead_credit_assign_user_id']])) {
                        $total_collection_loan += $row['loan_recommended'];
                        $loan_principle_received_amount += $row['loan_principle_received_amount'];
                        $sanction_report[$row['lead_credit_assign_user_id']]['collection_loan_recommended'] += $row['loan_recommended'];
                        $sanction_report[$row['lead_credit_assign_user_id']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];

                        if ($row['user_type'] == "NEW") {
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['name'] = $row['name'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['collection_new'] += 1;
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['collection_loan_recommended'] += $row['loan_recommended'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['repay_date'] = $row['repayment_date'];
                        } elseif ($row['user_type'] == "REPEAT") {
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['user_id'] = $row['lead_credit_assign_user_id'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['name'] = $row['name'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['collection_repeat'] += 1;
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['collection_loan_recommended'] += $row['loan_recommended'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                            $sanction_report[$row['lead_credit_assign_user_id']][$row['user_type']]['repay_date'] = $row['repayment_date'];
                        }
                    }
                }



                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="20" class="footer-tabels-text" style="text-align:center;">Sanction KPI Report  ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . '  Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr></thead>';
                $data .= '<tr class="sec-header">';
                $data .= '<th rowspan="2"  class="no-of-case" style="text-align:center !important; width:12%;">Executive Name</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Week 1 (1-7)</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Week 2 (8-14)</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Week 3 (15-21)</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Week 4 (22-28)</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Week 5 (29-31)</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Total</th>';
                $data .= '<th rowspan="2"  class="no-of-case" style="text-align:center !important; width:100px;">Grand Total</th>';
                $data .= '<th colspan="3" class="no-of-case" style="text-align:center !important; width:100px;">Loan Amount</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:100px;"><strong>' . date('M-y', strtotime($fromDate)) . ' Collection%</strong></th>';
                $data .= '</tr><tr class="thr-header">';

                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Total Amount</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Fresh </strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Repeat</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important; width:100px;"><strong>Over All %</strong></td></tr>';
                $data .= '<tbody>';

                $w1_new = 0;
                $w1_repeat = 0;
                $w2_new = 0;
                $w2_repeat = 0;
                $w3_new = 0;
                $w3_repeat = 0;
                $w4_new = 0;
                $w4_repeat = 0;
                $w5_new = 0;
                $w5_repeat = 0;

                foreach ($sanction_report as $row) {
                    $w1_new += (empty($row['Week_1']['NEW']['sanction_new']) ? "-" : $row['Week_1']['NEW']['sanction_new']);
                    $w1_repeat += (empty($row['Week_1']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_1']['REPEAT']['sanction_repeat']);
                    $w2_new += (empty($row['Week_2']['NEW']['sanction_new']) ? "-" : $row['Week_2']['NEW']['sanction_new']);
                    $w2_repeat += (empty($row['Week_2']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_2']['REPEAT']['sanction_repeat']);
                    $w3_new += (empty($row['Week_3']['NEW']['sanction_new']) ? "-" : $row['Week_3']['NEW']['sanction_new']);
                    $w3_repeat += (empty($row['Week_3']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_3']['REPEAT']['sanction_repeat']);
                    $w4_new += (empty($row['Week_4']['NEW']['sanction_new']) ? "-" : $row['Week_4']['NEW']['sanction_new']);
                    $w4_repeat += (empty($row['Week_4']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_4']['REPEAT']['sanction_repeat']);
                    $w5_new += (empty($row['Week_5']['NEW']['sanction_new']) ? "-" : $row['Week_5']['NEW']['sanction_new']);
                    $w5_repeat += (empty($row['Week_5']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_5']['REPEAT']['sanction_repeat']);

                    $collection_new_percentage = (($row['NEW']['loan_principle_received_amount']) / ($row['NEW']['collection_loan_recommended'])) > 0 ? number_format((($row['NEW']['loan_principle_received_amount']) / ($row['NEW']['collection_loan_recommended'])) * 100, 4) . '%' : "-";
                    $collection_repeat_percentage = (($row['REPEAT']['loan_principle_received_amount']) / ($row['REPEAT']['collection_loan_recommended'])) > 0 ? number_format((($row['REPEAT']['loan_principle_received_amount']) / ($row['REPEAT']['collection_loan_recommended'])) * 100, 4) . '%' : "-";
                    $percentage_total = (($row['loan_principle_received_amount'] / $row['collection_loan_recommended']) * 100) > 0 ? number_format(($row['loan_principle_received_amount'] / $row['collection_loan_recommended']) * 100, 4) . "%" : "-";

                    $data .= '<tr><td>' . $row['name'] . '</td>';
                    $data .= '<td>' . (empty($row['Week_1']['NEW']['sanction_new']) ? "-" : $row['Week_1']['NEW']['sanction_new']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_1']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_1']['REPEAT']['sanction_repeat']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_2']['NEW']['sanction_new']) ? "-" : $row['Week_2']['NEW']['sanction_new']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_2']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_2']['REPEAT']['sanction_repeat']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_3']['NEW']['sanction_new']) ? "-" : $row['Week_3']['NEW']['sanction_new']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_3']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_3']['REPEAT']['sanction_repeat']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_4']['NEW']['sanction_new']) ? "-" : $row['Week_4']['NEW']['sanction_new']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_4']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_4']['REPEAT']['sanction_repeat']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_5']['NEW']['sanction_new']) ? "-" : $row['Week_5']['NEW']['sanction_new']) . '</td>';
                    $data .= '<td>' . (empty($row['Week_5']['REPEAT']['sanction_repeat']) ? "-" : $row['Week_5']['REPEAT']['sanction_repeat']) . '</td>';
                    $data .= '<td>' . (empty($row['NEW']['user_type']) ? "-" : $row['NEW']['user_type']) . '</td>';
                    $data .= '<td>' . (empty($row['REPEAT']['user_type']) ? "-" : $row['REPEAT']['user_type']) . '</td>';
                    $data .= '<td>' . (empty(($row['NEW']['user_type'] + $row['REPEAT']['user_type'])) ? "-" : ($row['NEW']['user_type'] + $row['REPEAT']['user_type'])) . '</td>';
                    $data .= '<td>' . (empty($row['NEW']['loan_recommended']) ? "-" : number_format($row['NEW']['loan_recommended'], 0)) . '</td>';
                    $data .= '<td>' . (empty($row['REPEAT']['loan_recommended']) ? "-" : number_format($row['REPEAT']['loan_recommended'], 0)) . '</td>';
                    $data .= '<td>' . (empty($row['REPEAT']['loan_recommended'] + $row['NEW']['loan_recommended']) ? "-" : number_format($row['REPEAT']['loan_recommended'] + $row['NEW']['loan_recommended'], 0)) . '</td>';
                    $data .= '<td>' . $collection_new_percentage . '</td>';
                    $data .= '<td>' . $collection_repeat_percentage . '</td>';
                    $data .= '<td>' . $percentage_total . '</td></tr>';
                }

                $data .= '</tbody>';
                $data .= '<tr><td class="footer-tabels-text">Total </td>';
                $data .= '<td class="footer-tabels-text">' . $w1_new . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w1_repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w2_new . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w2_repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w3_new . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w3_repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w4_new . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w4_repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w5_new . '</td>';
                $data .= '<td class="footer-tabels-text">' . $w5_repeat . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_new) ? "-" : $total_new) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_repeat) ? "-" : $total_repeat) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total) ? "-" : $total) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_disburse_loan_new) ? "-" : number_format($total_disburse_loan_new, 0)) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_disburse_loan_repeat) ? "-" : number_format($total_disburse_loan_repeat, 0)) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_disburse_loan_repeat + $total_disburse_loan_new) ? "-" : number_format($total_disburse_loan_repeat + $total_disburse_loan_new, 0)) . '</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '</tr></table></div>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function TotalCollectionPercentageModel($fromDate, $toDate)
    {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $master_status = $this->db->select("U.name AS `Name`, SUM(IF(LD.user_type='NEW',1,0)) AS `NEW`, SUM(IF(LD.user_type='NEW',CAM.loan_recommended,0)) AS `NEW_AMOUNT`, SUM(IF(LD.user_type='REPEAT',1,0)) AS `REPEAT`, SUM(IF(LD.user_type='REPEAT',CAM.loan_recommended,0)) AS `REPEAT_AMOUNT`")
                ->from('leads LD')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'INNER')
                ->join('users U', 'LD.lead_credit_assign_user_id=U.user_id', 'INNER')
                ->where("LD.lead_active=1 AND LD.lead_data_source_id != 21 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate'")
                ->group_by('U.user_id')
                ->order_by('Name')
                ->get()->result_array();

            //                print_r($this->db->last_query());
            //                exit;

            if (!empty($master_status)) {
                $master_status_array = array();
                $i = 0;
                $total_New = 0;
                $total_amount_New = 0;
                $total_Repeat = 0;
                $total_amount_Repeat = 0;
                $total_SUM = 0;
                $total_amount_SUM = 0;
                foreach ($master_status as $row) {
                    $master_status_array[$i]['Name'] = $row['Name'];
                    $master_status_array[$i]['NEW'] = $row['NEW'];
                    $master_status_array[$i]['NEW_AMOUNT'] = $row['NEW_AMOUNT'];
                    $master_status_array[$i]['REPEAT'] = $row['REPEAT'];
                    $master_status_array[$i]['REPEAT_AMOUNT'] = $row['REPEAT_AMOUNT'];
                    $master_status_array[$i]['TOTAL'] = $row['NEW'] + $row['REPEAT'];
                    $master_status_array[$i]['TOTAL_AMOUNT'] = $row['NEW_AMOUNT'] + $row['REPEAT_AMOUNT'];

                    $total_New = $total_New + $row['NEW'];
                    $total_amount_New = $total_amount_New + $row['NEW_AMOUNT'];
                    $total_Repeat = $total_Repeat + $row['REPEAT'];
                    $total_amount_Repeat = $total_amount_Repeat + $row['REPEAT_AMOUNT'];
                    $total_SUM = $total_SUM + $row['REPEAT'] + $row['NEW'];
                    $total_amount_SUM = $total_amount_SUM + $row['REPEAT_AMOUNT'] + $row['NEW_AMOUNT'];
                    $i++;
                }

                $data = '<div class="table-responsive"><table class="table table-bordered table-hover" style="margin-top: 10px;margin-left: 30px" id="customers"><thead><tr>';
                $data .= '<th rowspan="2"><strong>' . 'Sanction Executive' . '</strong></th>';
                $data .= '<th colspan="2"><strong>' . 'NEW' . '</strong></th>';
                $data .= '<th colspan="2"><strong>' . 'REPEAT' . '</strong></th>';
                $data .= '<th colspan="2"><strong>' . 'TOTAL' . '</strong></th>';
                $data .= '</tr>';
                $data .= '<tr>';
                $data .= '<td><strong>Cases</strong></td>';
                $data .= '<td><strong>Amount</strong></td>';
                $data .= '<td><strong>Cases</td></strong>';
                $data .= '<td><strong>Amount</strong></td>';
                $data .= '<td><strong>Total Cases</strong></td>';
                $data .= '<td><strong>Total Amount</strong></td></tr>';
                $data .= '</thead><tbody>';
                foreach ($master_status_array as $result_data) {
                    $data .= '<tr><td>' . $result_data['Name'] . '</td>';
                    $data .= '<td>' . $result_data['NEW'] . '</td>';
                    $data .= '<td>' . $result_data['NEW_AMOUNT'] . '</td>';
                    $data .= '<td>' . $result_data['REPEAT'] . '</td>';
                    $data .= '<td>' . $result_data['REPEAT_AMOUNT'] . '</td>';
                    $data .= '<td>' . $result_data['TOTAL'] . '</td>';
                    $data .= '<td>' . $result_data['TOTAL_AMOUNT'] . '</td></tr>';
                }

                $data .= '<tfoot><td><strong>Total</strong></td>';
                $data .= '<td><strong>' . $total_New . '</strong></td>';
                $data .= '<td><strong>' . $total_amount_New . '</strong></td>';
                $data .= '<td><strong>' . $total_Repeat . '</strong></td>';
                $data .= '<td><strong>' . $total_amount_Repeat . '</strong></td>';
                $data .= '<td><strong>' . $total_SUM . '</strong></td>';
                $data .= '<td><strong>' . $total_amount_SUM . '</strong></td></tfoot>';

                $data .= '</tbody></table></div>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function MonthwisePendingCollectionModel($report_id)
    {
        if (!empty($report_id) && $report_id == 7) {
            $to_date = date('Y-m-d');
            $pre_to_date = date('Y-m-d', strtotime('-1 day', strtotime($to_date)));

            $q = "SELECT DATE_FORMAT(CAM.repayment_date, '%M-%y') as month_year, CAM.repayment_date as repay_date, CAM.loan_recommended, (SELECT SUM(C.received_amount) FROM collection C WHERE C.lead_id=LD.lead_id AND C.collection_active=1 AND C.payment_verification=1) as collection, (SELECT SUM(C.received_amount) FROM collection C WHERE C.lead_id=LD.lead_id AND C.collection_active=1 AND C.payment_verification=1 AND C.date_of_recived < '$to_date') as pre_collection ";
            $q .= "FROM credit_analysis_memo CAM INNER JOIN loan L ON(CAM.lead_id=L.lead_id) INNER JOIN leads LD ON(CAM.lead_id=LD.lead_id) ";
            $q .= "WHERE CAM.lead_id=LD.lead_id AND CAM.lead_id=L.lead_id AND LD.lead_status_id IN(14, 19) AND LD.lead_data_source_id NOT IN(21, 27) AND L.loan_status_id=14 AND CAM.cam_active=1 AND L.loan_active=1 AND CAM.repayment_date < '$to_date' ORDER BY `repay_date` DESC";

            $result = $this->db->query($q)->result_array();

            $q1 = "SELECT DATE_FORMAT(CAM.repayment_date, '%M-%y') as month_year, SUM(CAM.loan_recommended) as loan_recommended ";
            $q1 .= "FROM credit_analysis_memo CAM INNER JOIN loan L ON(CAM.lead_id=L.lead_id) INNER JOIN leads LD ON(CAM.lead_id=LD.lead_id) ";
            $q1 .= "WHERE CAM.lead_id=LD.lead_id AND CAM.lead_id=L.lead_id AND LD.lead_data_source_id NOT IN(21, 27) AND L.loan_status_id=14 AND CAM.cam_active=1 AND L.loan_active=1 AND CAM.repayment_date < '$to_date' GROUP BY month_year";

            $total_repay_cases = $this->db->query($q1)->result_array();

            if ($result) {

                $report_array = array();
                $outstanding_array = array();
                $date_array = array();

                $total_count = 0;
                $total_loan_amount = 0;
                $total_collection = 0;
                $total_pre_collection = 0;
                $total_outstanding = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $date_array[$row['month_year']]['month_year'] = $row['month_year'];
                        $report_array[$row['month_year']]['counts'] += 0;
                        $report_array[$row['month_year']]['loan_recommended'] = 0;
                        $report_array[$row['month_year']]['collection'] = 0;
                        $report_array[$row['month_year']]['pre_collection'] = 0;
                        $report_array[$row['month_year']]['outstanding'] = 0;
                    }
                }

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $total_loan_amount += $row['loan_recommended'];
                        $total_collection += $row['collection'];
                        $total_pre_collection += $row['pre_collection'];

                        $outstanding = 0;
                        if (($row['loan_recommended'] - $row['collection']) > 0) {
                            $outstanding = ($row['loan_recommended'] - $row['collection']);
                        }

                        $total_outstanding = $total_outstanding + $outstanding;

                        if (isset($date_array[$row['month_year']])) {
                            $report_array[$row['month_year']]['month_year'] = $row['month_year'];
                            $report_array[$row['month_year']]['counts'] = $report_array[$row['month_year']]['counts'] + 1;
                            $report_array[$row['month_year']]['loan_recommended'] = $report_array[$row['month_year']]['loan_recommended'] + $row['loan_recommended'];
                            $report_array[$row['month_year']]['collection'] = $report_array[$row['month_year']]['collection'] + $row['collection'];
                            $report_array[$row['month_year']]['pre_collection'] = $report_array[$row['month_year']]['pre_collection'] + $row['pre_collection'];
                            $report_array[$row['month_year']]['outstanding'] = $report_array[$row['month_year']]['outstanding'] + $outstanding;
                        }
                    }
                }

                if (!empty($total_repay_cases)) {
                    foreach ($total_repay_cases as $row) {
                        $outstanding_array[$row['month_year']]['month_year'] = $row['month_year'];
                        $outstanding_array[$row['month_year']]['total_loan_amount'] = $outstanding_array[$row['month_year']]['total_loan_amount'] + $row['loan_recommended'];
                    }
                }


                $data = '<table class="bordered" style="width:100%;"><tr class="fir-header"><th colspan="8" align="center" class="footer-tabels-text">Month Wise Pending Collection Amount - ' . date('d-m-Y', strtotime($pre_to_date)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th></tr><tr class="sec-header">';
                $data .= '<th align="center" class="no-of-case" valign="middle" style="color:#0363a3; width:15%;">Month</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:12%;">No. Of Cases</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:13%;">Principal Amount</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:13%;">Part Payment</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:13%;">Net Outstanding</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:12%;">%</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:15%;">Previous Day Outstanding</th>';
                $data .= '<th align="center" class="no-of-case" style="color:#0363a3; width:10%;">Increase/Decrease</th></tr>';

                $total_difference_amount = 0;

                foreach ($report_array as $row_data) {
                    $month_date = $row_data['month_year'];
                    $collection_amount = number_format($row_data['collection'], 2);
                    $pre_received_amount = number_format($row_data['pre_collection'], 2);
                    $count = number_format($row_data['counts'], 0);
                    $loan_amount = number_format($row_data['loan_recommended'], 2);
                    $outstanding = number_format($row_data['outstanding'], 2);
                    $total_loan_amount = $outstanding_array[$month_date]['total_loan_amount'];
                    $difference_amount = $row_data['pre_collection'] - $row_data['collection'];
                    $percentage_convert = str_split((($outstanding / $total_loan_amount) * 100), 6);
                    $total_difference_amount = $total_difference_amount + $difference_amount;

                    $data .= '<tr><td>' . $month_date . '</td>';
                    $data .= '<td>' . $count . '</td>';
                    $data .= '<td>' . $loan_amount . '</td>';
                    $data .= '<td>' . $collection_amount . '</td>';
                    $data .= '<td>' . $outstanding . '</td>';
                    $data .= '<td>' . round($percentage_convert[0], 2) . '%</td>';
                    $data .= '<td>' . $pre_received_amount . '</td>';
                    $data .= '<td>' . number_format($difference_amount, 2) . '</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Total </td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_count, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_loan_amount, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_collection, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_pre_collection, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_difference_amount, 2) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadSourceStatusModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT source, MS.status_order, status, COUNT(*) as total_count FROM leads LD INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) WHERE LD.lead_active=1 AND LD.lead_status_id=MS.status_id AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' GROUP BY source, status ORDER BY MS.status_order, source ASC";

            $result = $this->db->query($q)->result_array();

            if ($result) {

                $final_array = array();
                $header_values = array();
                $total_value = array();
                $total = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total += $row['total_count'];
                        $source = $row['source'];

                        $final_array[$row['status']][$source]['counts'] = $row['total_count'];
                        $final_array[$row['status']][$source]['status'] = $row['status'];
                        $final_array[$row['status']][$source]['source'] = $source;
                        $total_value['source'][$source] += $row['total_count'];
                        $total_value['status'][$row['status']] += $row['total_count'];
                        $header_values[$source] = $source;
                        $i++;
                    }
                }

                $data = '<table class="bordered"><thead><tr><th colspan=' . $i . ' class="footer-tabels-text" style="text-align:center;">Lead Status with Source Report - ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr><th width="' . ($i / 100) . '%" class="no-of-case">Status / Source</th>';
                ksort($header_values);
                foreach ($header_values as $header_key) {
                    $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">' . $header_key . '</th>';
                }
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">Total</th></tr>';

                foreach ($final_array as $key => $value) {
                    $data .= '<tr><td align="center" valign="middle">' . $key . '</td>';
                    foreach ($header_values as $header_key) {
                        $data .= '<td align="center" valign="middle">' . ($value[$header_key]['counts'] > 0 ? number_format($value[$header_key]['counts'], 0) : "-") . '</td>';
                    }
                    $data .= '<td align="center" valign="middle">' . $total_value['status'][$key] . '</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total</td>';
                foreach ($header_values as $header_key) {
                    $data .= '<td class="footer-tabels-text">' . number_format($total_value['source'][$header_key], 0) . '</td>';
                }
                $data .= '<td class="footer-tabels-text">' . number_format($total, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function OutstandingReportCasesModel($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));

            $collection_to_date = date('Y-m-d', strtotime($toDate));
            if ($collection_to_date >= date('Y-m-d')) {
                $toDate = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
            }

            $q = "SELECT LD.lead_id, U.name as sanction_executive, LD.lead_credit_assign_user_id, LD.status, LD.lead_status_id, LD.user_type, L.loan_no, CAM.loan_recommended, L.loan_principle_received_amount, L.loan_principle_outstanding_amount FROM leads LD INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";

            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_credit_assign_user_id=U.user_id AND LD.lead_id=CAM.lead_id AND LD.lead_active=1 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY U.name ASC";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();
                $total_array = array();
                $total = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $lead_status_id = $row['lead_status_id'];
                        $sanction_by = $row['sanction_executive'];
                        $user_type = $row['user_type'];
                        $total += 1;

                        if (in_array($lead_status_id, [14, 19])) {
                            $report_array[$sanction_by]['not_closed'][$user_type]['name'] = $sanction_by;
                            $report_array[$sanction_by]['not_closed'][$user_type]['counts'] += 1;
                            $report_array[$sanction_by]['not_closed']['total_counts'] += 1;
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_recommended'] = $row['loan_recommended'];
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_principle_received_amount'] = $row['loan_principle_received_amount'];
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_principle_outstanding_amount'] = $row['loan_principle_outstanding_amount'];
                            $report_array[$sanction_by][$user_type] += 1;
                            $total_array['not_closed'][$user_type] += 1;
                        } else {
                            $report_array[$sanction_by]['closed'][$user_type]['name'] = $sanction_by;
                            $report_array[$sanction_by]['closed'][$user_type]['counts'] += 1;
                            $report_array[$sanction_by]['closed']['total_counts'] += 1;
                            $report_array[$sanction_by]['closed'][$user_type]['loan_recommended'] = $row['loan_recommended'];
                            $report_array[$sanction_by]['closed'][$user_type]['loan_principle_received_amount'] = $row['loan_principle_received_amount'];
                            $report_array[$sanction_by]['closed'][$user_type]['loan_principle_outstanding_amount'] = $row['loan_principle_outstanding_amount'];
                            $report_array[$sanction_by][$user_type] += 1;
                            $total_array['closed'][$user_type] += 1;
                        }
                        $i++;
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="13" class="footer-tabels-text">Outstanding Executive Case Wise Report ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th rowspan="2" class="no-of-case">Sanction Executive</th>';
                $data .= '<th colspan="3" class="no-of-case">CLOSED</th>';
                $data .= '<th colspan="3" class="no-of-case">NOT CLOSED</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">TOTAL FRESH</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">TOTAL REPEAT</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">GRAND TOTAL</th>';
                $data .= '<th colspan="3" class="no-of-case">DEFAULT%</th></tr>';
                $data .= '<tr class="thr-header"><th width="8%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">OVERALL%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $key => $value) {

                    $data .= '<tr>';
                    $data .= '<td>' . $key . '</td>';
                    $data .= '<td>' . ($value['closed']['NEW']['counts'] > 0 ? $value['closed']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['closed']['REPEAT']['counts'] > 0 ? $value['closed']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['closed']['total_counts'] > 0 ? $value['closed']['total_counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['NEW']['counts'] > 0 ? $value['not_closed']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['REPEAT']['counts'] > 0 ? $value['not_closed']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['total_counts'] > 0 ? $value['not_closed']['total_counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['NEW'] > 0 ? $value['NEW'] : "-") . '</td>';
                    $data .= '<td>' . ($value['REPEAT'] > 0 ? $value['REPEAT'] : "-") . '</td>';
                    $data .= '<td>' . (($value['REPEAT'] + $value['NEW']) > 0 ? ($value['REPEAT'] + $value['NEW']) : "-") . '</td>';
                    $data .= '<td>' . (($value['not_closed']['NEW']['counts'] / $value['NEW']) > 0 ? number_format(($value['not_closed']['NEW']['counts'] / $value['NEW']) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '<td>' . (($value['not_closed']['REPEAT']['counts'] / $value['REPEAT']) > 0 ? number_format(($value['not_closed']['REPEAT']['counts'] / $value['REPEAT']) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '<td style="' . (((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) * 100) > 20 ? "color: #ff0000;  font-weight: bold;" : "") . '">' . ((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) > 0 ? number_format((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['closed']['NEW'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['closed']['REPEAT'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['REPEAT'] + $total_array['closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['not_closed']['NEW'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['not_closed']['REPEAT'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['not_closed']['REPEAT'] + $total_array['not_closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['NEW'] + $total_array['not_closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['REPEAT'] + $total_array['not_closed']['REPEAT']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['NEW'] / ($total_array['closed']['NEW'] + $total_array['not_closed']['NEW'])) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['REPEAT'] / ($total_array['closed']['REPEAT'] + $total_array['not_closed']['REPEAT'])) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['REPEAT'] + $total_array['not_closed']['NEW']) / $total * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function CollectionCallwithtimeModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT TIME(LCF.lcf_created_on) as times, U.name, LCF.lcf_created_on  FROM `loan_collection_followup` LCF INNER JOIN users U ON(U.user_id=LCF.lcf_user_id) WHERE LCF.lcf_type_id=1 AND LCF.lcf_active=1 AND DATE(LCF.lcf_created_on) >= '$fromDate' AND DATE(LCF.lcf_created_on) <= '$toDate' order by times asc;";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $raw_data_array = array();
                $report_array = array();
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $raw_data_array[$row['name']][$row['times']]['counts'] += 1;
                        $raw_data_array[$row['name']][$row['times']]['name'] = $row['name'];
                        $raw_data_array[$row['name']][$row['times']]['times'] = $row['times'];
                        $raw_data_array['grand_total']['grand_total'] += 1;
                        $i++;
                    }
                }

                if (!empty($raw_data_array)) {
                    foreach (array_keys($raw_data_array) as $row) {
                        if (isset($raw_data_array[$row])) {
                            foreach ($raw_data_array[$row] as $time) {
                                $c_time = strtotime($time['times']);
                                if ($c_time > strtotime('00:00') && $c_time <= strtotime('10:00')) {

                                    $report_array[$time['name']]['12:00-10:00 AM']['counts'] += 1;
                                    $report_array[$time['name']]['12:00-10:00 AM']['name'] = $time['name'];
                                    $report_array[$time['name']]['12:00-10:00 AM']['times'] = $time['times'];
                                    $report_array[$time['name']]['12:00-10:00 AM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['12:00-10:00 AM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('10:00') && $c_time <= strtotime('12:00')) {

                                    $report_array[$time['name']]['10:00-12:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['10:00-12:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['10:00-12:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['10:00-12:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['10:00-12:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('12:00') && $c_time <= strtotime('14:00')) {

                                    $report_array[$time['name']]['12:00-02:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['12:00-02:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['12:00-02:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['12:00-02:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['12:00-02:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('14:00') && $c_time <= strtotime('16:00')) {

                                    $report_array[$time['name']]['02:00-04:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['02:00-04:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['02:00-04:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['02:00-04:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['02:00-04:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('16:00') && $c_time <= strtotime('18:00')) {

                                    $report_array[$time['name']]['04:00-06:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['04:00-06:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['04:00-06:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['04:00-06:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['04:00-06:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('18:00') && $c_time <= strtotime('20:00')) {

                                    $report_array[$time['name']]['06:00-08:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['06:00-08:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['06:00-08:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['06:00-08:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['06:00-08:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('20:00') && $c_time <= strtotime('22:00')) {

                                    $report_array[$time['name']]['08:00-10:00 PM']['counts'] += 1;
                                    $report_array[$time['name']]['08:00-10:00 PM']['name'] = $time['name'];
                                    $report_array[$time['name']]['08:00-10:00 PM']['times'] = $time['times'];
                                    $report_array[$time['name']]['08:00-10:00 PM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['08:00-10:00 PM']['column_total'] += 1;
                                } elseif ($c_time > strtotime('22:00') && $c_time <= strtotime('24:00')) {

                                    $report_array[$time['name']]['10:00-12:00 AM']['counts'] += 1;
                                    $report_array[$time['name']]['10:00-12:00 AM']['name'] = $time['name'];
                                    $report_array[$time['name']]['10:00-12:00 AM']['times'] = $time['times'];
                                    $report_array[$time['name']]['10:00-12:00 AM']['lcf_created_on'] = $time['lcf_created_on'];
                                    $report_array[$time['name']]['total'] += 1;
                                    $report_array['column']['10:00-12:00 AM']['column_total'] += 1;
                                }
                            }
                        }
                    }
                }

                //                    echo "<pre>";
                //                    print_r($report_array);
                //                    exit;

                ksort($report_array);
                $data = '<table class="bordered" style="width:100%;"><thead><tr>';
                $data .= '<th colspan="10" align="center" class="footer-tabels-text" style="text-align:center;">Collection Call With Time - ' . date('d-m-Y', strtotime($fromDate)) . ' to ' . date('d-m-Y', strtotime($toDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';
                $data .= '<tr><th class="no-of-case">FollowUp By</th>';
                $data .= '<th class="no-of-case">12:00-10:00 AM</th>';
                $data .= '<th class="no-of-case">10:00-12:00 PM</th>';
                $data .= '<th class="no-of-case">12:00-02:00 PM</th>';
                $data .= '<th class="no-of-case">02:00-04:00 PM</th>';
                $data .= '<th class="no-of-case">04:00-06:00 PM</th>';
                $data .= '<th class="no-of-case">06:00-08:00 PM</th>';
                $data .= '<th class="no-of-case">08:00-10:00 PM</th>';
                $data .= '<th class="no-of-case">10:00-12:00 AM</th>';
                $data .= '<th class="no-of-case">Total</th></tr>';

                foreach (array_keys($report_array) as $key) {
                    if ($key == 'column') {
                        continue;
                    } else {
                        $data .= '<tr><td width = "20%">' . $key . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['12:00-10:00 AM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['10:00-12:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['12:00-02:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['02:00-04:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['04:00-06:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['06:00-08:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['08:00-10:00 PM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['10:00-12:00 AM']['counts'], 0) . '</td>';
                        $data .= '<td width="10%">' . number_format($report_array[$key]['total'], 0) . '</td></tr>';
                    }
                }

                $data .= '<tr><td class = "footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['12:00-10:00 AM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['10:00-12:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['12:00-02:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['02:00-04:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['04:00-06:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['06:00-08:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['08:00-10:00 PM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($report_array['column']['10:00-12:00 AM']['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($raw_data_array['grand_total']['grand_total'], 0) . '</td></tr></table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function CollectionCallwithStatusModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT U.name, MFS.m_followup_status_name as m_followup_status_name, LCF.lcf_status_id as lcf_status_id, LCF.lcf_created_on FROM `loan_collection_followup` LCF INNER JOIN master_followup_status MFS ON(MFS.m_followup_status_id=LCF.lcf_status_id) INNER JOIN users U ON(LCF.lcf_user_id=U.user_id) ";
            $q .= "WHERE LCF.lcf_user_id=U.user_id AND MFS.m_followup_status_id=LCF.lcf_status_id AND LCF.lcf_type_id=1 AND LCF.lcf_active=1 AND DATE(LCF.lcf_created_on) >= '$fromDate' AND DATE(LCF.lcf_created_on) <= '$toDate' order by U.name asc;";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $raw_data_array = array();
                $column_data_array = array();
                $i = 0;
                $total_count = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $raw_data_array[$row['name']][$row['lcf_status_id']]['counts'] += 1;
                        $raw_data_array[$row['name']][$row['lcf_status_id']]['name'] = $row['name'];
                        $raw_data_array[$row['name']][$row['lcf_status_id']]['m_followup_status_name'] = $row['m_followup_status_name'];
                        $raw_data_array[$row['name']]['total'] += 1;
                        $i++;
                    }
                }

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $column_data_array[$row['lcf_status_id']]['column_total'] += 1;
                        $column_data_array[$row['lcf_status_id']]['m_followup_status_name'] = $row['m_followup_status_name'];
                        $i++;
                    }
                }

                //                    echo "<pre>";
                //                    print_r($total_count);
                //                    exit;

                $data = '<table class="bordered" style="width:100%;"><tr><th colspan="20" align="center" class="footer-tabels-text">Collection Call With Status - ' . date('d-m-Y', strtotime($fromDate)) . ' to ' . date('d-m-Y', strtotime($toDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th></tr>';
                $data .= '<tr><th class="no-of-case">FOLLOWUP BY</th>';
                $data .= '<th class="no-of-case">NO ANSWERED</th>';
                $data .= '<th class="no-of-case">BAD/Rude Behavior of CM</th>';
                $data .= '<th class="no-of-case">CALLBACK</th>';
                $data .= '<th class="no-of-case">CM WANTS TO DISCUSS WITH SENIOR</th>';
                $data .= '<th class="no-of-case">COMMITMENT</th>';
                $data .= '<th class="no-of-case">COMMITED BUT NOT PAID</th>';
                $data .= '<th class="no-of-case">COVID EXCUSE</th>';
                $data .= '<th class="no-of-case">DEAD CASES</th>';
                $data .= '<th class="no-of-case">INVALID NO</th>';
                $data .= '<th class="no-of-case">LOAN CLOSED</th>';
                $data .= '<th class="no-of-case">LOST JOB</th>';
                $data .= '<th class="no-of-case">PART PAYMENT DONE</th>';
                $data .= '<th class="no-of-case">QUESTION TYPE</th>';
                $data .= '<th class="no-of-case">REFUSING TO PAY</th>';
                $data .= '<th class="no-of-case">REMINDER CALL</th>';
                $data .= '<th class="no-of-case">RENEW LOAN</th>';
                $data .= '<th class="no-of-case">SALARY NOT RECEIVED</th>';
                $data .= '<th class="no-of-case">SWITCH OFF</th>';
                $data .= '<th class="no-of-case">TOTAL</th></tr>';

                foreach (array_keys($raw_data_array) as $key) {

                    $data .= '<tr><td width="39%">' . strtoupper($key) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][1]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][2]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][3]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][4]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][5]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][6]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][7]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][8]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][9]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][10]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][11]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][12]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][13]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][14]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][15]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][16]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][17]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key][18]['counts'], 0) . '</td>';
                    $data .= '<td width="7%">' . number_format($raw_data_array[$key]['total'], 0) . '</td></tr>';
                }


                $data .= '<tr><td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[1]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[2]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[3]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[4]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[5]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[6]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[7]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[8]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[9]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[10]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[11]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[12]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[13]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[14]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[15]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[16]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[17]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($column_data_array[18]['column_total'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_count . '</td></tr></table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function UserTypeOutstandingModel($report_id, $month_data)
    {
        if (!empty($month_data) && $report_id == 11) {

            $fromDate = date("Y-m-01", strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($month_data));

            if (strtotime($toDate) >= strtotime(date('d-m-Y'))) {
                $toDate = date("Y-m-d", strtotime('-1 day', strtotime(date('d-m-Y'))));
            }

            $q = "SELECT LD.lead_id, LD.user_type, CAM.loan_recommended, (SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived <='$toDate') AS total_collection FROM `leads` LD INNER JOIN user_roles UR ON(LD.lead_credit_assign_user_id=UR.user_role_user_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) ";
            $q .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_credit_assign_user_id=UR.user_role_user_id AND UR.user_role_type_id=3 AND UR.user_role_active=1 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND LD.lead_data_source_id NOT IN(21, 27) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate';";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $raw_data_array = array();
                $report_array = array();
                $i = 0;
                $total_count = 0;
                $total_loan_amount = 0;
                $total_outstanding = 0;
                $total_collection = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $outstanding = 0;
                        if ($row['total_collection'] > $row['loan_recommended']) {
                            $outstanding = 0;
                        } else {
                            $outstanding = $row['loan_recommended'] - $row['total_collection'];
                        }

                        $raw_data_array[$i][$row['user_type']]['lead_id'] = $row['lead_id'];
                        $raw_data_array[$i][$row['user_type']]['user_type'] = $row['user_type'];
                        $raw_data_array[$i][$row['user_type']]['loan_recommended'] = $row['loan_recommended'];
                        $raw_data_array[$i][$row['user_type']]['total_collection'] = $row['total_collection'];
                        $raw_data_array[$i][$row['user_type']]['outstanding'] = $outstanding;
                        $i++;
                    }
                }

                if (!empty($raw_data_array)) {
                    foreach ($raw_data_array as $row) {
                        $arr = key($row);
                        if (isset($row[$arr])) {
                            $report_array[$arr]['counts'] += 1;
                            $report_array[$arr]['user_type'] = $arr;
                            $report_array[$arr]['loan_recommended'] += $row[$arr]['loan_recommended'];
                            $report_array[$arr]['total_collection'] += $row[$arr]['total_collection'];
                            $report_array[$arr]['outstanding'] += $row[$arr]['outstanding'];
                        }
                        $i++;
                    }
                }

                //                    echo "<pre>";
                //                    print_r($report_array);
                //                    exit;

                $data = '<table class="bordered"><thead><tr>';
                $data .= '<th colspan="6" class="footer-tabels-text" style="text-align:center;">BREAKUP OF ' . date('M-y', strtotime($month_data)) . ' OUTSTANDING CASES AS ON CLOSING ' . date('d-m-Y', strtotime($toDate)) . '</th></tr></thead>';
                $data .= '<tr><th class="no-of-case">Type</th>';
                $data .= '<th class="no-of-case">	No. of Cases</th>';
                $data .= '<th class="no-of-case">Loan Amount</th>';
                $data .= '<th class="no-of-case">Part Payment</th>';
                $data .= '<th class="no-of-case">Outstanding Principal</th>';
                $data .= '<th class="no-of-case">%</th></tr>';

                foreach (array_keys($report_array) as $key) {

                    $count = $report_array[$key]['counts'];
                    $loan_amount = $report_array[$key]['loan_recommended'];
                    $outstanding = $report_array[$key]['outstanding'];
                    $total_collection = $report_array[$key]['total_collection'];
                    $percentage_convert = str_split((($outstanding / $loan_amount) * 100), 6);
                    $total_count += $count;
                    $total_loan_amount += $loan_amount;
                    $total_outstanding += $outstanding;

                    $data .= '<tr><td width = "12%">' . $report_array[$key]['user_type'] . '</td>';
                    $data .= '<td width = "16%">' . $count . '</td>';
                    $data .= '<td width = "18%">' . number_format($loan_amount, 2) . '</td>';
                    $data .= '<td width = "16%">' . number_format($total_collection, 2) . '</td>';
                    $data .= '<td width = "21%">' . number_format($outstanding, 2) . '%</td>';
                    $data .= '<td width = "21%">' . round($percentage_convert[0], 2) . '%</td></tr>';
                }


                $data .= '<tr><td class = "footer-tabels-text">&nbsp;</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_count, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_amount, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_collection, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">' . number_format($total_outstanding, 2) . '</td>';
                $data .= '<td class = "footer-tabels-text">&nbsp;</td></tr></table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    // public function PreCollectionModel($month_data)   precollection data
    // {
    //     if (!empty($month_data)) {
    //         $fromDate = date('Y-m-01', strtotime($month_data));
    //         $toDate = date("Y-m-t", strtotime($fromDate));
    //         $q = "SELECT CAM.repayment_date as Month_year, COUNT(*) as counts, SUM(CAM.loan_recommended) as loan_amount, SUM(L.loan_principle_received_amount) as loan_principle_received_amount, SUM(L.loan_interest_received_amount) as loan_interest_received_amount, SUM(L.loan_principle_outstanding_amount) as loan_principle_outstanding_amount, SUM(L.loan_interest_outstanding_amount) as loan_interest_outstanding_amount, SUM(L.loan_penalty_received_amount) as loan_penalty_received_amount, SUM(L.loan_total_received_amount) as loan_total_received_amount ";
    //         $q .= "FROM credit_analysis_memo CAM INNER JOIN leads LD ON(CAM.lead_id=LD.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
    //         $q .= "WHERE CAM.lead_id=LD.lead_id AND LD.lead_id=L.lead_id AND L.loan_status_id=14 AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' GROUP BY CAM.repayment_date ORDER BY CAM.repayment_date ASC;";
    //         $result = $this->db->query($q)->result_array();
    //         $q1 = "SELECT CAM.repayment_date as pre_Month_year, COUNT(*) as pre_counts, SUM(CAM.loan_recommended) as pre_loan_amount FROM credit_analysis_memo CAM INNER JOIN leads LD ON(CAM.lead_id=LD.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
    //         $q1 .= "WHERE CAM.lead_id=LD.lead_id AND LD.lead_id=L.lead_id AND L.loan_status_id=14 AND LD.lead_status_id IN(14, 19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' GROUP BY CAM.repayment_date ORDER BY CAM.repayment_date ASC;";
    //         $remaining_cases = $this->db->query($q1)->result_array();
    //         if (!empty($result) && !empty($remaining_cases)) {
    //             $report_array = array();
    //             $total_count = 0;
    //             $total_loan_amount = 0;
    //             $total_pre_count = 0;
    //             $total_pre_loan_amount = 0;
    //             $total_loan_principle_received_amount = 0;
    //             $total_loan_interest_received_amount = 0;
    //             $total_loan_penalty_received_amount = 0;
    //             $total_loan_total_received_amount = 0;
    //             if (!empty($remaining_cases)) {
    //                 foreach ($remaining_cases as $data) {
    //                     $total_pre_count += $data['pre_counts'];
    //                     $total_pre_loan_amount += $data['pre_loan_amount'];
    //                     $report_array[$data['pre_Month_year']]['pre_counts'] = $data['pre_counts'];
    //                     $report_array[$data['pre_Month_year']]['pre_loan_amount'] = $data['pre_loan_amount'];
    //                 }
    //             }
    //             if (!empty($result)) {
    //                 foreach ($result as $data) {
    //                     if (isset($report_array[$data['Month_year']])) {
    //                         $total_count += $data['counts'];
    //                         $total_loan_amount += $data['loan_amount'];
    //                         $total_loan_principle_received_amount += $data['loan_principle_received_amount'];
    //                         $total_loan_interest_received_amount += $data['loan_interest_received_amount'];
    //                         $total_loan_penalty_received_amount += $data['loan_penalty_received_amount'];
    //                         $total_loan_total_received_amount += $data['loan_total_received_amount'];
    //                         $report_array[$data['Month_year']]['counts'] = $data['counts'];
    //                         $report_array[$data['Month_year']]['loan_amount'] = $data['loan_amount'];
    //                         $report_array[$data['Month_year']]['loan_principle_received_amount'] = $data['loan_principle_received_amount'];
    //                         $report_array[$data['Month_year']]['loan_interest_received_amount'] = $data['loan_interest_received_amount'];
    //                         $report_array[$data['Month_year']]['loan_penalty_received_amount'] = $data['loan_penalty_received_amount'];
    //                         $report_array[$data['Month_year']]['loan_total_received_amount'] = $data['loan_total_received_amount'];
    //                         // $report_array[$data['Month_year']]['loan_principle_outstanding_amount'] = $data['loan_principle_outstanding_amount'];
    //                         // $report_array[$data['Month_year']]['loan_interest_outstanding_amount'] = $data['loan_interest_outstanding_amount'];
    //                         $report_array[$data['Month_year']]['Month_year'] = $data['Month_year'];
    //                     }
    //                 }
    //             }
    //             $fromDate = date('d-M-Y', strtotime($fromDate));
    //             $toDate = date('d-M-Y', strtotime($toDate));
    //             $data = '<table class="bordered"><tbody>';
    //             // $data .= '<th colspan="3" align="center" class="footer-tabels-text">Previous Month</th>';
    //             $data .= "<tr><th colspan='14' align='center' class='footer-tabels-text'>Pre Collection Summary Report $fromDate to $toDate </th></tr>";
    //             $data .= '<tr><th width="7%" align="center" valign="middle" class="disbu" >Date</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">Total Cases</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">Total Amount</th>';
    //             $data .= '<th width = "7%" align = "center" class = "disbu">Closed Cases</th>';
    //             // $data .= '<th width = "7%" align = "center" class = "disbu">Closed  principle</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">principle Recievecd</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">Interest Recievecd</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">Panel Recievecd</th>';
    //             $data .= '<th width="7%" align="center" class="disbu">Total Recievecd</th>';
    //             $data .= '<th width = "7%"  align = "center" class = "disbu">Open Cases</th>';
    //             $data .= '<th width="7%" align = "center" class="disbu">Open principle</th >';
    //             $data .= '<th width = "7%" align = "center"  class = "disbu">Cases Wise%</th>';
    //             $data .= '<th width = "7%" align = "center" class = "disbu">Amount Wise%</th>';
    //             $data .= '<th width = "7%" align = "center" class = "disbu">Default%</th></tr>';
    //             foreach ($report_array as $row_data) {
    //                 $pre_count = number_format($row_data['pre_counts'], 0);
    //                 $pre_loan_amount = number_format($row_data['pre_loan_amount'], 2);
    //                 $month_date = date('d-m-Y', strtotime($row_data['Month_year']));
    //                 $count = number_format($row_data['counts'], 0);
    //                 $loan_amount = number_format($row_data['loan_amount'], 2);
    //                 $loan_principle_received_amount = number_format($row_data['loan_principle_received_amount'], 2);
    //                 $loan_interest_received_amount = number_format($row_data['loan_interest_received_amount'], 2);
    //                 $loan_penalty_received_amount = number_format($row_data['loan_penalty_received_amount'], 2);
    //                 $loan_total_received_amount = number_format($row_data['loan_total_received_amount'], 2);
    //                 $closed_count = ($row_data['counts'] - $row_data['pre_counts']);
    //                 $closed_loan_amount = number_format(($row_data['loan_amount'] - $row_data['pre_loan_amount']), 2);
    //                 $percentage_count = number_format(100 - (($row_data['pre_counts'] / $row_data['counts']) * 100), 2);
    //                 $percentage_loan_amount = number_format((($row_data['loan_principle_received_amount'] / $row_data['loan_amount']) * 100), 2);
    //                 $default_percentage = 0;
    //                 if (strtotime($row_data['Month_year']) < strtotime(date('Y-m-d'))) {
    //                     $default_percentage = number_format(100 - (($closed_count / $count) * 100), 2);
    //                 }
    //                 $data .= '<tr>';
    //                 $data .= '<td>' . $month_date . '</td>';
    //                 $data .= '<td>' . $count . '</td>';
    //                 $data .= '<td>' . $loan_amount . '</td>';
    //                 $data .= '<td>' . $closed_count . '</td>';
    //                 // $data .= '<td>' . $closed_loan_amount . '</td>';
    //                 $data .= '<td>' . $loan_principle_received_amount . '</td>';
    //                 $data .= '<td>' . $loan_interest_received_amount . '</td>';
    //                 $data .= '<td>' . $loan_penalty_received_amount . '</td>';
    //                 $data .= '<td>' . $loan_total_received_amount . '</td>';
    //                 $data .= '<td>' . $pre_count . '</td>';
    //                 $data .= '<td>' . $pre_loan_amount . '</td>';
    //                 $data .= '<td>' . $percentage_count . '%</td>';
    //                 $data .= '<td>' . $percentage_loan_amount . '%</td>';
    //                 $data .= '<td>' . $default_percentage . '%</td>';
    //                 $data .= '</tr>';
    //             }
    //             $data .= '<tr><td class = "footer-tabels-text">&nbsp;</td>';
    //             $data .= '<td class = "footer-tabels-text">' . $total_count . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . ($total_count - $total_pre_count) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format(($total_loan_amount - $total_pre_loan_amount), 2) . '</td>';
    //             // $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_principle_received_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_interest_received_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_penalty_received_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format($total_loan_total_received_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . $total_pre_count . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format($total_pre_loan_amount, 2) . '</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format(100 - (($total_pre_count / $total_count) * 100), 2) . '%</td>';
    //             $data .= '<td class = "footer-tabels-text">' . number_format(100 - (($total_pre_loan_amount / $total_loan_amount) * 100), 2) . '%</td>';
    //             $data .= '<td class = "footer-tabels-text">&nbsp;</td></tr>';
    //             $data .= '</tbody></table>';
    //         } else {
    //             return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
    //         }
    //     } else {
    //         return redirect(base_url('exportData/'), 'refresh');
    //     }
    //     return $data;
    // }


    public function MonthwiseCollectionModel($report_id, $month_data)
    {
        if (!empty($month_data) && $report_id == 13) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));

            // if (strtotime($toDate) > strtotime(date('d-m-Y'))) {
            //     $toDate = date("Y-m-d");
            // }

            $q = "SELECT LD.lead_id, CAM.repayment_date as month_year, LD.lead_status_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_principle_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_payable_amount, L.loan_penalty_payable_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_penalty_received_amount, L.loan_penalty_outstanding_amount, L.loan_total_received_amount ";
            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_id=CAM.lead_id AND L.loan_status_id=14 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >='$fromDate' AND CAM.repayment_date <= '$toDate ' ORDER BY CAM.repayment_date ASC;";

            $result = $this->db->query($q)->result_array();
            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();

                $total_count = 0;
                $total_loan_amount = 0;
                $total_collection = 0;
                $loan_principle_received_amount = 0;
                $loan_principle_outstanding_amount = 0;
                $loan_interest_received_amount = 0;
                $loan_interest_outstanding_amount = 0;
                $loan_penalty_received_amount = 0;
                $loan_penalty_outstanding_amount = 0;
                $not_closed = 0;
                $loan_interest_payable_amount = 0;
                $loan_penalty_payable_amount = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $total_loan_amount += $row['loan_recommended'];
                        $total_collection += $row['loan_total_received_amount'];
                        $loan_principle_received_amount += $row['loan_principle_received_amount'];
                        $loan_principle_outstanding_amount += $row['loan_principle_outstanding_amount'];
                        $loan_interest_received_amount += $row['loan_interest_received_amount'];
                        $loan_interest_outstanding_amount += $row['loan_interest_outstanding_amount'];
                        $loan_penalty_payable_amount += $row['loan_penalty_payable_amount'];
                        $loan_penalty_received_amount += $row['loan_penalty_received_amount'];
                        $loan_penalty_outstanding_amount += $row['loan_penalty_outstanding_amount'];
                        $loan_interest_payable_amount += $row['loan_interest_payable_amount'];

                        if (empty($report_array[$row['month_year']]['not_closed_counts'])) {
                            $report_array[$row['month_year']]['not_closed_counts'] = 0;
                        }

                        $report_array[$row['month_year']]['total_counts'] += 1;
                        $report_array[$row['month_year']]['date'] = $row['month_year'];
                        $report_array[$row['month_year']]['loan_recommended'] += $row['loan_recommended'];
                        $report_array[$row['month_year']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                        $report_array[$row['month_year']]['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_interest_received_amount'] += $row['loan_interest_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_payable_amount'] += $row['loan_interest_payable_amount'];
                        $report_array[$row['month_year']]['loan_interest_outstanding_amount'] += $row['loan_interest_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_penalty_payable_amount'] += $row['loan_penalty_payable_amount'];
                        $report_array[$row['month_year']]['loan_penalty_received_amount'] += $row['loan_penalty_received_amount'];
                        $report_array[$row['month_year']]['loan_penalty_outstanding_amount'] += $row['loan_penalty_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_total_received_amount'] += $row['loan_total_received_amount'];

                        if (in_array($row['lead_status_id'], array(14, 19, 18))) {
                            $not_closed += 1;
                            $report_array[$row['month_year']]['not_closed_counts'] += 1;
                        }
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="19" class="footer-tabels-text" style="text-align:center;"><strong>Month Wise Collection Report – ' . date('M-y', strtotime($fromDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</strong></th></tr class="sec-header"></thead><tr class="sec-header"><th rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Loan Applications</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Principal Amount</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Interest</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Penalty</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:70px;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Amount Percentage %</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;"><strong>Cases Percentage %</strong></th></tr>';
                $data .= '<tr class="thr-header" style="background:#dee9df; top: 15%"><td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Date</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Not Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Received Amt</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important;"><strong>Principal Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Interest Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Principal Default</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Default</strong></td></tr>';

                foreach ($report_array as $row_data) {

                    $data .= '<tr><td>' . date('d-m-Y', strtotime($row_data['date'])) . '</td>';
                    $data .= '<td>' . $row_data['total_counts'] . '</td>';
                    $data .= '<td>' . ($row_data['total_counts'] - $row_data['not_closed_counts']) . '</td>';
                    $data .= '<td>' . $row_data['not_closed_counts'] . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_recommended'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_total_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_received_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_interest_received_amount'] / $row_data['loan_interest_payable_amount']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_outstanding_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((1 - (($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts'])) * 100, 2) . '%</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_count - $not_closed) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $not_closed . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_loan_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_collection, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_received_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_interest_received_amount / $loan_interest_payable_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_outstanding_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_count - $not_closed) / $total_count) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((1 - (($total_count - $not_closed) / $total_count)) * 100, 2) . '%</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function MonthwiseDisbursalModel($report_id, $month_data)
    {
        if (!empty($month_data) && $report_id == 14) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));

            $q = "SELECT LD.lead_id, CAM.disbursal_date as month_year, LD.lead_status_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_principle_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_payable_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_penalty_received_amount, L.loan_penalty_outstanding_amount, L.loan_total_received_amount, L.loan_principle_discount_amount, L.loan_interest_discount_amount, L.loan_penalty_discount_amount ";
            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_id=CAM.lead_id AND L.loan_status_id=14 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.disbursal_date >='$fromDate' AND CAM.disbursal_date <= '$toDate ' ORDER BY CAM.disbursal_date ASC;";

            $result = $this->db->query($q)->result_array();
            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();

                $total_count = 0;
                $total_loan_amount = 0;
                $total_collection = 0;
                $loan_principle_received_amount = 0;
                $loan_principle_outstanding_amount = 0;
                $loan_interest_received_amount = 0;
                $loan_interest_outstanding_amount = 0;
                $loan_penalty_received_amount = 0;
                $loan_penalty_outstanding_amount = 0;
                $not_closed = 0;
                $loan_interest_payable_amount = 0;
                $loan_principle_discount_amount = 0;
                $loan_interest_discount_amount = 0;
                $loan_penalty_discount_amount = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $total_loan_amount += $row['loan_recommended'];
                        $total_collection += $row['loan_total_received_amount'];
                        $loan_principle_received_amount += $row['loan_principle_received_amount'];
                        $loan_principle_outstanding_amount += $row['loan_principle_outstanding_amount'];
                        $loan_interest_received_amount += $row['loan_interest_received_amount'];
                        $loan_interest_outstanding_amount += $row['loan_interest_outstanding_amount'];
                        $loan_penalty_received_amount += $row['loan_penalty_received_amount'];
                        $loan_penalty_outstanding_amount += $row['loan_penalty_outstanding_amount'];
                        $loan_interest_payable_amount += $row['loan_interest_payable_amount'];
                        $loan_principle_discount_amount += $row['loan_principle_discount_amount'];
                        $loan_interest_discount_amount += $row['loan_interest_discount_amount'];
                        $loan_penalty_discount_amount += $row['loan_penalty_discount_amount'];

                        if (empty($report_array[$row['month_year']]['not_closed_counts'])) {
                            $report_array[$row['month_year']]['not_closed_counts'] = 0;
                        }

                        $report_array[$row['month_year']]['total_counts'] += 1;
                        $report_array[$row['month_year']]['date'] = $row['month_year'];
                        $report_array[$row['month_year']]['loan_recommended'] += $row['loan_recommended'];
                        $report_array[$row['month_year']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                        $report_array[$row['month_year']]['loan_principle_discount_amount'] += $row['loan_principle_discount_amount'];
                        $report_array[$row['month_year']]['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_interest_received_amount'] += $row['loan_interest_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_payable_amount'] += $row['loan_interest_payable_amount'];
                        $report_array[$row['month_year']]['loan_interest_outstanding_amount'] += $row['loan_interest_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_penalty_received_amount'] += $row['loan_penalty_received_amount'];
                        $report_array[$row['month_year']]['loan_penalty_outstanding_amount'] += $row['loan_penalty_outstanding_amount'];
                        $report_array[$row['month_year']]['loan_total_received_amount'] += $row['loan_total_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_discount_amount'] += $row['loan_interest_discount_amount'];
                        $report_array[$row['month_year']]['loan_penalty_discount_amount'] += $row['loan_penalty_discount_amount'];

                        if (in_array($row['lead_status_id'], array(14, 19, 18))) {
                            $not_closed += 1;
                            $report_array[$row['month_year']]['not_closed_counts'] += 1;
                        }
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="21" class="footer-tabels-text" style="text-align:center;"><strong>Month Wise Disbursal Report – ' . date('M-y', strtotime($fromDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</strong></th></tr></thead><tr class="sec-header"><th rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Loan Applications</th>';
                $data .= '<th colspan="4"  class="no-of-case" style="text-align:center !important; width:70px;">Principal Amount</th>';
                $data .= '<th colspan="4"  class="no-of-case" style="text-align:center !important; width:70px;">Interest</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Penalty</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:70px;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Amount Percentage %</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;"><strong>Cases Percentage %</strong></th></tr>';
                $data .= '<tr class="thr-header" style="background:#dee9df; top: 15%"><td width="8%" class="no-of-case" style="text-align:center !important;"><strong>Date</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Not Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Discount </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding </strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Discount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Discount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Received Amount</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important;"><strong>Principal</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Interest</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Principal Default</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Default</strong></td></tr>';

                foreach ($report_array as $row_data) {

                    $data .= '<tr><td>' . date('d-m-Y', strtotime($row_data['date'])) . '</td>';
                    $data .= '<td>' . $row_data['total_counts'] . '</td>';
                    $data .= '<td>' . ($row_data['total_counts'] - $row_data['not_closed_counts']) . '</td>';
                    $data .= '<td>' . $row_data['not_closed_counts'] . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_recommended'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_discount_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_discount_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_discount_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_total_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_received_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_interest_received_amount'] / $row_data['loan_interest_payable_amount']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_outstanding_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((1 - (($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts'])) * 100, 2) . '%</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_count - $not_closed) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $not_closed . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_loan_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_discount_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_discount_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_discount_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_collection, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_received_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_interest_received_amount / $loan_interest_payable_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_outstanding_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_count - $not_closed) / $total_count) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((1 - (($total_count - $not_closed) / $total_count)) * 100, 2) . '%</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function HourlyDisbursalModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT LD.lead_final_disbursed_date, TIME(LD.lead_disbursal_approve_datetime) as times, LD.user_type, CAM.loan_recommended FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) ";
            $q .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND LD.lead_final_disbursed_date >= '$fromDate' AND LD.lead_final_disbursed_date <= '$toDate';";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();

                $report_array['12:01 AM-10:00 AM']['times'] = '12:01 AM-10:00 AM';
                $report_array['12:01 AM-10:00 AM']['NEW']['counts'] = 0;
                $report_array['12:01 AM-10:00 AM']['NEW']['times'] = '';
                $report_array['12:01 AM-10:00 AM']['NEW']['user_type'] = '';
                $report_array['12:01 AM-10:00 AM']['NEW']['loan_recommended'] = 0;

                $report_array['12:01 AM-10:00 AM']['REPEAT']['counts'] = 0;
                $report_array['12:01 AM-10:00 AM']['REPEAT']['times'] = '';
                $report_array['12:01 AM-10:00 AM']['REPEAT']['user_type'] = '';
                $report_array['12:01 AM-10:00 AM']['REPEAT']['loan_recommended'] = 0;

                $report_array['10:01 AM-12:00 PM']['times'] = '10:01 AM-12:00 PM';
                $report_array['10:01 AM-12:00 PM']['NEW']['counts'] = 0;
                $report_array['10:01 AM-12:00 PM']['NEW']['times'] = '';
                $report_array['10:01 AM-12:00 PM']['NEW']['user_type'] = '';
                $report_array['10:01 AM-12:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['10:01 AM-12:00 PM']['REPEAT']['counts'] = 0;
                $report_array['10:01 AM-12:00 PM']['REPEAT']['times'] = '';
                $report_array['10:01 AM-12:00 PM']['REPEAT']['user_type'] = '';
                $report_array['10:01 AM-12:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['12:01 PM-02:00 PM']['times'] = '12:01 PM-02:00 PM';
                $report_array['12:01 PM-02:00 PM']['NEW']['counts'] = 0;
                $report_array['12:01 PM-02:00 PM']['NEW']['times'] = '';
                $report_array['12:01 PM-02:00 PM']['NEW']['user_type'] = '';
                $report_array['12:01 PM-02:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['12:01 PM-02:00 PM']['REPEAT']['counts'] = 0;
                $report_array['12:01 PM-02:00 PM']['REPEAT']['times'] = '';
                $report_array['12:01 PM-02:00 PM']['REPEAT']['user_type'] = '';
                $report_array['12:01 PM-02:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['02:01 PM-04:00 PM']['times'] = '02:01 PM-04:00 PM';
                $report_array['02:01 PM-04:00 PM']['NEW']['counts'] = 0;
                $report_array['02:01 PM-04:00 PM']['NEW']['times'] = '';
                $report_array['02:01 PM-04:00 PM']['NEW']['user_type'] = '';
                $report_array['02:01 PM-04:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['02:01 PM-04:00 PM']['REPEAT']['counts'] = 0;
                $report_array['02:01 PM-04:00 PM']['REPEAT']['times'] = '';
                $report_array['02:01 PM-04:00 PM']['REPEAT']['user_type'] = '';
                $report_array['02:01 PM-04:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['04:01 PM-06:00 PM']['times'] = '04:01 PM-06:00 PM';
                $report_array['04:01 PM-06:00 PM']['NEW']['counts'] = 0;
                $report_array['04:01 PM-06:00 PM']['NEW']['times'] = '';
                $report_array['04:01 PM-06:00 PM']['NEW']['user_type'] = '';
                $report_array['04:01 PM-06:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['04:01 PM-06:00 PM']['REPEAT']['counts'] = 0;
                $report_array['04:01 PM-06:00 PM']['REPEAT']['times'] = '';
                $report_array['04:01 PM-06:00 PM']['REPEAT']['user_type'] = '';
                $report_array['04:01 PM-06:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['06:01 PM-08:00 PM']['times'] = '06:01 PM-08:00 PM';
                $report_array['06:01 PM-08:00 PM']['NEW']['counts'] = 0;
                $report_array['06:01 PM-08:00 PM']['NEW']['times'] = '';
                $report_array['06:01 PM-08:00 PM']['NEW']['user_type'] = '';
                $report_array['06:01 PM-08:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['06:01 PM-08:00 PM']['REPEAT']['counts'] = 0;
                $report_array['06:01 PM-08:00 PM']['REPEAT']['times'] = '';
                $report_array['06:01 PM-08:00 PM']['REPEAT']['user_type'] = '';
                $report_array['06:01 PM-08:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['08:01 PM-10:00 PM']['times'] = '08:01 PM-10:00 PM';
                $report_array['08:01 PM-10:00 PM']['NEW']['counts'] = 0;
                $report_array['08:01 PM-10:00 PM']['NEW']['times'] = '';
                $report_array['08:01 PM-10:00 PM']['NEW']['user_type'] = '';
                $report_array['08:01 PM-10:00 PM']['NEW']['loan_recommended'] = 0;

                $report_array['08:01 PM-10:00 PM']['REPEAT']['counts'] = 0;
                $report_array['08:01 PM-10:00 PM']['REPEAT']['times'] = '';
                $report_array['08:01 PM-10:00 PM']['REPEAT']['user_type'] = '';
                $report_array['08:01 PM-10:00 PM']['REPEAT']['loan_recommended'] = 0;

                $report_array['10:01 PM-12:00 AM']['times'] = '10:01 PM-12:00 AM';
                $report_array['10:01 PM-12:00 AM']['NEW']['counts'] = 0;
                $report_array['10:01 PM-12:00 AM']['NEW']['times'] = '';
                $report_array['10:01 PM-12:00 AM']['NEW']['user_type'] = '';
                $report_array['10:01 PM-12:00 AM']['NEW']['loan_recommended'] = 0;

                $report_array['10:01 PM-12:00 AM']['REPEAT']['counts'] = 0;
                $report_array['10:01 PM-12:00 AM']['REPEAT']['times'] = '';
                $report_array['10:01 PM-12:00 AM']['REPEAT']['user_type'] = '';
                $report_array['10:01 PM-12:00 AM']['REPEAT']['loan_recommended'] = 0;

                ///   new arrray

                if (!empty($result)) {
                    foreach ($result as $row) {
                        // $report_array['counts'] += 1;
                        // $report_array['loan_recommended'] += $row['loan_recommended'];
                        $c_time = strtotime($row['times']);

                        if ($c_time > strtotime('00:00') && $c_time <= strtotime('10:00')) {

                            $report_array['12:01 AM-10:00 AM']['times'] = '12:01 AM-10:00 AM';
                            $report_array['12:01 AM-10:00 AM']['counts'] += 1;
                            $report_array['12:01 AM-10:00 AM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['12:01 AM-10:00 AM']['NEW']['counts'] += 1;
                                $report_array['12:01 AM-10:00 AM']['NEW']['times'] = '12:01 AM-10:00 AM';
                                $report_array['12:01 AM-10:00 AM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['12:01 AM-10:00 AM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['12:01 AM-10:00 AM']['REPEAT']['counts'] += 1;
                                $report_array['12:01 AM-10:00 AM']['REPEAT']['times'] = '12:01 AM-10:00 AM';
                                $report_array['12:01 AM-10:00 AM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['12:01 AM-10:00 AM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('10:00') && $c_time <= strtotime('12:00')) {

                            $report_array['10:01 AM-12:00 PM']['times'] = '10:01 AM-12:00 PM';
                            $report_array['10:01 AM-12:00 PM']['counts'] += 1;
                            $report_array['10:01 AM-12:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['10:01 AM-12:00 PM']['NEW']['counts'] += 1;
                                $report_array['10:01 AM-12:00 PM']['NEW']['times'] = '10:01 AM-12:00 PM';
                                $report_array['10:01 AM-12:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['10:01 AM-12:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['10:01 AM-12:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['10:01 AM-12:00 PM']['REPEAT']['times'] = '10:01 AM-12:00 PM';
                                $report_array['10:01 AM-12:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['10:01 AM-12:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('12:00') && $c_time <= strtotime('14:00')) {

                            $report_array['12:01 PM-02:00 PM']['times'] = '12:01 PM-02:00 PM';
                            $report_array['12:01 PM-02:00 PM']['counts'] += 1;
                            $report_array['12:01 PM-02:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['12:01 PM-02:00 PM']['NEW']['counts'] += 1;
                                $report_array['12:01 PM-02:00 PM']['NEW']['times'] = '12:01 PM-02:00 PM';
                                $report_array['12:01 PM-02:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['12:01 PM-02:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['12:01 PM-02:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['12:01 PM-02:00 PM']['REPEAT']['times'] = '12:01 PM-02:00 PM';
                                $report_array['12:01 PM-02:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['12:01 PM-02:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('14:00') && $c_time <= strtotime('16:00')) {

                            $report_array['02:01 PM-04:00 PM']['times'] = '02:01 PM-04:00 PM';
                            $report_array['02:01 PM-04:00 PM']['counts'] += 1;
                            $report_array['02:01 PM-04:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['02:01 PM-04:00 PM']['NEW']['counts'] += 1;
                                $report_array['02:01 PM-04:00 PM']['NEW']['times'] = '02:01 PM-04:00 PM';
                                $report_array['02:01 PM-04:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['02:01 PM-04:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['02:01 PM-04:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['02:01 PM-04:00 PM']['REPEAT']['times'] = '02:01 PM-04:00 PM';
                                $report_array['02:01 PM-04:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['02:01 PM-04:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('16:00') && $c_time <= strtotime('18:00')) {

                            $report_array['04:01 PM-06:00 PM']['times'] = '04:01 PM-06:00 PM';
                            $report_array['04:01 PM-06:00 PM']['counts'] += 1;
                            $report_array['04:01 PM-06:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['04:01 PM-06:00 PM']['NEW']['counts'] += 1;
                                $report_array['04:01 PM-06:00 PM']['NEW']['times'] = '04:01 PM-06:00 PM';
                                $report_array['04:01 PM-06:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['04:01 PM-06:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['04:01 PM-06:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['04:01 PM-06:00 PM']['REPEAT']['times'] = '04:01 PM-06:00 PM';
                                $report_array['04:01 PM-06:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['04:01 PM-06:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('18:00') && $c_time <= strtotime('20:00')) {

                            $report_array['06:01 PM-08:00 PM']['times'] = '06:01 PM-08:00 PM';
                            $report_array['06:01 PM-08:00 PM']['counts'] += 1;
                            $report_array['06:01 PM-08:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['06:01 PM-08:00 PM']['NEW']['counts'] += 1;
                                $report_array['06:01 PM-08:00 PM']['NEW']['times'] = '06:01 PM-08:00 PM';
                                $report_array['06:01 PM-08:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['06:01 PM-08:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['06:01 PM-08:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['06:01 PM-08:00 PM']['REPEAT']['times'] = '06:01 PM-08:00 PM';
                                $report_array['06:01 PM-08:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['06:01 PM-08:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('20:00') && $c_time <= strtotime('22:00')) {

                            $report_array['08:01 PM-10:00 PM']['times'] = '08:01 PM-10:00 PM';
                            $report_array['08:01 PM-10:00 PM']['counts'] += 1;
                            $report_array['08:01 PM-10:00 PM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['08:01 PM-10:00 PM']['NEW']['counts'] += 1;
                                $report_array['08:01 PM-10:00 PM']['NEW']['times'] = '08:01 PM-10:00 PM';
                                $report_array['08:01 PM-10:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['08:01 PM-10:00 PM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['08:01 PM-10:00 PM']['REPEAT']['counts'] += 1;
                                $report_array['08:01 PM-10:00 PM']['REPEAT']['times'] = '08:01 PM-10:00 PM';
                                $report_array['08:01 PM-10:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['08:01 PM-10:00 PM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        } elseif ($c_time > strtotime('22:00') && $c_time <= strtotime('24:00')) {

                            $report_array['10:01 PM-12:00 AM']['times'] = '10:01 PM-12:00 AM';
                            $report_array['10:01 PM-12:00 AM']['counts'] += 1;
                            $report_array['10:01 PM-12:00 AM']['loan_recommended'] += $row['loan_recommended'];

                            if ($row['user_type'] == 'NEW') {
                                $report_array['10:01 PM-12:00 AM']['NEW']['counts'] += 1;
                                $report_array['10:01 PM-12:00 AM']['NEW']['times'] = '10:01 PM-12:00 AM';
                                $report_array['10:01 PM-12:00 AM']['NEW']['user_type'] = $row['user_type'];
                                $report_array['10:01 PM-12:00 AM']['NEW']['loan_recommended'] += $row['loan_recommended'];
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $report_array['10:01 PM-12:00 AM']['REPEAT']['counts'] += 1;
                                $report_array['10:01 PM-12:00 AM']['REPEAT']['times'] = '10:01 PM-12:00 AM';
                                $report_array['10:01 PM-12:00 AM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array['10:01 PM-12:00 AM']['REPEAT']['loan_recommended'] += $row['loan_recommended'];
                            }
                        }
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="7" class="footer-tabels-text" style="text-align:center;">Disbursal Hourly Report – ' . date('d-m-Y', strtotime($fromDate)) . ' to ' . date('d-m-Y', strtotime($toDate)) . '  Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr></thead>';
                $data .= '<tr><th width="111" rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th><th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">No of Applicatons</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Loan Amount</th></tr>';
                $data .= '<tr><td  width="25%" class="no-of-case" style="text-align:center !important;"><strong>Time</strong></td>';
                $data .= '<td width="15%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="15%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="14%" class="no-of-case" style="text-align:center !important;"><strong>Total</strong></td>';
                $data .= '<td width="14%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="14%" class="no-of-case" style="text-align:center !important;"><strong>Repeat </strong></td>';
                $data .= '<td width="14%" class="no-of-case" style="text-align:center !important;"><strong>Total </strong></td></tr> ';

                $fresh_count = 0;
                $repeat_count = 0;
                $total_count = 0;
                $fresh_loan = 0;
                $repeat_loan = 0;
                $total_loan = 0;

                foreach (array_keys($report_array) as $key) {

                    $fresh_count += $report_array[$key]['NEW']['counts'];
                    $repeat_count += $report_array[$key]['REPEAT']['counts'];
                    $total_count += ($report_array[$key]['NEW']['counts'] + $report_array[$key]['REPEAT']['counts']);
                    $fresh_loan += $report_array[$key]['NEW']['loan_recommended'];
                    $repeat_loan += $report_array[$key]['REPEAT']['loan_recommended'];
                    $total_loan += ($report_array[$key]['NEW']['loan_recommended'] + $report_array[$key]['REPEAT']['loan_recommended']);

                    $data .= '<tr><td align="center" valign="middle" style="text-align:center;">' . $report_array[$key]['times'] . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . $report_array[$key]['NEW']['counts'] . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . $report_array[$key]['REPEAT']['counts'] . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['NEW']['counts'] + $report_array[$key]['REPEAT']['counts']) . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . number_format($report_array[$key]['NEW']['loan_recommended'], 0) . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . number_format($report_array[$key]['REPEAT']['loan_recommended'], 0) . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . number_format(($report_array[$key]['NEW']['loan_recommended'] + $report_array[$key]['REPEAT']['loan_recommended']), 0) . '</td></tr>';
                }

                $data .= '<tr><td align="center" valign="middle" class="footer-tabels-text">Grand Total </td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $fresh_count . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $repeat_count . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . number_format($fresh_loan, 0) . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . number_format($repeat_loan, 0) . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . number_format($total_loan, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function BranchwiseVisitModel($from_Date, $to_date)
    {
        if (!empty($from_Date) && !empty($to_date)) {
            $fromDate = date('Y-m-d', strtotime($from_Date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $q = "SELECT LCV.col_lead_id, MB.m_branch_name, LD.lead_branch_id, LCV.col_visit_created_on, LCV.col_visit_field_status_id ";
            $q .= "FROM loan_collection_visit LCV INNER JOIN leads LD ON(LCV.col_lead_id=LD.lead_id) INNER JOIN master_branch MB ON(LD.lead_branch_id=MB.m_branch_id) ";
            $q .= "WHERE LD.lead_branch_id=MB.m_branch_id AND LCV.col_lead_id=LD.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND LD.lead_branch_id > 0 AND DATE(LCV.col_visit_created_on) >= '$fromDate' AND DATE(LCV.col_visit_created_on) <= '$toDate' ORDER BY LD.lead_branch_id ASC;";

            $result = $this->db->query($q)->result_array();
            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();
                $total_report_array = array();

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $total_report_array['total_counts'] += 1;
                        $total_report_array[$row['col_visit_field_status_id']]['counts'] += 1;

                        $report_array[$row['lead_branch_id']]['total_counts'] += 1;
                        $report_array[$row['lead_branch_id']]['branch'] = $row['m_branch_name'];
                        $report_array[$row['lead_branch_id']][$row['col_visit_field_status_id']]['counts'] += 1;
                        $report_array[$row['lead_branch_id']][$row['col_visit_field_status_id']]['branch'] = $row['m_branch_name'];
                    }
                }


                // echo "<pre>";
                // print_r($total_report_array);
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="9" class="footer-tabels-text" style="text-align:center;"><strong>Branch Wise Visit Report – ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</strong></th></tr></thead>';
                $data .= '<tr><td width="8%" class="no-of-case" style="text-align:center !important;"><strong>Branch</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total Visit Initiated</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Pending</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>In-Progress</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Cancel</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Hold</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Completed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Pending%</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Completed%</strong></td></tr>';

                foreach ($report_array as $row_data) {

                    $data .= '<tr><td>' . $row_data['branch'] . '</td>';
                    $data .= '<td>' . (empty(($row_data['total_counts'])) ? 0 : $row_data['total_counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[1]['counts'])) ? 0 : $row_data[1]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[2]['counts'])) ? 0 : $row_data[2]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[3]['counts'])) ? 0 : $row_data[3]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[4]['counts'])) ? 0 : $row_data[4]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['counts'])) ? 0 : $row_data[5]['counts']) . '</td>';
                    $data .= '<td>' . number_format((($row_data[1]['counts'] / $row_data['total_counts']) * 100), 2) . '%</td>';
                    $data .= '<td>' . number_format((($row_data[5]['counts'] / $row_data['total_counts']) * 100), 2) . '%</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_report_array['total_counts']) ? 0 : $total_report_array['total_counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[1]['counts'])) ? 0 : $total_report_array[1]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[2]['counts'])) ? 0 : $total_report_array[2]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[3]['counts'])) ? 0 : $total_report_array[3]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[4]['counts'])) ? 0 : $total_report_array[4]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[5]['counts'])) ? 0 : $total_report_array[5]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_report_array[1]['counts'] / $total_report_array['total_counts']) * 100), 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_report_array[5]['counts'] / $total_report_array['total_counts']) * 100), 2) . '%</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function RMwiseVisitModel($from_Date, $to_date)
    {
        if (!empty($from_Date) && !empty($to_date)) {
            $fromDate = date('Y-m-d', strtotime($from_Date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $q = "SELECT LCV.col_lead_id, LCV.col_fe_visit_total_distance_covered, LCV.col_fe_rtoh_total_distance_covered,  LCV.col_visit_allocate_on, LCV.col_fe_visit_approval_status, U.name, LCV.col_visit_allocated_to, LCV.col_visit_requested_datetime, LCV.col_visit_field_status_id ";
            $q .= "FROM loan_collection_visit LCV INNER JOIN leads LD ON(LCV.col_lead_id=LD.lead_id) INNER JOIN users U ON(LCV.col_visit_allocated_to=U.user_id) ";
            $q .= "WHERE LCV.col_visit_field_status_id !=3 AND LCV.col_lead_id=LD.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND DATE(LCV.col_visit_allocate_on) >= '$fromDate' AND DATE(LCV.col_visit_allocate_on) <= '$toDate' ORDER BY name  ASC;";

            $result = $this->db->query($q)->result_array();

            $q1 = "SELECT COL.received_amount, COL.collection_executive_user_id FROM collection COL WHERE COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived >= '$fromDate' AND COL.date_of_recived <= '$toDate' AND COL.collection_executive_user_id IS NOT NULL;";
            $collection_result = $this->db->query($q1)->result_array();
            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();
                $total_report_array = array();

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $total_report_array['total_counts'] += 1;
                        $total_report_array['total_collection_amount'] += $row['collection_amount'];
                        $total_report_array[$row['col_visit_field_status_id']]['counts'] += 1;
                        $total_report_array[$row['col_visit_field_status_id']]['collection_amount'] += $row['collection_amount'];

                        $report_array[$row['col_visit_allocated_to']]['total_counts'] += 1;
                        $report_array[$row['col_visit_allocated_to']]['rm_name'] = $row['name'];
                        $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['counts'] += 1;
                        $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['rm_name'] = $row['name'];

                        if ($row['col_visit_field_status_id'] == 5 && $row['col_fe_visit_approval_status'] == 1) {
                            $report_array[$row['col_visit_allocated_to']]['visit_distance'] += $row['col_fe_visit_total_distance_covered'];
                            $report_array[$row['col_visit_allocated_to']]['RTH_distance'] += $row['col_fe_rtoh_total_distance_covered'];
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['approved'] += 1;
                        } elseif ($row['col_visit_field_status_id'] == 5 && $row['col_fe_visit_approval_status'] == 2) {
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['rejected'] += 1;
                        } elseif ($row['col_visit_field_status_id'] == 5) {
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['pending'] += 1;
                        }
                    }
                }

                if (!empty($collection_result)) {
                    foreach ($collection_result as $row) {
                        if (isset($report_array[$row['collection_executive_user_id']])) {
                            $total_report_array['total_collection_amount'] += $row['received_amount'];
                            $report_array[$row['collection_executive_user_id']]['collection_amount'] += $row['received_amount'];
                        }
                    }
                }

                // echo "<pre>";
                // print_r($total_report_array);
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="15" class="footer-tabels-text" style="text-align:center;"><strong>RM Wise Visit & Collection Report – ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-M-y h:i:s') . '</strong></th></tr></thead>';
                $data .= '<tr><td width="12%" class="no-of-case" style="text-align:center !important;"><strong>RM Name</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Visit Assigned</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Completed</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Pending</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Collection Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Completed%</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Approved</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Pending</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Rejected</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Visit Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Visit Conveyance Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>RTH Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>RTH Conveyance Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Conveyance Amount</strong></td>';
                $data .= '</tr>';

                $approved = 0;
                $pending = 0;
                $rejected = 0;
                $visit_distance = 0;
                $RTH_distance = 0;
                $total_distance = 0;

                foreach ($report_array as $row_data) {

                    $approved += (empty(($row_data[5]['approved'])) ? 0 : $row_data[5]['approved']);
                    $pending += (empty(($row_data[5]['pending'])) ? 0 : $row_data[5]['pending']);
                    $rejected += (empty(($row_data[5]['rejected'])) ? 0 : $row_data[5]['rejected']);
                    $visit_distance += (empty(($row_data['visit_distance'])) ? 0 : $row_data['visit_distance']);
                    $RTH_distance += (empty(($row_data['RTH_distance'])) ? 0 : $row_data['RTH_distance']);
                    $total_distance += ($row_data['RTH_distance'] + $row_data['visit_distance']);

                    $data .= '<tr><td>' . $row_data['rm_name'] . '</td>';
                    $data .= '<td>' . (empty(($row_data['total_counts'])) ? 0 : $row_data['total_counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['counts'])) ? 0 : $row_data[5]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['total_counts'] - $row_data[5]['counts'])) ? 0 : $row_data['total_counts'] - $row_data[5]['counts']) . '</td>';
                    $data .= '<td>' . number_format((empty(($row_data['collection_amount'])) ? 0 : $row_data['collection_amount']), 2) . '</td>';
                    $data .= '<td>' . number_format((($row_data[5]['counts'] / $row_data['total_counts']) * 100), 2) . '%</td>';
                    $data .= '<td>' . (empty(($row_data[5]['approved'])) ? 0 : $row_data[5]['approved']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['pending'])) ? 0 : $row_data[5]['pending']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['rejected'])) ? 0 : $row_data[5]['rejected']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['visit_distance'])) ? 0 : $row_data['visit_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['visit_distance'])) ? 0 : ($row_data['visit_distance']) * 3) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'])) ? 0 : $row_data['RTH_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'])) ? 0 : ($row_data['RTH_distance']) * 3) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'] + $row_data['visit_distance'])) ? 0 : $row_data['RTH_distance'] + $row_data['visit_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'] + $row_data['visit_distance'])) ? 0 : ($row_data['RTH_distance'] + $row_data['visit_distance']) * 3) . '</td>';
                    $data .= '</tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_report_array['total_counts']) ? 0 : $total_report_array['total_counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[5]['counts'])) ? 0 : $total_report_array[5]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array['total_counts'] - $total_report_array[5]['counts'])) ? 0 : $total_report_array['total_counts'] - $total_report_array[5]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((empty(($total_report_array['total_collection_amount'])) ? 0 : $total_report_array['total_collection_amount']), 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_report_array[5]['counts'] / $total_report_array['total_counts']) * 100), 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . $approved . '</td>';
                $data .= '<td class="footer-tabels-text">' . $pending . '</td>';
                $data .= '<td class="footer-tabels-text">' . $rejected . '</td>';
                $data .= '<td class="footer-tabels-text">' . $visit_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($visit_distance * 3) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $RTH_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($RTH_distance * 3) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_distance * 3) . '</td>';
                $data .= '</tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function RMConveyanceModel($from_Date, $to_date)
    {
        if (!empty($from_Date) && !empty($to_date)) {
            $fromDate = date('Y-m-d', strtotime($from_Date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $q = "SELECT LCV.col_lead_id, LCV.col_fe_visit_total_distance_covered, LCV.col_fe_rtoh_total_distance_covered,  LCV.col_fe_visit_end_datetime, LCV.col_fe_visit_approval_status, U.name, LCV.col_visit_allocated_to, LCV.col_visit_requested_datetime, LCV.col_visit_field_status_id ";
            $q .= "FROM loan_collection_visit LCV INNER JOIN leads LD ON(LCV.col_lead_id=LD.lead_id) INNER JOIN users U ON(LCV.col_visit_allocated_to=U.user_id) ";
            $q .= "WHERE LCV.col_visit_field_status_id !=3 AND LCV.col_lead_id=LD.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND DATE(LCV.col_fe_visit_end_datetime) >= '$fromDate' AND DATE(LCV.col_fe_visit_end_datetime) <= '$toDate' ORDER BY name  ASC;";

            $result = $this->db->query($q)->result_array();

            $q1 = "SELECT COL.received_amount, COL.collection_executive_user_id FROM collection COL WHERE COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived >= '$fromDate' AND COL.date_of_recived <= '$toDate' AND COL.collection_executive_user_id IS NOT NULL;";
            $collection_result = $this->db->query($q1)->result_array();
            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();
                $total_report_array = array();

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $total_report_array['total_counts'] += 1;
                        $total_report_array['total_collection_amount'] += $row['collection_amount'];
                        $total_report_array[$row['col_visit_field_status_id']]['counts'] += 1;
                        $total_report_array[$row['col_visit_field_status_id']]['collection_amount'] += $row['collection_amount'];

                        $report_array[$row['col_visit_allocated_to']]['total_counts'] += 1;
                        $report_array[$row['col_visit_allocated_to']]['rm_name'] = $row['name'];
                        $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['counts'] += 1;
                        $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['rm_name'] = $row['name'];

                        if ($row['col_visit_field_status_id'] == 5 && $row['col_fe_visit_approval_status'] == 1) {
                            $report_array[$row['col_visit_allocated_to']]['visit_distance'] += $row['col_fe_visit_total_distance_covered'];
                            $report_array[$row['col_visit_allocated_to']]['RTH_distance'] += $row['col_fe_rtoh_total_distance_covered'];
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['approved'] += 1;
                        } elseif ($row['col_visit_field_status_id'] == 5 && $row['col_fe_visit_approval_status'] == 2) {
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['rejected'] += 1;
                        } elseif ($row['col_visit_field_status_id'] == 5) {
                            $report_array[$row['col_visit_allocated_to']][$row['col_visit_field_status_id']]['pending'] += 1;
                        }
                    }
                }

                if (!empty($collection_result)) {
                    foreach ($collection_result as $row) {
                        if (isset($report_array[$row['collection_executive_user_id']])) {
                            $total_report_array['total_collection_amount'] += $row['received_amount'];
                            $report_array[$row['collection_executive_user_id']]['collection_amount'] += $row['received_amount'];
                        }
                    }
                }

                // echo "<pre>";
                // print_r($total_report_array);
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="15" class="footer-tabels-text" style="text-align:center;"><strong>RM Conveyance Report – ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-M-y h:i:s') . '</strong></th></tr></thead>';
                $data .= '<tr><td width="12%" class="no-of-case" style="text-align:center !important;"><strong>RM Name</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Visit Assigned</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Completed</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Pending</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Collection Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Completed%</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Approved</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Pending</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Conveyance Rejected</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Visit Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Visit Conveyance Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>RTH Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>RTH Conveyance Amount</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Distance (KM)</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Conveyance Amount</strong></td>';
                $data .= '</tr>';

                $approved = 0;
                $pending = 0;
                $rejected = 0;
                $visit_distance = 0;
                $RTH_distance = 0;
                $total_distance = 0;

                foreach ($report_array as $row_data) {

                    $approved += (empty(($row_data[5]['approved'])) ? 0 : $row_data[5]['approved']);
                    $pending += (empty(($row_data[5]['pending'])) ? 0 : $row_data[5]['pending']);
                    $rejected += (empty(($row_data[5]['rejected'])) ? 0 : $row_data[5]['rejected']);
                    $visit_distance += (empty(($row_data['visit_distance'])) ? 0 : $row_data['visit_distance']);
                    $RTH_distance += (empty(($row_data['RTH_distance'])) ? 0 : $row_data['RTH_distance']);
                    $total_distance += ($row_data['RTH_distance'] + $row_data['visit_distance']);

                    $data .= '<tr><td>' . $row_data['rm_name'] . '</td>';
                    $data .= '<td>' . (empty(($row_data['total_counts'])) ? 0 : $row_data['total_counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['counts'])) ? 0 : $row_data[5]['counts']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['total_counts'] - $row_data[5]['counts'])) ? 0 : $row_data['total_counts'] - $row_data[5]['counts']) . '</td>';
                    $data .= '<td>' . number_format((empty(($row_data['collection_amount'])) ? 0 : $row_data['collection_amount']), 2) . '</td>';
                    $data .= '<td>' . number_format((($row_data[5]['counts'] / $row_data['total_counts']) * 100), 2) . '%</td>';
                    $data .= '<td>' . (empty(($row_data[5]['approved'])) ? 0 : $row_data[5]['approved']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['pending'])) ? 0 : $row_data[5]['pending']) . '</td>';
                    $data .= '<td>' . (empty(($row_data[5]['rejected'])) ? 0 : $row_data[5]['rejected']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['visit_distance'])) ? 0 : $row_data['visit_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['visit_distance'])) ? 0 : ($row_data['visit_distance']) * 3) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'])) ? 0 : $row_data['RTH_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'])) ? 0 : ($row_data['RTH_distance']) * 3) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'] + $row_data['visit_distance'])) ? 0 : $row_data['RTH_distance'] + $row_data['visit_distance']) . '</td>';
                    $data .= '<td>' . (empty(($row_data['RTH_distance'] + $row_data['visit_distance'])) ? 0 : ($row_data['RTH_distance'] + $row_data['visit_distance']) * 3) . '</td>';
                    $data .= '</tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . (empty($total_report_array['total_counts']) ? 0 : $total_report_array['total_counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array[5]['counts'])) ? 0 : $total_report_array[5]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . (empty(($total_report_array['total_counts'] - $total_report_array[5]['counts'])) ? 0 : $total_report_array['total_counts'] - $total_report_array[5]['counts']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((empty(($total_report_array['total_collection_amount'])) ? 0 : $total_report_array['total_collection_amount']), 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_report_array[5]['counts'] / $total_report_array['total_counts']) * 100), 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . $approved . '</td>';
                $data .= '<td class="footer-tabels-text">' . $pending . '</td>';
                $data .= '<td class="footer-tabels-text">' . $rejected . '</td>';
                $data .= '<td class="footer-tabels-text">' . $visit_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($visit_distance * 3) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $RTH_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($RTH_distance * 3) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_distance . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_distance * 3) . '</td>';
                $data .= '</tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function SanctionProductivityNew($report_id, $to_date)
    {

        if (!empty($report_id) && !empty($to_date)) {
            $fromDate = date('Y-m-01', strtotime($to_date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $q = "SELECT LD.lead_id, LD.lead_entry_date, LD.created_on, LD.lead_rejected_reason_id, LD.lead_status_id, LD.lead_screener_assign_datetime, LD.status, screen.name as screen_name, LD.lead_screener_assign_user_id as screen_id, sanction.name as sanction_name, sanction.user_id as sanction_id, sanction_approve.name as sanction_approve_name, sanction_approve.user_id as sanction_approve_id, disburse.name as disburse_name, disburse.user_id as disburse_id, CAM.disbursal_date, LD.lead_credit_approve_datetime, LD.lead_final_disbursed_date ";
            $q .= "FROM leads LD LEFT JOIN users screen ON(LD.lead_screener_assign_user_id=screen.user_id) LEFT JOIN users sanction ON(LD.lead_credit_assign_user_id=sanction.user_id) LEFT JOIN users sanction_approve ON(LD.lead_disbursal_approve_user_id=sanction_approve.user_id) LEFT JOIN users disburse ON(LD.lead_disbursal_approve_user_id=disburse.user_id) LEFT JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id) ";
            $q .= "WHERE LD.lead_active = 1 AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date!='' AND LD.user_type='NEW' AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' ORDER BY screen_name ASC;";

            $result = $this->db->query($q)->result_array();

            if (!empty($result)) {

                $report_array = array();
                $total_array = array();
                $jsola_count = 1;
                $panchsheel_count = 1;
                $team_data = [258, 259, 260, 257, 180, 51];

                foreach ($result as $row) {
                    if ($row['screen_id'] > 0 && $row['lead_entry_date'] <= date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) && date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) <= $toDate) {

                        if (in_array($row['screen_id'], $team_data)) { // 1 for jsola, 0 for panchsheel
                            if (!isset($report_array[$row['screen_id']])) {
                                $jsola_count += 1;
                            }
                            $report_array[$row['screen_id']]['team_id'] = 1;
                        } else {
                            if (!isset($report_array[$row['screen_id']])) {
                                $panchsheel_count += 1;
                            }
                            $report_array[$row['screen_id']]['team_id'] = 0;
                        }
                        $report_array[$row['screen_id']]['name'] = $row['screen_name'];
                        $report_array[$row['screen_id']]['screen']['mtd_total'] += 1;
                        $total_array['screen']['mtd_total'] += 1;
                        if ($row['sanction_approve_id'] > 0 && date('Y-m-d', strtotime($row['lead_credit_approve_datetime'])) <= $toDate) {
                            $report_array[$row['screen_id']]['sanction']['mtd_total'] += 1;
                            $total_array['sanction']['mtd_total'] += 1;
                        }
                        if ($row['disburse_id'] > 0 && $row['disbursal_date'] <= $toDate && $row['lead_status_id'] != 40) {
                            $report_array[$row['screen_id']]['disburse']['mtd_total'] += 1;
                            $total_array['disburse']['mtd_total'] += 1;
                        }
                    }
                    if (date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) == $toDate && $row['lead_entry_date'] == $toDate) {
                        if (!empty($row['screen_id'])) {
                            if (in_array($row['screen_id'], $team_data)) { // 1 for jsola, 0 for panchsheel
                                if (!isset($report_array[$row['screen_id']])) {
                                    $jsola_count += 1;
                                }
                                $report_array[$row['screen_id']]['team_id'] = 1;
                            } else {
                                if (!isset($report_array[$row['screen_id']])) {
                                    $panchsheel_count += 1;
                                }
                                $report_array[$row['screen_id']]['team_id'] = 0;
                            }
                            $report_array[$row['screen_id']]['name'] = $row['screen_name'];
                            $report_array[$row['screen_id']]['screen']['total'] += 1;
                            $total_array['screen']['total'] += 1;
                            if (date('Y-m-d', strtotime($row['lead_credit_approve_datetime'])) == $toDate) {
                                $report_array[$row['screen_id']]['sanction']['total'] += 1;
                                $total_array['sanction']['total'] += 1;
                            }
                            if ($row['lead_status_id'] != 40 && $row['lead_final_disbursed_date'] == $toDate) {
                                $report_array[$row['screen_id']]['disburse']['total'] += 1;
                                $total_array['disburse']['total'] += 1;
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($report_array);
                // print_r($panchsheel_count);
                // exit;

                $data = '<table class="bordered"><tr><th colspan="2"  class="footer-tabels-text" style="text-align:center !important; width:13%;">&nbsp;</th>';
                $data .= '<th colspan="4"  class="footer-tabels-text" style="text-align:center !important; width:12%;">Today Producitivity - ' . date('d-M-Y', strtotime($to_date)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th>';
                $data .= '<th colspan="4"  class="footer-tabels-text" style="text-align:center !important; width:12%;">MTD Producitivity - ' . date('M-Y', strtotime($to_date)) . '</th></tr>';
                $data .= '<tr><th rowspan="2"  class="no-of-case" style="text-align:center !important; width:5%;">Team</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Daily Sanction Report</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Screened Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Sanctioned Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Cases Disbursed Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">% Conversion Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Screened MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Sanctioned MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Cases Disbursed MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">% Conversion MTD</th></tr>';
                $data .= '<tr><th  class="no-of-case" style="text-align:center !important; width:9.5%;">Name Of Executive</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Fresh MTD</th></tr>';

                $data .= '<tr><td rowspan="' . $panchsheel_count . '" class="footer-tabels-text" style="text-align:center;"><strong>Panchseel</strong></td></tr>';

                foreach ($report_array as $row) {
                    // $data .= '<tr><td>1</td>';
                    if ($row['team_id'] == 0) {
                        $data .= '<tr><td>' . ($row['name']) . '</td>';
                        $data .= '<td>' . (($row['screen']['total']) > 0 ? ($row['screen']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['total']) > 0 ? ($row['sanction']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total']) > 0 ? ($row['disburse']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total'] / $row['screen']['total']) > 0 ? number_format(($row['disburse']['total'] / $row['screen']['total']) * 100, 2) . "%" : "-") . '</t(d>';
                        $data .= '<td>' . (($row['screen']['mtd_total']) > 0 ? ($row['screen']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['mtd_total']) > 0 ? ($row['sanction']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total']) > 0 ? ($row['disburse']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) > 0 ? number_format(($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td></tr>';
                    }
                }

                $data .= '<tr><td rowspan="' . $jsola_count . '" class="footer-tabels-text" style="text-align:center;"><strong>Jasola</strong></td></tr>';

                foreach ($report_array as $row) {
                    // $data .= '<tr><td>1</td>';
                    if ($row['team_id'] == 1) {
                        $data .= '<tr><td>' . ($row['name']) . '</td>';
                        $data .= '<td>' . (($row['screen']['total']) > 0 ? ($row['screen']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['total']) > 0 ? ($row['sanction']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total']) > 0 ? ($row['disburse']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total'] / $row['screen']['total']) > 0 ? number_format(($row['disburse']['total'] / $row['screen']['total']) * 100, 2) . "%" : "-") . '</t(d>';
                        $data .= '<td>' . (($row['screen']['mtd_total']) > 0 ? ($row['screen']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['mtd_total']) > 0 ? ($row['sanction']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total']) > 0 ? ($row['disburse']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) > 0 ? number_format(($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td></tr>';
                    }
                }

                $data .= '<tr><td colspan="2" class="footer-tabels-text" style="text-align:center;">Total</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['screen']['total']) > 0 ? ($total_array['screen']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['sanction']['total']) > 0 ? ($total_array['sanction']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['total']) > 0 ? ($total_array['disburse']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['total'] / $total_array['screen']['total']) > 0 ? number_format(($total_array['disburse']['total'] / $total_array['screen']['total']) * 100, 2) . "%" : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['screen']['mtd_total']) > 0 ? ($total_array['screen']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['sanction']['mtd_total']) > 0 ? ($total_array['sanction']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['mtd_total']) > 0 ? ($total_array['disburse']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['mtd_total'] / $total_array['screen']['mtd_total']) > 0 ? number_format(($total_array['disburse']['mtd_total'] / $total_array['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function SanctionProductivityRepeat($report_id, $to_date)
    {

        if (!empty($report_id) && !empty($to_date)) {
            $fromDate = date('Y-m-01', strtotime($to_date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $q = "SELECT LD.lead_id, LD.lead_entry_date, LD.created_on, LD.lead_rejected_reason_id, LD.lead_status_id, LD.lead_screener_assign_datetime, LD.status, screen.name as screen_name, LD.lead_screener_assign_user_id as screen_id, sanction.name as sanction_name, sanction.user_id as sanction_id, sanction_approve.name as sanction_approve_name, sanction_approve.user_id as sanction_approve_id, disburse.name as disburse_name, disburse.user_id as disburse_id, CAM.disbursal_date, LD.lead_credit_approve_datetime, LD.lead_final_disbursed_date ";
            $q .= "FROM leads LD LEFT JOIN users screen ON(LD.lead_screener_assign_user_id=screen.user_id) LEFT JOIN users sanction ON(LD.lead_credit_assign_user_id=sanction.user_id) LEFT JOIN users sanction_approve ON(LD.lead_disbursal_approve_user_id=sanction_approve.user_id) LEFT JOIN users disburse ON(LD.lead_disbursal_approve_user_id=disburse.user_id) LEFT JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id) ";
            $q .= "WHERE LD.lead_active = 1 AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date!='' AND LD.user_type='REPEAT' AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' ORDER BY screen_name ASC;";

            $result = $this->db->query($q)->result_array();

            if (!empty($result)) {

                $report_array = array();
                $total_array = array();
                $jsola_count = 1;
                $panchsheel_count = 1;
                $team_data = [258, 259, 260, 257, 180, 51];

                foreach ($result as $row) {
                    if ($row['screen_id'] > 0 && $row['lead_entry_date'] <= date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) && date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) <= $toDate) {

                        if (in_array($row['screen_id'], $team_data)) { // 1 for jsola, 0 for panchsheel
                            if (!isset($report_array[$row['screen_id']])) {
                                $jsola_count += 1;
                            }
                            $report_array[$row['screen_id']]['team_id'] = 1;
                        } else {
                            if (!isset($report_array[$row['screen_id']])) {
                                $panchsheel_count += 1;
                            }
                            $report_array[$row['screen_id']]['team_id'] = 0;
                        }
                        $report_array[$row['screen_id']]['name'] = $row['screen_name'];
                        $report_array[$row['screen_id']]['screen']['mtd_total'] += 1;
                        $total_array['screen']['mtd_total'] += 1;
                        if ($row['sanction_approve_id'] > 0 && date('Y-m-d', strtotime($row['lead_credit_approve_datetime'])) <= $toDate) {
                            $report_array[$row['screen_id']]['sanction']['mtd_total'] += 1;
                            $total_array['sanction']['mtd_total'] += 1;
                        }
                        if ($row['disburse_id'] > 0 && $row['disbursal_date'] <= $toDate && $row['lead_status_id'] != 40) {
                            $report_array[$row['screen_id']]['disburse']['mtd_total'] += 1;
                            $total_array['disburse']['mtd_total'] += 1;
                        }
                    }
                    if (date('Y-m-d', strtotime($row['lead_screener_assign_datetime'])) == $toDate && $row['lead_entry_date'] == $toDate) {
                        if (!empty($row['screen_id'])) {
                            if (in_array($row['screen_id'], $team_data)) { // 1 for jsola, 0 for panchsheel
                                if (!isset($report_array[$row['screen_id']])) {
                                    $jsola_count += 1;
                                }
                                $report_array[$row['screen_id']]['team_id'] = 1;
                            } else {
                                if (!isset($report_array[$row['screen_id']])) {
                                    $panchsheel_count += 1;
                                }
                                $report_array[$row['screen_id']]['team_id'] = 0;
                            }
                            $report_array[$row['screen_id']]['name'] = $row['screen_name'];
                            $report_array[$row['screen_id']]['screen']['total'] += 1;
                            $total_array['screen']['total'] += 1;
                            if (date('Y-m-d', strtotime($row['lead_credit_approve_datetime'])) == $toDate) {
                                $report_array[$row['screen_id']]['sanction']['total'] += 1;
                                $total_array['sanction']['total'] += 1;
                            }
                            if ($row['lead_status_id'] != 40 && $row['lead_final_disbursed_date'] == $toDate) {
                                $report_array[$row['screen_id']]['disburse']['total'] += 1;
                                $total_array['disburse']['total'] += 1;
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($report_array);
                // print_r($panchsheel_count);
                // exit;

                $data = '<table class="bordered"><tr><th colspan="2"  class="footer-tabels-text" style="text-align:center !important; width:13%;">&nbsp;</th>';
                $data .= '<th colspan="4"  class="footer-tabels-text" style="text-align:center !important; width:12%;">Today Producitivity - ' . date('d-M-Y', strtotime($to_date)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th>';
                $data .= '<th colspan="4"  class="footer-tabels-text" style="text-align:center !important; width:12%;">MTD Producitivity - ' . date('M-Y', strtotime($to_date)) . '</th></tr>';
                $data .= '<tr><th rowspan="2"  class="no-of-case" style="text-align:center !important; width:5%;">Team</th>';
                // $data .= '<th rowspan="2"  class="no-of-case" style="text-align:center !important; width:5%;">Ranking</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Daily Sanction Report</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Screened Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Sanctioned Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Cases Disbursed Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">% Conversion Today</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Screened MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Lead Sanctioned MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Cases Disbursed MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">% Conversion MTD</th></tr>';
                $data .= '<tr><th  class="no-of-case" style="text-align:center !important; width:9.5%;">Name Of Executive</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat MTD</th>';
                $data .= '<th  class="no-of-case" style="text-align:center !important; width:9.5%;">Repeat MTD</th></tr>';

                $data .= '<tr><td rowspan="' . $panchsheel_count . '" class="footer-tabels-text" style="text-align:center;"><strong>Panchseel</strong></td></tr>';

                foreach ($report_array as $row) {
                    // $data .= '<tr><td>1</td>';
                    if ($row['team_id'] == 0) {
                        $data .= '<tr><td>' . ($row['name']) . '</td>';
                        $data .= '<td>' . (($row['screen']['total']) > 0 ? ($row['screen']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['total']) > 0 ? ($row['sanction']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total']) > 0 ? ($row['disburse']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total'] / $row['screen']['total']) > 0 ? number_format(($row['disburse']['total'] / $row['screen']['total']) * 100, 2) . "%" : "-") . '</t(d>';
                        $data .= '<td>' . (($row['screen']['mtd_total']) > 0 ? ($row['screen']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['mtd_total']) > 0 ? ($row['sanction']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total']) > 0 ? ($row['disburse']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) > 0 ? number_format(($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td></tr>';
                    }
                }

                $data .= '<tr><td rowspan="' . $jsola_count . '" class="footer-tabels-text" style="text-align:center;"><strong>Jasola</strong></td></tr>';

                foreach ($report_array as $row) {
                    // $data .= '<tr><td>1</td>';
                    if ($row['team_id'] == 1) {
                        $data .= '<tr><td>' . ($row['name']) . '</td>';
                        $data .= '<td>' . (($row['screen']['total']) > 0 ? ($row['screen']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['total']) > 0 ? ($row['sanction']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total']) > 0 ? ($row['disburse']['total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['total'] / $row['screen']['total']) > 0 ? number_format(($row['disburse']['total'] / $row['screen']['total']) * 100, 2) . "%" : "-") . '</t(d>';
                        $data .= '<td>' . (($row['screen']['mtd_total']) > 0 ? ($row['screen']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['sanction']['mtd_total']) > 0 ? ($row['sanction']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total']) > 0 ? ($row['disburse']['mtd_total']) : "-") . '</td>';
                        $data .= '<td>' . (($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) > 0 ? number_format(($row['disburse']['mtd_total'] / $row['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td></tr>';
                    }
                }

                $data .= '<tr><td colspan="2" class="footer-tabels-text" style="text-align:center;">Total</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['screen']['total']) > 0 ? ($total_array['screen']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['sanction']['total']) > 0 ? ($total_array['sanction']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['total']) > 0 ? ($total_array['disburse']['total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['total'] / $total_array['screen']['total']) > 0 ? number_format(($total_array['disburse']['total'] / $total_array['screen']['total']) * 100, 2) . "%" : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['screen']['mtd_total']) > 0 ? ($total_array['screen']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['sanction']['mtd_total']) > 0 ? ($total_array['sanction']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['mtd_total']) > 0 ? ($total_array['disburse']['mtd_total']) : "-") . '</td>';
                $data .= '<td class="footer-tabels-text">' . (($total_array['disburse']['mtd_total'] / $total_array['screen']['mtd_total']) > 0 ? number_format(($total_array['disburse']['mtd_total'] / $total_array['screen']['mtd_total']) * 100, 2) . "%" : "-") . '</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function PaymentAnalysis($report_id, $financial_year)
    {
        if (!empty($report_id) && !empty($financial_year)) {
            $fromDate = date('Y-m-01', strtotime($financial_year));
            $toDate = date("Y-m-t", strtotime('+11 months', strtotime($fromDate)));

            $q = "SELECT LD.lead_id, CAM.disbursal_date, CAM.repayment_date, CAM.admin_fee, CAM.loan_recommended, CAM.repayment_amount ";
            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND L.loan_status_id=14 AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' ORDER BY CAM.repayment_date, CAM.disbursal_date ASC;";

            $result = $this->db->query($q)->result_array();

            $sql = "SELECT COL.lead_id, COL.date_of_recived, COL.received_amount, CAM.disbursal_date, CAM.repayment_date FROM collection COL INNER JOIN credit_analysis_memo CAM ON(COL.lead_id=CAM.lead_id) WHERE COL.lead_id=CAM.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' ORDER BY CAM.disbursal_date, COL.date_of_recived ASC;";

            $collection_result = $this->db->query($sql)->result_array();

            if (!empty($result)) {

                $report_array = array();
                $collection_array = array();
                $total_array = array();
                $due_month = array();

                for ($i = 0; $i <= 11; $i++) {
                    $dis_mnth = date('M-y', strtotime("$i months", strtotime($fromDate)));
                    for ($j = 0; $j <= 13; $j++) {
                        $due_mnth = date('M-y', strtotime("$j months", strtotime($fromDate)));
                        $report_array[$dis_mnth][$due_mnth] = array();
                        $due_month[$dis_mnth][$j] = $due_mnth;
                    }
                }

                // echo "<pre>";
                // print_r($due_month);
                // exit;

                $k = 0;
                foreach ($collection_result as $row) {
                    $disbursal_month = date('M-y', strtotime($row['disbursal_date']));
                    $repayment_month = date('M-y', strtotime($row['repayment_date']));
                    $date_of_recived = date('M-y', strtotime($row['date_of_recived']));

                    $collection_array[$disbursal_month][$date_of_recived][$repayment_month] += $row['received_amount'];
                    $collection_array[$disbursal_month][$date_of_recived]['total_received'] += $row['received_amount'];
                    $total_array[$date_of_recived]['total_received'] += $row['received_amount'];
                    $k++;
                }


                foreach ($result as $row) {
                    $disbursal_month = date('M-y', strtotime($row['disbursal_date']));
                    $repayment_month = date('M-y', strtotime($row['repayment_date']));

                    $report_array[$disbursal_month]['disbursal_month'] = $disbursal_month;
                    $report_array[$disbursal_month][$repayment_month]['count'] += 1;
                    $report_array[$disbursal_month][$repayment_month]['repay_amount'] += $row['repayment_amount'];
                    $report_array[$disbursal_month]['total_loan'] += $row['repayment_amount'];
                    $total_array[$repayment_month]['repay_amount'] += $row['repayment_amount'];
                }

                foreach ($report_array as $data) {
                    $disbursal_month = $data['disbursal_month'];
                    foreach (array_keys($data) as $row) {
                        $repayment_month = $row;
                        if (isset($collection_array[$disbursal_month][$repayment_month])) {
                            $report_array[$disbursal_month]['repay_data'][$repayment_month] = $collection_array[$disbursal_month][$repayment_month];
                            $report_array[$disbursal_month]['total_repayment'] += $collection_array[$disbursal_month][$repayment_month]['total_received'];
                        }
                    }
                }

                // echo "<pre>";
                // print_r($total_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="17" class="footer-tabels-text" style="text-align: center">Payment Analysis - NAMAN</th></tr></thead>';
                $data .= '<tr class="sec-header">';
                $data .= '<th class="no-of-case">Disbursal / Due Month</th>';
                foreach ($due_month[date('M-y', strtotime($fromDate))] as $key) {
                    $data .= '<th class="no-of-case">' . $key . '</th>';
                }
                $data .= '<th class="no-of-case">Total Repay Amt</th>';
                $data .= '<th class="no-of-case">Total Rcvd Amt</th>';
                $data .= '</tr>';

                foreach ($report_array as $dis_month => $row) {
                    if ($dis_month == date('M-y', strtotime('+1 months', strtotime(date('d-m-Y'))))) {
                        break;
                    }

                    $data .= '<tr>';
                    $data .= '<th class="disburse-green">' . $row['disbursal_month'] . '</th>';
                    foreach ($due_month[date('M-y', strtotime($fromDate))] as $key) {
                        $data .= '<th class="disburse-green">' . ($row[$key]['repay_amount'] > 0 ? number_format($row[$key]['repay_amount'], 0) : "-") . '</th>';
                    }
                    $data .= '<th class="disburse-green">' . number_format($row['total_loan'], 0) . '</th>';
                    $data .= '<th class="disburse-green">' . number_format($row['total_repayment'], 0) . '</th>';
                    $data .= '</tr>';


                    $repay_data = $row['repay_data'];
                    foreach ($repay_data as $recovery_month => $repay_month) {
                        $data .= '<tr>';
                        $data .= '<td width="7.5%">' . $recovery_month . '</td>';
                        foreach ($due_month[date('M-y', strtotime($fromDate))] as $key) {
                            $data .= '<td width="5.5%">' . ($repay_month[$key] > 0 ? number_format($repay_month[$key], 0) : "-") . '</td>';
                        }
                        $data .= '<td width="5.5%">-</td>';
                        $data .= '<td width="5.5%">' . number_format($repay_month['total_received'], 0) . '</td>';
                        $data .= '</tr>';
                    }
                }

                $data .= '<tr><td class="footer-tabels-text">Total Repay</td>';
                foreach ($due_month[date('M-y', strtotime($fromDate))] as $key) {
                    $data .= '<td class="footer-tabels-text">' . ($total_array[$key]['repay_amount'] > 0 ? number_format($total_array[$key]['repay_amount'], 0) : "-") . '</td>';
                }
                $data .= '<td class="footer-tabels-text">-</td>';
                $data .= '<td class="footer-tabels-text">-</td>';
                $data .= '</tr>';

                $data .= '<tr><td class="footer-tabels-text">Total Received</td>';
                foreach ($due_month[date('M-y', strtotime($fromDate))] as $key) {
                    $data .= '<td class="footer-tabels-text">' . ($total_array[$key]['total_received'] > 0 ? number_format($total_array[$key]['total_received'], 0) : "-") . '</td>';
                }
                $data .= '<td class="footer-tabels-text">-</td>';
                $data .= '<td class="footer-tabels-text">-</td>';
                $data .= '</tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function DateWiseCollectionModel($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));
            if (strtotime(date('Y-m-d')) < strtotime($toDate)) {
                $toDate = date("Y-m-d", strtotime('-1 days', strtotime(date('d-m-Y'))));
            }

            $result = $this->PreCollectionCalculationModel($fromDate, $toDate);

            if (!empty($result)) {

                $report_array = array();
                $total = 0;
                $loan_recommended = 0;
                $coll_total_collection = 0;
                $prnl_rcvd = 0;
                $int_rcvd = 0;
                $penal_rcvd = 0;
                $int_outstanding = 0;
                $prnl_outstanding = 0;
                $penal_outstanding = 0;
                $collection_payable = 0;
                $pre_prnl_rcvd = 0;

                foreach ($result as $row) {

                    if (strtotime($row['disbursal']['repayment_date']) <= strtotime('-1 days', strtotime(date('Y-m-d')))) {

                        $total += 1;
                        $repayment_date = $row['disbursal']['repayment_date'];
                        $loan_recommended += $row['disbursal']['loan_recommended'];
                        $coll_total_collection += $row['collection']['total_collection'];
                        $int_rcvd += $row['collection']['int_rcvd'];
                        $prnl_rcvd += $row['collection']['prnl_rcvd'];
                        $penal_rcvd += $row['collection']['penal_rcvd'];
                        $int_outstanding += $row['collection']['int_outstanding'];
                        $prnl_outstanding += $row['collection']['prnl_outstanding'];
                        $penal_outstanding += $row['collection']['penal_outstanding'];
                        $collection_payable += $row['collection']['collection_payable'];
                        $pre_prnl_rcvd += $row['pre_collection']['prnl_rcvd'];;

                        $report_array[$repayment_date]['count'] += 1;
                        $report_array[$repayment_date]['repayment_date'] = $repayment_date;
                        $report_array[$repayment_date]['loan_recommended'] += $row['disbursal']['loan_recommended'];
                        $report_array[$repayment_date]['collection_payable'] += $row['collection']['collection_payable'];
                        $report_array[$repayment_date]['coll_total_collection'] += $row['collection']['total_collection'];
                        $report_array[$repayment_date]['int_rcvd'] += $row['collection']['int_rcvd'];
                        $report_array[$repayment_date]['pre_prnl_rcvd'] += $row['pre_collection']['prnl_rcvd'];
                        $report_array[$repayment_date]['prnl_rcvd'] += $row['collection']['prnl_rcvd'];
                        $report_array[$repayment_date]['penal_rcvd'] += $row['collection']['penal_rcvd'];
                        $report_array[$repayment_date]['int_outstanding'] += $row['collection']['int_outstanding'];
                        $report_array[$repayment_date]['prnl_outstanding'] += $row['collection']['prnl_outstanding'];
                        $report_array[$repayment_date]['penal_outstanding'] += $row['collection']['penal_outstanding'];
                    }
                }

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="13" class="footer-tabels-text">Collection (Upto 10 DPD) - Collection Report As on ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th colspan="3" align="left" class="no-of-case">Loan</th>';
                $data .= '<th colspan="10" align="left" class="no-of-case">Collection Bucket</th></tr>';
                $data .= '<tr class="thr-header">';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Date</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">No. of Applications</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Loan Amount</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Principal Payable Amount</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Interest Received</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Principal Received</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Penal Received</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Interest Outstanding</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Principal Outstanding</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Penal Outstanding</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Pre-Collection Principal%</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Collection Principal%</th>';
                $data .= '<th width="8.5%" align="left" class="no-of-case">Principal Default%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $col) {

                    $data .= '<tr>';
                    $data .= '<td>' . date('d-m-Y', strtotime($col['repayment_date'])) . '</td>';
                    $data .= '<td>' . $col['count'] . '</td>';
                    $data .= '<td>' . number_format($col['loan_recommended'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['collection_payable'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['penal_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_outstanding'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_outstanding'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['penal_outstanding'], 2) . '</td>';
                    $data .= '<td>' . (number_format(($col['pre_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['pre_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_recommended, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($collection_payable, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($penal_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($penal_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($pre_prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_outstanding / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function DateWiseRecoveryModel($report_id, $month_data)
    {
        if (!empty($report_id) && !empty($month_data)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));
            if (strtotime(date('Y-m-d')) < strtotime($toDate)) {
                $toDate = date("Y-m-d", strtotime('-10 days', strtotime(date('d-m-Y'))));
            }

            $result = $this->PreCollectionCalculationModel($fromDate, $toDate);

            if (!empty($result)) {

                $report_array = array();
                $total = 0;
                $loan_recommended = 0;
                $coll_total_collection = 0;
                $prnl_rcvd = 0;
                $int_rcvd = 0;
                $penal_rcvd = 0;
                $int_outstanding = 0;
                $prnl_outstanding = 0;
                $penal_outstanding = 0;
                $recovery_payable = 0;
                $collection_prnl_rcvd = 0;
                $pre_collection_prnl_rcvd = 0;

                foreach ($result as $row) {

                    if (strtotime($row['disbursal']['repayment_date']) < strtotime('-10 days', strtotime(date('Y-m-d')))) {

                        $total += 1;
                        $repayment_date = $row['disbursal']['repayment_date'];
                        $loan_recommended += $row['disbursal']['loan_recommended'];
                        $coll_total_collection += $row['recovery']['total_collection'];
                        $int_rcvd += $row['recovery']['int_rcvd'];
                        $prnl_rcvd += $row['recovery']['prnl_rcvd'];
                        $penal_rcvd += $row['recovery']['penal_rcvd'];
                        $int_outstanding += $row['recovery']['int_outstanding'];
                        $prnl_outstanding += $row['recovery']['prnl_outstanding'];
                        $penal_outstanding += $row['recovery']['penal_outstanding'];
                        $recovery_payable += $row['recovery']['recovery_payable'];
                        $collection_prnl_rcvd += $row['collection']['prnl_rcvd'];
                        $pre_collection_prnl_rcvd += $row['pre_collection']['prnl_rcvd'];

                        $report_array[$repayment_date]['count'] += 1;
                        $report_array[$repayment_date]['repayment_date'] = $repayment_date;
                        $report_array[$repayment_date]['loan_recommended'] += $row['disbursal']['loan_recommended'];
                        $report_array[$repayment_date]['recovery_amount'] += $row['recovery']['recovery_payable'];
                        $report_array[$repayment_date]['coll_total_collection'] += $row['recovery']['total_collection'];
                        $report_array[$repayment_date]['int_rcvd'] += $row['recovery']['int_rcvd'];
                        $report_array[$repayment_date]['collection_prnl_rcvd'] += $row['collection']['prnl_rcvd'];
                        $report_array[$repayment_date]['pre_collection_prnl_rcvd'] += $row['pre_collection']['prnl_rcvd'];
                        $report_array[$repayment_date]['prnl_rcvd'] += $row['recovery']['prnl_rcvd'];
                        $report_array[$repayment_date]['penal_rcvd'] += $row['recovery']['penal_rcvd'];
                        $report_array[$repayment_date]['int_outstanding'] += $row['recovery']['int_outstanding'];
                        $report_array[$repayment_date]['prnl_outstanding'] += $row['recovery']['prnl_outstanding'];
                        $report_array[$repayment_date]['penal_outstanding'] += $row['recovery']['penal_outstanding'];
                    }
                }

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="14" class="footer-tabels-text">Collection (After 10 DPD) - Recovery Report As on ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th colspan="3" align="left" class="no-of-case">Loan</th>';
                $data .= '<th colspan="11" align="left" class="no-of-case">Recovery Bucket</th></tr>';
                $data .= '<tr class="thr-header">';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Date</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">No. of Applications</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Loan Amount</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Principal Payable Amount</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Interest Received</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Principal Received</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Penal Received</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Interest Outstanding</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Principal Outstanding</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Penal Outstanding</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Pre-Collection Principal%</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Collection Principal%</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Recovery Principal%</th>';
                $data .= '<th width="7.15%" align="left" class="no-of-case">Principal Default%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $col) {

                    $data .= '<tr>';
                    $data .= '<td>' . date('d-m-Y', strtotime($col['repayment_date'])) . '</td>';
                    $data .= '<td>' . $col['count'] . '</td>';
                    $data .= '<td>' . number_format($col['loan_recommended'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['recovery_amount'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['penal_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_outstanding'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_outstanding'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['penal_outstanding'], 2) . '</td>';
                    $data .= '<td>' . (number_format(($col['pre_collection_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['pre_collection_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['collection_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['collection_prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_recommended, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($recovery_payable, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($penal_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($penal_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($pre_collection_prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($collection_prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_outstanding / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function DateWisePreCollectionModel($report_id, $month_data)
    {
        if (!empty($report_id)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));
            if (strtotime(date('Y-m-d')) < strtotime($toDate)) {
                $toDate = date("Y-m-d", strtotime('+5 days', strtotime(date('Y-m-d'))));
            }

            $result = $this->PreCollectionCalculationModel($fromDate, $toDate);


            if (!empty($result)) {

                $report_array = array();
                $total = 0;
                $loan_recommended = 0;
                $coll_total_collection = 0;
                $prnl_rcvd = 0;
                $int_rcvd = 0;
                $penal_rcvd = 0;
                $int_outstanding = 0;
                $prnl_outstanding = 0;

                foreach ($result as $row) {

                    $total += 1;
                    $repayment_date = $row['disbursal']['repayment_date'];
                    $loan_recommended += $row['disbursal']['loan_recommended'];
                    $coll_total_collection += $row['pre_collection']['total_collection'];
                    $int_rcvd += $row['pre_collection']['int_rcvd'];
                    $prnl_rcvd += $row['pre_collection']['prnl_rcvd'];
                    $penal_rcvd += $row['pre_collection']['penal_rcvd'];

                    $report_array[$repayment_date]['count'] += 1;
                    $report_array[$repayment_date]['repayment_date'] = $repayment_date;
                    $report_array[$repayment_date]['loan_recommended'] += $row['disbursal']['loan_recommended'];
                    $report_array[$repayment_date]['coll_total_collection'] += $row['pre_collection']['total_collection'];
                    $report_array[$repayment_date]['int_rcvd'] += $row['pre_collection']['int_rcvd'];
                    $report_array[$repayment_date]['prnl_rcvd'] += $row['pre_collection']['prnl_rcvd'];

                    if ($repayment_date < date('Y-m-d')) {
                        $int_outstanding += $row['pre_collection']['int_outstanding'];
                        $prnl_outstanding += $row['pre_collection']['prnl_outstanding'];
                        $report_array[$repayment_date]['int_outstanding'] += $row['pre_collection']['int_outstanding'];
                        $report_array[$repayment_date]['prnl_outstanding'] += $row['pre_collection']['prnl_outstanding'];
                    }
                }

                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="9" class="footer-tabels-text">Collection (Till Repayment Date) - Pre-Collection Report As on ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th colspan="3" align="left" class="no-of-case">Loan</th>';
                $data .= '<th colspan="8" align="left" class="no-of-case">Pre Collection Bucket</th></tr>';
                $data .= '<tr class="thr-header">';
                $data .= '<th width="12%" align="left" class="no-of-case">Date</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">No. of Applications</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Loan Amount</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Interest Received</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Principal Received</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Interest Outstanding</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Principal Outstanding</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Pre-Collection Principal%</th>';
                $data .= '<th width="11.5%" align="left" class="no-of-case">Principal Default%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $col) {

                    $data .= '<tr>';
                    $data .= '<td>' . date('d-m-Y', strtotime($col['repayment_date'])) . '</td>';
                    $data .= '<td>' . $col['count'] . '</td>';
                    $data .= '<td>' . number_format($col['loan_recommended'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_rcvd'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['int_outstanding'], 2) . '</td>';
                    $data .= '<td>' . number_format($col['prnl_outstanding'], 2) . '</td>';
                    $data .= '<td>' . (number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_rcvd'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '<td>' . (number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) > 0 ? number_format(($col['prnl_outstanding'] / $col['loan_recommended']) * 100, 4) : 0) . '%</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_recommended, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_rcvd, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($int_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($prnl_outstanding, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_rcvd / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($prnl_outstanding / $loan_recommended) * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadStatusSanctionWiseNewModel($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));
            // if (strtotime(date('Y-m-d')) < strtotime($toDate)) {
            //     $toDate = date("Y-m-d");
            // }

            $sql = "SELECT U.name, LD.lead_status_id, LD.lead_rejected_reason_id, MS.status_name, LD.user_type, LD.lead_screener_assign_user_id, LD.lead_screener_recommend_datetime, LD.lead_credit_assign_user_id, LD.lead_credithead_assign_user_id, LD.lead_disbursal_approve_user_id, LD.lead_final_disbursed_date, LD.lead_disbursal_assign_user_id, LD.lead_disbursal_recommend_datetime, LD.lead_disbursal_approve_user_id ";
            $sql .= "FROM leads LD LEFT JOIN users U ON(LD.lead_screener_assign_user_id=U.user_id) INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) WHERE LD.lead_status_id=MS.status_id AND LD.lead_active=1 AND LD.user_type='NEW' AND LD.lead_entry_date >='$fromDate' AND LD.lead_entry_date <='$toDate' ORDER BY name ASC;";

            $result = $this->db->query($sql)->result_array();

            if (!empty($result)) {

                $report_array = array();
                $total = 0;
                $lead_in_process = 0;
                $lead_hold = 0;
                $lead_app_in_process = 0;
                $lead_app_hold = 0;
                $DUPLICATE = 0;
                $reject = 0;
                $app_send_back = 0;
                $sanction = 0;
                $disburse_pending = 0;
                $disburse_hold = 0;
                $disbursed = 0;
                $system_reject = 0;
                $lead_new = 0;
                $grand_total = 0;
                $lead_duplicate = 0;

                foreach ($result as $row) {


                    $name = $row['name'];
                    $user_type = $row['user_type'];
                    $lead_status_id = $row['lead_status_id'];
                    $status_name = $row['status_name'];

                    if (!empty($row['lead_screener_assign_user_id'])) {
                        $report_array[$name][$user_type]['grand_total'] += 1;
                    }
                    $grand_total += 1;

                    if (!empty($row['lead_screener_assign_user_id']) && !in_array($lead_status_id, [1, 14, 16, 17, 18, 19]) && $row['lead_rejected_reason_id'] != 42) {
                        $total += 1;
                        if ($lead_status_id == 2) {
                            $lead_in_process += 1;
                        }

                        if ($lead_status_id == 3) {
                            $lead_hold += 1;
                        }

                        if ($lead_status_id == 5) {
                            $lead_app_in_process += 1;
                        }

                        if ($lead_status_id == 6) {
                            $lead_app_hold += 1;
                        }

                        if ($lead_status_id == 7) {
                            $DUPLICATE += 1;
                        }

                        if ($lead_status_id == 9) {
                            $reject += 1;
                        }

                        if ($lead_status_id == 11) {
                            $app_send_back += 1;
                        }

                        if (in_array($lead_status_id, [12, 40])) {
                            $sanction += 1;
                        }

                        if ($lead_status_id == 13) {
                            $disburse_pending += 1;
                        }

                        if ($lead_status_id == 35) {
                            $disburse_hold += 1;
                        }


                        $report_array[$name]['name'] = $name;
                        $report_array[$name][$user_type]['total'] += 1;
                        $report_array[$name][$user_type][$status_name] += 1;
                    }

                    if (empty($row['lead_screener_assign_user_id']) && $lead_status_id == 7) {
                        $lead_duplicate += 1;
                    }

                    if (!empty($lead_status_id) && in_array($lead_status_id, [14, 16, 17, 18, 19])) {
                        $total += 1;
                        $disbursed += 1;
                        $report_array[$name]['name'] = $name;
                        $report_array[$name][$user_type]['total'] += 1;
                        $report_array[$name][$user_type]['DISBURSED'] += 1;
                    }


                    if (empty($row['lead_screener_assign_user_id']) && $lead_status_id == 9 && $row['lead_rejected_reason_id'] == 42) {
                        $system_reject += 1;
                    }

                    if ($lead_status_id == 1) {
                        $lead_new += 1;
                    }
                }

                $report_array['REJECT (SYSTEM)']['name'] = 'REJECT (SYSTEM)';
                $report_array['REJECT (SYSTEM)']['NEW']['REJECT'] = $system_reject;
                $report_array['REJECT (SYSTEM)']['NEW']['grand_total'] = $system_reject;
                $report_array['DUPLICATE']['name'] = 'DUPLICATE';
                $report_array['DUPLICATE']['NEW']['DUPLICATE'] = $lead_duplicate;
                $report_array['DUPLICATE']['NEW']['grand_total'] = $lead_duplicate;
                $report_array['LEAD-NEW']['name'] = 'LEAD-NEW';
                $report_array['LEAD-NEW']['NEW']['LEAD-NEW'] = $lead_new;
                $report_array['LEAD-NEW']['NEW']['grand_total'] = $lead_new;

                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="14" class="footer-tabels-text">Sanction Executive Wise Status Report (FRESH) ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr><th width="9%" align="left" class="no-of-case">Executive Name</th>';
                // $data .= '<th width="7%" align="left" class="no-of-case">USER-TYPE</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-NEW</th>';
                // $data .= '<th width="6.8%" align="left" class="no-of-case">TOTAL-SCREENED</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-INPROCESS</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-INPROCESS</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DUPLICATE</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">REJECT</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-SEND-BACK</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">SANCTION</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSE-PENDING</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSED-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSED</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">GRAND TOTAL</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $col) {

                    $data .= '<tr>';
                    $data .= '<td>' . $col['name'] . '</td>'; // rowspan="2" 
                    // $data .= '<td>NEW</td>';
                    $data .= '<td>' . ($col['NEW']['LEAD-NEW'] > 0 ? $col['NEW']['LEAD-NEW'] : "-") . '</td>';
                    // $data .= '<td>' . ($col['NEW']['total'] > 0 ? $col['NEW']['total'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['LEAD-INPROCESS'] > 0 ? $col['NEW']['LEAD-INPROCESS'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['LEAD-HOLD'] > 0 ? $col['NEW']['LEAD-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['APPLICATION-INPROCESS'] > 0 ? $col['NEW']['APPLICATION-INPROCESS'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['APPLICATION-HOLD'] > 0 ? $col['NEW']['APPLICATION-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['DUPLICATE'] > 0 ? $col['NEW']['DUPLICATE'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['REJECT'] > 0 ? $col['NEW']['REJECT'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['APPLICATION-SEND-BACK'] > 0 ? $col['NEW']['APPLICATION-SEND-BACK'] : "-") . '</td>';
                    $data .= '<td>' . (($col['NEW']['SANCTION'] + $col['NEW']['DISBURSED-WAIVED']) > 0 ? ($col['NEW']['SANCTION'] + $col['NEW']['DISBURSED-WAIVED']) : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['DISBURSE-PENDING'] > 0 ? $col['NEW']['DISBURSE-PENDING'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['DISBURSED-HOLD'] > 0 ? $col['NEW']['DISBURSED-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['DISBURSED'] > 0 ? $col['NEW']['DISBURSED'] : "-") . '</td>';
                    $data .= '<td>' . ($col['NEW']['grand_total'] > 0 ? $col['NEW']['grand_total'] : "-") . '</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                // $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_new . '</td>';
                // $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_in_process . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_app_in_process . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_app_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($DUPLICATE + $lead_duplicate) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($reject + $system_reject) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $app_send_back . '</td>';
                $data .= '<td class="footer-tabels-text">' . $sanction . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disburse_pending . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disburse_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disbursed . '</td>';
                $data .= '<td class="footer-tabels-text">' . $grand_total . '</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadStatusSanctionWiseRepeatModel($fromDate, $toDate)
    {
        if (!empty($fromDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));
            // if (strtotime(date('Y-m-d')) < strtotime($toDate)) {
            //     $toDate = date("Y-m-d");
            // }

            $sql = "SELECT U.name, LD.lead_status_id, LD.lead_rejected_reason_id, MS.status_name, LD.user_type, LD.lead_screener_assign_user_id, LD.lead_screener_recommend_datetime, LD.lead_credit_assign_user_id, LD.lead_credithead_assign_user_id, LD.lead_disbursal_approve_user_id, LD.lead_final_disbursed_date, LD.lead_disbursal_assign_user_id, LD.lead_disbursal_recommend_datetime, LD.lead_disbursal_approve_user_id ";
            $sql .= "FROM leads LD LEFT JOIN users U ON(LD.lead_screener_assign_user_id=U.user_id) INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) WHERE LD.lead_status_id=MS.status_id AND LD.lead_active=1 AND LD.user_type='REPEAT' AND LD.lead_entry_date >='$fromDate' AND LD.lead_entry_date <='$toDate' ORDER BY name ASC;";

            $result = $this->db->query($sql)->result_array();

            if (!empty($result)) {

                $report_array = array();
                $total = 0;
                $lead_in_process = 0;
                $lead_hold = 0;
                $lead_app_in_process = 0;
                $lead_app_hold = 0;
                $DUPLICATE = 0;
                $reject = 0;
                $app_send_back = 0;
                $sanction = 0;
                $disburse_pending = 0;
                $disburse_hold = 0;
                $disbursed = 0;
                $system_reject = 0;
                $lead_new = 0;
                $grand_total = 0;
                $lead_duplicate = 0;

                foreach ($result as $row) {


                    $name = $row['name'];
                    $user_type = $row['user_type'];
                    $lead_status_id = $row['lead_status_id'];
                    $status_name = $row['status_name'];

                    if (!empty($row['lead_screener_assign_user_id'])) {
                        $report_array[$name][$user_type]['grand_total'] += 1;
                    }
                    $grand_total += 1;

                    if (!empty($row['lead_screener_assign_user_id']) && !in_array($lead_status_id, [1, 14, 16, 17, 18, 19]) && $row['lead_rejected_reason_id'] != 42) {
                        $total += 1;
                        if ($lead_status_id == 2) {
                            $lead_in_process += 1;
                        }

                        if ($lead_status_id == 3) {
                            $lead_hold += 1;
                        }

                        if ($lead_status_id == 5) {
                            $lead_app_in_process += 1;
                        }

                        if ($lead_status_id == 6) {
                            $lead_app_hold += 1;
                        }

                        if ($lead_status_id == 7) {
                            $DUPLICATE += 1;
                        }

                        if ($lead_status_id == 9) {
                            $reject += 1;
                        }

                        if ($lead_status_id == 11) {
                            $app_send_back += 1;
                        }

                        if (in_array($lead_status_id, [12, 40])) {
                            $sanction += 1;
                        }

                        if ($lead_status_id == 13) {
                            $disburse_pending += 1;
                        }

                        if ($lead_status_id == 35) {
                            $disburse_hold += 1;
                        }

                        if ($lead_status_id == 7) {
                            $lead_duplicate += 1;
                        }

                        $report_array[$name]['name'] = $name;
                        $report_array[$name][$user_type]['total'] += 1;
                        $report_array[$name][$user_type][$status_name] += 1;
                    }

                    if (empty($row['lead_screener_assign_user_id']) && $lead_status_id == 7) {
                        $lead_duplicate += 1;
                    }

                    if (!empty($lead_status_id) && in_array($lead_status_id, [14, 16, 17, 18, 19])) {
                        $total += 1;
                        $disbursed += 1;
                        $report_array[$name]['name'] = $name;
                        $report_array[$name][$user_type]['total'] += 1;
                        $report_array[$name][$user_type]['DISBURSED'] += 1;
                    }


                    if ($lead_status_id == 9 && $row['lead_rejected_reason_id'] == 42) {
                        $system_reject += 1;
                    }

                    if ($lead_status_id == 1) {
                        $lead_new += 1;
                    }
                }

                $report_array['REJECT (SYSTEM)']['name'] = 'REJECT (SYSTEM)';
                $report_array['REJECT (SYSTEM)']['REPEAT']['REJECT'] = $system_reject;
                $report_array['REJECT (SYSTEM)']['REPEAT']['grand_total'] = $system_reject;
                $report_array['DUPLICATE']['name'] = 'DUPLICATE';
                $report_array['DUPLICATE']['REPEAT']['DUPLICATE'] = $lead_duplicate;
                $report_array['DUPLICATE']['REPEAT']['grand_total'] = $lead_duplicate;
                $report_array['LEAD-NEW']['name'] = 'LEAD-NEW';
                $report_array['LEAD-NEW']['REPEAT']['LEAD-NEW'] = $lead_new;
                $report_array['LEAD-NEW']['REPEAT']['grand_total'] = $lead_new;

                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="14" class="footer-tabels-text">Sanction Executive Wise Status Report (REPEAT) ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr><th width="9%" align="left" class="no-of-case">Executive Name</th>';
                // $data .= '<th width="7%" align="left" class="no-of-case">USER-TYPE</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-NEW</th>';
                // $data .= '<th width="6.8%" align="left" class="no-of-case">TOTAL-SCREENED</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-INPROCESS</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">LEAD-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-INPROCESS</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DUPLICATE</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">REJECT</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">APPLICATION-SEND-BACK</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">SANCTION</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSE-PENDING</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSED-HOLD</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">DISBURSED</th>';
                $data .= '<th width="6.8%" align="left" class="no-of-case">GRAND TOTAL</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $col) {

                    $data .= '<tr>';
                    $data .= '<td>' . $col['name'] . '</td>'; // rowspan="2" 
                    // $data .= '<td>NEW</td>';
                    $data .= '<td>' . ($col['REPEAT']['LEAD-NEW'] > 0 ? $col['REPEAT']['LEAD-NEW'] : "-") . '</td>';
                    // $data .= '<td>' . ($col['REPEAT']['total'] > 0 ? $col['REPEAT']['total'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['LEAD-INPROCESS'] > 0 ? $col['REPEAT']['LEAD-INPROCESS'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['LEAD-HOLD'] > 0 ? $col['REPEAT']['LEAD-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['APPLICATION-INPROCESS'] > 0 ? $col['REPEAT']['APPLICATION-INPROCESS'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['APPLICATION-HOLD'] > 0 ? $col['REPEAT']['APPLICATION-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['DUPLICATE'] > 0 ? $col['REPEAT']['DUPLICATE'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['REJECT'] > 0 ? $col['REPEAT']['REJECT'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['APPLICATION-SEND-BACK'] > 0 ? $col['REPEAT']['APPLICATION-SEND-BACK'] : "-") . '</td>';
                    $data .= '<td>' . (($col['REPEAT']['SANCTION'] + $col['REPEAT']['DISBURSED-WAIVED']) > 0 ? ($col['REPEAT']['SANCTION'] + $col['REPEAT']['DISBURSED-WAIVED']) : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['DISBURSE-PENDING'] > 0 ? $col['REPEAT']['DISBURSE-PENDING'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['DISBURSED-HOLD'] > 0 ? $col['REPEAT']['DISBURSED-HOLD'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['DISBURSED'] > 0 ? $col['REPEAT']['DISBURSED'] : "-") . '</td>';
                    $data .= '<td>' . ($col['REPEAT']['grand_total'] > 0 ? $col['REPEAT']['grand_total'] : "-") . '</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                // $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_new . '</td>';
                // $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_in_process . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_app_in_process . '</td>';
                $data .= '<td class="footer-tabels-text">' . $lead_app_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($DUPLICATE + $lead_duplicate) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($reject + $system_reject) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $app_send_back . '</td>';
                $data .= '<td class="footer-tabels-text">' . $sanction . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disburse_pending . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disburse_hold . '</td>';
                $data .= '<td class="footer-tabels-text">' . $disbursed . '</td>';
                $data .= '<td class="footer-tabels-text">' . $grand_total . '</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function HourlyStatusWiseModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT status, lead_status_id, TIME(created_on) as times, user_type, created_on, lead_entry_date FROM leads WHERE lead_entry_date >= '$fromDate' AND lead_entry_date <= '$toDate' AND lead_active=1 ORDER BY lead_status_id ASC;";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();
                $total_array = array();
                $total_new = 0;
                $total_repeat = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $c_time = strtotime($row['times']);
                        $status = $row['status'];

                        if ($c_time > strtotime('00:00') && $c_time <= strtotime('10:00')) {

                            $report_array[$status]['12:01 AM-10:00 AM']['times'] = '12:01 AM-10:00 AM';
                            $report_array[$status]['12:01 AM-10:00 AM']['counts'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['12:01 AM-10:00 AM']['NEW']['counts'] += 1;
                                $report_array[$status]['12:01 AM-10:00 AM']['NEW']['times'] = '12:01 AM-10:00 AM';
                                $report_array[$status]['12:01 AM-10:00 AM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[1]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['12:01 AM-10:00 AM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['12:01 AM-10:00 AM']['REPEAT']['times'] = '12:01 AM-10:00 AM';
                                $report_array[$status]['12:01 AM-10:00 AM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[1]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('10:00') && $c_time <= strtotime('12:00')) {

                            $report_array[$status]['10:01 AM-12:00 PM']['times'] = '10:01 AM-12:00 PM';
                            $report_array[$status]['10:01 AM-12:00 PM']['counts'] += 1;
                            $report_array[$status]['10:01 AM-12:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['10:01 AM-12:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['10:01 AM-12:00 PM']['NEW']['times'] = '10:01 AM-12:00 PM';
                                $report_array[$status]['10:01 AM-12:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[2]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['10:01 AM-12:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['10:01 AM-12:00 PM']['REPEAT']['times'] = '10:01 AM-12:00 PM';
                                $report_array[$status]['10:01 AM-12:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[2]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('12:00') && $c_time <= strtotime('14:00')) {

                            $report_array[$status]['12:01 PM-02:00 PM']['times'] = '12:01 PM-02:00 PM';
                            $report_array[$status]['12:01 PM-02:00 PM']['counts'] += 1;
                            $report_array[$status]['12:01 PM-02:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['12:01 PM-02:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['12:01 PM-02:00 PM']['NEW']['times'] = '12:01 PM-02:00 PM';
                                $report_array[$status]['12:01 PM-02:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[3]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['12:01 PM-02:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['12:01 PM-02:00 PM']['REPEAT']['times'] = '12:01 PM-02:00 PM';
                                $report_array[$status]['12:01 PM-02:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[3]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('14:00') && $c_time <= strtotime('16:00')) {

                            $report_array[$status]['02:01 PM-04:00 PM']['times'] = '02:01 PM-04:00 PM';
                            $report_array[$status]['02:01 PM-04:00 PM']['counts'] += 1;
                            $report_array[$status]['02:01 PM-04:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['02:01 PM-04:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['02:01 PM-04:00 PM']['NEW']['times'] = '02:01 PM-04:00 PM';
                                $report_array[$status]['02:01 PM-04:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[4]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['02:01 PM-04:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['02:01 PM-04:00 PM']['REPEAT']['times'] = '02:01 PM-04:00 PM';
                                $report_array[$status]['02:01 PM-04:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[4]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('16:00') && $c_time <= strtotime('18:00')) {

                            $report_array[$status]['04:01 PM-06:00 PM']['times'] = '04:01 PM-06:00 PM';
                            $report_array[$status]['04:01 PM-06:00 PM']['counts'] += 1;
                            $report_array[$status]['04:01 PM-06:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['04:01 PM-06:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['04:01 PM-06:00 PM']['NEW']['times'] = '04:01 PM-06:00 PM';
                                $report_array[$status]['04:01 PM-06:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[5]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['04:01 PM-06:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['04:01 PM-06:00 PM']['REPEAT']['times'] = '04:01 PM-06:00 PM';
                                $report_array[$status]['04:01 PM-06:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[5]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('18:00') && $c_time <= strtotime('20:00')) {

                            $report_array[$status]['06:01 PM-08:00 PM']['times'] = '06:01 PM-08:00 PM';
                            $report_array[$status]['06:01 PM-08:00 PM']['counts'] += 1;
                            $report_array[$status]['06:01 PM-08:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['06:01 PM-08:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['06:01 PM-08:00 PM']['NEW']['times'] = '06:01 PM-08:00 PM';
                                $report_array[$status]['06:01 PM-08:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[6]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['06:01 PM-08:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['06:01 PM-08:00 PM']['REPEAT']['times'] = '06:01 PM-08:00 PM';
                                $report_array[$status]['06:01 PM-08:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[6]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('20:00') && $c_time <= strtotime('22:00')) {

                            $report_array[$status]['08:01 PM-10:00 PM']['times'] = '08:01 PM-10:00 PM';
                            $report_array[$status]['08:01 PM-10:00 PM']['counts'] += 1;
                            $report_array[$status]['08:01 PM-10:00 PM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['08:01 PM-10:00 PM']['NEW']['counts'] += 1;
                                $report_array[$status]['08:01 PM-10:00 PM']['NEW']['times'] = '08:01 PM-10:00 PM';
                                $report_array[$status]['08:01 PM-10:00 PM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[7]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['08:01 PM-10:00 PM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['08:01 PM-10:00 PM']['REPEAT']['times'] = '08:01 PM-10:00 PM';
                                $report_array[$status]['08:01 PM-10:00 PM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[7]['REPEAT'] += 1;
                            }
                        } elseif ($c_time > strtotime('22:00') && $c_time <= strtotime('24:00')) {

                            $report_array[$status]['10:01 PM-12:00 AM']['times'] = '10:01 PM-12:00 AM';
                            $report_array[$status]['10:01 PM-12:00 AM']['counts'] += 1;
                            $report_array[$status]['10:01 PM-12:00 AM']['total'] += 1;

                            if ($row['user_type'] == 'NEW') {
                                $total_new += 1;
                                $report_array[$status]['10:01 PM-12:00 AM']['NEW']['counts'] += 1;
                                $report_array[$status]['10:01 PM-12:00 AM']['NEW']['times'] = '10:01 PM-12:00 AM';
                                $report_array[$status]['10:01 PM-12:00 AM']['NEW']['user_type'] = $row['user_type'];
                                $report_array[$status]['NEW']['total'] += 1;
                                $total_array[8]['NEW'] += 1;
                            }
                            if ($row['user_type'] == 'REPEAT') {
                                $total_repeat += 1;
                                $report_array[$status]['10:01 PM-12:00 AM']['REPEAT']['counts'] += 1;
                                $report_array[$status]['10:01 PM-12:00 AM']['REPEAT']['times'] = '10:01 PM-12:00 AM';
                                $report_array[$status]['10:01 PM-12:00 AM']['REPEAT']['user_type'] = $row['user_type'];
                                $report_array[$status]['REPEAT']['total'] += 1;
                                $total_array[8]['REPEAT'] += 1;
                            }
                        }
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr><th colspan="19" class="footer-tabels-text" style="text-align:center;">Hourly Status Wise Report – ' . date('d-m-Y', strtotime($fromDate)) . ' to ' . date('d-m-Y', strtotime($toDate)) . '  Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr></thead>';
                $data .= '<tr><th width="111" rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">12:01 AM-10:00 AM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">10:01 AM-12:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">12:01 PM-02:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">02:01 PM-04:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">04:01 PM-06:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">06:01 PM-08:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">08:01 PM-10:00 PM</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important;">10:01 PM-12:00 AM</th>';
                $data .= '<th colspan="2" class="no-of-case" style="text-align:center !important;">Total</th></tr>';
                $data .= '<tr><td  width="10%" class="no-of-case" style="text-align:center !important;"><strong>Lead Status</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat </strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Fresh</strong></td>';
                $data .= '<td width="5.5%" class="no-of-case" style="text-align:center !important;"><strong>Repeat </strong></td></tr>';

                $fresh_count = 0;
                $repeat_count = 0;
                $total_count = 0;
                $fresh_loan = 0;
                $repeat_loan = 0;
                $total_loan = 0;

                foreach (array_keys($report_array) as $key) {

                    $fresh_count += $report_array[$key]['NEW']['counts'];
                    $repeat_count += $report_array[$key]['REPEAT']['counts'];
                    $total_count += ($report_array[$key]['NEW']['counts'] + $report_array[$key]['REPEAT']['counts']);
                    $fresh_loan += $report_array[$key]['NEW']['loan_recommended'];
                    $repeat_loan += $report_array[$key]['REPEAT']['loan_recommended'];
                    $total_loan += ($report_array[$key]['NEW']['loan_recommended'] + $report_array[$key]['REPEAT']['loan_recommended']);

                    $data .= '<tr><td align="center" valign="middle" style="text-align:center;">' . $key . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['12:01 AM-10:00 AM']['NEW']['counts'] > 0 ? $report_array[$key]['12:01 AM-10:00 AM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['12:01 AM-10:00 AM']['REPEAT']['counts'] > 0 ? $report_array[$key]['12:01 AM-10:00 AM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['10:01 AM-12:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['10:01 AM-12:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['10:01 AM-12:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['10:01 AM-12:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['12:01 PM-02:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['12:01 PM-02:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['12:01 PM-02:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['12:01 PM-02:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['02:01 PM-04:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['02:01 PM-04:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['02:01 PM-04:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['02:01 PM-04:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['04:01 PM-06:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['04:01 PM-06:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['04:01 PM-06:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['04:01 PM-06:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['06:01 PM-08:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['06:01 PM-08:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['06:01 PM-08:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['06:01 PM-08:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['08:01 PM-10:00 PM']['NEW']['counts'] > 0 ? $report_array[$key]['08:01 PM-10:00 PM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['08:01 PM-10:00 PM']['REPEAT']['counts'] > 0 ? $report_array[$key]['08:01 PM-10:00 PM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['10:01 PM-12:00 AM']['NEW']['counts'] > 0 ? $report_array[$key]['10:01 PM-12:00 AM']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['10:01 PM-12:00 AM']['REPEAT']['counts'] > 0 ? $report_array[$key]['10:01 PM-12:00 AM']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['NEW']['total'] > 0 ? $report_array[$key]['NEW']['total'] : "-") . '</td>';
                    $data .= '<td align="center" valign="middle" style="text-align:center;">' . ($report_array[$key]['REPEAT']['total'] > 0 ? $report_array[$key]['REPEAT']['total'] : "-") . '</td></tr>';
                }

                $data .= '<tr><td align="center" valign="middle" class="footer-tabels-text">Grand Total </td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[1]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[1]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[2]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[2]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[3]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[3]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[4]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[4]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[5]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[5]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[6]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[6]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[7]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[7]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[8]['NEW'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . $total_array[8]['REPEAT'] . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . number_format($total_new, 0) . '</td>';
                $data .= '<td align="center" valign="middle" class="footer-tabels-text">' . number_format($total_repeat, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadUTMSourceStatusModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT LD.utm_source, MS.status_order as lead_status_id, status, COUNT(*) as total_count FROM leads LD INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) WHERE LD.lead_status_id=MS.status_id AND LD.lead_active=1 AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' GROUP BY utm_source, status ORDER BY lead_status_id, utm_source ASC";

            $result = $this->db->query($q)->result_array();

            if ($result) {

                $final_array = array();
                $header_values = array();
                $total_value = array();
                $total = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total += $row['total_count'];
                        $utm_source = strtoupper($row['utm_source']);
                        $final_array[$utm_source][$row['status']]['counts'] = $row['total_count'];
                        $final_array[$utm_source][$row['status']]['status'] = $row['status'];
                        $final_array[$utm_source][$row['status']]['utm_source'] = $utm_source;
                        $total_value['utm'][$utm_source] += $row['total_count'];
                        $total_value['status'][$row['status']] += $row['total_count'];
                        $header_values[$row['lead_status_id']] = $row['status'];
                        $i++;
                    }
                }

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan=' . $i . ' class="footer-tabels-text" style="text-align:center;">Lead UTM Source Wise Report - ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th width="0.2%" class="no-of-case">Sr_No.</th>';
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">Status / UTM Source</th>';
                ksort($header_values);
                foreach ($header_values as $header_key) {
                    $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">' . $header_key . '</th>';
                }
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">Total</th></tr>';

                $i = 1;
                foreach ($final_array as $key => $value) {
                    $data .= '<tr><td align="center" valign="middle">' . $i . '</td>';
                    $data .= '<td align="center" valign="middle">' . $key . '</td>';
                    foreach ($header_values as $header_key) {
                        $data .= '<td align="center" valign="middle">' . ($value[$header_key]['counts'] > 0 ? number_format($value[$header_key]['counts'], 0) : "-") . '</td>';
                    }
                    $data .= '<td align="center" valign="middle">' . $total_value['utm'][$key] . '</td></tr>';
                    $i++;
                }

                $data .= '<tr><td colspan="2" class="footer-tabels-text">Grand Total</td>';
                foreach ($header_values as $header_key) {
                    $data .= '<td class="footer-tabels-text">' . number_format($total_value['status'][$header_key], 0) . '</td>';
                }
                $data .= '<td class="footer-tabels-text">' . number_format($total, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function OutstandingReportAmountModel($report_id, $month_data)
    {
        if (!empty($month_data) && !empty($report_id)) {
            $fromDate = date('Y-m-01', strtotime($month_data));
            $toDate = date("Y-m-t", strtotime($fromDate));

            $collection_to_date = date('Y-m-d', strtotime($toDate));
            if ($collection_to_date >= date('Y-m-d')) {
                $toDate = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
            }

            $q = "SELECT LD.lead_id, U.name as sanction_executive, LD.lead_credit_assign_user_id, LD.status, LD.lead_status_id, LD.user_type, L.loan_no, CAM.loan_recommended, L.loan_principle_received_amount, L.loan_principle_outstanding_amount FROM leads LD INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";

            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_credit_assign_user_id=U.user_id AND LD.lead_id=CAM.lead_id AND LD.lead_active=1 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY U.name ASC";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();
                $total_array = array();
                $total = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {


                        $sanction_by = $row['sanction_executive'];
                        $user_type = $row['user_type'];
                        $total += $row['loan_recommended'];

                        $report_array[$sanction_by][$user_type]['name'] = $sanction_by;
                        $report_array[$sanction_by][$user_type]['counts'] += 1;
                        $report_array[$sanction_by]['total_amount'] += $row['loan_recommended'];
                        $report_array[$sanction_by][$user_type]['loan_recommended'] += $row['loan_recommended'];
                        $report_array[$sanction_by][$user_type]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                        $report_array[$sanction_by][$user_type]['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                        $total_array['loan'][$user_type] += $row['loan_recommended'];
                        $total_array['received'][$user_type] += $row['loan_principle_received_amount'];
                        $total_array['outstanding'][$user_type] += $row['loan_principle_outstanding_amount'];
                    }
                }


                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="13" class="footer-tabels-text">Outstanding Executive Amount Wise Report ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th rowspan="2" class="no-of-case">Sanction Executive</th>';
                $data .= '<th colspan="3" class="no-of-case">LOAN AMOUNT</th>';
                $data .= '<th colspan="3" class="no-of-case">PRINCIPAL RECEIVED</th>';
                $data .= '<th width="7.5%" colspan="3" class="no-of-case">ACTIVE POS</th>';
                $data .= '<th colspan="3" class="no-of-case">DEFAULT%</th></tr>';
                $data .= '<tr class="thr-header"><th width="8%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">OVERALL%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $key => $value) {

                    $data .= '<tr>';
                    $data .= '<td>' . $key . '</td>';
                    $data .= '<td>' . ($value['NEW']['loan_recommended'] > 0 ? number_format($value['NEW']['loan_recommended'], 2) : "-") . '</td>';
                    $data .= '<td>' . ($value['REPEAT']['loan_recommended'] > 0 ? number_format($value['REPEAT']['loan_recommended'], 2) : "-") . '</td>';
                    $data .= '<td>' . (($value['total_amount']) > 0 ? number_format($value['total_amount'], 2)  : "-") . '</td>';
                    $data .= '<td>' . ($value['NEW']['loan_principle_received_amount'] > 0 ? number_format($value['NEW']['loan_principle_received_amount'], 2) : "-") . '</td>';
                    $data .= '<td>' . ($value['REPEAT']['loan_principle_received_amount'] > 0 ? number_format($value['REPEAT']['loan_principle_received_amount'], 2) : "-") . '</td>';
                    $data .= '<td>' . (($value['REPEAT']['loan_principle_received_amount'] + $value['NEW']['loan_principle_received_amount']) > 0 ? number_format($value['REPEAT']['loan_principle_received_amount'] + $value['NEW']['loan_principle_received_amount'], 2)  : "-") . '</td>';
                    $data .= '<td>' . ($value['NEW']['loan_principle_outstanding_amount'] > 0 ? number_format($value['NEW']['loan_principle_outstanding_amount'], 2) : "-") . '</td>';
                    $data .= '<td>' . ($value['REPEAT']['loan_principle_outstanding_amount'] > 0 ? number_format($value['REPEAT']['loan_principle_outstanding_amount'], 2) : "-") . '</td>';
                    $data .= '<td>' . (($value['REPEAT']['loan_principle_outstanding_amount'] + $value['NEW']['loan_principle_outstanding_amount']) > 0 ? number_format($value['REPEAT']['loan_principle_outstanding_amount'] + $value['NEW']['loan_principle_outstanding_amount'], 2)  : "-") . '</td>';

                    $data .= '<td>' . (($value['NEW']['loan_principle_outstanding_amount'] / $value['NEW']['loan_recommended']) > 0 ? number_format($value['NEW']['loan_principle_outstanding_amount'] / $value['NEW']['loan_recommended'] * 100, 4) . "%" : "-") . '</td>';

                    $data .= '<td>' . (($value['REPEAT']['loan_principle_outstanding_amount'] / $value['REPEAT']['loan_recommended']) > 0 ? number_format($value['REPEAT']['loan_principle_outstanding_amount'] / $value['REPEAT']['loan_recommended'] * 100, 4) . "%" : "-") . '</td>';

                    $data .= '<td style="' . (((($value['REPEAT']['loan_principle_outstanding_amount'] + $value['NEW']['loan_principle_outstanding_amount']) / $value['total_amount']) * 100) > 20 ? "color: #ff0000;  font-weight: bold;" : "") . '">' . ((($value['REPEAT']['loan_principle_outstanding_amount'] + $value['NEW']['loan_principle_outstanding_amount']) / $value['total_amount']) > 0 ? number_format(($value['REPEAT']['loan_principle_outstanding_amount'] + $value['NEW']['loan_principle_outstanding_amount']) / $value['total_amount'] * 100, 4) . "%" : "-") . '</td>';


                    $data .= '</tr>';
                }

                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['loan']['NEW'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['loan']['REPEAT'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total, 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['received']['NEW'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['received']['REPEAT'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['received']['NEW'] + $total_array['received']['REPEAT'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['outstanding']['NEW'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['outstanding']['REPEAT'], 2) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['outstanding']['NEW'] + $total_array['outstanding']['REPEAT'], 2) . '</td>';

                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['outstanding']['NEW'] / $total_array['loan']['NEW']) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['outstanding']['REPEAT'] / $total_array['loan']['REPEAT']) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_array['outstanding']['REPEAT'] + $total_array['outstanding']['NEW']) / $total) * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function CollectionBucketCaseWiseModel($fromDate, $toDate)
    {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT LD.lead_id, LD.lead_status_id, CAM.loan_recommended, CAM.repayment_date, L.loan_closure_date, L.loan_settled_date, L.loan_writeoff_date, LD.user_type, (SELECT COL.date_of_recived FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 ORDER BY COL.date_of_recived DESC LIMIT 1) AS last_payment_date FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id)";

            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_id=CAM.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate'";

            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();
                $total_array = array();
                $total_count = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $repayment_date = $row['repayment_date'];
                        $lead_status_id = $row['lead_status_id'];
                        $user_type = $row['user_type'];
                        $closing_date = $row['last_payment_date'];

                        $dpd = '';
                        if (!empty($closing_date)) {
                            $dpd = (strtotime($closing_date) - strtotime($repayment_date)) / (60 * 60 * 24);
                        }

                        if (in_array($lead_status_id, [16, 17, 18])) {
                            if ($dpd <= 0) {
                                $report_array[1]['bucket'] = 'On Before Due Date';
                                $report_array[1]['On Before Due Date'][$user_type] += 1;
                            } elseif ($dpd > 0 && $dpd <= 10) {
                                $report_array[2]['bucket'] = 'Collection (1 to 10 DPD)';
                                $report_array[2]['Collection (1 to 10 DPD)'][$user_type] += 1;
                            } elseif ($dpd > 10) {
                                $report_array[3]['bucket'] = 'Recovery (10+ DPD)';
                                $report_array[3]['Recovery (10+ DPD)'][$user_type] += 1;
                            }
                        }

                        if (in_array($lead_status_id, [14, 19])) {
                            $report_array[4]['bucket'] = 'Not Closed';
                            $report_array[4]['Not Closed'][$user_type] += 1;
                        }

                        $total_count += 1;
                        $total_array[$user_type] += 1;
                    }
                }

                // traceObject($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="7" class="footer-tabels-text" style="text-align:center;">Collection Bucket Wise Report - ' . date('d-m-Y', strtotime($fromDate)) . ' to ' . date('d-m-Y', strtotime($toDate)) . ' Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th rowspan="2" class="no-of-case">Bucket</th>';
                $data .= '<th colspan="3" class="no-of-case">No of Loans</th>';
                $data .= '<th colspan="3" class="no-of-case">Bucket Wise %</th></tr>';
                $data .= '<tr class="thr-header"><th class="no-of-case">NEW</th>';
                $data .= '<th class="no-of-case">REPEAT</th>';
                $data .= '<th class="no-of-case">Total</th>';
                $data .= '<th class="no-of-case">NEW</th>';
                $data .= '<th class="no-of-case">REPEAT</th>';
                $data .= '<th class="no-of-case">Total</th></tr>';

                ksort($report_array);
                foreach ($report_array as $row) {

                    $total = ($row[$row['bucket']]['REPEAT'] + $row[$row['bucket']]['NEW']);

                    $data .= '<tr><td width="auto" align="center" valign="middle">' . $row['bucket'] . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . ($row[$row['bucket']]['NEW'] > 0 ? number_format($row[$row['bucket']]['NEW'], 0) : "-") . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . ($row[$row['bucket']]['REPEAT'] > 0 ? number_format($row[$row['bucket']]['REPEAT'], 0) : "-") . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . ($total > 0 ? number_format($total, 0) : "-") . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . (($row[$row['bucket']]['NEW'] / $total_array['NEW']) > 0 ? number_format(($row[$row['bucket']]['NEW'] / $total_array['NEW']) * 100, 2) . "%" : "-") . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . (($row[$row['bucket']]['REPEAT'] / $total_array['REPEAT']) > 0 ? number_format(($row[$row['bucket']]['REPEAT'] / $total_array['REPEAT']) * 100, 2) . "%" : "-") . '</td>';
                    $data .= '<td width="auto" align="center" valign="middle">' . number_format(($total / $total_count) * 100, 2) . '%</td></tr>';
                }


                $data .= '<tr><td class="footer-tabels-text">Grand Total</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['NEW'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_array['REPEAT'], 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_count, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';
                $data .= '<td class="footer-tabels-text">&nbsp;</td>';

                // $data .= '<td class="footer-tabels-text">' . number_format($total_array['NEW'] / $total_count * 100, 2) . '%</td>';
                // $data .= '<td class="footer-tabels-text">' . number_format($total_array['REPEAT'] / $total_count * 100, 2) . '%</td>';

                $data .= '<td class="footer-tabels-text">&nbsp;</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadSourcingCityWiseStatusModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT MC.m_city_name, MS.status_order as lead_status_id, status, COUNT(*) as total_count FROM leads LD INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) INNER JOIN master_city MC ON(LD.city_id=MC.m_city_id) WHERE MC.m_city_is_sourcing=1 AND LD.city_id=MC.m_city_id AND LD.lead_status_id=MS.status_id AND LD.lead_active=1 AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' GROUP BY MC.m_city_name, status ORDER BY MC.m_city_name ASC, lead_status_id ASC;";

            $result = $this->db->query($q)->result_array();

            $q1 = "SELECT m_city_id, m_city_name FROM `master_city` WHERE m_city_is_sourcing=1 AND m_city_active=1 ORDER BY m_city_name ASC;";

            $sourcing_city = $this->db->query($q1)->result_array();

            if ($result) {

                $final_array = array();
                $header_values = array();
                $total_value = array();
                $blank_city = array();
                $total = 0;
                $i = 0;

                if (!empty($sourcing_city)) {
                    foreach ($sourcing_city as $row1) {
                        // $total += $row['total_count'];
                        $m_city_name = strtoupper($row1['m_city_name']);
                        $final_array[$m_city_name]['LEAD-NEW']['status'] = '';
                        $final_array[$m_city_name]['LEAD-NEW']['m_city_name'] = $m_city_name;
                        // $total_value['status']['LEAD-NEW'] = 0;
                    }
                }

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total += $row['total_count'];
                        $m_city_name = strtoupper($row['m_city_name']);
                        $final_array[$m_city_name][$row['status']]['counts'] = $row['total_count'];
                        $final_array[$m_city_name][$row['status']]['status'] = $row['status'];
                        $final_array[$m_city_name][$row['status']]['m_city_name'] = $m_city_name;
                        $total_value['utm'][$m_city_name] += $row['total_count'];
                        $total_value['status'][$row['status']] += $row['total_count'];
                        $header_values[$row['lead_status_id']] = $row['status'];
                        $i++;
                    }
                }

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan=' . $i . ' class="footer-tabels-text" style="text-align:center;">Lead Sourcing-City Status-Wise Report - ' . date('d-M-Y', strtotime($fromDate)) . ' - ' . date('d-M-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th width="0.2%" class="no-of-case">SR._NO.</th>';
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">STATUS / CITY</th>';
                ksort($header_values);
                foreach ($header_values as $header_key) {
                    $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">' . $header_key . '</th>';
                }
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">TOTAL</th></tr>';

                $i = 1;
                foreach ($final_array as $key => $value) {
                    $data .= '<tr><td align="center" valign="middle">' . $i . '</td>';
                    $data .= '<td align="center" valign="middle">' . $key . '</td>';
                    foreach ($header_values as $header_key) {
                        $data .= '<td align="center" valign="middle">' . ($value[$header_key]['counts'] > 0 ? number_format($value[$header_key]['counts'], 0) : "-") . '</td>';
                    }
                    $data .= '<td align="center" valign="middle">' . (($total_value['utm'][$key]) > 0 ? $total_value['utm'][$key] : "-") . '</td></tr>';
                    $i++;
                }

                $data .= '<tr><td colspan="2" class="footer-tabels-text">GRAND TOTAL</td>';
                foreach ($header_values as $header_key) {
                    $data .= '<td class="footer-tabels-text">' . (!empty($total_value['status'][$header_key]) > 0 ? number_format($total_value['status'][$header_key], 0) : 0) . '</td>';
                }
                $data .= '<td class="footer-tabels-text">' . number_format($total, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function LeadCityWiseStatusModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT MC.m_city_name, MS.status_order as lead_status_id, status, COUNT(*) as total_count FROM leads LD INNER JOIN master_status MS ON(LD.lead_status_id=MS.status_id) INNER JOIN master_city MC ON(LD.city_id=MC.m_city_id) WHERE LD.city_id=MC.m_city_id AND LD.lead_status_id=MS.status_id AND LD.lead_active=1 AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' GROUP BY MC.m_city_name, status ORDER BY MC.m_city_name ASC, lead_status_id ASC;";

            // $q = "SELECT utm_source, lead_status_id, status, COUNT(*) as total_count FROM `leads` LD WHERE LD.lead_active=1 AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date <= '$toDate' GROUP BY utm_source, status ORDER BY utm_source ASC, lead_status_id ASC";

            $result = $this->db->query($q)->result_array();

            if ($result) {

                $final_array = array();
                $header_values = array();
                $total_value = array();
                $total = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total += $row['total_count'];
                        $m_city_name = strtoupper($row['m_city_name']);
                        $final_array[$m_city_name][$row['status']]['counts'] = $row['total_count'];
                        $final_array[$m_city_name][$row['status']]['status'] = $row['status'];
                        $final_array[$m_city_name][$row['status']]['m_city_name'] = $m_city_name;
                        $total_value['utm'][$m_city_name] += $row['total_count'];
                        $total_value['status'][$row['status']] += $row['total_count'];
                        $header_values[$row['lead_status_id']] = $row['status'];
                        $i++;
                    }
                }

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan=' . $i . ' class="footer-tabels-text" style="text-align:center;">Lead Pan-India Status-Wise Report - ' . date('d-M-Y', strtotime($fromDate)) . ' - ' . date('d-M-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th width="0.2%" class="no-of-case">SR._NO.</th>';
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">STATUS / CITY</th>';
                ksort($header_values);
                foreach ($header_values as $header_key) {
                    $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">' . $header_key . '</th>';
                }
                $data .= '<th width="' . ($i / 100) . '%" class="no-of-case">TOTAL</th></tr>';

                $i = 1;
                foreach ($final_array as $key => $value) {
                    $data .= '<tr><td align="center" valign="middle">' . $i . '</td>';
                    $data .= '<td align="center" valign="middle">' . $key . '</td>';
                    foreach ($header_values as $header_key) {
                        $data .= '<td align="center" valign="middle">' . ($value[$header_key]['counts'] > 0 ? number_format($value[$header_key]['counts'], 0) : "-") . '</td>';
                    }
                    $data .= '<td align="center" valign="middle">' . $total_value['utm'][$key] . '</td></tr>';
                    $i++;
                }

                $data .= '<tr><td colspan="2" class="footer-tabels-text">GRAND TOTAL</td>';
                foreach ($header_values as $header_key) {
                    $data .= '<td class="footer-tabels-text">' . number_format($total_value['status'][$header_key], 0) . '</td>';
                }
                $data .= '<td class="footer-tabels-text">' . number_format($total, 0) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function EMIPortfolioDisbursalModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT LD.lead_id, CAM.loan_recommended, DATE_FORMAT(CAM.disbursal_date, '%M-%y') as month_year, CAM.repayment_date, CAM.repayment_amount, LD.user_type, CAM.roi, CAM.processing_fee_percent, CAM.tenure, L.loan_principle_outstanding_amount, LD.lead_status_id, L.loan_interest_received_amount, CAM.admin_fee ";

            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE L.loan_disbursement_bank_id=5 AND LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CAM.disbursal_date >= '2022-09-16' ORDER BY CAM.disbursal_date ASC";

            // $q = "SELECT LD.lead_id, CAM.loan_recommended, DATE_FORMAT(CAM.disbursal_date, '%M-%y') as month_year, CAM.repayment_date, CAM.repayment_amount, LD.user_type, CAM.roi, CAM.processing_fee_percent, CAM.tenure, L.loan_principle_outstanding_amount, LD.lead_status_id, L.loan_interest_received_amount, CAM.admin_fee ";

            // $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE L.loan_disbursement_bank_id=5 AND LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CAM.disbursal_date >= '2022-09-16' ";

            // $q .= "UNION ";

            // $q .= "SELECT COUNT(LD.lead_id), SUM(CAM.loan_recommended), 'TOTAL' as month_year, CAM.repayment_date, SUM(CAM.repayment_amount), LD.user_type, CAM.roi, CAM.processing_fee_percent, CAM.tenure, SUM(L.loan_principle_outstanding_amount), LD.lead_status_id, SUM(L.loan_interest_received_amount), SUM(CAM.admin_fee) ";

            // $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE L.loan_disbursement_bank_id=5 AND LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CAM.disbursal_date >= '2022-09-16' ";



            $result = $this->db->query($q)->result_array();

            // echo "<pre>";
            // print_r($result); exit;

            if ($result) {

                $final_array = array();
                $total_disbursal_summary = array();
                $total_outstanding_amount = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_outstanding_amount += $row['loan_principle_outstanding_amount'];
                        $final_array[$row['month_year']][$row['user_type']]['counts'] += 1;
                        $final_array[$row['month_year']][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                        $final_array[$row['month_year']][$row['user_type']]['repayment_amount'] += $row['repayment_amount'];
                        $final_array[$row['month_year']][$row['user_type']]['processing_fee_percent'] += $row['processing_fee_percent'];
                        $final_array[$row['month_year']]['roi'] += $row['roi'];
                        $final_array[$row['month_year']]['admin_fee'] += $row['admin_fee'];
                        $final_array[$row['month_year']]['int_rcvcd'] += $row['loan_interest_received_amount'];
                        $final_array[$row['month_year']]['tenure'] += $row['tenure'];
                        $header_key[$row['month_year']] = $row['month_year'];
                        $total_disbursal_summary[$row['month_year']]['total_count'] += 1;
                        $total_disbursal_summary[$row['month_year']]['total_loan_amount'] += $row['loan_recommended'];

                        $dpd = round((strtotime(date('Y-m-d')) - strtotime($row['repayment_date'])) / (60 * 60 * 24));

                        if (in_array($row['lead_status_id'], [14, 19])) {
                            if ($dpd <= 0) {
                                $final_array[$row['month_year']]['running']['count'] += 1;
                                $final_array[$row['month_year']]['running']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } elseif ($dpd > 0 && $dpd <= 30) {
                                $final_array[$row['month_year']]['1_30_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['1_30_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } else if ($dpd > 30 && $dpd <= 90) {
                                $final_array[$row['month_year']]['31_90_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['31_90_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } else if ($dpd > 90) {
                                $final_array[$row['month_year']]['90_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['90_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            }
                            $final_array[$row['month_year']]['total_aum']['count'] += 1;
                            $final_array[$row['month_year']]['total_aum']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                        }
                    }
                }

                $col_count = (count($total_disbursal_summary) * 2) + 1;

                // traceObject($final_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="' . $col_count . '" class="footer-tabels-text" style="text-align:center;">EMI - ' . date('d-M-Y', strtotime($fromDate)) . ' - ' . date('d-M-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th class="no-of-case">EMI LOANS</th>';
                foreach ($header_key as $key) {
                    $data .= '<th colspan="2" class="no-of-case">' . $key . '</th>';
                }
                $data .= '</tr>';


                $data .= '<tr class="thr-header" style="background-color: #dce9f9;"><td class="no-of-case" >&nbsp;</th>';
                foreach ($header_key as $key) {
                    $data .= '<td width="' . (100 / $col_count) . '" class="no-of-case">Units</th>';
                    $data .= '<td width="' . (100 / $col_count) . '" class="no-of-case">Sum</th>';
                }
                $data .= '</tr>';



                $data .= '<tr ><td text-align="left">Loan Disbursed- NEW</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" valign="middle">' . $value['NEW']['counts'] . '</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($value['NEW']['loan_recommended']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Loan Disbursed- REPEAT</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" valign="middle">' . $value['REPEAT']['counts'] . '</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($value['REPEAT']['loan_recommended']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr style="background-color: yellow !important;"><td style="text-align:left; font-weight:bold;">Loan Disbursed- Total</th>';
                foreach ($total_disbursal_summary as $key) {
                    $data .= '<td style="font-weight:bold;">' . $key['total_count'] . '</th>';
                    $data .= '<td style="font-weight:bold;">' . number_format($key['total_loan_amount']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr   style="background-color: #00f3ff52 !important;"><td text-align="left">ATS</th>';
                foreach ($total_disbursal_summary as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format(($key['total_loan_amount'] / $key['total_count']), 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Running Cases</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['running']['count']) ? $key['running']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['running']['loan_principle_outstanding_amount']) ? number_format($key['running']['loan_principle_outstanding_amount'], 0) : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due +1-30 Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['1_30_dpd']['count']) ? $key['1_30_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['1_30_dpd']['loan_principle_outstanding_amount']) ? number_format($key['1_30_dpd']['loan_principle_outstanding_amount'], 0) : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due +31-90 Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['31_90_dpd']['count']) ? $key['31_90_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['31_90_dpd']['loan_principle_outstanding_amount']) ? number_format($key['31_90_dpd']['loan_principle_outstanding_amount'], 0) : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due 90+ Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['90_dpd']['count']) ? $key['90_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['90_dpd']['loan_principle_outstanding_amount']) ? number_format($key['90_dpd']['loan_principle_outstanding_amount'], 0) : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr   style="background-color: yellow !important;"><td style="text-align:left; font-weight:bold;">Loan Book(AUM)</th>';
                foreach ($final_array as $key) {
                    $data .= '<td style="font-weight:bold;">' . (!empty($key['total_aum']['count']) ? $key['total_aum']['count'] : "-") . '</th>';
                    $data .= '<td style="font-weight:bold;">' . (!empty($key['total_aum']['loan_principle_outstanding_amount']) ? number_format($key['total_aum']['loan_principle_outstanding_amount'], 0) : "-") . '</th>';
                }
                $data .= '</tr>';

                // DPD%

                $data .= '<tr ><td text-align="left">1+DPD%</th>';
                foreach ($final_array as $key => $value) {
                    if (isset($total_disbursal_summary[$key])) {
                        $data .= '<td align="center" valign="middle">' . number_format(($value['1_30_dpd']['count'] / $total_disbursal_summary[$key]['total_count']) * 100, 2) . '%</th>';
                        $data .= '<td align="center" valign="middle">' . number_format(($value['1_30_dpd']['loan_principle_outstanding_amount'] / $total_disbursal_summary[$key]['total_loan_amount']) * 100, 2) . '%</th>';
                    }
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">30+DPD%</th>';
                foreach ($final_array as $key => $value) {
                    if (isset($total_disbursal_summary[$key])) {
                        $data .= '<td align="center" valign="middle">' . number_format(($value['31_90_dpd']['count'] / $total_disbursal_summary[$key]['total_count']) * 100, 2) . '%</th>';
                        $data .= '<td align="center" valign="middle">' . number_format(($value['31_90_dpd']['loan_principle_outstanding_amount'] / $total_disbursal_summary[$key]['total_loan_amount']) * 100, 2) . '%</th>';
                    }
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">90+DPD%</th>';
                foreach ($final_array as $key => $value) {
                    if (isset($total_disbursal_summary[$key])) {
                        $data .= '<td align="center" valign="middle">' . number_format(($value['90_dpd']['count'] / $total_disbursal_summary[$key]['total_count']) * 100, 2) . '%</th>';
                        $data .= '<td align="center" valign="middle">' . number_format(($value['90_dpd']['loan_principle_outstanding_amount'] / $total_disbursal_summary[$key]['total_loan_amount']) * 100, 2) . '%</th>';
                    }
                }
                $data .= '</tr>';

                // AVG

                $data .= '<tr ><td text-align="left">PF Income</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($key['admin_fee'], 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Interest Income</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($key['int_rcvcd'], 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg PF (Incl. GST)</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['admin_fee'] / $total_disbursal_summary[$key]['total_count'])) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg ROI</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['roi'] / $total_disbursal_summary[$key]['total_count'], 2)) . '%</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg Tenure</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['tenure'] / $total_disbursal_summary[$key]['total_count'], 2)) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr style="background-color: #0095ff70;"><td colspan="2" style="font-weight: 900;">TOTAL LOAN BOOK (AUM)</td>';
                $data .= '<td colspan="' . $col_count . '" style="font-weight: 900;">' . number_format($total_outstanding_amount) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function EMIPortfolioRepaymentModel($fromDate, $toDate)
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date("Y-m-d", strtotime($toDate));

            $q = "SELECT LD.lead_id, CAM.loan_recommended, DATE_FORMAT(CAM.repayment_date, '%M-%y') as month_year, CAM.disbursal_date, CAM.repayment_amount, LD.user_type, CAM.roi, CAM.processing_fee_percent, CAM.tenure, L.loan_principle_outstanding_amount, LD.lead_status_id, L.loan_interest_received_amount, CAM.admin_fee ";

            $q .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY CAM.repayment_date ASC";

            $result = $this->db->query($q)->result_array();

            if ($result) {

                $final_array = array();
                $header_values = array();
                $total_disbursal_summary = array();
                $total_loan_amount = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_loan_amount += $row['loan_recommended'];
                        $final_array[$row['month_year']][$row['user_type']]['counts'] += 1;
                        $final_array[$row['month_year']][$row['user_type']]['loan_recommended'] += $row['loan_recommended'];
                        $final_array[$row['month_year']][$row['user_type']]['repayment_amount'] += $row['repayment_amount'];
                        $final_array[$row['month_year']][$row['user_type']]['processing_fee_percent'] += $row['processing_fee_percent'];
                        $final_array[$row['month_year']]['roi'] += $row['roi'];
                        $final_array[$row['month_year']]['admin_fee'] += $row['admin_fee'];
                        $final_array[$row['month_year']]['int_rcvcd'] += $row['loan_interest_received_amount'];
                        $final_array[$row['month_year']]['tenure'] += $row['tenure'];
                        $header_key[$row['month_year']] = $row['month_year'];
                        $total_disbursal_summary[$row['month_year']]['total_count'] += 1;
                        $total_disbursal_summary[$row['month_year']]['total_loan_amount'] += $row['loan_recommended'];

                        $dpd = round((strtotime(date('Y-m-d')) - strtotime($row['repayment_date'])) / (60 * 60 * 24));

                        if (in_array($row['lead_status_id'], [14, 19])) {
                            if ($dpd <= 0) {
                                $final_array[$row['month_year']]['running']['count'] += 1;
                                $final_array[$row['month_year']]['running']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } elseif ($dpd > 0 && $dpd <= 30) {
                                $final_array[$row['month_year']]['1_30_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['1_30_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } else if ($dpd > 30 && $dpd <= 90) {
                                $final_array[$row['month_year']]['31_90_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['31_90_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            } else if ($dpd > 90) {
                                $final_array[$row['month_year']]['90_dpd']['count'] += 1;
                                $final_array[$row['month_year']]['90_dpd']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            }
                            $final_array[$row['month_year']]['total_aum']['count'] += 1;
                            $final_array[$row['month_year']]['total_aum']['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                        }
                    }
                }

                $col_count = (count($total_disbursal_summary) * 2) + 1;

                // traceObject($final_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="' . $col_count . '" class="footer-tabels-text" style="text-align:center;">EMI - ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '  Generated at : ' . date('d-M-Y h:i:s') . '</th></tr></thead>';

                $data .= '<tr class="sec-header"><th class="no-of-case">EMI LOANS</th>';
                foreach ($header_key as $key) {
                    $data .= '<th colspan="2" class="no-of-case">' . $key . '</th>';
                }
                $data .= '</tr>';


                $data .= '<tr class="thr-header" style="background-color: #dce9f9;"><td class="no-of-case" >&nbsp;</th>';
                foreach ($header_key as $key) {
                    $data .= '<td width="' . (100 / $col_count) . '" class="no-of-case">Units</th>';
                    $data .= '<td width="' . (100 / $col_count) . '" class="no-of-case">Sum</th>';
                }
                $data .= '</tr>';



                $data .= '<tr ><td text-align="left">Loan Disbursed- NEW</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" valign="middle">' . $value['NEW']['counts'] . '</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($value['NEW']['loan_recommended']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Loan Disbursed- REPEAT</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" valign="middle">' . $value['REPEAT']['counts'] . '</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($value['REPEAT']['loan_recommended']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr style="background-color: yellow !important;"><td style="text-align:left; font-weight:bold;">Loan Disbursed- Total</th>';
                foreach ($total_disbursal_summary as $key) {
                    $data .= '<td style="font-weight:bold;">' . $key['total_count'] . '</th>';
                    $data .= '<td style="font-weight:bold;">' . number_format($key['total_loan_amount']) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr   style="background-color: #00f3ff52 !important;"><td text-align="left">ATS</th>';
                foreach ($total_disbursal_summary as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format(($key['total_loan_amount'] / $key['total_count']), 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Running Cases</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['running']['count']) ? $key['running']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['running']['loan_principle_outstanding_amount']) ? $key['running']['loan_principle_outstanding_amount'] : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due +1-30 Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['1_30_dpd']['count']) ? $key['1_30_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['1_30_dpd']['loan_principle_outstanding_amount']) ? $key['1_30_dpd']['loan_principle_outstanding_amount'] : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due +31-90 Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['31_90_dpd']['count']) ? $key['31_90_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['31_90_dpd']['loan_principle_outstanding_amount']) ? $key['31_90_dpd']['loan_principle_outstanding_amount'] : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Over Due 90+ Day</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">' . (!empty($key['90_dpd']['count']) ? $key['90_dpd']['count'] : "-") . '</th>';
                    $data .= '<td align="center" valign="middle">' . (!empty($key['90_dpd']['loan_principle_outstanding_amount']) ? $key['90_dpd']['loan_principle_outstanding_amount'] : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr   style="background-color: yellow !important;"><td style="text-align:left; font-weight:bold;">Loan Book(AUM)</th>';
                foreach ($final_array as $key) {
                    $data .= '<td style="font-weight:bold;">' . (!empty($key['total_aum']['count']) ? $key['total_aum']['count'] : "-") . '</th>';
                    $data .= '<td style="font-weight:bold;">' . (!empty($key['total_aum']['loan_principle_outstanding_amount']) ? $key['total_aum']['loan_principle_outstanding_amount'] : "-") . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">PF Income</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($key['admin_fee'], 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Interest Income</th>';
                foreach ($final_array as $key) {
                    $data .= '<td align="center" valign="middle">-</th>';
                    $data .= '<td align="center" valign="middle">' . number_format($key['int_rcvcd'], 0) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg PF (Incl. GST)</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['admin_fee'] / $total_disbursal_summary[$key]['total_count'])) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg ROI</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['roi'] / $total_disbursal_summary[$key]['total_count'], 2)) . '%</th>';
                }
                $data .= '</tr>';

                $data .= '<tr ><td text-align="left">Avg Tenure</th>';
                foreach ($final_array as $key => $value) {
                    $data .= '<td align="center" colspan="2" valign="middle">' . (number_format($value['tenure'] / $total_disbursal_summary[$key]['total_count'], 2)) . '</th>';
                }
                $data .= '</tr>';

                $data .= '<tr style="background-color: #0095ff70;"><td colspan="2" style="font-weight: 900;">TOTAL LOAN BOOK (AUM)</td>';
                $data .= '<td colspan="' . $col_count . '" style="font-weight: 900;">' . number_format($total_loan_amount) . '</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function FYdisbursementcollectionModel($report_id, $financial_year)
    {
        if (!empty($financial_year)) {
            $fromDate = date('Y-m-01', strtotime($financial_year));
            $toDate = date("Y-m-t", strtotime('+11 months', strtotime($fromDate)));

            // if (strtotime($toDate) > strtotime(date('d-m-Y'))) {
            //     $toDate = date("Y-m-d");
            // }

            $q = "SELECT LD.lead_id, DATE_FORMAT(CAM.disbursal_date, '%M-%y') as month_year, CAM.repayment_date, LD.lead_status_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_principle_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_payable_amount, L.loan_penalty_payable_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_penalty_received_amount, L.loan_penalty_outstanding_amount, L.loan_total_received_amount FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_id=CAM.lead_id AND L.loan_status_id=14 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.disbursal_date >='$fromDate' AND CAM.disbursal_date <= '$toDate' ORDER BY CAM.disbursal_date ASC;";

            $result = $this->db->query($q)->result_array();

            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();

                $total_count = 0;
                $total_loan_amount = 0;
                $total_repay_amount = 0;
                $total_collection = 0;
                $loan_principle_received_amount = 0;
                $loan_principle_outstanding_amount = 0;
                $loan_interest_received_amount = 0;
                $loan_interest_outstanding_amount = 0;
                $loan_penalty_received_amount = 0;
                $loan_penalty_outstanding_amount = 0;
                $not_closed = 0;
                $loan_interest_payable_amount = 0;
                $loan_penalty_payable_amount = 0;
                $total_count_percentage = 0;
                $not_closed_count_percentage = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $total_loan_amount += $row['loan_recommended'];
                        $total_repay_amount += $row['repayment_amount'];
                        $total_collection += $row['loan_total_received_amount'];
                        $loan_principle_received_amount += $row['loan_principle_received_amount'];
                        $loan_interest_received_amount += $row['loan_interest_received_amount'];
                        $loan_penalty_payable_amount += $row['loan_penalty_payable_amount'];
                        $loan_penalty_received_amount += $row['loan_penalty_received_amount'];
                        $loan_interest_payable_amount += $row['loan_interest_payable_amount'];

                        if (empty($report_array[$row['month_year']]['not_closed_counts'])) {
                            $report_array[$row['month_year']]['not_closed_counts'] = 0;
                        }
                        if ($row['repayment_date'] < date('Y-m-d')) {
                            $loan_penalty_outstanding_amount += $row['loan_penalty_outstanding_amount'];
                            $loan_interest_outstanding_amount += $row['loan_interest_outstanding_amount'];
                            $loan_principle_outstanding_amount += $row['loan_principle_outstanding_amount'];

                            $report_array[$row['month_year']]['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            $report_array[$row['month_year']]['loan_interest_outstanding_amount'] += $row['loan_interest_outstanding_amount'];
                            $report_array[$row['month_year']]['loan_penalty_outstanding_amount'] += $row['loan_penalty_outstanding_amount'];

                            $report_array[$row['month_year']]['total_counts_percentage'] += 1;
                            $total_count_percentage += 1;
                            if (in_array($row['lead_status_id'], array(14, 19))) {
                                $not_closed_count_percentage += 1;
                                $report_array[$row['month_year']]['not_closed_counts_percentage'] += 1;
                            }
                        }

                        $report_array[$row['month_year']]['total_counts'] += 1;
                        $report_array[$row['month_year']]['date'] = $row['month_year'];
                        $report_array[$row['month_year']]['loan_recommended'] += $row['loan_recommended'];
                        $report_array[$row['month_year']]['repayment_amount'] += $row['repayment_amount'];
                        $report_array[$row['month_year']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_received_amount'] += $row['loan_interest_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_payable_amount'] += $row['loan_interest_payable_amount'];
                        $report_array[$row['month_year']]['loan_penalty_payable_amount'] += $row['loan_penalty_payable_amount'];
                        $report_array[$row['month_year']]['loan_penalty_received_amount'] += $row['loan_penalty_received_amount'];
                        $report_array[$row['month_year']]['loan_total_received_amount'] += $row['loan_total_received_amount'];


                        if (in_array($row['lead_status_id'], array(14, 19))) {
                            $not_closed += 1;
                            $report_array[$row['month_year']]['not_closed_counts'] += 1;
                        }
                    }
                }

                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="18" class="footer-tabels-text" style="text-align:center;"><strong>Financial Year VS Disbursement Collection Report FY-(' . date('Y', strtotime($fromDate)) . '-' . date('y', strtotime($toDate)) . ') Generated at : ' . date('d-M-Y h:i:s') . '</strong></th></tr class="sec-header"></thead><tr class="sec-header"><th rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Loan Applications</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Amount</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;">Interest</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;">Penalty</th>';
                $data .= '<th colspan="2" class="no-of-case" style="text-align:center !important; width:70px;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Received Amount Percentage %</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;"><strong>Default Cases Percentage %</strong></th></tr>';

                $data .= '<tr class="thr-header" style="background:#dee9df; top: 15%"><td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Disbursal Month</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Not Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Repay Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received </strong></td>';
                // $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                // $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total Outstanding</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Received Amt</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important;"><strong>Principal Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Interest Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Principal Default</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Default</strong></td></tr>';

                foreach ($report_array as $row_data) {

                    $data .= '<tr><td>' . $row_data['date'] . '</td>';
                    $data .= '<td>' . $row_data['total_counts'] . '</td>';
                    $data .= '<td>' . ($row_data['total_counts'] - $row_data['not_closed_counts']) . '</td>';
                    $data .= '<td>' . $row_data['not_closed_counts'] . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_recommended'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['repayment_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_received_amount'], 0) . '</td>';
                    // $data .= '<td>' . number_format($row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_received_amount'], 0) . '</td>';
                    // $data .= '<td>' . number_format($row_data['loan_interest_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_outstanding_amount'] + $row_data['loan_interest_outstanding_amount'] + $row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_total_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_received_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_interest_received_amount'] / $row_data['loan_interest_payable_amount']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_outstanding_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts']) * 100, 2) . '%</td>';
                    $data .= '<td>' . (!empty($row_data['total_counts_percentage']) ? number_format((1 - (($row_data['total_counts_percentage'] - $row_data['not_closed_counts_percentage']) / $row_data['total_counts_percentage'])) * 100, 2) : 0) . '%</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_count - $not_closed) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $not_closed . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_loan_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_repay_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_received_amount, 0) . '</td>';
                // $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_received_amount, 0) . '</td>';
                // $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_outstanding_amount + $loan_interest_outstanding_amount + $loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_collection, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_received_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_interest_received_amount / $loan_interest_payable_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_outstanding_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_count - $not_closed) / $total_count) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((1 - (($total_count_percentage - $not_closed_count_percentage) / $total_count_percentage)) * 100, 2) . '%</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function FYrepaymentcollectionModel($report_id, $financial_year)
    {
        if (!empty($financial_year)) {
            $fromDate = date('Y-m-01', strtotime($financial_year));
            $toDate = date("Y-m-t", strtotime('+11 months', strtotime($fromDate)));

            // if (strtotime($toDate) > strtotime(date('d-m-Y'))) {
            //     $toDate = date("Y-m-d");
            // }

            $q = "SELECT LD.lead_id, DATE_FORMAT(CAM.repayment_date, '%M-%y') as month_year, CAM.repayment_date, LD.lead_status_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_principle_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_payable_amount, L.loan_penalty_payable_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_penalty_received_amount, L.loan_penalty_outstanding_amount, L.loan_total_received_amount FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_id=CAM.lead_id AND L.loan_status_id=14 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >='$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY CAM.repayment_date ASC;";

            $result = $this->db->query($q)->result_array();

            // echo "<pre>";
            // print_r($result);
            // exit;

            if ($result) {

                $report_array = array();

                $total_count = 0;
                $total_loan_amount = 0;
                $total_repay_amount = 0;
                $total_collection = 0;
                $loan_principle_received_amount = 0;
                $loan_principle_outstanding_amount = 0;
                $loan_interest_received_amount = 0;
                $loan_interest_outstanding_amount = 0;
                $loan_penalty_received_amount = 0;
                $loan_penalty_outstanding_amount = 0;
                $not_closed = 0;
                $loan_interest_payable_amount = 0;
                $loan_penalty_payable_amount = 0;
                $total_count_percentage = 0;
                $not_closed_count_percentage = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {
                        $total_count += 1;
                        $total_loan_amount += $row['loan_recommended'];
                        $total_repay_amount += $row['repayment_amount'];
                        $total_collection += $row['loan_total_received_amount'];
                        $loan_principle_received_amount += $row['loan_principle_received_amount'];
                        $loan_interest_received_amount += $row['loan_interest_received_amount'];
                        $loan_penalty_payable_amount += $row['loan_penalty_payable_amount'];
                        $loan_penalty_received_amount += $row['loan_penalty_received_amount'];
                        $loan_interest_payable_amount += $row['loan_interest_payable_amount'];

                        if (empty($report_array[$row['month_year']]['not_closed_counts'])) {
                            $report_array[$row['month_year']]['not_closed_counts'] = 0;
                        }
                        if ($row['repayment_date'] < date('Y-m-d')) {
                            $loan_penalty_outstanding_amount += $row['loan_penalty_outstanding_amount'];
                            $loan_interest_outstanding_amount += $row['loan_interest_outstanding_amount'];
                            $loan_principle_outstanding_amount += $row['loan_principle_outstanding_amount'];

                            $report_array[$row['month_year']]['loan_principle_outstanding_amount'] += $row['loan_principle_outstanding_amount'];
                            $report_array[$row['month_year']]['loan_interest_outstanding_amount'] += $row['loan_interest_outstanding_amount'];
                            $report_array[$row['month_year']]['loan_penalty_outstanding_amount'] += $row['loan_penalty_outstanding_amount'];

                            $report_array[$row['month_year']]['total_counts_percentage'] += 1;
                            $total_count_percentage += 1;
                            if (in_array($row['lead_status_id'], array(14, 19))) {
                                $not_closed_count_percentage += 1;
                                $report_array[$row['month_year']]['not_closed_counts_percentage'] += 1;
                            }
                        }

                        $report_array[$row['month_year']]['total_counts'] += 1;
                        $report_array[$row['month_year']]['date'] = $row['month_year'];
                        $report_array[$row['month_year']]['loan_recommended'] += $row['loan_recommended'];
                        $report_array[$row['month_year']]['repayment_amount'] += $row['repayment_amount'];
                        $report_array[$row['month_year']]['loan_principle_received_amount'] += $row['loan_principle_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_received_amount'] += $row['loan_interest_received_amount'];
                        $report_array[$row['month_year']]['loan_interest_payable_amount'] += $row['loan_interest_payable_amount'];
                        $report_array[$row['month_year']]['loan_penalty_payable_amount'] += $row['loan_penalty_payable_amount'];
                        $report_array[$row['month_year']]['loan_penalty_received_amount'] += $row['loan_penalty_received_amount'];
                        $report_array[$row['month_year']]['loan_total_received_amount'] += $row['loan_total_received_amount'];


                        if (in_array($row['lead_status_id'], array(14, 19))) {
                            $not_closed += 1;
                            $report_array[$row['month_year']]['not_closed_counts'] += 1;
                        }
                    }
                }

                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="18" class="footer-tabels-text" style="text-align:center;"><strong>Financial Year VS Repayment Collection Report FY-(' . date('Y', strtotime($fromDate)) . '-' . date('y', strtotime($toDate)) . ') Generated at : ' . date('d-M-Y h:i:s') . '</strong></th></tr class="sec-header"></thead><tr class="sec-header"><th rowspan="1"  class="no-of-case" style="text-align:center !important;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Loan Applications</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Amount</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;">Interest</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;">Penalty</th>';
                $data .= '<th colspan="2" class="no-of-case" style="text-align:center !important; width:70px;">&nbsp;</th>';
                $data .= '<th colspan="3"  class="no-of-case" style="text-align:center !important; width:70px;">Received Amount Percentage %</th>';
                $data .= '<th colspan="2"  class="no-of-case" style="text-align:center !important; width:70px;"><strong>Default Cases Percentage %</strong></th></tr>';

                $data .= '<tr class="thr-header" style="background:#dee9df; top: 15%"><td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Repay Month</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Not Closed</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Repay Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received </strong></td>';
                // $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                // $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Outstanding</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Received</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Total Outstanding</strong></td>';
                $data .= '<td width="7%" class="no-of-case" style="text-align:center !important;"><strong>Total Received Amt</strong></td>';
                $data .= '<td class="no-of-case" style="text-align:center !important;"><strong>Principal Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Interest Rcvd</strong></td>';
                $data .= '<td width="6%" class="no-of-case" style="text-align:center !important;"><strong>Principal Default</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Closed</strong></td>';
                $data .= '<td width="6%"class="no-of-case" style="text-align:center !important;"><strong>Default</strong></td></tr>';

                foreach ($report_array as $row_data) {

                    $data .= '<tr><td>' . $row_data['date'] . '</td>';
                    $data .= '<td>' . $row_data['total_counts'] . '</td>';
                    $data .= '<td>' . ($row_data['total_counts'] - $row_data['not_closed_counts']) . '</td>';
                    $data .= '<td>' . $row_data['not_closed_counts'] . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_recommended'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['repayment_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_principle_received_amount'], 0) . '</td>';
                    // $data .= '<td>' . number_format($row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_interest_received_amount'], 0) . '</td>';
                    // $data .= '<td>' . number_format($row_data['loan_interest_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_payable_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_penalty_outstanding_amount'] + $row_data['loan_interest_outstanding_amount'] + $row_data['loan_principle_outstanding_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format($row_data['loan_total_received_amount'], 0) . '</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_received_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_interest_received_amount'] / $row_data['loan_interest_payable_amount']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format(($row_data['loan_principle_outstanding_amount'] / $row_data['loan_recommended']) * 100, 2) . '%</td>';
                    $data .= '<td>' . number_format((($row_data['total_counts'] - $row_data['not_closed_counts']) / $row_data['total_counts']) * 100, 2) . '%</td>';
                    $data .= '<td>' . (!empty($row_data['total_counts_percentage']) ? number_format((1 - (($row_data['total_counts_percentage'] - $row_data['not_closed_counts_percentage']) / $row_data['total_counts_percentage'])) * 100, 2) : 0) . '%</td></tr>';
                }

                $data .= '<tr><td class="footer-tabels-text">Grand Total </td>';
                $data .= '<td class="footer-tabels-text">' . $total_count . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_count - $not_closed) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $not_closed . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_loan_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_repay_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_received_amount, 0) . '</td>';
                // $data .= '<td class="footer-tabels-text">' . number_format($loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_received_amount, 0) . '</td>';
                // $data .= '<td class="footer-tabels-text">' . number_format($loan_interest_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_payable_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_received_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($loan_penalty_outstanding_amount + $loan_interest_outstanding_amount + $loan_principle_outstanding_amount, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format($total_collection, 0) . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_received_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_interest_received_amount / $loan_interest_payable_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($loan_principle_outstanding_amount / $total_loan_amount) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((($total_count - $not_closed) / $total_count) * 100, 2) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format((1 - (($total_count_percentage - $not_closed_count_percentage) / $total_count_percentage)) * 100, 2) . '%</td></tr>';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }

    public function OutstandingReportCasesDateRangeModel($from_Date, $to_date)
    {
        if (!empty($from_Date)) {
            $fromDate = date('Y-m-d', strtotime($from_Date));
            $toDate = date("Y-m-d", strtotime($to_date));

            $collection_to_date = date('Y-m-d', strtotime($toDate));
            if ($collection_to_date >= date('Y-m-d')) {
                $toDate = date('Y-m-d', strtotime('-1 days', strtotime(date("Y-m-d"))));
            }

            $q = "SELECT LD.lead_id, U.name as sanction_executive, LD.lead_credit_assign_user_id, LD.status, LD.lead_status_id, LD.user_type, L.loan_no, CAM.loan_recommended, L.loan_principle_received_amount, L.loan_principle_outstanding_amount FROM leads LD INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";

            $q .= "WHERE LD.lead_id=L.lead_id AND LD.lead_credit_assign_user_id=U.user_id AND LD.lead_id=CAM.lead_id AND LD.lead_active=1 AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY U.name ASC";
            $result = $this->db->query($q)->result_array();

            if ($result) {

                $report_array = array();
                $total_array = array();
                $total = 0;
                $i = 0;

                if (!empty($result)) {
                    foreach ($result as $row) {

                        $lead_status_id = $row['lead_status_id'];
                        $sanction_by = $row['sanction_executive'];
                        $user_type = $row['user_type'];
                        $total += 1;

                        if (in_array($lead_status_id, [14, 19])) {
                            $report_array[$sanction_by]['not_closed'][$user_type]['name'] = $sanction_by;
                            $report_array[$sanction_by]['not_closed'][$user_type]['counts'] += 1;
                            $report_array[$sanction_by]['not_closed']['total_counts'] += 1;
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_recommended'] = $row['loan_recommended'];
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_principle_received_amount'] = $row['loan_principle_received_amount'];
                            $report_array[$sanction_by]['not_closed'][$user_type]['loan_principle_outstanding_amount'] = $row['loan_principle_outstanding_amount'];
                            $report_array[$sanction_by][$user_type] += 1;
                            $total_array['not_closed'][$user_type] += 1;
                        } else {
                            $report_array[$sanction_by]['closed'][$user_type]['name'] = $sanction_by;
                            $report_array[$sanction_by]['closed'][$user_type]['counts'] += 1;
                            $report_array[$sanction_by]['closed']['total_counts'] += 1;
                            $report_array[$sanction_by]['closed'][$user_type]['loan_recommended'] = $row['loan_recommended'];
                            $report_array[$sanction_by]['closed'][$user_type]['loan_principle_received_amount'] = $row['loan_principle_received_amount'];
                            $report_array[$sanction_by]['closed'][$user_type]['loan_principle_outstanding_amount'] = $row['loan_principle_outstanding_amount'];
                            $report_array[$sanction_by][$user_type] += 1;
                            $total_array['closed'][$user_type] += 1;
                        }
                        $i++;
                    }
                }


                // echo "<pre>";
                // print_r($report_array);
                // exit;

                $data = '<table class="bordered"><thead><tr class="fir-header"><th colspan="13" class="footer-tabels-text">Outstanding Executive Case Wise Report ' . date('d-M-Y', strtotime($fromDate)) . ' to ' . date('d-M-Y', strtotime($toDate)) . ' Generated at :- ' . date('d-m-Y h:i:s') . '</th></tr>';
                $data .= '<tr class="sec-header"><th rowspan="2" class="no-of-case">Sanction Executive</th>';
                $data .= '<th colspan="3" class="no-of-case">CLOSED</th>';
                $data .= '<th colspan="3" class="no-of-case">NOT CLOSED</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">TOTAL FRESH</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">TOTAL REPEAT</th>';
                $data .= '<th width="7.5%" rowspan="2" class="no-of-case">GRAND TOTAL</th>';
                $data .= '<th colspan="3" class="no-of-case">DEFAULT%</th></tr>';
                $data .= '<tr class="thr-header"><th width="8%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">TOTAL</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">FRESH</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">REPEAT</th>';
                $data .= '<th width="7.7%" align="left" class="no-of-case">OVERALL%</th>';
                $data .= '</tr></thead>';

                foreach ($report_array as $key => $value) {

                    $data .= '<tr>';
                    $data .= '<td>' . $key . '</td>';
                    $data .= '<td>' . ($value['closed']['NEW']['counts'] > 0 ? $value['closed']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['closed']['REPEAT']['counts'] > 0 ? $value['closed']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['closed']['total_counts'] > 0 ? $value['closed']['total_counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['NEW']['counts'] > 0 ? $value['not_closed']['NEW']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['REPEAT']['counts'] > 0 ? $value['not_closed']['REPEAT']['counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['not_closed']['total_counts'] > 0 ? $value['not_closed']['total_counts'] : "-") . '</td>';
                    $data .= '<td>' . ($value['NEW'] > 0 ? $value['NEW'] : "-") . '</td>';
                    $data .= '<td>' . ($value['REPEAT'] > 0 ? $value['REPEAT'] : "-") . '</td>';
                    $data .= '<td>' . (($value['REPEAT'] + $value['NEW']) > 0 ? ($value['REPEAT'] + $value['NEW']) : "-") . '</td>';
                    $data .= '<td>' . (($value['not_closed']['NEW']['counts'] / $value['NEW']) > 0 ? number_format(($value['not_closed']['NEW']['counts'] / $value['NEW']) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '<td>' . (($value['not_closed']['REPEAT']['counts'] / $value['REPEAT']) > 0 ? number_format(($value['not_closed']['REPEAT']['counts'] / $value['REPEAT']) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '<td style="' . (((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) * 100) > 20 ? "color: #ff0000;  font-weight: bold;" : "") . '">' . ((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) > 0 ? number_format((($value['not_closed']['REPEAT']['counts'] + $value['not_closed']['NEW']['counts']) / ($value['REPEAT'] + $value['NEW'])) * 100, 4) . "%" : "-") . '</td>';
                    $data .= '</tr>';
                }
                $data .= '<tr>';
                $data .= '<td class="footer-tabels-text">Total</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['closed']['NEW'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['closed']['REPEAT'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['REPEAT'] + $total_array['closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['not_closed']['NEW'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total_array['not_closed']['REPEAT'] . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['not_closed']['REPEAT'] + $total_array['not_closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['NEW'] + $total_array['not_closed']['NEW']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . ($total_array['closed']['REPEAT'] + $total_array['not_closed']['REPEAT']) . '</td>';
                $data .= '<td class="footer-tabels-text">' . $total . '</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['NEW'] / ($total_array['closed']['NEW'] + $total_array['not_closed']['NEW'])) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['REPEAT'] / ($total_array['closed']['REPEAT'] + $total_array['not_closed']['REPEAT'])) * 100, 4) . '%</td>';
                $data .= '<td class="footer-tabels-text">' . number_format(($total_array['not_closed']['REPEAT'] + $total_array['not_closed']['NEW']) / $total * 100, 4) . '%</td>';
                $data .= '</tr> ';
                $data .= '</table>';
            } else {
                return $data = '<div class="redalart-me"><strong>No Result Found.</strong></div>';
            }
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
        return $data;
    }
}
