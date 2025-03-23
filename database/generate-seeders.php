<?php

// This script should be run from the project root with:
// php database/generate-seeders.php table_name

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the table name from command line argument
$table = $argv[1] ?? null;

if (!$table) {
    echo "Please provide a table name\n";
    exit(1);
}

// Get all records from the table
$records = DB::table($table)->get();

if ($records->isEmpty()) {
    echo "No records found in table: {$table}\n";
    exit(1);
}

echo "Found " . count($records) . " records\n";

// Generate the PHP code
$output = "<?php\n\n";
$output .= "namespace Database\\Seeders;\n\n";
$output .= "use Illuminate\\Database\\Seeder;\n";
$output .= "use Illuminate\\Support\\Facades\\DB;\n\n";
$output .= "class " . ucfirst(str_replace('_', '', $table)) . "TableSeeder extends Seeder\n";
$output .= "{\n";
$output .= "    /**\n";
$output .= "     * Run the database seeds.\n";
$output .= "     */\n";
$output .= "    public function run(): void\n";
$output .= "    {\n";
$output .= "        \$data = [\n";

foreach ($records as $record) {
    $output .= "            [\n";
    foreach ((array)$record as $column => $value) {
        // Handle different data types
        if (is_null($value)) {
            $output .= "                '{$column}' => null,\n";
        } elseif (is_numeric($value)) {
            $output .= "                '{$column}' => {$value},\n";
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
            $output .= "                '{$column}' => {$value},\n";
        } else {
            // Escape single quotes in strings
            $value = str_replace("'", "\\'", $value);
            $output .= "                '{$column}' => '{$value}',\n";
        }
    }
    $output .= "            ],\n";
}

$output .= "        ];\n\n";
$output .= "        // Insert the data\n";
$output .= "        DB::table('{$table}')->insert(\$data);\n";
$output .= "    }\n";
$output .= "}\n";

// Save the output to a file
$filename = __DIR__ . "/seeders/" . ucfirst(str_replace('_', '', $table)) . "TableSeeder.php";
file_put_contents($filename, $output);

echo "Seeder created at: {$filename}\n"; 