<?php

namespace Crater\Http\Controllers\Central;

use Artisan;
use Crater\Http\Controllers\Controller;
use Crater\Models\Central\Tenant;
use Illuminate\Http\Request;
use phpseclib\Crypt\RSA as LegacyRSA;
use phpseclib3\Crypt\RSA;
use Illuminate\Support\Arr;

class SetupController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|string|unique:tenants,id',
            'name' => 'required',
            'domain' => 'required|string',
            'theme' => 'nullable|string',
            'admin_email' => 'required|string|email',
            'admin_name' => 'required|string',
            'tenancy_db_name' => 'required|string',
            'tenancy_db_username' => 'required|string',
            'tenancy_db_password' => 'required|string',
            'MAX_CUSTOMERS' => 'required',
            'currency' => 'nullable',
            'country' => 'nullable',
            'identity_force_app_secret' => 'required|string',
            'identity_force_app_key' => 'required|string',
            'identity_force_app_url' => 'required|string'
        ]);

        try {

            if (class_exists(LegacyRSA::class)) {

                $keys = (new LegacyRSA)->createKey(4096);

                $data['passport_public_key'] = Arr::get($keys, 'publickey');
                $data['passport_private_key'] = Arr::get($keys, 'privatekey');
            } else {
                $key = RSA::createKey(4096);

                $data['passport_public_key'] =  (string) $key->getPublicKey();
                $data['passport_private_key'] = (string) $key;
            }

            $tenant = Tenant::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'theme' => $data['theme'] ?? 'default',
                'passport_public_key' => $data['passport_public_key'],
                'passport_private_key' => $data['passport_private_key'],
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'tenancy_db_name' => $data['tenancy_db_name'],
                'tenancy_db_username' => $data['tenancy_db_username'],
                'tenancy_db_password' => $data['tenancy_db_password'],
                'max_customers' => $data['MAX_CUSTOMERS'],
                'currency' => $data['currency'],
                'country' => $data['country'],
                'identity_force_app_secret' => $data['identity_force_app_secret'],
                'identity_force_app_key' => $data['identity_force_app_key'],
                'identity_force_app_url' => $data['identity_force_app_url']
            ]);

            $tenant->domains()->create([
                'domain' => $data['domain'],
            ]);

            return response()->json($tenant->only([
                'id',
                'name'
            ]), 201);

        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
