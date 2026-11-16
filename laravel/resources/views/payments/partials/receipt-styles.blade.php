<style>
    * {
        box-sizing: border-box;
    }

    .receipt-page {
        width: 100%;
        max-width: {{ (int) $ticketConfig->ticket_rollo_ancho_mm }}mm;
        margin: 0 auto;
        background: #fff;
        overflow: hidden;
        font-family: Helvetica, Arial, sans-serif;
    }

    .receipt-copy {
        padding: {{ $receiptPaddingTop }}mm {{ $receiptPaddingRight }}mm {{ $receiptPaddingBottom }}mm {{ $receiptPaddingLeft }}mm;
    }

    .receipt-copy + .receipt-copy {
        border-top: 2px dashed #000000;
        position: relative;
        margin-top: 18px;
        padding-top: 24px;
    }

    .receipt-copy + .receipt-copy::before {
        content: "CORTE AQUI";
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        padding: 0 10px;
        font-size: 11px;
        font-weight: 700;
        color: #000000;
        letter-spacing: 0.12em;
    }

    .receipt-company,
    .receipt-centered,
    .receipt-label {
        text-align: center;
    }

    .receipt-company {
        margin-bottom: 12px;
    }

    .receipt-company h1 {
        margin: 0 0 4px;
        font-size: 16px;
    }

    .receipt-company-logo {
        margin-bottom: 8px;
    }

    .receipt-company-logo img {
        display: inline-block;
        width: {{ (int) $ticketConfig->ticket_logo_ancho }}px;
        height: auto;
        object-fit: contain;
    }

    .receipt-company p {
        margin: 2px 0;
        font-size: 12px;
    }

    .receipt-company h1,
    .receipt-company p,
    .receipt-centered,
    .receipt-centered div,
    .receipt-block,
    .receipt-block td,
    .receipt-block span,
    .receipt-kv,
    .receipt-kv td,
    .receipt-kv-label,
    .receipt-kv-value,
    .receipt-label,
    .receipt-cutline {
        font-family: inherit;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
    }

    .receipt-divider {
        border-top: {{ $dividerWidth }} {{ $dividerStyle }} #000000;
        margin: 10px 0;
    }

    .receipt-block {
        margin-bottom: 10px;
        font-size: 12px;
    }

    .receipt-kv {
        width: 100%;
        border-collapse: collapse;
    }

    .receipt-kv td {
        padding: 2px 0;
        vertical-align: top;
    }

    .receipt-kv-label {
        width: 34%;
    }

    .receipt-kv-value {
        text-align: right;
        word-break: break-word;
    }

    .receipt-block strong {
        font-weight: 700;
    }

    .receipt-total {
        font-size: 15px;
        font-weight: 800;
    }

    .receipt-label {
        margin-top: 10px;
        font-size: 11px;
        font-weight: 700;
        color: #000000;
        letter-spacing: 0.08em;
    }

    .receipt-cutline {
        margin-top: 12px;
        padding-top: 8px;
        border-top: 2px dashed #000000;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        color: #000000;
    }
</style>
