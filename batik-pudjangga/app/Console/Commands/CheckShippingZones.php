<?php

// ============================================
// FILE: app/Console/Commands/CheckShippingZones.php
// Create this file to check and fix shipping zones
// ============================================

namespace App\Console\Commands;

use App\Models\ShippingZone;
use Illuminate\Console\Command;

class CheckShippingZones extends Command
{
    protected $signature = 'shipping:check';
    protected $description = 'Check and fix shipping zones data';

    public function handle()
    {
        $this->info('Checking Shipping Zones...');
        
        $count = ShippingZone::count();
        $this->info("Found {$count} shipping zones in database");
        
        if ($count === 0) {
            $this->warn('No shipping zones found! Seeding...');
            $this->call('db:seed', ['--class' => 'ShippingZoneSeeder']);
            return;
        }
        
        // Check for NULL or 0 costs
        $invalid = ShippingZone::where(function($query) {
            $query->whereNull('cost_regular')
                  ->orWhereNull('cost_express')
                  ->orWhere('cost_regular', 0)
                  ->orWhere('cost_express', 0);
        })->get();
        
        if ($invalid->count() > 0) {
            $this->error("Found {$invalid->count()} zones with invalid costs:");
            foreach ($invalid as $zone) {
                $this->line("- {$zone->province}: Regular={$zone->cost_regular}, Express={$zone->cost_express}");
            }
            
            if ($this->confirm('Fix invalid costs?')) {
                foreach ($invalid as $zone) {
                    $zone->update([
                        'cost_regular' => $zone->cost_regular ?: 12000,
                        'cost_express' => $zone->cost_express ?: 22000,
                    ]);
                }
                $this->info('Fixed!');
            }
        } else {
            $this->info('All shipping zones are valid!');
        }
        
        // Display sample data
        $this->info("\nSample shipping zones:");
        ShippingZone::take(5)->get()->each(function($zone) {
            $this->line("{$zone->province} (Zone {$zone->zone}): Regular=Rp{$zone->cost_regular}, Express=Rp{$zone->cost_express}");
        });
    }
}


// ============================================
// FILE: database/seeders/ShippingZoneSeeder.php (UPDATED)
// Make sure this seeder is correct
// ============================================



// ============================================
// QUICK FIX SCRIPT - Run in tinker
// php artisan tinker
// Then paste this:
// ============================================

// Check if shipping zones exist
$count = \App\Models\ShippingZone::count();
echo "Shipping zones count: {$count}\n";

// If 0, seed them
if ($count === 0) {
    echo "Seeding shipping zones...\n";
    Artisan::call('db:seed', ['--class' => 'ShippingZoneSeeder']);
    echo "Done!\n";
}

// Check for invalid costs
$invalid = \App\Models\ShippingZone::where('cost_regular', 0)
    ->orWhere('cost_express', 0)
    ->orWhereNull('cost_regular')
    ->orWhereNull('cost_express')
    ->get();

if ($invalid->count() > 0) {
    echo "Found {$invalid->count()} invalid zones. Fixing...\n";
    foreach ($invalid as $zone) {
        $zone->update([
            'cost_regular' => $zone->cost_regular ?: 12000,
            'cost_express' => $zone->cost_express ?: 22000,
        ]);
    }
    echo "Fixed!\n";
}

// Display first 5 zones
echo "\nSample zones:\n";
\App\Models\ShippingZone::take(5)->get()->each(function($zone) {
    echo "{$zone->province}: Regular=Rp{$zone->cost_regular}, Express=Rp{$zone->cost_express}\n";
});