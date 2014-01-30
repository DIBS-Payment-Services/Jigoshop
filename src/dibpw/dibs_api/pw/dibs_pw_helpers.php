<?php
class dibs_pw_helpers extends dibs_pw_helpers_cms implements dibs_pw_helpers_interface {

    /**
     * Flag if this module uses tax amounts instead of tax percents.
     * 
     * @var bool
     */
    public static $bTaxAmount = true;
    
    /**
     * Process write SQL query (insert, update, delete) with build-in CMS ADO engine.
     * 
     * @param string $sQuery SQL query string.
     */
    public function helper_dibs_db_write($sQuery) {
        return true;
    }
    
    /**
     * Read single value ($sName) from SQL select result.
     * If result with name $sName not found null returned.
     * 
     * @param string $sQuery SQL query string.
     * @param string $sName Name of field to fetch.
     * @return mixed 
     */
    public function helper_dibs_db_read_single($sQuery, $sName) {
        $mResult = 'process query';

        return (isset($mResult[$sName])) ? $mResult[$sName] : null;
    }
    
    /**
     * Return settings with CMS method.
     * 
     * @param string $sVar Variable name.
     * @param string $sPrefix Variable prefix.
     * @return string 
     */
    public function helper_dibs_tools_conf($sVar, $sPrefix = 'DIBSPW_') {
        return Jigoshop_Base::get_options()->get_option('jigoshop_dibspayment_'.$sVar);
    }
    
    /**
     * Return CMS DB table prefix.
     * 
     * @return string 
     */
    public function helper_dibs_tools_prefix() {
        return 'pref_';
    }
    
    /**
     * Returns text by key using CMS engine.
     * 
     * @param type $sKey Key of text node.
     * @param type $sType Type of text node. 
     * @return type 
     */
    public function helper_dibs_tools_lang($sKey, $sType = 'msg') {
        return $sKey;
    }

    /**
     * Get full CMS url for page.
     * 
     * @param string $sLink Link or its part to convert to full CMS-specific url.
     * @return string 
     */
    public function helper_dibs_tools_url($sLink) {
        return $sLink;
    }
    
    /**
     * Build CMS order information to API object.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @param bool $bResponse Flag if it's response call of this method.
     * @return object 
     */
    public function helper_dibs_obj_order($mOrderInfo, $bResponse = FALSE) {
        if($bResponse === TRUE) {
            //some onResponse behavior
        }
        return (object)array(
            'orderid'  => $mOrderInfo->id,
            'amount'   => $mOrderInfo->order_total,
            'currency' => dibs_pw_api::api_dibs_get_currencyValue(Jigoshop_Base::get_options()->get_option('jigoshop_currency'))
        );
    }
    
    
    /**
     * Returns object with URLs needed for API, 
     * e.g.: callbackurl, acceptreturnurl, etc.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    public function helper_dibs_obj_urls($mOrderInfo = null) {
       // filter redirect page
       $checkout_redirect = apply_filters( 'jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks') );
       return (object)array(
           'callbackurl'     =>  site_url('/?cmd=jigoshop/dibscallback'),
	   'acceptreturnurl' =>  add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink( $checkout_redirect ))), 
	   'cancelreturnurl' =>  site_url('?cancel_order=true'),
        );
    }

    /**
     * Returns object with additional information to send with payment.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    public function helper_dibs_obj_etc($mOrderInfo) {
        return (object)array(
            'sysmod'      => 'wpjshop1_4_1_1',
        );
    }
    
    /**
     * Hook that allows to execute CMS-specific action during callback execution.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     */
    public function helper_dibs_hook_callback($mOrderInfo) {
        return;
    }
}
?>