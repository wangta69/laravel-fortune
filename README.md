This library is used in the production of [gilra.kr](https://www.gilra.kr) (Online Fortune Service).

# SAJU

> 사주에 사용되는 다양한 정보를 쉽게 사용할 수 있도록 프로그램 하였습니다.

## Installation

```
composer require wangta69/laravel-fortune
```

## 세팅

> 라라벨 7.x 이하이면 아래와 같이 config/app.php 에 등록 하여야 합니다.

```
'providers' => [
    // ...
    Pondol\Fortune\FortuneServiceProvider::class,
];
'aliases' => [
    // ...
    'Saju' => Pondol\Fortune\Facades\Saju::class,
    'Lunar' => Pondol\Fortune\Facades\Lunar::class,
    'Calendar' => Pondol\Fortune\Facades\Calendar::class,
    'JamiDusu' => Pondol\Fortune\Facades\JamiDusu::class,
];
```

## 만세력

### Facades 를 활용한 프로그램

#### 기본적인 사주 정보

```
use Pondol\Fortune\Facades\Saju;
..........
$saju = Saju::ymdhi($ymdhi)->sl($sl)->gender($gender)->leap($leap)->create();
```

- $ymdhi : 198010101330 (생년월일시를 숫자로)
- $sl : solar (양력) | lunar(음력) default: solar
- $gender : M(남성) | W(여성) default : M
- $leap : 윤달여부로 음력일경우 true | false default : false
  > 아래와 같이 양력/음력 날짜및 60갑자의 생년월일시 를 출력한다.

```
{"sl":"","solar":"","lunar":"","leap":,"ymd":"","hi":"","year":{"ch":"壬子","ko":"임자"},"month":{"ch":"辛亥","ko":"신해"},"day":{"ch":"丁未","ko":"정미"},"hour":{"ch":"己酉","ko":"기유"},"gender":"M","korean_age":54}
```

### Api 를 활용한 프로그램

- ymdhi : 생년월일 일시 (yyyymmdd) //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)

```
YourDomain/fortune/saju/{ymdhi}/{sl?}/{leap?}
```

## 사주

### Api

- ymdhi : 생년월일 일시 (yyyymmdd) //202010100350 (태어난 시를 모를 경우 시는 99 로 처리)
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
$saju = Saju::ymdhi($ymdhi)->create();
$saju = Saju::ymdhi($ymdhi)->sl('lunar')->leap(true)->create();

$today = Saju::ymdhi(date('YmdHi'))->create();
$today = Saju::ymdhi(date('YmdHi'))->sl('solar')->create(); // 오늘 날짜 기준으로 사주를 가져올경우
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

### 천간 구하기

```
$saju->get_h('year'); // year, month, day, 한자로 리턴(甲...)
$saju->get_h_serial($str); // 甲: 1
```

### 지지 구하기

```
$saju->get_e('year'); // year, month, day, 한자로 리턴(子...)
$saju->get_e_serial($str);  // 子:1....
$saju->get_e_wolgun($str);  // 子: 11..
```

### 천간과 지지 구하기

```
$saju->get_he('year'); // year, month, day
```

#### 오행 구하기

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

#### 십신 구하기

```
$saju = Saju::ymdhi(...)->create();
$sipsin = $saju->sipsin(); // withSaju가 자동으로 호출됨

// 1. 원국 십신 접근 (타입 힌팅으로 안전)
$monthSipsin_e = $sipsin->month->e; // '편관'

// 2. 지장간에 숨겨진 십신 보기
$zizanganSipsin = $sipsin->getZizanganSipsin();
$hiddenSipsin = $zizanganSipsin->day->정기; // 일지에 숨겨진 진짜 속마음

// 3. 올해 운(예: 甲辰년)과의 관계 보기
$unSipsin = $sipsin->getUnSipsin('甲', '辰');
// $unSipsin->h 는 '정인', $unSipsin->e 는 '상관'
// "올해는 새로운 공부(정인)를 시작하고 싶은 마음과, 기존의 틀을 깨고 싶은(상관) 마음이 충돌하는 한 해입니다." 와 같은 해석 가능

// 4. 십신 개수 요약 보기
$summary = $sipsin->getSipsinCountSummary(); // ['편재' => 2, '정관' => 2, ...]
..........
```

#### 신살 구하기

```
$saju->sinsal()
```

#### 지장간 구하기

```
$saju->zizangan();
```

#### 길신/흉신 구하기

```
..........
$saju->sinsal();
```

#### 12신살 구하기

```
..........
$saju->sinsal12();
```

#### 12운성 구하기

```
..........
$saju->woonsung12();
```

#### 대운 / 세운 구하기

```
..........
$saju->daewoon(); // 대운
$saju->saewoon(); // 세운
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

## 토정비결 작괘

### Facades

```
use Pondol\Fortune\Facades\Saju;
..........
$tojung = Saju::ymdhi($ymdhi)->sl($sl)->leap($leap)->create();
$jakgwae = $saju->tojeong()->create(date('Y'));
```

> 결과

```
[age] => 54
[taeseSu] => 17
[dalSu] => 30
[wolgeonSu] => 10
[iljinSu] => 18
[gwae] => Array
    (
        [0] => 7
        [1] => 4
        [2] => 1
    )

[totalGwae] => 741
```

작괘를 구한 후 [길라잡이 작괘 풀이](https://www.gilra.kr/fortune-lab/tojeong) 에서 확인 하시면 됩니다.

## 당사주

### Facades

```
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\DangSaju;
..........
$saju = Saju::ymdhi('200001011200')->sl('solar')->leap(false)->create();
$star =  DangSaju::getDangSajuStars($saju->get_e('year'), $saju->get_e('hour'), $saju->lunar);
```

> 결과

```
(
    [year] => 천귀
    [month] => 천인
    [day] => 천파
    [hour] => 천예
)
```

## 자미두수

```
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\JamiDusu;

......
$saju = Saju::ymdhi($profile->birth_ym)->sl($profile->sl)->leap($profile->flat_moon)->create();
$today = Saju::ymdhi(date('YmdHi'))->sl('solar')->create();

$myungban = JamiDusu::myungbanData($saju, $today);

// 자미두수 기본 데이터
$jamidusu = JamiDusu::getdefaultData($saju->gender, $saju->lunar, $saju->get_h('year'), $saju->get_e('hour'));
JamiDusu::jusungMyung($jamidusu); // 명궁
JamiDusu::jusungHyungjae($jamidusu); // 형제궁
JamiDusu::jusungBubu($jamidusu); // 부부궁
JamiDusu::jusungJanyeo($jamidusu); // 자녀궁
JamiDusu::jusungJaebaek($jamidusu); // 재백궁
JamiDusu::jusungJilaek($jamidusu); // 질액궁
JamiDusu::jusungChene($jamidusu); // 천이궁
JamiDusu::jusungNobok($jamidusu); // 노복궁
JamiDusu::jusungGuanrok($jamidusu); // 관록궁
JamiDusu::jusungJeuntaek($jamidusu); // 전택궁
JamiDusu::jusungBokduk($jamidusu); // 복덕궁
JamiDusu::jusungBumo($jamidusu); // 부모궁
```

```
// 각각의 결과로는 아래처럼 출력된다.
stdClass Object
(
    [gung] => 亥
    [jusung14] => 태양
)
```

## 주역

```
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Juyeok;

......
$saju = Saju::ymdhi('200010101000')->sl('lunar')->leap(true)->create();
$today = Saju::ymdhi(date('YmdHi'))->sl('solar')->create();

$que = Juyeok::getInnateGwe($saju, $today); // '선천괘'를 계산합니다. (매화역수)
$que = Juyeok::getTemporalGwe($saju, $today, $type); // 후천괘'를 계산합니다.(hour, day, month, year 등으로 시, 일, 월, 년에 대한 운세를 구한다.) default: day
```

## 육임정단

```
use Pondol\Fortune\Services\YukimService;

class MyController
{
    private YukimService $yukimService;

    public function __construct(YukimService $yukimService)
    {
        $this->yukimService = $yukimService;
    }
}
```

```
$userSaju = Saju::ymdhi('200010101000')->sl('lunar')->leap(true)->create();
$todaySaju = Saju::ymdhi(date('YmdHi'))->sl('solar')->create();

// 1. 정통 720과
$result = $this->yukimService->getReading('720gwa', $todaySaju);

// 2. 지두법
$result = $this->yukimService->getReading('jidu', $todaySaju);

// 3. '본명법'
$result = $this->yukimService->getReading('bonmyeong', $todaySaju, $userSaju);

// 4. 일간과법
$result = $this->yukimService->getReading('ilgangwa', $todaySaju);

// 5. '차객법'
$result = $this->yukimService->getReading('chaekgeok', $todaySaju);
```

## 택일

### 이사택일

```
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Calendar;
..........
$saju = Saju::ymdhi('200010101000')->sl('lunar')->leap(true)->create();
$options = [
    'moving_direction_enabled'=> true or false // 이사방향 처리
    'moving_direction' => '동'|'서'|'남'|'북'

    'family_harmony_enabled'=> true or false // 가족 구성원 생년월일
    'family_years' => ['19901010'....]
];

$moveCalendarData = Calendar::moveCalendar($saju, $yyyymm, $options); // $yyyymm : 년월을 입력하면 년월에 해당하는 모든 일별데이타 출력
```

### 결혼택일

```
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Calendar;
..........
$saju_male = Saju::ymdhi('200010101000')->sl('lunar')->leap(true)->create(); // 남자측 사주
$saju_female = Saju::ymdhi('200010101000')->sl('lunar')->leap(true)->create(); // 여차측 사주
$marriageCalendarData = Calendar::marriageCalendar($saju_male, $saju_female, $yyyymm, []); // $yyyymm : 년월을 입력하면 년월에 해당하는 모든 일별데이타 출력
```
