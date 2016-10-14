<?php ob_start();

	session_start();

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
	$Itineraryl 				=  ($_REQUEST['dbl']!='')?$_REQUEST['dbl']:0 ; // COL12
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
	$chk_guide = $_REQUEST['chk_guide'];
	$chk_boat=$_REQUEST['chk_boat'];
	$chk_restaurant=$_REQUEST['chk_restaurant'];
	$chk_vehicle=$_REQUEST['chk_vehicle'];
	$chk_visa=$_REQUEST['chk_visa'];
	$chk_misc=$_REQUEST['chk_misc'];
	$chk_entrancefee=$_REQUEST['chk_entrancefee'];
	$chk_water=$_REQUEST['chk_water'];
	$chk_hotel=$_REQUEST['chk_hotel'];
	$chk_flight=$_REQUEST['chk_flight'];
	#########################################################################################

	if($op_amend == "a"){

		#echo $chk_guide;
		#exit;

		try{

			if($quotationId==''){
				echo "Please check data again!" ;
				exit();
			}

			for($m = 1 ; $m <= $multi ; $m++) {
				if(trim($params['Special'])!=''){
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

				######################### Find column  ##########################
				list($columnNo,$columnDesc) = $Itinerary->findColumnNo($quotationId,$pax);

				if($Itinerary->detectPaxRange($quotationId,$columnDesc,$pax) == false){
					echo "Please check pax data again $quotationId, $columnNo, $pax, $columnDesc !" ;
					exit();
				}
				
				///////////////////////// Track Confirmation /////////////////////////////
				$confirmationId = $Itinerary->trackConfirmation($tourId ,$quotationId,$change,$dateFrom,$dateTo,$pax,$pp,$ppCost,$qty,$sel_unit,$cat,$service,$_SESSION['ss_fullname'],$sglsupp,$sgl,$tpldiscnt,$tpl);
				
				//////////////////// Track Confirmation Markup ///////////////////////////////
				$Itinerary->trackConfirmationMarkup($confirmationId,$quotationId);
				
				//////////////////// Track Hotel ///////////////////////////////
				if($chk_hotel == 1){
					list($outputHBId,$outputHotels) = $Itinerary->trackHotel($confirmationId, $tourId, $quotationId, $dateFrom, $dateTo, $_SESSION['ss_fullname'], $pax, $sgl, $Itineraryl, $twn, $tpl, $cat, $change, $qty);
				}
				
				//////////////////// Track restaurant ///////////////////////////////	
				if($chk_restaurant == 1){
					$Itinerary->trackRestaurant($confirmationId,$quotationId,$tourId,$dateFrom,$dateTo,$pax,$_SESSION['ss_fullname'],$change);
				}

				//////////////////// Track Guide ///////////////////////////////
				if($chk_guide == 1){
					$Itinerary->trackGuide($confirmationId,$quotationId,$tourId,$_SESSION['ss_fullname'],$dateFrom,$dateTo,$pax);
				}

				//////////////////// Track Misc ///////////////////////////////
				if($chk_misc == 1){
					// $Itinerary->trackMisc($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname']);
					$Itinerary->trackMiscNew($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'], $pax);
					$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Other");
				}

				//////////////////// Track water ///////////////////////////////
				if($chk_water == 1){
					$Itinerary->trackWater($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname']);
				}

				if(strstr($columnDesc,"+") OR $pax < $columnDesc){
					if($chk_entrancefee == 1){
						$Itinerary->trackEntranceFeePlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Entrance Fee");
					}
					//////////// Track Flight Boat Train Balloon ///////////////////////////////
					if($chk_flight == 1){
						$Itinerary->trackFlightPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Flight");
					}
					if($chk_boat == 1){
						$Itinerary->trackNotFlightPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"NoFlight");
					}

					// Track Vehicle and Other(Activity,Package,Helicopter,Miscellaneous)	 */
					if($chk_vehicle == 1){
						//var_dump($chk_vehicle);
						$Itinerary->trackVehicleAndOtherPlus($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);	
						///////////////// Track combine	
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Vehicle");		
					}
					//echo "hello";
				}else{
					//var_dump("Hi");
					//////////////////// Track EntranceFee ///////////////////////////////
					if($chk_entrancefee == 1){
						$Itinerary->trackEntranceFee($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Entrance Fee");
					}

					//////////////////// Track Flight Boat Train Balloon ///////////////////////////////
					if($chk_flight == 1){
						$Itinerary->trackFlight($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Flight");
					}
					
					if($chk_boat == 1){
						$Itinerary->trackNotFlight($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"NoFlight");
					}

					/*
					Track Vehicle and Other(Activity,Package,Helicopter,Miscellaneous)	 */
					//var_dump($chk_vehicle);
					if($chk_vehicle == 1){
						$Itinerary->trackVehicleAndOther($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo);
						/////////////////// Track combine	
						$Itinerary->trackCombineByServiceType($confirmationId,$tourId,$quotationId,$dateFrom,$dateTo,$_SESSION['ss_fullname'],$pax,$columnNo,"Vehicle");
					}
					//echo "hello";
				}	
				
				#$output .="$confirmationId+";			
				//echo "hello";
				echo "Save complete";
			}
		}catch(Exception $e){
			echo "Error=>".$e->getMessage();
		}

	}else if($op_amend == "d"){

		if(!empty($chk)){
			$array_of_booking = explode(",", $chk);
			foreach($array_of_booking as $booking_item){
				$booking_item = str_replace("'", "", $booking_item);
				delete_selected_service($booking_item);
			}
		}
	}

function delete_selected_service($confirmation_id){
	global  $chk_guide, $chk_boat, $chk_restaurant, $chk_vehicle, $chk_visa, $chk_misc, $chk_entrancefee, $chk_water, $chk_vehicle, $Itinerary, $tourId, $chk_flight, $chk_hotel;

	$db = $Itinerary->getConnection();

	// var_dump($confirmation_id);
	//exit;

	try{

			if($chk_guide == 1){
				deleteGuide($confirmation_id);
			}

			if($chk_vehicle == 1){
				deleteVehicle($confirmation_id);	
			}
			
			if($chk_misc == 1){
				//deleteMisc($confirmation_id);
				deleteMiscOther($confirmation_id); // Miscellaneous 
				deleteOther($confirmation_id); // OtherCostBooking 
			}

			if($chk_water == 1){
				deleteWater($confirmation_id);	
			}

			if($chk_entrancefee == 1){
				deleteFee($confirmation_id);
			}

			if($chk_restaurant == 1){
				deleteRestaurant($confirmation_id);	
			}

			if($chk_boat == 1){
				deleteNoFlight($confirmation_id);
			}

			if($chk_hotel == 1){
				deleteHotel($confirmation_id);
			}

			if($chk_flight == 1){
				deleteFlight($confirmation_id);
			}
			$found_invoice = 0;
			$sql = "SELECT COUNT(*) AS cnt
										FROM INVOICEDETAILS  
										WHERE ConfirmationsId = '$confirmation_id' and TourId='$tourId' " ;

			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$found_invoice = $row->cnt;
				}
			}
			#
			if($found_invoice > 0){
				$MSG= "CAN NOT DELETE THE ITEMS BECAUSE  INVOICE WAS PRINTED. \n";	
			}else{
				$sql =  "DELETE FROM [tbConfirmations]  WHERE ConfirmationsId = '$confirmation_id' ";		
				$sql.= "DELETE FROM  [tbConfirmationsMarkup]  WHERE ConfirmationsId = '$confirmation_id' ";
				$sql.= "DELETE  FROM dbo.tbFlightBookings
							WHERE   ConfirmationsId IN ( '$confirmation_id' )
									AND nf = 1
									AND ( Status = ''
										  OR Status IS NULL
										) ";
				$sql.="UPDATE  DBLOG.dbo.AuditConfirmations
						SET
							DBLOG.dbo.AuditConfirmations.dby = '{$_SESSION['FullName']}'
						WHERE  DBLOG.dbo.AuditConfirmations.Tourid = '$tourId' AND DBLOG.dbo.AuditConfirmations.AuditType = 'Del' AND DBLOG.dbo.AuditConfirmations.dby IS NULL";
				$MSG = "Delete Complete \n";
				$db->query($sql);
			}
		}catch(Exception $e){
				$MSG = $e->getMessage();
		}
		
		echo $MSG;
	}
	## Vehicle Booking
	function deleteVehicle($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(VBId AS VARCHAR(36)) AS VBId, TourId
						FROM    dbo.VehicleBooking
						WHERE   ConfirmationsId = '$confirmation_id' 
							AND ISNULL(Status,'') IN('','PD') ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->VBId;
					$tour_id = $row->TourId;
					//echo $id;
					$sql= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' 
																		AND TourId = '$tour_id') ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' 
							 AND TourId = '$tour_id' ";
					$sql.="DELETE FROM dbo.VehicleBooking 
							  WHERE VBId = '$id' AND TourId = '$tour_id' ";
					//echo $sql."<hr/>";
					//exit;
					$db->query($sql);
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}
	## Misc Booking
	function deleteMisc($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT   CAST(id AS VARCHAR(36)) AS id
						FROM    dbo.MisceBooking
						WHERE   ConfirmationsId = '$confirmation_id'  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->id;
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.MisceBooking 
							  WHERE id = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}
	##  Misc Other Booking
	function deleteMiscOther($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(MOBId AS VARCHAR(36)) AS MOBId
						FROM    dbo.MisceOtherBooking
						WHERE   ConfirmationsId = '$confirmation_id' 
							AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->MOBId;
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.MisceOtherBooking 
							  WHERE MOBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	function deleteOther($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(dbo.OtherCostBooking.OtherCostBookingId AS VARCHAR(36)) AS OBId
						FROM    dbo.OtherCostBooking
						WHERE   ConfirmationsId = '$confirmation_id' 
							AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->OBId;
					$sql.= "DELETE FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";

					$sql.="DELETE FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";

					$sql.="DELETE FROM dbo.OtherCostBooking 
							  WHERE id = '$id' ";
					// echo $sql."<hr/>"; exit();
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}
	##  Water Booking
	function deleteWater($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(WBId AS VARCHAR(36)) AS WBId
						FROM    dbo.WaterBooking
						WHERE   ConfirmationsId = '$confirmation_id' AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->WBId;
					$sql = "DELETE  FROM dbo.BookingRateCost
								WHERE   ReferanceId = '$id' AND confirmationsId = '$confirmation_id' ";
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.WaterBooking 
							  WHERE WBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	##  Entrance Fee Booking
	function deleteFee($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(EBId AS VARCHAR(36)) AS EBId
						FROM    dbo.EntranceFeeBooking
						WHERE   ConfirmationsId = '$confirmation_id' AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->EBId;
					$sql = "DELETE  FROM dbo.BookingRateCost
								WHERE   ReferanceId = '$id' AND confirmationsId = '$confirmation_id' ";
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.EntranceFeeBooking 
							  WHERE EBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	##  Guide Booking
	function deleteGuide($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "DELETE FROM dbo.tbGuideBookings
					   WHERE   ConfirmationsId = '$confirmation_id'
							        AND NewGuideId IS NULL
									AND ISNULL(GuideId,'') = '' ";
			$db->query($sql);
		}catch(Exception $e){
			var_dump(__FUNCTION__,$e->getMessage());
		}
	}

	##  Restaurant Booking
	function deleteRestaurant($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(RBId AS VARCHAR(36)) AS RBId
					   FROM dbo.tbRestaurantBookings
					   WHERE   ConfirmationsId = '$confirmation_id'
						 AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->RBId;
					$sql = "DELETE  FROM dbo.RestaurantBookingCostPrice 
							    WHERE RBId = '$id' ";	
					$sql.="DELETE FROM dbo.tbRestaurantBookings 
							  WHERE RBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	##  Hotel Booking
	function deleteHotel($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(HBId AS VARCHAR(36)) AS HBId
					   FROM dbo.tbHotelBookings
					   WHERE   ConfirmationsId = '$confirmation_id'
							AND ISNULL(Status,'') IN('','PD')  ";
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->HBId;
					$sql = "DELETE  FROM dbo.HotelBookingCostPrice 
							    WHERE HBId = '$id' ";	
					$sql.="DELETE FROM dbo.tbHotelBookings 
							  WHERE HBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	## Flight Booking
	function deleteFlight($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(FBIdUniqueId AS VARCHAR(36)) AS FBId
						FROM    dbo.tbFlightBookings
						WHERE   ConfirmationsId = '$confirmation_id' 
							AND ISNULL(Status,'') IN('','PD') ";
			#echo $sql;
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->FBId;
					#echo $id;
					$sql = "DELETE  FROM dbo.BookingRateCost
								WHERE   ReferanceId = '$id' AND confirmationsId = '$confirmation_id' ";
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.tbFlightBookings 
							  WHERE FBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

	## No Flight Booking
	function deleteNoFlight($confirmation_id){
		global $Itinerary;
		$db = $Itinerary->getConnection();
		try{
			$sql = "SELECT  CAST(FBId AS VARCHAR(36)) AS FBId
						FROM    dbo.tbFlightBookings
						WHERE   ConfirmationsId = '$confirmation_id' 
							AND Status IN('','PD') AND nf = 1 ";
			#echo $sql;
			$rs = $db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$id = $row->FBId;
					#echo $id;
					$sql = "DELETE  FROM dbo.BookingRateCost
								WHERE   ReferanceId = '$id' AND confirmationsId = '$confirmation_id' ";
					$sql.= "DELETE  FROM dbo.BookingRateSpecialChargeCost
								WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
															   FROM     dbo.BookingRateCost
															   WHERE    confirmationsId = '$confirmation_id'
																		AND ReferanceId = '$id' ) ";
					$sql.="DELETE  FROM dbo.BookingRateCost 
					          WHERE confirmationsId = '$confirmation_id' 
							 AND ReferanceId = '$id' ";
					$sql.="DELETE FROM dbo.tbFlightBookings 
							  WHERE FBId = '$id' ";
					#echo $sql."<hr/>";
					$db->query($sql);
					
				}
			}
		}catch(Exception $e){
			var_dump($sql."=".__FUNCTION__,$e->getMessage());
		}
	}

ob_end_flush();?>