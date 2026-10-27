<?php

namespace App\Http\Controllers;

use App\Http\Requests\Provider\StorePortfolioItemRequest;
use App\Models\PortfolioItem;
use App\Models\ProviderProfile;
use App\Services\PortifolioService;
use Illuminate\Http\Request;

class PortfolioItemController extends Controller
{
    public function __construct(private PortifolioService $portifolio) {}

    public function store(StorePortfolioItemRequest $request, ProviderProfile $provider)
    {
        $this->authorize('update', $provider);

        $item = $this->portifolio->addItem(
            $provider,
            $request->file('image'),
            $request->validated('caption')
        );

        return response()->json($item, 201);
    }

    public function update(Request $request, PortfolioItem $portfolioItem)
    {
        $this->authorize('manage', $portfolioItem);

        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(
            $this->portifolio->updateCaption($portfolioItem, $validated['caption'] ?? null)
        );
    }

    public function reorder(Request $request, ProviderProfile $provider)
    {
        $this->authorize('update', $provider);

        $validated = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:portfolio_items,id'],
        ]);

        return response()->json($this->portifolio->reorder($provider, $validated['order']));
    }

    public function destroy(PortfolioItem $portfolioItem)
    {
        $this->authorize('manage', $portfolioItem);

        $this->portifolio->delete($portfolioItem);

        return response()->json(['message' => 'Deleted']);
    }
}
