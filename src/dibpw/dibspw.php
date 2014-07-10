<?php
/*
  Plugin Name:  Jigoshop - Dibs Payment Window
  Plugin URI:   http://tech.dibspayment.com/toolbox/dibs_standard_shopmodules/
  Description:  Allows you to use Dibs payment gateway with the Jigoshop ecommerce plugin.
  Version:      4.1.0
  Author:       DIBS
  Author URI:   http://tech.dibspayment.com
 */

require "dibs_api/pw/dibs_pw_api.php";

add_action('plugins_loaded', 'jigoshop_dibspayment', 0);

function jigoshop_dibspayment()
{
	if (!class_exists('jigoshop_payment_gateway'))
		return;

	/**
	 * Add the gateway to JigoShop
	 **/
	function add_dibspayment_gateway( $methods ) {
		$methods[] = 'dibspayment';
		return $methods;
	}
	add_filter( 'jigoshop_payment_gateways', 'add_dibspayment_gateway', 50 );


	class dibspayment extends jigoshop_payment_gateway {

		public function __construct() {
			
			parent::__construct();
			
			$this->id = 'dibspayment';
			$this->icon = '';
			$this->has_fields = false;
			
			$this->enabled = Jigoshop_Base::get_options()->get_option('jigoshop_dibspayment_enabled');
			$this->title = Jigoshop_Base::get_options()->get_option('jigoshop_dibspayment_title');
			add_action('init', array(&$this, 'check_callback') );
			add_action('valid-dibspayment-callback', array(&$this, 'successful_request') );
			add_action('receipt_dibspayment', array(&$this, 'receipt_page'));
			add_filter('jigoshop_thankyou_message', array(&$this, 'thankyou_message') );

		}


		/**
		 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
		 *
		 * These will be installed on the Jigoshop_Options 'Payment Gateways' tab by the parent class 'jigoshop_payment_gateway'
		 *
		 */	
		protected function get_default_options() {
		
			$defaults = array();
			
			// Define the Section name for the Jigoshop_Options
			$defaults[] = array( 
				'name' => __('DIBS Payment Window', 'jigoshop'), 'type' => 'title', 
				'desc' => __('Detailed description of configuration parameters can be found on <a href="http://tech.dibs.dk/">our Tech site.</a>')
				);
			
			// List each option in order of appearance with details
			$defaults[] = array(
				'name'		=> __('Enable DIBS Payment Window','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_dibspayment_enabled',
				'std' 		=> 'no',
				'type' 		=> 'checkbox',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			);
			
			$defaults[] = array(
				'name'		=> __('Method Title','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_title',
				'std' 		=> __('DIBS','jigoshop'),
				'type' 		=> 'text'
			);
			
			$defaults[] = array(
				'name'		=> __('Description','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the description which the user sees during checkout.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_description',
				'std' 		=> __("Pay via DIBS using credit card or bank transfer.", 'jigoshop'),
				'type' 		=> 'longtext'
			);

			$defaults[] = array(
				'name'		=> __('DIBS Merchant ID','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your DIBS merchant id; this is needed in order to take payment!','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_merchant',
				'std' 		=> '',
				'type' 		=> 'text'
			);
                        
                       $defaults[] = array(
				'name'		=> __('DIBS HMAC Key','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('HMAC Key','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_hmackey',
				'std' 		=> '',
				'type' 		=> 'text'
			);
                        
                
                       $defaults[] = array(
				'name'		=> __('Enable test mode','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('When test mode is enabled only DIBS specific test-cards are accepted.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_testmode',
				'std' 		=> 'no',
				'type' 		=> 'select',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			);
                       
                         $defaults[] = array(
				'name'		=> __('Add fee','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('When test mode is enabled only DIBS specific test-cards are accepted.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_addfee',
				'std' 		=> 'no',
				'type' 		=> 'select',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			 );
                         
                               $defaults[] = array(
				'name'		=> __('Capture now','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('When enabled the capture will be authomatically','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_capturenow',
				'std' 		=> 'no',
				'type' 		=> 'select',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			);
                               
                           $defaults[] = array(
				'name'		=> __('Paytype','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('Define paytype here like Visa, MC e.t.c','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_paytype',
				'std' 		=> '',
				'type' 		=> 'text'
			   );
                        
                        
                              $defaults[] = array(
				'name'		=> __('Enable test mode','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('When test mode is enabled only DIBS specific test-cards are accepted.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_testmode',
				'std' 		=> 'no',
				'type' 		=> 'select',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			);

			$defaults[] = array(
				'name'		=> __('Language','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('Show Dibs Payment Window in this language. If set to WPML detect, it switches between the languages listed here, but if not found defaults to English.','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_language',
				'std' 		=> 'en',
				'type' 		=> 'select',
				'choices'	=> array(
					'en_US'				=> __('English (US)', 'jigoshop'),
					'en_GB'				=> __('English (GB)', 'jigoshop'),
					'da_DK'				=> __('Danish', 'jigoshop'),
					'sv_SE'				=> __('Swedish', 'jigoshop'),
					'nb_NO'				=> __('Norwegian (Bokmål)', 'jigoshop'),
				)
			);
                        
             $defaults[] = array(
				'name'		=> __('Account','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('Merchant\'s account','jigoshop'),
				'id' 		=> 'jigoshop_dibspayment_account',
				'std' 		=> '',
				'type' 		=> 'text'
			   );

			return $defaults;
		}
                


		/**
		* There are no payment fields for dibs, but we want to show the description if set.
		**/
		function payment_fields() {
			if ($jigoshop_dibspayment_description = Jigoshop_Base::get_options()->get_option('jigoshop_dibspayment_description')) echo wpautop(wptexturize($jigoshop_dibspayment_description));
		}

	        /**
		* Generate the dibs button link
		**/
		public function generate_form( $order_id ) {
			$order = new jigoshop_order( $order_id );
			$action_adr = 'https://sat1.dibspayment.com/dibspaymentwindow/entrypoint';
			// filter redirect page
			$checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
			            
            $dibsObj = new dibs_pw_api();
            $args = $dibsObj->api_dibs_get_requestFields($order); 
                        
			$fields = '';
			foreach ($args as $key => $value) {
				$fields .= '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'" />';
			}

			return '<form action="'.$action_adr.'" method="post" id="dibs_payment_form">
					' . $fields . '
					<input type="submit" class="button-alt" id="submit_dibs_payment_form" value="'.__('Pay via DIBS', 'jigoshop').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_url()).'">'.__('Cancel order &amp; restore cart', 'jigoshop').'</a>
					<script type="text/javascript">
						jQuery(function(){
							jQuery("body").block(
								{
									message: "<img src=\"'.jigoshop::assets_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />'.__('Thank you for your order. We are now redirecting you to DIBS to make payment.', 'jigoshop').'",
									overlayCSS:
									{
										background: "#fff",
										opacity: 0.6
									},
									css: {
												padding:        20,
												textAlign:      "center",
												color:          "#555",
												border:         "3px solid #aaa",
												backgroundColor:"#fff",
												cursor:         "wait"
										}
								});
							jQuery("#submit_dibs_payment_form").click();
						});
					</script>
				</form>';

		}

		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {

			$order = new jigoshop_order( $order_id );

			return array(
				'result' => 'success',
				'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, apply_filters('jigoshop_get_return_url', get_permalink(jigoshop_get_page_id('pay')))))
			);

		}

		/**
		* receipt_page
		**/
		function receipt_page( $order ) {
			echo '<p>'.__('Thank you for your order, please click the button below to pay with DIBS.', 'jigoshop').'</p>';
			echo $this->generate_form( $order );
		}

		/**
		* Check for DIBS Response
		**/
		function check_callback() {

			// Cancel order POST
			if ( strpos($_SERVER["REQUEST_URI"], 'cancel_order=true') !== false) {
				$this->cancel_order(stripslashes_deep($_POST));
            	return;
            }
            
            // Callback action
			if ( strpos($_SERVER["REQUEST_URI"], 'jigoshop/dibscallback') !== false ) {
				header("HTTP/1.1 200 Ok");
                do_action("valid-dibspayment-callback", stripslashes_deep($_POST));
            }
		}



		
        // Cancelling order process
		function cancel_order($posted) {
			if(isset($posted['orderid']) && is_numeric($posted['orderid'])) {
        		// Cancel order the same way as jigoshop_cancel_order
				$order_id = $_POST['orderid'];

				$order = new jigoshop_order( $order_id );
                $dibsObj = new dibs_pw_api();
                if( $dibsObj->api_dibs_checkMainFields($order, $bUrlDecode = TRUE) == 0 && $order->status=='pending') :
         			// Cancel the order + restore stock
					$order->cancel_order( __('Order cancelled by customer.', 'jigoshop') );
					// Message
					jigoshop::add_message( __('Your order was cancelled.', 'jigoshop') );
                    // Get empty cart
                    jigoshop_cart::empty_cart();
				elseif ($order->status!='pending') :
					jigoshop::add_error( __('Your order is no longer pending and could not be cancelled. Please contact us if you need assistance.', 'jigoshop') );
				else :
					jigoshop::add_error( __('Invalid order.', 'jigoshop') );
				endif;
				wp_safe_redirect(jigoshop_cart::get_cart_url());
				exit;
			}
		}

		function thankyou_message($message) {
			
			if(isset($_POST['orderId']) && is_numeric($_POST['orderId']) && isset($_POST['s_jigoshop_order_key']) ) {
				$_GET['order'] = $_POST['orderId'];
				$_GET['key'] = $_POST['s_jigoshop_order_key'];
			}
			return $message;
		}
                    

		/**
		 * Successful Payment
	         * complete payment. 
		**/
		function successful_request( $posted ) {
	      	       if($_POST['status'] == "ACCEPTED") {      
                         $order_id  = (int) $posted['orderid'];
            	        $dibsObj = new dibs_pw_api();
            
     		       // Load this order from database
    		        $order = new jigoshop_order( $order_id );
		
			$transaction_id = $_POST['transaction'];
        	
            		// We check fields and complete payment. 
			if ($dibsObj->api_dibs_checkMainFields($order, $bUrlDecode = TRUE) == 0) {
         		    $order->add_order_note( sprintf( __('DIBS payment completed with transaction id %s.', 'jigoshop'), $transaction_id ) );
                 	$order->payment_complete();
					print "Success.\n";
     			}
			exit('Dibs callback complete.');
		      }
		}



		

		// This function converts an array holding the form key values to a string.
		// The generated string represents the message to be signed by the MAC.
		function dibs_create_message($formKeyValues) {
			$string = "";
			if (is_array($formKeyValues)) {
				ksort($formKeyValues); // Sort the posted values by alphanumeric
				foreach ($formKeyValues as $key => $value) {
					if ($key != "MAC") { // Don't include the MAC in the calculation of the MAC.
						if (strlen($string) > 0) $string .= "&";
						$string .= "$key=$value"; // create string representation
					}
				}
				return $string;
		 
			} else {
				return "An array must be used as input!";
			}
		}
	}

}
