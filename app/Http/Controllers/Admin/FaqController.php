<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesSimpleContent;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ManagesSimpleContent;

    protected function modelClass(): string
    {
        return Faq::class;
    }

    protected function viewPath(): string
    {
        return 'admin.faqs';
    }

    protected function routeName(): string
    {
        return 'admin.faqs';
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function transform(array $data, Request $request): array
    {
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
