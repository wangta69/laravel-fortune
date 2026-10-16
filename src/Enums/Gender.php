<?php

namespace Pondol\Fortune\Enums;

enum Gender: string
{
    case MALE = 'M';
    case FEMALE = 'F';

    /**
     * 한글 표기 ('남' / '여')
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => '남',
            self::FEMALE => '여',
        };
    }

    /**
     * 여성 여부 (기존 'W' 호환 지원)
     */
    public function isFemale(): bool
    {
        return $this === self::FEMALE;
    }

    /**
     * 남성 여부
     */
    public function isMale(): bool
    {
        return $this === self::MALE;
    }

    /**
     * 어떤 형태의 값(문자열, 레거시 W/F, 한글 등)이 들어와도 안전하게 Enum 인스턴스로 변환
     */
    public static function fromMixed(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        $v = strtoupper(trim((string) $value));

        if (in_array($v, ['F', 'W', 'FEMALE', 'WOMAN', '여', '여성'])) {
            return self::FEMALE;
        }

        return self::MALE;
    }
}
