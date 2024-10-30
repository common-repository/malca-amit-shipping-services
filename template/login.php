<?php
if (!defined('ABSPATH'))
    exit;
$wp_nonce = wp_create_nonce('ajax-nonce');
?>
<body id="Ma_body">
    <style type="text/css">
        .error{color: red;display: none;}
        .msg{color: green;display: none;}
        .thank{padding: 20px;text-align: center;}
    </style> 
    <header>
        <div class="left">
            <img src="<?php echo plugin_dir_url(__FILE__);?>../assets/images/App_Logo.jpg">
        </div>
        <div class="right">
            <img src="<?php echo plugin_dir_url(__FILE__);?>../assets/images/logo.png">
        </div>
    </header>   
    <div class="ma_forms">
        <div class="loader" style="display: none;">
            <img src="<?php echo plugin_dir_url(__FILE__);?>../assets/images/45.gif">
        </div>
        <div class="col-36 detail">
            <div class="border-div">
                <ul>
                    <li>Easily Create Shipping Labels For <b>FedEx, UPS </b> and <b>USPS</b>.</li>
                    <li>Get <b> Less</b> Than Market Price on Each Transactions.</li>
                    <li>Insured Every Transaction <b>Up To jQuery100,000.</b></li>
                    <li>Track Your Shipment And be Notified upon Delivery.</li>
                    <li><b>All Calls Are Personally Answered.</b></li>
                </ul>
            </div>
        </div>
        <div class="col-32 login">
            <div class="border-div login_form">
                <h3>Login</h3>
                <img class="ma_loginLogo" src="<?php echo plugin_dir_url(__FILE__);?>../assets/images/logo.png">
                <form name="login" action="index.php" method="post" id="login" >
                    <div class="error"></div>

                    <ul>
                        <li class="user_nm">
                            <input type="text" name="username" placeholder="User Name" required="required">
                        </li>
                        <li class="pwd">
                            <input type="password" name="password" placeholder="Password" required="required">
                        </li>
                        <li class="stn-code">
                            <input type="text" name="stationcode" placeholder="Station Code" required="required">
                        </li>
                        <!-- <li>
                            <div class="g-recaptcha" data-sitekey="6LfjS2UUAAAAAO_C0OmMjQGIUffkcPkVQAaYcX5k" style=""></div>
                        </li> -->
                        <li>
                            <button type="submit" name="login" value="Login">Login</button>
                        </li>
                    </ul>
                </form>
            </div>
            <div class="divider">
                <span>or</span>
            </div>
        </div>
        <div class="col-32 sign-up">
            <div class="border-div register_form">
                <h3>Sign Up</h3>
                <form action="index.php" name="register" method="post" id="register" autocomplete="on">
                <div class="msg" ></div>
                <div class="error" ></div>
                    <ul>
                        <li class="cnm">
                            <input type="text" name="companyname" placeholder="Company Name" required="required">
                        </li>
                        <li class="cweb">
                            <input type="text" name="companywebsite" placeholder="Company Website" required="required">
                        </li>
                        <li class="con_per">
                            <input type="text" name="contactperson" placeholder="Contact Person" required="required">
                        </li>
                        <li class="phone">
                            <input type="text" name="phone" placeholder="Phone" required="required">
                        </li>
                        <li class="email">
                            <input type="email" name="emailsignup" id="emailsignup" placeholder="Email" required="required">
                        </li>
                        <!-- <li><div class="g-recaptcha" data-sitekey="6LfjS2UUAAAAAO_C0OmMjQGIUffkcPkVQAaYcX5k"></div></li> -->
                        <li>
                            <button type="submit" name="register" value="Sign up">Sign up</button>
                        </li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
    <div class="ma_popup" id="thankyou">
        <div class="popup_content">
            <div class="ma_header" style="border-bottom: none;">
                <a href="" class="close">X</a>
            </div>
            <div class="ma_body">
                <div class="thank">
                    <h2>Thank You for your registration!</h2>
                    <p>Our team shall contact you shortly with account activation details.</p>
                </div>
            </div>
        </div>
    </div>
    <footer class="ma-footer text-center">
        <p>A Malca-Amit specialist awaits your call to customize your solutions, contact us anytime.</p>
        <p><a href="mailto:Solutions@MyMalca.com" target="_parent">Solutions@MyMalca.com</a> or 1-844-MyMalca</b></p>
        <a href="/"><img src="<?php echo plugin_dir_url(__FILE__);?>../assets/images/logo.png"></a>
        <div>
        <ul class="f_links"> 
            <li><a href="https://mawordpress.azurewebsites.net/kb/terms-and-conditions-of-service/" target="_blank">Terms &amp; Condition</a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/terms-of-use/" target="_blank">Terms of Use </a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/privacy-policy-shipping-service-agreement/" target="_blank">Privacy Policy</a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/wordpress-frequently-asked-questions/" target="_blank">FAQ</a></li>            
        </ul>
    </div>
    </footer>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            setTimeout(function(){ jQuery('.error,.msg').hide(); }, 5000);
            jQuery('.ma_header .close').on('click',function(){
                jQuery('#thankyou').hide();
            })

            jQuery( "#login" ).submit(function( event ) {
                jQuery('.loader').show();
                event.preventDefault();
                jQuery.ajax({
                    url:ajaxurl,
                    type:'post',
                    data:{'action':'malca_login','form_data':jQuery("form#login").serialize(), _ajax_nonce: '<?= $wp_nonce; ?>'},
                    dataType:'json',
                    success:function(data){
                        if(!data.flag){
                            jQuery('.login_form').find('.error').text(data.error).show();
                            setTimeout(function(){ jQuery('.error,.msg').hide(); }, 8000);

                        }else{
                            window.location.reload();
                        }
                        /*grecaptcha.reset(); */
                        jQuery('.loader').hide();
                    }
                })
            });

            jQuery( "#register" ).submit(function( event ) {
                event.preventDefault();
                jQuery('.loader').show();
                jQuery.ajax({
                    url:ajaxurl,
                    type:'post',
                    data:{'action':'malca_register','form_data':jQuery("form#register").serialize(), _ajax_nonce: '<?= $wp_nonce; ?>'},
                    dataType:'json',
                    success:function(data){
                        if(!data.flag){
                            jQuery('.register_form').find('.error').text(data.error).show();
                        }else{
                            jQuery('.msg').text(data.msg).show();
                            jQuery('#thankyou').show();

                            setTimeout(function(){
                               jQuery('#thankyou').hide();
                            },50000);
                            jQuery(document).find('input[type=text],input[type=number],input[type=email]').val('');
                        }
                        /*grecaptcha.reset(); */
                        jQuery('.loader').hide();
                        setTimeout(function(){ jQuery('.error,.msg').hide(); window.location.reload();}, 80000);
                    }
                })
            });

        })
    </script>
</body>
