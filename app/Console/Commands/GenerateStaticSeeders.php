<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateStaticSeeders extends Command
{
    // Sample Command:
    // php artisan make:static-seeder food_categories
    protected $signature = 'make:static-seeder {table} {--output=}'; 
    protected $description = 'Generate a static seeder from database table';

    public function handle()
    {
        $table = $this->argument('table');
        $outputPath = $this->option('output') ?: database_path("seeders/{$table}TableSeeder.php");
        
        $this->info("Generating static seeder for table: {$table}");
        
        // Get all records from the table
        $records = DB::table($table)->get();
        
        if ($records->isEmpty()) {
            $this->error("No records found in table: {$table}");
            return 1;
        }
        
        $this->info("Found " . count($records) . " records");
        
        // Convert records to PHP array format
        $dataArray = "[\n";
        foreach ($records as $record) {
            $dataArray .= "            [\n";
            foreach ((array)$record as $column => $value) {
                // Handle different data types
                if (is_null($value)) {
                    $dataArray .= "                '{$column}' => null,\n";
                } elseif (is_numeric($value)) {
                    $dataArray .= "                '{$column}' => {$value},\n";
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                    $dataArray .= "                '{$column}' => {$value},\n";
                } else {
                    // Escape single quotes in strings
                    $value = str_replace("'", "\\'", $value);
                    $dataArray .= "                '{$column}' => '{$value}',\n";
                }
            }
            $dataArray .= "            ],\n";
        }
        $dataArray .= "        ]";
        
        // Generate the seeder class
        $className = ucfirst(Str::camel($table)) . 'TableSeeder';
        $seederContent = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$className} extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \$data = {$dataArray};
        
        // Insert the data
        DB::table('{$table}')->insert(\$data);
    }
}
PHP;
        
        // Save the seeder file
        File::put($outputPath, $seederContent);
        
        $this->info("Static seeder created at: {$outputPath}");
        
        return 0;
    }
} 