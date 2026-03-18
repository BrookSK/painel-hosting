<?php

declare(strict_types=1);

use LRV\App\Controllers\InicialController;
use LRV\App\Controllers\Cliente\CriarContaController;
use LRV\App\Controllers\Cliente\EntrarController as ClienteEntrarController;
use LRV\App\Controllers\Cliente\AssinarPlanoController;
use LRV\App\Controllers\Cliente\PainelController as ClientePainelController;
use LRV\App\Controllers\Cliente\PlanosController as ClientePlanosController;
use LRV\App\Controllers\Cliente\SairController as ClienteSairController;
use LRV\App\Controllers\Cliente\TicketsController as ClienteTicketsController;
use LRV\App\Controllers\Cliente\VpsController as ClienteVpsController;
use LRV\App\Controllers\Equipe\ConfiguracoesController;
use LRV\App\Controllers\Equipe\EntrarController as EquipeEntrarController;
use LRV\App\Controllers\Equipe\AssinaturasController;
use LRV\App\Controllers\Equipe\AsaasEventosController;
use LRV\App\Controllers\Equipe\AjudaController;
use LRV\App\Controllers\Equipe\JobsController;
use LRV\App\Controllers\Equipe\PainelController as EquipePainelController;
use LRV\App\Controllers\Equipe\PlanosController as EquipePlanosController;
use LRV\App\Controllers\Equipe\ServidoresController;
use LRV\App\Controllers\Equipe\PrimeiroAcessoController;
use LRV\App\Controllers\Equipe\SairController as EquipeSairController;
use LRV\App\Controllers\Equipe\TicketsController as EquipeTicketsController;
use LRV\App\Controllers\Equipe\VpsController as EquipeVpsController;
use LRV\App\Controllers\Webhooks\AsaasController;
use LRV\Core\Middlewares;

$roteador->get('/', [InicialController::class, 'index']);

$roteador->get('/equipe/entrar', [EquipeEntrarController::class, 'formulario']);
$roteador->post('/equipe/entrar', [EquipeEntrarController::class, 'entrar']);
$roteador->get('/equipe/primeiro-acesso', [PrimeiroAcessoController::class, 'formulario']);
$roteador->post('/equipe/primeiro-acesso', [PrimeiroAcessoController::class, 'criar']);
$roteador->get('/equipe/painel', [EquipePainelController::class, 'index'], [Middlewares::exigirLoginEquipe()]);
$roteador->get('/equipe/ajuda', [AjudaController::class, 'index'], [Middlewares::exigirLoginEquipe()]);
$roteador->get('/equipe/configuracoes', [ConfiguracoesController::class, 'formulario'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/configuracoes', [ConfiguracoesController::class, 'salvar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/assinaturas', [AssinaturasController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/asaas-eventos', [AsaasEventosController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos', [EquipePlanosController::class, 'listar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos/novo', [EquipePlanosController::class, 'novo'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/planos/editar', [EquipePlanosController::class, 'editar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->post('/equipe/planos/salvar', [EquipePlanosController::class, 'salvar'], [Middlewares::exigirPermissao('manage_billing')]);
$roteador->get('/equipe/servidores', [ServidoresController::class, 'listar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/novo', [ServidoresController::class, 'novo'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/servidores/editar', [ServidoresController::class, 'editar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->post('/equipe/servidores/salvar', [ServidoresController::class, 'salvar'], [Middlewares::exigirPermissao('manage_servers')]);
$roteador->get('/equipe/tickets', [EquipeTicketsController::class, 'listar'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->get('/equipe/tickets/ver', [EquipeTicketsController::class, 'ver'], [Middlewares::exigirPermissao('view_tickets')]);
$roteador->post('/equipe/tickets/responder', [EquipeTicketsController::class, 'responder'], [Middlewares::exigirPermissao('reply_tickets')]);
$roteador->post('/equipe/tickets/fechar', [EquipeTicketsController::class, 'fechar'], [Middlewares::exigirPermissao('close_tickets')]);
$roteador->get('/equipe/vps', [EquipeVpsController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/vps/provisionar', [EquipeVpsController::class, 'provisionar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/vps/suspender', [EquipeVpsController::class, 'suspender'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->post('/equipe/vps/reativar', [EquipeVpsController::class, 'reativar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/jobs', [JobsController::class, 'listar'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/jobs/ver', [JobsController::class, 'ver'], [Middlewares::exigirPermissao('manage_vps')]);
$roteador->get('/equipe/sair', [EquipeSairController::class, 'sair'], [Middlewares::exigirLoginEquipe()]);

$roteador->get('/cliente/entrar', [ClienteEntrarController::class, 'formulario']);
$roteador->post('/cliente/entrar', [ClienteEntrarController::class, 'entrar']);
$roteador->get('/cliente/criar-conta', [CriarContaController::class, 'formulario']);
$roteador->post('/cliente/criar-conta', [CriarContaController::class, 'criar']);
$roteador->get('/cliente/painel', [ClientePainelController::class, 'index'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/planos', [ClientePlanosController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/assinar', [AssinarPlanoController::class, 'assinar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/tickets', [ClienteTicketsController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/tickets/novo', [ClienteTicketsController::class, 'novo'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/tickets/criar', [ClienteTicketsController::class, 'criar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/tickets/ver', [ClienteTicketsController::class, 'ver'], [Middlewares::exigirLoginCliente()]);
$roteador->post('/cliente/tickets/responder', [ClienteTicketsController::class, 'responder'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/vps', [ClienteVpsController::class, 'listar'], [Middlewares::exigirLoginCliente()]);
$roteador->get('/cliente/sair', [ClienteSairController::class, 'sair'], [Middlewares::exigirLoginCliente()]);

$roteador->post('/webhooks/asaas', [AsaasController::class, 'receber']);
