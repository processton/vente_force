<?php

namespace Crater\Http\Controllers\V1\Installation;

use Auth;
use Crater\Http\Controllers\Controller;
use Crater\Models\Company;
use Crater\Models\Setting;
use Crater\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Silber\Bouncer\BouncerFacade;
use Vinkla\Hashids\Facades\Hashids;

class FinishController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        Setting::setSetting('profile_complete', "COMPLETED");
        \Storage::disk('local')->put('database_created', 'database_created');

        $user = User::create([
            'email' => tenant('admin_email'),
            'name' => tenant('admin_name'),
            'role' => 'super admin',
            'password' => 'crater@123',
        ]);

        // $user = User::where('role', 'super admin')->first();

        // $company = Company::where('owner_id', $user->id)->first();

        $company = Company::create([
            'name' => tenant('name'),
            'owner_id' => $user->id,
            'slug' => tenant('id')
        ]);

        $company->unique_hash = Hashids::connection(Company::class)->encode($company->id);
        $company->save();
        $company->setupDefaultData();
        $user->companies()->attach($company->id);
        BouncerFacade::scope()->to($company->id);

        $user->assign('super admin');
        Auth::login($user);

        return response()->json([
            'success' => true,
            'user' => $user,
            'company' => $user->companies()->first()
        ]);
    }
}
