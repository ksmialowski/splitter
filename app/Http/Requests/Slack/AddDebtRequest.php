<?php

namespace App\Http\Requests\Slack;

use App\Http\Responses\Slack\EphemeralMessage;
use App\Models\User;
use App\Services\Slack\InvalidSplitterCommand;
use App\Services\Slack\SplitterCommand;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddDebtRequest extends FormRequest
{
    private SplitterCommand $command;

    private User $creditor;

    private User $debtor;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string'],
            'user_id' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                try {
                    $this->command = SplitterCommand::fromText((string) $this->input('text'));
                } catch (InvalidSplitterCommand $e) {
                    $validator->errors()->add(
                        'text',
                        $e->getMessage()."\nUżycie: `/splitter @osoba kwota miejsce`, np. `/splitter @Marcin 15zl Lidl`"
                    );

                    return;
                }

                if ($this->command->debtorSlackId === $this->input('user_id')) {
                    $validator->errors()->add('text', 'Nie możesz dopisać zamówienia samemu sobie.');

                    return;
                }

                $creditor = User::where('slack_id', $this->input('user_id'))->first();

                if (! $creditor) {
                    $validator->errors()->add('user_id', 'Twoje konto Slack nie jest połączone z kontem w Splitterze.');

                    return;
                }

                $debtor = User::where('slack_id', $this->command->debtorSlackId)->first();

                if (! $debtor) {
                    $validator->errors()->add('text', sprintf(
                        'Konto Slack <@%s> nie jest połączone z kontem w Splitterze.',
                        $this->command->debtorSlackId,
                    ));

                    return;
                }

                $this->creditor = $creditor;
                $this->debtor = $debtor;
            },
        ];
    }

    public function command(): SplitterCommand
    {
        return $this->command;
    }

    public function creditor(): User
    {
        return $this->creditor;
    }

    public function debtor(): User
    {
        return $this->debtor;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            (new EphemeralMessage($validator->errors()->first()))->toResponse($this)
        );
    }
}
