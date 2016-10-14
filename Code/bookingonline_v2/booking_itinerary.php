<? 
include_once("SQLServerDB.php");
include_once("HtmlHelper.php");
require_once("connect2.php") ;
$db->debug=false;
$id 		= $_REQUEST['id'] ;

 $icsdb = new SQLServerDB();

	$sql= "SELECT BookingClassId as cid,
							BookingClassName as cname
				FROM BookingClass 
				ORDER BY DisplayNo";
	$rs = $icsdb->query($sql);
	$booking_class = array();
	if($rs){
		while($row = $rs->read()){
			$booking_class["$row[cid]"] = $row['cname'];
		}
	}

	$sql = "SELECT  e.FirstName + ' ' + e.LastName AS full_name ,
						i.ISID
				FROM    dbo.[IS] AS i
						INNER JOIN dbo.Employee AS e ON e.EmployeeID = i.EmployeeID
				WHERE   e.EmployeeID IN ( 35476 )
						AND ISNULL(i.inactive, 0) = 0 ";

	$rs = $icsdb->query($sql);
	$array_sale_person = array();
	if($rs){
		while($row = $rs->read()){
			$array_sale_person["$row[ISID]"] = $row['full_name'];
		}
	}

require_once("booking_edit_data2.php") ;


//$id = 'bkg0801976' ;

$rsT 	= loadData("tour" , $id ) ;

if(! $rsT -> EOF ) {

	 
 	$start_of_tour 	=  $rsT->Fields("TourStartDate");
	$end_of_tour 	=  $rsT->Fields("TourEndDate");
	$no_of_pax 		=  $rsT->Fields("NoPax");
	$Ccode 			=  $rsT->Fields("Ccode");
	$isuserid 			=  $rsT->Fields("UsersId");
	$Nr 					=  $rsT->Fields("ContactsId");
	$Company 		=  $rsT->Fields("CompanyDesc");
	$OldId 				=  $rsT->Fields("OldId") ;
	$ARremark = $rsT->Fields("invoiceRemark");
	$ICSSale = $rsT->Fields("ICSSale");
	$TACommision = $rsT->Fields("TACommision");
}

$SQL = "SELECT 
			Duedate , 
			ReminderInterval
		FROM tbContacts  
		WHERE ContactsId = ? AND isMainContact  = 1  " ;

unset($params) ;

$params[] = $Nr ;
$rsd = $db->Execute( $SQL , $params ) ;

if( $rsd -> EOF ){
		$remind 			=  "" ;
		$duedate 		=  "" ;
}else { 
	$remind 			=  $rsd->Fields("ReminderInterval") ;
	$duedate 		=  $rsd->Fields("Duedate") ;
}

if( $duedate == '' )	 
		$duedate 	= (!isset($duedate))?-14:$duedate ;  
if( $remind == '' )
		$remind 	= (!$remind)?7:$remind ;  

$sqlInv2 = "SELECT TOP(1) ConfirmationsInvoiceId,
					TourId , 
					Paidon , 
					dbo.Date_Format( Dueon, 'dd-mmm-yyyy') AS [Dueon], 
					AmountPaid , 
					ReminderNo , 
					ReminderDate , 
					Account , 
					PaymentRemark , 
					cdate , 
					udate , 
					ddate , 
					OldId ,
					uby 
			FROM   tbConfirmationsInvoice
			WHERE ( TourId = ? ) ORDER BY cdate DESC ;";
 
unset( $params ) ;
$params[] = $id ;

 
$rs2  = $db->Execute( $sqlInv2 , $params ) ;

 
if(! $rs2 -> EOF ){
 			$dateon = $rs2->Fields("Dueon")  ;
			$uby = $rs2->Fields("uby")  ;
}else{
	
			$duedate 		= ( $duedate < 0 )?abs($duedate):($duedate*-1) ;
			$dateon 			= dateAdd( "d" , $duedate ,  ($start_of_tour) )   ;
}

	$msgR =  		" remind every $remind days hereafter" ;	 

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ITINERARY</title>	
<script language="javascript" src="fnct.js"></script>
<script src="/jslib/ajax/prototype.js" type="text/javascript"></script>
<script language="javascript">
function deleteRedBgColor( line ){
								if( line )
									yy = $(line).style.backgroundColor; 
								else
									yy = '';
								if( yy == "#cc6666" || yy == "rgb(204, 102, 102)" ){
									doDelete = true;
								}else{
											doDelete = true;
											xx = '';  
											display =  'tr[rateid]' ; 
											$('frm2').getElementsBySelector(display).each(function(e1){
											xx	= e1.style.backgroundColor; 
												if(xx == "#cc6666" || xx == "rgb(204, 102, 102)"){
														 doDelete = false; 
														 $break;
												}
											}) ;
							}
							return doDelete;
}
function setBgColor( cl  , line ){
  	display =  'tr[id="'+ line +'"]' ;  
 	e1 =  $('frm2').getElementsBySelector(display)[0] ;   
 	e1.style.backgroundColor=cl;
}
function fixhotel(){
					if( document.getElementById("service") ){
									var x = 0 , y = 0  ,yy = 0 , xx ;		 
									servicex = document.forms[0].service;
								 	destination = servicex.options[servicex.selectedIndex].value;
										 	if( destination == "Hotel" )  {
												 		x = document.frm.Date_from.value	;
													 	arx = x.split("-") ;
														xx =  arx[0] + ' ' + arx[1] + ', ' + arx[2] ;
														y = document.frm.Date_to.value	;
													 	ary = y.split("-") ;
														yy =  ary[0] + ' ' + ary[1] + ', ' + ary[2] ;
														since = dd( xx , yy  );
														document.frm.qty.value = since ;	
								 	} 
									else
									{
										document.frm.qty.value = 1 ;	
									}
					}else
					{	return false; }
}
function dd( d1 , d2 ){
	
	datDate1= Date.parse(d2);
	datDate2= Date.parse(d1);

	datediff = ((datDate1-datDate2)/(24*60*60*1000)) ;
	
	return datediff ;
}		
</script>
<script type="text/javascript" src="/jslib/ajax/effects.js"> </script>
<script type="text/javascript" src="/jslib/ajax/window.js"> </script>
<script type="text/javascript" src="/jslib/ajax/window_effects.js"> </script>
<script type="text/javascript" src="/jslib/ajax/debug.js"> </script>
<script type="text/javascript"> 
function autoValue( no , mode , value1 , value2 ){

		var name = "frm" + no ;
if( mode == 1 ){ // NO SELECTED
		$(name).getElementsBySelector('input[id="Nr"]')[0].value = '';
	 	$(name).getElementsBySelector('input[id="nr_val"]')[0].value ='';  



	 if( $(name).getElementsBySelector('span[id="resultNr"]')[0] ) { 
	 	
				var e = $(name).getElementsBySelector('span[id="resultNr"]')[0] ;
			 	e.style.display='none' ; 
	 
	 } 
	 
	 
}else if(mode==2){ 
  
		$(name).getElementsBySelector('input[id="Nr"]')[0].value =  value1 ;
	 	$(name).getElementsBySelector('input[id="nr_val"]')[0].value =value2 ;  

  var e = $(name).getElementsBySelector('span[id="resultNr"]')[0] ; 
  e.style.display='none' ;
  
}

}
function Rep( id ){
 

						var url =   "prInv.php" ;
						var pars = "id=" + id + "&"  ;
							 
					  	var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
						
						msg = req.responseText ;
						
						if( msg == 'yes' ) {
										if( confirm('Booking not completely confirmed! Do you want to go ahead?') ){
											Dialog.closeInfo() ;	
											a = window.open( 'RepInv.php?id=' + id  + '&InvAccLab=' + $F('InvAccLab')+ '&' + $('frm3').serialize()   , 'x6' ,'width=1010,height=700,scrollbars=yes,menubar=yes,toolbar=yes,status=yes,resizable=yes,top=5,left=5' ); 
											a.focus() ;
											 
										}else{
											Dialog.closeInfo() ;	
											return false;
										}
									
						}else if( msg=='no') {
									Dialog.closeInfo() ;	
									a = window.open( 'RepInv.php?id=' + id  + '&InvAccLab=' + $F('InvAccLab')+ '&' + $('frm3').serialize()   , 'x6' ,'width=1010,height=700,scrollbars=yes,menubar=yes,toolbar=yes,status=yes,resizable=yes,top=5,left=5' ); 
									a.focus() ;
									
						}else {
							
									Dialog.closeInfo() ;	
									a = window.open( msg + '?id=' + id  + '&InvAccLab=' + $F('InvAccLab')+ '&' + $('frm3').serialize()   , 'x6' ,'width=1010,height=700,scrollbars=yes,menubar=yes,toolbar=yes,status=yes,resizable=yes,top=5,left=5' ); 
									a.focus() ;
									
						
						}
  						
				 
				 }});
				 
				
				 
}
function saveRemark()
{
		var url =   "updateARremark.php" ;
		var pars = $('frm3').serialize() ;
		var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
						msg = req.responseText ;
						alert( msg) ;						
						$('btnsavear').value='save' ;   
						$('btnsavear').disabled=false;
					//	window.location.href=window.location.href ;
					 window.location='booking_itinerary_piti.php?id=<?=$id?>' ;
						
				 }});
}


function saveInv(){
 
		 
		if(    $('InvAccLab').value != ''  ){
		
		 
						var url =   "updateInv.php" ;
						var pars = $('frm3').serialize() ;
					 
				 var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
						msg = req.responseText ;
						alert( msg) ;
						$('btnsaveInv').value='save invoice' ;   
						$('btnsaveInv').disabled=false;
					//	window.location.href=window.location.href ;
					 window.location='booking_itinerary.php?id=<?=$id?>' ;
						
				 }});
 		}else{
						
						alert( 'information is required. (recheck paid on , bank account , amount paid)' ) ;
						
						$('btnsaveInv').value='save invoice';
						$('btnsaveInv').disabled=false;						
						return false ;				
		
		}
}
function updateList( id  ,  no , tourid ){
					var  tr 	= "tr" + no ;
					var  from 	= "from" + no ;
					var  to 	= "to" + no ; 
					var  country = "country" + no ;
					var  comp 	= "comp" + no ;
					var  service = "service" + no ;
					var  cat 	= "cat" + no ;
					var  description = "description" + no ;
					var  qty 	= "qty" + no ;	
					var  xunit 	= "unit" + no ;	  
					var  paxX 	= "pax" + no ;
					var  manual = "manual" + no ;
					var  desc_val = "desc_val" + no ;
					  
					$("Date_from").value = $F(from) ;
					$("Date_to").value = $F( to ); 
				 	$("country").value ='';
					$("Nr").value = $(comp).innerHTML;  
					$("service").value = $(service).innerHTML;  
					$("cat").value = $( cat ).innerHTML; 
				 	$("description").value = $( description ).innerHTML;  
				 	$("qty").value = $F( qty ); 
					$("sel_unit").value = $F( xunit ); 
				 	$("pax").value = $F( paxX ); 
					
					$("hidManualChng").value = $F( manual ); 
					$("desc_val").value = $F( desc_val ); 
					$("change").value =  "yes" ;
					 		
 

					
					var pars = "tourid=" + tourid + "&ratesid="+id + "&mode=findRm&from="+$F(from)+"&to="+$F( to ) ;					
					var url =   "booking_itinerary_data5.php" ;					
					var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
					
					var a = eval("("+req.responseText+")");
					var valsgl =	a['sglx'] ; 
					var valdbl =	a['dblx'] ; 
					var valtpl =	a['tplx'] ; 
				 	var status =	a['statusx'] ; 
					var clause =	a['clausex'] ; 



					$("sgl").value = valsgl ; 
					$("dbl").value = valdbl ; 
					$("tpl").value = valtpl ; 
					
					$("special").value = clause ; 
				
			 	 
					//no_update('no') ;
					
			 
		 }});			 
 
}
function upInv( id ){ 
				y=prompt('Enter date example:-\n30 12 8\n30.12.8\n30-12-8\n30/12/8 ' ,'<?=date("d-M-Y")?>', 'IS2K' ) ; 
				
				if(y != '' && y != null){
					 
								var url =   "updateInvDate.php" ;
								try{
									y= changeFormat( y ) ;
								}catch(er){
									alert( er.description );
									return false;
								}
								var pars = "date=" + y +"&tourid=" + id  ;
								 
								var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
							
								msg = req.responseText ;
							
							if( msg.indexOf( "completed" ) > 0 )	
								window.location.href=window.location.href ;
							else
								alert( msg) ;

					 }});			
				}else{
						 		return false;
				}
}

function calprice( no , pax , totalRw ){

	var par1 = "valz" + no ;
	var par3 = "valSubTotalz" + no ;
	$(par3).innerHTML = $(par1).innerHTML * pax  ;
	var dummy = 0 ;
	for( i = 1 ; i <= totalRw ; i++){
				var parN = "valSubTotalz" + i ;
				dummy += parseFloat( $(parN).innerHTML ) ; 
	}
	$('totalz').innerHTML = dummy;
}
	function add_list( chng ){
		//alert('add list');
		var url =   "booking_confirm2.php" ;
		var pars = $('frm').serialize() ;
		pars += "&change=" + chng ; 
		
		var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
			
				var msg = req.responseText ;
				//alert('aaaaa');
				//alert(msg);
		if( msg.indexOf("Could not update") > 0 ){ 
 				alert("Could not update!\nPlease try again or call to IT team.") ;
  				$('addBtn').value='        Add     \nRecord' ;   
				$('addBtn').disabled=false;
		}else{
 		if( msg.indexOf('|') >= 0 ){	
					arrmsg = msg.split("|");
					//alert(arrmsg[1]) ;
						$("msgError").innerHTML = arrmsg[1];
					arrid = arrmsg[0].split("+"); 
		}else{
			txt = msg.split("\n");
			var msg2='';
			for( var i = 0 ; i < txt.length;i++)	{
				if( txt[i] != '' )
					//alert(txt[i]);
					msg2 = msg2 + "\n"+  txt[i].trim();
			}
		  alert(msg2) ;
		 //	$("msgError").innerHTML = msg2;
		}

		//

 
		window.opener.document.location.reload(); 
		//****************[FOR REAL]*************//
	  
	 if( $F('multi') == '1' ){
  				window.location = "booking_itinerary.php?id=<?=$id?>";
		}else{
				window.location = "booking_itinerary.php?id=<?=$id?>&setID=" + arrmsg[0];
		} 
		
		//****************[FOR REAL]*************//
		
		}
		}});
	}

	function book_to_amend( chng ){
		var c_value = "";

		/*if(document.frm2){			
			if(document.frm2.chk.value && document.frm2.chk.checked)	
					var c_value = document.frm2.chk.value ;
			else
					var chkLn = document.frm2.chk.length ;
			
			for (var i=0; i < chkLn ; i++){
				if (document.frm2.chk[i].checked){
					c_value =  c_value + "'" +document.frm2.chk[i].value + "',";
				}
			}
		}*/

		var url =   "booking_amend.php" ;
		var pars = $('frm').serialize() ;
		pars += "&change=" + chng + '&chk=' + c_value ; 
		
		var win = window.open("booking_amend.php?" + pars, 'windows_amend');
		win.focus();
	}

	function delete_to_amend(){

		var chng = 0;
		var c_value = "";
		
		if(document.frm2 && document.frm2.chk){
			if(document.frm2.chk.value && document.frm2.chk.checked)	
					var c_value = document.frm2.chk.value ;
			else
					var chkLn = document.frm2.chk.length ;
			
			for (var i=0; i < chkLn ; i++){
				if (document.frm2.chk[i].checked){
					c_value =  c_value + "'" +document.frm2.chk[i].value + "',";
				}
			}
		}

		if( c_value.length  == 0    ){	
			alert('Please choose line you want to delete..') ;
			return false;	
		}

		var url =   "booking_amend.php" ;
		var pars = $('frm').serialize() ;
		pars += "&change=" + chng + '&chk=' + c_value ; 
		
		var win = window.open("booking_amend.php?" + pars, 'windows_amend');
		win.focus();
	}

	function track_cost()
	{
		var url =   "trackCost.php" ;
		var pars = $('frm').serialize() ;
		
		var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: 
		function(req){
		 	var msg = req.responseText ;
			//alert(msg);
		}});
	}
	function update_list( mode , par1 ){
	
		var 	url 		=   "booking_confirm3.php" ;
		var 	pars 		= $('frm2').serialize() ;
		
				pars 		+= "&mode=" + mode + "&tourid=<?=$id?>&delid=" + par1 ;
		
				var myAjax 	= new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
		 	
				var msg 	= req.responseText ;
				unloadPage();
		 		 //window.opener.document.location.reload(); 
				//  alert( msg ) ;
				$("msgError").innerHTML = msg;
				 //window.location = "booking_itinerary.php?id=<?=$id?>";		
			  
		}});
	}
	
	
	
	function Description_click( QName , Manual , ID , valid ){
	 
	 	document.frm.description.value		= QName ;
	 	
		document.frm.hidManualChng.value	= Manual ;
	 	
		document.frm.desc_val.value 			= ID  ;
		 
		$("valid").innerHTML 						= "valid " + valid;
		
		var e = document.getElementById('result_desc');
		
		e.style.display='none' ;
		
 		
		var Cat = document.frm.cat.value  ;
		
		var Pax = document.frm.pax.value ;
		
		$('btnDescr').disabled=false ; 
		
		$('btnDescr').value=' v '; 	
		
		//no_update('no') ;
		
		Category_change( ID , Manual , Cat , Pax ) ;
		
	}
	function Category_change( ID , Manual , Cat , Pax  ){
		
		// alert('Category_change : '+ID + Manual + Cat + Pax);
		 
		 openInfoDialog(); 
		 
		 var url = "booking_itinerary_data2.php" ;

		 var pars = "ManualChange=" + Manual + "&desc_val=" + ID + "&Cat=" + Cat + "&Pax=" + Pax ;
	 
	 	$('cat_id').innerHTML = "<img src='images/loading.gif'>&nbsp;" ;

		var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){

			var a = eval("("+req.responseText+")");
			
			var cat					=	a['Cat'] ;
 			var sglsuppamount		=	a['sglsuppamount'] ;
			var tplredamount		=	a['tplredamount'] ;
			var Price				=	a['Price'] ;
			var paxs				=	a['paxs'] ;		
			var sglsupprice			= 	a['sglsupprice'] ;
			var tplamount			= 	a['tplamount'] ;				
			var ManualChange		= 	a['ManualChange'] ;				 
			
		 	var ExtrabedBlock = a['ExtrabedBlock'];
												
			if( cat == ""){
						$('cat_id').innerHTML = "<select id='cat' name='cat'><option>..</option></select>" ;
			}else{ 
					var mid = '';
					var head = '';
					 
					splitString = cat.split(";") ;
					
					head 	= "<select id=\"cat\" name=\"cat\" onChange = \" no_update('no')\">" ;
					
	 
					for(i = 0 ; i < splitString.length  ; i++){
						if( splitString[i] != "" ){
							if( splitString[i] == Cat ){
								  chk="selected = \"selected\" " ;
							}else{
								  chk="";
							}
							
							mid += "<option value=\"" + splitString[i] + "\" " + chk + ">" + splitString[i] + "</option>" ;
						}
					}
					
					$('cat_id').innerHTML  = head + mid + "</select>" ;		
			}
	 	
			$('pp').innerHTML			= Price ;
			$('sglsupp').innerHTML	= sglsupprice ;
			$('tpldiscnt').innerHTML	= tplamount ;						
			$('result').innerHTML    	= document.frm.qty.value * Price ; 

			var category_service = $('service').value;

			if(category_service != "Hotel"){
				if(ExtrabedBlock=='Yes')
				{
					$('tpl').disabled=true;
					$('tpl').style.backgroundColor='#CCC';
				}else{
					$('tpl').disabled=false;
					$('tpl').style.backgroundColor='';
				}
				
			}
		no_update('no');
			
		} }); 
		$('cat_id').innerHTML  = "<img src='images/loading.gif'>&nbsp;";
		$('cat_id').innerHTML +="<font face=verdana size='1'>loading...</font>"; 
		 
	}	
	function no_update( dummy ){
			
			var 	  url 	= "booking_itinerary_data2.php" ;

			 var   pars 	  = "ManualChange=" + document.frm.hidManualChng.value ; 


if( dummy == 'yes' ){
				resultPaxs = (parseFloat(document.frm.tpl.value)*3) + (parseFloat(document.frm.dbl.value)*2) + parseFloat(document.frm.sgl.value) ;  
				document.frm.pax.value = resultPaxs;
}else{
				resultPaxs = document.frm.pax.value  ;
}

 


					  pars += "&desc_val=" 	+ document.frm.desc_val.value ;
					  pars += "&Cat=" 			+ document.frm.cat.value ;
					  pars += "&Pax=" 			+ resultPaxs ; //document.frm.pax.value ; 
					  pars += "&sglno="			+ document.frm.sgl.value  ;
					  pars += "&tplno=" 		+ document.frm.tpl.value  ;					 
					  pars += "&dblno=" 		+ document.frm.dbl.value  ;
					  pars += "&twnno="			+ document.frm.twn.value  ;
					  pars += "&qty=" 			+ document.frm.qty.value  ;		 
					  pars += "&unit=" 			+ document.frm.sel_unit.value  ;	
					
					

					
					
		var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){

			var a = eval("("+req.responseText+")");
		    var cat						=	a['Cat'] ;
			var Price					=	a['Price'] ;
			var PriceCost				= 	a['PriceCost'];			
			var paxs					=	a['paxs'] ;		
			var sglsupprice				= 	a['sglsupprice'] ;
			var tplamount				= 	a['tplamount'] ;
			var ManualChange			= 	a['ManualChange'] ;				 
		 	var sgltotal				= 	a['sgltotal'] ;		
			var tpltotal				= 	a['tpltotal'] ;		
			var total					= 	a['total'] ;		
			var result2					= 	a['result2'] ;	
				
			$('pp').innerHTML			= Price ;
			$('sglsupp').innerHTML	= sglsupprice ;
			$('tpldiscnt').innerHTML	= tplamount ;
			document.frm.hid_sgltotal.value	= sgltotal ;
			document.frm.hid_pp.value = Price;		
			document.frm.hid_ppCost.value = PriceCost ;	
			document.frm.hid_sglsupp.value = sglsupprice ;									
			document.frm.hid_tpldiscnt.value 	=  tplamount ;						
			$('result').innerHTML    	= document.frm.qty.value * Price; 
//if( dummy == "no" )
			$('pax').value = paxs ;
			$('total').innerHTML = total ;
			$('sgltotal').innerHTML = sgltotal ;						
			$('result2').innerHTML = result2 ;			
			$('tpltotal').innerHTML = tpltotal ;
				
				if( $F('service') != 'Hotel'  && $F('service') != 'Div' &&   $F('service') != 'SIC' &&  $F('service') != 'RT'  &&  $F('service') != ''  ){
					
					document.frm.sgl.value  = 0 ; 
					document.frm.dbl.value  = 0 ; 
					document.frm.tpl.value  = 0 ; 
					
			}
			
			
		} }); 
	 
		 Dialog.closeInfo() ;	
					
	}
	document.onclick=function(){
			 
			var e 	= document.getElementById('resultNr');
			var e1 	= document.getElementById('result_desc');	
			var e2 	= document.getElementById('result_country');		
		
		// 	var ebk 	= document.getElementById('result_bank');	
			
			e.style.display='none' ;
			e1.style.display='none' ;
			e2.style.display='none' ;	 
		 	
		//	$('result_bank').style.display='none' ;	 

	}
	function searchresult( btn , div , url ){ 


					var 	e = document.getElementById( div );
							e.style.display='block' ;
					
					var pars = Form.serialize("frm") ;  
					
					if( btn == 'btnCountry' ) {	
							
							pars += "&key=all" ;  
					
					}
					
					var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars , onComplete: function(req){
					msg = req.responseText ; 
					
					$(btn).disabled=false ; 
					
					$(btn).value=' v '; 
					
					
					
						if( msg == "NO DATA"){
									e.style.display='none' ;
									resultDiv.style.display='none';
						}else{ 
									e.style.display='block' ;
						 
									$(div).innerHTML = msg ;
									
						}	
						
					 	Dialog.closeInfo() ;	
			 
					
					} });
					 
					$(div).innerHTML = "<img src='images/loading.gif'>&nbsp;";
					
					$(div).innerHTML +="<font face=verdana size='1'>loading...</font>"; 
		
	}
	function searchresultX(  btn , div , url ){ 


					var 	e = document.getElementById( div );
							e.style.display='block' ;
					
					var pars = Form.serialize("frm") ;  
					
					if( btn == 'XX' ) {	
							
							pars += "&key=all" ;  
					
					}
					
					var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars , onComplete: function(req){
					msg = req.responseText ; 
					
					$('btnCountry').disabled=false ; 
					
					$('btnCountry').value=' v '; 
					
					
					
						if( msg == "NO DATA"){
									e.style.display='none' ;
									resultDiv.style.display='none';
						}else{ 
									e.style.display='block' ;
						 
									$(div).innerHTML = msg ;
									
						}	
						
					 	Dialog.closeInfo() ;	
						
				 
					
					} });
					 
					$(div).innerHTML = "<img src='images/loading.gif'>&nbsp;";
					
					$(div).innerHTML +="<font face=verdana size='1'>loading...</font>"; 
		
	}
	function openInfoDialog() {
	Dialog.info("<div align='right'><a href='#' onclick='javascript:window.location.reload();' style='color:black;text-decoration:none;'>&times;</a></div>" , {width:50, height:50, showProgress: true});
} 
function searchresult2( status ){ 
				
	 	var e = document.getElementById("resultNr");
		e.style.display='block' ;
		if( status == 'all' ){
			var 	pars = 'Nr=' ; 
 		}
		else{
			var 	pars = 'Nr=' + $F('Nr'); 
		 
 		}
	var myAjax = new Ajax.Request("autocomplete_result.php",{method:'post',encoding:'UTF-8',parameters:  pars , onComplete: searchmenu_complete2  }); 
		$('resultNr').innerHTML = "<img src='images/loading.gif'>&nbsp;";
		$('resultNr').innerHTML +="<font face=verdana size='1'>loading...</font>"; 
 
	}
function searchmenu_complete2(req){
			$('btn_Nr').disabled=false; 
			$('btn_Nr').value=' v ' ; 
			msg = req.responseText ; 
		 	var ex = document.getElementById("resultNr");
		if( msg == "NO DATA"){
				ex.style.display='none' ;
				resultDiv.style.display='none';
		}else{ 
					ex.style.display='block' ;
 					$('resultNr').innerHTML = msg ;
		}
		
	}
function DateDiff(from, until, format){
  var past = from == '' ? new Date() : new Date(from);
  var future = until == '' ? new Date() : new Date(until);

  if(past >= future){
   var tmp = past;
   past = future;
   future = tmp;
  }

  var between = [
   future.getFullYear() - past.getFullYear(),
   future.getMonth() - past.getMonth(),
   future.getDate() - past.getDate()
  ];

  if(between[2] < 0){
   between[1]--;
   var ynum = future.getFullYear();
   var mlengths = [31, (ynum % 4 == 0 && ynum % 100 != 0 || ynum % 400 == 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
   var mnum = future.getMonth() - 1;
   if (mnum < 0){ mnum += 12; }
   between[2] += mlengths[mnum];
  }

  if(between[1] < 0){
   between[0]--;
   between[1] += 12;
  }

  return formatDateDiff(between, format);
 }
function formatDateDiff(difference, format){
  var str = '';

  if(format == "year"){
   if(difference[0] > 0){
    str += difference[0] + ' year';
    str += difference[0] == 1 ? '' : 's';
   }
  }else if(format == "month"){
   if(difference[1] > 0){
    str += difference[1] + ' month';
    str += difference[1] == 1 ? '' : 's';
   }
  }else if(format == "day"){
 
     str += difference[2]  ;
  }else{
   if(difference[0] > 0){
    str += difference[0] + ' year';
    str += difference[0] == 1 ? '' : 's';
    if (difference[1] > 0){
     str += difference[2] > 0 ? ', ' : ' and ';
    }else{
     str += difference[2] > 0 ? ' and ' : '';
    }
   }

   if(difference[1] > 0){
    str += difference[1] + ' month';
    str += difference[1] == 1 ? '' : 's';
    str += difference[2] > 0 ? ' and ' : '';
   }

 
     str += difference[2] ; 
   
  }

  return str;
 }
function unloadPage(){
 	
		if( document.frm.desc_val.value.length > 0 ){
		
				if( confirm("Add current record?"))  {
								  
								  	no_update('no');
								
									add_list(0);	
									
								 $('addBtn').value='please wait while inserting...' ;   $('addBtn').disabled=true;
						 
				 	}else{
					
					
					
										var 		url 		  = "booking_itinerary_data4.php" ;
			 							var   	pars 	  = "tourid=<?=$id?>" ; 
			 										pars 	+= "&duedate=<?=$dateon?>";
						 
												var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars , onComplete: function(req){
															msg = req.responseText ; 
										 
												window.opener.document.location.reload(); 
												window.location = "booking_itinerary.php?id=<?=$id?>";		

											//window.opener.document.location.reload(); 
											//window.close(); 
											}});
											
					
					
				}
				
		}else {
		
										var 	  	url 		  = "booking_itinerary_data4.php" ;
			 							var   	pars 	  = "tourid=" + document.frm.hid_TourID.value ; 
			 								//		pars 	+= "&duedate=" + document.frm3.due_on.value;
									 
												var myAjax = new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars , onComplete: function(req){
															msg = req.responseText ; 
											//	  alert( msg ) ;
													
											
											try{							
					 //win = window.opener;
					    	
					 	//window.location='booking_edit.php?id=<?=$id?>' ;// window.opener.document.location.reload() ;
					  
					 window.opener.document.location.reload(); 
												window.location = "booking_itinerary.php?id=<?=$id?>";		
						// window.close();
				}catch( er )	{
					
					 	window.location='booking_edit.php?id=<?=$id?>' ;//window.close(); //alert( er.description ) ; 
				}	
											}});
											
  						 
 }
 
 }
 function checkAll(checkname, exby) {
  
 	if( ! checkname.length ){
		
		
			checkname.checked = exby.checked? true:false ;

	}else{
	 
        if( deleteRedBgColor('') == false ){ 
					  alert('Please update red line before you save change '); 
				  	  $('checkbox').checked = false;
					  return false;
		}else{
						for (i = 0; i < checkname.length; i++){
							checkname[i].checked = exby.checked? true:false ;
						}
		}
	}


		 
	 

}  
</script> 
<link href="/jslib/ajax/themes/default.css" rel="stylesheet" type="text/css" ></link>
<link href="/jslib/ajax/themes/spread.css" rel="stylesheet" type="text/css" ></link>
<link href="/jslib/ajax/themes/alert.css" rel="stylesheet" type="text/css" ></link>
<link href="/jslib/ajax/themes/alert_lite.css" rel="stylesheet" type="text/css" ></link>
<link href="/jslib/ajax/themes/alphacube.css" rel="stylesheet" type="text/css" ></link>
<link href="/jslib/ajax/themes/debug.css" rel="stylesheet" type="text/css" ></link>


<link href="style.css" rel="stylesheet" type="text/css" />
 
<style type="text/css" media="screen">
    .selected { background-color: #888; }
	  ul.contacts  {
      list-style-type: none;
      margin:0px;
      padding:0px;
    }
  .style1 {
	color: #666666;
	font-weight: bold;
}
 body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px; 
}
 .style2 {
	font-size: 18px;
	font-weight: bold;
}
.style3 , td {
	font-size:8pt;
	color:black;
	font-weight:lighter;
	white-space: nowrap;
}
 input{
	font-size:7.5pt;
	color:black;
	font-weight:lighter;
	 
	 
}
.style5 {color: #00CCFF}
.style9 {font-size: 9px; }
</style>
</head>
<body 
class="style3" 
onMouseOver="fixhotel();"  
onMouseOut="fixhotel()" 
onClick=" fixhotel();" 
onLoad="showRate($('select_invoicebank').value) ; setColor() ;  ">  
<span id="msgError" style="font-family:system;color:red;font-weight:bold;background-color:#E5E5E5;font-size:14px;position: absolute; left: 0px; top: 0px;"></span>
<div class="style3" id="edit">
  <table width="100%" border="0" cellpadding="0" cellspacing="1" class="style3">
 
  <tr>
      <td colspan="6">
      <hr size="1" color="black" /></td>
    </tr>    
  

  <tr>
      <td>&nbsp;<span class="style2">Itinerary</span>&nbsp;&nbsp;&nbsp;ID:&nbsp;<strong><?=$id?></strong></td>
      <td>start of tour:&nbsp;<strong><?= ($start_of_tour)?></strong></td>
      <td>end of tour:&nbsp;<strong><?= ($end_of_tour)?></strong> </td>
      <td>pax:&nbsp;<strong><?=$no_of_pax?></strong></td>
 
      <td>booked by:<strong>&nbsp;<?=$Company?> </strong></td>
      <td><div align="right"><span style="cursor:pointer">
        
        <input name="button3" id="button3" type="button" style="font-weight:bold;font-size:10pt; color:#660000;display:none;" value="B A C K" onClick="$('button3').disabled=true;$('button33').disabled=true;unloadPage();" />
        
      </span></div></td>
  </tr>
    <tr>
      <td colspan="6">
      <hr size="1" color="black" />	</td>
    </tr>
 
    <tr>
      <td colspan="6"><form method="post" id="frm" name="frm">
        
          
          <div align="right">
            <strong>
            <?=($OldId)?" ( $OldId )":'' ?>
            </strong>
            <input type="hidden" name="hid_TourID" id="hid_TourID" value="<?=$id?>">
              
            <input name="change" type="hidden" id="change" value="no">  
            <span style="cursor:pointer">
            <input name="button" type="button" style="font-weight:bolder; color: #660000;" value="&radic; recalculate" onClick="  no_update('no'); " />
            </span> </div>
          <table width="100%" border="0" cellpadding="2" cellspacing="1" >
        <tr valign="top">
          <td bgcolor="#BBBBBB"><div align="center">From/on</div></td>
          <td bgcolor="#CDCDCD"><div align="center">To</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Company</div></td>
          <td bgcolor="#CDCDCD"><div align="center">Country</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Service</div></td>
          <td bgcolor="#CDCDCD"><div align="center">Days</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Description</div></td>
          <td bgcolor="#CDCDCD"><div align="center">Cat</div></td>
          <td bgcolor="#BBBBBB"><div align="center">sgl</div></td>
          <td bgcolor="#CDCDCD"><div align="center">dbl</div></td>
          <td bgcolor="#CDCDCD" style="display:none"><div align="center">twn</div></td>
          <td bgcolor="#BBBBBB"><div align="center">tpl</div></td>
          <td bgcolor="#CDCDCD"><div align="center">p.p.</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Qty</div></td>
          <td bgcolor="#CDCDCD"><div align="center">Unit</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Sum</div></td>
          <td bgcolor="#CDCDCD"><div align="center">Pax</div></td>
          <td bgcolor="#BBBBBB"><div align="center">Total</div></td>
          </tr>
 


 <tr>

<td align="center" bgcolor="#BBBBBB"> 

<INPUT tabindex="1" NAME="Date_from" id="Date_from" TYPE="text" class="style3" SIZE="11" value="<?= $start_of_tour ?>"  
onchange="this.value = changeFormat(this.value , 1 ); isDateOutOfRange(this, '<?=$start_of_tour?>', '<?=$end_of_tour?>'); " onMouseOver="fixhotel()" onMouseOut="fixhotel()"></td>
 			
<td align="center" bgcolor="#CDCDCD">

<input tabindex="2" name="Date_to" type="text" id="Date_to" class="style3" value="<?= $end_of_tour ?>" size="11"  onchange="this.value = changeFormat(this.value , 1); isDateOutOfRange(this, '<?=$start_of_tour?>', '<?=$end_of_tour?>');" onMouseOver="fixhotel()"  onMouseOut="fixhotel()" /></td>
			
			<td align="center" bgcolor="#BBBBBB">

            <? $rsNr =   loadData("Nr" ,$Nr ) ; ?>
<input name="Nr" tabindex="3" type="text"  class='booking bold' id="Nr" onClick="this.select()"  onKeyUp="if(event.keyCode== 13){searchresult2('');}else{return false;} "   style="width: 40px;" autocomplete="off" value="<? $shortcut=$rsNr->Fields("Shortcut") ; echo $shortcut ?>" /><input type="hidden" name="nr_val" id="nr_val" value=""><span id="btnNr"><input name="btn_Nr" id="btn_Nr" type="button" class="style3" value=" v "  ONCLICK="  searchresult2('all') ; frm.description.value=''; frm.desc_val.value='';  $('btn_Nr').value='waiting..' ; $('btn_Nr').disabled=true; " /></span></td><td bgcolor="#CDCDCD"><div align="center">

			 
              <input tabindex="4" style="width:49px" type="text" id="country" name="country" value="<?=$Ccode?>" 
              onChange=" frm.description.value=''; frm.desc_val.value=''; " 
              onKeyUp="frm.description.value=''; frm.desc_val.value=''; searchresultX( 'X' , 'result_country' , 'country.php' );   $('btnCountry').disabled=true ; $('btnCountry').value=' waiting.. ';  " /><input tabindex="500" name="btnCountry" id="btnCountry" type="button" class="style3" value=" v "  
          onClick="frm.description.value=''; frm.desc_val.value=''; searchresultX( 'XX' , 'result_country' , 'country.php' );      " />
             
</div></td>
          <td bgcolor="#BBBBBB"><div align="center">
<? $service  = array( "" , "Trsf" , "CT" , "Exc" , "Hotel" , "Flight" , "RT" , "SIC" , "Div" ) ;?>
 
            <select tabindex="5" name="service" id="service" onChange="frm.description.value=''; frm.desc_val.value=''; fixhotel(); ">
              <? for($i = 0 ; $i < count($service) ; $i++) { ?>
              <option value="<?=$service[$i]?>"><?=$service[$i]?></option>
              <? } ?>
              </select>
            </div></td>
          <td bgcolor="#CDCDCD"><div align="center">
            <input tabindex="6" name="Days" type="text" id="Days" size="3" onkeypress ="frm.description.value=''; frm.desc_val.value='';" />
            </div>		  </td>
          <td align="center" bgcolor="#BBBBBB">
		  <input type="hidden" name="desc_val" id="desc_val" value="">
		  <input type="hidden" id="hidManualChng" name="hidManualChng" value="">
		  <input tabindex="7" 
          
          onKeyUp="if( event.keyCode == 13 ){ searchresult( 'btnDesc' , 'result_desc' , 'booking_itinerary_data6.php' ); } else{ return false; }" 
          
          name="description" type="text"  class='booking bold' id="description" style="width: 120px;" autocomplete="off"/><span id="btnDesc"><input name="btnDescr" id="btnDescr" type="button" class="style3" value=" v "  
          onClick="   searchresult( 'btnDescr' , 'result_desc' , 'booking_itinerary_data6.php' );   $('btnDescr').disabled=true ; $('btnDescr').value=' waiting.. '; openInfoDialog(); " /></span></td>
		  
		  <td align="center" bgcolor="#CDCDCD"><span id="cat_id">
		    <select name="cat" id="cat" onChange="no_update( 'no' );" tabindex="8">
		  		<option value="">&nbsp;&nbsp;&nbsp;&nbsp;</option>
                <option value="Std">Var A</option><option value="Sup">Var B</option><option value="Dlx">Var C</option>
           	  </select></span>            </td>
          <td bgcolor="#BBBBBB"><div align="center">
            <input name="sgl" type="text" id="sgl" value="0" size="3" style="text-align:right" tabindex="9" onChange=" no_update('yes'); "      />
            </div></td>
          <td bgcolor="#CDCDCD"><div align="center">
            <input name="dbl" type="text" id="dbl" value="0" size="3" style="text-align:right" tabindex="10" onChange=" no_update('yes'); "  />
            </div></td>
          <td bgcolor="#CDCDCD" style="display:none"><div align="center">
            <input name="twn" type="text" id="twn" value="0" size="3" style="text-align:right" tabindex="10" onChange=" no_update('yes'); "  />
            </div></td>
          <td bgcolor="#BBBBBB"><div align="center">
            <input name="tpl" type="text" id="tpl" value="0" size="3" style="text-align:right;" tabindex="11" onChange=" no_update('yes'); "   />
            </div></td>
          <td bgcolor="#CDCDCD"><div align="right"><span id="pp"></span>&nbsp;</div></td>
          <input type="hidden" id="hid_pp"  name="hid_pp" value="">
          <input type="hidden" id="hid_ppCost"  name="hid_ppCost" value="">
          
          <td bgcolor="#BBBBBB"><div align="center">
            <input name="qty" type="text" id="qty" value="1" size="1" style="text-align:right" tabindex="12" onChange=" no_update('no'); "      />
            </div></td>
          <td bgcolor="#CDCDCD"><div align="center">
		  <?
		  $arr_unit =array("o/n","o/n BF","o/n HB","o/n FB","tour","day","hrs.","tkts","trsf","") ; 
		  ?>
            <select name="sel_unit" id="sel_unit" tabindex="13" onFocus=" no_update('no');">
            <? for( $i = 0 ; $i < count($arr_unit) ; $i++){?>
				<option value="<?=$arr_unit[$i]?>" <?=($i==9 )?" selected = \"selected\" ":""?>><?=$arr_unit[$i]?></option>
			<? } ?>
			  </select>
            </div></td>
          <td align="center" bgcolor="#BBBBBB"><span id="result">&nbsp;</span></td>
          <td bgcolor="#CDCDCD"><div align="center">
          
            <input tabindex="14" name="pax" type="text" id="pax" value="<?=$no_of_pax?>" style="text-align:right" size="3" onKeyUp=" no_update('no');" />
            
         
			            
            </div></td>
          <td bgcolor="#BBBBBB"><div align="right"><strong><span id="total"></span></strong>&nbsp;</div></td>
          </tr>
        <tr>
          <td><span id="result_desc" style="background-color:#CCCCCC; position:absolute; display:none; width: 910px; height: 450px; overflow: auto; padding:2px; border: 1px solid black;  cursor:pointer;" class="style3"></span>&nbsp;</td>
          <td valign="top">&nbsp;</td>
          <td valign="top"><span id="resultNr" style="cursor:pointer;background-color:#CCCCCC; position:absolute; display:none; width: 525px; height: 250px; overflow: auto; padding:2px; border: 1px solid black;" class="bookingsmall" ></span></td>
          <td valign="top"><p><span id="result_country" style="background-color:#CCCCCC; position:absolute; display:none; width: 80px; height: 200px; overflow: auto; padding:2px; border: 1px solid black;  cursor:pointer;" class="style3"  ></span>
          &nbsp;</p></td>
          <td>&nbsp;</td>
          <td colspan="3" valign="top" align="center"><span id="valid" style="font-size:10px;font-family:verdana"></span>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td  style="display:none">&nbsp;</td>
          <td>&nbsp;</td>
          <td><div align="right"><span id="sglsupp"></span>&nbsp;</div></td>
          <input type="hidden" name="hid_sglsupp" id="hid_sglsupp" value="">
          <td colspan="2"><span class="style1">sgl supp </span></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td><div align="right"><strong><span id="sgltotal"></span></strong>&nbsp;</div></td>
         <input type="hidden" name="hid_sgltotal" id="hid_sgltotal" value="">
        </tr>
       
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td style="display:none">&nbsp;</td>
          <td>&nbsp;</td>
          <td><div align="right"><span id="tpldiscnt"></span>&nbsp;</div></td>
          <td colspan="2"><span class="style1">tpl discount </span></td>
         <input type="hidden" name="hid_tpldiscnt" id="hid_tpldiscnt" value="">          
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td><div align="right"><span id="tpltotal" style="text-decoration:underline;font-weight:bold;"></span>&nbsp;</div></td>
        </tr>
         <tr class="style1">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td style="display:none">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td colspan="5"><span style="display:block" class="style1" id="multiline">multiline    
          <select id="multi" name="multi" class="style1">
          <option value="1">&nbsp;</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
          <option value="8">8</option>
          <option value="9">9</option>
          <option value="10">10</option>
          </select>
          </span>&nbsp;</td>
          </tr>
        <tr>
          <td colspan="18"><div align="right"><strong><span id="result2"></span></strong>&nbsp;</div></td>
        </tr>
        <tr>
          <td colspan="18">
			<div align="center">  
				<input type="hidden" id="op_amend" name="op_amend">
				<input type="hidden" id="special" name="special" value=""> 
				<input tabindex="15" type="button" name="addBtn2" id="addBtn2" value="Book to amend" 
					style="font-weight:bold;border-style:outset" 
					onClick="$('op_amend').value='a'; book_to_amend($F('change'));" />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="hidden" id="special" name="special" value=""> 
				<input tabindex="15" type="button" name="addBtn3" id="addBtn3" value="Delete to amend" 
					style="font-weight:bold;border-style:outset" 
					onClick="$('op_amend').value='d'; delete_to_amend();" />				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input tabindex="15" type="button" name="addBtn" id="addBtn" value="Add Record" 
					style="font-weight:bold;border-style:outset" 
					onClick="openInfoDialog();  $('addBtn').value='please wait while inserting...' ;add_list($F('change'));" />
				
			</div>
		</td>
          </tr>
      </table>
      </form></td>
    </tr>
	 
    <tr>
      <td colspan="6"><HR color="black" size="1"></td>
    </tr>
	   <tr>
      <td colspan="6">&nbsp;</td>
    </tr>
    <tr>		
      <td colspan="6">
	  <div id="result_add" >
      <?
	  
		
$rs  = loadData("conf2" , $id ) ;

if( ! $rs->EOF  ) {   	   ?>
<script language="javascript">
function doHey(){
	              if( deleteRedBgColor('') == true ){ 
					  $('btnSave2').value='Please wait while updating.. ' ; 
					  update_list('update' , 0);
					  }else{ 
					  alert('Please update red line before you save change '); $('btnSave2').disabled=false;return false; } 
				}
function doDeleteEach( val1 , val2  , val3  ){

							if( deleteRedBgColor(val2) == true ){
									return true; 
							}else{ 
								alert('Please update red line before you delete this line'); 
								return false; 
							}
				}
function get_check_value()
{


		var op_amend = $F('op_amend');


			var c_value = "";
			
			if(document.frm2.chk.value && document.frm2.chk.checked)	
					var c_value = document.frm2.chk.value ;
			else
					var chkLn = document.frm2.chk.length ;
			
			for (var i=0; i < chkLn ; i++)
			{
				if (document.frm2.chk[i].checked)
				{
					c_value =  c_value + "'" +document.frm2.chk[i].value + "',";
				}
			}
			
			
			if( c_value.length  == 0    ){
			
					alert('Please choose line you want to delete..') ;
					//$("msgError").innerHTML = 'Please choose line you want to delete..';
					return false;	
			} else{
					
				 
					
					 if( confirm('Are you sure to delete ?') ){
							$("msgError").innerHTML = 'PLEASE WAIT....';
							var 	url 		=   "booking_confirm3.php" ;
							var			pars 		= "&mode=deleteAll&tourid=<?=$id?>&chkAll=" + c_value + '&amend=' + op_amend ;
							var myAjax 	= new Ajax.Request(url,{method:'post',encoding:'UTF-8',parameters:  pars, onComplete: function(req){
							var msg 	= req.responseText ;
					
							//alert(msg);
							$("msgError").innerHTML = msg;
						 	if( msg.indexOf("CAN NOT") == -1 ){
									window.location = "booking_itinerary.php?id=<?=$id?>";		
							}
							
						}});
						 
					}else{
						return false; 
					}
	
			}
		 

}
</script>
<form id="frm2" name="frm2">
	  <table width="100%" border="0" align="center" cellpadding="1" cellspacing="1"  id="myTablex">
	  <tr>
	    <td bgcolor="#FFFFCC"><div align="center"><img src="images/Delete.gif" width="24" height="24" onclick = " get_check_value() ;   "></div></td>
	    <td colspan="17" bgcolor="#FFFFCC"><div align="right">
            <input type="button" value="     save changes     " id="btnSave2" name="btnSave2"  onClick="this.disabled=true;this.value='Please wait while updating.. '; setTimeout('doHey();', 0.01); "  >
                </div></td>
	    </tr>
	  <tr>
		<td bgcolor="#FFFFCC"><div align="center">
		  <input type="checkbox" name="checkbox" id="checkbox" onClick="checkAll(document.frm2.chk,this)"/>
		  </div></td>
		<td bgcolor="#FFFFCC"><div align="center" class="style9">Pos</div></td>
		<td bgcolor="#FFFFCC"><div align="center" class="style9">From/on</div></td>
		<td bgcolor="#FFFFCC"><div align="center" class="style9">To</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Comp</div></td>
	    <td width="30" bgcolor="#FFFFCC"><div align="center" class="style9">Service</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Description</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Class</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Cat</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Type</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">p.p.</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Qty</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Unit</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Sum</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Pax</div></td>
	    <td bgcolor="#FFFFCC"><div align="center" class="style9">Total</div></td>
	    <td colspan="2" bgcolor="#FFFFCC"><div align="center"><img src="images/eyes.jpg" width="18" height="18"></div></td>
	    </tr>
<?

$setID = $_REQUEST['setID'];

$cnt = 0 ;
	
	// $totally = $result ->Size() ;
	$totally = $rs ->RecordCount() ;
	 
	//while( $rs = $result -> Next() ){ 
	while( ! $rs -> EOF  ){ 

	$cnt++ ; 
	
	 if($bg=="#cccccc")  $bg = "#e4e4e4"; else $bg = "#cccccc";
 
			unset( $col1 ,  $col2 ,  $col3 ,  $col4 ,  $col5 ,  $col6 ,  $col7 ,  $col8 ,  $col9 ,  $col10 ,  $col11 ,  $col12 ,  $col13 ,  $col14 ,  $col15 ,  $col16 ,  $col17, $col18 ,  $col19 ,  $col20 ,  $col21, $col22, $col23 ); 
 
			$col1  	= $rs->Fields("pos") ; // POS
			$col2  	= $rs->Fields("DateFrom") ; // FROM
			$col3  	= $rs->Fields("DateTo"); // TO
			$col4  	= $rs->Fields("Comp") ; // COMP
			$col5  	= $rs->Fields("QuotationCategory") ; // SERVICE
			$col6  	= $rs->Fields("QuotationName") ; //$rs->Fields("Unit") ; // DESC
			$col7  	= $rs->Fields("Category") ; // CAT
			$col8  	= $rs->Fields("Room"); // TYPE 
			$col9  	= number_format($rs->Fields("Confpricepp"),2) ; // P.P.
			$col10 	= $rs->Fields("Quantity") ;//QTY
			$col11 	=  $rs->Fields("Units")  ;// UNIT
			$col12 	= number_format($rs->Fields("result"),2); // SUM
			$col13 	= $rs->Fields("Pax") ; // PAX
			$col14 	= $rs->Fields("Company")  ; // COMPANY
			$col15 	= number_format($rs->Fields("Total"),2) ; // TOTAL													
			$col16 	= $rs->Fields("show") ; // SHOW			 										
			$col17 	= $rs ->Fields("ID") ;
			$col18  = $rs->Fields("Ratex") ;
			$col19  = $rs->Fields("Codex") ;
			$col20  = $rs->Fields("Ccode") ;	
			$col21  = $rs->Fields("ManualChange") ;	
			 $col22  = $rs->Fields("Description") ;	
			 $col23 = $rs->Fields("BookingClassId");
	// Quotation.Code
		$oldbg = $bg ;	
	   ?>
   <? $arr_unit =array( " " ,"o/n","o/n BF","tour","day","hrs.","tkts","Ü","Ü/F","Tour","Tag","Std.","Tkts"   ) ;  ?>
<tr valign="top" id="<?=$col17?>" <?php if( strlen($setID) > 0 ){

	 	$newbg = ( strstr( $setID , $col17 ) )?"#cc6666":"$oldbg";
		
		echo " style='background-color:$newbg' onKeyUp=\"if( event.keyCode == 13 ){ setBgColor('$oldbg' , '$col17') }\" ";
		
	 }else echo " style='background-color:$oldbg' "?>
rateid="<?=$col18?>">
  <td align="center"><?php /*?>   <a href="#" id="imgDel2<?=$cnt?>" name="imgDel2<?=$cnt?>" onClick=" setTimeout( 'doDeleteEach(\'<?=$cnt?>\' , \'<?=$col17?>\' , \'<?=$col19?> from <?=$col2?> to <?=$col3?>\')' , 1 ) ">X</a><?php */?>
      
      <?
	  
	  $flagChk = '' ; $sqlX = '' ;
	  
      $sqlX= "SELECT InvDetailId FROM InvoiceDetails WHERE ConfirmationsId = '$col17' " ;
	  $RSX = $db->Execute($sqlX )  or die($db->ErrorMsg() ) ;
	  	if( ! $RSX->EOF  ) {
			 $flagChk  = " disabled = \"disabled\" " ;
			 echo "Inv." ;
		}else{ 
			 
	  ?>
      <input type="checkbox" id="chk" name="chk[]" value="<?=$col17?>" onClick="setTimeout( 'doDeleteEach(\'<?=$cnt?>\' , \'<?=$col17?>\' , \'<?=$col19?> from <?=$col2?> to <?=$col3?>\'  )' , 1 ) "  <?=$flagChk  ;?>>  
     <? } ?>
      </td>
 <td><div align="center">
          <input type="hidden" id="country<?=$cnt?>" name="country<?=$cnt?>" value="<?=$col20?>" />
          <input type="hidden" id="manual<?=$cnt?>" name="manual<?=$cnt?>" value="<?=$col21?>" />
          <input type="hidden" id="desc_val<?=$cnt?>" name="desc_val<?=$cnt?>" value="<?=$col18?>" />
          <input style="text-align:right;background-color:#99CCCC;border:thin;" type="text" name="pos<?=$cnt?>" id="pos<?=$cnt?>" value="<?=$col1?>" size="1" />
        </div></td>
        <td><div align="center">
		  <input  name="from<?=$cnt?>" type="text" id="from<?=$cnt?>"  style="background-color:#99CCCC;border:thin;" onChange="this.value = changeFormat(this.value , 1 )" value="<?= ($col2)?>" size="12"/> 
		  
		  </div></td>
		<td><div align="center">
		  <input name="to<?=$cnt?>"  type="text" id="to<?=$cnt?>" style="background-color:#99CCCC;border:thin;" onChange="this.value = changeFormat(this.value , 1)"  value="<?= ($col3)?>" size="13"   />
		  
		  </div></td>
	    <td><div align="center"><span id="comp<?=$cnt?>"><?=$col4?></span></div></td>
	    <td><div align="left"><span id="service<?=$cnt?>"><?=$col5?></span></div></td>
    <? if( $flagChk == '' ) { ?>    
	    <td align="center" valign="top" title="Double Click Here if you want to change the price."  onDblClick="$('myTablex').getElementsBySelector('[rateid=<?=$col18?>]').invoke('hide') ; updateList( '<?=$col18?>' , '<?=$cnt?>' , '<?=$id?>' ) ;"><span id="description<?=$cnt?>" style="display:none"><?=$col6?></span>
<textarea  id="desc<?=$cnt?>" name="desc<?=$cnt?>"  style="width:330px;"><?=($col22=='')?$col6:$col22?></textarea><br>
<span class="style5"><a href="#" onClick="var win = window.open('/quoteonline/editQuotepure.php?QuotationId=<?=$col18?>&ssid=<?=$_SESSION['ssid']?>&isid=<?=$_SESSION['isid']?>&v=2','qwin'); win.focus();" style="text-decoration:inherit;font-size:9px;"><?=$col19?></a></span></td>
<? } else { ?>
 
<td align="center" valign="top" title=""  onDblClick="alert('sorry this function is not available .')"><span id="description<?=$cnt?>" style="display:none"><?=$col6?></span>
<textarea  id="desc<?=$cnt?>" name="desc<?=$cnt?>"  style="width:330px;"><?=($col22=='')?$col6:$col22?></textarea><br>
<span class="style5"><a href="#" onClick="var win = window.open('/quoteonline/editQuotepure.php?QuotationId=<?=$col18?>&ssid=<?=$_SESSION['ssid']?>&isid=<?=$_SESSION['isid']?>&v=2','qwin'); win.focus();" style="text-decoration:inherit;font-size:9px;"><?=$col19?></a></span></td>
 
<? } ?>

	    <td><div align="center"><span id="class<?=$cnt?>"><?=HtmlHelper::SelectorList($booking_class, "BookingClassId".$cnt, $col23, "", "hash");?></span></div></td>
	    <td><div align="center"><span id="cat<?=$cnt?>"><?=$col7?></span></div></td>
	    <td><div align="center"><input name="type<?=$cnt?>" type="text" id="type<?=$cnt?>" style="background-color:#99CCCC;border:thin;" value="<?=$col8?>" size="3" /></div></td>
	    <td><div align="center">    <input name="pp<?=$cnt?>" type="text" 
              style="text-align:right;background-color:#99CCCC;border:thin;" id="pp<?=$cnt?>" 
              value="<?=$col9?>" 
              size="5" 
              onChange="$('valz<?=$cnt?>').innerHTML  =  document.frm2.pp<?=$cnt?>.value.replace(',','') * document.frm2.qty<?=$cnt?>.value ;$('valSubTotalz<?=$cnt?>').innerHTML =  parseFloat( document.frm2.pp<?=$cnt?>.value)*document.frm2.qty<?=$cnt?>.value*document.frm2.pax<?=$cnt?>.value ;calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$totally ?>' ) ; "
              onKeyPress="$('valz<?=$cnt?>').innerHTML =   document.frm2.pp<?=$cnt?>.value.replace(',','')  * document.frm2.qty<?=$cnt?>.value ; $('valSubTotalz<?=$cnt?>').innerHTML =  parseFloat( document.frm2.pp<?=$cnt?>.value ) * document.frm2.qty<?=$cnt?>.value * document.frm2.pax<?=$cnt?>.value ; calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$totally ?>' ) ; "/> 
              
              
	      </div></td>
	    <td><div align="center">
          
          <input  name="qty<?=$cnt?>" 
                      
                  type="text" 
                  
                  id="qty<?=$cnt?>" 
                 
                  value="<?=$col10?>" 
                  
                  size="1" 
                 
                  style="text-align:right;background-color:#99CCCC;border:thin;" 
                  
                  onChange="$('valz<?=$cnt?>').innerHTML =  document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value ;$('valSubTotalz<?=$cnt?>').innerHTML =  parseFloat(document.frm2.pp<?=$cnt?>.value)*document.frm2.qty<?=$cnt?>.value*document.frm2.pax<?=$cnt?>.value ;calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$totally?>' ) ; "
                  
                  onKeyUp="$('valz<?=$cnt?>').innerHTML =  document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value ;$('valSubTotalz<?=$cnt?>').innerHTML =  parseFloat(document.frm2.pp<?=$cnt?>.value)*document.frm2.qty<?=$cnt?>.value*document.frm2.pax<?=$cnt?>.value ;calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$totally ?>' ) ; " />
                      
                      
                      
	    </div></td>
	    <td width="60">
        
	           <input type="text" onKeyUp="if(event.keyCode==13){$('result_unit<?=$cnt?>').style.display = 'block' ;}return false ;" id="unit<?=$cnt?>" name="unit<?=$cnt?>" value="<?=$col11?>" size="5" style="background-color:#99CCCC;border:thin;"><input onClick="$('result_unit<?=$cnt?>').style.display = 'block' ;" type="button" id="btnunit" name="btnunit" value="&bull;" style="height:17px;width:16px;"><br><div id="result_unit<?=$cnt?>" style="display:none;position:absolute;">
	            <table width="94%"  border='0' cellpadding='2' cellspacing='0' bgcolor="white" class='bookingsmall' >
				 <? foreach( $arr_unit as $val ) { ?>  
                   
                 <tr style="cursor:pointer;height:22px;"  valign='top'  onmouseover="bgColor='#e4e4e4'" onMouseOut="bgColor='#cccccc'"   bgcolor="#cccccc"
    onclick="$('unit<?=$cnt?>').value='<?=$val?>' ; $('result_unit<?=$cnt?>').style.display = 'none' ;"   > 
          
                <td><?=$val?></td>
                </tr>
                   <tr height="1"> 
    
    <td height="1"></td>
  </tr>
             <? } ?>
                 </table>
              </div></td>
	    <td align="right"><span id="valz<?=$cnt?>"><?=$col12?></span></td>
	    <td align="center">
        
     
              
        <input name="pax<?=$cnt?>" type="text" id="pax<?=$cnt?>" style="text-align:right;background-color:#99CCCC;border:thin;" value="<?=$col13?>" size="2"  
        
        onChange="$('valz<?=$cnt?>').innerHTML = document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value ;$('valSubTotalz<?=$cnt?>').innerHTML =  document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value*document.frm2.pax<?=$cnt?>.value ;calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$rs->RecordCount() ?>' ) ; "
        
        
              onkeyup="$('valz<?=$cnt?>').innerHTML = document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value ;$('valSubTotalz<?=$cnt?>').innerHTML =  document.frm2.pp<?=$cnt?>.value.replace(',','') *document.frm2.qty<?=$cnt?>.value*document.frm2.pax<?=$cnt?>.value ;calprice( <?=$cnt?> , document.frm2.pax<?=$cnt?>.value , '<?=$rs->RecordCount()  ?>' ) ; " />              </td>
	    <td align="right"><span id="valSubTotalz<?=$cnt?>"><?=$col15?></span></td>
	    <td colspan="2" align="center"><input type="checkbox" name="chkV<?=$cnt?>" id="chkV<?=$cnt?>"  value="Yes" <?=($col16==1)?"checked":""?>   /></td>
	    </tr>
	  
	<input type="hidden" name="hid_id[]" id="hid_id[]" value="<?=$col17?>">
		 
	<? 
	 $rs->MoveNext(); 
	}
	 
	
	$rsSum = loadData( "sumItin" , $id ) ;
	
 	if( !$rsSum -> EOF ){
		 
		$sumx = $rsSum->Fields("sumx") ;
		
	}
 		
	?>	
	  <tr bgcolor="<?=$bg?>">
	    <td align="center" bgcolor="<?=$bg?>"><div align="center"><img src="images/Delete.gif" width="24" height="24" onclick = " get_check_value() ;   "></div></td>
	    <td colspan="14" align="center" bgcolor="<?=$bg?>"><div align="right">Invoice amount: US$ </div></td>
	    <td bgcolor="#FFFFCC" align="center"><div align="right">
	      <span id="totalz"><?=number_format($sumx,2)?></span>
	      </div></td>
	    <td colspan="2" align="center" bgcolor="<?=$bg?>">&nbsp;</td>
	    </tr>
	  <tr bgcolor="<?=$bg?>">
	    <td colspan="18" bgcolor="#FFFFCC" align="center">
	      
	        <div align="right">
            
            <input type="button" value="     save changes     " id="btnSave" name="btnSave"  onClick="this.disabled=true;this.value='Please wait while updating.. '; setTimeout('doHey();', 0.01);"  >
	        </div></td>
	    </tr>
	</table>
	</form>
		<? } ?>
	  </div>	  </td>
    </tr>
    <tr>
      <td colspan="6"><HR color="black" size="1"></td>
    </tr>
    <tr>
 
      
      
      <td colspan="6"><div align="right"><span style="cursor:pointer">
        
        <input name="button33" id="button33" type="button" style="font-weight:bold;font-size:10pt; color:#660000;display:none;" value="B A C K" onClick="$('button3').disabled=true;$('button33').disabled=true;unloadPage();" />
        
      </span></div></td>
    </tr>
</table> 
<script language="javascript">
	function saveCommision(){
		try{
				var url =   "update_commision.php" ;
				var pars = $('frmComm').serialize() ;
				var myAjax = new Ajax.Request(url
					, {
						method:'post'
						, encoding:'UTF-8'
						, parameters:pars
						, onComplete: function(req){
							msg = req.responseText ;
							alert(msg) ;												
						}
					});	
		}catch(e){
			alert(e.description);
		}
	 }
</script>
<form id="frmComm" name="frmComm">
<input type="hidden" name="tour_id" id="tour_id" value="<?=$id?>" />
<table style="font-size:12px;">
<tr>
	<th>Sale Person</th>
	<th><select id="sale_person" name="sale_person">
			<option value="">-select-</option>
			<?php
				foreach($array_sale_person as $key => $value){
					echo '<option value="'.$key.'"';
					if($key == $ICSSale){
						echo ' selected="selected" ';
					}
					echo '>'.$value.'</option>';
				}
			?>
			</select>
	</th>
	<th><input type="text" name="commision_value" id="commision_value" size="3" value="<?php echo $TACommision;?>">&nbsp;% of TA commision</th>
	<th><input type="button" value="Save Commision" id="btnComm" name="btnComm" onclick="saveCommision();" /></th>
</tr>
</table>
</form>

<hr color="black" size="1" width="100%">
 
<? include("invPart.php") ?>
 
&nbsp;&nbsp;&nbsp;<a href="booking_itineraryHistory.php?id=<?=$_REQUEST['id']?>" class="bold" style="font-weight:bolder; color:#000000">itinerary history</a>&nbsp;&nbsp; 
</div>
</body>
</html> 