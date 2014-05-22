<?php

/**
 * Created by PhpStorm.
 * User: ismael trascastro
 * Date: 21/12/13
 * Time: 22:47
 */

namespace views\helpers;

use xen\mvc\helpers\ViewHelper;

class UsersMenuHelper extends ViewHelper {

    function __construct($params = array()) {
        $this->_html = '
            <ul class="list-inline">
                <li><a href="/users/getBirdsEyeView/">Vista general clientes</a></li>
                <li><a href="/users/getPlatedIncomings/">Entradas matriculadas</a></li>
                <li><a href="/users/getExpirationControl/">Control de vencimientos</a></li>
            </ul>
        ';
    }
}
