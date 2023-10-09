<?php

namespace FatihOzpolat\Param\Commands;

use Illuminate\Console\Command;

class ParamCommand extends Command
{
    public $signature = 'laravel-param-pos';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
