<?php
    global $woocommerce;
    $order_price_cents = $woocommerce->cart->total * 100;
    // echo $order_price_cents;
?>
<fieldset class="inoviodirectmethod_gate_form">
    <p class="form-row form-row-wide validate-required inoviodirectmethod_gate_card_number_wrap">
        <label
            for="inoviodirectmethod_gate_card_numbers"
        >
            <?php echo esc_html( Zigu_Translator::t( 'Número de tarjeta' ) ); ?>
        </label>
        <input
            class="input-text"
            name="inoviodirectmethod_gate_card_numbers"
            title="<?php echo esc_attr( Zigu_Translator::t( 'Favor de ingresar un número de tarjeta válido' ) ); ?>"
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
            <?php echo esc_html( Zigu_Translator::t( 'Fecha de expiración' ) ); ?>
        </label>
        <select
            id="cc-exp-month"
            class="txt"
            name="exp_month"
        >
            <option value="01"><?php echo esc_html( Zigu_Translator::t( 'Ene' ) ); ?></option>
            <option value="02"><?php echo esc_html( Zigu_Translator::t( 'Feb' ) ); ?></option>
            <option value="03"><?php echo esc_html( Zigu_Translator::t( 'Mar' ) ); ?></option>
            <option value="04"><?php echo esc_html( Zigu_Translator::t( 'Abr' ) ); ?></option>
            <option value="05"><?php echo esc_html( Zigu_Translator::t( 'May' ) ); ?></option>
            <option value="06"><?php echo esc_html( Zigu_Translator::t( 'Jun' ) ); ?></option>
            <option value="07"><?php echo esc_html( Zigu_Translator::t( 'Jul' ) ); ?></option>
            <option value="08"><?php echo esc_html( Zigu_Translator::t( 'Ago' ) ); ?></option>
            <option value="09"><?php echo esc_html( Zigu_Translator::t( 'Sep' ) ); ?></option>
            <option value="10"><?php echo esc_html( Zigu_Translator::t( 'Oct' ) ); ?></option>
            <option value="11"><?php echo esc_html( Zigu_Translator::t( 'Nov' ) ); ?></option>
            <option value="12"><?php echo esc_html( Zigu_Translator::t( 'Dic' ) ); ?></option>
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
            <?php echo esc_html( Zigu_Translator::t( 'Código de seguridad' ) ); ?>
        </label>
        <input
            type="password"
            class="input-text"
            id="inoviodirectmethod_gate_card_cvv"
            title="<?php echo esc_attr( Zigu_Translator::t( 'Favor de ingresar un código de seguridad válido' ) ); ?>"
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
                <?php echo esc_html( Zigu_Translator::t( 'Meses sin intereses' ) ); ?>
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
        