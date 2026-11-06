-- ==============================================================================
-- SISTEMA INMOBILIARIO PROPTECH (MODELO OPERATIVO TIPO HOUM)
-- Consultas SQL Estratégicas y Operativas de Negocio
-- Proyecto Formativo SENA ADSO
-- Archivo: database/consultas.sql
-- ==============================================================================

USE `inmobiliaria_db`;

-- ==============================================================================
-- CONSULTA 1: Catálogo General de Inmuebles con Información del Propietario (JOIN)
-- ------------------------------------------------------------------------------
-- Propósito: Visualizar en el panel administrativo o del Houmer la ficha completa
-- de cada propiedad junto a los datos de contacto de su dueño (consignante).
-- ==============================================================================
SELECT 
    i.id AS inmueble_id,
    i.titulo AS titulo_inmueble,
    i.tipo AS tipo_inmueble,
    i.direccion,
    i.ciudad,
    i.estrato,
    i.area_m2,
    i.habitaciones,
    i.banos,
    CONCAT('$ ', FORMAT(i.precio_canon, 2)) AS canon_mensual,
    CASE 
        WHEN i.precio_venta IS NOT NULL THEN CONCAT('$ ', FORMAT(i.precio_venta, 2))
        ELSE 'No está en venta'
    END AS precio_venta,
    i.estado AS estado_inmueble,
    u.id AS propietario_id,
    u.nombre AS propietario_nombre,
    u.telefono AS propietario_telefono,
    u.email AS propietario_email
FROM `inmuebles` i
INNER JOIN `usuarios` u ON i.propietario_id = u.id
ORDER BY i.created_at DESC;


-- ==============================================================================
-- CONSULTA 2: Agenda Operativa de Citas y Visitas en Terreno por Asesor ("Houmer")
-- ------------------------------------------------------------------------------
-- Propósito: Permite a los asesores comerciales y a la administración revisar 
-- el cronograma de visitas, saber qué cliente asistirá, qué inmueble visitará
-- y cuál es el estado de la cita.
-- ==============================================================================
SELECT 
    c.id AS cita_id,
    c.fecha_hora,
    c.estado AS estado_cita,
    asesor.nombre AS houmer_asignado,
    asesor.telefono AS telefono_houmer,
    cliente.nombre AS cliente_interesado,
    cliente.telefono AS telefono_cliente,
    cliente.email AS email_cliente,
    i.id AS inmueble_id,
    i.titulo AS titulo_inmueble,
    i.direccion AS direccion_inmueble,
    c.observaciones
FROM `citas` c
INNER JOIN `inmuebles` i ON c.inmueble_id = i.id
INNER JOIN `usuarios` cliente ON c.cliente_id = cliente.id
INNER JOIN `usuarios` asesor ON c.asesor_id = asesor.id
ORDER BY c.fecha_hora ASC;


-- ==============================================================================
-- CONSULTA 3: Reporte Financiero de Cartera, Contratos y Recaudos por Arrendamiento
-- ------------------------------------------------------------------------------
-- Propósito: Consolidar el estado de cada contrato activo o finalizado, calculando
-- el total efectivamente recaudado (PAGADO), pagos pendientes y rechazos.
-- ==============================================================================
SELECT 
    ct.id AS contrato_id,
    ct.estado AS estado_contrato,
    ct.fecha_inicio,
    ct.fecha_fin,
    CONCAT('$ ', FORMAT(ct.canon_mensual, 2)) AS canon_acordado,
    arrendatario.nombre AS arrendatario_nombre,
    arrendatario.telefono AS arrendatario_telefono,
    i.titulo AS inmueble_arrendado,
    propietario.nombre AS propietario_inmueble,
    -- Agregaciones financieras por contrato
    COUNT(p.id) AS total_intentos_pago,
    CONCAT('$ ', FORMAT(COALESCE(SUM(CASE WHEN p.estado = 'PAGADO' THEN p.monto ELSE 0 END), 0), 2)) AS total_recaudado,
    CONCAT('$ ', FORMAT(COALESCE(SUM(CASE WHEN p.estado = 'PENDIENTE' THEN p.monto ELSE 0 END), 0), 2)) AS saldo_pendiente,
    CONCAT('$ ', FORMAT(COALESCE(SUM(CASE WHEN p.estado = 'RECHAZADO' THEN p.monto ELSE 0 END), 0), 2)) AS monto_rechazado
FROM `contratos` ct
INNER JOIN `inmuebles` i ON ct.inmueble_id = i.id
INNER JOIN `usuarios` arrendatario ON ct.arrendatario_id = arrendatario.id
INNER JOIN `usuarios` propietario ON i.propietario_id = propietario.id
LEFT JOIN `pagos` p ON ct.id = p.contrato_id
GROUP BY 
    ct.id, ct.estado, ct.fecha_inicio, ct.fecha_fin, ct.canon_mensual,
    arrendatario.nombre, arrendatario.telefono, i.titulo, propietario.nombre
ORDER BY ct.id ASC;


-- ==============================================================================
-- CONSULTA 4: Búsqueda y Filtro de Inmuebles Disponibles para el Portal Web
-- ------------------------------------------------------------------------------
-- Propósito: Consulta utilizada en el controlador PHP (MVC) para filtrar
-- el catálogo de cara a los usuarios (arrendatarios) según tipo, estrato y rango de canon.
-- ==============================================================================
SELECT 
    i.id,
    i.titulo,
    i.tipo,
    i.direccion,
    i.estrato,
    i.habitaciones,
    i.banos,
    i.area_m2,
    i.precio_canon,
    i.estado
FROM `inmuebles` i
WHERE i.estado = 'DISPONIBLE'
  AND i.ciudad = 'Bogotá'
  AND (i.precio_canon BETWEEN 1500000 AND 6000000)
ORDER BY i.precio_canon ASC;


-- ==============================================================================
-- CONSULTA 5: Dashboard de Indicadores Clave de Desempeño (KPIs Houm)
-- ------------------------------------------------------------------------------
-- Propósito: Métricas consolidadas en una sola consulta para la vista inicial
-- del panel de control del Administrador.
-- ==============================================================================
SELECT 
    (SELECT COUNT(*) FROM `inmuebles` WHERE estado = 'DISPONIBLE') AS inmuebles_disponibles,
    (SELECT COUNT(*) FROM `inmuebles` WHERE estado = 'ARRENDADO') AS inmuebles_arrendados,
    (SELECT COUNT(*) FROM `contratos` WHERE estado = 'ACTIVO') AS contratos_activos,
    (SELECT COUNT(*) FROM `citas` WHERE estado = 'PROGRAMADA' AND fecha_hora >= NOW()) AS citas_proximas,
    (SELECT CONCAT('$ ', FORMAT(COALESCE(SUM(monto), 0), 2)) FROM `pagos` WHERE estado = 'PAGADO') AS recaudo_historico_total,
    (SELECT CONCAT('$ ', FORMAT(COALESCE(SUM(monto), 0), 2)) FROM `pagos` WHERE estado = 'PENDIENTE') AS cartera_por_cobrar;
