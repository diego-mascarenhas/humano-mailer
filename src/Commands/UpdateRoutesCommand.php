<?php

namespace Idoneo\HumanoMailer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateRoutesCommand extends Command
{
    public $signature = 'humano-mailer:update-routes {--backup : Create backup of existing routes}';

    public $description = 'Update existing routes to use the humano-mailer package controllers';

    public function handle(): int
    {
        $this->info('🔄 Updating routes to use humano-mailer package...');

        $routesPath = base_path('routes/web.php');
        
        if (!File::exists($routesPath)) {
            $this->error('❌ routes/web.php not found');
            return self::FAILURE;
        }

        // Create backup if requested
        if ($this->option('backup')) {
            $backupPath = base_path('routes/web.php.backup-' . date('Y-m-d-H-i-s'));
            File::copy($routesPath, $backupPath);
            $this->info("📋 Backup created: {$backupPath}");
        }

        $routesContent = File::get($routesPath);

        // Routes to update
        $routeUpdates = [
            // Update MessageController import
            "use App\Http\Controllers\MessageController;" => "// MessageController now handled by humano-mailer package",
            
            // Update message routes to use package routes
            "Route::get('message/list'" => "// Route::get('message/list'", // Comment out old routes
            "Route::get('message/create'" => "// Route::get('message/create'",
            "Route::get('message/{id}'" => "// Route::get('message/{id}'",
            "Route::post('message'" => "// Route::post('message'",
            "Route::delete('message/{id}'" => "// Route::delete('message/{id}'",
            
            // Add comment about package routes
            "// MessageController now handled by humano-mailer package" => 
                "// MessageController now handled by humano-mailer package\n// Routes are automatically registered by the package"
        ];

        $updatedContent = $routesContent;
        $changesCount = 0;

        foreach ($routeUpdates as $search => $replace) {
            if (strpos($updatedContent, $search) !== false) {
                $updatedContent = str_replace($search, $replace, $updatedContent);
                $changesCount++;
                $this->info("✅ Updated: {$search}");
            }
        }

        if ($changesCount > 0) {
            File::put($routesPath, $updatedContent);
            $this->info("✅ Updated {$changesCount} route references");
        } else {
            $this->info('ℹ️  No route updates needed');
        }

        $this->info('📝 Note: The humano-mailer package automatically registers all message routes');
        $this->info('🔗 Available routes:');
        $this->line('  - GET  /message/list');
        $this->line('  - GET  /message/create');
        $this->line('  - GET  /message/{id}');
        $this->line('  - POST /message');
        $this->line('  - DELETE /message/{id}');
        $this->line('  - POST /message/{id}/start');
        $this->line('  - POST /message/{id}/pause');
        $this->line('  - POST /message/{id}/test');

        return self::SUCCESS;
    }
}
