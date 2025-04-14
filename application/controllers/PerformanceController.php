<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PerformanceController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Performance_Model', 'Performance');
        date_default_timezone_set('Asia/Kolkata');
        $login = new IsLogin();
        $login->index();
    }

    public function SanctionPerformancePopup() {

        $user_id = $_SESSION['isUserSession']['user_id'];
        $role_id = $_SESSION['isUserSession']['role_id'];

        $label_name = array();
        $today_disburse_cases = 0;
        $today_disburse_amount = 0;
        $monthly_achieve_cases = 0;
        $monthly_achieve_amount = 0;
        $cases_percentage = 0;
        $amount_percentage = 0;
        $principle_amount = 0;
        $principle_received = 0;
        $monthly_target_amount = 0;
        $total_collection_amount = 0;
        $monthly_target_cases = 0;
        $monthly_target_amount = 0;

        $type_id = 0;
        $message = "";

        if (in_array($role_id, array(2, 3))) {
            $type_id = 1;
        } elseif (in_array($role_id, array(7))) {
            $type_id = 2;
        }



        $data = $this->Performance->sanction_popup_model($user_id, $type_id);

        if ($data['status'] == 1) {

            if ($type_id == 1) {

                $today_disburse_cases = $data['data']['today_disburse_cases'] ? $data['data']['today_disburse_cases'] : 0;
                $today_disburse_amount = $data['data']['today_disburse_amount'] ? $data['data']['today_disburse_amount'] : 0;
                $monthly_achieve_cases = $data['data']['monthly_achieve_cases'] ? $data['data']['monthly_achieve_cases'] : 0;
                $monthly_achieve_amount = $data['data']['monthly_sanction_achieve_amount'] ? $data['data']['monthly_sanction_achieve_amount'] : 0;

                $monthly_target_cases = $data['data']['monthly_target_cases'] ? $data['data']['monthly_target_cases'] : 0;
                $monthly_target_amount = $data['data']['monthly_target_amount'] ? $data['data']['monthly_target_amount'] : 0;

                $today_sanction_cases = $data['data']['today_sanction_cases'] ? $data['data']['today_sanction_cases'] : 0;
                $today_sanction_amount = $data['data']['today_sanction_amount'] ? $data['data']['today_sanction_amount'] : 0;

                $monthly_sanction_cases = $data['data']['monthly_sanction_cases'] ? $data['data']['monthly_sanction_cases'] : 0;
                $monthly_sanction_amount = $data['data']['monthly_sanction_amount'] ? $data['data']['monthly_sanction_amount'] : 0;

                $past_days = date('d');
                $remainin_days = date('t') - $past_days;
                $current_run_case = $monthly_achieve_cases / $past_days;
                $current_run_amount = $monthly_achieve_amount / $past_days;
                $required_run_case = ($monthly_target_cases - $monthly_achieve_cases) / $remainin_days;
                $required_run_amount = ($monthly_target_amount - $monthly_achieve_amount) / $remainin_days;

                $label_name['today_heder'] = 'Today Sanction Details';
                $label_name['monthly_heder'] = 'Monthly Sanction Details';
                $label_name['per_heder'] = 'Collection Percentage (%) as on Date';
                $label_name['sub_heder1'] = 'Sanction Cases';
                $label_name['sub_heder2'] = 'Sanction Amount';
            } elseif ($type_id == 2) {

                $today_disburse_cases = $data['data']['today_followup_cases'] ? $data['data']['today_followup_cases'] : 0;
                $today_disburse_amount = $data['data']['today_collection_amount'] ? $data['data']['today_collection_amount'] : 0;

                $monthly_achieve_amount = $data['data']['monthly_sanction_achieve_amount'] ? $data['data']['monthly_sanction_achieve_amount'] : 0;

                $amount_percentage = !empty($monthly_achieve_amount) && !empty($monthly_target_amount) ? number_format(($monthly_achieve_amount / $monthly_target_amount) * 100, 2) : 0;

                $label_name['today_heder'] = 'Today Collection Details';
                $label_name['monthly_heder'] = 'Monthly Collection Details';
                $label_name['per_heder'] = 'Achievement Percentage (%)';
                $label_name['sub_heder1'] = 'Follow Ups';
                $label_name['sub_heder2'] = 'Collected Amount';
            }
            //require_once (COMMON_COMPONENT.'includes/popup_templates.php');
            //print_r(COMMON_COMPONENT.'includes/popup_templates.php');
            
            $data['today_disburse_cases']=$today_disburse_cases;
            $data['today_disburse_amount']=$today_disburse_amount;
            $data['monthly_achieve_cases']=$monthly_achieve_cases;
            $data['monthly_achieve_amount']=$monthly_achieve_amount;
            $data['monthly_target_cases']=$monthly_target_cases;
            $data['monthly_target_amount']=$monthly_target_amount;
            $data['today_sanction_cases']=$monthly_sanction_amount;
            $data['today_sanction_amount']=$monthly_sanction_amount;
            $data['monthly_sanction_cases']=$monthly_sanction_amount;
            $data['monthly_sanction_amount']=$monthly_sanction_amount;
            $data['current_run_case']=$current_run_case;
            $data['current_run_amount']=$current_run_amount;
            $data['required_run_case']=$required_run_case;
            $data['required_run_amount']=$required_run_amount;
            
            //$template_id=rand(1,4);
            $template_id=3;
            if($template_id==1){
                $message = $this->template_1($data);
            } else if($template_id==2){
                $message = $this->template_2($data);
            } else if($template_id==3){
                $message = $this->template_3($data);
            } else if($template_id==4){
                $message = $this->template_4($data);
            } 
            

            $response['popup_data'] = $message;
            echo json_encode($response);
        } else {
            return false;
        }
    }

    function template_1($data)
{
    $view='<link href="'.WEBSITE_URL.'public/pop_ups/pop_up2/css/style.css?v=1.3" rel="stylesheet" itemprop="url">
    <div class="modal fade main_full_mdl" style="overflow-x:hidden;overflow-y:auto" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <h5 class="modal-title" id="myModalLabel">Modal title</h5> -->
        <button type="button" class="close" onclick="closeModal()" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- <marquee attribute_name = "attribute_value"....more attributes behavior="scroll">
          Sanction Disbursal Run Rate
       </marquee>  -->
       <marquee width="100%" behavior="scroll">  
        Sanction Disbursal Run Rate 
       </marquee>  
        <div class="main_to"> 
          <div class="container">
            <ol class="step-list">
                <li class="step-list__item wow fadeInLeft" data-wow-delay="1ms" data-wow-duration="1500ms">
                    <div class="step-list__item__inner bg_gradient">
                        <div class="content">
                            <div class="body">
                                <h2 class="line-1 anim-typewriter wow fadeInLeft"> 
                                    <span class="word">Sanction</span>  
                                  </h2>  
                                  
                                <p name="answer" style="cursor:pointer" value="Show Div" class="know_expand" onclick=toggleVisibility("menu1")>
                                  Know Details
                                </p> 
                                  <table class="bordered" id="menu1" style="display:none;"> 
                                    <tr>
                                        <th colspan="2"  class="no-of-case">Target</th>
                                        <th colspan="2"  class="no-of-case">Today</th>
                                        <th colspan="2"  class="no-of-case">Monthly</th>
                                    </tr>
                                    <tr>
                                        <td class="no-of-case"><strong>Cases </strong></td>
                                        <td class="no-of-case"><strong>Amount</strong></td>
                  
                                        <td class="no-of-case"><strong>Cases</strong></td>
                                        <td class="no-of-case"><strong>Amount</strong></td>
                                       
                                        <td class="no-of-case"><strong>Cases</strong></td>
                                        <td class="no-of-case"><strong>Amount</strong></td>
                                    </tr>    
                                    <tr>
                                        <td class="no-of-case"><strong>'.$data['monthly_target_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['monthly_target_amount'].'</strong></td>
                  
                                        <td class="no-of-case"><strong>'.$data['today_sanction_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['today_sanction_amount'].'</strong></td>
                                           
                                        <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
                                    </tr>    
                                </table> 
                            </div> 
                            <div class="icon">
                                <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/sanction1.gif" alt="Check" />
                            </div>                
                        </div>
                    </div>
                    <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/dashed-line-arrow-free-png.png" class="line_main" style="width: 200px"> 
                </li>
                <!-- <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/dashed-line-arrow-free-png.png"> -->
                <li class="step-list__item  wow bounceInRight" data-wow-delay="2ms" data-wow-duration="5000ms">
                    <div class="step-list__item__inner">
                        <div class="content">
                            <div class="body">
                              <h2 class="line-1 anim-typewriter wow fadeInLeft"> 
                                <span class="word">Disbursal</span>  
                              </h2>  
                             
                              <p name="answer" style="cursor:pointer"  value="Show Div" class="know_expand" onclick=toggleVisibility("menu2")>
                                Know Details
                              </p>
                        


                                <table class="bordered" id="menu2" style="display:none;"> 
                                  <tr>
                                    <th colspan="2"  class="no-of-case">Target</th>
                                    <th colspan="2"  class="no-of-case">Today</th>
                                    <th colspan="2"  class="no-of-case">Monthly</th>
                                </tr>
                                <tr>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Cases </strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>
              
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Cases </strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Amount </strong></td>
              
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Cases</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>
                                </tr>
                                <tr>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ceil($data['monthly_target_cases']) . '</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . $data['monthly_target_amount'] . '</strong></td> 

                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ceil($data['today_disburse_cases']) . '</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . $data['today_disburse_amount'] . '</strong></td>

                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ceil($data['monthly_achieve_cases']) . '</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . $data['monthly_achieve_amount'] . '</strong></td> 
                                </tr>  
                              </table> 
                            </div>
        
                            <div class="icon">
                              <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/disbursement.gif" alt="Check" />
                            </div>                
                        </div>
                    </div> 
                    <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/dashed-line-arrow-free-png2.PNG" class="line_main2" style="width: 200px"> 
                </li>
                <li class="step-list__item wow fadeInLeft" data-wow-delay="3ms" data-wow-duration="2400ms">
                    <div class="step-list__item__inner">
                        <div class="content">
                            <div class="body">
                              <h2 class="line-1 anim-typewriter"> 
                                <span class="word">Run Rate</span>  
                              </h2> 
                             
                              <p name="answer" style="cursor:pointer"  value="Show Div" class="know_expand" onclick=toggleVisibility("menu3")>
                                Know Details
                              </p>
                        


                                <table class="bordered" id="menu3" style="display:none;"> 
                                  <tr>
                                    <th colspan="2"  class="no-of-case">Current</th>
                                    <th colspan="2"  class="no-of-case">Required</th>
                                </tr>
                                <tr>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Cases </strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Amount</strong></td>
              
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Cases </strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>Amount </strong></td>
                                </tr>
                                <tr>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ($data['current_run_case'] > 0 ? ceil($data['current_run_case']) : 0) . '</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ($data['current_run_amount'] > 0 ? number_format($data['current_run_amount'], 2) : 0) . '</strong></td> 

                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ($data['required_run_case'] > 0 ? ceil($data['required_run_case']) : 0) . '</strong></td>
                                    <td class="no-of-case" style="text-align:center !important;"><strong>' . ($data['required_run_amount'] > 0 ? number_format($data['required_run_amount'], 2) : 0) . '</strong></td>
                                </tr>   
                              </table> 
                            </div>
        
                            <div class="icon">
                              <img src="'.WEBSITE_URL.'public/pop_ups/pop_up2/images/payment1.gif" alt="Check" />
                            </div>                
                        </div>
                    </div>
                </li> 
            </ol>
        </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()" data-dismiss="modal">Close</button> 
      </div>
    </div>
  </div>
  </div>';
    
    return $view;
}

function template_2($data){
    $view='
    <link href="'.WEBSITE_URL.'public/pop_ups/pop_up4/css/style.css?v=1.4" rel="stylesheet" itemprop="url">
    <div class="main-to4">
    <div class="modal fade"  style="overflow-x:hidden;overflow-y:auto" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#185ef5"> 
          <button type="button" class="close" onclick="closeModal()" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="container"> 
          <div class="step-wrapper move-line">
          
          <article class="linestep linestep1" style="left: 20em; top: -72px;"> 
            <span class="num"><img src="'.WEBSITE_URL.'public/pop_ups/pop_up4/images/sanction1.png"></span>
            <div class="bottom">
              <table class="bordered">  
                <tr>
                    <th colspan="2"  class="no-of-case">Target</th>
                    <th colspan="2"  class="no-of-case">Today</th>
                    <th colspan="2"  class="no-of-case">Monthly</th>
                </tr>
                <tr>
                    <td class="no-of-case"><strong>Cases </strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
          
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
                   
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
                </tr>    
                <tr>
                <td class="no-of-case"><strong>'.$data['monthly_target_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['monthly_target_amount'].'</strong></td>
                  
                                        <td class="no-of-case"><strong>'.$data['today_sanction_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['today_sanction_amount'].'</strong></td>
                                           
                                        <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
                                        <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>

                </tr>    
             </table>
            </div>
          </article>
          
          
          
          <article class="linestep linestep3" style="left: 34%; top: 33%;"> 
            <span class="num"><img src="'.WEBSITE_URL.'public/pop_ups/pop_up4/images/disbursal1.PNG"></span>
            <div class="bottom">
              <table class="bordered">  
                <tr>
                    <th colspan="2"  class="no-of-case">Target</th>
                    <th colspan="2"  class="no-of-case">Today</th>
                    <th colspan="2"  class="no-of-case">Monthly</th>
                </tr>
                <tr>
                    <td class="no-of-case"><strong>Cases </strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
          
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
                   
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
                </tr>    
                <tr>
                <td class="no-of-case"><strong>' . ceil($data['monthly_target_cases']) . '</strong></td>
                <td class="no-of-case"><strong>' . $data['monthly_target_amount'] . '</strong></td> 

                <td class="no-of-case"><strong>' . ceil($data['today_disburse_cases']) . '</strong></td>
                <td class="no-of-case"><strong>' . $data['today_disburse_amount'] . '</strong></td>

                <td class="no-of-case"><strong>' . ceil($data['monthly_achieve_cases']) . '</strong></td>
                <td class="no-of-case"><strong>' . $data['monthly_achieve_amount'] . '</strong></td> 

                </tr>    
             </table> 
            </div>
          </article>
          
           
          
          <article class="linestep linestep7" style="right: 55%;  top: 80%;"> 
            <span class="num"> <img src="'.WEBSITE_URL.'public/pop_ups/pop_up4/images/runrate.PNG"></span>
            <div class="bottom">
              <table class="bordered">  
                <tr>
                    <th colspan="2"  class="no-of-case">Current</th>
                    <th colspan="2"  class="no-of-case">Required</th>
                    
                </tr>
                <tr>
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
          
                    <td class="no-of-case"><strong>Cases</strong></td>
                    <td class="no-of-case"><strong>Amount</strong></td>
                   
                   
                </tr>    
                <tr>
                   
                    <td class="no-of-case"><strong>' . ($data['current_run_case'] > 0 ? ceil($data['current_run_case']) : 0) . '</strong></td>
                    <td class="no-of-case"><strong>' . ($data['current_run_amount'] > 0 ? number_format($data['current_run_amount'], 2) : 0) . '</strong></td> 

                    <td class="no-of-case"><strong>' . ($data['required_run_case'] > 0 ? ceil($data['required_run_case']) : 0) . '</strong></td>
                    <td class="no-of-case"><strong>' . ($data['required_run_amount'] > 0 ? number_format($data['required_run_amount'], 2) : 0) . '</strong></td>
                   
                </tr>    
               </table>
            </div>
          </article>
          
         
          
          
          
          <svg width="100%" viewBox="0 0 1146 608" xmlns="http://www.w3.org/2000/svg">
            <path class="path" d="m560.30957,10.588011c0,0 438.0947,1.90476 439.04708,1.90476c0.95238,0 144.57857,-1.02912 143.80934,137.14269c-0.76923,138.17181 -116.81095,142.30859 -131.61967,143.8923c-14.80873,1.58372 -840.41472,-0.71429 -860.5941,0.71429c-20.17938,1.42858 -148.4991,6.80903 -146.83244,147.05973c1.66666,140.2507 129.52365,152.14266 129.33243,151.27321c0.19122,0.86945 815.268425,2.687632 951.42748,0" opacity="0.5" stroke-width="3" stroke="#fff" fill="none"/>
          </svg>
          
          </div>
          
          </div> 
        </div>
        <!-- <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()" data-dismiss="modal">Close</button> 
        </div> -->
      </div>
    </div>
  </div>
  </div>';
    return $view;
}

function template_3($data){
    $view='<link href="'.WEBSITE_URL.'public/pop_ups/pop_up5/css/style.css?v=1.3" rel="stylesheet" itemprop="url">
    <div class="main-to5">
    <div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content"> 
 <div class="modal-head">
 <button type="button" class="btn btn-secondary" onclick="closeModal()" style="background:transparent !important" data-dismiss="modal"><img src="'.WEBSITE_URL.'public/pop_ups/pop_up5/images/close_a.gif" style="width: 30px;"></button> 
</div>



        <div class="modal-body">
          <div class="card">
            <input checked onchange="showtabdetails()" class="tab-btn" id="rad1" name="rad" type="radio">
            <div for="rad1">
              <h1>Sanction</h1>
              <div class="btn"></div>
            </div>
            <input id="rad2" onchange="showtabdetails()" name="rad" class="tab-btn" type="radio">
            <div for="rad2">
              <h1>Disbursal</h1>
              <div class="btn"></div>
            </div>
            <input id="rad3" name="rad" onchange="showtabdetails()" class="tab-btn" type="radio">
            <div for="rad3">
              <h1>Run Rate</h1>
              <div class="btn"></div>
            </div> 
           
            <div class="shapes"></div>
            <div class="photo">
              <div></div>
              <div></div>
              <div></div>
            </div>
            <div class="blob">
              <div class="glob"></div>
            </div>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
          <defs>
          <filter id="goo">
          <feGaussianBlur in="SourceGraphic" stdDeviation="12" result="blur" />
          <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo" />
          <feComposite in="SourceGraphic" in2="goo" operator="atop"/>
          </filter>
          </defs>
          </svg>
          <svg class="svg" viewBox="0 0 400 400">
          <defs>
          <filter id="duotone-filter-post-one">
          <feColorMatrix type="matrix" values="0.14453125 0 0 0 0.33203125 0.71875 0 0 0 0.27734375 -0.34765625 0 0 0 0.73046875 0 0 0 1 0"></feColorMatrix>
          </filter>
          </defs>
          </svg>
          
           <button class="toggle btn_details" onclick="showtabdetails()">View Details</button>
           <div id="target">
            <div class="row">
              <div class="col-md-12 tab-details rad1" style="display: none;">
            <table class="bordered"> 
              <b>Sanction</b>
              <tr>
                  <th colspan="2"  class="no-of-case">Target</th>
                  <th colspan="2"  class="no-of-case">Today</th>
                  <th colspan="2"  class="no-of-case">Monthly</th>
              </tr>
              <tr>
                  <td class="no-of-case"><strong>Cases </strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
        
                  <td class="no-of-case"><strong>Cases</strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
                 
                  <td class="no-of-case"><strong>Cases</strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
              </tr>    
              <tr>
              <td class="no-of-case"><strong>'.$data['monthly_target_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['monthly_target_amount'].'</strong></td>

              <td class="no-of-case"><strong>'.$data['today_sanction_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['today_sanction_amount'].'</strong></td>
                 
              <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
              </tr>    
           </table>
          </div>
          <div class="col-md-12 tab-details rad2" style="display: none;">
           <table class="bordered"> 
            <b>Disbursal</b>
            <tr>
                <th colspan="2"  class="no-of-case">Target</th>
                <th colspan="2"  class="no-of-case">Today</th>
                <th colspan="2"  class="no-of-case">Monthly</th>
            </tr>
            <tr>
                <td class="no-of-case"><strong>Cases </strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
      
                <td class="no-of-case"><strong>Cases</strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
               
                <td class="no-of-case"><strong>Cases</strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
            </tr>    
            <tr>
            <td class="no-of-case"><strong>' . ceil($data['monthly_target_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['monthly_target_amount'] . '</strong></td> 

            <td class="no-of-case"><strong>' . ceil($data['today_disburse_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['today_disburse_amount'] . '</strong></td>

            <td class="no-of-case"><strong>' . ceil($data['monthly_achieve_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['monthly_achieve_amount'] . '</strong></td> 

            </tr>    
         </table> 
         </div>
  
         <div class="col-md-12 tab-details rad3" style="display: none;">
         <table class="bordered"> 
          <b>Run Rate </b>
          <tr>
              <th colspan="2"  class="no-of-case">Current</th>
              <th colspan="2"  class="no-of-case">Required</th>
             
          </tr>
          <tr>
              <td class="no-of-case"><strong>Cases </strong></td>
              <td class="no-of-case"><strong>Amount</strong></td>
    
              <td class="no-of-case"><strong>Cases</strong></td>
              <td class="no-of-case"><strong>Amount</strong></td>
             
              
          </tr>    
          <tr>
              
          <td class="no-of-case"><strong>' . ($data['current_run_case'] > 0 ? ceil($data['current_run_case']) : 0) . '</strong></td>
          <td class="no-of-case"><strong>' . ($data['current_run_amount'] > 0 ? number_format($data['current_run_amount'], 2) : 0) . '</strong></td> 

          <td class="no-of-case"><strong>' . ($data['required_run_case'] > 0 ? ceil($data['required_run_case']) : 0) . '</strong></td>
          <td class="no-of-case"><strong>' . ($data['required_run_amount'] > 0 ? number_format($data['required_run_amount'], 2) : 0) . '</strong></td>
 
          </tr>    
         </table>
         </div> 
      </div>
       </div>
  
  
        </div>
        
      </div>
    </div>
  </div>
  </div>';
    return $view;
}

function template_4($data){
    $view='<link href="'.WEBSITE_URL.'public/pop_ups/pop_up6/css/style.css?v=1.4" rel="stylesheet" itemprop="url">
    <div class="main-to6">
    <div class="modal fade" style="overflow-x:hidden;overflow-y:auto" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <img src="'.WEBSITE_URL.'public/pop_ups/pop_up6/images/sanctions.png" class="main_mdl wow fadeIn"> 
        <div class="modal-body"> 
          <ul>
            <li class="main_bg wow slideInLeft"><span>01 &nbsp; | <img src="'.WEBSITE_URL.'public/pop_ups/pop_up6/images/stamp.png"> </span>
               <span>Sanction  </span></li>
            <li class="main_bg1 wow slideInRight"><span>02 &nbsp; | <img src="'.WEBSITE_URL.'public/pop_ups/pop_up6/images/stamp2.png"> </span> 
              <span> Disbursal</span></li>
            <li class="main_bg wow slideInLeft"><span>03 &nbsp; | <img src="'.WEBSITE_URL.'public/pop_ups/pop_up6/images/stamp.png"> </span> 
              <span> Run Rate  </span></li>
          </ul> 
   
  
   
          <button class="toggle btn_details" onclick="showPerformance()">View Details</button>
          <div id="target" style="display: none;">
            <div class="row">
              <div class="col-md-6">
            <table class="bordered"> 
              <b>Sanction</b>
              <tr>
                  <th colspan="2"  class="no-of-case">Target</th>
                  <th colspan="2"  class="no-of-case">Today</th>
                  <th colspan="2"  class="no-of-case">Monthly</th>
              </tr>
              <tr>
                  <td class="no-of-case"><strong>Cases </strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
        
                  <td class="no-of-case"><strong>Cases</strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
                 
                  <td class="no-of-case"><strong>Cases</strong></td>
                  <td class="no-of-case"><strong>Amount</strong></td>
              </tr>    
              <tr>
              <td class="no-of-case"><strong>'.$data['monthly_target_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['monthly_target_amount'].'</strong></td>

              <td class="no-of-case"><strong>'.$data['today_sanction_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['today_sanction_amount'].'</strong></td>
                 
              <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
              <td class="no-of-case"><strong>'.$data['monthly_sanction_cases'].'</strong></td>
              </tr>    
           </table>
          </div>
          <div class="col-md-6">
           <table class="bordered"> 
            <b>Disbursal</b>
            <tr>
                <th colspan="2"  class="no-of-case">Target</th>
                <th colspan="2"  class="no-of-case">Today</th>
                <th colspan="2"  class="no-of-case">Monthly</th>
            </tr>
            <tr>
                <td class="no-of-case"><strong>Cases </strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
      
                <td class="no-of-case"><strong>Cases</strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
               
                <td class="no-of-case"><strong>Cases</strong></td>
                <td class="no-of-case"><strong>Amount</strong></td>
            </tr>    
            <tr>
            <td class="no-of-case"><strong>' . ceil($data['monthly_target_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['monthly_target_amount'] . '</strong></td> 

            <td class="no-of-case"><strong>' . ceil($data['today_disburse_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['today_disburse_amount'] . '</strong></td>

            <td class="no-of-case"><strong>' . ceil($data['monthly_achieve_cases']) . '</strong></td>
            <td class="no-of-case"><strong>' . $data['monthly_achieve_amount'] . '</strong></td> 
            </tr>    
         </table> 
         </div>
  
         <div class="col-md-6">
         <table class="bordered"> 
          <b>Run Rate </b>
          <tr>
              <th colspan="2"  class="no-of-case">Current</th>
              <th colspan="2"  class="no-of-case">Required</th>
            
          </tr>
          <tr>
              <td class="no-of-case"><strong>Cases</strong></td>
              <td class="no-of-case"><strong>Amount</strong></td>
    
              <td class="no-of-case"><strong>Cases</strong></td>
              <td class="no-of-case"><strong>Amount</strong></td>
             
           
          </tr>    
          <tr>
                    
          <td class="no-of-case"><strong>' . ($data['current_run_case'] > 0 ? ceil($data['current_run_case']) : 0) . '</strong></td>
          <td class="no-of-case"><strong>' . ($data['current_run_amount'] > 0 ? number_format($data['current_run_amount'], 2) : 0) . '</strong></td> 

          <td class="no-of-case"><strong>' . ($data['required_run_case'] > 0 ? ceil($data['required_run_case']) : 0) . '</strong></td>
          <td class="no-of-case"><strong>' . ($data['required_run_amount'] > 0 ? number_format($data['required_run_amount'], 2) : 0) . '</strong></td>
 
          </tr>    
         </table>
         </div> 
      </div>
          </div>
              
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal()" data-dismiss="modal"><img src="'.WEBSITE_URL.'public/pop_ups/pop_up6/images/close_a.gif" style="width: 30px;"></button> 
        </div>
      </div>
    </div> 
  </div>
  </div>';
    return $view;
}

}
