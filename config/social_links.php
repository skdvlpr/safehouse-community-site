<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public social / contact links (CMS → Impostazioni → Social)
    |--------------------------------------------------------------------------
    */

    'storage_key' => 'social.links',

    'networks' => [
        'instagram' => [
            'label' => 'Instagram',
            'placeholder' => 'https://www.instagram.com/…',
            'type' => 'url',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'placeholder' => 'https://www.facebook.com/…',
            'type' => 'url',
        ],
        'whatsapp' => [
            'label' => 'WhatsApp',
            'placeholder' => 'https://wa.me/39…',
            'type' => 'url',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'info@safehouse.community',
            'type' => 'email',
        ],
        'tiktok' => [
            'label' => 'TikTok',
            'placeholder' => 'https://www.tiktok.com/@…',
            'type' => 'url',
        ],
    ],

];
