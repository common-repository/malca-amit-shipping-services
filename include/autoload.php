<?php

/* Define all ajax action */
add_action( 'wp_ajax_malca_login', 'malca_login' );
add_action( 'wp_ajax_nopriv_malca_login', 'malca_login' );

add_action( 'wp_ajax_malca_register', 'malca_register' );
add_action( 'wp_ajax_nopriv_malca_register', 'malca_register' );

add_action( 'wp_ajax_MalcaSignOut', 'MalcaSignOut' );
add_action( 'wp_ajax_nopriv_MalcaSignOut', 'MalcaSignOut' );

add_action( 'wp_ajax_Malca_OrderList', 'Malca_OrderList' );
add_action( 'wp_ajax_nopriv_Malca_OrderList', 'Malca_OrderList' );

add_action( 'wp_ajax_MalcaPrintLable', 'MalcaPrintLable' );
add_action( 'wp_ajax_nopriv_MalcaPrintLable', 'MalcaPrintLable' );

add_action( 'wp_ajax_MalcaBulkPrintLable', 'MalcaBulkPrintLable' );
add_action( 'wp_ajax_nopriv_MalcaBulkPrintLable', 'MalcaBulkPrintLable' );

add_action( 'wp_ajax_MalcaReturnLable', 'MalcaReturnLable' );
add_action( 'wp_ajax_nopriv_MalcaReturnLable', 'MalcaReturnLable' );

add_action( 'wp_ajax_MalcagetEstimatedCost', 'MalcagetEstimatedCost' );
add_action( 'wp_ajax_nopriv_MalcagetEstimatedCost', 'MalcagetEstimatedCost' );

add_action("init", "MalcaFormHandle");

?>