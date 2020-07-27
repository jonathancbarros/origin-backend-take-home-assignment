<?php

namespace App;

class Vehicle
{
    private $year;

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public static function validationRules(): array
    {
        return [
            'vehicle' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_array($value) || !isset($value['year'])) {
                        $fail($attribute . ' must be an object containing the year.');
                        return;
                    }

                    if (!is_int($value['year']) || $value['year'] < 0) {
                        $fail($attribute . ' year must be a positive integer.');
                    }
                },
            ]
        ];
    }
}
