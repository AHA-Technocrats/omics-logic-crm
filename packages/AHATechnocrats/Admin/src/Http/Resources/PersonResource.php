<?php

namespace AHATechnocrats\Admin\Http\Resources;

use AHATechnocrats\OmicsLogic\Support\LeadScoreBadge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request
     * @return array
     */
    public function toArray($request)
    {
        $leadScore = (int) ($this->lead_score ?? 0);
        $scoreBand = $this->score_band ?? null;
        $breakdown = LeadScoreBadge::breakdownFromPerson($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'emails' => $this->emails,
            'contact_numbers' => $this->contact_numbers,
            'organization' => $this->organization ? new OrganizationResource($this->organization) : null,
            'lead_score' => $leadScore,
            'score_band' => $scoreBand,
            'score_badge' => LeadScoreBadge::meta($leadScore, $scoreBand),
            'score_breakdown' => $breakdown,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
