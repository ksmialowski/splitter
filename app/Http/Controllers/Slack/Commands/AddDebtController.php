<?php

namespace App\Http\Controllers\Slack\Commands;

use App\Http\Controllers\Controller;
use App\Http\Requests\Slack\AddDebtRequest;
use App\Http\Responses\Slack\EphemeralMessage;
use App\Services\Settlement\SettlementService;

class AddDebtController extends Controller
{
    public function __invoke(AddDebtRequest $request, SettlementService $settlements): EphemeralMessage
    {
        $command = $request->command();

        $settlement = $settlements->createDebt(
            $request->creditor(),
            $request->debtor(),
            $command->amount,
            $command->place,
        );

        return new EphemeralMessage(sprintf(
            'Dodano rozliczenie: <@%s> wisi Ci %s zł (%s). <%s|Zobacz w Splitterze>',
            $command->debtorSlackId,
            number_format($command->amount, 2, ',', ' '),
            $command->place,
            route('settlements.show', $settlement),
        ));
    }
}
