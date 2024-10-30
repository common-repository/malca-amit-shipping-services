<?php 
if (!defined('ABSPATH'))
    exit;
$wp_nonce = wp_create_nonce('ajax-nonce');
// Get orders with extra info about the results.
$args = array(
    'paginate' => true,
);
$results = wc_get_orders( $args );
$total_record = $results->total;
$limit = 20;
$totalPages = ceil($total_record/$limit);
?>

<body id="Ma_body">
   <header>
      <div class="left">
          <img src="<?php echo plugin_dir_url(__FILE__); ?>../assets/images/App_Logo.jpg">
      </div>
      <div class="right">
          <img src="<?php echo plugin_dir_url(__FILE__); ?>../assets/images/logo.png">
      </div>
    </header>
    <div class="page_content ma_listing">
        <div class="loader" style="display: none;">
            <img src="<?php echo plugin_dir_url(__FILE__); ?>../assets/images/45.gif">
        </div>
        <form method="post" action="?action=MalcaDownloadLabel" id="label_form">
            <input type="hidden" name="filename" value="">
            <input type="hidden" name="type" value="uniq">
        </form>
        <form method="post" action="?action=MalcaDownloadLabel" id="bulk_label_form">
            
        </form>
        <div id="tab-1" class="tab-content current">
          	<div class="alert alert-danger common_error" style="display:none;">
              	<strong>Error!</strong> <span></span>
          	</div>
          	<div class="filter-strip">
	            <div class="filter1">
	              	<select class="filter" id="main_filter">
		                <option>Filter</option>
		                <option value="fulfillment_status" selected="selected">Fulfillment Status</option>
		                <option value="financial_status">Financial Status</option>
		                <option value="status">Order Status</option>
		                <option value="created_at">Date</option>
	              	</select>
	            </div>
	            <div class="">        
	              	<span class="search-icon">
		                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 56.966 56.966" style="enable-background:new 0 0 56.966 56.966;" xml:space="preserve" width="12px" height="12px">
		                <path d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z" fill="#abb9c7"/>
		                </svg>
	              	</span>
	              	<select class="f-search" id="sub_filter">
		                <option>Search Orders</option>
		                <option value="completed">Fulfilled</option>
                      <option value="incompleted" selected="selected">Unfulfilled</option>
	              	</select>
	                <select id="fulfillment_status" name="filter2" class="sub_filter">
	                   	<option>Search Orders</option>
                      <option value="completed">Fulfilled</option>
                      <option value="incompleted" selected="selected">Unfulfilled</option>
	                </select>

	                <select id="status" name="filter2" class="sub_filter">
	                    <option>Search Orders</option>
	                    <option value="processing">Processing</option>
                      <option value="completed">Completed</option>
                      <option value="on-hold">On Hold</option>
                      <option value="cancelled">Cancelled</option>
                      <option value="refunded">Refunded</option>
	                    <option value="failed">Failed</option>
	                </select>

	                <select id="financial_status" name="filter2" class="sub_filter">
	                    <option>Search Orders</option>
	                    <option value="pending">Pending</option>
	                    <option value="paid">Paid</option>
	                    <option value="refunded">Refunded</option>
	                    <option value="failed">Failed</option>
	                </select>

	                <select id="created_at" name="filter2" class="sub_filter">
	                    <option>Search Orders</option>
	                    <option value="today">Today</option>
	                    <option value="this_week">This Week</option>
	                    <option value="this_month">This Month</option>
	                    <option value="less_than">On or Before</option>
	                    <option value="greater_than">On or After</option>
	                </select>
	            </div>
          	</div>
          	<input type="text" id="search_date" name="search_date" placeholder="YYYY-MM-DD" autocomplete="off">

          	<div class="refresh_orders"><button class="refresh_tbl">Refresh</button></div>
          	<div class="refresh_orders"><button class="bulk_print" style="margin-right: 10px;">Print Multiply Labels</button></div>
         
          	<table class="table table-hover expanded" id="Allorder" style="background:#fff;font-size:14px;font-weight: normal;">
              	<thead>
                  <th></th>
                  <th class="is-sortable order">Order</th>
                  <th class="is-sortable date"><div order='desc' class="order_by">Date <i class="icon icon-caret-down"></i></div></th>
                  <th class="is-sortable customer">Customer</th>
                  <th class="is-sortable tcn">Tracking Number</th>
                  <th class="is-sortable text-center payment_status">Payment Status</th>
                  <th class="is-sortable fulfillment_status">Fulfillment Status</th>
                  <th class="is-sortable totalth">Total</th>
                  <th class="is-sortable text-center" colspan="3">Actions</th>
              	</thead>
              	<tbody>
                  <tr>
                      <td colspan="12">
                          <div class="text-center thankyou_page welcome_page">
                              <div class="text-center thankyou_page welcome_page">
                                  <h2>Welcome to Malca Amit Shipping Services</h2>
                                  <p class="center"> No Orders With Malca Amit  </p>
                              </div>
                          </div>
                      </td>
                  </tr>
              	</tbody>
          	</table>
          	<div class="pages" align="center" style="display: inline-block;width: 100%;">
                <ul class="" style="text-align: center;">
                    <li class="ma_pagination ma_first">
                        <a class="ma_prev" href="javascript:void(0);" page="0"></a>
                    </li>
                    <?php 
                        for ($i=0; $i < $totalPages; $i++) { 
                            $style='';
                            if ($i >=3) $style="style=display:none;"; ?>
                            <li class="ma_pagination <?php echo $cls=($i==0)?'ma_active':''; ?>" <?php echo $style; ?>>
                                <a href="javascript:void(0);" class="page" page="<?php echo $i+1;?>">
                                    <?php echo ($i+1);?>
                                </a>
                            </li>
                    <?php
                        }
                    ?>
                    <li class="ma_pagination">
                        <a class="ma_next" href="javascript:void(0);" page="2"></a>
                    </li>
                </ul>
                <span>( Page <span class="ma_cur-page">1</span> of <span class="total_page">
                    <?php echo $totalPages;?>
                </span> )</span>
            </div>
        </div>
    </div>
    <div class="ma_popup" id="estimated_cost">
        <div class="popup_content">
            <div class="ma_header">
                <a href="javascript:void(0);" class="close">X</a>
                <h4 class="modal-title">Order <span></span></h4>
            </div>
            <div class="ma_body">
             	<p>Estimated price for shipment</p>
              	<table class="table" id="estimated_table">
                  	<thead>
                      <tr>
                          <th>Description</th>
                          <th>Charges</th>
                      </tr>
                  	</thead>
                  	<tbody></tbody>
              	</table>
            </div>
            <div class="ma_footer">
              <button type="submit" oid="" id="PrintLable">Print Label</button>
              <button class="cancel">Cancel</button>
            </div>
        </div>
    </div>
    <footer class="ma-footer text-center">
        <p>A Malca-Amit specialist awaits your call to customize your solutions, contact us anytime.</p>
        <p><a href="mailto:Solutions@MyMalca.com" target="_parent">Solutions@MyMalca.com</a> or 1-844-MyMalca</b></p>
        <a href="/"><img src="<?php echo plugin_dir_url(__FILE__); ?>../assets/images/logo.png"></a>
        <div>
        <ul class="f_links"> 
            <li><a href="https://mawordpress.azurewebsites.net/kb/terms-and-conditions-of-service/" target="_blank">Terms &amp; Condition</a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/terms-of-use/" target="_blank">Terms of Use </a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/privacy-policy-shipping-service-agreement/" target="_blank">Privacy Policy</a></li>
            <li><a href="https://mawordpress.azurewebsites.net/kb/wordpress-frequently-asked-questions/" target="_blank">FAQ</a></li>      
            <li><a href="javascript:void(0);" id="sign_out">Sign Out</a></li>      
        </ul>
    </div>
    </footer>
</body>
<script type="text/javascript">
    var collId_arr = {};

    jQuery(document).ready(function(){
        Malca_OrderList(1);

        setTimeout(function(){
          jQuery('#Relogin').show();
        },10000);

        jQuery(document).on("click", "input[name='check']", function (e) {
          var oid = jQuery(this).attr("value");
          var track = jQuery(this).attr('track');

          if (jQuery(this).is(":checked")) {
              collId_arr[oid] = track;
          } else {
              delete collId_arr[oid];
          }
        });

        jQuery('#sign_out').on('click',function(){
          jQuery('.loader').show();
          jQuery.ajax({
            url:ajaxurl,
            type:'post',
            dataType:'json',
            data:{action:'MalcaSignOut', _ajax_nonce: '<?= $wp_nonce; ?>'},
            success:function(data){
              window.location.reload();  
            },
            complete:function(){
              jQuery('.loader').hide();
            }
          })
        })

        jQuery('.bulk_print').on('click',function(){
          if(jQuery(document).find('input[name=check]:checked').length != 0){
            jQuery('.loader').show();
            jQuery.ajax({
                url:ajaxurl,
                type:'post',
                dataType:'json',
                data:{action:'MalcaBulkPrintLable',oid_ary:JSON.stringify(collId_arr), _ajax_nonce: '<?= $wp_nonce; ?>'},
                success:function(data){
                    var inpt = '';
                    collId_arr = {};
                    if(data.filename != ''){
                      jQuery.each(data.filename,function(k,v){
                        inpt += '<input type="hidden" name="filename[]" value="'+v+'">';
                      });

                      if(inpt != ''){
                        inpt += '<input type="hidden" name="type" value="bulk">';
                        jQuery('#bulk_label_form').html(inpt).submit();
                      }
                    }
                    if(data.tracknumber != ''){
                      jQuery.each(data.tracknumber,function(k,v){
                        if(v != ''){
                          jQuery(document).find('td[oid='+k+']').html(v);
                          jQuery(document).find('tr[order='+k+'] td.fulfill').html('<span class="bck-gray">Fulfilled</span>');
                        }
                      });
                    }

                    if(data.return != ''){
                      jQuery.each(data.return,function(k,v){
                        if(v != '') jQuery(document).find('td[return_id='+k+']').html(v);
                      });
                    }

                    if(!data.flag){
                      var bulk_error = '';
                      jQuery.each(data.error,function(k,v){
                        bulk_error +='<br><p><strong>'+jQuery(document).find('a.oname[oid='+k+']').text()+': </strong>'+v+'</p>';
                      })
                      jQuery(document).find('.common_error span').html(bulk_error);
                      jQuery('.common_error').show();
                      setTimeout(function(){ jQuery('.common_error span').html(''); jQuery('.common_error').hide();},80000);
                    }
                      jQuery(document).find('input[name=check]').prop('checked',false);
                },
                complete:function(){
                    jQuery('.loader').hide();
                }
            })
          }else{
            alert('Please select order!');
          }
        })

        jQuery('.refresh_tbl').on('click',function(){
          Malca_OrderList();
        })
        jQuery('.ma_header .close,.ma_footer .cancel').on('click',function(){
            jQuery('#estimated_cost').hide();
        })

        jQuery('#search_date').datetimepicker({
          'timepicker':false,
          'validateOnBlur':true ,
          'format':'Y-m-d',
          onSelectDate:function(ct,$i){
            Malca_OrderList('','');
          }
        });

        jQuery(document).on('click','.pagination li a',function(){
          var page = jQuery(this).attr('page');
          var page_type = jQuery(this).attr('page_type');
          Malca_OrderList(page,page_type);
        });

        jQuery(document).on("click",".ma_pagination",function(){
          obj_a = jQuery(this);
          obj = jQuery(this).find('a');
          curPage = parseInt(obj.attr('page'));

          if(curPage == 0) return false;
          
          jQuery(".ma_se-pre-con").show();

          jQuery(".ma_prev").attr("page",curPage-1);
          jQuery(".ma_next").attr("page",curPage+1);
          Malca_OrderList(curPage);
        });

        jQuery('#main_filter').on('change',function(){
          var val = jQuery(this).val();
          var options = jQuery('#'+val).html();
          if(val == 'Filter'){
            options = '<option>Search Orders</option>';
          }
          jQuery('#sub_filter').html(options);
          if(val == 'Filter') Malca_OrderList();
        });

       
        jQuery('.order_by').on('click',function(){
            if(jQuery('.order_by').attr('order') == 'desc'){
                jQuery('.order_by').attr('order','asc').find('i').attr('class','icon icon-caret-up');
            }else{
                jQuery('.order_by').attr('order','desc').find('i').attr('class','icon icon-caret-down');
            }
            var sort = jQuery('.order_by').attr('order');
            Malca_OrderList(1,sort);
        })

        jQuery(document).on('click','.lable_btn',function(){
            jQuery('.loader').show();
            var oid = jQuery(this).attr('id');

            jQuery.ajax({
                url:ajaxurl,
                type:'post',
                dataType:'json',
                data:{action:'MalcagetEstimatedCost',oid:oid, _ajax_nonce: '<?= $wp_nonce; ?>'},
                success:function(data){
                    if(data.flag){
                        jQuery('#estimated_table tbody').html(data.tbody);
                        jQuery('#estimated_cost .modal-title span').html(data.order_name);
                        jQuery('#estimated_cost').find('#PrintLable').attr('oid',oid);
                        jQuery('#estimated_cost').show();
                        jQuery('.common_error').hide();
                    }else{
                        jQuery(document).find('.common_error span').text(data.error);
                        jQuery('.common_error').show();
                        setTimeout(function(){ jQuery('.common_error').hide();},8000);
                    }
                },
                complete:function(){
                    jQuery('.loader').hide();
                }
            })
        })

        jQuery(document).on('click','#PrintLable,.PrintLable',function(){
            jQuery('.loader').show();
            var oid = jQuery(this).attr('oid');
           
            jQuery.ajax({
                url:ajaxurl,
                type:'post',
                dataType:'json',
                data:{action:'MalcaPrintLable',oid:oid, _ajax_nonce: '<?= $wp_nonce; ?>'},
                success:function(data){
                    if(data.flag == true){
                        jQuery('#label_form').find('input[name=filename]').val(data.filename);
                        jQuery('#label_form').submit();
                        if(data.tracknumber != ''){
                          jQuery(document).find('td[oid='+oid+']').html(data.tracknumber);
                          jQuery(document).find('tr[order='+oid+'] td.fulfill').html('<span class="bck-gray">Fulfilled</span>');
                        }
                        if(data.return != '') jQuery(document).find('td[return_id='+oid+']').html(data.return);

                    }else{
                        jQuery('.common_error span').text(data.error);
                        jQuery('.common_error').show();
                        setTimeout(function(){ jQuery('.common_error').hide();},8000);
                    }
                },
                complete:function(){
                    jQuery('.loader').hide();
                }
            })
        })

        jQuery(document).on('click','.return_lbl',function(){
            var oid = jQuery(this).attr('oid');
            jQuery('.loader').show();
            jQuery.ajax({
                url:ajaxurl,
                type:'post',
                dataType:'json',
                data:{action:'MalcaReturnLable',oid:oid, _ajax_nonce: '<?= $wp_nonce; ?>'},
                success:function(data){
                    if(data.flag == true){
                        jQuery('#label_form').find('input[name=filename]').val(data.filename);
                        jQuery('#label_form').submit();
                    }else{
                        jQuery('.common_error span').text(data.error);
                        jQuery('.common_error').show();
                        setTimeout(function(){ jQuery('.common_error').hide();},8000);
                    }
                },
                complete:function(){
                    jQuery('.loader').hide();
                }
            })
            
        })

        jQuery(document).on('change','#sub_filter',function(event){
          event.preventDefault();
          if(jQuery(this).val() == 'less_than' || jQuery(this).val() == 'greater_than'){
            jQuery('#search_date').show();
          }else{
            //if(jQuery(this).val() != 'Search Orders'){
            jQuery('#search_date').hide();
            Malca_OrderList(1,'');
            //}
          }
        })

        /*jQuery('#search_date').on('change',function(){
          if(jQuery(this).val() != '') Get_orders('','');
        })*/
    });

    function Malca_OrderList(page='',sort=''){
      if(page == '' || page == '0') page = 1;   
      jQuery('.loader').show();
      //jQuery('.common_error').hide();
      var search_str = jQuery('#main_filter').val();
      var sub_str = jQuery('#sub_filter').val();

      if(search_str == 'Filter' || sub_str == 'Search Orders') sub_str = '';

      var date = '';
      if(sub_str == 'less_than' || sub_str == 'greater_than'){
          date = jQuery('#search_date').val();
      }
      if(sort == ''){
          sort = jQuery('.order_by').attr('order');
      }

      jQuery.ajax({
        url:ajaxurl,
        type:'post',
        dataType:'json',
        data:{action:'Malca_OrderList',page:page,search:search_str,search_val:sub_str,date:date,sort:sort, _ajax_nonce: '<?= $wp_nonce; ?>'},
        success:function(data){
          jQuery('#Allorder tbody').html(data.tbody);
         
          jQuery(".ma_cur-page").text(page);
          jQuery(".page").parent().removeClass('ma_active')
          jQuery(".page[page="+page+"]").parent().addClass('ma_active');
          var total = data.pages;
          if(data.pages == 1 || data.pages == 0){
            jQuery('.pages').hide();
          }else{
            jQuery('.pages').show();
            if(page != 1 && page != total){
                obj_a.siblings().not(":first").not(":last").hide();
                jQuery(".page[page="+page+"]").parent().show();
                jQuery(".page[page="+page+"]").parent().next().show();
                jQuery(".page[page="+page+"]").parent().prev().show();
            }
            if(page == total){
                jQuery(".ma_next").attr("page","0");                        
            }
          }
        },
        complete:function(){
          jQuery('.loader').hide();
        }
      })
    }

</script>