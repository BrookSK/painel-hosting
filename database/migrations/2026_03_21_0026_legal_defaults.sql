-- Migration: 0026 — Textos padrão para Termos de Uso, Política de Privacidade e Licença de Uso
-- Insere os valores iniciais em settings. Usa INSERT IGNORE para não sobrescrever edições existentes.

INSERT IGNORE INTO settings (`key`, `value`) VALUES

('legal.terms_html', '<h1>Termos de Uso</h1>
<p class="legal-meta">Versão 1.0 &nbsp;·&nbsp; Vigência: a partir de 21 de março de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>

<div class="aviso">⚠️ Ao utilizar o sistema, você concorda integralmente com estes Termos. Caso não concorde, não utilize o serviço.</div>

<h2>1. Aceitação</h2>
<p>O acesso e uso do sistema LRV Cloud Manager implica a aceitação integral e irrevogável destes Termos de Uso, da Política de Privacidade e da Licença de Uso Proprietária. Estes documentos formam o contrato entre o Usuário e a LRV Web / LRV Cloud.</p>

<h2>2. Descrição do Serviço</h2>
<p>O LRV Cloud Manager é uma plataforma SaaS de gerenciamento de infraestrutura em nuvem, oferecendo provisionamento de servidores VPS, monitoramento, suporte via tickets e chat, gerenciamento de e-mails e demais funcionalidades descritas nos planos disponíveis.</p>

<h2>3. Cadastro e Conta</h2>
<ul>
  <li>O usuário deve fornecer informações verdadeiras, completas e atualizadas no cadastro.</li>
  <li>É responsabilidade do usuário manter a confidencialidade de suas credenciais de acesso.</li>
  <li>O usuário é responsável por todas as atividades realizadas em sua conta.</li>
  <li>É proibido compartilhar credenciais com terceiros não autorizados pelo plano contratado.</li>
  <li>A LRV Web reserva-se o direito de suspender contas com informações falsas ou que violem estes Termos.</li>
</ul>

<h2>4. Planos e Pagamentos</h2>
<ul>
  <li>O acesso ao sistema está condicionado à contratação de um plano e ao pagamento das mensalidades em dia.</li>
  <li>O não pagamento na data de vencimento pode resultar na suspensão automática do acesso.</li>
  <li>Os preços dos planos podem ser alterados com aviso prévio de 30 dias.</li>
  <li>Reembolsos são analisados caso a caso conforme a política vigente.</li>
</ul>

<h2>5. Uso Aceitável</h2>
<p>É expressamente proibido utilizar o sistema para:</p>
<ul>
  <li>Atividades ilegais, fraudulentas ou que violem direitos de terceiros;</li>
  <li>Envio de spam, phishing ou qualquer forma de comunicação não solicitada em massa;</li>
  <li>Hospedagem ou distribuição de malware, vírus ou conteúdo malicioso;</li>
  <li>Ataques a outros sistemas, redes ou infraestruturas;</li>
  <li>Mineração de criptomoedas sem autorização expressa;</li>
  <li>Qualquer atividade que sobrecarregue indevidamente a infraestrutura compartilhada.</li>
</ul>

<h2>6. Disponibilidade e SLA</h2>
<p>A LRV Web empenha-se em manter o sistema disponível 24/7, mas não garante disponibilidade ininterrupta. Manutenções programadas serão comunicadas com antecedência. Incidentes podem ser acompanhados em <a href="/status">/status</a>.</p>

<h2>7. Propriedade Intelectual</h2>
<p>Todo o conteúdo, código, design e funcionalidades do sistema são de propriedade exclusiva da LRV Web / LRV Cloud, protegidos pela legislação brasileira de direitos autorais e software. O uso do sistema não transfere ao usuário qualquer direito de propriedade intelectual.</p>

<h2>8. Limitação de Responsabilidade</h2>
<p>A LRV Web não se responsabiliza por perdas indiretas, lucros cessantes ou danos consequentes decorrentes do uso ou impossibilidade de uso do sistema. A responsabilidade máxima fica limitada ao valor pago nos últimos 3 meses de serviço.</p>

<h2>9. Rescisão</h2>
<p>O usuário pode cancelar sua assinatura a qualquer momento. A LRV Web pode encerrar o acesso em caso de violação destes Termos, sem direito a reembolso proporcional. Após o cancelamento, os dados podem ser excluídos conforme a Política de Privacidade.</p>

<h2>10. Alterações nos Termos</h2>
<p>Estes Termos podem ser atualizados a qualquer momento. A versão vigente estará sempre disponível em <a href="/termos">/termos</a>. O uso continuado do sistema após alterações implica aceitação das novas condições.</p>

<h2>11. Lei Aplicável e Foro</h2>
<p>Estes Termos são regidos pelas leis da República Federativa do Brasil. Fica eleito o foro da Comarca de São José do Rio Preto — SP para dirimir quaisquer controvérsias.</p>

<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">
  Dúvidas? Entre em <a href="/contato">contato</a>.
</div>'),

('legal.privacy_html', '<h1>Política de Privacidade</h1>
<p class="legal-meta">Versão 1.0 &nbsp;·&nbsp; Vigência: a partir de 21 de março de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>

<div class="destaque">Esta Política descreve como coletamos, usamos, armazenamos e protegemos seus dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).</div>

<h2>1. Controlador dos Dados</h2>
<p>O controlador dos dados pessoais tratados nesta plataforma é a <strong>LRV Web / LRV Cloud</strong>, com sede em São José do Rio Preto — SP, Brasil. Para exercer seus direitos ou tirar dúvidas, acesse <a href="/contato">/contato</a>.</p>

<h2>2. Dados Coletados</h2>
<p>Coletamos os seguintes dados para a prestação do serviço:</p>
<ul>
  <li><strong>Dados de cadastro:</strong> nome, e-mail, senha (armazenada com hash bcrypt), endereço;</li>
  <li><strong>Dados de uso:</strong> logs de acesso, ações realizadas no painel, sessões de terminal;</li>
  <li><strong>Dados de pagamento:</strong> processados pelos gateways Asaas e Stripe — não armazenamos dados de cartão;</li>
  <li><strong>Dados técnicos:</strong> endereço IP, user agent, timestamps de login/logout;</li>
  <li><strong>Dados de suporte:</strong> mensagens de tickets e chat para atendimento.</li>
</ul>

<h2>3. Finalidade do Tratamento</h2>
<ul>
  <li>Prestação dos serviços contratados (provisionamento de VPS, e-mail, monitoramento);</li>
  <li>Autenticação e segurança da conta;</li>
  <li>Processamento de pagamentos e gestão de assinaturas;</li>
  <li>Comunicação sobre o serviço (alertas, notificações, suporte);</li>
  <li>Cumprimento de obrigações legais;</li>
  <li>Melhoria contínua do sistema.</li>
</ul>

<h2>4. Base Legal</h2>
<p>O tratamento de dados é realizado com base em: execução de contrato (art. 7º, V da LGPD), cumprimento de obrigação legal (art. 7º, II) e legítimo interesse (art. 7º, IX), conforme aplicável a cada finalidade.</p>

<h2>5. Compartilhamento de Dados</h2>
<p>Seus dados podem ser compartilhados com:</p>
<ul>
  <li><strong>Gateways de pagamento</strong> (Asaas, Stripe) para processamento de cobranças;</li>
  <li><strong>Provedores de infraestrutura</strong> para hospedagem e operação do sistema;</li>
  <li><strong>Autoridades competentes</strong> quando exigido por lei ou ordem judicial.</li>
</ul>
<p>Não vendemos, alugamos ou compartilhamos seus dados com terceiros para fins de marketing.</p>

<h2>6. Retenção de Dados</h2>
<p>Mantemos seus dados pelo período necessário à prestação do serviço e cumprimento de obrigações legais. Após o cancelamento da conta, os dados podem ser excluídos em até 90 dias, salvo obrigação legal de retenção.</p>

<h2>7. Segurança</h2>
<p>Adotamos medidas técnicas e organizacionais para proteger seus dados, incluindo:</p>
<ul>
  <li>Senhas armazenadas com hash bcrypt;</li>
  <li>Comunicações via HTTPS/TLS;</li>
  <li>Autenticação de dois fatores (2FA) disponível;</li>
  <li>Logs de auditoria de acesso e ações;</li>
  <li>Bloqueio automático por tentativas excessivas de login.</li>
</ul>

<h2>8. Seus Direitos (LGPD)</h2>
<p>Você tem direito a:</p>
<ul>
  <li>Confirmar a existência de tratamento de seus dados;</li>
  <li>Acessar seus dados pessoais;</li>
  <li>Corrigir dados incompletos, inexatos ou desatualizados;</li>
  <li>Solicitar a anonimização, bloqueio ou eliminação de dados desnecessários;</li>
  <li>Portabilidade dos dados;</li>
  <li>Revogar o consentimento, quando aplicável.</li>
</ul>
<p>Para exercer seus direitos, acesse <a href="/contato">/contato</a>.</p>

<h2>9. Cookies</h2>
<p>Utilizamos cookies de sessão estritamente necessários para o funcionamento do sistema (autenticação). Não utilizamos cookies de rastreamento ou publicidade.</p>

<h2>10. Alterações nesta Política</h2>
<p>Esta Política pode ser atualizada a qualquer momento. A versão vigente estará sempre disponível em <a href="/privacidade">/privacidade</a>. Alterações significativas serão comunicadas por e-mail.</p>

<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">
  Dúvidas sobre privacidade? Entre em <a href="/contato">contato</a>.
</div>'),

('legal.license_html', '');
