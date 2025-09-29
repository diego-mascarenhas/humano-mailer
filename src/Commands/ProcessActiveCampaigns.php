<?php

namespace Idoneo\HumanoMailer\Commands;

use Idoneo\HumanoMailer\Models\Message;
use Idoneo\HumanoMailer\Models\MessageDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessActiveCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'humano-mailer:process-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active campaigns and create message deliveries with scheduled times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Processing active campaigns...');

        // Get all active messages that have been started
        $activeMessages = Message::where('status_id', 1)
            ->whereNotNull('started_at')
            ->get();

        if ($activeMessages->isEmpty())
        {
            $this->info('📭 No active campaigns found.');
            return 0;
        }

        $totalProcessed = 0;
        $totalCreated = 0;

        foreach ($activeMessages as $message)
        {
            $this->info("📧 Processing campaign: {$message->name} (ID: {$message->id})");

            $created = $this->processMessageCampaign($message);
            $totalCreated += $created;
            $totalProcessed++;

            if ($created > 0)
            {
                $this->info("   ✅ Created {$created} new deliveries");
            } else
            {
                $this->info('   ⏸️  No new deliveries needed');
            }
        }

        $this->info("🎉 Processed {$totalProcessed} campaigns, created {$totalCreated} deliveries");

        Log::info('📊 ProcessActiveCampaigns completed', [
            'campaigns_processed' => $totalProcessed,
            'deliveries_created' => $totalCreated,
        ]);

        return 0;
    }

    /**
     * Process a single message campaign
     */
    private function processMessageCampaign(Message $message): int
    {
        $createdCount = 0;
        $deliveryIndex = 0;

        // Get base time for scheduling
        $baseTime = now();

        // Get contacts for this message (this would depend on your contact selection logic)
        $contacts = $this->getContactsForMessage($message);

        foreach ($contacts as $contact)
        {
            // Check if delivery already exists
            $existingDelivery = MessageDelivery::where('message_id', $message->id)
                ->where('contact_id', $contact->id)
                ->first();

            if (!$existingDelivery)
            {
                // Check if we can send to this contact based on minimum hours between emails
                if (!$message->canSendToContact($contact))
                {
                    $nextAvailableTime = $message->getNextAvailableTimeForContact($contact);
                    $this->info("   ⏰ Skipping {$contact->email} - next available: {$nextAvailableTime->format('Y-m-d H:i:s')}");
                    continue;
                }

                // Calculate scheduled time based on configuration
                $baseMinutes = config('humano-mailer.delays.base_minutes', 1);
                $maxRandomSeconds = config('humano-mailer.delays.random_seconds', 60);

                $delayMinutes = $deliveryIndex * $baseMinutes;
                $randomSeconds = rand(0, $maxRandomSeconds);
                $scheduledTime = $baseTime->copy()->addMinutes($delayMinutes)->addSeconds($randomSeconds);

                // Ensure scheduled time respects minimum hours between emails
                $nextAvailableTime = $message->getNextAvailableTimeForContact($contact);
                if ($scheduledTime->lt($nextAvailableTime))
                {
                    $scheduledTime = $nextAvailableTime->copy()->addMinutes($delayMinutes)->addSeconds($randomSeconds);
                }

                MessageDelivery::create([
                    'team_id' => $message->team_id,
                    'message_id' => $message->id,
                    'contact_id' => $contact->id,
                    'status_id' => 1, // pending
                    'sent_at' => $scheduledTime,
                ]);

                $createdCount++;
                $deliveryIndex++;

                // Limit deliveries per run to avoid overload
                $maxDeliveries = config('humano-mailer.processing.deliveries_per_campaign_run', 50);
                if ($createdCount >= $maxDeliveries)
                {
                    $this->info("   ⚠️  Reached max deliveries per run ({$maxDeliveries}), stopping");
                    break;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Get contacts for a message campaign
     * This is a placeholder - you would implement your contact selection logic here
     */
    private function getContactsForMessage(Message $message)
    {
        // This would depend on your contact selection logic
        // For example, you might have contact lists, segments, etc.
        
        // Placeholder implementation - you would replace this with your actual logic
        $contactModel = config('humano-mailer.models.contact', \App\Models\Contact::class);
        
        if (!class_exists($contactModel)) {
            Log::warning("Contact model {$contactModel} not found");
            return collect();
        }

        return $contactModel::where('team_id', $message->team_id)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->limit(100) // Reasonable limit
            ->get();
    }
}
