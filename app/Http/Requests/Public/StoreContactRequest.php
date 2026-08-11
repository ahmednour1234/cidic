<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
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
            'email' => 'البريد الإلكتروني',
            'subject' => 'الموضوع',
            'message' => 'الرسالة',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'يرجى إدخال رقم جوال سعودي صحيح (مثال: 0512345678).',
        ];
    }
}
