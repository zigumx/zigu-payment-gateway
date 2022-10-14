function makeid(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * 
 charactersLength));
   }
   return result;
}

jQuery(document).ready(function () {
    // Three DS
    var threeDS = new ThreeDS({
        apiKey  : wc_threeds_params.apiKey,
        host    : wc_threeds_params.host,
        sandbox : wc_threeds_params.sandbox
    })

    var wc_min_price_3ds = wc_threeds_params.min_price;

    var defaultScore = 100
    var score = defaultScore

    var defaultRiskProbabilityIndex = 1
    var riskProbabilityIndex = defaultRiskProbabilityIndex // 1 - high risk, 2 - medium risk, 3 - average, 4 - safe, 5 - very safe
    var thresholdRiskProbability = 1
    

    var thresholdScore = 50

    var tempCard = undefined

    var transId = makeid(16)

    var $form = jQuery('form.checkout,form#order_review');

    var removeThreeDsInputs = function () {
        jQuery('form.checkout').find('[name="zigu_threeds_cavv"]').remove();
        jQuery('form.checkout').find('[name="zigu_threeds_eci"]').remove();
        jQuery('form.checkout').find('[name="zigu_threeds_xid"]').remove();
        jQuery('form.checkout').find('[name="zigu_threeds_dsTransId"]').remove();
    }

    jQuery('body').on('click', 'form.checkout button:submit', function(){
		jQuery('.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message').remove();
		// jQuery('form.checkout').find('[name="zigu_token"]').remove();
        removeThreeDsInputs();
	});

    var addValueField = function (name, value) {
        $form.append(jQuery('<input type="hidden" name="'+name+'" />').val(value));
    }

    var generateThreeDs = function () {
        var card = jQuery('#inoviodirectmethod_gate_card_numbers').val();
        var expMonth = jQuery('#cc-exp-month').val();
        var expYear = jQuery('#cc-exp-year').val().substr(2);
        var total = jQuery('#zigu_checkout_total').val();
        var totalNumber = Number.isNaN(Number.parseInt(total, 10)) ? 0 : Number.parseInt(total, 10);

        threeDS.request3DSFull({
            number          : card,
            expiryMonth     : expMonth,
            expiryYear      : expYear,
            total           : totalNumber / 100
        }).then((value) => {
            console.log(value);
            var eci = value?.eci
            // var cavv = value?.cavv
            // var xid = value?.xid
            var dsTransId = value?.dsTransId
            var cavv = value?.authenticationValue
            console.log('three ds', value)

            if (cavv) {
                removeThreeDsInputs();
                addValueField('zigu_threeds_cavv', cavv);
                addValueField('zigu_threeds_eci', eci);
                // addValueField('zigu_threeds_xid', xid);
                addValueField('zigu_threeds_dsTransId', dsTransId);
                $form.submit();
            } else {
                $form.unblock();
            }
        }).catch(function (error) {
            console.log('error al ejecutar 3ds')
            console.log(error)
            $form.unblock();
        })
    }

    // var vestaSession = function (onSuccess, onError) {
    //     function getFormData($form){
    //         var unindexed_array = $form.serializeArray();
    //         var indexed_array = {};
        
    //         jQuery.map(unindexed_array, function(n, i){
    //             indexed_array[n['name']] = n['value'];
    //         });
        
    //         return indexed_array;
    //     }
    //     var data = getFormData($form)
    //     console.log('data', data)
    //     // data = {}
    //     data['action'] = 'vesta_session'
    //     jQuery.ajax({
    //         url: wc_threeds_params.admin_url, // this is the object instantiated in wp_localize_script function
    //         type: 'POST',
    //         data: {
    //             transId : transId,
    //             action  : 'vesta_session'
    //         },
    //         dataType: "json"
    //     })
    //     .done(function(data) {
    //         onSuccess(data);
    //         // console.log('vesta result', data);
    //     })
    //     .fail(function(error) {
    //         onError(error)
    //         // console.log('error')
    //     })
    // }

    // vestaSession(
    //     function (data) {
    //         console.log('vesta session', data)
    //     },
    //     function (error) {
    //         console.log('vesta session error', error)
    //     }
    // )

    var vestaScore = function (onSuccess, onError) {
        function getFormData($form){
            var unindexed_array = $form.serializeArray();
            var indexed_array = {};
        
            jQuery.map(unindexed_array, function(n, i){
                indexed_array[n['name']] = n['value'];
            });
        
            return indexed_array;
        }
        var data = getFormData($form)
        console.log('data', data)
        // data = {}
        data['action'] = 'vesta'
        jQuery.ajax({
            url: wc_threeds_params.admin_url, // this is the object instantiated in wp_localize_script function
            type: 'POST',
            data: data,
            dataType: "json"
        })
        .done(function(data) {
            onSuccess(data);
            // console.log('vesta result', data);
        })
        .fail(function(error) {
            onError(error)
            // console.log('error')
        })
    }

    var blockForm = function () {
        console.log("form.checkout");
        if (jQuery('input[name=payment_method]:checked').val() !== 'inoviodirectmethod') {
            return true;
        }
        console.log("checkout_place_order");
        $form.find('.payment-errors').html('');
        $form.block({message: null, overlayCSS: {background: "#fff url(" + woocommerce_params.ajax_loader_url + ") no-repeat center", backgroundSize: "16px 16px", opacity: 0.6}});
    }

    var checkHighPrice = function () {
        var total = jQuery('#zigu_checkout_total').val();
        var totalNumber = Number.isNaN(Number.parseInt(total, 10)) ? 0 : Number.parseInt(total, 10);
        var wc_min_price_3ds_number = Number.isNaN(Number.parseInt(wc_min_price_3ds, 10)) ? 0 : Number.parseInt(wc_min_price_3ds, 10);
        console.log('test values', totalNumber, wc_min_price_3ds, wc_min_price_3ds_number);

        var isHighPrice = (totalNumber / 100) > wc_min_price_3ds_number
        console.log('is high price', isHighPrice)
        if (isHighPrice) {
            return true
        } else {
            return false
        }
    }

    var checkLowScore = function () {
        var parsedScore = Number.isNaN(Number.parseInt(score)) ? defaultScore : Number.parseInt(score)
        var parsedRiskProbabilityIndex = Number.isNaN(Number.parseInt(riskProbabilityIndex)) ? defaultRiskProbabilityIndex : Number.parseInt(riskProbabilityIndex)
        console.log('test values', parsedScore, parsedRiskProbabilityIndex);
        
        var isLowScore = parsedScore > thresholdScore
        console.log('is low score', parsedScore, thresholdScore, isLowScore)
        if (isLowScore) {
            return true
        } else {
            return false
        }
        // var isLowScoreProbability = parsedRiskProbabilityIndex >= thresholdRiskProbability
        // console.log('is low score', parsedRiskProbabilityIndex, thresholdRiskProbability, isLowScoreProbability)
        // if (isLowScoreProbability) {
        //     return true
        // } else {
        //     return false
        // }
    }

    var decisionMaker = function () {
        console.log('desision maker')
        blockForm()
        if ($form.find('[name="zigu_threeds_cavv"]').length){
			return true;
		}
        if (score === undefined || tempCard !== jQuery('#inoviodirectmethod_gate_card_numbers').val()) {
            getScore()
            return false
        }
        // return false
        if (checkHighPrice() || checkLowScore()) {
            generateThreeDs()
            return false
        } else {
            return true
        }
    }

    var getScore = function () {
        console.log('get score')
        tempCard = jQuery('#inoviodirectmethod_gate_card_numbers').val();
        vestaScore (
            function (data) {
                console.log('success ', data)
                score = data.RiskScore || defaultScore
                riskProbabilityIndex = data.RiskProbabilityIndex || defaultRiskProbabilityIndex
                // check3DS()
                $form.submit();
            },
            function (error) {
                console.log('error ', error)
                score = defaultScore
                riskProbabilityIndex = defaultRiskProbabilityIndex
                // check3DS()
                $form.submit();
            }
        )
    }

    jQuery('form.checkout').bind('checkout_place_order', decisionMaker);
});