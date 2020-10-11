<?php
session_start();
require_once("dbcontroller.php");
require_once("Shipping.php");
$provinces = $shipping->provinces;
if(empty($_SESSION["shipping"]) == false) {
    $shipping_ss = $_SESSION["shipping"];
    $districts = $shipping->getDistrictsByProvinceId($shipping_ss['province_id']);
    $wards = $shipping->getWardByDistrictId($shipping_ss['district_id']);
}

$db_handle = new DBController();
if (!empty($_GET["action"])) {
    switch ($_GET["action"]) {
        case "add":
            if (!empty($_POST["quantity"])) {
                $productByCode = $db_handle->runQuery("SELECT * FROM tblproduct WHERE code='" . $_GET["code"] . "'");
                $itemArray = array($productByCode[0]["code"] => array('name' => $productByCode[0]["name"], 'code' => $productByCode[0]["code"], 'quantity' => $_POST["quantity"], 'price' => $productByCode[0]["price"], 'image' => $productByCode[0]["image"]));

                if (!empty($_SESSION["cart_item"])) {
                    if (in_array($productByCode[0]["code"], array_keys($_SESSION["cart_item"]))) {
                        foreach ($_SESSION["cart_item"] as $k => $v) {
                            if ($productByCode[0]["code"] == $k) {
                                if (empty($_SESSION["cart_item"][$k]["quantity"])) {
                                    $_SESSION["cart_item"][$k]["quantity"] = 0;
                                }
                                $_SESSION["cart_item"][$k]["quantity"] += $_POST["quantity"];
                            }
                        }
                    } else {
                        $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"], $itemArray);
                    }
                } else {
                    $_SESSION["cart_item"] = $itemArray;
                }
            }
            break;
        case "remove":
            if (!empty($_SESSION["cart_item"])) {
                foreach ($_SESSION["cart_item"] as $k => $v) {
                    if ($_GET["code"] == $k)
                        unset($_SESSION["cart_item"][$k]);
                    if (empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                }
            }
            break;
        case "empty":
            unset($_SESSION["cart_item"]);
            break;
    }
}
?>
<!doctype html>
<html lang="en">
<!doctype html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Simple PHP Shopping Cart</title>
    <link href="style.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div id="shopping-cart">
    <div class="txt-heading">Shopping Cart</div>

    <a id="btnEmpty" href="index.php?action=empty">Empty Cart</a>
    <?php
    if (isset($_SESSION["cart_item"])) {
        $total_quantity = 0;
        $total_price = 0;
        ?>
        <table class="tbl-cart" cellpadding="10" cellspacing="1">
            <tbody>
            <tr>
                <th style="text-align:left;">Name</th>
                <th style="text-align:left;">Code</th>
                <th style="text-align:right;" width="5%">Quantity</th>
                <th style="text-align:right;" width="10%">Unit Price</th>
                <th style="text-align:right;" width="10%">Price</th>
                <th style="text-align:center;" width="5%">Remove</th>
            </tr>
            <?php
            foreach ($_SESSION["cart_item"] as $item) {
                $item_price = $item["quantity"] * $item["price"];
                ?>
                <tr>
                    <td><img src="<?php echo $item["image"]; ?>" class="cart-item-image"/><?php echo $item["name"]; ?>
                    </td>
                    <td><?php echo $item["code"]; ?></td>
                    <td style="text-align:right;"><?php echo $item["quantity"]; ?></td>
                    <td style="text-align:right;"><?php echo number_format($item["price"])." VND"; ?></td>
                    <td style="text-align:right;"><?php echo number_format($item_price)." VND"; ?></td>
                    <td style="text-align:center;"><a href="index.php?action=remove&code=<?php echo $item["code"]; ?>"
                                                      class="btnRemoveAction"><img src="icon-delete.png"
                                                                                   alt="Remove Item"/></a></td>
                </tr>
                <?php
                $total_quantity += $item["quantity"];
                $total_price += ($item["price"] * $item["quantity"]);
            }
            ?>

            <tr>
                <td align="">Shipping</td>
                <td align="">
                    <label for="provinces">Province: </label>
                    <select name="provinces" id="provinces">
                        <option value="">Please select province</option>
                        <?php
                            foreach ($provinces['data'] as $key => $province) {
                                $selected = '';
                                if (!empty($shipping_ss['province_id']) && ($shipping_ss['province_id'] == $province['ProvinceID']) ){
                                    $selected = 'selected';
                                }
                                echo '<option value="'.$province['ProvinceID'].'" '.$selected.'>'.$province['ProvinceName'].'</option>';
                            }
                        ?>
                    </select>
                    <label for="district">District: </label>
                    <select name="district" id="district">
                        <option value="">Please select</option>
                        <?php
                        if (empty($districts) == false) {
                            foreach ($districts as $key => $district) {
                                $selected = '';
                                if (!empty($shipping_ss['district_id']) && ($shipping_ss['district_id'] == $district['DistrictID']) ){
                                    $selected = 'selected';
                                }
                                echo '<option value="'.$district['DistrictID'].'" '.$selected.'>'.$district['DistrictName'].'</option>';
                            }
                        }
                        ?>
                    </select>
                    <label for="ward">Ward: </label>
                    <select name="ward" id="ward">
                        <option value="">Please select</option>
                        <?php
                        if (empty($wards['data']) == false) {
                            foreach ($wards['data'] as $key => $ward) {
                                $selected = '';
                                if (!empty($shipping_ss['WardCode']) && ($shipping_ss['WardCode'] == $ward['WardCode']) ){
                                    $selected = 'selected';
                                }
                                echo '<option value="'.$ward['WardCode'].'" '.$selected.'>'.$ward['WardName'].'</option>';
                            }
                        }
                        ?>
                    </select>

                </td>
                <td align="right"></td>
                <td align="right" colspan="2">
                    <?php
                        if (empty($_SESSION["shipping"]) == false) {
                            $shipping = $_SESSION["shipping"];
                            echo number_format($shipping['feeShipping'])." VND";
                            $total_price += $shipping['feeShipping'];
                        }
                    ?>
                </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="2" align="right">Total:</td>
                <td align="right"><?php echo $total_quantity; ?></td>
                <td align="right" colspan="2"><strong><?php echo number_format($total_price)." VND"; ?></strong></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <?php
    } else {
        ?>
        <div class="no-records">Your Cart is Empty</div>
        <?php
    }
    ?>
</div>

<div id="product-grid">
    <div class="txt-heading">Products</div>
    <?php
    $product_array = $db_handle->runQuery("SELECT * FROM tblproduct ORDER BY id ASC");
    if (!empty($product_array)) {
        foreach ($product_array as $key => $value) {
            ?>
            <div class="product-item">
                <form method="post" action="index.php?action=add&code=<?php echo $product_array[$key]["code"]; ?>">
                    <div class="product-image"><img src="<?php echo $product_array[$key]["image"]; ?>"></div>
                    <div class="product-tile-footer">
                        <div class="product-title"><?php echo $product_array[$key]["name"]; ?></div>
                        <div class="product-price"><?php echo number_format($product_array[$key]["price"])." VND"; ?></div>
                        <div class="cart-action"><input type="text" class="product-quantity" name="quantity" value="1"
                                                        size="2"/><input type="submit" value="Add to Cart"
                                                                         class="btnAddAction"/></div>
                    </div>
                </form>
            </div>
            <?php
        }
    }
    ?>
</div>
<script>
    window.onload = function () {
        var provinces = document.getElementById("provinces"),
            district = document.getElementById("district"),
            ward = document.getElementById("ward");
        /*for (var country in stateObject) {
            countySel.options[countySel.options.length] = new Option(country, country);
        }*/
        provinces.onchange = function () {
            district.length = 1; // remove all options bar first
            ward.length = 1; // remove all options bar first
            if (this.selectedIndex < 1) return; // done
            let request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    if(request.status === 200) {
                        let districts = JSON.parse(request.response);
                        for (var key in districts) {
                            district.options[district.options.length] = new Option(districts[key].DistrictName, districts[key].DistrictID );
                        }
                    } else {

                    }
                }
            };
            let ProvinceId = provinces.value;
            request.open('GET', 'ajax-get-district.php?ProvinceId='+ProvinceId);
            request.send();
        };
        district.onchange = function () {
            ward.length = 1; // remove all options bar first
            if (this.selectedIndex < 1) return; // done
            let getWard = new XMLHttpRequest();
            getWard.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    if(getWard.status === 200) {
                        let wards = JSON.parse(getWard.response).data;
                        for (var key_ward in wards) {
                            ward.options[ward.options.length] = new Option(wards[key_ward].WardName, wards[key_ward].WardCode );
                        }
                    } else {

                    }
                }
            };
            let DistrictId = district.value;
            getWard.open('GET', 'ajax-get-ward.php?DistrictId='+DistrictId);
            getWard.send();
        };
        ward.onchange = function () {
            if (ward.value != '') {
                let getFee = new XMLHttpRequest();
                getFee.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        if(getFee.status === 200) {
                            location.reload();
                        } else {

                        }
                    }
                };
                let ProvinceId = provinces.value;
                let ToDistrictID = district.value;
                let WardCode = ward.value;
                getFee.open('GET', 'ajax-get-fee.php?ToDistrictID='+ToDistrictID+'&ProvinceId='+ProvinceId+'&WardCode='+WardCode);
                getFee.send();
            }
        }
    }
</script>
</body>
</html>