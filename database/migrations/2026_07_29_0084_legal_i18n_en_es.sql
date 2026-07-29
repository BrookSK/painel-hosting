-- Migration: 0084 — English (en-US) and Spanish (es-ES) translations for Terms of Service and Privacy Policy

-- 1. Terms of Service — English (en-US)
INSERT INTO settings (`key`, `value`) VALUES ('legal.terms_html.en-US', '<h1>Terms of Service</h1>
<p class="legal-meta">Version 1.0 &nbsp;·&nbsp; Effective: March 21, 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="aviso">⚠️ By using the system, you fully agree to these Terms. If you do not agree, do not use the service.</div>
<h2>1. Acceptance</h2>
<p>Access to and use of the LRV Cloud Manager system implies full and irrevocable acceptance of these Terms of Service, the Privacy Policy, and the Proprietary License Agreement.</p>
<h2>2. Service Description</h2>
<p>LRV Cloud Manager is a SaaS platform for cloud infrastructure management, offering VPS server provisioning, monitoring, ticket and chat support, email management, and other features described in the available plans.</p>
<h2>3. Account</h2>
<ul>
  <li>Users must provide truthful, complete, and up-to-date information during registration.</li>
  <li>Users are responsible for maintaining the confidentiality of their access credentials.</li>
  <li>Users are responsible for all activities performed under their account.</li>
  <li>Sharing credentials with unauthorized third parties is prohibited.</li>
  <li>LRV Web reserves the right to suspend accounts with false information or that violate these Terms.</li>
</ul>
<h2>4. Plans and Payments</h2>
<ul>
  <li>Access to the system is subject to contracting a plan and timely payment of monthly fees.</li>
  <li>Failure to pay by the due date may result in automatic suspension of access.</li>
  <li>Plan prices may be changed with 30 days'' prior notice.</li>
  <li>Refunds are analyzed on a case-by-case basis in accordance with the applicable policy.</li>
</ul>
<h2>5. Acceptable Use</h2>
<p>It is expressly prohibited to use the system for:</p>
<ul>
  <li>Illegal or fraudulent activities, or activities that violate third-party rights;</li>
  <li>Sending spam, phishing, or any form of unsolicited mass communication;</li>
  <li>Hosting or distributing malware, viruses, or malicious content;</li>
  <li>Attacks against other systems, networks, or infrastructure;</li>
  <li>Cryptocurrency mining without express authorization.</li>
</ul>
<h2>6. Availability</h2>
<p>LRV Web strives to keep the system available 24/7 but does not guarantee uninterrupted availability. Incidents can be monitored at <a href="/status">/status</a>.</p>
<h2>7. Intellectual Property</h2>
<p>All content, code, design, and features of the system are the exclusive property of LRV Web / LRV Cloud. Use of the system does not transfer any intellectual property rights to the user.</p>
<h2>8. Limitation of Liability</h2>
<p>LRV Web shall not be liable for indirect losses or consequential damages. Maximum liability is limited to the amount paid in the last 3 months of service.</p>
<h2>9. Termination</h2>
<p>Users may cancel their subscription at any time. LRV Web may terminate access in the event of a violation of these Terms. After cancellation, data may be deleted in accordance with the Privacy Policy.</p>
<h2>10. Changes</h2>
<p>These Terms may be updated at any time. The current version will always be available at <a href="/termos">/termos</a>.</p>
<h2>11. Applicable Law</h2>
<p>These Terms are governed by the laws of the Federative Republic of Brazil. The courts of São José do Rio Preto — SP are elected as the competent jurisdiction.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">Questions? Get in <a href="/contato">touch</a>.</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 2. Terms of Service — Spanish (es-ES)
INSERT INTO settings (`key`, `value`) VALUES ('legal.terms_html.es-ES', '<h1>Términos de Uso</h1>
<p class="legal-meta">Versión 1.0 &nbsp;·&nbsp; Vigencia: a partir del 21 de marzo de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="aviso">⚠️ Al utilizar el sistema, usted acepta íntegramente estos Términos. Si no está de acuerdo, no utilice el servicio.</div>
<h2>1. Aceptación</h2>
<p>El acceso y uso del sistema LRV Cloud Manager implica la aceptación integral e irrevocable de estos Términos de Uso, la Política de Privacidad y la Licencia de Uso Propietaria.</p>
<h2>2. Descripción del Servicio</h2>
<p>LRV Cloud Manager es una plataforma SaaS de gestión de infraestructura en la nube, que ofrece aprovisionamiento de servidores VPS, monitoreo, soporte mediante tickets y chat, gestión de correos electrónicos y demás funcionalidades descritas en los planes disponibles.</p>
<h2>3. Registro y Cuenta</h2>
<ul>
  <li>El usuario debe proporcionar información veraz, completa y actualizada al registrarse.</li>
  <li>Es responsabilidad del usuario mantener la confidencialidad de sus credenciales de acceso.</li>
  <li>El usuario es responsable de todas las actividades realizadas en su cuenta.</li>
  <li>Está prohibido compartir credenciales con terceros no autorizados por el plan contratado.</li>
  <li>LRV Web se reserva el derecho de suspender cuentas con información falsa o que infrinjan estos Términos.</li>
</ul>
<h2>4. Planes y Pagos</h2>
<ul>
  <li>El acceso al sistema está condicionado a la contratación de un plan y al pago puntual de las mensualidades.</li>
  <li>El impago en la fecha de vencimiento puede resultar en la suspensión automática del acceso.</li>
  <li>Los precios de los planes pueden modificarse con un preaviso de 30 días.</li>
  <li>Los reembolsos se analizan caso por caso conforme a la política vigente.</li>
</ul>
<h2>5. Uso Aceptable</h2>
<p>Está expresamente prohibido utilizar el sistema para:</p>
<ul>
  <li>Actividades ilegales, fraudulentas o que vulneren derechos de terceros;</li>
  <li>Envío de spam, phishing o cualquier forma de comunicación masiva no solicitada;</li>
  <li>Alojamiento o distribución de malware, virus o contenido malicioso;</li>
  <li>Ataques contra otros sistemas, redes o infraestructuras;</li>
  <li>Minería de criptomonedas sin autorización expresa.</li>
</ul>
<h2>6. Disponibilidad</h2>
<p>LRV Web se esfuerza por mantener el sistema disponible las 24 horas del día, los 7 días de la semana, pero no garantiza disponibilidad ininterrumpida. Los incidentes pueden consultarse en <a href="/status">/status</a>.</p>
<h2>7. Propiedad Intelectual</h2>
<p>Todo el contenido, código, diseño y funcionalidades del sistema son propiedad exclusiva de LRV Web / LRV Cloud. El uso del sistema no transfiere al usuario ningún derecho de propiedad intelectual.</p>
<h2>8. Limitación de Responsabilidad</h2>
<p>LRV Web no se responsabiliza por pérdidas indirectas ni daños consecuentes. La responsabilidad máxima se limita al monto pagado en los últimos 3 meses de servicio.</p>
<h2>9. Rescisión</h2>
<p>El usuario puede cancelar su suscripción en cualquier momento. LRV Web puede cancelar el acceso en caso de violación de estos Términos. Tras la cancelación, los datos podrán eliminarse conforme a la Política de Privacidad.</p>
<h2>10. Modificaciones</h2>
<p>Estos Términos pueden actualizarse en cualquier momento. La versión vigente estará siempre disponible en <a href="/termos">/termos</a>.</p>
<h2>11. Ley Aplicable y Fuero</h2>
<p>Estos Términos se rigen por las leyes de la República Federativa de Brasil. Se elige el fuero de la Comarca de São José do Rio Preto — SP para resolver cualquier controversia.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">¿Dudas? Póngase en <a href="/contato">contacto</a>.</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 3. Privacy Policy — English (en-US)
INSERT INTO settings (`key`, `value`) VALUES ('legal.privacy_html.en-US', '<h1>Privacy Policy</h1>
<p class="legal-meta">Version 1.0 &nbsp;·&nbsp; Effective: March 21, 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="destaque">This Policy describes how we collect, use, store, and protect your personal data, in compliance with the LGPD (Lei nº 13.709/2018).</div>
<h2>1. Data Controller</h2>
<p>The data controller is <strong>LRV Web / LRV Cloud</strong>, headquartered in São José do Rio Preto — SP, Brasil. Contact: <a href="/contato">/contato</a>.</p>
<h2>2. Data Collected</h2>
<ul>
  <li><strong>Registration:</strong> name, email, password (bcrypt hash), address;</li>
  <li><strong>Usage:</strong> access logs, panel actions, terminal sessions;</li>
  <li><strong>Payment:</strong> processed by Asaas and Stripe gateways — we do not store card data;</li>
  <li><strong>Technical:</strong> IP address, user agent, login/logout timestamps;</li>
  <li><strong>Support:</strong> ticket and chat messages.</li>
</ul>
<h2>3. Purpose</h2>
<ul>
  <li>Provision of contracted services;</li>
  <li>Authentication and account security;</li>
  <li>Payment processing;</li>
  <li>Service-related communications;</li>
  <li>Compliance with legal obligations.</li>
</ul>
<h2>4. Legal Basis</h2>
<p>Contract performance (art. 7, V of the LGPD), compliance with legal obligation (art. 7, II), and legitimate interest (art. 7, IX).</p>
<h2>5. Sharing</h2>
<p>Data may be shared with payment gateways (Asaas, Stripe), infrastructure providers, and competent authorities when required by law. We do not sell data for marketing purposes.</p>
<h2>6. Retention</h2>
<p>Data is retained for as long as necessary to provide the service. After cancellation, data may be deleted within 90 days, except where required by law.</p>
<h2>7. Security</h2>
<ul>
  <li>Passwords hashed with bcrypt;</li>
  <li>Communications via HTTPS/TLS;</li>
  <li>Two-factor authentication (2FA) available;</li>
  <li>Audit logs;</li>
  <li>Account lockout after excessive login attempts.</li>
</ul>
<h2>8. Your Rights (LGPD)</h2>
<p>You may request access, correction, deletion, portability, and withdrawal of consent. Contact us at <a href="/contato">/contato</a>.</p>
<h2>9. Cookies</h2>
<p>We use only strictly necessary session cookies for authentication. No tracking or advertising cookies are used.</p>
<h2>10. Changes</h2>
<p>This Policy may be updated at any time. The current version is available at <a href="/privacidade">/privacidade</a>.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">Questions? Get in <a href="/contato">touch</a>.</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 4. Privacy Policy — Spanish (es-ES)
INSERT INTO settings (`key`, `value`) VALUES ('legal.privacy_html.es-ES', '<h1>Política de Privacidad</h1>
<p class="legal-meta">Versión 1.0 &nbsp;·&nbsp; Vigencia: a partir del 21 de marzo de 2026 &nbsp;·&nbsp; LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil</p>
<div class="destaque">Esta Política describe cómo recopilamos, utilizamos, almacenamos y protegemos sus datos personales, en cumplimiento de la LGPD (Lei nº 13.709/2018).</div>
<h2>1. Responsable del Tratamiento</h2>
<p>El responsable del tratamiento es <strong>LRV Web / LRV Cloud</strong>, con sede en São José do Rio Preto — SP, Brasil. Contacto: <a href="/contato">/contato</a>.</p>
<h2>2. Datos Recopilados</h2>
<ul>
  <li><strong>Registro:</strong> nombre, correo electrónico, contraseña (hash bcrypt), dirección;</li>
  <li><strong>Uso:</strong> registros de acceso, acciones en el panel, sesiones de terminal;</li>
  <li><strong>Pago:</strong> procesados por las pasarelas Asaas y Stripe — no almacenamos datos de tarjeta;</li>
  <li><strong>Técnicos:</strong> dirección IP, user agent, marcas de tiempo de inicio/cierre de sesión;</li>
  <li><strong>Soporte:</strong> mensajes de tickets y chat.</li>
</ul>
<h2>3. Finalidad</h2>
<ul>
  <li>Prestación de los servicios contratados;</li>
  <li>Autenticación y seguridad de la cuenta;</li>
  <li>Procesamiento de pagos;</li>
  <li>Comunicaciones relativas al servicio;</li>
  <li>Cumplimiento de obligaciones legales.</li>
</ul>
<h2>4. Base Legal</h2>
<p>Ejecución de contrato (art. 7º, V de la LGPD), cumplimiento de obligación legal (art. 7º, II) e interés legítimo (art. 7º, IX).</p>
<h2>5. Compartición de Datos</h2>
<p>Los datos pueden compartirse con pasarelas de pago (Asaas, Stripe), proveedores de infraestructura y autoridades competentes cuando lo exija la ley. No vendemos datos con fines de marketing.</p>
<h2>6. Retención</h2>
<p>Los datos se conservan durante el tiempo necesario para la prestación del servicio. Tras la cancelación, podrán eliminarse en un plazo de 90 días, salvo obligación legal en contrario.</p>
<h2>7. Seguridad</h2>
<ul>
  <li>Contraseñas con hash bcrypt;</li>
  <li>Comunicaciones mediante HTTPS/TLS;</li>
  <li>Autenticación de dos factores (2FA) disponible;</li>
  <li>Registros de auditoría;</li>
  <li>Bloqueo por intentos excesivos de inicio de sesión.</li>
</ul>
<h2>8. Sus Derechos (LGPD)</h2>
<p>Puede solicitar acceso, corrección, eliminación, portabilidad y revocación del consentimiento. Contáctenos en <a href="/contato">/contato</a>.</p>
<h2>9. Cookies</h2>
<p>Utilizamos únicamente cookies de sesión estrictamente necesarias para la autenticación. No se utilizan cookies de rastreo ni publicidad.</p>
<h2>10. Modificaciones</h2>
<p>Esta Política puede actualizarse en cualquier momento. La versión vigente está disponible en <a href="/privacidade">/privacidade</a>.</p>
<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">¿Dudas? Póngase en <a href="/contato">contacto</a>.</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
