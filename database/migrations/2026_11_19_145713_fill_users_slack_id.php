<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SLACK_IDS = [
        'mirek@appgo.pl' => 'U9XA35R0T',
        'm.baran@appgo.pl' => 'U07Q2S3BSUU',
        'k.dlugon@appgo.pl' => 'U03FKAUGDS6',
        'wojtek@appgo.pl' => 'U9KTB308J',
        'p.knap@appgo.pl' => 'U06GTG1Q6F7',
        'b.nowak@meatnet.pl' => 'U03AXFQGD3Q',
        'm.jaksan@appgo.pl' => 'U04D9GDKYG3',
        'k.smialowski@appgo.pl' => 'U03JRLVGD09',
        'm.kawa@appgo.pl' => 'U03SPMN4BD4',
        'marcin@appgo.pl' => 'U8CJHHFHR',
        'tomek@appgo.pl' => 'U02BM3H5GJX',
        'k.mazur@appgo.pl' => 'U08914L1ZEC',
        'mariusz@appgo.pl' => 'U01FQEP0H5F',
        'k.leniar@appgo.pl' => 'U036EKTTBHT',
    ];

    public function up(): void
    {
        foreach (self::SLACK_IDS as $email => $slackId) {
            DB::table('users')
                ->where('email', $email)
                ->update(['slack_id' => $slackId]);
        }
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', array_keys(self::SLACK_IDS))
            ->update(['slack_id' => null]);
    }
};
