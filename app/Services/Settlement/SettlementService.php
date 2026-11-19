<?php

namespace App\Services\Settlement;

use App\Models\Notification;
use App\Models\Settlement;
use App\Models\SettlementItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettlementService
{
    public function createDebt(User $creditor, User $debtor, float $amount, string $place): Settlement
    {
        return DB::transaction(function () use ($creditor, $debtor, $amount, $place) {
            $settlement = Settlement::create([
                'uuid' => substr(Str::uuid()->toString(), 0, 8),
                'restaurant_name' => $place,
                'date' => now()->toDateString(),
                'user_id' => $creditor->id,
            ]);

            SettlementItem::create([
                'settlement_id' => $settlement->id,
                'user_id' => $debtor->id,
                'amount' => $amount,
                'discounted_amount' => $amount,
                'final_amount' => $amount,
                'created_by' => $creditor->id,
                'status' => 'unpaid',
            ]);

            Notification::create([
                'title' => 'Nowe zamówienie do zapłaty',
                'message' => $creditor->name.' utworzył nowe zamówienie w restauracji '.$settlement->restaurant_name.' w której zamawiałeś',
                'user_id' => $debtor->id,
                'route' => 'settlements.show',
                'route_params' => ['settlement' => $settlement->id],
                'read' => false,
            ]);

            return $settlement;
        });
    }
}
