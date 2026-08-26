<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Support\DemoCourses\DemoCourseCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

final class DemoCourseSeederService
{
    /**
     * @return array{created: int, updated: int, tags_created: int}
     */
    public function seed(int $count = 30): array
    {
        $courses = array_slice(DemoCourseCatalog::all(), 0, $this->validateCount($count));
        $categories = PostCategory::query()
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id']);

        $assignments = $this->assignCoursesToCategories($courses, $categories);

        return DB::transaction(function () use ($assignments): array {
            $summary = ['created' => 0, 'updated' => 0, 'tags_created' => 0];

            foreach ($assignments as $assignment) {
                $courseData = $assignment['course'];
                $title = '[Demo] '.$courseData['title'];
                $course = Course::withTrashed()->where('title', $title)->first();
                $isNew = $course === null;
                $course ??= new Course;

                if ($course->trashed()) {
                    $course->restore();
                }

                $course->forceFill([
                    'title' => $title,
                    'description' => $courseData['description'],
                    'thumbnail' => $courseData['thumbnail'],
                    'banner' => $courseData['thumbnail'],
                    'author' => $courseData['author'],
                    'authorDescription' => 'Demo instructor profile generated for the course catalog.',
                    'course_duration' => '3 hours',
                    'content' => "demo_course_key:{$courseData['key']}\n\n{$courseData['description']}",
                    'status' => 1,
                    'original_price' => $courseData['original_price'],
                    'sale_off_price' => $courseData['sale_off_price'],
                ])->save();

                $course->courseCategories()->sync([$assignment['category_id']]);

                $tagIds = [];
                foreach ($courseData['tags'] as $tagName) {
                    [$tag, $created] = $this->findOrCreateTag($tagName);
                    $tagIds[] = $tag->id;
                    $summary['tags_created'] += $created ? 1 : 0;
                }
                $course->courseTags()->sync($tagIds);

                $summary[$isNew ? 'created' : 'updated']++;
            }

            return $summary;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $courses
     * @param  Collection<int, object{id: int}>  $categories
     * @return list<array{course: array<string, mixed>, category_id: int}>
     */
    public function assignCoursesToCategories(array $courses, Collection $categories): array
    {
        if ($categories->isEmpty()) {
            throw new LogicException('At least one active category is required before seeding demo courses.');
        }

        $categoryPlans = $categories->values()->map(function (object $category, int $index) use ($courses, $categories): array {
            return [
                'id' => (int) $category->id,
                'topic' => $this->resolveCategoryTopic($category->category_name ?? null),
                'quota' => intdiv(count($courses), $categories->count()) + ($index < count($courses) % $categories->count() ? 1 : 0),
                'remaining' => 0,
            ];
        })->all();

        $unassignedCourses = collect($courses)->values();
        $assignments = [];
        foreach ($categoryPlans as &$plan) {
            if ($plan['topic'] === null) {
                $plan['remaining'] = $plan['quota'];

                continue;
            }

            $selectedCourses = $unassignedCourses
                ->filter(fn (array $course): bool => in_array($plan['topic'], $course['topics'] ?? [], true))
                ->take($plan['quota']);

            foreach ($selectedCourses as $course) {
                $assignments[] = ['course' => $course, 'category_id' => $plan['id']];
                $unassignedCourses = $unassignedCourses->reject(fn (array $candidate): bool => $candidate['key'] === $course['key'])->values();
            }

            $plan['remaining'] = $plan['quota'] - $selectedCourses->count();
        }
        unset($plan);

        while ($unassignedCourses->isNotEmpty()) {
            foreach ($categoryPlans as &$plan) {
                if ($plan['remaining'] === 0 || $unassignedCourses->isEmpty()) {
                    continue;
                }

                $course = $unassignedCourses->shift();
                $assignments[] = ['course' => $course, 'category_id' => $plan['id']];
                $plan['remaining']--;
            }
        }
        unset($plan);

        return $assignments;
    }

    private function resolveCategoryTopic(?string $categoryName): ?string
    {
        $normalizedName = Str::slug(Str::lower((string) $categoryName));

        return match ($normalizedName) {
            'development', 'programming', 'software-development', 'technology', 'it' => 'development',
            'design', 'art', 'creative' => 'design',
            'health-fitness', 'health-and-fitness', 'fitness', 'health', 'wellness' => 'health-fitness',
            'lifestyle' => 'lifestyle',
            'business', 'finance', 'career' => 'business',
            'music', 'audio' => 'music',
            'marketing', 'digital-marketing' => 'marketing',
            'photography-video', 'photography-and-video', 'photography', 'video' => 'photography-video',
            default => null,
        };
    }

    private function validateCount(int $count): int
    {
        if ($count < 1 || $count > count(DemoCourseCatalog::all())) {
            throw new InvalidArgumentException('The demo course count must be between 1 and 30.');
        }

        return $count;
    }

    /**
     * @return array{0: Tag, 1: bool}
     */
    private function findOrCreateTag(string $tagName): array
    {
        $normalizedName = Str::lower(trim($tagName));
        $tag = Tag::query()
            ->whereNull('deleted_at')
            ->where('tag_name', $normalizedName)
            ->first();

        if ($tag !== null) {
            return [$tag, false];
        }

        $tag = new Tag;
        $tag->forceFill(['tag_name' => $normalizedName])->save();

        return [$tag, true];
    }
}
