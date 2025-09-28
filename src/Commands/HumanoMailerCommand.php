<?php

namespace Idoneo\HumanoMailer\Commands;

use Illuminate\Console\Command;

class HumanoMailerCommand extends Command
{
    public $signature = 'humano-mailer';

    public $description = 'Humano Mailer package command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
