<?php

namespace App\Services;

use app\UserProfile;

class RiskProfileService
{
    private $personalInformationService;

    private $autoScore;
    private $disabilityScore;
    private $homeScore;
    private $lifeScore;

    public function __construct(UserProfile $personalInformationService)
    {
        $this->personalInformationService = $personalInformationService;
    }

    public function getResult() : array
    {
        $this->calculateRisk();

        return [
            'auto' => $this->parseScore($this->autoScore),
            'disability' =>  $this->parseScore($this->disabilityScore),
            'home' => $this->parseScore($this->homeScore),
            'life' => $this->parseScore($this->lifeScore)
        ];
    }

    private function calculateRisk() : void
    {
        $this->calculateBaseScore();
        $this->evaluateIneligibility();
        $this->evaluateAge();
        $this->evaluateIncome();
        $this->evaluateHouse();
        $this->evaluateDependents();
        $this->evaluateMaritalStatus();
        $this->evaluateVehicle();
    }

    private function calculateBaseScore() : void
    {
        $baseScore = array_sum($this->personalInformationService->getRiskQuestions());

        $this->autoScore = $baseScore;
        $this->disabilityScore = $baseScore;
        $this->homeScore = $baseScore;
        $this->lifeScore = $baseScore;
    }

    private function evaluateIneligibility() : void
    {
        if (!$this->personalInformationService->getIncome()) {
            $this->disabilityScore = false;
        }

        if (!$this->personalInformationService->getVehicle()) {
            $this->autoScore = false;
        }

        if (!$this->personalInformationService->getHouse()) {
            $this->homeScore = false;
        }

        if ($this->personalInformationService->getAge() > 60) {
            $this->disabilityScore = false;
            $this->lifeScore = false;
        }
    }

    private function evaluateAge() : void
    {
        $age = $this->personalInformationService->getAge();
        $riskPointsToDeduct = 0;

        if ($age < 30) {
            $riskPointsToDeduct = -2;
        }

        if ($age >= 30 && $age <= 40) {
            $riskPointsToDeduct = -1;
        }

        $this->autoScore = $this->addOrDeductRiskPoint($riskPointsToDeduct, $this->autoScore);
        $this->disabilityScore = $this->addOrDeductRiskPoint($riskPointsToDeduct, $this->disabilityScore);
        $this->homeScore = $this->addOrDeductRiskPoint($riskPointsToDeduct, $this->homeScore);
        $this->lifeScore = $this->addOrDeductRiskPoint($riskPointsToDeduct, $this->lifeScore);
    }

    private function evaluateIncome() : void
    {
        if ($this->personalInformationService->getIncome() > 200000) {
            $this->autoScore = $this->addOrDeductRiskPoint(-1, $this->autoScore);
            $this->disabilityScore = $this->addOrDeductRiskPoint(-1, $this->disabilityScore);
            $this->homeScore = $this->addOrDeductRiskPoint(-1, $this->homeScore);
            $this->lifeScore = $this->addOrDeductRiskPoint(-1, $this->lifeScore);
        }
    }

    private function evaluateHouse() : void
    {
        $house = $this->personalInformationService->getHouse();

        if ($house && $house->getOwnershipStatus() == 'mortgaged') {
            $this->homeScore = $this->addOrDeductRiskPoint(1, $this->homeScore);
            $this->disabilityScore = $this->addOrDeductRiskPoint(1, $this->disabilityScore);
        }
    }

    private function evaluateDependents() : void
    {
        if ($this->personalInformationService->getDependents()) {
            $this->lifeScore = $this->addOrDeductRiskPoint(1, $this->lifeScore);
            $this->disabilityScore = $this->addOrDeductRiskPoint(1, $this->disabilityScore);
        }
    }

    private function evaluateMaritalStatus() : void
    {
        if ($this->personalInformationService->getMaritalStatus() == 'married') {
            $this->lifeScore = $this->addOrDeductRiskPoint(1, $this->lifeScore);
            $this->disabilityScore = $this->addOrDeductRiskPoint(-1, $this->disabilityScore);
        }
    }

    private function evaluateVehicle() : void
    {
        $vehicle = $this->personalInformationService->getVehicle();

        if ($vehicle && $vehicle->getYear() >= date('Y') - 5) {
            $this->autoScore = $this->addOrDeductRiskPoint(1, $this->autoScore);
        }
    }

    /**
     * @param int $riskPoint
     * @param $currentScore
     * @return bool|int
     */
    private function addOrDeductRiskPoint(int $riskPoint, $currentScore)
    {
        if ($currentScore === false) {
            return false;
        }

        $currentScore += $riskPoint;

        return $currentScore;
    }

    private function parseScore($score) : string
    {
        if ($score === false) {
            return 'ineligible';
        }

        if ($score <= 0) {
            return 'economic';
        }

        if ($score >= 1 && $score <= 2) {
            return 'regular';
        }

        return 'responsible';
    }
}
