<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecruitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'mobile' => ['required', 'string', 'max:32', 'regex:/^(?:\+?966|0)?5\d{8}$/'],
            'whatsapp' => ['nullable', 'string', 'max:32', 'regex:/^(?:\+?966|0)?5\d{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            // exists() keeps unknown ids out without a manual query.
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'nationality_id' => ['nullable', 'integer', 'exists:nationalities,id'],
            'candidate_category_id' => ['nullable', 'integer', 'exists:candidate_categories,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'mobile' => 'رقم الجوال',
            'whatsapp' => 'رقم الواتساب',
            'email' => 'البريد الإلكتروني',
            'city' => 'المدينة',
            'service_id' => 'الخدمة',
            'nationality_id' => 'الجنسية',
            'candidate_category_id' => 'تصنيف العمالة',
            'notes' => 'ملاحظات',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'يرجى إدخال رقم جوال سعودي صحيح (مثال: 0512345678).',
            'whatsapp.regex' => 'يرجى إدخال رقم واتساب سعودي صحيح (مثال: 0512345678).',
        ];
    }
}
