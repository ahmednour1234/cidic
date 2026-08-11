<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesSimpleContent;
use App\Http\Controllers\Controller;
use App\Models\HowItWorks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HowItWorksController extends Controller
{
    use ManagesSimpleContent;

    protected function modelClass(): string
    {
        return HowItWorks::class;
    }

    protected function viewPath(): string
    {
        return 'admin.how-it-works';
    }

    protected function routeName(): string
    {
        return 'admin.how-it-works';
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:100'],
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
