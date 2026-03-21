<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\SistemaConfig;
use LRV\Core\View;

final class ConfiguracoesController
{
    public function formulario(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
            'salvo' => false,
            'erro' => '',
            'asaas_token' => ConfiguracoesSistema::asaasToken(),
            'asaas_url_base' => ConfiguracoesSistema::asaasUrlBase(),
            'asaas_webhook_segredo' => ConfiguracoesSistema::webhookSegredoAsaas(),
            'stripe_secret_key' => ConfiguracoesSistema::stripeSecretKey(),
            'stripe_webhook_secret' => ConfiguracoesSistema::stripeWebhookSecret(),
            'app_url_base' => ConfiguracoesSistema::appUrlBase(),
            'tolerancia_dias' => (string) ConfiguracoesSistema::toleranciaPagamentoDias(),
            'evolution_url_base' => ConfiguracoesSistema::evolutionUrlBase(),
            'evolution_token' => ConfiguracoesSistema::evolutionToken(),
            'email_admin' => ConfiguracoesSistema::emailAdmin(),
            'whatsapp_admin_numero' => ConfiguracoesSistema::whatsappAdminNumero(),
            'evolution_instance' => ConfiguracoesSistema::evolutionInstance(),
            'ssh_key_dir' => ConfiguracoesSistema::sshKeyDir(),
            'monitoring_token' => ConfiguracoesSistema::monitoringToken(),
            'infra_node_max_util_percent' => (string) ConfiguracoesSistema::infraNodeMaxUtilPercent(),
            'terminal_ws_internal_port' => (string) ConfiguracoesSistema::terminalWsInternalPort(),
            'terminal_token_ttl_seconds' => (string) ConfiguracoesSistema::terminalTokenTtlSegundos(),
            'terminal_idle_timeout_seconds' => (string) ConfiguracoesSistema::terminalIdleTimeoutSegundos(),
            'terminal_safe_mode' => ConfiguracoesSistema::terminalSafeModeHabilitado() ? '1' : '0',
            'smtp_host'          => (string) Settings::obter('smtp.host', ''),
            'smtp_port'          => (string) Settings::obter('smtp.port', '587'),
            'smtp_user'          => (string) Settings::obter('smtp.user', ''),
            'smtp_pass'          => (string) Settings::obter('smtp.pass', ''),
            'smtp_encryption'    => (string) Settings::obter('smtp.encryption', 'tls'),
            'smtp_from_email'    => (string) Settings::obter('smtp.from_email', ''),
            'smtp_from_name'     => (string) Settings::obter('smtp.from_name', ''),
            'mailcow_url'          => (string) Settings::obter('email.mailcow_url', ''),
            'mailcow_key'          => (string) Settings::obter('email.mailcow_key', ''),
            'webmail_url'          => (string) Settings::obter('email.webmail_url', ''),
            'email_default_domain' => (string) Settings::obter('email.default_domain', ''),
            'email_webmail_mode'   => (string) Settings::obter('email.webmail_mode', 'global'),
            'email_max_accounts'   => (string) Settings::obter('email.max_accounts_per_plan', '5'),
            'email_dns_template'   => (string) Settings::obter('email.dns_instructions_template', ''),
            'chat_ws_port'         => (string) Settings::obter('chat.ws_port', '8082'),
            'system_name'           => SistemaConfig::nome(),            'system_logo_url'       => SistemaConfig::logoUrl(),
            'system_favicon_url'    => SistemaConfig::faviconUrl(),
            'system_company_name'   => SistemaConfig::empresaNome(),
            'system_copyright_text' => (string) Settings::obter('system.copyright_text', ''),
            'legal_terms_html'      => SistemaConfig::termsHtml(),
            'legal_privacy_html'    => SistemaConfig::privacyHtml(),
            'trial_enabled'         => (string) Settings::obter('trial.enabled', '0'),
            'trial_dias'            => (string) Settings::obter('trial.dias', '7'),
            'trial_vcpu'            => (string) Settings::obter('trial.vcpu', '1'),
            'trial_ram_mb'          => (string) Settings::obter('trial.ram_mb', '1024'),
            'trial_disco_gb'        => (string) Settings::obter('trial.disco_gb', '20'),
            'trial_descricao'       => (string) Settings::obter('trial.descricao', ''),
            'trial_label_cta'       => (string) Settings::obter('trial.label_cta', 'Testar grátis'),
            'seo_titulo'            => SistemaConfig::seoTitulo(),
            'seo_descricao'         => SistemaConfig::seoDescricao(),
            'seo_palavras_chave'    => SistemaConfig::seoPalavrasChave(),
            'seo_og_image'          => SistemaConfig::seoOgImage(),
            'seo_robots'            => SistemaConfig::seoRobots(),
            'seo_google_analytics_id' => SistemaConfig::seoGoogleAnalyticsId(),
            'seo_canonical_base'    => SistemaConfig::seoCanonicalBase(),
            'seo_schema_type'       => SistemaConfig::seoSchemaType(),
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $in = $req->input();

        $asaasToken = $in->postString('asaas_token', 300, false);
        $asaasUrl = $in->postUrl('asaas_url_base', 255, false);
        $asaasSegredo = $in->postString('asaas_webhook_segredo', 255, false);
        $stripeSecretKey = $in->postString('stripe_secret_key', 255, false);
        $stripeWebhookSecret = $in->postString('stripe_webhook_secret', 255, false);
        $appUrlBase = $in->postUrl('app_url_base', 255, false);

        $tolerancia = $in->postInt('tolerancia_dias', 1, 365, false);

        $evoUrl = $in->postUrl('evolution_url_base', 255, false);
        $evoToken = $in->postString('evolution_token', 255, false);
        $emailAdmin = $in->postEmail('email_admin', 190, false);

        $whatsAdminNumero = $in->postString('whatsapp_admin_numero', 30, false);
        $evoInstance = $in->postString('evolution_instance', 190, false);
        $sshKeyDir = $in->postString('ssh_key_dir', 400, false);
        $monitoringToken = $in->postString('monitoring_token', 255, false);

        $infraNodeMaxUtilPercent = $in->postInt('infra_node_max_util_percent', 50, 100, false);
        $terminalPorta = $in->postInt('terminal_ws_internal_port', 1, 65535, false);
        $terminalTokenTtl = $in->postInt('terminal_token_ttl_seconds', 10, 86400, false);
        $terminalIdleTimeout = $in->postInt('terminal_idle_timeout_seconds', 60, 604800, false);
        $terminalSafeMode = $in->postEnum('terminal_safe_mode', ['0', '1', 'on', 'true'], '0');

        $mailcowUrl          = $in->postUrl('mailcow_url', 255, false);
        $mailcowKey          = $in->postString('mailcow_key', 255, false);
        $webmailUrl          = $in->postUrl('webmail_url', 255, false);
        $emailDefaultDomain  = $in->postString('email_default_domain', 253, false);
        $emailWebmailMode    = $in->postEnum('email_webmail_mode', ['global', 'custom'], 'global');
        $emailMaxAccounts    = $in->postInt('email_max_accounts', 1, 9999, false);
        $emailDnsTemplate    = $in->postString('email_dns_template', 65535, false);
        $chatWsPort          = $in->postInt('chat_ws_port', 1, 65535, false);

        $smtpHost       = $in->postString('smtp_host', 255, false);
        $smtpPort       = $in->postInt('smtp_port', 1, 65535, false);
        $smtpUser       = $in->postString('smtp_user', 255, false);
        $smtpPass       = $in->postString('smtp_pass', 255, false);
        $smtpEncryption = $in->postEnum('smtp_encryption', ['tls', 'ssl', 'none'], 'tls');
        $smtpFromEmail  = $in->postEmail('smtp_from_email', 190, false);
        $smtpFromName   = $in->postString('smtp_from_name', 190, false);

        $systemName          = $in->postString('system_name', 190, false);
        $systemLogoUrl       = $in->postString('system_logo_url', 500, false);
        $systemFaviconUrl    = $in->postString('system_favicon_url', 500, false);
        $systemCompanyName   = $in->postString('system_company_name', 190, false);
        $systemCopyrightText = $in->postString('system_copyright_text', 255, false);
        $legalTermsHtml      = $in->postString('legal_terms_html', 65535, false);
        $legalPrivacyHtml    = $in->postString('legal_privacy_html', 65535, false);

        $trialEnabled    = $in->postEnum('trial_enabled', ['0', '1', 'on', 'true'], '0');
        $trialDias       = $in->postInt('trial_dias', 1, 365, false);
        $trialVcpu       = $in->postInt('trial_vcpu', 1, 64, false);
        $trialRamMb      = $in->postInt('trial_ram_mb', 128, 131072, false);
        $trialDiscoGb    = $in->postInt('trial_disco_gb', 1, 10000, false);
        $trialDescricao  = $in->postString('trial_descricao', 500, false);
        $trialLabelCta   = $in->postString('trial_label_cta', 100, false);

        $seoTitulo          = $in->postString('seo_titulo', 255, false);
        $seoDescricao       = $in->postString('seo_descricao', 500, false);
        $seoPalavrasChave   = $in->postString('seo_palavras_chave', 500, false);
        $seoOgImage         = $in->postString('seo_og_image', 500, false);
        $seoRobots          = $in->postEnum('seo_robots', ['index, follow', 'noindex, nofollow', 'noindex, follow', 'index, nofollow'], 'index, follow');
        $seoGaId            = $in->postString('seo_google_analytics_id', 50, false);
        $seoCanonicalBase   = $in->postUrl('seo_canonical_base', 255, false);
        $seoSchemaType      = $in->postEnum('seo_schema_type', ['Organization', 'LocalBusiness', 'SoftwareApplication', 'WebSite'], 'Organization');

        if ($in->temErros()) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
                'salvo' => false,
                'erro' => $in->primeiroErro(),
                'asaas_token' => $asaasToken,
                'asaas_url_base' => $asaasUrl,
                'asaas_webhook_segredo' => $asaasSegredo,
                'stripe_secret_key' => $stripeSecretKey,
                'stripe_webhook_secret' => $stripeWebhookSecret,
                'app_url_base' => $appUrlBase,
                'tolerancia_dias' => $tolerancia > 0 ? (string) $tolerancia : '3',
                'evolution_url_base' => $evoUrl,
                'evolution_token' => $evoToken,
                'email_admin' => $emailAdmin,
                'whatsapp_admin_numero' => $whatsAdminNumero,
                'evolution_instance' => $evoInstance,
                'ssh_key_dir' => $sshKeyDir,
                'monitoring_token' => $monitoringToken,
                'infra_node_max_util_percent' => $infraNodeMaxUtilPercent > 0 ? (string) $infraNodeMaxUtilPercent : '85',
                'terminal_ws_internal_port' => $terminalPorta > 0 ? (string) $terminalPorta : '8081',
                'terminal_token_ttl_seconds' => $terminalTokenTtl > 0 ? (string) $terminalTokenTtl : '60',
                'terminal_idle_timeout_seconds' => $terminalIdleTimeout > 0 ? (string) $terminalIdleTimeout : '900',
                'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? '1' : '0'),
                'smtp_host'          => $smtpHost,
                'smtp_port'          => $smtpPort > 0 ? (string) $smtpPort : '587',
                'smtp_user'          => $smtpUser,
                'smtp_pass'          => $smtpPass,
                'smtp_encryption'    => $smtpEncryption,
                'smtp_from_email'    => $smtpFromEmail,
                'smtp_from_name'     => $smtpFromName,
                'mailcow_url'          => $mailcowUrl,
                'mailcow_key'          => $mailcowKey,
                'webmail_url'          => $webmailUrl,
                'email_default_domain' => $emailDefaultDomain,
                'email_webmail_mode'   => $emailWebmailMode,
                'email_max_accounts'   => $emailMaxAccounts > 0 ? (string) $emailMaxAccounts : '5',
                'email_dns_template'   => $emailDnsTemplate,
                'chat_ws_port'         => $chatWsPort > 0 ? (string) $chatWsPort : '8082',
                'system_name'           => $systemName,
                'system_logo_url'       => $systemLogoUrl,
                'system_favicon_url'    => $systemFaviconUrl,
                'system_company_name'   => $systemCompanyName,
                'system_copyright_text' => $systemCopyrightText,
                'legal_terms_html'      => $legalTermsHtml,
                'legal_privacy_html'    => $legalPrivacyHtml,
                'trial_enabled'         => (($trialEnabled === '1' || $trialEnabled === 'on' || $trialEnabled === 'true') ? '1' : '0'),
                'trial_dias'            => $trialDias > 0 ? (string) $trialDias : '7',
                'trial_vcpu'            => $trialVcpu > 0 ? (string) $trialVcpu : '1',
                'trial_ram_mb'          => $trialRamMb > 0 ? (string) $trialRamMb : '1024',
                'trial_disco_gb'        => $trialDiscoGb > 0 ? (string) $trialDiscoGb : '20',
                'trial_descricao'       => $trialDescricao,
                'trial_label_cta'       => $trialLabelCta,
                'seo_titulo'            => $seoTitulo,
                'seo_descricao'         => $seoDescricao,
                'seo_palavras_chave'    => $seoPalavrasChave,
                'seo_og_image'          => $seoOgImage,
                'seo_robots'            => $seoRobots,
                'seo_google_analytics_id' => $seoGaId,
                'seo_canonical_base'    => $seoCanonicalBase,
                'seo_schema_type'       => $seoSchemaType,
            ]);

            return Resposta::html($html, 422);
        }

        if ($asaasUrl === '') {
            $asaasUrl = 'https://api.asaas.com/v3';
        }

        Settings::definir('asaas.token', $asaasToken);
        Settings::definir('asaas.url_base', $asaasUrl);
        Settings::definir('asaas.webhook_segredo', $asaasSegredo);
        Settings::definir('stripe.secret_key', $stripeSecretKey);
        Settings::definir('stripe.webhook_secret', $stripeWebhookSecret);
        Settings::definir('app.url_base', rtrim($appUrlBase, '/'));
        Settings::definir('cobranca.tolerancia_dias', $tolerancia > 0 ? $tolerancia : 3);
        Settings::definir('whatsapp.evolution.url_base', $evoUrl);
        Settings::definir('whatsapp.evolution.token', $evoToken);
        Settings::definir('alertas.email_admin', $emailAdmin);
        Settings::definir('alertas.whatsapp_admin_numero', $whatsAdminNumero);
        Settings::definir('whatsapp.evolution.instance', $evoInstance);
        Settings::definir('infra.ssh_key_dir', $sshKeyDir);
        Settings::definir('monitoring.token', $monitoringToken);
        Settings::definir('infra.node_max_util_percent', $infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85);
        Settings::definir('terminal.ws_internal_port', $terminalPorta > 0 ? $terminalPorta : 8081);
        Settings::definir('terminal.token_ttl_seconds', $terminalTokenTtl > 0 ? $terminalTokenTtl : 60);
        Settings::definir('terminal.idle_timeout_seconds', $terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900);
        Settings::definir('terminal.safe_mode', ($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0);
        Settings::definir('email.mailcow_url', $mailcowUrl);
        Settings::definir('email.mailcow_key', $mailcowKey);
        Settings::definir('email.webmail_url', $webmailUrl);
        Settings::definir('email.default_domain', $emailDefaultDomain);
        Settings::definir('email.webmail_mode', $emailWebmailMode);
        Settings::definir('email.max_accounts_per_plan', $emailMaxAccounts > 0 ? $emailMaxAccounts : 5);
        Settings::definir('email.dns_instructions_template', $emailDnsTemplate);
        Settings::definir('chat.ws_port', $chatWsPort > 0 ? $chatWsPort : 8082);

        Settings::definir('smtp.host',       $smtpHost);
        Settings::definir('smtp.port',       $smtpPort > 0 ? $smtpPort : 587);
        Settings::definir('smtp.user',       $smtpUser);
        Settings::definir('smtp.pass',       $smtpPass);
        Settings::definir('smtp.encryption', $smtpEncryption);
        Settings::definir('smtp.from_email', $smtpFromEmail);
        Settings::definir('smtp.from_name',  $smtpFromName);

        Settings::definir('system.name', $systemName);
        Settings::definir('system.logo_url', $systemLogoUrl);
        Settings::definir('system.favicon_url', $systemFaviconUrl);
        Settings::definir('system.company_name', $systemCompanyName);
        Settings::definir('system.copyright_text', $systemCopyrightText);
        Settings::definir('legal.terms_html', $legalTermsHtml);
        Settings::definir('legal.privacy_html', $legalPrivacyHtml);

        Settings::definir('trial.enabled',   ($trialEnabled === '1' || $trialEnabled === 'on' || $trialEnabled === 'true') ? 1 : 0);
        Settings::definir('trial.dias',      $trialDias > 0 ? $trialDias : 7);
        Settings::definir('trial.vcpu',      $trialVcpu > 0 ? $trialVcpu : 1);
        Settings::definir('trial.ram_mb',    $trialRamMb > 0 ? $trialRamMb : 1024);
        Settings::definir('trial.disco_gb',  $trialDiscoGb > 0 ? $trialDiscoGb : 20);
        Settings::definir('trial.descricao', $trialDescricao);
        Settings::definir('trial.label_cta', $trialLabelCta !== '' ? $trialLabelCta : 'Testar grátis');

        Settings::definir('seo.titulo',              $seoTitulo);
        Settings::definir('seo.descricao',           $seoDescricao);
        Settings::definir('seo.palavras_chave',      $seoPalavrasChave);
        Settings::definir('seo.og_image',            $seoOgImage);
        Settings::definir('seo.robots',              $seoRobots);
        Settings::definir('seo.google_analytics_id', $seoGaId);
        Settings::definir('seo.canonical_base',      rtrim($seoCanonicalBase, '/'));
        Settings::definir('seo.schema_type',         $seoSchemaType);

        $whatsLast4 = '';
        if ($whatsAdminNumero !== '') {
            $digits = preg_replace('/\D+/', '', $whatsAdminNumero);
            $whatsLast4 = (string) substr((string) $digits, -4);
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'settings.update',
            'settings',
            null,
            [
                'asaas_url_base' => $asaasUrl,
                'asaas_token_set' => $asaasToken !== '',
                'asaas_webhook_segredo_set' => $asaasSegredo !== '',
                'stripe_secret_key_set' => $stripeSecretKey !== '',
                'stripe_webhook_secret_set' => $stripeWebhookSecret !== '',
                'app_url_base' => rtrim($appUrlBase, '/'),
                'tolerancia_dias' => $tolerancia > 0 ? $tolerancia : 3,
                'evolution_url_base' => $evoUrl,
                'evolution_token_set' => $evoToken !== '',
                'evolution_instance' => $evoInstance,
                'email_admin' => $emailAdmin,
                'whatsapp_admin_last4' => $whatsLast4,
                'ssh_key_dir' => $sshKeyDir,
                'monitoring_token_set' => $monitoringToken !== '',
                'infra_node_max_util_percent' => $infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85,
                'terminal_ws_internal_port' => $terminalPorta > 0 ? $terminalPorta : 8081,
                'terminal_token_ttl_seconds' => $terminalTokenTtl > 0 ? $terminalTokenTtl : 60,
                'terminal_idle_timeout_seconds' => $terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900,
                'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0),
            ],
            $req,
        );

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
            'salvo' => true,
            'erro' => '',
            'asaas_token' => $asaasToken,
            'asaas_url_base' => $asaasUrl,
            'asaas_webhook_segredo' => $asaasSegredo,
            'stripe_secret_key' => $stripeSecretKey,
            'stripe_webhook_secret' => $stripeWebhookSecret,
            'app_url_base' => rtrim($appUrlBase, '/'),
            'tolerancia_dias' => (string) ($tolerancia > 0 ? $tolerancia : 3),
            'evolution_url_base' => $evoUrl,
            'evolution_token' => $evoToken,
            'email_admin' => $emailAdmin,
            'whatsapp_admin_numero' => $whatsAdminNumero,
            'evolution_instance' => $evoInstance,
            'ssh_key_dir' => $sshKeyDir,
            'monitoring_token' => $monitoringToken,
            'infra_node_max_util_percent' => (string) ($infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85),
            'terminal_ws_internal_port' => (string) ($terminalPorta > 0 ? $terminalPorta : 8081),
            'terminal_token_ttl_seconds' => (string) ($terminalTokenTtl > 0 ? $terminalTokenTtl : 60),
            'terminal_idle_timeout_seconds' => (string) ($terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900),
            'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? '1' : '0'),
            'smtp_host'          => $smtpHost,
            'smtp_port'          => $smtpPort > 0 ? (string) $smtpPort : '587',
            'smtp_user'          => $smtpUser,
            'smtp_pass'          => $smtpPass,
            'smtp_encryption'    => $smtpEncryption,
            'smtp_from_email'    => $smtpFromEmail,
            'smtp_from_name'     => $smtpFromName,
            'mailcow_url'          => $mailcowUrl,
            'mailcow_key'          => $mailcowKey,
            'webmail_url'          => $webmailUrl,
            'email_default_domain' => $emailDefaultDomain,
            'email_webmail_mode'   => $emailWebmailMode,
            'email_max_accounts'   => (string) ($emailMaxAccounts > 0 ? $emailMaxAccounts : 5),
            'email_dns_template'   => $emailDnsTemplate,
            'chat_ws_port'         => $chatWsPort > 0 ? (string) $chatWsPort : '8082',
            'system_name'           => $systemName,
            'system_logo_url'       => $systemLogoUrl,
            'system_favicon_url'    => $systemFaviconUrl,
            'system_company_name'   => $systemCompanyName,
            'system_copyright_text' => $systemCopyrightText,
            'legal_terms_html'      => $legalTermsHtml,
            'legal_privacy_html'    => $legalPrivacyHtml,
            'trial_enabled'         => (($trialEnabled === '1' || $trialEnabled === 'on' || $trialEnabled === 'true') ? '1' : '0'),
            'trial_dias'            => (string) ($trialDias > 0 ? $trialDias : 7),
            'trial_vcpu'            => (string) ($trialVcpu > 0 ? $trialVcpu : 1),
            'trial_ram_mb'          => (string) ($trialRamMb > 0 ? $trialRamMb : 1024),
            'trial_disco_gb'        => (string) ($trialDiscoGb > 0 ? $trialDiscoGb : 20),
            'trial_descricao'       => $trialDescricao,
            'trial_label_cta'       => $trialLabelCta !== '' ? $trialLabelCta : 'Testar grátis',
            'seo_titulo'            => $seoTitulo,
            'seo_descricao'         => $seoDescricao,
            'seo_palavras_chave'    => $seoPalavrasChave,
            'seo_og_image'          => $seoOgImage,
            'seo_robots'            => $seoRobots,
            'seo_google_analytics_id' => $seoGaId,
            'seo_canonical_base'    => $seoCanonicalBase,
            'seo_schema_type'       => $seoSchemaType,
        ]);

        return Resposta::html($html);
    }
}
