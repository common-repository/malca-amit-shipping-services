<?php
if($oid != ''){
    
	$all_o = MalcagetOrderDetail($oid);
	$orders = $all_o['data']['order'];
	$shippingAddress = $all_o['shipping_address'];
  $shippingAddress['SelectedShippingMethodDesc'] = ($all_o['shipping_methods'] != '' ? $all_o['shipping_methods'] : 'Free');
  $shippingAddress['subtotalPrice'] = $all_o['subtotal'];
  $shippingAddress['currencyCode'] = $all_o['currency'];
  $shippingAddress['oName'] = '#'.$oid;

	$country_code = wc_get_base_location();
  $country_code = $country_code['country'];  

 	if($country_code == $shippingAddress['country']){
      try {
          $tracknumber = get_post_meta($oid , 'MalcaTrackingNumber', true );
			
          $Labels_param = array('UserAuthentication' => MalcaCredArray(),
                    				'TrackingNumber' => $tracknumber,
                  		);

          
          $result = Malca_SoapResponse('CreateMAEXReturnDomesticShipment',$Labels_param);
          $result = $result->CreateMAEXReturnDomesticShipmentResult;

          if($result->NotificationType == 'Success'){
            	
            	$shop_dir = MALCA_FOLDER.'/return/';
            	if(!is_dir($shop_dir)){
                	mkdir($shop_dir);  
            	}
            	$filename = $oid."_Label.pdf";
            	$file = $shop_dir.$filename;
            	$data = base64_encode($result->FileStream);
              $decoded = base64_decode($data);  
                
            	file_put_contents($file, $decoded);
            	if (file_exists($file)){
              	$label_file = $file;
      			    $flag = true;
            	}
          }else{
            $error = $result->Message;
            Malca_LockErrorLogs($error,$oid);
            $flag = false;
          }
      } catch (Exception $e) {
          $error = $e->getMessage();
          $flag = false;
      }
	}else{
  	$error = 'Return label can not be print for international order!';
  	$flag = false;
	}
}
?>