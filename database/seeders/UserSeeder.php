<?php

namespace Database\Seeders;

use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- One demo client ---
        $client = User::firstOrCreate(
            ['email' => 'ssemamborodney94@gmail.com'],
            [
                'name'     => 'Sarah Nakato',
                'password' => Hash::make('password'),
                'role'     => 'client',
                'status'   => 'active',
            ]
        );

        ClientProfile::updateOrCreate(
            ['user_id' => $client->id],
            ['phone' => '256762379935', 'default_location' => 'Kampala']
        );

        // --- Providers ---
        $providers = [
            [
                'name' => 'Aisha Kigozi',
                'email' => 'aisha@evently.test',
                'business_name' => 'Kigozi Catering Co.',
                'category_slug' => 'catering',
                'location' => 'Ntinda, Kampala',
                'bio' => 'Family-run catering studio serving Kampala since 2015. We specialise in buffet-style service for 50–400 guests, with a focus on local ingredients and presentation.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Brian Okello',
                'email' => 'brian@evently.test',
                'business_name' => 'Okello Decor Studio',
                'category_slug' => 'decoration',
                'location' => 'Bugolobi, Kampala',
                'bio' => 'Draping, floral installations, table styling, and lighting design for weddings, corporate dinners, and private parties.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Moses Ssekandi',
                'email' => 'moses@evently.test',
                'business_name' => 'Ssekandi Photography',
                'category_slug' => 'photography',
                'location' => 'Kololo, Kampala',
                'bio' => 'Documentary-style event photography. 200+ weddings shot across Uganda. Two shooters, full-day coverage, edited gallery within 7 days.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Daniel Musinguzi',
                'email' => 'daniel@evently.test',
                'business_name' => 'Musinguzi Films',
                'category_slug' => 'videography',
                'location' => 'Entebbe',
                'bio' => 'Cinematic event films. Same-day edit available for weddings and corporate launches.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'DJ Klear',
                'email' => 'klear@evently.test',
                'business_name' => 'Klear Sound',
                'category_slug' => 'dj',
                'location' => 'Kamwokya, Kampala',
                'bio' => 'Open-format DJ with full sound and lighting rig. Weddings, corporate events, private parties.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Patrick Wamala',
                'email' => 'patrick@evently.test',
                'business_name' => 'Wamala Events MC',
                'category_slug' => 'mc',
                'location' => 'Kampala',
                'bio' => 'Bilingual MC (English + Luganda). 8 years hosting weddings, galas, and corporate award nights.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Grace Atim',
                'email' => 'grace@evently.test',
                'business_name' => 'The Atim Gardens',
                'category_slug' => 'venue',
                'location' => 'Munyonyo, Kampala',
                'bio' => 'Outdoor garden venue for 80–300 guests. Includes parking, generator backup, and on-site coordinator.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
            [
                'name' => 'Samuel Kizza',
                'email' => 'samuel@evently.test',
                'business_name' => 'Kizza Live Band',
                'category_slug' => 'live-band',
                'location' => 'Kampala',
                'bio' => 'Nine-piece live band playing soul, afrobeat, and jazz standards. Available for weddings and corporate events.',
                'price_range_min' => 500,
                'price_range_max' => 550,
            ],
        ];

        $categories = DB::table('categories')->pluck('id', 'slug');

        foreach ($providers as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'     => $p['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'provider',
                    'status'   => 'active',
                ]
            );

            ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'category_id'         => $categories[$p['category_slug']] ?? null,
                    'business_name'       => $p['business_name'],
                    'location'            => $p['location'],
                    'bio'                 => $p['bio'],
                    'whatsapp_number'     => '2567' . str_pad((string) random_int(10000000, 99999999), 8, '0'),
                    'price_range_min'     => $p['price_range_min'],
                    'price_range_max'     => $p['price_range_max'],
                    'availability_status' => 'available',
                    'verification_status' => 'verified',
                ]
            );
        }
    }
}
