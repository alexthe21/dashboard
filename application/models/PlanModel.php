<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlanModel
 *
 * @author Alejandro Jurado
 */
class PlanModel {

    private $_id;
    private $_name;
    private $_description;
    private $_strDate;
    private $_periods;

    public function __construct($id, $name, $strDate, $description = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_strDate = $strDate;
        $this->_description = $description;
        $this->_periods = array();
    }

    public function getId() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getStrDate() {
        return $this->_strDate;
    }

    public function getPeriods() {
        return $this->_periods;
    }

    public function setId($_id) {
        $this->_id = $_id;
    }

    public function setName($_name) {
        $this->_name = $_name;
    }

    public function setDescription($_description) {
        $this->_description = $_description;
    }

    public function setStrDate($_strDate) {
        $this->_strDate = $_strDate;
    }

    public function addPeriod($_periods) {
        array_push($this->_periods, $_periods);
    }
}
