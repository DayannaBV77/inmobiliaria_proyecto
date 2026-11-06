-- ==============================================================================
-- SISTEMA INMOBILIARIO PROPTECH (MODELO OPERATIVO TIPO HOUM)
-- Datos de prueba para el mercado inmobiliario de Bogotá D.C., Colombia
-- Proyecto Formativo SENA ADSO
-- Archivo: database/datos_prueba.sql
-- Nota: La contraseña de todos los usuarios de prueba es: Password123*
--       (Hasheada con BCRYPT compatible con password_verify() de PHP)
-- ==============================================================================

USE `inmobiliaria_db`;

-- Deshabilitar chequeo de llaves foráneas para inserción limpia
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `pagos`;
TRUNCATE TABLE `contratos`;
TRUNCATE TABLE `citas`;
TRUNCATE TABLE `inmuebles`;
TRUNCATE TABLE `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. POBLADO DE LA TABLA: usuarios
-- ------------------------------------------------------------------------------
-- Se incluyen los 4 roles del ecosistema Houm:
-- Administrador, Houmers (asesores de campo), Propietarios y Arrendatarios.
-- Hash de 'Password123*': $2y$10$8.4Yj7u3lF8T0v3g0jHjce7E3vVz5.V3J2.Qp6zX9P6zX9P6zX9P6 (o equivalente estándar)
-- ==============================================================================
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `rol`, `created_at`) VALUES
-- Administrador Houm Colombia
(1, 'Laura Sofía Morales', 'laura.admin@houm.com.co', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573105550101', 'administrador', '2026-01-15 08:30:00'),

-- Houmers (Asesores comerciales y de visitas en terreno)
(2, 'Carlos Eduardo Restrepo', 'carlos.houmer@houm.com.co', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573158882020', 'houmer', '2026-02-01 09:00:00'),
(3, 'Valentina Gómez Pardo', 'valentina.houmer@houm.com.co', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573204443030', 'houmer', '2026-02-15 10:15:00'),

-- Propietarios (Dueños e inversionistas inmobiliarios en Bogotá)
(4, 'Felipe Andrés Mendoza', 'felipe.mendoza@gmail.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573001234567', 'propietario', '2026-03-01 11:20:00'),
(5, 'María del Pilar Cárdenas', 'pilar.cardenas@outlook.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573129876543', 'propietario', '2026-03-10 14:00:00'),
(6, 'Inversiones Los Cedros S.A.S.', 'inversiones.cedros@gmail.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573187654321', 'propietario', '2026-03-20 16:45:00'),

-- Clientes / Arrendatarios
(7, 'Daniel Santiago Peña', 'daniel.pena@hotmail.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573112345678', 'arrendatario', '2026-04-05 10:00:00'),
(8, 'Camila Andrea Torres', 'camila.torres@gmail.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573145678901', 'arrendatario', '2026-04-12 11:30:00'),
(9, 'Julián Mateo Vargas', 'julian.vargas@yahoo.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573167890123', 'arrendatario', '2026-04-18 15:00:00'),
(10, 'Catalina Rincón Ortiz', 'catalina.rincon@gmail.com', '$2y$10$6ztjR61wouyDRnb3fFZSUet8YoNre9gotzIH2a5P6jIvqLQIKodiu', '+573178901234', 'arrendatario', '2026-05-02 08:45:00');


-- ==============================================================================
-- 2. POBLADO DE LA TABLA: inmuebles
-- ------------------------------------------------------------------------------
-- Propiedades ubicadas en sectores de alta demanda inmobiliaria en Bogotá:
-- Chapinero Alto, Santa Bárbara Occidental, Cedritos, Park Way, Chicó Norte y Fontibón.
-- ==============================================================================
INSERT INTO `inmuebles` (`id`, `propietario_id`, `titulo`, `descripcion`, `tipo`, `precio_canon`, `precio_venta`, `direccion`, `ciudad`, `estrato`, `habitaciones`, `banos`, `area_m2`, `estado`, `created_at`) VALUES
(1, 4, 
 'Apartamento Moderno en Chapinero Alto con Vista a los Cerros', 
 'Espectacular apartamento iluminado de 2 habitaciones, cocina integral tipo americano, balcón con vista panorámica, parqueadero cubierto y depósito. Conjunto cerrado con gimnasio, terraza BBQ y vigilancia 24/7.', 
 'apartamento', 2800000.00, NULL, 
 'Carrera 4 # 58-32 Apto 502', 'Bogotá', 4, 2, 2, 68.50, 'ARRENDADO', '2026-04-01 10:00:00'),

(2, 5, 
 'Apartamento Familiar en Santa Bárbara Occidental cerca a Unicentro', 
 'Amplio apartamento clásico de 3 alcobas principales más cuarto de servicio, sala-comedor independiente con chimenea a leña, estudio cerrado, 2 parqueaderos en línea. Estratégica ubicación cerca a parques y centros comerciales.', 
 'apartamento', 4200000.00, 680000000.00, 
 'Calle 122 # 17A-45 Apto 301', 'Bogotá', 5, 3, 3, 115.00, 'DISPONIBLE', '2026-04-10 12:30:00'),

(3, 6, 
 'Apartaestudio Loft en Cedritos con Terraza Privada', 
 'Loft contemporáneo ideal para profesionales o nómadas digitales. Cuenta con zona de lavandería independiente, acabados en madera laminada, garaje independiente y caldera comunal.', 
 'apartamento', 1950000.00, NULL, 
 'Calle 142 # 13-20 Apto 404', 'Bogotá', 4, 1, 1, 46.00, 'RESERVADO', '2026-04-15 14:10:00'),

(4, 4, 
 'Casa Tradicional Remodelada en Park Way - Teusaquillo', 
 'Hermosa casa de arquitectura patrimonial conservada, 3 niveles totalmente adecuados para vivienda familiar o despacho profesional. Cuenta con jardín interior, patio de ropas y garaje para 2 vehículos.', 
 'casa', 5500000.00, 950000000.00, 
 'Carrera 21 # 39-15', 'Bogotá', 4, 4, 3, 210.00, 'DISPONIBLE', '2026-04-20 09:15:00'),

(5, 6, 
 'Local Comercial sobre Eje Principal en Chicó Norte', 
 'Local a pie de calle con excelente visibilidad y flujo peatonal continuo. Fachada en vidrio templado, punto de gas comercial, batería de baños para clientes y trifásica instalada. Apto para café o franquicia.', 
 'local', 8000000.00, NULL, 
 'Carrera 15 # 93-60 Local 102', 'Bogotá', 6, 1, 2, 92.00, 'DISPONIBLE', '2026-04-25 16:00:00'),

(6, 5, 
 'Bodega Industrial con Altura Doble en Montevideo - Fontibón', 
 'Bodega con piso de alta resistencia en concreto pulido (5 ton/m2), entrada para tractomula con cortina eléctrica, área administrativa de oficinas en segundo nivel y caseta de vigilancia.', 
 'bodega', 11500000.00, 1850000000.00, 
 'Calle 19 # 68D-40', 'Bogotá', 3, 1, 3, 480.00, 'MANTENIMIENTO', '2026-05-01 11:00:00');


-- ==============================================================================
-- 3. POBLADO DE LA TABLA: citas
-- ------------------------------------------------------------------------------
-- Registra visitas operativas coordinadas por los Houmers (Carlos y Valentina)
-- con diferentes clientes en los inmuebles consignados.
-- ==============================================================================
INSERT INTO `citas` (`id`, `inmueble_id`, `cliente_id`, `asesor_id`, `fecha_hora`, `estado`, `observaciones`, `created_at`) VALUES
(1, 2, 7, 2, '2026-11-10 10:00:00', 'PROGRAMADA', 
 'Cliente Daniel Peña busca mudanza inmediata por traslado laboral. Desea inspeccionar presión de agua y estado de parqueaderos.', '2026-11-05 14:00:00'),

(2, 4, 9, 3, '2026-11-12 15:30:00', 'PROGRAMADA', 
 'Cliente Julián Vargas solicita visita con su socia para evaluar viabilidad de bufete de abogados.', '2026-11-06 09:30:00'),

(3, 1, 8, 3, '2026-10-18 11:00:00', 'COMPLETADA', 
 'Visita exitosa con Camila Torres. La cliente quedó satisfecha con la iluminación y radicó solicitud de póliza de arrendamiento con afianzadora.', '2026-10-14 16:20:00'),

(4, 3, 10, 2, '2026-10-25 16:00:00', 'COMPLETADA', 
 'Visita concluida con Catalina Rincón. La cliente dejó comprobante de reserva bancaria para asegurar el canon.', '2026-10-22 10:15:00'),

(5, 5, 9, 2, '2026-10-28 09:00:00', 'CANCELADA', 
 'Cliente canceló la visita con 2 horas de antelación por reunión imprevista. Se reprogramará para la siguiente quincena.', '2026-10-26 18:00:00');


-- ==============================================================================
-- 4. POBLADO DE LA TABLA: contratos
-- ------------------------------------------------------------------------------
-- Contratos formales de arrendamiento vinculados a inmuebles y arrendatarios.
-- Contempla contratos ACTIVO, FINALIZADO e INCUMPLIDO.
-- ==============================================================================
INSERT INTO `contratos` (`id`, `inmueble_id`, `arrendatario_id`, `fecha_inicio`, `fecha_fin`, `canon_mensual`, `estado`, `created_at`) VALUES
-- Contrato vigente sobre Apartamento Chapinero Alto
(1, 1, 8, '2026-11-01', '2027-10-31', 2800000.00, 'ACTIVO', '2026-10-29 15:00:00'),

-- Contrato recién pactado sobre Loft Cedritos
(2, 3, 10, '2026-11-15', '2027-11-14', 1950000.00, 'ACTIVO', '2026-11-02 11:30:00'),

-- Contrato ejecutado satisfactoriamente (histórico año anterior)
(3, 2, 7, '2025-10-01', '2026-09-30', 3900000.00, 'FINALIZADO', '2025-09-25 10:00:00'),

-- Contrato en mora o causal de terminación jurídica
(4, 4, 9, '2026-02-01', '2027-01-31', 5500000.00, 'INCUMPLIDO', '2026-01-20 16:45:00'),

-- Contrato comercial culminado sin novedades
(5, 5, 7, '2025-05-01', '2026-04-30', 7500000.00, 'FINALIZADO', '2025-04-22 14:15:00');


-- ==============================================================================
-- 5. POBLADO DE LA TABLA: pagos
-- ------------------------------------------------------------------------------
-- Recaudos de cánones con diferentes pasarelas y estados operativos.
-- ==============================================================================
INSERT INTO `pagos` (`id`, `contrato_id`, `referencia_pago`, `monto`, `fecha_pago`, `estado`, `metodo_pago`, `created_at`) VALUES
-- Pagos del Contrato 1 (Camila Torres - Chapinero)
(1, 1, 'PAY-HOUM-202611-001', 2800000.00, '2026-11-02', 'PAGADO', 'PSE', '2026-11-02 08:25:14'),
(2, 1, 'PAY-HOUM-202612-002', 2800000.00, '2026-12-01', 'PENDIENTE', 'PSE', '2026-11-05 10:00:00'),

-- Pago del Contrato 2 (Catalina Rincón - Cedritos)
(3, 2, 'PAY-HOUM-202611-003', 1950000.00, '2026-11-04', 'PAGADO', 'PSE', '2026-11-04 14:10:55'),

-- Pagos del Contrato 3 (Daniel Peña - Santa Bárbara / Histórico)
(4, 3, 'PAY-HOUM-202609-004', 3900000.00, '2026-09-03', 'PAGADO', 'TRANSFERENCIA_BANCARIA', '2026-09-03 16:40:22'),

-- Pagos del Contrato 4 (Julián Vargas - Mora / Rechazo)
(5, 4, 'PAY-HOUM-202609-005', 5500000.00, '2026-09-05', 'RECHAZADO', 'TARJETA_CREDITO', '2026-09-05 23:15:08'),
(6, 4, 'PAY-HOUM-202610-006', 5500000.00, '2026-10-05', 'RECHAZADO', 'PSE', '2026-10-05 20:02:11'),
(7, 4, 'PAY-HOUM-202611-007', 5500000.00, '2026-11-01', 'PENDIENTE', 'TRANSFERENCIA_BANCARIA', '2026-11-01 07:00:00'),

-- Pago del Contrato 5 (Daniel Peña - Local Chicó / Histórico)
(8, 5, 'PAY-HOUM-202604-008', 7500000.00, '2026-04-02', 'PAGADO', 'TRANSFERENCIA_BANCARIA', '2026-04-02 11:15:30');
