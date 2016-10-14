<?php ob_start();
	require_once("zendDB.php");
	$query_string = $_SERVER['QUERY_STRING'];
	parse_str($query_string);
/*
	echo '<pre>';
	var_dump($_REQUEST);
	echo '</pre>';
	exit;
*/

	$quotationId	=  $_REQUEST['desc_val'] ;
	$change			= $_REQUEST['change'] ;
	$dateFrom 	=  $_REQUEST['Date_from'] ;
	$dateTo 			=  $_REQUEST['Date_to'] ;
	$nr_val 			=  $_REQUEST['nr_val'] ; 
	$nr  					=  $_REQUEST['Nr'] ;
	$country 			=  $_REQUEST['country'] ; 
	$service 			=  $_REQUEST['service'] ; 
	$days 				=  ($_REQUEST['Days'])?$_REQUEST['Days']:0; 
	$cat 				= $_REQUEST['cat'] ; 
	$hidManualChng 	=  $_REQUEST['hidManualChng'] ;
	$description 	=  $_REQUEST['description'] ; 
	$sgl 				=  ($_REQUEST['sgl']!='')?$_REQUEST['sgl']:0 ; 
	$dbl 				=  ($_REQUEST['dbl']!='')?$_REQUEST['dbl']:0 ; 
	$twn 				=  ($_REQUEST['twn']!='')?$_REQUEST['twn']:0 ;
	$tpl 					=	($_REQUEST['tpl']!='')?$_REQUEST['tpl']:0 ; 
	$qty 				=  $_REQUEST['qty'] ;
	$sel_unit			=  $_REQUEST['sel_unit'] ;
	$pax				=  $_REQUEST['pax'] ; 
	$tourId				= $_REQUEST['hid_TourID'] ;
	$sglsupp			= $_REQUEST['hid_sglsupp'] ;
	$tpldiscnt		= $_REQUEST['hid_tpldiscnt'] ;
	$pp					= $_REQUEST['hid_pp'] ; 
	$ppCost 		= $_REQUEST['hid_ppCost'];
	$multi 				= intval($_REQUEST['multi']); 
	$special			= $_REQUEST['special'];
	$op_amend	= $_REQUEST['op_amend'];
	$chk	= $_REQUEST['chk'];

	$array_of_booking = array();

	if($op_amend == "d"){
		if(!empty($chk)){
			if(strpos($chk, ",") !== false){
				$chk = substr($chk, 0, -1);
			}else{
				$chk = "'$chk'";
			}
			#$array_of_booking = explode(",", $chk);
			$sql = "SELECT  TourId ,
									q.QuotationName ,
									q.QuotationCode ,
									dbo.DFormat(DateTo, 'dd-MMM-yyyy') AS DateTo ,
									dbo.DFormat(DateFrom, 'dd-MMM-yyyy') AS DateFrom ,
									c.ConfPricePerPax ,
									c.Quantity ,
									c.Pax ,
									c.ConfPricePerPax * c.Pax * c.Quantity AS SumTotal ,
									c.ConfirmationsId
							FROM    dbo.tbConfirmations AS c
									INNER JOIN dbo.Quotation AS q ON q.QuotationId = c.QuotationId
							WHERE   ConfirmationsId IN ( $chk ); ";
			if($rs = $dbZend->fetchAll($sql)){
				foreach($rs as $row){
					$array_of_booking[] = array("TourId" => $row->TourId ,
																	"ConfirmationsId" => $row->ConfirmationsId ,
																	"QuotationName" => $row->QuotationName ,
																	"QuotationCode" => $row->QuotationCode ,
																	"DateTo" => $row->DateTo ,
																	"DateFrom" => $row->DateFrom ,
																	"ConfPricePerPax" => $row->ConfPricePerPax ,
																	"Quantity" => $row->Quantity ,
																	"Pax" => $row->Pax ,
																	"SumTotal" => $row->SumTotal);
				}
			}
		}
	}

?>
<html>
<head>
	<title>Book / Delete To Amend</title>
	<script src="/jslib/jquery-1.7.2.js"></script>
	<script>
	var url = "booking_confirm_amend.php";
		$(document).ready(function(){
			$('#book_confirm').click(function(){
				$('#error_msg').html("Please waiting......");
				var par = $('#frm').serialize();
				$.post(url, par, function(data){
					alert(data);
					$('#error_msg').html(data);
					window.opener.location.reload();
				});
			});
			/**/
			$('#book_delete').click(function(){
				var par = $('#frm').serialize();
				$('#error_msg').html("Please waiting......");
				$.post(url, par, function(data){
					alert(data);
					$('#error_msg').html(data);
					window.opener.location.reload();
				});
			});
			/**/
		});
	</script>
</head>
<body>
<form id="frm" method="post">
	<input type="hidden" name="desc_val" id="desc_val" value="<?=$quotationId?>" />
	<input type="hidden" name="change" id="change" value="<?=$change?>" />
	<input type="hidden" name="Date_from" id="Date_from" value="<?=$Date_from?>" />
	<input type="hidden" name="Date_to" id="Date_to" value="<?=$Date_to?>" />
	<input type="hidden" name="nr_val" id="nr_val" value="<?=$nr_val?>" />
	<input type="hidden" name="Nr" id="Nr" value="<?=$Nr?>" />
	<input type="hidden" name="country" id="country" value="<?=$country?>" />
	<input type="hidden" name="service" id="service" value="<?=$service?>" />
	<input type="hidden" name="Days" id="Days" value="<?=$days?>" />
	<input type="hidden" name="cat" id="cat" value="<?=$cat?>" />
	<input type="hidden" name="hidManualChng" id="hidManualChng" value="<?=$hidManualChng?>" />
	<input type="hidden" name="description" id="description" value="<?=$description?>" />
	<input type="hidden" name="sgl" id="sgl" value="<?=$sgl?>" />
	<input type="hidden" name="dbl" id="dbl" value="<?=$dbl?>" />
	<input type="hidden" name="twn" id="twn" value="<?=$twn?>" />
	<input type="hidden" name="tpl" id="tpl" value="<?=$tpl?>" />
	<input type="hidden" name="qty" id="qty" value="<?=$qty?>" />
	<input type="hidden" name="sel_unit" id="sel_unit" value="<?=$sel_unit?>" />
	<input type="hidden" name="pax" id="pax" value="<?=$pax?>" />
	<input type="hidden" name="hid_TourID" id="hid_TourID" value="<?=$tourId?>" />
	<input type="hidden" name="hid_sglsupp" id="hid_sglsupp" value="<?=$sglsupp?>" />
	<input type="hidden" name="hid_tpldiscnt" id="hid_tpldiscnt" value="<?=$tpldiscnt?>" />
	<input type="hidden" name="hid_pp" id="hid_pp" value="<?=$pp?>" />
	<input type="hidden" name="hid_ppCost" id="hid_ppCost" value="<?=$ppCost?>" />
	<input type="hidden" name="multi" id="multi" value="<?=$multi?>" />
	<input type="hidden" name="special" id="special" value="<?=$special?>" />
	<input type="hidden" name="op_amend" id="op_amend" value="<?=$op_amend?>" />
	<input type="hidden" name="chk" id="chk" value="<?=$chk?>" />
<?php
	if($op_amend == "a"){
		echo '<table cellpadding="5" cellspacing="0" border="0">';
		echo '<tr><th colspan="8">Book to Amend</th></tr>';
		##
		echo '<tr bgcolor="#c0c0c0">';
		echo '<th>TourID</th>';
		echo '<th>From/on</th>';
		echo '<th>To</th>';
		echo '<th>Description</th>';
		echo '<th>Sgl</th>';
		echo '<th>Dbl</th>';
		echo '<th>Tpl</th>';
		echo '<th>Pax</th>';
		echo '</tr>';
		##
		echo '<tr>';
		echo '<td>'.$hid_TourID.'</td>';
		echo '<td align="center">'.$Date_from.'</td>';
		echo '<td align="center">'.$Date_to.'</td>';
		echo '<td>'.$description.'</td>';
		echo '<td align="center">'.$sgl.'</td>';
		echo '<td align="center">'.$dbl.'</td>';
		echo '<td align="center">'.$tpl.'</td>';
		echo '<td align="center">'.$pax.'</td>';
		echo '</tr>';
		echo '</table>';
		##
		echo '<table cellpadding="5" cellspacing="0" border="0">';
		echo '<tr><th colspan="11">&nbsp;</th></tr>';
		echo '<tr bgcolor="#c0c0c0"><th colspan="11">Please select service box that you would like to add in itinerary</th></tr>';
		##
		echo '<tr>';
		echo '<th><input type="checkbox" id="chk_hotel" name="chk_hotel" value="1" />Hotel</th>';
		echo '<th><input type="checkbox" id="chk_guide" name="chk_guide" value="1" checked="checked" />Guide</th>';
		echo '<th><input type="checkbox" id="chk_flight" name="chk_flight" value="1"/>Flight</th>';
		echo '<th><input type="checkbox" id="chk_boat" name="chk_boat" value="1" checked="checked" />Boat / Train</th>';
		echo '<th><input type="checkbox" id="chk_restaurant" name="chk_restaurant" value="1" checked="checked" />Restaurant</th>';
		echo '<th><input type="checkbox" id="chk_vehicle" name="chk_vehicle" value="1" checked="checked" />Vehicle</th>';
		#echo '<th><input type="checkbox" id="chk_visa" name="chk_visa" value="1" checked="checked" />Visa</th>';
		echo '<th><input type="checkbox" id="chk_misc" name="chk_misc" value="1" checked="checked" />Miscellaneous</th>';
		echo '<th><input type="checkbox" id="chk_water" name="chk_water" value="1" checked="checked" />Water</th>';
		echo '<th><input type="checkbox" id="chk_entrancefee" name="chk_entrancefee" value="1" checked="checked" />Entrance fee</th>';
		echo '</tr>';
		##
		echo '<tr><th colspan="11">';
		echo '<input type="button" value="Confirm" id="book_confirm" name="book_confirm" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="button" value="Close" id="book_close" name="book_close" onclick="window.close();" />';
		echo '</th></tr>';
		##
		echo '</table>';
	}else if($op_amend == "d"){
		echo '<table cellpadding="5" cellspacing="0" border="0">';
		echo '<tr><th colspan="8">Delete to Amend</th></tr>';
		##
		echo '<tr bgcolor="#c0c0c0">';
		echo '<th>TourID</th>';
		echo '<th>From/on</th>';
		echo '<th>To</th>';
		echo '<th>Code</th>';
		echo '<th>Description</th>';
		echo '<th>p.p.</th>';
		echo '<th>Qty</th>';
		echo '<th>Pax</th>';
		echo '<th>Sum</th>';
		echo '</tr>';
		##
		foreach($array_of_booking as $booking_item){
			echo '<tr>';
			echo '<th>'.$booking_item['TourId'].'</th>';
			echo '<th>'.$booking_item['QuotationCode'].'</th>';
			echo '<th>'.$booking_item['QuotationName'].'</th>';
			echo '<th>'.$booking_item['DateFrom'].'</th>';
			echo '<th>'.$booking_item['DateTo'].'</th>';
			echo '<th>'.round($booking_item['ConfPricePerPax'], 2).'</th>';
			echo '<th>'.$booking_item['Quantity'].'</th>';
			echo '<th>'.$booking_item['Pax'].'</th>';
			echo '<th>'.round($booking_item['SumTotal'],2).'</th>';
			echo '</tr>';
		}
		echo '</table>';
		##
		echo '<table cellpadding="5" cellspacing="0" border="0">';
		echo '<tr><th colspan="11">&nbsp;</th></tr>';
		echo '<tr bgcolor="#c0c0c0"><th colspan="11">Please select service box that you would like to delete in itinerary</th></tr>';
		##
		echo '<tr>';
		echo '<th><input type="checkbox" id="chk_hotel" name="chk_hotel" value="1" />Hotel</th>';
		echo '<th><input type="checkbox" id="chk_guide" name="chk_guide" value="1" checked="checked" />Guide</th>';
		echo '<th><input type="checkbox" id="chk_flight" name="chk_flight" value="1"/>Flight</th>';
		echo '<th><input type="checkbox" id="chk_boat" name="chk_boat" value="1" checked="checked" />Boat / Train</th>';
		echo '<th><input type="checkbox" id="chk_restaurant" name="chk_restaurant" value="1" checked="checked" />Restaurant</th>';
		echo '<th><input type="checkbox" id="chk_vehicle" name="chk_vehicle" value="1" checked="checked" />Vehicle</th>';
		#echo '<th><input type="checkbox" id="chk_visa" name="chk_visa" value="1"/>Visa</th>';
		echo '<th><input type="checkbox" id="chk_misc" name="chk_misc" value="1" checked="checked" />Miscellaneous</th>';
		echo '<th><input type="checkbox" id="chk_water" name="chk_water" value="1" checked="checked" />Water</th>';
		echo '<th><input type="checkbox" id="chk_entrancefee" name="chk_entrancefee" value="1" checked="checked" />Entrance fee</th>';
		echo '</tr>';
		##
		echo '<tr><th colspan="11">';
		echo '<input type="button" value="Confirm" id="book_delete" name="book_delete" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="button" value="Close" id="book_close" name="book_close" onclick="window.close();" />';
		echo '</th></tr>';
		##
		echo '</table>';
	}
?>
</form>
<div id="error_msg" name="error_msg"></div>
</body>
</html>
<? ob_end_flush(); ?>