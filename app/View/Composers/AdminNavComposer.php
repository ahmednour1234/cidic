<?php

namespace App\View\Composers;

use App\Models\CandidateRequest;
use App\Models\ContactMessage;
use App\Models\RecruitmentRequest;
use Illuminate\View\View;

class AdminNavComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'newRequestsCount' => CandidateRequest::query()->new()->count()
                + RecruitmentRequest::query()->new()->count(),
            'newMessagesCount' => ContactMessage::query()->new()->count(),
        ]);
    }
}
