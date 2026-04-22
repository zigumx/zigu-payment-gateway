<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Zigu_Translator
 *
 * Lightweight translator for customer-facing checkout strings.
 * Spanish text is used as the canonical key; the English dictionary maps
 * those keys to their English counterparts. Any key not present in the
 * English dictionary falls back to the Spanish key itself.
 */
class Zigu_Translator {

    private static $language = 'es';

    private static $en = array(
        // Form labels
        'Número de tarjeta'   => 'Card number',
        'Fecha de expiración' => 'Expiration date',
        'Código de seguridad' => 'Security code',
        'Meses sin intereses' => 'Interest-free months',

        // Month abbreviations (select options)
        'Ene' => 'Jan',
        'Feb' => 'Feb',
        'Mar' => 'Mar',
        'Abr' => 'Apr',
        'May' => 'May',
        'Jun' => 'Jun',
        'Jul' => 'Jul',
        'Ago' => 'Aug',
        'Sep' => 'Sep',
        'Oct' => 'Oct',
        'Nov' => 'Nov',
        'Dic' => 'Dec',

        // Field placeholders (title attributes)
        'Favor de ingresar un número de tarjeta válido'  => 'Please enter a valid card number',
        'Favor de ingresar un código de seguridad válido' => 'Please enter a valid security code',

        // Validation errors
        'Favor de ingresar el número de tarjeta'   => 'Please enter the card number',
        'Favor de ingresar la fecha de expiración' => 'Please enter the expiration date',
        'Favor de ingresar el código de seguridad' => 'Please enter the security code',

        // Auth / 3DS / card validation errors
        'Please contact to service provider' => 'Please contact the service provider',
        'Error Missing 3D secure'            => '3D Secure verification is missing',
        'Error Invalid Credit Card Length'   => 'Invalid credit card length',
        'Error Invalid Card Expiry date'     => 'Invalid card expiration date',

        // Final transaction failure wrapper
        'Transacción fallida, estado de la transacción: ' => 'Transaction failed, transaction status: ',
        'Error desconocido'                               => 'Unknown error',

        // PROCESSOR_RESPONSE mapping
        'APROBADA' => 'APPROVED',
        'DECLINADA: favor de contactar a su banco'                           => 'DECLINED: please contact your bank',
        'DECLINADA: tarjeta bloqueada por el banco emisor'                   => 'DECLINED: card blocked by issuing bank',
        'DECLINADA: por el banco emisor'                                     => 'DECLINED: by the issuing bank',
        'DECLINADA: Transacción inválida'                                    => 'DECLINED: invalid transaction',
        'DECLINADA: número de tarjeta no válido'                             => 'DECLINED: invalid card number',
        'DECLINADA: tarjeta reportada como perdida'                          => 'DECLINED: card reported lost',
        'DECLINADA: tarjeta reportada como robada'                           => 'DECLINED: card reported stolen',
        'DECLINADA: fondos insuficientes'                                    => 'DECLINED: insufficient funds',
        'DECLINADA: tarjeta caducada'                                        => 'DECLINED: expired card',
        'DECLINADA: tarjeta no registrada'                                   => 'DECLINED: card not registered',
        'DECLINADA: transacción no permitida por la tarjeta'                 => 'DECLINED: transaction not allowed for this card',
        'DECLINADA: transacción excede el límite permitido por su tarjeta'   => 'DECLINED: transaction exceeds the limit allowed for your card',
        'DECLINADA: tarjeta restringida'                                     => 'DECLINED: card restricted',
        'DECLINADA: transacción excede la frecuencia permitida por su tarjeta' => 'DECLINED: transaction exceeds the frequency allowed for your card',
        'DECLINADA: transacción detenida por el módulo de seguridad de su tarjeta' => 'DECLINED: transaction stopped by your card security module',
        'DECLINADA: datos inválidos'                                         => 'DECLINED: invalid data',
        'DECLINADA: servicio inválido'                                       => 'DECLINED: invalid service',
        'DECLINADA: no se logró contactar al banco emisor'                   => 'DECLINED: unable to contact the issuing bank',
        'DECLINADA: no fue posible autorizar la transacción'                 => 'DECLINED: the transaction could not be authorized',
        'DECLINADA: transacción no autorizada por el banco emisor de la tarjeta' => 'DECLINED: transaction not authorized by the card issuing bank',

        // SERVICE_RESPONSE mapping
        'Autorizada'                                => 'Authorized',
        'Servicio disponible'                       => 'Service available',
        'Producto no encontrado'                    => 'Product not found',
        'Tipo de producto no encontrado'            => 'Product type not found',
        'Divisa no configurada'                     => 'Currency not configured',
        'Configuración inválida de producto'        => 'Invalid product configuration',
        'Producto no activo'                        => 'Product not active',
        'No tiene cuenta configurada'               => 'No account configured',
        'Cliente no encontrado'                     => 'Customer not found',
        'Error en la transacción'                   => 'Transaction error',
        'Servicio no disponible'                    => 'Service unavailable',
        'Orden ajustada a cero'                     => 'Order adjusted to zero',
        'Monto a cobrar mayor al monto de la orden' => 'Amount to charge is greater than the order amount',
        'Orden capturada completa'                  => 'Order fully captured',
        'Orden devuelta'                            => 'Order returned',
        'Orden reportada como contra-cargo'         => 'Order reported as chargeback',
        'Orden no encontrada'                       => 'Order not found',
        'Orden reembolsada'                         => 'Order refunded',
        'Reembolso mayor al valor de la orden'      => 'Refund greater than the order value',
        'Missing required field'                    => 'Missing required field',
        'Missing Trial Descriptor'                  => 'Missing Trial Descriptor',
        'Divisa no aceptada'                        => 'Currency not accepted',
        'Marca de tarjeta no aceptada'              => 'Card brand not accepted',
        'Batch Closed: Please credit'               => 'Batch Closed: Please credit',
        'Downstream Processor Unavailable'          => 'Downstream Processor Unavailable',
        'Order not settled: Please reverse'         => 'Order not settled: Please reverse',
        'Call Center'                               => 'Call Center',
        'Invalid Service Action'                    => 'Invalid Service Action',
        'Monto no válido'                           => 'Invalid amount',
        'Marca de tarjeta no válida'                => 'Invalid card brand',
        'Solicitud no admitida'                     => 'Request not supported',
        'Declinada por el banco emisor. Favor de contactar a su banco.' => 'Declined by the issuing bank. Please contact your bank.',
        'Transacción detenida por módulo anti-fraude. Esta tarjeta está temporalmente bloqueada en este sitio, intente nuevamente mañana.' => 'Transaction stopped by the anti-fraud module. This card is temporarily blocked on this site, please try again tomorrow.',
        'Transacción fraudulenta'                   => 'Fraudulent transaction',
        'Tarjeta reportada como robada'             => 'Card reported as stolen',
        'Recoger tarjeta'                           => 'Pick up card',
        'Tarjeta reportada como perdida'            => 'Card reported as lost',
        'CVV inválido'                              => 'Invalid CVV',
        'CVV fallido'                               => 'CVV failed',
        'Validación de dirección fallida'           => 'Address validation failed',
        'Tarjeta vencida'                           => 'Expired card',
        'Uso excesivo'                              => 'Excessive use',
        'Número de tarjeta inválido'                => 'Invalid card number',
        'Fondos insuficientes'                      => 'Insufficient funds',
        'Reintentar'                                => 'Retry',
        'RECHAZADA - No intente nuevamente'         => 'DECLINED - Do not try again',
        'Aprobación parcial'                        => 'Partial approval',
        'Transacción duplicada'                     => 'Duplicate transaction',
        'Orden duplicada'                           => 'Duplicate order',
        'Active Membership Exists'                  => 'Active Membership Exists',
        'Invalid Rebill Product'                    => 'Invalid Rebill Product',
        'Site Username Unavailable'                 => 'Site Username Unavailable',
        'Membresía no activa'                       => 'Membership not active',
        'Membresía no encontrada'                   => 'Membership not found',
        'Membresía no está configurada como suscripción' => 'Membership is not configured as a subscription',
    );

    public static function set_language( $lang ) {
        self::$language = ( $lang === 'en' ) ? 'en' : 'es';
    }

    public static function get_language() {
        return self::$language;
    }

    public static function t( $spanish_text ) {
        if ( self::$language === 'en' && isset( self::$en[ $spanish_text ] ) ) {
            return self::$en[ $spanish_text ];
        }
        return $spanish_text;
    }
}
