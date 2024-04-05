jQuery(document).ready(function () {
  // Three DS
  var threeDS = new ThreeDS({
    apiKey: wc_threeds_params.apiKey,
    host: wc_threeds_params.host,
    sandbox: wc_threeds_params.sandbox,
    version: "2.1.1",
  });

  var wc_min_price_3ds = wc_threeds_params.min_price;

  var $form = jQuery("form.checkout,form#order_review");

  var removeThreeDsInputs = function () {
    jQuery("form.checkout").find('[name="zigu_threeds_cavv"]').remove();
    jQuery("form.checkout").find('[name="zigu_threeds_eci"]').remove();
    jQuery("form.checkout").find('[name="zigu_threeds_xid"]').remove();
    jQuery("form.checkout").find('[name="zigu_threeds_send_hex"]').remove();
  };

  jQuery("body").on("click", "form.checkout button:submit", function () {
    jQuery(
      ".woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message"
    ).remove();
    // jQuery('form.checkout').find('[name="zigu_token"]').remove();
    removeThreeDsInputs();
  });

  var addValueField = function (name, value) {
    $form.append(
      jQuery('<input type="hidden" name="' + name + '" />').val(value)
    );
  };

  var generateThreeDs = function () {
    var card = jQuery("#inoviodirectmethod_gate_card_numbers").val();
    var expMonth = jQuery("#cc-exp-month").val();
    var expYear = jQuery("#cc-exp-year").val().substr(2);
    var total = jQuery("#zigu_checkout_total").val();
    var totalNumber = Number.isNaN(Number.parseInt(total, 10))
      ? 0
      : Number.parseInt(total, 10);

    threeDS
      .request3DSFull({
        number: card,
        expiryMonth: expMonth,
        expiryYear: expYear,
        total: totalNumber / 100,
        merchantRiskIndicator: {
          shipIndicator: "05",
          deliveryTimeFrame: "01",
          reorderItemsInd: "01",
        },
      })
      .then((value) => {
        console.log(value);
        var cavv = value?.authenticationValue;
        var eci = value?.eci;
        var xid = value?.dsTransId;
        console.log("three ds", cavv, eci, xid);

        if (cavv) {
          removeThreeDsInputs();
          addValueField("zigu_threeds_cavv", cavv);
          addValueField("zigu_threeds_eci", eci);
          addValueField("zigu_threeds_xid", xid);
          addValueField("zigu_threeds_send_hex", card.charAt(0) === "4");
          $form.submit();
        } else {
          $form.unblock();
        }
      })
      .catch(function (error) {
        console.log("error al ejecutar 3ds");
        console.log(error);
        $form.unblock();
      });
  };

  jQuery("form.checkout").bind("checkout_place_order", function (e) {
    console.log("form.checkout");
    if (
      jQuery("input[name=payment_method]:checked").val() !==
      "inoviodirectmethod"
    ) {
      return true;
    }
    console.log("checkout_place_order");
    $form.find(".payment-errors").html("");
    $form.block({
      message: null,
      overlayCSS: {
        background:
          "#fff url(" +
          woocommerce_params.ajax_loader_url +
          ") no-repeat center",
        backgroundSize: "16px 16px",
        opacity: 0.6,
      },
    });

    if ($form.find('[name="zigu_threeds_cavv"]').length) {
      return true;
    }

    // if ($form.find('[name="zigu_token"]').length){
    // 	return true;
    // }
    // addValueField('zigu_token', '');

    var total = jQuery("#zigu_checkout_total").val();
    var totalNumber = Number.isNaN(Number.parseInt(total, 10))
      ? 0
      : Number.parseInt(total, 10);
    var wc_min_price_3ds_number = Number.isNaN(
      Number.parseInt(wc_min_price_3ds, 10)
    )
      ? 0
      : Number.parseInt(wc_min_price_3ds, 10);
    console.log(
      "test values",
      totalNumber,
      wc_min_price_3ds,
      wc_min_price_3ds_number
    );
    if (totalNumber / 100 > wc_min_price_3ds_number) {
      generateThreeDs();
    } else {
      return true;
    }

    // // Prevent the form from submitting with the default action
    return false;
  });
});
