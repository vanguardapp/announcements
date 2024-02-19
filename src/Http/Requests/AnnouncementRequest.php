<?php

namespace Vanguard\Announcements\Http\Requests;

use Vanguard\Http\Requests\Request;

class AnnouncementRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:150',
            'body' => 'required|max:1500',
            'email_notifications' => 'boolean',
        ];
    }
}
