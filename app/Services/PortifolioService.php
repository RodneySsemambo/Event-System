<?php

namespace App\Services;

use App\Models\PortfolioItem;
use App\Models\ProviderProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PortifolioService
{
    public function addItem(ProviderProfile $provider, UploadedFile $file, ?string $caption = null): PortfolioItem
    {
        $isVideo = str_starts_with($file->getMimeType() ?? '', 'video/');
        $path = $file->store('portfolio', 'public');

        return $provider->portfolioItems()->create([
            'image_path' => $path,
            'media_type' => $isVideo ? 'video' : 'image',
            'caption'    => $caption,
            'sort_order' => ($provider->portfolioItems()->max('sort_order') ?? 0) + 1,
        ]);
    }

    public function updateCaption(PortfolioItem $item, ?string $caption): PortfolioItem
    {
        $item->update(['caption' => $caption]);
        return $item;
    }

    public function reorder(ProviderProfile $provider, array $orderedIds)
    {
        $ownedIds = $provider->portfolioItems()
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        foreach ($orderedIds as $id) {
            if (! in_array((int) $id, $ownedIds, true)) {
                throw ValidationException::withMessages([
                    'order' => ['One or more items do not belong to this provider.'],
                ]);
            }
        }

        foreach ($orderedIds as $index => $id) {
            PortfolioItem::whereKey($id)->update(['sort_order' => $index]);
        }

        return $provider->portfolioItems()->get();
    }

    public function delete(PortfolioItem $item): void
    {
        Storage::disk('public')->delete($item->image_path);
        $item->delete();
    }
}
