<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearLearningData extends Command
{
    protected $signature = 'learning:clear-data
        {--force : Confirm the destructive operation without an interactive prompt}
        {--customer-id= : Clear data for one customer only}';

    protected $description = 'Clear learning progress, weekly learning and streak data';

    /**
     * Tables intentionally limited to learner tracking/progress data.
     * Course, video, customer and quiz-definition data are preserved.
     */
    private const TABLES = [
        'customer_learning_tracking_sessions',
        'customer_video_weekly_progress',
        'customer_learning_weeks',
        'customer_learning_streaks',
        'customer_video_progress',
        'customer_quiz_progress',
        'customer_course_progress',
    ];

    public function handle(): int
    {
        $customerId = $this->option('customer-id');

        if (!$this->option('force')) {
            $scope = $customerId ? "customer {$customerId}" : 'all customers';
            if (!$this->confirm("This will permanently clear learning data for {$scope}. Continue?")) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        $deleted = DB::transaction(function () use ($customerId): array {
            $counts = [];

            foreach (self::TABLES as $table) {
                if (!Schema::hasTable($table)) {
                    $counts[$table] = 0;
                    continue;
                }

                $query = DB::table($table);
                if ($customerId !== null) {
                    $query->where('customer_id', (int) $customerId);
                }

                $counts[$table] = $query->delete();
            }

            return $counts;
        });

        $this->table(['Table', 'Deleted rows'], collect($deleted)
            ->map(fn (int $count, string $table) => [$table, $count])
            ->values()
            ->all());

        $this->info('Learning data cleared successfully.');

        return self::SUCCESS;
    }
}
