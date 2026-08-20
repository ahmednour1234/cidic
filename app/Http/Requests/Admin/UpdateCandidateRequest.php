<?php

namespace App\Http\Requests\Admin;

/**
 * Update shares the store rules; uploads stay optional so an edit that omits a
 * file keeps the existing one.
 */
class UpdateCandidateRequest extends StoreCandidateRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // A CV is mandatory to publish, but an edit that does not touch the
        // upload must keep the stored file rather than demanding it again.
        // It is only required here when the record has none yet.
        $rules['cv_file'] = $this->route('candidate')?->cv_file
            ? ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240']
            : ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'];

        return $rules;
    }
}
