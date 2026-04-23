# Zigu Payment Gateway — Response Codes Dictionary

This document describes the response codes returned by the payment gateway and provides
English translations for all messages originally written in Spanish.

The gateway response includes two key fields:

- **`PROCESSOR_RESPONSE`** — Code returned by the card network / issuing bank.
- **`SERVICE_RESPONSE`** — Code returned by the payment processor service layer.

When `PROCESSOR_RESPONSE` is not `0`, display the message from the **Processor Response** table.
When `PROCESSOR_RESPONSE` is `0`, display the message from the **Service Response** table.

The final user-facing message is built as:

```
"Transaction failed, transaction status: {message}"
```

---

## Processor Response Codes (`PROCESSOR_RESPONSE`)

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `0`  | APROBADA | APPROVED |
| `2`  | DECLINADA: favor de contactar a su banco | Declined: please contact your bank |
| `4`  | DECLINADA: tarjeta bloqueada por el banco emisor | Declined: card blocked by issuing bank |
| `5`  | DECLINADA: por el banco emisor | Declined by issuing bank |
| `12` | DECLINADA: Transacción inválida | Declined: invalid transaction |
| `14` | DECLINADA: número de tarjeta no válido | Declined: invalid card number |
| `41` | DECLINADA: tarjeta reportada como perdida | Declined: card reported as lost |
| `43` | DECLINADA: tarjeta reportada como robada | Declined: card reported as stolen |
| `51` | DECLINADA: fondos insuficientes | Declined: insufficient funds |
| `54` | DECLINADA: tarjeta caducada | Declined: expired card |
| `56` | DECLINADA: tarjeta no registrada | Declined: card not registered |
| `57` | DECLINADA: transacción no permitida por la tarjeta | Declined: transaction not permitted by card |
| `61` | DECLINADA: transacción excede el límite permitido por su tarjeta | Declined: transaction exceeds card limit |
| `62` | DECLINADA: tarjeta restringida | Declined: restricted card |
| `65` | DECLINADA: transacción excede la frecuencia permitida por su tarjeta | Declined: transaction exceeds card frequency limit |
| `82` | DECLINADA: transacción detenida por el módulo de seguridad de su tarjeta | Declined: blocked by card security module |
| `87` | DECLINADA: datos inválidos | Declined: invalid data |
| `89` | DECLINADA: servicio inválido | Declined: invalid service |
| `91` | DECLINADA: no se logró contactar al banco emisor | Declined: could not reach issuing bank |
| `N0` | DECLINADA: no fue posible autorizar la transacción | Declined: transaction could not be authorized |
| `N7` | DECLINADA: transacción no autorizada por el banco emisor de la tarjeta | Declined: not authorized by issuing bank |
| `O6` | DECLINADA: datos inválidos | Declined: invalid data |
| `P1` | DECLINADA: transacción excede el límite permitido por su tarjeta | Declined: transaction exceeds card limit |
| `Q2` | DECLINADA: transacción no permitida por la tarjeta | Declined: transaction not permitted by card |

---

## Service Response Codes (`SERVICE_RESPONSE`)

### Success

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `100` | Autorizada | Authorized |
| `101` | Servicio disponible | Service available |
| `660` | Aprobación parcial | Partial approval |

### Product / Configuration Errors

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `150` | Producto no encontrado | Product not found |
| `152` | Tipo de producto no encontrado | Product type not found |
| `155` | Divisa no configurada | Currency not configured |
| `190` | Configuración inválida de producto | Invalid product configuration |
| `192` | Producto no activo | Product not active |

### Account / Order Errors

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `500` | No tiene cuenta configurada | No account configured |
| `501` | Cliente no encontrado | Customer not found |
| `502` | Error en la transacción | Transaction error |
| `503` | Servicio no disponible | Service unavailable |
| `505` | Orden ajustada a cero | Order adjusted to zero |
| `506` | Monto a cobrar mayor al monto de la orden | Charge amount exceeds order amount |
| `507` | Orden capturada completa | Order fully captured |
| `510` | Orden devuelta | Order returned |
| `511` | Orden reportada como contra-cargo | Order reported as chargeback |
| `512` | Orden no encontrada | Order not found |
| `515` | Orden reembolsada | Order refunded |
| `516` | Reembolso mayor al valor de la orden | Refund exceeds order value |
| `518` | Missing required field | Missing required field |
| `519` | Missing Trial Descriptor | Missing Trial Descriptor |

### Currency / Card Brand Errors

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `520` | Divisa no aceptada | Currency not accepted |
| `522` | Marca de tarjeta no aceptada | Card brand not accepted |
| `565` | Monto no válido | Invalid amount |
| `570` | Marca de tarjeta no válida | Invalid card brand |
| `580` | Solicitud no admitida | Request not accepted |

### Processor / Technical Errors

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `525` | Batch Closed: Please credit | Batch Closed: Please credit |
| `530` | Downstream Processor Unavailable | Downstream Processor Unavailable |
| `536` | Order not settled: Please reverse | Order not settled: Please reverse |
| `555` | Call Center | Call Center |
| `560` | Invalid Service Action | Invalid Service Action |

### Fraud / Security Declines

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `600` | Declinada por el banco emisor. Favor de contactar a su banco. | Declined by issuing bank. Please contact your bank. |
| `601` | Transacción detenida por módulo anti-fraude. Esta tarjeta está temporalmente bloqueada en este sitio, intente nuevamente mañana. | Transaction blocked by fraud module. This card is temporarily blocked on this site, please try again tomorrow. |
| `603` | Transacción fraudulenta | Fraudulent transaction |
| `605` | Tarjeta reportada como robada | Card reported as stolen |
| `610` | Recoger tarjeta | Collect card |
| `615` | Tarjeta reportada como perdida | Card reported as lost |
| `650` | RECHAZADA - No intente nuevamente | REJECTED — Do not retry |

### Card Validation Declines

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `620` | CVV inválido | Invalid CVV |
| `621` | CVV fallido | CVV failed |
| `622` | Validación de dirección fallida | Address validation failed |
| `623` | Validación de dirección fallida | Address validation failed |
| `624` | Tarjeta vencida | Expired card |
| `625` | Uso excesivo | Excessive use |
| `630` | Número de tarjeta inválido | Invalid card number |
| `635` | Fondos insuficientes | Insufficient funds |
| `640` | Reintentar | Retry |
| `680` | Transacción duplicada | Duplicate transaction |
| `685` | Orden duplicada | Duplicate order |

### Membership Errors

| Code | Spanish Message | English Message |
|------|----------------|-----------------|
| `690` | Active Membership Exists | Active Membership Exists |
| `692` | Invalid Rebill Product | Invalid Rebill Product |
| `695` | Site Username Unavailable | Site Username Unavailable |
| `697` | Membresía no activa | Membership not active |
| `698` | Membresía no encontrada | Membership not found |
| `699` | Membresía no está configurada como suscripción | Membership not configured as subscription |

---

## How to Use This Dictionary

The gateway returns a response object. The programmer should:

1. Read `PROCESSOR_RESPONSE` from the response.
2. If `PROCESSOR_RESPONSE !== "0"`, look up the code in the **Processor Response Codes** table.
3. If `PROCESSOR_RESPONSE === "0"`, read `SERVICE_RESPONSE` and look up the code in the **Service Response Codes** table.
4. Display the corresponding English message to the end user.

### Example response object (simplified)

```json
{
  "PROCESSOR_RESPONSE": "51",
  "SERVICE_RESPONSE": "0",
  "TRANS_ID": "TXN123456"
}
```

In the example above, `PROCESSOR_RESPONSE` is `"51"` → display **"Declined: insufficient funds"**.

---

*Generated from source file: `includes/inoviopay/methods/class-inovio-direct-method.php`*
