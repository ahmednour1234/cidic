<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact.index', [
            'messages' => $messages,
            'statusOptions' => ContactMessageStatus::options(),
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        // Opening an unread message marks it read.
        if ($contactMessage->status === ContactMessageStatus::New) {
            $contactMessage->update(['status' => ContactMessageStatus::Read]);
        }

        return view('admin.contact.show', [
            'message' => $contactMessage,
            'statusOptions' => ContactMessageStatus::options(),
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ContactMessageStatus::values())],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $contactMessage->update($validated);

        return back()->with('success', 'تم تحديث حالة الرسالة.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'تم حذف الرسالة.');
    }
}
