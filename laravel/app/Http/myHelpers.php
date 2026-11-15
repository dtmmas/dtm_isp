<?php

use Carbon\Carbon;
use App\Models\Setting;

function convertir_minusculas($var){
    return strtolower($var);
}

function primerdiames()
{
    return Carbon::now()->startOfMonth()->format('Y-m-d');
}

function ultimoDiaMesAnterior()
{
    return Carbon::now()->subMonth()->endOfMonth();
}

function ultimoDiaMesSumado($meses)
{
    return Carbon::now()->addMonth($meses)->endOfMonth();
}

function fecha_actual()
{
    return Carbon::now()->format('Y-m-d H:i:s');
}

function ultimoDiaMesFecha($fecha)
{
    $mañana = new Carbon($fecha);
    return $mañana->endOfMonth()->format('Y-m-d');
}

function fecha_tomorrow()
{
    $mañana = new Carbon('tomorrow');
    return $mañana->format('Y-m-d');
}

function normalizeWhatsAppPhone($phone, $defaultCountryCode = '502')
{
   $phone = preg_replace('/\D+/', '', (string) $phone);

   if ($phone === '') {
      return '';
   }

   if (strpos($phone, $defaultCountryCode) !== 0 && strlen($phone) <= 8) {
      return $defaultCountryCode.$phone;
   }

   return $phone;
}


function normalizeSearchText($value)
{
   $value = trim((string) $value);

   if ($value === '') {
      return '';
   }

   $value = function_exists('mb_strtolower')
      ? mb_strtolower($value, 'UTF-8')
      : strtolower($value);

   $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
   $value = preg_replace('/\s+/u', ' ', $value);

   return trim($value);
}

function compactSearchText($value)
{
   $normalized = normalizeSearchText($value);

   if ($normalized === '') {
      return '';
   }

   return preg_replace('/[^\pL\pN]+/u', '', $normalized);
}

function searchTokens($value)
{
   $normalized = normalizeSearchText($value);

   if ($normalized === '') {
      return [];
   }

   $parts = preg_split('/\s+/u', $normalized);
   $tokens = [];

   foreach ($parts as $part) {
      $token = preg_replace('/[^\pL\pN]+/u', '', $part);

      if ($token !== '') {
         $tokens[] = $token;
      }
   }

   return array_values(array_unique($tokens));
}

function normalizedSearchSql($expression)
{
   $sql = "LOWER(COALESCE($expression, ''))";
   $characters = [' ', '-', '.', ',', '/', '\\\\', '(', ')', '#', '_'];

   foreach ($characters as $character) {
      $sql = "REPLACE($sql, '$character', '')";
   }

   return $sql;
}

function applyFlexibleSearch($query, $search, $expressions)
{
   $compact = compactSearchText($search);
   $tokens = searchTokens($search);

   if ($compact === '' && empty($tokens)) {
      return $query;
   }

   $normalizedExpressions = array_map(function ($expression) {
      return normalizedSearchSql($expression);
   }, $expressions);

   $applyAnyExpression = function ($builder, $term) use ($normalizedExpressions) {
      $builder->where(function ($searchQuery) use ($normalizedExpressions, $term) {
         foreach ($normalizedExpressions as $index => $expression) {
            if ($index === 0) {
               $searchQuery->whereRaw($expression.' LIKE ?', ['%'.$term.'%']);
            } else {
               $searchQuery->orWhereRaw($expression.' LIKE ?', ['%'.$term.'%']);
            }
         }
      });
   };

   $query->where(function ($outerQuery) use ($compact, $tokens, $applyAnyExpression) {
      if ($compact !== '') {
         $applyAnyExpression($outerQuery, $compact);
      }

      if (!empty($tokens)) {
         if ($compact !== '') {
            $outerQuery->orWhere(function ($tokenQuery) use ($tokens, $applyAnyExpression) {
               foreach ($tokens as $token) {
                  $applyAnyExpression($tokenQuery, $token);
               }
            });
         } else {
            foreach ($tokens as $token) {
               $applyAnyExpression($outerQuery, $token);
            }
         }
      }
   });

   return $query;
}

function userSearchExpressions($table = 'users')
{
   return [
      "$table.name",
      "$table.lastname",
      "$table.dni",
      "$table.phone",
      "$table.address",
      "$table.email",
      "CONCAT_WS('', $table.name, $table.lastname)",
      "CONCAT_WS('', $table.lastname, $table.name)",
   ];
}

function planSearchExpressions($table = 'client_product')
{
   return [
      "$table.address",
      "$table.reference",
      "CONCAT_WS('', $table.address, $table.reference)",
      "CONCAT_WS('', $table.reference, $table.address)",
   ];
}
function formatWhatsAppCurrency($amount, $prefix = 'Q')
{
   if (is_string($amount)) {
      $normalized = preg_replace('/[^\d,.\-]/', '', $amount);

      if ($normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
         $amount = 0;
      } else {
         $lastComma = strrpos($normalized, ',');
         $lastDot = strrpos($normalized, '.');

         if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
               $normalized = str_replace('.', '', $normalized);
               $normalized = str_replace(',', '.', $normalized);
            } else {
               $normalized = str_replace(',', '', $normalized);
            }
         } elseif ($lastComma !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
         } else {
            $normalized = str_replace(',', '', $normalized);
         }

         $amount = (float) $normalized;
      }
   }

   return $prefix.number_format((float) $amount, 2, '.', ',');
}

function whatsappTemplateConfig($type, $context = [])
{
   $config = whatsappConfig();
   $templates = isset($config->templates) ? $config->templates : [];
   $template = isset($templates[$type]) ? $templates[$type] : null;

   if (!$template) {
      return null;
   }

   $name = isset($template['name']) ? $template['name'] : '';
   $language = isset($template['language']) ? $template['language'] : 'en_US';

   if ($name === '') {
      return null;
   }

   $parameters = [];

   if ($name !== 'hello_world') {
      if ($type === 'moroso') {
         $parameters = [
            $context['name'] ?? '',
            $context['monthList'] ?? '',
            $context['total_deuda'] ?? '',
         ];
      }

      if ($type === 'vencimiento') {
         $parameters = [
            $context['name'] ?? '',
            $context['monthList'] ?? '',
            $context['total_deuda'] ?? '',
         ];
      }
   }

   return [
      'name' => $name,
      'language' => $language,
      'parameters' => $parameters,
   ];
}

function whatsappConfig()
{
   return (Object)[
      'token' => env('WHATSAPP_TOKEN', ''),
      'number' => env('WHATSAPP_NUMBER_ID', ''),
      'templates' => [
         'moroso' => [
            'name' => env('WHATSAPP_TEMPLATE_MOROSO_NAME', 'aviso_mora_cliente'),
            'language' => env('WHATSAPP_TEMPLATE_MOROSO_LANGUAGE', 'es'),
         ],
         'vencimiento' => [
            'name' => env('WHATSAPP_TEMPLATE_VENCIMIENTO_NAME', 'vencimiento_factura'),
            'language' => env('WHATSAPP_TEMPLATE_VENCIMIENTO_LANGUAGE', 'es'),
         ],
      ],
   ];
}

function twilioConfig()
{
       return (Object)[
            'number'=> env('TWILIO_NUMBER', ''),
            'Token'=> env('TWILIO_TOKEN', ''),
            'SID'=> env('TWILIO_SID', ''),
            'MessagingServiceSid'=> env('TWILIO_MESSAGING_SERVICE_SID', ''),
       ];
}

function logoEmpresaConfig()
{
   $setting = Setting::where('type','empresa')->where('name','logo_empresa')->select('value')->first();
   $logo = optional($setting)->value;

   return (!empty($logo)) ? 'laravel/storage/app/public/'.ltrim($logo, '/') : "plugins/images/logo-default.png";
}

function nombreEmpresaConfig()
{
   $setting = Setting::where('type','empresa')->where('name','nombre_empresa')->select('value')->first();

   return optional($setting)->value ?? '';
}

function empresaConfig()
{
   return (Object)Setting::where('type','empresa')->select(['name','value'])->get()->pluck('value','name')->toArray();
}

function ticketPrintConfig()
{
   $config = array_merge([
      'ticket_documento_titulo' => 'RECIBO',
      'ticket_copias' => 2,
      'ticket_mensaje_final' => '',
      'ticket_rollo_ancho_mm' => 80,
      'ticket_margen_interno_mm' => 4,
      'ticket_margen_superior_mm' => 4,
      'ticket_margen_derecho_mm' => 4,
      'ticket_margen_inferior_mm' => 4,
      'ticket_margen_izquierdo_mm' => 4,
      'ticket_separador_estilo' => 'dashed',
      'ticket_mostrar_linea_corte' => true,
      'ticket_mostrar_logo' => true,
      'ticket_logo_ancho' => 80,
      'ticket_mostrar_dpi' => true,
      'ticket_mostrar_direccion_plan' => true,
      'ticket_mostrar_observacion' => true,
      'ticket_auto_imprimir' => true,
   ], Setting::where('type', 'ticket')->select(['name', 'value'])->get()->pluck('value', 'name')->toArray());

   $styles = ticketPrintStyleDefaults();

   foreach ($config as $key => $value) {
      if (strpos($key, 'ticket_estilo_') !== 0) {
         continue;
      }

      $styleName = substr($key, strlen('ticket_estilo_'));
      $lastUnderscorePosition = strrpos($styleName, '_');

      if ($lastUnderscorePosition === false) {
         continue;
      }

      $section = substr($styleName, 0, $lastUnderscorePosition);
      $attribute = substr($styleName, $lastUnderscorePosition + 1);

      if (!isset($styles[$section]) || !array_key_exists($attribute, $styles[$section])) {
         continue;
      }

      $styles[$section][$attribute] = $attribute === 'tamano' ? (int) $value : $value;
   }

   $config['ticket_copias'] = max(1, (int) $config['ticket_copias']);
   $config['ticket_rollo_ancho_mm'] = in_array((int) $config['ticket_rollo_ancho_mm'], [58, 72, 80, 110, 112], true) ? (int) $config['ticket_rollo_ancho_mm'] : 80;
   $config['ticket_margen_interno_mm'] = in_array((int) $config['ticket_margen_interno_mm'], [2, 4, 6], true) ? (int) $config['ticket_margen_interno_mm'] : 4;
   $config['ticket_margen_superior_mm'] = in_array((int) $config['ticket_margen_superior_mm'], [2, 4, 6, 8], true) ? (int) $config['ticket_margen_superior_mm'] : (int) $config['ticket_margen_interno_mm'];
   $config['ticket_margen_derecho_mm'] = in_array((int) $config['ticket_margen_derecho_mm'], [2, 4, 6, 8], true) ? (int) $config['ticket_margen_derecho_mm'] : (int) $config['ticket_margen_interno_mm'];
   $config['ticket_margen_inferior_mm'] = in_array((int) $config['ticket_margen_inferior_mm'], [2, 4, 6, 8], true) ? (int) $config['ticket_margen_inferior_mm'] : (int) $config['ticket_margen_interno_mm'];
   $config['ticket_margen_izquierdo_mm'] = in_array((int) $config['ticket_margen_izquierdo_mm'], [2, 4, 6, 8], true) ? (int) $config['ticket_margen_izquierdo_mm'] : (int) $config['ticket_margen_interno_mm'];
   $config['ticket_separador_estilo'] = in_array($config['ticket_separador_estilo'], ['dashed', 'solid', 'dotted'], true) ? $config['ticket_separador_estilo'] : 'dashed';
   $config['ticket_mostrar_linea_corte'] = filter_var($config['ticket_mostrar_linea_corte'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_mostrar_linea_corte'] === '1';
   $config['ticket_logo_ancho'] = max(40, min(180, (int) $config['ticket_logo_ancho']));
   $config['ticket_mostrar_logo'] = filter_var($config['ticket_mostrar_logo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_mostrar_logo'] === '1';
   $config['ticket_mostrar_dpi'] = filter_var($config['ticket_mostrar_dpi'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_mostrar_dpi'] === '1';
   $config['ticket_mostrar_direccion_plan'] = filter_var($config['ticket_mostrar_direccion_plan'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_mostrar_direccion_plan'] === '1';
   $config['ticket_mostrar_observacion'] = filter_var($config['ticket_mostrar_observacion'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_mostrar_observacion'] === '1';
   $config['ticket_auto_imprimir'] = filter_var($config['ticket_auto_imprimir'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $config['ticket_auto_imprimir'] === '1';
   $config['styles'] = $styles;

   return (Object) $config;
}

function ticketPrintStyleDefaults()
{
   return [
      'encabezado' => ['familia' => 'sans', 'tamano' => 12, 'peso' => 'normal'],
      'logo' => ['familia' => 'sans', 'tamano' => 18, 'peso' => 'full'],
      'titulo' => ['familia' => 'sans', 'tamano' => 14, 'peso' => 'full'],
      'cliente' => ['familia' => 'sans', 'tamano' => 12, 'peso' => 'medium'],
      'concepto' => ['familia' => 'mono', 'tamano' => 12, 'peso' => 'normal'],
      'pie' => ['familia' => 'sans', 'tamano' => 11, 'peso' => 'medium'],
   ];
}

function ticketSectionStyle($ticketConfig, $section)
{
   $styles = isset($ticketConfig->styles) ? (array) $ticketConfig->styles : ticketPrintStyleDefaults();
   $default = ticketPrintStyleDefaults()[$section] ?? ['familia' => 'sans', 'tamano' => 12, 'peso' => 'normal'];
   $style = array_merge($default, $styles[$section] ?? []);

   return (Object) [
      'familia' => in_array($style['familia'], ['sans', 'serif', 'mono'], true) ? $style['familia'] : $default['familia'],
      'tamano' => max(10, min(24, (int) $style['tamano'])),
      'peso' => in_array($style['peso'], ['normal', 'medium', 'full'], true) ? $style['peso'] : $default['peso'],
   ];
}

function ticketSectionFontFamilyCss($family)
{
   switch ($family) {
      case 'serif':
         return "Georgia, 'Times New Roman', serif";
      case 'mono':
         return "'Courier New', monospace";
      case 'sans':
      default:
         return "Arial, Helvetica, sans-serif";
   }
}

function ticketSectionFontWeightCss($weight)
{
   switch ($weight) {
      case 'medium':
         return '600';
      case 'full':
         return '800';
      case 'normal':
      default:
         return '400';
   }
}

function ticketSectionStyleCss($ticketConfig, $section)
{
   $style = ticketSectionStyle($ticketConfig, $section);

   return 'font-family: '.ticketSectionFontFamilyCss($style->familia)
      .'; font-size: '.$style->tamano.'px'
      .'; font-weight: '.ticketSectionFontWeightCss($style->peso).';';
}
?>
