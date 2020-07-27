<?php


namespace App;


class House
{
    private $ownershipStatus;

    public function __construct(string $ownershipStatus)
    {
        $this->ownershipStatus = $ownershipStatus;
    }

    public function getOwnershipStatus() : string
    {
        return $this->ownershipStatus;
    }

    public static function validationRules() : array
    {
        return [
            'house' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_array($value) || !isset($value['ownership_status'])) {
                        $fail($attribute . ' must be either an object containing the ownership_status.');
                        return;
                    }

                    if (!in_array($value['ownership_status'], ['owned', 'mortgaged']) ) {
                        $fail($attribute . ' ownership_status must be either owned or mortgaged.');
                    }
                }
            ]
        ];
    }
}
