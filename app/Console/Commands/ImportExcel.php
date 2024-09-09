<?php

namespace App\Console\Commands;

use App\Imports\JugadorsImport;
use Illuminate\Console\Command;

class ImportExcel extends Command
{
    protected $signature = 'import:excel';

    protected $description = 'Laravel Excel importer';

    public function handle()
    {
        $this->output->title('Starting import');
        (new JugadorsImport)->withOutput($this->output)->import('users.xlsx');
        $this->output->success('Import successful');
    }
}
