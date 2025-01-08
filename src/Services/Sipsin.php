<?php
namespace Pondol\Fortune\Services;

class Sipsin
{

  private static $sipsin = [
    'h' => [
      '甲'=> ['甲'=>'비견', '乙'=>'겁재', '丙'=>'식신', '丁'=>'상관', '戊'=>'편재', '己'=>'정재', '庚'=>'편관', '辛'=>'정관', '壬'=>'편인', '癸'=>'정인'],
      '乙'=> ['乙'=>'비견', '甲'=>'겁재', '丁'=>'식신', '丙'=>'상관', '己'=>'편재', '戊'=>'정재', '辛'=>'편관', '庚'=>'정관', '癸'=>'편인', '壬'=>'정인'],
      '丙'=> ['丙'=>'비견', '丁'=>'겁재', '戊'=>'식신', '己'=>'상관', '庚'=>'편재', '辛'=>'정재', '壬'=>'편관', '癸'=>'정관', '甲'=>'편인', '乙'=>'정인'],
      '丁'=> ['丁'=>'비견', '丙'=>'겁재', '己'=>'식신', '戊'=>'상관', '辛'=>'편재', '庚'=>'정재', '癸'=>'편관', '壬'=>'정관', '乙'=>'편인', '甲'=>'정인'],
      '戊'=> ['戊'=>'비견', '己'=>'겁재', '庚'=>'식신', '辛'=>'상관', '壬'=>'편재', '癸'=>'정재', '甲'=>'편관', '乙'=>'정관', '丙'=>'편인', '丁'=>'정인'],
      '己'=> ['己'=>'비견', '戊'=>'겁재', '辛'=>'식신', '庚'=>'상관', '癸'=>'편재', '壬'=>'정재', '乙'=>'편관', '甲'=>'정관', '丁'=>'편인', '丙'=>'정인'],
      '庚'=> ['庚'=>'비견', '辛'=>'겁재', '壬'=>'식신', '癸'=>'상관', '甲'=>'편재', '乙'=>'정재', '丙'=>'편관', '丁'=>'정관', '戊'=>'편인', '己'=>'정인'],
      '辛'=> ['辛'=>'비견', '庚'=>'겁재', '癸'=>'식신', '壬'=>'상관', '乙'=>'편재', '甲'=>'정재', '丁'=>'편관', '丙'=>'정관', '己'=>'편인', '戊'=>'정인'],
      '壬'=> ['壬'=>'비견', '癸'=>'겁재', '甲'=>'식신', '乙'=>'상관', '丙'=>'편재', '丁'=>'정재', '戊'=>'편관', '己'=>'정관', '庚'=>'편인', '辛'=>'정인'],
      '癸'=> ['癸'=>'비견', '壬'=>'겁재', '乙'=>'식신', '甲'=>'상관', '丁'=>'편재', '丙'=>'정재', '己'=>'편관', '戊'=>'정관', '辛'=>'편인', '庚'=>'정인']
    ], 
    'e' => [
      '甲'=> ['寅'=>'비견', '卯'=>'겁재', '巳'=>'식신', '午'=>'상관', '辰'=>'편재', '戌'=>'편재', '丑'=>'정재', '未'=>'정재', '申'=>'편관', '酉'=>'정관', '亥'=>'편인', '子'=>'정인'],
      '乙'=> ['卯'=>'비견', '寅'=>'겁재', '午'=>'식신', '巳'=>'상관', '丑'=>'편재', '未'=>'편재', '辰'=>'정재', '戌'=>'정재', '酉'=>'편관', '申'=>'정관', '子'=>'편인', '亥'=>'정인'],
      '丙'=> ['巳'=>'비견', '午'=>'겁재', '辰'=>'식신', '戌'=>'식신', '丑'=>'상관', '未'=>'상관', '申'=>'편재', '酉'=>'정재', '亥'=>'편관', '子'=>'정관', '寅'=>'편인', '卯'=>'정인'],
      '丁'=> ['午'=>'비견', '巳'=>'겁재', '丑'=>'식신', '未'=>'식신', '辰'=>'상관', '戌'=>'상관', '酉'=>'편재', '申'=>'정재', '子'=>'편관', '亥'=>'정관', '卯'=>'편인', '寅'=>'정인'],
      '戊'=> ['辰'=>'비견', '戌'=>'비견', '丑'=>'겁재', '未'=>'겁재', '申'=>'식신', '酉'=>'상관', '亥'=>'편재', '子'=>'정재', '寅'=>'편관', '卯'=>'정관', '巳'=>'편인', '午'=>'정인'],
      '己'=> ['丑'=>'비견', '未'=>'비견', '辰'=>'겁재', '戌'=>'겁재', '酉'=>'식신', '申'=>'상관', '子'=>'편재', '亥'=>'정재', '卯'=>'편관', '寅'=>'정관', '午'=>'편인', '巳'=>'정인'],
      '庚'=> ['申'=>'비견', '酉'=>'겁재', '亥'=>'식신', '子'=>'상관', '寅'=>'편재', '卯'=>'정재', '巳'=>'편관', '午'=>'정관', '辰'=>'편인', '戌'=>'편인', '丑'=>'정인', '未'=>'정인'],
      '辛'=> ['酉'=>'비견', '申'=>'겁재', '子'=>'식신', '亥'=>'상관', '卯'=>'편재', '寅'=>'정재', '午'=>'편관', '巳'=>'정관', '丑'=>'편인', '未'=>'편인', '辰'=>'정인', '戌'=>'정인'],
      '壬'=> ['亥'=>'비견', '子'=>'겁재', '寅'=>'식신', '卯'=>'상관', '巳'=>'편재', '午'=>'정재', '辰'=>'편관', '戌'=>'편관', '丑'=>'정관', '未'=>'정관', '申'=>'편인', '酉'=>'정인'],
      '癸'=> ['子'=>'비견', '亥'=>'겁재', '卯'=>'식신', '寅'=>'상관', '午'=>'편재', '巳'=>'정재', '丑'=>'편관', '未'=>'편관', '辰'=>'정관', '戌'=>'정관', '酉'=>'편인', '申'=>'정인']
    ]
  ];


    /**
    *@param string $day_h : 출생일의 일간
    *@param string $he : 천간 혹은 지지
    *@param string $flag : h: 천간  e: 지지
    */
    static function cal($day_h, $he, $flag) {
      return self::$sipsin[$flag][$day_h][$he];
    }
   /*
    static function sipsin2($day_h, $he, $flag) {
        $sipsin = '';
        switch($flag) {
            case 'h':
            switch ($day_h) {
                case '甲':
                    switch($he) {
                        case '甲'=>'비견'; break; // 比肩
                        case '乙': $sipsin = '겁재'; break; // 劫財
                        case '丙': $sipsin = '식신'; break; // 食神
                        case '丁': $sipsin = '상관'; break; // 傷官
                        case '戊': $sipsin = '편재'; break; // 偏財
                        case '己': $sipsin = '정재'; break; // 正財
                        case '庚': $sipsin = '편관'; break; // 偏官
                        case '辛': $sipsin = '정관'; break; // 正官
                        case '壬': $sipsin = '편인'; break; // 偏印
                        case '癸': $sipsin = '정인'; break; // 正印
                    }
                    break;
                case '乙':
                    switch($he) {
                        case '乙': $sipsin = '비견'; break;
                        case '甲': $sipsin = '겁재'; break;
                        case '丁': $sipsin = '식신'; break;
                        case '丙': $sipsin = '상관'; break;
                        case '己': $sipsin = '편재'; break;
                        case '戊': $sipsin = '정재'; break;
                        case '辛': $sipsin = '편관'; break;
                        case '庚': $sipsin = '정관'; break;
                        case '癸': $sipsin = '편인'; break;
                        case '壬': $sipsin = '정인'; break;
                    }
                    break;
                case '丙':
                    switch($he) {
                        case '丙': $sipsin = '비견'; break;
                        case '丁': $sipsin = '겁재'; break;
                        case '戊': $sipsin = '식신'; break;
                        case '己': $sipsin = '상관'; break;
                        case '庚': $sipsin = '편재'; break;
                        case '辛': $sipsin = '정재'; break;
                        case '壬': $sipsin = '편관'; break;
                        case '癸': $sipsin = '정관'; break;
                        case '甲': $sipsin = '편인'; break;
                        case '乙': $sipsin = '정인'; break;
                    }
                    break;
                case '丁':
                    switch($he) {
                        case '丁': $sipsin = '비견'; break;
                        case '丙': $sipsin = '겁재'; break;
                        case '己': $sipsin = '식신'; break;
                        case '戊': $sipsin = '상관'; break;
                        case '辛': $sipsin = '편재'; break;
                        case '庚': $sipsin = '정재'; break;
                        case '癸': $sipsin = '편관'; break;
                        case '壬': $sipsin = '정관'; break;
                        case '乙': $sipsin = '편인'; break;
                        case '甲': $sipsin = '정인'; break;
                    }
                    break;
                case '戊':
                    switch($he) {
                        case '戊': $sipsin = '비견'; break;
                        case '己': $sipsin = '겁재'; break;
                        case '庚': $sipsin = '식신'; break;
                        case '辛': $sipsin = '상관'; break;
                        case '壬': $sipsin = '편재'; break;
                        case '癸': $sipsin = '정재'; break;
                        case '甲': $sipsin = '편관'; break;
                        case '乙': $sipsin = '정관'; break;
                        case '丙': $sipsin = '편인'; break;
                        case '丁': $sipsin = '정인'; break;
                    }
                    break;
                case '己':
                    switch($he) {
                        case '己': $sipsin = '비견'; break;
                        case '戊': $sipsin = '겁재'; break;
                        case '辛': $sipsin = '식신'; break;
                        case '庚': $sipsin = '상관'; break;
                        case '癸': $sipsin = '편재'; break;
                        case '壬': $sipsin = '정재'; break;
                        case '乙': $sipsin = '편관'; break;
                        case '甲': $sipsin = '정관'; break;
                        case '丁': $sipsin = '편인'; break;
                        case '丙': $sipsin = '정인'; break;
                    }
                    break;
                case '庚':
                    switch($he) {
                        case '庚': $sipsin = '비견'; break;
                        case '辛': $sipsin = '겁재'; break;
                        case '壬': $sipsin = '식신'; break;
                        case '癸': $sipsin = '상관'; break;
                        case '甲': $sipsin = '편재'; break;
                        case '乙': $sipsin = '정재'; break;
                        case '丙': $sipsin = '편관'; break;
                        case '丁': $sipsin = '정관'; break;
                        case '戊': $sipsin = '편인'; break;
                        case '己': $sipsin = '정인'; break;
                    }
                    break;
                case '辛':
                    switch($he) {
                        case '辛': $sipsin = '비견'; break;
                        case '庚': $sipsin = '겁재'; break;
                        case '癸': $sipsin = '식신'; break;
                        case '壬': $sipsin = '상관'; break;
                        case '乙': $sipsin = '편재'; break;
                        case '甲': $sipsin = '정재'; break;
                        case '丁': $sipsin = '편관'; break;
                        case '丙': $sipsin = '정관'; break;
                        case '己': $sipsin = '편인'; break;
                        case '戊': $sipsin = '정인'; break;
                    }
                    break;
                case '壬':
                    switch($he) {
                        case '壬': $sipsin = '비견'; break;
                        case '癸': $sipsin = '겁재'; break;
                        case '甲': $sipsin = '식신'; break;
                        case '乙': $sipsin = '상관'; break;
                        case '丙': $sipsin = '편재'; break;
                        case '丁': $sipsin = '정재'; break;
                        case '戊': $sipsin = '편관'; break;
                        case '己': $sipsin = '정관'; break;
                        case '庚': $sipsin = '편인'; break;
                        case '辛': $sipsin = '정인'; break;
                    }
                    break;
                case '癸':
                    switch($he) {
                        case '癸': $sipsin = '비견'; break;
                        case '壬': $sipsin = '겁재'; break;
                        case '乙': $sipsin = '식신'; break;
                        case '甲': $sipsin = '상관'; break;
                        case '丁': $sipsin = '편재'; break;
                        case '丙': $sipsin = '정재'; break;
                        case '己': $sipsin = '편관'; break;
                        case '戊': $sipsin = '정관'; break;
                        case '辛': $sipsin = '편인'; break;
                        case '庚': $sipsin = '정인'; break;
                    }
                    break;
                }
                break;
            case 'e':
            switch ($day_h) {
                case '甲':
                    switch($he) {
                        case '寅': $sipsin = '비견'; break;
                        case '卯': $sipsin = '겁재'; break;
                        case '巳': $sipsin = '식신'; break;
                        case '午': $sipsin = '상관'; break;
                        case '辰': $sipsin = '편재'; break;
                        case '戌': $sipsin = '편재'; break;
                        case '丑': $sipsin = '정재'; break;
                        case '未': $sipsin = '정재'; break;
                        case '申': $sipsin = '편관'; break;
                        case '酉': $sipsin = '정관'; break;
                        case '亥': $sipsin = '편인'; break;
                        case '子': $sipsin = '정인'; break;
                    }
                    break;
                case '乙':
                    switch($he) {
                        case '卯': $sipsin = '비견'; break;
                        case '寅': $sipsin = '겁재'; break;
                        case '午': $sipsin = '식신'; break;
                        case '巳': $sipsin = '상관'; break;
                        case '丑': $sipsin = '편재'; break;
                        case '未': $sipsin = '편재'; break;
                        case '辰': $sipsin = '정재'; break;
                        case '戌': $sipsin = '정재'; break;
                        case '酉': $sipsin = '편관'; break;
                        case '申': $sipsin = '정관'; break;
                        case '子': $sipsin = '편인'; break;
                        case '亥': $sipsin = '정인'; break;
                    }
                    break;
                case '丙':
                    switch($he) {
                        case '巳': $sipsin = '비견'; break;
                        case '午': $sipsin = '겁재'; break;
                        case '辰': $sipsin = '식신'; break;
                        case '戌': $sipsin = '식신'; break;
                        case '丑': $sipsin = '상관'; break;
                        case '未': $sipsin = '상관'; break;
                        case '申': $sipsin = '편재'; break;
                        case '酉': $sipsin = '정재'; break;
                        case '亥': $sipsin = '편관'; break;
                        case '子': $sipsin = '정관'; break;
                        case '寅': $sipsin = '편인'; break;
                        case '卯': $sipsin = '정인'; break;
                    }
                    break;
                case '丁':
                    switch($he) {
                        case '午': $sipsin = '비견'; break;
                        case '巳': $sipsin = '겁재'; break;
                        case '丑': $sipsin = '식신'; break;
                        case '未': $sipsin = '식신'; break;
                        case '辰': $sipsin = '상관'; break;
                        case '戌': $sipsin = '상관'; break;
                        case '酉': $sipsin = '편재'; break;
                        case '申': $sipsin = '정재'; break;
                        case '子': $sipsin = '편관'; break;
                        case '亥': $sipsin = '정관'; break;
                        case '卯': $sipsin = '편인'; break;
                        case '寅': $sipsin = '정인'; break;
                    }
                    break;
                case '戊':
                    switch($he) {
                        case '辰': $sipsin = '비견'; break;
                        case '戌': $sipsin = '비견'; break;
                        case '丑': $sipsin = '겁재'; break;
                        case '未': $sipsin = '겁재'; break;
                        case '申': $sipsin = '식신'; break;
                        case '酉': $sipsin = '상관'; break;
                        case '亥': $sipsin = '편재'; break;
                        case '子': $sipsin = '정재'; break;
                        case '寅': $sipsin = '편관'; break;
                        case '卯': $sipsin = '정관'; break;
                        case '巳': $sipsin = '편인'; break;
                        case '午': $sipsin = '정인'; break;
                    }
                    break;
                case '己':
                    switch($he) {
                        case '丑': $sipsin = '비견'; break;
                        case '未': $sipsin = '비견'; break;
                        case '辰': $sipsin = '겁재'; break;
                        case '戌': $sipsin = '겁재'; break;
                        case '酉': $sipsin = '식신'; break;
                        case '申': $sipsin = '상관'; break;
                        case '子': $sipsin = '편재'; break;
                        case '亥': $sipsin = '정재'; break;
                        case '卯': $sipsin = '편관'; break;
                        case '寅': $sipsin = '정관'; break;
                        case '午': $sipsin = '편인'; break;
                        case '巳': $sipsin = '정인'; break;
                    }
                    break;
                case '庚':
                    switch($he) {
                        case '申': $sipsin = '비견'; break;
                        case '酉': $sipsin = '겁재'; break;
                        case '亥': $sipsin = '식신'; break;
                        case '子': $sipsin = '상관'; break;
                        case '寅': $sipsin = '편재'; break;
                        case '卯': $sipsin = '정재'; break;
                        case '巳': $sipsin = '편관'; break;
                        case '午': $sipsin = '정관'; break;
                        case '辰': $sipsin = '편인'; break;
                        case '戌': $sipsin = '편인'; break;
                        case '丑': $sipsin = '정인'; break;
                        case '未': $sipsin = '정인'; break;
                    }
                    break;
                case '辛':
                    switch($he) {
                        case '酉': $sipsin = '비견'; break;
                        case '申': $sipsin = '겁재'; break;
                        case '子': $sipsin = '식신'; break;
                        case '亥': $sipsin = '상관'; break;
                        case '卯': $sipsin = '편재'; break;
                        case '寅': $sipsin = '정재'; break;
                        case '午': $sipsin = '편관'; break;
                        case '巳': $sipsin = '정관'; break;
                        case '丑': $sipsin = '편인'; break;
                        case '未': $sipsin = '편인'; break;
                        case '辰': $sipsin = '정인'; break;
                        case '戌': $sipsin = '정인'; break;
                    }
                    break;
                case '壬':
                    switch($he) {
                        case '亥': $sipsin = '비견'; break;
                        case '子': $sipsin = '겁재'; break;
                        case '寅': $sipsin = '식신'; break;
                        case '卯': $sipsin = '상관'; break;
                        case '巳': $sipsin = '편재'; break;
                        case '午': $sipsin = '정재'; break;
                        case '辰': $sipsin = '편관'; break;
                        case '戌': $sipsin = '편관'; break;
                        case '丑': $sipsin = '정관'; break;
                        case '未': $sipsin = '정관'; break;
                        case '申': $sipsin = '편인'; break;
                        case '酉': $sipsin = '정인'; break;
                    }
                    break;
                case '癸':
                    switch($he) {
                        case '子': $sipsin = '비견'; break;
                        case '亥': $sipsin = '겁재'; break;
                        case '卯': $sipsin = '식신'; break;
                        case '寅': $sipsin = '상관'; break;
                        case '午': $sipsin = '편재'; break;
                        case '巳': $sipsin = '정재'; break;
                        case '丑': $sipsin = '편관'; break;
                        case '未': $sipsin = '편관'; break;
                        case '辰': $sipsin = '정관'; break;
                        case '戌': $sipsin = '정관'; break;
                        case '酉': $sipsin = '편인'; break;
                        case '申': $sipsin = '정인'; break;
                    }
                    break;
                }
                break;
        }

        return $sipsin;

    } // static function sipsin($hour_h, $day_h, $month_h, $year_h, $hour_e, $day_e, $month_e, $year_e) {
*/
     /*
    static function sipsin($hour_h, $day_h, $month_h, $year_h, $hour_e, $day_e, $month_e, $year_e) {
        return  (object)[
            'sin_hour_h'=>self::sipsin2($day_h, $hour_h, 'h'),
            'sin_month_h'=>self::sipsin2($day_h, $month_h, 'h'),
            'sin_year_h'=>self::sipsin2($day_h, $year_h, 'h'),
            'sin_hour_e'=>self::sipsin2($day_h, $hour_e, 'e'),
            'sin_day_e'=>self::sipsin2($day_h, $day_e, 'e'),
            'sin_month_e'=>self::sipsin2($day_h, $month_e, 'e'),
            'sin_year_e'=>self::sipsin2($day_h, $year_e, 'e')
        ];
    } // static function sipsin($hour_h, $day_h, $month_h, $year_h, $hour_e, $day_e, $month_e, $year_e) {


    static function sipsin3($hour_h, $day_h, $month_h, $year_h, $hour_e, $day_e, $month_e, $year_e) {
        return  (object)[
            'hour_h'=>self::sipsin2($day_h, $hour_h, 'h'),
            'day_h'=> '일원',
            'month_h'=>self::sipsin2($day_h, $month_h, 'h'),
            'year_h'=>self::sipsin2($day_h, $year_h, 'h'),
            'hour_e'=>self::sipsin2($day_h, $hour_e, 'e'),
            'day_e'=>self::sipsin2($day_h, $day_e, 'e'),
            'month_e'=>self::sipsin2($day_h, $month_e, 'e'),
            'year_e'=>self::sipsin2($day_h, $year_e, 'e')
        ];
    } 
    /**
    * 10신을 받아와서 컬러 코드로 매핑 한다.
    */
    /*
    static function color($sipsin) {
        switch($sipsin) {
            case '화':
                return 'red';
            case '수':
                return 'black';
            case '목':
                return 'blue';
            case '금':
                return 'white';
            case '토':
                return 'yellow';

        }
    }
    */
}
