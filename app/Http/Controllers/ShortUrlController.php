<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShortUrlRequest;
use App\Models\ShortUrl;
use App\Services\ShortUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortUrlController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ShortUrl::class);

        $user = $request->user();
        $query = ShortUrl::query()->with(['company', 'user'])->latest();

        if (! $user->role->hasPermission('short_urls.view:any')) {
            $query->forCompany($user->company_id);

            if (! $user->role->hasPermission('short_urls.view:company')) {
                $query->createdBy($user->id);
            }
        }

        return view('short_urls.index', [
            'shortUrls' => $query->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ShortUrl::class);

        return view('short_urls.create');
    }

    public function store(StoreShortUrlRequest $request, ShortUrlService $shortUrls): RedirectResponse
    {
        $shortUrl = $shortUrls->create($request->user(), $request->validated('original_url'));

        return redirect()
            ->route('short-urls.show', $shortUrl)
            ->with('status', 'Short URL created.');
    }

    public function show(ShortUrl $shortUrl): View
    {
        $this->authorize('view', $shortUrl);

        return view('short_urls.show', compact('shortUrl'));
    }

    public function redirect(string $shortCode): RedirectResponse
    {
        $shortUrl = ShortUrl::where('short_code', $shortCode)->firstOrFail();
        $scheme = parse_url($shortUrl->original_url, PHP_URL_SCHEME);

        abort_unless(is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true), 404);

        return redirect()->away($shortUrl->original_url, 302);
    }
}
