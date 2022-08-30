<?php
    global $woocommerce;
    $order_price_cents = $woocommerce->cart->total * 100;
    // echo $order_price_cents;

    // echo $responseArray->OrgID;
    // echo $responseArray->WebSessionID;
    // echo $transId;
?>
<!-- Begin fingerprinting tags below -->
   
<p style="background:url(https://fp.ecustomerpayments.com/DF/fp/clear.png?org_id=<?php echo $responseArray->OrgID; ?>&session_id=<?php echo $responseArray->WebSessionID; ?>&m=1)"></p>
   
<img src="https://fp.ecustomerpayments.com/DF/fp/clear.png?org_id=<?php echo $responseArray->OrgID; ?>&session_id=<?php echo $responseArray->WebSessionID; ?>&m=2" alt=""/>
   
<script src="https://fp.ecustomerpayments.com/DF/fp/check.js?org_id=<?php echo $responseArray->OrgID; ?>&session_id=<?php echo $responseArray->WebSessionID; ?>" type="text/javascript"></script>

<!-- Cambiar a produccion -->
<!-- Begin Sandbox Data Collector tags below -->
<script src="https://riskcsproxy.ecustomersupport.com/DCCSProxy/Service/vdccs.js?AccountName=<?php echo $AccountName; ?>&WebSessionID=<?php echo $responseArray->WebSessionID; ?>" async type=“text/javascript”></script>
   
<!-- End fingerprinting tags -->
<fieldset class="inoviodirectmethod_gate_form">
    <p class="form-row form-row-wide validate-required inoviodirectmethod_gate_card_number_wrap">
        <label
            for="inoviodirectmethod_gate_card_numbers"
        >
            Número de tarjeta
        </label>
        <input
            class="input-text"
            name="inoviodirectmethod_gate_card_numbers"
            title="Please enter valid card no"
            id="inoviodirectmethod_gate_card_numbers"
            pattern="^0[1-16]|[1-16]\d$"
            maxlength="16"
            size="16"
            type="text"
            required
        >
        <span
            id="inoviodirectmethod_gate_card_type_image"
        ></span>
    </p>
    <p
        class="form-row form-row-first validate-required"
    >
        <label
            for="inoviodirectmethod_gate_card_expiration"
        >
            Fecha de expiración
        </label>
        <select
            id="cc-exp-month"
            class="txt"
            name="exp_month"
        >
            <option value="01">Ene</option>
            <option value="02">Feb</option>
            <option value="03">Mar</option>
            <option value="04">Abr</option>
            <option value="05">May</option>
            <option value="06">Jun</option>
            <option value="07">Jul</option>
            <option value="08">Ago</option>
            <option value="09">Sep</option>
            <option value="10">Oct</option>
            <option value="11">Nov</option>
            <option value="12">Dic</option>
        </select>
        <select
            id="cc-exp-year"
            class="txt"
            name="exp_year"
        >
            <?php
                $html="";
                $today = date( 'Y' );
                $start = date( 'Y' );
                for ( $start; $start <= $today + 10; $start++ ) {
                    $html .= "<option value='" . $start . "'>$start</option>";
                }
                echo $html;
            ?>
        </select>
    </p>
    <p
        class="form-row form-row-last validate-required"
    >
        <label
            for="inoviodirectmethod_gate_card_csc"
        >
            Código de seguridad
        </label>
        <input
            type="password"
            class="input-text"
            id="inoviodirectmethod_gate_card_cvv"
            title="Please enter valid card security no"
            name="inoviodirectmethod_gate_card_cvv"
            maxlength="4"
            size="4"
            pattern="[0-9]+"
            required
        />
    </p>
    <div class="clear"></div>
    <p
        class="form-row form-row-last"
        style="display: none;"
    >
        <label
            for="inoviodirectmethod_gate_card_csc"
        >
            Kount Session ID
        </label>
        <input
            type="text"
            class="input-text"
            id="kountSessionId"
            title="Kount Session ID"
            name="KOUNT_SESSIONID"
        />
    </p>
    <?php
        if (!empty($this->installments)) {
    ?>
        <p
            class="form-row form-row-first validate-required"
        >
            <label
                for="inoviodirectmethod_gate_card_expiration"
            >
                Meses sin intereses
            </label>
            <select
                id="cc-installments"
                class="txt"
                name="inoviodirectmethod_installments"
            >
                <option value="01">01</option>
                <?php
                    $html = '';
                    foreach($this->installments as $key => $value) {
                        $html .= "<option value='" . $key . "'>$value</option>";
                    }
                    echo $html;
                ?>
            </select>
        </p>
    <?php
        }
    ?>
    <input
        type="hidden"
        class="input-text"
        id="zigu_checkout_total"
        title="zigu checkout total"
        name="zigu_checkout_total"
        value="<?php echo $order_price_cents; ?>"
    />
    <div class="clear"></div>
</fieldset>
        