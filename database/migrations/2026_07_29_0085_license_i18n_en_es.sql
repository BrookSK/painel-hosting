-- Migration: 0085 — English (en-US) and Spanish (es-ES) translations for Proprietary License Agreement

-- 1. Proprietary License Agreement — English (en-US)
INSERT INTO settings (`key`, `value`) VALUES ('legal.license_html.en-US', '<div class="legal-meta">
  Version 1.0 &nbsp;·&nbsp; Effective: March 21, 2026 &nbsp;·&nbsp;
  Company: LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil
</div>

<div class="aviso">
  ⚠️ READ CAREFULLY. Use of the LRV Cloud Manager system implies full and irrevocable acceptance of all terms of this License. If you do not agree with any provision, do not use the system.
</div>

<h2>1. DEFINITIONS</h2>
<p>For the purposes of this License, the terms below have the following meanings:</p>
<ul>
  <li><strong>System:</strong> the software named "LRV Cloud Manager", including all its features, interfaces, source code, databases, APIs, visual design, documentation, and any updates, made available as a service (SaaS) by the Licensor.</li>
  <li><strong>Licensor:</strong> LRV Web / LRV Cloud, exclusive holder of all intellectual property rights over the System.</li>
  <li><strong>Licensee:</strong> the natural or legal person who contracts access to the System through a plan subscription and acceptance of this License.</li>
  <li><strong>End User:</strong> any person authorized by the Licensee to access the System within the limits of the contracted plan.</li>
  <li><strong>Plan:</strong> the resource package contracted by the Licensee, as described on the plans page.</li>
</ul>

<h2>2. LICENSE GRANT</h2>
<p>Subject to compliance with this License and payment of monthly fees, the Licensor grants the Licensee a license that is:</p>
<ul>
  <li><strong>Limited:</strong> restricted to the features and limits of the contracted Plan;</li>
  <li><strong>Non-exclusive:</strong> the Licensor may grant similar licenses to other clients;</li>
  <li><strong>Non-transferable:</strong> it may not be assigned or sublicensed without prior express authorization;</li>
  <li><strong>Revocable:</strong> it may be suspended in the event of a violation of this License;</li>
  <li><strong>Personal:</strong> intended exclusively for the internal use of the Licensee and its authorized End Users.</li>
</ul>
<p>This license <strong>does not transfer any ownership rights</strong> over the System to the Licensee.</p>

<h2>3. USE RESTRICTIONS</h2>
<div class="aviso">The following actions are expressly prohibited to the Licensee, under penalty of immediate termination and civil and criminal liability:</div>
<ul>
  <li>Copy, reproduce, or store any part of the source code, database, or business logic of the System;</li>
  <li>Modify, adapt, or create derivative works based on the System;</li>
  <li>Redistribute, publish, or make the System available to third parties;</li>
  <li>Resell, sublicense, or commercialize access to the System without express authorization;</li>
  <li>Clone or recreate a similar system based on features observed in the System;</li>
  <li>Perform reverse engineering or any attempt to obtain the source code;</li>
  <li>Remove copyright notices or ownership identification of the Licensor;</li>
  <li>Use the System for illegal or fraudulent purposes;</li>
  <li>Share credentials with persons not authorized under the contracted Plan.</li>
</ul>

<h2>4. INTELLECTUAL PROPERTY</h2>
<p>The LRV Cloud Manager System, including source code, design, brand, logo, architecture, database, documentation, and APIs, is the exclusive property of <strong>LRV Web / LRV Cloud</strong>, protected by Lei nº 9.609/1998, Lei nº 9.610/1998, and other applicable legislation.</p>
<p>The trademarks "LRV Cloud Manager", "LRV Web", and "LRV Cloud" are for the exclusive use of the Licensor.</p>

<h2>5. PERMITTED USE</h2>
<ul>
  <li>Access and use the System exclusively through the provided web interface;</li>
  <li>Use the features available in the contracted Plan;</li>
  <li>Authorize End Users within the limits of the Plan;</li>
  <li>Use the officially provided APIs, as described in the documentation;</li>
  <li>Export your own data when such functionality is available.</li>
</ul>

<h2>6. SUSPENSION AND CANCELLATION</h2>
<h3>6.1 Suspension due to non-payment</h3>
<p>Access will be automatically suspended in the event of late payment. Reactivation will occur upon settlement of the outstanding balance.</p>
<h3>6.2 Suspension due to misuse</h3>
<p>The Licensor may immediately suspend access in the event of use that violates this License or causes harm to third parties.</p>
<h3>6.3 Termination due to breach</h3>
<p>Violation of this License results in immediate termination, without the right to a refund, in addition to civil and criminal liability.</p>
<h3>6.4 Voluntary cancellation</h3>
<p>The Licensee may cancel at any time. Access remains active until the end of the paid period.</p>

<h2>7. LIMITATION OF LIABILITY</h2>
<p>The System is provided "as is". The Licensor shall not be liable for indirect, incidental, or consequential losses. Maximum liability is limited to the amount paid in the last 3 months of service.</p>

<h2>8. SYSTEM UPDATES</h2>
<p>The Licensor may add, modify, or remove features at any time, perform maintenance, and discontinue older versions. Significant changes will be communicated with reasonable advance notice.</p>

<h2>9. CONFIDENTIALITY</h2>
<p>The Licensee undertakes to keep confidential all information regarding the architecture, internal operation, and business logic of the System. The confidentiality obligation remains in effect for 5 years after the termination of the contractual relationship.</p>

<h2>10. DATA PROTECTION</h2>
<p>The processing of personal data complies with the LGPD (Lei nº 13.709/2018) and is detailed in the Privacy Policy, available at <a href="/privacidade">/privacidade</a>.</p>

<h2>11. APPLICABLE LAW AND JURISDICTION</h2>
<p>This License is governed by the laws of the Federative Republic of Brazil. The courts of the District of <strong>São José do Rio Preto — SP</strong> are elected as the competent jurisdiction for any disputes.</p>

<h2>12. ACCEPTANCE AND DURATION</h2>
<div class="destaque">
  Use of the LRV Cloud Manager System, whether through registration, login, or any form of use, implies full and irrevocable acceptance of all terms of this License, the Terms of Service, and the Privacy Policy.
</div>
<p>This License takes effect on the date of its acceptance and remains in force for the duration of the contractual relationship, surviving cancellation with respect to provisions regarding intellectual property, confidentiality, and limitation of liability.</p>
<p>The Licensor may update this License at any time. The current version will always be available at <strong>/licenca</strong>.</p>

<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">
  © 2026 LRV Web / LRV Cloud — All rights reserved.<br>
  Questions about this license? Get in <a href="/contato">touch</a>.
</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 2. Proprietary License Agreement — Spanish (es-ES)
INSERT INTO settings (`key`, `value`) VALUES ('legal.license_html.es-ES', '<div class="legal-meta">
  Versión 1.0 &nbsp;·&nbsp; Vigencia: a partir del 21 de marzo de 2026 &nbsp;·&nbsp;
  Empresa: LRV Web / LRV Cloud &nbsp;·&nbsp; São José do Rio Preto — SP, Brasil
</div>

<div class="aviso">
  ⚠️ LEA ATENTAMENTE. El uso del sistema LRV Cloud Manager implica la aceptación integral e irrevocable de todos los términos de esta Licencia. Si no está de acuerdo con alguna disposición, no utilice el sistema.
</div>

<h2>1. DEFINICIONES</h2>
<p>A los efectos de esta Licencia, los términos siguientes tienen los significados indicados a continuación:</p>
<ul>
  <li><strong>Sistema:</strong> el software denominado "LRV Cloud Manager", incluyendo todas sus funcionalidades, interfaces, código fuente, bases de datos, APIs, diseño visual, documentación y cualesquiera actualizaciones, puesto a disposición como servicio (SaaS) por la Licenciante.</li>
  <li><strong>Licenciante:</strong> LRV Web / LRV Cloud, titular exclusiva de todos los derechos de propiedad intelectual sobre el Sistema.</li>
  <li><strong>Licenciatario:</strong> la persona física o jurídica que contrata el acceso al Sistema mediante la suscripción de un plan y la aceptación de esta Licencia.</li>
  <li><strong>Usuario Final:</strong> cualquier persona autorizada por el Licenciatario para acceder al Sistema dentro de los límites del plan contratado.</li>
  <li><strong>Plan:</strong> el paquete de recursos contratado por el Licenciatario, conforme a la descripción en la página de planes.</li>
</ul>

<h2>2. CONCESIÓN DE LICENCIA</h2>
<p>Sujeto al cumplimiento de esta Licencia y al pago de las mensualidades, la Licenciante concede al Licenciatario una licencia:</p>
<ul>
  <li><strong>Limitada:</strong> restringida a las funcionalidades y límites del Plan contratado;</li>
  <li><strong>No exclusiva:</strong> la Licenciante puede conceder licencias similares a otros clientes;</li>
  <li><strong>Intransferible:</strong> no puede cederse ni sublicenciarse sin autorización previa y expresa;</li>
  <li><strong>Revocable:</strong> puede suspenderse en caso de incumplimiento de esta Licencia;</li>
  <li><strong>Personal:</strong> destinada exclusivamente al uso interno del Licenciatario y sus Usuarios Finales autorizados.</li>
</ul>
<p>Esta licencia <strong>no transfiere al Licenciatario ningún derecho de propiedad</strong> sobre el Sistema.</p>

<h2>3. RESTRICCIONES DE USO</h2>
<div class="aviso">Queda expresamente prohibido al Licenciatario, bajo pena de rescisión inmediata y responsabilidad civil y penal:</div>
<ul>
  <li>Copiar, reproducir o almacenar cualquier parte del código fuente, base de datos o lógica de negocio del Sistema;</li>
  <li>Modificar, adaptar o crear obras derivadas basadas en el Sistema;</li>
  <li>Redistribuir, publicar o poner el Sistema a disposición de terceros;</li>
  <li>Revender, sublicenciar o comercializar el acceso al Sistema sin autorización expresa;</li>
  <li>Clonar o recrear un sistema similar basándose en funcionalidades observadas en el Sistema;</li>
  <li>Realizar ingeniería inversa o cualquier intento de obtener el código fuente;</li>
  <li>Eliminar avisos de derechos de autor o identificación de propiedad de la Licenciante;</li>
  <li>Utilizar el Sistema con fines ilegales o fraudulentos;</li>
  <li>Compartir credenciales con personas no autorizadas por el Plan contratado.</li>
</ul>

<h2>4. PROPIEDAD INTELECTUAL</h2>
<p>El Sistema LRV Cloud Manager, incluyendo código fuente, diseño, marca, logotipo, arquitectura, base de datos, documentación y APIs, es propiedad exclusiva de <strong>LRV Web / LRV Cloud</strong>, protegido por la Lei nº 9.609/1998, Lei nº 9.610/1998 y demás normativa aplicable.</p>
<p>Las marcas "LRV Cloud Manager", "LRV Web" y "LRV Cloud" son de uso exclusivo de la Licenciante.</p>

<h2>5. USO PERMITIDO</h2>
<ul>
  <li>Acceder y utilizar el Sistema exclusivamente a través de la interfaz web proporcionada;</li>
  <li>Utilizar las funcionalidades disponibles en el Plan contratado;</li>
  <li>Autorizar Usuarios Finales dentro de los límites del Plan;</li>
  <li>Utilizar las APIs oficialmente proporcionadas, conforme a la documentación;</li>
  <li>Exportar sus propios datos cuando dicha funcionalidad esté disponible.</li>
</ul>

<h2>6. SUSPENSIÓN Y CANCELACIÓN</h2>
<h3>6.1 Suspensión por impago</h3>
<p>El acceso será suspendido automáticamente en caso de retraso en el pago. La reactivación se producirá tras la regularización del adeudo.</p>
<h3>6.2 Suspensión por uso indebido</h3>
<p>La Licenciante podrá suspender inmediatamente el acceso en caso de uso que infrinja esta Licencia o cause perjuicios a terceros.</p>
<h3>6.3 Rescisión por incumplimiento</h3>
<p>El incumplimiento de esta Licencia conlleva la rescisión inmediata, sin derecho a reembolso, además de responsabilidad civil y penal.</p>
<h3>6.4 Cancelación voluntaria</h3>
<p>El Licenciatario puede cancelar en cualquier momento. El acceso permanece activo hasta el final del período pagado.</p>

<h2>7. LIMITACIÓN DE RESPONSABILIDAD</h2>
<p>El Sistema se proporciona "tal cual". La Licenciante no se responsabiliza por pérdidas indirectas, incidentales o consecuentes. La responsabilidad máxima se limita al importe pagado en los últimos 3 meses de servicio.</p>

<h2>8. ACTUALIZACIONES DEL SISTEMA</h2>
<p>La Licenciante puede añadir, modificar o eliminar funcionalidades en cualquier momento, realizar mantenimientos y descontinuar versiones anteriores. Los cambios significativos se comunicarán con antelación razonable.</p>

<h2>9. CONFIDENCIALIDAD</h2>
<p>El Licenciatario se compromete a mantener en secreto toda información relativa a la arquitectura, funcionamiento interno y lógica de negocio del Sistema. La obligación de confidencialidad permanece vigente durante 5 años tras la finalización de la relación contractual.</p>

<h2>10. PROTECCIÓN DE DATOS</h2>
<p>El tratamiento de datos personales se rige por la LGPD (Lei nº 13.709/2018) y se detalla en la Política de Privacidad, disponible en <a href="/privacidade">/privacidade</a>.</p>

<h2>11. LEY APLICABLE Y FUERO</h2>
<p>Esta Licencia se rige por las leyes de la República Federativa de Brasil. Se elige el fuero de la Comarca de <strong>São José do Rio Preto — SP</strong> para dirimir cualquier controversia.</p>

<h2>12. ACEPTACIÓN Y VIGENCIA</h2>
<div class="destaque">
  El uso del Sistema LRV Cloud Manager, ya sea mediante registro, inicio de sesión o cualquier forma de utilización, implica la aceptación integral e irrevocable de todos los términos de esta Licencia, los Términos de Uso y la Política de Privacidad.
</div>
<p>Esta Licencia entra en vigor en la fecha de su aceptación y permanece vigente mientras dure la relación contractual, subsistiendo tras la cancelación en lo relativo a las disposiciones sobre propiedad intelectual, confidencialidad y limitación de responsabilidad.</p>
<p>La Licenciante puede actualizar esta Licencia en cualquier momento. La versión vigente estará siempre disponible en <strong>/licenca</strong>.</p>

<div style="margin-top:36px;padding-top:24px;border-top:1px solid #f1f5f9;font-size:13px;color:#94a3b8;text-align:center;">
  © 2026 LRV Web / LRV Cloud — Todos los derechos reservados.<br>
  ¿Dudas sobre esta licencia? Póngase en <a href="/contato">contacto</a>.
</div>') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
