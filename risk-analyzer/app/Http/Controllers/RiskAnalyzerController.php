<?php


namespace App\Http\Controllers;

use App\House;
use App\Services\RiskProfileService;
use App\UserProfile;
use App\Vehicle;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class RiskAnalyzerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('vehicle') && !empty($request->input('vehicle'))) {
            $this->validate($request, Vehicle::validationRules());
            $vehicle = new Vehicle($request->input('vehicle.year'));
        }

        if ($request->has('house') && !empty($request->input('house'))) {
            $this->validate($request, House::validationRules());
            $house = new House($request->input('house.ownership_status'));
        }

        $this->validate($request, UserProfile::validationRules());

        $userProfile = new UserProfile(
            $request->input('age'),
            $request->input('dependents'),
            $request->input('income'),
            $request->input('marital_status'),
            $request->input('risk_questions'),
            $vehicle ?? null,
            $house ?? null
        );

        $riskProfile = new RiskProfileService($userProfile);

        return response()->json($riskProfile->getResult());
    }
}
