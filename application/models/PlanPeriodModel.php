<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlanPeriodModel
 *
 * @author Alejandro Jurado
 */
class PlanPeriodModel {
    private $_id;
    private $_idPeriod;
    private $_strDate;
    private $_finDate;
    private $_goal;
    
    public function __construct($id, $idPeriod, $strDate, $finDate, $goal)
    {
        $this->_id = $id;
        $this->_idPeriod = $idPeriod;
        $this->_strDate = $strDate;
        $this->_finDate = $finDate;
        $this->_goal = $goal;
    }
    
    public function getId() {
        return $this->_id;
    }

    public function getIdPeriod() {
        return $this->_idPeriod;
    }

    public function getStrDate() {
        return $this->_strDate;
    }

    public function getFinDate() {
        return $this->_finDate;
    }

    public function getGoal() {
        return $this->_goal;
    }

    public function setId($_id) {
        $this->_id = $_id;
    }

    public function setIdPeriod($_idPeriod) {
        $this->_idPeriod = $_idPeriod;
    }

    public function setStrDate($_strDate) {
        $this->_strDate = $_strDate;
    }

    public function setFinDate($_finDate) {
        $this->_finDate = $_finDate;
    }

    public function setGoal($_goal) {
        $this->_goal = $_goal;
    }
}
