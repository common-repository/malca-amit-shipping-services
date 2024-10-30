<?php
if($oid != ''){
    $return = "";
    $tracknumber = get_post_meta($oid , 'MalcaTrackingNumber', true );
    $track_url = get_post_meta($oid , 'MalcaTrackUrl', true );
    $tracknumber = '';
 
    if(!isset($tracknumber) || $tracknumber == ''){
        $all_o = MalcagetOrderDetail($oid);

        if(!empty($all_o)){
            $shippingAddress = $all_o['shipping_address'];
            $shippingAddress['SelectedShippingMethodDesc'] = ($all_o['shipping_methods'] != '' ? $all_o['shipping_methods'] : 'Free');
            $shippingAddress['subtotalPrice'] = ($all_o['total'] - $all_o['shipping_price']);
            /*$shippingAddress['subtotalPrice'] = $all_o['subtotal'];*/
            $shippingAddress['currencyCode'] = $all_o['currency'];
            $shippingAddress['oName'] = '#'.$oid;
            /*print_r($shippingAddress);*/
            try {
              
                $country_code = wc_get_base_location();
                $country_code = $country_code['country'];

                if($country_code != $shippingAddress['country']){
                  
                  $shippingAddress['oid'] = $oid;
                  $shop_url = site_url();
                  $params = Malca_InternationalParams($shippingAddress);
                  $result = Malca_SoapResponse('CreateMAEXInternationalShipment',$params);

                  $trackShipment  = $result->CreateMAEXInternationalShipmentResult;

                }else{
                    $return = "<a class='or_link return_lbl' oid='{$oid}'>Print return label</a>";
                  
                    $params = Malca_DomesticParams($shippingAddress);

                    $result = Malca_SoapResponse('CreateMAEXDomesticShipment',$params);
                    $trackShipment = $result->CreateMAEXDomesticShipmentResult;
                }
                
                if($trackShipment->NotificationType == 'Success'){
                $tracknumber = $trackShipment->Message;
                $CourierType = $trackShipment->CourierType;

                if(strtolower($CourierType) == 'usps'){
                  $track_url = 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1='.$tracknumber;
                }elseif(strtolower($CourierType) == 'ups'){
                  $track_url = 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='.$tracknumber;
                }elseif(strtolower($CourierType) == 'fedex'){
                  $track_url = 'http://www.fedex.com/Tracking?action=track&tracknumbers='.$tracknumber;
                }

                if ( ! add_post_meta( $oid, 'MalcaTrackingNumber', $tracknumber, true ) ) { 
                  update_post_meta ( $oid, 'MalcaTrackingNumber', $tracknumber );
                }
                if ( ! add_post_meta( $oid, 'MalcaCourierType', $CourierType, true ) ) { 
                  update_post_meta ( $oid, 'MalcaCourierType', $CourierType );
                }
                if ( ! add_post_meta( $oid, 'MalcaTrackUrl', $track_url, true ) ) { 
                  update_post_meta ( $oid, 'MalcaTrackUrl', $track_url );
                }

                $order = new WC_Order($oid);
                $order->update_status('completed', 'Order completed by malca shipping service');

                }else{
                $error = $trackShipment->Message;
                Malca_LockErrorLogs($error,$oid);
                $flag = false;
                }
            } catch (Exception $e) {
              $error = $e->getMessage();
              $flag = false;
            }
        
        }else{
            $error = 'Order details can\'t retrive!';
            $flag = false;
        }
    }
 
    if($tracknumber){
        $Labels_param = array('UserAuthentication' => MalcaCredArray(),
                              'TrackingNumber' => $tracknumber,
                              'DocumentType' => 'Label'
                            );

        $result = Malca_SoapResponse('GetMAEXDocument',$Labels_param);
        $result = $result->GetMAEXDocumentResult;

        if($result->NotificationType == 'Success'){
            
            $shop_dir = MALCA_FOLDER.'/labels/';
            if(!is_dir($shop_dir)){
                mkdir($shop_dir);
                chmod($shop_dir, '777');
            }
            $filename = $oid."_Label.pdf";

            $file = $shop_dir.$filename;
            $data = base64_encode($result->FileStream);
            $decoded = base64_decode($data);  
         
            file_put_contents($file, $decoded);
          
            if (file_exists($file)){
                $flag = true;
                $label_file = $file;
            }
        }else{
            $error = $result->Message;
            Malca_LockErrorLogs($error,$oid);
            $flag = false;
        }
    }else{
        $error = "Tracking number not found.";
        $flag = false;
    }
}else{
    $error = "Order ID not found.";
    $flag = false;
}
?>