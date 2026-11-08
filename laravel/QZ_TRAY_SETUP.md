# QZ Tray - Impresion termica directa

## Objetivo

Imprimir tickets de pago directo a la impresora termica predeterminada, sin vista previa del navegador.

## Flujo implementado

1. Al reportar un pago, la vista llama a `window.DTMPrinter.printPaymentReceipt(paymentId)`.
2. El navegador intenta conectar con QZ Tray.
3. El sistema solicita el ticket ESC/POS en `GET /payments/thermal/{payment}`.
4. Si QZ Tray esta disponible, envia el ticket a la impresora predeterminada o a la configurada en `THERMAL_PRINTER_NAME`.
5. Si la estacion aun no tiene QZ Tray o falla la conexion, el sistema usa el respaldo actual por navegador.

## Variables de entorno

Agregar en `.env`:

```env
THERMAL_PRINT_ENABLED=true
THERMAL_PRINTER_NAME=
THERMAL_PRINT_COPIES=2
THERMAL_FORCE_RAW=true
THERMAL_BROWSER_FALLBACK=true
THERMAL_LINE_WIDTH=32
QZ_TRAY_SCRIPT_URL=https://cdn.jsdelivr.net/npm/qz-tray@2.2.6/qz-tray.js
QZ_TRAY_SIGNATURE_ALGORITHM=SHA512
QZ_TRAY_CERT_PATH=
QZ_TRAY_PRIVATE_KEY_PATH=
```

## Silencioso real

Para eliminar por completo los dialogos de confianza de QZ Tray:

1. Instalar QZ Tray en cada estacion.
2. Generar o adquirir `digital-certificate.txt` y `private-key.pem`.
3. Guardarlos en el servidor.
4. Configurar:

```env
QZ_TRAY_CERT_PATH=storage/app/qz/digital-certificate.txt
QZ_TRAY_PRIVATE_KEY_PATH=storage/app/qz/private-key.pem
```

5. Reiniciar QZ Tray en la estacion.

## Notas

- Si `THERMAL_PRINTER_NAME` queda vacio, el sistema usa la impresora predeterminada del sistema operativo.
- El ticket se genera en ESC/POS texto plano y corta papel al final de cada copia.
- Si la impresora no acepta raw por su driver actual, probar con cola raw/generic o mantener `THERMAL_FORCE_RAW=true`.
