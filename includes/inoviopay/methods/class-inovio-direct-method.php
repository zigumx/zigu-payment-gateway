<?php

/**
 * Class Inovio_Direct_Method
 * Use to extend WC_Payment_Gateway
 */
class Inovio_Direct_Method extends WC_Payment_Gateway {

    public static $inovio_direct_api_log = false;
    /**
     * Default constructor to set initial parameters and methods
     */
    public function __construct() {
        // Make unique name for inovio direct method
        $this->id = 'inoviodirectmethod';

        // Bool. Can be set to true if you want payment fields to show on the checkout (if doing a direct integration)
        $this->has_fields = true;
        // Title of the payment method shown on the admin page.
        $this->method_title = __( 'Inovio', $this->id );
        // Enter a URL to an image
        $this->icon = plugins_url()."/".explode("/", plugin_basename( __file__ ))[0] . '/assets/img/inovio-logo.png';

        $this->method_title = 'Zigu';
	    $this->method_description = 'Pay with credit card Zigu'; 
        $this->supports = array ( 'products',
                                'refunds',
                                'subscriptions',
                                'subscription_cancellation', 
                                'subscription_suspension',
                                'subscription_reactivation',
                                'subscription_amount_changes',
                                'subscription_date_changes',
                                'multiple_subscriptions',
                            );
         
        // To set admin section form field
        $this->init_form_fields();

        // To set admin section
        $this->init_settings();
        // Get user define values from admin
        $this->enabled = $this->get_option( 'enabled' );
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions' );
        $this->api_endpoint = $this->get_option( 'apiEndPoint' );
        $this->site_id = $this->get_option( 'site_id' );
        $this->req_username = $this->get_option( 'req_username' );
        $this->req_password = $this->get_option( 'req_password' );
        $this->debug = $this->get_option('debug');
        $this->debug = 'yes' == $this->get_option( 'debug', 'no' );
        $this->req_product_id = $this->get_option( 'req_product_id' );
        $this->three_ds_min_price = $this->get_option( 'three_ds_min_price' );
        $this->three_ds_api_key = $this->get_option( 'three_ds_api_key' );
        $this->installments = [];
        if ($this->get_option( 'installments_3_months', 'no' ) != 'no') {
            $this->installments += [
                '03' => '03'
            ];
        }
        if ($this->get_option( 'installments_6_months', 'no' ) != 'no') {
            $this->installments += [
                '06' => '06'
            ];
        }
        if ($this->get_option( 'installments_9_months', 'no' ) != 'no') {
            $this->installments += [
                '09' => '09'
            ];
        }
        if ($this->get_option( 'installments_12_months', 'no' ) != 'no') {
            $this->installments += [
                '12' => '12'
            ];
        }
        
        $this->common_class = new class_common_inovio_payment();
        add_action( 'wp_enqueue_scripts', array( $this, 'inovio_payment_script' ) );

        // Check WooCommerce version
        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array ( &$this, 'process_admin_options' ) );
        } else {
            add_action( 'woocommerce_update_options_payment_gateways', array ( &$this, 'process_admin_options' ) );
        }
        $this->_maybe_register_callback_in_subscriptions(); 
    }


    /**
     * Use to load js file for Inovio ACH payment gateway
     *
     * @access public
     */
    public function inovio_payment_script() {
        if (!is_checkout()) {
            return;
        }
        
        wp_enqueue_script(
            'inovio-gateway-js', plugins_url()."/".explode("/", plugin_basename( __file__ ))[0] . '/assets/js/inovio-script.js', array ( 'jquery' )
        );
        wp_enqueue_script(
            'zigu-three-ds', plugins_url()."/".explode("/", plugin_basename( __file__ ))[0] . '/assets/js/zigu-three-ds.js', array ( 'jquery' )
        );
        wp_enqueue_script(
            'three-ds', plugins_url()."/".explode("/", plugin_basename( __file__ ))[0] . '/assets/js/three-ds.js', array ( 'jquery' )
        );

        $inovioPlugindir = plugins_url()."/".explode("/", plugin_basename( __file__ ))[0];
        wp_localize_script( 'inovio-gateway-js', 'inovioPlugindir', $inovioPlugindir );

        wp_localize_script( 'three-ds', 'wc_threeds_params', array (
            'apiKey' => $this->three_ds_api_key,
            'host' => 'http://zigu.mx',
            'sandbox' => false,
            'min_price' => $this->three_ds_min_price
        ));
    }

    /**
    * Maybe register callback in WooCommerce Subscription hooks.
    *
    * @since 1.2.0
    */
    protected function _maybe_register_callback_in_subscriptions() {
        if ( ! class_exists( 'WC_Subscriptions_Order' ) ) {
                        return;
        }
        add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array ( $this, 'scheduled_subscription_payment' ), 10, 2 );
    }

    /**
    * Scheduled subscription payment hook.
    * @param  array    $order
    * @param  float  $amount
    *
    */

    public function scheduled_subscription_payment( $amount, $order ) {
        if( ! defined( 'SSPI_DONE' ) || TRUE !== SSPI_DONE ){
        $old_wc               = version_compare( WC_VERSION, '3.0', '<' );
        $order                = wc_get_order( $order );
        $order_id             = $old_wc ? $order->id : $order->get_id();
        $order_param = $this->common_class->get_order_params_subscription($order_id);
        $params = array_merge( $this->common_class->merchant_credential( $this ), $order_param, 
                        $this->common_class->get_product_ids( $order, $this )
        );
        update_post_meta( $order->get_id(), '_inovio_gateway_scheduled_request', json_encode( $params ) );
        // Advance Params
        $final_params = $params + $this->common_class->get_advaceparam( $this );
        
        $status = 'WC-' . $order_id . '-' . time();
        $service_config = new InovioServiceConfig( $final_params );
        $processor = new InovioProcessor( $service_config );
        // Set method auth and capture
        $response = $processor->set_methodname( 'auth_and_capture' )->get_response();
        $parse_result = json_decode( $response );
    
        update_post_meta( $order->get_id(), '_inovio_gateway_scheduled_response', json_encode($parse_result) );
        define( 'SSPI_DONE', TRUE );
        if (
            isset( $parse_result->TRANS_STATUS_NAME ) &&
            'APPROVED' == $parse_result->TRANS_STATUS_NAME &&
            empty( $parse_result->API_ADVICE ) &&
            empty( $parse_result->SERVICE_ADVICE )
        ) {
            // Add order note
            $order->add_order_note( ' Billing Direct API Payment completed and Transaction Id:' . $parse_result->PO_ID );
            add_post_meta( $order->id, '_inoviotransaction_id', $parse_result->PO_ID, true );
            // Payment complete add PO_ID as transaction id in post_meta table
            $order->payment_complete( $parse_result->PO_ID );
        
            // Add token Id in as note
            $order->add_order_note( 'Token ID:-' . $parse_result->PO_ID );
        
        } elseif ( !empty( $parse_result->API_ADVICE ) || empty( $parse_result->SERVICE_ADVICE ) ) {
        
            $status = 'ERROR';
        
            // Add note
            if( isset( $parse_result->PO_ID ) ) {
                $order->add_order_note(sprintf( __( 'TransactionID %s', 'wc_iveri'), $parse_result->PO_ID ) );
                
                    add_post_meta($order_id, '_inoviotransaction_id', $parse_result->PO_ID, true);
                    // Payment failed
            $order->update_status('failed', sprintf(__('Card payment failed. Payment was rejected due to an error%s', $this->id)));
            }

            if ($this->debug == 'yes') :
                // Add log
                $this->common_class->inovio_logger( 'Transaction Failed', $this );
                $this->common_class->inovio_logger( $response, $this );
            endif;
            die();
        }
    }
}

    /**
     * Process refund functionality if gateway supported refund.
     *
     * @param  int    $order_id
     * @param  float  $amount
     * @param  string $reason
     *
     * @return bool     True or false based on success, or a WP_Error object
     */
    public function process_refund( $order_id, $amount = NULL, $reason = '' ) {
        global $wpdb;
        // Get order related data
        $order = wc_get_order($order_id);

        if ( !$order_id ) {
            throw new Exception( __( 'Invalid order ID.', 'woocommerce' ) );
        }
        $action = wc_clean( $_REQUEST['action'] );
        // Add partial refunded amount with inovio for total refund
        if ( !empty( $action ) && $action === 'woocommerce_refund_line_items' ) {
            $this->insert_refunded_data( $order_id, $amount );
        }
        $order_status = wc_clean( $_REQUEST['order_status'] );

        if ( !empty( $order_status ) && $order_status === 'wc-refunded' ) {

            $amount = $order->get_total() - $this->inovio_get_total_refunded( $order_id );
        }
        // Transaction_id will be only found in case of inovio payment method
        $transaction_id = get_post_meta( $order_id, '_inoviotransaction_id', true );

        if ( get_post_meta( $order_id, '_payment_method', true ) != 'inoviodirectmethod' || empty( $transaction_id ) ) {
            return;
        }
        // Merge params
        $params = array_merge(
                $this->common_class->merchant_credential( $this ), array (
                'request_ref_po_id' => $transaction_id,
                'credit_on_fail' => 1,
                'li_value_1' => $amount,
                )
        );

        $service_config = new InovioServiceConfig( $params );
        $processor = new InovioProcessor( $service_config );

        // Set method ccreverse
        $response = $processor->set_methodname( 'ccreverse' )->get_response();
        $parse_result = json_decode($response);
        if ( !empty( $parse_result->PO_ID ) && !empty( $parse_result->PO_ID ) && $parse_result->PO_ID == $transaction_id ) {
            $order->add_order_note( 'Inovio Payment refund completed. Refund Transaction ID:-' . $parse_result->PO_ID );
            $this->common_class->inovio_logger( $response, $this );
            return true;
            
        } else {
            $order->add_order_note( 'Order already refunded or something went wrong' );
            $this->common_class->inovio_logger( $response, $this );
            return false;
        }
    }

    /**
     * Add Partial refunded amount
     *
     * @global object $wpdb
     * @param  int $order_id
     * @param int $amount
     */
    public function insert_refunded_data( $order_id = null, $amount = null ) {
        global $wpdb;
        $wpdb->insert(
                $wpdb->prefix . 'inovio_refunded', array(
            'inovio_order_id' => $order_id,
            'inovio_refunded_amount' => $amount,
                ), array(
            '%s',
            '%f',
                )
        );
    }

    /**
     * Create form to configure merchant information
     * @param  int $order_id
     */
    public function inovio_get_total_refunded( $order_id = null ) {
        $order = new WC_Order( $order_id );
        global $wpdb;
        $qry = "SELECT sum( inovio_refunded_amount ) as  already_refunded_amount from {$wpdb->prefix}inovio_refunded as t1 WHERE t1.inovio_order_id=$order_id";
        $resultset = $wpdb->get_results( $qry, OBJECT );
        return $resultset[0]->already_refunded_amount;
    }

    /**
     * Create form to configure merchant information
     */
    public function init_form_fields() {
        $form_object = new inovio_payment_shortcodes();
        $this->form_fields = $form_object->inovio_admin_setting_form( "inoviodirect" );
    }

    /**
     * Use to create payment form on checkout page.
     *
     * @global object $woocommerce
     */
    public function payment_fields() {
        // if ( !empty( $this->description ) ) {
        //     echo wpautop( wptexturize( $this->description ) );
        // }
        // echo do_shortcode( '[direct_checkoutform]' );
        include_once('templates/payment.php');
        // include_once(plugin_dir_path( __FILE__ ) . 'templates/payment.php');
    }

    /**
     * Use to process payment
     *
     * @param int $order_id
     */
    public function process_payment( $order_id ) {
        global $woocommerce;
        $expiry_month = wc_clean( $_POST['exp_month'] );
        $expiry_year = wc_clean( $_POST['exp_year'] );
        $order = new WC_Order( $order_id );
        $card_number = !empty( wc_clean( $_POST['inoviodirectmethod_gate_card_numbers'] ) ) ? str_replace( array( ' ', '-' ), '', wc_clean( $_POST['inoviodirectmethod_gate_card_numbers'] ) ) : '';
        $card_cvc = !empty( wc_clean( $_POST['inoviodirectmethod_gate_card_cvv'] ) ) ? wc_clean( $_POST['inoviodirectmethod_gate_card_cvv'] ) : '';
        try {
            if ( empty( $card_number ) ) {
                throw new Exception( __( 'Favor de ingresar el número de tarjeta', $this->id ) );
            } elseif ( empty( $expiry_month ) || empty( $expiry_year ) ) {
                throw new Exception( __( 'Favor de ingresar la fecha de expiración', $this->id ) );
            } elseif ( $this->common_class->validate_expirydate( $expiry_year . $expiry_month) == false ) {
                throw new Exception( __( 'Favor de ingresar la fecha de expiración', $this->id ) );
            } elseif ( empty( $card_cvc ) ) { // check expiry date
                throw new Exception( __( 'Favor de ingresar el código de seguridad', $this->id ) );
            }
            // Restrict product's quantity
            if ( $this->common_class->restrict_quantity( $this ) == false ) {
                global $woocommerce;
                $cart_url = $woocommerce->cart->get_cart_url();
                throw new Exception(
                __(
                        "For any single product's quantity should not be greater than " .
                        $this->get_option( 'inovio_product_quantity_restriction' ) .
                        ".<a href='$cart_url'> Back to cart page.</a>", $this->id
                )
                );
            }

            // echo $card_number;
            $pattern = "/^(3[47]\d{13})$/";
            $isAmex = false;
            if (preg_match($pattern, $card_number)) {
                $isAmex = true;
                // throw new Exception('is Amex');
            }
            // throw new Exception('test '.$isAmex);

            // merchant authentication
            $isAuth = false;
            if ($isAmex) {
                $isAuth = $this->common_class->merchant_authorization_amex( $this );
            } else {
                $isAuth = $this->common_class->merchant_authorization( $this );
            }
            if ( $isAuth == false ) {
                // throw new Exception('Usuario no autorizado'); 
                throw new Exception( __( 'Please contact to service provider', $this->id ) );
            } else {
                // throw new Exception('autorizado');
                $sanitize_post = wc_clean( $_POST );
                $order_param = $this->common_class->get_order_params( $order_id, $sanitize_post, $expiry_month.$expiry_year );
                // Combine array parameters to call auth_and_capture
                $authParams = [];
                if ($isAmex) {
                    $authParams = $this->common_class->merchant_credential_amex( $this );
                } else {
                    $authParams = $this->common_class->merchant_credential( $this );
                }
                $params = array_merge( $authParams, $order_param, 
                    $this->common_class->get_product_ids( $order, $this )
                );
                $final_params = $params + $this->common_class->get_advaceparam($this);

                $total = (float) $order->get_total();
                $three_ds_min_price = (float) $this->three_ds_min_price;

                if ($three_ds_min_price < $total && !isset($final_params['p3ds_cavv'])) {
                    throw new Exception( __( 'Error Missing 3D secure', $this->id ) );
                }

                $status = 'WC-' . $order_id . '-' . time();
                $service_config = new InovioServiceConfig( $final_params );
                $processor = new InovioProcessor( $service_config );
                // Set method auth and capture
                $response = $processor->set_methodname( 'auth_and_capture' )->get_response();
                $parse_result = json_decode( $response );
                update_post_meta( $order->get_id(), '_inovio_gateway_scheduled_first_request', json_encode( $params ) );
                update_post_meta( $order->id, 'uniqid', $uniqid );
                update_post_meta( $order->id, 'CUST_ID', $parse_result->CUST_ID );
                update_post_meta( $order->id, 'PMT_L4', $parse_result->PMT_L4 );
                update_post_meta( $order->id, 'REQ_ID', $parse_result->REQ_ID );
                update_post_meta( $order->id, 'TRANS_STATUS_NAME', $parse_result->TRANS_STATUS_NAME );
                update_post_meta( $order->id, 'TRANS_ID', $parse_result->TRANS_ID );
                update_post_meta( $order->get_id(), '_inovio_gateway_scheduled_first_response', json_encode( $parse_result ) );
                
                // throw new Exception($response);

                // check card length
                if ( isset( $parse_result->REF_FIELD ) && 'pmt_numb' == strtolower( $parse_result->REF_FIELD ) ) {
                    throw new Exception( __( 'Error Invalid Credit Card Length', $this->id ) );
                }

                // check card expiry date
                if ( isset( $parse_result->REF_FIELD ) && 'pmt_expiry' == strtolower( $parse_result->REF_FIELD ) ) {
                    throw new Exception( __( 'Error Invalid Card Expiry date', $this->id ) );
                }
                // throw new Exception($response);
                // throw new Exception('pre '.$parse_result->TRANS_STATUS_NAME.isset( $parse_result->TRANS_STATUS_NAME ));
                if (
                        isset( $parse_result->TRANS_STATUS_NAME ) &&
                        'APPROVED' == $parse_result->TRANS_STATUS_NAME &&
                        empty( $parse_result->API_ADVICE ) &&
                        empty( $parse_result->SERVICE_ADVICE )
                ) {
                    // Add thank you message after complete payment
                    $thankyou_msg = 'Transaction has been completed successfully.';

                    // Add order note
                    $order->add_order_note( ' Billing Direct API Payment completed and Order Id:' . $parse_result->PO_ID );
                    add_post_meta( $order->id, '_inoviotransaction_id', $parse_result->PO_ID, true );
                    // Payment complete add PO_ID as transaction id in post_meta table
                    $order->payment_complete( $parse_result->PO_ID );

                    // Reduce stock
                    $order->reduce_order_stock();

                    // Add notice thank you page
                    wc_add_notice( $thankyou_msg, 'success' );

                    // Add token Id in as note
                    $order->add_order_note( 'Token ID:-' . $parse_result->PO_ID );
                    // Remove cart
                    $woocommerce->cart->empty_cart();
                    if ( !is_admin() ) {
                        WC()->session->set( 'affiliate_hash', '' );
                    }

                    // Return thank you page redirect
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url( $order ),
                    );
                } elseif ( !empty( $parse_result->API_ADVICE ) || !empty( $parse_result->SERVICE_ADVICE ) ) {
                    $status = 'ERROR';
                    // throw new Exception('error');

                    // $parse_result->SERVICE_RESPONSE;
                    $estado_error = "";
                    if ($parse_result->PROCESSOR_RESPONSE != 0) {
                        switch ($parse_result->PROCESSOR_RESPONSE) {
                            case "0": 
                                $estado_error = "APROBADA";
                                break;
                            case "2": 
                                $estado_error = "DECLINADA: favor de contactar a su banco";
                                break;
                            case "4": 
                                $estado_error = "DECLINADA: tarjeta bloqueada por el banco emisor";
                                break;
                            case "5": 
                                $estado_error = "DECLINADA: por el banco emisor";
                                break;
                            case "12": 
                                $estado_error = "DECLINADA: Transacción inválida";
                                break;
                            case "14": 
                                $estado_error = "DECLINADA: número de tarjeta no válido";
                                break;
                            case "41": 
                                $estado_error = "DECLINADA: tarjeta reportada como perdida";
                                break;
                            case "43": 
                                $estado_error = "DECLINADA: tarjeta reportada como robada";
                                break;
                            case "51": 
                                $estado_error = "DECLINADA: fondos insuficientes";
                                break;
                            case "54": 
                                $estado_error = "DECLINADA: tarjeta caducada";
                                break;
                            case "56": 
                                $estado_error = "DECLINADA: tarjeta no registrada";
                                break;
                            case "57": 
                                $estado_error = "DECLINADA: transacción no permitida por la tarjeta";
                                break;
                            case "61": 
                                $estado_error = "DECLINADA: transacción excede el límite permitido por su tarjeta";
                                break;
                            case "62": 
                                $estado_error = "DECLINADA: tarjeta restringida";
                                break;
                            case "65": 
                                $estado_error = "DECLINADA: transacción excede la frecuencia permitida por su tarjeta";
                                break;
                            case "82": 
                                $estado_error = "DECLINADA: transacción detenida por el módulo de seguridad de su tarjeta";
                                break;
                            case "87": 
                                $estado_error = "DECLINADA: datos inválidos";
                                break;
                            case "89": 
                                $estado_error = "DECLINADA: servicio inválido";
                                break;
                            case "91": 
                                $estado_error = "DECLINADA: no se logró contactar al banco emisor";
                                break;
                            case "N0": 
                                $estado_error = "DECLINADA: no fue posible autorizar la transacción";
                                break;
                            case "N7": 
                                $estado_error = "DECLINADA: transacción no autorizada por el banco emisor de la tarjeta";
                                break;
                            case "O6": 
                                $estado_error = "DECLINADA: datos inválidos";
                                break;
                            case "P1": 
                                $estado_error = "DECLINADA: transacción excede el límite permitido por su tarjeta";
                                break;
                            case "Q2": 
                                $estado_error = "DECLINADA: transacción no permitida por la tarjeta";
                                break;
                        }
                    } else {
                        switch ($parse_result->SERVICE_RESPONSE) {
                            case "100":
                                $estado_error = "Autorizada";
                                break;
                            case "101":
                                $estado_error = "Servicio disponible";
                                break;
                            case "150":
                                $estado_error = "Producto no encontrado";
                                break;
                            case "152":
                                $estado_error = "Tipo de producto no encontrado";
                                break;
                            case "155":
                                $estado_error = "Divisa no configurada";
                                break;
                            case "190":
                                $estado_error = "Configuración inválida de producto";
                                break;
                            case "192":
                                $estado_error = "Producto no activo";
                                break;
                            case "500":
                                $estado_error = "No tiene cuenta configurada";
                                break;
                            case "501":
                                $estado_error = "Cliente no encontrado";
                                break;
                            case "502":
                                $estado_error = "Error en la transacción";
                                break;
                            case "503":
                                $estado_error = "Servicio no disponible";
                                break;
                            case "505":
                                $estado_error = "Orden ajustada a cero";
                                break;
                            case "506":
                                $estado_error = "Monto a cobrar mayor al monto de la orden";
                                break;
                            case "507":
                                $estado_error = "Orden capturada completa";
                                break;
                            case "510":
                                $estado_error = "Orden devuelta";
                                break;
                            case "511":
                                $estado_error = "Orden reportada como contra-cargo";
                                break;
                            case "512":
                                $estado_error = "Orden no encontrada";
                                break;
                            case "515":
                                $estado_error = "Orden reembolsada";
                                break;
                            case "516":
                                $estado_error = "Reembolso mayor al valor de la orden";
                                break;
                            case "518":
                                $estado_error = "Missing required field";
                                break;
                            case "519":
                                $estado_error = "Missing Trial Descriptor";
                                break;
                            case "520":
                                $estado_error = "Divisa no aceptada";
                                break;
                            case "522":
                                $estado_error = "Marca de tarjeta no aceptada";
                                break;
                            case "525":
                                $estado_error = "Batch Closed: Please credit";
                                break;
                            case "530":
                                $estado_error = "Downstream Processor Unavailable";
                                break;
                            case "536":
                                $estado_error = "Order not settled: Please reverse";
                                break;
                            case "555":
                                $estado_error = "Call Center";
                                break;
                            case "560":
                                $estado_error = "Invalid Service Action";
                                break;
                            case "565":
                                $estado_error = "Monto no válido";
                                break;
                            case "570":
                                $estado_error = "Marca de tarjeta no válida";
                                break;
                            case "580":
                                $estado_error = "Solicitud no admitida";
                                break;
                            case "600":
                                $estado_error = "Declinada por el banco emisor. Favor de contactar a su banco.";
                                break;
                            case "601":
                                $estado_error = "Transacción detenida por módulo anti-fraude. Esta tarjeta está temporalmente bloqueada en este sitio, intente nuevamente mañana.";
                                break;
                            case "603":
                                $estado_error = "Transacción fraudulenta";
                                break;
                            case "605":
                                $estado_error = "Tarjeta reportada como robada";
                                break;
                            case "610":
                                $estado_error = "Recoger tarjeta";
                                break;
                            case "615":
                                $estado_error = "Tarjeta reportada como perdida";
                                break;
                            case "620":
                                $estado_error = "CVV inválido";
                                break;
                            case "621":
                                $estado_error = "CVV fallido";
                                break;
                            case "622":
                                $estado_error = "Validación de dirección fallida";
                                break;
                            case "623":
                                $estado_error = "Validación de dirección fallida";
                                break;
                            case "624":
                                $estado_error = "Tarjeta vencida";
                                break;
                            case "625":
                                $estado_error = "Uso excesivo";
                                break;
                            case "630":
                                $estado_error = "Número de tarjeta inválido";
                                break;
                            case "635":
                                $estado_error = "Fondos insuficientes";
                                break;
                            case "640":
                                $estado_error = "Reintentar";
                                break;
                            case "650":
                                $estado_error = "RECHAZADA - No intente nuevamente";
                                break;
                            case "660":
                                $estado_error = "Aprobación parcial";
                                break;
                            case "680":
                                $estado_error = "Transacción duplicada";
                                break;
                            case "685":
                                $estado_error = "Orden duplicada";
                                break;
                            case "690":
                                $estado_error = "Active Membership Exists";
                                break;
                            case "692":
                                $estado_error = "Invalid Rebill Product";
                                break;
                            case "695":
                                $estado_error = "Site Username Unavailable";
                                break;
                            case "697":
                                $estado_error = "Membresía no activa";
                                break;
    
                            case "698":
                                $estado_error = "Membresía no encontrada";
                                break;
                            case "699":
                                $estado_error = "Membresía no está configurada como suscripción";
                                break;
                        }
                    }
                    
                    

                    // Add note
                    $order->add_order_note(sprintf( __( 'Transaction Failed, TransactionID: %s, Estado: %s', 'wc_iveri'), $parse_result->TRANS_ID, $estado_error ) );
                    add_post_meta( $order_id, '_inoviotransaction_id', $parse_result->TRANS_ID, true );

                    // Payment failed
                    $order->update_status( 'failed', sprintf( __( 'Card payment failed. Payment was rejected due to an errors', $this->id ) ) );

                    // // Remove cart
                    // $woocommerce->cart->empty_cart();
                    if ( $this->debug == 'yes' ) :
                        // Add log
                        $this->common_class->inovio_logger( 'Transaction Failed', $this );
                        $this->common_class->inovio_logger( $response, $this );
                    endif;

                    // throw new Exception($response);

                    throw new Exception(
                        __(
                            'Transacción fallida, estado de la transacción: '. $estado_error
                            , $this->id
                        )
                    );
                }
            }
        } catch ( Exception $ex ) { // Add log
            $this->common_class->inovio_logger($ex->getMessage(), $this);
            wc_add_notice( $ex->getMessage(), 'error' );
        }
    }
}
// end class Inovio_Direct_Method

/**
 * Use to load Custom Gateway extention into WooCommerce
 *
 * @param array $method
 * @return array $method
 */
function add_inovio_class( $method ) {
    $method[] = 'Inovio_Direct_Method';

    return $method;
}

// add Inovio Payment Gateway using hooks woocommerce_payment_gateways
add_filter( 'woocommerce_payment_gateways', 'add_inovio_class' );



