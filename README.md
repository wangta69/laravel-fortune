# SAJU API
> 사주에 사용되는 다양한 정보를 API화 하여 처리하였습니다. 

## Installation
```
composer require wangta69/laravel-fortune
```


## 만세력  

### Api
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/saju/{ymdhi}/{sl?}/{leap?}
```

### Facades
#### 기본적인 사주 정보
```
use Pondol\Fortune\Facades\Saju;
..........
$saju = Saju::ymdhi($ymdhi)->sl($sl)->gender($gender)->leap($leap)->create();
``` 
- $ymdhi : 198010101330 (생년월일시를 숫자로)
- $sl : solar (양력) | lunar(음력)  default: solar
- $gender : M(남성) | W(여성) default : M
- $leap : 윤달여부로 음력일경우 true | false default : false
> 아래와 같이 양력/음력 날짜및 60갑자의 생년월일시 를 출력한다.
```
{"sl":"","solar":"","lunar":"","leap":,"ymd":"","hi":"","year":{"ch":"壬子","ko":"임자"},"month":{"ch":"辛亥","ko":"신해"},"day":{"ch":"丁未","ko":"정미"},"hour":{"ch":"己酉","ko":"기유"},"gender":"M","korean_age":54}
```
## 사주 
### Api
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/saj/{ymdhi}/{sl?}/{leap?}
```

### Facades
> 초기 기본적인 saju를 구한후 필요한 데이타를 계속해서 받아 오면 됩니다.
```
use Pondol\Fortune\Facades\Saju;
..........
$saju = Saju::ymdhi($ymdhi)->create()
$saju = Saju::ymdhi(date('YmdHi'))->sl('solar')->create(); // 오늘 날짜 기준으로 가져올 경우
```
```
Pondol\Fortune\Services\Saju Object
(
    [sl] => solar
    [solar] => 2025-09-01
    [lunar] => 2025-07-10

    [leap] => 
    [ymd] => 2025-09-01
    [hi] => 0907
    [year] => stdClass Object
        (
            [ko] => 을사
            [ch] => 乙巳
        )

    [month] => stdClass Object
        (
            [ko] => 갑신
            [ch] => 甲申
        )

    [day] => stdClass Object
        (
            [ko] => 계유
            [ch] => 癸酉
        )

    [hour] => stdClass Object
        (
            [ko] => 병진
            [ch] => 丙辰
        )

    [gender] => M
    [korean_age] => 1
)
```
> 각각에 대해서 보고 싶을때는 아래처럼 처리하면 됩니다.
### 천간
```
$saju->get_h('year'); // year, month, day
```

### 지지
```
$saju->get_e('year'); // year, month, day
```
### 천간과 지지
```
$saju->get_he('year'); // year, month, day
```
#### 오행
```
$saju->oheng();
```
 [oheng] => Pondol\Fortune\Services\Oheng Object
(
  [year_h] => stdClass Object
    (
      [ch] => 水
      [ko] => 수
      [en] => wed
      [flag] => +
    )
    ..........
)
```
```
#### 십신
```
$saju->sipsin(); // 오행과 십신을 동시에 가져오려면 $saju->oheng()->sipsin();
..........

```
#### 지장간
```
$saju->zizangan();
..........
->zizangan();
```
#### 길신/흉신
```
..........
->sinsal();
```
#### 12신살
```
..........
->sinsal12();
```
#### 12운성
```
..........
->woonsung12();
```
#### 대운 / 세운
```
..........
->daewoon(); // 대운
->saewoon(); // 세운
```

## 토정비결 작괘
### Facades
```
use Pondol\Fortune\Facades\Saju;
..........
Saju::ymdhi($ymdhi)->sl($sl)->leap($leap)->create()->jakque(); // default 당해년
Saju::ymdhi($ymdhi)->sl($sl)->leap($leap)->create()->>jakque(function($jakque){
  $jakque->set_year('2025');
}); // 특정년을 넣을 경우
```
> 결과
```
"jakque":{"que":[8,6,3],"total":"863"}
```
## 카렌다
### 음력달력
#### API
```
YourDomain/fortune/calendar/lunar/202502
```
#### Facades
```
use Pondol\Fortune\Facades\Calendar;
..........
$days = Calendar::lunarCalendar($yyyymm);
```

### 24절기달력
#### API
```
YourDomain/fortune/calendar/season-24/2025
```
#### Facades
```
use Pondol\Fortune\Facades\Calendar;
..........
$days = Calendar::season24Calendar($yyyy);
```

### 삼재
> 특정해의 삼재를 가져옮
#### API
```
YourDomain/fortune/calendar/samjae/2025
```
#### Facades
```
use Pondol\Fortune\Facades\Calendar;
..........
$samjae = Calendar::samjae($yyyy);
```


## 자미두수
```
$saju = Saju::ymdhi($profile->birth_ym)->sl($profile->sl)->leap($profile->flat_moon)->create();
$today = Saju::ymdhi(date('YmdHi'))->sl('solar')->create();

$myungban = $this->jamidusuSvc->myungbanData($saju, $today);

// 자미두수 기본 데이터
$jamidusu = $this->jamidusuSvc->getdefaultData($saju->gender, $saju->lunar, $saju->get_h('year'), $saju->get_e('hour'));

// 명궁
$myung = $this->jamidusuSvc->jusungMyung($jamidusu);

// 형제궁
$hyungjae = $this->jamidusuSvc->jusungHyungjae($jamidusu);

// 부부궁
$bubu = $this->jamidusuSvc->jusungBubu($jamidusu);

// 자녀궁
$janyeo = $this->jamidusuSvc->jusungJanyeo($jamidusu);

// 재백궁
$jaebaek = $this->jamidusuSvc->jusungJaebaek($jamidusu);

// 질액궁
$jilaek = $this->jamidusuSvc->jusungJilaek($jamidusu);

// 천이궁
$chene = $this->jamidusuSvc->jusungChene($jamidusu);


// 노복궁
$nobok = $this->jamidusuSvc->jusungNobok($jamidusu);

// 관록궁
$guanrok = $this->jamidusuSvc->jusungGuanrok($jamidusu);

// 전택궁
$jeuntaek = $this->jamidusuSvc->jusungJeuntaek($jamidusu);

// 복덕궁
$bokduk = $this->jamidusuSvc->jusungBokduk($jamidusu);

// 부모궁
$bumo = $this->jamidusuSvc->jusungBumo($jamidusu);
```