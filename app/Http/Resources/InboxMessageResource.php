<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InboxMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'patients' => $this->formatPatients(),
            'latest_message' => [
                'message' => $this->latestMessage->message ?? null,
                'created_at' => $this->latestMessage->formatted_time ?? null,
                'message_direction' => $this->latestMessage->message_direction ?? null,
                'mobile_number' => $this->latestMessage->mobile_number ?? null,
            ],
            'mobile_number' => $this->mobile_number,
            'unread_count' => $this->unread_messages_count,
        ];
    }

    private function formatPatients(): array
    {
        return $this->patients->map(function ($patient) {
            return [
                'id' => $patient->id,
                'name' => $patient->name,
                'mobile_number' => $patient->mobile_number,
                'dob' => $patient->formatted_dob,
            ];
        })->toArray();
    }
}
