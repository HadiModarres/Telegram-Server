<?php

use danog\MadelineProto\API;
use danog\MadelineProto\Serialization;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;
use TelegramServer\Config;
use TelegramServer\Logger;
use TelegramServer\TelegramServer;

require __DIR__ . '/vendor/autoload.php';

Config::config(require __DIR__ . '/config.php');

// quick fix for windows environments
if (!function_exists('posix_isatty')) {
    function posix_isatty()
    {
        return false;
    }
}

array_shift($argv);

Config::config(Config::TEST_MODE, in_array('test', $argv));

$mtpSession = Config::config(Config::CACHE_PATH) . '/session' . (Config::config(Config::TEST_MODE) ? '-test-mode' : '');

if (in_array('clean', $argv)) {
    @unlink($mtpSession);
    @unlink($mtpSession . '.lock');
    clearstatcache();
}

try {
    if (!file_exists($mtpSession)) {
        throw new \Exception('');
    }
    $mtp = Serialization::deserialize($mtpSession);
} catch (\Exception $e) {
    $mtp = new API([
        'connection_settings' => [
            'all' => [
                'test_mode' => Config::config(Config::TEST_MODE)
            ]
        ],
        'app_info' => [
            'api_id' => Config::config(Config::API_ID),
            'api_hash' => Config::config(Config::API_HASH)
        ],
        'updates' => [
            'handle_updates' => false
        ]
    ]);
    $mtp->serialize($mtpSession);
}

$loop = Factory::create();
$telegram = new TelegramServer($mtp);
$server = new Server(function (ServerRequestInterface $request) use ($telegram) {
    $status = 200;

    try {
        $params = $request->getQueryParams();

        if (!isset($params['__signature'])) {
            throw new \Exception('Missing request signature.');
        }

        $signature = $params['__signature'];
        unset($params['__signature']);

        if ($signature !== sha1(json_encode($params) . Config::config(Config::SERVER_SALT))) {
            throw new \Exception('Invalid request signature.');
        }

        $response = $telegram->handle($params);
        $result = [
            'ok' => true,
            'response' => $response,
            'signature' => sha1(json_encode($response) . Config::config(Config::SERVER_SALT))
        ];
    } catch (\Exception $e) {
        $status = 500;
        $result = [
            'ok' => false,
            'error' => $e->getMessage()
        ];
    }

    $result = json_encode($result, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0);
    $length = mb_strlen($result);

    return new Response($status, [
        'Content-Type' => 'application/json',
        'Content-Length' => $length
    ], $result);
});
$socket = new React\Socket\Server(Config::config(Config::IP) . ':' . Config::config(Config::PORT), $loop);

$server->listen($socket);
$loop->addPeriodicTimer(10, [$telegram, 'expireSessions']);

Logger::log(PHP_EOL . str_repeat('-', 80));
Logger::log('|' . str_repeat(' ', 78) . '|');
Logger::log('|' . str_pad('Server running on ' . $socket->getAddress(), 78, ' ', STR_PAD_BOTH) . '|');
Logger::log('|' . str_repeat(' ', 78) . '|');
Logger::log(str_repeat('-', 80) . PHP_EOL);

$loop->run();