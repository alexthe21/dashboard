<?php

/**
 * Created by PhpStorm.
 * User: ismael trascastro
 * Date: 21/12/13
 * Time: 22:43
 */

namespace models;

use xen\db\Adapter;
use xen\mvc\Model;

/**
 * Class UsersModel
 *
 * @package models
 * @author  Ismael Trascastro itrascastro@xenframework.com
 *
 * @var Adapter $_db Database connection
 *
 */
class UsersModel extends Model {

    private $_mssqlDb;

    public function __construct()
    {
    }

    public function add($name, $password)
    {
        $password = hash('sha256', $password);
        $sql = "INSERT INTO users (name, password) VALUES (:name, :password)";
        $query = $this->_db->prepare($sql);
        $query->bindParam(':name', $name);
        $query->bindParam(':password', $password);
        $query->execute();
    }

    public function all()
    {
        $sql = "SELECT * FROM users";
        $query = $this->_db->prepare($sql);
        $query->execute();
        $users = array();
        while ($row = $query->fetch(Adapter::FETCH_OBJ)) {
            $users[] = new UserModel($row->id, $row->name, $row->password);
        }
        return $users;
    }

    public function remove($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $query = $this->_db->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $query = $this->_db->prepare($sql);
        $query->bindParam(':id', $id);
        $query->execute();
        $row = $query->fetch(Adapter::FETCH_ASSOC);
        $user = new UserModel($row['id'], $row['name'], $row['password']);
        return $user;
    }

    public function login($name, $password)
    {
        $password = hash('sha256', $password);
        $sql = "SELECT * FROM users WHERE name = :name AND password = :password";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        if ($row = $stmt->fetch(Adapter::FETCH_ASSOC)) {
            $stmt->closeCursor();
            return new UserModel($row['id'], $row['name'], $row['password']);
        }
        $stmt->close();
        return null;
    }

    public function update($id, $name, $password)
    {
        $sql = "UPDATE users SET name = :name, password = :password WHERE id = :id";
        $query = $this->_db->prepare($sql);
        $query->bindParam(':id', $id);
        $query->bindParam(':name', $name);
        $query->bindParam(':password', $password);
        $query->execute();
    }
    
    public function getPlans()
    {
        $today = new \DateTime('now');
        $strToday = $today->format('Y-m-d');
        $sql = "SELECT id, name, description"
                . " FROM plan"
                . " WHERE str_date < :date";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':date', $strToday);
        $stmt->execute();
        $data = array();
        $plan = array();
        while ($plan = $stmt->fetch(Adapter::FETCH_ASSOC)) {
            //Recorremos los planes
            $planData['name'] = $plan['name'];
            $planData['description'] = $plan['description'];
            $sql = "SELECT *"
                    . " FROM plan_period"
                    . " WHERE id = :id";
            $stmt1 = $this->_db->prepare($sql);
            $stmt1->bindParam(':id', $plan['id']);
            $stmt1->execute();
            $planData['lines'] = array();
            while ($period = $stmt1->fetch(Adapter::FETCH_ASSOC)) {
                // Recorremos cada periodo del plan actual
                $periodData = array();
                $day = new \DateTime($period['str_date']);
                $periodFinDate = new \DateTime($period['fin_date']);
                do {
                    /*
                     * Iteramos entre los días comprendidos en cada plan. Si
                     * es día laborable realizamos los cálculos oportunos.
                     */
                    if ($this->esDiaLaborable($day)) {
                        $dayData = array();
                        $strDia = $day->format('Y-m-d');

                        // Número de albaranes con matricula.
                        $query = "SELECT COUNT(Matricula)"
                                . " FROM alm_Entradas"
                                . " WHERE Fecha = '$strDia'"
                                . " AND RTRIM(Matricula) != ''";
                        $stmt2 = mssql_query($query);
                        $plated = mssql_fetch_assoc($stmt2);

                        // Número de albaranes totales.
                        $query = "SELECT COUNT(ID_Albaran)"
                                . " FROM alm_Entradas"
                                . " WHERE Fecha = '$strDia'";
                        $stmt3 = mssql_query($query);
                        $total = mssql_fetch_assoc($stmt3);
                        $dayData['plated'] = $plated['computed'];
                        $dayData['total'] = $total['computed'];
                        // Si hay entradas de almacén realizamos los cálculos
                        if ($dayData['total'] > 0) {
                            $dayData['rating'] = $dayData['plated'] /
                                    $dayData['total'] * 100;
                            $dayData['date'] = $day->format('Y-m-d');
                            array_push($periodData, $dayData);
                        }
                    }
                    $day->add(new \DateInterval('P1D'));
                } while ($day <= $periodFinDate);
                $line = array();
                $line['rating'] = 0;
                for ($i = 0; $i < sizeof($periodData); $i++) {
                    $line['rating'] += $periodData[$i]['rating'];
                }
                $line['rating'] /= sizeof($periodData);
                $line['rating'] = round($line['rating'], 2);
                $line['str_date'] = $period['str_date'];
                $line['fin_date'] = $period['fin_date'];
                $line['goal'] = $period['goal'];
                array_push($planData['lines'], $line);
            }
            array_push($data, $planData);
        }
        return $data;
    }

    private function getTotalBilling($dates)
    {
        $strFechaInicial = $dates[0]->format('Y-m-d');
        $strFechaFinal = $dates[1]->format('Y-m-d');
        $query = "SELECT SUM(Importe)"
                . " FROM fac_Facturas_vencimientos"
                . " WHERE Fecha BETWEEN '$strFechaInicial'"
                . " AND '$strFechaFinal'";
        $stmt = mssql_query($query);
        $totalBilling = mssql_fetch_assoc($stmt);
        return $totalBilling;
    }

    private function getClientById($id)
    {
        $query = "SELECT ID_Cliente as ID, Nombre"
                . " FROM gen_Clientes"
                . " WHERE ID_Cliente = $id";
        $stmt = mssql_query($query);
        $client = mssql_fetch_assoc($stmt);
        return $client;
    }

    public function getBirdsEyeView($dates)
    {
        $strFechaInicial = $dates[0]->format('Y-m-d');
        $strFechaFinal = $dates[1]->format('Y-m-d');
        $totalBilling = $this->getTotalBilling($dates);
        //Seleccionamos los clientes por ID ascendente.
        $query = 'SELECT ID_Cliente as ID, Nombre'
                . ' FROM gen_Clientes'
                . ' ORDER BY ID_Cliente ASC';
        $stmt = mssql_query($query);
        $listaClientes = array();
        while ($cliente = mssql_fetch_assoc($stmt)) {
            //Calculamos el riesgo para el cliente actual del cursor.
            $idCliente = $cliente['ID'];
            $fechaActual = new \DateTime('now');
            $strFechaActual = $fechaActual->format('Y-m-d');
            $query = "SELECT c.ID_Cliente, fv.ID_Numero, fv.Importe, fv.Fecha"
                    . " FROM fac_Facturas_vencimientos fv,"
                    . " fac_Facturas f, gen_Clientes c"
                    . " WHERE fv.ID_Numero = f.ID_Numero"
                    . " AND f.Codigo_cliente = c.ID_Cliente"
                    . " AND fv.Fecha > '$strFechaActual'"
                    . " AND c.ID_Cliente = $idCliente"
                    . " ORDER BY fv.ID_Numero DESC";

            $stmt2 = mssql_query($query);
            $cliente['Riesgo'] = 0;
            while ($infoVencimiento = mssql_fetch_array($stmt2)) {
                $cliente['Riesgo'] += $infoVencimiento['Importe'];
            }

            /*
             * Calculamos el total facturado por el cliente en el periodo 
             * comprendido por las fechas facilitadas.
             */

            $strFechaInicial = $dates[0]->format('Y-m-d');
            $strFechaFinal = $dates[1]->format('Y-m-d');
            $idCliente = $cliente['ID'];
            $query = "SELECT c.ID_Cliente, fv.ID_Numero, fv.Importe, fv.Fecha"
                    . " FROM fac_Facturas_vencimientos fv,"
                    . " fac_Facturas f, gen_Clientes c"
                    . " WHERE fv.ID_Numero = f.ID_Numero"
                    . " AND f.Codigo_cliente = c.ID_Cliente"
                    . " AND fv.Fecha >= '$strFechaInicial'"
                    . " AND fv.Fecha <= '$strFechaFinal'"
                    . " AND c.ID_Cliente = '$idCliente'"
                    . " ORDER BY fv.ID_Numero DESC";
            $stmt3 = mssql_query($query);
            $cliente['Facturado'] = 0;
            while ($infoVencimiento = mssql_fetch_array($stmt3)) {
                $cliente['Facturado'] += $infoVencimiento['Importe'];
            }

            /*
             * Calculamos la proporción de la facturación del cliente respecto 
             * el total del periodo.   
             */
            $cuotaFac = $cliente['Facturado'] / $totalBilling['computed'] * 100;
            $cliente['Proporcion'] = round($cuotaFac, 2) . '%';
            array_push($listaClientes, $cliente);
        }
        return $listaClientes;
    }

    public function getPlatedIncomings($dates, $periodoSMA)
    {
        /*
         * Restamos 5 días a la fecha inicial para poder calcular la
         * Media móvil simple (SMA). 
         */
        $dates[0]->sub(new \DateInterval('P' . $periodoSMA . 'D'));

        // $dia se usará para hacer una consulta por día.
        $dia = new \DateTime($dates[0]->format('Y-m-d'));

        $data = array();
        $headers[0] = 'Fecha';
        $headers[1] = 'Matriculadas';
        $headers[2] = 'Totales';
        array_push($data, $headers);
        do {
            //Si el día es laborable obtenemos datos.
            if ($this->esDiaLaborable($dia)) {
                // Fecha de un determinado día en formato (día/mes).
                $datosDia[0] = $dia->format('d/m');

                // Número de albaranes totales.
                $strDia = $dia->format('Y-m-d');
                $query = "SELECT COUNT(ID_Albaran)"
                        . " FROM alm_Entradas"
                        . " WHERE Fecha = '$strDia'";
                $stmt1 = mssql_query($query);
                $numAlbaranes = mssql_fetch_assoc($stmt1);

                /* Si el número de albaranes no es 0, se hará la proporción,
                 * de lo contrario, se devolverá 0 albaranes, 0 matriculados.
                 */
                if ($numAlbaranes['computed'] != 0) {
                    // Número de albaranes con matricula.
                    $query = "SELECT COUNT(Matricula)"
                            . " FROM alm_Entradas"
                            . " WHERE Fecha = '$strDia'"
                            . " AND RTRIM(Matricula) != ''";
                    $stmt2 = mssql_query($query);
                    $numMatriculas = mssql_fetch_assoc($stmt2);
                    // Número de albaranes matriculados.
                    $datosDia[1] = $numMatriculas['computed'];
                    // Número de albaranes totales.
                    $datosDia[2] = $numAlbaranes['computed'];
                } else {
                    $datosDia[1] = 0;
                    $datosDia[2] = 0;
                }
                array_push($data, $datosDia);
            }
            $dia->add(new \DateInterval('P1D'));
            $diff = $dia->diff($dates[1])->days;
        } while ($diff > 0);

        $smoothedData = $this->smoothData($data, $periodoSMA);
        return $smoothedData;
    }

    private function esDiaLaborable($dia)
    {
        $query = "SELECT dia"
                . " FROM festivos"
                . " WHERE dia = :fecha";
        $stmt = $this->_db->prepare($query);
        $strDia = $dia->format('Y-m-d');
        $stmt->bindParam(':fecha', $strDia);
        $stmt->execute();
        $esDiaFestivo = $stmt->fetch();
        $stmt->closeCursor();
        if ($dia->format('l') != 'Saturday' && $dia->format('l') != 'Sunday' &&
                $esDiaFestivo == false) {
            return true;
        } else {
            return false;
        }
    }

    public function generateDates()
    {
        $dates = array();
        $dates[0] = new \DateTime('now');
        $dates[0]->setTime(0, 0);
        $dates[0]->sub(new \DateInterval('P3M'));
        $dates[1] = new \DateTime('now');
        $dates[1]->setTime(0, 0);
        return $dates;
    }

    public function validateDates($fechaInicial, $fechaFinal)
    {
        $dates = array();
        $fechaFinal = str_replace('/', '', $fechaFinal);
        if ($fechaInicial > $fechaFinal) {
            $dates[0] = new \DateTime($fechaFinal);
            $dates[1] = new \DateTime($fechaInicial);
        } else {
            $dates[0] = new \DateTime($fechaInicial);
            $dates[1] = new \DateTime($fechaFinal);
        }
        return $dates;
    }

    private function smoothData($data, $periodoSMA)
    {
        $smoothedData = array();
        $perSMA = $periodoSMA - 1;
        for ($i = $perSMA; $i < sizeof($data); $i++) {
            if ($i == $perSMA) {
                $smoothedData[$i - $perSMA][0] = $data[0][0];
                $smoothedData[$i - $perSMA][1] = $data[0][1];
                $smoothedData[$i - $perSMA][2] = $data[0][2];
            } else {
                $smoothedData[$i - $perSMA][0] = $data[$i][0];
                $mediaMatr = 0;
                $mediaTot = 0;
                for ($j = $i; $j > $i - $periodoSMA; $j--) {
                    $mediaMatr += $data[$j][1];
                    $mediaTot += $data[$j][2];
                }
                $smoothedData[$i - $perSMA][1] = $mediaMatr / $periodoSMA;
                $smoothedData[$i - $perSMA][2] = $mediaTot / $periodoSMA;
            }
        }
        return $smoothedData;
    }

    public function getExpirationControl($dates, $daysFilter)
    {

        //Selecciona los vencimientos entre las fechas facilitadas.
        $fechaInicial = $dates[0]->format('Y-m-d');
        $fechaFinal = $dates[1]->format('Y-m-d');
        $query = "SELECT ID_Numero"
                . " FROM fac_Facturas_vencimientos"
                . " WHERE Fecha >= '$fechaInicial'"
                . " AND Fecha <= '$fechaFinal'"
                . " ORDER BY ID_Numero DESC";
        $stmt = mssql_query($query);
        $lineasNoCuadran = array();

        while ($idVnmts = mssql_fetch_assoc($stmt)) {
            foreach ($idVnmts as $value) {
                /* Selecciona ID_factura, ID_cliente, Fecha_vencimiento, 
                 * Fecha_facturación Dias_vencimiento de un deternimado 
                 * ID_vencimiento. */
                $query = "SELECT f.ID_Numero, c.ID_Cliente, c.Nombre,"
                        . " fv.Fecha as Fecha_vencim, f.Fecha as Fecha_fact,"
                        . " fp.Dias_vencimiento1 as Dias_vencim,"
                        . " c.Dia_pago1 as Dia_pago"
                        . " FROM fac_Facturas f, gen_Clientes c,"
                        . " fac_Facturas_vencimientos fv,"
                        . " fac_Formas_de_pago fp"
                        . " WHERE f.ID_Numero = $value"
                        . " AND f.ID_Numero = fv.ID_Numero"
                        . " AND f.Codigo_cliente = c.ID_Cliente"
                        . " AND c.Forma_pago = fp.ID_Forma_pago";
                $stmt2 = mssql_query($query);
                $infoVnmts = mssql_fetch_assoc($stmt2);

                /* Calcular diferencia entre Fecha vencimiento y Fecha facturación y
                 * mostrar Fechas y Diferencia en un formato más amable. */
                $fechaVenc = new \DateTime($infoVnmts['Fecha_vencim']);
                $fechaFac = new \DateTime($infoVnmts['Fecha_fact']);
                $diaTeorVenc = $fechaVenc->format('d');
                $diaTeorVenc = floor($diaTeorVenc);

                /* Si ya ha pasado el día de pago del cliente, 
                 * se pasará al mes siguiente en y día de pago del cliente */
                if ($diaTeorVenc > $infoVnmts['Dia_pago'] &&
                        $infoVnmts['Dia_pago'] > 0) {
                    $fechaVenc->add(new \DateInterval('P1M'));
                    $year = $fechaVenc->format('Y');
                    $month = $fechaVenc->format('m');
                    $day = $infoVnmts['Dia_pago'];
                    $fechaVenc->setDate($year, $month, $day);
                    $infoVnmts['Fecha_vencim'] = $fechaVenc;
                }
                $infoVnmts['Fecha_vencim'] = $fechaVenc->format('d/m/Y');
                $infoVnmts['Fecha_fact'] = $fechaFac->format('d/m/Y');

                /*
                 * Calculamos la diferencia entre la fecha de vencimiento
                 * y la fecha de facturación. A partir de la diferencia se 
                 * compara con las condiciones del cliente y se calcula el 
                 * retraso respecto a las condiciones.
                 */
                $diffFechas = $fechaVenc->diff($fechaFac);
                $diffDias = $diffFechas->format('%a');
                $diffDias = floor($diffDias);
                $diffDias = $diffDias - $infoVnmts['Dias_vencim'];

                $infoVnmts['Dif_dias'] = $diffFechas->format('%a días');
                $infoVnmts['Dias_retraso'] = $diffDias;

                // Si hay un retraso se añade a la lista de incidencias.
                if ($infoVnmts['Dias_retraso'] >= $daysFilter) {
                    array_push($lineasNoCuadran, $infoVnmts);
                }
            }
        }
        return $lineasNoCuadran;
    }
    
    public function addPlan($idPlan, $nombrePlan, $description,
                $fechaInicio, $fechaFinal, $objetivo)
    {
        $sql = "INSERT INTO plan"
                    . " SET id = :id, name = :name, description = :description,"
                    . " starting_date = :str_date, final_date = :final_date";
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':id', $idPlan);
            $stmt->bindParam(':name', $nombrePlan);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':starting_date', $fechaInicio);
            $stmt->bindParam(':final_date', $fechaFinal);
            $stmt->bindParam(':goal', $objetivo);
            $stmt->execute();
    }
}