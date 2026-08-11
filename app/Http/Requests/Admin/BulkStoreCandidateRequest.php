<?php

namespace App\Http\Requests\Admin;

use App\Enums\AvailabilityStatus;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreCandidateRequest extends FormRequest
{
    /** Upper bound per batch, kept in step with the view's hint. */
    public const MAX_FILES = 50;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission(Permission::ManageCandidates) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // The CV is the only source of the candidate's name, so it is required.
            'cv_files' => ['required', 'array', 'min:1', 'max:' . self::MAX_FILES],
            'cv_files.*' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],

            'nationality_id' => ['required', 'integer', 'exists:nationalities,id'],
            'candidate_category_id' => ['required', 'integer', 'exists:candidate_categories,id'],

            // Optional shared defaults applied to every candidate in the batch.
            'availability_status' => ['nullable', Rule::in(AvailabilityStatus::values())],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'language_arabic' => ['nullable', Rule::in(['none', 'basic', 'good', 'fluent'])],
            'language_english' => ['nullable', Rule::in(['none', 'basic', 'good', 'fluent'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cv_files' => 'ملفات السير الذاتية',
            'cv_files.*' => 'ملف السيرة الذاتية',
            'nationality_id' => 'الجنسية',
            'candidate_category_id' => 'التصنيف',
            'availability_status' => 'حالة التوفر',
            'years_of_experience' => 'سنوات الخبرة',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cv_files.required' => 'يجب اختيار ملف سيرة ذاتية واحد على الأقل.',
            'cv_files.max' => 'الحد الأقصى ' . self::MAX_FILES . ' ملف في المرة الواحدة.',
            'cv_files.*.mimes' => 'يجب أن تكون جميع الملفات بصيغة PDF.',
            'cv_files.*.mimetypes' => 'يجب أن تكون جميع الملفات بصيغة PDF.',
            'cv_files.*.max' => 'حجم كل ملف يجب ألا يتجاوز 10 ميجابايت.',
        ];
    }
}
