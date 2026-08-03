<?php

declare(strict_types=1);

namespace Setting\Route\Function\Controllers\Location;

class Location
{

  public string $HOST;
  public string $BASIC_PATH;

  public function __construct()
  {
    $this->HOST = $_ENV['XUI_URL_SUBSCRIPTION'] ?? '';
    $this->BASIC_PATH = '/public/assets/images/icons/services/default/flags/';
  }

  public function getLocation()
  {

    if (strpos($this->HOST, '/fi.') !== false) {// не логическое 0 == false
      return [
        'location' => 'Финляндия',
        'url' => $this->BASIC_PATH . 'finland.svg'
      ];
    } elseif (strpos($this->HOST, '/nl.') !== false) {
      return [
        'location' => 'Нидерланды',
        'url' => $this->BASIC_PATH . 'netherlands.svg'
      ];
    } elseif (strpos($this->HOST, '/de.') !== false) {
      return [
        'location' => 'Германия',
        'url' => $this->BASIC_PATH . 'germany.svg'
      ];
    } elseif (strpos($this->HOST, '/ro.') !== false) {
      return [
        'location' => 'Румыния',
        'url' => $this->BASIC_PATH . 'romania.svg'
      ];
    } elseif (strpos($this->HOST, '/us.') !== false) {
      return [
        'location' => 'США',
        'url' => $this->BASIC_PATH . 'usa.svg'
      ];
    } elseif (strpos($this->HOST, '/gb.') !== false) {
      return [
        'location' => 'Лондон',
        'url' => $this->BASIC_PATH . 'london.svg'
      ];
    } elseif (strpos($this->HOST, '/cz.') !== false) {
      return [
        'location' => 'Чехия',
        'url' => $this->BASIC_PATH . 'czech.svg'
      ];
    }

    // Fallback
    return [
      'location' => 'Не определенно',
      'url' => $this->BASIC_PATH . 'none.svg'
    ];
  }
}