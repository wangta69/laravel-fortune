# SAJU API
> 사주에 사용되는 다양한 정보를 API화 하여 처리하였습니다.

## Installation
```
composer require wangta69/laravel-fortune

```


## 만세력  
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/manse/{ymdhi}/{sl?}/{leap?}
```

## 사주 
- ymdhi : 생년월일 일시 (yyyymmdd)  //202010100350
- sl : solar | lunar (default : solar)
- leap : 윤 여부 (default : false)
```
YourDomain/fortune/saj/{ymdhi}/{sl?}/{leap?}
```