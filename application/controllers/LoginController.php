<?php
/**
 * Created by PhpStorm.
 * User: ismael trascastro
 * Date: 21/12/13
 * Time: 19:01
 */

namespace controllers;

use xen\mvc\Controller;

class LoginController extends Controller
{
    public function init()
    {
    }

    public function indexAction()
    {
        $this->_layout->title           = 'Login Form';
        $this->_layout->description     = 'Introduce your credentials';

        $this->render();
    }

    public function logInDoAction()
    {
        $user = $this->_model->login($_REQUEST['usuario'], $_REQUEST['password']);

        if ($user != null) {
            $_SESSION['user'] = $user;
            if(isset($_SESSION['controller']) && isset($_SESSION['action'])){
            $this->_redirect($_SESSION['controller'], $_SESSION['action']);
            } else {
                $this->_redirect('index', 'index');
            }
        } else {
            $this->_redirect('login', 'index');
        }
    }
    
    public function logOutAction()
    {
        unset($_SESSION['user']);
        session_destroy();
        $this->_redirect('index', 'index');
    }
} 