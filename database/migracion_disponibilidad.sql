-- ==============================================================================
-- Migración: Sistema de Disponibilidad Inmobiliaria en Tiempo Real
-- Ejecutar UNA vez sobre una BD creada con schema.sql anterior.
-- Para instalaciones nuevas, schema.sql ya incluye estos cambios.
-- ==============================================================================

USE `inmobiliaria_db`;

-- 1. Nuevo estado transaccional en el ENUM ------------------------------------
ALTER TABLE `inmuebles`
    MODIFY `estado` ENUM('DISPONIBLE', 'EN_PROCESO_RESERVA', 'RESERVADO', 'ARRENDADO', 'MANTENIMIENTO')
    NOT NULL DEFAULT 'DISPONIBLE';

-- 2. Control de expiración y titular de la reserva temporal -------------------
ALTER TABLE `inmuebles`
    ADD COLUMN `reserva_expira_at` DATETIME NULL AFTER `estado`,
    ADD COLUMN `reserva_usuario_id` INT UNSIGNED NULL AFTER `reserva_expira_at`,
    ADD KEY `idx_inmuebles_expira` (`estado`, `reserva_expira_at`),
    ADD CONSTRAINT `fk_inmuebles_reserva_usuario`
        FOREIGN KEY (`reserva_usuario_id`) REFERENCES `usuarios` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL;

-- 3. Rutina de expiración automática (llamar desde el botón/cron o desde
--    PublicoController::index() antes de listar el catálogo) -------------------
-- Libera a DISPONIBLE toda reserva temporal vencida:
--   UPDATE inmuebles
--      SET estado = 'DISPONIBLE', reserva_expira_at = NULL, reserva_usuario_id = NULL
--    WHERE estado = 'EN_PROCESO_RESERVA'
--      AND (reserva_expira_at <= NOW()
--           OR (reserva_expira_at IS NULL AND updated_at <= NOW() - INTERVAL 24 HOUR));

-- 4. Consulta de verificación de estados en tiempo real -----------------------
-- SELECT estado, COUNT(*) AS total, MAX(updated_at) AS ultima_actualizacion
--   FROM inmuebles GROUP BY estado;
