<?php

namespace Tests\Unit\Enums;

use App\Enums\RepositoryStack;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RepositoryStackTest extends TestCase
{
    public function test_has_all_expected_cases(): void
    {
        $expectedCases = [
            'Laravel', 'Symfony', 'React', 'Vue', 'Angular', 'Node', 'Python',
            'Django', 'Flask', 'FastApi', 'Spring', 'Express', 'Next', 'Nuxt',
            'Nest', 'DotNet', 'AspNet', 'Html', 'Css',
        ];

        $actualCases = array_map(fn ($case) => $case->name, RepositoryStack::cases());

        $this->assertEquals($expectedCases, $actualCases);
    }

    #[DataProvider('labelProvider')]
    public function test_returns_correct_label(RepositoryStack $stack, string $expectedLabel): void
    {
        $this->assertEquals($expectedLabel, $stack->label());
    }

    public static function labelProvider(): array
    {
        return [
            'Laravel' => [RepositoryStack::Laravel, 'Laravel'],
            'Symfony' => [RepositoryStack::Symfony, 'Symfony'],
            'React' => [RepositoryStack::React, 'React'],
            'Vue' => [RepositoryStack::Vue, 'Vue'],
            'Angular' => [RepositoryStack::Angular, 'Angular'],
            'Node' => [RepositoryStack::Node, 'Node.js'],
            'Python' => [RepositoryStack::Python, 'Python'],
            'Django' => [RepositoryStack::Django, 'Django'],
            'Flask' => [RepositoryStack::Flask, 'Flask'],
            'FastApi' => [RepositoryStack::FastApi, 'FastAPI'],
            'Spring' => [RepositoryStack::Spring, 'Spring'],
            'Express' => [RepositoryStack::Express, 'Express'],
            'Next' => [RepositoryStack::Next, 'Next.js'],
            'Nuxt' => [RepositoryStack::Nuxt, 'Nuxt'],
            'Nest' => [RepositoryStack::Nest, 'NestJS'],
            'DotNet' => [RepositoryStack::DotNet, '.NET'],
            'AspNet' => [RepositoryStack::AspNet, 'ASP.NET'],
            'Html' => [RepositoryStack::Html, 'HTML'],
            'Css' => [RepositoryStack::Css, 'CSS'],
        ];
    }

    #[DataProvider('iconProvider')]
    public function test_returns_correct_icon(RepositoryStack $stack, string $expectedIcon): void
    {
        $this->assertEquals($expectedIcon, $stack->icon());
    }

    public static function iconProvider(): array
    {
        return [
            'Laravel' => [RepositoryStack::Laravel, 'fa-brands fa-laravel'],
            'Symfony' => [RepositoryStack::Symfony, 'fa-brands fa-symfony'],
            'React' => [RepositoryStack::React, 'fa-brands fa-react'],
            'Vue' => [RepositoryStack::Vue, 'fa-brands fa-vuejs'],
            'Angular' => [RepositoryStack::Angular, 'fa-brands fa-angular'],
            'Node' => [RepositoryStack::Node, 'fa-brands fa-node-js'],
            'Python' => [RepositoryStack::Python, 'fa-brands fa-python'],
            'Django' => [RepositoryStack::Django, 'fa-solid fa-leaf'],
            'Flask' => [RepositoryStack::Flask, 'fa-solid fa-flask'],
            'FastApi' => [RepositoryStack::FastApi, 'fa-solid fa-bolt'],
            'Spring' => [RepositoryStack::Spring, 'fa-solid fa-seedling'],
            'Express' => [RepositoryStack::Express, 'fa-solid fa-server'],
            'Next' => [RepositoryStack::Next, 'fa-solid fa-n'],
            'Nuxt' => [RepositoryStack::Nuxt, 'fa-solid fa-layer-group'],
            'Nest' => [RepositoryStack::Nest, 'fa-solid fa-cubes'],
            'DotNet' => [RepositoryStack::DotNet, 'fa-solid fa-code'],
            'AspNet' => [RepositoryStack::AspNet, 'fa-solid fa-globe'],
            'Html' => [RepositoryStack::Html, 'fa-solid fa-code'],
            'Css' => [RepositoryStack::Css, 'fa-solid fa-paint-brush'],
        ];
    }

    #[DataProvider('extensionsProvider')]
    public function test_returns_correct_extensions(RepositoryStack $stack, array $expectedExtensions): void
    {
        $this->assertEquals($expectedExtensions, $stack->extensions());
    }

    public static function extensionsProvider(): array
    {
        return [
            'Laravel' => [RepositoryStack::Laravel, ['php', 'html', 'css', 'scss']],
            'Symfony' => [RepositoryStack::Symfony, ['php', 'html', 'css', 'scss']],
            'React' => [RepositoryStack::React, ['js', 'jsx', 'ts', 'tsx', 'css', 'scss']],
            'Vue' => [RepositoryStack::Vue, ['js', 'ts', 'vue', 'css', 'scss']],
            'Angular' => [RepositoryStack::Angular, ['ts', 'html', 'css', 'scss']],
            'Node' => [RepositoryStack::Node, ['js', 'jsx', 'ts', 'tsx', 'css', 'scss']],
            'Python' => [RepositoryStack::Python, ['py', 'html', 'css']],
            'Django' => [RepositoryStack::Django, ['py', 'html', 'css']],
            'Flask' => [RepositoryStack::Flask, ['py', 'html', 'css']],
            'FastApi' => [RepositoryStack::FastApi, ['py', 'html', 'css']],
            'Spring' => [RepositoryStack::Spring, ['java', 'kt', 'html', 'css']],
            'Express' => [RepositoryStack::Express, ['js', 'jsx', 'ts', 'tsx', 'css', 'scss']],
            'Next' => [RepositoryStack::Next, ['js', 'jsx', 'ts', 'tsx', 'css', 'scss']],
            'Nuxt' => [RepositoryStack::Nuxt, ['js', 'ts', 'vue', 'css', 'scss']],
            'Nest' => [RepositoryStack::Nest, ['ts', 'html', 'css', 'scss']],
            'DotNet' => [RepositoryStack::DotNet, ['cs', 'html', 'css']],
            'AspNet' => [RepositoryStack::AspNet, ['cs', 'html', 'css']],
            'Html' => [RepositoryStack::Html, ['html']],
            'Css' => [RepositoryStack::Css, ['css', 'scss']],
        ];
    }

    #[DataProvider('detectorMethodProvider')]
    public function test_returns_correct_detector_method(RepositoryStack $stack, string $expectedMethod): void
    {
        $this->assertEquals($expectedMethod, $stack->detectorMethod());
    }

    public static function detectorMethodProvider(): array
    {
        return [
            'Laravel' => [RepositoryStack::Laravel, 'detectLaravel'],
            'Symfony' => [RepositoryStack::Symfony, 'detectSymfony'],
            'React' => [RepositoryStack::React, 'detectReact'],
            'Vue' => [RepositoryStack::Vue, 'detectVue'],
            'Angular' => [RepositoryStack::Angular, 'detectAngular'],
            'Node' => [RepositoryStack::Node, 'detectNode'],
            'Python' => [RepositoryStack::Python, 'detectPython'],
            'Django' => [RepositoryStack::Django, 'detectDjango'],
            'Flask' => [RepositoryStack::Flask, 'detectFlask'],
            'FastApi' => [RepositoryStack::FastApi, 'detectFastApi'],
            'Spring' => [RepositoryStack::Spring, 'detectSpring'],
            'Express' => [RepositoryStack::Express, 'detectExpress'],
            'Next' => [RepositoryStack::Next, 'detectNext'],
            'Nuxt' => [RepositoryStack::Nuxt, 'detectNuxt'],
            'Nest' => [RepositoryStack::Nest, 'detectNest'],
            'DotNet' => [RepositoryStack::DotNet, 'detectDotNet'],
            'AspNet' => [RepositoryStack::AspNet, 'detectAspNet'],
            'Html' => [RepositoryStack::Html, 'detectHtml'],
            'Css' => [RepositoryStack::Css, 'detectCss'],
        ];
    }

    public function all_stacks_have_non_empty_label(): void
    {
        foreach (RepositoryStack::cases() as $stack) {
            $this->assertNotEmpty($stack->label(), "Stack {$stack->name} should have a non-empty label");
        }
    }

    public function all_stacks_have_valid_icon_class(): void
    {
        foreach (RepositoryStack::cases() as $stack) {
            $icon = $stack->icon();
            $this->assertMatchesRegularExpression(
                '/^fa-(brands|solid) fa-[\w-]+$/',
                $icon,
                "Stack {$stack->name} should have a valid FontAwesome icon class"
            );
        }
    }

    public function all_stacks_have_at_least_one_extension(): void
    {
        foreach (RepositoryStack::cases() as $stack) {
            $this->assertNotEmpty(
                $stack->extensions(),
                "Stack {$stack->name} should have at least one extension"
            );
        }
    }

    public function all_stacks_have_detector_method_starting_with_detect(): void
    {
        foreach (RepositoryStack::cases() as $stack) {
            $this->assertStringStartsWith(
                'detect',
                $stack->detectorMethod(),
                "Stack {$stack->name} detector method should start with 'detect'"
            );
        }
    }
}
