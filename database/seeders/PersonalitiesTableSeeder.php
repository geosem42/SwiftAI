<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Courtesy of
        // https://github.com/f/awesome-chatgpt-prompts

        $csvFile = storage_path('app/personalities/prompts.csv'); // Provide the path to your CSV file
        $delimiter = ','; // Specify the delimiter used in your CSV file
        $header = true; // Set to true if your CSV file has a header row

        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if ($header) {
                    $header = false;
                    continue;
                }
                DB::table('personalities')->insert(
                    [
                        'name' => $data[0],
                        'system_message' => $data[1],
                    ]
                );
            }
            fclose($handle);
        }
    }
}
