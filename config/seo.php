<?php

/*
|--------------------------------------------------------------------------
| SEO defaults
|--------------------------------------------------------------------------
| Fallback title / description for every static page. The admin panel
| (SEO Pages) can override any of these; services and job openings have
| their own meta fields. Keep titles under ~60 characters (the site suffix
| is added automatically) and descriptions under ~160.
*/

return [
    'brand' => 'A2Z Global Maintenance',
    'title_suffix' => ' | A2Z Global Maintenance',
    'default_image' => 'img/og-default.jpg',
    'locale' => 'en_IN',

    'pages' => [
        'home' => [
            'label' => 'Home',
            'path' => '/',
            'title' => 'A2Z Global Maintenance – Facility Management Services',
            'description' => 'Housekeeping, plumbing, electrical, painting, BWSSB projects and field staffing in Bangalore and Kochi. One-stop facility services from A2Z Global Maintenance.',
        ],
        'about' => [
            'label' => 'About Us',
            'path' => '/about',
            'title' => 'About Us – Vision & Mission',
            'description' => 'A2Z Global Maintenance is a one-stop facility management and manpower company serving Bangalore and Kochi. Meet our team, vision and mission.',
        ],
        'services' => [
            'label' => 'Services',
            'path' => '/services',
            'title' => 'Our Facility & Maintenance Services',
            'description' => 'Explore housekeeping, office and pantry staff, plumbing, electrical, carpentry, painting, polishing, BWSSB projects, banking, sales and field services.',
        ],
        'recruitment' => [
            'label' => 'Recruitment',
            'path' => '/recruitment',
            'title' => 'Careers – Jobs in Bangalore & Kochi',
            'description' => 'Join A2Z Global Maintenance. Browse current job openings in facility management, field operations and sales, and apply online with your resume.',
        ],
        'gallery' => [
            'label' => 'Gallery',
            'path' => '/gallery',
            'title' => 'Photo Gallery – Our Work & Team',
            'description' => 'See A2Z Global Maintenance at work: our teams, facility services and projects across Bangalore and Kochi.',
        ],
        'contact' => [
            'label' => 'Contact Us',
            'path' => '/contact',
            'title' => 'Contact Us – Enquiries & Locations',
            'description' => 'Call or send an enquiry to A2Z Global Maintenance. Find our offices in Bangalore and Kochi, with phone, email and map directions.',
        ],
    ],
];
