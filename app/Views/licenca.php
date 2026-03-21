<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$conteudoCustomizado = (string)($conteudo_customizado ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php $seo_titulo = I18n::t('licenca.titulo') . ' — ' . SistemaConfig::nome(); require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    body{background:#060d1f;}
    .pub-page-hero{background:linear-gradient(135deg,#060d1f,#0B1C3D,#1e3a8a);padding:56px 24px 48px;text-align:center;position:relative;overflow:hidden;}
    .pub-page-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .pub-page-hero-inner{position:relative;max-width:600px;margin:0 auto;}
    .pub-page-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#a78bfa;margin-bottom:12px;}
    .pub-page-title{font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;letter-spacing:-.03em;margin-bottom:10px;}
    .pub-page-sub{font-size:15px;color:rgba(255,255,255,.6);line-height:1.7;}
    .legal-wrap{max-width:860px;margin:0 auto;padding:48px 24px 72px;}
    .legal-body{background:#fff;border-radius:20px;padding:40px 44px;box-shadow:0 4px 32px rgba(0,0,0,.25);}
    .legal-body h1{font-size:24px;font-weight:800;margin-bottom:4px;color:#0B1C3D;letter-spacing:-.02em;}
    .legal-body .legal-meta{font-size:13px;color:#94a3b8;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid #f1f5f9;}
    .legal-body h2{font-size:16px;font-weight:700;margin:32px 0 8px;color:#1e3a8a;padding-left:12px;border-left:3px solid #4F46E5;}
    .legal-body h3{font-size:13px;font-weight:700;margin:16px 0 6px;color:#334155;text-transform:uppercase;letter-spacing:.04em;}
    .legal-body p{color:#475569;line-height:1.8;margin:0 0 12px;font-size:14px;}
    .legal-body ul{color:#475569;line-height:1.8;padding-left:20px;margin:0 0 12px;font-size:14px;}
    .legal-body li{margin-bottom:5px;}
    .legal-body .aviso{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin:20px 0;font-size:13px;color:#991b1b;font-weight:500;}
    .legal-body .destaque{background:#f0f4ff;border:1px solid #c7d2fe;border-radius:10px;padding:14px 16px;margin:20px 0;font-size:13px;color:#1e40af;}
    @media(max-width:600px){.legal-body{padding:24px 20px;}}
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

  <div class="pub-page-hero">
    <div class="pub-page-hero-inner">
      <div class="pub-page-label"><?php echo View::e(I18n::t('licenca.label')); ?></div>
      <h1 class="pub-page-title"><?php echo View::e(I18n::t('licenca.titulo')); ?></h1>
      <p class="pub-page-sub"><?php echo View::e(I18n::t('licenca.subtitulo')); ?></p>
    </div>
  </div>

  <div class="legal-wrap">
    <div class="legal-body">

      <?php if ($conteudoCustomizado !== ''): ?>
        <?php echo $conteudoCustomizado; ?>
      <?php else: ?>
      <div class="legal-meta">
        Versão 1.0 &nbsp;·&nbsp; Vigência: a partir de 21 de março de 2026 &nbsp;·&nbsp;
        Empresa: LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil
      </div>

      <div class="aviso">
        ⚠️ LEIA COM ATENÇÃO. O uso do sistema LRV Cloud Manager implica a aceitação integral e irrevogável de todos os termos desta Licença. Caso não concorde com qualquer disposição, não utilize o sistema.
      </div>

      <h2>1. DEFINIÇÕES</h2>
      <p>Para os fins desta Licença, os termos abaixo têm os seguintes significados:</p>
      <ul>
        <li><strong>Sistema:</strong> o software denominado "LRV Cloud Manager", incluindo todas as suas funcionalidades, interfaces, código-fonte, código compilado, banco de dados, APIs, design visual, documentação e quaisquer atualizações ou versões derivadas, disponibilizado como serviço (SaaS) pela Licenciante.</li>
        <li><strong>Licenciante:</strong> LRV Web / LRV Cloud, pessoa jurídica de direito privado, detentora exclusiva de todos os direitos de propriedade intelectual sobre o Sistema.</li>
        <li><strong>Licenciado:</strong> a pessoa física ou jurídica que contrata o acesso ao Sistema mediante assinatura de plano, aceitação desta Licença e dos Termos de Uso.</li>
        <li><strong>Usuário Final:</strong> qualquer pessoa autorizada pelo Licenciado a acessar e utilizar o Sistema dentro dos limites do plano contratado.</li>
        <li><strong>Código-Fonte:</strong> o conjunto de instruções, algoritmos, estruturas de dados, arquivos de configuração e demais elementos que compõem o Sistema em sua forma legível por humanos.</li>
        <li><strong>Plano:</strong> o pacote de recursos e funcionalidades contratado pelo Licenciado junto à Licenciante, conforme descrito na proposta comercial ou página de planos.</li>
      </ul>

      <h2>2. CONCESSÃO DE LICENÇA</h2>
      <p>Sujeito ao cumprimento integral desta Licença e ao pagamento das mensalidades devidas, a Licenciante concede ao Licenciado uma licença:</p>
      <ul>
        <li><strong>Limitada:</strong> restrita às funcionalidades e limites do Plano contratado;</li>
        <li><strong>Não exclusiva:</strong> a Licenciante pode conceder licenças semelhantes a outros clientes;</li>
        <li><strong>Intransferível:</strong> não pode ser cedida, sublicenciada ou transferida a terceiros sem autorização prévia e expressa da Licenciante;</li>
        <li><strong>Revogável:</strong> pode ser suspensa ou encerrada a qualquer momento em caso de violação desta Licença;</li>
        <li><strong>Pessoal:</strong> destinada exclusivamente ao uso interno do Licenciado e seus Usuários Finais autorizados.</li>
      </ul>
      <p>Esta licença <strong>não transfere ao Licenciado qualquer direito de propriedade</strong> sobre o Sistema, seu código-fonte, design, marca ou qualquer outro ativo intelectual da Licenciante.</p>

      <h2>3. RESTRIÇÕES DE USO</h2>
      <div class="aviso">É expressamente proibido ao Licenciado e a qualquer Usuário Final, sob pena de rescisão imediata da licença e responsabilização civil e criminal:</div>
      <ul>
        <li>Copiar, reproduzir, duplicar ou armazenar qualquer parte do código-fonte, banco de dados, interfaces ou lógica de negócio do Sistema;</li>
        <li>Modificar, adaptar, traduzir, alterar ou criar obras derivadas baseadas no Sistema ou em qualquer de seus componentes;</li>
        <li>Redistribuir, publicar, compartilhar ou disponibilizar o Sistema ou partes dele a terceiros, a qualquer título;</li>
        <li>Revender, sublicenciar, alugar, arrendar ou comercializar o acesso ao Sistema sem autorização expressa e por escrito da Licenciante;</li>
        <li>Clonar, recriar, imitar ou desenvolver sistema semelhante com base em funcionalidades, design, fluxos ou lógica observados no Sistema;</li>
        <li>Realizar engenharia reversa, descompilação, desmontagem ou qualquer tentativa de obter o código-fonte do Sistema;</li>
        <li>Acessar ou tentar acessar o código-fonte, arquivos de configuração, banco de dados ou infraestrutura do Sistema por meios não autorizados;</li>
        <li>Remover, ocultar ou alterar avisos de direitos autorais, marcas registradas ou qualquer identificação de propriedade da Licenciante;</li>
        <li>Utilizar o Sistema para fins ilegais, fraudulentos ou que violem direitos de terceiros;</li>
        <li>Automatizar o acesso ao Sistema de forma não prevista ou autorizada pela Licenciante;</li>
        <li>Compartilhar credenciais de acesso com pessoas não autorizadas pelo Plano contratado.</li>
      </ul>

      <h2>4. PROPRIEDADE INTELECTUAL</h2>
      <p>O Sistema LRV Cloud Manager, incluindo mas não se limitando a seu código-fonte, código compilado, design visual, identidade visual, marca, logotipo, arquitetura de software, banco de dados, documentação, APIs e funcionalidades, é de propriedade exclusiva da <strong>LRV Web / LRV Cloud</strong>, protegido pela Lei nº 9.609/1998 (Lei de Software), Lei nº 9.610/1998 (Lei de Direitos Autorais) e demais normas aplicáveis.</p>
      <p>Nenhuma disposição desta Licença transfere ao Licenciado qualquer direito de propriedade intelectual sobre o Sistema ou qualquer de seus componentes. O Licenciado reconhece expressamente que o uso do Sistema não lhe confere qualquer direito além do acesso limitado previsto nesta Licença.</p>
      <p>A marca "LRV Cloud Manager", "LRV Web" e "LRV Cloud", bem como seus logotipos e elementos visuais associados, são de uso exclusivo da Licenciante e não podem ser utilizados pelo Licenciado sem autorização prévia e por escrito.</p>

      <h2>5. USO PERMITIDO</h2>
      <p>Dentro dos limites desta Licença, é permitido ao Licenciado:</p>
      <ul>
        <li>Acessar e utilizar o Sistema exclusivamente por meio da interface web disponibilizada pela Licenciante;</li>
        <li>Utilizar as funcionalidades disponíveis no Plano contratado, conforme descrito na proposta comercial;</li>
        <li>Autorizar Usuários Finais a acessar o Sistema dentro dos limites do Plano;</li>
        <li>Utilizar as APIs disponibilizadas oficialmente pela Licenciante, conforme documentação fornecida;</li>
        <li>Exportar seus próprios dados cadastrais e de uso, quando tal funcionalidade estiver disponível no Sistema.</li>
      </ul>

      <h2>6. SUSPENSÃO E CANCELAMENTO</h2>
      <h3>6.1 Suspensão por inadimplência</h3>
      <p>O acesso ao Sistema será suspenso automaticamente em caso de atraso no pagamento das mensalidades, sem necessidade de notificação prévia, após o vencimento da fatura. A reativação ocorrerá mediante regularização do débito.</p>
      <h3>6.2 Suspensão por uso indevido</h3>
      <p>A Licenciante reserva-se o direito de suspender imediatamente o acesso do Licenciado em caso de uso que viole esta Licença, os Termos de Uso, a legislação vigente ou que cause danos à Licenciante, a outros clientes ou a terceiros.</p>
      <h3>6.3 Rescisão por violação da licença</h3>
      <p>A violação de qualquer disposição desta Licença, especialmente das restrições previstas na Cláusula 3, implicará rescisão imediata e automática da licença, sem direito a reembolso, além de responsabilização civil e criminal do infrator nos termos da legislação brasileira.</p>
      <h3>6.4 Cancelamento voluntário</h3>
      <p>O Licenciado pode cancelar sua assinatura a qualquer momento. O acesso permanecerá ativo até o fim do período já pago. Após o cancelamento, os dados do Licenciado poderão ser excluídos conforme a Política de Privacidade.</p>

      <h2>7. LIMITAÇÃO DE RESPONSABILIDADE</h2>
      <p>O Sistema é fornecido "no estado em que se encontra" (<em>as is</em>), sem garantias expressas ou implícitas de qualquer natureza, incluindo, sem limitação, garantias de adequação a uma finalidade específica ou de ausência de erros.</p>
      <p>A Licenciante não se responsabiliza por:</p>
      <ul>
        <li>Perdas indiretas, incidentais, especiais ou consequentes decorrentes do uso ou impossibilidade de uso do Sistema;</li>
        <li>Danos causados por uso incorreto, negligente ou não autorizado do Sistema pelo Licenciado ou Usuários Finais;</li>
        <li>Falhas de infraestrutura de terceiros, incluindo provedores de internet, data centers, serviços de nuvem ou outros fornecedores externos;</li>
        <li>Perda de dados decorrente de falhas não causadas diretamente pela Licenciante;</li>
        <li>Interrupções temporárias de serviço para manutenção, atualizações ou por causas de força maior;</li>
        <li>Ataques cibernéticos, invasões ou acessos não autorizados que não decorram de negligência comprovada da Licenciante.</li>
      </ul>
      <p>Em qualquer hipótese, a responsabilidade máxima da Licenciante ficará limitada ao valor pago pelo Licenciado nos últimos 3 (três) meses de serviço.</p>

      <h2>8. ATUALIZAÇÕES E MODIFICAÇÕES DO SISTEMA</h2>
      <p>A Licenciante reserva-se o direito de, a qualquer momento e sem obrigação de aviso prévio:</p>
      <ul>
        <li>Adicionar, modificar, suspender ou remover funcionalidades do Sistema;</li>
        <li>Alterar a interface, fluxos de uso e integrações disponíveis;</li>
        <li>Descontinuar versões antigas do Sistema;</li>
        <li>Realizar manutenções programadas ou emergenciais que impliquem indisponibilidade temporária.</li>
      </ul>
      <p>A Licenciante não tem obrigação de manter versões anteriores do Sistema disponíveis. Alterações significativas serão comunicadas com antecedência razoável quando possível.</p>

      <h2>9. CONFIDENCIALIDADE</h2>
      <p>O Licenciado compromete-se a manter em sigilo todas as informações confidenciais obtidas em razão do uso do Sistema, incluindo:</p>
      <ul>
        <li>Detalhes sobre a arquitetura, funcionamento interno e lógica de negócio do Sistema;</li>
        <li>Informações sobre preços, condições comerciais e estratégias da Licenciante;</li>
        <li>Dados de outros clientes ou usuários que eventualmente venha a ter conhecimento;</li>
        <li>Qualquer informação identificada como confidencial pela Licenciante.</li>
      </ul>
      <p>A obrigação de confidencialidade permanece vigente por 5 (cinco) anos após o encerramento da relação contratual.</p>

      <h2>10. PROTEÇÃO DE DADOS</h2>
      <p>O tratamento de dados pessoais realizado no âmbito do uso do Sistema obedece à Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD) e está detalhado na Política de Privacidade da Licenciante, disponível em <a href="/privacidade">/privacidade</a>.</p>

      <h2>11. LEI APLICÁVEL E FORO</h2>
      <p>Esta Licença é regida exclusivamente pelas leis da República Federativa do Brasil. Fica eleito o foro da Comarca de <strong>São José do Rio Preto — SP</strong> para dirimir quaisquer controvérsias decorrentes desta Licença, com renúncia expressa a qualquer outro, por mais privilegiado que seja.</p>

      <h2>12. ACEITE E VIGÊNCIA</h2>
      <div class="destaque">
        O uso do Sistema LRV Cloud Manager, seja por meio de cadastro, login, acesso ao painel ou qualquer outra forma de utilização, implica a aceitação integral, expressa e irrevogável de todos os termos desta Licença, dos Termos de Uso e da Política de Privacidade da Licenciante.
      </div>
      <p>Esta Licença entra em vigor na data de seu aceite pelo Licenciado e permanece vigente enquanto durar a relação contratual, sobrevivendo ao cancelamento nas disposições relativas a propriedade intelectual, confidencialidade e limitação de responsabilidade.</p>
      <p>A Licenciante reserva-se o direito de atualizar esta Licença a qualquer momento. A versão vigente estará sempre disponível em <strong>/licenca</strong>. O uso continuado do Sistema após a publicação de alterações implica aceitação das novas condições.</p>

      <div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">
        © <?php echo date('Y'); ?> LRV Web / LRV Cloud — Todos os direitos reservados.<br>
        Dúvidas sobre esta licença? Entre em <a href="/contato" style="color:#4F46E5;">contato</a>.
      </div>

    <?php endif; ?>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
