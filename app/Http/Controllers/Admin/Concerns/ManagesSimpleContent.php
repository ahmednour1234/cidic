<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shared CRUD for the simple, sortable content blocks (how-it-works,
 * why-choose-us, testimonials, FAQs). Each consumer supplies its model,
 * view directory, route name and validation rules.
 */
trait ManagesSimpleContent
{
    abstract protected function modelClass(): string;

    abstract protected function viewPath(): string;

    abstract protected function routeName(): string;

    /** @return array<string, mixed> */
    abstract protected function rules(Request $request, ?Model $record = null): array;

    protected function labels(): array
    {
        return [
            'created' => 'تمت الإضافة بنجاح.',
            'updated' => 'تم التحديث بنجاح.',
            'deleted' => 'تم الحذف بنجاح.',
        ];
    }

    /** Hook for subclasses to post-process validated input. */
    protected function transform(array $data, Request $request): array
    {
        return $data;
    }

    public function index(): View
    {
        $model = $this->modelClass();

        return view($this->viewPath() . '.index', [
            'records' => $model::query()->ordered()->paginate(20),
        ]);
    }

    public function create(): View
    {
        $model = $this->modelClass();

        return view($this->viewPath() . '.create', ['record' => new $model()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $model = $this->modelClass();
        $data = $this->transform($request->validate($this->rules($request)), $request);

        $model::create($data);

        return redirect()
            ->route($this->routeName() . '.index')
            ->with('success', $this->labels()['created']);
    }

    /**
     * Resolve the bound record by id.
     *
     * The route parameter is not type-hinted as a concrete model because this
     * trait is shared; Laravel cannot autowire the abstract Model class, so the
     * lookup is explicit.
     */
    protected function findRecord(int|string $id): Model
    {
        $model = $this->modelClass();

        return $model::query()->findOrFail($id);
    }

    public function edit(int|string $record): View
    {
        return view($this->viewPath() . '.edit', ['record' => $this->findRecord($record)]);
    }

    public function update(Request $request, int|string $record): RedirectResponse
    {
        $model = $this->findRecord($record);
        $data = $this->transform($request->validate($this->rules($request, $model)), $request);

        $model->update($data);

        return redirect()
            ->route($this->routeName() . '.index')
            ->with('success', $this->labels()['updated']);
    }

    public function destroy(int|string $record): RedirectResponse
    {
        $this->findRecord($record)->delete();

        return redirect()
            ->route($this->routeName() . '.index')
            ->with('success', $this->labels()['deleted']);
    }
}
