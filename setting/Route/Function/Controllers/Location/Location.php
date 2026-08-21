<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Location;

use Setting\Route\Function\Controllers\Server\Network as ServerNetwork;

class Location
{

  public string $HOST;
  public string $BASIC_PATH;

  public function __construct()
  {
    // HOST — URL подписки текущего пользователя (субдомен = его сервер),
    // без подписки — сервер по умолчанию из реестра Network
    $this->HOST = ServerNetwork::getSubscriptionUrl();
    $this->BASIC_PATH = '/public/assets/images/icons/services/default/flags/';
  }

  public function getLocation()
  {
    // Сервер определяется по субдомену HOST через реестр Network
    $code = ServerNetwork::getServerCodeFromUrl($this->HOST);
    if ($code !== '') {
      $server = ServerNetwork::selectServer(null, $code);
      return [
        'location' => $server['country'],
        'url' => $this->BASIC_PATH . $server['flag']
      ];
    }

    // Fallback
    return [
      'location' => 'Не определенно',
      'url' => $this->BASIC_PATH . 'none.svg'
    ];
  }
}
