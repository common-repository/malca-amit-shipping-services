<?php
if(!function_exists('malca_login')){
    function malca_login(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();
        parse_str($_POST['form_data'],$data);
        
        /*$captcha=sanitize_text_field($data['g-recaptcha-response']);

        if($captcha == ''){
            $error ="Please check the captcha form.";// Captcha verification is incorrect.    
            $flag = false; 
        }else{

            $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfjS2UUAAAAANdyVu-WWbh-YuJNXoYnEvxxBNBt&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
            if($response['success'] == false) {
                $error = 'You are spammer ! Get the @$%K out';
                $flag = false; 

            } else {
                $flag = true;
            }
        }*/
        $flag = true;
        if($flag){

            $login_ary = array('UserName' => trim(sanitize_text_field($data['username'])),'UserPassword'=> trim(sanitize_text_field($data['password'])),'UserStationCode'=>trim(sanitize_text_field($data['stationcode'])),'SourceTypeId' => 1);
            //array('UserName' => "maTest",'UserPassword'=> "asdf1234",'UserStationCode'=>'NYC')
            $error = '';
            try {
                $client = new SoapClient("https://my.malca-amit.us/MyMABookingWebService/MalcaAmitServices.asmx?wsdl");
                $client->__setLocation('https://my.malca-amit.us/MyMABookingWebService/MalcaAmitServices.asmx');
                $client->sendRequest = true;
                $client->printRequest = true;
                $client->formatXML = true; 
                $actionHeader = new SoapHeader('http://tempuri.org/','CheckMAEXUserAuthentication',true);
                $client->__setSoapHeaders($actionHeader);

                $params = array('UserAuthentication' => $login_ary);
                $result = $client->__soapCall('CheckMAEXUserAuthentication',array($params));
                
                $check_user = $result->CheckMAEXUserAuthenticationResult;

                if($check_user->Code == '200'){
                    $msg = $check_user->Description;
                    $flag = true;

                    /* Save option in Wp */
                    update_option( 'malcaLoginUser', trim($data['username']));
                    update_option( 'malcaLoginPswd', trim($data['password']));
                    update_option( 'malcaLoginCode', trim($data['stationcode']));
                   
                }else{
                    $error = $check_user->Description;
                    $flag = false;
                }
            } catch (Exception $e) {
                $error = 'Please try again!';
            }
        }
        $resp = array();
        $resp['error'] = $error;
        $resp['flag'] = $flag;
        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('malca_register')){
    function malca_register(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

    	parse_str($_POST['form_data'],$data);
    	
    	/*$captcha=sanitize_text_field($data['g-recaptcha-response']);

        if($captcha == ''){
            $error ="Please check the captcha form.";// Captcha verification is incorrect.    
            $flag = false; 
        }else{

            $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfjS2UUAAAAANdyVu-WWbh-YuJNXoYnEvxxBNBt&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
            if($response['success'] == false) {
                $error = 'You are spammer ! Get the @$%K out';
                $flag = false; 
            } else {
                $flag = true;
            }
        }*/
        $flag = true;
        if($flag){
            
            $StoreDetails = array(
                'PlatformType' => 'WordPress',
                'StoreId' => MALCA_STORE_URL,
                'Token' => '0000000000',
                'AppStatus' =>'installed',
                'StoreName' => get_bloginfo('name'),
                'StoreDomain' => site_url(),
                'CompanyWebsite' => sanitize_text_field($data['companywebsite']), 
                'CompanyName' =>sanitize_text_field($data['companyname']),
                'ContactPerson' => sanitize_text_field($data['contactperson']),
                'CustomerPhone' =>sanitize_text_field($data['phone']),
                'CustomerEmail' => sanitize_text_field($data['emailsignup']),
                'AppStatusChangeDate' =>  date('Y-m-d'),
                'AppStatusCreatedDate' =>  date('Y-m-d'),
            );
            
            $params = array('StoreDetails' => $StoreDetails,'ApplicationId' => 969);
            $result = Malca_SoapResponse('SetMAEXEcommerceStoreDetails',$params);
            $result = $result->SetMAEXEcommerceStoreDetailsResult;
               
            if($result->NotificationType == 'Success'){
                $flag = true;
                $message = 'Thank You for your registration!';
            }else{
                $error = $result->Message;
                Malca_LockErrorLogs($error,'');
                $flag = false;
            }
        }

        $resp = array();
        $resp['msg'] = $message;
        $resp['error'] = $error;
        $resp['flag'] = $flag;
        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('MalcaSignOut')){
    function MalcaSignOut(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        delete_option( 'malcaLoginUser' );
        delete_option( 'malcaLoginPswd' );
        delete_option( 'malcaLoginCode' );
        echo true;
        exit;
    }
}

if(!function_exists('Malca_OrderList')){
    function Malca_OrderList(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        include 'static_array.php';
        $page = sanitize_text_field($_POST['page']);
        $sort = 'DESC';
        $limit = 20;

        if($page == '' || $page == '0') $page = 1;
        if(sanitize_text_field($_POST['sort']) != '' && sanitize_text_field($_POST['sort']) == 'asc') $sort = 'ASC';

        $arg =  array(
                    'limit' => $limit,
                    'page' => $page,
                    'orderby' => 'date',
                    'order' => $sort,
                    'paginate' => true,
                );

        if(isset($_POST['search']) && $_POST['search'] != '' && $_POST['search_val'] != ''){
            $search_post = sanitize_text_field($_POST['search']);
            $search_val = sanitize_text_field($_POST['search_val']);

            if($search_post == 'created_at'){
                if($search_val == 'today'){
                    $arg['date_created'] = '='.date('Y-m-d');

                }elseif($search_val == 'this_week'){
                    $arg['date_created'] = '>='.date("Y-m-d", strtotime("previous monday"));

                }elseif($search_val == 'this_month'){
                    $arg['date_created'] = '>='.date('Y-m-01');

                }elseif($search_val == 'less_than' && $_POST['date'] != ''){
                   $arg['date_created'] = '<='.sanitize_text_field($_POST['date']);

                }elseif($search_val == 'greater_than' && $_POST['date'] != ''){
                    $arg['date_created'] = '>='.sanitize_text_field($_POST['date']);
                }

            }else{
                if($search_val == 'incompleted'){
                    $arg['status'] = array('processing','on-hold','failed','refunded','cancelled','pending');
                }elseif($search_val == 'paid'){
                    $arg['status'] =  array('processing', 'completed');
                }else{
                    $arg['status'] =  sanitize_text_field($_POST['search_val']);
                }
            }
        }
        
        $results = wc_get_orders( $arg );
        
        $totalPages = $results->max_num_pages;
        $orders = $results->orders;
       
        $country_code = wc_get_base_location();
        $country_code = $country_code['country'];

        if(!empty($orders)){
            foreach ($orders as $key => $order) {
                $id = $order->Id;
                $return = $tracknumber = '';
                $track_url = 'javascript:void(0);';
                $order_meta = get_post_meta($id);
                $tracknumber = $order_meta['MalcaTrackingNumber'][0];
               
                if($tracknumber != ''){
                    $track_url = $order_meta['MalcaTrackUrl'][0];
                    if($order_meta['_shipping_country'][0] == $country_code)
                    $return = "<a class='or_link return_lbl' oid='{$id}'>Print return label</a>";
                }
               
                $name = $order_meta['_billing_first_name'][0].' '.$order_meta['_billing_last_name'][0];
                
                $financls = 'bck-yellow';

                $fulfill_status = 'Unfulfilled';
                $fulcls = 'bck-yellow';

                if($order->status == 'completed'){
                    $fulcls = 'bck-gray';
                    $fulfill_status = 'fulfilled';
                }

                if($order->status == 'completed' || $order->status == 'processing'){
                    $financls = 'bck-gray';
                    $order->status = 'Paid';
                }elseif($order->status == 'cancelled' || $order->status == 'failed' || $order->status == 'refunded'){
                    $financls = 'bck-orange';
                }

                $symbl = $currency_symbols[$order->currency];
                $date_cre = $order->date_created;
                $createdAt = $date_cre->date('M d, H:i a');
                $href = get_edit_post_link($id);
                $trs.="<tr order='{$id}'>
                        <td><input type='checkbox' name='check' value='{$id}' track='{$tracknumber}' class='check_order'></td>
                        <td><a href='{$href}' oid='{$id}' class='oname' target='_blank'>#".$order->get_order_number()."</a></td>
                        <td>{$createdAt}</td>
                        <td>{$name}</td>
                        <td class='track' oid='{$id}'><a href='{$track_url}' target='_blank'>{$tracknumber}</a></td>
                        <td><span class='{$financls}'>".ucfirst(strtolower($order->status))."</span></td>
                        <td class='fulfill'><span class='{$fulcls}'>".ucfirst(strtolower($fulfill_status))."</span></td>
                        <td>{$symbl}{$order->total}</td>
                        <td><a class='PrintLable or_link' oid='{$id}' href='javascript:void(0);'>Print Label</a></td>
                        <td return_id='{$id}'>{$return}</td>
                        <td><a class='lable_btn or_link' id='{$id}'>Est.Cost</a></td>
                    </tr>";

                $total_records++;

            }
            // end orders with graphQl 
        }
        $is_orders = true;

        if($trs == ''){
            $trs = '<tr>
                        <td colspan="12">
                            <div class="text-center thankyou_page welcome_page">
                                <div class="text-center thankyou_page welcome_page">
                                    <h2>Welcome to Malca Amit Shipping Services</h2>
                                    <p class="center"> No Orders Found  </p>
                                </div>
                            </div>
                        </td>
                    </tr>';
            $is_orders = false;
        }
       
        $resp = array();
        $resp['flag'] = true;
        $resp['pages'] = $totalPages;
        $resp['tbody']= $trs;
        $resp['pagination'] = $pagination;
        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('MalcagetEstimatedCost')){
    function MalcagetEstimatedCost(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        $oid = sanitize_text_field($_POST['oid']);
        $all_o = MalcagetOrderDetail($oid);
       
        $shippingAddress = $all_o['shipping_address'];
        $shippingAddress['SelectedShippingMethodDesc'] = $all_o['shipping_methods'];
        $shippingAddress['subtotalPrice'] = ($all_o['total'] - $all_o['shipping_price']);
        
        $shippingAddress['currencyCode'] = $all_o['currency'];

        // Get estimated cost from function
        
        $est_price = Malca_Cost($oid,$shippingAddress);
        $trs = '';

        if($est_price['flag'] == true){
            $Charge = $est_price['Charges'];
            $Charges = $Charge->MAEX_ChargeDetails;

            foreach ($Charges as $key => $value) {
                $t_list_charg += $value->ListCharge;
                $t_charge += $value->CustomerCharge;

                $trs .= "<tr>
                    <td>$value->Description</td>
                    <td>$".number_format($value->CustomerCharge, 2, '.', '')."</td>
                </tr>";
            }

            $trs .= "<tr>
                    <td>TOTAL ESTIMATED COST </td>
                    <td>$".number_format($t_charge, 2, '.', '')."</td>
                </tr>";
               
                $flag =  true;
        }else{
            $flag =  false;
            $error = $est_price['error'];
        }

        $resp = array();
        $resp['flag'] = $flag;
        $resp['order_name'] = $all_o['data']['order']['name'];
        $resp['id'] = $all_o['data']['order']['id'];
        $resp['error'] = $error;
        $resp['tbody'] = $trs;

        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('MalcaPrintLable')){
    function MalcaPrintLable(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        $print_type = 'single';
        $oid = sanitize_text_field($_POST['oid']);

        $tracknumber = '';
        include  MALCA_FOLDER.'/template/order_bulk_print.php';
        $resp = array();
        if($tracknumber != ''){
            $tracknumber = '<a target="_blank" href="'.$track_url.'">'.$tracknumber.'</a>';
        }
        $resp['flag'] = $flag;
        $resp['error'] = $error;
        $resp['filename'] = $label_file;
        $resp['tracknumber'] = $tracknumber;
        $resp['return'] = $return;
        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('MalcaBulkPrintLable')){
    function MalcaBulkPrintLable(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        $print_type = 'bulk';
        $oid_ary = json_decode(stripslashes($_POST['oid_ary']),true);
       
        $file_array = $error_array = $orderid_ary = array();
        $status = true;
        foreach ($oid_ary as $oid => $tracknumber) {
            include  MALCA_FOLDER.'/template/order_bulk_print.php';

            if($flag){
                if($tracknumber != ''){
                    $tracknumber = '<a target="_blank" href="'.$track_url.'">'.$tracknumber.'</a>';
                }
                $file_array[$oid] = $label_file;
                $orderid_ary[$oid] = $tracknumber;
                $return_ary[$oid] = $return;
            }else{
                $error_array[$oid] = $error;
                $status = false;
            }
        }

        $resp = array();
        $resp['flag'] = $status;
        $resp['error'] = $error_array;
        $resp['filename'] = $file_array;
        $resp['tracknumber'] = $orderid_ary;
        $resp['return'] = $return_ary;
        echo json_encode($resp);
        exit;
    }
}

if(!function_exists('MalcaReturnLable')){
    function MalcaReturnLable(){
        $nonce = $_POST['_ajax_nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
            die ( 'Busted!');

        ob_clean();

        $oid = sanitize_text_field($_POST['oid']);
      
        include MALCA_FOLDER.'/template/order_bulk_return.php';

        $resp = array();
        $resp['flag'] = $flag;
        $resp['error'] = $error;
        $resp['filename'] = $label_file;
        echo json_encode($resp);
        exit;
    }
}
?>