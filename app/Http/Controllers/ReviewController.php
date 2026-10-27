<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\Booking;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews) {}

    public function store(StoreReviewRequest $request, Booking $booking)
    {
        $this->authorize('review', $booking);
        $review = $this->reviews->create($booking, $request->validated());
        return response()->json($review, 201);
    }
}
