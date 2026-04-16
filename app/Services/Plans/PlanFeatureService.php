<?php

declare(strict_types=1);

namespace LRV\App\Services\Plans;

use LRV\Core\BancoDeDados;

/**
 * Determina as features e limites disponíveis para um cliente
 * com base no tipo de plano da assinatura ativa.
 */
final class PlanFeatureService
{
    /**
     * Features padrão por tipo de plano.
     * VPS tem acesso total, outros tipos têm acesso restrito.
     */
    private const FEATURES_BY_TYPE = [
        'vps' => [
            'vps', 'monitoramento', 'aplicacoes', 'catalogo', 'git_deploy',
            'banco_dados', 'arquivos', 'terminal', 'cron_jobs', 'backups',
            'emails', 'dominios',
        ],
        'wordpress' => [
            'aplicacoes', 'catalogo', 'banco_dados', 'arquivos',
            'dominios', 'backups',
        ],
        'webhosting' => [
            'aplicacoes', 'catalogo', 'git_deploy', 'banco_dados',
            'arquivos', 'dominios', 'backups',
        ],
        'nodejs' => [
            'aplicacoes', 'banco_dados', 'dominios', 'git_deploy',
        ],
        'cpp' => [
            'aplicacoes', 'banco_dados', 'dominios', 'git_deploy',
        ],
        'php' => [
            'aplicacoes', 'catalogo', 'banco_dados', 'arquivos',
            'dominios', 'git_deploy', 'backups',
        ],
        'python' => [
            'aplicacoes', 'banco_dados', 'dominios', 'git_deploy',
        ],
        // 'app' usa allowed_features do plano
    ];

    /**
     * Mapeamento de feature → prefixo de rota do cliente.
     */
    private const FEATURE_ROUTES = [
        'vps'           => '/cliente/vps',
        'monitoramento' => '/cliente/monitoramento',
        'aplicacoes'    => '/cliente/aplicacoes',
        'catalogo'      => '/cliente/aplicacoes/catalogo',
        'git_deploy'    => '/cliente/git-deploy',
        'banco_dados'   => '/cliente/banco-dados',
        'arquivos'      => '/cliente/arquivos',
        'terminal'      => '/cliente/terminal',
        'cron_jobs'     => '/cliente/cron-jobs',
        'backups'       => '/cliente/backups',
        'emails'        => '/cliente/emails',
        'dominios'      => '/cliente/dominios',
    ];

    /**
     * Rotas sempre acessíveis (independente do plano).
     */
    private const ALWAYS_ALLOWED_ROUTES = [
        '/cliente/painel',
        '/cliente/tickets',
        '/cliente/assinaturas',
        '/cliente/faturas',
        '/cliente/minha-conta',
        '/cliente/2fa',
        '/cliente/ajuda',
        '/cliente/sair',
        '/cliente/planos',
        '/cliente/pagamento',
        '/cliente/onboarding',
        '/cliente/chat',
        '/cliente/status',
    ];

    /**
     * Busca o plano ativo do cliente (da assinatura ativa).
     * Retorna null se não tem assinatura ativa.
     */
    public static function planoAtivo(int $clientId): ?array
    {
        static $cache = [];
        if (isset($cache[$clientId])) {
            return $cache[$clientId];
        }

        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->prepare(
                "SELECT p.id, p.name, p.plan_type, p.max_sites, p.max_databases,
                        p.max_storage_per_site_mb, p.max_cron_jobs, p.allowed_features,
                        p.cpu, p.ram, p.storage, p.specs_json
                 FROM subscriptions s
                 INNER JOIN plans p ON p.id = s.plan_id
                 WHERE s.client_id = :c AND s.status IN ('ACTIVE', 'active')
                 ORDER BY s.id DESC LIMIT 1"
            );
            $stmt->execute([':c' => $clientId]);
            $plano = $stmt->fetch();
            $cache[$clientId] = is_array($plano) ? $plano : null;
        } catch (\Throwable) {
            $cache[$clientId] = null;
        }

        return $cache[$clientId];
    }

    /**
     * Retorna a lista de features permitidas para o cliente.
     * Se não tem plano ativo, retorna todas (retrocompatível).
     */
    public static function featuresPermitidas(int $clientId): array
    {
        $plano = self::planoAtivo($clientId);

        if ($plano === null) {
            // Sem plano ativo → acesso total (retrocompatível)
            return self::FEATURES_BY_TYPE['vps'];
        }

        $tipo = (string)($plano['plan_type'] ?? 'vps');

        if ($tipo === 'app') {
            // Tipo genérico: usa allowed_features do plano
            $raw = (string)($plano['allowed_features'] ?? '');
            if ($raw !== '') {
                $features = json_decode($raw, true);
                if (is_array($features)) {
                    return $features;
                }
            }
            // Fallback: acesso total
            return self::FEATURES_BY_TYPE['vps'];
        }

        return self::FEATURES_BY_TYPE[$tipo] ?? self::FEATURES_BY_TYPE['vps'];
    }

    /**
     * Verifica se o cliente tem acesso a uma feature específica.
     */
    public static function temAcesso(int $clientId, string $feature): bool
    {
        return in_array($feature, self::featuresPermitidas($clientId), true);
    }

    /**
     * Verifica se uma rota é permitida para o cliente.
     */
    public static function rotaPermitida(int $clientId, string $rota): bool
    {
        // Rotas sempre permitidas
        foreach (self::ALWAYS_ALLOWED_ROUTES as $prefix) {
            if (str_starts_with($rota, $prefix)) {
                return true;
            }
        }

        // Verificar se a rota corresponde a alguma feature
        $features = self::featuresPermitidas($clientId);
        foreach (self::FEATURE_ROUTES as $feature => $routePrefix) {
            if (str_starts_with($rota, $routePrefix)) {
                return in_array($feature, $features, true);
            }
        }

        // Rota não mapeada → permitir (segurança: não bloquear o que não conhecemos)
        return true;
    }

    /**
     * Retorna os limites do plano ativo do cliente.
     */
    public static function limites(int $clientId): array
    {
        $plano = self::planoAtivo($clientId);

        return [
            'max_sites'              => $plano !== null ? ($plano['max_sites'] !== null ? (int)$plano['max_sites'] : null) : null,
            'max_databases'          => $plano !== null ? ($plano['max_databases'] !== null ? (int)$plano['max_databases'] : null) : null,
            'max_storage_per_site_mb'=> $plano !== null ? ($plano['max_storage_per_site_mb'] !== null ? (int)$plano['max_storage_per_site_mb'] : null) : null,
            'max_cron_jobs'          => $plano !== null ? ($plano['max_cron_jobs'] !== null ? (int)$plano['max_cron_jobs'] : null) : null,
            'plan_type'              => $plano !== null ? (string)($plano['plan_type'] ?? 'vps') : 'vps',
        ];
    }

    /**
     * Verifica se o cliente pode criar mais sites/aplicações.
     * Retorna [bool $pode, int $atual, ?int $limite]
     */
    public static function podeCriarSite(int $clientId): array
    {
        $limites = self::limites($clientId);
        $max = $limites['max_sites'];

        if ($max === null) {
            return [true, 0, null]; // Sem limite
        }

        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM applications a
                 INNER JOIN vps v ON v.id = a.vps_id
                 WHERE v.client_id = :c AND a.status NOT IN ('deleted', 'error')"
            );
            $stmt->execute([':c' => $clientId]);
            $atual = (int)$stmt->fetchColumn();
        } catch (\Throwable) {
            $atual = 0;
        }

        return [$atual < $max, $atual, $max];
    }

    /**
     * Verifica se o cliente pode criar mais bancos de dados.
     * Retorna [bool $pode, int $atual, ?int $limite]
     */
    public static function podeCriarBanco(int $clientId): array
    {
        $limites = self::limites($clientId);
        $max = $limites['max_databases'];

        if ($max === null) {
            return [true, 0, null];
        }

        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM client_databases WHERE client_id = :c AND status = 'active'"
            );
            $stmt->execute([':c' => $clientId]);
            $atual = (int)$stmt->fetchColumn();
        } catch (\Throwable) {
            $atual = 0;
        }

        return [$atual < $max, $atual, $max];
    }

    /**
     * Verifica se o cliente pode criar mais cron jobs.
     * Retorna [bool $pode, int $atual, ?int $limite]
     */
    public static function podeCriarCronJob(int $clientId): array
    {
        $limites = self::limites($clientId);
        $max = $limites['max_cron_jobs'];

        if ($max === null) {
            return [true, 0, null];
        }

        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM client_cron_jobs WHERE client_id = :c AND status = 'active'"
            );
            $stmt->execute([':c' => $clientId]);
            $atual = (int)$stmt->fetchColumn();
        } catch (\Throwable) {
            $atual = 0;
        }

        return [$atual < $max, $atual, $max];
    }

    /**
     * Retorna o tipo de plano do cliente (para exibição).
     */
    public static function tipoPlanoBadge(string $planType): array
    {
        $badges = [
            'vps'        => ['VPS',                '#e0e7ff', '#1e3a8a', '🖥️'],
            'wordpress'  => ['WordPress',          '#dbeafe', '#1d4ed8', '📝'],
            'webhosting' => ['Web Hosting',        '#dcfce7', '#166534', '🌐'],
            'nodejs'     => ['Node.js',            '#fef3c7', '#92400e', '⬢'],
            'cpp'        => ['C/C++',              '#fce7f3', '#9d174d', '⚙️'],
            'php'        => ['PHP/Laravel',        '#fef3c7', '#78350f', '🐘'],
            'python'     => ['Python',             '#e0f2fe', '#075985', '🐍'],
            'app'        => ['App',                '#f3e8ff', '#6b21a8', '📦'],
        ];

        return $badges[$planType] ?? $badges['vps'];
    }
}
