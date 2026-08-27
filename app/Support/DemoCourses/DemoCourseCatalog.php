<?php

declare(strict_types=1);

namespace App\Support\DemoCourses;

final class DemoCourseCatalog
{
    /**
     * @return list<array{
     *     key: string,
     *     title: string,
     *     description: string,
     *     author: string,
     *     original_price: int,
     *     sale_off_price: int,
     *     thumbnail: string,
     *     tags: list<string>,
     *     topics: list<string>
     * }>
     */
    public static function all(): array
    {
        return [
            self::course('react-foundations', 'React Foundations: Build Modern Interfaces', 'Learn components, hooks, state management, and practical UI composition for modern web applications.', 'Linh Nguyen', 499000, 299000, 'photo-1516321318423-f06f85e504b3', ['react', 'javascript', 'frontend']),
            self::course('vue-3-practical', 'Vue 3 Practical Projects', 'Build polished Vue applications with Composition API, routing, reusable components, and maintainable state.', 'Minh Tran', 459000, 279000, 'photo-1552664730-d307ca884978', ['vue', 'javascript', 'frontend']),
            self::course('laravel-api', 'Laravel API Design and Development', 'Create secure, structured REST APIs with Laravel, validation, service classes, and database relationships.', 'Huy Le', 549000, 349000, 'photo-1484417894907-623942c8ee29', ['laravel', 'php', 'api']),
            self::course('python-data-analysis', 'Python for Data Analysis', 'Explore data cleaning, analysis, and visualisation workflows with practical Python exercises.', 'An Pham', 529000, 329000, 'photo-1517245386807-bb43f82c33c4', ['python', 'data-analysis', 'pandas']),
            self::course('sql-query-mastery', 'SQL Query Mastery', 'Write confident SQL queries, joins, aggregations, indexes, and performance-friendly reports.', 'Quang Ho', 399000, 249000, 'photo-1542744173-8e7e53415bb0', ['sql', 'database', 'mysql']),
            self::course('docker-for-developers', 'Docker for Developers', 'Containerise applications, manage local environments, and understand deployment-ready Docker workflows.', 'Khanh Bui', 489000, 289000, 'photo-1556761175-b413da4baf72', ['docker', 'devops', 'backend']),
            self::course('ui-design-systems', 'UI Design Systems from Scratch', 'Create consistent interfaces using typography, colour systems, components, and scalable design decisions.', 'Mai Pham', 429000, 269000, 'photo-1499750310107-5fef28a66643', ['ui-design', 'figma', 'design-system']),
            self::course('figma-ui-kit', 'Figma UI Kit Workshop', 'Design a reusable product UI kit and hand off clear specifications to development teams.', 'Trang Do', 369000, 219000, 'photo-1517836357463-d25dfeac3438', ['figma', 'ui-ux', 'prototyping']),
            self::course('brand-identity', 'Brand Identity for Digital Products', 'Develop visual identity, brand voice, and practical brand guidelines for product teams.', 'Hanh Vu', 449000, 299000, 'photo-1500530855697-b586d89ba3ee', ['branding', 'graphic-design', 'creative']),
            self::course('video-editing', 'Video Editing Essentials', 'Edit engaging videos with pacing, sound, transitions, and storytelling techniques for online audiences.', 'Tuan Phan', 479000, 319000, 'photo-1544717305-2782549b5136', ['video-editing', 'content-creation', 'media']),
            self::course('digital-illustration', 'Digital Illustration Basics', 'Learn composition, colour, brushes, and a repeatable workflow for expressive digital illustrations.', 'Nhi Vo', 419000, 259000, 'photo-1516321318423-f06f85e504b3', ['illustration', 'digital-art', 'creative']),
            self::course('photography-lighting', 'Photography and Lighting Fundamentals', 'Capture stronger portraits and product photos by understanding light, framing, and basic editing.', 'Duc Nguyen', 459000, 299000, 'photo-1552664730-d307ca884978', ['photography', 'lighting', 'visual-storytelling']),
            self::course('fitness-strength', 'Strength Training for Beginners', 'Build a safe strength-training routine with form cues, progression, recovery, and sustainable habits.', 'Anh Nguyen', 359000, 199000, 'photo-1484417894907-623942c8ee29', ['fitness', 'strength-training', 'wellness']),
            self::course('yoga-mobility', 'Yoga and Daily Mobility', 'Improve flexibility, balance, and everyday movement with approachable guided mobility sessions.', 'Thanh Le', 329000, 179000, 'photo-1517245386807-bb43f82c33c4', ['yoga', 'mobility', 'health']),
            self::course('nutrition-basics', 'Nutrition Basics for Everyday Life', 'Understand balanced meals, food labels, and realistic nutrition habits without restrictive rules.', 'Ha Tran', 339000, 189000, 'photo-1542744173-8e7e53415bb0', ['nutrition', 'healthy-eating', 'wellness']),
            self::course('mindfulness-focus', 'Mindfulness and Focus at Work', 'Use practical mindfulness techniques to manage attention, stress, and healthy work routines.', 'Lan Hoang', 299000, 159000, 'photo-1556761175-b413da4baf72', ['mindfulness', 'productivity', 'mental-health']),
            self::course('personal-finance', 'Personal Finance Made Simple', 'Plan a realistic budget, build savings habits, and make clearer everyday money decisions.', 'Phuong Bui', 399000, 249000, 'photo-1499750310107-5fef28a66643', ['personal-finance', 'budgeting', 'money']),
            self::course('digital-marketing', 'Digital Marketing Fundamentals', 'Plan campaigns, define audiences, measure results, and improve digital marketing decisions.', 'Son Dang', 499000, 319000, 'photo-1517836357463-d25dfeac3438', ['digital-marketing', 'marketing', 'analytics']),
            self::course('product-management', 'Product Management Essentials', 'Turn customer problems into focused product goals, roadmaps, experiments, and measurable outcomes.', 'Kiet Pham', 559000, 369000, 'photo-1500530855697-b586d89ba3ee', ['product-management', 'product-strategy', 'business']),
            self::course('public-speaking', 'Public Speaking with Confidence', 'Structure presentations, manage nerves, and communicate clearly in meetings and on stage.', 'Vy Nguyen', 389000, 229000, 'photo-1544717305-2782549b5136', ['public-speaking', 'communication', 'career']),
            self::course('excel-productivity', 'Excel Productivity for Work', 'Use formulas, sorting, lookups, and practical reports to work faster with spreadsheets.', 'Khoa Tran', 319000, 169000, 'photo-1516321318423-f06f85e504b3', ['excel', 'productivity', 'office']),
            self::course('english-workplace', 'Workplace English Communication', 'Practise useful vocabulary and communication patterns for email, meetings, and teamwork.', 'Thu Le', 379000, 219000, 'photo-1552664730-d307ca884978', ['english', 'communication', 'career']),
            self::course('creative-writing', 'Creative Writing: Tell Better Stories', 'Shape ideas into memorable scenes, characters, and short stories through guided exercises.', 'Hoa Pham', 349000, 199000, 'photo-1484417894907-623942c8ee29', ['creative-writing', 'storytelling', 'writing']),
            self::course('guitar-beginners', 'Guitar for Complete Beginners', 'Learn chords, rhythm, and simple songs with a structured daily practice plan.', 'Nam Vo', 409000, 259000, 'photo-1517245386807-bb43f82c33c4', ['guitar', 'music', 'beginners']),
            self::course('music-production', 'Introduction to Music Production', 'Arrange simple tracks and understand rhythm, melody, sound selection, and basic mixing.', 'Duy Pham', 479000, 299000, 'photo-1542744173-8e7e53415bb0', ['music-production', 'audio', 'creative']),
            self::course('travel-photography', 'Travel Photography Storytelling', 'Plan shots, work with natural light, and tell better travel stories through images.', 'Trang Nguyen', 439000, 279000, 'photo-1556761175-b413da4baf72', ['travel-photography', 'photography', 'storytelling']),
            self::course('fashion-styling', 'Personal Style and Fashion Basics', 'Build a versatile wardrobe and understand colour, fit, and styling for everyday confidence.', 'Linh Do', 369000, 229000, 'photo-1499750310107-5fef28a66643', ['fashion', 'styling', 'lifestyle']),
            self::course('home-coffee', 'Home Coffee Brewing Workshop', 'Brew better coffee at home by learning beans, grind size, water, and simple brewing methods.', 'Hieu Tran', 289000, 149000, 'photo-1517836357463-d25dfeac3438', ['coffee', 'lifestyle', 'hobby']),
            self::course('web-accessibility', 'Web Accessibility Essentials', 'Design and build inclusive web experiences with semantic HTML, keyboard support, and clear content.', 'My Nguyen', 449000, 289000, 'photo-1500530855697-b586d89ba3ee', ['accessibility', 'web-development', 'frontend']),
            self::course('cybersecurity-basics', 'Cybersecurity Basics for Everyone', 'Recognise common security risks and adopt safer password, device, and online habits.', 'Bao Le', 429000, 269000, 'photo-1544717305-2782549b5136', ['cybersecurity', 'online-safety', 'technology']),
        ];
    }

    /**
     * These keys intentionally mirror the category vocabulary supported by
     * DemoCourseSeederService. A course can serve more than one relevant topic.
     *
     * @var array<string, list<string>>
     */
    private const TOPICS_BY_COURSE_KEY = [
        'react-foundations' => ['development'],
        'vue-3-practical' => ['development'],
        'laravel-api' => ['development'],
        'python-data-analysis' => ['development'],
        'sql-query-mastery' => ['development'],
        'docker-for-developers' => ['development'],
        'ui-design-systems' => ['design'],
        'figma-ui-kit' => ['design'],
        'brand-identity' => ['design', 'marketing'],
        'video-editing' => ['design', 'photography-video'],
        'digital-illustration' => ['design'],
        'photography-lighting' => ['photography-video'],
        'fitness-strength' => ['health-fitness'],
        'yoga-mobility' => ['health-fitness'],
        'nutrition-basics' => ['health-fitness'],
        'mindfulness-focus' => ['health-fitness', 'lifestyle'],
        'personal-finance' => ['business', 'lifestyle'],
        'digital-marketing' => ['marketing'],
        'product-management' => ['business'],
        'public-speaking' => ['business'],
        'excel-productivity' => ['business'],
        'english-workplace' => ['business'],
        'creative-writing' => ['lifestyle'],
        'guitar-beginners' => ['music'],
        'music-production' => ['music'],
        'travel-photography' => ['photography-video', 'lifestyle'],
        'fashion-styling' => ['lifestyle'],
        'home-coffee' => ['lifestyle'],
        'web-accessibility' => ['development'],
        'cybersecurity-basics' => ['development'],
    ];

    /**
     * @param  list<string>  $tags
     * @return array{key: string, title: string, description: string, author: string, original_price: int, sale_off_price: int, thumbnail: string, tags: list<string>, topics: list<string>}
     */
    private static function course(
        string $key,
        string $title,
        string $description,
        string $author,
        int $originalPrice,
        int $saleOffPrice,
        string $imageId,
        array $tags,
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'original_price' => $originalPrice,
            'sale_off_price' => $saleOffPrice,
            'thumbnail' => "https://images.unsplash.com/{$imageId}?auto=format&fit=crop&w=1200&q=80",
            'tags' => $tags,
            'topics' => self::TOPICS_BY_COURSE_KEY[$key],
        ];
    }
}
