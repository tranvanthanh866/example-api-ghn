<?php
/**
 * Created by PhpStorm.
 * User: Kai-Tran
 * Date: 10/11/2020
 * Time: 11:01 AM
 */

if ($_SERVER['REQUEST_METHOD'] == 'GET' && empty($_GET['ProvinceId']) == false) {
    require_once "Shipping.php";
    $provinces = $shipping->getDistrictsByProvinceId($_GET['ProvinceId']);
    echo json_encode($provinces);
}