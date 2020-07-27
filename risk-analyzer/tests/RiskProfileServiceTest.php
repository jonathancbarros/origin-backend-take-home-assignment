<?php

namespace tests;

use App\House;
use App\Services\RiskProfileService;
use App\UserProfile;
use App\Vehicle;
use PHPUnit\Framework\TestCase;

class RiskProfileServiceTest extends TestCase
{
    protected $vehicle;
    protected $house;
    protected $userProfile;
    protected $riskProfile;

    protected $homeScore;
    protected $autoScore;
    protected $lifeScore;
    protected $disabilityScore;

    protected function setUp(): void
    {
        $this->homeScore = new \ReflectionProperty('App\Services\RiskProfileService', 'homeScore');
        $this->homeScore->setAccessible(true);

        $this->autoScore = new \ReflectionProperty('App\Services\RiskProfileService', 'autoScore');
        $this->autoScore->setAccessible(true);

        $this->disabilityScore = new \ReflectionProperty('App\Services\RiskProfileService', 'disabilityScore');
        $this->disabilityScore->setAccessible(true);

        $this->lifeScore = new \ReflectionProperty('App\Services\RiskProfileService', 'lifeScore');
        $this->lifeScore->setAccessible(true);
    }

    protected function tearDown(): void
    {
        $this->vehicle = null;
        $this->house = null;
        $this->userProfile = null;
        $this->riskProfile = null;
    }

    private function customSetUp(
        int $age,
        int $dependents,
        int $income,
        string $maritalStatus,
        array $riskQuestions,
        ?int $vehicleYear,
        ?string $houseOwnershipStatus
    ) {
        $this->vehicle = $vehicleYear ? new Vehicle($vehicleYear) : null;
        $this->house = $houseOwnershipStatus ? new House($houseOwnershipStatus) : null;
        $this->userProfile = new UserProfile($age, $dependents, $income, $maritalStatus, $riskQuestions, $this->vehicle, $this->house);
        $this->riskProfile = new RiskProfileService($this->userProfile);
    }

    public function testGetResult()
    {
        $this->customSetUp(35, 2, 0, 'married', [0, 1, 0], 2018, 'owned');
        $this->assertEquals(
            [
                'auto' => 'regular',
                'disability' => 'ineligible',
                'home' => 'economic',
                'life' => 'regular'
            ],
            $this->riskProfile->getResult()
        );
    }

    public function testCalculateBaseScore()
    {
        $calculateBaseScore = new \ReflectionMethod('App\Services\RiskProfileService', 'calculateBaseScore');
        $calculateBaseScore->setAccessible(true);

        $this->customSetUp(35, 2, 0, 'married', [1, 1, 1], 2018, 'owned');
        $calculateBaseScore->invoke($this->riskProfile);
        $this->assertEquals(3, $this->autoScore->getValue($this->riskProfile));

        $this->customSetUp(35, 2, 0, 'married', [false, false, false], 2018, 'owned');
        $calculateBaseScore->invoke($this->riskProfile);
        $this->assertEquals(0, $this->autoScore->getValue($this->riskProfile));
    }

    public function testEvaluateIneligibility()
    {
        $evaluateIneligibility = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateIneligibility');
        $evaluateIneligibility->setAccessible(true);

        $this->customSetUp(35, 2, 0, 'married', [1, 0, 1], 2018, null);

        $evaluateIneligibility->invoke($this->riskProfile);

        $this->assertFalse($this->disabilityScore->getValue($this->riskProfile));
        $this->assertFalse($this->homeScore->getValue($this->riskProfile));
    }

    public function testEvaluateAge()
    {
        $evaluateAge = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateAge');
        $evaluateAge->setAccessible(true);

        $this->customSetUp(25, 2, 0, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateAge->invoke($this->riskProfile);
        $this->assertEquals(-2, $this->homeScore->getValue($this->riskProfile));

        $this->customSetUp(35, 2, 0, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateAge->invoke($this->riskProfile);
        $this->assertEquals(-1, $this->homeScore->getValue($this->riskProfile));

        $this->customSetUp(50, 2, 0, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateAge->invoke($this->riskProfile);
        $this->assertEquals(0, $this->homeScore->getValue($this->riskProfile));
    }

    public function testEvaluateIncome()
    {
        $evaluateIncome = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateIncome');
        $evaluateIncome->setAccessible(true);

        $this->customSetUp(25, 2, 250000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateIncome->invoke($this->riskProfile);
        $this->assertEquals(-1, $this->homeScore->getValue($this->riskProfile));

        $this->customSetUp(25, 2, 10000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateIncome->invoke($this->riskProfile);
        $this->assertEquals(0, $this->homeScore->getValue($this->riskProfile));

    }

    public function testEvaluateHouse()
    {
        $evaluateHouse = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateHouse');
        $evaluateHouse->setAccessible(true);

        $this->customSetUp(25, 2, 250000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateHouse->invoke($this->riskProfile);
        $this->assertEquals(0, $this->homeScore->getValue($this->riskProfile));

        $this->customSetUp(25, 2, 250000, 'married', [1, 0, 1], 2018, 'mortgaged');
        $evaluateHouse->invoke($this->riskProfile);
        $this->assertEquals(1, $this->homeScore->getValue($this->riskProfile));

    }

    public function testEvaluateDependents()
    {
        $evaluateDependents = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateDependents');
        $evaluateDependents->setAccessible(true);

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateDependents->invoke($this->riskProfile);

        $this->assertEquals(1, $this->lifeScore->getValue($this->riskProfile));
        $this->assertEquals(1, $this->disabilityScore->getValue($this->riskProfile));
    }

    public function testEvaluateMaritalStatus()
    {
        $evaluateMaritalStatus = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateMaritalStatus');
        $evaluateMaritalStatus->setAccessible(true);

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateMaritalStatus->invoke($this->riskProfile);

        $this->assertEquals(1, $this->lifeScore->getValue($this->riskProfile));
        $this->assertEquals(-1, $this->disabilityScore->getValue($this->riskProfile));
    }

    public function testEvaluateVehicle()
    {
        $evaluateVehicle = new \ReflectionMethod('App\Services\RiskProfileService', 'evaluateVehicle');
        $evaluateVehicle->setAccessible(true);

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2018, 'owned');
        $evaluateVehicle->invoke($this->riskProfile);
        $this->assertEquals(1, $this->autoScore->getValue($this->riskProfile));

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2014, 'owned');
        $evaluateVehicle->invoke($this->riskProfile);
        $this->assertNull($this->autoScore->getValue($this->riskProfile));
    }

    public function testAddOrDeductRiskPoint()
    {
        $addOrDeductRiskPoint = new \ReflectionMethod('App\Services\RiskProfileService', 'addOrDeductRiskPoint');
        $addOrDeductRiskPoint->setAccessible(true);

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2014, 'owned');

        $this->assertFalse($addOrDeductRiskPoint->invoke($this->riskProfile, 10, false));
        $this->assertEquals(9, $addOrDeductRiskPoint->invoke($this->riskProfile, 10, -1));
        $this->assertEquals(-5, $addOrDeductRiskPoint->invoke($this->riskProfile, -5, 0));
    }

    public function testParseScore()
    {
        $parseScore = new \ReflectionMethod('App\Services\RiskProfileService', 'parseScore');
        $parseScore->setAccessible(true);

        $this->customSetUp(25, 1, 250000, 'married', [1, 0, 1], 2014, 'owned');

        $this->assertEquals('responsible', $parseScore->invoke($this->riskProfile, 10));
        $this->assertEquals('ineligible', $parseScore->invoke($this->riskProfile, false));
        $this->assertEquals('economic', $parseScore->invoke($this->riskProfile, -100));
        $this->assertEquals('economic', $parseScore->invoke($this->riskProfile, 0));
        $this->assertEquals('regular', $parseScore->invoke($this->riskProfile, 1));
    }
}
