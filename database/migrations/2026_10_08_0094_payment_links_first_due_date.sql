-- Permite ao admin definir a data de vencimento do PRIMEIRO pagamento ao gerar o link.
-- Sem valor (NULL) = comportamento padrão (vence no dia seguinte para boleto/PIX).
ALTER TABLE payment_links ADD COLUMN first_due_date DATE NULL DEFAULT NULL AFTER periodo;
