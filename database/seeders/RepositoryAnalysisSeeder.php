<?php

namespace Database\Seeders;

use App\Services\RepositoryAnalyzerService;
use Illuminate\Database\Seeder;

class RepositoryAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        $repositories = [
            'https://github.com/chatwoot/chatwoot',
            'https://github.com/calcom/cal.com',
            'https://github.com/immich-app/immich',
            'https://github.com/directus/directus',
            'https://github.com/outline/outline',
            'https://github.com/appwrite/appwrite',
            'https://github.com/umami-software/umami',
            'https://github.com/rocket-chat/rocket.chat',
            'https://github.com/activepieces/activepieces',
            'https://github.com/novuhq/novu',
            'https://github.com/budibase/budibase',
            'https://github.com/tooljet/tooljet',
            'https://github.com/medusajs/medusa',
            'https://github.com/saleor/saleor',
            'https://github.com/payloadcms/payload',
            'https://github.com/strapi/strapi',
            'https://github.com/coollabsio/coolify',
            'https://github.com/fossbilling/fossbilling',
            'https://github.com/filamentphp/demo',
            'https://github.com/invoiceninja/invoiceninja',
            'https://github.com/akaunting/akaunting',
            'https://github.com/maybe-finance/maybe',
            'https://github.com/monicahq/monica',
            'https://github.com/docusealco/docuseal',
            'https://github.com/plankanban/planka',
            'https://github.com/mintplex-labs/anything-llm',
            'https://github.com/bytebase/bytebase',
            'https://github.com/refined-github/refined-github',
            'https://github.com/element-hq/element-web',
            'https://github.com/goauthentik/authentik',
        ];

        foreach ($repositories as $repositoryUrl) {
            try {
                app(RepositoryAnalyzerService::class)
                    ->setRepositoryUrl($repositoryUrl)
                    ->analyze();
            } catch (\Throwable $e) {
                $this->command->warn("Erro analisando: {$repositoryUrl}");
                $this->command->error($e->getMessage());
            }
        }
    }
}
