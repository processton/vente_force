<?php

namespace Database\Seeders;

use Crater\Models\Address;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Crater\Models\Country;
use Crater\Models\Currency;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CurrenciesTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(UsersTableSeeder::class);

        $currency = Currency::where('code', tenant('currency'))->first();

        CompanySetting::updateOrCreate([
            'option' => 'currency'
        ],[
            'value' => $currency->id
        ]);

        $country = Country::where('code', tenant('country'))->first();

        $company = Company::first();

        Address::create([
            'country_id' => $country->id,
            'company_id' => $company->id
        ]);

    }
}
