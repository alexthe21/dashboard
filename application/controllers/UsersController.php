<?php

/**
 * Created by PhpStorm.
 * User: ismael trascastro
 * Date: 21/12/13
 * Time: 22:44
 */

namespace controllers;

use xen\mvc\Controller;

class UsersController extends Controller {

    public function init() {
        
    }

    public function indexAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->data = $this->_model->getPlans();
            $this->_layout->title = 'Dashboard';
            $this->_layout->description = 'Dashboard for managing business goals';

            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'index';
            $this->_redirect('login', 'index');
        }
    }

    public function addAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Add a new user';
            $this->_layout->description = 'Insert a new user';

            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'add';
            $this->_redirect('login', 'index');
        }
    }

    public function updateAction() {
        if (isset($_SESSION['user'])) {
            $user = $this->_model->getUserById($this->_params['id']);
            $this->_layout->title = 'Update an user';
            $this->_layout->description = 'Change user';
            $this->_layout->user = $user;
            
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'update';
            $this->_redirect('login', 'index');
        }
    }

    public function updateDoAction() {
        $this->_model->update($_GET['id'], $_GET['email'], $_GET['password']);
        return $this->_redirect('users', 'list');
    }

    public function addPlanAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Nuevo plan';
            $this->_layout->description = 'Cree un nuevo plan de control';
            $dates = $this->_model->generateDates();
            $this->_layout->dates = $dates;
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'addPlan';
            $this->_redirect('login', 'index');
        }
    }

    public function addPlanDoAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Nuevo plan';
            $this->_layout->description = 'Cree un nuevo plan de control';
            $this->_model->addPlan($_GET['id_plan'], $_GET['nombre_plan'], 
                    $_GET['fecha_inicio'], $_GET['fecha_final'], 
                    $_GET['objetivo']);
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'addPlan';
            $this->_redirect('login', 'index');
        }
    }

    public function getBirdsEyeViewAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Vista general';
            $this->_layout->description = 'Vista general de clientes';
            $this->_layout->dates = $this->_model->generateDates();
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getBirdsEyeView';
            $this->_redirect('login', 'index');
        }
    }

    public function getBirdsEyeViewDoAction() {
        if (isset($_SESSION['user'])) {
            $dates = $this->_model->validateDates($_GET['fecha_inicial'], 
                    $_GET['fecha_final']);
            $clientes = $this->_model->getBirdsEyeView($dates);
            $this->_layout->title = 'Vista general';
            $this->_layout->description = 'Vista general de clientes';
            $this->_layout->dates = $dates;
            $this->_layout->clientes = $clientes;
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getPlatedIncomingsDo/?fecha_inicial=' 
                    . $_GET['fecha_inicial']. '&fecha_final=' . 
                    $_GET['fecha_final'];
            $this->_redirect('login', 'index');
        }
    }

    public function getPlatedIncomingsAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Entradas matriculadas';
            $this->_layout->description = 'Comparación entre entradas matriculadas '
                    . 'y no matriculadas';
            $dates = $this->_model->generateDates();
            $this->_layout->dates = $dates;
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getPlatedIncomings';
            $this->_redirect('login', 'index');
        }
    }

    public function getPlatedIncomingsDoAction() {
        if (isset($_SESSION['user'])) {
            $dates = $this->_model->validateDates($_GET['fecha_inicial'], 
                    $_GET['fecha_final']);
            $periodoSMA = intval($_GET['periodo_sma']);
            $data = $this->_model->getPlatedIncomings($dates, $periodoSMA);
            $this->_layout->title = 'Entradas matriculadas';
            $this->_layout->description = 'Comparación entre entradas matriculadas '
                    . 'y no matriculadas';
            $this->_layout->data = $data;
            $this->_layout->dates = $dates;
            $this->_layout->periodoSMA = $_GET['periodo_sma'];
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getPlatedIncomingsDo/?fecha_inicial=' 
                    . $_GET['fecha_inicial']. '&fecha_final=' . 
                    $_GET['fecha_final'] . '&periodo_sma=' . $_GET['periodo_sma'];
            $this->_redirect('login', 'index');
        }
    }

    public function getExpirationControlAction() {
        if (isset($_SESSION['user'])) {
            $this->_layout->title = 'Control de vencimientos';
            $this->_layout->description = 'Control de los vencimientos incorrectos';
            $this->_layout->dates = $this->_model->generateDates();
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getExpirationControl';
            $this->_redirect('login', 'index');
        }
    }

    public function getExpirationControlDoAction() {
        if (isset($_SESSION['user'])) {
            $dates = $this->_model->validateDates($_GET['fecha_inicial'], 
                    $_GET['fecha_final']);
            $daysFilter = $_GET['filtro_dias'];
            $data = $this->_model->getExpirationControl($dates, $daysFilter);
            $this->_layout->title = 'Control de vencimientos';
            $this->_layout->description = 'Control de los vencimientos incorrectos';
            $this->_layout->data = $data;
            $this->_layout->dates = $dates;
            return $this->render();
        } else {
            $_SESSION['controller'] = 'users';
            $_SESSION['action'] = 'getExpirationControlDo/?fecha_inicial=' 
                    . $_GET['fecha_inicial']. '&fecha_final=' . 
                    $_GET['fecha_final'] . '&filtro_dias=' . $_GET['filtro_dias'];
            $this->_redirect('login', 'index');
        }
    }

}
