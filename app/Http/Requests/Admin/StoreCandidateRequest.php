<?php

namespace App\Http\Requests\Admin;

use App\Enums\AvailabilityStatus;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidateRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'nationality_id' => ['required', 'integer', 'exists:nationalities,id'],
            'candidate_category_id' => ['required', 'integer', 'exists:candidate_categories,id'],

            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'min:18', 'max:70'],
            'gender' => ['required', Rule::in(['female', 'male'])],
            'religion' => ['nullable', 'string', 'max:64'],
            'marital_status' => ['nullable', 'string', 'max:32'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'education' => ['nullable', 'string', 'max:128'],

            'profession' => ['required', 'string', 'max:150'],
            'years_of_experience' => ['required', 'integer', 'min:0', 'max:50'],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'contract_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],

            'language_arabic' => ['required', Rule::in(['none', 'basic', 'good', 'fluent'])],
            'language_english' => ['required', Rule::in(['none', 'basic', 'good', 'fluent'])],
            'other_languages' => ['nullable', 'string', 'max:255'],

            'skills' => ['nullable', 'string', 'max:1000'],
            'previous_countries' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],

            // Content-type and size are validated, not just the extension.
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'intro_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:51200'],

            'availability_status' => ['required', Rule::in(AvailabilityStatus::values())],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Unchecked checkboxes are absent from the payload; normalise to booleans.
        $this->merge([
            'featured' => $this->boolean('featured'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'nationality_id' => 'الجنسية',
            'candidate_category_id' => 'التصنيف',
            'date_of_birth' => 'تاريخ الميلاد',
            'age' => 'العمر',
            'gender' => 'الجنس',
            'profession' => 'المهنة',
            'years_of_experience' => 'سنوات الخبرة',
            'salary' => 'الراتب',
            'contract_price' => 'قيمة العقد',
            'language_arabic' => 'اللغة العربية',
            'language_english' => 'اللغة الإنجليزية',
            'profile_image' => 'الصورة الشخصية',
            'cv_file' => 'ملف السيرة الذاتية',
            'intro_video' => 'الفيديو التعريفي',
            'availability_status' => 'حالة التوفر',
        ];
    }
}
