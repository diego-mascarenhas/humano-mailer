<?php

namespace Idoneo\HumanoMailer\Commands;

use Idoneo\HumanoMailer\Jobs\SendMessageCampaignJob;
use Idoneo\HumanoMailer\Models\MessageDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'humano-mailer:send-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all pending MessageDelivery records (sent_at null) using associated template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Processing pending message deliveries...');

        $pendings = MessageDelivery::whereNull('sent_at')
            ->with(['contact', 'message.template', 'team'])
            ->get();

        if ($pendings->isEmpty()) {
            $this->info('📭 No pending deliveries found.');
            return 0;
        }

        $this->info("📧 Found {$pendings->count()} pending deliveries");

        $sent = 0;
        $errors = 0;
        $delay = 0;

        foreach ($pendings as $delivery)
        {
            if (!$delivery->contact || !$delivery->contact->email)
            {
                $this->warn("⚠️  No email for delivery ID: {$delivery->id}");
                $errors++;
                continue;
            }

            // Check that the message is active
            if (!$delivery->message || $delivery->message->status_id != 1)
            {
                $this->warn("⚠️  Inactive message for delivery ID: {$delivery->id}");
                $errors++;
                continue;
            }

            // Check that team exists
            if (!$delivery->team)
            {
                $this->warn("⚠️  No team for delivery ID: {$delivery->id}");
                $errors++;
                continue;
            }

            try
            {
                // Use configurable delays
                $maxRandomSeconds = config('humano-mailer.delays.random_seconds', 120);
                $randomDelay = rand(60, max(300, $maxRandomSeconds)); // Min 60s, configurable max

                // Dispatch the job with delay
                SendMessageCampaignJob::dispatch($delivery)
                    ->onQueue('mailer')
                    ->delay(now()->addSeconds($delay));

                $this->info("✅ Queued job for: {$delivery->contact->email} (delay: {$delay}s, team: {$delivery->team->name})");
                $sent++;
                $delay += $randomDelay;

            } catch (\Exception $e)
            {
                $this->error("❌ Error queueing job for {$delivery->contact->email}: {$e->getMessage()}");
                $delivery->markAsError($e->getMessage());
                $errors++;
            }
        }

        $this->info("🎉 Total jobs queued: {$sent}");
        $this->info("❌ Total errors: {$errors}");

        Log::info('📊 SendPendingMessages completed', [
            'queued' => $sent,
            'errors' => $errors,
        ]);

        return 0;
    }
}
