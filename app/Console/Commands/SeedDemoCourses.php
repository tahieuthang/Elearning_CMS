<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DemoCourseSeederService;
use Illuminate\Console\Command;
use InvalidArgumentException;
use LogicException;

class SeedDemoCourses extends Command
{
    protected $signature = 'demo:seed-courses
        {--count=30 : Number of demo courses to upsert (1-30)}';

    protected $description = 'Upsert deterministic demo courses across active categories';

    public function __construct(private readonly DemoCourseSeederService $demoCourseSeederService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $summary = $this->demoCourseSeederService->seed((int) $this->option('count'));
        } catch (InvalidArgumentException|LogicException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Created', 'Updated', 'Tags created'],
            [[
                $summary['created'],
                $summary['updated'],
                $summary['tags_created'],
            ]],
        );
        $this->components->info('Demo courses have been synchronized.');

        return self::SUCCESS;
    }
}
