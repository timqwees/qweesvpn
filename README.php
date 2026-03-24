<?php
use Setting\Route\Function\Functions;

$client = (new Functions())->client(/*<ID>*/ null);

$price = (new Functions())->isPrice();//полуаем все цены
