<?php

use TelegramServer\Config;

return [
    // choose a (only) locally accessible IP address to bind the server to
    Config::IP              => '127.0.0.1',

    // choose an open port to bind the server to
    Config::PORT            => 4000,

    // choose the path to a writable directory to use as cache
    Config::CACHE_PATH      => __DIR__ . '/cache',

    // provide a my.telegram.org api ID
    Config::API_ID          => '196585',

    // provide a my.telegram.org api hash key
    Config::API_HASH        => '2e16b4227b0bf2057f71bdbfc7a7371d',

    // provide a random string sequence which will be used to verify requests and sign responses
    // only requests signed with this salt will be honored
    Config::SERVER_SALT     => '.XSRApwB|=KR9SRlq)%ejzqi~x/Oa.sgtq2_h&[@RfRNL]h,cP:*I4>R&d-c2,s',

    // set this to TRUE to enable test mode, set to FALSE in production
    Config::TEST_MODE       => true
];