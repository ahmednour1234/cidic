<?php

namespace App\Http\Controllers;

use App\Enums\ContactMessageStatus;
use App\Http\Requests\Public\StoreContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        ContactMessage::create([
            ...$request->validated(),
            'status' => ContactMessageStatus::New,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return redirect()
            ->route('contact.create')
            ->with('success', 'تم إرسال رسالتك بنجاح وسيتم التواصل معك قريباً.');
    }
}
