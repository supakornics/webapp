<?php
require_once("BookingCoreDB.php");

class Itinerary extends BookingCoreDB {

	private $columnNo;

	public function __construct($db = null)
	{
		parent::__construct();
	}

	public function getConnection(){
		return $this->db;
	}


	public function delete($allId='')
	{
		/*
				DELETE FROM dbo.tbHotelBookings WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.HotelBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.tbRestaurantBookings WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.RestaurantBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.MisceBooking WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.MisceOtherBooking WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.MisceOtherBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.tbFlightBookings WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.FlightBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.VehicleBooking WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.VehicleBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.tbGuideBookings WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.EntranceFeeBooking WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.EntranceFeeBookingCostPrice WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.WaterBooking WHERE ConfirmationsId IN( $allId )
				DELETE  FROM dbo.BookingRateCost WHERE  ConfirmationsId IN( $allId )
				DELETE  FROM dbo.BookingRateSpecialChargeCost WHERE  ConfirmationsId IN( $allId )

		*/

		$sql = "DELETE  FROM  [tbConfirmations]  WHERE ConfirmationsId IN( $allId )
				DELETE  FROM  [tbConfirmationsMarkup]  WHERE ConfirmationsId IN( $allId )
				";
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
		}catch(Exception $e){
			echo $sql;
			print_r($params);
			echo "<hr/>";
			echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
			echo "<hr/>";
			var_dump($e);
			return false;
		}
		return true;
	}

	public function SearchAll( $params=array())
	{

	}

	public function SearchByTourId($tourid="")
	{
		$sql = "SELECT
				Quotation.QuotationName,
				Quotation.QuotationCategory,
				Quotation.ContactsId AS booked_by ,
				tbConfirmations.ConfPricePerPax * tbConfirmations.Quantity AS result,
				tbConfirmations.ConfPricePerPax * tbConfirmations.Quantity * tbConfirmations.Pax * 1.00 AS Total,
				tbContacts.Shortcut AS Comp ,
				tbConfirmations.Positions AS pos ,
				dbo.Date_Format(  tbConfirmations.DateTo  , 'dd-mmm-yyyy' ) as DateTo ,
				dbo.Date_Format(  tbConfirmations.DateFrom  , 'dd-mmm-yyyy' ) as DateFrom  ,
				tbConfirmations.ConfPricePerPax as  Confpricepp ,
				tbConfirmations.Quantity,
				tbConfirmations.Units,
				tbConfirmations.Pax ,
				tbConfirmations.Category,
				tbConfirmations.IsVisible AS show ,
				tbConfirmations.Room ,
				tbConfirmations.ConfirmationsId as ID ,
				tbConfirmations.QuotationId as Ratex ,
				Quotation.QuotationCode AS Codex ,
				Quotation.CountryCode as Ccode  ,
				Quotation.ManualRates as ManualChange ,
				tbConfirmations.INVdate ,
				tbConfirmations.Description ,
				tbConfirmations.BookingClassId
				FROM tbConfirmations
					 LEFT JOIN Quotation ON tbConfirmations.QuotationId = Quotation.QuotationId
					 LEFT JOIN tbContacts ON Quotation.ContactsId = tbContacts.ContactsId
				WHERE (dbo.tbConfirmations.TourId = ? )  AND  tbContacts.isMainContact = 1
				ORDER BY tbConfirmations.DateFrom , tbConfirmations.Positions";

				$params = array();
				$params[] = $tourid;

				$stmt = $this->db->query($sql,$params);

				return $stmt;

	}

	public function deleteConfirmation($params)
	{
		try{
		$sql = " DELETE FROM tbConfirmations WHERE
					QuotationId = ?  AND
					TourId  = ?  AND
					DateFrom  =  CAST( ? AS DATETIME )   AND
					DateTo  = CAST( ? AS DATETIME ) " ;

		$rs = $this->db->query($sql , $params);
		}catch(Exception $e){
			echo $sql;
			print_r($params);
			echo "<hr/>";
			echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
			echo "<hr/>";
			var_dump($e);
			return false;
		}
		return true;
	}

	public function findColumnNo($QuotationId,$Pax)
	{

		try{
			$sql = "
					SELECT Quotation.QuotationCategory
					FROM Quotation
					WHERE  QuotationId = ?
					";
			unset($params);
			$params[] = "$QuotationId" ;

			$rs = $this->db->query($sql , $params);
			while($row = $rs->fetch())
			{
				$QuotationCategory = $row->QuotationCategory;
			}

			//echo '<pre>'.$sql;
			if($QuotationCategory == 'Flight'){
				return array(1,1);
			}

			$sql = "SELECT  QuotationId,
							col ,
							position
					FROM    dbo.QuotationColumn
					WHERE   QuotationId = ?
					ORDER BY Position";

			unset($params);
			$params[] = "$QuotationId" ;

			$rs = $this->db->query($sql , $params);

			//var_dump($rs);

			while($row = $rs->fetch())
			{
				$col = $row->col;
				if(strstr($col,"-"))
				{
					list($a,$b) = split("-",$col);
				}
				elseif(strstr($col,"+"))
				{
					$a = substr($col,0,-1);
					$b = 9999;
				}
				else
				{
					$a = $col;
					$b = $col;
				}
				//echo "$a , $b ,$Pax \n";
				if($Pax >= $a && $Pax<=$b){
					return  array($row->position,$col);
				}
			}

			return  array(1,$col);
			/*
			if($position)
				return $position;
			else
			{
					$sql = "SELECT max(position)
								FROM    dbo.QuotationColumn
						WHERE   QuotationId = ?
						";

				unset($params);
				$params[] = "$QuotationId" ;


				$position = $this->db->fetchOne($sql , $params);

				return $position;
			}

			exit();
			*/
		}catch(Exception $e){
			echo $sql;
			print_r($params);

			echo "<hr/>";
			echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
			echo "<hr/>";
			var_dump($e);
			exit();
			return false;
		}
	}

	public function trackConfirmation($tourId,$quotationId,$change,$dateFrom,$dateTo,$pax,$price,$priceCost,$qty,$unit,$cat,$serviceType,$cby,$sglsupp,$sgl,$tpldiscount,$tpl)
	{
	try{
	// table tbConfirmations
	//echo "Track Confirmation \n";

	$stmt = $this->loadData("tour" ,  $tourId ) ;
	$rs =  $stmt->fetch();
	$services =  $rs -> Services ;
	unset( $rs ) ;
//-----------------------------------------------------------------------//
	$stmt  = $this->loadData("Quotation1" , $tourId) ;
	$rs =  $stmt->fetch();
	if($rs)
	{
		$GuideLang		= $rs-> GuideLang ;
		$IntAptTax 		= $rs-> IntAptTax ;
		$DomAptTax 		= $rs-> DomAptTax ;
		$FOCgrant		= $rs-> FOCGrant ;
		$FOCWhere		= $rs-> FOCWhere ;
		$srvtype		= $rs-> QuotationCategory ;
	}

	$stmt  = $this->loadData("Quotation11" , $quotationId ) ;
	$rs11 = $stmt->fetch();
	if($rs11)
	{
		$GuideLang			= $rs11 -> GuideLang ;
		$IntAptTax 			= $rs11 -> IntAptTax ;
		$DomAptTax 			= $rs11 -> DomAptTax ;
		$FOCgrant			= $rs11 -> FOCGrant ;
		$FOCWhere			= $rs11 -> FOCWhere ;
		$srvtype			= $rs11 -> QuotationCategory ;
	}

	$TBservices = $services ;

	//echo $GuideLang;
	if( strlen(trim($GuideLang)) > 0 && ($GuideLang != " " || $GuideLang !=""))
	{
		$TBservices .=   " * " ;
		$TBservices .= ucfirst(strtolower($GuideLang)) . " speaking guide " ;
	}

	if( $DomAptTax == 1 )
	{
		$TBservices = $TBservices . " * " ;
		$TBservices = $TBservices . "Domestic Apt Tax" ;
	}

	if( $IntAptTax == 1 )
	{
		$TBservices = $TBservices . " * " ;
		$TBservices = $TBservices . "Inter'l Apt Tax" ;
	}

	$sql = " UPDATE  tbTours  SET [Services] = ?  WHERE TourId = ? " ;
	unset($params) ;
	$params[] =  $TBservices;
	$params[] =  $tourId;

	$stmt =$this->db->prepare($sql);
	$stmt->execute($params);


	unset($rs) ;
	$TBservices = "" ;

	if($change == "yes" )
	{
		$sql = "DELETE FROM tbConfirmationsSubTable WHERE
				QuotationId = ?  AND
				TourId = ? AND
				( DateFrom BETWEEN CAST( ? AS DATETIME ) AND
				CAST( ? AS DATETIME ) )" ;
		unset($params) ;
		$params[] =  "$quotationId";
		$params[] =  $tourId;
		$params[] =  $dateFrom;
		$params[] =  $dateTo;
		$rs =  $this->db->query($sql , $params);

		$sql = "DELETE FROM tbConfirmations WHERE
				QuotationId = ?  AND
				TourId = ? AND
				DateFrom = CAST( ? AS DATETIME ) AND
				DateTo = CAST( ? AS DATETIME ) AND
				( Room = 'sgl' OR Room = 'Tpl' )	" ;
		unset($params) ;
		$params[] =  "$quotationId";
		$params[] =  $tourId;
		$params[] =  $dateFrom;
		$params[] =  $dateTo;
		$rs =  $this->db->query($sql , $params);
	}

	$pax = ( $pax  > 0 )?$pax:0;


	$confirmationsId =  $this->GetAutoKey("tbConfirmations", "ConfirmationsId" ) ;

	unset( $params ) ;
	$params[]  = $confirmationsId;
	$params[]  = $tourId ;
	$params[]  = "$quotationId" ;
	$params[]  = $dateTo ;
	$params[]  = $dateFrom ;
	$params[]  = $price ;
	$params[]  = $priceCost ;
	$params[]  = $qty ;
	$params[]  = $unit ;
	$params[]  = $pax ;
	$params[]  = $cat ;
	$params[]  = $cby ;
	$params[]  = $serviceType ;

	$rs =  $this->insertConfirmationNormal($params);


	if( $qty > 1 )
	{
		for( $intC = 1 ; $intC <= $qty - 1 ; $intC++)
		{
			$day = $this->DateAdd( "d" , $intC , $Date_from ) ;
			$ConfirmationsSubId =  $this->GetAutoKey("tbConfirmationsSubTable", "ConfirmationsSubId" ) ;
			$sql = "INSERT INTO [tbConfirmationsSubTable] (  ConfirmationsSubId , TourId , QuotationId , DateFrom , cby  )
					VALUES ( ? , ? , ?,  CAST( ? AS DATETIME ) , ? ) ; " ;
			unset($params ) ;
			$params[] = $ConfirmationsSubId;
			$params[] = $tourId;
			$params[] = "$quotationId";
			$params[] = $day;
			$params[] = $cby;
			$rs =   $this->db->query($sql , $params);
		}// End for
	} // End if $qty > 1



		$sglsuppamount = $sglsupp ;
		if(is_null( $sglsuppamount ) || strlen($sglsuppamount) == 0 )
			$sglsuppamount = 0 ;

		$sglno = $sgl ;

		if( $sglno > 0 && $pax > 1 )
		{
			$rmx = "SGL" ;

			$confirmationsId2 =  $this->GetAutoKey("tbConfirmations", "ConfirmationsId" ) ;
			unset( $params ) ;
			$params[]  = $confirmationsId2;
			$params[]  = $tourId ;
			$params[] = "$quotationId" ;
			$params[] = $dateTo ;
			$params[] = $dateFrom ;
			$params[] = $sglsuppamount ;
			$params[] = $qty ;
			$params[] = $unit ;
			$params[] = $sglno ;
			$params[] = $cat ;
			$params[] = $cby;
			$params[] = $serviceType ;
			$this->insertConfirmationSGL($params);
		}

		$tplno = $tpl ;
		if( $tplno > 0  )
		{
			$rmx = "TPL" ;
			$confirmationsId3 =  $this->GetAutoKey("tbConfirmations", "ConfirmationsId" ) ;
			unset( $params ) ;
			$params[]  = $confirmationsId3;
			$params[]  = $tourId ;
			$params[] = "$quotationId" ;
			$params[] = $dateTo ;
			$params[] = $dateFrom ;
			$params[] = $tpldiscount ;
			$params[] = $qty ;
			$params[] = $unit ;
			$params[] = $tplno ;
			$params[] = $cat ;
			$params[] = $cby;
			$params[] = $serviceType;

			$this->insertConfirmationTPL($params);
		}

		if( $FOCgrant != '' && $FOCgrant <= $pax )
		{
			if( $FOCWhere == 1 )
			{
				$x	= $price  +  $sglsupp ;
				$y = "SGL" ;
			}
			else
			{
				$x	= $price ;
				$y = "DBL" ;
			}
			$typeF= 'FOC' ;
			$Confpricepp = (-1* $x ) ;
			$rmx = $y;

			$confirmationsId4 =  $this->GetAutoKey("tbConfirmations", "ConfirmationsId" ) ;
			unset( $params ) ;
			$params[]  = $confirmationsId4;
			$params[]  = $tourId ;
			$params[]  = "$quotationId" ;
			$params[]  = $dateTo ;
			$params[]  = $dateFrom ;
			$params[]  = $Confpricepp ;
			$params[]  = $qty ;
			$params[]  = $typeF ;
			$params[]  = 1 ;
			$params[]  = $cat ;
			$params[]  = $y ;
			$params[]  = $cby;
			$params[]  = $serviceType ;
			$this->insertConfirmationFOC($params);

		}
	return $confirmationsId;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function insertBookingChanges($params)
{
	try{
		$sql = "INSERT INTO [tbChanges] ( ChangesId , [TourId],[ChangesDate],[Changes] , cby )
						VALUES ( ? , ? ,  CAST( ? AS DATETIME ) , ? , ? )";
		$rs =   $this->db->query($sql , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function updateBookingChanges($params)
{
	try{
		$sql = "UPDATE [tbChanges]
				SET [ChangesDate] = CAST( ? AS DATETIME ) , [Changes] = ? , uby = ? , udate =  GETDATE()
				WHERE [TourId]= ? ";
		$rs =   $this->db->query($sql , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

private function insertConfirmationNormal($params)
{
	try{
		$sql = "INSERT INTO  tbConfirmations
		( ConfirmationsId , TourId, QuotationId, DateTo, DateFrom, ConfPricePerPax,
				ConfPricePerPaxCost, Quantity, Units, Pax,   Category,  Positions, IsVisible , cby  , ServicesType  )
		VALUES(	? , ? , ? ,  CAST( ? AS DATETIME ),  CAST( ? AS DATETIME ), ? , ?  , ?  , ? ,  ?  , ?,  1 , 1  , ?  , ? ); " ;

		$rs =   $this->db->query($sql , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

private function insertConfirmationSGL($params)
{

	try{
	$sql=" INSERT INTO [tbConfirmations]
	(  ConfirmationsId , TourId, QuotationId,  DateTo , DateFrom, ConfPricePerPax,  Quantity, Units, Pax ,  Category , Room, Positions,  IsVisible , cby  , ServicesType )
	VALUES (	 ? , ? , ? , CAST( ? AS DATETIME ) , CAST( ? AS DATETIME ) , ? ,  ? , ? , ? ,  ? , 'SGL' , 2 , 0    , ?  , ? ) 	" ;
	$rs =   $this->db->query($sql , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

private function insertConfirmationTPL($params)
{
	try{
		$sql=" INSERT INTO [tbConfirmations] (  ConfirmationsId , TourId, QuotationId,  DateTo , DateFrom, ConfPricePerPax,  Quantity, Units, Pax ,  Category , Room, Positions,  IsVisible , cby  ,  ServicesType ) VALUES ( ? , ? , ? , CAST( ? AS DATETIME ) , CAST( ? AS DATETIME ) , ? ,  ? , ? , ? ,  ? , 'TPL' , 3 , 0 , ?   , ?  ) 	" ;
		$rs = $this->db->query($sql , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

private function insertConfirmationFOC($params)
{
	try{
		$sql=" INSERT INTO [tbConfirmations] (
						ConfirmationsId 	, TourId		, QuotationId,
						DateTo 				, DateFrom	, ConfPricePerPax,
						Quantity			, Units		, Pax ,
						Category 			, Room		, Positions,
						IsVisible , cby , ServicesType )
						VALUES (
					  ? , ? , ? ,
					  CAST( ? AS DATETIME ) , CAST( ? AS DATETIME ) , ? ,
					  ? , ? , ? ,
					   ? , ? , 4 ,
					   0  , ? , ? ) 	" ;
		$rs = $this->db->query( $sql  , $params);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackConfirmationMarkup($confirmationsId,$quotationId)
{
	try{
	$sql = "INSERT INTO dbo.tbConfirmationsMarkup
        (
          ConfirmationsId ,
          PercentB ,
          PercentC ,
          PercentI ,
          PercentL ,
          PercentM ,
          PercentT ,
          PercentV ,
          PercentSTDV ,
          PercentSTDM ,
          PercentSTDL ,
          PercentSTDC ,
          PercentSTDI ,
          PercentSTDB ,
          PercentSTDT ,
          PercentSUPV ,
          PercentSUPM ,
          PercentSUPL ,
          PercentSUPC ,
          PercentSUPI ,
          PercentSUPB ,
          PercentSUPT ,
          PercentDLXV ,
          PercentDLXM ,
          PercentDLXL ,
          PercentDLXC ,
          PercentDLXI ,
          PercentDLXB ,
          PercentDLXT
        )
		SELECT
		  '$confirmationsId',
		  PercentB ,
          PercentC ,
          PercentI ,
          PercentL ,
          PercentM ,
          PercentT ,
          PercentV ,
          PercentSTDV ,
          PercentSTDM ,
          PercentSTDL ,
          PercentSTDC ,
          PercentSTDI ,
          PercentSTDB ,
          PercentSTDT ,
          PercentSUPV ,
          PercentSUPM ,
          PercentSUPL ,
          PercentSUPC ,
          PercentSUPI ,
          PercentSUPB ,
          PercentSUPT ,
          PercentDLXV ,
          PercentDLXM ,
          PercentDLXL ,
          PercentDLXC ,
          PercentDLXI ,
          PercentDLXB ,
          PercentDLXT FROM dbo.Quotation
		  WHERE QuotationId=? ";
		  unset($params);
		  $params[] = "$quotationId";

		  $rs = $this->db->query( $sql  , $params);
		}catch(Exception $e){
			echo $sql;
			print_r($params);
			echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
			var_dump($e);
			return false;
		}
}

public function trackHotel($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$sgl,$dbl,$twn,$tpl,$cat,$change,$qty=1)
{
	try{
		//echo "Track Hotel \n";
		$sql = "SELECT count(*) as ct FROM QuotationData WHERE [QuotationId] = ? " ;
		unset( $params);
		$params[] = "$quotationId" ;

		//echo " 1. $sql <hr/>";
		//print_r($params);

		$rscount = $this->db->fetchOne($sql , $params);
		$percentage = 0;
		$hotel_selling_price = array();
		$brochure_yes = 0;
		if( $rscount  > 0 ) {
			$xcat = strtolower( $cat ) ;
			switch ($xcat) {
				case  "sup" :
					$sql = "SELECT CAST (Q.QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
										   Q.DayNo AS Day,
										   Q.RoomId2 AS hotelprice_id,
										   HRC.HotelId AS hid,
										   CASE
											 WHEN HRC.RoomCategory IS NULL THEN ''
											 ELSE HRC.RoomCategory
										   END AS rc,
										   dbo.tbCity.City,
										   RoomId,
										   InclABF2 AS InclABF,
										   dbo.tbHotels.Country ,
										   PercentSUPV AS PercentageV,
										   PercentSUPM AS PercentageM,
										   PercentSUPC AS PercentageC,
										   PercentSUPL  AS PercentageL,
										   PercentSUPT AS PercentageT,
										   PercentSUPB AS PercentageB,
										   PercentSUPI AS percentageI ,
										   Brochure
									FROM QuotationData AS Q
										 INNER JOIN dbo.Quotation AS m ON m.QuotationId = Q.QuotationId
										 INNER JOIN tbCity ON Q.CityId = tbCity.CityId
										 LEFT OUTER JOIN tbHotelRoomCategory AS HRC ON Q.RoomId2 = HRC.RoomId
										 LEFT OUTER JOIN dbo.tbHotels ON dbo.tbHotels.HotelId = HRC.HotelId
									WHERE (HRC.HotelId <> '0') AND
										  (NOT (HRC.HotelId IS NULL)) AND
										  (Q.[QuotationId] = ?)
									ORDER BY DAY " ;
				break;
				case  "dlx" :
					$sql = "SELECT CAST (Q.QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
									   Q.DayNo AS Day,
									   Q.RoomId3 AS hotelprice_id,
									   HRC.HotelId AS hid,
									   CASE
										 WHEN HRC.RoomCategory IS NULL THEN ''
										 ELSE HRC.RoomCategory
									   END AS rc,
									   dbo.tbCity.City,
									   RoomId,
									   InclABF3 AS InclABF,
									   dbo.tbHotels.Country ,
									   PercentDLXV AS PercentageV,
									   PercentDLXM AS PercentageM,
									   PercentDLXC AS PercentageC,
									   PercentDLXL  AS PercentageL,
									   PercentDLXT AS PercentageT,
									   PercentDLXB AS PercentageB,
									   PercentDLXI AS percentageI ,
									   Brochure
								FROM QuotationData AS Q
									 INNER JOIN dbo.Quotation AS m ON m.QuotationId = Q.QuotationId
									 INNER JOIN tbCity ON Q.CityId = tbCity.CityId
									 LEFT OUTER JOIN tbHotelRoomCategory AS HRC ON Q.RoomId3 = HRC.RoomId
									 LEFT OUTER JOIN dbo.tbHotels ON dbo.tbHotels.HotelId = HRC.HotelId
								WHERE (HRC.HotelId <> '0') AND
									  (NOT (HRC.HotelId IS NULL)) AND
									  (Q.[QuotationId] = ?)
								ORDER BY DAY " ;
				break;
				default :
					$sql = "SELECT CAST (Q.QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
									   Q.DayNo AS Day,
									   Q.RoomId1 AS hotelprice_id,
									   HRC.HotelId AS hid,
									   CASE
										 WHEN HRC.RoomCategory IS NULL THEN ''
										 ELSE HRC.RoomCategory
									   END AS rc,
									   dbo.tbCity.City,
									   RoomId,
									   InclABF1 AS InclABF,
									   dbo.tbHotels.Country,
									   PercentSTDV AS PercentageV,
									   PercentSTDM AS PercentageM,
									   PercentSTDC AS PercentageC,
									   PercentSTDL  AS PercentageL,
									   PercentSTDT AS PercentageT,
									   PercentSTDB AS PercentageB,
									   PercentSTDI AS percentageI ,
									   Brochure
								FROM QuotationData AS Q
									 INNER JOIN dbo.Quotation AS m ON m.QuotationId = Q.QuotationId
									 INNER JOIN tbCity ON Q.CityId = tbCity.CityId
									 LEFT OUTER JOIN tbHotelRoomCategory AS HRC ON Q.RoomId1 = HRC.RoomId
									 LEFT OUTER JOIN dbo.tbHotels ON dbo.tbHotels.HotelId = HRC.HotelId
								WHERE (HRC.HotelId <> '0') AND
									  (NOT (HRC.HotelId IS NULL)) AND
									  (Q.[QuotationId] = ?)
								ORDER BY DAY " ;

				break;
			}// end switch

			unset($params);
			$params[] = "$quotationId";

			//echo " 2. $sql <hr/>";
			//print_r($params);

			$stmt2 = $this->db->query( $sql , $params );

			$tonight = 0 ;
			$rst2 = $stmt2->fetchAll();
			$rnd2 =  count($rst2);
			$percentage = 0;
			if(  $rnd2 > 0 )
			{
				$hpid 		= $rst2[0]->hotelprice_id ; // RoomID
				$HID 		= $rst2[0]->hid ; 			//  HotelID
				$QDataId 	= $rst2[0]->QuotationDataId ;  // QuotationDataId
				$roomId 	= $rst2[0]->RoomId ;
				$inclABF 	= $rst2[0]->InclABF ;
				$hotel_country 	= strtolower($rst2[0]->Country) ;
				switch($hotel_country){
					case "vietnam":
						$percentage = $rst2[0]->PercentageV;
						break;
					case "cambodia":
						$percentage = $rst2[0]->PercentageC;
						break;
					case "laos":
						$percentage = $rst2[0]->PercentageL;
						break;
					case "myanmar":
						$percentage = $rst2[0]->PercentageM;
						break;
					case "thailand":
						$percentage = $rst2[0]->PercentageT;
						break;
					case "indoneisa":
						$percentage = $rst2[0]->PercentageB;
						break;
					case "india":
						$percentage = $rst2[0]->PercentageI;
						break;
					default:
						$percentage = 0;
						break;
				}

				if($percentage > 0){
					$percentage = 1+round(($percentage/100.00), 2);
				}else{
					$percentage = 1;
				}

				if($rs2[0]->Brochure == "1" and $percentage == 1){
					if($xcat == "dlx"){
						$hotel_selling_price = getHotelSellingPrice($quotationId, "C");
					}else if($xcat == "sup"){
						$hotel_selling_price = getHotelSellingPrice($quotationId, "B");
					}else{
						$hotel_selling_price = getHotelSellingPrice($quotationId, "A");
					}
					$brochure_yes = 1;
				}

				if($inclABF == ""){
					$inclABF = 0;
				}

				$sql 		= "SELECT  Hotel FROM tbHotels WHERE [HotelId] = ? " ;
				unset( $params);
				$params[] = $HID ;

				//echo " 3. $sql <hr/>";
				//print_r($params);

				$Hotelname = $this->db->fetchOne($sql,$params);

				if( strstr( $Hotelname ,  "Preferred") ) {
					$hproomid = $hpid ;
				}  else
					$hproomid = ''  ;

				$roomcat   	= $rst2[0]->rc ;
				$countday 	= $rst2[0]->Day - 1 ;
				$countnight = $qty ;
				$tonight 	= $rst2[0]->Day + 1 ;

				//************************************//
				for( $intRst2Count = 1 ;  $intRst2Count <  $rnd2  ; $intRst2Count++ )
				{

					// check duplicate hotel
					if(($rst2[$intRst2Count]->hotelprice_id == $hpid && $rst2[$intRst2Count]->Day == $tonight))
					{
						$countnight++ ;
						$tonight++ ;
					}
					else
					{
						$param1 = $this->DateAdd("d" , $countday , $dateFrom ) ;
						$param2 = $this->DateAdd("d" , $countday + $countnight , $dateFrom );

						$cntx 	= $this->getHotelBlackout( $param1 , $param2 , $roomId, $HID ) ;
						if( $cntx[1] )
						{
							$output[] = $cntx[1] .".";
						}
						$param3 = $roomId;
						$param4 = $HID ;
						//echo " getPeakSurcharge 1  $param1 , $param2 ,$param3 ,$param4  ";
						$getPeak = $this->getPeakSurcharge( $param1 , $param2 ,$param3 ,$param4  ) ;
						$output[] =  $getPeak ;

						if( strlen( $HID ) > 0  )
						{
							 $sql = "SELECT HotelId ,  RoomCategory  FROM tbHotelRoomCategory WHERE RoomId = ? " ;
							 unset($params);
							 $params[] = $hpid ;
							 //echo " 4. $sql <hr/>";
							 //print_r($params);
							 $stmt = $this->db->query($sql , $params);

							if($row = $stmt->fetch())
							{
								$hotel_id = $row->HotelId ;
								$room_cat = $row->RoomCategory ;
							}
							else
							{
								$sql = "SELECT [HotelId] FROM  tbHotels  WHERE [Hotel] = ? ;" ;
								unset($params);
								$params[] = $Hotelname  ;
								//echo " 4.1 $sql <hr/>";
								//print_r($params);
								$hotel_id = $this->db->fetchOne( $sql  , $params);
								$room_cat = "" ;
							}

							$sglroomx 		=( $sgl )?$sgl:0;
							$dblroomx 		=( $dbl )?$dbl:0;
							$twnroomx		=( $twn )?$twn:0;
							$tplroomx 		=( $tpl )?$tpl:0;


							$room_catx = $room_cat ;
							$date 			= date("d-M-Y") ;
							$startdate1 	= $this->DateAdd( "d" , $countday, $dateFrom ) ;
							$startdate2 	= $this->DateAdd( "d" , $countday + $countnight , $dateFrom ) ;

							if( $change == "no" )
							{
								$HBId = $this->GetAutoKey("tbHotelBookings", "HBId");
								if($brochure_yes == 0){
									$sgl_selling_price = $hotel_selling_price[0];
									$dbl_selling_price = $hotel_selling_price[1];
									$tpl_selling_price = $hotel_selling_price[2];

									if($sgl_selling_price == ""){
										$sgl_selling_price = 0;
									}
									if($dbl_selling_price == ""){
										$dbl_selling_price = 0;
									}
									if($tpl_selling_price == ""){
										$tpl_selling_price = 0;
									}


									$sqlI = "INSERT INTO tbHotelBookings( HBId, PHRoomId, TourId,  HotelId,  Sgl, Dbl,Twn, Tpl,  RoomCategory ,BookDate, CheckIn, CheckOut , QuotationDataId  , cby  , RoomId , ConfirmationsId , inclABF)
									VALUES(
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 ? ,
													 CAST( ? AS DATETIME ) ,
													 CAST( ? AS DATETIME ) ,
													 CAST( ? AS DATETIME )  , ?  , ? , ? , ? , $inclABF)

										INSERT INTO dbo.HotelBookingCostPrice( HBId , TourId ,ConfirmationsId
																, SGLPriceSellingCurrency, SGLPriceSellingLocal, SGLPriceSellingUS
																, DBLPriceSellingCurrency, DBLPriceSellingLocal, DBLPriceSellingUS
																, TWNPriceSellingCurrency, TWNPriceSellingLocal, TWNPriceSellingUS
																, TPLPriceSellingCurrency, TPLPriceSellingLocal, TPLPriceSellingUS)
											VALUES(?,?,?
															, 'USD', '$sgl_selling_price', '$sgl_selling_price'
															,'USD', '$dbl_selling_price', '$dbl_selling_price'
															,'USD', '$dbl_selling_price', '$dbl_selling_price'
															,'USD', '$tpl_selling_price', '$tpl_selling_price'
															)

									" ;
								}else{
									$sgl_selling_price = $hotel_selling_price[0];
									$dbl_selling_price = $hotel_selling_price[1];
									$tpl_selling_price = $hotel_selling_price[2];

									if($sgl_selling_price == ""){
										$sgl_selling_price = 0;
									}
									if($dbl_selling_price == ""){
										$dbl_selling_price = 0;
									}
									if($tpl_selling_price == ""){
										$tpl_selling_price = 0;
									}

									$sqlI = "INSERT INTO tbHotelBookings( HBId, PHRoomId, TourId,  HotelId,  Sgl, Dbl,Twn, Tpl,  RoomCategory ,BookDate, CheckIn, CheckOut , QuotationDataId  , cby  , RoomId , ConfirmationsId , inclABF)
										VALUES(
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 ? ,
														 CAST( ? AS DATETIME ) ,
														 CAST( ? AS DATETIME ) ,
														 CAST( ? AS DATETIME )  , ?  , ? , ? , ? , $inclABF)

											INSERT INTO dbo.HotelBookingCostPrice( HBId , TourId ,ConfirmationsId
																, SGLPriceSellingCurrency, SGLPriceSellingLocal, SGLPriceSellingUS
																, DBLPriceSellingCurrency, DBLPriceSellingLocal, DBLPriceSellingUS
																, TWNPriceSellingCurrency, TWNPriceSellingLocal, TWNPriceSellingUS
																, TPLPriceSellingCurrency, TPLPriceSellingLocal, TPLPriceSellingUS)
											VALUES(?,?,?
															, 'USD', '$sgl_selling_price', '$sgl_selling_price'
															,'USD', '$dbl_selling_price', '$dbl_selling_price'
															,'USD', '$dbl_selling_price', '$dbl_selling_price'
															,'USD', '$tpl_selling_price', '$tpl_selling_price'
															)

										" ;
								}

								$params4Hotel[]  =  $hotel_id ;
								$params4HBId[]  =  $HBId ;
								$params4HotelCHK =$params4Hotel[count($params4Hotel)-2] ;
								$params4HBIdCHK =$params4HBId[count($params4HBId)-2] ;

								$tmpHB[] = "  $params4HotelCHK ==  $hotel_id   "	 ;

								$params4roomType[] = $room_catx;
								$params4RoomTypeCHK =$params4roomType[count($params4roomType)-2] ;

								if( $params4HotelCHK ==  $hotel_id  && stristr( $room_catx , 'Additional night') )
								{
									$sqlextDate = "UPDATE tbHotelBookings
												   SET CheckOut = CAST( '$startdate2' AS DATETIME )
												   WHERE HBId = '$params4HBIdCHK' " ;

									$tmpHB[] = "-4. $sqlextDate | $HBId	|	$QDataId	|	$startdate1	|	$startdate2	|	{$_SESSION['FullName']}	|	$tmpDate" ;

								 //echo " 5. $sql <hr/>";
								// print_r($sqlextDate);
									if( $params4HBIdCHK )
										$rs = $this->db->query($sqlextDate) ;
								 }
								 else
								 {
									 unset( $params ) ;
									 $params[]  =  $HBId ;
									 $params[]  =  $hproomid ;
									 $params[]  =  $tourId ;
									 $params[]  =  $hotel_id ;
									 $params[]  =  $sglroomx ;
									 $params[]  =  $dblroomx ;
									 $params[]  =  $twnroomx ;
									 $params[]  =  $tplroomx ;
									 $params[]  =  $room_catx ;
									 $params[]  =  $date ;
									 $params[]  =  $startdate1 ;
									 $params[]  =  $startdate2 ;
									 $params[] =  $QDataId  ;
									 $params[] = $cby ;
									 $params[] = $hpid  ;
									 $params[] = $confirmationsId;
									 $params[] = $HBId ;;
									 $params[] = $tourId ;
									 $params[] = $confirmationsId;
									 //echo " 6. $sqlI <hr/>";
									 //print_r($params);
									 $rs =	$this->db->query( $sqlI , $params);
								}
							}
							else if( $change == "yes" )
							{
								$sql44 = "SELECT [HBId] FROM [tbHotelBookings]
										  WHERE
											[TourId] = ?  and
											[HotelId]= ? and
											[CheckIn]=CAST( ? AS DATETIME ) and
											[Status] NOT LIKE  'cancel%'  " ;
								 unset( $params ) ;
								 $params[]  =  $tourId ;
								 $params[]  =  $HID ;
								 $params[]  =  $startdate1 ;
								  //echo " 8. $sql44 <hr/>";
								 //print_r($params);
								 $HotelBookingID = $this->db->fetchOne($sql44 , $params);

							 }
						}

						//echo"HID : {$rst2[$intRst2Count]->hid}";
						if( $rst2[$intRst2Count]->hid  != '' )
						{
							$tonight 	= $rst2[$intRst2Count]->Day + 1 ;
							$hpid 		= $rst2[$intRst2Count]->hotelprice_id ;
							$QDataId 	= $rst2[$intRst2Count]->QuotationDataId ;  // QuotationDataId
							$HID 		= $rst2[$intRst2Count]->hid ;

							$roomId 	= $rst2[$intRst2Count]->RoomId ;
							$inclABF 	= $rst2[$intRst2Count]->InclABF ;

							if($inclABF == ""){
								$inclABF = 0;
							}

							$sql = "	SELECT [Hotel] FROM  tbHotels WHERE [HotelId] =?  ; " ;
							unset($params);
							$params[] = $HID ;

							//echo "9. $sql <hr/>";
							//print_r($params);

							$Hotelname= $this->db->fetchOne( $sql,$params);

							//echo "9.01 $Hotelname ";

							if(strstr($Hotelname , "Preferred"))
								$hproomid = $hpid ;
							else
								$hproomid = '';

							$roomcat 			= $rst2[$intRst2Count]->rc;
							$countday 			= $rst2[$intRst2Count]->Day - 1;
							$countnight 		= 1 ;
						}

					}

				} // end for
			}

			############ IF
			if(trim($HID) != '' )
			{
				$startdate1 	= $this->DateAdd( "d" , $countday, $dateFrom ) ;
				$startdate2 	= $this->DateAdd( "d" , $countday + $countnight , $dateFrom ) ;

				$getBO = $this->getHotelBlackout( $startdate1 , $startdate2 , $roomId, $HID) ;

				if( $getBO ){
					$output[] = $getBO[1] ;
				}

				$getPS = $this->getPeakSurcharge( $startdate1 , $startdate2 ,$roomId, $HID) ;
				$output[] = $getPS ;

				$sglroomx 	=($sgl)?$sgl:0;
				$dblroomx 	=($dbl)?$dbl:0;
				$twnroomx 	=($twn)?$twn:0;
				$tplroomx 	=($tpl)?$tpl:0;

				//  $room_catx 		=($roomcat)?"'$roomcat'":"' '";
				$room_catx = $roomcat ;
				$date 				= date("d-M-Y") ;
				if( $change == "no" )
				{

				$sgl_selling_price = $hotel_selling_price[0];
				$dbl_selling_price = $hotel_selling_price[1];
				$tpl_selling_price = $hotel_selling_price[2];

				if($sgl_selling_price == ""){
					$sgl_selling_price = 0;
				}
				if($dbl_selling_price == ""){
					$dbl_selling_price = 0;
				}
				if($tpl_selling_price == ""){
					$tpl_selling_price = 0;
				}

					$HBId = $this->GetAutoKey("tbHotelBookings", "HBId");
					$sqlI = "INSERT INTO tbHotelBookings( HBId, PHRoomId, TourId,  HotelId,  Sgl, Dbl,Twn, Tpl,  RoomCategory , BookDate , CheckIn , CheckOut , QuotationDataId , cby , RoomId , ConfirmationsId , inclABF ) VALUES(
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 ? ,
							 CAST( ? AS DATETIME ) ,
							 CAST( ? AS DATETIME ) ,
							 CAST( ? AS DATETIME ) , ? , ? , ? , ?, $inclABF)

							INSERT INTO dbo.HotelBookingCostPrice( HBId , TourId ,ConfirmationsId
													, SGLPriceSellingCurrency, SGLPriceSellingLocal, SGLPriceSellingUS
													, DBLPriceSellingCurrency, DBLPriceSellingLocal, DBLPriceSellingUS
													, TWNPriceSellingCurrency, TWNPriceSellingLocal, TWNPriceSellingUS
													, TPLPriceSellingCurrency, TPLPriceSellingLocal, TPLPriceSellingUS)
								VALUES(?,?,?
												, 'USD', '$sgl_selling_price', '$sgl_selling_price'
												,'USD', '$dbl_selling_price', '$dbl_selling_price'
												,'USD', '$dbl_selling_price', '$dbl_selling_price'
												,'USD', '$tpl_selling_price', '$tpl_selling_price'
												) " ;
					$params4Hotel[]  =  $HID ;
					$params4HBId[]  =  $HBId ;
					$params4HotelCHK =$params4Hotel[count($params4Hotel)-2] ;
					$params4HBIdCHK =$params4HBId[count($params4HBId)-2] ;
					$tmpHB[] = "$params4HotelCHK ==  $HID"				  ;

					$params4roomType[] = $room_catx;
					$params4RoomTypeCHK =$params4roomType[count($params4roomType)-2] ;

					if( $params4HotelCHK ==  $HID  && stristr( $room_catx , 'Additional night') )
					{
						$sqlextDate = "UPDATE tbHotelBookings SET CheckOut = CAST( '$startdate2' AS DATETIME )
										WHERE HBId = '$params4HBIdCHK' " ;
						$tmpHB[] = "-5. $sqlextDate | $HBId	|	$QDataId	|	$startdate1	|	$startdate2	|	{$_SESSION['FullName']}	|	$tmpDate" ;
						 //echo "10. $sqlextDate <hr/>";
						//print_r($params);
					if( $params4HBIdCHK )
						$rs = $this->db->query($sqlextDate) ;
				}
				else
				{
					 unset( $params ) ;
					 $params[]  =  $HBId ;
					 $params[]  =  $hproomid ;
					 $params[]  =  $tourId ;
					 $params[]  =  $HID ;
					 $params[]  =  $sglroomx ;
					 $params[]  =  $dblroomx ;
					 $params[]  =  $twnroomx ;
					 $params[]  =  $tplroomx ;
					 $params[]  =  $room_catx ;
					 $params[]  =  $date ;
					 $params[]  =  $startdate1 ;
					 $params[]  =  $startdate2 ;
					 $params[] =   $QDataId ;
					 $params[] =   $_SESSION['FullName'] ;
					 $params[] =   $hpid  ;
					 $params[] =   $confirmationsId;
					 $params[] =   $HBId;
					 $params[] =   $tourId;
					 $params[] =   $confirmationsId;

					 //echo "11. $sqlI <hr/>";
					// print_r($params);

					 $rs = $this->db->query($sqlI  , $params) ;


				}
			}
			else if($change == "yes")
			{
				$sql44 = "SELECT [HBId] FROM [tbHotelBookings]
						WHERE
								[TourId] = ?  and
								[HotelId]= ? and
								[CheckIn]=CAST( ? AS DATETIME ) and
								[Status] NOT LIKE  'cancel%'  " ;
				 unset( $params ) ;
				 $params[]  =  $tourId ;
				 $params[]  =  $HID ;
				 $params[]  =  $startdate1 ;
				 //echo "13. $sql44 <hr/>";
				 //print_r($params);
				 $HotelBookingID  = $this->db->fetchOne($sql44 , $params);

			}
		}

		################ end if
	}

#############################################################################################



// Track Cost Quote and Booking
$xcat = strtolower( $cat ) ;
switch ($xcat) {
	case  "sup" :
		$sqlCost = "UPDATE  dbo.HotelBookingCostPrice
					SET
							SGLPriceQuotedCurrency = 'USD' ,
							SGLPriceQuotedLocal = SGLPrice2 ,
							SGLPriceQuotedUS = SGLPrice2 ,
							DBLPriceQuotedCurrency = 'USD' ,
							DBLPriceQuotedLocal = DBLPrice2 ,
							DBLPriceQuotedUS = DBLPrice2 ,
							TWNPriceQuotedCurrency = 'USD' ,
							TWNPriceQuotedLocal = DBLPrice2 ,
							TWNPriceQuotedUS = DBLPrice2 ,
							TPLPriceQuotedCurrency = 'USD' ,
							TPLPriceQuotedLocal = TPLPrice2 ,
							TPLPriceQuotedUS = TPLPrice2 ,

							SGLPriceSellingCurrency = 'USD' ,
							SGLPriceSellingLocal = $percentage*SGLPrice2 ,
							SGLPriceSellingUS = $percentage*SGLPrice2 ,
							DBLPriceSellingCurrency = 'USD' ,
							DBLPriceSellingLocal = $percentage*DBLPrice2 ,
							DBLPriceSellingUS = $percentage*DBLPrice2 ,
							TWNPriceSellingCurrency = 'USD' ,
							TWNPriceSellingLocal = $percentage*DBLPrice2 ,
							TWNPriceSellingUS = $percentage*DBLPrice2 ,
							TPLPriceSellingCurrency = 'USD' ,
							TPLPriceSellingLocal =$percentage* TPLPrice2 ,
							TPLPriceSellingUS =$percentage* TPLPrice2
					FROM    dbo.tbHotelBookings
							INNER JOIN dbo.HotelBookingCostPrice ON dbo.tbHotelBookings.HBId = dbo.HotelBookingCostPrice.HBId
							INNER JOIN dbo.QuotationData ON dbo.tbHotelBookings.QuotationDataId = dbo.QuotationData.QuotationDataId
					WHERE   dbo.tbHotelBookings.TourId = ? " ;
	break;
	case  "dlx" :
		$sqlCost = "UPDATE  dbo.HotelBookingCostPrice
					SET     SGLPriceQuotedCurrency = 'USD' ,
							SGLPriceQuotedLocal = SGLPrice3 ,
							SGLPriceQuotedUS = SGLPrice3 ,
							DBLPriceQuotedCurrency = 'USD' ,
							DBLPriceQuotedLocal = DBLPrice3 ,
							DBLPriceQuotedUS = DBLPrice3 ,
							TWNPriceQuotedCurrency = 'USD' ,
							TWNPriceQuotedLocal = DBLPrice3 ,
							TWNPriceQuotedUS = DBLPrice3 ,
							TPLPriceQuotedCurrency = 'USD' ,
							TPLPriceQuotedLocal = TPLPrice3 ,
							TPLPriceQuotedUS = TPLPrice3 ,

							SGLPriceSellingCurrency = 'USD' ,
							SGLPriceSellingLocal = $percentage*SGLPrice3 ,
							SGLPriceSellingUS = $percentage*SGLPrice3 ,
							DBLPriceSellingCurrency = 'USD' ,
							DBLPriceSellingLocal = $percentage*DBLPrice3 ,
							DBLPriceSellingUS = $percentage*DBLPrice3 ,
							TWNPriceSellingCurrency = 'USD' ,
							TWNPriceSellingLocal = $percentage*DBLPrice3 ,
							TWNPriceSellingUS = $percentage*DBLPrice3 ,
							TPLPriceSellingCurrency = 'USD' ,
							TPLPriceSellingLocal = $percentage*TPLPrice3 ,
							TPLPriceSellingUS = $percentage*TPLPrice3
					FROM    dbo.tbHotelBookings
							INNER JOIN dbo.HotelBookingCostPrice ON dbo.tbHotelBookings.HBId = dbo.HotelBookingCostPrice.HBId
							INNER JOIN dbo.QuotationData ON dbo.tbHotelBookings.QuotationDataId = dbo.QuotationData.QuotationDataId
					WHERE   dbo.tbHotelBookings.TourId = ? " ;
	break;
	default :
		$sqlCost = "UPDATE  dbo.HotelBookingCostPrice
					SET     SGLPriceQuotedCurrency = 'USD' ,
							SGLPriceQuotedLocal = SGLPrice1 ,
							SGLPriceQuotedUS = SGLPrice1 ,
							DBLPriceQuotedCurrency = 'USD' ,
							DBLPriceQuotedLocal = DBLPrice1 ,
							DBLPriceQuotedUS = DBLPrice1 ,
							TWNPriceQuotedCurrency = 'USD' ,
							TWNPriceQuotedLocal = DBLPrice1 ,
							TWNPriceQuotedUS = DBLPrice1 ,
							TPLPriceQuotedCurrency = 'USD' ,
							TPLPriceQuotedLocal = TPLPrice1 ,
							TPLPriceQuotedUS = TPLPrice1 ,

							SGLPriceSellingCurrency = 'USD' ,
							SGLPriceSellingLocal = $percentage*SGLPrice1 ,
							SGLPriceSellingUS = $percentage*SGLPrice1 ,
							DBLPriceSellingCurrency = 'USD' ,
							DBLPriceSellingLocal = $percentage*DBLPrice1 ,
							DBLPriceSellingUS = $percentage*DBLPrice1 ,
							TWNPriceSellingCurrency = 'USD' ,
							TWNPriceSellingLocal = $percentage*DBLPrice1 ,
							TWNPriceSellingUS = $percentage*DBLPrice1 ,
							TPLPriceSellingCurrency = 'USD' ,
							TPLPriceSellingLocal = $percentage*TPLPrice1 ,
							TPLPriceSellingUS = $percentage*TPLPrice1
					FROM    dbo.tbHotelBookings
							INNER JOIN dbo.HotelBookingCostPrice ON dbo.tbHotelBookings.HBId = dbo.HotelBookingCostPrice.HBId
							INNER JOIN dbo.QuotationData ON dbo.tbHotelBookings.QuotationDataId = dbo.QuotationData.QuotationDataId
					WHERE   dbo.tbHotelBookings.TourId = ? " ;
	break;
}

#var_dump($sqlCost);

// end switch

				/*
				$sqlCost .="
				UPDATE  dbo.HotelBookingCostPrice
				SET     SGLPriceBookedCurrency = 'USD' ,
						SGLPriceBookedLocal = SglContact ,
						SGLPriceBookedUS = SglContact ,
						DBLPriceBookedCurrency = 'USD' ,
						DBLPriceBookedLocal = Dblcontact ,
						DBLPriceBookedUS = Dblcontact ,
						TWNPriceBookedCurrency = 'USD' ,
						TWNPriceBookedLocal = Dblcontact ,
						TWNPriceBookedUS = Dblcontact ,
						TPLPriceBookedCurrency = 'USD' ,
						TPLPriceBookedLocal = (Triple + Dblcontact) ,
						TPLPriceBookedUS = (Triple + Dblcontact )
				FROM    dbo.tbHotelBookings
						INNER JOIN dbo.HotelBookingCostPrice ON dbo.tbHotelBookings.HBId = dbo.HotelBookingCostPrice.HBId
						INNER JOIN dbo.tbHotelRoomCategory ON dbo.tbHotelBookings.RoomId = dbo.tbHotelRoomCategory.RoomId
				WHERE   dbo.tbHotelBookings.TourId = ?
				";
				*/

				$sqlCost .="
				UPDATE  dbo.HotelBookingCostPrice
				SET     SGLPriceBookedCurrency =  c.Currency ,
						SGLPriceBookedLocal = p.SGLRoom ,
						SGLPriceBookedUS = SglContact ,
						DBLPriceBookedCurrency =  c.Currency ,
						DBLPriceBookedLocal = p.DBLRoom ,
						DBLPriceBookedUS = Dblcontact ,
						TWNPriceBookedCurrency =  c.Currency ,
						TWNPriceBookedLocal = p.DBLRoom ,
						TWNPriceBookedUS = Dblcontact ,
						TPLPriceBookedCurrency =  c.Currency ,
						TPLPriceBookedLocal = p.DBLRoom + p.ExtraBed ,
						TPLPriceBookedUS = (Triple + Dblcontact )
				FROM    dbo.tbHotelBookings
						INNER JOIN dbo.HotelBookingCostPrice ON dbo.tbHotelBookings.HBId = dbo.HotelBookingCostPrice.HBId
						INNER JOIN dbo.tbHotelRoomCategory ON dbo.tbHotelBookings.RoomId = dbo.tbHotelRoomCategory.RoomId
						LEFT JOIN dbo.HotelRatePrice AS p ON p.PriceId = dbo.tbHotelRoomCategory.HotelRatePriceId
						LEFT JOIN dbo.HotelRateMaster AS m ON m.RateMasterId = p.RateMasterId
						LEFT JOIN dbo.Currency AS c ON c.Id = m.CurrencyId
				WHERE   dbo.tbHotelBookings.TourId = ?
				";

					unset( $params ) ;
					$params[]  =  $tourId ;
					$params[]  =  $tourId ;
					//echo "12. $sqlCost <hr/>";
					//print_r($params);
					$rs =	$this->db->query( $sqlCost , $params);

				$sql = "UPDATE  dbo.tbHotelBookings
				SET
				dbo.tbHotelBookings.SGLPrice=dbo.tbHotelRoomCategory.SglContact,
				dbo.tbHotelBookings.DBLPrice=dbo.tbHotelRoomCategory.Dblcontact,
				dbo.tbHotelBookings.TPLPrice= (dbo.tbHotelRoomCategory.Triple+dbo.tbHotelRoomCategory.Dblcontact)
				FROM  dbo.tbHotelBookings
				INNER JOIN dbo.tbHotelRoomCategory ON dbo.tbHotelBookings.RoomId = dbo.tbHotelRoomCategory.RoomId
				WHERE dbo.tbHotelBookings.HBId= ? ";
				$params = array();
				$params[]  =  $HBId ;
				$rs =	$this->db->query( $sql , $params);
			#############################################################################################

		if($output){
			$output = trim( implode(  "\n"  , $output  ) );
			return array ($HBId,$output);
		}else{
			return array ($HBId,true);
		}
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackRestaurant($confirmationsId,$quotationId,$tourId,$dateFrom,$dateTo,$pax,$cby,$change)
{
	try{
	//echo 'track Restaurant \n';
	// Track menu table -> tbRestaurantBookings

	$sql = " SELECT
					PercentV AS PercentageV,
					PercentM AS PercentageM,
					PercentC AS PercentageC,
					PercentL  AS PercentageL,
					PercentT AS PercentageT,
					PercentB AS PercentageB,
					PercentI AS percentageI
					FROM dbo.Quotation
					WHERE QuotationId = '$quotationId' ";

	$stmt = $this->db->query($sql , $params);
	$rst4 = $stmt->fetchAll();
	foreach($rst4 as $row){
		$percentV = $row->PercentV;
		$percentM = $row->PercentM;
		$percentC = $row->PercentC;
		$percentL = $row->PercentL;
		$percentT = $row->PercentT;
		$percentB = $row->PercentB;
		$percentI = $row->PercentI;
	}

	$sql= "SELECT [Quotation-ID] AS quotationId,
					   Day,
					   [Menu ID] AS menuId,
					   Restaurantid,
					   LD,
					   CAST(QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
					   Price,
					   Country ,
					   CurrencyId
				FROM
				(
					SELECT QuotationData.QuotationId AS [Quotation-ID],
						   QuotationData.DayNo AS Day,
						   tbMenu.MenuId AS [Menu ID],
						   tbRestaurants.RestaurantId,
						   'Lunch' AS LD,
						   CAST(QuotationData.QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
						   Price,
						   Country,
						   CAST(tbMenu.CurrencyId AS VARCHAR(36)) AS CurrencyId
					FROM QuotationData
						 INNER JOIN tbMenu ON tbMenu.MenuId = QuotationData.MenuId1
						 INNER JOIN tbRestaurants ON tbRestaurants.RestaurantId = tbMenu.RestaurantId
					WHERE(QuotationId = ?)
					UNION ALL
					SELECT QuotationData_1.QuotationId AS [Quotation-ID],
						   QuotationData_1.DayNo AS Day,
						   tbMenu_1.MenuId AS [Menu ID],
						   tbRestaurants_1.RestaurantId,
						   'Dinner' AS LD,
						   CAST(QuotationData_1.QuotationDataId AS NVARCHAR(36)) AS QuotationDataId,
						   Price,
						   Country,
						   CAST(tbMenu_1.CurrencyId AS VARCHAR(36)) AS CurrencyId
					FROM QuotationData AS QuotationData_1
						 INNER JOIN tbMenu AS tbMenu_1 ON tbMenu_1.MenuId = QuotationData_1.MenuId2
						 INNER JOIN tbRestaurants AS tbRestaurants_1 ON tbRestaurants_1.RestaurantId = tbMenu_1.RestaurantId
					WHERE(QuotationId = ?)
				) AS [quotationdata confII restaurant]
				ORDER BY Day; " ;

		unset($params);
		$params[] = "$quotationId";
		$params[] = "$quotationId";
		$stmt = $this->db->query($sql , $params);
		$rst4 = $stmt->fetchAll();

		$ct = count($rst4);

		foreach($rst4 as $row)
		{
			$totalrows 		=  $ct;
			$restaurantid 	=  $row->Restaurantid; 	// 0
			$menu 			=  $row->menuId ; 		//1
			$ld 			=  $row->LD ; 					// 2
			$day 			=  $row->Day ;	 				// 3
			$QDataId 		= $row ->QuotationDataId ;
			$country_name = strtolower($row->Country);
			$startdate 		= $this->DateAdd( "d" , $day - 1  , $dateFrom  ) ;
			$today= date("d-M-Y") ;
			$currency_id = $row->CurrencyId;

			$percent = 1;
			if($country_name == "cambodia"){
				$percent = 1+round(($percentC/100.00), 2);
			}else if($country_name == "vietnam"){
				$percent = 1+round(($percentV/100.00), 2);
			}else if($country_name == "laos"){
				$percent = 1+round(($percentL/100.00), 2);
			}else if($country_name == "myanmar"){
				$percent = 1+round(($percentM/100.00), 2);
			}else if($country_name == "thailand"){
				$percent = 1+round(($percentT/100.00), 2);
			}else if($country_name == "indonesia"){
				$percent = 1+round(($percentB/100.00), 2);
			}else if($country_name == "india"){
				$percent = 1+round(($percentI/100.00), 2);
			}

			if( $change == "no" )
			{
				$RBId =  $this->GetAutoKey("tbRestaurantBookings", "RBId" ) ;
				$sql = "INSERT INTO [tbRestaurantBookings] (
								 [RBId] ,
								 [TourId] ,
								 [RestaurantId] ,
								 [MenuId] ,
								 [LunchDinner] ,
								 [Pax] ,
								 [BookingDate] ,
								 [OnDay]  ,
								 QuotationDataId ,
								 cby,
								 ConfirmationsId,
								 CurrencyId )  VALUES(
								 ? ,
								 ? ,
								 ? ,
								 ? ,
								 ? ,
								 ? ,
								 CAST( ? AS DATETIME ) ,
								 CAST( ? AS DATETIME ) ,
								 ? ,
								 ? ,
								 ? , '$currency_id')

						INSERT  INTO dbo.RestaurantBookingCostPrice
							(RBId ,TourId,ConfirmationsId,PriceQuotedUS,PriceSellingUS)
						VALUES(?,?,?,?,?)

						UPDATE dbo.RestaurantBookingCostPrice
								SET PriceBookedUS = dbo.tbMenu.Price ,
										PriceBookedLocal = dbo.tbMenu.LocalPrice
						FROM    dbo.RestaurantBookingCostPrice
								INNER JOIN dbo.tbRestaurantBookings ON dbo.RestaurantBookingCostPrice.RBId = dbo.tbRestaurantBookings.RBId
								INNER JOIN dbo.tbMenu ON dbo.tbRestaurantBookings.MenuId = dbo.tbMenu.MenuId
						WHERE dbo.tbRestaurantBookings.TourId = ?

" ;

				unset( $params ) ;
				$params[] = $RBId ;
				$params[] = $tourId ;
				$params[] = $restaurantid ;
				$params[] = $menu ;
				$params[] = $ld ;
				$params[] = $pax ;
				$params[] = $today ;
				$params[] = $startdate ;
				$params[] = $QDataId ;
				$params[] = $cby;
				$params[] = $confirmationsId;

				$params[] = $RBId;
				$params[] = $tourId;
				$params[] = $confirmationsId;
				$params[] = $row->Price ;
				$params[] = $row->Price*$percent ;

				$params[] = $tourId ;

				$rs = $this->db->query( $sql  , $params) ;

				//echo "insert ".++$i."OK\n";
			}
			else if( $change == "yes" )
			{
				$sql44 = "SELECT [RBId]
						  FROM [tbRestaurantBookings]
						  WHERE
									[TourId] = ? and
									[RestaurantId]= ?  and
									[OnDay]=CAST(? AS DATETIME) and
									[LunchDinner]= ? and
									[Status] NOT LIKE  'cancel%'  " ;
				unset( $params ) ;
				$params[] = $tourId ;
				$params[] = $restaurantid ;
				$params[] = $startdate ;
				$params[] = $ld ;
				$rst44 = $this->db->query($sql44 , $params);

					if($row = $rst44->fetch() )
					{
						$rbid = $row->RBId ;

						$sql = "UPDATE tbRestaurantBookings
									SET 		[Pax] 	=   	?
									WHERE
											( 	[RBId]	= 	 	?  ) AND
											( 	[Pax] 	<> 	?  ); " ;
						unset($params);
						$params[] = $pax ;
						$params[] = $rbid ;
						$params[] = $pax ;
						$rs = $this->db->query( $sql , $params) ;
						//echo "update ".++$i."OK\n";
					}
			} // end if
		}// end for
	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}



public function trackGuide($confirmationsId,$quotationId,$tourId,$cby,$dateFrom,$dateTo,$pax='')// Function 4
{
	try{
		//echo "Track Guide \n ";

		$sql= "SELECT  DayNo ,
							dbo.QuotationDataSavedGuidePrice.GuideId ,
							GuideDescription ,
							dbo.QuotationDataSavedGuidePrice.Price ,
							dbo.Quotation.PercentV ,
							dbo.Quotation.PercentC ,
							dbo.Quotation.PercentL ,
							dbo.Quotation.PercentM ,
							dbo.Quotation.PercentT ,
							dbo.Quotation.PercentI ,
							dbo.tbCountry.CountryId ,
							dbo.tbCountry.CountryDesc
					FROM    dbo.QuotationData
							INNER JOIN dbo.QuotationDataSavedGuidePrice ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataSavedGuidePrice.QuotationDataId
							INNER JOIN dbo.Quotation ON dbo.Quotation.QuotationId = dbo.QuotationData.QuotationId
							INNER JOIN dbo.tbGuidePrice ON dbo.tbGuidePrice.GPId = dbo.QuotationDataSavedGuidePrice.GuideId
							INNER JOIN dbo.tbCountry ON dbo.tbCountry.CountryId = dbo.tbGuidePrice.CountryId
					WHERE   dbo.QuotationData.QuotationId = ?
							AND LEN(dbo.QuotationDataSavedGuidePrice.GuideId) = 16
					ORDER BY DayNo	" ;
			unset($params);
			$params[] = "$quotationId";
			$rst4 = $this->db->query($sql , $params);

			while($row = $rst4->fetch())
			{
				$GuideId 			=  $row->GuideId ;
				$GuideDescription 	=  $row->GuideDescription;
				$Price 				=  $row->Price;
				$day 				=  $row->DayNo;
				$startdate 			= $this->DateAdd( "d" , $day - 1  , $dateFrom  ) ;

				$country_name = strtolower(trim($row->CountryDesc));
				$countryid = $row->CountryId;
				$selling_price = 0;
				$percent = 1;

				switch ($country_name) {
					case 'vietnam':
						$percent = 1+($row->PercentV/100.00);
						break;
					case 'cambodia':
						$percent = 1+($row->PercentC/100.00);
						break;
					case 'laos':
						$percent = 1+($row->PercentL/100.00);
						break;
					case 'myanmar':
						$percent = 1+($row->PercentM/100.00);
						break;
					case 'thailand':
						$percent = 1+($row->PercentT/100.00);
						break;
					case 'indonesia':
						$percent = 1+($row->PercentI/100.00);
						break;
				}

				$selling_price = ceil($Price*$percent);

				$sql	=	"	SELECT multiply_Formula 
								FROM  dbo.GuideFormula 
								WHERE GuideFormula.CountryId = ?
												AND  ? BETWEEN GuideFormula.StartPax AND GuideFormula.EndPax";
				unset($params);
				$params[]	=	$countryid;
				$params[]	=	$pax;
				$rstFormula = $this->db->query($sql , $params);

				while($row2 = $rstFormula->fetch())
				{
					$multiply_Formula = $row2->multiply_Formula;
				}
				//var_dump($sql , $params ,$multiply_Formula);

				if($multiply_Formula > 0){
					
					for ($i = 1; $i <= $multiply_Formula; $i++) {
						$GBId =  $this->GetAutoKey("tbGuideBookings", "GBId" ) ;
						$sql = "INSERT INTO dbo.tbGuideBookings
									( GBId , TourId , Startdate ,
									  GPId , cdate , cby ,
									  QuotationId ,  ConfirmationsId , Price
									)
									VALUES  ( ?,?,CAST(? AS DATETIME),
											  ?,GETDATE(),?,
											  ?,?,?)" ;
						unset( $params ) ;
						$params[] = $GBId ;
						$params[] = $tourId ;
						$params[] = $startdate ;
						$params[] = $GuideId ;
						$params[] = $cby;
						$params[] = "$quotationId";
						$params[] = $confirmationsId;
						$params[] = $Price;
						$this->db->query( $sql  , $params) ;
						###
						$sql = "INSERT  INTO dbo.GuideBookingCostPrice
									( GBId ,
									  TourId ,
									  ConfirmationsId ,
									  PriceSellingCurrency ,
									  PriceSellingLocal ,
									  PriceSellingUS ,
									  PriceQuotedCurrency ,
									  PriceQuotedLocal ,
									  PriceQuotedUS ,
									  PriceBookedCurrency ,
									  PriceBookedLocal ,
									  PriceBookedUS ,
									  CreateDate ,
									  CreateBy ,
									  GPId
									)
									SELECT  '$GBId' ,
											'$tourId' ,
											'$confirmationsId' ,
											'USD' ,
											'$selling_price' ,
											'$selling_price' ,
											'USD' ,
											'$Price' ,
											'$Price' ,
											'USD' ,
											'$Price' ,
											'$Price' ,
											GETDATE() ,
											'$cby' ,
											'$GuideId' ";
						$this->db->query( $sql) ;
						###
					}
				}
			}
		return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackMisc($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby)
{
	//echo "Track Misc \n ";
	// Other expense
	try{
		$sql= "
			SELECT
			dbo.QuotationData.DayNo,
			dbo.QuotationData.savedExtraPPCost,
			dbo.QuotationData.savedExtraSharedCost,
			dbo.QuotationData.ExtraPP,
			dbo.QuotationData.ExtraShared,

			dbo.QuotationData.SavedExtraPPCost2,
			dbo.QuotationData.SavedExtraSharedCost2,
			dbo.QuotationData.ExtraPP2,
			dbo.QuotationData.ExtraShared2
			FROM dbo.QuotationData
			WHERE QuotationId= ?
			ORDER BY DayNo
			" ;
		unset($params);
		$params[] = "$quotationId";
		$rst51 = $this->db->query($sql , $params);
		while($row = $rst51->fetch())
		{
			$day 			=  $row->DayNo;	 				// 3
			$startdate 		= $this->DateAdd( "d" , $day - 1  , $dateFrom  ) ;

			$sql = "INSERT INTO dbo.MisceBooking
							( DayNo , DateRun , PerPaxDesc,PerPaxCost ,
							  SharedDesc,SharedCost , TourId , QuotationId ,
							  ConfirmationsId,cby,cdate )
					VALUES  ( ?,CAST(? AS DATETIME),?,?,
							  ?,?,?,?,
							  ?,?,GETDATE()) " ;

			unset( $params ) ;
			$params[] = $day ;
			$params[] = $startdate ;
			$params[] = $row->ExtraPP;
			$params[] = $row->savedExtraPPCost;
			$params[] = $row->ExtraShared;
			$params[] = $row->savedExtraSharedCost;
			$params[] = $tourId ;
			$params[] = "$quotationId" ;
			$params[] = $confirmationsId;
			$params[] = $cby;
			$rs = $this->db->query( $sql  , $params,52) ;

			$sql = "INSERT INTO dbo.MisceBooking
							( DayNo , DateRun , PerPaxDesc,PerPaxCost ,
							  SharedDesc,SharedCost , TourId , QuotationId ,
							  ConfirmationsId,cby,cdate )
					VALUES  ( ?,CAST(? AS DATETIME),?,?,
							  ?,?,?,?,
							  ?,?,GETDATE()) " ;

			unset( $params ) ;
			$params[] = $day ;
			$params[] = $startdate ;
			$params[] = $row->ExtraPP2;
			$params[] = $row->SavedExtraPPCost2;
			$params[] = $row->ExtraShared2;
			$params[] = $row->SavedExtraSharedCost2;
			$params[] = $tourId ;
			$params[] = "$quotationId" ;
			$params[] = $confirmationsId;
			$params[] = $cby;
			$rs = $this->db->query( $sql  , $params,52) ;

		}
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackMiscNew($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby, $pax) 
{
	// echo "Track Misc \n ";
	// exit();
	// Other expense
	try{

		// Get day
		$sql= "SELECT qd.DayNo
			, qd.savedExtraPPCost
			, qd.savedExtraSharedCost
			, qd.ExtraPP
			, qd.ExtraShared

			, qd.SavedExtraPPCost2
			, qd.SavedExtraSharedCost2
			, qd.ExtraPP2
			, qd.ExtraShared2

			, co1p.CountryId AS [CountryIdExtraPP]
    		, co1e.CountryId AS [CountryIdExtraShared]
    		, co2p.CountryId AS [CountryIdExtraPP2]
    		, co2e.CountryId AS [CountryIdExtraShared2]
			, co1p.CountryDesc AS [CountryExtraPP]
    		, co1e.CountryDesc AS [CountryExtraShared]
    		, co2p.CountryDesc AS [CountryExtraPP2]
    		, co2e.CountryDesc AS [CountryExtraShared2]

			FROM dbo.QuotationData qd
			LEFT JOIN dbo.tbCountry co1P ON co1P.CountryId = qd.CountryId1PPCost
			LEFT JOIN dbo.tbCountry co1E ON co1E.CountryId = qd.CountryId1ExtraSharedCost
			LEFT JOIN dbo.tbCountry co2P ON co2P.CountryId = qd.CountryId2PPCost
			LEFT JOIN dbo.tbCountry co2E ON co2E.CountryId = qd.CountryId2ExtraSharedCost
			WHERE QuotationId = ?
			ORDER BY DayNo
		";
		//BKG6201600003105
		unset($params);
		$params[] = "$quotationId";
		$rs = $this->db->query($sql , $params);
		// var_dump($quotationId); exit();
		// var_dump($pax); exit();
		// echo "<pre>"; var_dump($rs); exit();

		while($row = $rs->fetch())
		{
			// echo "<pre>"; var_dump($row); exit();
			$day 			=  $row->DayNo;	 				// 3					
			$startdate 		= $this->DateAdd("d", $day - 1, $dateFrom ) ;	

			// var_dump($row -> ExtraPP, $row -> ExtraShared, $row -> ExtraPP2, $row -> ExtraShared2); exit();
			
			if(!empty($row -> ExtraPP))
				$this -> insertOtherCostBookingQuery('Pax1', $day, $startdate, $tourId, $confirmationsId,$quotationId, $pax, $row);
			
			if(!empty($row -> ExtraShared))
				$this -> insertOtherCostBookingQuery('Group1', $day, $startdate, $tourId, $confirmationsId, $quotationId, $pax, $row);
			
			if(!empty($row -> ExtraPP2))
				$this -> insertOtherCostBookingQuery('Pax2', $day, $startdate, $tourId, $confirmationsId, $quotationId, $pax, $row);
			
			if(!empty($row -> ExtraShared2))
				$this -> insertOtherCostBookingQuery('Group2', $day, $startdate, $tourId, $confirmationsId, $quotationId, $pax, $row);
		}


	}
	catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);			
		return false;
	}	
}

public function insertOtherCostBookingQuery($type, $day, $startdate, $tourId, $confirmationsId, $quotationId, $pax, $row){
	$sql = "INSERT INTO dbo.OtherCostBooking
( 
	dbo.OtherCostBooking.OtherCostBookingId
    --1
    , dbo.OtherCostBooking.CountryId
    , dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.DateRun
    --2
    , dbo.OtherCostBooking.OtherCostDesc
    , dbo.OtherCostBooking.OtherCostType
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.QuotationId
     --3
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.ConfirmationsId
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.cby
    , dbo.OtherCostBooking.cdate

	--4
    , dbo.OtherCostBooking.QuoteUS
	, dbo.OtherCostBooking.QuoteLocal
	, dbo.OtherCostBooking.QuoteCurrency
	, dbo.OtherCostBooking.BookUS
	, dbo.OtherCostBooking.BookLocal

	--5
	, dbo.OtherCostBooking.BookCurrency
	, dbo.OtherCostBooking.CompanyId
	, dbo.OtherCostBooking.CurrencyId
)
VALUES  ( 
	NEWID()
    --1
    , ?
    , ?
    , ?
    , CAST(? AS DATETIME)
    --2
    , ?
    , ?
    , ?
    , ?
    --3
    , ?
    , ?
    , ?
    , ?
    , GETDATE()
    --4
    , ?
    , ?
    , 'USD'
    , ?
    , ?
    --5
    , 'USD'
    , ?
    , ?
)
					";  	

			unset( $params ) ;

			$Extra_Remark = NULL ; //otherCostDesc
			$Extra_Type = NULL; //otherCostType
			$Extra_Cost = NULL;
			$serviceCategoryId = NULL;
			$CountryId = NULL;
			$CityId = NULL;
			$CompanyId = NULL;
			$CurrencyId = 'D0B52178-2825-4004-8F00-E6FBC0E8F062'; //usd

			// 	, qd.savedExtraPPCost
			// , qd.savedExtraSharedCost
			// , qd.ExtraPP
			// , qd.ExtraShared

			// , qd.SavedExtraPPCost2
			// , qd.SavedExtraSharedCost2
			// , qd.ExtraPP2
			// , qd.ExtraShared2

			// , co1p.CountryId AS [CountryIdExtraPP]
   //  		, co1e.CountryId AS [CountryIdExtraShared]
   //  		, co2p.CountryId AS [CountryIdExtraPP2]
   //  		, co2e.CountryId AS [CountryIdExtraShared2]
			// , co1p.CountryDesc AS [CountryExtraPP]
   //  		, co1e.CountryDesc AS [CountryExtraShared]
   //  		, co2p.CountryDesc AS [CountryExtraPP2]
   //  		, co2e.CountryDesc AS [CountryExtraShared2]


			if($type == "Pax1"){
				$CountryId = $row -> CountryIdExtraPP;
				$Extra_Cost = $row -> savedExtraPPCost;
				$Extra_Remark = $row -> ExtraPP;
				$Extra_Type = 1;
			}
			else if($type == "Group1"){
				$CountryId = $row -> CountryIdExtraShared;
				$Extra_Cost = $row -> savedExtraSharedCost;
				$Extra_Remark = $row -> ExtraShared;
				$Extra_Type = 2;
			}
			else if($type == "Pax2"){
				$CountryId = $row -> CountryIdExtraPP2;
				$Extra_Cost = $row -> SavedExtraPPCost2;
				$Extra_Remark = $row -> ExtraPP2;
				$Extra_Type = 1;
			}
			else if($type == "Group2"){
				$CountryId = $row -> CountryIdExtraShared2;
				$Extra_Cost = $row -> SavedExtraSharedCost2;
				$Extra_Remark = $row -> ExtraShared2;
				$Extra_Type = 2;
			}
			else{
				//do nothing
			}

			//Add defaul city and company
			if($CountryId == 'BKG1200800000002'){ //Cambodia
				$CityId = 'BKG1200800000120';
				$CompanyId = '96DCA8DB-B77F-4C02-AC93-07C8261C5427';
			}
			else if($CountryId == 'BKG1200800000006'){ //Indonesia
				$CityId = 'BKG1200900000050';
				$CompanyId = '0E5E86FE-6D37-452B-8EA5-E03DBAA378AD';
			}
			else if($CountryId == 'BKG1200800000003'){ //Laos
				$CityId = 'BKG1200800000144';
				$CompanyId = '974F9A3F-2C36-48A1-BB33-DA1A4C8D485B';
			}
			else if($CountryId == 'BKG1200800000004'){ //Myanmar
				$CityId = 'BKG1200800000126';
				$CompanyId = '5954AA86-B0E7-4FE2-923B-71275B83A493';
			}
			else if($CountryId == 'BKG1200800000001'){ //Vietnam
				$CityId = 'BKG1200800000111';
				$CompanyId = '6011A8EE-06F9-422F-96B3-D832DF175044';
			}
			else{
				//do nothing
			}
		
			$params[] = $CountryId;
			$params[] = $CityId;
			$params[] = $day ;
			$params[] = $startdate ;

			$params[] = $Extra_Remark ; //otherCostDesc
			$params[] = $Extra_Type ; //otherCostType
			$params[] = $tourId ;
			$params[] = "$quotationId" ;

    		$params[] = $serviceCategoryId;
			$params[] = $confirmationsId;
			$params[] = $pax;
			// $cby = "VTest";
			$cby = "TorTest";
			$params[] = $cby;

			$params[] = $Extra_Cost;
			$params[] = $Extra_Cost;
			$params[] = $Extra_Cost;
			$params[] = $Extra_Cost;

			$params[] = $CompanyId;
			$params[] = $CurrencyId;

			// var_dump($sql, $params); exit();
			$this->db->query($sql, $params) ;		//, 52
}

public function trackMiscNew2($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby, $pax) 
{
	// echo "Track Misc \n ";
	// exit();
	// Other expense
	try{

		// Get day
		$sql= "SELECT DISTINCT dbo.QuotationData.DayNo
				, dbo.QuotationData_ExtraCost.Extra_Cost
				--, dbo.QuotationData_ExtraCost.Extra_Cost_Saved
				, dbo.QuotationData_ExtraCost.Extra_Type
				, dbo.QuotationData_ExtraCost.Extra_Remark
				, dbo.QuotationData_ExtraCost.Pos
				, dbo.QuotationData_ExtraCost.CountryId
				, dbo.QuotationData_ExtraCost.CityId
				, dbo.QuotationData_ExtraCost.ServiceCategoryId
				
				FROM dbo.QuotationData_ExtraCost
				INNER JOIN dbo.QuotationData ON dbo.QuotationData.QuotationDataId = dbo.QuotationData_ExtraCost.QuotationDataId
				WHERE dbo.QuotationData.QuotationId = ?
				ORDER BY dbo.QuotationData.DayNo
					, dbo.QuotationData_ExtraCost.Pos 
		";
		unset($params);
		$params[] = "$quotationId";
		$rs = $this->db->query($sql , $params);
		// var_dump($quotationId); exit();
		// var_dump($pax); exit();
		// echo "<pre>"; var_dump($rs); exit();

		while($row = $rs->fetch())
		{
			// echo "<pre>"; var_dump($row); exit();
			$day 			=  $row->DayNo;	 				// 3					
			$startdate 		= $this->DateAdd("d", $day - 1, $dateFrom ) ;	
			
			$sql = "INSERT INTO dbo.OtherCostBooking
( 
	dbo.OtherCostBooking.OtherCostBookingId
    --1
    , dbo.OtherCostBooking.CountryId
    , dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.DateRun
    --2
    , dbo.OtherCostBooking.OtherCostDesc
    , dbo.OtherCostBooking.OtherCostType
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.QuotationId
    --3
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.ConfirmationsId
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.cby
    , dbo.OtherCostBooking.cdate

	--4
    , dbo.OtherCostBooking.QuoteUS
	, dbo.OtherCostBooking.QuoteLocal
	, dbo.OtherCostBooking.QuoteCurrency
	, dbo.OtherCostBooking.BookUS
	, dbo.OtherCostBooking.BookLocal

	--5
	, dbo.OtherCostBooking.BookCurrency
)
VALUES  ( 
	NEWID()
    --1
    , ?
    , ?
    , ?
    , CAST(? AS DATETIME)
    --2
    , ?
    , ?
    , ?
    , ?
    --3
    , ?
    , ?
    , ?
    , ?
    , GETDATE()
    --4
    , ?
    , ?
    , 'USD'
    , ?
    , ?
    --5
    , 'USD'
)
					";  	

			unset( $params ) ;

			$params[] = $row -> CountryId ;
			$params[] = $row -> CityId ;
			$params[] = $day ;
			$params[] = $startdate ;

			$params[] = $row -> Extra_Remark ; //otherCostDesc
			$params[] = $row -> Extra_Type ; //otherCostType
			$params[] = $tourId ;
			$params[] = "$quotationId" ;

    		$params[] = $row -> ServiceCategoryId;
			$params[] = NULL; //$confirmationsId
			$params[] = $pax;
			$params[] = $cby;

			$params[] = $row -> Extra_Cost;
			$params[] = $row -> Extra_Cost;
			$params[] = $row -> Extra_Cost;
			$params[] = $row -> Extra_Cost;

			// var_dump($sql, $params); exit();
			$this->db->query($sql, $params) ;		//, 52
		}


	}
	catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);			
		return false;
	}	
}

public function trackWater($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby)
{
	try{
	// Water expense
	$sql= " SELECT  DayNo ,
					cast(QuotationDataDrinkingWaterId as varchar(36)) as QuotationDataDrinkingWaterId,
					DrinkWaterId,
					Price
			FROM    dbo.QuotationData
					INNER JOIN dbo.QuotationDataSavedDrinkingWater ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataSavedDrinkingWater.QuotationDataId
			WHERE   dbo.QuotationData.QuotationId = ?
			ORDER BY DayNo  " ;

	unset($params);
	$params[] = "$quotationId";
	$rst52 = $this->db->query($sql , $params);

	while($row2 = $rst52->fetch())
	{
		$day 		=  $row2->DayNo ;	 				// 3
		$OnDay 		= $this->DateAdd( "d" , $day - 1  , $dateFrom  ) ;

		$sql = " INSERT INTO dbo.WaterBooking
						 ( OnDay , BookDate , TourId ,
						   QuotationId , ConfirmationsId , QuotationDataDrinkingWaterId ,
						   DrinkWaterId , Price ,cby, Status )
				 VALUES  (cast(? as datetime),GETDATE(),?,
				 		  ?,?,?,
						  ?,?,?,'')" ;
		unset( $params ) ;
		$params[] = $OnDay ;
		$params[] = $tourId ;

		$params[] = "$quotationId" ;
		$params[] = $confirmationsId ;
		$params[] = $row2->QuotationDataDrinkingWaterId;

		$params[] = $row2->DrinkWaterId;
		$params[] = $row2->Price;
		$params[] = $cby;

		$rs = $this->db->query( $sql  , $params) ;
	}
	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackEntranceFee($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{

	// Entrance Fee
	$sql1 = "SELECT  DayNo ,
					QuotationDataQuoteItem.QuotationId ,
					CAST(QuotationDataQuoteItem.QuotationDataQuoteItemId AS VARCHAR(36)) AS QuotationDataQuoteItemId ,
					CAST(QuotationDataQuoteItem.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
					CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
					CAST(dbo.SupplierServiceRate.CompanyId AS VARCHAR(36)) AS CompanyId ,
					CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId ,
					USPrice ,
					LocalPrice ,
					Currency ,
					CurrencyRate ,
					dbo.SupplierServiceRate.IsPax ,
					Combineflag
			FROM    dbo.QuotationData
					LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
					LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
					AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
					LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
					LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
					LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
					LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
					LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
			WHERE
					 Combineflag = 1
					AND dbo.ServiceCategory.ServiceCategory_Desc IN ( 'Entrance Fee' )
					AND QuotationDataQuoteItem.QuotationId = ?

			ORDER BY DayNo ";
	unset($params1);
	$params1 = array();
	$params1[] = $columnNo;
	$params1[] = "$quotationId";

	//echo "$sql <br />";
//	print_r($params);
//	exit();
	try{
		$rs = $this->db->query($sql1 , $params1);
	}catch(Exception $e){
		echo $sql1;
		print_r($params1);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
	}
	try{
	$count = 1;
	while($row = $rs->fetch())
	{

		$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
		$sql = "
				SET NOCOUNT  ON
				DECLARE @ebid AS UNIQUEIDENTIFIER
				SET @ebid = NEWID()

				INSERT INTO dbo.EntranceFeeBooking
				( EBId,OnDay , BookDate , TourId ,
				   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
				   CompanyId , ServiceContractId ,ServiceRateId , cby ,Status)
					VALUES  (@ebid, ?,GETDATE(),?,
					  		  ?,?,?,
					   		  ?,?,?,?,'OK')

				select cast(@ebid as varchar(36)) as ebid

				 ";

		unset($params);
		$params = array();
		$params[] = $OnDay;
		$params[] = $tourId;

		$params[] = "$quotationId";
		$params[] = $confirmationsId;
		$params[] = $row->QuotationDataQuoteItemId;

		$params[] = $row->CompanyId;
		$params[] = $row->ServiceContractId;
		$params[] = $row->ServiceRateId;
		$params[] = $cby ;

		$rs2 = $this->db->query($sql , $params);

		if($row2 = $rs2->fetch() and !empty($row->ServiceRateId))
		{
			//echo "EBID =" . $rst56->Fields('ebid')." \n  ".$rst55->Fields('ItemSavedOriginalRateId');
			$this->SaveCostSelling($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostQuoted($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax,$tourId,$columnNo);
			$this->SaveCostConfirmed($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}



	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}


public function trackEntranceFeePlus($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{

	// Entrance Fee
	$sql1 = "SELECT  DayNo ,
					QuotationDataQuoteItem.QuotationId ,
					CAST(QuotationDataQuoteItem.QuotationDataQuoteItemId AS VARCHAR(36)) AS QuotationDataQuoteItemId ,
					CAST(QuotationDataQuoteItem.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
					CAST(SupplierServiceRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
					CAST(dbo.SupplierServiceRate.CompanyId AS VARCHAR(36)) AS CompanyId ,
					CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId ,
					QuotationDataQuoteItemOriginalRate.USPrice ,
					QuotationDataQuoteItemOriginalRate.LocalPrice ,
					Currency.Currency ,
					Currency.CurrencyRate ,
					dbo.SupplierServiceRate.IsPax ,
					SupplierServiceContract.Combineflag,
					  dbo.SupplierServiceRate.StartDefaultCapacity,
					  dbo.SupplierServiceRate.EndDefaultCapacity,
					  SupplierServiceContract.ServiceName
			FROM    dbo.QuotationData
					LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
					LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
					AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
          LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
					LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
					AND  dbo.SupplierServiceRate.markettype ='Worldwide'
					AND  ? BETWEEN dbo.SupplierServiceRate.StartDefaultCapacity AND dbo.SupplierServiceRate.EndDefaultCapacity
					LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
					LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
					LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
			WHERE
					 Combineflag = 1
					AND dbo.ServiceCategory.ServiceCategory_Desc IN ( 'Entrance Fee' )
					AND QuotationDataQuoteItem.QuotationId = ?

			ORDER BY DayNo ";
	unset($params1);
	$params1 = array();
	$params1[] = $columnNo;
	$params1[] = $pax;
	$params1[] = "$quotationId";

	// echo "$sql <br />";
	// print_r($params);
	// exit();

	try{
		$rs = $this->db->query($sql1 , $params1);
	}catch(Exception $e){
		echo $sql1;
		print_r($params1);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
	}
	try{
	$count = 1;
	while($row = $rs->fetch())
	{

		$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
		$sql = "
				SET NOCOUNT  ON
				DECLARE @ebid AS UNIQUEIDENTIFIER
				SET @ebid = NEWID()

				INSERT INTO dbo.EntranceFeeBooking
				( EBId,OnDay , BookDate , TourId ,
				   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
				   CompanyId , ServiceContractId ,ServiceRateId , cby , Status)
					VALUES  (@ebid, ?,GETDATE(),?,
					  		  ?,?,?,
					   		  ?,?,?,?,'OK')

				select cast(@ebid as varchar(36)) as ebid

				 ";

		unset($params);
		$params = array();
		$params[] = $OnDay;
		$params[] = $tourId;

		$params[] = "$quotationId";
		$params[] = $confirmationsId;
		$params[] = $row->QuotationDataQuoteItemId;

		$params[] = $row->CompanyId;
		$params[] = $row->ServiceContractId;
		$params[] = $row->ServiceRateId;
		$params[] = $cby ;

		$rs2 = $this->db->query($sql , $params);

		if($row2 = $rs2->fetch() and !empty($row->ServiceRateId))
		{
			//echo "EBID =" . $row2->ebid." \n  ".$row->ItemSavedOriginalRateId;
			$this->SaveCostSelling($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostQuoted($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax,$tourId,$columnNo);
			$this->SaveCostConfirmed($confirmationsId,$row2->ebid,$row->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}



	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackFlight($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	$sql = "SELECT  DayNo ,
				airline ,
				startCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.StartCity
				) AS SFcode ,
				endCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.EndCity
				) AS EFcode ,
				DATEADD(DAY, DayNo - 1, GETDATE()) AS flightDate ,
				GETDATE() AS bookdate ,
				nf ,
				GETDATE() AS cdate ,
				USPrice ,
				LocalPrice ,
				StartCityId ,
				EndCityId ,
				ServiceCategory_Desc ,
				ServiceMasterId ,
				ServiceMasterName ,
				QuotationId ,
				ServiceRateId ,
				ItemSavedOriginalRateId,
				FlightClass
		FROM    ( SELECT    DayNo ,
							ServiceCategory_Desc ,
							QuotationDataQuoteItem.QuotationId ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
								 ELSE 1
							END AS nf ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightFromCityId
									  )
								 ELSE NULL
							END AS StartCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightToCityId
									  )
								 ELSE NULL
							END AS EndCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN Airline
								 ELSE ServiceCategory_Desc
							END AS Airline ,
							FlightFromCityId AS StartCityId ,
							FlightToCityId AS EndCityId ,
							USPrice ,
							LocalPrice ,
							dbo.SupplierServiceMaster.ServiceMasterId ,
							ServiceMasterName ,
							CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
							dbo.SupplierServiceRate.IsPax ,
							CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId,
							SupplierServiceMaster.FlightClass
				  FROM      dbo.QuotationData
							LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
							LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
									AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
							LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
							LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
							LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
							LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
							LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				  WHERE     Combineflag = 1
							AND ServiceCategory_Desc IN ( 'Flight', 'Boat', 'Train',
														  'Balloon' )

				) AS tmp
		WHERE   tmp.QuotationId = ?
		ORDER BY tmp.DayNo

		 ";

		unset($params);
		$params[] = $columnNo;
		$params[] = "$quotationId";

//		echo $sql."\n";
//		print_r($params);
//		exit();

		$rst61 = $this->db->query($sql , $params);

		while($row = $rst61->fetch())
		{
			//var_dump($row);
			$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
			$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

			if(trim($row->startCity) == 'Bangkok'){
				$startCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$startCity = $row->startCity;
			}


			if(trim($row->endCity) == 'Bangkok'){
				$endCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$endCity = $row->endCity;
			}

			if($row->FlightClass == 'business'){
				$flightClass = "C-Class";
			}else if($row->FlightClass == 'premium'){
				$flightClass = "U-Class";
			}else{
				$flightClass = "Y-Class";
			}

			$sqlinsert = "
			SET NOCOUNT  ON
			DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
				SET @FBIdUniqueId = NEWID()

			INSERT  INTO dbo.tbFlightBookings
					 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
					 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
					 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId ,ServiceRateId,Pax,class)
					 Values(?,@FBIdUniqueId,?,?,?,?,
					 		?,?,?,cast(? as DATETIME),GETDATE(),?,
							GETDATE(),?,?,?,?,?,?,?,?)


			SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

			unset($params);
			$params[] = $FBId;
			$params[] = $tourId;
			$params[] = $confirmationsId;
			$params[] = $row->airline;
			$params[] = $startCity;

			$params[] = $row->SFcode;
			$params[] = $endCity;
			$params[] = $row->EFcode;
			$params[] = $FlightDate;
			$params[] = $row->nf;

			$params[] = "$quotationId";
			$params[] = $row->USPrice;
			$params[] = $cby;
			$params[] = $row->StartCityId;
			$params[] = $row->EndCityId;
			$params[] = $row->ServiceRateId;
			$params[] = $pax;
			$params[] = $flightClass;

			$rst62 = $this->db->query($sqlinsert , $params);

			//echo $sqlinsert ."<hr />";
			//print_r($params);
			//return false;

			if($row2 = $rst62->fetch() and !empty($row->ServiceRateId)){
				$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax,$tourId,$columnNo);
			}
		}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackFlightPlus($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	$sql = "
		SELECT  DayNo ,
				airline ,
				startCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.StartCity
				) AS SFcode ,
				endCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.EndCity
				) AS EFcode ,
				DATEADD(DAY, DayNo - 1, GETDATE()) AS flightDate ,
				GETDATE() AS bookdate ,
				nf ,
				GETDATE() AS cdate ,
				USPrice ,
				LocalPrice ,
				StartCityId ,
				EndCityId ,
				ServiceCategory_Desc ,
				ServiceMasterId ,
				ServiceMasterName ,
				QuotationId ,
				ServiceRateId ,
				ItemSavedOriginalRateId,
				FlightClass
		FROM    ( SELECT    DayNo ,
							ServiceCategory_Desc ,
							QuotationDataQuoteItem.QuotationId ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
								 ELSE 1
							END AS nf ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightFromCityId
									  )
								 ELSE NULL
							END AS StartCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightToCityId
									  )
								 ELSE NULL
							END AS EndCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN Airline
								 ELSE ServiceCategory_Desc
							END AS Airline ,
							FlightFromCityId AS StartCityId ,
							FlightToCityId AS EndCityId ,
							USPrice ,
							LocalPrice ,
							dbo.SupplierServiceMaster.ServiceMasterId ,
							ServiceMasterName ,
							CAST(SupplierServiceRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
							dbo.SupplierServiceRate.IsPax ,
							CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId,
							SupplierServiceMaster.FlightClass
				  FROM      dbo.QuotationData
							LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
							LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
							AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
							LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							AND  dbo.SupplierServiceRate.markettype ='Worldwide'
							AND  ? BETWEEN dbo.SupplierServiceRate.StartDefaultCapacity AND dbo.SupplierServiceRate.EndDefaultCapacity
							LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
							LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
							LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
							LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				  WHERE     Combineflag = 1
							AND ServiceCategory_Desc IN ( 'Flight', 'Boat', 'Train','Balloon' )

				) AS tmp
		WHERE   tmp.QuotationId = ?
		ORDER BY tmp.DayNo
		 ";

		unset($params);
		$params[] = $columnNo;
		$params[] = $pax;
		$params[] = "$quotationId";

//		echo $sql."\n";
//		print_r($params);
//		exit();

		$rst61 = $this->db->query($sql , $params);

		while($row = $rst61->fetch())
		{
			//var_dump($row);
			$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
			$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

			if(trim($row->startCity) == 'Bangkok'){
				$startCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$startCity = $row->startCity;
			}


			if(trim($row->endCity) == 'Bangkok'){
				$endCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$endCity = $row->endCity;
			}

			if($row->FlightClass == 'business'){
				$flightClass = "C-Class";
			}else if($row->FlightClass == 'premium'){
				$flightClass = "U-Class";
			}else{
				$flightClass = "Y-Class";
			}

			$sqlinsert = "
			SET NOCOUNT  ON
			DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
				SET @FBIdUniqueId = NEWID()

			INSERT  INTO dbo.tbFlightBookings
					 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
					 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
					 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId ,ServiceRateId,Pax,class)
					 Values(?,@FBIdUniqueId,?,?,?,?,
					 		?,?,?,cast(? as DATETIME),GETDATE(),?,
							GETDATE(),?,?,?,?,?,?,?,?)


			SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

			unset($params);
			$params[] = $FBId;
			$params[] = $tourId;
			$params[] = $confirmationsId;
			$params[] = $row->airline;
			$params[] = $startCity;

			$params[] = $row->SFcode;
			$params[] = $endCity;
			$params[] = $row->EFcode;
			$params[] = $FlightDate;
			$params[] = $row->nf;

			$params[] = "$quotationId";
			$params[] = $row->USPrice;
			$params[] = $cby;
			$params[] = $row->StartCityId;
			$params[] = $row->EndCityId;
			$params[] = $row->ServiceRateId;
			$params[] = $pax;
			$params[] = $flightClass;

			$rst62 = $this->db->query($sqlinsert , $params);

			//echo $sqlinsert ."<hr />";
			//print_r($params);
			//return false;

			if($row2 = $rst62->fetch() and !empty($row->ServiceRateId)){
				$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax,$tourId,$columnNo);
			}
		}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackFlightOnly($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	$sql = "SELECT  DayNo ,
				airline ,
				startCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.StartCity
				) AS SFcode ,
				endCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.EndCity
				) AS EFcode ,
				DATEADD(DAY, DayNo - 1, GETDATE()) AS flightDate ,
				GETDATE() AS bookdate ,
				nf ,
				GETDATE() AS cdate ,
				USPrice ,
				LocalPrice ,
				StartCityId ,
				EndCityId ,
				ServiceCategory_Desc ,
				ServiceMasterId ,
				ServiceMasterName ,
				QuotationId ,
				ServiceRateId ,
				ItemSavedOriginalRateId
		FROM    ( SELECT    DayNo ,
							ServiceCategory_Desc ,
							QuotationDataQuoteItem.QuotationId ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
								 ELSE 1
							END AS nf ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightFromCityId
									  )
								 ELSE NULL
							END AS StartCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightToCityId
									  )
								 ELSE NULL
							END AS EndCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN Airline
								 ELSE ServiceCategory_Desc
							END AS Airline ,
							FlightFromCityId AS StartCityId ,
							FlightToCityId AS EndCityId ,
							USPrice ,
							LocalPrice ,
							dbo.SupplierServiceMaster.ServiceMasterId ,
							ServiceMasterName ,
							CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
							dbo.SupplierServiceRate.IsPax ,
							CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId
				  FROM      dbo.QuotationData
							LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
							LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
									AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
							LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
							LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
							LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
							LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
							LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				  WHERE     Combineflag = 1
							AND ServiceCategory_Desc IN ( 'Flight' )

				) AS tmp
		WHERE   tmp.QuotationId = ?
		ORDER BY tmp.DayNo

		 ";

		unset($params);
		$params[] = $columnNo;
		$params[] = "$quotationId";

		//echo $sql."\n";
		//print_r($params);
		//exit();

		$rst61 = $this->db->query($sql , $params);

		while($row = $rst61->fetch())
		{
			//var_dump($row);
			$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
			$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

			$sqlinsert = "
			SET NOCOUNT  ON
			DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
				SET @FBIdUniqueId = NEWID()

			INSERT  INTO dbo.tbFlightBookings
					 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
					 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
					 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId ,ServiceRateId,Pax)
					 Values(?,@FBIdUniqueId,?,?,?,?,
					 		?,?,?,cast(? as DATETIME),GETDATE(),?,
							GETDATE(),?,?,?,?,?,?,?)


			SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

			unset($params);
			$params[] = $FBId;
			$params[] = $tourId;
			$params[] = $confirmationsId;
			$params[] = $row->airline;
			$params[] = $row->startCity;

			$params[] = $row->SFcode;
			$params[] = $row->endCity;
			$params[] = $row->EFcode;
			$params[] = $FlightDate;
			$params[] = $row->nf;

			$params[] = "$quotationId";
			$params[] = $row->USPrice;
			$params[] = $cby;
			$params[] = $row->StartCityId;
			$params[] = $row->EndCityId;
			$params[] = $row->ServiceRateId;
			$params[] = $pax;

			$rst62 = $this->db->query($sqlinsert , $params);
			//echo $sqlinsert ."<hr />";
			//print_r($params);

			if($row2 = $rst62->fetch() and !empty($row->ServiceRateId)){
				$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax,$tourId,$columnNo);
			}
		}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackNotFlight($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	$sql = "SELECT  DayNo ,
				airline ,
				startCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.StartCity
				) AS SFcode ,
				endCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.EndCity
				) AS EFcode ,
				DATEADD(DAY, DayNo - 1, GETDATE()) AS flightDate ,
				GETDATE() AS bookdate ,
				nf ,
				GETDATE() AS cdate ,
				USPrice ,
				LocalPrice ,
				StartCityId ,
				EndCityId ,
				ServiceCategory_Desc ,
				ServiceMasterId ,
				ServiceMasterName ,
				QuotationId ,
				ServiceRateId ,
				ItemSavedOriginalRateId
		FROM    ( SELECT    DayNo ,
							ServiceCategory_Desc ,
							QuotationDataQuoteItem.QuotationId ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
								 ELSE 1
							END AS nf ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightFromCityId
									  )
								 ELSE NULL
							END AS StartCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightToCityId
									  )
								 ELSE NULL
							END AS EndCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN Airline
								 ELSE ServiceCategory_Desc
							END AS Airline ,
							FlightFromCityId AS StartCityId ,
							FlightToCityId AS EndCityId ,
							USPrice ,
							LocalPrice ,
							dbo.SupplierServiceMaster.ServiceMasterId ,
							ServiceMasterName ,
							CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
							dbo.SupplierServiceRate.IsPax ,
							CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId
				  FROM      dbo.QuotationData
							LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
							LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
									AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
							LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
							LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
							LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
							LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
							LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				  WHERE     Combineflag = 1
							AND ServiceCategory_Desc IN ( 'Boat', 'Train', 'Balloon' )

				) AS tmp
		WHERE   tmp.QuotationId = ?
		ORDER BY tmp.DayNo

		 ";

		unset($params);
		$params[] = $columnNo;
		$params[] = "$quotationId";

//		echo $sql."\n";
//		print_r($params);
//		exit();

		$rst61 = $this->db->query($sql , $params);

		while($row = $rst61->fetch())
		{
			//var_dump($row);
			$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
			$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

			if($row->startCity == 'Bangkok'){
				$startCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$startCity = $row->startCity;
			}


			if($row->endCity == 'Bangkok'){
				$endCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$endCity = $row->endCity;
			}

			$sqlinsert = "
			SET NOCOUNT  ON
			DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
				SET @FBIdUniqueId = NEWID()

			INSERT  INTO dbo.tbFlightBookings
					 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
					 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
					 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId ,ServiceRateId,Pax)
					 Values(?,@FBIdUniqueId,?,?,?,?,
					 		?,?,?,cast(? as DATETIME),GETDATE(),?,
							GETDATE(),?,?,?,?,?,?,?)


			SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

			unset($params);
			$params[] = $FBId;
			$params[] = $tourId;
			$params[] = $confirmationsId;
			$params[] = $row->airline;
			$params[] = $startCity;

			$params[] = $row->SFcode;
			$params[] = $endCity;
			$params[] = $row->EFcode;
			$params[] = $FlightDate;
			$params[] = $row->nf;

			$params[] = "$quotationId";
			$params[] = $row->USPrice;
			$params[] = $cby;
			$params[] = $row->StartCityId;
			$params[] = $row->EndCityId;
			$params[] = $row->ServiceRateId;
			$params[] = $pax;

			$rst62 = $this->db->query($sqlinsert , $params);

			//echo $sqlinsert ."<hr />";
			//print_r($params);
			//return false;

			if($row2 = $rst62->fetch() and !empty($row->ServiceRateId)){
				$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax,$tourId,$columnNo);
			}
		}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackNotFlightPlus($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	$sql = "
				SELECT  DayNo ,
				airline ,
				startCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.StartCity
				) AS SFcode ,
				endCity ,
				( SELECT    Fcode
				  FROM      dbo.tbFlightDestination
				  WHERE     Destination = tmp.EndCity
				) AS EFcode ,
				DATEADD(DAY, DayNo - 1, GETDATE()) AS flightDate ,
				GETDATE() AS bookdate ,
				nf ,
				GETDATE() AS cdate ,
				USPrice ,
				LocalPrice ,
				StartCityId ,
				EndCityId ,
				ServiceCategory_Desc ,
				ServiceMasterId ,
				ServiceMasterName ,
				QuotationId ,
				ServiceRateId ,
				ItemSavedOriginalRateId
		FROM    ( SELECT    DayNo ,
							ServiceCategory_Desc ,
							QuotationDataQuoteItem.QuotationId ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
								 ELSE 1
							END AS nf ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightFromCityId
									  )
								 ELSE NULL
							END AS StartCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight'
								 THEN ( SELECT  City
										FROM    dbo.tbCity
										WHERE   CityId = dbo.SupplierServiceMaster.FlightToCityId
									  )
								 ELSE NULL
							END AS EndCity ,
							CASE WHEN ServiceCategory_Desc = 'Flight' THEN Airline
								 ELSE ServiceCategory_Desc
							END AS Airline ,
							FlightFromCityId AS StartCityId ,
							FlightToCityId AS EndCityId ,
							USPrice ,
							LocalPrice ,
							dbo.SupplierServiceMaster.ServiceMasterId ,
							ServiceMasterName ,
							CAST(SupplierServiceRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
							dbo.SupplierServiceRate.IsPax ,
							CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId
				  FROM      dbo.QuotationData
							LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
							LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
							AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
							LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
							AND  dbo.SupplierServiceRate.markettype ='Worldwide'
							AND  ? BETWEEN dbo.SupplierServiceRate.StartDefaultCapacity AND dbo.SupplierServiceRate.EndDefaultCapacity
							LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
							LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
							LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
							LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				  WHERE     Combineflag = 1
							AND ServiceCategory_Desc IN ( 'Boat', 'Train', 'Balloon' )

				) AS tmp
		WHERE   tmp.QuotationId = ?
		ORDER BY tmp.DayNo

		 ";

		unset($params);
		$params[] = $columnNo;
		$params[]= $pax;
		$params[] = "$quotationId";

//		echo $sql."\n";
//		print_r($params);
//		exit();

		$rst61 = $this->db->query($sql , $params);

		while($row = $rst61->fetch())
		{
			//var_dump($row);
			$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
			$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

			if($row->startCity == 'Bangkok'){
				$startCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$startCity = $row->startCity;
			}


			if($row->endCity == 'Bangkok'){
				$endCity = 'BKK - Suvarnabhumi Airport';
			}else{
				$endCity = $row->endCity;
			}

			$sqlinsert = "
			SET NOCOUNT  ON
			DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
				SET @FBIdUniqueId = NEWID()

			INSERT  INTO dbo.tbFlightBookings
					 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
					 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
					 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId ,ServiceRateId,Pax)
					 Values(?,@FBIdUniqueId,?,?,?,?,
					 		?,?,?,cast(? as DATETIME),GETDATE(),?,
							GETDATE(),?,?,?,?,?,?,?)


			SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

			unset($params);
			$params[] = $FBId;
			$params[] = $tourId;
			$params[] = $confirmationsId;
			$params[] = $row->airline;
			$params[] = $startCity;

			$params[] = $row->SFcode;
			$params[] = $endCity;
			$params[] = $row->EFcode;
			$params[] = $FlightDate;
			$params[] = $row->nf;

			$params[] = "$quotationId";
			$params[] = $row->USPrice;
			$params[] = $cby;
			$params[] = $row->StartCityId;
			$params[] = $row->EndCityId;
			$params[] = $row->ServiceRateId;
			$params[] = $pax;

			$rst62 = $this->db->query($sqlinsert , $params);

			//echo $sqlinsert ."<hr />";
			//print_r($params);
			//return false;

			if($row2 = $rst62->fetch() and !empty($row->ServiceRateId)){
				$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
				$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$row->ServiceRateId,$pax,$tourId,$columnNo);
			}
		}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackVehicleAndOther($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	// Vehicle
	$sql = "SELECT   DayNo ,
					QuotationDataQuoteItem.QuotationId ,
					CAST(SupplierServiceContract.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
					CAST(dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
					CAST(dbo.SupplierServiceContract.CompanyId AS VARCHAR(36)) AS CompanyId ,
					dbo.SupplierServiceMaster.FlightFromCityId AS StartCity ,
					dbo.SupplierServiceMaster.FlightToCityId AS EndCity ,
					USPrice ,
					LocalPrice ,
					Currency ,
					ServiceMasterName ,
					ServiceCategory_Desc ,
					dbo.SupplierServiceRate.IsPax ,
					CAST(dbo.QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId,
					QuotationDataQuoteItemOriginalRate.ParentServiceRateId
		   FROM     dbo.QuotationData
					LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
					LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
					AND dbo.QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
					LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
					LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
					LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
					LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
					LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
		   WHERE    ServiceCategory_Desc IN ( 'Vehicle' )
					AND Combineflag = 1
					AND QuotationDataQuoteItem.QuotationId = ?
		   ORDER BY DayNo ";


	unset($params);
	$params[]= $columnNo;
	$params[] = "$quotationId";

	//echo $sql;
	//print_r($params);

	$rst71 = $this->db->query($sql , $params);

	$count = 1;
	while($row = $rst71->fetch())
	{
		$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
		$sql = "SET NOCOUNT  ON
				DECLARE @vbid AS UNIQUEIDENTIFIER
				SET @vbid = NEWID()

				INSERT INTO dbo.VehicleBooking
				( VBID,DayNo , Position, OnDay,
				  BookDate , Tourid , QuotationId ,
				  ConfirmationsId ,  QuotationDataQuoteItemId,  Price,
				  CompanyId,ServiceRateId,ServiceContractId,StartCity,EndCity,isShow,Status,pax,
				  ParentServiceRateId
				)
				Values(@vbid,?,?,cast(? as DATETIME),
					   GETDATE(),?,?,
					   ?,?,?,
					   ?,?,?,?,?,1,'PD',?,?)

				SELECT cast(@vbid as varchar(36)) as vbid ";


		unset($params);
		$params[] = $row->DayNo;
		$params[] = $count++;
		$params[] = $OnDay;

		$params[] = $tourId;
		$params[] = $desc_val; // QuotationId

		$params[] = $confirmationsId;
		$params[] = $row->QuotationDataQuoteItemId;
		$params[] = $row->USPrice;

		$params[] = $row->CompanyId;
		$params[] = $row->ServiceRateId;
		$params[] = $row->ServiceContractId;
		$params[] = $row->StartCity;
		$params[] = $row->EndCity;
		$params[] = $pax;
		$params[] = $row->ParentServiceRateId;

		$rst72 = $this->db->query($sql , $params);

		//echo "<hr/>Track Vehicle not combine";
		//echo $sql;
		//print_r($params);
		//echo "<hr/>";

		if($row2 = $rst72->fetch() and !empty($row->ServiceRateId)){
			$this->SaveCostSelling($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostQuoted($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax,$tourId,$columnNo);
			//$this->SaveCostConfirmed($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

	// Check combine of Vehicle


	// Track Other
	/*
	Activity
	Package
	Helicopter
	Miscellaneous
	Meal
	Hotel
	Guide
	*/
	try{
	$sql = "
	SELECT  DayNo ,
			QuotationDataQuoteItem.QuotationId ,
			CAST(QuotationDataQuoteItem.QuotationDataQuoteItemId AS VARCHAR(36)) AS QuotationDataQuoteItemId ,
			CAST(QuotationDataQuoteItem.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
			CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
			CAST(dbo.SupplierServiceContract.CompanyId AS VARCHAR(36)) AS CompanyId ,
			USPrice ,
			LocalPrice ,
			Currency ,
			dbo.SupplierServiceRate.IsPax ,
			ServiceCategory_Desc ,
			CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId ,
			Combineflag,
			ServiceCategory_Desc ,
			CAST(dbo.ServiceCategory.Id AS VARCHAR(36)) AS CategoryId
	FROM    dbo.QuotationData
			LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
			LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
				AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
			LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
			LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceRate.ServiceRateId = dbo.QuotationDataQuoteItemOriginalRate.ServiceRateId
			LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
			LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
			LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
	WHERE   Combineflag = 1
			AND QuotationDataQuoteItem.QuotationId = ?
			AND dbo.ServiceCategory.Id NOT IN (
			'92D2F393-942D-4437-B432-31EF282A1214',
			'3926324A-C7CC-4CEA-91AF-3ABF00E9CE7F',
			'F54AC04A-B481-4BF4-A58A-3C2D5898C65F',
			'26E99294-233D-4901-8FE4-3EF0CA43546A',
			'0C71BBF8-F636-4F77-86BD-7C0BA55B07C3',
			'E386DE20-D0A7-4FF5-A80C-E3D3542E4344' 
			)
			ORDER BY DayNo  ";
	unset($params);
	$params[] = $columnNo;
	$params[] = "$quotationId";
/*
	echo "<hr/>";
	echo $sql;
	print_r($params);
	echo "<hr/>";
	//exit();
*/
	$rst73 = $this->db->query($sql , $params);
	$count = 1;
	while($row3 = $rst73->fetch())
	{
		$OnDay	= $this->DateAdd( "d" , $row3->DayNo - 1  , $dateFrom);
		$MiscStatus = "OK";
		//var_dump($row->CategoryId);
		if($row3->CategoryId == "91D6946C-A5FC-4880-93FB-44B65566A2E5" 
				or $row3->CategoryId == "F2F97670-8F7E-44D1-BEF2-052FD5F95AE1"){
			$MiscStatus = "PD";
		}
		$sql = "
				SET NOCOUNT  ON
				DECLARE @mobid AS UNIQUEIDENTIFIER
				SET @mobid = NEWID()

				INSERT INTO dbo.MisceOtherBooking
				( MOBId,OnDay , BookDate , TourId ,
				   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
				   Price , CompanyId , ServiceContractId ,
				   ServiceRateId , cby  , Status)
					VALUES  (@mobid,?,GETDATE(),?,
					  		  ?,?,?,
					   		  ?,?,?,
					   		  ?,?,'$MiscStatus')
				SELECT cast(@mobid as varchar(36)) as mobid
							  	";

		unset($params);
		$params[] = $OnDay;
		$params[] = $tourId;

		$params[] = $desc_val;
		$params[] = $confirmationsId;
		$params[] = $row3->QuotationDataQuoteItemId;

		$params[] = $row3->USPrice;
		$params[] = $row3->CompanyId;
		$params[] = $row3->ServiceContractId;


		$params[] = $row3->ServiceRateId;
		$params[] = $cby;

		//echo "<hr/>";
		//echo $sql;
		//print_r($params);
		//echo "<hr/>";
		$rst74 = $this->db->query($sql , $params);

		if($row4 = $rst74->fetch() and !empty($row3->ServiceRateId)){
			$this->SaveCostQuoted($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax ,$quotationId,$tourId,$row3->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax,$tourId,$columnNo);
			$this->SaveCostConfirmed($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackVehicleAndOtherPlus($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo)
{
	try{
	// Vehicle
	$sql = "SELECT   	DayNo ,
          QuotationDataQuoteItem.QuotationId ,
          CAST(SupplierServiceContract.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
          CAST(dbo.SupplierServiceRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
          CAST(dbo.SupplierServiceContract.CompanyId AS VARCHAR(36)) AS CompanyId ,
          dbo.SupplierServiceMaster.FlightFromCityId AS StartCity ,
          dbo.SupplierServiceMaster.FlightToCityId AS EndCity ,
          USPrice ,
          LocalPrice ,
          Currency ,
          ServiceMasterName ,
          ServiceCategory_Desc ,
          dbo.SupplierServiceRate.IsPax ,
          CAST(dbo.QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId,
          QuotationDataQuoteItemOriginalRate.ParentServiceRateId
FROM     dbo.QuotationData
          LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
          LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
          AND dbo.QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
          LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
          LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceContract.ServiceContractId = dbo.SupplierServiceRate.ServiceContractId
          AND  dbo.SupplierServiceRate.markettype ='Worldwide'
          AND  ? BETWEEN dbo.SupplierServiceRate.StartDefaultCapacity AND dbo.SupplierServiceRate.EndDefaultCapacity
          LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
          LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
          LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
WHERE    ServiceCategory_Desc IN ( 'Vehicle' )
  AND Combineflag = 1
  AND QuotationDataQuoteItem.QuotationId = ?
ORDER BY DayNo    ";


	unset($params);
	$params[]= $columnNo;
	$params[]= $pax;
	$params[] = "$quotationId";

	// echo $sql;
	// print_r($params);

	$rst71 = $this->db->query($sql , $params);

	$count = 1;
	while($row = $rst71->fetch())
	{
		$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
		$sql = "SET NOCOUNT  ON
				DECLARE @vbid AS UNIQUEIDENTIFIER
				SET @vbid = NEWID()

				INSERT INTO dbo.VehicleBooking
				( VBID,DayNo , Position, OnDay,
				  BookDate , Tourid , QuotationId ,
				  ConfirmationsId ,  QuotationDataQuoteItemId,  Price,
				  CompanyId,ServiceRateId,ServiceContractId,StartCity,EndCity,isShow,Status,pax,ParentServiceRateId
				)
				Values(@vbid,?,?,cast(? as DATETIME),
					   GETDATE(),?,?,
					   ?,?,?,
					   ?,?,?,?,?,1,'PD',?,?)

				SELECT cast(@vbid as varchar(36)) as vbid ";


		unset($params);
		$params[] = $row->DayNo;
		$params[] = $count++;
		$params[] = $OnDay;

		$params[] = $tourId;
		$params[] = $desc_val; // QuotationId

		$params[] = $confirmationsId;
		$params[] = $row->QuotationDataQuoteItemId;
		$params[] = $row->USPrice;

		$params[] = $row->CompanyId;
		$params[] = $row->ServiceRateId;
		$params[] = $row->ServiceContractId;
		$params[] = $row->StartCity;
		$params[] = $row->EndCity;
		$params[] = $pax;
		$params[] = $row->ParentServiceRateId;

		$rst72 = $this->db->query($sql , $params);

		// echo "<hr/>Track Vehicle not combine";
		// echo $sql;
		// print_r($params);
		// echo "<hr/>";

		if($row2 = $rst72->fetch() and !empty($row->ServiceRateId)){
			$this->SaveCostSelling($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostQuoted($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax ,$quotationId,$tourId,$row->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax,$tourId,$columnNo);
			//$this->SaveCostConfirmed($confirmationsId,$row2->vbid,$row->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

	// Check combine of Vehicle


	// Track Other
	/*
	Activity
	Package
	Helicopter
	Miscellaneous
	Meal
	Hotel
	Guide
	*/
	try{
	$sql = "SELECT  DayNo ,
			QuotationDataQuoteItem.QuotationId ,
			CAST(QuotationDataQuoteItem.QuotationDataQuoteItemId AS VARCHAR(36)) AS QuotationDataQuoteItemId ,
			CAST(QuotationDataQuoteItem.ServiceContractId AS VARCHAR(36)) AS ServiceContractId ,
			CAST(QuotationDataQuoteItemOriginalRate.ServiceRateId AS VARCHAR(36)) AS ServiceRateId ,
			CAST(dbo.SupplierServiceContract.CompanyId AS VARCHAR(36)) AS CompanyId ,
			USPrice ,
			LocalPrice ,
			Currency ,
			dbo.SupplierServiceRate.IsPax ,
			ServiceCategory_Desc ,
			CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId ,
			Combineflag,
			ServiceCategory_Desc ,
			CAST(dbo.ServiceCategory.Id AS VARCHAR(36)) AS CategoryId
	FROM    dbo.QuotationData
			LEFT JOIN dbo.QuotationDataQuoteItem ON dbo.QuotationData.QuotationDataId = dbo.QuotationDataQuoteItem.QuotationDataId
			LEFT JOIN dbo.QuotationDataQuoteItemOriginalRate ON dbo.QuotationDataQuoteItem.QuotationDataQuoteItemId = dbo.QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
				AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
			LEFT JOIN dbo.SupplierServiceContract ON dbo.QuotationDataQuoteItem.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
          LEFT JOIN dbo.SupplierServiceRate ON dbo.SupplierServiceContract.ServiceContractId = dbo.SupplierServiceRate.ServiceContractId
          AND  dbo.SupplierServiceRate.markettype ='Worldwide'
          AND  ? BETWEEN dbo.SupplierServiceRate.StartDefaultCapacity AND dbo.SupplierServiceRate.EndDefaultCapacity
			LEFT JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
			LEFT JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
			LEFT JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
	WHERE   Combineflag = 1
			AND QuotationDataQuoteItem.QuotationId = ?
			AND dbo.ServiceCategory.Id NOT IN (
			'92D2F393-942D-4437-B432-31EF282A1214',
			'3926324A-C7CC-4CEA-91AF-3ABF00E9CE7F',
			'F54AC04A-B481-4BF4-A58A-3C2D5898C65F',
			'26E99294-233D-4901-8FE4-3EF0CA43546A',
			'0C71BBF8-F636-4F77-86BD-7C0BA55B07C3',
			'E386DE20-D0A7-4FF5-A80C-E3D3542E4344' 
			)
			ORDER BY DayNo  ";
	unset($params);
	$params[] = $columnNo;
	$params[] = $pax;
	$params[] = "$quotationId";

	//echo "<hr/>";
	//echo $sql;
	//print_r($params);
	//echo "<hr/>";
	//exit();

	$rst73 = $this->db->query($sql , $params);
	$count = 1;
	while($row3 = $rst73->fetch())
	{
		$OnDay	= $this->DateAdd( "d" , $row3->DayNo - 1  , $dateFrom);
		$MiscStatus = "OK";
		///var_dump($row3->CategoryId);
		if($row3->CategoryId == "91D6946C-A5FC-4880-93FB-44B65566A2E5" 
				or $row3->CategoryId == "F2F97670-8F7E-44D1-BEF2-052FD5F95AE1"){
			$MiscStatus = "PD";
		}
		$sql = "
				SET NOCOUNT  ON
				DECLARE @mobid AS UNIQUEIDENTIFIER
				SET @mobid = NEWID()

				INSERT INTO dbo.MisceOtherBooking
				( MOBId,OnDay , BookDate , TourId ,
				   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
				   Price , CompanyId , ServiceContractId ,
				   ServiceRateId , cby  , Status)
					VALUES  (@mobid,?,GETDATE(),?,
					  		  ?,?,?,
					   		  ?,?,?,
					   		  ?,?,'$MiscStatus')
				SELECT cast(@mobid as varchar(36)) as mobid
							  	";

		unset($params);
		$params[] = $OnDay;
		$params[] = $tourId;

		$params[] = $desc_val;
		$params[] = $confirmationsId;
		$params[] = $row3->QuotationDataQuoteItemId;

		$params[] = $row3->USPrice;
		$params[] = $row3->CompanyId;
		$params[] = $row3->ServiceContractId;


		$params[] = $row3->ServiceRateId;
		$params[] = $cby;

		//echo "<hr/>";
		//echo $sql;
		//print_r($params);
		//echo "<hr/>";
		$rst74 = $this->db->query($sql , $params);

		if($row4 = $rst74->fetch() and !empty($row3->ServiceRateId)){
			$this->SaveCostQuoted($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax ,$quotationId,$tourId,$row3->ItemSavedOriginalRateId,$columnNo);
			$this->SaveCostBooked($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax,$tourId,$columnNo);
			$this->SaveCostConfirmed($confirmationsId,$row4->mobid,$row3->ServiceRateId,$pax,$tourId,$columnNo);
		}
	}

	return true;
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}

}

public function trackCombine($confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo){

	//echo "Track Combine \n $confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo";

	try{
		$sql = "

				SELECT 	sc.ServiceCategory_Desc,
						QuotationData.DayNo,
						QuotationData.QuotationId,
						QuotationDataQuoteItem.QuotationDataQuoteItemId,
						ssc.CompanyId,
						SupplierServiceContract.ServiceContractId,
						SupplierServiceContract.ServiceContractId as ParentServiceContractId,
						ssc.ServiceContractId AS CombineServiceContractId ,
						SupplierServiceContract.Combineflag,
						sc.Id,
						ssm.FlightFromCityId,
						ssm.FlightToCityId,
						QuotationDataQuoteItemOriginalRate.ServiceRateId,
						ssr.IsPax
				FROM 	dbo.QuotationData
						INNER JOIN dbo.QuotationDataQuoteItem ON QuotationData.QuotationDataId = QuotationDataQuoteItem.QuotationDataId
						INNER JOIN SupplierServiceContract ON QuotationDataQuoteItem.ServiceContractId = SupplierServiceContract.ServiceContractId
						INNER JOIN QuotationDataQuoteItemOriginalRate ON QuotationDataQuoteItem.QuotationDataQuoteItemId = QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
						AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
						INNER JOIN SupplierServiceContract ssc ON ssc.ServiceContractId = QuotationDataQuoteItemOriginalRate.ServiceContractId
						INNER JOIN dbo.SupplierServiceMaster AS ssm ON ssc.ServiceMasterId = ssm.ServiceMasterId
						INNER JOIN dbo.ServiceCategory AS sc ON sc.Id = ssm.ServiceCategoryId
						INNER JOIN dbo.SupplierServiceRate as ssr ON ssr.ServiceRateId = QuotationDataQuoteItemOriginalRate.ServiceRateId
				WHERE 	QuotationData.QuotationId = ?
						AND SupplierServiceContract.Combineflag = 0
				ORDER BY QuotationData.DayNo,QuotationDataQuoteItem.Position,SupplierServiceContract.ServiceContractId
				";
		unset($params);
		$params[] = "$columnNo";
		$params[] = "$quotationId";

		//echo "$sql <br />";
		//print_r($params);


		$rs = $this->db->query($sql , $params);
		$count = 1;
		while($row = $rs->fetch()){

			$category = trim($row->ServiceCategory_Desc);

			$CombineServiceContractId = $row->CombineServiceContractId;
			$QuotationDataQuoteItemId = $row->QuotationDataQuoteItemId;
			$ParentServiceContractId = $row->ParentServiceContractId;
			$ServiceRateId = $row->ServiceRateId;
			$IsPax = $row->IsPax;

			$sqlSavedOriginalRate = "
				SELECT  CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId
				FROM    dbo.QuotationDataQuoteItemOriginalRate
				WHERE   QuotationId = ?
						AND ServiceContractId = ?
						AND ColumnPosition = ?
						AND ServiceRateId = ?
						AND QuotationDataQuoteItemId = ? ";

			$params = array();
			$params[] = $quotationId;
			$params[] = $CombineServiceContractId;
			$params[] = $columnNo;
			$params[] = $ServiceRateId;
			$params[] = $QuotationDataQuoteItemId;

			$ItemSavedOriginalRateId = $this->db->fetchOne($sqlSavedOriginalRate,$params);
			if(empty($ItemSavedOriginalRateId )){
				echo "$sqlSavedOriginalRate <br />";
				print_r($params);
			}

			$sqlRateId = "	SELECT  ServiceRateId
							FROM    dbo.SupplierServiceRate
							WHERE   ServiceContractId = ?
									AND ? BETWEEN StartDefaultCapacity AND EndDefaultCapacity
									AND markettype ='Worldwide'
									AND IsPax = ?
						";
			$params = array();
			$params[] = $CombineServiceContractId;
			$params[] = $pax;
			$params[] = $IsPax;

			//echo "$sqlRateId <br />";
			//print_r($params);

			$serviceRateId = $this->db->fetchOne($sqlRateId,$params);

/*			if(empty($serviceRateId )){
				echo "$sqlRateId <br />";
				print_r($params);
			}
*/

			$serviceRateIdInsert = !empty($serviceRateId)?"'$serviceRateId'":"NULL";

			$x++;

			if($category =="Entrance Fee"){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @ebid AS UNIQUEIDENTIFIER
						SET @ebid = NEWID()

						INSERT INTO dbo.EntranceFeeBooking
						( EBId,OnDay , BookDate , TourId ,
						   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
						   CompanyId , ServiceContractId ,ServiceRateId , cby , Status)
							VALUES  (@ebid, ?,GETDATE(),?,
									  ?,?,?,
									  ?,?,$serviceRateIdInsert,?,'OK')

						select cast(@ebid as varchar(36)) as ebid

						 ";

				unset($params);
				$params = array();
				$params[] = $OnDay;
				$params[] = $tourId;

				$params[] = "$quotationId";
				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;

				$params[] = $row->CompanyId;
				$params[] = $CombineServiceContractId;

				$params[] = $cby ;

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rs2 = $this->db->query($sql , $params);


				if($row2 = $rs2->fetch() and !empty($serviceRateId))
				{
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->ebid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->ebid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->ebid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
					$this->SaveCostConfirmed($confirmationsId,$row2->ebid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}


			}elseif($category=='Flight' or $category=='Boat' or $category== 'Train' or $category=='Balloon'){

				$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
				$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

				$sql = "
				SET NOCOUNT  ON
				DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
					SET @FBIdUniqueId = NEWID()

				INSERT  INTO dbo.tbFlightBookings
						 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
						 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
						 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId , ServiceContractId , ServiceRateId)
						 Values(?,@FBIdUniqueId,?,?,?,?,
								?,?,?,cast(? as DATETIME),GETDATE(),?,
								GETDATE(),?,?,?,?,?,?,$serviceRateIdInsert)

				UPDATE  dbo.tbFlightBookings
				SET     Airline = dbo.tbAirline.Airline ,
						FlightFrom = ( SELECT   City
									   FROM     dbo.tbCity AS c1
									   WHERE    c1.CityId = FlightFromCityId
									 ) ,
						FromCode = ( SELECT Fcode
									 FROM   dbo.tbFlightDestination
									 WHERE  Destination = ( SELECT  City
															FROM    dbo.tbCity AS c1
															WHERE   c1.CityId = FlightFromCityId
														  )
								   ) ,
						FlightTo = ( SELECT City
									 FROM   dbo.tbCity AS c1
									 WHERE  c1.CityId = FlightToCityId
								   ) ,
						toCode = ( SELECT   Fcode
								   FROM     dbo.tbFlightDestination
								   WHERE    Destination = ( SELECT  City
															FROM    dbo.tbCity AS c1
															WHERE   c1.CityId = FlightToCityId
														  )
								 ) ,
						nf = ( CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
									ELSE 1
							   END )
				FROM    dbo.tbFlightBookings
						INNER JOIN dbo.SupplierServiceContract ON dbo.tbFlightBookings.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
						INNER JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
						INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
						INNER JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
						LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				WHERE   FBIdUniqueId = @FBIdUniqueId

				SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

				unset($params);
				$params =array();
				$params[] = $FBId;
				$params[] = $tourId;
				$params[] = $confirmationsId;
				$params[] = "";
				$params[] = "";

				$params[] = "";
				$params[] = "";
				$params[] = "";
				$params[] = $FlightDate;
				$params[] = "";

				$params[] = "$quotationId";
				$params[] = "";
				$params[] = $cby;
				$params[] = $row->FlightFromCityId;
				$params[] = $row->FlightToCityId;
				$params[] = $CombineServiceContractId;

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rst62 = $this->db->query($sql , $params);

				if($row2 = $rst62->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}

			}elseif($category=="Vehicle"){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @vbid AS UNIQUEIDENTIFIER
						SET @vbid = NEWID()

						INSERT INTO dbo.VehicleBooking
						( VBID,DayNo ,  OnDay,
						  BookDate , Tourid , QuotationId ,
						  ConfirmationsId ,  QuotationDataQuoteItemId,  Price,
						  CompanyId,ServiceRateId,ServiceContractId,StartCity,EndCity,isShow,status
						)
						Values(@vbid,?,cast(? as DATETIME),
							   GETDATE(),?,?,
							   ?,?,?,
							   ?,$serviceRateIdInsert,?,?,?,1,'PD')

						SELECT cast(@vbid as varchar(36)) as vbid ";


				unset($params);
				$params = array();
				$params[] = $row->DayNo;
				$params[] = $OnDay;

				$params[] = $tourId;
				$params[] = "$quotationId"; // QuotationId

				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;
				$params[] = "";

				$params[] = $row->CompanyId;

				$params[] = $CombineServiceContractId;
				$params[] = "";
				$params[] = "";

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rst72 = $this->db->query($sql , $params);

				if($row2 = $rst72->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->vbid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->vbid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->vbid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}

			}elseif(!($category=="Vehicle" or $category=="Flight" or $category=="Entrance Fee" or $category=="Train" or $category=="Hotel" or $category=="Boat" or $category=="Balloon" )){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @mobid AS UNIQUEIDENTIFIER
						SET @mobid = NEWID()

						INSERT INTO dbo.MisceOtherBooking
						( MOBId,OnDay , BookDate , TourId ,
						   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
						   Price , CompanyId , ServiceContractId ,
						   ServiceRateId , cby , Status)
							VALUES  (@mobid,?,GETDATE(),?,
									  ?,?,?,
									  ?,?,?,
									  $serviceRateIdInsert,?,'OK')
						SELECT cast(@mobid as varchar(36)) as mobid
										";

				unset($params);
				$params = array();
				$params[] = $OnDay;
				$params[] = $tourId;

				$params[] = "$quotationId";
				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;

				$params[] = "";
				$params[] = $row->CompanyId;
				$params[] = $CombineServiceContractId;

				$params[] = $cby;

				$rst74 = $this->db->query($sql , $params);

				//echo "<h1>$x -> $category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";


				if($row4 = $rst74->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row4->mobid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row4->mobid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row4->mobid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
					$this->SaveCostConfirmed($confirmationsId,$row4->mobid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}
			}

		}

	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function trackCombineByServiceType($confirmationsId, $tourId, $quotationId, $dateFrom, $dateTo, $cby, $pax, $columnNo, $serviceType){

	//echo "Track Combine \n $confirmationsId,$tourId,$quotationId,$dateFrom,$dateTo,$cby,$pax,$columnNo";

	try{
		$sql = "

				SELECT 	sc.ServiceCategory_Desc,
						QuotationData.DayNo,
						QuotationData.QuotationId,
						QuotationDataQuoteItem.QuotationDataQuoteItemId,
						ssc.CompanyId,
						SupplierServiceContract.ServiceContractId,
						SupplierServiceContract.ServiceContractId as ParentServiceContractId,
						ssc.ServiceContractId AS CombineServiceContractId ,
						SupplierServiceContract.Combineflag,
						sc.Id,
						ssm.FlightFromCityId,
						ssm.FlightToCityId,
						QuotationDataQuoteItemOriginalRate.ServiceRateId,
						ssr.IsPax
				FROM 	dbo.QuotationData
						INNER JOIN dbo.QuotationDataQuoteItem ON QuotationData.QuotationDataId = QuotationDataQuoteItem.QuotationDataId
						INNER JOIN SupplierServiceContract ON QuotationDataQuoteItem.ServiceContractId = SupplierServiceContract.ServiceContractId
						INNER JOIN QuotationDataQuoteItemOriginalRate ON QuotationDataQuoteItem.QuotationDataQuoteItemId = QuotationDataQuoteItemOriginalRate.QuotationDataQuoteItemId
						AND QuotationDataQuoteItemOriginalRate.ColumnPosition = ?
						INNER JOIN SupplierServiceContract ssc ON ssc.ServiceContractId = QuotationDataQuoteItemOriginalRate.ServiceContractId
						INNER JOIN dbo.SupplierServiceMaster AS ssm ON ssc.ServiceMasterId = ssm.ServiceMasterId
						INNER JOIN dbo.ServiceCategory AS sc ON sc.Id = ssm.ServiceCategoryId
						INNER JOIN dbo.SupplierServiceRate as ssr ON ssr.ServiceRateId = QuotationDataQuoteItemOriginalRate.ServiceRateId
				WHERE 	QuotationData.QuotationId = ?
						AND SupplierServiceContract.Combineflag = 0
				ORDER BY QuotationData.DayNo,QuotationDataQuoteItem.Position,SupplierServiceContract.ServiceContractId
				";
		unset($params);
		$params[] = "$columnNo";
		$params[] = "$quotationId";

		#echo "$sql, $columnNo, $quotationId, $serviceType <br />";
		//print_r($params);


		$rs = $this->db->query($sql , $params);
		$count = 1;
		while($row = $rs->fetch()){

			$category = trim($row->ServiceCategory_Desc);

			$CombineServiceContractId = $row->CombineServiceContractId;
			$QuotationDataQuoteItemId = $row->QuotationDataQuoteItemId;
			$ParentServiceContractId = $row->ParentServiceContractId;
			$ServiceRateId = $row->ServiceRateId;
			$IsPax = $row->IsPax;

			$sqlSavedOriginalRate = "
				SELECT  CAST(QuotationDataQuoteItemOriginalRate.Id AS VARCHAR(36)) AS ItemSavedOriginalRateId
				FROM    dbo.QuotationDataQuoteItemOriginalRate
				WHERE   QuotationId = ?
						AND ServiceContractId = ?
						AND ColumnPosition = ?
						AND ServiceRateId = ?
						AND QuotationDataQuoteItemId = ? ";

			$params = array();
			$params[] = $quotationId;
			$params[] = $CombineServiceContractId;
			$params[] = $columnNo;
			$params[] = $ServiceRateId;
			$params[] = $QuotationDataQuoteItemId;

			$ItemSavedOriginalRateId = $this->db->fetchOne($sqlSavedOriginalRate,$params);
			if(empty($ItemSavedOriginalRateId )){
				echo "$sqlSavedOriginalRate <br />";
				print_r($params);
			}

			$sqlRateId = "	SELECT  ServiceRateId
							FROM    dbo.SupplierServiceRate
							WHERE   ServiceContractId = ?
									AND ? BETWEEN StartDefaultCapacity AND EndDefaultCapacity
									AND markettype ='Worldwide'
									AND IsPax = ?
						";
			$params = array();
			$params[] = $CombineServiceContractId;
			$params[] = $pax;
			$params[] = $IsPax;

			//echo "$sqlRateId <br />";
			//print_r($params);

			$serviceRateId = $this->db->fetchOne($sqlRateId,$params);
/*
			if(empty($serviceRateId )){
				echo "$sqlRateId <br />";
				print_r($params);
			}
*/

			$serviceRateIdInsert = !empty($serviceRateId)?"'$serviceRateId'":"NULL";

			$x++;

			if($category =="Entrance Fee" and $serviceType == "Entrance Fee"){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @ebid AS UNIQUEIDENTIFIER
						SET @ebid = NEWID()

						INSERT INTO dbo.EntranceFeeBooking
						( EBId,OnDay , BookDate , TourId ,
						   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
						   CompanyId , ServiceContractId ,ServiceRateId , cby, Status)
							VALUES  (@ebid, ?,GETDATE(),?,
									  ?,?,?,
									  ?,?,$serviceRateIdInsert,?,'OK')

						select cast(@ebid as varchar(36)) as ebid

						 ";

				unset($params);
				$params = array();
				$params[] = $OnDay;
				$params[] = $tourId;

				$params[] = "$quotationId";
				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;

				$params[] = $row->CompanyId;
				$params[] = $CombineServiceContractId;

				$params[] = $cby ;

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rs2 = $this->db->query($sql , $params);


				if($row2 = $rs2->fetch() and !empty($serviceRateId))
				{
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->ebid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->ebid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->ebid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
					$this->SaveCostConfirmed($confirmationsId,$row2->ebid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}


			}elseif(($category=='Flight' or $category=='Boat' or $category== 'Train' or $category=='Balloon') and ($serviceType=='Flight' or $serviceType=='NoFlight')){

				$FBId =  $this->GetAutoKey("tbFlightBookings", "FBId" ) ;
				$FlightDate	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);

				$sql = "
				SET NOCOUNT  ON
				DECLARE @FBIdUniqueId AS UNIQUEIDENTIFIER
					SET @FBIdUniqueId = NEWID()

				INSERT  INTO dbo.tbFlightBookings
						 (FBId , FBIdUniqueId , TourId ,  ConfirmationsId ,  Airline , FlightFrom ,
						 FromCode , FlightTo ,  toCode , FlightDate ,  bookingdate ,  nf ,
						 cdate , QuotationId , Price ,  cby ,  FromCityId , ToCityId , ServiceContractId , ServiceRateId)
						 Values(?,@FBIdUniqueId,?,?,?,?,
								?,?,?,cast(? as DATETIME),GETDATE(),?,
								GETDATE(),?,?,?,?,?,?,$serviceRateIdInsert)

				UPDATE  dbo.tbFlightBookings
				SET     Airline = dbo.tbAirline.Airline ,
						FlightFrom = ( SELECT   City
									   FROM     dbo.tbCity AS c1
									   WHERE    c1.CityId = FlightFromCityId
									 ) ,
						FromCode = ( SELECT Fcode
									 FROM   dbo.tbFlightDestination
									 WHERE  Destination = ( SELECT  City
															FROM    dbo.tbCity AS c1
															WHERE   c1.CityId = FlightFromCityId
														  )
								   ) ,
						FlightTo = ( SELECT City
									 FROM   dbo.tbCity AS c1
									 WHERE  c1.CityId = FlightToCityId
								   ) ,
						toCode = ( SELECT   Fcode
								   FROM     dbo.tbFlightDestination
								   WHERE    Destination = ( SELECT  City
															FROM    dbo.tbCity AS c1
															WHERE   c1.CityId = FlightToCityId
														  )
								 ) ,
						nf = ( CASE WHEN ServiceCategory_Desc = 'Flight' THEN 0
									ELSE 1
							   END )
				FROM    dbo.tbFlightBookings
						INNER JOIN dbo.SupplierServiceContract ON dbo.tbFlightBookings.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
						INNER JOIN dbo.SupplierServiceMaster ON dbo.SupplierServiceMaster.ServiceMasterId = dbo.SupplierServiceContract.ServiceMasterId
						INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.SupplierServiceMaster.ServiceCategoryId
						INNER JOIN dbo.Currency ON dbo.Currency.Id = dbo.SupplierServiceContract.CurrencyId
						LEFT JOIN dbo.tbAirline ON dbo.tbAirline.AirlineId = dbo.SupplierServiceMaster.AirlineId
				WHERE   FBIdUniqueId = @FBIdUniqueId

				SELECT cast(@FBIdUniqueId as varchar(36)) as FBIdUniqueId ";

				unset($params);
				$params =array();
				$params[] = $FBId;
				$params[] = $tourId;
				$params[] = $confirmationsId;
				$params[] = "";
				$params[] = "";

				$params[] = "";
				$params[] = "";
				$params[] = "";
				$params[] = $FlightDate;
				$params[] = "";

				$params[] = "$quotationId";
				$params[] = "";
				$params[] = $cby;
				$params[] = $row->FlightFromCityId;
				$params[] = $row->FlightToCityId;
				$params[] = $CombineServiceContractId;

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rst62 = $this->db->query($sql , $params);

				if($row2 = $rst62->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->FBIdUniqueId,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}

			}elseif($category=="Vehicle" and $serviceType == "Vehicle"){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @vbid AS UNIQUEIDENTIFIER
						SET @vbid = NEWID()

						INSERT INTO dbo.VehicleBooking
						( VBID,DayNo ,  OnDay,
						  BookDate , Tourid , QuotationId ,
						  ConfirmationsId ,  QuotationDataQuoteItemId,  Price,
						  CompanyId,ServiceRateId,ServiceContractId,StartCity,EndCity,isShow,status
						)
						Values(@vbid,?,cast(? as DATETIME),
							   GETDATE(),?,?,
							   ?,?,?,
							   ?,$serviceRateIdInsert,?,?,?,1,'PD')

						SELECT cast(@vbid as varchar(36)) as vbid ";

				#echo "$sql $onDay <hr/>";

				unset($params);
				$params = array();
				$params[] = $row->DayNo;
				$params[] = $OnDay;

				$params[] = $tourId;
				$params[] = "$quotationId"; // QuotationId

				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;
				$params[] = "";

				$params[] = $row->CompanyId;

				$params[] = $CombineServiceContractId;
				$params[] = "";
				$params[] = "";

				//echo "<h1>$x ->$category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";

				$rst72 = $this->db->query($sql , $params);

				if($row2 = $rst72->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row2->vbid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row2->vbid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row2->vbid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}

			}elseif(!($category=="Vehicle" or $category=="Flight" or $category=="Entrance Fee" or $category=="Train" or $category=="Hotel" or $category=="Boat" or $category=="Balloon" ) and ($serviceType == "Other")){

				$OnDay	= $this->DateAdd( "d" , $row->DayNo - 1  , $dateFrom);
				$sql = "SET NOCOUNT  ON
						DECLARE @mobid AS UNIQUEIDENTIFIER
						SET @mobid = NEWID()

						INSERT INTO dbo.MisceOtherBooking
						( MOBId,OnDay , BookDate , TourId ,
						   QuotationId , ConfirmationsId , QuotationDataQuoteItemId ,
						   Price , CompanyId , ServiceContractId ,
						   ServiceRateId , cby, Status   )
							VALUES  (@mobid,?,GETDATE(),?,
									  ?,?,?,
									  ?,?,?,
									  $serviceRateIdInsert,?,'OK')
						SELECT cast(@mobid as varchar(36)) as mobid
										";

				unset($params);
				$params = array();
				$params[] = $OnDay;
				$params[] = $tourId;

				$params[] = "$quotationId";
				$params[] = $confirmationsId;
				$params[] = $row->QuotationDataQuoteItemId;

				$params[] = "";
				$params[] = $row->CompanyId;
				$params[] = $CombineServiceContractId;

				$params[] = $cby;

				$rst74 = $this->db->query($sql , $params);

				//echo "<h1>$x -> $category</h1>";
				//echo $sql;
				//print_r($params);
				//echo "<hr/>";


				if($row4 = $rst74->fetch()and !empty($serviceRateId)){
					//echo "EBID =" . $row2->ebid." \n  ".$ItemSavedOriginalRateId;
					$this->SaveCostSelling($confirmationsId,$row4->mobid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostQuoted($confirmationsId,$row4->mobid,$serviceRateId,$pax ,$quotationId,$tourId,$ItemSavedOriginalRateId,$columnNo,$ParentServiceContractId);
					$this->SaveCostBooked($confirmationsId,$row4->mobid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
					$this->SaveCostConfirmed($confirmationsId,$row4->mobid,$serviceRateId,$pax,$tourId,$columnNo,$ParentServiceContractId);
				}
			}

		}

	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}



public function SaveCostSelling($confirmationsId,$ReferanceId,$ServiceRateId,$Pax,$QuotationId,$tourId, $QuotationDataQuoteItemSavedOriginalRateId="", $columnNo, $ParentServiceContractId='')
{
	if(empty($ParentServiceContractId))
		$ParentServiceContractId= "NULL";
	else
		$ParentServiceContractId = "'$ParentServiceContractId'";

	try{
	$sqlid = "	DECLARE @Id UNIQUEIDENTIFIER
				SET @Id = NEWID()
				select @Id as BookingRateCostId ";

	$BookingRateCostId = $this->db->fetchOne($sqlid);

	$sql = "
			INSERT INTO dbo.BookingRateCost
			(
			  confirmationsId,
			  BookingRateCostId,
			  TourId,
			  ReferanceId ,
			  ServiceRateId ,
			  CompanyId ,
			  ServiceContractId ,
			  ServiceUnitTypeId ,
			  StartDefaultCapacity ,
			  EndDefaultCapacity ,
			  Price ,
			  IsPax ,
			  markettype ,
			  CurrencyId ,
			  Currency ,
			  CurrencyRate ,
			  Pax ,
			  CostTypeId ,
			  QuotationId ,
			  QuotationDataQuoteItemId ,
			  QuotationDataQuoteItemSavedOriginalRateId,
			  NoOfUse,
			  ParentServiceContractId
			)
		SELECT  '$confirmationsId',
				'$BookingRateCostId',
				'$tourId',
				'$ReferanceId' ,
				QOR.ServiceRateId ,
				SSR.CompanyId ,
				QOR.ServiceContractId ,
				SSR.ServiceUnitTypeId ,
				StartDefaultCapacity ,
				EndDefaultCapacity ,
				CASE
				  WHEN tbCountry.CountryDesc  = 'Vietnam' THEN  (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentV,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'Cambodia' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentC,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'Laos' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentL,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'Myanmar' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentM,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'Indonesia' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentB,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'Thailand' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentT,0))/100))* ISNULL(LocalPrice,0)
				  WHEN tbCountry.CountryDesc  = 'India' THEN (1+(CONVERT(DECIMAL(10,2),ISNULL(Quotation.PercentI,0))/100))* ISNULL(LocalPrice,0)
				  ELSE 0
				END,
				SSR.IsPax ,
				SSR.markettype ,
				QOR.CurrencyId ,
				c.Currency ,
				c.CurrencyRate ,
				'$Pax' ,
				1 ,
				QOR.QuotationId ,
				QOR.QuotationDataQuoteItemId ,
				QOR.Id,
				ISNULL(ssmv.NoOfUse,1) AS NoOfUse,
				$ParentServiceContractId
		FROM    dbo.QuotationDataQuoteItemOriginalRate QOR
				INNER JOIN Quotation ON QOR.QuotationId = Quotation.QuotationId
				INNER JOIN dbo.SupplierServiceRate SSR ON QOR.ServiceRateId = SSR.ServiceRateId
				INNER JOIN SupplierServiceContract ON QOR.ServiceContractId = SupplierServiceContract.ServiceContractId
				INNER JOIN SupplierServiceMaster ON SupplierServiceContract.ServiceMasterId = SupplierServiceMaster.ServiceMasterId
				INNER JOIN tbCountry ON SupplierServiceMaster.FromCountryId = tbCountry.CountryId
				LEFT JOIN dbo.Currency C ON QOR.CurrencyId = c.Id
				LEFT JOIN dbo.SupplierServicerateMixVehicle AS ssmv ON ssmv.ParentServiceRateId = QOR.ParentServiceRateId AND ssmv.ServiceRateId = QOR.ServiceRateId
		WHERE   QOR.Id = ? ";

	$stmt = $this->db->prepare($sql);
	$params = array();
	$params[] = $QuotationDataQuoteItemSavedOriginalRateId ;
	$stmt->execute($params);

	//echo $sql;
	//print_r($params);
	//echo"<hr/>";
	// spcial for quote

	//$this->SaveSpecialChargePrice($confirmationsId,$BookingRateCostId,$ReferanceId,$ServiceRateId,$Pax,$columnNo,$QuotationId,$isSupplier=2);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function SaveCostQuoted($confirmationsId,$ReferanceId,$ServiceRateId,$Pax,$QuotationId,$tourId,$QuotationDataQuoteItemSavedOriginalRateId="",$columnNo,$ParentServiceContractId='')
{

	if(empty($ParentServiceContractId))
		$ParentServiceContractId= "NULL";
	else
		$ParentServiceContractId = "'$ParentServiceContractId'";

	try{
	$sqlid = "	DECLARE @Id UNIQUEIDENTIFIER
				SET @Id = NEWID()
				select @Id as BookingRateCostId ";

	$BookingRateCostId = $this->db->fetchOne($sqlid);

	$sql = "
			INSERT INTO dbo.BookingRateCost
			(
			  confirmationsId,
			  BookingRateCostId,
			  TourId,
			  ReferanceId ,
			  ServiceRateId ,
			  CompanyId ,
			  ServiceContractId ,
			  ServiceUnitTypeId ,
			  StartDefaultCapacity ,
			  EndDefaultCapacity ,
			  Price ,
			  IsPax ,
			  markettype ,
			  CurrencyId ,
			  Currency ,
			  CurrencyRate ,
			  Pax ,
			  CostTypeId ,
			  QuotationId ,
			  QuotationDataQuoteItemId ,
			  QuotationDataQuoteItemSavedOriginalRateId,
			  NoOfUse,
			  ParentServiceContractId
			)
		SELECT  '$confirmationsId',
				'$BookingRateCostId',
				'$tourId',
				'$ReferanceId' ,
				QOR.ServiceRateId ,
				CompanyId ,
				QOR.ServiceContractId ,
				SSR.ServiceUnitTypeId ,
				StartDefaultCapacity ,
				EndDefaultCapacity ,
				LocalPrice ,
				SSR.IsPax ,
				SSR.markettype ,
				CurrencyId ,
				Currency ,
				CurrencyRate ,
				'$Pax' ,
				2 ,
				QuotationId ,
				QuotationDataQuoteItemId ,
				QOR.Id,
				ISNULL(ssmv.NoOfUse,1) AS NoOfUse,
				$ParentServiceContractId
		FROM    dbo.QuotationDataQuoteItemOriginalRate QOR
				INNER JOIN dbo.SupplierServiceRate SSR ON QOR.ServiceRateId = SSR.ServiceRateId
				LEFT JOIN dbo.Currency C ON QOR.CurrencyId = c.Id
				LEFT JOIN dbo.SupplierServicerateMixVehicle AS ssmv ON ssmv.ParentServiceRateId = QOR.ParentServiceRateId AND ssmv.ServiceRateId = QOR.ServiceRateId
		WHERE   QOR.Id = ? ";

	$stmt = $this->db->prepare($sql);
	$params = array();
	$params[] = $QuotationDataQuoteItemSavedOriginalRateId ;
	$stmt->execute($params);

	//echo $sql;
	//print_r($params);
	//echo"<hr/>";
	// spcial for quote

	//$this->SaveSpecialChargePrice($confirmationsId,$BookingRateCostId,$ReferanceId,$ServiceRateId,$Pax,$columnNo,$QuotationId,$isSupplier=2);
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function SaveCostBooked($confirmationsId,$ReferanceId,$ServiceRateId,$Pax,$tourId,$columnNo,$ParentServiceContractId='')
{

	if(empty($ParentServiceContractId))
		$ParentServiceContractId= "NULL";
	else
		$ParentServiceContractId = "'$ParentServiceContractId'";

	try{
	$sqlid = "	DECLARE @Id UNIQUEIDENTIFIER
				SET @Id = NEWID()
				select @Id as BookingRateCostId ";

	$BookingRateCostId = $this->db->fetchOne($sqlid);

	$sql = "SELECT ISNULL(ssmv.NoOfUse, 1) AS no_of_use
				FROM dbo.VehicleBooking vb
					 INNER JOIN dbo.SupplierServicerateMixVehicle AS ssmv ON ssmv.ParentServiceRateId = vb.ParentServiceRateId
				WHERE vb.Tourid = '$tourId'
					  AND ssmv.ServiceRateId = '$ServiceRateId'
					  AND vb.VBId = '$ReferanceId'; ";
	$no_of_use = 1;
	$no_of_use = $this->db->fetchOne($sql);
	if($no_of_use == ""){
		$no_of_use = 1;
	}

	$sql = "
			INSERT INTO dbo.BookingRateCost
			(
			  confirmationsId,
			  BookingRateCostId,
			  TourId,
			  ReferanceId ,
			  ServiceRateId ,
			  CompanyId ,
			  ServiceContractId ,
			  ServiceUnitTypeId ,
			  StartDefaultCapacity ,
			  EndDefaultCapacity ,
			  Price ,
			  IsPax ,
			  markettype ,
			  CurrencyId ,
			  Currency ,
			  CurrencyRate ,
			  Pax ,
			  CostTypeId,
			  NoOfUse,
			  ParentServiceContractId
			)
		SELECT  '$confirmationsId',
				'$BookingRateCostId',
				'$tourId',
				'$ReferanceId' ,
				SSR.ServiceRateId,
				SSC.CompanyId ,
				SSC.ServiceContractId ,
				SSR.ServiceUnitTypeId ,
				StartDefaultCapacity ,
				EndDefaultCapacity ,
				Price ,
				SSR.IsPax ,
				SSR.markettype ,
				CurrencyId,
				Currency ,
				CurrencyRate ,
				'$Pax' ,
				3 ,
				$no_of_use,
				$ParentServiceContractId
		FROM   dbo.SupplierServiceRate SSR
				INNER JOIN dbo.SupplierServiceContract SSC ON SSR.ServiceContractId = SSC.ServiceContractId
				LEFT JOIN dbo.Currency C ON C.Id = SSC.CurrencyId
		WHERE  ServiceRateId = ?
		";

	$stmt = $this->db->prepare($sql);
	$params = array();
	$params[] = $ServiceRateId ;
	$stmt->execute($params);

	//if($ParentServiceContractId!='NULL')
	//echo $sql;

	//print_r($params);
	//echo"<hr/>";
	// special for supplier
	$this->SaveSpecialChargePrice($confirmationsId,$BookingRateCostId,$ReferanceId,$ServiceRateId,$Pax,$columnNo,$QuotationId,$isSupplier=1);

	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

public function SaveCostConfirmed($confirmationsId,$ReferanceId,$ServiceRateId,$Pax,$tourId,$columnNo,$ParentServiceContractId='')
{
	if(empty($ParentServiceContractId))
		$ParentServiceContractId= "NULL";
	else
		$ParentServiceContractId = "'$ParentServiceContractId'";



	try{

	$sqlid = "	DECLARE @Id UNIQUEIDENTIFIER
				SET @Id = NEWID()
				select @Id as BookingRateCostId ";
	$BookingRateCostId = $this->db->fetchOne($sqlid);

	$sql = "SELECT ISNULL(ssmv.NoOfUse, 1) AS no_of_use
			FROM dbo.VehicleBooking vb
				 INNER JOIN dbo.SupplierServicerateMixVehicle AS ssmv ON ssmv.ParentServiceRateId = vb.ParentServiceRateId
			WHERE vb.Tourid = '$tourId'
				  AND ssmv.ServiceRateId = '$ServiceRateId'
				  AND vb.VBId = '$ReferanceId'; ";
	$no_of_use = 1;
	$no_of_use = $this->db->fetchOne($sql);
	if($no_of_use == ""){
		$no_of_use = 1;
	}

	$sql = "
			INSERT INTO dbo.BookingRateCost
			( confirmationsId,
			  BookingRateCostId,
			  TourId,
			  ReferanceId ,
			  ServiceRateId ,
			  CompanyId ,
			  ServiceContractId ,
			  ServiceUnitTypeId ,
			  StartDefaultCapacity ,
			  EndDefaultCapacity ,
			  Price ,
			  IsPax ,
			  markettype ,
			  CurrencyId ,
			  Currency ,
			  CurrencyRate ,
			  Pax ,
			  CostTypeId,
			  NoOfUse,
			  ParentServiceContractId
			)
		SELECT  '$confirmationsId',
				'$BookingRateCostId',
				'$tourId',
				'$ReferanceId' ,
				SSR.ServiceRateId,
				SSC.CompanyId ,
				SSC.ServiceContractId ,
				SSR.ServiceUnitTypeId ,
				StartDefaultCapacity ,
				EndDefaultCapacity ,
				Price ,
				IsPax ,
				SSR.markettype ,
				CurrencyId,
				Currency ,
				CurrencyRate ,
				'$Pax' ,
				4,
				1,
				$ParentServiceContractId
		FROM   dbo.SupplierServiceRate SSR
				INNER JOIN dbo.SupplierServiceContract SSC ON SSR.ServiceContractId = SSC.ServiceContractId
				LEFT JOIN dbo.Currency C ON C.Id = SSC.CurrencyId
		WHERE  ServiceRateId = ?
		";

	$stmt = $this->db->prepare($sql);
	$params = array();
	$params[] = $ServiceRateId ;
	$stmt->execute($params);

	//if($ParentServiceContractId!='NULL')
	//	echo $sql;

	//print_r($params);
	//echo"<hr/>";
	$this->SaveSpecialChargePrice($confirmationsId,$BookingRateCostId,$ReferanceId,$ServiceRateId,$Pax,$columnNo,$QuotationId,$isSupplier=1); // spcial for supplier
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}


public function SaveSpecialChargePrice($confirmationsId,$BookingRateCostId,$ReferanceId,$ServiceRateId,$Pax,$columnNo,$QuotationId="",$isSupplier=1){
	try{
	if($isSupplier==1){
		$sql="
		INSERT INTO dbo.BookingRateSpecialChargeCost
			(
			  confirmationsId,
			  BookingRateCostId ,
			  ReferanceId ,
			  SupplierServiceRateSpecialChargeId ,
			  ServiceContractId ,
			  ParentServiceRateId ,
			  StartChargePax ,
			  EndChargePax ,
			  Price ,
			  IsPax ,
			  PaxToCharge ,
			  TotalPrice
			)
			SELECT  '$confirmationsId',
					'$BookingRateCostId' ,
					'$ReferanceId' ,
					ssrsc.Id ,
					ServiceContractId ,
					ParentServiceRateId ,
					StartChargePax ,
					EndChargePax ,
					Price ,
					IsPax ,
					($Pax-StartChargePax)+1 ,
					Price * (($Pax-StartChargePax)+1)
		FROM SupplierServiceRateSpecialCharge ssrsc
		WHERE ssrsc.ParentServiceRateId = '$ServiceRateId'
			AND '$Pax' between ssrsc.StartChargePax and ssrsc.EndChargePax
		ORDER BY StartChargePax,EndChargePax
		";
	}else{

		$sql="
		INSERT INTO dbo.BookingRateSpecialChargeCost
			(
			  confirmationsId,
			  BookingRateCostId ,
			  ReferanceId ,
			  SupplierServiceRateSpecialChargeId ,
			  ServiceContractId ,
			  ParentServiceRateId ,
			  StartChargePax ,
			  EndChargePax ,
			  Price ,
			  IsPax ,
			  PaxToCharge ,
			  TotalPrice
			)
			SELECT  '$confirmationsId',
					'$BookingRateCostId' ,
					'$ReferanceId' ,
					ssrsc.Id ,
					qd.ServiceContractId ,
					ParentServiceRateId ,
					StartChargePax ,
					EndChargePax ,
					LocalPrice ,
					qd.IsPax ,
					($Pax-StartChargePax)+1 ,
					LocalPrice * (($Pax-StartChargePax)+1)
		   FROM     dbo.QuotationDataQuoteItemOriginalRate qd
					INNER JOIN dbo.SupplierServiceRateSpecialCharge ssrsc ON qd.ServiceRateId = ssrsc.Id
		   WHERE    qd.QuotationId = '$QuotationId'
					AND ColumnPosition = $columnNo
					AND ssrsc.ParentServiceRateId = '$ServiceRateId'
					AND $Pax BETWEEN ssrsc.StartChargePax AND ssrsc.EndChargePax ";


	}// end iscontract
	$stmt = $this->db->prepare($sql);
	$stmt->execute();
	//echo $sql;
	//echo"<hr/>";
	}catch(Exception $e){
		echo $sql;
		print_r($params);
		echo $e->getMessage();
		echo "<hr/>";
		echo "File : ".__FILE__.' | Class : '.__CLASS__.'| Line:'.__LINE__.'<br/>';
		echo "<hr/>";
		var_dump($e);
		return false;
	}
}

	public function costZoneFree($qid){
		$sql = "
				SELECT
						( SELECT    PriceA
						  FROM      dbo.QuotationColumn
						  WHERE     Position = 1
									AND dbo.QuotationColumn.QuotationId = dbo.Quotation.QuotationId
						) AS PriceA1
				FROM    dbo.Quotation
				WHERE   Quotation.QuotationId = ?
		";
		unset( $params ) ;
		$inputParam[] = $qid ;

		try{
			$Price = $this->db->fetchOne($sql, $inputParam) ;
			$msg = 'OK' ;
		}catch( Exception $e ){
			$errormsg = $e->getMessage();
			$msg= array( "msg" => "Search BookingVersion ERROR." ) ;
		}
		return $Price;
	}

	public function detectPaxRange($qid,$col,$pax){
		if(strstr($col,"+") OR $col == 0){
			//
			$sqlNomal = "
				SELECT QuotationData.DayNo,SupplierServiceContract.*,
				(
				  SELECT COUNT(*)
				  FROM SupplierServiceRate
				  WHERE SupplierServiceRate.ServiceContractId = QuotationDataQuoteItem.ServiceContractId
				  AND $pax BETWEEN SupplierServiceRate.StartDefaultCapacity AND SupplierServiceRate.EndDefaultCapacity
				  AND SupplierServiceRate.markettype = 'Worldwide'
				) as detectpax
				FROM QuotationDataQuoteItem
				LEFT JOIN QuotationData ON QuotationData.QuotationDataId = QuotationDataQuoteItem.QuotationDataId
				LEFT JOIN SupplierServiceContract ON SupplierServiceContract.ServiceContractId = QuotationDataQuoteItem.ServiceContractId
				WHERE QuotationDataQuoteItem.QuotationId = '$qid' AND SupplierServiceContract.Combineflag = 1
				ORDER BY QuotationData.DayNo
			";
			$rsService = $this->db->fetchAll($sqlNomal) ;
			foreach($rsService as $data){
				if($data->detectpax == 0){
					return false;
				}
			}



			$sqlCombine = "
				SELECT  ServiceCategory_Desc ,
					DayNo,
					qdi.QuotationId ,
					QuotationDataQuoteItemId,
					ssc.CompanyId,
					ssc.ServiceContractId ,
					ParentServiceContractId ,
					scs.ServiceContractId AS CombineServiceContractId ,
					Combineflag ,
					sc.Id,
					FlightFromCityId,
					FlightToCityId,
				(
				  SELECT COUNT(*)
				  FROM SupplierServiceRate
				  WHERE SupplierServiceRate.ServiceContractId = scs.ServiceContractId
				  AND '$pax' BETWEEN SupplierServiceRate.StartDefaultCapacity AND SupplierServiceRate.EndDefaultCapacity
				  AND SupplierServiceRate.markettype = 'Worldwide'
				)   as detectpax
				FROM    dbo.QuotationData AS qd
					INNER JOIN dbo.QuotationDataQuoteItem AS qdi ON qd.QuotationDataId = qdi.QuotationDataId
					INNER JOIN dbo.SupplierServiceContract AS ssc ON qdi.ServiceContractId = ssc.ServiceContractId
					INNER JOIN dbo.SupplierCombineService AS scs ON scs.ParentServiceContractId = ssc.ServiceContractId
					INNER JOIN dbo.SupplierServiceMaster AS ssm ON scs.ServiceMasterId = ssm.ServiceMasterId
					INNER JOIN dbo.ServiceCategory AS sc ON sc.Id = ssm.ServiceCategoryId
				WHERE   Combineflag = 0
					AND qdi.QuotationId = '$qid'
				ORDER BY DayNo,scs.RunNo,ParentServiceContractId
			";

			$rsService = $this->db->fetchAll($sqlCombine) ;
			foreach($rsService as $data){
				if($data->detectpax == 0){
					return false;
				}
			}
			return true;
		}else{
			return true;
		}
	}

	public function __destruct()
	{

	}

	public function getHotelSellingPrice($quotation_id, $category){
		try{
			$array_selling_price = array();
			$column_name = "PriceA";
			if($category == "C"){
				$column_name = "PriceC";
			}else if($category == "B"){
				$column_name = "PriceB";
			}
			$sql = "SELECT QuotationId,
			   (
				 SELECT $column_name
				 FROM dbo.QuotationColumn
				 WHERE dbo.QuotationColumn.QuotationId = dbo.Quotation.QuotationId AND Position = 1
			   ) AS SglPrice,
			   (
				 SELECT $column_name
				 FROM dbo.QuotationColumn
				 WHERE dbo.QuotationColumn.QuotationId = dbo.Quotation.QuotationId AND Position = 2
			   ) AS DblPrice,
			   (
				 SELECT $column_name
				 FROM dbo.QuotationColumn
				 WHERE dbo.QuotationColumn.QuotationId = dbo.Quotation.QuotationId AND Position = 7
			   ) AS ExBed

			FROM dbo.Quotation
			WHERE dbo.Quotation.QuotationId = '$quotation_id' ";
			$rs = $this->db->fetchAll($sql);
			if($rs){
				foreach($rs as $row){
					$sgl_price = $row->SglPrice;
					$dbl_price = $row->DblPrice*2;
					$eb_price = $row->ExBed;
					if($eb_price > 0){
						$tpl_price = $dbl_price+$eb_price;
					}else{
						$tpl_price = 0;
					}
					$array_selling_price = array($sgl_price, $dbl_price, $tpl_price);
				}
			}
		}catch(Exception $ex){
		}
		return $array_selling_price;
	}

}
?>
