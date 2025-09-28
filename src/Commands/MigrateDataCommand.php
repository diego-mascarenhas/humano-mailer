<?php

namespace Idoneo\HumanoMailer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateDataCommand extends Command
{
    public $signature = 'humano-mailer:migrate-data {--dry-run : Show what would be migrated without making changes}';

    public $description = 'Migrate existing message data to use the humano-mailer package structure';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting data migration for humano-mailer package...');

        // Check if tables exist
        if (!$this->checkTablesExist()) {
            return self::FAILURE;
        }

        // Migrate data
        $this->migrateMessageTypes($isDryRun);
        $this->migrateMessages($isDryRun);
        $this->migrateMessageDeliveries($isDryRun);
        $this->migrateMessageDeliveryStats($isDryRun);
        $this->migrateMessageDeliveryLinks($isDryRun);
        $this->migrateMessageDeliveryTracking($isDryRun);

        if ($isDryRun) {
            $this->info('✅ Dry run completed - no changes were made');
        } else {
            $this->info('✅ Data migration completed successfully!');
        }

        return self::SUCCESS;
    }

    private function checkTablesExist(): bool
    {
        $requiredTables = [
            'messages',
            'message_type',
            'message_deliveries',
            'message_delivery_stats',
            'message_delivery_links'
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("❌ Required table '{$table}' does not exist");
                return false;
            }
        }

        $this->info('✅ All required tables exist');
        return true;
    }

    private function migrateMessageTypes(bool $isDryRun): void
    {
        $this->info('📋 Checking message types...');
        
        $count = DB::table('message_type')->count();
        
        if ($count === 0) {
            $this->warn('⚠️  No message types found, creating default types...');
            
            if (!$isDryRun) {
                DB::table('message_type')->insert([
                    ['name' => 'Newsletter', 'status' => 1],
                    ['name' => 'Promotional', 'status' => 1],
                    ['name' => 'Transactional', 'status' => 1],
                    ['name' => 'Welcome', 'status' => 1],
                ]);
            }
            
            $this->info('✅ Default message types created');
        } else {
            $this->info("✅ Found {$count} message types");
        }
    }

    private function migrateMessages(bool $isDryRun): void
    {
        $this->info('📧 Checking messages...');
        
        $count = DB::table('messages')->count();
        $this->info("✅ Found {$count} messages");

        // Check for any data inconsistencies
        $messagesWithoutTeam = DB::table('messages')->whereNull('team_id')->count();
        if ($messagesWithoutTeam > 0) {
            $this->warn("⚠️  Found {$messagesWithoutTeam} messages without team_id");
            
            if (!$isDryRun) {
                // Assign to first available team
                $firstTeam = DB::table('teams')->first();
                if ($firstTeam) {
                    DB::table('messages')
                        ->whereNull('team_id')
                        ->update(['team_id' => $firstTeam->id]);
                    $this->info("✅ Assigned orphaned messages to team: {$firstTeam->name}");
                }
            }
        }
    }

    private function migrateMessageDeliveries(bool $isDryRun): void
    {
        $this->info('📬 Checking message deliveries...');
        
        $count = DB::table('message_deliveries')->count();
        $this->info("✅ Found {$count} message deliveries");

        // Check for deliveries without team_id
        $deliveriesWithoutTeam = DB::table('message_deliveries')->whereNull('team_id')->count();
        if ($deliveriesWithoutTeam > 0) {
            $this->warn("⚠️  Found {$deliveriesWithoutTeam} deliveries without team_id");
            
            if (!$isDryRun) {
                // Update deliveries to match their message's team_id
                DB::statement("
                    UPDATE message_deliveries md
                    JOIN messages m ON md.message_id = m.id
                    SET md.team_id = m.team_id
                    WHERE md.team_id IS NULL
                ");
                $this->info("✅ Updated deliveries with team_id from their messages");
            }
        }
    }

    private function migrateMessageDeliveryStats(bool $isDryRun): void
    {
        $this->info('📊 Checking message delivery stats...');
        
        $count = DB::table('message_delivery_stats')->count();
        $this->info("✅ Found {$count} delivery stats records");

        // Check for messages without stats
        $messagesWithoutStats = DB::table('messages')
            ->leftJoin('message_delivery_stats', 'messages.id', '=', 'message_delivery_stats.message_id')
            ->whereNull('message_delivery_stats.id')
            ->count();

        if ($messagesWithoutStats > 0) {
            $this->warn("⚠️  Found {$messagesWithoutStats} messages without delivery stats");
            
            if (!$isDryRun) {
                // Create basic stats for messages without them
                $messagesNeedingStats = DB::table('messages')
                    ->leftJoin('message_delivery_stats', 'messages.id', '=', 'message_delivery_stats.message_id')
                    ->whereNull('message_delivery_stats.id')
                    ->select('messages.id')
                    ->get();

                foreach ($messagesNeedingStats as $message) {
                    DB::table('message_delivery_stats')->insert([
                        'message_id' => $message->id,
                        'subscribers' => 0,
                        'remaining' => 0,
                        'failed' => 0,
                        'sent' => 0,
                        'rejected' => 0,
                        'delivered' => 0,
                        'opened' => 0,
                        'unsubscribed' => 0,
                        'clicks' => 0,
                        'unique_opens' => 0,
                        'ratio' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $this->info("✅ Created stats for {$messagesWithoutStats} messages");
            }
        }
    }

    private function migrateMessageDeliveryLinks(bool $isDryRun): void
    {
        $this->info('🔗 Checking message delivery links...');
        
        $count = DB::table('message_delivery_links')->count();
        $this->info("✅ Found {$count} delivery links");
    }

    private function migrateMessageDeliveryTracking(bool $isDryRun): void
    {
        $this->info('📈 Checking message delivery tracking...');
        
        if (Schema::hasTable('message_delivery_tracking')) {
            $count = DB::table('message_delivery_tracking')->count();
            $this->info("✅ Found {$count} tracking events");
        } else {
            $this->warn('⚠️  message_delivery_tracking table does not exist (optional)');
        }
    }
}
