-- ==============================================================================
-- Migración: Gestión de Usuarios (RBAC), Contratos (verificación/PDF) y Reportes
-- Ejecutar UNA vez sobre una BD ya creada con database/schema.sql anterior.
-- Para instalaciones nuevas, schema.sql ya incluye codigo_verificacion.
-- ==============================================================================

USE `inmobiliaria_db`;

-- 1. Llave única de verificación de contratos ---------------------------------
ALTER TABLE `contratos`
    ADD COLUMN `codigo_verificacion` VARCHAR(32) NULL AFTER `canon_mensual`;

ALTER TABLE `contratos`
    ADD UNIQUE KEY `uq_contratos_codigo_verificacion` (`codigo_verificacion`);

-- Backfill determinista para contratos existentes (MD5 es estable por id).
UPDATE `contratos`
SET `codigo_verificacion` = CONCAT('HOUM-', UPPER(SUBSTRING(MD5(CONCAT('houm-', `id`)), 1, 12)))
WHERE `codigo_verificacion` IS NULL;

-- 2. Vista: cartera morosa -----------------------------------------------------
-- Contratos INCUMPLIDO o con pagos PENDIENTE/RECHAZADO vencidos a hoy.
CREATE OR REPLACE VIEW `v_carter_morosa` AS
SELECT
    ct.id AS contrato_id,
    ct.codigo_verificacion,
    ct.estado AS estado_contrato,
    i.titulo AS inmueble,
    a.nombre AS arrendatario,
    ct.canon_mensual,
    COUNT(p.id) AS total_pagos,
    COALESCE(SUM(CASE WHEN p.estado = 'PAGADO' THEN p.monto ELSE 0 END), 0) AS total_pagado,
    COALESCE(SUM(CASE WHEN p.estado = 'PENDIENTE' THEN p.monto ELSE 0 END), 0) AS total_pendiente,
    COALESCE(SUM(CASE WHEN p.estado = 'RECHAZADO' THEN p.monto ELSE 0 END), 0) AS total_rechazado,
    (COALESCE(SUM(CASE WHEN p.estado IN ('PENDIENTE', 'RECHAZADO') THEN p.monto ELSE 0 END), 0)) AS saldo_moroso,
    MIN(CASE WHEN p.estado IN ('PENDIENTE', 'RECHAZADO') THEN p.fecha_pago END) AS primer_vencimiento,
    DATEDIFF(CURDATE(), MIN(CASE WHEN p.estado IN ('PENDIENTE', 'RECHAZADO') THEN p.fecha_pago END)) AS dias_mora
FROM `contratos` ct
INNER JOIN `inmuebles` i ON i.id = ct.inmueble_id
INNER JOIN `usuarios` a ON a.id = ct.arrendatario_id
LEFT JOIN `pagos` p ON p.contrato_id = ct.id
GROUP BY ct.id, ct.codigo_verificacion, ct.estado, i.titulo, a.nombre, ct.canon_mensual
HAVING ct.estado = 'INCUMPLIDO'
    OR (ct.estado = 'ACTIVO' AND MIN(CASE WHEN p.estado IN ('PENDIENTE', 'RECHAZADO') THEN p.fecha_pago END) < CURDATE())
ORDER BY saldo_moroso DESC;

-- 3. Vista: tiempos de colocación ---------------------------------------------
-- Días entre la publicación del inmueble (created_at) y el inicio del contrato.
CREATE OR REPLACE VIEW `v_tiempos_colocacion` AS
SELECT
    ct.id AS contrato_id,
    i.id AS inmueble_id,
    i.titulo AS inmueble,
    i.tipo,
    i.ciudad,
    i.created_at AS fecha_publicacion,
    ct.fecha_inicio,
    DATEDIFF(ct.fecha_inicio, DATE(i.created_at)) AS dias_colocacion
FROM `contratos` ct
INNER JOIN `inmuebles` i ON i.id = ct.inmueble_id;
