<?php

declare(strict_types=1);

use LRV\App\Controllers\InicialController;
use LRV\App\Controllers\StatusController;
use LRV\App\Controllers\SeoController;
use LRV\App\Controllers\Cliente\CriarContaController;
use LRV\App\Controllers\Cliente\EntrarController as ClienteEntrarController;
use LRV\App\Controllers\Cliente\AssinarPlanoController;
use LRV\App\Controllers\Cliente\AplicacoesController as ClienteAplicacoesController;
use LRV\App\Controllers\Cliente\MonitoramentoController as ClienteMonitoramentoController;
use LRV\App\Controllers\Cliente\PainelController as ClientePainelController;
use LRV\App\Controllers\Cliente\PlanosController as ClientePlanosController;
use LRV\App\Controllers\Cliente\SairController as ClienteSairController;
use LRV\App\Controllers\Cliente\StatusController as ClienteStatusController;
use LRV\App\Controllers\Cliente\TerminalController as ClienteTerminalController;
use LRV\App\Controllers\Cliente\TicketsController as ClienteTicketsController;
use LRV\App\Controllers\Cliente\VpsController as ClienteVpsController;
use LRV\App\Controllers\Cliente\StripeCheckoutController;
use LRV\App\Controllers\Equipe\ConfiguracoesController;
use LRV\App\Controllers\Equipe\ImagemUploadController;
use LRV\App\Controllers\Equipe\EntrarController as EquipeEntrarController;
use LRV\App\Controllers\Equipe\AssinaturasController;
use LRV\App\Controllers\Equipe\AsaasEventosController;
use LRV\App\Controllers\Equipe\AjudaController;
use LRV\App\Controllers\Equipe\BackupsController;
use LRV\App\Controllers\Equipe\InicializacaoController;
use LRV\App\Controllers\Equipe\JobsController;
use LRV\App\Controllers\Equipe\NotificacoesController;
use LRV\App\Controllers\Equipe\PainelController as EquipePainelController;
use LRV\App\Controllers\Equipe\PlanosController as EquipePlanosController;
use LRV\App\Controllers\Equipe\AplicacoesController;
use LRV\App\Controllers\Equipe\MonitoramentoController;
use LRV\App\Controllers\Equipe\StatusController as EquipeStatusController;
use LRV\App\Controllers\Equipe\ServidoresController;
use LRV\App\Controllers\Equipe\PrimeiroAcessoController;
use LRV\App\Controllers\Equipe\SairController as EquipeSairController;
use LRV\App\Controllers\Equipe\TicketsController as EquipeTicketsController;
use LRV\App\Controllers\Equipe\UsuariosController;
use LRV\App\Controllers\Equipe\VpsController as EquipeVpsController;
use LRV\App\Controllers\Equipe\TerminalController;
use LRV\App\Controllers\Webhooks\AsaasController;
use LRV\App\Controllers\Webhooks\StripeController;
use LRV\App\Controllers\Equipe\DoisFatoresController;
use LRV\App\Controllers\Equipe\PermissoesController;
use LRV\App\Controllers\Cliente\AssinaturasController as ClienteAssinaturasController;
use LRV\App\Controllers\Cliente\AjudaController as ClienteAjudaController;
use LRV\App\Controllers\Cliente\ChatController as ClienteChatController;
use LRV\App\Controllers\Cliente\EmailController as ClienteEmailController;
use LRV\App\Controllers\Cliente\DominiosEmailController as ClienteDominiosEmailController;
use LRV\App\Controllers\Cliente\ResetSenhaController as ClienteResetSenhaController;
use LRV\App\Controllers\Equipe\ChatController as EquipeChatController;
use LRV\App\Controllers\Equipe\EmailsController as EquipeEmailsController;
use LRV\App\Controllers\LegalController;
use LRV\App\Controllers\ChangelogController;
use LRV\App\Controllers\Equipe\ErrosController;
use LRV\App\Controllers\Equipe\MinhaContaController;
use LRV\App\Controllers\Equipe\ClientesController;
use LRV\App\Controllers\Equipe\ResetSenhaController as EquipeResetSenhaController;
use LRV\App\Controllers\Equipe\SatisfacaoController;
use LRV\App\Controllers\Cliente\AvaliacaoController;
use LRV\App\Controllers\Cliente\MinhaContaController as ClienteMinhaContaController;
use LRV\App\Controllers\Cliente\DoisFatoresController as ClienteDoisFatoresController;
use LRV\Core\Middlewares;

$roteador->get('/', [InicialController::class, 'index']);
$roteador->get('/infraestrutura', [InicialController::class, 'infraestrutura']);

$roteador->get('/robots.txt', [SeoController::class, 'robots']);
$roteador->get('/sitemap.xml', [SeoController::class, 'sitemap']);

$roteador->get('/status', [StatusController::class, 'index']);

$roteador->get('/equipe/entrar', [EquipeEntrarController::class, 'formulario']);
$roteador->post('/equipe/entrar', [EquipeEntrarController::class, 'entrar'], [Middlewares::rateLimitIp('login_team', 10, 60)]);
$roteador->get('/equipe/primeiro-acesso', [PrimeiroAcessoController::class, 'formulario']);
$roteador->post('/equipe/primeiro-acesso', [PrimeiroAcessoController::class, 'criar']);
$roteador->get('/equipe/painel', [EquipePainelController::class, 'index'], [Middlewares::exigirLoginEquipe()]);
$roteador->get('/equipe/notificacoes', [NotificacoesController::class, 'listar'], [Middlewares::exigirLoginEquipe()]);
$roteador->post('/equipe/notificacoes/marcar-lida', [NotificacoesController::class, 'marcarLida'], [Middlewares::exigirLoginEquipe()]);
$roteador->post('/equipe/notificacoes/marcar-todas', [NotificacoesController::class, 'marcarTodasLidas'], [Middlewares::exigirLoginEquipe()]);
$roteador->get('/equipe/ajuda', [AjudaController::class, 'index'], [Middlewares::exigirLoginEquipe()]);

$roteador->get('/equipe/inicializacao', [InicializacaoController::class, 'index'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/aplicar-schema', [InicializacaoController::class, 'aplicarSchema'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/aplicar-migrations', [InicializacaoController::class, 'aplicarMigrations'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/criar-diretorios', [InicializacaoController::class, 'criarDiretorios'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/gerar-tokens', [InicializacaoController::class, 'gerarTokensEDefaults'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/processar-job', [InicializacaoController::class, 'processarUmJob'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/coletar-status', [InicializacaoController::class, 'enfileirarColetaStatus'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/coletar-status-continuo', [InicializacaoController::class, 'iniciarColetaStatusContinua'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/backup-automatico', [InicializacaoController::class, 'iniciarBackupAutomatico'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/testar-nodes', [InicializacaoController::class, 'testarNodes'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/terminal/instalar-deps', [InicializacaoController::class, 'terminalInstalarDependencias'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/terminal/iniciar-daemon', [InicializacaoController::class, 'terminalIniciarDaemon'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/inicializacao/terminal/parar-daemon', [InicializacaoController::class, 'terminalPararDaemon'], [Middlewares::exigirPermissao('manage_servers')]);

$roteador->get('/equipe/configuracoes', [ConfiguracoesController::class, 'formulario'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/configuracoes', [ConfiguracoesController::class, 'salvar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/configuracoes/instalar-agente-email', [ConfiguracoesController::class, 'instalarAgenteEmail'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/configuracoes/setup-daemons', [ConfiguracoesController::class, 'setupDaemons'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/configuracoes/upload-imagem', [ImagemUploadController::class, 'upload'], [Middlewares::exigirPermissao('manage_billing'), Middlewares::rateLimitEquipe('img_upload', 20, 60)]);
$roteador->get('/equipe/assinaturas', [AssinaturasController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/asaas-eventos', [AsaasEventosController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos', [EquipePlanosController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos/novo', [EquipePlanosController::class, 'novo'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos/editar', [EquipePlanosController::class, 'editar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/planos/salvar', [EquipePlanosController::class, 'salvar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/servidores', [ServidoresController::class, 'listar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/novo', [ServidoresController::class, 'novo'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/editar', [ServidoresController::class, 'editar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/terminal-seguro', [ServidoresController::class, 'terminalSeguro'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/salvar', [ServidoresController::class, 'salvar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/testar-conexao', [ServidoresController::class, 'testarConexao'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/inicializar', [ServidoresController::class, 'inicializar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/inicializar-passo', [ServidoresController::class, 'inicializarPasso'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/inicializar-finalizar', [ServidoresController::class, 'inicializarFinalizar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/logs-inicializacao', [ServidoresController::class, 'logsInicializacao'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/backups', [BackupsController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/backups/criar', [BackupsController::class, 'criar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/backups/baixar', [BackupsController::class, 'baixar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/backups/excluir', [BackupsController::class, 'excluir'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/usuarios', [UsuariosController::class, 'listar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/usuarios/novo', [UsuariosController::class, 'novo'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/usuarios/editar', [UsuariosController::class, 'editar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/usuarios/salvar', [UsuariosController::class, 'salvar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/tickets', [EquipeTicketsController::class, 'listar'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->get('/equipe/tickets/ver', [EquipeTicketsController::class, 'ver'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->post('/equipe/tickets/responder', [EquipeTicketsController::class, 'responder'], [Middlewares::exigirPermissao('reply_tickets')]);
$roteador->post('/equipe/tickets/fechar', [EquipeTicketsController::class, 'fechar'], [Middlewares::exigirPermissao('close_tickets')]);
$roteador->get('/equipe/aplicacoes', [AplicacoesController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/aplicacoes/novo', [AplicacoesController::class, 'novo'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/aplicacoes/editar', [AplicacoesController::class, 'editar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/aplicacoes/salvar', [AplicacoesController::class, 'salvar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/aplicacoes/excluir', [AplicacoesController::class, 'excluir'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/aplicacoes/deploy', [AplicacoesController::class, 'deploy'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/monitoramento', [MonitoramentoController::class, 'listar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/monitoramento/ver', [MonitoramentoController::class, 'ver'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/status', [EquipeStatusController::class, 'listar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/status/incidentes/criar', [EquipeStatusController::class, 'criarIncidente'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/status/incidentes/atualizar', [EquipeStatusController::class, 'atualizarIncidente'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/status/incidentes/servicos', [EquipeStatusController::class, 'atualizarIncidenteServicos'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/vps', [EquipeVpsController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/vps/provisionar', [EquipeVpsController::class, 'provisionar'], [Middlewares::exigirPermissao('manage_vps'), Middlewares::rateLimitEquipe('vps_action', 30, 60)]);
$roteador->post('/equipe/vps/suspender', [EquipeVpsController::class, 'suspender'], [Middlewares::exigirPermissao('manage_vps'), Middlewares::rateLimitEquipe('vps_action', 30, 60)]);
$roteador->post('/equipe/vps/reativar', [EquipeVpsController::class, 'reativar'], [Middlewares::exigirPermissao('manage_vps'), Middlewares::rateLimitEquipe('vps_action', 30, 60)]);
$roteador->post('/equipe/vps/reiniciar', [EquipeVpsController::class, 'reiniciar'], [Middlewares::exigirPermissao('manage_vps'), Middlewares::rateLimitEquipe('vps_action', 30, 60)]);
$roteador->post('/equipe/vps/remover', [EquipeVpsController::class, 'remover'], [Middlewares::exigirPermissao('manage_vps'), Middlewares::rateLimitEquipe('vps_action', 10, 60)]);
$roteador->get('/equipe/vps/logs', [EquipeVpsController::class, 'logs'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/terminal', [TerminalController::class, 'index'], [Middlewares::exigirPermissao('manage_terminal')]);
$roteador->post('/equipe/terminal/token', [TerminalController::class, 'emitirToken'], [Middlewares::exigirPermissao('manage_terminal'), Middlewares::rateLimitEquipe('terminal_token', 60, 60)]);
$roteador->post('/equipe/terminal/exec', [TerminalController::class, 'exec'], [Middlewares::exigirPermissao('manage_terminal'), Middlewares::rateLimitEquipe('terminal_exec', 120, 60)]);
$roteador->post('/equipe/terminal/upload', [TerminalController::class, 'upload'], [Middlewares::exigirPermissao('manage_terminal'), Middlewares::rateLimitEquipe('terminal_upload', 30, 60)]);
$roteador->get('/equipe/terminal/download', [TerminalController::class, 'download'], [Middlewares::exigirPermissao('manage_terminal')]);
$roteador->get('/equipe/terminal/auditoria', [TerminalController::class, 'auditoria'], [Middlewares::exigirPermissao('manage_terminal')]);
$roteador->get('/equipe/terminal/auditoria/ver', [TerminalController::class, 'auditoriaVer'], [Middlewares::exigirPermissao('manage_terminal')]);
$roteador->get('/equipe/jobs', [JobsController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/jobs/ver', [JobsController::class, 'ver'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/sair', [EquipeSairController::class, 'sair'], [Middlewares::exigirLoginEquipe()]);

// Clientes
$roteador->get('/equipe/clientes', [ClientesController::class, 'listar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/clientes/novo', [ClientesController::class, 'novo'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/clientes/editar', [ClientesController::class, 'editar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->get('/equipe/clientes/ver', [ClientesController::class, 'ver'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/clientes/salvar', [ClientesController::class, 'salvar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/clientes/ocultar', [ClientesController::class, 'ocultar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/clientes/deletar', [ClientesController::class, 'deletar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/clientes/assinar-plano', [ClientesController::class, 'assinarPlano'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/clientes/impersonar', [ClientesController::class, 'impersonar'], [Middlewares::exigirPermissao('manage_users')]);

// Minha conta
$roteador->get('/equipe/minha-conta', [MinhaContaController::class, 'index'], [Middlewares::exigirLoginEquipe()]);
$roteador->post('/equipe/minha-conta/salvar', [MinhaContaController::class, 'salvar'], [Middlewares::exigirLoginEquipe()]);

// Reset de senha — equipe
$roteador->get('/equipe/reset-senha', [EquipeResetSenhaController::class, 'formulario']);
$roteador->post('/equipe/reset-senha/solicitar', [EquipeResetSenhaController::class, 'solicitar'], [Middlewares::rateLimitIp('reset_equipe', 5, 300)]);
$roteador->get('/equipe/reset-senha/nova', [EquipeResetSenhaController::class, 'formularioNovaSenha']);
$roteador->post('/equipe/reset-senha/salvar', [EquipeResetSenhaController::class, 'salvar'], [Middlewares::rateLimitIp('reset_equipe_save', 10, 300)]);

$roteador->get('/cliente/entrar', [ClienteEntrarController::class, 'formulario']);
$roteador->post('/cliente/entrar', [ClienteEntrarController::class, 'entrar'], [Middlewares::rateLimitIp('login_client', 10, 60)]);
$roteador->get('/cliente/criar-conta', [CriarContaController::class, 'formulario']);
$roteador->post('/cliente/criar-conta', [CriarContaController::class, 'criar']);
$roteador->get('/contratar', [\LRV\App\Controllers\Cliente\ContratarController::class, 'wizard']);
$roteador->post('/contratar/finalizar', [\LRV\App\Controllers\Cliente\ContratarController::class, 'finalizar'], [Middlewares::rateLimitIp('contratar', 5, 60)]);
$roteador->get('/cliente/painel', [ClientePainelController::class, 'index'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/planos', [ClientePlanosController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/planos/checkout', [ClientePlanosController::class, 'checkout'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/assinar', [AssinarPlanoController::class, 'assinar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('subscribe', 5, 60)]);
$roteador->get('/cliente/stripe/sucesso', [StripeCheckoutController::class, 'sucesso'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/stripe/cancelado', [StripeCheckoutController::class, 'cancelado'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/pagamento', [\LRV\App\Controllers\Cliente\PagamentoController::class, 'aguardando'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/pagamento/status', [\LRV\App\Controllers\Cliente\PagamentoController::class, 'statusApi'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/pagamento/cartao', [\LRV\App\Controllers\Cliente\PagamentoController::class, 'pagarCartao'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('pay_card', 5, 60)]);
$roteador->get('/cliente/aplicacoes', [ClienteAplicacoesController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/aplicacoes/catalogo', [ClienteAplicacoesController::class, 'catalogo'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/aplicacoes/instalar', [ClienteAplicacoesController::class, 'instalar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('app_install', 10, 60)]);
$roteador->post('/cliente/aplicacoes/reinstalar', [ClienteAplicacoesController::class, 'reinstalar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('app_install', 10, 60)]);
$roteador->post('/cliente/aplicacoes/deletar', [ClienteAplicacoesController::class, 'deletar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/aplicacoes/status', [ClienteAplicacoesController::class, 'status'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/aplicacoes/logs', [ClienteAplicacoesController::class, 'logs'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/monitoramento', [ClienteMonitoramentoController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/monitoramento/ver', [ClienteMonitoramentoController::class, 'ver'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/status', [ClienteStatusController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/tickets', [ClienteTicketsController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/tickets/novo', [ClienteTicketsController::class, 'novo'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/tickets/criar', [ClienteTicketsController::class, 'criar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('ticket_create', 10, 60)]);
$roteador->get('/cliente/tickets/ver', [ClienteTicketsController::class, 'ver'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/tickets/responder', [ClienteTicketsController::class, 'responder'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('ticket_reply', 20, 60)]);
$roteador->get('/cliente/vps', [ClienteVpsController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/vps/terminal', [ClienteTerminalController::class, 'vps'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/vps/terminal/token', [ClienteTerminalController::class, 'emitirToken'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('terminal_token', 30, 60)]);
$roteador->post('/cliente/vps/terminal/exec', [ClienteTerminalController::class, 'exec'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('terminal_exec', 120, 60)]);
$roteador->post('/cliente/vps/terminal/upload', [ClienteTerminalController::class, 'upload'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('terminal_upload', 20, 60)]);
$roteador->get('/cliente/vps/terminal/download', [ClienteTerminalController::class, 'download'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);

// Gerenciador de arquivos
$roteador->get('/cliente/arquivos', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'index'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/arquivos/listar', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/arquivos/ler', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'ler'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/arquivos/salvar', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'salvar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('file_write', 60, 60)]);
$roteador->post('/cliente/arquivos/criar-pasta', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'criarPasta'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('file_write', 60, 60)]);
$roteador->post('/cliente/arquivos/deletar', [\LRV\App\Controllers\Cliente\ArquivosController::class, 'deletar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('file_write', 30, 60)]);
$roteador->get('/cliente/sair', [ClienteSairController::class, 'sair'], [Middlewares::exigirLoginCliente()]);

$roteador->post('/webhooks/asaas', [AsaasController::class, 'receber']);
$roteador->post('/webhooks/stripe', [StripeController::class, 'receber']);

// 2FA equipe
$roteador->get('/equipe/2fa/configurar', [DoisFatoresController::class, 'configurar'], [Middlewares::exigirLoginEquipe()]);
$roteador->post('/equipe/2fa/ativar', [DoisFatoresController::class, 'ativar'], [Middlewares::exigirLoginEquipe()]);
$roteador->post('/equipe/2fa/desativar', [DoisFatoresController::class, 'desativar'], [Middlewares::exigirLoginEquipe()]);
$roteador->get('/equipe/2fa/verificar', [DoisFatoresController::class, 'formularioVerificar']);
$roteador->post('/equipe/2fa/verificar', [DoisFatoresController::class, 'verificar'], [Middlewares::rateLimitIp('2fa_verify', 10, 60)]);

// Permissões por role (interface)
$roteador->get('/equipe/permissoes', [PermissoesController::class, 'index'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/permissoes/salvar', [PermissoesController::class, 'salvar'], [Middlewares::exigirPermissao('manage_users')]);

// Tickets equipe - atribuição manual
$roteador->post('/equipe/tickets/atribuir', [\LRV\App\Controllers\Equipe\TicketsController::class, 'atribuir'], [Middlewares::exigirPermissao('reply_tickets')]);
$roteador->post('/equipe/tickets/status', [\LRV\App\Controllers\Equipe\TicketsController::class, 'alterarStatus'], [Middlewares::exigirPermissao('reply_tickets')]);

// Contato público
$roteador->get('/contato', [InicialController::class, 'contato']);
$roteador->post('/contato', [InicialController::class, 'enviarContato'], [Middlewares::rateLimitIp('contato', 5, 300)]);

// Status público histórico de incidentes
$roteador->get('/status/incidentes', [StatusController::class, 'incidentes']);

// Cliente - assinaturas e ajuda
$roteador->get('/cliente/assinaturas', [\LRV\App\Controllers\Cliente\AssinaturasController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/assinaturas/historico', [\LRV\App\Controllers\Cliente\AssinaturasController::class, 'historico'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/assinaturas/reembolso', [\LRV\App\Controllers\Cliente\AssinaturasController::class, 'solicitarReembolso'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/ajuda', [\LRV\App\Controllers\Cliente\AjudaController::class, 'index'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);

// Chat cliente — página removida (cliente usa widget flutuante), mas endpoints de API mantidos para o widget
$roteador->post('/cliente/chat/token', [ClienteChatController::class, 'token'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('chat_token', 10, 60)]);
$roteador->get('/cliente/chat/historico', [ClienteChatController::class, 'historico'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/chat/poll', [ClienteChatController::class, 'poll'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/chat/enviar', [ClienteChatController::class, 'enviar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('chat_send', 30, 60)]);

// Upload de arquivo no chat (cliente ou equipe)
$roteador->post('/chat/upload', [\LRV\App\Controllers\Api\ChatUploadController::class, 'upload']);

// E-mails equipe (visão administrativa)
$roteador->get('/equipe/emails', [EquipeEmailsController::class, 'listar'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/emails/remover-email', [EquipeEmailsController::class, 'removerEmail'], [Middlewares::exigirPermissao('manage_users')]);
$roteador->post('/equipe/emails/remover-dominio', [EquipeEmailsController::class, 'removerDominio'], [Middlewares::exigirPermissao('manage_users')]);

// Chat equipe
$roteador->get('/equipe/chat', [EquipeChatController::class, 'listar'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->get('/equipe/chat/ver', [EquipeChatController::class, 'ver'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->post('/equipe/chat/token', [EquipeChatController::class, 'token'], [Middlewares::exigirPermissao('reply_tickets'), Middlewares::rateLimitEquipe('chat_token', 10, 60)]);
$roteador->post('/equipe/chat/fechar', [EquipeChatController::class, 'fechar'], [Middlewares::exigirPermissao('close_tickets')]);
$roteador->get('/equipe/chat/poll', [EquipeChatController::class, 'poll'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->post('/equipe/chat/enviar', [EquipeChatController::class, 'enviar'], [Middlewares::exigirPermissao('reply_tickets'), Middlewares::rateLimitEquipe('chat_send', 30, 60)]);

// Email cliente
$roteador->get('/cliente/emails', [ClienteEmailController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/emails/criar', [ClienteEmailController::class, 'criar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('email_create', 5, 60)]);
$roteador->post('/cliente/emails/remover', [ClienteEmailController::class, 'remover'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/emails/alterar-senha', [ClienteEmailController::class, 'alterarSenha'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('email_pw', 10, 60)]);

// Domínios de email cliente
$roteador->get('/cliente/emails/dominios', [ClienteDominiosEmailController::class, 'index'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/emails/dominios/adicionar', [ClienteDominiosEmailController::class, 'adicionar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_add', 5, 60)]);
$roteador->post('/cliente/emails/dominios/verificar', [ClienteDominiosEmailController::class, 'verificar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_verify', 10, 60)]);
$roteador->post('/cliente/emails/dominios/remover', [ClienteDominiosEmailController::class, 'remover'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/emails/dominios/instrucoes', [ClienteDominiosEmailController::class, 'instrucoes'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/emails/dominios/webmail-ativar', [ClienteDominiosEmailController::class, 'ativarWebmail'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('webmail_activate', 10, 60)]);
$roteador->post('/cliente/emails/dominios/webmail-verificar', [ClienteDominiosEmailController::class, 'verificarWebmail'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('webmail_verify', 10, 60)]);

// Domínios centralizados (subdomínios + raiz)
$roteador->get('/cliente/dominios', [\LRV\App\Controllers\Cliente\DominiosController::class, 'index'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/dominios/adicionar-raiz', [\LRV\App\Controllers\Cliente\DominiosController::class, 'adicionarRaiz'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_add', 5, 60)]);
$roteador->post('/cliente/dominios/adicionar-sub', [\LRV\App\Controllers\Cliente\DominiosController::class, 'adicionarSub'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_add', 5, 60)]);
$roteador->post('/cliente/dominios/verificar-txt', [\LRV\App\Controllers\Cliente\DominiosController::class, 'verificarTxt'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_verify', 10, 60)]);
$roteador->post('/cliente/dominios/verificar-cname', [\LRV\App\Controllers\Cliente\DominiosController::class, 'verificarCname'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_verify', 10, 60)]);
$roteador->post('/cliente/dominios/verificar-a', [\LRV\App\Controllers\Cliente\DominiosController::class, 'verificarA'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('domain_verify', 10, 60)]);
$roteador->post('/cliente/dominios/remover-raiz', [\LRV\App\Controllers\Cliente\DominiosController::class, 'removerRaiz'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/dominios/remover-sub', [\LRV\App\Controllers\Cliente\DominiosController::class, 'removerSub'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);

// Reset de senha — cliente
$roteador->get('/cliente/reset-senha', [ClienteResetSenhaController::class, 'formulario']);
$roteador->post('/cliente/reset-senha/solicitar', [ClienteResetSenhaController::class, 'solicitar'], [Middlewares::rateLimitIp('reset_cliente', 5, 300)]);
$roteador->get('/cliente/reset-senha/nova', [ClienteResetSenhaController::class, 'formularioNovaSenha']);
$roteador->post('/cliente/reset-senha/salvar', [ClienteResetSenhaController::class, 'salvar'], [Middlewares::rateLimitIp('reset_cliente_save', 10, 300)]);

// Minha conta — cliente
$roteador->get('/cliente/minha-conta', [ClienteMinhaContaController::class, 'index'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/minha-conta/salvar', [ClienteMinhaContaController::class, 'salvar'], [Middlewares::exigirLoginCliente()]);

// 2FA cliente
$roteador->get('/cliente/2fa/configurar', [ClienteDoisFatoresController::class, 'configurar'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/2fa/ativar', [ClienteDoisFatoresController::class, 'ativar'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/2fa/desativar', [ClienteDoisFatoresController::class, 'desativar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/2fa/verificar', [ClienteDoisFatoresController::class, 'formularioVerificar']);
$roteador->post('/cliente/2fa/verificar', [ClienteDoisFatoresController::class, 'verificar'], [Middlewares::rateLimitIp('2fa_verify_cli', 10, 60)]);

// Git Deploy
$roteador->get('/cliente/git-deploy', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/git-deploy/novo', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'novo'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/git-deploy/editar', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'editar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/git-deploy/salvar', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'salvar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/git-deploy/deploy', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'deploy'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('git_deploy', 10, 60)]);
$roteador->get('/cliente/git-deploy/logs', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'logs'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/git-deploy/excluir', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'excluir'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/git-deploy/regenerar-chave', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'regenerarChave'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/git-deploy/console', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'console'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('git_console', 60, 60)]);
$roteador->get('/cliente/git-deploy/server-logs', [\LRV\App\Controllers\Cliente\GitDeployController::class, 'serverLogs'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);

// Cron Jobs
$roteador->get('/cliente/cron-jobs', [\LRV\App\Controllers\Cliente\CronJobsController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/cron-jobs/salvar', [\LRV\App\Controllers\Cliente\CronJobsController::class, 'salvar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('cron_save', 10, 60)]);
$roteador->post('/cliente/cron-jobs/excluir', [\LRV\App\Controllers\Cliente\CronJobsController::class, 'excluir'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/cron-jobs/toggle', [\LRV\App\Controllers\Cliente\CronJobsController::class, 'toggle'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/cron-jobs/executar', [\LRV\App\Controllers\Cliente\CronJobsController::class, 'executarAgora'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('cron_exec', 10, 60)]);

// Bancos de Dados
$roteador->get('/cliente/banco-dados', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'listar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/banco-dados/criar', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'criar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/banco-dados/salvar', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'salvar'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('db_create', 5, 60)]);
$roteador->get('/cliente/banco-dados/ver', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'ver'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/banco-dados/sql', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'executarSql'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('db_sql', 30, 60)]);
$roteador->post('/cliente/banco-dados/excluir', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'excluir'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/banco-dados/senha', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'senha'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/banco-dados/nota', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'nota'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->get('/cliente/banco-dados/phpmyadmin', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'phpmyadmin'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/banco-dados/config-phpmyadmin', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'configPhpmyadmin'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado(), Middlewares::rateLimitCliente('pma_config', 5, 60)]);
$roteador->get('/cliente/banco-dados/config-phpmyadmin', [\LRV\App\Controllers\Cliente\BancoDadosController::class, 'lerConfigPhpmyadmin'], [Middlewares::exigirLoginCliente(), Middlewares::bloquearClienteGerenciado()]);
$roteador->post('/cliente/onboarding/concluir', [ClientePainelController::class, 'concluirOnboarding'], [Middlewares::exigirLoginCliente()]);

// Backups cliente
$roteador->get('/cliente/backups', [\LRV\App\Controllers\Cliente\BackupsController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/backups/criar', [\LRV\App\Controllers\Cliente\BackupsController::class, 'criar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('backup_create', 3, 60)]);
$roteador->get('/cliente/backups/baixar', [\LRV\App\Controllers\Cliente\BackupsController::class, 'baixar'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/backups/restaurar', [\LRV\App\Controllers\Cliente\BackupsController::class, 'restaurar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('backup_restore', 2, 60)]);

// Soluções (landing pages públicas)
$roteador->get('/solucoes/vps', [\LRV\App\Controllers\SolucoesController::class, 'vps']);
$roteador->get('/solucoes/aplicacoes', [\LRV\App\Controllers\SolucoesController::class, 'aplicacoes']);
$roteador->get('/solucoes/devops', [\LRV\App\Controllers\SolucoesController::class, 'devops']);
$roteador->get('/solucoes/email', [\LRV\App\Controllers\SolucoesController::class, 'email']);
$roteador->get('/solucoes/seguranca', [\LRV\App\Controllers\SolucoesController::class, 'seguranca']);

// Páginas públicas legais e changelog
$roteador->get('/termos', [LegalController::class, 'termos']);
$roteador->get('/privacidade', [LegalController::class, 'privacidade']);
$roteador->get('/licenca', [LegalController::class, 'licenca']);
$roteador->get('/changelog', [ChangelogController::class, 'index']);

// Erros do sistema (equipe)
$roteador->get('/equipe/erros', [ErrosController::class, 'listar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/erros/ver', [ErrosController::class, 'ver'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/erros/resolver', [ErrosController::class, 'resolver'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/erros/excluir', [ErrosController::class, 'excluir'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/erros/limpar-resolvidos', [ErrosController::class, 'limparResolvidos'], [Middlewares::exigirPermissao('manage_servers')]);

// Satisfação / avaliações
$roteador->get('/equipe/satisfacao', [SatisfacaoController::class, 'index'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->get('/cliente/avaliar', [AvaliacaoController::class, 'formulario'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/avaliar', [AvaliacaoController::class, 'salvar'], [Middlewares::exigirLoginCliente(), Middlewares::rateLimitCliente('avaliacao', 10, 60)]);

// Cookies / Consentimento (LGPD)
$roteador->post('/cookies/consent', [\LRV\App\Controllers\CookieConsentController::class, 'salvar'], [Middlewares::rateLimitIp('cookie_consent', 20, 60)]);
$roteador->get('/cookies/consent', [\LRV\App\Controllers\CookieConsentController::class, 'obter']);

// Chat Flows (equipe)
$roteador->get('/equipe/chat-flows', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/chat-flows/novo', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'novo'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/salvar', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'salvar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/chat-flows/editar', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'editar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/excluir', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'excluir'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/passo/salvar', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'salvarPasso'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/passo/remover', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'removerPasso'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/passo/reordenar', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'reordenarPassos'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/chat-flows/dispatch', [\LRV\App\Controllers\Equipe\ChatFlowsController::class, 'dispatch'], [Middlewares::exigirPermissao('reply_tickets')]);

// API — Métricas de monitoramento (recebe dados dos servidores)
$roteador->post('/api/metrics/servers', [\LRV\App\Controllers\Api\MetricsController::class, 'registrarServidor'], [Middlewares::rateLimitIp('metrics_push', 30, 60)]);
