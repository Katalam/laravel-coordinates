<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Commands;

use Illuminate\Console\Command;

class CoordinatesCommand extends Command
{
    public $signature = 'laravel-coordinates';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
