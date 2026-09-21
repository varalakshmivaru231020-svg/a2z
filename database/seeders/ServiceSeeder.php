<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * The service list carried over from the company brochure, grouped into four categories.
 * The wording is starter copy — the owner can edit every service from the admin panel.
 * Safe to re-run: services are matched by slug and only created if missing.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $order = 0;

        foreach ($this->services() as $service) {
            $order += 10;
            $slug = Service::uniqueSlug($service['title']);

            // Re-running the seeder must not overwrite edits made in the admin panel.
            if (Service::where('slug', \Illuminate\Support\Str::slug($service['title']))->exists()) {
                continue;
            }

            Service::create([
                'category' => $service['category'],
                'title' => $service['title'],
                'slug' => $slug,
                'summary' => $service['summary'],
                'description' => implode("\n\n", $service['description']),
                'icon' => $service['icon'],
                'features' => $service['features'],
                'is_featured' => $service['featured'] ?? false,
                'is_active' => true,
                'sort_order' => $order,
            ]);
        }
    }

    private function services(): array
    {
        return [
            // ---------------------------------------------------------- Facility management
            [
                'category' => 'facility', 'title' => 'House Keeping', 'icon' => 'sparkles', 'featured' => true,
                'summary' => 'Trained housekeeping staff who keep offices, schools, apartments and commercial spaces clean, tidy and hygienic every day.',
                'description' => [
                    'A clean workplace is the first thing people notice. Our housekeeping teams take care of daily and periodic cleaning so your premises always look cared for, whether it is an office floor, a school, an apartment complex or a commercial building.',
                    'Our staff are trained and supervised, and we plan shifts around your working hours so cleaning never gets in the way of your day.',
                ],
                'features' => ['Daily and periodic cleaning', 'Restroom, floor and glass cleaning', 'Trained and supervised housekeepers', 'Shifts planned around your working hours'],
            ],
            [
                'category' => 'facility', 'title' => 'Office Boy', 'icon' => 'user',
                'summary' => 'Dependable office boys for errands, file handling, tea service and everyday support in your workplace.',
                'description' => [
                    'Every office runs better with someone who takes care of the small things. Our office boys handle errands, move files and documents, serve tea and water, and help with day-to-day tasks so your team can stay focused on work.',
                    'We select punctual, well-mannered people and provide replacements so your office is never left without support.',
                ],
                'features' => ['Errands, courier and document handling', 'Tea, water and guest service', 'Punctual, well-mannered staff', 'Replacement cover when someone is absent'],
            ],
            [
                'category' => 'facility', 'title' => 'Pantry Boy', 'icon' => 'coffee',
                'summary' => 'Pantry staff who keep your pantry stocked, spotless and ready to serve teams and visitors.',
                'description' => [
                    'Our pantry boys look after your office pantry from start to finish — preparing and serving beverages, keeping the space clean and making sure supplies are ready when your team and visitors need them.',
                    'Neat, hygienic and courteous service makes a real difference to how your office feels.',
                ],
                'features' => ['Tea, coffee and beverage service', 'Pantry cleaning and upkeep', 'Stock monitoring and refilling', 'Courteous service for visitors'],
            ],
            [
                'category' => 'facility', 'title' => 'Swimming Pool Maintenance', 'icon' => 'waves', 'featured' => true,
                'summary' => 'Regular cleaning and upkeep for swimming pools in apartments, clubs and commercial properties.',
                'description' => [
                    'A swimming pool needs steady, careful attention to stay safe and inviting. We provide regular pool cleaning and upkeep for apartment communities, clubs and commercial properties.',
                    'Tell us about your pool and how often it is used, and we will suggest a maintenance routine that fits.',
                ],
                'features' => ['Regular pool cleaning', 'Water and surface upkeep', 'Poolside area cleaning', 'Maintenance schedule to suit your usage'],
            ],
            [
                'category' => 'facility', 'title' => 'Other Maintenance', 'icon' => 'home',
                'summary' => 'General upkeep and minor repairs for your premises, handled by one team so nothing slips through the cracks.',
                'description' => [
                    'Beyond cleaning and specialist trades, every building needs a steady stream of small jobs done well. We take care of general maintenance and minor repairs so you have a single team to call.',
                    'If you need something that is not listed on this website, contact us — chances are we can help or point you to the right team.',
                ],
                'features' => ['Day-to-day upkeep and minor repairs', 'One point of contact for your premises', 'Support for offices, schools and residences', 'Flexible, on-request service'],
            ],

            // ------------------------------------------------------- Maintenance & renovation
            [
                'category' => 'maintenance', 'title' => 'Plumbing', 'icon' => 'wrench', 'featured' => true,
                'summary' => 'Prompt plumbing repairs, fittings and installations for homes, offices and commercial buildings.',
                'description' => [
                    'From a dripping tap to a full bathroom fit-out, our plumbers handle repairs, fittings and installations with care for your fixtures and floors.',
                    'We aim to fix the problem properly the first time, and to leave the area clean when we are done.',
                ],
                'features' => ['Leak detection and repair', 'Tap, sink and sanitary fittings', 'Pipe and water line work', 'Clean, tidy finish'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Electrical', 'icon' => 'zap', 'featured' => true,
                'summary' => 'Safe, tidy electrical repairs, wiring, fittings and fault-finding for homes, offices and commercial buildings.',
                'description' => [
                    'Our electricians take care of repairs, wiring, switchboards, lighting and fittings, and track down faults quickly so your premises stay safe and running.',
                    'Safety comes first on every job — we work neatly and explain what we have done.',
                ],
                'features' => ['Wiring, switches and switchboards', 'Lighting and fan fittings', 'Fault-finding and repairs', 'Neat, safe workmanship'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Carpentry', 'icon' => 'pencil',
                'summary' => 'Custom carpentry, repairs and fittings — from doors and wardrobes to office partitions and furniture.',
                'description' => [
                    'Whether you need a new wardrobe, a repaired door or office furniture made to measure, our carpenters combine practical skill with a careful finish.',
                    'We work from your requirements and measurements, and keep you informed as the job progresses.',
                ],
                'features' => ['Doors, windows and frames', 'Wardrobes, shelves and cabinets', 'Office partitions and furniture', 'Repairs and refinishing'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Painting', 'icon' => 'roller', 'featured' => true,
                'summary' => 'Interior and exterior painting with careful preparation and a clean, even finish for homes and offices.',
                'description' => [
                    'A good paint job starts with good preparation. Our painters protect your furniture and floors, prepare the surfaces properly and finish with an even, lasting coat.',
                    'We handle homes, offices, shops and commercial buildings, indoors and out.',
                ],
                'features' => ['Interior and exterior painting', 'Surface preparation and putty work', 'Furniture and floor protection', 'Clean-up after the job'],
            ],
            [
                'category' => 'maintenance', 'title' => 'False Ceiling', 'icon' => 'layers',
                'summary' => 'Neat false ceiling design and installation that improves the look, lighting and finish of any room.',
                'description' => [
                    'A well-made false ceiling transforms a room — hiding wiring, improving lighting and giving the space a finished look.',
                    'We install false ceilings in homes, offices and commercial spaces, coordinating with lighting and electrical work so everything fits together.',
                ],
                'features' => ['False ceiling design and installation', 'Lighting and electrical coordination', 'Clean, level finish', 'Homes, offices and commercial spaces'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Aluminium Fabrication', 'icon' => 'frame',
                'summary' => 'Aluminium windows, doors, partitions and frames, fabricated and fitted to your measurements.',
                'description' => [
                    'Aluminium is durable, low-maintenance and looks sharp. We fabricate and fit aluminium windows, doors, partitions and frames made to your measurements.',
                    'From site measurement to final fitting, we handle the whole job.',
                ],
                'features' => ['Windows and sliding doors', 'Office partitions', 'Made to measure', 'Site measurement and fitting'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Tile Polishing', 'icon' => 'grid',
                'summary' => 'Professional tile polishing to bring back the shine of worn, stained or dull tiled floors.',
                'description' => [
                    'Tiled floors lose their shine over time. Our polishing service removes surface dullness and stains and brings back a fresh, clean finish without the cost of replacing the floor.',
                    'We work in homes, offices, shops and commercial spaces.',
                ],
                'features' => ['Removes dullness and surface stains', 'Homes, offices and shops', 'Cost-effective alternative to replacement', 'Clean, tidy working'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Marble Polishing', 'icon' => 'grid',
                'summary' => 'Marble polishing and restoration that removes dullness and stains and revives a mirror-like finish.',
                'description' => [
                    'Marble is beautiful but shows wear. Our polishing and restoration work removes scratches, dullness and stains and revives the natural depth of the stone.',
                    'We handle floors, steps and surfaces in homes, offices and commercial buildings.',
                ],
                'features' => ['Floor, step and surface polishing', 'Stain and scratch removal', 'Restores a mirror-like shine', 'Homes and commercial buildings'],
            ],
            [
                'category' => 'maintenance', 'title' => 'Glass Polishing', 'icon' => 'window',
                'summary' => 'Glass polishing and cleaning for windows, partitions and façades — streak-free and crystal clear.',
                'description' => [
                    'Clear glass makes a building look bright and well kept. We polish and clean windows, partitions and glass façades, removing marks and build-up for a streak-free finish.',
                    'We work safely at height and plan around your operating hours.',
                ],
                'features' => ['Windows, partitions and façades', 'Removes marks and build-up', 'Streak-free finish', 'Planned around your operating hours'],
            ],

            // ------------------------------------------------------------ Government projects
            [
                'category' => 'government', 'title' => 'BWSSB Projects', 'icon' => 'droplet', 'featured' => true,
                'summary' => 'Support for BWSSB projects in Bangalore, including Kaveri water line sanctions and related work.',
                'description' => [
                    'We undertake government projects with the Bangalore Water Supply and Sewerage Board (BWSSB), including Kaveri water line sanctions.',
                    'If you need help with a water connection or a related BWSSB requirement, contact us with the details of your property and we will explain how we can help.',
                ],
                'features' => ['Kaveri water line sanctions', 'Government project support', 'Guidance for property owners and builders', 'Experienced, dependable team'],
            ],
            [
                'category' => 'government', 'title' => 'Sewerage Service', 'icon' => 'droplet',
                'summary' => 'Sewerage services for properties and projects across Bangalore, including work linked to the Bangalore Water Supply and Sewerage Board.',
                'description' => [
                    'Sewerage work has to be done right, and in line with the rules. We provide sewerage services for properties and projects across Bangalore, including work connected with the Bangalore Water Supply and Sewerage Board (BWSSB).',
                    'Contact us with your requirement and we will advise on the next steps.',
                ],
                'features' => ['Sewerage services for properties and projects', 'Work linked to BWSSB requirements', 'Residential and commercial', 'Clear guidance on next steps'],
            ],

            // ------------------------------------------------------- Financial & field services
            [
                'category' => 'field', 'title' => 'Banking Services', 'icon' => 'landmark', 'featured' => true,
                'summary' => 'Banking-related support and business services for financial institutions and their customers.',
                'description' => [
                    'We provide banking-sector support services that help financial institutions reach and serve their customers, backed by trained people who represent your brand well.',
                    'Tell us what your institution needs and we will put together the right team.',
                ],
                'features' => ['Support for banks and financial institutions', 'Trained, presentable teams', 'Customer outreach and assistance', 'Flexible engagement'],
            ],
            [
                'category' => 'field', 'title' => 'Sales', 'icon' => 'chart',
                'summary' => 'Sales executives and teams who promote and sell your products and services, on the ground and in the market.',
                'description' => [
                    'Good sales people make the difference between a product that sits and one that moves. We provide sales executives and teams trained to present your offer clearly and follow through with customers.',
                    'We work with you to agree targets, territories and reporting.',
                ],
                'features' => ['Sales executives and teams', 'Trained to present your offer', 'Agreed targets and reporting', 'Flexible team size'],
            ],
            [
                'category' => 'field', 'title' => 'Tele Marketing', 'icon' => 'phone',
                'summary' => 'Tele-marketing teams that reach your prospects by phone, explain your offer clearly and follow up.',
                'description' => [
                    'Our tele-marketing callers reach out to prospects on your behalf, explain your products and services in a clear, polite way and follow up on interest.',
                    'We agree call scripts and reporting with you so you always know what is happening.',
                ],
                'features' => ['Outbound calling teams', 'Clear, polite communication', 'Follow-up on interested prospects', 'Regular reporting'],
            ],
            [
                'category' => 'field', 'title' => 'Field Marketing', 'icon' => 'megaphone', 'featured' => true,
                'summary' => 'Field marketing teams that take your brand to customers — on the ground, face to face.',
                'description' => [
                    'Some customers are best reached in person. Our field marketing teams take your brand and offers to the places where your customers are and start real conversations.',
                    'We plan routes, locations and targets with you and report back on results.',
                ],
                'features' => ['Face-to-face customer outreach', 'Location and route planning', 'Trained, presentable teams', 'Reporting on results'],
            ],
            [
                'category' => 'field', 'title' => 'Field Executives', 'icon' => 'users',
                'summary' => 'Trained field executives who represent your business on the ground — customer visits, verification and follow-ups.',
                'description' => [
                    'Field executives are your eyes, ears and voice outside the office. We provide trained people for customer visits, verification and follow-ups, supervised and reporting to you.',
                    'Tell us what tasks you need covered and in which areas, and we will build the team.',
                ],
                'features' => ['Customer visits and follow-ups', 'On-ground verification', 'Supervised teams', 'Coverage across Bangalore and nearby areas'],
            ],
            [
                'category' => 'field', 'title' => 'Field Delivery', 'icon' => 'truck',
                'summary' => 'Field delivery staff for last-mile delivery, including sub-contract delivery work for Amazon.',
                'description' => [
                    'Reliable delivery is all about people who show up and take care. We provide field delivery staff for last-mile deliveries, including sub-contract delivery work for Amazon.',
                    'Our teams are supervised, and we plan capacity around your volumes.',
                ],
                'features' => ['Last-mile delivery staff', 'Sub-contract delivery work for Amazon', 'Supervised teams', 'Capacity planned around your volumes'],
            ],
        ];
    }
}
