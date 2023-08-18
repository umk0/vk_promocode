<?php
$setting = [
    "LANG" => "ru",
    "DEBUG" => [
        "status" => true,
        "url_webhook" => "https://webhook.site/61ea55b2-918e-423c-b1cb-5bae51ff1eb6",
    ],
    "PROMOCODE" => [
        "mask" => "XXXX-XXXX-XXXX-XXXX", //Можно делать любую маску менятся будет только символ X на рандомный остальные символы в маске не меняются к примеру маска uMk0-XXXX-XXXX выведет промокод uMk0-A4KE-MP9F
        "characters" => "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",
    ],
    "SERVER" => [
        "STATUS_SERVER" => true,
        "IP" => "127.0.0.1",
        "PORT" => 27015,
    ],
    "MYSQL" => [
        "host" => "localhost",
        "database" => "db",
        "username" => "root",
        "password" => "",
        "prefix"   => "vkp"
    ],
    "YOOMONEY" => [
        "enabled" => true,
        "id" => 0,// id yoomoney
        "commission_type" => 0, //0 - комисию платит покупатель 1 - комиссию платит продавец
    ],
    "VK_PAY" => [
        "enabled" => true,
    ],
    "VK" => [
        "group_id" => 0,//id группы получить можно тут https://regvk.com/id/
        "access_token" => "access token", //access token
        "confirmation" => "confirm code", //код проверки для вк
        "back_btn_color" => "white",// цвет кнопки назад
        "main_menu_btn_color" => "white", //
        "buy_more_btn" => "green",
        "main_menu" => [
            [
                "type" => "pay_menu",
                "text" => "pay_menu_btn",
                "color" => "green",
            ],
            [
                "type" => "status",
                "text" => "status_server_btn",
                "color" => "blue",
            ],

        ],
        "services" => [
            [
                "name" => "Админка",
                "image" => "id картинки",//получить можно на странице /id_photo.php
                "days_and_price" => [
                    "1" => 10, //"количествой дней" => цена в РУБЛЯХ
                    "30" => 300, //"количествой дней" => цена в РУБЛЯХ
                ],
                "flags" => "a",
                "desc" => "Текст принятия правил",
                "color" => "red",
            ],
            [
                "name" => "Вип",
                "image" => "id картинки",//получить можно на странице /id_photo.php
                "days_and_price" => [
                    "1" => 1, //"количествой дней" => цена в РУБЛЯХ
                    "30" => 150, //"количествой дней" => цена в РУБЛЯХ
                ],
                "flags" => "t",
                "desc" => "Текст принятия правил",
                "color" => "green",
            ],
        ]
    ]
];
