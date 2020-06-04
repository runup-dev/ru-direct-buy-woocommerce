<?php
/*
Plugin Name: Ru DirectBuy For Woocommerce
Plugin URI:  https://run-up.co.kr
Description: Direct Purchase For Woocommerce
Version:     1.0.0
Author:      RUNUP
Author URI:  http://www.run-up.co.kr
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

class ru_direct_buy{

	public function __construct(){

		## 즉시구매 체크를 위한 객체생성
		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'action_woocommerce_after_add_to_cart_form') );

		# 푸터에 이벤트 삽입
		add_action( 'wp_footer', array($this, 'ru_direct_checkout'), 10, 1 );

		# 장바구니 버튼 직전에 인라인 스타일링
		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'ru_direct_checkout_button_before') );

		# 바로구매 후킹
		add_action( 'woocommerce_after_add_to_cart_button', array($this, 'ru_direct_checkout_button_after') );

		# 리다이렉트 후킹
		add_filter( 'woocommerce_add_to_cart_redirect', array($this, 'ru_redirect_checkout_add_cart') );
		
	}


	# 리다이렉트 커스터마이징
	public function ru_redirect_checkout_add_cart($wc_get_cart_url) {

   		if(isset($_POST["is_direct_checkout"]) && $_POST["is_direct_checkout"] == 1){
	        	global $woocommerce;
        		if($woocommerce->cart->cart_contents_count > 1) return wc_get_cart_url();
		        else						return wc_get_checkout_url();
	   	}else{
        		 return $wc_get_cart_url;
		}
	}

	# 히든객체 생성
	public function action_woocommerce_after_add_to_cart_form() {
	    echo '<input type="hidden" name="is_direct_checkout" />';
	}

	# 바로구매를 위한 인라인 스타일
	public function ru_direct_checkout_button_before(){
        	echo '<style type="text/css">
                .woocommerce div.product form.cart a.added_to_cart{float:left}
                .woocommerce div.product form.cart{display:block !important}
                .woocommerce div.product form.cart .price-calculation{float:left;vertical-align:middle;font-weight:bold}
                .woocommerce div.product form.cart .button.single_add_to_cart_button {float:left !important; width:48% !important; margin:0 !important; clear:left !important;}
                .woocommerce div.product form.cart .button.ru_quick_buy{float:left !important; width:48% !important; margin:0 !important; margin-left:2% !important; color:#fff; background-color:#000}
                .woocommerce div.product form.cart .button.ru_quick_buy:hover{color:yellow}
	        </style>
        	';
	}

	# 바로구매 버튼 노출
	public function ru_direct_checkout_button_after(){
        	global $product;
	        echo '<button value="'.$product->get_id().'" name="add-to-cart" class="ru_quick_buy button alt">바로구매</button>';
	}

	# 푸터이벤트
	public function ru_direct_checkout(){

	  if( is_product() ){
		global $product;
		echo "<script>";
        	echo '
	        (function($){
        	// 옵션이 있는 제품은 우선 DISABLED로 세팅
	        $( "form.variations_form" ).on( "hide_variation", function(){
                $(this).find(".ru_quick_buy").addClass("disabled");
        	});

    		// 옵션이 있는 제품의 폼상태 변경시 DISABLED 복사
	        $( "form.variations_form" ).on( "woocommerce_variation_has_changed", function(){
                	$cart_button = $(this).find("button.single_add_to_cart_button");
	                if( $cart_button.hasClass("disabled") === true ){
        	                $cart_button.closest("form").find(".ru_quick_buy").addClass("disabled");
                	}else{
                        	$cart_button.closest("form").find(".ru_quick_buy").removeClass("disabled");
	                }
        	});

	        // 상품가격 노출
        	$("form.cart div.quantity").after("<div class=\"price-calculation\"><span class=\"number\">'.number_format($product->get_price()).'</span><span class=\"symbol\">'.get_woocommerce_currency_symbol().'</span></div>");

	        // 수량변경시 업데이트
        	$("form.cart input[name=quantity]").bind("change", function(e){
                	var original_price = "'.$product->get_price().'";
	                var qty = $(this).val();
        	        var price = (original_price * qty).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
                	$(this).closest("form").find(".price-calculation .number").text(price);
	        });

        	// 바로구매 이벤트
	        $("form.cart .ru_quick_buy").bind("click", function(e){
                	var $form = $(this).closest("form");
                	var $thisbutton  = $( this );
                	$form.find("input[name=is_direct_checkout]").val("1");
                	if( $thisbutton.hasClass( "disabled" ) ) {
                        	alert("옵션을 먼저 선택해주세요");
	                        e.preventDefault();
                	}

	        });

        	// 장바구니 이벤트
	        $("form.cart .button.single_add_to_cart_button").bind("click", function(e){
        	        var $this_form = $(this).closest("form");
                	$this_form.find("input[name=is_direct_checkout]").val("0");
	        });

        	// 장바구니 이벤트 콜백
	        $( document.body ).on( "added_to_cart", function(e, fragments, cart_hash, button){

                	if ( typeof button === "undefined" ) return false;

	                if ( $( "button.single_add_to_cart_button" ).length ) {
        	                $( button ).closest("form").find(".ru_quick_buy").css("display", "none");
                	}

        	});

	})(jQuery);
        ';
        echo "</script>";
  	}
  }

}

$ru_direct_buy = new ru_direct_buy();
