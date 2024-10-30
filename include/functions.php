<?php
/* Common Functions */
if(!function_exists('MalcaFormHandle')){
    function MalcaFormHandle(){
        if( isset( $_REQUEST["action"] ) && $_REQUEST["action"] == "MalcaDownloadLabel" ) {
           include 'download.php';
        }
    }
}

if(!function_exists('Malca_SoapResponse')){
    function Malca_SoapResponse($action,$params){
       
        try {
            $client = new SoapClient("https://my.malca-amit.us/MyMABookingWebService/MalcaAmitServices.asmx?wsdl");
            $client->__setLocation('https://my.malca-amit.us/MyMABookingWebService/MalcaAmitServices.asmx');
            $client->sendRequest = true;
            $client->printRequest = true;
            $client->formatXML = true; 

            $actionHeader = new SoapHeader('http://tempuri.org/',$action,true);
            $client->__setSoapHeaders($actionHeader);
            $result = $client->__soapCall($action,array($params));
            return $result;
        } catch (Exception $e) {
            return $e;
        }
    }
}

if(!function_exists('MalcaCredArray')){
    function MalcaCredArray(){
        $user_arry = array('UserName' => get_option( 'malcaLoginUser' ),'UserPassword'=> get_option( 'malcaLoginPswd' ),'UserStationCode'=>get_option( 'malcaLoginCode' ),'SourceTypeId' => 6);
        return $user_arry;
    }
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'MalcaOrderDetailsDisplayTrack', 10, 1 );

if(!function_exists('MalcaOrderDetailsDisplayTrack')){
    function MalcaOrderDetailsDisplayTrack( $order ){
        $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
        $trackno = get_post_meta( $order_id , 'MalcaTrackingNumber', true );
        if($trackno != ''){
            echo '<p><strong>'.__('Tracking Info').': </strong><br> ' . get_post_meta( $order_id , 'MalcaCourierType', true ) . '
              <br> ' . get_post_meta( $order_id , 'MalcaTrackingNumber', true ) . '
              <br> <a target="_blank" href="'.get_post_meta( $order_id , 'MalcaTrackUrl', true ).'">' . get_post_meta( $order_id , 'MalcaTrackUrl', true ) . '</a></p>';
        }
    }
}

if(!function_exists('MalcagetOrderDetail')){
    function MalcagetOrderDetail($oid){
        $order = wc_get_order($oid);
        $order_data = array(
                'id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'created_at' => $order->get_date_created()->date('Y-m-d H:i:s'),
                'updated_at' => $order->get_date_modified()->date('Y-m-d H:i:s'),
                'completed_at' => !empty($order->get_date_completed()) ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
                'status' => $order->get_status(),
                'currency' => $order->get_currency(),
                'total' => wc_format_decimal($order->get_total(), 2),
                'subtotal' => wc_format_decimal($order->get_subtotal(), 2),
                'shipping_methods' => $order->get_shipping_method(),
                'shipping_price' => $order->get_total_shipping(),
                'order_key' => $order->get_order_key(),
                'payment_details' => array(
                    'method_id' => $order->get_payment_method(),
                    'method_title' => $order->get_payment_method_title(),
                    'paid_at' => !empty($order->get_date_paid()) ? $order->get_date_paid()->date('Y-m-d H:i:s') : '',
                ),
                'shipping_address' => array(
                    'first_name' => $order->get_shipping_first_name(),
                    'last_name' => $order->get_shipping_last_name(),
                    'company' => $order->get_shipping_company(),
                    'address_1' => $order->get_shipping_address_1(),
                    'address_2' => $order->get_shipping_address_2(),
                    'city' => $order->get_shipping_city(),
                    'state' => $order->get_shipping_state(),
                    'formated_state' => WC()->countries->states[$order->get_shipping_country()][$order->get_shipping_state()], //human readable formated state name
                    'postcode' => $order->get_shipping_postcode(),
                    'country' => $order->get_shipping_country(),
                    'email' => $order->get_billing_email(),
                    'phone' => $order->get_billing_phone(),
                    'formated_country' => WC()->countries->countries[$order->get_shipping_country()] //human readable formated country name
                ),
                'customer_id' => $order->get_user_id(),
            );

        return $order_data;
    }
}

if(!function_exists('Malca_Cost')){
    function Malca_Cost($oid,$shippingAddress){
        
        $user_auth['SourceTypeId'] = 6; 
        $country_code = wc_get_base_location();
        $country_code = $country_code['country'];
        
        if(!empty($shippingAddress)){
            $total = '';
           
            try {
               
                if($country_code != $shippingAddress['country']){
                    $shippingAddress['oid'] = $oid;
                    $shop_url = site_url();
                    $params = Malca_InternationalParams($shippingAddress);
                    $result = Malca_SoapResponse('GetMAEXEstimatedPriceForInternationalShipment',$params);
                    
                    $ChargesDetails  = $result->GetMAEXEstimatedPriceForInternationalShipmentResult;
                }else{
                    
                    $params = Malca_DomesticParams($shippingAddress);
                    $result = Malca_SoapResponse('GetMAEXEstimatedPriceForDomesticShipment',$params);
                    $ChargesDetails  = $result->GetMAEXEstimatedPriceForDomesticShipmentResult;
            
                }
               
                if($ChargesDetails->NotificationType == 'Success'){
                    $total = 0;
                    $Charges = $ChargesDetails->ChargesDetails;
                    
                    $flag = true;
                }else{
                    $error = $ChargesDetails->Message;
                    Malca_LockErrorLogs($error,$oid);
                    $flag = false;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                $flag = false;
            }
        }else{
            $error = 'Shipping address not found';
        }
        $resp = array();
        $resp['flag'] = $flag;
        $resp['Charges'] = $Charges;
        $resp['error'] = $error;

        return $resp;
    }
}

if(!function_exists('Malca_InternationalParams')){
    function Malca_InternationalParams($shippingAddress){
        $user_arrys = MalcaCredArray();

        $shipAdd = $shippingAddress['address_1'].($shippingAddress['address_2'] != '' ? ', '.$shippingAddress['address_2'] : '');

        $params = array('UserAuthentication' => $user_arrys,
                'ShipmentDetails' => array(
                    'EcommercePlatformID' => 'WordPress',
                    'ConsigneeCountryCode' =>$shippingAddress['country'],
                    'CourierType' => 'None',
                    'ServiceType' => 'None',
                    'PackageType' => 'YourPackaging',//Fedex10KgBox,Fedex25KgBox,YourPackaging,FedexBox,Envelope
                    'SignatureOption' => 'DirectSignature', //DirectSignature or AdultSignature
                    'IsCODShipment' => false,
                    'IsResidentialAddress' => true,
                    'ConsigneeAddress1' => $shipAdd,
                    'ConsigneeCompanyName' =>($shippingAddress['company'] == '' ? $shippingAddress['first_name'] : $shippingAddress['company']) ,
                    'ConsigneeName' => $shippingAddress['first_name'],//'Jordan',
                    'ConsigneeNameOnLabel' => $shippingAddress['first_name'].' '.$shippingAddress['last_name'],//'Jordan Dev',
                    'ConsigneePhoneCountryCode' => Malca_GetCountryPhoneCode($shippingAddress['country']), 
                    'ConsigneeCity' => $shippingAddress['city'],
                    'ConsigneePhoneNumber' => $shippingAddress['phone'],//'8989786788',
                    'ConsigneeZipCode' => $shippingAddress['postcode'], //'10036' '395008',//
                    'ConsigneeStateCode' => $shippingAddress['state'],
                    'ConsigneeReference' => $shippingAddress['oid'],
                    'PackageWeight' => 1,
                    'PickUpDateTime' => date('Y-m-d'),
                    'PickupMethod' => 'PickupByCourier',
                    'PickupState' => $shippingAddress['state'],
                    'PickupPhoneNumber' => $shippingAddress['phone'],
                    'LiabilityValueUSD' => Malca_GetUSDValue($shippingAddress['subtotalPrice'],$shippingAddress['currencyCode']),
                    'PackageLength' => '',
                    'PackageWidth' => '',
                    'PackageHeight' => '',
                    'InvoiceNumber' => $shippingAddress['oid'],
                    'IsEcommerce' => true,
                    'PONumbers' => $shippingAddress['oid'],
                    'IsSaturdayDelivery' => false,
                    'AESCustomsExemptionType' => 'None',
                    'Own_ITN_Number' => 1,
                    'Is_EEI_Required' => false,
                    'SelectedShippingMethodDesc' => $shippingAddress['SelectedShippingMethodDesc'],
                    'VatNumber' => $shippingAddress['oid'],
                    'CommoditiesDetails'=> '',
                    'Remarks' => '',
                )
            );

        if(strlen($shipAdd)>35){
            $adrArr = str_split($shipAdd, 35);
            foreach ($adrArr as $key => $value) {
                $i=$key+1;
                $params['ShipmentDetails']['ConsigneeAddress'.$i] = $value;
            }
        }
        
        return $params;
    }
}

if(!function_exists('Malca_DomesticParams')){
    function Malca_DomesticParams($shippingAddress){

        $shipAdd = $shippingAddress['address_1'].($shippingAddress['address_2'] != '' ? ', '.$shippingAddress['address_2'] : '');
        $user_arrys = MalcaCredArray();
        $params = array(
                'UserAuthentication' => $user_arrys,
                'ShipmentDetails' => array(
                    'EcommercePlatformID' => 'WordPress',
                    'CourierType' => 'None',
                    'ServiceType' => 'None',
                    'PackageType' => 'None',
                    'SignatureOption' => 'DirectSignature',
                    'IsCODShipment' => false,
                    'CODValueUSD' => 0,
                    'CODPaymentMethod' => 'Any',
                    'IsSaturdayDelivery' => false,
                    'IsResidentialAddress' => true,
                    'IsEcommerce' => true,
                    'ConsigneeAddress1' => $shipAdd,//'16-A,Kamal Park',
                    'ConsigneeCompanyName' =>($shippingAddress['company'] == '' ? $shippingAddress['first_name'] : $shippingAddress['company']) ,
                    'ConsigneeName' => $shippingAddress['first_name'],//'Jordan',
                    'ConsigneeEmail' => $shippingAddress['email'],
                    'ConsigneeNameOnLabel' => $shippingAddress['first_name'].' '.$shippingAddress['last_name'],//'Jordan Dev',
                    'ConsigneePhoneCountryCode' => Malca_GetCountryPhoneCode($shippingAddress['country']),
                    'ConsigneePhoneNumber' => $shippingAddress['phone'],//'8989786788',
                    'ConsigneeZipCode' => $shippingAddress['postcode'],
                    'PackageWeight' => 1,
                    'PickUpDateTime' => date('Y-m-d'),
                    'PickupMethod' => 'PickupByCourier',
                    'SelectedShippingMethodDesc' => $shippingAddress['SelectedShippingMethodDesc'],
                    'LiabilityValueUSD' => Malca_GetUSDValue($shippingAddress['subtotalPrice'],$shippingAddress['currencyCode']),
                    'PackageLength' => '',
                    'PackageWidth' => '',
                    'PackageHeight' => ''
                )
            );

        if(strlen($shipAdd)>35){
            $adrArr = str_split($shipAdd, 35);
            foreach ($adrArr as $key => $value) {
                $i=$key+1;
                $params['ShipmentDetails']['ConsigneeAddress'.$i] = $value;
            }
        }
        
        return $params;
    }
}

if(!function_exists('Malca_GetCountryPhoneCode')){
    function Malca_GetCountryPhoneCode($country_code){
        include 'static_array.php';
        return $countryArray[$country_code]['code'];
    }
}

if(!function_exists('Malca_GetUSDValue')){
    function Malca_GetUSDValue($amount,$curr){
        /*$js = file_get_contents("https://cdn.shopify.com/s/javascripts/currencies.js");*/
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://cdn.shopify.com/s/javascripts/currencies.js',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $js = curl_exec($curl);

        curl_close($curl);
        
        $search = strstr($js, "rates");
        $pat = '/\s*/m';
        $replace = '';
        // $final = preg_replace($pat, $replace, $search);
        // $final = str_replace("rates:", "", $final);
        // $final = strchr($final, "},", true);
        // $final .= "}";

        $final = preg_replace($pat, $replace, $search);
        $final = str_replace("rates:", "", $final);
        $final = str_replace(",", ',"', $final);
        $final = str_replace(":", '":', $final);
        $final = str_replace(":.", ':0.', $final);
        $final = str_replace("{", '{"', $final);
        $final = strchr($final, "},", true);
        $final .= "}";

        $arr = json_decode($final,true);

        $usd = str_replace(',', '', number_format(($amount * $arr[$curr]) / $arr['USD'],4));
        return $usd;
    }
}

if(!function_exists('Malca_LockErrorLogs')){
    function Malca_LockErrorLogs($Message,$OrderNumber=''){
        $params = array(
            "LogDetails"=>array(
              "PlatformType"=>'Shopify',
              "StoreId"=> MALCA_STORE_URL,
              "MessageText"=>$Message,
              "MessageType"=>'Error',
              "OrderNumber"=>$OrderNumber,
              "LogCreatedDateTime"=>date('Y-m-d') 
            ),
            "ApplicationId"=>969
        );

        try{
            $result = Malca_SoapResponse('AddMAEXEcommerceAppLog',$params);
            if($result->AddMAEXEcommerceAppLogResult->NotificationType != 'Success'){
               file_put_contents("addLog.txt", date('Y-m-d H:i:s')."\n".json_encode($result)."\n".json_encode($params)."\n==========\n",FILE_APPEND);
            }
            
        }catch(Exception $e){
          
            file_put_contents("addLog.txt", date('Y-m-d H:i:s')."\n".$e->getMessage()."\n".json_encode($params)."\n==========\n",FILE_APPEND);
        }       
    }
}

if(!function_exists('Malca_remove_http')){
    function Malca_remove_http($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
            if(strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }
       return $url;
    }
}
?>