<?php

return [

    'paypal' => [
        'endpoint' => env('PAYPAL_URL','https://api-m.sandbox.paypal.com'), //https://api-m.paypal.com
        'email_address' => env('PAYPAL_EMAIL','saxxx@gmail.com'),
        'secret' => env('PAYPAL_SECRET', 'EFSseEhIy5kkd7en7vavSSbWxQ_6j9tjoDNJEvQ1prQ3qFTYIpp5EIUCnjLyNpbgzWcs-i0SoqZr1ed_'),
        'client_id' => env('PAYPAL_CLIENT_ID', 'AYqGDla0uHIqd17s9KKDSyI9x6XC2lrSZ7rdTikjWkH0njSIaOdCvEdzjqlPXGYhwJKdnIYzKnIn3qJI'),
    ],
];

?>