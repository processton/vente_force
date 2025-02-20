<?php

namespace Database\Seeders;

use Crater\Models\Company;
use Crater\Models\Setting;
use Crater\Models\User;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade;
use Vinkla\Hashids\Facades\Hashids;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::setSetting('profile_complete', "COMPLETED");
        \Storage::disk('local')->put('database_created', 'database_created');

        $user = User::create([
            'email' => tenant('admin_email'),
            'name' => tenant('admin_name'),
            'role' => 'super admin',
            'password' => 'crater@123',
        ]);

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
    }
}
