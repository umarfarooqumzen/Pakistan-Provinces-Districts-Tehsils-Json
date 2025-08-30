<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Models\Tehsil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PakistanLocationSeeder extends Seeder
{
    /**
     * Run the database seeds for the new pakistan-provinces-districts-tehsils.json format. 
     * This is Laravel Seeder to populate your provinces districts and tehsils table interlinking with slugs and ids automatically just run this and enjoy
     */
    public function run(): void
    {
        // Path to the new JSON file
        $jsonPath = public_path('pakistan-provinces-districts-tehsils.json');
        
        // Check if the JSON file exists in the public directory
        if (!File::exists($jsonPath)) {
            $this->command->error('Pakistan location data file not found in public directory.');
            return;
        }
        
        // Parse the JSON data
        $jsonContent = File::get($jsonPath);
        $data = json_decode($jsonContent, true);
        
        if (!isset($data['provinces']) || !is_array($data['provinces'])) {
            $this->command->error('Invalid JSON structure: "provinces" array not found.');
            return;
        }
        
        // Create a backup of existing data (optional)
        $this->createBackup();
        
        // Process provinces, districts, and tehsils
        $this->command->info('Starting to seed Pakistan location data from pakistan-provinces-districts-tehsils.json...');
        
        // Insert provinces
        $provinceMap = [];
        foreach ($data['provinces'] as $provinceData) {
            $provinceName = $provinceData['name'];
            $slug = Str::slug($provinceName);
            
            // Check if province with this slug already exists
            $existingProvince = Province::where('slug', $slug)->first();
            
            if ($existingProvince) {
                $this->command->info("Province with slug {$slug} already exists. Using existing province.");
                $provinceMap[$provinceName] = $existingProvince->id;
            } else {
                // Check if province with this name already exists
                $existingByName = Province::where('name', ucwords($provinceName))->first();
                
                if ($existingByName) {
                    $this->command->info("Province with name {$provinceName} already exists. Using existing province.");
                    $provinceMap[$provinceName] = $existingByName->id;
                } else {
                    $province = Province::create([
                        'name' => ucwords($provinceName),
                        'slug' => $slug,
                    ]);
                    
                    $provinceMap[$provinceName] = $province->id;
                    $this->command->info("Created new province: " . ucwords($provinceName));
                }
            }
            
            // Process districts for this province
            if (isset($provinceData['districts']) && is_array($provinceData['districts'])) {
                $this->processDistricts($provinceData['districts'], $provinceMap[$provinceName], $provinceName);
            }
        }
        
        $this->command->info('Pakistan locations data from pakistan2.json seeded successfully!');
    }
    
    /**
     * Process districts and their tehsils
     */
    private function processDistricts(array $districts, int $provinceId, string $provinceName): void
    {
        $districtMap = [];
        
        foreach ($districts as $districtData) {
            $districtName = $districtData['name'];
            $slug = Str::slug($districtName);
            
            // Check if district with this slug already exists
            $existingDistrict = District::where('slug', $slug)->first();
            
            if ($existingDistrict) {
                // Use existing district with matching slug
                $this->command->info("Using existing district with slug: {$slug}");
                $districtMap[$districtName] = $existingDistrict->id;
            } else {
                // Check if district with this name and province already exists
                $existingByName = District::where('name', ucwords($districtName))
                    ->where('province_id', $provinceId)
                    ->first();
                    
                if ($existingByName) {
                    // Use existing district with matching name and province
                    $this->command->info("Using existing district: {$districtName} in province: " . ucwords($provinceName));
                    $districtMap[$districtName] = $existingByName->id;
                } else {
                    // Create new district
                    $district = District::create([
                        'name' => ucwords($districtName),
                        'slug' => $slug,
                        'province_id' => $provinceId,
                    ]);
                    $districtMap[$districtName] = $district->id;
                    $this->command->info("Created new district: " . ucwords($districtName) . " in province: " . ucwords($provinceName));
                }
            }
            
            // Process tehsils for this district
            if (isset($districtData['tehsils']) && is_array($districtData['tehsils'])) {
                $this->processTehsils($districtData['tehsils'], $districtMap[$districtName], $districtName);
            }
        }
    }
    
    /**
     * Process tehsils for a district
     */
    private function processTehsils(array $tehsils, int $districtId, string $districtName): void
    {
        foreach ($tehsils as $tehsilName) {
            // Check if tehsil already exists for this district
            $existingTehsil = Tehsil::where('name', ucwords($tehsilName))
                ->where('district_id', $districtId)
                ->first();
            
            if ($existingTehsil) {
                // Skip if tehsil already exists for this district
                $this->command->info("Skipping existing tehsil: {$tehsilName} in district: " . ucwords($districtName));
                continue;
            }
            
            // Create a base slug from the tehsil name
            $baseSlug = Str::slug($tehsilName);
            
            // Check if tehsil with this slug already exists
            $existingSlug = Tehsil::where('slug', $baseSlug)->first();
            
            // If slug exists, create a unique slug by appending district name
            if ($existingSlug) {
                $slug = Str::slug($tehsilName . '-' . $districtName);
                $this->command->info("Creating unique slug for duplicate tehsil: {$tehsilName} in {$districtName} district. New slug: {$slug}");
            } else {
                $slug = $baseSlug;
            }
            
            // Create the tehsil
            Tehsil::create([
                'name' => ucwords($tehsilName),
                'slug' => $slug,
                'district_id' => $districtId,
            ]);
            $this->command->info("Created new tehsil: " . ucwords($tehsilName) . " in district: " . ucwords($districtName));
        }
    }
    
    /**
     * Create a backup of existing location data
     */
    private function createBackup(): void
    {
        $backupDir = storage_path('app/backups');
        
        // Create backup directory if it doesn't exist
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        
        // Backup provinces
        $provinces = Province::all()->toJson();
        File::put("{$backupDir}/provinces_{$timestamp}.json", $provinces);
        
        // Backup districts
        $districts = District::all()->toJson();
        File::put("{$backupDir}/districts_{$timestamp}.json", $districts);
        
        // Backup tehsils
        $tehsils = Tehsil::all()->toJson();
        File::put("{$backupDir}/tehsils_{$timestamp}.json", $tehsils);
        
        $this->command->info("Backup of existing location data created at {$backupDir}");
    }
}
