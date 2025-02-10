<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            'support_mobile_signup' => 0,
            'client_mobile_signup' => 0,
        ];

        foreach ($settings as $name => $value) {
            BusinessSetting::updateOrCreate([
                'name' => $name,
                'value' => $value
            ]);
        }
    }
}
