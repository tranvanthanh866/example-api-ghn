<?php
/**
 * Created by PhpStorm.
 * User: Kai-Tran
 * Date: 10/11/2020
 * Time: 11:01 AM
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET'
    && empty($_GET['ToDistrictID']) == false
    && empty($_GET['ProvinceId']) == false
    && empty($_GET['WardCode']) == false
) {
    require_once "Shipping.php";
    $services = $shipping->getService(['ToDistrictID' => $_GET['ToDistrictID']]);
    $params = [
        'serviceID' => $services['data'][0]['service_id'],
        'serviceTypeID' => $services['data'][0]['service_type_id'],
        'ToDistrictID' => $_GET['ToDistrictID']
    ];
    $fee = $shipping->getFeeShipping($params);
    $shipping = [
        'province_id' => $_GET['ProvinceId'],
        'district_id' => $_GET['ToDistrictID'],
        'WardCode' => $_GET['WardCode'],
        'feeShipping' => $fee['data']['total']
    ];
    if (isset($_SESSION["shipping"])) {
        unset($_SESSION["shipping"]);
    }
    $_SESSION["shipping"] = $shipping;

    echo json_encode($fee);
    exit();
}