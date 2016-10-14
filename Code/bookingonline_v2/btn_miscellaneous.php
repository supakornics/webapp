<?
// load data //

$tour_id = $_REQUEST['id'] ;
if(!empty($_REQUEST['ssid'])){
	$ssid = $_REQUEST['ssid'];
}
if(!empty($_REQUEST['isid'])){
	$isid = $_REQUEST['isid'];
}
if(!empty($_REQUEST['ccode'])){
	$ccode = $_REQUEST['ccode'];
}


//$tour_id = 0.0002 ;
require_once("SQLServerDB.php");
require("connect.php") ;
require("fnct.php") ;
require("booking_misc_data.php") ;

require_once("config_Zend.php");

$array_status = array("", "OK", "CXL", "PD", "FULL", "WL", "Booked");

	// DevMark
function isCurrency($value){
	$pass = false;
	if(isset($value) && trim($value) != "" && is_numeric($value)){
		$pass = true;
	}
	return $pass;
}

function isNotNullAndNotEmpty($value){
	$pass = false;
	if(isset($value) && trim($value) != ""){
		$pass = true;
	}
	return $pass;
}

function RecalculateCostWithAdjustmentEntranceFee($EBId, $CostTypeId, $val=""){
	if((!isset($EBId) || empty($EBId)) && (!isset($CostTypeId) || empty($CostTypeId))){
		// var_dump('Error : $FBIdUniqueId/$CostTypeId can not be empty!');
		return "";
	}

	//Convert Text To Num
	if(isset($val)){
		$val = str_replace(",", "", $val);
		$val = (float)$val;
	}

	$arr = array();

	//Get Currency 
	$sql = "SELECT eb.ConfirmUS
				, eb.ConfirmLocal
    			, eb.ConfirmCurrency
				, eb.ExtraCostUS
				, eb.ExtraCostLocal
    			, eb.ExtraCostCurrency
    			, eb.CurrencyExtraCostId
    			, eb.ReductionUS
    			, eb.ReductionLocal
    			, eb.ReductionCurrency
    			, eb.CurrencyReductionId
				, brc.CurrencyId AS [CurrencyId]
    			, CAST (brc.CurrencyRate as FLOAT) AS [CurrencyRate]
    			, brc.Currency AS [Currency]
    			, CAST (brc.Price as FLOAT) AS [Price]
    			, brc.RepeatPax
    			, brc.CostTypeId
			FROM EntranceFeeBooking eb
			LEFT JOIN BookingRateCost brc ON eb.EBId = brc.ReferanceId
			WHERE eb.EBId = '{$EBId}'
    			AND brc.CostTypeId = '{$CostTypeId}'
	";
	unset($params);

	$resultCurrency =  ICSDB::Query($sql, $params, true)  ;
	while($rsCurrency = $resultCurrency -> Next() ){
		$arr['ExtraCostLocal'] = $rsCurrency["ExtraCostLocal"];
		$arr['ExtraCostCurrency'] = $rsCurrency["ExtraCostCurrency"];
		$arr['ReductionLocal'] = $rsCurrency["ReductionLocal"];
		$arr['ReductionCurrency'] = $rsCurrency["ReductionCurrency"];
	}

	$ExtraCostLocal = !empty($arr['ExtraCostLocal']) ? $arr['ExtraCostLocal'] : 0;
	// $ExtraCostCurrency = $arr['ExtraCostCurrency'];
	$ReductionLocal = !empty($arr['ReductionLocal']) ? $arr['ReductionLocal'] : 0;
	// $ReductionCurrency = $arr['ReductionCurrency'];

	// var_dump($val, $ExtraCostLocal, $ReductionLocal);
	if(isset($val) && !empty($val)){
		$val = $val + $ExtraCostLocal - $ReductionLocal;
	}
	// var_dump($val);

	return $val;
}

function RecalculateCostWithAdjustmentMiscellaneous($MOBId, $CostTypeId, $val=""){
	if((!isset($MOBId) || empty($MOBId)) && (!isset($CostTypeId) || empty($CostTypeId))){
		// var_dump('Error : $MOBId/$CostTypeId can not be empty!');
		return "";
	}

	//Convert Text To Num
	if(isset($val)){
		$val = str_replace(",", "", $val);
		$val = (float)$val;
	}

	$arr = array();

	//Get Currency 
	$sql = "SELECT mob.ConfirmUS
				, mob.ConfirmLocal
    			, mob.ConfirmCurrency
				, mob.ExtraCostUS
				, mob.ExtraCostLocal
    			, mob.ExtraCostCurrency
    			, mob.CurrencyExtraCostId
    			, mob.ReductionUS
    			, mob.ReductionLocal
    			, mob.ReductionCurrency
    			, mob.CurrencyReductionId
				, brc.CurrencyId AS [CurrencyId]
    			, CAST (brc.CurrencyRate as FLOAT) AS [CurrencyRate]
    			, brc.Currency AS [Currency]
    			, CAST (brc.Price as FLOAT) AS [Price]
    			, brc.RepeatPax
    			, brc.CostTypeId
			FROM MisceOtherBooking mob
			LEFT JOIN BookingRateCost brc ON mob.MOBId = brc.ReferanceId
			WHERE mob.MOBId = '{$MOBId}'
    			AND brc.CostTypeId = '{$CostTypeId}'
	";
	unset($params);

	$resultCurrency =  ICSDB::Query($sql, $params, true)  ;
	while($rsCurrency = $resultCurrency -> Next() ){
		$arr['ExtraCostLocal'] = $rsCurrency["ExtraCostLocal"];
		$arr['ExtraCostCurrency'] = $rsCurrency["ExtraCostCurrency"];
		$arr['ReductionLocal'] = $rsCurrency["ReductionLocal"];
		$arr['ReductionCurrency'] = $rsCurrency["ReductionCurrency"];
	}

	$ExtraCostLocal = !empty($arr['ExtraCostLocal']) ? $arr['ExtraCostLocal'] : 0;
	// $ExtraCostCurrency = $arr['ExtraCostCurrency'];
	$ReductionLocal = !empty($arr['ReductionLocal']) ? $arr['ReductionLocal'] : 0;
	// $ReductionCurrency = $arr['ReductionCurrency'];

	if(isset($val) && !empty($val)){
		$val = $val + $ExtraCostLocal - $ReductionLocal;
	}
	// var_dump($val, $ExtraCostLocal, $ReductionLocal);

	return $val;
}

//Get Currency 
$sql = "SELECT 
			CAST(Id AS varchar(36)) AS [CurrencyId]
			, CAST(HotelCurrencyRate as float) AS [CurrencyRate]
			, Currency AS [Currency]
		FROM dbo.Currency
		ORDER BY Currency
";
unset($params) ; 
$resultCurrency =  ICSDB::Query( $sql , $params  , true )  ;
while($rsCurrency = $resultCurrency -> Next() ){
	$arrCurrency[] = $rsCurrency["CurrencyId"]."~".$rsCurrency["CurrencyRate"]."~".$rsCurrency["Currency"];
}
// var_dump($arrCurrency); exit();

$sql = "SELECT Status FROM tbHotelBookingStatus ORDER BY Pos" ;
unset($params) ; 
$resultStat =  $db_Zend->query($sql);
while( $rsStat = $resultStat->fetch()){
		$arrStat[] = $rsStat->Status ;
}
	 
$sql = "SELECT CountryId,CountryDesc FROM dbo.tbCountry WHERE OperationOffice=1  ORDER BY CountryDesc";
$Countrys = $db_Zend->query($sql);
$Countrys2 = $db_Zend->query($sql);
$Countrys3 = $db_Zend->query($sql);

$sql = "SELECT Id,ServiceUnitType FROM dbo.ServiceUnitType WHERE ServiceCategoryId='92D2F393-942D-4437-B432-31EF282A1214'
ORDER BY ServiceUnitType" ;
$resultVehicle =  $db_Zend->query($sql);
while( $rsTrans = $resultVehicle -> fetch()){
	$arrTrans[$rsTrans->Id] = $rsTrans->ServiceUnitType ;
}

//ServiceCategory
$sql = "SELECT Id, ServiceCategory_Desc FROM dbo.ServiceCategory WHERE 1=1 ORDER BY ServiceCategory_Desc " ;
if($rs =  $db_Zend->fetchAll($sql)){
	foreach($rs as $row){
		$array_category[$row->Id] = $row->ServiceCategory_Desc;
	}	
}

//Currency
$sql = "SELECT Id, CurrencyRate, Currency FROM dbo.Currency WHERE 1=1 ORDER BY dbo.Currency.Currency " ;
if($rs =  $db_Zend->fetchAll($sql)){
	foreach($rs as $row){
		$array_currency[$row->Id."~".$row->CurrencyRate."~".$row->Currency] = $row->Currency;
	}	
}

//Country
$sql = "SELECT dbo.tbCountry.CountryId, dbo.tbCountry.CountryDesc FROM dbo.tbCountry WHERE 1=1 AND dbo.tbCountry.CountryDesc <> '' ORDER BY dbo.tbCountry.CountryDesc" ;
if($rs =  $db_Zend->fetchAll($sql)){
	foreach($rs as $row){
		$array_country[$row->CountryId] = $row->CountryDesc;
	}	
}

//City
$sql = "SELECT dbo.tbCity.CityId, dbo.tbCity.City FROM dbo.tbCity WHERE 1=1 AND dbo.tbCity.City <> '' ORDER BY dbo.tbCity.City" ;
if($rs =  $db_Zend->fetchAll($sql)){
	foreach($rs as $row){
		$array_city[$row->CityId] = $row->City;
	}	
}

//Company
$sql = "SELECT CompanyId, CompanyName FROM dbo.ContactCompany WHERE 1=1 AND CompanyName <> ' ' ORDER BY CompanyName " ;
if($rs =  $db_Zend->fetchAll($sql)){
	foreach($rs as $row){
		$array_company[$row->CompanyId] = $row->CompanyName;
	}	
}

$array_costtype = array('1' => 'Per Pax', '2' => 'Per Group');
$array_status = array('OK' => 'OK', 'Cancel' => 'Cancel');

$resultT = loadData("tour" , $tour_id ) ;
$rsT = $resultT -> Next() ;
$bookingDate = $rsT['BD'];
$clients 	 = $rsT['Clients'];
$pax 		 = $rsT['NoPax'];
$start 		 = $rsT['Start'];
$end 		 = $rsT['End'];
$fstart 		 = $rsT['FStart'];
$fend 		 = $rsT['FEnd'];
$routing 	 = $rsT['Routing'] ;
$company 	 = $rsT['CompanyDesc'] ;		

		
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Miscellaneous cost</title> 
<script type="text/javascript" src="fnct.js"></script>
<script type="text/javascript" src="jquery/jquery-1.5.1.js"></script>
<script type="text/javascript" src="jquery/ui/jquery-ui-1.8.11.custom.js"></script>
<script type="text/javascript" src="jquery/jquery.selectboxes.js"></script>
<script type="text/javascript">
	var url = "action_miscellaneous.php";
	$(document).ready(function(){
			$('#buttonAction').attr('disabled','disabled');
			$('#buttonAction2').attr('disabled','disabled');
			$('#dialog').dialog({bgiframe: true,title:"Entrance Fee",width:600,height: 500,modal: true,autoOpen: false});
			$('#dialog2').dialog({bgiframe: true,title:"Miscellaneous",width:600,height: 500,modal: true,autoOpen: false});
			$('#dialogWater').dialog({bgiframe: true,title:"Water cost",width:600,height: 500,modal: true,autoOpen: false});
			$('#dialogOther').dialog({bgiframe: true,title:"Other cost",width:600,height: 500,modal: true,autoOpen: false});
			$('#dialogOtherCost').dialog({bgiframe: true,title:"Other cost",width:600,height: 500,modal: true,autoOpen: false});
			$('#dialogDelete').dialog({
				bgiframe: true,
				modal: true,autoOpen: false});		
					
			////////////////  Delete Entrance Fee  All //////////////////////
			$('#imgDel').click(function(){
				
				if(!confirm('Are you sure ? '))
				return false;
				
				var chkEntrance = $('input[name^=chkEntrance]:checked');
			
				if(chkEntrance.length==0)
				{
					alert('Please select item(s) to delete');	
					return false;
				}
				
				var EBId = "";
				$('input[name^=chkEntrance]:checked').each(function(index, element) {
                    EBId = EBId + "'" + $(this).val() + "',";
                });
				EBId = EBId.substr(0,EBId.length-1);
								
				$('#dialogDelete').dialog("option","title","Delete Entrance Fee " + chkEntrance.length + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');
				
				$.post('action_miscellaneous.php',{'mode':'deleteEntranceMulti','EBId':EBId},function(data){							
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Delete complete');
						window.location.reload();
					}else
						$('#dialogDelete').html(data);
					
				});
				
			});
			////////////////  Delete Miscellaneous  All //////////////////////
			$('#imgDel2').click(function(){
				
				if(!confirm('Are you sure ? '))
				return false;
				
				var chkMisce = $('input[name^=chkMisce]:checked');
			
				if(chkMisce.length==0)
				{
					alert('Please select item(s) to delete');	
					return false;
				}
				
				var MOBId = "";
				$('input[name^=chkMisce]:checked').each(function(index, element) {
                    MOBId = MOBId + "'" + $(this).val() + "',";
                });
				MOBId = MOBId.substr(0,MOBId.length-1);
								
				$('#dialogDelete').dialog("option","title","Delete Miscellaneous " + chkMisce.length + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');
				
				$.post('action_miscellaneous.php',{'mode':'deleteMiscMulti','MOBId':MOBId},function(data){							
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Delete complete');
						window.location.reload();
					}else
						$('#dialogDelete').html(data);
					
				});
				
			});
			////////////////  Delete Water  All //////////////////////
			$('#imgDel3').click(function(){
				
				if(!confirm('Are you sure ? '))
				return false;
				
				var chkWater = $('input[name^=chkWater]:checked');
			
				if(chkWater.length==0)
				{
					alert('Please select item(s) to delete');	
					return false;
				}
				
				var WBId = "";
				$('input[name^=chkWater]:checked').each(function(index, element) {
                    WBId = WBId + "'" + $(this).val() + "',";
                });
				WBId = WBId.substr(0,WBId.length-1);
								
				$('#dialogDelete').dialog("option","title","Delete Water " + chkWater.length + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');
				
				$.post('action_miscellaneous.php',{'mode':'deleteWaterMulti','WBId':WBId},function(data){							
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Delete complete');
						window.location.reload();
					}else
						$('#dialogDelete').html(data);
					
				});
				
			});
			/* UPDATE STATUS Water All */
			$('#btn_water').click(function(){
				
				if(!confirm('Are you sure ? '))
					return false;
				
				var chkWater = $('input[name^=chkWater]:checked');
			
				if(chkWater.length==0)
				{
					alert('Please select item(s) to update');	
					return false;
				}
				
				var hidWaterId = "";
				$('input[name^=chkWater]:checked').each(function(index, element) {
                    hidWaterId = hidWaterId + "'" + $(this).val() + "',";
                });
				hidWaterId = hidWaterId.substr(0,hidWaterId.length-1);
				var water_status = $('#water_status').val();
								
				$('#dialogDelete').dialog("option","title","Update water cost " + chkWater.length + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');

				$.post('action_miscellaneous.php',{'mode':'updateWaterMultiple', 'hidWaterId':hidWaterId, 'water_status':water_status}, function(data){			
					//alert(data);
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Update complete');
						window.location.reload();
					}else{
						$('#dialogDelete').html(data);
					}
				});
				
			});
			/* UPDATE STATUS Entrance Fee All */
			$('#btn_entrance').click(function(){
				
				if(!confirm('Are you sure ? '))
					return false;
				
				var chkEntrance = $('input[name^=chkEntrance]:checked');
			
				if(chkEntrance.length==0)
				{
					alert('Please select item(s) to update');	
					return false;
				}
				var count = 0;
				var EBId = "";
				$('input[name^=chkEntrance]:checked').each(function(index, element) {
                    EBId = EBId + "'" + $(this).val() + "',";
					count++;
				});
				EBId = EBId.substr(0,EBId.length-1);
				var entrance_status = $('#entrance_status').val();
								
				$('#dialogDelete').dialog("option","title","Update entrance fee cost " + count + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');

				$.post('action_miscellaneous.php',{'mode':'updateEntranceFeeMultiple', 'EBId':EBId, 'entrance_status':entrance_status}, function(data){							alert(data);
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Update complete');
						window.location.reload();
					}else{
						$('#dialogDelete').html(data);
					}
				});
				
			});
			/* UPDATE STATUS Miscellaneous All */
			$('#btn_misce').click(function(){
				
				if(!confirm('Are you sure ? ')){
					return false;
				}
				
				var chkMisce = $('input[name^=chkMisce]:checked');
			
				if(chkMisce.length==0)
				{
					alert('Please select item(s) to update');	
					return false;
				}
				var count = 0;
				var MOBId = "";
				$('input[name^=chkMisce]:checked').each(function(index, element) {
                    MOBId = MOBId + "'" + $(this).val() + "',";
					count++;
				});
				MOBId = MOBId.substr(0,MOBId.length-1);
				var misce_status = $('#misce_status').val();
								
				$('#dialogDelete').dialog("option","title","Update miscellaneous cost " + count + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');
				
				$.post('action_miscellaneous.php',{'mode':'updateMiscellaneousMultiple','MOBId':MOBId, 'misce_status':misce_status},function(data){							
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Update complete');
						window.location.reload();
					}else
						$('#dialogDelete').html(data);
					
				});
				
			});
			////////////////  Delete Other  All //////////////////////
			$('#imgDel4').click(function(){
				
				if(!confirm('Are you sure ? '))
				return false;
				
				var chkOther = $('input[name^=chkOther]:checked');
			
				if(chkOther.length==0)
				{
					alert('Please select item(s) to delete');	
					return false;
				}
				
				var hidMiscId = "";
				$('input[name^=chkOther]:checked').each(function(index, element) {
                    hidMiscId = hidMiscId + "'" + $(this).val() + "',";
                });
				hidMiscId = hidMiscId.substr(0,hidMiscId.length-1);
								
				$('#dialogDelete').dialog("option","title","Delete Miscellaneous " + chkOther.length + " item(s) ");
				$('#dialogDelete').dialog("option","width",250);
				$('#dialogDelete').dialog("option","height",100);
				$('#dialogDelete').dialog("open");
				$('#dialogDelete').html('Please wait.....');
				
				$.post('action_miscellaneous.php',{'mode':'deleteOtherMulti','hidMiscId':hidMiscId},function(data){							
					if(data.indexOf('OK')>-1)
					{
						$('#dialogDelete').html('Delete complete');
						window.location.reload();
					}else
						$('#dialogDelete').html(data);
					
				});
				
			});
			
			// DevMark
			$('#selCountry').change(function(){
				$("#selCity").removeOption(/./);
				$("#selCity").ajaxAddOption(url, {"mode" : "getCity","CountryId":$(this).val()},false, $("#selCity").selectOptions("0"));
				$("#selSupplier").removeOption(/./);
				$("#selSupplier").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CountryId":$(this).val(),"SupplierTypeId":"'16'"},false, $("#selSupplier").selectOptions("0"));
			});
			
			$('#selCity').change(function(){
				$("#selSupplier").removeOption(/./);
				$("#selSupplier").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CityId":$(this).val(),"CountryId":$('#selCountry').val(),"SupplierTypeId":"'16'"},false, $("#selSupplier").selectOptions("0"));
			});
			
			$('#selSupplier').change(function(){
				$("#selService").removeOption(/./);
				$("#selService").ajaxAddOption(url, {"mode" : "getService","CompanyId":$(this).val()},false, $("#selService").selectOptions("0"));
			});
			
			$('#selService').change(function(){
				$("#selVehicle").removeOption(/./);
				$("#selVehicle").ajaxAddOption(url, {"mode" : "getVehicleNewVersion","ContractId":$(this).val()},false, $("#selVehicle").selectOptions("0"));
			});
			$("#selVehicle").change(function(){
				$('#buttonAction').removeAttr('disabled');
			});
			
			
			/////////////////////////////////// Miscellaneouos ///////////////////////////////
			$('#selCountry2').change(function(){
				$("#selCity2").removeOption(/./);
				$("#selCity2").ajaxAddOption(url, {"mode" : "getCity","CountryId":$(this).val()},false, $("#selCity2").selectOptions("0"));
				$("#selSupplier2").removeOption(/./);
				$("#selSupplier2").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CountryId":$(this).val(),"SupplierTypeId":"'8','5','15','14','18','19','20'"},false, $("#selSupplier2").selectOptions("0"));
			});
			
			$('#selCity2').change(function(){
				$("#selSupplier2").removeOption(/./);
				$("#selSupplier2").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CityId":$(this).val(),"CountryId":$('#selCountry2').val(),"SupplierTypeId":"'8','5','15','14','18','19','20'"},false, $("#selSupplier2").selectOptions("0"));
			});
			
			$('#selSupplier2').change(function(){
				$("#selService2").removeOption(/./);
				$("#selService2").ajaxAddOption(url, {"mode" : "getServiceMisc","CompanyId":$(this).val()},false, $("#selService2").selectOptions("0"));
			});
			
			$('#selService2').change(function(){
				$("#selVehicle2").removeOption(/./);
				$("#selVehicle2").ajaxAddOption(url, {"mode" : "getmiscotherNewVersion", "ContractId":$(this).val()},false, $("#selVehicle2").selectOptions("0"));
			});

			$("#selVehicle2").change(function(){
				$('#buttonAction2').removeAttr('disabled');
			});

			/////////////// OtherCost /////////////////
			$('#selCountryOtherCost').change(function(){
				$("#selCityOtherCost").removeOption(/./);
				$("#selCityOtherCost").ajaxAddOption(url, {"mode" : "getCity","CountryId":$(this).val()},false, $("#selCityOtherCost").selectOptions("0"));
				$("#selCompanyOtherCost").removeOption(/./);
				$("#selCompanyOtherCost").ajaxAddOption(url, {"mode" : "getCompany","CountryId":$(this).val()},false, $("#selCompanyOtherCost").selectOptions("0"));
			});
			
			$('#selCityOtherCost').change(function(){
				$("#selCompanyOtherCost").removeOption(/./);
				$("#selCompanyOtherCost").ajaxAddOption(url, {"mode" : "getCompany","CountryId":$('#selCountryOtherCost').val(), "CityId":$(this).val()},false, $("#selCompanyOtherCost").selectOptions("0"));
			});

			/////////////// Water /////////////////
			$("#selWater").ajaxAddOption(url, {"mode" : "getWater"} ,false, $("#selWater").selectOptions("0"));
			
			$('#selCountryWater').change(function(){
				$("#selWater").removeOption(/./);
				$("#selWater").ajaxAddOption(url, {"mode" : "getWater","CountryId":$(this).val()},false, $("#selWater").selectOptions("0"));
			});
					$( "#txtOnDayOtherCost" ).datepicker( "option", "minDate", '<?=$fstart?>' );
		//$( "#txtOnDayOtherCost" ).datepicker( "option", "maxDate", '<?=$fend?>' );
			$('.datepicker').datepicker({dateFormat:'dd-M-yy'
															, changeMonth: true
															, yearRange: '-5:+5'
															, changeYear: true
															, minDate:'<?=$fstart?>'
															, maxDate:'<?=$fend?>'});			
			$('#chkAllEntranceFee').change(
				function(){
					$('input:checkbox[id="chkEntrance"]').attr('checked', $(this).attr('checked'));  
				}
			);

			$('#chkAllMisce').change(
				function(){
					$('input:checkbox[id="chkMisce"]').attr('checked', $(this).attr('checked'));  
				}
			);

			$('#chkAllWater').change(
				function(){
					$('input:checkbox[id="chkWater"]').attr('checked', $(this).attr('checked'));  
				}
			);


			$('#chkAllOther').change(
				function(){
					$('input:checkbox[id="chkOther"]').attr('checked', $(this).attr('checked'));  
				}
			);
	});	

function EditOtherCostFilter1(i){
	$("#selCityOtherCost"+i).removeOption(/./);
	$("#selCityOtherCost"+i).ajaxAddOption(url, {"mode" : "getCity","CountryId":$(this).val()},false, $("#selCityOtherCost"+i).selectOptions("0"));
	$("#selCompanyOtherCost"+i).removeOption(/./);
	$("#selCompanyOtherCost"+i).ajaxAddOption(url, {"mode" : "getCompany","CountryId":$(this).val()},false, $("#selCompanyOtherCost"+i).selectOptions("0"));
}

function EditOtherCostFilter2(i){
	$("#selCompanyOtherCost"+i).removeOption(/./);
	$("#selCompanyOtherCost"+i).ajaxAddOption(url, {"mode" : "getCompany","CountryId":$('#selCountryOtherCost'+i).val(), "CityId":$(this).val()},false, $("#selCompanyOtherCost"+i).selectOptions("0"));
}

function isNotNullAndNotEmpty(value){
	if(typeof value !== undefined && value.trim() != ""){
		return true;
	}
	return false;
}

function isNumeric(value){
	if(typeof value !== undefined && value.trim() != "" && !isNaN(value)){
		return true;
	}
	return false;
}

function AutoMatchCurrencyEntranceFee(){
	var selVehicleCurrency = $( "#selVehicle option:selected" ).val();
	var selLocalCurrency = $( "#sel_local_currency option:selected" ).val();
	var status = $( "#selStatus option:selected" ).val();
	// console.log(selFlightPrice);
	// console.log(selLocalCurrency);

	//Auto select ConfirmedCurrency id instead of ServiceRateCurrency id if ConfirmedCurrency has value
	var currentId = "";
	var sel = "";
	if(status != "" && status.trim() == "OK" && selLocalCurrency != "" && selLocalCurrency != 0){
		sel = selLocalCurrency;
		splitArray = sel.split("~");
		currentId = splitArray[0];
	}
	else if(selVehicleCurrency != "" && selVehicleCurrency != 0){
		sel = selVehicleCurrency;
		splitArray = sel.split("~");
		currentId = splitArray[3];
	}
	// console.log(currentId);

	$("#sel_local_currency_extra_cost option[value^='"+ currentId +"']").attr('selected', 'selected');
	ConvertCurrency('sel_local_currency_extra_cost', 'txt_local_rate_extra_cost', 'txt_usd_extra_cost');

	$("#sel_local_currency_reduction option[value^='"+ currentId +"']").attr('selected', 'selected');
	ConvertCurrency('sel_local_currency_reduction', 'txt_local_rate_reduction', 'txt_usd_reduction');
}

function AutoMatchCurrencyMiscellaneous(){
	var selVehicleCurrency = $( "#selVehicle2 option:selected" ).val();
	var selLocalCurrency = $( "#sel_local_currency2 option:selected" ).val();
	var status = $( "#selStatus2 option:selected" ).val();
	// console.log(selFlightPrice);
	// console.log(selLocalCurrency);

	//Auto select ConfirmedCurrency id instead of ServiceRateCurrency id if ConfirmedCurrency has value
	var currentId = "";
	var sel = "";
	if(status != "" && status.trim() == "OK" && selLocalCurrency != "" && selLocalCurrency != 0){
		sel = selLocalCurrency;
		splitArray = sel.split("~");
		currentId = splitArray[0];
	}
	else if(selVehicleCurrency != "" && selVehicleCurrency != 0){
		sel = selVehicleCurrency;
		splitArray = sel.split("~");
		currentId = splitArray[3];
	}
	// console.log(currentId);

	$("#sel_local_currency_extra_cost2 option[value^='"+ currentId +"']").attr('selected', 'selected');
	ConvertCurrency('sel_local_currency_extra_cost2', 'txt_local_rate_extra_cost2', 'txt_usd_extra_cost2');

	$("#sel_local_currency_reduction2 option[value^='"+ currentId +"']").attr('selected', 'selected');
	ConvertCurrency('sel_local_currency_reduction2', 'txt_local_rate_reduction2', 'txt_usd_reduction2');
}

function ClearCurrency(currency_tag, rate_tag, usd_tag){
	var local_rate = document.getElementById(rate_tag).value;
	var i = document.getElementById(currency_tag).selectedIndex;
	var currency = document.getElementById(currency_tag).options[i].value;
	if(local_rate.trim() == ""){
		// alert(local_rate);
		// document.getElementById(LocalCurrencyTag).options[0].selected = 'selected';
		document.getElementById(usd_tag).value = "";
	}
}

function ConvertCurrency(currency_tag, rate_tag, usd_tag){
	var local_rate = document.getElementById(rate_tag).value;
	var i = document.getElementById(currency_tag).selectedIndex;
	var currency = document.getElementById(currency_tag).options[i].value;
    if(currency != '' && currency != 0 && local_rate != '' && isNumber(local_rate)){
    	currency = currency.split('~')[1];
    	currency = parseFloat(currency);
		var res = local_rate / currency;
		res = res.toFixed(2);
		document.getElementById(usd_tag).value = res;
    }
}

function removeOptions(dropdownlist_tag, selected=""){
	var sel = selected;
	// console.log(sel);

	$('#'+dropdownlist_tag).find('option').each(function() {

		var CompanyId = $(this).val();
		// console.log($(this).val());

		//Check if service date is expired (Skip if selected)
		var date = new Date();
		var day = date.getDate();
		var month = date.getMonth()+1;
		if(month < 10){
			month = '0'+month;
		}
		var year = date.getFullYear();

		var currentDate = year + '-' + month + '-' + day;
		// console.log(currentDate);
		
		var text = $(this).text();
		// console.log(text);
		array_of_split_ddl = text.split("~");
		// console.log(array_of_split_ddl[2]);
		dateto = array_of_split_ddl[2];

		if(dateto != null && dateto.trim() != ''){

			array_of_split_dateto = dateto.split("-");
			var day = array_of_split_dateto[0];
			var month = array_of_split_dateto[1];
			month = ConvertMonthToNum(month);
			var year = array_of_split_dateto[2];
			var endDate = year + '-' + month + '-' + day;

			// console.log(currentDate);
			// console.log(endDate);
			// var currentDate = 2017 + '-' + 08 + '-' + 30; //*For test

			if(sel != CompanyId && new Date(endDate) < new Date(currentDate)){
				$(this).remove();
			}
		}
	});
}

function ConvertMonthToText(month){
	var newMonth = '';
	if(month == 1 || month == 01){
		newMonth = 'Jan';
	}
	else if(month == 2 || month == 02){
		newMonth = 'Feb';
	}
	else if(month == 3 || month == 03){
		newMonth = 'Mar';
	}
	else if(month == 4 || month == 04){
		newMonth = 'Apr';
	}
	else if(month == 5 || month == 05){
		newMonth = 'May';
	}
	else if(month == 6 || month == 06){
		newMonth = 'Jun';
	}
	else if(month == 7 || month == 07){
		newMonth = 'Jul';
	}
	else if(month == 8 || month == 08){
		newMonth = 'Aug';
	}
	else if(month == 9 || month == 09){
		newMonth = 'Sep';
	}
	else if(month == 10){
		newMonth = 'Oct';
	}
	else if(month == 11){
		newMonth = 'Nov';
	}
	else if(month == 12){
		newMonth = 'Dec';
	}
	else {
		newMonth = 'err';
	}
	return newMonth;
}

function ConvertMonthToNum(month){
	var newMonth = '';
	if(month == 'Jan'){
		newMonth = 1;
	}
	else if(month == 'Feb'){
		newMonth = 2;
	}
	else if(month == 'Mar'){
		newMonth = 3;
	}
	else if(month == 'Apr'){
		newMonth = 4;
	}
	else if(month == 'May'){
		newMonth = 5;
	}
	else if(month == 'Jun'){
		newMonth = 6;
	}
	else if(month == 'Jul'){
		newMonth = 7;
	}
	else if(month == 'Aug'){
		newMonth = 8;
	}
	else if(month == 'Sep'){
		newMonth = 9;
	}
	else if(month == 'Oct'){
		newMonth = 10;
	}
	else if(month == 'Nov'){
		newMonth = 11;
	}
	else if(month == 'Dec'){
		newMonth = 12;
	}
	else {
		newMonth = 'err';
	}
	return newMonth;
}

//DEvMark*
function editEntranceFee(EBId)
{
	$('#hidMode').val('editEntrance');
	$('#hidEBId').val(EBId);
	
	$.post(url, { "mode": "editEntranceFee","EBId":EBId },
		 function(data){
			 //alert("CountryId"+data.CountryId);
			 $("#selCountry").selectOptions(data.CountryId);
			 $("#selCity").removeOption(/./);
			 $("#selCity").ajaxAddOption(url, {"mode" : "getCity","CountryId":$("#selCountry").val()},false, function(){
				 // after load City
				 //alert("CityId"+data.CityId);
				 $("#selCity").selectOptions(data.CityId);
				  $("#selSupplier").removeOption(/./);
				 $("#selSupplier").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CityId":$("#selCity").val(),"CountryId":$('#selCountry').val()},false, function(){
					 // after load company
					 //alert("CompanyId"+data.CompanyId);
					 $("#selSupplier").selectOptions(data.CompanyId);
					 $("#selService").removeOption(/./);
					 $("#selService").ajaxAddOption(url, {"mode" : "getService","CompanyId":$(this).val()},false,function(){
						 //after load contract		
						 //alert("ServiceContractId"+data.ServiceContractId);		 

						  removeOptions('selService', data.ServiceContractId);
						 $("#selService").selectOptions(data.ServiceContractId);

						 $("#selVehicle").removeOption(/./);
						 $("#selVehicle").ajaxAddOption(url, {"mode" : "getVehicleNewVersion","ContractId":$("#selService").val()},false, function(){
							 // after load vehicle
							 //alert("ServiceRateId"+data.ServiceRateId);	

						  	 // $("#selVehicle").selectOptions(data.ServiceRateId);
						  	 $("#selVehicle option[value^='"+ data.ServiceRateId +"']").attr('selected', 'selected');

							 $('#buttonAction').removeAttr('disabled');
						 });
					  });	
				  });			 	 
			 });
			 
			 $('#txtPax').val(data.Pax);
			 $('#txtOnDay').val(data.OnDay);
			 $('#selStatus').selectOptions(data.Status);
			 $('#txtRemarks').text(data.Remark);
			 $('#hidEBId').val(data.EBId);

			 // DevMark
			 if(data.Status.trim() == "OK"){
			 	expandTab();
			 }

			 if(data.CurrencyId.trim() != "")
			 	$('#sel_local_currency option[value^='+data.CurrencyId+']').attr('selected','selected');
			 if(data.CurrencyExtraCostId.trim() != "")
			 	$('#sel_local_currency_extra_cost option[value^='+data.CurrencyExtraCostId+']').attr('selected','selected');
			 if(data.CurrencyReductionId.trim() != "")
			 	$('#sel_local_currency_reduction option[value^='+data.CurrencyReductionId+']').attr('selected','selected');

			 $('#txt_usd').val(data.ConfirmUS);
			 // $('#sel_local_currency').selectOptions(data.CurrencyId); // **
			 $('#txt_local_rate').val(data.ConfirmLocal);
			 // console.log(data.ConfirmUS, data.CurrencyId, data.ConfirmLocal);

			 $('#txt_usd_extra_cost').val(data.ExtraCostUS);
			 // $('#sel_local_currency_extra_cost').selectOptions(data.CurrencyExtraCostId); // **
			 $('#txt_local_rate_extra_cost').val(data.ExtraCostLocal);
			 // console.log(data.ExtraCostUS, data.CurrencyExtraCostId, data.ExtraCostLocal);

			 $('#txt_usd_reduction').val(data.ReductionUS);
			 // $('#sel_local_currency_reduction').selectOptions(data.CurrencyReductionId); // **
			 $('#txt_local_rate_reduction').val(data.ReductionLocal);
			 // console.log(data.ReductionUS, data.CurrencyReductionId, data.ReductionLocal);

			 $('#txt_extra_cost_remark').val(data.ExtraCostRemark);
			 $('#txt_reduction_remark').val(data.ReductionRemark);
			 // console.log(data.ExtraCostRemark, data.ReductionRemark);
			 
	}, "json");
	$('#dialog').dialog('open');
}

function editEntranceSave()
{
	// DevMark
	$('#sel_local_currency_extra_cost').removeAttr('disabled');
	$('#sel_local_currency_reduction').removeAttr('disabled');

	//Validation
	var pax = $('#txtPax').val();
	if(!isNumeric(pax) || pax < 1){
		alert('Pax must not empty and not 0!');
		return;
	}
	var selVehicle = $('#selVehicle option:selected').val();
	if(!isNotNullAndNotEmpty(selVehicle) || selVehicle == 0){
		alert('Miscellaneous must not empty and not 0!');
		return;
	}


	var params = "mode=editEntranceSave&";
	params = params + $('#form1').serialize();
	if($('#hidEBId').val()=="")
		str = "Add ";
	else
		str = "Update ";
	
	$('#dialog').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
	$('#dialog').dialog('option','width','300');
	$('#dialog').dialog('option','height','120');
	
	$.post(url, params,function(data){
		if(data.indexOf('OK')!=-1)
		{
			$('#dialog').html(str+' Complete<br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');				
		}
		else
		{
			$('#dialog').html(str + ' Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
		}
	});
}


function deleteEntranceFee(EBId)
{
	if(confirm('Are you sure ?'))
	{
		$('#hidMode').val('deleteEntranceFee');
		$('#hidEBId').val(EBId);	
		$('#dialog').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
		$('#dialog').dialog('option','width','300');
		$('#dialog').dialog('option','height','120');
		$('#dialog').dialog('open');
		$.post(url, { "mode": "deleteEntranceFee","EBId":EBId },function(data){
			if(data.indexOf('OK')!=-1)
			{
				$('#dialog').html('Delete complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
			}
			else
			$('#dialog').html('Update Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	
		});
	}
}

function editBookingOther(MOBId)
{
	$('#hidMode2').val('editBookingOther');
	$('#hidEBId2').val(MOBId);
	
	$.post(url, { "mode": "editBookingOther","MOBId":MOBId },
		 function(data){
			 //alert("CountryId"+data.CountryId);
			 $("#selCountry2").selectOptions(data.CountryId);
			 $("#selCity2").removeOption(/./);
			 $("#selCity2").ajaxAddOption(url, {"mode" : "getCity","CountryId":$("#selCountry2").val()},false, function(){
				 // after load City
				 //alert("CityId"+data.CityId);
				 $("#selCity2").selectOptions(data.CityId);
				 $("#selSupplier2").removeOption(/./);
				 $("#selSupplier2").ajaxAddOption("action_trans.php", {"mode" : "getSupplier","CityId":$("#selCity2").val(),"CountryId":$('#selCountry2').val()},false, function(){
					 // after load company
					 //alert("CompanyId"+data.CompanyId);
					 $("#selSupplier2").selectOptions(data.CompanyId);
					 $("#selService2").removeOption(/./);
					 $("#selService2").ajaxAddOption(url, {"mode" : "getServiceMisc","CompanyId":$(this).val()},false,function(){
						 //after load contract		
						 //alert("ServiceContractId"+data.ServiceContractId);		 

						 removeOptions('selService2', data.ServiceContractId);
						 $("#selService2").selectOptions(data.ServiceContractId);

						 $("#selVehicle2").removeOption(/./);
						 $("#selVehicle2").ajaxAddOption(url, {"mode" : "getmiscotherNewVersion", "ContractId":$("#selService2").val()},false, function(){
							 // after load vehicle
							 //alert("ServiceRateId"+data.ServiceRateId);	
						  	 // $("#selVehicle2").selectOptions(data.ServiceRateId);
						  	 $("#selVehicle2 option[value^='"+ data.ServiceRateId +"']").attr('selected', 'selected');

							 $('#buttonAction2').removeAttr('disabled');
						 });
					  });	
				  });			 	 
			 });
			 $('#txtPax2').val(data.Pax);
			 $('#txtOnDay2').val(data.OnDay);
			 $('#selStatus2').selectOptions(data.Status);
			 $('#txtRemarks2').text(data.Remark);
			 $('#hidEBId2').val(data.MOBId);

			 // DevMark*
			 if(data.Status.trim() == "OK"){
			 	expandTab2();
			 }

			 if(data.CurrencyId.trim() != "")
			 	$('#sel_local_currency2 option[value^='+data.CurrencyId+']').attr('selected','selected');
			 if(data.CurrencyExtraCostId.trim() != "")
			 	$('#sel_local_currency_extra_cost2 option[value^='+data.CurrencyExtraCostId+']').attr('selected','selected');
			 if(data.CurrencyReductionId.trim() != "")
			 	$('#sel_local_currency_reduction2 option[value^='+data.CurrencyReductionId+']').attr('selected','selected');

			 $('#txt_usd2').val(data.ConfirmUS);
			 // $('#sel_local_currency').selectOptions(data.CurrencyId); // **
			 $('#txt_local_rate2').val(data.ConfirmLocal);
			 // console.log(data.ConfirmUS, data.CurrencyId, data.ConfirmLocal);

			 $('#txt_usd_extra_cost2').val(data.ExtraCostUS);
			 // $('#sel_local_currency_extra_cost').selectOptions(data.CurrencyExtraCostId); // **
			 $('#txt_local_rate_extra_cost2').val(data.ExtraCostLocal);
			 // console.log(data.ExtraCostUS, data.CurrencyExtraCostId, data.ExtraCostLocal);

			 $('#txt_usd_reduction2').val(data.ReductionUS);
			 // $('#sel_local_currency_reduction').selectOptions(data.CurrencyReductionId); // **
			 $('#txt_local_rate_reduction2').val(data.ReductionLocal);
			 // console.log(data.ReductionUS, data.CurrencyReductionId, data.ReductionLocal);

			 $('#txt_extra_cost_remark2').val(data.ExtraCostRemark);
			 $('#txt_reduction_remark2').val(data.ReductionRemark);
			 // console.log(data.ExtraCostRemark, data.ReductionRemark);
			 
	}, "json");
	$('#dialog2').dialog('open');
}

function editBookingOtherSave()
{
	// DevMark
	$('#sel_local_currency_extra_cost2').removeAttr('disabled');
	$('#sel_local_currency_reduction2').removeAttr('disabled');

	//Validation
	var pax = $('#txtPax2').val();
	if(!isNumeric(pax) || pax < 1){
		alert('Pax must not empty and not 0!');
		return;
	}
	var selVehicle2 = $('#selVehicle2 option:selected').val();
	if(!isNotNullAndNotEmpty(selVehicle2) || selVehicle2 == 0){
		alert('Miscellaneous must not empty and not 0!');
		return;
	}

	var params = "mode=editBookingOtherSave&";
	params = params + $('#form2').serialize();
	if($('#hidEBId2').val()=="")
		str = "Add ";
	else
		str = "Update ";
	
	$('#dialog2').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
	$('#dialog2').dialog('option','width','300');
	$('#dialog2').dialog('option','height','120');
		
	$.post(url, params,function(data){
		if(data.indexOf('OK')!=-1)
		{
			$('#dialog2').html(str+' Complete<br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');				
		}
		else
		{
			$('#dialog2').html(str + ' ' + data + ' Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
		}
	});
}


function deleteBookingOther(MOBId)
{
	if(confirm('Are you sure ?'))
	{
		$('#hidMode2').val('deleteBookingOther');
		$('#hidEBId2').val(MOBId);	
		$('#dialog2').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
		$('#dialog2').dialog('option','width','300');
		$('#dialog2').dialog('option','height','120');
		$('#dialog2').dialog('open');
		$.post(url, { "mode": "deleteBookingOther","MOBId":MOBId },function(data){
			if(data.indexOf('OK')!=-1)
			{
				$('#dialog2').html('Delete complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
			}
			else
			$('#dialog2').html('Update Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	
		});
	}
}

function editWaterSave()
{
	// DevMark
	// $('#sel_local_currency_extra_costWater').removeAttr('disabled');
	// $('#sel_local_currency_reductionWater').removeAttr('disabled');

	var params = "mode=editWaterSave&";
	params = params + $('#formWater').serialize();
	if($('#hidWBId').val()=="")
		str = "Add ";
	else
		str = "Update ";
	
	$('#dialogWater').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
	$('#dialogWater').dialog('option','width','300');
	$('#dialogWater').dialog('option','height','120');
	
	$.post(url, params,function(data){
		if(data.indexOf('OK')!=-1)
		{
			$('#dialogWater').html(str+' Complete<br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');				
		}
		else
			$('#dialogWater').html(str + ' Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	});
}

function editWater(WBId)
{

	$('#hidWBId').val(WBId);

	$.post(url, { "mode": "editWater","WBId":WBId }, function(data){
			 //alert("CountryId"+data.CountryId);
			 //alert(data.CountryId);
			 $("#selCountryWater").selectOptions(data.CountryId);
			 //alert(data.WaterId);
			 $("#selWater").ajaxAddOption(url, {"mode" : "getWater","CountryId":$('#selCountryWater').val()},false, function(){
			 	$("#selWater").selectOptions(data.WaterId);	
			 });
			
			 $('#txtOnDayWater').val(data.OnDay);
			 //alert(data.Status);
			 $('#selStatusWater').selectOptions(data.Status);
			// alert(data.Remark);
			 $('#txtRemarksWater').text(data.Remark);
			 $('#hidWBId').val(data.WBId);

			 // DevMark*
			 if(data.Status.trim() == "OK"){
			 	expandTabWater();
			 }

			 if(data.CurrencyId.trim() != "")
			 	$('#sel_local_currencyWater option[value^='+data.CurrencyId+']').attr('selected','selected');
			 if(data.CurrencyExtraCostId.trim() != "")
			 	$('#sel_local_currency_extra_costWater option[value^='+data.CurrencyExtraCostId+']').attr('selected','selected');
			 if(data.CurrencyReductionId.trim() != "")
			 	$('#sel_local_currency_reductionWater option[value^='+data.CurrencyReductionId+']').attr('selected','selected');

			 $('#txt_usdWater').val(data.ConfirmUS);
			 // $('#sel_local_currency').selectOptions(data.CurrencyId); // **
			 $('#txt_local_rateWater').val(data.ConfirmLocal);
			 // console.log(data.ConfirmUS, data.CurrencyId, data.ConfirmLocal);

			 $('#txt_usd_extra_costWater').val(data.ExtraCostUS);
			 // $('#sel_local_currency_extra_cost').selectOptions(data.CurrencyExtraCostId); // **
			 $('#txt_local_rate_extra_costWater').val(data.ExtraCostLocal);
			 // console.log(data.ExtraCostUS, data.CurrencyExtraCostId, data.ExtraCostLocal);

			 $('#txt_usd_reductionWater').val(data.ReductionUS);
			 // $('#sel_local_currency_reduction').selectOptions(data.CurrencyReductionId); // **
			 $('#txt_local_rate_reductionWater').val(data.ReductionLocal);
			 // console.log(data.ReductionUS, data.CurrencyReductionId, data.ReductionLocal);

			 $('#txt_extra_cost_remarkWater').val(data.ExtraCostRemark);
			 $('#txt_reduction_remarkWater').val(data.ReductionRemark);
			 // console.log(data.ExtraCostRemark, data.ReductionRemark);
			 
	}, "json");
	$('#dialogWater').dialog('open');
}

function deleteWater(WBId)
{
	if(confirm('Are you sure ?'))
	{
		$('#hidMode').val('deleteWater');
		$('#WBId').val(WBId);	
		$('#dialogWater').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
		$('#dialogWater').dialog('option','width','300');
		$('#dialogWater').dialog('option','height','120');
		$('#dialogWater').dialog('open');
		$.post(url, { "mode": "deleteWater","WBId":WBId },function(data){
			if(data.indexOf('OK')!=-1)
			{
				$('#dialogWater').html('Delete complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
			}
			else
			$('#dialogWater').html('Update Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	
		});
	}
}

function editOtherSave(row)
{
	var params = {
		"selCompany":$('#selCompanyOtherCost'+row).val()
		, "selCategory":$('#selCategoryOtherCost'+row).val()
		, "txtOtherCostDesc":$('#txtOtherCostDesc'+row).val()
		, "txtOtherCostPrice":$('#txtOtherCostPrice'+row).val()
		, "selCostType":$('#selCostType'+row).val()
		, "txtRemark":$('#txtRemark'+row).val()
		, "hidOtherCostBookingId":$('#hidOtherCostBookingId'+row).val()

		, "selCountry":$('#selCountryOtherCost'+row).val()
		, "selCity":$('#selCityOtherCost'+row).val()
		, "txtPax":$('#txtPax'+row).val()

		, "selCurrency":$('#selOtherCostCurrency'+row).val()
		, "selStatus":$('#selOtherCostStatus'+row).val()

		, "mode":"editOtherSave"
	};

	$('#dialogOther').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
	$('#dialogOther').dialog('option','width','300');
	$('#dialogOther').dialog('option','height','120');
	$('#dialogOther').dialog('open');
					  
	$.post(url, params ,function(data){
		if(data.indexOf('OK')!=-1)
		{
			$('#dialogOther').html('Update complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
		}
		else
		$('#dialogOther').html('Update Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	
	});		  
}

function deleteOtherSave(row)
{
	if(!confirm('Are you sure ? ')) return false;

	var params = {
		"hidOtherCostBookingId":$('#hidOtherCostBookingId'+row).val()
		, "mode":"deleteOtherSave"
	};

	$('#dialogOther').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
	$('#dialogOther').dialog('option','width','300');
	$('#dialogOther').dialog('option','height','120');
	$('#dialogOther').dialog('open');
					  
	$.post(url, params ,function(data){
		if(data.indexOf('OK')!=-1)
		{
			$('#dialogOther').html('Delete complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
		}
		else
			$('#dialogOther').html('Delete Fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
	
	});		  
	
}

function addOtherCost(){
	try{

		var param = $('#formOtherCost').serialize() + "&mode=createMisceOtherCost";
		$('#dialogOtherCost').html(' Please wait....<br/><img src="jquery/loading.gif"/>');	
		$('#dialogOtherCost').dialog('option','width','300');
		$('#dialogOtherCost').dialog('option','height','120');
		$('#dialogOtherCost').dialog('open');
					  
		$.post(url, param ,function(data){
			if(data.indexOf('OK')!=-1){
				$('#dialogOtherCost').html('Create complete <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');					
			}else{
				console.log(data);
				$('#dialogOtherCost').html('Create fail <br/><input type="button" value="Close" onclick="window.location=\'btn_miscellaneous.php?id=<?=$tour_id;?>\';"> ');
			}
		});
	}catch(e){
		alert(e.description);
	}
}

// DevMark
function expandTab(){
	// alert(i);
	var isStatusOK = false;
	var select = $('#selStatus option:selected').text();
	if(select.trim() == "OK"){
		isStatusOK = true;
	}
	else{
		isStatusOK = false;
	}

	if(isStatusOK){
		$("tr[name^='confirm_row']").show();
	}
	else{
		$("tr[name^='confirm_row']").hide();
	}
}

function expandTab2(){
	// alert(i);
	var isStatusOK = false;
	var select = $('#selStatus2 option:selected').text();
	if(select.trim() == "OK"){
		isStatusOK = true;
	}
	else{
		isStatusOK = false;
	}

	if(isStatusOK){
		$("tr[name^='confirm_row2']").show();
	}
	else{
		$("tr[name^='confirm_row2']").hide();
	}
}

function expandTabWater(){
	// alert(i);
	var isStatusOK = false;
	var select = $('#selStatusWater option:selected').text();
	if(select.trim() == "OK"){
		isStatusOK = true;
	}
	else{
		isStatusOK = false;
	}

	if(isStatusOK){
		$("tr[name^='confirm_rowWater']").show();
	}
	else{
		$("tr[name^='confirm_rowWater']").hide();
	}
}

// function expandTabAdd(){
// 	// alert(i);
// 	var isStatusOK = false;
// 	var select = $('#selStatus option:selected').text();
// 	if(select.trim() == "OK"){
// 		isStatusOK = true;
// 	}
// 	else{
// 		isStatusOK = false;
// 	}

// 	if(isStatusOK){
// 		$("tr[name='confirm_row']").show();
// 	}
// 	else{
// 		$("tr[name='confirm_row']").hide();
// 	}
// }

// function ConvertCurrency(currency_tag, rate_tag, usd_tag){
// 	var local_rate = document.getElementById(rate_tag).value;
// 	var i = document.getElementById(currency_tag).selectedIndex;
// 	var currency = document.getElementById(currency_tag).options[i].value;
//     if(currency != '' && currency != 0 && local_rate != '' && isNumber(local_rate)){
//     	currency = currency.split('~')[1];
//     	currency = parseFloat(currency);
// 		var res = local_rate / currency;
// 		res = res.toFixed(2);
// 		document.getElementById(usd_tag).value = res;
//     }
// }


</script>
<link rel="stylesheet" type="text/css" href="jquery/themes/blitzer/jquery-ui-1.8.11.custom.css">
<style type="text/css" media="screen">
  .selected { background-color: #888; }
  ul.contacts  {
  list-style-type: none;
  margin:0px;
  padding:0px;
  }

  .selCountry > option { 
  	width: 80px; 
  }

  .selCity > option { 
  	width: 80px; 
  }

  .selCompany > option { 
  	width: 280px; 
  }

  .selCategory > option {
	width: 80px;
  }

  .selCurrency > option {
	width: 30px;
  }

  .selStatus > option { 
  	width: 30px; 
  }

</style>
<!-- DevMark -->
<style type="text/css">
  .hide{
	display: none;
  }
  .border {
  	border-style: solid;
  	border-width: 1px;
  }
</style>
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript"> window.onload = function() {setColor() ;} </script>
</head>
<body class="booking"  id="edit">
 <span style="font-size:18px;font-weight:bold;"><hr color="black" size="1">
 Miscellaneous Request   [ <?php echo $tour_id ?> ] 
 <hr color="black" size="1">
 <br />
</span>
<table style="border:1px dashed #F2F2F2;width:85%;background-color:#B6B6B6;" cellpadding="2">
	 
<tr>
    <td colspan="4" bgcolor="#B6B6B6">
    <table style="width:100%;background-color:#CCC;border:none;border-spacing:0" cellpadding="5">
    
      <tr>
        <td width="150" bgcolor="#AEAEAE" class="bold"><div align="right">Clients:</div></td>
        <td bgcolor="#B6B6B6" class="bold"><?=chngW($clients)?></td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="150" bgcolor="#AEAEAE" class="bold"><div align="right">pax:</div></td>
        <td bgcolor="#B6B6B6"><?=$pax?></td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="150" bgcolor="#AEAEAE" class="bold"><div align="right">from:</div></td>
        <td bgcolor="#B6B6B6"><?=$start?>
          <span class="bold">to</span>
          <?=$end?></td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="150" bgcolor="#AEAEAE" class="bold"><div align="right">routng:</div></td>
        <td bgcolor="#B6B6B6"><?=chngW(( $routing )) ?></td>
      </tr>
      <tr bgcolor="#CCCCCC">
        <td width="150" bgcolor="#AEAEAE" class="bold"><div align="right">company:</div></td>
        <td bgcolor="#B6B6B6"><?=chngW($company)?></td>
      </tr>
    </table></td>
  </tr>
</table>
<br />
<form id="frmEntrance" name="frmEntrance" method="post">
<?
$sql = "	
		SELECT  dbo.EntranceFeeBooking.EBId ,
        dbo.EntranceFeeBooking.Tourid ,
        dbo.EntranceFeeBooking.OnDay ,
        dbo.EntranceFeeBooking.BookDate ,
        dbo.EntranceFeeBooking.Price ,
        dbo.EntranceFeeBooking.Status ,
        dbo.EntranceFeeBooking.Remark ,
        ContactCompany.CompanyId ,
        ContactCompany.CompanyName ,
        CountryDesc ,
        dbo.SupplierServiceRate.ServiceRateId ,
        dbo.EntranceFeeBooking.ServiceContractId ,
		ServiceName,
       ( SELECT    ServiceMasterName
          FROM      dbo.SupplierServiceContract
                    INNER JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceContract.ServiceMasterId = dbo.SupplierServiceMaster.ServiceMasterId
          WHERE     dbo.SupplierServiceContract.ServiceContractId = dbo.BookingRateCost.ParentServiceContractId
        ) AS ServiceName2 ,
		dbo.BookingRateCost.Currency,
        dbo.EntranceFeeBooking.Status ,
        dbo.EntranceFeeBooking.Remark ,
        dbo.BookingRateCost.Pax ,
        dbo.BookingRateCost.Price ,
        dbo.BookingRateCost.IsPax ,
		dbo.BookingRateCost.RepeatPax,
        CAST(dbo.BookingRateSpecialChargeCost.Id AS VARCHAR(36)) AS BookingRateSpecialChargeCostId ,
        TotalPrice ,
        CostTypeId ,
        Combineflag

        -- DevMark
		, EntranceFeeBooking.CurrencyId
		, EntranceFeeBooking.CurrencyExtraCostId
		, EntranceFeeBooking.CurrencyReductionId

		, EntranceFeeBooking.ConfirmUS
		, EntranceFeeBooking.ConfirmLocal
		, EntranceFeeBooking.ConfirmCurrency

		, EntranceFeeBooking.ExtraCostUS
		, EntranceFeeBooking.ExtraCostLocal
		, EntranceFeeBooking.ExtraCostCurrency

		, EntranceFeeBooking.ReductionUS
		, EntranceFeeBooking.ReductionLocal
		, EntranceFeeBooking.ReductionCurrency

		, EntranceFeeBooking.ExtraCostRemark
		, EntranceFeeBooking.ReductionRemark
FROM    dbo.EntranceFeeBooking
        LEFT JOIN ContactCompany ON dbo.EntranceFeeBooking.CompanyId = ContactCompany.CompanyId
		INNER JOIN dbo.BookingRateCost ON dbo.EntranceFeeBooking.EBId = dbo.BookingRateCost.ReferanceId
		 LEFT JOIN dbo.BookingRateSpecialChargeCost ON dbo.BookingRateCost.BookingRateCostId = dbo.BookingRateSpecialChargeCost.BookingRateCostId
        LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.BookingRateCost.ServiceRateId
        LEFT JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceContract.ServiceContractId = dbo.SupplierServiceRate.ServiceContractId
       
        LEFT JOIN dbo.tbCountry ON dbo.tbCountry.CountryId = dbo.ContactCompany.CountryId
WHERE   ( dbo.EntranceFeeBooking.Tourid = ? )
        AND CostTypeId = ( SELECT   MAX(CostTypeId)
                           FROM     dbo.BookingRateCost AS c
                           WHERE    c.ReferanceId = dbo.EntranceFeeBooking.EBId
                                    AND CostTypeId IN ( 3, 4 )
                         )
ORDER BY onday

		";

unset($params);
$params[] = $tour_id;
//echo $sql;
//print_r($params);
$result = $db_Zend->query($sql,$params);		

?>

<!-- DevMark -->
<!-- Entrance Fee -->

<table style="width:85%;background-color:#CCC;border-spacing: 1px;" cellpadding="5" class="booking" >
  <tr valign="top" bgcolor="#CCCCCC">
    <td colspan="13" bgcolor="#FFFFFF" class="bold">
    
    <table style="width:100%;" >
      <tr>
          <td><h2>Entrance Fee </h2></td>
          <td align="right"><span style="cursor:pointer">
            <a name="Entrance"></a>
			Status:&nbsp;<select id="entrance_status" name="entrance_status">
			<?php 
				foreach($array_status as $item_status){
					echo '<option value="'.$item_status.'">'.$item_status.'</option>';
				}
			?>
			</select>
			<input type="button" name="btn_entrance" style="font-weight:bolder; color: #660000;" id="btn_entrance" value="Update status by checked item">
			<!-- DevMark -->
            <input type="button" name="button" style="font-weight:bolder; color: #660000;" id="button" value="Add" onClick="
            	document.frmEntrance.reset();

            	document.form1.reset();
            	$('#txtRemarks').val('');
            	expandTab();

            	$('#dialog').dialog('open');
            ">
            <input name="button3" type="button" style="font-weight:bolder; color: #660000;" value="back" onClick="window.location='booking_edit.php?id=<?=$tour_id?>' ;" />
          </span></td>
        </tr>
    </table>
	
 </td>
  </tr>
  <tr valign="top" bgcolor="#CCCCCC">
    <th bgcolor="#FFFFFF" class="bold">No.</th>
    <th bgcolor="#FFFFFF" class="bold">Company</th>
    <th bgcolor="#FFFFFF" class="bold">Country</th>
    <th bgcolor="#FFFFFF" class="bold">On Day</th>
    <th bgcolor="#FFFFFF" class="bold">Service</th>
    <th bgcolor="#FFFFFF" class="bold">Pax</th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Price Per Group</strong></th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Price Per Pax</strong></th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Total</strong></th>
    <th bgcolor="#FFFFFF" class="bold">Status</th>
    <th bgcolor="#FFFFFF" class="bold">Remark</th>
    <th bgcolor="#FFFFFF" class="bold">Action</th>
    <th bgcolor="#FFFFFF" class="bold"><img src="images/Delete.gif" name="imgDel"  id="imgDel" alt="Delete" title="Delete" style="cursor:pointer;"  ><br/><input type="checkbox" name="chkAllEntranceFee" id="chkAllEntranceFee" /></th>
  </tr> 
<? 
$bg			= "#99cccc" ;
$i = 0 ;

while($row = $result->fetch())
{
	$bg=($bg=="#E4E4E4")?"#99cccc":"#E4E4E4" ;
	$i++;
?>

    <tr	bgcolor="<?=$bg?>" onMouseOver="bgColor='#D7EBFF' "  onmouseout="bgColor='<?=$bg?>' " >		
  	<td><?=$i?></td>
  	<td><?=$row->CompanyName; ?></td>
  	<td><?=$row->CountryDesc;?></td>
  	<td>
  	 <?=$row->OnDay->format('d-M-Y');?></td>
  	<td><?=$row->ServiceName?>
  	  <?=!empty($row->ServiceName2)?"<br /><b>(Combine: {$row->ServiceName2} )</b>":""?></td>
  	<td align="center"><?=$row->Pax?></td>

  	<?php
    	$Value = ""; //reset

		$newPax = $row->Pax < 1 ? 1:$row->Pax;
		if($row->RepeatPax >1){
			$repletePax = ceil($newPax / $row->RepeatPax);
		}else{
			$repletePax = 1;
		}

		//Get NoOfUse from CostType 3, 4
		$sql = "SELECT EntranceFeeBooking.EBId
					, dbo.BookingRateCost.CostTypeId
					, dbo.BookingRateCost.NoOfUse
				FROM    EntranceFeeBooking
				INNER JOIN dbo.BookingRateCost ON dbo.EntranceFeeBooking.EBId = dbo.BookingRateCost.ReferanceId
					AND CostTypeId = ( 
						SELECT  MAX(CostTypeId)
                        FROM  dbo.BookingRateCost AS c
                        WHERE  c.ReferanceId = dbo.EntranceFeeBooking.EBId
                            AND CostTypeId IN ( 3, 4 )
						)
				WHERE  --EntranceFeeBooking.TourId = 'BKG1600620' 
					EntranceFeeBooking.EBId = ?
		";

		unset($params);
		$params[] = $VBId;
		// var_dump($sql, $params);
		$resultx = $db_Zend->query($sql, $params);
		while($rowx = $resultx->fetch()){
			$NoOfUse = $rowx -> NoOfUse;
		}
		$NoOfUse = empty($NoOfUse) || $NoOfUse < 1 ? 1:$NoOfUse;
		// var_dump($NoOfUse);
		
		//Check costtype
		$status = $row->Status;
		$CostTypeId = "";
		if($status == "OK"){
			$CostTypeId = 4;
		}
		else{
			$CostTypeId = 3;
		}
		// var_dump($CostTypeId);
	?>

	<!-- Price Per Group Column -->
  	<td align="center">
  	  <?php
      	$html = "";
      	if($row->IsPax=='0'){
      		$html .= '<b>'.$row->Currency.'</b> ';
      		//Check if status is ok
      		// $Value = $status == "OK" ? $row->Price : $row->ConfirmUS;
      		$Value = $row->Price;
      		$html .= number_format($Value, 2);
      	}
      	echo $html;
      ?>
  	  <span style="color:red;">
  	    <?=!empty($row->BookingRateSpecialChargeCostId) && $row->IsPax=='0'?' * ':''?>
	    </span>
	</td>
	<!-- End Price Per Group Column -->

	<!-- Price Per Pax Column -->
  	<td align="center">
  	  <?php
      	$html = "";
      	if($row->IsPax=='1'){
      		$html .= '<b>'.$row->Currency.'</b> ';
      		//Check if status is ok
      		// $Value = $status == "OK" ? $row->Price : $row->ConfirmUS;
      		$Value .= $row->Price;
      		$html .= number_format($Value,2);
      	}
      	echo $html;
      ?>
  	  <span style="color:red;">
  	    <?=!empty($row->BookingRateSpecialChargeCostId )&& $row->IsPax=='1'?' * ':''?>
	    </span>
	</td>
	<!-- End Price Per Pax Column -->

	<!-- Total Column -->
	<td align="center">
		<?php
    		$html = '';
    		if(isset($row->Price) && trim($row->Price) != ""){

    			if(!empty($repletePax) && $repletePax > 1){
    				$html .= '<b>RepeatPax</b> '.$repletePax.'<br/>';
    			}

    			if(!empty($NoOfUse) && $NoOfUse > 1 && $row->IsPax=='0'){
    				$html .= '<b>NoOfUse :</b>'.$NoOfUse.'<br>';
    			}

    			$html .= '<b>'.$row->Currency.'</b> ';

      			if($row->IsPax=='1'){
    				$Value = $row->Price*$newPax*$repletePax;
    			}
    			else{
    				$Value = $row->Price*$NoOfUse*$repletePax;
    			}

    			$Value = RecalculateCostWithAdjustmentEntranceFee($row -> EBId, $CostTypeId, $Value);

      			if(trim($Value) != ""){
      				$html .= number_format($Value, 2);
      			}
    		}
    		echo $html;
    	?>
	</td>
	<!-- End Total Column -->

  	<td><?=$row->Status?></td>
  	 	<td><?=$row->Remark?>&nbsp;</td>
  	 	<!-- DevMark -->
  	 	<td align="center"><a href="#Entrance" onClick="
  	 		document.form1.reset();
  	 		editEntranceFee('<?=$row->EBId?>');

  	 		$('tr[name^=\'confirm_row\']').hide();
  	 		expandTap();
  	 		return false;
  	 	">U</a>
        <a href="#Entrance" onClick="document.form1.reset();deleteEntranceFee('<?=$row->EBId?>');return false;">X</a>
        </td>
  	 	<td align="center"><input type="checkbox" name="chkEntrance" id="chkEntrance" value="<?=$row->EBId ?>">&nbsp;</td>
    </tr>
  <? } ?> 
    <tr>
  	<td colspan="15" align="right">&nbsp;</td>
  </tr>
</table>
</form>

<!-- End Entrance Fee -->

<br/>
<form id="frmBookingOther" name="frmBookingOther" method="post">
<?
$sql = "SELECT  dbo.MisceOtherBooking.MOBId ,
        dbo.MisceOtherBooking.Tourid ,
        dbo.MisceOtherBooking.OnDay ,
        dbo.MisceOtherBooking.BookDate ,
        dbo.MisceOtherBooking.Price ,
        dbo.MisceOtherBooking.Status ,
        dbo.MisceOtherBooking.Remark ,
        ContactCompany.CompanyId ,
        ContactCompany.CompanyName ,
        CountryDesc ,
        dbo.SupplierServiceRate.ServiceRateId ,
        dbo.MisceOtherBooking.ServiceContractId ,
        ServiceName ,
		( SELECT    ServiceMasterName
          FROM      dbo.SupplierServiceContract
                    INNER JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceContract.ServiceMasterId = dbo.SupplierServiceMaster.ServiceMasterId
          WHERE     dbo.SupplierServiceContract.ServiceContractId = dbo.BookingRateCost.ParentServiceContractId
        ) AS ServiceName2 ,
		dbo.BookingRateCost.Currency,
        dbo.MisceOtherBooking.Status ,
        dbo.MisceOtherBooking.Remark ,
        ServiceCategory_Desc ,
        dbo.BookingRateCost.Pax ,
        dbo.BookingRateCost.Price ,
        dbo.BookingRateCost.IsPax ,
		dbo.BookingRateCost.RepeatPax,
        CAST(dbo.BookingRateSpecialChargeCost.Id AS VARCHAR(36)) AS BookingRateSpecialChargeCostId ,
        TotalPrice ,
        CostTypeId,
		Combineflag

		-- DevMark
		, MisceOtherBooking.CurrencyId
		, MisceOtherBooking.CurrencyExtraCostId
		, MisceOtherBooking.CurrencyReductionId

		, MisceOtherBooking.ConfirmUS
		, MisceOtherBooking.ConfirmLocal
		, MisceOtherBooking.ConfirmCurrency

		, MisceOtherBooking.ExtraCostUS
		, MisceOtherBooking.ExtraCostLocal
		, MisceOtherBooking.ExtraCostCurrency

		, MisceOtherBooking.ReductionUS
		, MisceOtherBooking.ReductionLocal
		, MisceOtherBooking.ReductionCurrency

		, MisceOtherBooking.ExtraCostRemark
		, MisceOtherBooking.ReductionRemark
		, MisceOtherBooking.PayFullAmount
		
FROM    dbo.MisceOtherBooking
        LEFT JOIN ContactCompany ON dbo.MisceOtherBooking.CompanyId = ContactCompany.CompanyId
		INNER JOIN dbo.BookingRateCost ON dbo.MisceOtherBooking.MOBId = dbo.BookingRateCost.ReferanceId
        LEFT JOIN dbo.BookingRateSpecialChargeCost ON dbo.BookingRateCost.BookingRateCostId = dbo.BookingRateSpecialChargeCost.BookingRateCostId
		LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.BookingRateCost.ServiceRateId
        LEFT JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceContract.ServiceContractId = dbo.SupplierServiceRate.ServiceContractId      
        LEFT JOIN ServiceUnitType ON dbo.SupplierServiceRate.ServiceUnitTypeId = ServiceUnitType.Id
        LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.ServiceUnitType.ServiceCategoryId
        LEFT JOIN dbo.tbCountry ON dbo.tbCountry.CountryId = dbo.ContactCompany.CountryId
WHERE   ( dbo.MisceOtherBooking.Tourid = ? )
        AND CostTypeId = ( SELECT   MAX(CostTypeId)
                           FROM     dbo.BookingRateCost AS c
                           WHERE    c.ReferanceId =  dbo.MisceOtherBooking.MOBId
                                    AND CostTypeId IN ( 3, 4 )
                         )
		-- Exclude Package and activities services
		AND ServiceCategory_Desc NOT IN ('Package', 'Activity')
ORDER BY onday ,
        ServiceName
		";

unset($params);
$params[] = $tour_id;

//echo $sql;
//print_r($params);

$result = $db_Zend->query($sql,$params);

//echo "<pre>"; var_dump($result);

?>

<!-- DevMark -->
<!-- Miscellaneous -->

<table  style="width:85%;background-color:#CCC;border-spacing: 1px;" cellpadding="5"  class="booking" >
  <tr valign="top" bgcolor="#CCCCCC">
    <td colspan="14" bgcolor="#FFFFFF" class="bold">
    <table  style="width:100%;">
      <tr>
          <td><h2>Miscellaneous</h2></td>
          <td align="right"><span style="cursor:pointer">
            <a name="Misce"></a>
			Status:&nbsp;<select id="misce_status" name="misce_status">
			<?php 
				foreach($array_status as $item_status){
					echo '<option value="'.$item_status.'">'.$item_status.'</option>';
				}
			?>
			</select>
			<input type="button" name="btn_misce" style="font-weight:bolder; color: #660000;" id="btn_misce" value="Update status by checked item">
			<!-- DevMark -->
            <input type="button" name="button" style="font-weight:bolder; color: #660000;" id="button" value="Add" onClick="
            	document.frmBookingOther.reset();

            	document.form2.reset();
            	$('#txtRemarks2').val('');
            	expandTab2();

            	$('#dialog2').dialog('open');
            ">
            <input name="button3" type="button" style="font-weight:bolder; color: #660000;" value="back" onClick="window.location='booking_edit.php?id=<?=$tour_id?>' ;" />
          </span></td>
        </tr>
    </table>
 </td>
  </tr>
  <tr valign="top" bgcolor="#CCCCCC">
    <th bgcolor="#FFFFFF" class="bold">No.</th>
    <th bgcolor="#FFFFFF" class="bold">Company</th>
    <th bgcolor="#FFFFFF" class="bold">Country</th>
    <th bgcolor="#FFFFFF" class="bold">On Day</th>
    <th bgcolor="#FFFFFF" class="bold">Category</th>
    <th bgcolor="#FFFFFF" class="bold">Service</th>
    <th bgcolor="#FFFFFF" class="bold">Pax</th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Price Per Group</strong></th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Price Per Pax</strong></th>
    <th bgcolor="#FFFFFF" class="bold"><strong>Total</strong></th>
    <th bgcolor="#FFFFFF" class="bold">Status</th>
    <th bgcolor="#FFFFFF" class="bold">Remark</th>
    <th bgcolor="#FFFFFF" class="bold">Action</th>
    <th width="25" bgcolor="#FFFFFF" class="bold"><img src="images/Delete.gif"  name="imgDel2"  id="imgDel2"  alt="Delete" title="Delete" style="cursor:pointer;" ><br/><input type="checkbox" name="chkAllMisce" id="chkAllMisce" /></th>
  </tr> 
<? 
$bg			= "#99cccc" ;
$i = 0 ;

while($row = $result->fetch()){
	$bg=($bg=="#E4E4E4")?"#99cccc":"#E4E4E4" ;
	$i++;
?>

    <tr	bgcolor="<?=$bg?>" onMouseOver="bgColor='#D7EBFF' "  onmouseout="bgColor='<?=$bg?>' " >		
  	<td><?=$i?></td>
  	<td><?=$row->CompanyName; ?></td>
  	<td><?=$row->CountryDesc;?></td>
  	<td>
  	 <?=$row->OnDay->format('d-M-Y');?></td>
  	<td><?=$row->ServiceCategory_Desc?></td>
  	<td><?=$row->ServiceName?>
  	  <?=!empty($row->ServiceName2)?"<br /><b>(Combine: {$row->ServiceName2} )</b>":""?></td>
  	<td align="center"><?=$row->Pax?></td>
	
	<?php
		$Value = ""; //reset

		$newPax = $row->Pax < 1 ? 1:$row->Pax;
		if($row->RepeatPax >1){
			$repletePax = ceil($newPax / $row->RepeatPax);
		}else{
			$repletePax = 1;
		}

		//Get NoOfUse from CostType 3, 4
		$sql = "SELECT MisceOtherBooking.MOBId
					, dbo.BookingRateCost.CostTypeId
					, dbo.BookingRateCost.NoOfUse
				FROM    MisceOtherBooking
				INNER JOIN dbo.BookingRateCost ON dbo.MisceOtherBooking.MOBId = dbo.BookingRateCost.ReferanceId
					AND CostTypeId = ( 
						SELECT  MAX(CostTypeId)
                        FROM  dbo.BookingRateCost AS c
                        WHERE  c.ReferanceId = dbo.MisceOtherBooking.MOBId
                            AND CostTypeId IN ( 3, 4 )
						)
				WHERE  --MisceOtherBooking.TourId = 'BKG1600620' 
					MisceOtherBooking.MOBId = ?
		";

		unset($params);
		$params[] = $VBId;
		// var_dump($sql, $params);
		$resultx = $db_Zend->query($sql, $params);
		while($rowx = $resultx->fetch()){
			$NoOfUse = $rowx -> NoOfUse;
		}
		$NoOfUse = empty($NoOfUse) || $NoOfUse < 1 ? 1:$NoOfUse;
		// var_dump($NoOfUse);
		
		//Check costtype
		$status = $row->Status;
		$CostTypeId = "";
		if($status == "Booked"){
			$CostTypeId = 3;
		}else if($status == "OK"){
			$CostTypeId = 4;
		}
		// var_dump($CostTypeId);

	?>

  	<!-- Price Per Group Column -->
  	<td align="center">
  		<?php
      	$html = "";
      	if($row->IsPax=='0'){
    		$html .= '<b>'.$row->Currency.'</b> ';
    		//Check if status is ok
    		// $Value = $status == "OK" ? $row->Price : $row->ConfirmUS;
    		$Value = $row->Price;
   		 	$html .= number_format($Value, 2);
		}
      	echo $html;
      ?>
  	  <span style="color:red;">
  	    <?=!empty($row->BookingRateSpecialChargeCostId) && $row->IsPax=='0'?' * ':''?>
	    </span>
	</td>
	<!-- End Price Per Group Column -->

	<!-- Price Per Pax Column -->
  	<td align="center">
  	  <?php
      	$html = "";
      	if($row->IsPax=='1'){
    		$html .= '<b>'.$row->Currency.'</b> ';
    		//Check if status is ok
    		// $Value = $status == "OK" ? $row->Price : $row->ConfirmUS;
    		$Value .= $row->Price;
    		$html .= number_format($Value,2);
		}
      	echo $html;
      ?>
  	  <span style="color:red;">
  	    <?=!empty($row->BookingRateSpecialChargeCostId )&& $row->IsPax=='1'?' * ':''?>
	    </span>
	</td>
	<!-- End Price Per Pax Column -->

	<!-- Total Column -->
	<td align="center">
		<?php
    		$html = '';
    		if(isset($row->Price) && trim($row->Price) != ""){

    			if(!empty($repletePax) && $repletePax > 1){
    				$html .= '<b>RepeatPax</b> '.$repletePax.'<br/>';
    			}

    			if(!empty($NoOfUse) && $NoOfUse > 1 && $row->IsPax=='0'){
    				$html .= '<b>NoOfUse :</b>'.$NoOfUse.'<br>';
    			}

    			$html .= '<b>'.$row->Currency.'</b> ';
      			
      			if($row->IsPax=='1'){
    				$Value = $row->Price*$newPax*$repletePax;
				}else{
					$Value = $row->Price*$NoOfUse*$repletePax;
				}

				$newPrice = RecalculateCostWithAdjustmentMiscellaneous($row -> MOBId, $CostTypeId, $Value);
				if(trim($Value) != ""){
   	 				$html .= number_format($Value, 2);
				}
    		}
    		echo $html;
    	?>
	</td>
	<!-- End Total Column -->

  		<td><?=$row->Status?></td>
  	 	<td><?=$row->Remark?>&nbsp;</td>
  	 	<!-- DevMark -->
  	 	<?php if($row->PayFullAmount==1){?>
  	 	
  	 		<td align="center"><span style="color:#027016;font-weight:bold;">PAID</span></td>
  	 		<td align="center"> &nbsp;</td>
  	 	<?php }else{?>
  	 	<td align="center">
	  	 	<a href="#Entrance" title="Update" onClick="
	  	 		document.frmBookingOther.reset();
	  	 		editBookingOther('<?=$row->MOBId?>');
	
	  	 		$('tr[name^=\'confirm_row2\']').hide();
	  	 		expandTap2();
	  	 		return false;
	  	 	">U</a>
        	<a href="#" title="Delete" onClick="document.frmBookingOther.reset();deleteBookingOther('<?=$row->MOBId?>');return false;">X</a>
        </td>
 	  <td align="center"><input type="checkbox" name="chkMisce" id="chkMisce" value="<?=$row->MOBId ?>">&nbsp;</td>
 	  
 	  <?php }?>
    </tr>
  <? } ?> 
    <tr>
  	<td colspan="16" align="right">&nbsp;</td>
  </tr>
</table>
</form>

<!-- End Miscellaneous -->

<br>
<form id="frmOther" name="frmOther" method="post">
<?
$sql = "	
		SELECT  WBId ,
				CountryDesc ,
				OnDay ,
				DrinkingWaterDesc ,
				dbo.WaterBooking.Price,
				dbo.WaterBooking.Status,
				dbo.WaterBooking.Remark

				-- DevMark
				, WaterBooking.CurrencyId
				, WaterBooking.CurrencyExtraCostId
				, WaterBooking.CurrencyReductionId

				, WaterBooking.ConfirmUS
				, WaterBooking.ConfirmLocal
				, WaterBooking.ConfirmCurrency

				, WaterBooking.ExtraCostUS
				, WaterBooking.ExtraCostLocal
				, WaterBooking.ExtraCostCurrency

				, WaterBooking.ReductionUS
				, WaterBooking.ReductionLocal
				, WaterBooking.ReductionCurrency

				, WaterBooking.ExtraCostRemark
				, WaterBooking.ReductionRemark
		FROM    dbo.WaterBooking
				INNER JOIN dbo.DrinkWater ON dbo.DrinkWater.Id = DrinkWaterId
				INNER JOIN dbo.tbCountry ON dbo.DrinkWater.CountryId = dbo.tbCountry.CountryId
		WHERE   TourId = ?
		ORDER BY OnDay 

		";

unset($params);
$params[] = $tour_id;

$result = $db_Zend->query($sql,$params);		

?>

<!-- DevMark -->
<!-- Watercost -->

<table  style="width:85%;background-color:#CCC;border-spacing: 1px;" cellpadding="5" class="booking" >
  <tr valign="top" bgcolor="#CCCCCC">
    <td colspan="9" bgcolor="#FFFFFF" class="bold">
    <table   style="width:100%;">
      <tr>
          <td><h2>Water cost </h2></td>
          <td align="right"><span style="cursor:pointer">
            <a name="Water"></a>
			Status:&nbsp;<select id="water_status" name="water_status">
			<?php 
				foreach($array_status as $item_status){
					echo '<option value="'.$item_status.'">'.$item_status.'</option>';
				}
			?>
			</select>
			<input type="button" name="btn_water" style="font-weight:bolder; color: #660000;" id="btn_water" value="Update status by checked item">
			<!-- DevMark -->
            <input type="button" name="button" style="font-weight:bolder; color: #660000;" id="button" value="Add" onClick="
            	

            	document.formWater.reset();
            	$('#txtRemarksWater').val('');
            	expandTabWater();

            	$('#dialogWater').dialog('open');
            ">
            <!-- document.formWater.reset(); -->
            <input name="button3" type="button" style="font-weight:bolder; color: #660000;" value="back" onClick="window.location='booking_edit.php?id=<?=$tour_id?>' ;" />
          </span></td>
        </tr>
    </table>
 </td>
  </tr>
  <tr valign="top" bgcolor="#CCCCCC">
    <th bgcolor="#FFFFFF" class="bold">No.</th>
    <th bgcolor="#FFFFFF" class="bold">Country</th>
    <th bgcolor="#FFFFFF" class="bold">On Day</th>
    <th bgcolor="#FFFFFF" class="bold">water</th>
    <th bgcolor="#FFFFFF" class="bold">Price</th>
    <th bgcolor="#FFFFFF" class="bold">Status</th>
    <th bgcolor="#FFFFFF" class="bold">Remark</th>
    <th bgcolor="#FFFFFF" class="bold">Action</th>
    <th width="25" bgcolor="#FFFFFF" class="bold"><img src="images/Delete.gif" alt="Delete" title="Delete" style="cursor:pointer;" name="imgDel3"  id="imgDel3"   ><br/><input type="checkbox" name="chkAllWater" id="chkAllWater" /></th>
  </tr> 
<? 
$bg			= "#99cccc" ;
$i = 0 ;

while($row = $result->fetch())
{
	$bg=($bg=="#E4E4E4")?"#99cccc":"#E4E4E4" ;
	$i++;
?>

    <tr	bgcolor="<?=$bg?>" onMouseOver="bgColor='#D7EBFF' "  onmouseout="bgColor='<?=$bg?>' " >		
  	<td><?=$i?></td>
  	<td><?=$row->CountryDesc;?></td>
  	<td>
  	 <?=$row->OnDay->format('d-M-Y');?></td>
  	<td><?=$row->DrinkingWaterDesc?></td>
  	<!-- DevMark -->
  	<td align="right">
  		<?= !empty($row -> ConfirmLocal) ? "<strong>".$row -> ConfirmCurrency."</strong> ".$row -> ConfirmLocal : "$ ".number_format($row -> Price,2) ?>
  	</td>
 	  <td><?=$row->Status?></td>
  	 	<td><?=$row->Remark?>&nbsp;</td>
  	 	<!-- DevMark -->
  	 	<td align="center"><a href="#Water" onClick="
  	 		document.form1.reset();
  	 		editWater('<?=$row->WBId?>');

  	 		expandTabWater();
  	 		return false;
  	 	">U</a>
        <a href="#Water" onClick="document.form1.reset();deleteWater('<?=$row->WBId?>');return false;">X</a>
        </td>
  	 	<td align="center"><input type="checkbox" name="chkWater" id="chkWater" value="<?=$row->WBId ?>">&nbsp;</td>
    </tr>
  <? } ?> 
    <tr>
  	<td colspan="12" align="right">&nbsp;</td>
  </tr>
</table>
</form>

<!-- End Watercost -->

<form id="frmWater" name="frmWater" method="post">
<?
$sql = "SELECT dbo.OtherCostBooking.OtherCostBookingId
	, dbo.OtherCostBooking.CountryId
	, dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.CompanyId
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.DateRun
    , dbo.OtherCostBooking.OtherCostDesc
    --, dbo.OtherCostBooking.OtherCostPrice
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.QuotationId
    , dbo.OtherCostBooking.ConfirmationsId
    , dbo.OtherCostBooking.Detail
    , dbo.OtherCostBooking.Remark
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.CurrencyId
    , dbo.OtherCostBooking.CurrencyExtraCostId
    , dbo.OtherCostBooking.CurrencyReductionId
    , dbo.OtherCostBooking.ConfirmUS
    , dbo.OtherCostBooking.ConfirmLocal
    , dbo.OtherCostBooking.ConfirmCurrency
    , dbo.OtherCostBooking.ExtraCostUS
    , dbo.OtherCostBooking.ExtraCostLocal
    , dbo.OtherCostBooking.ExtraCostCurrency
    , dbo.OtherCostBooking.ReductionUS
    , dbo.OtherCostBooking.ReductionLocal
    , dbo.OtherCostBooking.ReductionCurrency
    , dbo.OtherCostBooking.ExtraCostRemark
    , dbo.OtherCostBooking.ReductionRemark
    , dbo.OtherCostBooking.PayFullAmount
    , dbo.OtherCostBooking.[Status]
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.OtherCostType

	, dbo.OtherCostBooking.QuoteUS
    , dbo.OtherCostBooking.QuoteLocal
    , dbo.OtherCostBooking.QuoteCurrency
	, dbo.OtherCostBooking.BookUS
    , dbo.OtherCostBooking.BookLocal
    , dbo.OtherCostBooking.BookCurrency
    , dbo.OtherCostBooking.ConfirmUS
    , dbo.OtherCostBooking.ConfirmLocal
    , dbo.OtherCostBooking.ConfirmCurrency
    
    , dbo.tbCountry.CountryDesc  AS [Country]
    , dbo.tbCity.City AS [City]
    , dbo.ServiceCategory.ServiceCategory_Desc AS [Category]
FROM dbo.OtherCostBooking 
LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.OtherCostBooking.ServiceCategoryId
LEFT JOIN dbo.tbCountry ON dbo.tbCountry.CountryId = dbo.OtherCostBooking.CountryId
LEFT JOIN dbo.tbCity ON dbo.tbCity.CityId = dbo.OtherCostBooking.CityId
WHERE dbo.OtherCostBooking.TourId = ?
ORDER BY dbo.OtherCostBooking.DateRun
	, dbo.ServiceCategory.ServiceCategory_Desc
	, dbo.OtherCostBooking.cdate
		";

unset($params);
$params[] = $tour_id;

$result = $db_Zend->query($sql,$params);		

?>

<!-- DevMark -->
<!-- Othercost -->
<table style="width:85%;background-color:#CCC;border-spacing: 1px;" cellpadding="5" class="booking" >
  <tr valign="top" bgcolor="#CCCCCC">
    <td colspan="15" bgcolor="#FFFFFF" class="bold">
    <table style="width:100%;" >
      <tr>
          <td><h2>Other cost </h2></td>
          <td align="right"><span style="cursor:pointer">
            <a name="Other"></a>
			<input type="button" name="button" style="font-weight:bolder; color: #660000;" id="button" value="Add" onClick="document.formOtherCost.reset();$('#dialogOtherCost').dialog('open');">
            <input name="button3" type="button" style="font-weight:bolder; color: #660000;" value="back" onClick="window.location='booking_edit.php?id=<?=$tour_id?>' ;" />
          </span></td>
        </tr>
    </table>
 </td>
  </tr>
  <tr valign="top" bgcolor="#CCCCCC">
    <th bgcolor="#FFFFFF" class="bold">No.</th>
    <th bgcolor="#FFFFFF" class="bold">On Day</th>
    <th bgcolor="#FFFFFF" class="bold">Country</th>
    <th bgcolor="#FFFFFF" class="bold">City</th>
    <th bgcolor="#FFFFFF" class="bold">Company</th>
    <th bgcolor="#FFFFFF" class="bold">Category</th>
    <th bgcolor="#FFFFFF" class="bold">Cost Detail</th>
    <th bgcolor="#FFFFFF" class="bold">Cost Price</th>
    <th bgcolor="#FFFFFF" class="bold">Cost Currency</th>
    <th bgcolor="#FFFFFF" class="bold">Cost Type (Pax/Group)</th>
    <th bgcolor="#FFFFFF" class="bold">Pax</th>
    <th bgcolor="#FFFFFF" class="bold">Status</th>
    <th bgcolor="#FFFFFF" class="bold">Remark</th>
    <th bgcolor="#FFFFFF" class="bold">Action</th>
    <th width="25" bgcolor="#FFFFFF" class="bold"><img src="images/Delete.gif" alt="Delete" title="Delete" style="cursor:pointer;" name="imgDel4"  id="imgDel4"><br/><input type="checkbox" name="chkAllOther" id="chkAllOther" /></th>
  </tr> 
<? 
$bg			= "#99cccc" ;
$i = 0 ;

while($row = $result->fetch())
{
	// echo "<pre>"; var_dump($row);
	// var_dump($row -> PayFullAmount);
	//** Override ddlCity , ddlCompany if have selected data

	//Modification Authorize
	$disabled = "";
	$readonly = "";
	$isPaid = 0;
	// $row -> PayFullAmount = 1;
	if(isset($row -> PayFullAmount) && $row -> PayFullAmount == 0){
		$disabled = "";
		$readonly = "";
		$isPaid = 0;
	}
	else if(isset($row -> PayFullAmount) && $row -> PayFullAmount == 1){
		$disabled = "disabled=\"disabled\"";
		$readonly = "readonly=\"readonly\"";
		$isPaid = 1;
	}
	else{
		var_dump('Error : PayFullAmount record is missing.');
	}

	//City
	$CountryId = $row -> CountryId;
	$where = "";
	if(isset($CountryId)){
		$where .= " AND dbo.tbCity.CountryId = '$CountryId' ";
		$array_city = array(); //reset
		$sql = "SELECT dbo.tbCity.CityId, dbo.tbCity.City FROM dbo.tbCity WHERE 1=1 AND dbo.tbCity.City <> '' $where ORDER BY dbo.tbCity.City" ;
		if($rs =  $db_Zend->fetchAll($sql)) foreach($rs as $r){
			$array_city[$r->CityId] = $r->City;
		}
	}
	
	//Company
	$CityId = $row -> CityId;
	$where = "";
	if(isset($CountryId)){
		$where .= " AND dbo.ContactCompany.CountryId = '$CountryId' ";
	}
	if(isset($CityId)){
		$where .= " AND dbo.ContactCompany.CityId = '$CityId' ";
	} 
	if(isset($CountryId) || isset($CityId)){
		$array_company = array(); //reset
		$sql = "SELECT CompanyId, CompanyName FROM dbo.ContactCompany WHERE 1=1 AND CompanyName <> ' ' $where ORDER BY CompanyName " ;
		if($rs =  $db_Zend->fetchAll($sql)) foreach($rs as $r){
			$array_company[$r->CompanyId] = $r->CompanyName;
		}	
	}
	// echo "<pre>"; var_dump($array_company, $row -> CompanyId);

	//OtherCostPrice
	if(!empty($row -> Status) && trim($row -> Status) == 'OK'){
		$OtherCostPrice = $row -> ConfirmLocal;
	}
	else{
		$OtherCostPrice = $row -> BookLocal;
	}
	

	//***********************************************

	$bg=($bg=="#E4E4E4")?"#99cccc":"#E4E4E4" ;
	$i++;
?>

    <tr	bgcolor="<?=$bg?>" onMouseOver="bgColor='#D7EBFF' "  onmouseout="bgColor='<?=$bg?>' " >		
  	<td><?=$i?></td>
  	<td><?=$row->DateRun->format('d-M-Y');?></td>

  	<td>
  		<select <?= $disabled; ?> class="selCountry" id="selCountryOtherCost<?=$i?>" name="selCountryOtherCost<?=$i?>" onchange="EditOtherCostFilter1(<?=$i?>)"><option value="">-select-</option>
		<?php
			foreach($array_country as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->CountryId){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select>
	</td>

	<td>
  		<select <?= $disabled; ?> class="selCity" id="selCityOtherCost<?=$i?>" name="selCityOtherCost<?=$i?>" onchange="EditOtherCostFilter2(<?=$i?>)"><option value="">-select-</option>
		<?php
			foreach($array_city as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->CityId){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select>
	</td>

  	<td>
  		<select <?= $disabled; ?> class="selCompany" id="selCompanyOtherCost<?=$i?>" name="selCompanyOtherCost<?=$i?>"><option value="">-select-</option>
		<?php
			foreach($array_company as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->CompanyId){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select>
	</td>

  	<td><select <?= $disabled; ?> class="selCategory" id="selCategoryOtherCost<?=$i?>" name="selCategoryOtherCost<?=$i?>"><option value="">-select-</option>
		<?php
			foreach($array_category as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->ServiceCategoryId){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select></td>
  	<td align="center">
  		<textarea id="txtOtherCostDesc<?=$i?>" name="txtOtherCostDesc<?=$i?>" cols="5"><?=$row->OtherCostDesc;?></textarea>
  	</td>
  	<td align="center">
  	 <!-- DevMark -->
  	  <input <?= $disabled ?> name="txtOtherCostPrice<?=$i?>" type="text" id="txtOtherCostPrice<?=$i?>" size="10" value="<?=number_format($OtherCostPrice, 2);?>">
  	</td>
  	<td><select <?= $disabled; ?> class="selCurrency" id="selOtherCostCurrency<?=$i?>" name="selOtherCostCurrency<?=$i?>"><option value="">-select-</option>
		<?php
			$split_arr = array();
			foreach($array_currency as $key => $value){
				$split_arr = explode('~', $key);
				echo '<option value="'.$key.'"';
				if($split_arr[0] == $row->CurrencyId){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select></td>
  	<td>
  		<select <?= $disabled; ?> id="selCostType<?=$i?>" name="selCostType<?=$i?>"><option value="">-select-</option>
		<?php
			foreach($array_costtype as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->OtherCostType){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select>
	</td>
	<td align="center">
  	  <input <?= $disabled ?> size="1" name="txtPax<?=$i?>" type="text" id="txtPax<?=$i?>" size="10" value="<?=$row->Pax;?>" onblur="
		if($(this).val().trim() == '' || isNaN($(this).val())) {
        	alert('pax must be number only.');
        	$(this).val('');
        }
  	  ">
  	</td>
  	<td><select <?= $disabled; ?> class="selStatus" id="selOtherCostStatus<?=$i?>" name="selOtherCostStatus<?=$i?>"><option value="">-select-</option>
		<?php
			foreach($array_status as $key => $value){
				echo '<option value="'.$key.'"';
				if($key == $row->Status){
					echo ' selected ';
				}
				echo '>'.$value.'</option>';
			}
		?>
		</select>
	</td>
  	<td>&nbsp;
  	  <input size="7" name="txtRemark<?=$i?>" type="text" id="txtRemark<?=$i?>" size="10" value="<?=$row->Remark?>">
  	  <input type="hidden" name="hidOtherCostBookingId<?=$i?>" id="hidOtherCostBookingId<?=$i?>" value="<?=$row->OtherCostBookingId?>">
  	</td>
  	<td align="center">
        <a href="#Other" onClick="editOtherSave('<?=$i?>');return false;">U</a>
        <?php if(!$isPaid) : ?>
        	<a href="#Other" onClick="deleteOtherSave('<?=$i?>');return false;">X</a>
        <?php endif; ?>
    </td>
  	<td align="center">
  		<?php if($isPaid) : ?>
  			<strong><font color="red">Paid</font></strong>
  		<?php else: ?>	
  			<input type="checkbox" name="chkOther" id="chkOther" value="<?=$row->OtherCostBookingId?>">&nbsp;
  		<?php endif; ?>
  	</td>
  </tr>
  <? } ?> 
    <tr>
  	<td colspan="12" align="right">&nbsp;</td>
  </tr>
</table>
</form>

<!-- End Othercost -->

<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td align="center"><input type="button" name="button4" id="button2" value="E-mail">
      <input type="button" name="button5" id="button3" value="Fax">
      <input type="button" name="button6" id="button4" value="Print"></td>
  </tr>
</table>

<!-- DevMark -->
<div id="dialog">
  <form name="form1" id="form1" method="post" action="">
    <table border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td><strong>Country</strong></td>
        <td><select name="selCountry" id="selCountry">
        <option value="0" selected="selected">--select--</option>
        <?
        while($Country = $Countrys->fetch())
		{
		?>
        <option value="<?=$Country->CountryId?>"><?=$Country->CountryDesc?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>City</strong></td>
        <td><select name="selCity" id="selCity">
        </select></td>
      </tr>
      <tr>
        <td><strong>Supplier Company</strong></td>
        <td><select name="selSupplier" id="selSupplier">
        </select></td>
      </tr>
      <tr>
        <td><strong>Service name</strong></td>
        <td><select name="selService" id="selService">
        </select></td>
      </tr>
      <tr>
        <td><strong>Entrance Fee</strong></td>
        <td><select name="selVehicle" id="selVehicle" onchange="AutoMatchCurrencyEntranceFee()">
        </select></td>
      </tr>
      <tr>
        <td><strong>Pax</strong></td>
        <td><input name="txtPax" type="text" id="txtPax" size="15"></td>
      </tr>
      <tr>
        <td><strong>On Day</strong></td>
        <td><input name="txtOnDay" class="datepicker" type="text" id="txtOnDay" size="15"></td>
      </tr>
      <tr>
        <td><strong>Status</strong></td>
        <td><select name="selStatus" id="selStatus" onchange="expandTab()">
        <option value="">--status--</option>
        <? foreach($arrStat as $value){ ?>
        <option value="<?=$value?>"><?=$value?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>Remarks
          <input type="hidden" name="hidEBId" id="hidEBId">
          <input type="hidden" name="hidMode" id="hidMode" value="addEntranceFee">      
          <input type="hidden" name="tour_id" id="tour_id" value="<?=$tour_id;?>">         
        </strong></td>
        <td><label for="txtRemarks"></label>
        <textarea name="txtRemarks" id="txtRemarks" cols="45" rows="5"></textarea></td>
      </tr>

		<!-- DevMark -->
		<!-- Currency -->
    <tr class="hide" name="confirm_row">
    	<td colspan="2">
    		<!-- Confirmation -->
			<table>
    			<tr>
    				<td><b>Confirm: </b></td>
    				<td>
						<input type="text" id="txt_local_rate" name="txt_local_rate" size="12" value="" onblur="
        					if(!isNumber(this.value)) {this.value = ''; return;};
        					ClearCurrency('sel_local_currency', 'txt_local_rate', 'txt_usd');
        					ConvertCurrency('sel_local_currency', 'txt_local_rate', 'txt_usd');
        				">
    				</td>
    				<td><b>Currency: </b></td>
    				<td>
    					<select id="sel_local_currency" name="sel_local_currency" onchange="
    						ClearCurrency('sel_local_currency', 'txt_local_rate', 'txt_usd');
    						AutoMatchCurrencyEntranceFee();
        					ConvertCurrency('sel_local_currency', 'txt_local_rate', 'txt_usd');
						">
						<option value=""></option>
						<?php
						//Get Currency
						foreach ($arrCurrency as $data) {
							$split_arr = explode('~', $data);
							echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
						}
						?>
						</select>
					</td>
    				<td>
        				<input id="txt_usd" name="txt_usd" type="text" value="" size="12" onblur="if(!isNumber(this.value)) {this.value = ''};"
							readonly
        				/>
        				US$&nbsp;
    				</td>
    			</tr>
    		</table>
    		<!-- End Confirmation -->
		</td>
    </tr>

    <tr name="extracost_reduction_row">
    	<td colspan="2">
    		<!-- Extracost and Reduction -->
    		<div class="border">
    		<table>
    			<tr>
    				<td><b>Extra cost: </b></td> 
    				<td>
    					<input id="txt_local_rate_extra_cost" name="txt_local_rate_extra_cost" type="text" value="" size="12" onchange="
        					if(!isNumber(this.value)) {this.value = ''; return;};
        					ClearCurrency('sel_local_currency_extra_cost', 'txt_local_rate_extra_cost', 'txt_usd_extra_cost');
        					ConvertCurrency('sel_local_currency_extra_cost', 'txt_local_rate_extra_cost', 'txt_usd_extra_cost');
        				" />
        			</td>
    				<td><b>Currency: </b></td>
    				<td>
						<select id="sel_local_currency_extra_cost" name="sel_local_currency_extra_cost" onchange="
							ClearCurrency('sel_local_currency_extra_cost', 'txt_local_rate_extra_cost', 'txt_usd_extra_cost');
        					ConvertCurrency('sel_local_currency_extra_cost', 'txt_local_rate_extra_cost', 'txt_usd_extra_cost');
        				" disabled>
						<option value=""></option>
						<?php
							//Get Currency
							foreach ($arrCurrency as $data) {
								$split_arr = explode('~', $data);
								echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
							}
						?>
						</select>
    				</td>
    				<td><b>Rate :</b></td>
    				<td>
        				<input id="txt_usd_extra_cost" name="txt_usd_extra_cost" type="text" value="" size="12" onblur="
    						if(!isNumber(this.value)) {this.value = ''};
    					" readonly/>
        				US$&nbsp;
    				</td>
    			</tr>
    			<tr>
    				<td><b>Remark: </b></td>
    				<td colspan="5">
    					<textarea style="width:100%;" id="txt_extra_cost_remark" name="txt_extra_cost_remark"></textarea>
    				</td>
    			</tr>
    			<tr>
    				<td><font color="red"><b>Reduction: </b></font></td>
    				<td>
						<input id="txt_local_rate_reduction" name="txt_local_rate_reduction" type="text" value="" size="12" onchange="
							if(!isNumber(this.value)) {this.value = ''};
							ClearCurrency('sel_local_currency_reduction', 'txt_local_rate_reduction', 'txt_usd_reduction');
							ConvertCurrency('sel_local_currency_reduction', 'txt_local_rate_reduction', 'txt_usd_reduction');
        				" />
    				</td>
    				<td><font color="red"><b>Currency: </b></font></td>
    				<td>
						<select id="sel_local_currency_reduction" name="sel_local_currency_reduction" onchange="
							ClearCurrency('sel_local_currency_reduction', 'txt_local_rate_reduction', 'txt_usd_reduction');
        					ConvertCurrency('sel_local_currency_reduction', 'txt_local_rate_reduction', 'txt_usd_reduction');
        				" disabled>
						<option value=""></option>
						<?
							//Get Currency
							foreach ($arrCurrency as $data) {
								$split_arr = explode('~', $data);
								echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
							}
						?>
						</select>
    				</td>
    				<td><font color="red"><b>Rate: </b></font></td>
    				<td>
        				<input id="txt_usd_reduction" name="txt_usd_reduction" type="text" value="" size="12" onblur="
							if(!isNumber(this.value)) {this.value = ''};
						" readonly />
        				US$&nbsp;
    				</td>
    			</tr>
    			<tr>
    				<td><font color="red"><b>Remark :</b></font></td>
    				<td colspan="5">
						<textarea style="width:100%;" id="txt_reduction_remark" name="txt_reduction_remark"></textarea>
    				</td>
    			</tr>
    			<tr>
		      		<td>&nbsp;</td>
		    		 <td colspan="5">
						<span style="color:red">*Please fill in total amount. </span>
		    		</td>
    			</tr>
    			
    			
    		</table>
    		</div>
    		<!-- End Extracost and Reduction -->
    	</td>
    </tr>
  	<!-- End Currency -->


      <tr>
        <td>&nbsp;</td>
        <td><input type="button" name="buttonAction" id="buttonAction" value="S A V E" onClick="editEntranceSave();">
        <input type="button" name="buttonCancel" id="buttonCancel" onClick="$('#dialog').dialog('close');" value="C L O S E"></td>
      </tr>
    </table>
  </form>
</div>

<div id="dialog2">
  <form name="form2" id="form2" method="post" action="">
    <table border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td><strong>Country</strong></td>
        <td><select name="selCountry2" id="selCountry2">
        <option value="0" selected="selected">--select--</option>
        <?
        while($Country = $Countrys2->fetch())
		{
		?>
        <option value="<?=$Country->CountryId?>"><?=$Country->CountryDesc?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>City</strong></td>
        <td><select name="selCity2" id="selCity2">
        </select></td>
      </tr>
      <tr>
        <td><strong>Supplier Company</strong></td>
        <td><select name="selSupplier2" id="selSupplier2">
        </select></td>
      </tr>
      <tr>
        <td><strong>Service name</strong></td>
        <td><select name="selService2" id="selService2">
        </select></td>
      </tr>
      <tr>
        <td><strong>Miscellaneous</strong></td>
        <td><select name="selVehicle2" id="selVehicle2" onchange="AutoMatchCurrencyMiscellaneous()">
        </select></td>
      </tr>
      <tr>
        <td><strong>Pax</strong></td>
        <td><input name="txtPax2" type="text" id="txtPax2" size="15"></td>
      </tr>
      <tr>
        <td><strong>On Day</strong></td>
        <td><input name="txtOnDay2" class="datepicker" type="text" id="txtOnDay2" size="15"></td>
      </tr>
      <tr>
        <td><strong>Status</strong></td>
        <td><select name="selStatus2" id="selStatus2" onchange="expandTab2()">
        <option value="">--status--</option>
        <? foreach($arrStat as $value){ ?>
        <option value="<?=$value?>"><?=$value?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>Remarks
          <input type="hidden" name="hidEBId2" id="hidEBId2">
          <input type="hidden" name="hidMode2" id="hidMode2" value="addBookingOther">      
          <input type="hidden" name="tour_id2" id="tour_id2" value="<?=$tour_id;?>">         
        </strong></td>
        <td><label for="txtRemarks2"></label>
        <textarea name="txtRemarks2" id="txtRemarks2" cols="45" rows="5"></textarea></td>
      </tr>

		<!-- DevMark -->
		<!-- Currency -->
    <tr class="hide" name="confirm_row2">
    	<td colspan="2">
    		<!-- Confirmation -->
			<table>
    			<tr>
    				<td><b>Confirm:</b></td>
    				<td>
						<input type="text" id="txt_local_rate2" name="txt_local_rate2" size="12" value="" onblur="
        					if(!isNumber(this.value)) {this.value = ''; return;};
        					ClearCurrency('sel_local_currency2', 'txt_local_rate2', 'txt_usd2');
        					ConvertCurrency('sel_local_currency2', 'txt_local_rate2', 'txt_usd2');
        				">
    				</td>
    				<td><b>Currency: </b></td>
    				<td>
    					<select id="sel_local_currency2" name="sel_local_currency2" onchange="
    						ClearCurrency('sel_local_currency2', 'txt_local_rate2', 'txt_usd2');
    						AutoMatchCurrencyMiscellaneous();
        					ConvertCurrency('sel_local_currency2', 'txt_local_rate2', 'txt_usd2');
						">
						<option value=""></option>
						<?php
						//Get Currency
						foreach ($arrCurrency as $data) {
							$split_arr = explode('~', $data);
							echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
						}
						?>
						</select>
					</td>
    				<td>
        				<input id="txt_usd2" name="txt_usd2" type="text" value="" size="12" onblur="if(!isNumber(this.value)) {this.value = ''};"
        				 readonly 
        				/>
        				US$&nbsp;
    				</td>
    			</tr>
    		</table>
    		<!-- End Confirmation -->
		</td>
    </tr>

    <tr name="extracost_reduction_row2">
    	<td colspan="2">
    		<div class="border">
    		<table>
    			<!-- Extracost -->
    			<tr>
    				<td><b>Extra cost: </b></td> 
    				<td>
    					<input id="txt_local_rate_extra_cost2" name="txt_local_rate_extra_cost2" type="text" value="" size="12" onchange="
        					if(!isNumber(this.value)) {this.value = ''; return;};
        					ClearCurrency('sel_local_currency_extra_cost2', 'txt_local_rate_extra_cost2', 'txt_usd_extra_cost2');
        					ConvertCurrency('sel_local_currency_extra_cost2', 'txt_local_rate_extra_cost2', 'txt_usd_extra_cost2');
        				" />
        			</td>
    				<td><b>Currency: </b></td>
    				<td>
						<select id="sel_local_currency_extra_cost2" name="sel_local_currency_extra_cost2" onchange="
							ClearCurrency('sel_local_currency_extra_cost2', 'txt_local_rate_extra_cost2', 'txt_usd_extra_cost2');
        					ConvertCurrency('sel_local_currency_extra_cost2', 'txt_local_rate_extra_cost2', 'txt_usd_extra_cost2');
        				" disabled >
						<option value=""></option>
						<?php
							//Get Currency
							foreach ($arrCurrency as $data) {
								$split_arr = explode('~', $data);
								echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
							}
						?>
						</select>
    				</td>
    				<td><b>Rate :</b></td>
    				<td>
    					<input id="txt_usd_extra_cost2" name="txt_usd_extra_cost2" type="text" value="" size="12" onblur="
    						if(!isNumber(this.value)) {this.value = ''};
    					" readonly/>
        				US$&nbsp;
    				</td>
    			</tr>
    			<tr>
    				<td><b>Remark: </b></td>
    				<td colspan="5">
    					<textarea style="width:100%;" id="txt_extra_cost_remark2" name="txt_extra_cost_remark2"></textarea>
    				</td>
    			</tr>
    			<!-- End Extracost -->

    			<!-- Reduction -->
    			<tr>
    				<td><font color="red"><b>Reduction: </b></font></td>
    				<td>
						<input id="txt_local_rate_reduction2" name="txt_local_rate_reduction2" type="text" value="" size="12" onchange="
							if(!isNumber(this.value)) {this.value = ''};
							ClearCurrency('sel_local_currency_reduction2', 'txt_local_rate_reduction2', 'txt_usd_reduction2');
							ConvertCurrency('sel_local_currency_reduction2', 'txt_local_rate_reduction2', 'txt_usd_reduction2');
        				" />
    				</td>
    				<td><font color="red"><b>Currency: </b></font></td>
    				<td>
						<select id="sel_local_currency_reduction2" name="sel_local_currency_reduction2" onchange="
							ClearCurrency('sel_local_currency_reduction2', 'txt_local_rate_reduction2', 'txt_usd_reduction2');
        					ConvertCurrency('sel_local_currency_reduction2', 'txt_local_rate_reduction2', 'txt_usd_reduction2');
        				" disabled >
						<option value=""></option>
						<?
							//Get Currency
							foreach ($arrCurrency as $data) {
								$split_arr = explode('~', $data);
								echo '<option value="'.$split_arr[0].'~'.$split_arr[1].'~'.$split_arr[2].'">'.$split_arr[2].'</option>';
							}
						?>
						</select>
    				</td>
    				<td><font color="red"><b>Rate: </b></font></td>
    				<td>
        				<input id="txt_usd_reduction2" name="txt_usd_reduction2" type="text" value="" size="12" onblur="
							if(!isNumber(this.value)) {this.value = ''};
						" readonly />
        				US$&nbsp;
    				</td>
    			</tr>
    			<tr>
    				<td><font color="red"><b>Remark :</b></font></td>
    				<td colspan="5">
						<textarea style="width:100%;" id="txt_reduction_remark2" name="txt_reduction_remark2"></textarea>
    				</td>
    			</tr>
    		 	<tr>
		      		<td>&nbsp;</td>
		    		 <td colspan="5">
						<span style="color:red">*Please fill in total amount. </span>
		    		</td>
    			</tr>
    			<!-- End Reduction -->
    		</table>
    		</div>
    	</td>
    </tr>
  	<!-- End Currency -->

      <tr>
        <td>&nbsp;</td>
        <td><input type="button" name="buttonAction2" id="buttonAction2" value="S A V E" onClick="editBookingOtherSave();">
        <input type="button" name="buttonCancel2" id="buttonCancel2" onClick="$('#dialog2').dialog('close');" value="C L O S E"></td>
      </tr>
    </table>
  </form>
</div>

<div id="dialogWater">
  <form name="formWater" id="formWater" method="post" action="">
    <table border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td><strong>Country</strong></td>
        <td><select name="selCountryWater" id="selCountryWater">
        <option value="0" selected="selected">--select--</option>
        <?	
        while($Country = $Countrys3->fetch())
		{
		?>
        <option value="<?=$Country->CountryId?>"><?=$Country->CountryDesc?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>Water</strong></td>
        <td><select name="selWater" id="selWater">
        </select></td>
      </tr>
      <tr>
        <td><strong>On Day</strong></td>
        <td><input name="txtOnDayWater" class="datepicker" type="text" id="txtOnDayWater" size="15"></td>
      </tr>
      <tr>
        <td><strong>Status</strong></td>
        <td><select name="selStatusWater" id="selStatusWater" onchange="expandTabWater()">
        <option value="">--status--</option>
        <? foreach($arrStat as $value){ ?>
        <option value="<?=$value?>"><?=$value?></option>
        <? } ?>
        </select></td>
      </tr>
      <tr>
        <td><strong>Remarks
          <input type="hidden" name="hidWBId" id="hidWBId">
          <input type="hidden" name="tour_id" id="tour_id" value="<?=$tour_id;?>">         
        </strong></td>
        <td><label for="txtRemarksWater"></label>
        <textarea name="txtRemarksWater" id="txtRemarksWater" cols="45" rows="5"></textarea></td>
      </tr>

      <tr>
        <td>&nbsp;</td>
        <td><input type="button" name="buttonAction3" id="buttonAction3" value="S A V E" onClick="editWaterSave();">
        <input type="button" name="buttonCancel" id="buttonCancel" onClick="$('#dialogWater').dialog('close');" value="C L O S E"></td>
      </tr>
    </table>
  </form>
</div>
<div id="dialogOther"> </div>
<div id="dialogDelete"></div>

<!-- OthercostForm -->
<div id="dialogOtherCost">
	<form name="formOtherCost" id="formOtherCost" method="post" action="">
	<input type="hidden" name="tour_id" id="tour_id" value="<?=$tour_id;?>" />
    <table border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td><strong>On Day</strong></td>
        <td><input class="datepicker" id="txtDateRun" name="txtDateRun" type="text" size="15"></td> <!-- id="txtOnDayOtherCostr" -->
      </tr>
      <tr>
        <td><strong>Country</strong></td>
        <td><select id="selCountryOtherCost" name="selCountryOtherCost"><option value="">-select-</option>
		<?php
			foreach($array_country as $key => $value){
				echo '<option value="'.$key.'">'.$value.'</option>';
			}
		?>
		</select></td>
      </tr>
      <tr>
        <td><strong>City</strong></td>
        <td><select id="selCityOtherCost" name="selCityOtherCost">
		</select></td>
      </tr>
      <tr>
        <td><strong>Company</strong></td>
        <td><select id="selCompanyOtherCost" name="selCompanyOtherCost">
		</select></td>
      </tr>
	  <tr>
        <td><strong>Category</strong></td>
        <td><select id="selCategoryOtherCost" name="selCategoryOtherCost"><option value="">-select-</option>
		<?php
			foreach($array_category as $key => $value){
				echo '<option value="'.$key.'">'.$value.'</option>';
			}
		?>
		</select></td>
      </tr>
	 <tr>
        <td valign="top"><strong>Cost Detail</strong></td>
        <td><textarea id="txtOtherCostDesc" name="txtOtherCostDesc" cols="30" rows="3"></textarea></td>
      </tr>
	  <tr>
        <td><strong>Cost Price</strong></td>
        <td><input type="text" id="txtOtherCostPrice" name="txtOtherCostPrice" value="0" /></td>
      </tr>
      <tr>
        <td><strong>Cost Currency</strong></td>
        <td><select id="selCurrencyOtherCost" name="selCurrencyOtherCost"><option value="">-select-</option>
		<?php
			foreach($array_currency as $key => $value){
				echo '<option value="'.$key.'">'.$value.'</option>';
			}
		?>
		</select></td>
      </tr>
	  <tr>
        <td><strong>Cost Type</strong></td>
        <td><select id="selCostType" name="selCostType"><option value="">-select-</option>
		<?php
			foreach($array_costtype as $key => $value){
				echo '<option value="'.$key.'">'.$value.'</option>';
			}
		?>
		</select></td>
      </tr>
      <tr>
        <td><strong>Pax</strong></td>
        <td><input type="text" id="txtPax" name="txtPax" value="" onblur="
        	if($(this).val().trim() == '' || isNaN($(this).val())) {
        		alert('pax must be number only.');
        		$(this).val('');
        	}
        "/></td>
      </tr>
      <tr>
        <td><strong>Status</strong></td>
        <td><select id="selStatusOtherCost" name="selStatusOtherCost"><option value="">-select-</option>
		<?php
			foreach($array_status as $key => $value){
				echo '<option value="'.$key.'">'.$value.'</option>';
			}
		?>
		</select></td>
      </tr>
      <tr>
        <td valign="top"><strong>Remarks</strong></td>
        <td></label><textarea id="txtRemarksOtherCost" name="txtRemarksOtherCost" cols="45" rows="5"></textarea></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="button" name="buttonAction3" id="buttonAction3" value="S A V E" onClick="addOtherCost();">
        <input type="button" name="buttonCancel" id="buttonCancel" onClick="$('#dialogOtherCost').dialog('close');" value="C L O S E"></td>
      </tr>
    </table>
  </form>
<!-- End OthercostForm -->
</div>
</body>
</html>
