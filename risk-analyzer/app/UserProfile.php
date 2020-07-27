<?php


namespace App;


use Illuminate\Validation\Rule;

class UserProfile
{
    private $age;
    private $dependents;
    private $house;
    private $income;
    private $maritalStatus;
    private $riskQuestions;
    private $vehicle;

    public function __construct(int $age, int $dependents, int $income, string $maritalStatus, array $riskQuestions, ?Vehicle $vehicle, ?House $house)
    {
        $this->age = $age;
        $this->dependents = $dependents;
        $this->income = $income;
        $this->maritalStatus = $maritalStatus;
        $this->riskQuestions = $riskQuestions;
        $this->vehicle = $vehicle;
        $this->house = $house;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getDependents(): int
    {
        return $this->dependents;
    }

    public function getIncome(): int
    {
        return $this->income;
    }

    public function getMaritalStatus(): string
    {
        return $this->maritalStatus;
    }

    public function getRiskQuestions(): array
    {
        return $this->riskQuestions;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function getHouse(): ?House
    {
        return $this->house;
    }

    public static function validationRules() : array
    {
        return [
            'age' => 'required|int|min:0',
            'dependents' => 'required|int|min:0',
            'income' => 'required|int|min:0',
            'risk_questions' => 'required|array|size:3',
            'risk_questions.*' => 'bool',
            'marital_status' => [
                'required',
                'string',
                Rule::in(['single', 'married']),
            ]
        ];
    }
}
