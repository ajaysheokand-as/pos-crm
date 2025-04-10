<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Consumer Base Report</title>
<style type="text/css">

@media print
{
  table { page-break-after:auto; 
  -webkit-print-color-adjust:exact;}
  thead { display:table-header-group; }
  tfoot { display:table-footer-group; }
  body
	{
	margin-top:10px;
	margin-bottom:10px;
	margin-right:25px;
	margin-left:30px;
	}
}

.shading
{
	background-color: #e6e6ff;
	background:#e6e6ff;
}
.box {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: #FFFFFF;
	border-collapse: collapse;
	text-align: left;
	-moz-box-shadow: 0px 0px 30px #DADADA;
	-webkit-box-shadow: 0px 0px 30px #DADADA;
	box-shadow: 0px 0px 30px #DADADA;
}

.box1 {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
}

.tabStyle {
	background: #FFFFFF;
	border-style: inset;
	border-width: thin;
	border-color: black;
	border-collapse: collapse;
}

.rowStyle {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: grey;
	border-collapse: collapse;
}

.box1 tr:nt-child(even) {
	background-color: white;
}

.box1 tr:nth-child(odd) {
	background-color: #F1F3F5;
}

.style14 {
	font-face: segoe ui semibold;
	font-size: 2px;
}

.summarytable {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
	border-left: none;
	border-right: none;
}

.reportHead {
	font-family: segoe ui semibold;
	font-size: 24px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
}
.dataHead {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	text-indent: 5px;
}
.mainHeader {
	font-family: segoe ui semibold;
	font-size: 16px;
	color: #FFFFFF;
	background: #0f3f6b;
	text-align: left;
	font-weight: 600;
	padding-bottom: 3px;
}

.subHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	text-align: left;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader1 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}
.dataHeaderNone {
	font-family: segoe ui semibold;
	font-size: 14px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: center;
	text-indent: 5px;
	white-space: nowrap;
	height : 23;	
	valign:middle
}

.subHeader2 {
	font-family: segoe ui semibold;
	border-collapse: collapse;
	border-bottom: 0px;
	border-left: 1px solid #ffffff;
	border-right: 0px;
	border-top: 1px solid #ffffff;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
	white-space: nowrap;
}

.dataHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataHeaderScore {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #464646;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataValueValue {
	font-family: segoe ui semibold;
	font-size: 25px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}
.dataValuePerform {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}
.dataValuePerform2 {
	border-collapse: separate; 
       Color: #464646; 
       font-family: segoe ui semibold;
       font-size: 12px;
	font-weight: 280;
}
.dataHeadern {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	padding-top: 2px;
}

.dataValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}

.dataValueAlert {
	font-family: segoe ui semibold;
	font-size: 17px;
	font-weight: 600;
	color: #800000;
	text-align: left;
	padding-left: 12px;	
	padding-top: 1px;
	background-color:#ffe1dc;
}

.dataAmtValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	padding-right: 7px;	
	padding-top: 1px;
}

.dataHeader1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
}

.dataValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	text-indent: 5px;
}

.mainAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #FFFFFF;
	background: #0f3f6b;
	font-weight: 600;
}

.AccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
}

.subAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	background: #e6e6ff;
	font-weight: 600;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	
}

.AccValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
}
.AccValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
}

.AccSummaryTab {
	border-width: thin;
	border-collapse: collapse;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	border-bottom: 0px;
	text-indent: 5px;
}

.disclaimerValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
}

.infoValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}

.maroonFields {
	color: Maroon;
	font-family: segoe ui semibold;
	font-size: 15px;
	font-weight: 600;
}
.AccValueComm2 {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
}
.AccValue2 {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	
}

.container {
	/* this will give container dimension, because floated child nodes don't give any */
	/* if your child nodes are inline-blocked, then you don't have to set it */
	overflow: auto;
}

.container .headActive {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 10em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #ffe1dc;
	color: #be0000;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headActive .vertActive {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #be0000;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.container .headClosed {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 10em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #e1f0be;
	color: #415a05;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headClosed .vertClosed {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #415a05;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.infoValueNote {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}
.dataValuePerform1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-top: 1px;
}
</style>

</head>
<body style="font-family: segoe ui semibold, arial, verdana;">
<table class="box" align="center" border="0px" cellpadding="0"
	cellspacing="0" width="1050px">
		<thead>
	<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td>
									<table align="center" border="0" width="1020px">
										<tbody>
											<tr height="10">
												<td></td>
											</tr>
											<tr>

												<td colspan="2" valign="top"><img src="data:image/gif;base64,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" 
alt="CRIF HighMark Credit Information Services Pvt. Ltd." align="left" width="166" height="72"/></td>
												<td width="120"></td>

												<td align="left" width="380" valign="top">
												<table border="0" cellpadding="0" cellspacing="0">
													<tbody>
														<tr>
															<td align="left" class="reportHead"> CONSUMER BASE&trade; REPORT <br>
															</td>
														</tr>
														<tr valign="top">
															<td class="dataHead" align="right" valign="top">															For MALLIKA S </td>
														</tr>
													</tbody>
												</table>
												</td>
												<td width="70"></td>
												<td rowspan="2" align="right" valign="top" width="350">
												<table>
													<tbody>
														<tr>
															<td class="dataHeader1">CHM Ref #:</td>
															<td class="dataValue1">DEVM230210CR375075409 </td>
														</tr>
														<tr>
															<td class="dataHeader1">Prepared For:</td>
															<td class="dataValue1">DEVMUNI LEASING AND FINANCE LIMITED </td>
														</tr>
														<tr>
															<td class="dataHeader1">Application ID:</td>
															<td class="dataValue1">23  </td>
														</tr>


														<tr>
															<td class="dataHeader1">Date of Request:</td>
															<td class="dataValue1">10-02-2023 16:23:31 </td>
														</tr>
														<tr>
															<td class="dataHeader1">Date of Issue:</td>
															<td class="dataValue1">10-02-2023 </td>
														</tr>



													</tbody>
												</table>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
<tr>
									<td height="10">
									<hr size="1" style="color: #C8C8C8;" />
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					
	</thead>
		<tbody>
		<tr>
			<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0">
				<tbody>
					
					<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0"
							width="1020px">
							<tbody>
								

								<tr>
									<td>
									<table align="center" bgcolor="#0f3f6b" border="0" width="1020px">
										<tbody>
											<tr height="20">
												<td width="10"></td>
												<td class="mainHeader">Inquiry Input Informationsss</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="1030px">
							<tbody>
								<tr>
									<td>
									<table align="center" border="0" width="1030px">
										<tbody>
											<tr>

												<td>
												<table border="0" width="1030px">
													<tbody>
														<tr>
															<td height="10px"></td>
														</tr>
														<tr>
															<td align="left" width="110 px" class="dataHeader">Name:</td>
															<td align="left" width="270 px" class="dataValue"> MALLIKA S </td>

															<td width="70 px" class="dataHeader">DOB/Age:</td>
															<td width="190 px" class="dataValue">01-01-1983  /  40 years </td>

															<td width="70 px" class="dataHeader">Gender:</td>
															<td width="200 px" class="dataValue">FEMALE </td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td align="left" width="100 px" class="dataHeader">Father:</td>
															<td align="left" width="200 px" class="dataValue"> </td>

															<td width="70 px" class="dataHeader">Spouse:</td>
															<td width="100 px" class="dataValue"> </td>

															<td width="70 px" class="dataHeader">Mother:</td>
															<td width="120 px" class="dataValue"> </td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td class="dataHeader" valign="top" width="100 px">Phone
															Numbers:</td>
															<td valign="top">
															<table width="200px" cellpadding="0" cellspacing="0">
																<tr>
																	<td class="dataValue"> 6692787823 </td>
																</tr>
																<tr>
																	<td class="dataValue"></td>
																</tr>
																<tr>
																	<td class="dataValue"></td>
																</tr>
															</table>
															</td>

															<td class="dataHeader" valign="top">ID(s):</td>
															<td valign="top">
															<table width="300px" cellpadding="0" cellspacing="0" text-indent="-20px">
																<tr>
																	<td class="dataValue"> DEEPA0101K [PAN] </td>
																</tr>
																<tr>
																	<td class="dataValue"></td>
																</tr>
																<tr>
																	<td class="dataValue"></td>
																</tr>
															</table>
															</td>

															<td class="dataHeader" valign="top">Email ID(s):</td>
															<td valign="top">
															<table width="200px" cellpadding="0" cellspacing="0">
																<tr>
																	<td class="dataValue"> JAVED.ALI@BHARATLOAN.COM </td>
																</tr>
																<tr>
																	<td class="dataValue"></td>
																</tr>
															</table>
															</td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td align="left" width="100 px" class="dataHeader">Entity
															Id:</td>
															<td align="left" width="200 px" class="dataValue"
																colspan="5">  </td>

															
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td align="left" width="100 px" class="dataHeader">Current
															Address:</td>
															<td align="left" width="200 px" class="dataValue"
																colspan="5"> O NO 5 105 B N 112 COIMBATORE 642007 PARAMADAIYUR Coimbatore 642007 TN </td>

															
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td align="left" width="100 px" class="dataHeader">Other
															Address:</td>
															<td align="left" width="200 px" class="dataValue"
																colspan="5"> O NO 5 105 B N 112 COIMBATORE 642007 PARAMADAIYUR> Coimbatore 642007 TN </td>

															</td>
														</tr>


													</tbody>
												</table>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
					</tr>
					</tbody>
					</table>
										</td>
 						
												
						
						
						
						<tr>
							<td height ="20px"></td> 			
	
						</tr>
						
						
		        						 <tr>
								<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1020px">
										<tbody>
											<tr>
												<td>
													<table align="center" bgcolor="#0f3f6b" border="0"
														width="1020px">
														<tbody>
															<tr height="20">
																<td width="10"></td>
																<td class="mainHeader">CRIF HM Score(S):</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							 							<tr>
								<td>
									<table align="left" border="0" cellpadding="0"
										cellspacing="0" width="1020px">
										<tbody>
											<tr>
												<td>
													<table border="0" align="left" style="width: 1028px; padding-left: 8px;">
																		<tbody>
																			<tr>
																				<td height="10px" colspan="2"></td>
																			</tr>
																			<tr>
																				<td align="left" width="200px" class="dataHeader" style="padding-left: 20px;">SCORE NAME</td>
																																								<td width="200 px" class="dataHeader">&nbsp;SCORE </td>
																				<td align="left" style="width: 428x;" class="dataHeader">SCORING FACTORS</td>
																																							</tr>
																																						<tr  class="shading">
																																							<td align="left" width="200 px" class="dataHeaderScore" style="padding-left: 20px;">PERFORM CONSUMER 2.0</td>
																				
																				<td align="left" class="dataValueValue" style="padding-left: 5px; width:200px;">&nbsp;620																																											<span class="dataValuePerform2" style="width: 300px; vertical-align: middle; padding-left: 5px;">Score Range : 300-900</span>
																																									</td>
																				
																																								
																																								<td align="left" width="400 px">
																																										<div>
																						<table>
																							<tr>
																								<td valign="top" style="padding-top: 8px;">
																									<div style="width: 6px;height: 6px;background-color: GREEN;display: inline-block;"></div>
																								</td>
																								<td>
																									<font size="2px" >No/minimal recent missed payments																									</font>
																								</td>
																							</tr>
																						</table>
																					</div>
																																										<div>
																						<table>
																							<tr>
																								<td valign="top" style="padding-top: 8px;">
																									<div style="width: 6px;height: 6px;background-color: GREEN;display: inline-block;"></div>
																								</td>
																								<td>
																									<font size="2px" >No/minimal missed payments observed historically																									</font>
																								</td>
																							</tr>
																						</table>
																					</div>
																																										<div>
																						<table>
																							<tr>
																								<td valign="top" style="padding-top: 8px;">
																									<div style="width: 6px;height: 6px;background-color: GREEN;display: inline-block;"></div>
																								</td>
																								<td>
																									<font size="2px" >Decent number of self/overall loans disbursed in the past																									</font>
																								</td>
																							</tr>
																						</table>
																					</div>
																																									</td>
																																																										</tr>
																																					</tbody>
													</table>		 
												</td>
											</tr>
										</tbody>
									</table>
								</td>
																																	<!--CR 1456 Scoring Factor-->
								
																<tr height="20">
									<td class="infoValueNote" align="right" bgcolor="#FFFFFF" style="padding-right: 93px;">Tip: &nbsp;&nbsp;&nbsp;&nbsp;<div style="width: 6px;height: 6px;background-color: green;display: inline-block;"></div>&nbsp;&nbsp;Positive impact on credit score&nbsp;&nbsp;&nbsp;&nbsp;
										<div style="width: 6px;height: 6px;background-color: red;display: inline-block;"></div>&nbsp;&nbsp;Negative impact on credit score
										
									</td>
									
								</tr>
																
							</tr>
																		<tr>
						<td>
												</td>
					</tr>
<!--  CCP 472 Prod defect - Consumer to Commercial cross alert is not displayed in INDV PDFHTML report-->
<tr>
    <td height="30px"></td>
</tr>
<tr>
    <td>
						    </td>
</tr>
<!--CCP 472 Prod defect - Consumer to Commercial cross alert is not displayed in INDV PDFHTML report  -->
					<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Consumer Base Report</title>
<style type="text/css">

@media print
{
  table { page-break-after:auto; 
  -webkit-print-color-adjust:exact;}
  thead { display:table-header-group; }
  tfoot { display:table-footer-group; }
  body
	{
	margin-top:10px;
	margin-bottom:10px;
	margin-right:25px;
	margin-left:30px;
	}
}

.shading
{
	background-color: #e6e6ff;
	background:#e6e6ff;
}
.box {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: #FFFFFF;
	border-collapse: collapse;
	text-align: left;
	-moz-box-shadow: 0px 0px 30px #DADADA;
	-webkit-box-shadow: 0px 0px 30px #DADADA;
	box-shadow: 0px 0px 30px #DADADA;
}

.box1 {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
}

.tabStyle {
	background: #FFFFFF;
	border-style: inset;
	border-width: thin;
	border-color: black;
	border-collapse: collapse;
}

.rowStyle {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: grey;
	border-collapse: collapse;
}

.box1 tr:nt-child(even) {
	background-color: white;
}

.box1 tr:nth-child(odd) {
	background-color: #F1F3F5;
}

.style14 {
	font-face: segoe ui semibold;
	font-size: 2px;
}

.summarytable {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
	border-left: none;
	border-right: none;
}

.reportHead {
	font-family: segoe ui semibold;
	font-size: 24px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
}
.dataHead {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	text-indent: 5px;
}
.mainHeader {
	font-family: segoe ui semibold;
	font-size: 16px;
	color: #FFFFFF;
	background: #0f3f6b;
	text-align: left;
	font-weight: 600;
	padding-bottom: 3px;
}

.subHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	text-align: left;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader1 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}
.dataHeaderNone {
	font-family: segoe ui semibold;
	font-size: 14px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: center;
	text-indent: 5px;
	white-space: nowrap;
	height : 23;	
	valign:middle
}

.subHeader2 {
	font-family: segoe ui semibold;
	border-collapse: collapse;
	border-bottom: 0px;
	border-left: 1px solid #ffffff;
	border-right: 0px;
	border-top: 1px solid #ffffff;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
	white-space: nowrap;
}

.dataHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataHeaderScore {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #464646;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataValueValue {
	font-family: segoe ui semibold;
	font-size: 25px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}
.dataValuePerform {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}
.dataValuePerform2 {
	border-collapse: separate; 
       Color: #464646; 
       font-family: segoe ui semibold;
       font-size: 12px;
	font-weight: 280;
}
.dataHeadern {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	padding-top: 2px;
}

.dataValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}

.dataValueAlert {
	font-family: segoe ui semibold;
	font-size: 17px;
	font-weight: 600;
	color: #800000;
	text-align: left;
	padding-left: 12px;	
	padding-top: 1px;
	background-color:#ffe1dc;
}

.dataAmtValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	padding-right: 7px;	
	padding-top: 1px;
}

.dataHeader1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
}

.dataValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	text-indent: 5px;
}

.mainAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #FFFFFF;
	background: #0f3f6b;
	font-weight: 600;
}

.AccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
}

.subAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	background: #e6e6ff;
	font-weight: 600;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	
}

.AccValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
}
.AccValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
}

.AccSummaryTab {
	border-width: thin;
	border-collapse: collapse;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	border-bottom: 0px;
	text-indent: 5px;
}

.disclaimerValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
}

.infoValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}

.maroonFields {
	color: Maroon;
	font-family: segoe ui semibold;
	font-size: 15px;
	font-weight: 600;
}
.AccValueComm2 {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
}
.AccValue2 {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	
}

.container {
	/* this will give container dimension, because floated child nodes don't give any */
	/* if your child nodes are inline-blocked, then you don't have to set it */
	overflow: auto;
}

.container .headActive {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 10em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #ffe1dc;
	color: #be0000;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headActive .vertActive {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #be0000;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.container .headClosed {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 10em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #e1f0be;
	color: #415a05;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headClosed .vertClosed {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #415a05;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.infoValueNote {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}
.dataValuePerform1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-top: 1px;
}
</style>

</head>
<body style="font-family: segoe ui semibold, arial, verdana;">
<table class="box" align="center" border="0px" cellpadding="0"
	cellspacing="0" width="1050px">
		<tbody>
		<tr>
			<td>
								</td>
 						
												
						
						
						
						<tr>
							<td height ="20px"></td> 			
	
						</tr>
						
						
		        					<tr>
						<td>
												<table align="center" border="0" cellpadding="0" cellspacing="0"
							width="1020px">
							<tbody>
								<tr>
									<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1020px">
										<tbody>
											<tr>
												<td height="30px"></td>
											</tr>
											<tr>
												<td>
												<table align="center" bgcolor="#0f3f6b" border="0"
													width="1020px">

													<tbody>
														<tr height="20">

															<td width="10"></td>
															<td class="mainHeader">Personal Information -
															Variations</td>
														</tr>
													</tbody>
												</table>
												</td>
											</tr>

										</tbody>
									</table>
									</td>
								</tr>
								<tr height="20">
									<td align="right" bgcolor="#FFFFFF" class="infoValue">Tip: These
									are applicant's personal information variations as contributed
									by various financial institutions.</td>
								</tr>
								<tr>
									<td align="center">
									<table cellpadding="2" cellspacing="4" border="0px">
										<tbody>
																						<tr>
												<td width="670" valign="top">
												<table cellpadding="0" cellspacing="4" border="0px"
													width="670">
													<tbody>

														<tr>
															<td bgcolor="#FFFFFF">															<table class="box1" border="0px" bordercolor="lightgray"
																cellpadding="3" cellspacing="0">
																<tbody>

																	<tr height="20">
																		<td align="center" bgcolor="#FFFFFF" width="550px"
																			class="subHeader">Name Variations</td>
																		<td align="center" bgcolor="#FFFFFF" width="90px"
																			class="subHeader">Reported On</td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">MALLIGA M </td>
																		<td align="center" class="dataValue">31-03-2019 </td>
																	</tr>
																																	</tbody>
															</table>
															</td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td bgcolor="#FFFFFF">
																														<table class="box1" border="0px" bordercolor="lightgray"
																cellpadding="3" cellspacing="0">
																<tbody>
																	<tr height="20">
																		<td align="center" width="550px" class="subHeader">Address
																		Variations</td>
																		<td align="center" width="90px" class="subHeader">Reported
																		On</td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">DOOR NO - 5/112 `PARAMADAYUR REDDIYARUR KAMBALAPATTI POLLACHI 191 642007 TN </td>
																		<td align="center" class="dataValue">29-02-2020 </td>
																	</tr>
																																	</tbody>
															</table>
															</td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td bgcolor="#FFFFFF"></td>
														</tr>
													</tbody>
												</table>
												</td>

												<td width="330" valign="top">
												<table cellpadding="0" cellspacing="4" border="0px"
													width="330">
													<tbody>
														<tr>
															<td bgcolor="#FFFFFF">
																														<table class="box1" border="0px" bordercolor="lightgray"
																cellpadding="3" cellspacing="0">
																<tbody>
																	<tr height="20">
																		<td align="center" width="230px" class="subHeader">DOB Variations </td>
																		<td align="center" width="90px" class="subHeader">Reported
																		On</td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">20-07-1983 </td>
																		<td align="center" class="dataValue">31-10-2019 </td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">01-01-1983 </td>
																		<td align="center" class="dataValue">31-03-2019 </td>
																	</tr>
																																	</tbody>
															</table>
															</td>
														</tr>
														<tr> 
															<td height="5px"></td>
														</tr>
														<tr>
															<td bgcolor="#FFFFFF">															<table class="box1" border="0px" bordercolor="lightgray"
																cellpadding="3" cellspacing="0">
																<tbody>
																	<tr height="20">
																		<td align="center" width="230px" class="subHeader">Phone Variations </td>
																		<td align="center" width="90px" class="subHeader">Reported
																		On</td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">6692787823 </td>
																		<td align="center" class="dataValue"> 31-03-2019 </td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">6517635471 </td>
																		<td align="center" class="dataValue"> 29-02-2020 </td>
																	</tr>
																																	</tbody>
															</table>
															</td>
														</tr>
														<tr>
															<td height="5px"></td>
														</tr>
														<tr>
															<td bgcolor="#FFFFFF">															<table class="box1" border="0px" bordercolor="lightgray"
																cellpadding="3" cellspacing="0">
																<tbody>
																	<tr height="20">
																		<td align="center" width="230px" class="subHeader">ID Variations </td>
																		<td align="center" width="90px" class="subHeader">Reported
																		On</td>
																	</tr>
																																		<tr height="20">
																		<td class="dataValue">42443912910385 [Voter ID] </td>
																		<td align="center" class="dataValue"> 29-02-2020 </td>
																	</tr>
																																	</tbody>
															</table>
															</td>
														</tr>
													</tbody>
												</table>
												</td>
											</tr>
																					</tbody>
									</table>
									</td>
								</tr>
																<tr>
									<td>

									<table align="center" border="0" cellpadding="0"
										cellspacing="0">
										<tbody>
											<tr height="10">
												<td align="right" bgcolor="#FFFFFF" class="infoValue"></td>
											</tr>
											<tr height="20">
												<td align="right" bgcolor="#FFFFFF" class="infoValue">Tip: All
												amounts are in INR.</td>
											</tr>
											<tr></tr>
											<tr>
												<td>
												<table align="center" bgcolor="#0f3f6b" border="0"
													width="1020px">
													<tbody>
														<tr height="20">
															<td width="10"></td>
															<td class="mainHeader">Account Summary</td>
														</tr>
													</tbody>
												</table>
												</td>
											</tr>
											<tr height="20">
												<td align="right" bgcolor="#FFFFFF" class="infoValue">Tip: Current Balance & Disbursed Amount is considered ONLY for ACTIVE accounts.</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
								
								<tr>
									<td align="right" bgcolor="#FFFFFF" class="infoValue" height="20"></td>
								</tr>
								<tr>
									<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1020px">
										<tbody>
																					<tr>
												<td>
												<table align="center"
													style="border-collapse: collapse; border: 2px solid #A7CBE3;"
													cellspacing="0" cellpadding="2" width="1000px">
													<tbody>
														<tr height="20">
															<td>
															<table align="center" border="0px" cellspacing="0"
																cellpadding="0" width="1000px">
																<tbody>
																	<tr>
																		<td width="center">
																		<table align="center" border="0px" cellspacing="0"
																			cellpadding="0" width="1000px">
																			<tbody>
																				<tr height="20">
																					<td width="150" class="subHeader1">Type</td>
																					<td align="center" width="175" class="subHeader1">Number
																					of Account(s)</td>
																					<td align="center" width="175" class="subHeader1">Active
																					Account(s)</td>
																					<td align="center" width="175" class="subHeader1">Overdue
																					Account(s)</td>
																					<td align="right" width="175" class="subHeader1">Current
																					Balance</td>
																					<td align="right" width="175" class="subHeader1">Amt Disbd/
																					High Credit</td>
																					<td align="right" width="5" class="subHeader1"></td>
																				</tr>
																				<tr height="20">
																					<td align="left" class="dataHeader">Primary
																					Match</td>
																					<td align="center" class="AccValue">1</td>

																					<td align="center" class="AccValue">0</td>
																					<td align="center" class="AccValue">0</td>

																					<td align="right" class="AccValue">0</td>

																					<td align="right" class="AccValue">0</td>
																				</tr>
																				
																				<tr height="20" bgcolor="e6e6ff">
																					<td align="left" class="dataHeader">Total</td>
																					<td align="center" class="AccValue">1</td>

																					<td align="center" class="AccValue">0</td>
																					<td align="center" class="AccValue">0</td>
																					<td align="right" class="AccValue">0</td>

																					<td align="right" class="AccValue">0</td>
																				</tr>
																			</tbody>
																		</table>
																		</td>
																	</tr>
																	<tr>
																		<td height="10"></td>
																	</tr>
																	<tr>
																		<td>
																		<table align="center" border="0px" cellspacing="0"
																			width="1000">
																			<tbody>
																				<tr bgcolor="#FFFFFF">

																					<td colspan="2" align="left" bgcolor="#FFFFFF"
																						width="300" class="AccHeader">Inquiries in
																					last 24 Months: <font color="#464646">25 </font></td>
																					<td colspan="2" align="center" bgcolor="#FFFFFF"
																						width="300" class="AccHeader">New Account(s)
																					in last 6 Months: <font color="#464646">
																					 0 </font></td>
																					<td colspan="2" align="right" bgcolor="#FFFFFF"
																						width="300" class="AccHeader">New Delinquent
																					Account(s) in last 6 Months: <font color="#464646">0 </font></td>
																					<td align="right" width="5" class="AccHeader"></td>
																				</tr>
																			</tbody>
																		</table>
																		</td>

																	</tr>

																</tbody>
															</table>
															</td>

														</tr>
													</tbody>
												</table>
												</td>
											</tr>
																	</tbody>
									</table>
									</td>

								</tr>
															</tbody>
						</table>
												</td>
					</tr>
<!--  CCP 472 Prod defect - Consumer to Commercial cross alert is not displayed in INDV PDFHTML report-->
<tr>
    <td height="30px"></td>
</tr>
<tr>
    <td>
						        <tr>
            <td>
                <tr>
                    <td height="5"></td>
                </tr>
                <tr>
                    <td>
                       <table align="left" border="0" cellpadding="0" cellspacing="0" style="padding-left:10px;">
							<tbody>
								<tr>

									<td>
									<table class="box1" align="left" border="0px" cellpadding="3"
										cellspacing="0" width="665px">
										<tbody>
																					
																					</tbody>
									</table>
									</td>
								</tr>

							</tbody>
						</table>
                    </td>
                </tr>
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
					    </td>
</tr>
<!--CCP 472 Prod defect - Consumer to Commercial cross alert is not displayed in INDV PDFHTML report  -->
										<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr height="30"></tr>

								<tr>
									<td>
									<table align="center" bgcolor="#0f3f6b" border="0"
										width="1020px">
										<tbody>
											<tr height="20">
												<td width="10"></td>
												<td class="mainHeader">Account Information</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						<br>
						</td>
					</tr>
										<tr>
						<td>

						<table align="center" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td>
									<table align="center" border="0" width="1020px" cellpadding="0"
										cellspacing="0">
										<tbody>

											<tr height="20">
												<td align="center" class="mainAccHeader" width="20px">1 </td>
												<td align="center">
												<table align="left" border="0" width="1000px"
													bgcolor="e6e6ff" cellpadding="2" cellspacing="0">
													<tbody>
														<tr height="20">


															<td align="left" width="400" class="AccHeader" nowrap="true">Account
															Type: <font class="maroonFields" nowrap="true">CONSUMER LOAN </td>
															</font>
															
															<td align="left" width="330" class="AccHeader"
																nowrap="nowrap">Credit Grantor: <font
																color="#464646">XXXX </font></td>
															<td align="left" nowrap="true" width="125" class="AccHeader :">Account #:
															    <font color="#464646">xxxx </font></td>
															<td align="right" width="170" class="AccHeader">Info.
															as of: <font color="#464646">29-02-2020 </font></td>

														</tr>
													</tbody>
												</table>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>

								<tr>
								
								
								
								
									<td>
									<table align="center" border="0" width="1000" cellpadding="0" cellspacing="0">
									<tr>
									
										<td class="container" width="30">

											
																								<div class="headClosed" width="30" >

													<div class="vertClosed" width="30" align="center" style="background: #e1f0be; text-align: center; ">CLOSED </div>
												</div>
																																			

 

										</td>
										<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1000px">
										<tbody>
											<tr height="10"></tr>

											<tr height="25">
												<td width="100" class="dataHeader">&nbsp;&nbsp;Ownership:</td>
												<td width="160" class="dataValue">INDIVIDUAL </td>
												<td  width="20"></td>
												<td width="100" class="dataHeader">Disbursed Date:</td>
												<td width="80" class="dataValue">23-12-2021 </td>
												<td  width="20"></td>
												<td width="145" class="dataHeader">Disbd Amt/High Credit:</td>
												<td width="90" align="right" class="dataAmtValue">2,899 </td>
												<td  width="20"></td>
												

											</tr>

											<tr height="25">
												<td width="100" class="dataHeader">&nbsp;&nbsp;Credit
												Limit:</td>
												<td width="160" class="dataValue"> </td>
												<td  width="20"></td>
												<td width="100" class="dataHeader">Last Payment Date:</td>
												<td width="80" class="dataValue">17-12-2021 </td>
												<td  width="20"></td>
												<td width="145" class="dataHeader">Current Balance:</td>
												<td width="90" align="right" class="dataAmtValue">0 </td>
												<td  width="20"></td>
												

											</tr>

											<tr height="25">
												<td width="100" class="dataHeader">&nbsp;&nbsp;Cash
												Limit:</td>
												<td width="160" class="dataValue"></td>
												<td  width="20"></td>
												<td width="100" class="dataHeader">Closed Date:</td>
												<td width="80" class="dataValue">17-12-2019 </td>
												<td  width="20"></td>
												<td width="145" class="dataHeader">Last Paid Amt:</td>
												<td width="90" align="right" class="dataAmtValue"></td>
												
												<td  width="20"></td>
												
												

											</tr>
											<tr height="25">
											     <td width="100" class="dataHeader">&nbsp;&nbsp;InstlAmt/Freq:</td>
												<td width="160" class="dataValue"></td>
												<td  width="20"></td>
												<td width="100" class="dataHeader">Tenure(month):</td>
												<td width="80" class="dataValue">0 </td>
												<td  width="20"></td>
												<td width="145" class="dataHeader">Overdue Amt:</td>
												<td width="90" align="right" class="dataAmtValue">0 </td>
												<td  width="20"></td>
											</tr>
																						<tr height="25">
											     <td width="100" class="dataHeader">&nbsp;&nbsp;Status:</td>
												 															    <td width="160" class="dataValue" colspan="4"></td>
															 												<td  width="20"></td>
												<td width="100" class="dataHeader">Principal Writeoff Amt:</td>
												<td width="80" class="dataValue">0 </td>
												<td  width="20"></td>
												
											</tr>
											
											<tr height="25">
											     <td width="100" class="dataHeader">&nbsp;&nbsp;Settlement Amt:</td>
												<td width="160" class="dataValue" colspan="4"></td>
												<td  width="20"></td>
												<td width="100" class="dataHeader"></td>
												<td width="80" class="dataValue"></td>
												<td  width="20"></td>
												<td width="100" class="dataHeader">Total Writeoff Amt:</td>
												<td width="80" class="dataAmtValue"></td>
												<td  width="20"></td>
											</tr>
																						
											
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>

						</td>
					</tr>
					
					</tbody>
						</table>

						</td>
					</tr>
					<tr>
						<td height="10px"></td>
					</tr>
					<tr>
						<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0"
							width="1000px">
							<tbody>
								<tr>
									<td>
									<table width="1000px">
										<tbody>
											<tr>
												<td width="2"></td>
												<td class="dataHeader" height="25">Payment
												History/Asset Classification:</td>
											</tr>
										</tbody>
									</table>
									<table width="1000px">
										<tbody>
											<tr>

												<td>
												<table align="left" border="1px" bordercolor="#A7CBE3"
													style="border-collapse: collapse; table-layout: fixed;"
													cellpadding="0" cellspacing="0" width="1000px">

													<tbody>
														<tr align="center" bordercolor="#A7CBE3" style="border-width: thin;">
															<td width="25px" class="subHeader2"></td>
															<td width="40px" class="subAccHeader">January</td>
															<td width="40px" class="subAccHeader">February</td>
															<td width="40px" class="subAccHeader">March</td>
															<td width="40px" class="subAccHeader">April</td>
															<td width="40px" class="subAccHeader">May</td>
															<td width="40px" class="subAccHeader">June</td>
															<td width="40px" class="subAccHeader">July</td>
															<td width="40px" class="subAccHeader">August</td>
															<td width="40px" class="subAccHeader">September</td>
															<td width="40px" class="subAccHeader">October</td>
															<td width="40px" class="subAccHeader">November</td>
															<td width="40px" class="subAccHeader">December</td>

														</tr>
																												<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2019</td>
																														
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																															
																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																														</tr>
														 														<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2018</td>
																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																																																																		<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																																																																			<td width="40px" class="AccValue1" bgcolor="#FFFFFF">000/XXX</td>
																																																														</tr>
														 														<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2017</td>
															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																															
																																	<td width="40px" class="AccValueComm2" bgcolor="#FFFFFF">XXX/XXX</td>
																																													</tr>
														 
													</tbody>
												</table>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					
					<tr>
						<td height="20px"></td>
					</tr>
					
					
					<tr>
						<td>
						<table cellpadding="0" cellspacing="0" align="center" border="0"
							width="1000px">
							<tr>

								<td>
															</td>
							</tr>
							
						</table>
						</td>
					</tr>
					
					
					<tr>
						<td height="20px"></td>
					</tr>
					
					
					
					<tr>
						<td>
						<table cellpadding="0" cellspacing="0" align="center" border="0"
							width="1000px">
							<tr>

								<td>
															</td>
							</tr>
							
						</table>
						</td>
					</tr>
					
					
					<tr>
						<td height="20px"></td>
					</tr>
					
					<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>MALLIKA S</title>
<style type="text/css">
@media print
{
  table { page-break-after:auto; 
  -webkit-print-color-adjust:exact;}
  thead { display:table-header-group; }
  tfoot { display:table-footer-group; }
  body
	{
	margin-top:10px;
	margin-bottom:10px;
	margin-right:25px;
	margin-left:30px;
	}
}
.dataHeaderNone {
	font-family: segoe ui semibold;
	font-size: 14px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: center;
	text-indent: 5px;
	white-space: nowrap;
	height : 23;	
	valign:middle
}
.shading
{
    background-color: #e6e6ff;
	background:#e6e6ff;
}

.dataHeaderScore {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #464646;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataValuePerform2 {
	border-collapse: separate; 
       Color: #464646; 
       font-family: segoe ui semibold;
       font-size: 12px;
	font-weight: 280;
}
.dataValueValue {
	font-family: segoe ui semibold;
	font-size: 25px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}

.dataValuePerform {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
}



.box {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: #FFFFFF;
	border-collapse: collapse;
	text-align: left;
	-moz-box-shadow: 0px 0px 30px #DADADA;
	-webkit-box-shadow: 0px 0px 30px #DADADA;
	box-shadow: 0px 0px 30px #DADADA;
}

.box1 {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
}

.tabStyle {
	background: #FFFFFF;
	border-style: inset;
	border-width: thin;
	border-color: black;
	border-collapse: collapse;
}

.rowStyle {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: grey;
	border-collapse: collapse;
}

.box1 tr:nt-child(even) {
	background-color: white;
}

.box1 tr:nth-child(odd) {
	background-color: #F1F3F5;
}

.style14 {
	font-face: segoe ui semibold;
	font-size: 2px;
}

.summarytable {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
	border-left: none;
	border-right: none;
}

.reportHead {
	font-family: segoe ui semibold;
	font-size: 24px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	font-variant: small-caps;
}
.dataHead {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	text-indent: 5px;
}
.mainHeader {
	font-family: segoe ui semibold;
	font-size: 16px;
	color: #FFFFFF;
	background: #0f3f6b;
	text-align: left;
	font-weight: 600;
	padding-bottom: 3px;
}

.subHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	text-align: left;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader1 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader2 {
	font-family: segoe ui semibold;
	border-collapse: collapse;
	border-bottom: 0px;
	border-left: 1px solid #ffffff;
	border-right: 0px;
	border-top: 1px solid #ffffff;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
	white-space: nowrap;
}

.dataHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}

.dataValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
	word-wrap:break-word;
}
.dataValue2 {
	font-family: segoe ui semibold;
	font-size: 13px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
	word-wrap:break-word;
}

.dataValueAlert {
	font-family: segoe ui semibold;
	font-size: 17px;
	font-weight: 600;
	color: #800000;
	text-align: left;
	padding-left: 12px;	
	padding-top: 1px;
	background-color:#ffe1dc;
}

.dataAmtValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	padding-right: 7px;	
	padding-top: 1px;
}

.dataHeader1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
}
.dataHeader2 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	text-indent: 5px;
}

.mainAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #FFFFFF;
	background: #0f3f6b;
	font-weight: 600;
}

.AccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
}

.subAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	background: #e6e6ff;
	font-weight: 600;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	text-align: center;
	
}

.AccValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
}
.AccValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	text-align: center;

}
.AccValueComm2 {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
}
.AccSummaryTab {
	border-width: thin;
	border-collapse: collapse;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	border-bottom: 0px;
	text-indent: 5px;
}

.disclaimerValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
}

.infoValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}

.maroonFields {
	color: Maroon;
	font-family: segoe ui semibold;
	font-size: 15px;
	font-weight: 600;
}

.container {
	/* this will give container dimension, because floated child nodes don't give any */
	/* if your child nodes are inline-blocked, then you don't have to set it */
	overflow: auto;
}

.container .headActive {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 11em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #ffe1dc;
	color: #be0000;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headActive .vertActive {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #be0000;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.container .headClosed {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 11em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #e1f0be;
	color: #415a05;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.infoValueNote {
	font-family: segoe ui semibold;
	font-size: 11px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}

.container .headClosed .vertClosed {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #415a05;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}
}

</style>
</head>

<body style="font-family: segoe ui semibold, arial, verdana;">
	<table class="box" align="center" border="0px" cellpadding="0"
		cellspacing="0" width="1050">
				<thead>
			<tr>
				<td>
					<table align="center" border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0">
										<tbody>
											<tr>
												<td>
													<table align="center" border="0" width="1020px">
														<tbody>
															<tr height="10">
																<td></td>
															</tr>
															<tr>

																<td colspan="2" valign="top"><img
																	src="data:image/gif;base64,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"
																	alt="HighMark Credit Information Services Pvt. Ltd."
																	align="left">
																</td>
																<td width="150"></td>

																<td align="left" width="350" valign="top">
																	<table border="0" cellpadding="0" cellspacing="0">
																		<tbody>
																			<tr>
																				<td align="left" class="reportHead">Advanced
																					Overlap Report<br>
																				</td>
																			</tr>
																			<tr valign="top">
																				<td class="AccValue" align="right" valign="top">MALLIKA S</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
																<td width="70"></td>
																<td rowspan="2" align="right" valign="top" width="350">
																	<table>
																		<tbody>
																			<tr>
																				<td class="dataHeader1">CHM Ref #:</td>
																				<td class="dataValue1">DEVM230210CR375075409</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Prepared For:</td>
																				<td class="dataValue1">DEVMUNI LEASING AND FINANCE LIMITED</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Date of Request:</td>
																				<td class="dataValue1">10-02-2023 16:23:31</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Date of Issue:</td>
																				<td class="dataValue1">10-02-2023</td>
																			</tr>



																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<hr size="1" style="color: #C8C8C8;" />
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
				</td>
			</tr>						
</thead>
 						 						
                           							<tr>
								<td>

									<table align="center" border="0" cellpadding="0"
										cellspacing="0">
										<tbody>
											<tr height="10">
												<td align="right" bgcolor="#FFFFFF" class="infoValue"></td>
											</tr>
											<tr height="20">
												<td align="right" bgcolor="#FFFFFF" class="infoValue">Tip:
													All amounts are in INR</td>
											</tr>
											<tr></tr>
											<tr>
												<td>
													<table align="center" bgcolor="#0f3f6b" border="0"
														width="1020px">
														<tbody>
															<tr height="20">
																<td width="10"></td>
																<td class="mainHeader">Summary</td>
															</tr>

														</tbody>
													</table>
												</td>

											</tr>

										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td height="25" align="right" bgcolor="#FFFFFF"
									class="infoValue">Tip: Current Balance, Disbursed Amount &
									Instalment Amount is considered ONLY for ACTIVE account
									&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td height="15"></td>
							</tr>

							<tr>
								<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1050">
										<tbody>
											<tr>
												<td>
													<table align="center"
														style="border-collapse: collapse; border: 2px solid #A7CBE3;"
														cellspacing="0" cellpadding="2" width="1000">
														<tbody>
															<tr height="20">
																<td>
																	<table align="center" border="0px" cellspacing="0"
																		cellpadding="0" width="1000">
																		<tbody>
																			<tr>
																				<td width="center">
																					<table align="center" border="0px" cellspacing="0"
																						cellpadding="0" width="1000">
																						<thead>
																							<tr height="25">
																								<td width="150" class="subHeader1" rowspan="2">Type</td>
																								<td align="center" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Association</td>
																								<td align="center" width="175" scope="colgroup"
																									colspan="3" class="dataHeader2">Account
																									Summary</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Disbursed
																									Amount</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Instalment
																									Amount</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Current
																									Balance</td>
																								<td align="right" width="5" ></td>
																							</tr>
																							<tr height="20">

																								<td align="center" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Active</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Closed</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Default</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="5" class="subHeader1"></td>
																							</tr>


																						</thead>
																						<tbody>

																							<tr height="20">
																								<td align="left" class="dataHeader">Primary
																									Match</td>
																								<td align="center"><span class="AccValue">0</span>
																								</td>
																								<td align="center"><span class="AccValue">2</span>
																								</td>
																								<td align="center"><span class="AccValue">1</span>
																								</td>
																								<td align="center"><span class="AccValue">2</span>
																								</td>
																								<td align="center"><span class="AccValue">0</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">45,000</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">2,420</span>
																								</td>																								
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">44,516</span>
																								</td>
																								
																							</tr>
																							<tr>
																								<tr height="20">
																									<td align="left" class="dataHeader">Secondary
																										Match</td>

																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>																									
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									
																								</tr>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																			<tr>
																				<td height="10"></td>
																			</tr>


																		</tbody>
																	</table>
																</td>

															</tr>
														</tbody>
													</table>
												</td>
											</tr>

										</tbody>
									</table>
								</td>

							</tr>
														<tr>
				<td height="30px"></td>
			</tr>
			<tr>
			<td>
				</td>
</tr>
							
						<tr>
				<td>
					<table align="center" border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr height="30"></tr>

							<tr>
								<td>
									<table align="center" bgcolor="#0f3f6b" border="0"
										width="1020px">
										<tbody>
											<tr height="20">
												<td width="10"></td>
												<td class="mainHeader">Account Details - Primary</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table> <br>
				</td>
			</tr>
			 <tr>
	<td>
		<table width="1000" border="0" align="center" cellpadding="0" cellspacing="0">
									<tr>
				<td colspan="2">
					<table align="center" border="0" width="1020px" cellpadding="0" cellspacing="0">
						<tbody>
														<tr height="20">
								<td height="20" align="center" class="mainAccHeader" width="20px">1
								</td>
								<td height="20" align="center"  width="20px">
								</td>
								<td align="center">
									<table align="left" border="0" width="1000px"
										bgcolor="e6e6ff" cellpadding="2" cellspacing="0">
										<tbody>
											<tr height="20">


												<td align="left" width="450" class="AccHeader">Member
													& Account Information with Credit Grantor: <font
													class="maroonFields">XXXX  (Branch: 8081) </font>
												</td>
												

											</tr>
										</tbody>
									</table>
								</td>
							</tr>
													</tbody>
					</table>
				</td>
			</tr>
						
			
						
			<tr>
				<td>
					<table width="1049" border="0">
												<tr>
							<td colspan="2">
								<table align="center" border="0" cellpadding="0"
									cellspacing="0" width="1000px">
									<tbody>
										<tr>
											<td>
												<table align="center" border="0" width="1000px">
													<tbody>
														<tr>

															<td>
																<table border="0" width="1000px">
																	<tbody>
																		<tr>
																			<td height="10px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="110 px" class="dataHeader1">Member
																				Name:</td>
																			<td align="left" width="270 px" class="dataValue2">
																				MALLIGA M</td>

																			<td width="70 px" class="dataHeader1">DOB/Age:</td>
																			<td width="210 px" class="dataValue2">20-07-1983  </td>

																			<td width="70 px" class="dataHeader1">Info. As On:</td>
																			<td width="130 px" class="dataValue2">31-05-2020</td>
																		</tr>
																		<tr>
																			<td height="3px"></td>
																		</tr>

																		<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td class="dataHeader1" valign="top" width="100 px">Relationships:</td>
																			<td valign="top">
																				<table width="220px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> NACHIMUTHU  A (Father) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> DHANALAKSHMI  A (Mother) </td>
																					</tr>

																				</table>
																			</td>

																			<td class="dataHeader1" valign="top">ID(s):</td>
																			<td valign="top">
																				<table width="200px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> XXXXXXXXXXXX (UID) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> 42443912910385 (Voters Id) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>

																			<td class="dataHeader1" valign="top" width="100 px">Phone
																				Numbers:</td>
																			<td valign="top">
																				<table width="150px" cellpadding="0"
																					cellspacing="0">
																					  <td>
                                                                                      
                                                                                                                                                                         
																					<tr>
																						<td class="dataValue2"> 6692787823 </td>
																					</tr>
																					
																					<tr>
																						<td class="dataValue2"> 6692787823 </td>
																					</tr>
																					

                                                                                                                                                                            </td>
																				</table>
																			</td>
																		</tr>
																		<tr>
																			<td height="1px"></td>
																		</tr>
																																				<tr>
																			<td align="left" width="100 px" class="dataHeader1">Current
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="3">DOOR NO - 5/112 `PARAMADAYUR REDDIYARUR KAMBALAPATTI POLLACHI TN 642007</td>
																			
																			<td class="dataHeader1" valign="top" width="200 px">Monthly Household
																				Income:</td>
																			<td valign="top">
																				<table width="100px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>

																		</tr>
																																				<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="100 px" class="dataHeader1">Other
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="5"><div style="width:890px;overflow:none">DOOR NO 5/112 ANNA NAGAR PARAMADAIYUR KAMBALAPATTI COIMBATORE DOOR NO 5/112 ANNA NAGAR PARAMADAIYUR KAMBALAPATTI COIMBATORE POLLACHI TN 642007</div></td>
																		</tr>


																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
												<tr>
							<td colspan="2">
								<table align="center" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" colspan="1" height="15" width="1010">
											<hr size="1" style="color: #A7CBE3;" />
										</td>
									</tr>
								</table>
							</td>

						</tr>
						<tr>
							<td>
								<table align="center" border="0" width="1000" cellpadding="0" cellspacing="0">
									<tr>
										<td class="container" width="30">

											
																								<div class="headActive" width="30">

													<div class="vertActive" width="30" align="center" style="background: #ffe1dc; text-align: center; ">ACTIVE</div>
												</div>
																							

 

										</td>

										<td>
											<table>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr height="10"></tr>
																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		Type:</td>
																	<td width="160" class="dataValue">JLG INDIVIDUAL</td>
																	<td width="110" class="dataHeader">Disbursed
																		Date:</td>
																	<td width="90" class="dataValue">18-12-2019</td>
																	<td width="110" class="dataHeader">Amt Disbursed:</td>
																	<td width="90" align="right" class="dataAmtValue">45,000</td>
																	<td width="110" class="dataHeader">Info. As On:</td>
																	<td width="90" class="dataValue">31-05-2020</td>
																</tr>

																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		#:</td>
																	<td width="160" class="dataValue">xxxx</td>
																	<td width="110" class="dataHeader">Closed Date:</td>
																	<td width="90" class="dataValue"></td>
																	<td width="110" class="dataHeader">Current
																		Balance:</td>
																	<td width="90" align="right" class="dataAmtValue">44,516</td>
																	<td width="110" class="dataHeader">Amount
																		Write-Off:</td>
																	<td width="90" class="dataValue" align="right">0  </td>
																</tr>

																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Instl
																		Freq:</td>
																	<td width="160" class="dataValue">MONTHLY</td>
																	<td width="110" class="dataHeader">Instl Amount:</td>
																	<td width="90" class="dataValue">2,420</td>
																	<td width="115" class="dataHeader">Amount
																		Overdue:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">DPD:</td>
																	<td width="90" class="dataValue">0</td>
																</tr>
																
																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Account Remarks:</td>
																	<td width="160" class="dataValue"></td>
																</tr>
																
																
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr>
																	<td>
																		<table width="1000px">
																			<tbody>
																				<tr>
																					<td class="dataHeader" height="25">Payment
																						History:</td>
																				</tr>
																				<tr>
																					<td>
																						<table align="left" border="1px" bordercolor="#A7CBE3"
																							style="border-collapse: collapse; "
																							cellpadding="0" cellspacing="0">
																							
																							<tbody>
																																					<tr align="center" bordercolor="#A7CBE3" style="border-width: thin;">
															<td width="25px" class="subHeader2"></td>
															<td width="40px" class="subAccHeader">January</td>
															<td width="40px" class="subAccHeader">February</td>
															<td width="40px" class="subAccHeader">March</td>
															<td width="40px" class="subAccHeader">April</td>
															<td width="40px" class="subAccHeader">May</td>
															<td width="40px" class="subAccHeader">June</td>
															<td width="40px" class="subAccHeader">July</td>
															<td width="40px" class="subAccHeader">August</td>
															<td width="40px" class="subAccHeader">September</td>
															<td width="40px" class="subAccHeader">October</td>
															<td width="40px" class="subAccHeader">November</td>
															<td width="40px" class="subAccHeader">December</td>

														</tr>
																																										<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2020</td>
																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																													</tr>
														 														<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2019</td>
																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																													</tr>
														  
													</tbody>
																						</table>
																					</td>
																				</tr>																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
												
												
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
<td height="20"></td>
</tr>
 <tr>
	<td>
		<table width="1000" border="0" align="center" cellpadding="0" cellspacing="0">
									
			
						
			<tr>
				<td>
					<table width="1049" border="0">
												<tr>
							<td colspan="2">
								<table align="center" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" colspan="1" height="15" width="1010">
											<hr size="1" style="color: #A7CBE3;" />
										</td>
									</tr>
								</table>
							</td>

						</tr>
						<tr>
							<td>
								<table align="center" border="0" width="1000" cellpadding="0" cellspacing="0">
									<tr>
										<td class="container" width="30">

											
																								<div class="headClosed" width="30">
													<div class="vertClosed" width="30" align="center" style="background: #e1f0be; text-align: center; ">CLOSED</div>
												</div>
																							

 

										</td>

										<td>
											<table>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr height="10"></tr>
																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		Type:</td>
																	<td width="160" class="dataValue">JLG INDIVIDUAL</td>
																	<td width="110" class="dataHeader">Disbursed
																		Date:</td>
																	<td width="90" class="dataValue">08-12-2017</td>
																	<td width="110" class="dataHeader">Amt Disbursed:</td>
																	<td width="90" align="right" class="dataAmtValue">27,500</td>
																	<td width="110" class="dataHeader">Info. As On:</td>
																	<td width="90" class="dataValue">29-02-2020</td>
																</tr>

																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		#:</td>
																	<td width="160" class="dataValue">xxxx</td>
																	<td width="110" class="dataHeader">Closed Date:</td>
																	<td width="90" class="dataValue">13-12-2019</td>
																	<td width="110" class="dataHeader">Current
																		Balance:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">Amount
																		Write-Off:</td>
																	<td width="90" class="dataValue" align="right">0  </td>
																</tr>

																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Instl
																		Freq:</td>
																	<td width="160" class="dataValue">MONTHLY</td>
																	<td width="110" class="dataHeader">Instl Amount:</td>
																	<td width="90" class="dataValue">1,480</td>
																	<td width="115" class="dataHeader">Amount
																		Overdue:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">DPD:</td>
																	<td width="90" class="dataValue">0</td>
																</tr>
																
																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Account Remarks:</td>
																	<td width="160" class="dataValue"></td>
																</tr>
																
																
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr>
																	<td>
																		<table width="1000px">
																			<tbody>
																				<tr>
																					<td class="dataHeader" height="25">Payment
																						History:</td>
																				</tr>
																				<tr>
																					<td>
																						<table align="left" border="1px" bordercolor="#A7CBE3"
																							style="border-collapse: collapse; "
																							cellpadding="0" cellspacing="0">
																							
																							<tbody>
																																					<tr align="center" bordercolor="#A7CBE3" style="border-width: thin;">
															<td width="25px" class="subHeader2"></td>
															<td width="40px" class="subAccHeader">January</td>
															<td width="40px" class="subAccHeader">February</td>
															<td width="40px" class="subAccHeader">March</td>
															<td width="40px" class="subAccHeader">April</td>
															<td width="40px" class="subAccHeader">May</td>
															<td width="40px" class="subAccHeader">June</td>
															<td width="40px" class="subAccHeader">July</td>
															<td width="40px" class="subAccHeader">August</td>
															<td width="40px" class="subAccHeader">September</td>
															<td width="40px" class="subAccHeader">October</td>
															<td width="40px" class="subAccHeader">November</td>
															<td width="40px" class="subAccHeader">December</td>

														</tr>
																																										<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2019</td>
																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																													</tr>
														   
													</tbody>
																						</table>
																					</td>
																				</tr>																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
												
												
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
<td height="20"></td>
</tr>
 <tr>
	<td>
		<table width="1000" border="0" align="center" cellpadding="0" cellspacing="0">
									<tr>
				<td colspan="2">
					<table align="center" border="0" width="1020px" cellpadding="0" cellspacing="0">
						<tbody>
														<tr height="20">
								<td height="20" align="center" class="mainAccHeader" width="20px">2
								</td>
								<td height="20" align="center"  width="20px">
								</td>
								<td align="center">
									<table align="left" border="0" width="1000px"
										bgcolor="e6e6ff" cellpadding="2" cellspacing="0">
										<tbody>
											<tr height="20">


												<td align="left" width="450" class="AccHeader">Member
													& Account Information with Credit Grantor: <font
													class="maroonFields">XXXX  (Branch: POLLACHI) </font>
												</td>
												

											</tr>
										</tbody>
									</table>
								</td>
							</tr>
													</tbody>
					</table>
				</td>
			</tr>
						
			
						
			<tr>
				<td>
					<table width="1049" border="0">
												<tr>
							<td colspan="2">
								<table align="center" border="0" cellpadding="0"
									cellspacing="0" width="1000px">
									<tbody>
										<tr>
											<td>
												<table align="center" border="0" width="1000px">
													<tbody>
														<tr>

															<td>
																<table border="0" width="1000px">
																	<tbody>
																		<tr>
																			<td height="10px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="110 px" class="dataHeader1">Member
																				Name:</td>
																			<td align="left" width="270 px" class="dataValue2">
																				M MALLIKA</td>

																			<td width="70 px" class="dataHeader1">DOB/Age:</td>
																			<td width="210 px" class="dataValue2">08-04-1982 /  33 years ()  </td>

																			<td width="70 px" class="dataHeader1">Info. As On:</td>
																			<td width="130 px" class="dataValue2">31-12-2016</td>
																		</tr>
																		<tr>
																			<td height="3px"></td>
																		</tr>

																		<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td class="dataHeader1" valign="top" width="100 px">Relationships:</td>
																			<td valign="top">
																				<table width="220px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> NACHIMUTHU (Father) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> MURUGANANDHAM (Husband) </td>
																					</tr>

																				</table>
																			</td>

																			<td class="dataHeader1" valign="top">ID(s):</td>
																			<td valign="top">
																				<table width="200px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> 28331729173463 (Voters Id) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> V5553233 (Ration Card) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>

																			<td class="dataHeader1" valign="top" width="100 px">Phone
																				Numbers:</td>
																			<td valign="top">
																				<table width="150px" cellpadding="0"
																					cellspacing="0">
																					  <td>
                                                                                      
                                                                                                                                                                         
																					<tr>
																						<td class="dataValue2"> 6517635471 </td>
																					</tr>
																					

                                                                                                                                                                            </td>
																				</table>
																			</td>
																		</tr>
																		<tr>
																			<td height="1px"></td>
																		</tr>
																																				<tr>
																			<td align="left" width="100 px" class="dataHeader1">Current
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="3">O NO 5/105B N NO 5/112 PARAMADAIYUR KAMBALAPATTI POLLACHI POLLACHI TN 642007</td>
																			
																			<td class="dataHeader1" valign="top" width="200 px">Monthly Household
																				Income:</td>
																			<td valign="top">
																				<table width="100px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>

																		</tr>
																																				<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="100 px" class="dataHeader1">Other
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="5"><div style="width:890px;overflow:none">O NO 5/105B N NO 5/112 PARAMADAIYUR KAMBALAPATTI KAMBALAPATTI POLLACHI TN 642007</div></td>
																		</tr>


																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
												<tr>
							<td colspan="2">
								<table align="center" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" colspan="1" height="15" width="1010">
											<hr size="1" style="color: #A7CBE3;" />
										</td>
									</tr>
								</table>
							</td>

						</tr>
						<tr>
							<td>
								<table align="center" border="0" width="1000" cellpadding="0" cellspacing="0">
									<tr>
										<td class="container" width="30">

											
																								<div class="headClosed" width="30">
													<div class="vertClosed" width="30" align="center" style="background: #e1f0be; text-align: center; ">CLOSED</div>
												</div>
																							

 

										</td>

										<td>
											<table>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr height="10"></tr>
																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		Type:</td>
																	<td width="160" class="dataValue">JLG INDIVIDUAL</td>
																	<td width="110" class="dataHeader">Disbursed
																		Date:</td>
																	<td width="90" class="dataValue">22-06-2015</td>
																	<td width="110" class="dataHeader">Amt Disbursed:</td>
																	<td width="90" align="right" class="dataAmtValue">20,184</td>
																	<td width="110" class="dataHeader">Info. As On:</td>
																	<td width="90" class="dataValue">31-12-2016</td>
																</tr>

																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		#:</td>
																	<td width="160" class="dataValue">CI-Ceased/Membership Terminated</td>
																	<td width="110" class="dataHeader">Closed Date:</td>
																	<td width="90" class="dataValue">22-12-2016</td>
																	<td width="110" class="dataHeader">Current
																		Balance:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">Amount
																		Write-Off:</td>
																	<td width="90" class="dataValue" align="right">0  </td>
																</tr>

																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Instl
																		Freq:</td>
																	<td width="160" class="dataValue">MONTHLY</td>
																	<td width="110" class="dataHeader">Instl Amount:</td>
																	<td width="90" class="dataValue">1,366</td>
																	<td width="115" class="dataHeader">Amount
																		Overdue:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">DPD:</td>
																	<td width="90" class="dataValue">0</td>
																</tr>
																
																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Account Remarks:</td>
																	<td width="160" class="dataValue"></td>
																</tr>
																
																
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr>
																	<td>
																		<table width="1000px">
																			<tbody>
																				<tr>
																					<td class="dataHeader" height="25">Payment
																						History:</td>
																				</tr>
																				<tr>
																					<td>
																						<table align="left" border="1px" bordercolor="#A7CBE3"
																							style="border-collapse: collapse; "
																							cellpadding="0" cellspacing="0">
																							
																							<tbody>
																																					<tr align="center" bordercolor="#A7CBE3" style="border-width: thin;">
															<td width="25px" class="subHeader2"></td>
															<td width="40px" class="subAccHeader">January</td>
															<td width="40px" class="subAccHeader">February</td>
															<td width="40px" class="subAccHeader">March</td>
															<td width="40px" class="subAccHeader">April</td>
															<td width="40px" class="subAccHeader">May</td>
															<td width="40px" class="subAccHeader">June</td>
															<td width="40px" class="subAccHeader">July</td>
															<td width="40px" class="subAccHeader">August</td>
															<td width="40px" class="subAccHeader">September</td>
															<td width="40px" class="subAccHeader">October</td>
															<td width="40px" class="subAccHeader">November</td>
															<td width="40px" class="subAccHeader">December</td>

														</tr>
																																										<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2016</td>
																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																													</tr>
														   
													</tbody>
																						</table>
																					</td>
																				</tr>																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
												
												
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
<td height="20"></td>
</tr>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>MALLIKA S</title>
<style type="text/css">
@media print
{
  table { page-break-after:auto; 
  -webkit-print-color-adjust:exact;}
  thead { display:table-header-group; }
  tfoot { display:table-footer-group; }
  body
	{
	margin-top:10px;
	margin-bottom:10px;
	margin-right:25px;
	margin-left:30px;
	}
}


.box {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: #FFFFFF;
	border-collapse: collapse;
	text-align: left;
	-moz-box-shadow: 0px 0px 30px #DADADA;
	-webkit-box-shadow: 0px 0px 30px #DADADA;
	box-shadow: 0px 0px 30px #DADADA;
}

.box1 {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
}

.tabStyle {
	background: #FFFFFF;
	border-style: inset;
	border-width: thin;
	border-color: black;
	border-collapse: collapse;
}

.rowStyle {
	background: #FFFFFF;
	border-style: solid;
	border-width: thin;
	border-color: grey;
	border-collapse: collapse;
}

.box1 tr:nt-child(even) {
	background-color: white;
}

.box1 tr:nth-child(odd) {
	background-color: #F1F3F5;
}

.style14 {
	font-face: segoe ui semibold;
	font-size: 2px;
}

.summarytable {
	background: #FFFFFF;
	border-style: solid;
	border-width: 0px;
	border-collapse: collapse;
	text-align: left;
	border-left: none;
	border-right: none;
}

.reportHead {
	font-family: segoe ui semibold;
	font-size: 24px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	font-variant: small-caps;
}
.dataHead {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	text-indent: 5px;
}
.mainHeader {
	font-family: segoe ui semibold;
	font-size: 16px;
	color: #FFFFFF;
	background: #0f3f6b;
	text-align: left;
	font-weight: 600;
	padding-bottom: 3px;
}

.subHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	text-align: left;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader1 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	border-width: thin;
	border-collapse: collapse;
	border-bottom: 1px solid #A7CBE3;
	border-left: 0px;
	border-right: 0px;
	border-top: 0px;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
}

.subHeader2 {
	font-family: segoe ui semibold;
	border-collapse: collapse;
	border-bottom: 0px;
	border-left: 1px solid #ffffff;
	border-right: 0px;
	border-top: 1px solid #ffffff;
	background: #FFFFFF;
	text-indent: 5px;
	font-weight: 600;
	white-space: nowrap;
}

.dataHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}

.dataValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
	word-wrap:break-word;
}
.dataValue2 {
	font-family: segoe ui semibold;
	font-size: 13px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	padding-left: 7px;	
	padding-top: 1px;
	word-wrap:break-word;
}

.dataAmtValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-align: right;
	padding-right: 7px;	
	padding-top: 1px;
}


.dataValueAlert {
	font-family: segoe ui semibold;
	font-size: 17px;
	font-weight: 600;
	color: #800000;
	text-align: left;
	padding-left: 12px;	
	padding-top: 1px;
	background-color:#ffe1dc;
}

.dataHeader1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	color: #0f3f6b;
	font-weight: 600;
	text-align: left;
	text-indent: 5px;
}
.dataHeader2 {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
	white-space: nowrap;
	padding-top: 2px;
}
.dataValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-align: left;
	text-indent: 5px;
}

.mainAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #FFFFFF;
	background: #0f3f6b;
	font-weight: 600;
}

.AccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	font-weight: 600;
	text-indent: 5px;
}

.subAccHeader {
	font-family: segoe ui semibold;
	font-size: 13px;
	color: #0f3f6b;
	background: #e6e6ff;
	font-weight: 600;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	text-align: center;
	
}

.AccValue {
	font-family: segoe ui semibold;
	font-size: 14px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
}
.AccValue1 {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 600;
	color: #464646;
	text-indent: 5px;
	border-width: thin;
	border-bottom: 1px solid #A7CBE3;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	text-align: center;

}

.AccSummaryTab {
	border-width: thin;
	border-collapse: collapse;
	border-left: 1px solid #A7CBE3;
	border-right: 1px solid #A7CBE3;
	border-top: 1px solid #A7CBE3;
	border-bottom: 0px;
	text-indent: 5px;
}

.disclaimerValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
}

.infoValue {
	font-family: segoe ui semibold;
	font-size: 12px;
	font-weight: 500;
	color: grey;
	padding-right: 15px;
	font-style: normal;
}

.maroonFields {
	color: Maroon;
	font-family: segoe ui semibold;
	font-size: 15px;
	font-weight: 600;
}

.container {
	/* this will give container dimension, because floated child nodes don't give any */
	/* if your child nodes are inline-blocked, then you don't have to set it */
	overflow: auto;
}

.container .headActive {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 11em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #ffe1dc;
	color: #be0000;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headActive .vertActive {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #be0000;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
	transform: rotate(-270deg) translate(1em, 0);
	transform-origin: -5px 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: -5px 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: -5px 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

.container .headClosed {
	/* float your elements or inline-block them to display side by side */
	float: left;
	/* these are height and width dimensions of your header */
	height: 11em;
	width: 1.5em;
	/* set to hidden so when there's too much vertical text it will be clipped. */
	overflow: hidden;
	/* these are not relevant and are here to better see the elements */
	background: #e1f0be;
	color: #415a05;
	margin-right: 1px;
	font-family: segoe ui ;
	font-weight:bold;
}

.container .headClosed .vertClosed {
	/* line height should be equal to header width so text will be middle aligned */
	line-height: 1.5em;
	/* setting background may yield better results in IE text clear type rendering */
	background: #ffe1dc;
	color: #415a05;
	display: block;
	/* this will prevent it from wrapping too much text */
	white-space: nowrap;
	/* so it stays off the edge */
	padding-left: 3px;
	font-family: segoe ui ;
	font-weight:bold;
	/* CSS3 specific totation code */
	/* translate should have the same negative dimension as head height */
		transform: rotate(-270deg) translate(1em, 0);
	transform-origin: 0 30px;
	-moz-transform: rotate(-270deg) translate(1em, 0);
	-moz-transform-origin: 0 30px;
	-webkit-transform: rotate(-270deg) translate(1em, 0);
	-webkit-transform-origin: 0 30px;
	-ms-transform-origin:none;-ms-transform:none;-ms-writing-mode:tb-rl;*writing-mode:tb-rl;
}

</style>
</head>

<body style="font-family: segoe ui semibold, arial, verdana;">
	<table class="box" align="center" border="0px" cellpadding="0"
		cellspacing="0" width="1050">
		<thead>
			<tr>
				<td>
					<table align="center" border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0">
										<tbody>
											<tr>
												<td>
													<table align="center" border="0" width="1020px">
														<tbody>
															<tr height="10">
																<td></td>
															</tr>
															<tr>

																<td colspan="2" valign="top"><img src="data:image/gif;base64,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" 
alt="CRIF HighMark Credit Information Services Pvt. Ltd." align="left"/>
																</td>
																<td width="150"></td>

																<td align="left" width="250" valign="top">
																	<table border="0" cellpadding="0" cellspacing="0">
																		<tbody>
																			<tr>
																				<td align="center" class="reportHead">		
GROUP DETAILS
																				</td>
																			</tr>
																			
																		</tbody>
																	</table>
																</td>
																<td width="70"></td>
																<td rowspan="2" align="right" valign="top" width="350">
																	<table>
																		<tbody>
																			<tr>
																				<td class="dataHeader1">CHM Ref #:</td>
																				<td class="dataValue1">DEVM230210CR375075409</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Prepared For:</td>
																				<td class="dataValue1">DEVMUNI LEASING AND FINANCE LIMITED</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Date of Request:</td>
																				<td class="dataValue1">10-02-2023 16:23:31</td>
																			</tr>
																			<tr>
																				<td class="dataHeader1">Date of Issue:</td>
																				<td class="dataValue1">10-02-2023</td>
																			</tr>



																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td>
													<hr size="1" style="color: #C8C8C8;" />
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
				</td>
			</tr>						
</thead>
                            							<tr>
								<td>

									<table align="center" border="0" cellpadding="0"
										cellspacing="0">
										<tbody>
											<tr height="10">
												<td align="right" bgcolor="#FFFFFF" class="infoValue"></td>
											</tr>
											<tr height="20">
												<td align="right" bgcolor="#FFFFFF" class="infoValue">Tip:
													All amounts are in INR</td>
											</tr>
											<tr></tr>
											<tr>
												<td>
													<table align="center" bgcolor="#0f3f6b" border="0"
														width="1020px">
														<tbody>
															<tr height="20">
																<td width="10"></td>
																<td class="mainHeader">Summary</td>
															</tr>

														</tbody>
													</table>
												</td>

											</tr>

										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td height="25" align="right" bgcolor="#FFFFFF"
									class="infoValue">Tip: Current Balance, Disbursed Amount &
									Instalment Amount is considered ONLY for ACTIVE account
									&nbsp;&nbsp;</td>
							</tr>
							<tr>
								<td height="15"></td>
							</tr>

							<tr>
								<td>
									<table align="center" border="0" cellpadding="0"
										cellspacing="0" width="1050">
										<tbody>
											<tr>
												<td>
													<table align="center"
														style="border-collapse: collapse; border: 2px solid #A7CBE3;"
														cellspacing="0" cellpadding="2" width="1000">
														<tbody>
															<tr height="20">
																<td>
																	<table align="center" border="0px" cellspacing="0"
																		cellpadding="0" width="1000">
																		<tbody>
																			<tr>
																				<td width="center">
																					<table align="center" border="0px" cellspacing="0"
																						cellpadding="0" width="1000">
																						<thead>
																							<tr height="25">
																								<td width="150" class="subHeader1" rowspan="2">Type</td>
																								<td align="center" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Association</td>
																								<td align="center" width="175" scope="colgroup"
																									colspan="3" class="dataHeader2">Account
																									Summary</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Disbursed
																									Amount</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Instalment
																									Amount</td>
																								<td align="right" width="175" scope="colgroup"
																									colspan="2" class="dataHeader2">Current
																									Balance</td>
																								<td align="right" width="5" ></td>
																							</tr>
																							<tr height="20">

																								<td align="center" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Active</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Closed</td>
																								<td align="center" width="50" scope="col"
																									class="subHeader1">Default</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Own</td>
																								<td align="right" width="50" scope="col"
																									class="subHeader1">Other</td>
																								<td align="right" width="5" class="subHeader1"></td>
																							</tr>


																						</thead>
																						<tbody>

																							<tr height="20">
																								<td align="left" class="dataHeader">Primary
																									Match</td>
																								<td align="center"><span class="AccValue">0</span>
																								</td>
																								<td align="center"><span class="AccValue">1</span>
																								</td>
																								<td align="center"><span class="AccValue">0</span>
																								</td>
																								<td align="center"><span class="AccValue">1</span>
																								</td>
																								<td align="center"><span class="AccValue">0</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>																								
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								<td align="right"><span class="AccValue">-</span>
																								</td>
																								
																							</tr>
																							<tr>
																								<tr height="20">
																									<td align="left" class="dataHeader">Secondary
																										Match</td>

																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="center"><span class="AccValue">0</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>																									
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									<td align="right"><span class="AccValue">-</span>
																									</td>
																									
																								</tr>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																			<tr>
																				<td height="10"></td>
																			</tr>


																		</tbody>
																	</table>
																</td>

															</tr>
														</tbody>
													</table>
												</td>
											</tr>

										</tbody>
									</table>
								</td>

							</tr>
														
							 <tr>
				<td height="30px"></td>
			</tr>
			<tr>
			<td>
				</td>
</tr>					 
						 
			<tr>
				<td>
					<table align="center" border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr height="30"></tr>

							<tr>
								<td>
									<table align="center" bgcolor="#0f3f6b" border="0"
										width="1020px">
										<tbody>
											<tr height="20">
												<td width="10"></td>
												<td class="mainHeader">Account Details - Primary</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table> <br>
				</td>
			</tr>
     <tr>
	<td>
		<table width="1000" border="0" align="center" cellpadding="0" cellspacing="0">
									<tr>
				<td colspan="2">
					<table align="center" border="0" width="1020px" cellpadding="0" cellspacing="0">
						<tbody>
														<tr height="20">
								<td height="20" align="center" class="mainAccHeader" width="20px">1
								</td>
								<td height="20" align="center"  width="20px">
								</td>
								<td align="center">
									<table align="left" border="0" width="1000px"
										bgcolor="e6e6ff" cellpadding="2" cellspacing="0">
										<tbody>
											<tr height="20">


												<td align="left" width="450" class="AccHeader">Member
													& Account Information with Credit Grantor: <font
													class="maroonFields">XXXX  (Branch: POLLACHI) </font>
												</td>
												

											</tr>
										</tbody>
									</table>
								</td>
							</tr>
													</tbody>
					</table>
				</td>
			</tr>
						
			
						
			<tr>
				<td>
					<table width="1049" border="0">
												<tr>
							<td colspan="2">
								<table align="center" border="0" cellpadding="0"
									cellspacing="0" width="1000px">
									<tbody>
										<tr>
											<td>
												<table align="center" border="0" width="1000px">
													<tbody>
														<tr>

															<td>
																<table border="0" width="1000px">
																	<tbody>
																		<tr>
																			<td height="10px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="110 px" class="dataHeader1">Member
																				Name:</td>
																			<td align="left" width="270 px" class="dataValue2">
																				MALLIKA M</td>

																			<td width="70 px" class="dataHeader1">DOB/Age:</td>
																			<td width="210 px" class="dataValue2">02-05-1982 /  32 years ()  </td>

																			<td width="190 px" class="dataHeader1">Info. As On:</td>
																			<td width="100 px" class="dataValue2">01-05-2015</td>
																		</tr>
																		<tr>
																			<td height="3px"></td>
																		</tr>

																		<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td class="dataHeader1" valign="top" width="100 px">Relationships:</td>
																			<td valign="top">
																				<table width="220px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> NACHIMUTHU (Father) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> MURUGANANDHAM (Husband) </td>
																					</tr>

																				</table>
																			</td>

																			<td class="dataHeader1" valign="top">ID(s):</td>
																			<td valign="top">
																				<table width="200px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"> XXXXXXXXXXXX (UID) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"> 42443912910385 (Voters Id) </td>
																					</tr>
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>

																			<td class="dataHeader1" valign="top" width="190 px">Phone
																				Numbers:</td>
																			<td valign="top">
																				<table width="100px" cellpadding="0"
																					cellspacing="0">
																					 
																					<tr>
																						<td class="dataValue2"> 6692787823 </td>
																					</tr>
																																									</table>
																			</td>
																		</tr>
																		
																		<tr>
																			<td align="left" width="110 px" class="dataHeader1">Group ID:</td>
																			<td align="left" width="270 px" class="dataValue2">28275352_VASANTHAM HBG PAR  </td>	
																			
																			<td width="130 px" class="dataHeader1">Group Creation Date:</td>
																			<td width="150 px" class="dataValue2">  </td>
																			
																			
																			<td width="70 px" class="dataHeader1"></td>
																			<td width="130 px" class="dataValue2"></td>
																			
																		</tr>							
																		
																		
																		
																		
																		
																		
																		
																		
																		
																		<tr>
																			<td height="3px"></td>
																		</tr>
																																				<tr>
																			<td align="left" width="100 px" class="dataHeader1">Current
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="3">O NO 5/105 B,  N NO 5/112 PARAMADAIYUR  KAMBALAPATTI POLLACHI TN 642007</div></td>
																				
																			<td width="190 px" class="dataHeader1" valign="top">Monthly Household Income:</td>
																			<td valign="top">
																				<table width="100px" cellpadding="0"
																					cellspacing="0">
																					<tr>
																						<td class="dataValue2"></td>
																					</tr>
																				</table>
																			</td>
																		</tr>
																																				<tr>
																			<td height="3px"></td>
																		</tr>
																		<tr>
																			<td align="left" width="100 px" class="dataHeader1">Other
																				Address:</td>
																			<td align="left" width="200 px" class="dataValue2"
																				colspan="5"><div style="width:890px;overflow:none"></div></td>
																		</tr>


																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
												<tr>
							<td colspan="2">
								<table align="center" cellpadding="0" cellspacing="0">
									<tr>
										<td align="center" colspan="1" height="15" width="1010">
											<hr size="1" style="color: #A7CBE3;" />
										</td>
									</tr>
								</table>
							</td>

						</tr>
						<tr>
							<td width="30">
								<table align="center" cellpadding="0" cellspacing="0">
									<tr>
										<td class="container" width="30">

											
																								<div class="headClosed" width="30">
													<div class="vertClosed" width="30" align="center" style="background: #e1f0be;">CLOSED</div>
												</div>
																							

 

										</td>

										<td>
											<table>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr height="10"></tr>
																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		Type:</td>
																	<td width="160" class="dataValue">JLG GROUP</td>
																	<td width="110" class="dataHeader">Disbursed
																		Date:</td>
																	<td width="90" class="dataValue">29-05-2014</td>
																	<td width="110" class="dataHeader">Amt Disbursed:</td>
																	<td width="90" align="right" class="dataAmtValue">1,00,552</td>
																	<td width="110" class="dataHeader">Info. As On:</td>
																	<td width="90" class="dataValue">30-04-2015</td>
																</tr>

																<tr height="25">
																	<td width="110" class="dataHeader">&nbsp;&nbsp;Account
																		#:</td>
																	<td width="160" class="dataValue">CI-Ceased/Membership Terminated</td>
																	<td width="110" class="dataHeader">Closed Date:</td>
																	<td width="90" class="dataValue">20-04-2015</td>
																	<td width="110" class="dataHeader">Current
																		Balance:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">Amount
																		Write-Off:</td>
																	<td width="90" class="dataValue" align="right">0  </td>
																</tr>

																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Instl
																		Freq:</td>
																	<td width="160" class="dataValue">MONTHLY</td>
																	<td width="110" class="dataHeader">Instl Amount:</td>
																	<td width="90" class="dataValue">9,605</td>
																	<td width="115" class="dataHeader">Amount
																		Overdue:</td>
																	<td width="90" align="right" class="dataAmtValue">0</td>
																	<td width="110" class="dataHeader">DPD:</td>
																	<td width="90" class="dataValue">0</td>
																</tr>
																
																<tr height="25">
																	<td width="100" class="dataHeader">&nbsp;&nbsp;Account Remarks:</td>
																	<td width="160" class="dataValue"></td>
																</tr>
																
																
																<tr height="25">																	<td width="100" class="dataHeader">&nbsp;&nbsp;No of Borrowers:</td>																	<td width="160" class="dataValue"></td>																	<td width="110" class="dataHeader">Active Borrower:</td>																	<td width="90" class="dataValue">
																</td>																	<td width="115" class="dataHeader"></td>																	<td width="90" align="right" class="dataAmtValue"></td>																	<td width="110" class="dataHeader"></td>																	<td width="90" class="dataValue"></td>																</tr>							
																
																
																
																
																
																
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table align="center" border="0" cellpadding="0"
															cellspacing="0" width="1000px">
															<tbody>
																<tr>
																	<td>
																		<table width="1000px">
																			<tbody>
																				<tr>
																					<td class="dataHeader" height="25">Payment
																						History:</td>
																				</tr>
																				<tr>
																					<td>
																						<table align="left" border="1px" bordercolor="#A7CBE3"
																							style="border-collapse: collapse; "
																							cellpadding="0" cellspacing="0">
	
<tbody>
																																					<tr align="center" bordercolor="#A7CBE3" style="border-width: thin;">
															<td width="25px" class="subHeader2"></td>
															<td width="40px" class="subAccHeader">January</td>
															<td width="40px" class="subAccHeader">February</td>
															<td width="40px" class="subAccHeader">March</td>
															<td width="40px" class="subAccHeader">April</td>
															<td width="40px" class="subAccHeader">May</td>
															<td width="40px" class="subAccHeader">June</td>
															<td width="40px" class="subAccHeader">July</td>
															<td width="40px" class="subAccHeader">August</td>
															<td width="40px" class="subAccHeader">September</td>
															<td width="40px" class="subAccHeader">October</td>
															<td width="40px" class="subAccHeader">November</td>
															<td width="40px" class="subAccHeader">December</td>

														</tr>
																																										<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2015</td>
																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">XXX</td>	
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																														
																																<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																													</tr>
														 														<tr align="center">
															<td width="25px" class="AccValue1" bgcolor="e6e6ff">2014</td>
																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">-</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																																															<td width="80px" class="AccValue1" bgcolor="#FFFFFF">000</td>
																																													</tr>
														  
													</tbody>
																						</table>
																					</td>
																				</tr>																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
												
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
<td height="20"></td>
</tr>
<tr>
				<td height="30px"></td>
			</tr>
						<tr>
				<td>
				<table cellpadding="0" cellspacing="0" align="center" border="0">
					<tbody>
						<tr height="10"></tr>

						<tr>

							<td>
							<table width="1020px" align="center" bgcolor="#0f3f6b" border="0">
								<tbody>
									<tr height="20">
										<td width="10"></td>
										<td class="mainHeader">Inquiries (reported for past 24
										months)</td>
									</tr>
								</tbody>
							</table>
							</td>

						</tr>
						<tr height="10"></tr>
					</tbody>
				</table>
				</td>
			</tr>

			<tr>
				<td height="5"></td>
			</tr>
			<tr>
				<td>
				<table cellpadding="0" cellspacing="0" align="center" border="0">
					<tbody>
						<tr>

							<td>
							<table class="box1" cellpadding="0" cellspacing="0" width="1000px" align="center" border="0px">
								<tbody>
									<tr height="20">
										<td height="20" width="200" class="subHeader" align="left">Credit Grantor</td>
										<td width="200" class="subHeader" align="right">Date of Inquiry</td>
										<td width="200" class="subHeader" align="left">Purpose</td>
										<td width="200" class="subHeader" align="right">Amount</td>
										<td width="200" class="subHeader" align="left">Remark</td>
									</tr>
																					<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 17-11-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 30-06-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 24,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 07-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 23,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 07-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 22,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 05-05-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 25,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 06-09-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 6,42,007 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 25-10-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > JLG Individual </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 27-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 36,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 12-04-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 2,650 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 20-12-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Personal Loan </td>
												<td align="left" class="AccValue" > 1,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 11-03-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> DEVMUNI LEASING AND FINANCE L </td>
												<td align="right" class="dataValue"> 09-02-2023 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Personal Loan </td>
												<td align="left" class="AccValue" > 8,500 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 23-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 25,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 07-12-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 12-01-2023 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 22-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 2,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 19-05-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 30,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 26-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 38,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 17-05-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 25,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 06-12-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > JLG Individual </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 10-11-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 25,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 27-07-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 25,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 09-05-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > OTHER </td>
												<td align="left" class="AccValue" > 25,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 20-12-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 2,00,000 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
																						<tr height="20">
												<td align="left" class="dataValue"> XXXX </td>
												<td align="right" class="dataValue"> 16-12-2022 </td>
												<td align="left" class="dataValue" style="padding-right:30px;"  > Housing Loan </td>
												<td align="left" class="AccValue" > 0 </td>
												<td align="left" class="dataValue">  </td>
											</tr>
											
								</tbody>
							</table>
							</td>
						</tr>

					</tbody>
				</table>
				</td>
			</tr>
            
			<tr>
				<td>
					<table align="center" border="0" cellpadding="0"
						cellspacing="0">
						<tbody>
                            							<tr>
								<td>
									<table width="1000px">
										<tbody>
											<tr>
												<td align="center" class="AccHeader">-END OF
													INDIVIDUAL REPORT-</td>

											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>

								<td height="10"></td>
							</tr>
							<tr>
												<td>
												<table align="center" border="0" cellpadding="0"
													cellspacing="0">
													<tbody>
														<tr height="10"></tr>

														<tr>

															<td>
															<table align="center" bgcolor="#0f3f6b" border="0"
																width="1020px">
																<tbody>
																	<tr height="20">
																		<td width="10"></td>
																		<td class="mainHeader">Appendix</td>
																	</tr>
																</tbody>
															</table>
															</td>

														</tr>
														<tr height="10"></tr>
													</tbody>
												</table>
												</td>
											</tr>

											<tr>
												<td height="5"></td>
											</tr>

											<tr>
												<td>
												<table align="center" border="0" cellpadding="0"
													cellspacing="0">
													<tbody>
														<tr>

															<td>
															<table class="box1" align="center" border="0px"
																cellpadding="0" cellspacing="0" width="1000px">
																<tbody>
																	<tr height="20">
																		<td align="left" class="subHeader" width="250">Section</td>
																		<td align="left" class="subHeader" width="220">Code</td>
																		<td align="left" class="subHeader" width="480">Description</td>

																	</tr>
																	<tr height="20">
																		<td align="left" class="dataValue1" width="250">Account
																		Summary</td>
																		<td align="left" class="dataValue1" width="220">Number
																		of Delinquent Accounts</td>
																		<td align="left" class="dataValue1" width="480">
																		Indicates number of accounts that the applicant has
																		defaulted on within the last 6 months</td>

																	</tr>
																	<tr height="20"  bgcolor="#F1F3F5">
																		<td align="left" class="dataValue1" width="250">Account
																		Information - Credit Grantor</td>
																		<td align="left" class="dataValue1" width="220">XXXX</td>
																		<td align="left" class="dataValue1" width="480">
																		Name of grantor undisclosed as credit grantor is
																		different from inquiring institution</td>

																	</tr>
																	<tr height="20">
																		<td align="left" class="dataValue1" width="250">Account
																		Information - Account #</td>
																		<td align="left" class="dataValue1" width="220">xxxx</td>
																		<td align="left" class="dataValue1" width="480">
																		Account Number undisclosed as credit grantor is
																		different from inquiring institution</td>

																	</tr>
																	<tr height="20"  bgcolor="#F1F3F5">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">XXX</td>
																		<td align="left" class="dataValue1" width="480">Data
																		not reported by institution</td>

																	</tr>
																	<tr height="20">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">&nbsp;&nbsp;&nbsp;&nbsp;-</td>
																		<td align="left" class="dataValue1" width="480">Not applicable</td>

																	</tr>
																	<tr height="20"  bgcolor="#F1F3F5">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">STD</td>
																		<td align="left" class="dataValue1" width="480">Account 
																		Reported as STANDARD Asset</td>

																	</tr>
																	<tr height="20">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">SUB</td>
																		<td align="left" class="dataValue1" width="480">Account 
																		Reported as SUB-STANDARD Asset</td>

																	</tr>
																	<tr height="20"  bgcolor="#F1F3F5">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">DBT</td>
																		<td align="left" class="dataValue1" width="480">Account 
																		Reported as DOUBTFUL Asset</td>

																	</tr>
																	<tr height="20">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">LOS</td>
																		<td align="left" class="dataValue1" width="480">Account 
																		Reported as LOSS Asset</td>

																	</tr>
																	<tr height="20"  bgcolor="#F1F3F5">
																		<td align="left" class="dataValue1" width="250">Payment
																		History / Asset Classification</td>
																		<td align="left" class="dataValue1" width="220">SMA</td>
																		<td align="left" class="dataValue1" width="480">Account 
																		Reported as SPECIAL MENTION</td>

																	</tr>
                                                                    																	<tr height="20">
														<td align="left" class="dataValue1" width="250">Account Information - Account #</td>
														<td align="left" class="dataValue1" width="220">CI-Ceased/Membership Terminated</td>
														<td align="left" class="dataValue1" width="480">
																		Credit Institution has Ceased to Operate or Membership Terminated</td>

														</tr>
												

																</tbody>
															</table>
															</td>
														</tr>

													</tbody>
												</table>
												</td>
											</tr>
                                            							 
							<tr>
<td height="20" > </td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
					<tfoot>
		<tr>
			<td>
				<table summary="" align="center" border="0" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td>
								<table summary="" border="0" width="1020px">
																			<tbody>
																				<tr height="10">
																					<td colspan="5">
																					<hr color="silver">
																					</td>
																				</tr>
																				<tr>
																					<td color="#CCCCCC" valign="top" width="70"
																						class="disclaimerValue">Disclaimer:</td>

																					<td colspan="4" class="disclaimerValue">This
																					document contains proprietary information to CRIF High
																					Mark and may not be used or disclosed to others,
																					except with the written permission of CRIF High Mark.
																					Any paper copy of this document will be considered
																					uncontrolled. If you are not the intended
																					recipient, you are not authorized to read, print,
																					retain, copy, disseminate, distribute, or use this
																					information or any part thereof.
																					</td>
																				</tr>

																				<tr>

																					<td><br>
																					<br>
																					</td>

																					<td color="#CCCCCC " align="left" width="300"
																						class="disclaimerValue">Copyrights reserved
																					(c) 2023</td>


																					<td color="#CCCCCC " align="center" width="400"
																						class="disclaimerValue">CRIF High Mark Credit
																					Information Services Pvt. Ltd</td>
																					<td color="#CCCCCC " align="right" width="300"
																						class="disclaimerValue">Company Confidential
																					Data</td>
																					<td width="70"><br>
																					<br>
																					</td>
																				</tr>

																			</tbody>
																		</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
					
	</tfoot>
			</tbody>
	</table>
</body>
</html>