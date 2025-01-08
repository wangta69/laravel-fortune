# SAJU API
> 사주에 사용되는 다양한 정보를 API화 하여 처리하였습니다. 

## Installation
```
composer require wangta69/laravel-fortune
```


## 만세력  
### Sample
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/manse/{ymdhi}/{sl?}/{leap?}
```
### 사용법

```
use Pondol\Fortune\Facades\Manse;
..........
$manse = Manse::ymdhi($ymdhi)->sl($sl)->gender($gender)->leap($leap)->create();
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
### Sample
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/saj/{ymdhi}/{sl?}/{leap?}
```
### 사용법
> 위의 만세력을 이용하여 각각의 함수를 호출하면  결과 값들을 리턴한다.
```
use Pondol\Fortune\Facades\Manse;
..........
$saju = Manse::ymdhi($ymdhi)->create()
  ->oheng()
  ->sinsal12()
  ->woonsung12()
  ->zizangan()
  ->sinsal()
  ->daewoon()
  ->saewoon();
```
> 각각에 대해서 보고 싶을때는 아래처럼 처리하면 됩니다.
### 오행
```
Manse::ymdhi($ymdhi)->create()
  ->oheng();
```
### 십신
```
..........

```
### 지장간
```
..........
->zizangan();
```
### 길신/흉신
```
..........
->sinsal();
```
### 12신살
```
..........
->sinsal12();
```
### 12운성
```
..........
->woonsung12();
```
### 대운 / 세운
```
..........
->daewoon(); // 대운
->saewoon(); // 세운
```