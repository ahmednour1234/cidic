<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateCustomerRequest extends FormRequest
{
    /** Guests may submit; no account required. */
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
            'customer_name' => ['required', 'string', 'min:3', 'max:150'],
            // Saudi mobile: 05xxxxxxxx, +9665xxxxxxxx or 9665xxxxxxxx.
            'mobile' => ['required', 'string', 'max:32', 'regex:/^(?:\+?966|0)?5\d{8}$/'],
            'whatsapp' => ['nullable', 'string', 'max:32', 'regex:/^(?:\+?966|0)?5\d{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            'service_type' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'الاسم',
            'mobile' => 'رقم الجوال',
            'whatsapp' => 'رقم الواتساب',
            'email' => 'البريد الإلكتروني',
            'city' => 'المدينة',
            'service_type' => 'نوع الخدمة',
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
