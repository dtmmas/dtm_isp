# Paquete de despliegue para Hostinger

Este paquete deja listo lo necesario para actualizar el sistema en hosting sin subir todo el proyecto completo.

Actualizado para incluir tambien:
- facturacion mensual desde dashboard
- respaldo completo desde Configuracion
- restauracion desde Configuracion
- correccion del cache de tipo de cambio en `storage/app/exchange-rates`

## Contenido

- `files/`: archivos que debes subir al hosting respetando la misma ruta.
- `sql/2026_08_24_hosting_updates.sql`: script SQL para columnas nuevas y tabla `tickets`.
- `hosting.env.example`: variables clave que debes revisar en el `.env` del hosting.
- `files-to-upload.txt`: listado corto de referencia.

## Orden recomendado

1. Haz respaldo de archivos y base de datos.
2. Sube el contenido de `files/` respetando rutas.
3. Actualiza el `.env` del hosting tomando como base `hosting.env.example`.
4. Ejecuta el SQL de `sql/2026_08_24_hosting_updates.sql` en phpMyAdmin.
5. Si tu hosting tiene terminal, limpia caché:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Importante para este proyecto

### 1. Google Maps

Debes tener esta variable en el `.env` del hosting:

```env
GOOGLE_MAPS_API_KEY=tu_clave
```

### 2. Fotos de instalación

Los planes ahora pueden guardar fotos en el disco `public`, dentro de:

```text
laravel/storage/app/public/client-products
```

En hosting, la URL pública esperada es `/storage/...`.

Si tu instalación no tiene enlace simbólico `storage`, crea una carpeta pública `storage` en la raíz pública y copia dentro el contenido de:

```text
laravel/storage/app/public/
```

Debes mantener esa ruta pública para que las fotos carguen correctamente.

### 3. Compatibilidad con PHP 8.2

Si tu hosting usa PHP 8.2 y el sistema da errores al abrir, también sube estos archivos opcionales:

```text
laravel/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php
laravel/vendor/nesbot/carbon/src/Carbon/Traits/Creator.php
```

### 4. Respaldo y restauracion en hosting compartido

La opcion de respaldo descarga un ZIP reutilizando el ultimo respaldo generado para no agotar el tiempo de ejecucion web.

La opcion de restauracion sube un archivo ZIP y reemplaza base de datos y archivos incluidos. Antes de usarla en hosting compartido valida:

- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`
- espacio disponible en disco

Si Hostinger tiene limites bajos, conviene restaurar primero un respaldo pequeno o hacer el proceso por bloques.

## Qué actualiza este paquete

- menú y submenús
- dashboard
- tickets por plan
- coordenadas y fotos del plan
- impresión doble de pagos
- impresión térmica silenciosa con QZ Tray
- mapa general de clientes
- integración de Google Maps
- gestión de respaldo y restauración desde Configuración

## Impresión térmica silenciosa

Este despliegue ahora incluye soporte para impresión directa de tickets con QZ Tray y ESC/POS.

### Variables nuevas en `.env`

```env
THERMAL_PRINT_ENABLED=true
THERMAL_PRINTER_NAME=
THERMAL_PRINT_COPIES=2
THERMAL_FORCE_RAW=true
THERMAL_BROWSER_FALLBACK=false
THERMAL_LINE_WIDTH=32
QZ_TRAY_SCRIPT_URL=https://cdn.jsdelivr.net/npm/qz-tray@2.2.6/qz-tray.js
QZ_TRAY_SIGNATURE_ALGORITHM=SHA512
QZ_TRAY_CERT_PATH=
QZ_TRAY_PRIVATE_KEY_PATH=
```

### Para impresión silenciosa real

1. Instala QZ Tray en cada estación de caja.
2. Configura la impresora térmica como predeterminada o define `THERMAL_PRINTER_NAME`.
3. Si quieres evitar avisos de confianza de QZ Tray, agrega en el servidor:
   - `digital-certificate.txt`
   - `private-key.pem`
4. Luego apunta `QZ_TRAY_CERT_PATH` y `QZ_TRAY_PRIVATE_KEY_PATH` a esas rutas.

## Validación rápida después de subir

Revisa estas pantallas:

1. `Clientes -> Mapa Clientes`
2. `Clientes -> Planes contratados`
3. `Tickets`
4. `Pagos -> imprimir`
5. `Dashboard`
6. `Configuracion -> Descargar respaldo completo`
7. `Configuracion -> Restaurar respaldo`
