<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $source = \App\Models\Source::updateOrcreate([
            'name' => 'Smadex',
            'description' => 'Smadex source',
            'status' => 'active',
        ]);

        $source1 = \App\Models\Source::updateOrcreate([
            'name' => 'Google-Ads',
            'description' => 'Google Ads source',
            'status' => 'active',
        ]);

        $source2 = \App\Models\Source::updateOrcreate([
            'name' => 'Monitizer',
            'description' => 'Monitizer source',
            'status' => 'active',
        ]);

        $project1 = \App\Models\Project::updateOrcreate([
            'name' => 'HE Score',
            'description' => 'HE Score project',
            'status' => 'active',
        ]);

        $project2 = \App\Models\Project::updateOrcreate([
            'name' => 'HE Grand Prize',
            'description' => 'HE Grand Prize project',
            'status' => 'active',
        ]);

        $project1->sources()->attach($source->id, ['campaign_id' => 98]);
        $project1->sources()->attach($source1->id, ['campaign_id' => 100]);
        $project1->sources()->attach($source2->id, ['campaign_id' => 102]);
        $project2->sources()->attach($source2->id, ['campaign_id' => 109]);
    }
}
