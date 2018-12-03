<?php
namespace app\api\controller\v2;


use think\Db;
use think\Exception;

class HtmlContent extends Base
{
    public function getCarData()
    {
        set_time_limit(0);
        try {
            $brandData = $this->getBrand();
//            dump($brandData);die;
            if (!$brandData) {
                throw new Exception('品牌列表不存在');
            }
            $series   = $this->getFamily();
            if (!$series) {
                throw new Exception('车系列表不存在');
            }
            $brandIds = array_keys($series);
            foreach ($brandData as $key => $value) {
                Db::startTrans();
                $brandList = [
                    'brandCode'    => $value['id'],
                    'brandName'    => $value['name'],
                    'brandInitial' => $value['bfirstletter'],
                    'imgUrl'       => $value['logo'],
                    'hasFamily'    => in_array($value['id'], $brandIds, true) ? 0 : 1
                ];
                $hash = md5(serialize($brandList));
                $brandList['hash'] = $hash;

                ksort($brandList);
                $where = ['brandCode' => $value['id']];
                $brand = Db::name('car_carbrand')->where($where)->field('brandId as id,brandCode as code,hash')->find();
                if (!$brand) {
                    $result = Db::name('car_carbrand')->insert($brandList);
                    if (!$result) {
                        throw new Exception('添加车辆品牌数据失败');
                    }
                    $brandId = Db::name('car_carbrand')->getLastInsID();
                    $brandCode = $value['id'];
                }else{
                    if($brand['hash'] != $hash){
                        $result = Db::name('car_carbrand')->where($where)->update($brandList);
                        if ($result === false) {
                            throw new Exception('更新车辆品牌数据失败');
                        }
                    }
                    $brandId   = $brand['id'];
                    $brandCode = $value['id'];
                }

                if (array_key_exists($brandCode, $series)) {
                    $brandSeries = $series[$brandCode];
                    foreach ($brandSeries as $k => &$val) {
                        $val['brandId'] = $brandId;
                        if (!Db::name('car_carfamily')->where(['familyId' => $val['familyId'], 'carFamilyName' => $val['carFamilyName'], 'brandId' => $val['brandId']])->count()) {
                            $result = Db::name('car_carfamily')->insert($val);
                            if (!$result) {
                                throw new Exception('插入车系' . $val['cf_name'] . '失败');
                            }
                        }
                    }
                }
                Db::commit();
            }

            $this->apiReturn(200);
        } catch (Exception $e) {
            Db::rollback();
            $this->apiReturn(201, '', $e->getMessage());
        }
    }

    /**
     * 获取品牌列表
     * */
    private function getBrand()
    {
        $brandUrl = 'https://www.autohome.com.cn/ashx/AjaxIndexCarFind.ashx?type=11';
        $brandData = curl_get($brandUrl);
        $brandData = iconv('gbk', 'utf-8', $brandData);
        $brandData = json_decode($brandData, true);
        if (!empty($brandData['result']['branditems'])) {
            return $brandData['result']['branditems'];
        }
        return false;
    }

    /**
     * 获取车系列表
     * */
    private function getFamily()
    {
        $url = 'https://car.autohome.com.cn/javascript/NewSpecCompare.js?20131010';
        $data = file_get_contents($url);
        $data = iconv('gbk', 'utf-8', $data);
        $data = str_replace('var listCompare$100= ', '', $data);
        $data = trim($data);
        $data = trim($data, ';');
        $data = json_decode($data, true);
        $series = [];
        $this->_toFormatTree($data, 0, '', $series);
        ksort($series);
        return $series;
    }

    private function _toFormatTree($list, $level = 0, $B = '', &$data)
    {
        foreach ($list as $key => &$val) {
            if (!array_key_exists('List', $val)) {
                $val['B'] = $B;
                $data[$B][] = [
                    'familyId' => $val['I'],
                    'carFamilyName' => $val['N'],
                    'brandId' => $B,
                ];
            } else {
                $tmpAry = $val['List'];
                unset($val['List']);
                if ($level > 1) {
                    $val['B'] = $B;
                    $data[$B][] = [
                        'familyId' => $val['I'],
                        'carFamilyName' => $val['N'],
                        'cf_brandId' => $B,
                    ];
                } else {
                    $B = $level == 0 ? $val['I'] : $B;
                }

                $this->_toFormatTree($tmpAry, $level + 1, $B, $data); //进行下一层递归
            }
        }
        return;
    }

    private function getData($content)
    {
        preg_match_all("/厂商指导价：(.*)<em>万元<\/em><\/span>/is", $content, $price);
        $data['guidePrice'] = $price && !empty($price[1][0]) ? round($price[1][0] * 10000, 2) : 0;
        preg_match_all("/<span class=\"scaleimg\">(.*)<span class=\"tit\">/is", $content, $url);
        $url = $url && !empty($url[1][0]) ? strchr(strchr($url[1][0], 'src="'), '" srcset', true) : '';
        $data['carImage'] = trim(trim($url, 'src='), '"');
        preg_match_all("/<div class=\"information-tit\">\s(.*)<div class=\"athm-select\" data-toggle=\"carpicker\">/is", $content, $string);
        $string = $string && !empty($string[1][0]) ? $string[1][0] : '';
        $data['carName'] = str_replace(['<h2>', '</h2>'], '', trim($string));
        $yearPattern = explode(' ', $data['carName']);
        $data['yearPattern'] = current($yearPattern);
        unset($content, $yearPattern, $price, $url, $string);
        return $data;
    }

    private function getGuidePrice($content)
    {
        $mode = "/厂商指导价：(.*)<em>万元<\/em><\/span>/is";
        preg_match_all($mode, $content, $matches);
        $price = 0;
        if ($matches) {
            $price = $matches[1][0] * 10000;
        }
        unset($matches, $mode, $content);
        return $price;
    }

    private function getPrice($content)
    {
        $mode = "/id=\"cityDealerPrice\">(.*)<em>万元起<\/em>/is";
        preg_match_all($mode, $content, $matches);
        if ($matches) {
            foreach ($matches as $match) {
                if (!empty($match[0]) && is_numeric($match[0])) {
                    $price = $match[0] * 10000;
                    unset($matches, $match, $content);
                    return $price;
                }
            }
        }
        unset($matches, $match, $content);
        return 0;
    }

    /**
     * 裸车价
     * */
    private function getSpecPrice($content)
    {
        $mode = "/id=\"specPrice\">(.*)元/is";
        preg_match_all($mode, $content, $matches);
        if ($matches) {
            foreach ($matches as $match) {
                if (!empty($match[0]) && is_numeric($match[0])) {
                    $price = $match[0] * 10000;
                    unset($matches, $match, $content);
                    return $price;
                }
            }
        }
        unset($matches, $match, $content);
        return 0;
    }

    private function getCarImage($content)
    {
        $mode = "/<span class=\"scaleimg\">(.*)<span class=\"tit\">/is";
        preg_match_all($mode, $content, $matches);
        $url = '';
        if ($matches) {
            $url = strchr(strchr($matches[1][0], 'src="'), '" srcset', true);
            $url = trim(trim($url, 'src='), '"');
        }
        unset($content, $mode, $matches);
        return $url;
    }

    private function getCarName($content)
    {
        $mode = "/<div class=\"information-tit\">\s(.*)<div class=\"athm-select\" data-toggle=\"carpicker\">/is";
        preg_match_all($mode, $content, $matches);
        $string = '';
        if ($matches) {
            $string = $matches[1][0];
            $string = str_replace(['<h2>', '</h2>'], '', trim($string));
        }
        unset($content, $matches, $mode);
        return $string;
    }

    private function getCarInfo($content)
    {
        $mode = "/<ul class=\"baseinfo-list\">(.*)更多参数/is";
        preg_match_all($mode, $content, $matches);

        if ($matches) {
            preg_match_all('|<[^>]+>(.*)</[^>]+>|U', trim($matches[1][0]), $match);
            $field = ['level', 'style', 'size', 'oilConsumption', ''];
            foreach ($match[1] as &$value) {
                $value = trim(strrchr($value, '>'), '>');
            }
            return $match[1];
        }
        return '';
    }

    /**
     * 获取车系所有车型
     * @param $familyId integer 车系ID
     * @return array
     * */
    private function getFamilyCars($familyId)
    {
        $url = 'https://car.autohome.com.cn/duibi/ashx/specComparehandler.ashx?callback=jsonpCallback&type=1&format=json&seriesid=' . $familyId;
        $cars = curl_get($url);
        $encode = mb_detect_encoding($cars, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        logs_write($encode, request()->controller(), request()->action(), [], 'public/encode/');
        $cars = iconv($encode, 'utf-8//IGNORE', $cars);
        $cars = trim($cars);
        $cars = trim($cars, ');');
        $cars = trim($cars, 'jsonpCallback(');
//        $cars = trim($cars, ')');
        $cars = json_decode($cars, true);
        $carList = [];
        if (!empty($cars['List'])) {
            foreach ($cars['List'] as $val) {
                $carList = array_merge($carList, $val['List']);
            }
            unset($cars);
        }
        return $carList;
    }

    /**
     * 保存车系所有车型
     * @param $carList array 车型列表
     * @param $family  array 车系数据
     * @return boolean;
     * */
    private function saveCars($carList, $family)
    {
        if (!is_array($carList) || !is_array($family)) {
            return false;
        }
        foreach ($carList as $key => $item) {
            $carInfoUrl = 'https://www.autohome.com.cn/spec/' . $item['I'];
            $content = curl_get($carInfoUrl);
            $encode  = mb_detect_encoding($content, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
            $content = iconv($encode, 'utf-8//IGNORE', $content);
            $carInfo = $this->getData($content);
            $carName = $family['brandName'] . ' ' . $family['familyName'] . ' ' . $carInfo['carName'];
            $carName = mb_strpos($carName, '停售') !== false ? str_replace('(停售)', '', $carName) : $carName;
            $carData = [
                'brandId'      => $family['brandId'],
                'indexImage'   => $carInfo['carImage'],
                'carName'      => $carName,
                'brandCode'    => $family['brandCode'],
                'brandName'    => $family['brandName'],
                'brandInitial' => $family['brandInitial'],
                'familyId'     => $family['id'],
                'familyName'   => $family['familyName'],
                'yearPattern'  => $carInfo['yearPattern'],
                'price'        => $item['P'] ?: $carInfo['guidePrice'],
                'bareCarPrice' => $item['P'] ?: $carInfo['guidePrice'],
            ];

            ksort($carData);
            $where = ['carName' => [['like', '%' . $family['familyName'] . ' ' . $carInfo['carName']  . '%'], ['like', '%' . $family['familyName'] . $carInfo['carName']  . '%']], 'brandId' => $family['brandId']];
//            $car = Db::name('car_cars')->where($where)->count();
            $car = Db::query('SELECT carId as id FROM `car_cars` WHERE ( `carName` LIKE "%' .$family['familyName'] . ' ' . $carInfo['carName'] . '%" OR `carName` LIKE "%' . $family['familyName'] . $carInfo['carName'] . '%" ) AND `brandId` = 23 LIMIT 1');
            logs_write($carData, request()->controller(), request()->action(), $car, 'public/car/');
            if (!$car) {
                $result = Db::name('car_cars')->insert($carData);
                logs_write($result, request()->controller(), request()->action(), $carData, 'public/saveCars/');
            }else{
                $result = Db::name('car_cars')->where(['carId' => ['in', array_column($car, 'id')]])->update($carData);
            }
            unset($content, $carInfo, $carData, $car);
        }
        unset($item, $carList);
        return true;
    }

    public function saveFamilyCars(){
        set_time_limit(0);
        $familyField = 'familyId,carFamilyId as id,carFamilyName as familyName,b.brandId,b.brandCode,b.brandName,brandInitial';
        $familyData = Db::name('car_carfamily f')->field($familyField)->where(['f.brandId' => 23])->join('car_carbrand b', 'f.brandId=b.brandId', 'left')->select();
        foreach ($familyData as $key => $family) {
            $carList = $this->getFamilyCars($family['familyId']);
            if (!empty($carList)) {
                $this->saveCars($carList, $family);
            }
        }
        unset($familyData, $family);
    }

    public function saveFamily(){
        set_time_limit(0);
        empty($this->data['brandCode']) && $this->apiReturn(201, '', '请输入品牌代码');
        $brandCode = $this->data['brandCode'] + 0;
        $series = $this->getFamily();
        if (!$series) {
            $this->apiReturn(201, '', '车系列表不存在');
        }

        $brand = Db::name('car_carbrand')->where(['brandCode' => $brandCode])->field('brandId')->find();
        if(!$brand){
            $this->apiReturn(201, '', '请先更新品牌信息');
        }

        $brandSeries = $series[$brandCode];
        if(!$brandSeries){
            $this->apiReturn(201, '', '品牌不存在');
        }

        foreach ($brandSeries as $k => &$val) {
            $val['brandId'] = $brand['brandId'];
            $val['carFamilyName'] = mb_strpos($val['carFamilyName'], '停售') !== false ? str_replace('(停售)', '', $val['carFamilyName']) : $val['carFamilyName'];
            if (!Db::name('car_carfamily')->where(['carFamilyName' => $val['carFamilyName'], 'brandId' => $val['brandId']])->count()) {
                $result = Db::name('car_carfamily')->insert($val);
                if (!$result) {
                    $this->apiReturn(201, '', '插入车系' . $val['carFamilyName'] . '失败');
                }
            } else {
                $result = Db::name('car_carfamily')->where(['carFamilyName' => $val['carFamilyName'], 'brandId' => $val['brandId']])->update($val);
                if ($result === false) {
                    $this->apiReturn(201, '', '更新车系' . $val['carFamilyName'] . '失败');
                }
            }
        }

        $familyField = 'familyId,carFamilyId as id,carFamilyName as familyName,b.brandId,b.brandCode,b.brandName,brandInitial';
        $familyData = Db::name('car_carfamily f')->field($familyField)->where(['f.brandId' => $brand['brandId']])->join('car_carbrand b', 'f.brandId=b.brandId', 'left')->select();
        foreach ($familyData as $key => $family) {
            $carList = $this->getFamilyCars($family['familyId']);
            if (!empty($carList)) {
                $this->saveCars($carList, $family);
            }
        }
        unset($familyData, $family);
    }
}
