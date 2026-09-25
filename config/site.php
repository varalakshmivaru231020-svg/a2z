<?php

/*
|--------------------------------------------------------------------------
| Company details
|--------------------------------------------------------------------------
| Everything the public pages show about the company (name, contact info,
| offices, clients, leadership) lives here so it is edited in one place.
|
| REVIEW BEFORE LAUNCH: the phone, email, offices, founding year, clients and
| leadership below were carried over from the earlier company brochure and
| have not yet been replaced with AKS Global Maintenance's own details.
*/

return [
    'name' => 'AKS Global Maintenance Facility Management Services',
    'brand' => 'AKS Global Maintenance',
    'tagline' => 'We care your needs',
    'slogan' => 'Have an exciting comfort always…',
    'footer_text' => 'A one-stop facility management and manpower company serving Bangalore and Kochi.',

    // The bar at the very top of the site (edited in Admin > Site settings > Announcement bar).
    'announcement' => [
        'enabled' => true,
        'text' => 'We care your needs — facility & manpower services all South India',
        'home_only' => true,
    ],
    'founded' => 2023,

    // Login created by `php artisan db:seed` (see database/seeders/AdminUserSeeder.php)
    'admin' => [
        'name' => env('ADMIN_NAME', 'AKS Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'phone' => '+91 87146 34801',
    'phone_link' => '+918714634801',
    'whatsapp' => '918714634801',
    'email' => 'josha.infinity777@gmail.com',

    // Order matters: the first office is the one shown on the map by default
    // and used as the primary address in structured data.
    'offices' => [
        [
            'key' => 'bangalore',
            'label' => 'Branch Office · Bangalore',
            'street' => 'No.194, 1st Floor, AMR Complex, Horamavu Agara, Main Road',
            'locality' => 'Bangalore',
            'region' => 'Karnataka',
            'postal' => '560043',
            'map' => 'AMR Complex, Horamavu Agara Main Road, Bangalore 560043',
        ],
        [
            'key' => 'corporate',
            'label' => 'Corporate Office · Bengaluru',
            'street' => '2nd Floor, Fountain Head Building, No.73, Varthur Rd',
            'locality' => 'Bengaluru',
            'region' => 'Karnataka',
            'postal' => '560093',
            'map' => 'Fountain Head Building, Varthur Road, Bengaluru 560093',
        ],
        [
            'key' => 'kochi',
            'label' => 'Branch Office · Kochi',
            'street' => '#38/354B, Arakkakadavu Road, Chttupabukara, Edappally',
            'locality' => 'Kochi',
            'region' => 'Kerala',
            'postal' => '682024',
            'map' => 'Arakkakadavu Road, Edappally, Kochi 682024',
        ],
    ],

    'nav' => [
        ['label' => 'Home', 'route' => 'home', 'match' => 'home'],
        ['label' => 'About Us', 'route' => 'about', 'match' => 'about'],
        ['label' => 'Services', 'route' => 'services.index', 'match' => 'services.*'],
        ['label' => 'Recruitment', 'route' => 'recruitment.index', 'match' => 'recruitment.*'],
        ['label' => 'Gallery', 'route' => 'gallery', 'match' => 'gallery'],
        ['label' => 'Contact Us', 'route' => 'contact', 'match' => 'contact'],
    ],

    'service_categories' => [
        'facility' => [
            'name' => 'Facility Management',
            'blurb' => 'Housekeeping, pantry and office support staff, and day-to-day upkeep for offices, schools and residences.',
            'icon' => 'building',
        ],
        'maintenance' => [
            'name' => 'Maintenance & Renovation',
            'blurb' => 'Plumbing, electrical, carpentry, painting, false ceilings, aluminium fabrication and stone, tile and glass polishing.',
            'icon' => 'wrench',
        ],
        'government' => [
            'name' => 'Government Projects',
            'blurb' => 'BWSSB projects including Kaveri water line sanctions and sewerage services.',
            'icon' => 'droplet',
        ],
        'field' => [
            'name' => 'Financial & Field Services',
            'blurb' => 'Banking services, sales, tele-marketing, field marketing, field executives and delivery sub-contracting.',
            'icon' => 'landmark',
        ],
    ],

    'employment_types' => [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'contract' => 'Contract',
        'temporary' => 'Temporary',
    ],

    // Shown as a scrolling text strip on the Home and About pages.
    'clients' => [
        'Salarpuria Sattva',
        'Greenwood High School',
        'Noah Enterprises',
        'Kailash Enterprises',
        'VM Home Interiors',
        'L&T Limited',
        'IndianMoney.com',
    ],

    'leadership' => [
        [
            'name' => 'Mr. Prasad Ashok',
            'role' => 'Founder & CEO',
            'bio' => 'Founder and CEO of AKS Global Maintenance. He brings more than 7 years of hands-on experience in this trade, and an innovative management style that has earned the loyalty of the whole team.',
            'photo' => 'img/md-prasad-ashok.jpg',
        ],
        [
            'name' => 'Mrs. Shamali Prasad',
            'role' => 'General Manager',
            'bio' => 'Oversees accounting and business growth, and makes sure quality service is delivered every single time.',
        ],
        [
            'name' => 'Mr. Ashok',
            'role' => 'Operations Manager',
            'bio' => 'Supports day-to-day business operations and keeps our teams and sites running smoothly.',
        ],
        [
            'name' => 'Mr. Noor Hussain',
            'role' => 'Business Developer',
            'bio' => 'Works with clients to understand what they need and match them with the right service.',
        ],
    ],
];
