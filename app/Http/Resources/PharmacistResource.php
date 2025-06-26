<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $totalMinutes = $this->sessionPairs->sum(function ($pair) {
            if ($pair->login && $pair->logout) {
                $login  = Carbon::parse($pair->login->occurred_at);
                $logout = Carbon::parse($pair->logout->occurred_at);
                return abs(
                    $logout->diffInMinutes($login, false)
                );
            }
            return 0;
        });

        $hoursWorked = round($totalMinutes / 60, 2);
        $salary = $this->hourly_rate ? round($hoursWorked * $this->hourly_rate, 2) : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'hourly_rate' => $this->hourly_rate,
            'hours_worked' => $hoursWorked,
            'salary' => $salary,
        ];
    }
}
