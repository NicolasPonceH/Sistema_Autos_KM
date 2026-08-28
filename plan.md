# Sistema de Control de Kilometraje y Mantención Vehicular

Plan de implementación. Este documento es la referencia para el desarrollo:
cada fase se implementa y se prueba antes de pasar a la siguiente.

---

## 1. Decisiones de diseño

Estas decisiones son deliberadas y no deben cambiarse sin justificación,
porque condicionan el modelo de datos completo.

### 1.1 El odómetro se reporta como lectura absoluta

El usuario informa el total que marca el tablero (ej. `45320`), no el
incremento (ej. `+10 km`). Si se guardaran incrementos, cualquier reporte
olvidado desfasa el kilometraje de forma permanente. Con lecturas absolutas
el sistema se autocorrige en el siguiente reporte.

La interfaz puede ofrecer ambos modos de ingreso, pero lo que se persiste es
siempre el total.

### 1.2 Se guarda el historial completo de lecturas

La tabla `lectura_odometro` es la fuente de verdad. El campo
`vehiculo.km_actual` es una copia denormalizada para consultas rápidas.

El historial permite auditoría (quién reportó qué y cuándo), corrección de
errores de tipeo sin perder trazabilidad, y reportes de uso por período.

### 1.3 Los intervalos de mantención NO se hardcodean

"Aceite cada 10.000 km" es un registro en `plan_mantencion`, no una constante
en el código. La misma estructura cubre filtro de aire cada 20.000, frenos
cada 30.000, e intervalos distintos según tipo de vehículo.

### 1.4 El umbral de aviso es configurable y la comparación es `<=`

El aviso a 500 km es un campo del plan (`umbral_aviso`), no una constante.

La condición es `km_faltantes <= umbral_aviso`, **incluyendo valores
negativos**. Si se compara contra un rango cerrado (entre 0 y 500), un salto
de 9.800 a 10.400 km nunca cae dentro de la ventana y ese vehículo jamás se
notifica.

### 1.5 Los tipos de vehículo van en tabla, no en enum

Catálogo inicial:

| Código | Nombre           |
|--------|------------------|
| SW     | Station Wagon    |
| SD     | Sedán            |
| HB     | Hatchback        |
| PU     | Camioneta/Pickup |
| SUV    | SUV              |
| VN     | Furgón           |
| CM     | Camión           |

---

## 2. Modelo de datos

### 2.1 Catálogo y vehículos

```sql
CREATE TABLE tipo_vehiculo (
  codigo      VARCHAR(5) PRIMARY KEY,   -- SW, SD, HB, PU, SUV, VN, CM
  nombre      VARCHAR(50) NOT NULL
);

CREATE TABLE vehiculo (
  patente        VARCHAR(10) PRIMARY KEY,
  tipo_codigo    VARCHAR(5) NOT NULL REFERENCES tipo_vehiculo(codigo),
  marca          VARCHAR(50),
  modelo         VARCHAR(50) NOT NULL,
  anio           SMALLINT NOT NULL,
  nro_motor      VARCHAR(40),
  nro_chasis     VARCHAR(40),
  km_actual      INTEGER NOT NULL DEFAULT 0,   -- copia denormalizada
  fecha_km       TIMESTAMP,                    -- cuándo se tomó esa lectura
  email_contacto VARCHAR(120) NOT NULL,
  activo         BOOLEAN DEFAULT TRUE,
  creado_en      TIMESTAMP DEFAULT NOW()
);
```

### 2.2 Historial de odómetro

```sql
CREATE TABLE lectura_odometro (
  id            BIGSERIAL PRIMARY KEY,
  patente       VARCHAR(10) NOT NULL REFERENCES vehiculo(patente),
  km            INTEGER NOT NULL CHECK (km >= 0),
  fecha         TIMESTAMP NOT NULL DEFAULT NOW(),
  reportado_por INTEGER REFERENCES usuario(id),
  origen        VARCHAR(20) DEFAULT 'MANUAL',   -- MANUAL, IMPORT, CORRECCION
  anulada       BOOLEAN DEFAULT FALSE,
  observacion   TEXT
);

CREATE INDEX ix_lectura_patente_fecha ON lectura_odometro(patente, fecha DESC);
```

### 2.3 Mantención

```sql
CREATE TABLE plan_mantencion (
  id            SERIAL PRIMARY KEY,
  nombre        VARCHAR(60) NOT NULL,      -- 'Cambio de aceite'
  intervalo_km  INTEGER NOT NULL,          -- 10000
  umbral_aviso  INTEGER NOT NULL DEFAULT 500,
  tipo_codigo   VARCHAR(5) REFERENCES tipo_vehiculo(codigo)  -- NULL = todos
);

CREATE TABLE evento_mantencion (
  id        BIGSERIAL PRIMARY KEY,
  patente   VARCHAR(10) NOT NULL REFERENCES vehiculo(patente),
  plan_id   INTEGER NOT NULL REFERENCES plan_mantencion(id),
  km_evento INTEGER NOT NULL,
  fecha     DATE NOT NULL,
  costo     NUMERIC(12,0),
  taller    VARCHAR(100),
  notas     TEXT
);
```

### 2.4 Control de notificaciones

```sql
CREATE TABLE notificacion_enviada (
  id           BIGSERIAL PRIMARY KEY,
  patente      VARCHAR(10) NOT NULL,
  plan_id      INTEGER NOT NULL,
  km_objetivo  INTEGER NOT NULL,   -- km en que corresponde el servicio
  enviada_en   TIMESTAMP DEFAULT NOW(),
  destinatario VARCHAR(120),
  estado       VARCHAR(15) DEFAULT 'ENVIADA',  -- ENVIADA, FALLIDA, REINTENTO
  UNIQUE (patente, plan_id, km_objetivo)
);
```

El `UNIQUE (patente, plan_id, km_objetivo)` es lo que impide enviar decenas
de correos si el usuario reporta kilometraje todos los días dentro de la
ventana de aviso.

---

## 3. Lógica de detección

Para cada vehículo activo y cada plan que le aplica (por tipo o global):

```
km_ultimo_servicio = MAX(km_evento) en evento_mantencion   -- 0 si nunca se hizo
km_objetivo        = km_ultimo_servicio + plan.intervalo_km
km_faltantes       = km_objetivo - vehiculo.km_actual

SI km_faltantes <= plan.umbral_aviso
   Y NO existe notificacion_enviada(patente, plan_id, km_objetivo):
       encolar correo
       registrar notificacion_enviada
```

Puntos críticos:

- Se usa `<=`, no un rango cerrado. Un salto de 9.800 a 10.400 debe disparar
  el aviso igual (ya vencido), no ignorarse.
- Si `km_faltantes` es negativo, cambia el texto del correo
  ("vencido hace X km" en lugar de "faltan X km"), pero es el mismo flujo.
- Al registrar un `evento_mantencion`, el `km_objetivo` se recalcula solo y
  la próxima notificación tendrá otra clave única. No hay que borrar nada.

---

## 4. Disparadores de evaluación

Ambos son necesarios.

**Al insertar una lectura de odómetro.** Evaluación inmediata solo del
vehículo afectado. Cubre el caso "reporté 9.600 y me llegó el aviso al
instante". El envío del correo se encola; no se ejecuta dentro de la
transacción de la lectura.

**Job diario programado.** Recorre todos los vehículos activos. Cubre
reintentos de correos fallidos, planes recién creados, cambios de umbral, y
vehículos que ya estaban dentro de la ventana antes de que existiera la regla.

---

## 5. Validaciones

| Validación | Regla |
|---|---|
| Patente | Formato chileno: `LLLL·NN` (desde 2007), `LL·NNNN` o `LLL·NNN` (antiguas). Normalizar a mayúsculas, sin puntos ni guiones, antes de guardar. |
| Odómetro no retrocede | Si la lectura nueva es menor a `km_actual`, rechazar. Override solo con `origen='CORRECCION'`, observación obligatoria y usuario con permiso. |
| Salto sospechoso | Si la diferencia con la lectura anterior supera 5.000 km, pedir confirmación explícita. Evita que un `453200` en vez de `45320` arruine el historial. |
| Año | Entre 1900 y (año actual + 1). |
| Motor / Chasis | Únicos si se usarán como identificador secundario. |
| Email | Formato válido y obligatorio (es el destino de las notificaciones). |

---

## 6. Fases de implementación

### Fase 1 — CRUD base
- Catálogo `tipo_vehiculo` con datos iniciales (seeder).
- Alta, edición y baja lógica de vehículos.
- Listado con filtros por patente y tipo.
- Validaciones de patente y año.

**Entregable:** se puede registrar y administrar una flota. Sin kilometraje aún.

### Fase 2 — Odómetro
- Formulario de reporte de lectura.
- Persistencia en `lectura_odometro` + actualización de `km_actual`.
- Historial de lecturas por vehículo.
- Validación de retroceso y de salto sospechoso.

**Entregable:** el kilometraje se mantiene actualizado y auditado.

### Fase 3 — Planes de mantención
- CRUD de `plan_mantencion`, con asignación opcional por tipo de vehículo.
- Registro de `evento_mantencion` (servicios realizados).
- Vista por vehículo: "próximo servicio en X km" para cada plan aplicable.

**Entregable:** el estado de mantención es visible, sin correos todavía.

### Fase 4 — Notificaciones
- Integración SMTP configurable por variable de entorno.
- En desarrollo, el driver escribe a log en vez de enviar correo real.
- Plantilla de correo (ver sección 7).
- Evaluación al insertar lectura + job diario programado.
- Registro en `notificacion_enviada` y manejo de fallos.

**Entregable:** sistema completo y funcional. Probar primero contra un buzón
interno antes de apuntar a direcciones reales.

### Fase 5 — Refinamientos
- Dashboard de vehículos próximos a vencer.
- Reporte de km recorridos por mes / por vehículo.
- Exportación a CSV o Excel.
- Mantención por tiempo además de kilometraje (cada 10.000 km **o** 12 meses,
  lo que ocurra primero).
- Roles y permisos.

**Las fases 1 a 4 constituyen el producto mínimo funcional.**

---

## 7. Contenido del correo

Datos que debe incluir:

- Patente, marca, modelo y tipo del vehículo.
- Nombre del servicio (ej. "Cambio de aceite").
- Kilometraje actual y fecha de esa lectura.
- Kilometraje objetivo del servicio.
- Kilómetros restantes, o kilómetros de atraso si ya está vencido.
- Fecha y kilometraje del último servicio del mismo tipo.

El asunto debe distinguir ambos casos, por ejemplo:
- `[ABCD12] Cambio de aceite próximo — faltan 420 km`
- `[ABCD12] Cambio de aceite VENCIDO — 180 km de atraso`

---

## 8. Stack sugerido

- **Framework:** Laravel o Django. Ambos aportan CRUD, autenticación, envío
  de correos y scheduler sin trabajo adicional, que es exactamente lo que
  este sistema necesita.
- **Base de datos:** PostgreSQL o MySQL.
- **Correo:** SMTP transaccional (Brevo, Mailgun o similar). Evitar Gmail
  directo: bloquea los envíos automáticos a mediano plazo.

---

## 9. Tests mínimos

La lógica de detección debe tener tests desde el inicio. Casos obligatorios:

1. **Salto por sobre la ventana.** Lectura pasa de 9.800 a 10.400 con
   intervalo 10.000 → debe notificar (vencido), no ignorar.
2. **Km faltantes negativo.** El correo debe usar el texto de vencido y
   enviarse una sola vez.
3. **Reportes repetidos dentro de la ventana.** Tres lecturas seguidas entre
   9.500 y 9.900 → un solo correo.
4. **Servicio registrado reinicia el ciclo.** Tras un `evento_mantencion` en
   10.050, el siguiente objetivo es 20.050 y vuelve a poder notificar.
5. **Retroceso de odómetro.** Lectura menor a `km_actual` sin permiso de
   corrección → rechazada.
6. **Plan por tipo.** Un plan con `tipo_codigo = 'PU'` no debe evaluarse
   sobre un sedán.
