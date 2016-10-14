<?
session_start();

//require_once("core/config.php");
require_once("core/booking/Itinerary.php");
//require_once("core/booking/ICSDB.php");

$Itinerary = new Itinerary();

##############################################################

$quotationId		=  $_REQUEST['desc_val'] ;
$change			= $_REQUEST['change'] ;
$dateFrom 		=  $_REQUEST['Date_from'] ; // COL2
$dateTo 			=  $_REQUEST['Date_to'] ; // COL3
$nr_val 			=  $_REQUEST['nr_val'] ; // COL4 
$nr  				=  $_REQUEST['Nr'] ;
$country 			=  $_REQUEST['country'] ; //COLX
$service 			=  $_REQUEST['service'] ; // COL5
$days 				=  ($_REQUEST['Days'])?$_REQUEST['Days']:0; // COLXX
$cat 				= $_REQUEST['cat'] ; // COL7
$hidManualChng 	=  $_REQUEST['hidManualChng'] ;// COL9
$description 		=  $_REQUEST['description'] ; // COLXXX
$sgl 				=  ($_REQUEST['sgl']!='')?$_REQUEST['sgl']:0 ; // COL11
$dbl 				=  ($_REQUEST['dbl']!='')?$_REQUEST['dbl']:0 ; // COL12
$twn 				=  ($_REQUEST['twn']!='')?$_REQUEST['twn']:0 ;
$tpl 				=  ($_REQUEST['tpl']!='')?$_REQUEST['tpl']:0 ; // COL13
$qty 				=  $_REQUEST['qty'] ; // COL 10
$sel_unit		=  $_REQUEST['sel_unit'] ; // COL15
$pax				=  $_REQUEST['pax'] ; // COL13
$tourId			= $_REQUEST['hid_TourID'] ;
$sglsupp			= $_REQUEST['hid_sglsupp'] ;
$tpldiscnt		= $_REQUEST['hid_tpldiscnt'] ;
$pp				= $_REQUEST['hid_pp'] ; 
$ppCost 			= $_REQUEST['hid_ppCost'];
$multi 			= (int)$_REQUEST['multi'] ; 
$special			= $_REQUEST['special'];
$op_amend			= $_REQUEST['op_amend'];
#########################################################################################

//echo "12345";

if($quotationId=='')
{
	echo "Quotation is empty.";
	echo "Please check data again!" ;
 	exit();
}

for($m = 1 ; $m <= $multi ; $m++) 
{
	
	//var_dump($m,$multi);
	if(trim($params['Special'])!='')
	{
		$data 		= explode("," , $this->params['special'] );
		$rates_id 	= $data[0] ;
		$tour_id 	= $data[1] ;
		$from 		= $data[2] ;	
		$to 		= $data[3] ;
		unset( $params ) ;
		$params[] =  $rates_id ;
		$params[] =  $tour_id ;
		$params[] =  $from ;
		$params[] =  $to ;   
		$Itinerary->deleteConfirmation($params);
	}

	//exit();
	######################### Find column  ##########################
	list($columnNo,$columnDesc) = $Itinerary->findColumnNo($quotationId,$pax);
	
	//var_dump($pax,$columnNo,$columnDesc,$Itinerary->detectPaxRange($quotationId,$columnDesc,$pax));
	//exit();

	if($Itinerary->detectPaxRange($quotationId,$columnDesc,$pax) == false)
	{
		echo "???";
		echo "Please check data again!" ;
		exit();
	}
	
	///////////////////////// Track Confirmation /////////////////////////////
	$confirmationId = $Itinerary->trackConfirmation($tourId ,$quotationId,$change,$dateFrom,$dateTo,$pax,$pp,$ppCost,$qty,$sel_unit,$cat,$service,$_SESSION['ss_fullname'],$sglsupp,$sgl,$tpldiscnt,$tpl);
	
	
	//////////////////// Track Confirmation Markup ///////////////////////////////
	$Itinerary->trackConfirmationMarkup($confirmationId,$quotationId);
	

	if($op_amend != "a"){
		//////////////////// Track Hotel ///////////////////////////////
		list($outputHBId,$outputHotels) = $Itinerary->trackHotel($confirmationId, $tourId, $quotationId, $dateFrom, $dateTo, $_SESSION['ss_fullname'], $pax, $sgl, $dbl, $twn, $tpl, $cat, $change, $qty);
	}
	
	//////////////////// Track restaurant ///////////////////////////////	
	$Itinerary->trackRestaurant($confirmationId,$quotationId,$tourId,$dateFrom,$dateTo,$pax,$_SESSION['ss_fullname'],$change);
	
	//////////////////// Track Guide ///////////////////////////////
	$Itinerary->trackGuide($confirmationId,$quotationId,$tourId,$_SESSION['ss_fullname'],$dateFrom,$dateTo,$pax);
	
	//////////////////// Track Misc ///////////////////////////////
	// $Itinerary->trackMisc($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname']);
	$Itinerary->trackMiscNew($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'], $pax);
	
	//////////////////// Track water ///////////////////////////////
	$Itinerary->trackWater($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname']);
	
	if(strstr($columnDesc,"+")){
		//echo "direct";
		$Itinerary->trackEntranceFeePlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		if($op_amend != "a"){
			//////////// Track Flight Boat Train Balloon ///////////////////////////////
			$Itinerary->trackFlightPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		}else{
			$Itinerary->trackNotFlightPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		}	
		// /*
		// Track Vehicle and Other(Activity,Package,Helicopter,Miscellaneous)	 */
		
		$Itinerary->trackVehicleAndOtherPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);	
		///////////////// Track combine	
		$Itinerary->trackCombine($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);		
	}else{
		//echo "quote";
		//////////////////// Track EntranceFee ///////////////////////////////
		$Itinerary->trackEntranceFee($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		if($op_amend != "a"){
			//////////////////// Track Flight Boat Train Balloon ///////////////////////////////
			$Itinerary->trackFlight($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		}else{
			$Itinerary->trackNotFlight($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		}
		/*
		Track Vehicle and Other(Activity,Package,Helicopter,Miscellaneous)	 */
		$Itinerary->trackVehicleAndOther($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
		/////////////////// Track combine	
		$Itinerary->trackCombine($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
	}	

	if(count($output) > 3){
		
	}
	
	$output .="$confirmationId+";
	
	//echo "Save complete";
}

	$outputHotels = str_replace('<br/>',"\n",$outputHotels);

	if( $outputHotels == "" )
			echo "$output|operation has been completed\n$dummy";
	else
			echo  $outputHotels ;

?>