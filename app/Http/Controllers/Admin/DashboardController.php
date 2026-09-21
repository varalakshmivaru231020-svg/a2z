<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\GalleryItem;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Service;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'services' => Service::count(),
                'open_jobs' => JobOpening::open()->count(),
                'new_applications' => JobApplication::where('status', 'new')->count(),
                'unread_enquiries' => Enquiry::unread()->count(),
                'photos' => GalleryItem::count(),
            ],
            'applications' => JobApplication::latest()->limit(6)->get(),
            'enquiries' => Enquiry::latest()->limit(6)->get(),
        ]);
    }
}
