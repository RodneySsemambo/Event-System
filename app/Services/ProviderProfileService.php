<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProviderProfileService
{
    public function update(ProviderProfile $provider, array $data, ?UploadedFile $photo, ?UploadedFile $banner): ProviderProfile
    {
        if ($photo) {
            $this->replaceFile($provider, 'profile_photo_path', $photo, 'providers/photos');
        }

        if ($banner) {
            $this->replaceFile($provider, 'banner_path', $banner, 'providers/banners');
        }

        $provider->fill($data);
        $provider->save();

        return $provider->fresh(['category', 'portfolioItems']);
    }

    public function replaceFile(ProviderProfile $provider, string $column, UploadedFile $file, string $directory): void
    {
        if ($provider->{$column}) {
            Storage::disk('public')->delete($provider->{$column});
        }

        $provider->{$column} = $file->store($directory, 'public');
    }
}
