<?php

require_once __DIR__ . '/vendor/autoload.php';

use mishannn\Tinkoff\TinkoffAPI;
use mishannn\Tinkoff\WaitingConfirmationException;

try {
	$params = [];

    $post = filter_input_array(INPUT_POST);
    if (!empty($post)) {
        if (is_string($post['wuid'])) {
            $params['webUserId'] = $post['wuid'];
        }

        if (is_string($post['session'])) {
            $params['sessionId'] = $post['session'];
        }
    }

    $tinkoffApi = new TinkoffAPI($params);

    if (!empty($post) && $post['sms'] === 'true') {
        $tinkoffApi->confirmBySms($post['method'], $post['ticket'], $post['code']);
        $tinkoffApi->levelUp();

        $sessionId = $tinkoffApi->getLocalSessionId();
        $webUserId = $tinkoffApi->getLocalWebUserId();

        echo "<b>SESSION:</b> {$sessionId}<br><b>WUID:</b> {$webUserId}";
    } else {
        $tinkoffApi->signUp(LOGIN, PASSWORD);
        $tinkoffApi->levelUp();

        $sessionId = $tinkoffApi->getLocalSessionId();
        $webUserId = $tinkoffApi->getLocalWebUserId();

        echo "<b>SESSION:</b> {$sessionId}<br><b>WUID:</b> {$webUserId}";
    }
} catch (WaitingConfirmationException $exception) {
    $data = $exception->getData();

    $html = '<form method="post">'
        . '<input type="hidden" name="sms" value="true">'
        . '<input type="hidden" name="wuid" value="' . $exception->getWebUserId() . '">'
        . '<input type="hidden" name="session" value="' . $exception->getSessionId() . '">'
        . '<input type="hidden" name="method" value="' . $data->initialOperation . '">'
        . '<input type="hidden" name="ticket" value="' . $data->operationTicket . '">'
        . '<input type="text" name="code">'
        . '<button type="submit">Confirm</button>'
        . '</form>';

    echo $html;
} catch (Exception $exception) {
    echo $exception->getMessage();
} catch (Throwable $exception) {
    echo $exception->getMessage();
} 

?>