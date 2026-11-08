<?php

namespace App\Traits;

class WhatsApp
{
    private $basic_token;
    private $number_id;
    private $url_base;

    public function __construct($token = '', $number = '')
    {
        $this->basic_token = $token;
        $this->number_id = $number;
        $this->url_base = 'https://graph.facebook.com/v15.0/';
    }

    public function peticion($ruta, $metodo, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url_base . $ruta);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: Application/json',
            'Authorization: Bearer ' . $this->basic_token,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rps = json_decode($response, true);

        return [
            'respuesta' => !(isset($rps['error'])),
            'http_code' => $http_code,
            'error' => @$rps['error'],
            'wa_id' => @$rps['contacts'][0]['wa_id'],
            'message_id' => @$rps['messages'][0]['id'],
            'raw_response' => $rps,
        ];
    }

    public function SendMsm($cellphone, $name, $nro_factura, $monto)
    {
        return $this->sendTemplateMessage(
            $cellphone,
            'hello_world',
            'en_US',
            [$name, $nro_factura, $monto]
        );
    }

    public function sendTextMessage($cellphone, $message)
    {
        $ruta = $this->number_id . '/messages';
        $metodo = 'POST';

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $cellphone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        return $this->peticion($ruta, $metodo, $data);
    }

    public function sendTemplateMessage($cellphone, $templateName, $languageCode = 'en_US', $parameters = [])
    {
        $ruta = $this->number_id . '/messages';
        $metodo = 'POST';

        $template = [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
        ];

        if (!empty($parameters)) {
            $template['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(function ($parameter) {
                        return [
                            'type' => 'text',
                            'text' => (string) $parameter,
                        ];
                    }, $parameters),
                ],
            ];
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $cellphone,
            'type' => 'template',
            'template' => $template,
        ];

        return $this->peticion($ruta, $metodo, $data);
    }
}
