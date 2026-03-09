<?php

namespace Tests\Feature;

use App\Models\RepositoryAnalysis;
use App\Services\RepositoryAnalyzerService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RepositoryAnalysisControllerTest extends TestCase
{
    public function testStoreRedirectsToShowWhenAnalysisSucceeds(): void
    {
        $analysis = new RepositoryAnalysis([
            'slug' => 'owner-repo',
            'repository_url' => 'https://github.com/owner/repo',
            'repository_name' => 'repo',
            'owner' => 'owner',
            'metrics' => [],
        ]);

        /** @var MockInterface $service */
        $service = Mockery::mock(RepositoryAnalyzerService::class);
        $service->shouldReceive('setRepositoryUrl')
            ->once()
            ->with('https://github.com/owner/repo')
            ->andReturnSelf();
        $service->shouldReceive('analyze')->once()->andReturnSelf();
        $service->shouldReceive('object')->once()->andReturn($analysis);

        $this->app->instance(RepositoryAnalyzerService::class, $service);

        $response = $this->post(route('repository-analyses.store'), [
            'repository_url' => 'https://github.com/owner/repo',
        ]);

        $response->assertRedirect(route('repository-analyses.show', $analysis));
    }

    public function testStoreReturnsWithValidationErrorWhenAnalysisFails(): void
    {
        /** @var MockInterface $service */
        $service = Mockery::mock(RepositoryAnalyzerService::class);
        $service->shouldReceive('setRepositoryUrl')
            ->once()
            ->with('https://github.com/owner/repo')
            ->andReturnSelf();
        $service->shouldReceive('analyze')
            ->once()
            ->andThrow(new \RuntimeException('Failed to clone repository'));

        $this->app->instance(RepositoryAnalyzerService::class, $service);

        $response = $this->from(route('repository-analyses.index'))
            ->post(route('repository-analyses.store'), [
                'repository_url' => 'https://github.com/owner/repo',
            ]);

        $response->assertRedirect(route('repository-analyses.index'));
        $response->assertSessionHasErrors([
            'repository_url' => 'Nao foi possivel analisar este repositorio agora. Verifique a URL e tente novamente.',
        ]);
        $response->assertSessionHasInput('repository_url', 'https://github.com/owner/repo');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
