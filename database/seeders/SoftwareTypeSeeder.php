<?php

namespace Database\Seeders;

use App\Models\SoftwareType;
use Illuminate\Database\Seeder;

class SoftwareTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Tailor',
            'ARM',
            'Accounting',
            'App',
            'Bakery',
            'CloudPOS Report',
            'FellowPro',
            'Garments production management',
            'HRM',
            'Inventory',
            'Jotey Retail ERP',
            'MHRM',
            'Mobil Shop POS',
            'POS',
            'Payroll',
            'Pos',
            'Pos',
            'Retail ERP',
            'Retail Master (Pro)',
            'Software',
            'Tpos',
            'WEB',
            'Web',
            'pharmacy management software',
            'point of sales',
            'pos',
            'software'
        ];

        foreach($types as $type){
            SoftwareType::create([
                'software_type' => $type
            ]);
        }
    }
}
