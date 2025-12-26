<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "type" => $this->type,
            "title" => $this->getTitle($request),
            "body" => $this->getBody($request),
            'redirect_id' => isset($this->data['redirect_id']) ? $this->data['redirect_id'].'' : '',
            'redirect_action' => isset($this->data['redirect_action']) ? $this->data['redirect_action'].'' : '',
            "created_at" => $this->created_at->format('Y-m-d g:i A'),
            "read_at" => $this->read_at ? $this->read_at->format('Y-m-d g:i A') : null,
        ];
    }

    public function getTitle($request)
    {
        $title = isset($this->data['title']) ? $this->data['title'] : 'No Title';
        if ($request->header('lang') == 'ar') {
            $title = isset($this->data['title_ar']) ? $this->data['title_ar'] : $title;
        }
        return $title;
    }

    public function getBody($request)
    {
        $body = isset($this->data['body']) ? $this->data['body'] : 'No body';
        if ($request->header('lang') == 'ar') {
            $body = isset($this->data['body_ar']) ? $this->data['body_ar'] : $body;
        }
        return $body;
    }
}
