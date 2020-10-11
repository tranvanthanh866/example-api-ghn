<?php
/**
 * Created by PhpStorm.
 * User: Kai-Tran
 * Date: 10/11/2020
 * Time: 11:02 AM
 */

if ($_SERVER['REQUEST_METHOD'] == 'GET' && empty($_GET['DistrictId']) == false) {
    require_once "Shipping.php";
    $ward = $shipping->getWardByDistrictId($_GET['DistrictId']);
    echo json_encode($ward);
}
