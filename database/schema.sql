-- ==============================================================================
-- SISTEMA INMOBILIARIO PROPTECH (MODELO OPERATIVO TIPO HOUM)
-- Proyecto Formativo SENA ADSO - Base de Datos Relacional Normalizada (3FN)
-- Arquitectura: MySQL / MariaDB (Motor InnoDB)
-- Codificación: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Archivo: database/schema.sql
-- ==============================================================================

-- Creación y selección de la base de datos
CREATE DATABASE IF NOT EXISTS `inmobiliaria_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `inmobiliaria_db`;

-- Desactivar temporalmente revisión de llaves foráneas para scripts idempotentes
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- ELIMINACIÓN DE TABLAS SI YA EXISTEN (Orden inverso por dependencias de FK)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pagos`;
DROP TABLE IF EXISTS `contratos`;
DROP TABLE IF EXISTS `citas`;
DROP TABLE IF EXISTS `inmuebles`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- TABLA 1: usuarios
-- ------------------------------------------------------------------------------
-- Representa a todos los actores del ecosistema inmobiliario según el modelo Houm:
--  - 'administrador': Control de operaciones, contratos, liquidaciones y plataforma.
--  - 'houmer': Asesor/agente inmobiliario en campo responsable de visitas y captaciones.
--  - 'propietario': Titular o inversionista que consigna inmuebles para arrendar/vender.
--  - 'arrendatario': Cliente final interesado en postular, alquilar o habitar inmuebles.
-- ==============================================================================
CREATE TABLE `usuarios` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(120) NOT NULL COMMENT 'Nombre completo o razón social del usuario',
    `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'Correo electrónico corporativo o personal (login único)',
    `password` VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada (compatible con PHP password_hash - BCRYPT/Argon2)',
    `telefono` VARCHAR(20) NOT NULL COMMENT 'Número telefónico de contacto o WhatsApp para coordinación',
    `rol` ENUM('administrador', 'houmer', 'propietario', 'arrendatario') NOT NULL DEFAULT 'arrendatario',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro en la plataforma',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificación del registro',
    
    INDEX `idx_usuarios_rol` (`rol`),
    INDEX `idx_usuarios_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios y roles operativos del sistema inmobiliario';


-- ==============================================================================
-- TABLA 2: inmuebles
-- ------------------------------------------------------------------------------
-- Inventario de propiedades consignadas por propietarios.
-- Relación 1:N con usuarios (un propietario puede tener múltiples propiedades).
-- Cumple 3FN: no almacena datos de contacto del propietario (dependen de usuarios).
-- ==============================================================================
CREATE TABLE `inmuebles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `propietario_id` INT UNSIGNED NOT NULL COMMENT 'FK a usuarios: Propietario consignante',
    `titulo` VARCHAR(200) NOT NULL COMMENT 'Título comercial para el portal inmobiliario',
    `descripcion` TEXT NOT NULL COMMENT 'Descripción detallada de amenidades, acabados y entorno',
    `tipo` ENUM('apartamento', 'casa', 'local', 'bodega') NOT NULL COMMENT 'Tipología del bien inmueble',
    `precio_canon` DECIMAL(12,2) NULL COMMENT 'Canon de arrendamiento mensual en COP (NULL si solo está en venta)',
    `precio_venta` DECIMAL(14,2) NULL COMMENT 'Precio de venta del inmueble en COP (NULL si solo está en arriendo)',
    `direccion` VARCHAR(255) NOT NULL COMMENT 'Nomenclatura catastral o física del inmueble',
    `ciudad` VARCHAR(100) NOT NULL DEFAULT 'Bogotá' COMMENT 'Ciudad donde se ubica el predio',
    `estrato` TINYINT UNSIGNED NOT NULL COMMENT 'Estrato socioeconómico en Colombia (1 al 6)',
    `habitaciones` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Cantidad de alcobas o habitaciones',
    `banos` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Número de baños completos o sociales',
    `area_m2` DECIMAL(8,2) NOT NULL COMMENT 'Área privada o construida en metros cuadrados',
    `estado` ENUM('DISPONIBLE', 'RESERVADO', 'ARRENDADO', 'MANTENIMIENTO') NOT NULL DEFAULT 'DISPONIBLE' COMMENT 'Estado operativo en el pipeline Houm',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de publicación o consignación',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización de datos del inmueble',
    
    -- Restricción de Integridad Referencial
    CONSTRAINT `fk_inmuebles_propietario`
        FOREIGN KEY (`propietario_id`) REFERENCES `usuarios` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    -- Restricción de Dominio (Estratos válidos en Colombia)
    CONSTRAINT `chk_inmuebles_estrato` 
        CHECK (`estrato` BETWEEN 1 AND 6),
        
    -- Índices para optimización de filtros en catálogo web
    INDEX `idx_inmuebles_estado` (`estado`),
    INDEX `idx_inmuebles_tipo_ciudad` (`tipo`, `ciudad`),
    INDEX `idx_inmuebles_precios` (`precio_canon`, `precio_venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inventario inmobiliario disponible y en gestión';


-- ==============================================================================
-- TABLA 3: citas
-- ------------------------------------------------------------------------------
-- Gestión de visitas de prospección presencial o virtual.
-- Núcleo del modelo Houm: coordina al cliente interesado con un "Houmer" asignado.
-- Relaciona 3 entidades independientes: inmuebles, cliente (usuarios) y asesor (usuarios).
-- ==============================================================================
CREATE TABLE `citas` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `inmueble_id` INT UNSIGNED NOT NULL COMMENT 'FK a inmuebles: Predio a visitar',
    `cliente_id` INT UNSIGNED NOT NULL COMMENT 'FK a usuarios: Cliente/potencial arrendatario que agenda',
    `asesor_id` INT UNSIGNED NOT NULL COMMENT 'FK a usuarios: Houmer o asesor encargado de coordinar la visita',
    `fecha_hora` DATETIME NOT NULL COMMENT 'Fecha y hora programada para la visita',
    `estado` ENUM('PROGRAMADA', 'COMPLETADA', 'CANCELADA') NOT NULL DEFAULT 'PROGRAMADA' COMMENT 'Estado del agendamiento',
    `observaciones` TEXT NULL COMMENT 'Anotaciones del Houmer tras la visita o feedback del cliente',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Restricciones de Integridad Referencial
    CONSTRAINT `fk_citas_inmueble`
        FOREIGN KEY (`inmueble_id`) REFERENCES `inmuebles` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
        
    CONSTRAINT `fk_citas_cliente`
        FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    CONSTRAINT `fk_citas_asesor`
        FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    -- Índices para consulta ágil de agendas y calendarios de asesores
    INDEX `idx_citas_fecha_hora` (`fecha_hora`),
    INDEX `idx_citas_estado` (`estado`),
    INDEX `idx_citas_asesor_agenda` (`asesor_id`, `fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Agendamiento operativo de visitas con Houmers';


-- ==============================================================================
-- TABLA 4: contratos
-- ------------------------------------------------------------------------------
-- Representa el acuerdo de arrendamiento legalizado entre la inmobiliaria,
-- el propietario (a través del inmueble) y el arrendatario adjudicado.
-- Cumple 3FN: no almacena el canon original de publicación ni la dirección;
-- almacena el canon_mensual formalmente acordado y firmado.
-- ==============================================================================
CREATE TABLE `contratos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `inmueble_id` INT UNSIGNED NOT NULL COMMENT 'FK a inmuebles: Inmueble objeto del contrato',
    `arrendatario_id` INT UNSIGNED NOT NULL COMMENT 'FK a usuarios: Arrendatario que suscribe el contrato',
    `fecha_inicio` DATE NOT NULL COMMENT 'Fecha de inicio de vigencia del arrendamiento',
    `fecha_fin` DATE NOT NULL COMMENT 'Fecha de finalización o renovación contractual',
    `canon_mensual` DECIMAL(12,2) NOT NULL COMMENT 'Monto mensual pactado en el contrato (COP)',
    `estado` ENUM('ACTIVO', 'FINALIZADO', 'INCUMPLIDO') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado jurídico del contrato',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Restricciones de Integridad Referencial
    CONSTRAINT `fk_contratos_inmueble`
        FOREIGN KEY (`inmueble_id`) REFERENCES `inmuebles` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    CONSTRAINT `fk_contratos_arrendatario`
        FOREIGN KEY (`arrendatario_id`) REFERENCES `usuarios` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    -- Restricción de Coherencia Temporal
    CONSTRAINT `chk_contrato_vigencia` 
        CHECK (`fecha_fin` >= `fecha_inicio`),
        
    -- Índices para reportes de vencimientos y auditoría de cartera
    INDEX `idx_contratos_estado` (`estado`),
    INDEX `idx_contratos_periodo` (`fecha_inicio`, `fecha_fin`),
    INDEX `idx_contratos_arrendatario` (`arrendatario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contratos legales de arrendamiento inmobiliario';


-- ==============================================================================
-- TABLA 5: pagos
-- ------------------------------------------------------------------------------
-- Control de recaudo y cartera de los cánones mensuales.
-- Cada pago está asociado a un contrato específico y posee una referencia única
-- para conciliación con pasarelas (PSE, Wompi, PayU, bancos).
-- ==============================================================================
CREATE TABLE `pagos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contrato_id` INT UNSIGNED NOT NULL COMMENT 'FK a contratos: Contrato sobre el cual se liquida el canon',
    `referencia_pago` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador único de transacción (ej. pasarela o bancario)',
    `monto` DECIMAL(12,2) NOT NULL COMMENT 'Valor monetario recaudado o facturado en COP',
    `fecha_pago` DATE NOT NULL COMMENT 'Fecha efectiva o límite de pago',
    `estado` ENUM('PAGADO', 'PENDIENTE', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Estado de la conciliación bancaria',
    `metodo_pago` ENUM('PSE', 'TRANSFERENCIA_BANCARIA', 'TARJETA_CREDITO', 'EFECTIVO') NOT NULL COMMENT 'Canal de recepción del dinero',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Restricción de Integridad Referencial
    CONSTRAINT `fk_pagos_contrato`
        FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
        
    -- Índices para analítica de recaudo, cartera vencida y conciliaciones
    INDEX `idx_pagos_estado` (`estado`),
    INDEX `idx_pagos_fecha_pago` (`fecha_pago`),
    INDEX `idx_pagos_contrato_estado` (`contrato_id`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recaudos y estados de pago de cánones inmobiliarios';
