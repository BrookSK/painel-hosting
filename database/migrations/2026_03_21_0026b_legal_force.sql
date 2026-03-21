-- Migration: 0026b — Força os textos completos de Termos e Privacidade
-- Sobrescreve o placeholder gerado automaticamente pelo sistema de inicialização.

UPDATE settings SET `value` = '<h1>Termos de Uso</h1>
<p class="legal-meta">Versão 1.0 &nbsp;·&nbsp; Vigência: a partir de 21 de março de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="aviso">⚠️ Ao utilizar o sistema, você concorda integralmente com estes Termos. Caso não concorde, não utilize o serviço.</div>
<h2>1. Aceitação</h2>
<p>O acesso e uso do sistema LRV Cloud Manager implica a aceitação integral e irrevogável destes Termos de Uso, da Política de Privacidade e da Licença de Uso Proprietária.</p>
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
  <li>Mineração de criptomoedas sem autorização expressa.</li>
</ul>
<h2>6. Disponibilidade</h2>
<p>A LRV Web empenha-se em manter o sistema disponível 24/7, mas não garante disponibilidade ininterrupta. Incidentes podem ser acompanhados em <a href="/status">/status</a>.</p>
<h2>7. Propriedade Intelectual</h2>
<p>Todo o conteúdo, código, design e funcionalidades do sistema são de propriedade exclusiva da LRV Web / LRV Cloud. O uso do sistema não transfere ao usuário qualquer direito de propriedade intelectual.</p>
<h2>8. Limitação de Responsabilidade</h2>
<p>A LRV Web não se responsabiliza por perdas indiretas ou danos consequentes. A responsabilidade máxima fica limitada ao valor pago nos últimos 3 meses de serviço.</p>
<h2>9. Rescisão</h2>
<p>O usuário pode cancelar sua assinatura a qualquer momento. A LRV Web pode encerrar o acesso em caso de violação destes Termos. Após o cancelamento, os dados podem ser excluídos conforme a Política de Privacidade.</p>
<h2>10. Alterações</h2>
<p>Estes Termos podem ser atualizados a qualquer momento. A versão vigente estará sempre disponível em <a href="/termos">/termos</a>.</p>
<h2>11. Lei Aplicável e Foro</h2>
<p>Estes Termos são regidos pelas leis da República Federativa do Brasil. Fica eleito o foro da Comarca de São José do Rio Preto — SP.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">Dúvidas? Entre em <a href="/contato">contato</a>.</div>'
WHERE `key` = 'legal.terms_html';

UPDATE settings SET `value` = '<h1>Política de Privacidade</h1>
<p class="legal-meta">Versão 1.0 &nbsp;·&nbsp; Vigência: a partir de 21 de março de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="destaque">Esta Política descreve como coletamos, usamos, armazenamos e protegemos seus dados pessoais, em conformidade com a LGPD (Lei nº 13.709/2018).</div>
<h2>1. Controlador dos Dados</h2>
<p>O controlador é a <strong>LRV Web / LRV Cloud</strong>, com sede em São José do Rio Preto — SP, Brasil. Contato: <a href="/contato">/contato</a>.</p>
<h2>2. Dados Coletados</h2>
<ul>
  <li><strong>Cadastro:</strong> nome, e-mail, senha (hash bcrypt), endereço;</li>
  <li><strong>Uso:</strong> logs de acesso, ações no painel, sessões de terminal;</li>
  <li><strong>Pagamento:</strong> processados pelos gateways Asaas e Stripe — não armazenamos dados de cartão;</li>
  <li><strong>Técnicos:</strong> endereço IP, user agent, timestamps de login/logout;</li>
  <li><strong>Suporte:</strong> mensagens de tickets e chat.</li>
</ul>
<h2>3. Finalidade</h2>
<ul>
  <li>Prestação dos serviços contratados;</li>
  <li>Autenticação e segurança da conta;</li>
  <li>Processamento de pagamentos;</li>
  <li>Comunicação sobre o serviço;</li>
  <li>Cumprimento de obrigações legais.</li>
</ul>
<h2>4. Base Legal</h2>
<p>Execução de contrato (art. 7º, V da LGPD), cumprimento de obrigação legal (art. 7º, II) e legítimo interesse (art. 7º, IX).</p>
<h2>5. Compartilhamento</h2>
<p>Dados podem ser compartilhados com gateways de pagamento (Asaas, Stripe), provedores de infraestrutura e autoridades competentes quando exigido por lei. Não vendemos dados para marketing.</p>
<h2>6. Retenção</h2>
<p>Dados são mantidos pelo período necessário ao serviço. Após cancelamento, podem ser excluídos em até 90 dias, salvo obrigação legal.</p>
<h2>7. Segurança</h2>
<ul>
  <li>Senhas com hash bcrypt;</li>
  <li>Comunicações via HTTPS/TLS;</li>
  <li>2FA disponível;</li>
  <li>Logs de auditoria;</li>
  <li>Bloqueio por tentativas excessivas de login.</li>
</ul>
<h2>8. Seus Direitos (LGPD)</h2>
<p>Você pode solicitar acesso, correção, exclusão, portabilidade e revogação de consentimento. Acesse <a href="/contato">/contato</a>.</p>
<h2>9. Cookies</h2>
<p>Utilizamos apenas cookies de sessão estritamente necessários para autenticação. Sem cookies de rastreamento ou publicidade.</p>
<h2>10. Alterações</h2>
<p>Esta Política pode ser atualizada a qualquer momento. Versão vigente em <a href="/privacidade">/privacidade</a>.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">Dúvidas? Entre em <a href="/contato">contato</a>.</div>'
WHERE `key` = 'legal.privacy_html';
