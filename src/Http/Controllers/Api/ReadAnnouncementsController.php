<?php

namespace Vanguard\Announcements\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;

class ReadAnnouncementsController extends ApiController
{
    /**
     * Update the timestamp when announcements were last read
     * by the currently authenticated user.
     *
     * @return JsonResponse
     */
    public function index()
    {
        auth()->user()->forceFill([
            'announcements_last_read_at' => now(),
        ])->save();

        return $this->respondWithSuccess();
    }
}
