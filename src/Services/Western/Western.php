<?php

namespace Pondol\Fortune\Services\Western;

use Pondol\Fortune\Services\Western\Biorhythm\Biorhythm;
use Pondol\Fortune\Services\Western\Biorhythm\BiorhythmResult;
use Pondol\Fortune\Services\Western\Numerology\Numerology;
use Pondol\Fortune\Services\Western\Numerology\NumerologyResult;
use Pondol\Fortune\Services\Western\Tarot\Tarot;
use Pondol\Fortune\Services\Western\Zodiac\Zodiac;
use Pondol\Fortune\Services\Western\Zodiac\ZodiacSign;

class Western
{
    private static ?Zodiac $zodiacInstance = null;

    private static ?Tarot $tarotInstance = null;

    private static ?Numerology $numerologyInstance = null;

    private static ?Biorhythm $biorhythmInstance = null;

    /**
     * [1] 황도 12궁 점성술
     * Western::zodiac('1990-05-20') -> ZodiacSign 객체 반환
     */
    public static function zodiac(?string $ymd = null): Zodiac|ZodiacSign
    {
        self::$zodiacInstance ??= new Zodiac;

        return $ymd !== null ? self::$zodiacInstance->find($ymd) : self::$zodiacInstance;
    }

    /**
     * [2] 78장 타로 덱 & 스프레드 엔진
     * Western::tarot()->drawThree() -> [past, present, future] 카드 반환
     */
    public static function tarot(): Tarot
    {
        return self::$tarotInstance ??= new Tarot;
    }

    /**
     * [3] 피타고라스 수비학 (생명수/개인년도수)
     * Western::numerology('1990-05-20') -> NumerologyResult 반환
     */
    public static function numerology(?string $birthYmd = null, ?string $englishName = null): Numerology|NumerologyResult
    {
        self::$numerologyInstance ??= new Numerology;

        return $birthYmd !== null ? self::$numerologyInstance->analyze($birthYmd, $englishName) : self::$numerologyInstance;
    }

    /**
     * [4] 바이오리듬 (신체/감정/지성)
     * Western::biorhythm('1990-05-20') -> BiorhythmResult 반환
     */
    public static function biorhythm(string $birthYmd, ?string $targetYmd = null): BiorhythmResult
    {
        self::$biorhythmInstance ??= new Biorhythm;

        return self::$biorhythmInstance->calculate($birthYmd, $targetYmd);
    }
}
