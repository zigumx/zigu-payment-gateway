jQuery(document).ready(function () {
    //Restrict to enter Character
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_numbers', enter_numeric);
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_expiration', enter_numeric);
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_cvv', enter_numeric);

    // Validate the accepted card brand while the user types / leaves the field
    jQuery(document).on('input blur', '#inoviodirectmethod_gate_card_numbers', function () {
        zigu_validate_card_brand();
    });

    // Block WooCommerce checkout submission when the card brand is not accepted
    jQuery(document.body).on('checkout_place_order', function () {
        return zigu_validate_card_brand();
    });

    // add loader after clicked on place order
    jQuery('form.checkout').on('submit', function (e) {
        // Stop submission and the loader if the card brand is not supported
        if (!zigu_validate_card_brand()) {
            e.preventDefault();
            return false;
        }
        jQuery('.woocommerce-checkout-review-order-table').block({
            message: null,
            overlayCSS: {
                'background': '#fff',
                'background-image': inovioPlugindir + "/assets/img/FhHRx.gif",
                'background-repeat': 'no-repeat',
                'background-position': 'center',
                'opacity': 0.6
            }
        });
    });
});

// Restrict to enter any character
var enter_numeric = function (e) {
    return (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) ? false : true;
};

// Detect the card brand from the card number.
// Amex  -> starts with 3 OR has 15 digits
// Visa  -> starts with 4
// MasterCard -> starts with 5 (or 2, for the 2-series 2221-2720)
// Anything else -> null (unknown, let the processor decide)
var zigu_detect_card_brand = function (num) {
    num = (num || '').replace(/\D/g, '');
    if (num.length === 0) {
        return null;
    }
    var first = num.charAt(0);
    if (first === '3' || num.length === 15) {
        return 'amex';
    }
    if (first === '4') {
        return 'visa';
    }
    if (first === '5' || first === '2') {
        return 'mastercard';
    }
    return null;
};

// A brand is enabled unless its config value is an explicit "off"
// (handles booleans, 0/'0', ''/'false' coming from wp_localize_script).
var zigu_brand_enabled = function (val) {
    return !(val === false || val === 0 || val === '0' || val === '' ||
             val === 'false' || val === null || typeof val === 'undefined');
};

// Validate the entered card number against the accepted brands (ziguCardConfig).
// Returns true when the brand is allowed (or unknown/not enough input), false otherwise.
var zigu_validate_card_brand = function () {
    var $field = jQuery('#inoviodirectmethod_gate_card_numbers');
    if (!$field.length) {
        return true;
    }
    zigu_clear_card_error();

    // Config not localized -> do not block
    if (typeof ziguCardConfig === 'undefined' || !ziguCardConfig.brands) {
        return true;
    }

    var brand = zigu_detect_card_brand($field.val());
    if (!brand) {
        return true;
    }
    if (!zigu_brand_enabled(ziguCardConfig.brands[brand])) {
        var message = (ziguCardConfig.messages && ziguCardConfig.messages[brand])
            ? ziguCardConfig.messages[brand]
            : 'Card brand not supported';
        zigu_show_card_error(message);
        return false;
    }
    return true;
};

var zigu_show_card_error = function (message) {
    var $target = jQuery('#inoviodirectmethod_gate_card_type_image');
    if (!$target.length) {
        $target = jQuery('.inoviodirectmethod_gate_card_number_wrap');
    }
    var $error = jQuery('#zigu_card_brand_error');
    if (!$error.length) {
        $error = jQuery('<span id="zigu_card_brand_error" class="zigu-card-error" style="display:block;color:#e2401c;font-weight:600;margin-top:6px;font-size:0.95em;"></span>');
        $target.after($error);
    }
    $error.text(message).show();
    if ($error.length && $error[0].scrollIntoView) {
        $error[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
};

var zigu_clear_card_error = function () {
    jQuery('#zigu_card_brand_error').text('').hide();
};

// window.onload = function onLoad () {
//     function getKountSessionId(){
//         return Math.round((new Date()).getTime() / 1000);
//     }

//     var kountMerchantId = "820500";
//     var kountHostUrl = "https://tst.kaptcha.com";
//     var kountSessionId = getKountSessionId();

//     var script1 = document.createElement('script');
//     script1.onload = function () {

//         var script2 = document.createElement('script');
//         script2.setAttribute('type', 'text/javascript');
//         script2.text = "var client = new ka.ClientSDK();client.setupCallback({'collect-begin':function(params){console.log('collection begins')}, 'collect-end':function(params){console.log('collection ends')}});client.autoLoadEvents();";

//         document.body.appendChild(script2);
//     };
//     script1.setAttribute('type', 'text/javascript');
//     script1.src = kountHostUrl+"/collect/sdk?m="+kountMerchantId+"&s="+kountSessionId;
//     script1.setAttribute("id", "kountCollector");
//     document.body.appendChild(script1);

//     var img = document.createElement('img');
//     img.src = kountHostUrl+"/logo.gif?m="+kountMerchantId+"&s="+kountSessionId;
//     document.body.appendChild(img);
//
//     var kountField = document.getElementById("kountSessionId");

//     document.addEventListener("DOMContentLoaded", function(event){
        
//         if (kountField) {
//             kountField.value = kountSessionId;
//         }

//     });
//     if (kountField) {
//         kountField.value = kountSessionId;
//     }
//     window.kountSessionId = kountSessionId
//     window.kountHostUrl = kountHostUrl
//     window.kountMerchantId = kountMerchantId
//     window.kountSessionId2 = kountSessionId
// }