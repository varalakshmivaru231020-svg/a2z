<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter');

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::query()
                ->when($filter === 'unread', fn ($q) => $q->unread())
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'filter' => $filter,
            'unreadCount' => Enquiry::unread()->count(),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        if (! $enquiry->isRead()) {
            $enquiry->update(['read_at' => now()]);
        }

        return view('admin.enquiries.show', ['enquiry' => $enquiry]);
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('status', 'Enquiry deleted.');
    }
}
