<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'max:51200', // 50MB — matches video cap
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime',
            ],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.mimetypes' => 'Files must be JPG, PNG, WebP, MP4, WebM, or MOV.',
            'image.max'       => 'Files must be under 50MB.',
        ];
    }
}
