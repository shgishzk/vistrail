<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = News::orderByDesc('created_at')->paginate(15);

        return view('admin.news.index', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_public'] = (bool) $validated['is_public'];

        News::create($validated);

        return redirect()->route('admin.news')
            ->with('success', __('News created successfully.'));
    }

    public function edit(News $news): View
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_public'] = (bool) $validated['is_public'];

        $news->update($validated);

        return redirect()->route('admin.news')
            ->with('success', __('News updated successfully.'));
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news')
            ->with('success', __('News deleted successfully.'));
    }
}
