<?php

namespace tests;

use TestCase;

class RiskAnalyzerApiTest extends TestCase
{
    private function getRequestParams(
        int $age,
        int $dependents,
        int $income,
        string $maritalStatus,
        array $riskQuestions,
        ?array $vehicle,
        ?array $house
    ) {
        return [
            'age' => $age,
            'dependents' => $dependents,
            'income' => $income,
            'marital_status' => $maritalStatus,
            'risk_questions' => $riskQuestions,
            'vehicle' => $vehicle ?? null,
            'house' => $house ?? null,
        ];
    }

    public function testSuccessfulProposedCase()
    {
        $this->json(
            'POST',
            '/api/risk-analyzer',
            $this->getRequestParams(35, 2, 0, 'married', [0, 1, 0], ['year' => 2018], ['ownership_status' => 'owned'])
        )->seeJsonEquals([
            'auto' => 'regular',
            'disability' => 'ineligible',
            'home' => 'economic',
            'life' => 'regular'
        ]);
    }

    public function testAllIneligibility()
    {
        $this->json(
            'POST',
            '/api/risk-analyzer',
            $this->getRequestParams(61, 2, 0, 'married', [0, 1, 0], null, null)
        )->seeJsonEquals([
            'auto' => 'ineligible',
            'disability' => 'ineligible',
            'home' => 'ineligible',
            'life' => 'ineligible'
        ]);
    }

    public function testInvalidRiskQuestions()
    {
        $requestParams = $this->getRequestParams(61, 2, 0, 'married', [true, 'test', 0], null, null);

        $this->json(
            'POST',
            '/api/risk-analyzer',
            $requestParams
        )->seeStatusCode(422);

        $this->json(
            'POST',
            '/api/risk-analyzer',
            $requestParams
        )->seeJsonEquals([
            'risk_questions.1' => ['The risk_questions.1 field must be true or false.']
        ]);
    }

    public function testInvalidMaritalStatus()
    {
        $requestParams = $this->getRequestParams(61, 2, 0, 'divorced', [1, 0, 0], null, null);

        $this->json(
            'POST',
            '/api/risk-analyzer',
            $requestParams
        )->seeStatusCode(422);
    }

    public function testInvalidHouseOwnershipStatus()
    {
        $requestParams = $this->getRequestParams(61, 2, 0, 'divorced', [1, 0, 0], null, ['ownership_status' => 'sold']);

        $this->json(
            'POST',
            '/api/risk-analyzer',
            $requestParams
        )->seeStatusCode(422);

    }

    public function testInvalidVehicleYear()
    {
        $requestParams = $this->getRequestParams(61, 2, 0, 'divorced', [1, 0, 0], ['year' => 'year'], null);

        $this->json(
            'POST',
            '/api/risk-analyzer',
            $requestParams
        )->seeStatusCode(422);

    }
}
