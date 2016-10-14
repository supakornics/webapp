<?ob_start();
	session_start();
include("connect.php") ;
include_once("SQLServerDB.php");
require_once("booking_confirm_function.php");



$db  = new SQLServerDB();
$mode = $_REQUEST['mode'];

if($mode=="getCity")
{
	$CountryId = $_REQUEST['CountryId'];
	if(!empty($CountryId))
	$wheere = " and  CountryId=? ";
	
	$sql = "SELECT CityId,City FROM dbo.tbCity WHERE 1=1 $wheere ORDER BY City";
	
	if(!empty($CountryId))
	$params[] = $CountryId;
	
	$result = $db->query($sql,$params);
	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['CityId']] = $row['City'];	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode == "getCompany"){
	$CountryId = $_REQUEST['CountryId'];
	$CityId = $_REQUEST['CityId'];

	// var_dump($CountryId, $CityId);
	
	$where = "";
	if(!empty($CountryId)) {
		$where .= " AND CountryId=? ";
		$params[] = $CountryId;
	}

	if(!empty($CityId)) {
		$where .= " AND CityId=? ";
		$params[] = $CityId;
	}
	
	$sql = "SELECT CompanyId, CompanyName FROM dbo.ContactCompany WHERE 1=1 AND CompanyName <> ' ' $where ORDER BY CompanyName ";
	
	$result = $db->query($sql,$params);

	// var_dump($sql, $params);

	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['CompanyId']] = $row['CompanyName'];	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode=="getService")// Entrance Fee
{
	$CompanyId = $_REQUEST['CompanyId'];
	
	$sql = "SELECT ServiceContractId,
				   ServiceName ,
				   dbo.Date_Format(ValidFrom,'dd-MMM-yyyy') as ValidFrom,
				   dbo.Date_Format(ValidTo,'dd-MMM-yyyy') as ValidTo,Combineflag
	FROM dbo.SupplierServiceContract INNER JOIN dbo.SupplierServiceMaster
			ON dbo.SupplierServiceContract.ServiceMasterId = dbo.SupplierServiceMaster.ServiceMasterId 
	WHERE dbo.SupplierServiceContract.CompanyId= ?  
	AND ServiceCategoryId='F54AC04A-B481-4BF4-A58A-3C2D5898C65F' 
	AND Combineflag = 1
	order by ServiceName";
	unset($params);
	$params[] = $CompanyId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	while($row = $result->next())
	{	$str = ($row['Combineflag']==1)?$row['ServiceName']."(~$row[ValidFrom]~$row[ValidTo]~)":$row['ServiceName']."($row[ValidFrom]-$row[ValidTo]) *";
		$arr[$row['ServiceContractId']] = $str ;	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode=="getServiceMisc")
{
	// Activity Package Helicopter Miscellaneous
	$CompanyId = $_REQUEST['CompanyId'];
	
	$sql = "SELECT ServiceContractId,ServiceName ,
	dbo.Date_Format(ValidFrom,'dd-mmm-YYYY') AS ValidFrom,
	dbo.Date_Format(ValidTo,'dd-mmm-YYYY') AS ValidTo
	FROM dbo.SupplierServiceContract INNER JOIN dbo.SupplierServiceMaster
		 ON dbo.SupplierServiceContract.ServiceMasterId = dbo.SupplierServiceMaster.ServiceMasterId 
		 
	WHERE dbo.SupplierServiceContract.CompanyId= ? AND Combineflag = 1
	  		AND ServiceCategoryId NOT IN ( 
				'92D2F393-942D-4437-B432-31EF282A1214',
				'3926324A-C7CC-4CEA-91AF-3ABF00E9CE7F',
				'F54AC04A-B481-4BF4-A58A-3C2D5898C65F',
				'26E99294-233D-4901-8FE4-3EF0CA43546A',				
				'44457B07-0E00-48DF-9D2A-5F1843C3BA94',
				'0C71BBF8-F636-4F77-86BD-7C0BA55B07C3',
				'E386DE20-D0A7-4FF5-A80C-E3D3542E4344'	 ) 
	Order by ServiceName,ValidFrom,ValidTo";
	// '4ED188D2-314B-4A35-A199-4BA701AB31C5', Guide
	//'13F23CBF-06DC-4CB4-99B8-9830ACE1C5BC', Meal
	unset($params);
	$params[] = $CompanyId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['ServiceContractId']] = $row['ServiceName']."(~".$row['ValidFrom']."~".$row['ValidTo']."~)";	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode=="getVehicle")
{
	
	$ContractId = $_REQUEST['ContractId'];
	
	$sql = "SELECT ServiceRateId,'['+CAST(StartDefaultCapacity AS VARCHAR(10))+'-'+CAST(EndDefaultCapacity AS VARCHAR(10))+']' AS rate,
				Price AS Pricex,
				ServiceCategory_Desc,Currency
				FROM dbo.SupplierServiceRate
				INNER JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
				INNER JOIN dbo.Currency ON Id = dbo.SupplierServiceContract.CurrencyId
				INNER JOIN dbo.ServiceUnitType ON dbo.ServiceUnitType.Id = dbo.SupplierServiceRate.ServiceUnitTypeId
        		INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.ServiceUnitType.ServiceCategoryId
			WHERE dbo.SupplierServiceRate.ServiceContractId=?
			AND ServiceCategoryId IN ('F54AC04A-B481-4BF4-A58A-3C2D5898C65F' )
			AND Combineflag = 1 
			AND Mixvehicleflag = 0
			ORDER BY StartDefaultCapacity,EndDefaultCapacity";
	unset($params);
	$params[] = $ContractId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	
	while($row = $result->next())
	{
		$arr[$row['ServiceRateId']] = $row['ServiceCategory_Desc'].' ' . $row['rate'].":".$row['Currency']." " .number_format($row['Pricex'],2);	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode=="getVehicleNewVersion")
{
	
	$ContractId = $_REQUEST['ContractId'];
	
	$sql = "SELECT ServiceRateId,'['+CAST(StartDefaultCapacity AS VARCHAR(10))+'-'+CAST(EndDefaultCapacity AS VARCHAR(10))+']' AS rate,
				Price AS Pricex,
				ServiceCategory_Desc,
				Currency,
				dbo.Currency.Id AS [CurrencyId]
				FROM dbo.SupplierServiceRate
				INNER JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
				INNER JOIN dbo.Currency ON Id = dbo.SupplierServiceContract.CurrencyId
				INNER JOIN dbo.ServiceUnitType ON dbo.ServiceUnitType.Id = dbo.SupplierServiceRate.ServiceUnitTypeId
        		INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.ServiceUnitType.ServiceCategoryId
			WHERE dbo.SupplierServiceRate.ServiceContractId=?
			AND ServiceCategoryId IN ('F54AC04A-B481-4BF4-A58A-3C2D5898C65F' )
			AND Combineflag = 1 
			AND Mixvehicleflag = 0
			ORDER BY StartDefaultCapacity,EndDefaultCapacity";

	unset($params);
	$params[] = $ContractId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	
	while($row = $result->next())
	{
		$arr[$row['ServiceRateId'].'~'.number_format($row['Pricex'], 2).'~'.$row['Currency'].'~'.$row['CurrencyId']] = $row['ServiceCategory_Desc'].' ' . $row['rate'].":".$row['Currency']." " .number_format($row['Pricex'],2);	
	}
	echo json_encode($arr);
	exit();
}
elseif($mode=="getmiscother")
{
	$ContractId = $_REQUEST['ContractId'];
	
	$sql = "SELECT ServiceRateId,'['+CAST(StartDefaultCapacity AS VARCHAR(10))+'-'+CAST(EndDefaultCapacity AS VARCHAR(10))+']' + '('+dbo.SupplierServiceRate.markettype+')' AS rate,
				Price/CurrencyRate AS Pricex,
				ServiceCategory_Desc
				FROM dbo.SupplierServiceRate
				INNER JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
				INNER JOIN dbo.Currency ON Id = dbo.SupplierServiceContract.CurrencyId
				INNER JOIN dbo.ServiceUnitType ON dbo.ServiceUnitType.Id = dbo.SupplierServiceRate.ServiceUnitTypeId
        		INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.ServiceUnitType.ServiceCategoryId
			WHERE dbo.SupplierServiceRate.ServiceContractId=? AND Combineflag = 1
			and ServiceCategoryId NOT IN ( 
				'92D2F393-942D-4437-B432-31EF282A1214',
				'3926324A-C7CC-4CEA-91AF-3ABF00E9CE7F',
				'F54AC04A-B481-4BF4-A58A-3C2D5898C65F',
				'26E99294-233D-4901-8FE4-3EF0CA43546A',
				
				'44457B07-0E00-48DF-9D2A-5F1843C3BA94',
				'0C71BBF8-F636-4F77-86BD-7C0BA55B07C3',
				'E386DE20-D0A7-4FF5-A80C-E3D3542E4344'	 ) 
			ORDER BY StartDefaultCapacity,EndDefaultCapacity";
			//'4ED188D2-314B-4A35-A199-4BA701AB31C5',
			//'13F23CBF-06DC-4CB4-99B8-9830ACE1C5BC',
	unset($params);
	$params[] = $ContractId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['ServiceRateId']] = $row['ServiceCategory_Desc'].' ' . $row['rate'].":$".number_format($row['Pricex'],2);	
	}
	echo json_encode($arr);
	exit();	
}
elseif($mode=="getmiscotherNewVersion")
{
	$ContractId = $_REQUEST['ContractId'];
	
	$sql = "SELECT ServiceRateId,'['+CAST(StartDefaultCapacity AS VARCHAR(10))+'-'+CAST(EndDefaultCapacity AS VARCHAR(10))+']' + '('+dbo.SupplierServiceRate.markettype+')' AS rate,
				--Price/CurrencyRate AS Pricex,
				Price AS Pricex,
				ServiceCategory_Desc,
				dbo.Currency.Currency,
				dbo.Currency.Id AS [CurrencyId]
				FROM dbo.SupplierServiceRate
				INNER JOIN dbo.SupplierServiceContract ON dbo.SupplierServiceRate.ServiceContractId = dbo.SupplierServiceContract.ServiceContractId
				INNER JOIN dbo.Currency ON Id = dbo.SupplierServiceContract.CurrencyId
				INNER JOIN dbo.ServiceUnitType ON dbo.ServiceUnitType.Id = dbo.SupplierServiceRate.ServiceUnitTypeId
        		INNER JOIN dbo.ServiceCategory ON dbo.ServiceCategory.Id = dbo.ServiceUnitType.ServiceCategoryId
			WHERE dbo.SupplierServiceRate.ServiceContractId = ? 
				AND Combineflag = 1 
				AND ServiceCategoryId NOT IN ( 
					'91D6946C-A5FC-4880-93FB-44B65566A2E5' --Package
					, 'F2F97670-8F7E-44D1-BEF2-052FD5F95AE1' --Activities
				) 
			ORDER BY StartDefaultCapacity,EndDefaultCapacity";
	unset($params);
	$params[] = $ContractId;
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['ServiceRateId'].'~'.number_format($row['Pricex'], 2).'~'.$row['Currency'].'~'.$row['CurrencyId']] = $row['ServiceCategory_Desc'].' ' . $row['rate'].":$".number_format($row['Pricex'],2);	
	}
	echo json_encode($arr);
	exit();	
}
elseif($mode=="getWater")
{
	$CountryId = $_REQUEST['CountryId'];
	
	$sql = "SELECT  id,DrinkingWaterDesc,Price FROM dbo.DrinkWater WHERE 1=1 ";
	unset($params);
	
	if(!empty($CountryId))
	{
		$sql.=" and  CountryId= ? ";	
		$params[] = $CountryId;
	}
	
	$sql.="  order by DrinkingWaterDesc ";
	
	$result = $db->query($sql,$params);
	
	$arr[0] = "--select--";
	while($row = $result->next())
	{
		$arr[$row['id']] = $row['DrinkingWaterDesc'].":$".number_format($row['Price'],2);
	}
	echo json_encode($arr);
	exit();
	
}
elseif($mode=="editEntranceFee")
{
	
	$EBId = $_REQUEST['EBId'];
	$sql  = "SELECT 
				EBId ,
				OnDay ,
				dbo.BookingRateCost.ServiceContractId ,
				dbo.BookingRateCost.ServiceRateId ,
				Status ,
				dbo.EntranceFeeBooking.Remark ,
				dbo.EntranceFeeBooking.CompanyId ,
				CountryId ,
				CityId,
				dbo.BookingRateCost.Pax 
				-- DevMark
				, dbo.EntranceFeeBooking.CurrencyId
				, dbo.EntranceFeeBooking.CurrencyExtraCostId
				, dbo.EntranceFeeBooking.CurrencyReductionId
						
				, dbo.EntranceFeeBooking.ConfirmUS
				, dbo.EntranceFeeBooking.ConfirmLocal
				, dbo.EntranceFeeBooking.ConfirmCurrency

				, dbo.EntranceFeeBooking.ExtraCostUS
				, dbo.EntranceFeeBooking.ExtraCostLocal
				, dbo.EntranceFeeBooking.ExtraCostCurrency

				, dbo.EntranceFeeBooking.ReductionUS
				, dbo.EntranceFeeBooking.ReductionLocal
				, dbo.EntranceFeeBooking.ReductionCurrency

				, dbo.EntranceFeeBooking.ExtraCostRemark
				, dbo.EntranceFeeBooking.ReductionRemark
			 FROM  dbo.EntranceFeeBooking
			 INNER JOIN dbo.ContactCompany ON  dbo.EntranceFeeBooking.CompanyId = dbo.ContactCompany.CompanyId
			 INNER JOIN dbo.BookingRateCost ON dbo.EntranceFeeBooking.EBId = dbo.BookingRateCost.ReferanceId
			 WHERE EBId=? 
			  AND CostTypeId  in(3,4)
			 
			 ";
	unset($params);
	$params[] = $EBId;	
		 
	$result = $db->query($sql,$params);	
	$row = $result->next();
	
	$arr['EBId'] = $row['EBId'];
	$arr['Pax'] = $row['Pax'];
	$arr['OnDay'] = $row['OnDay']->format('d-M-Y');
	$arr['CompanyId'] = $row['CompanyId'];
	$arr['ServiceContractId'] = $row['ServiceContractId'];
	$arr['ServiceRateId'] = $row['ServiceRateId'];
	$arr['Status'] = $row['Status'];
	$arr['Remark'] = $row['Remark'];
	$arr['CountryId'] = $row['CountryId'];
	$arr['CityId'] = $row['CityId'];

	// DevMark
	$arr['CurrencyId'] = !is_null($row['CurrencyId']) ? $row['CurrencyId']:'';
	$arr['CurrencyExtraCostId'] = !is_null($row['CurrencyExtraCostId']) ? $row['CurrencyExtraCostId']:'';
	$arr['CurrencyReductionId'] = !is_null($row['CurrencyReductionId']) ? $row['CurrencyReductionId']:'';

	$arr['ConfirmUS'] = $row['ConfirmUS'];
	$arr['ConfirmLocal'] = $row['ConfirmLocal'];
	$arr['ConfirmCurrency'] = $row['ConfirmCurrency'];

	$arr['ExtraCostUS'] = $row['ExtraCostUS'];
	$arr['ExtraCostLocal'] = $row['ExtraCostLocal'];
	$arr['ExtraCostCurrency'] = $row['ExtraCostCurrency'];

	$arr['ReductionUS'] = $row['ReductionUS'];
	$arr['ReductionLocal'] = $row['ReductionLocal'];
	$arr['ReductionCurrency'] = $row['ReductionCurrency'];

	$arr['ExtraCostRemark'] = $row['ExtraCostRemark'];
	$arr['ReductionRemark'] = $row['ReductionRemark'];
	
	echo json_encode($arr);
	exit();
	
}
elseif($mode=="editEntranceSave")
{
	//foreach($_REQUEST as $key =>$value)
	//echo "\$$key =  \$_REQUEST['$key'];\n";
	
	$mode =  $_REQUEST['mode'];
	$selCountry =  $_REQUEST['selCountry'];
	$selCity =  $_REQUEST['selCity'];
	$selSupplier =  $_REQUEST['selSupplier'];
	$selService =  $_REQUEST['selService'];
	$selVehicle =  $_REQUEST['selVehicle'];
	$txtOnDay =  $_REQUEST['txtOnDay'];
	$txtPax =  $_REQUEST['txtPax'];
	$selStatus =  $_REQUEST['selStatus'];
	$hidEBId =  $_REQUEST['hidEBId'];
	$hidMode =  $_REQUEST['hidMode'];
	$txtRemarks =  $_REQUEST['txtRemarks'];
	$tour_id = $_REQUEST['tour_id'];
	$ssid =  $_REQUEST['ssid'];
	$isid =  $_REQUEST['isid'];
	$FullName =  $_SESSION['FullName'];

	// DevMark
	if(!empty($_REQUEST['selVehicle'])){
		$split_arr = explode('~', $_REQUEST['selVehicle']);
		$selVehicle = $split_arr[0];
		$pricex = $split_arr[1];
		$currencyx = $split_arr[2];
	}
	else{
		$selVehicle = 0;
		$pricex = '';
		$currencyx = '';
	}

	if(!empty($_REQUEST['sel_local_currency'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency']);
		$CurrencyId = $split_arr[0];
		$pricey = $split_arr[1];
		$currencyy = $split_arr[2];
	}
	else{
		$CurrencyId = NULL;
		$pricey = '';
		$currencyy = '';
	}

	// echo "<pre>"; var_dump($selFlightPrice, $pricex, $currencyx); echo "</pre>";

	if(!empty($selStatus) && trim($selStatus) == 'OK'){
		$ConfirmUS = $_REQUEST['txt_usd'];
		$ConfirmCurrencyRate = $pricey;
		$ConfirmCurrency = $currencyy;
		$ConfirmLocal = $_REQUEST['txt_local_rate'];
	}
	else{
		// DevMark
		$ConfirmUS = '';
		$ConfirmCurrencyRate = '';
		$ConfirmCurrency = '';
		$ConfirmLocal = '';
	}

	// echo "<pre>"; var_dump($ConfirmUS, $ConfirmCurrencyRate, $ConfirmCurrency, $ConfirmLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_extra_cost'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_extra_cost']);
		$CurrencyExtraCostId =  $split_arr[0];
		$ExtraCostCurrencyRate = $split_arr[1];
		$ExtraCostCurrency = $split_arr[2];
	}
	else{
		$CurrencyExtraCostId =  NULL;
		$ExtraCostCurrencyRate = '';
		$ExtraCostCurrency = '';
	}

	$ExtraCostUS = $_REQUEST['txt_usd_extra_cost'];
	$ExtraCostLocal = $_REQUEST['txt_local_rate_extra_cost'];
	// echo "<pre>"; var_dump($CurrencyExtraCostId, $ExtraCostCurrencyRate, $ExtraCostCurrency, $ExtraCostUS, $ExtraCostLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_reduction'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_reduction']);
		$CurrencyReductionId =  $split_arr[0];
		$ReductionCurrencyRate = $split_arr[1];
		$ReductionCurrency = $split_arr[2];
	}
	else{
		$CurrencyReductionId =  NULL;
		$ReductionCurrencyRate = '';
		$ReductionCurrency = '';
	}

	$ReductionUS = $_REQUEST['txt_usd_reduction'];
	$ReductionLocal = $_REQUEST['txt_local_rate_reduction'];
	// echo "<pre>"; var_dump($CurrencyReductionId, $ReductionCurrencyRate, $ReductionCurrency, $ReductionUS, $ReductionLocal); echo "</pre>";

	$ExtraCostRemark = $_REQUEST['txt_extra_cost_remark'];
	$ReductionRemark = $_REQUEST['txt_reduction_remark'];
	// echo "<pre>";  var_dump($ExtraCostRemark, $ReductionRemark); echo "</pre>";
	
	if($hidMode=="editEntrance")
	{	
			$sql="UPDATE dbo.EntranceFeeBooking  SET 
						OnDay=cast(? as datetime),
						CompanyId=?,
						ServiceContractId=?,
						ServiceRateId=?,
						Status=?,
						Remark=?,
						uby=?,
						udate=GETDATE()
						
						-- DevMark
						, CurrencyId = ?
						, CurrencyExtraCostId = ?
						, CurrencyReductionId = ?
						
						, ConfirmUS = ?
						, ConfirmLocal = ?
						, ConfirmCurrency = ?

						, ExtraCostUS = ?
						, ExtraCostLocal = ?
						, ExtraCostCurrency = ?

						, ReductionUS = ?
						, ReductionLocal = ?
						, ReductionCurrency = ?

						, ExtraCostRemark = ?
						, ReductionRemark = ?
						
				  WHERE EBId=? 
				  
				  DELETE  FROM dbo.BookingRateSpecialChargeCost
				  WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
										   FROM     dbo.BookingRateCost
										   WHERE    ReferanceId = ?
													AND CostTypeId in(3,4) )
			 
				  DELETE  FROM dbo.BookingRateCost
				  WHERE   ReferanceId = ?
				 		  AND CostTypeId  in(3,4)
						";

			unset($params);
			$params[] = $txtOnDay;
			$params[] = $selSupplier;
			$params[] = $selService;
			$params[] = $selVehicle;
			$params[] = $selStatus;
			$params[] = $txtRemarks;
			$params[] = $_SESSION['FullName'];			

			// DevMark
			$params[] = $CurrencyId;
			$params[] = $CurrencyExtraCostId;
			$params[] = $CurrencyReductionId;

			$params[] = $ConfirmUS;
			$params[] = $ConfirmLocal;
			$params[] = $ConfirmCurrency;	

			$params[] = $ExtraCostUS;
			$params[] = $ExtraCostLocal;
			$params[] = $ExtraCostCurrency;	

			$params[] = $ReductionUS;
			$params[] = $ReductionLocal;
			$params[] = $ReductionCurrency;	

			$params[] = $ExtraCostRemark;
			$params[] = $ReductionRemark;	

			$params[] = $hidEBId;
			$params[] = $hidEBId;
			$params[] = $hidEBId;

			$result = $db->execute($sql,$params);		
				
			// var_dump($hidEBId,$selVehicle,$txtPax,$tour_id, $pricex);

			// DevMark
			SaveCostBooked($hidEBId ,$selVehicle,$txtPax,$tour_id);
			
			if($selStatus == "OK"){
				SaveCostConfirmed($hidEBId, $selVehicle, $txtPax, $tour_id, "", $CurrencyId, $ConfirmLocal, $ConfirmCurrency, $ConfirmCurrencyRate);
			}
			echo "OK";
			exit();
			
	}
	elseif($hidMode=="addEntranceFee")
	{				
		$sql = "
				DECLARE @newid AS UNIQUEIDENTIFIER
				SET @newid = NEWID()
				
				INSERT INTO dbo.EntranceFeeBooking
						( EBId,OnDay , BookDate , Status ,
						  Remark , CompanyId ,
						  ServiceContractId , ServiceRateId , cby , tourid 

						  	-- DevMark
							, CurrencyId
							, CurrencyExtraCostId
							, CurrencyReductionId
						
							, ConfirmUS
							, ConfirmLocal
							, ConfirmCurrency

							, ExtraCostUS
							, ExtraCostLocal
							, ExtraCostCurrency

							, ReductionUS
							, ReductionLocal
							, ReductionCurrency

							, ExtraCostRemark
							, ReductionRemark
						  )
				VALUES  ( @newid,cast(? as datetime),getdate(),?,
						  ?,?,
						  ?,?,?,?

							-- DevMark
							, ?
							, ?
							, ?
						
							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
						  )
						  
				select @newid as EBId 
				 ";
		
		unset($params);
		$params[] = $txtOnDay;	
		$params[] = $selStatus;
		
		$params[] = $txtRemarks;		
		$params[] = $selSupplier;	
			
		$params[] = $selService;	
		$params[] = $selVehicle;	
		$params[] = $_SESSION['FullName'];	
		$params[] = $tour_id;

		// DevMark
		$params[] = $CurrencyId;
		$params[] = $CurrencyExtraCostId;
		$params[] = $CurrencyReductionId;

		$params[] = $ConfirmUS;
		$params[] = $ConfirmLocal;
		$params[] = $ConfirmCurrency;	

		$params[] = $ExtraCostUS;
		$params[] = $ExtraCostLocal;
		$params[] = $ExtraCostCurrency;	

		$params[] = $ReductionUS;
		$params[] = $ReductionLocal;
		$params[] = $ReductionCurrency;	

		$params[] = $ExtraCostRemark;
		$params[] = $ReductionRemark;	
		
		// var_dump($sql,$params);
		$result = $db->query($sql,$params);	
		
		$result->next_result();		
		$row = $result->next();				
		$newebid = $row[0];
		
		// DevMark
		SaveCostBooked($newebid ,$selVehicle,$txtPax,$tour_id);
			
		if($selStatus == "OK"){
			SaveCostConfirmed($newebid ,$selVehicle,$txtPax,$tour_id, "", $CurrencyId, $ConfirmLocal, $ConfirmCurrency, $ConfirmCurrencyRate);
		}
		
		echo "OK";		
		exit();	
	}
	
	exit();	
}
elseif($mode=="deleteEntranceFee")
{
	$EBId = $_REQUEST['EBId'];
	
	$sql = "
			DELETE FROM dbo.BookingRateCost WHERE ReferanceId = ?
			DELETE FROM dbo.BookingRateSpecialChargeCost WHERE ReferanceId = ?
			DELETE FROM dbo.EntranceFeeBooking WHERE EBId= ?	";
	unset($params);
	$params[] = $EBId;
	$params[] = $EBId;
	$params[] = $EBId;
	
	$result = $db->execute($sql,$params);
	echo "OK";
	exit();	
}
elseif($mode=="deleteEntranceMulti")
{
	$EBId = $_REQUEST['EBId'];
	
	$sql = "
			DELETE FROM dbo.BookingRateCost WHERE ReferanceId in ($EBId)
			DELETE FROM dbo.BookingRateSpecialChargeCost WHERE ReferanceId  in ($EBId)
			DELETE FROM dbo.EntranceFeeBooking WHERE EBId in ($EBId)	";
	
	$result = $db->execute($sql);
	echo "OK";
	exit();	
}
elseif($mode=="editWater")
{
	$WBId = $_REQUEST['WBId'];
	
	$sql = " SELECT WBId ,
            dbo.DrinkWater.CountryId ,
            OnDay ,
            Id , 
			ISNULL(Status,'') as Status,			
            ISNULL(dbo.WaterBooking.Remark,'') as Remark     

            -- DevMark
			, dbo.WaterBooking.CurrencyId
			, dbo.WaterBooking.CurrencyExtraCostId
			, dbo.WaterBooking.CurrencyReductionId
						
			, dbo.WaterBooking.ConfirmUS
			, dbo.WaterBooking.ConfirmLocal
			, dbo.WaterBooking.ConfirmCurrency

			, dbo.WaterBooking.ExtraCostUS
			, dbo.WaterBooking.ExtraCostLocal
			, dbo.WaterBooking.ExtraCostCurrency

			, dbo.WaterBooking.ReductionUS
			, dbo.WaterBooking.ReductionLocal
			, dbo.WaterBooking.ReductionCurrency

			, dbo.WaterBooking.ExtraCostRemark
			, dbo.WaterBooking.ReductionRemark     
     FROM   dbo.WaterBooking
            INNER JOIN dbo.DrinkWater ON dbo.DrinkWater.Id = DrinkWaterId
            INNER JOIN dbo.tbCountry ON dbo.DrinkWater.CountryId = dbo.tbCountry.CountryId
            WHERE WBId=?
		";
	unset($params);
	$params[] = $WBId;
	
	$result = $db->query($sql,$params);	
	$row = $result->next();
	
	$arr['WBId'] = $row['WBId'];
	$arr['CountryId'] = $row['CountryId'];
	$arr['OnDay'] = $row['OnDay']->format('d-M-Y');
	$arr['WaterId'] = $row['Id'];
	$arr['Status'] = $row['Status'];
	$arr['Remark'] = $row['Remark'];

	// DevMark
	$arr['CurrencyId'] = !is_null($row['CurrencyId']) ? $row['CurrencyId']:'';
	$arr['CurrencyExtraCostId'] = !is_null($row['CurrencyExtraCostId']) ? $row['CurrencyExtraCostId']:'';
	$arr['CurrencyReductionId'] = !is_null($row['CurrencyReductionId']) ? $row['CurrencyReductionId']:'';

	$arr['ConfirmUS'] = $row['ConfirmUS'];
	$arr['ConfirmLocal'] = $row['ConfirmLocal'];
	$arr['ConfirmCurrency'] = $row['ConfirmCurrency'];

	$arr['ExtraCostUS'] = $row['ExtraCostUS'];
	$arr['ExtraCostLocal'] = $row['ExtraCostLocal'];
	$arr['ExtraCostCurrency'] = $row['ExtraCostCurrency'];

	$arr['ReductionUS'] = $row['ReductionUS'];
	$arr['ReductionLocal'] = $row['ReductionLocal'];
	$arr['ReductionCurrency'] = $row['ReductionCurrency'];

	$arr['ReductionLocal'] = $row['ReductionLocal'];
	$arr['ReductionCurrency'] = $row['ReductionCurrency'];

	$arr['ExtraCostRemark'] = $row['ExtraCostRemark'];
	$arr['ReductionRemark'] = $row['ReductionRemark'];
	
	echo json_encode($arr);
	exit();	
}
elseif($mode=="editWaterSave")
{
	//foreach($_REQUEST as $key =>$value)
	//echo "\$$key =  \$_REQUEST['$key'];\n";
	
	$mode =  $_REQUEST['mode'];
	$selCountryWater =  $_REQUEST['selCountryWater'];
	$selWater =  $_REQUEST['selWater'];
	$txtOnDayWater =  $_REQUEST['txtOnDayWater'];
	$selStatusWater =  $_REQUEST['selStatusWater'];
	$hidWBId =  $_REQUEST['hidWBId'];
	$tour_id =  $_REQUEST['tour_id'];
	$txtRemarksWater =  $_REQUEST['txtRemarksWater'];

	// DevMark
	// if(!empty($_REQUEST['selVehicleWater'])){
	// 	$split_arr = explode('~', $_REQUEST['selVehicleWater']);
	// 	$selVehicle = $split_arr[0]; //also CurrencyId
	// 	$pricex = $split_arr[1];
	// 	$currencyx = $split_arr[2];
	// }
	// else{
	// 	$selVehicle = 0; //also CurrencyId
	// 	$pricex = '';
	// 	$currencyx = '';
	// }

	if(!empty($_REQUEST['sel_local_currencyWater'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currencyWater']);
		$CurrencyId = $split_arr[0];
		$pricey = $split_arr[1];
		$currencyy = $split_arr[2];
	}
	else{
		$CurrencyId = NULL;
		$pricey = '';
		$currencyy = '';
	}

	// echo "<pre>"; var_dump($selFlightPrice, $pricex, $currencyx); echo "</pre>";

	if(!empty($selStatusWater) && trim($selStatusWater) == 'OK'){
		$ConfirmUS = $_REQUEST['txt_usdWater'];
		$ConfirmCurrencyRate = $pricey;
		$ConfirmCurrency = $currencyy;
		$ConfirmLocal = $_REQUEST['txt_local_rateWater'];
	}
	else{
		// DevMark
		$ConfirmUS = '';
		$ConfirmCurrencyRate = '';
		$ConfirmCurrency = '';
		$ConfirmLocal = '';
	}

	// echo "<pre>"; var_dump($ConfirmUS, $ConfirmCurrencyRate, $ConfirmCurrency, $ConfirmLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_extra_costWater'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_extra_costWater']);
		$CurrencyExtraCostId =  $split_arr[0];
		$ExtraCostCurrencyRate = $split_arr[1];
		$ExtraCostCurrency = $split_arr[2];
	}
	else{
		$CurrencyExtraCostId =  NULL;
		$ExtraCostCurrencyRate = '';
		$ExtraCostCurrency = '';
	}

	$ExtraCostUS = $_REQUEST['txt_usd_extra_costWater'];
	$ExtraCostLocal = $_REQUEST['txt_local_rate_extra_costWater'];
	// echo "<pre>"; var_dump($CurrencyExtraCostId, $ExtraCostCurrencyRate, $ExtraCostCurrency, $ExtraCostUS, $ExtraCostLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_reductionWater'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_reductionWater']);
		$CurrencyReductionId =  $split_arr[0];
		$ReductionCurrencyRate = $split_arr[1];
		$ReductionCurrency = $split_arr[2];
	}
	else{
		$CurrencyReductionId =  NULL;
		$ReductionCurrencyRate = '';
		$ReductionCurrency = '';
	}

	$ReductionUS = $_REQUEST['txt_usd_reductionWater'];
	$ReductionLocal = $_REQUEST['txt_local_rate_reductionWater'];
	// echo "<pre>"; var_dump($CurrencyReductionId, $ReductionCurrencyRate, $ReductionCurrency, $ReductionUS, $ReductionLocal); echo "</pre>";

	$ExtraCostRemark = $_REQUEST['txt_extra_cost_remarkWater'];
	$ReductionRemark = $_REQUEST['txt_reduction_remarkWater'];
	// echo "<pre>";  var_dump($ExtraCostRemark, $ReductionRemark); echo "</pre>";
	
	// Add 
	if(empty($hidWBId))
	{
		$sql  = "
				DECLARE @newid AS UNIQUEIDENTIFIER
				SET @newid = NEWID()
				
				INSERT INTO dbo.WaterBooking
						( WBId,OnDay , BookDate , TourId ,
						  Status , Remark , DrinkWaterId , cby  

							-- DevMark
							, CurrencyId
							, CurrencyExtraCostId
							, CurrencyReductionId
						
							, ConfirmUS
							, ConfirmLocal
							, ConfirmCurrency

							, ExtraCostUS
							, ExtraCostLocal
							, ExtraCostCurrency

							, ReductionUS
							, ReductionLocal
							, ReductionCurrency

							, ExtraCostRemark
							, ReductionRemark
						  )
				VALUES  ( @newid,?,GETDATE(),?,
						  ?,?,?,?

							-- DevMark
							, ?
							, ?
							, ?
						
							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
						  ) 
						  
				update WaterBooking set Price=(select Price from dbo.DrinkWater where Id=?)	
				where WBId = @newid	  
				";
		unset($params);
		$params[]= $txtOnDayWater;	
		$params[]= $tour_id;	
		
		$params[]= $selStatusWater;	
		$params[]= $txtRemarksWater;	
		$params[]= $selWater;
		$params[]= $_SESSION['ss_fullname'];	

		// DevMark
		$params[] = $CurrencyId;
		$params[] = $CurrencyExtraCostId;
		$params[] = $CurrencyReductionId;

		$params[] = $ConfirmUS;
		$params[] = $ConfirmLocal;
		$params[] = $ConfirmCurrency;	

		$params[] = $ExtraCostUS;
		$params[] = $ExtraCostLocal;
		$params[] = $ExtraCostCurrency;	

		$params[] = $ReductionUS;
		$params[] = $ReductionLocal;
		$params[] = $ReductionCurrency;	

		$params[] = $ExtraCostRemark;
		$params[] = $ReductionRemark;	
		
		$params[]= $selWater;
		
		$result = $db->execute($sql,$params);
		echo "OK";			  
	}
	// Update
	else
	{
		$sql = "UPDATE  dbo.WaterBooking
				SET     OnDay = ? ,
						Status = ? ,
						Remark = ? ,
						DrinkWaterId = ? ,
						uby = ?,
						udate=GETDATE()

						-- DevMark
						, CurrencyId = ?
						, CurrencyExtraCostId = ?
						, CurrencyReductionId = ?
						
						, ConfirmUS = ?
						, ConfirmLocal = ?
						, ConfirmCurrency = ?

						, ExtraCostUS = ?
						, ExtraCostLocal = ?
						, ExtraCostCurrency = ?

						, ReductionUS = ?
						, ReductionLocal = ?
						, ReductionCurrency = ?

						, ExtraCostRemark = ?
						, ReductionRemark = ?
				WHERE   WBId = ? 
				
				UPDATE dbo.WaterBooking set Price=(select Price from dbo.DrinkWater where Id=?)	
				where WBId = ?   
				";
		unset($params);
		$params[] = $txtOnDayWater;
		$params[] = $selStatusWater;
		$params[] = $txtRemarksWater;
		$params[] = $selWater;
		$params[] = $_SESSION['FullName'];

		// DevMark
		$params[] = $CurrencyId;
		$params[] = $CurrencyExtraCostId;
		$params[] = $CurrencyReductionId;

		$params[] = $ConfirmUS;
		$params[] = $ConfirmLocal;
		$params[] = $ConfirmCurrency;	

		$params[] = $ExtraCostUS;
		$params[] = $ExtraCostLocal;
		$params[] = $ExtraCostCurrency;	

		$params[] = $ReductionUS;
		$params[] = $ReductionLocal;
		$params[] = $ReductionCurrency;	

		$params[] = $ExtraCostRemark;
		$params[] = $ReductionRemark;
		
		$params[] = $hidWBId;
		$params[] = $selWater;
		$params[] = $hidWBId;

		// var_dump($sql, $params);
		$result = $db->execute($sql,$params);
		echo "OK";		
	}
	exit();	
}
elseif($mode=="deleteWater")
{
	$WBId = $_REQUEST['WBId'];
	
	$sql = "delete from dbo.WaterBooking where WBId = ? ";
	unset($params);
	$params[] = $WBId;
	
	$result = $db->execute($sql,$params);
	echo "OK";
	exit();	
}
elseif($mode=="deleteWaterMulti")
{
	$WBId = $_REQUEST['WBId'];
	
	$sql = "delete from dbo.WaterBooking where WBId in($WBId) ";

	$result = $db->execute($sql);
	echo "OK";
	exit();	
}
elseif($mode=="editOtherSave")
{
	//foreach($_REQUEST as $key =>$value)
	//echo "\$$key =  \$_REQUEST['$key'];\n";
	$uby = $_SESSION['ss_fullname'];
	$selCompany =  !empty($_REQUEST['selCompany']) ? $_REQUEST['selCompany']:null;
	$selCategory =  !empty($_REQUEST['selCategory']) ? $_REQUEST['selCategory']:null;
	$txtOtherCostDesc =  $_REQUEST['txtOtherCostDesc'];
	$txtOtherCostPrice =  $_REQUEST['txtOtherCostPrice'];
	$selCostType =  !empty($_REQUEST['selCostType']) ? $_REQUEST['selCostType']:0;
	$txtRemark =  $_REQUEST['txtRemark'];
	$hidOtherCostBookingId =  $_REQUEST['hidOtherCostBookingId'];

	$selCountryId = !empty($_REQUEST['selCountry']) ? $_REQUEST['selCountry']:null;
	$selCityId = !empty($_REQUEST['selCity']) ? $_REQUEST['selCity']:null;
	$pax = $_REQUEST['txtPax'];

	$selCurrency = !empty($_REQUEST['selCurrency']) ? $_REQUEST['selCurrency']:null;
	$splitArr = array();
	if(!empty($selCurrency)){
		$splitArr = explode("~", $selCurrency);
		$currencyId = $splitArr[0];
		$currencyRate = $splitArr[1];
		$currencyName = $splitArr[2];
	}
	else{
		$currencyId = NULL;
		$currencyRate = NULL;
		$currencyName = NULL;
	}

	$selStatus = !empty($_REQUEST['selStatus']) ? $_REQUEST['selStatus']:null;
	$bookUS = ""; $bookLocal = ""; $bookCurrency = ""; $confirmUS = ""; $confirmLocal = ""; $confirmCurrency = "";
	if(!empty($selStatus) && trim($selStatus) == 'OK'){
		$confirmLocal = $txtOtherCostPrice;
		$confirmUS = isset($currencyRate) && $txtOtherCostPrice > 0 ? number_format($txtOtherCostPrice / $currencyRate, 2) : NULL;
		$confirmCurrency = $currencyName;

		$sqlAddtional = "
			, dbo.OtherCostBooking.ConfirmUS = ?
			, dbo.OtherCostBooking.ConfirmLocal = ?
			, dbo.OtherCostBooking.ConfirmCurrency = ?
		";
	}
	else{
		// $confirmLocal = NULL;
		// $confirmUS = NULL;
		// $confirmCurrency = NULL;

		// $sqlAddtional = "
		// 	, dbo.OtherCostBooking.ConfirmUS = ?
		// 	, dbo.OtherCostBooking.ConfirmLocal = ?
		// 	, dbo.OtherCostBooking.ConfirmCurrency = ?
		// ";
		$bookLocal = $txtOtherCostPrice;
		$bookUS = isset($currencyRate) && $txtOtherCostPrice > 0 ? $txtOtherCostPrice / $currencyRate : NULL;
		$bookCurrency = $currencyName;

		$sqlAddtional = "
			, dbo.OtherCostBooking.BookUS = ?
			, dbo.OtherCostBooking.BookLocal = ?
			, dbo.OtherCostBooking.BookCurrency = ?
		"; 
	}
	
	$sql = "UPDATE dbo.OtherCostBooking SET 
					dbo.OtherCostBooking.CompanyId = ?
					, dbo.OtherCostBooking.ServiceCategoryId = ?
					, dbo.OtherCostBooking.OtherCostDesc = ?
				    , dbo.OtherCostBooking.OtherCostType = ?

					, dbo.OtherCostBooking.CurrencyId = ?
					, dbo.OtherCostBooking.[Status] = ?

				    , dbo.OtherCostBooking.CountryId = ?
				    , dbo.OtherCostBooking.CityId = ?
				    , dbo.OtherCostBooking.Pax = ?
				    , dbo.OtherCostBooking.Remark = ?
				    , dbo.OtherCostBooking.uby = ?

				    $sqlAddtional

			WHERE dbo.OtherCostBooking.OtherCostBookingId=? ";

	unset($params);
	$params[] = $selCompany;
	$params[] = $selCategory;
	$params[] = $txtOtherCostDesc;
	$params[] = $selCostType;

	$params[] = $currencyId;
	$params[] = $selStatus;

	$params[] = $selCountryId;
	$params[] = $selCityId;
	$params[] = $pax;
	$params[] = $txtRemark;
	$params[] = $uby;

	if(!empty($selStatus) && trim($selStatus) == 'OK'){
		$params[] = $confirmUS;
		$params[] = $confirmLocal;
		$params[] = $confirmCurrency;
	}
	else{
		// $params[] = NULL;
		// $params[] = NULL;
		// $params[] = NULL;
		$params[] = $bookUS;
		$params[] = $bookLocal;
		$params[] = $bookCurrency;
	}

	$params[] = $hidOtherCostBookingId;
	
	$result = $db->execute($sql, $params);

	echo "OK";
	exit();	
}
elseif($mode=="deleteOtherSave")
{
	$hidOtherCostBookingId =  $_REQUEST['hidOtherCostBookingId'];
	
	$sql = "DELETE FROM dbo.OtherCostBooking WHERE dbo.OtherCostBooking.OtherCostBookingId = ? ";
	
	unset($params);
	$params[] = $hidOtherCostBookingId;
	$result = $db->execute($sql, $params);
	echo "OK";
	exit();	
}
elseif($mode=="deleteOtherMulti")
{
	$hidMiscId =  $_REQUEST['hidMiscId'];
	
	$sql = "delete from dbo.OtherCostBooking  WHERE dbo.OtherCostBooking.OtherCostBookingId IN ($hidMiscId) ";
	
	$result = $db->execute($sql);
	echo "OK";
	exit();	
	
}
elseif($mode=="editBookingOther")
{
	
	$MOBId = $_REQUEST['MOBId'];
	
	$sql  = "SELECT  MOBId ,
					OnDay ,
					dbo.BookingRateCost.ServiceContractId ,
					dbo.BookingRateCost.ServiceRateId ,
					dbo.MisceOtherBooking.Status ,
					dbo.MisceOtherBooking.Remark ,
					dbo.MisceOtherBooking.CompanyId ,
					CountryId ,
					CityId ,
					dbo.BookingRateCost.Pax

					-- DevMark
					, dbo.MisceOtherBooking.CurrencyId
					, dbo.MisceOtherBooking.CurrencyExtraCostId
					, dbo.MisceOtherBooking.CurrencyReductionId
						
					, dbo.MisceOtherBooking.ConfirmUS
					, dbo.MisceOtherBooking.ConfirmLocal
					, dbo.MisceOtherBooking.ConfirmCurrency

					, dbo.MisceOtherBooking.ExtraCostUS
					, dbo.MisceOtherBooking.ExtraCostLocal
					, dbo.MisceOtherBooking.ExtraCostCurrency

					, dbo.MisceOtherBooking.ReductionUS
					, dbo.MisceOtherBooking.ReductionLocal
					, dbo.MisceOtherBooking.ReductionCurrency

					, dbo.MisceOtherBooking.ExtraCostRemark
					, dbo.MisceOtherBooking.ReductionRemark   
			FROM    dbo.MisceOtherBooking
					INNER JOIN dbo.ContactCompany ON dbo.MisceOtherBooking.CompanyId = dbo.ContactCompany.CompanyId
					INNER JOIN dbo.BookingRateCost ON dbo.BookingRateCost.ReferanceId = dbo.MisceOtherBooking.MOBId
			WHERE   MOBId = ?
					AND CostTypeId = ( SELECT   MAX(CostTypeId)
									   FROM     dbo.BookingRateCost AS c
									   WHERE    c.ReferanceId = dbo.MisceOtherBooking.MOBId
												AND CostTypeId IN ( 3, 4 )
                         )";
	unset($params);
	$params[] = $MOBId;	
		 
	$result = $db->query($sql,$params);	
	$row = $result->next();
	
	$arr['MOBId'] = $row['MOBId'];
	$arr['Pax'] = $row['Pax'];
	$arr['OnDay'] = $row['OnDay']->format('d-M-Y');
	$arr['CompanyId'] = $row['CompanyId'];
	$arr['ServiceContractId'] = $row['ServiceContractId'];
	$arr['ServiceRateId'] = $row['ServiceRateId'];
	$arr['Status'] = $row['Status'];
	$arr['Remark'] = $row['Remark'];
	$arr['CountryId'] = $row['CountryId'];
	$arr['CityId'] = $row['CityId'];

	// DevMark
	$arr['CurrencyId'] = !is_null($row['CurrencyId']) ? $row['CurrencyId']:'';
	$arr['CurrencyExtraCostId'] = !is_null($row['CurrencyExtraCostId']) ? $row['CurrencyExtraCostId']:'';
	$arr['CurrencyReductionId'] = !is_null($row['CurrencyReductionId']) ? $row['CurrencyReductionId']:'';

	$arr['ConfirmUS'] = $row['ConfirmUS'];
	$arr['ConfirmLocal'] = $row['ConfirmLocal'];
	$arr['ConfirmCurrency'] = $row['ConfirmCurrency'];

	$arr['ExtraCostUS'] = $row['ExtraCostUS'];
	$arr['ExtraCostLocal'] = $row['ExtraCostLocal'];
	$arr['ExtraCostCurrency'] = $row['ExtraCostCurrency'];

	$arr['ReductionUS'] = $row['ReductionUS'];
	$arr['ReductionLocal'] = $row['ReductionLocal'];
	$arr['ReductionCurrency'] = $row['ReductionCurrency'];

	$arr['ExtraCostRemark'] = $row['ExtraCostRemark'];
	$arr['ReductionRemark'] = $row['ReductionRemark'];
	
	echo json_encode($arr);
	exit();
	
}
elseif($mode=="editBookingOtherSave")
{
	//foreach($_REQUEST as $key =>$value)
	//echo "\$$key =  \$_REQUEST['$key'];\n";
	//echo "$key = $value <br/>";

	$mode =  $_REQUEST['mode'];
	$selCountry =  $_REQUEST['selCountry2'];
	$selCity =  $_REQUEST['selCity2'];
	$selSupplier =  $_REQUEST['selSupplier2'];
	$selService =  $_REQUEST['selService2'];
	$selVehicle =  $_REQUEST['selVehicle2'];
	$txtPax =  $_REQUEST['txtPax2'];
	$txtOnDay =  $_REQUEST['txtOnDay2'];
	$selStatus =  $_REQUEST['selStatus2'];
	$hidMOBId =  $_REQUEST['hidEBId2'];
	$hidMode =  $_REQUEST['hidMode2'];
	$txtRemarks =  $_REQUEST['txtRemarks2'];
	$tour_id = $_REQUEST['tour_id2'];
	$ssid =  $_REQUEST['ssid'];
	$isid =  $_REQUEST['isid'];
	$FullName =  $_SESSION['FullName'];

	// DevMark
	if(!empty($_REQUEST['selVehicle2'])){
		$split_arr = explode('~', $_REQUEST['selVehicle2']);
		$selVehicle = $split_arr[0];
		$pricex = $split_arr[1];
		$currencyx = $split_arr[2];
	}
	else{
		$selVehicle = 0;
		$pricex = '';
		$currencyx = '';
	}

	if(!empty($_REQUEST['sel_local_currency2'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency2']);
		$CurrencyId = $split_arr[0];
		$pricey = $split_arr[1];
		$currencyy = $split_arr[2];
	}
	else{
		$CurrencyId = NULL;
		$pricey = '';
		$currencyy = '';
	}

	// echo "<pre>"; var_dump($selFlightPrice, $pricex, $currencyx); echo "</pre>";

	if(!empty($selStatus) && trim($selStatus) == 'OK'){
		$ConfirmUS = $_REQUEST['txt_usd2'];
		$ConfirmCurrencyRate = $pricey;
		$ConfirmCurrency = $currencyy;
		$ConfirmLocal = $_REQUEST['txt_local_rate2'];
	}
	else{
		// DevMark
		$ConfirmUS = '';
		$ConfirmCurrencyRate = '';
		$ConfirmCurrency = '';
		$ConfirmLocal = '';
	}

	// echo "<pre>"; var_dump($ConfirmUS, $ConfirmCurrencyRate, $ConfirmCurrency, $ConfirmLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_extra_cost2'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_extra_cost2']);
		$CurrencyExtraCostId =  $split_arr[0];
		$ExtraCostCurrencyRate = $split_arr[1];
		$ExtraCostCurrency = $split_arr[2];
	}
	else{
		$CurrencyExtraCostId =  NULL;
		$ExtraCostCurrencyRate = '';
		$ExtraCostCurrency = '';
	}

	$ExtraCostUS = $_REQUEST['txt_usd_extra_cost2'];
	$ExtraCostLocal = $_REQUEST['txt_local_rate_extra_cost2'];
	// echo "<pre>"; var_dump($CurrencyExtraCostId, $ExtraCostCurrencyRate, $ExtraCostCurrency, $ExtraCostUS, $ExtraCostLocal); echo "</pre>";

	if(!empty($_REQUEST['sel_local_currency_reduction2'])){
		$split_arr = explode('~', $_REQUEST['sel_local_currency_reduction2']);
		$CurrencyReductionId =  $split_arr[0];
		$ReductionCurrencyRate = $split_arr[1];
		$ReductionCurrency = $split_arr[2];
	}
	else{
		$CurrencyReductionId =  NULL;
		$ReductionCurrencyRate = '';
		$ReductionCurrency = '';
	}

	$ReductionUS = $_REQUEST['txt_usd_reduction2'];
	$ReductionLocal = $_REQUEST['txt_local_rate_reduction2'];
	// echo "<pre>"; var_dump($CurrencyReductionId, $ReductionCurrencyRate, $ReductionCurrency, $ReductionUS, $ReductionLocal); echo "</pre>";

	$ExtraCostRemark = $_REQUEST['txt_extra_cost_remark2'];
	$ReductionRemark = $_REQUEST['txt_reduction_remark2'];
	// echo "<pre>";  var_dump($ExtraCostRemark, $ReductionRemark); echo "</pre>";
	
	if($hidMode=="editBookingOther")
	{		

		$sql="UPDATE dbo.MisceOtherBooking  SET 
						OnDay=cast(? as datetime),
						CompanyId=?,
						ServiceContractId=?,
						ServiceRateId=?,
						Status=?,
						Remark=?,
						uby=?,
						udate=GETDATE()
						
						-- DevMark
						, CurrencyId = ?
						, CurrencyExtraCostId = ?
						, CurrencyReductionId = ?
						
						, ConfirmUS = ?
						, ConfirmLocal = ?
						, ConfirmCurrency = ?

						, ExtraCostUS = ?
						, ExtraCostLocal = ?
						, ExtraCostCurrency = ?

						, ReductionUS = ?
						, ReductionLocal = ?
						, ReductionCurrency = ?

						, ExtraCostRemark = ?
						, ReductionRemark = ?
			WHERE MOBId=? 
			
			DELETE  FROM dbo.BookingRateSpecialChargeCost
				  WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
										   FROM     dbo.BookingRateCost
										   WHERE    ReferanceId = ?
													AND CostTypeId in(3,4) )
			 
		  DELETE  FROM dbo.BookingRateCost
		  WHERE   ReferanceId = ?
				  AND CostTypeId  in(3,4)
			";
			unset($params);
			$params[] = $txtOnDay;
			$params[] = $selSupplier;
			$params[] = $selService;
			$params[] = $selVehicle;
			$params[] = $selStatus;
			$params[] = $txtRemarks;
			$params[] = $_SESSION['FullName'];			

			// DevMark
		$params[] = $CurrencyId;
		$params[] = $CurrencyExtraCostId;
		$params[] = $CurrencyReductionId;

		$params[] = $ConfirmUS;
		$params[] = $ConfirmLocal;
		$params[] = $ConfirmCurrency;	

		$params[] = $ExtraCostUS;
		$params[] = $ExtraCostLocal;
		$params[] = $ExtraCostCurrency;	

		$params[] = $ReductionUS;
		$params[] = $ReductionLocal;
		$params[] = $ReductionCurrency;	

		$params[] = $ExtraCostRemark;
		$params[] = $ReductionRemark;	

			$params[] = $hidMOBId;
			$params[] = $hidMOBId;
			$params[] = $hidMOBId;
	
		// var_dump($hidMOBId);
		
		$result = $db->execute($sql,$params);
		
		SaveCostBooked($hidMOBId ,$selVehicle,$txtPax,$tour_id);
				
		if($selStatus == "OK"){
			SaveCostConfirmed($hidMOBId ,$selVehicle,$txtPax,$tour_id, "", $CurrencyId, $ConfirmLocal, $ConfirmCurrency, $ConfirmCurrencyRate);
		}

		echo "OK";
		exit();		
			
	}
	elseif($hidMode=="addBookingOther")
	{		
		
		
		
		
		$sql = "
				DECLARE @newid AS UNIQUEIDENTIFIER
				SET @newid = NEWID()
				
				INSERT INTO dbo.MisceOtherBooking
						( MOBId,OnDay , BookDate , Status ,
						  Remark , CompanyId ,
						  ServiceContractId , ServiceRateId , cby , tourid

						  -- DevMark
							, CurrencyId
							, CurrencyExtraCostId
							, CurrencyReductionId
						
							, ConfirmUS
							, ConfirmLocal
							, ConfirmCurrency

							, ExtraCostUS
							, ExtraCostLocal
							, ExtraCostCurrency

							, ReductionUS
							, ReductionLocal
							, ReductionCurrency

							, ExtraCostRemark
							, ReductionRemark
						  )
				VALUES  ( @newid,cast(? as datetime),getdate(),?,
						  ?,?,
						  ?,?,?,?
	
							-- DevMark
							, ?
							, ?
							, ?
						
							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
							, ?

							, ?
							, ?
						  )
						  
			select @newid as MOBId 
				 ";
		
		unset($params);
		$params[] = $txtOnDay;	
		$params[] = $selStatus;
		
		$params[] = $txtRemarks;		
		$params[] = $selSupplier;	
			
		$params[] = $selService;	
		$params[] = $selVehicle;	
		$params[] = $_SESSION['FullName'];	
		$params[] = $tour_id;

		// DevMark
		$params[] = $CurrencyId;
		$params[] = $CurrencyExtraCostId;
		$params[] = $CurrencyReductionId;

		$params[] = $ConfirmUS;
		$params[] = $ConfirmLocal;
		$params[] = $ConfirmCurrency;	

		$params[] = $ExtraCostUS;
		$params[] = $ExtraCostLocal;
		$params[] = $ExtraCostCurrency;	

		$params[] = $ReductionUS;
		$params[] = $ReductionLocal;
		$params[] = $ReductionCurrency;	

		$params[] = $ExtraCostRemark;
		$params[] = $ReductionRemark;	
	
		$result = $db->query($sql,$params);	
		
		$result->next_result();		
		$row = $result->next();				
		$newmobid = $row[0];

		var_dump($newmobid);
		
		// DevMark
		SaveCostBooked($newmobid ,$selVehicle,$txtPax,$tour_id);
			
		if($selStatus == "OK"){
			SaveCostConfirmed($newmobid ,$selVehicle,$txtPax,$tour_id, "", $CurrencyId, $ConfirmLocal, $ConfirmCurrency, $ConfirmCurrencyRate);
		}
		echo "OK";
		exit();	
	}
	
}
elseif($mode=="deleteBookingOther")
{
	$MOBId = $_REQUEST['MOBId'];
	
	$sql = "
			DELETE FROM dbo.BookingRateCost WHERE ReferanceId = ?
			DELETE FROM dbo.BookingRateSpecialChargeCost WHERE ReferanceId = ?
			DELETE FROM dbo.MisceOtherBooking WHERE MOBId= ?	";
	
	unset($params);
	$params[] = $MOBId;
	$params[] = $MOBId;
	$params[] = $MOBId;

	$result = $db->execute($sql,$params);
	echo "OK";
	exit();	
}
elseif($mode=="deleteMiscMulti")
{
	$MOBId = $_REQUEST['MOBId'];
	
	$sql = "
			DELETE FROM dbo.BookingRateCost WHERE ReferanceId in($MOBId)
			DELETE FROM dbo.BookingRateSpecialChargeCost WHERE ReferanceId  in($MOBId)
			DELETE FROM dbo.MisceOtherBooking WHERE MOBId in ($MOBId)	";
	
	$result = $db->execute($sql);
	echo "OK";
	exit();	
}
elseif($mode=="editWater")
{
	$WBId = $_REQUEST['WBId'];
	
	$sql = " SELECT WBId ,
            dbo.DrinkWater.CountryId ,
            OnDay ,
            Id , 
			ISNULL(Status,'') as Status,			
            ISNULL(dbo.WaterBooking.Remark,'') as Remark            
     FROM   dbo.WaterBooking
            INNER JOIN dbo.DrinkWater ON dbo.DrinkWater.Id = DrinkWaterId
            INNER JOIN dbo.tbCountry ON dbo.DrinkWater.CountryId = dbo.tbCountry.CountryId
            WHERE WBId=?
		";
	unset($params);
	$params[] = $WBId;
	
	$result = $db->query($sql,$params);	
	$row = $result->next();
	
	$arr['WBId'] = $row['WBId'];
	$arr['CountryId'] = $row['CountryId'];
	$arr['OnDay'] = $row['OnDay']->format('d-M-Y');
	$arr['WaterId'] = $row['Id'];
	$arr['Status'] = $row['Status'];
	$arr['Remark'] = $row['Remark'];
	
	echo json_encode($arr);
	exit();	
}else if($mode == "updateEntranceFeeMultiple"){
	updateEntranceFeeMultiple();
	
}else if($mode == "updateMiscellaneousMultiple"){
	updateMiscellaneousMultiple();
	
}else if($mode == "updateWaterMultiple"){
	updateWaterMultiple();
	
}else if($mode == "updateOtherMultiple"){
	updateOtherMultiple();
}else if($mode == "createMisceOtherCost"){
	createMisceOtherCost();
}

function updateEntranceFeeMultiple(){
	global $db;
	$EBId = $_REQUEST['EBId'];
	$entrance_status = $_REQUEST['entrance_status'];
	if(! empty($EBId)){
		$array_eb = explode(",", $EBId);
		$uby = $_SESSION['ss_fullname'];
		foreach($array_eb as $eb_id){
			$eb_id = str_replace("'", "", $eb_id);
			$sql = "SELECT TourId
							, ServiceRateId
						FROM dbo.EntranceFeeBooking 
						WHERE EBId='$eb_id' ";
			if($result = $db->query($sql)){
				$row = $result->next();
				$tour_id = $row['TourId'];
				$service_rate_id = $row['ServiceRateId'];
			}
			//
			$sql = "SELECT Pax
						FROM dbo.BookingRateCost
						WHERE ReferanceId = '$eb_id' AND CostTypeId = 1";
			if($result = $db->query($sql)){
				$row = $result->next();
				$pax = $row['Pax'];
			}
			//var_dump($sql);
			//exit;
			//
			$sql="UPDATE dbo.EntranceFeeBooking  SET 
						Status='$entrance_status',
						uby='$uby',
						udate=GETDATE()
				  WHERE EBId='$eb_id'
				  
				  DELETE  FROM dbo.BookingRateSpecialChargeCost
				  WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
										   FROM     dbo.BookingRateCost
										   WHERE    ReferanceId = '$eb_id'
													AND CostTypeId in(3,4) )
			 
				  DELETE  FROM dbo.BookingRateCost
				  WHERE   ReferanceId = '$eb_id'
				 		  AND CostTypeId  in(3,4)
				";
			//echo $sql;
			$db->execute($sql);
			SaveCostBooked($eb_id, $service_rate_id, $pax, $tour_id);			
			if($entrance_status == "OK"){
				SaveCostConfirmed($eb_id ,$service_rate_id,$pax,$tour_id);
			}
		}
	}
	echo "OK";
}

function updateMiscellaneousMultiple(){
	global $db;
	$MOBId = $_REQUEST['MOBId'];
	$misce_status = $_REQUEST['misce_status'];
	if(! empty($MOBId)){
		$array_mob = explode(",", $MOBId);
		$uby = $_SESSION['ss_fullname'];
		foreach($array_mob as $mob_id){
			$mob_id = str_replace("'", "", $mob_id);
			$sql = "SELECT TourId
							, ServiceRateId
						FROM dbo.MisceOtherBooking 
						WHERE MOBId='$mob_id' ";
			if($result = $db->query($sql)){
				$row = $result->next();
				$tour_id = $row['TourId'];
				$service_rate_id = $row['ServiceRateId'];
			}
			//
			$sql = "SELECT Pax
						FROM dbo.BookingRateCost
						WHERE ReferanceId = '$mob_id' AND CostTypeId IN(1,3)";
			if($result = $db->query($sql)){
				$row = $result->next();
				$pax = $row['Pax'];
			}
			//
			$sql="UPDATE dbo.MisceOtherBooking  SET 
						Status='$misce_status',
						uby='$uby',
						udate=GETDATE()
			WHERE MOBId='$mob_id'
			
			DELETE  FROM dbo.BookingRateSpecialChargeCost
				  WHERE   BookingRateCostId IN ( SELECT   BookingRateCostId
										   FROM     dbo.BookingRateCost
										   WHERE    ReferanceId = '$mob_id'
													AND CostTypeId in(3,4) )

		  DELETE  FROM dbo.BookingRateCost
		  WHERE   ReferanceId = '$mob_id'
				  AND CostTypeId  in(3,4)
			
			";
			//var_dump($mob_id ,$service_rate_id, $pax, $tour_id);
			//exit;
			$db->execute($sql);
			SaveCostBooked($mob_id ,$service_rate_id, $pax, $tour_id);
			if($misce_status == "OK"){
				SaveCostConfirmed($mob_id ,$service_rate_id, $pax, $tour_id);
			}
			//
		}
	}
		
	echo "OK";
}

function updateWaterMultiple(){
	global $db;
	$hidWaterId = $_REQUEST['hidWaterId'];
	$water_status = $_REQUEST['water_status'];
	if(! empty($hidWaterId)){
		$array_water = explode(",", $hidWaterId);
		$uby = $_SESSION['ss_fullname'];
		foreach($array_water as $wb_id){
			$sql = "UPDATE  dbo.WaterBooking
					SET
							Status = '$water_status' ,
							uby = '$uby',
							udate=GETDATE()
					WHERE   WBId = $wb_id 
					";
			//echo $sql."<br/>\n";
			//$db->execute($sql);
		}
		echo "OK";	
	}
}

function createMisceOtherCost(){
	global $db;
	try{

		$cby = $_SESSION['ss_fullname'];
		$uby = $_SESSION['ss_fullname'];
		$tour_id = $_REQUEST['tour_id'];
		$date_run = $_REQUEST['txtDateRun'];
		$sel_country = !empty($_REQUEST['selCountryOtherCost']) ? $_REQUEST['selCountryOtherCost']:null;
		$sel_city = !empty($_REQUEST['selCityOtherCost']) ? $_REQUEST['selCityOtherCost']:null;
		$sel_company = !empty($_REQUEST['selCompanyOtherCost']) ? $_REQUEST['selCompanyOtherCost']:null;
		$sel_category = !empty($_REQUEST['selCategoryOtherCost']) ? $_REQUEST['selCategoryOtherCost']:null;
		$other_cost_desc = $_REQUEST['txtOtherCostDesc'];
		$other_cost_price = $_REQUEST['txtOtherCostPrice'];
		$pax = $_REQUEST['txtPax'];
		$sel_cost_type = !empty($_REQUEST['selCostType']) ? $_REQUEST['selCostType']:0;
		$remark = $_REQUEST['txtRemarksOtherCost'];

		$sel_currency = !empty($_REQUEST['selCurrencyOtherCost']) ? $_REQUEST['selCurrencyOtherCost']:null;
		$split_arr = array();
		if(!empty($sel_currency)){
			$split_arr = explode("~", $sel_currency);
			$currency_id = $split_arr[0];
			$currency_rate = $split_arr[1];
			$currency_name = $split_arr[2];
		}
		else{
			$currency_id = NULL;
			$currency_rate = NULL;
			$currency_name = NULL;
		}

		$sel_status = !empty($_REQUEST['selStatusOtherCost']) ? $_REQUEST['selStatusOtherCost']:null;
		$BookUS = ""; $BookLocal = ""; $BookCurrency = ""; $ConfirmUS = ""; $ConfirmLocal = ""; $ConfirmCurrency = "";
		if(!empty($sel_status) && trim($sel_status) == 'OK'){
			$confirm_local = $other_cost_price;
			$confirm_us = isset($currency_rate) && $other_cost_price > 0 ? number_format($other_cost_price / $currency_rate, 2) : NULL;
			$confirm_currency = $currency_name;

			$sql_addtional = "
				, dbo.OtherCostBooking.ConfirmUS
				, dbo.OtherCostBooking.ConfirmLocal
				, dbo.OtherCostBooking.ConfirmCurrency
			";
		}
		else{
			// $confirm_local = NULL;
			// $confirm_us = NULL;
			// $confirm_currency = NULL;

			// $sql_addtional = "
			// 	, dbo.OtherCostBooking.ConfirmUS
			// 	, dbo.OtherCostBooking.ConfirmLocal
			// 	, dbo.OtherCostBooking.ConfirmCurrency
			// ";
			
			$book_local = $other_cost_price;
			$book_us = isset($currency_rate) && $other_cost_price > 0 ? $other_cost_price / $currency_rate : NULL;
			$book_currency = $currency_name;

			$sql_addtional = "
				, dbo.OtherCostBooking.BookUS
				, dbo.OtherCostBooking.BookLocal
				, dbo.OtherCostBooking.BookCurrency
			"; 
		}
		// var_dump($confirm_local, $confirm_us, $confirm_currency);
		// var_dump($book_local, $book_us, $book_currency);
		// var_dump($cby, $uby, $tour_id, $date_run, $sel_country, $sel_city, 
		// 	$sel_company, $sel_category, $other_cost_desc, $other_cost_price, $pax, $sel_cost_type, $remark);

		if(empty($pax) || !is_numeric($pax)){
			var_dump('Error : pax must be number only.');
			return;
		}

		// $sql = "INSERT dbo.MisceBooking(
		// 			id, DayNo, DateRun, PerPaxDesc, PerPaxCost
		// 			, SharedDesc, SharedCost, TourId, cby, cdate
		// 			, Remark, ServiceCategoryId
		// 		)
		// 			SELECT
		// 				NEWID(),
		// 				(
		// 					SELECT MAX(DayNo) + 1
		// 				 	FROM dbo.MisceBooking
		// 				 	WHERE TourId = '$tour_id'
		// 				),
		// 				'$date_run',
		// 				?,
		// 				'$per_pax_cost',
		// 				?,
		// 				'$shared_cost',
		// 				'$tour_id',
		// 				'$cby',
		// 				GETDATE(),
		// 				?,
		// 				?";

		$sql = "INSERT INTO dbo.OtherCostBooking
( 
	dbo.OtherCostBooking.OtherCostBookingId
    --1
    , dbo.OtherCostBooking.CountryId
    , dbo.OtherCostBooking.CityId
    , dbo.OtherCostBooking.CompanyId
    , dbo.OtherCostBooking.DayNo
    , dbo.OtherCostBooking.DateRun

    --2
    , dbo.OtherCostBooking.OtherCostDesc
    , dbo.OtherCostBooking.OtherCostType
    , dbo.OtherCostBooking.TourId
    , dbo.OtherCostBooking.ServiceCategoryId
    , dbo.OtherCostBooking.remark

    --3
    , dbo.OtherCostBooking.Pax
    , dbo.OtherCostBooking.cby
    , dbo.OtherCostBooking.cdate
    , dbo.OtherCostBooking.uby
    , dbo.OtherCostBooking.udate

    --4
    , dbo.OtherCostBooking.CurrencyId
    , dbo.OtherCostBooking.[Status]

	$sql_addtional
)
	SELECT 
	NEWID()
    --1
    , ?
    , ?
    , ?
    , (
		SELECT MAX(dbo.OtherCostBooking.DayNo) + 1
		FROM dbo.OtherCostBooking
		WHERE dbo.OtherCostBooking.TourId = '$tour_id'
	)
	, ?
    --2
    , ?
    , ?
    , ?
    , ?
    , ?
    --3
    , ?
    , ?
    , GETDATE()
    , ?
    , GETDATE()

	--4
	, ?
	, ?

	, ?
	, ?
	, ?
		";

		$params = array();
		$params[] = $sel_country;
		$params[] = $sel_city;
		$params[] = $sel_company;
		$params[] = $date_run;

		$params[] = $other_cost_desc;
		$params[] = $sel_cost_type;
		$params[] = $tour_id;
		$params[] = $sel_category;
		$params[] = $remark;

		$params[] = $pax;
		$params[] = $cby;
		$params[] = $uby;

		$params[] = $currency_id;
		$params[] = $sel_status;

		if(!empty($sel_status) && trim($sel_status) == 'OK'){
			$params[] = $confirm_us;
			$params[] = $confirm_local;
			$params[] = $confirm_currency;
		}
		else{
			// $params[] = NULL;
			// $params[] = NULL;
			// $params[] = NULL;
			$params[] = $book_us;
			$params[] = $book_local;
			$params[] = $book_currency;
		}

		$db->execute($sql, $params);
		echo "OK";
	}catch(Exception $ex){
		var_dump($ex);
	}
}

function updateOtherMultiple(){
	global $db;
}

ob_end_flush();?> 