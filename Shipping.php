<?php
/**
 * Created by PhpStorm.
 * User: Kai-Tran
 * Date: 10/10/2020
 * Time: 6:30 PM
 */
class Shipping
{
    protected $Token;

    protected $headers;

    public $shopInfo;

    public $provinces;

    public $districts;

    function __construct()
    {
        $this->Token = '8438aadf-0b69-11eb-84a9-aef8461f938e';
        $this->headers = [
            'Token: '.$this->Token,
            'Content-Type: application/json',
        ];
        $this->provinces = $this->getProvinces();
        $this->districts = $this->getDistricts();
        $this->shopInfo = $this->getShopInfo();
    }

    /**
     * @return mixed
     */
    private function getShopInfo () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shop/all');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result, true);
        return $result['data'];
    }

    /**
     * @return mixed
     */
    private function getProvinces () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @return mixed
     */
    private function getDistricts () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @param $ProvinceId
     * @return array|int
     */
    public function getDistrictsByProvinceId($ProvinceId) {
        $districts = $this->districts;
        return array_filter(
            $districts['data'],
            function ($district, $key) use ($ProvinceId, &$data) {
                return $district['ProvinceID'] == $ProvinceId;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param $DistrictId
     * @return mixed
     */
    public function getWardByDistrictId($DistrictId) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id='.$DistrictId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @param $DistrictId
     * @param $WardCode
     * @return mixed
     */
    public function getStation ($DistrictId, $WardCode) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/station/get?district_id='.$DistrictId.'&ward_code='.$WardCode.'&offset=0&limit=1000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @param $params array
     * @return mixed
     */
    public function getService($params) {
        $postData = array (
            "ShopID" => 75576,
            "FromDistrictID" => 1444,
            "ToDistrictID" => $params['ToDistrictID'],
            "offset" => 0
        );
        $headers = [];
        $headers[] = 'Token: '.$this->Token;
        $headers[] = "Content-Type: multipart/form-data";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getFeeShipping ($params) {
        $postData = array (
            "serviceID" => $params['serviceID'],
            "serviceTypeID" => $params['serviceTypeID'],
            "ToDistrictID" => $params['ToDistrictID'],
            "height" => 15,
            "width" => 15,
            "length" => 900,
            "weight" => 15,
            "insurance_value" => 5000000,
            "coupon" => null
        );
        $headers = [];
        $headers[] = 'ShopId: 75576';
        $headers[] = 'Token: '.$this->Token;
        $headers[] = "Content-Type: multipart/form-data";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result, true);
    }
}
$shipping = new Shipping();
