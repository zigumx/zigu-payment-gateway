jQuery(document).ready(function () {
    //Restrict to enter Character
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_numbers', enter_numeric);
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_expiration', enter_numeric);
    jQuery(document).on('keypress', '#inoviodirectmethod_gate_card_cvv', enter_numeric);

    // add loader after clicked on place order
    jQuery('form.checkout').on('submit', function () {
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

window.onload = function onLoad () {
    function getKountSessionId(){
        return Math.round((new Date()).getTime() / 1000);
    }

    var kountMerchantId = "820500";
    var kountHostUrl = "https://tst.kaptcha.com";
    var kountSessionId = getKountSessionId();

    var script1 = document.createElement('script');
    script1.onload = function () {

        var script2 = document.createElement('script');
        script2.setAttribute('type', 'text/javascript');
        script2.text = "var client = new ka.ClientSDK();client.setupCallback({'collect-begin':function(params){console.log('collection begins')}, 'collect-end':function(params){console.log('collection ends')}});client.autoLoadEvents();";

        document.body.appendChild(script2);
    };
    script1.setAttribute('type', 'text/javascript');
    script1.src = kountHostUrl+"/collect/sdk?m="+kountMerchantId+"&s="+kountSessionId;
    script1.setAttribute("id", "kountCollector");
    document.body.appendChild(script1);

    var img = document.createElement('img');
    img.src = kountHostUrl+"/logo.gif?m="+kountMerchantId+"&s="+kountSessionId;
    document.body.appendChild(img);

    document.addEventListener("DOMContentLoaded", function(event){
        
        document.getElementById("kountSessionId").value = kountSessionId;

    });
    document.getElementById("kountSessionId").value = kountSessionId;
    window.kountSessionId = kountSessionId
    window.kountHostUrl = kountHostUrl
    window.kountMerchantId = kountMerchantId
    window.kountSessionId2 = kountSessionId
}