<?php

namespace Idoneo\HumanoMailer;

use Idoneo\HumanoMailer\Commands\HumanoMailerCommand;
use Idoneo\HumanoMailer\Models\SystemModule;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HumanoMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('humano-mailer')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                'create_messages_table',
                'create_message_deliveries_table',
                'create_message_delivery_stats_table',
                'create_message_delivery_links_table',
                'create_message_types_table'
            ])
            ->hasCommand(HumanoMailerCommand::class);
    }

    /**
     * Ensure module registry row exists on install/boot.
     */
    public function bootingPackage()
    {
        parent::bootingPackage();

        try {
            if (Schema::hasTable('modules')) {
                // Register module if not present (works with/without host App\Models\Module)
                if (class_exists(\App\Models\Module::class)) {
                    \App\Models\Module::updateOrCreate(
                        ['key' => 'mailer'],
                        [
                            'name' => 'Mailer',
                            'icon' => 'ti ti-mail',
                            'description' => 'Email marketing and messaging campaigns module',
                            'is_core' => false,
                            'status' => 1,
                        ]
                    );
                } else {
                    SystemModule::query()->updateOrCreate(
                        ['key' => 'mailer'],
                        [
                            'name' => 'Mailer',
                            'icon' => 'ti ti-mail',
                            'description' => 'Email marketing and messaging campaigns module',
                            'is_core' => false,
                            'status' => 1,
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore if host app hasn't migrated yet
        }
    }
}
