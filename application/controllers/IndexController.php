<?php
/**
 * Created by PhpStorm.
 * User: ismael trascastro
 * Date: 12/12/13
 * Time: 16:45
 */

namespace controllers;

use xen\application\Router;
use xen\eventSystem\Event;
use xen\eventSystem\EventSystem;
use xen\mvc\Controller;

class IndexController extends Controller
{
    public function init()
    {

//        $event = new Event('Bootstrap_Load', array('msg' => 'event from Bootstrap'));
//        $eventSystem = $this->_bootstrap->getResource('EventSystem');
//        $eventSystem->raiseEvent($event);

    }

    public function indexAction()
    {
        if(isset($_SESSION['user'])){
            $this->_redirect('users', 'index');
        }
        $this->_layout->title = 'Application index';
        $this->_layout->description = 'Home Page';
        $this->render();
    }
} 