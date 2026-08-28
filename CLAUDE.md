# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Contexto del proyecto

Sistema de control de kilometraje y mantención vehicular.
El plan completo está en [plan.md](plan.md). Léelo antes de implementar
cualquier fase — contiene el modelo de datos completo (sección 2), la lógica
de detección de mantención (sección 3), los disparadores de evaluación
(sección 4), las validaciones (sección 5), las fases de implementación
(sección 6) y los casos de test obligatorios (sección 9).

## Estado actual

Fases 1 a 4 completas (MVP de `plan.md` sección 6). De Fase 5
(refinamientos, opcional) están hechos: dashboard de vencimientos,
reporte de km + exportación CSV, y mantención por tiempo. Sin hacer:
roles y permisos (no hay ningún sistema de login todavía).

## Mantención por tiempo (Fase 5)

- No estaba en el DDL original de `plan.md` — es una extensión. Un plan
  puede tener `intervalo_meses` + `umbral_aviso_dias` (ambos nullable;
  un plan puede seguir siendo solo por km). "Lo que ocurra primero":
  `EstadoMantencionPlan::vencido()`/`enVentanaAviso()` son un OR entre
  ambos ejes.
- Sin servicio previo, la fecha base es `vehiculo.creado_en` (equivalente
  temporal de "0 km").
- `descripcion()` (km) y `descripcionTiempo()` (días, nullable) son
  independientes a propósito — cada uno se colorea por su propio
  vencido/no-vencido en las vistas, no por el estado agregado.

## Dashboard y reporte de km (Fase 5)

- `/dashboard` (ahora la home) agrega `CalcularEstadoMantencion` sobre
  toda la flota activa; ordena vencidos primero, luego por `kmFaltantes`
  ascendente (heurística simple, no pondera km vs. días).
- `/reportes/km`: km recorridos por mes = diferencia entre el cierre de
  odómetro de este mes y el del mes anterior con datos. Un mes sin
  lectura nueva repite el cierre anterior (delta 0), distinto de "sin
  datos todavía" (`—`, antes de la primera lectura).

## Gotcha: timestamps custom de Eloquent

`LecturaOdometro::create(['fecha' => ...])` **ignora silenciosamente**
la fecha pasada y usa la hora actual — Eloquent autogestiona su
`CREATED_AT` (aquí renombrado a `fecha`) en cada insert. No importa para
el flujo normal (que nunca pasa `fecha`), pero muerde a cualquier
backfill/import futuro. Ver comentario en el modelo; usar
`withoutTimestamps()` o un insert directo para fechas históricas.

## Notificaciones (Fase 4)

- `App\Actions\EvaluarAvisosMantencion` implementa la sección 3 completa:
  reclama la fila única de `notificacion_enviada` (patente+plan_id+km_objetivo)
  dentro de su propio savepoint — necesario porque Postgres aborta toda la
  transacción envolvente ante un choque de UNIQUE, no solo la sentencia
  que falló — y solo si la reclama encola `EnviarAvisoMantencionJob`.
- Se dispara en dos lugares (sección 4 del plan): `LecturaOdometroController@store`
  (evaluación inmediata, después de que `RegistrarLecturaOdometro` cierra
  su transacción — regla 10) y el comando `mantencion:evaluar-avisos`,
  agendado a diario en `routes/console.php`.
- En desarrollo el correo se escribe a `storage/logs/laravel.log`
  (`MAIL_MAILER=log`), nunca se envía de verdad — verificado. Para
  procesar la cola manualmente: `php artisan queue:work` (no tiene el bug
  de ruta con tilde, corre normal). En producción cambiar `MAIL_MAILER` a
  `smtp` y completar `MAIL_HOST`/`MAIL_USERNAME`/etc — probar primero
  contra un buzón interno antes de apuntar a direcciones reales.

## Sistema de diseño

Instalados en `.claude/skills/`: los skills de emilkowalski/skills
(animación/diseño) e impeccable (guía de diseño de producto/UI). El
sistema visual actual sigue `impeccable/reference/operate.md` (modo
"Operate": estabilidad y escaneabilidad por sobre expresión) y
`colorize.md`/`typeset.md`:

- Tokens de color en `resources/css/app.css` (`@theme`): `canvas`,
  `surface`, `surface-muted`, `border`, `text`, `text-muted`, `accent` (un
  solo acento, OKLCH) y semánticos `success`/`warning`/`danger`. Generan
  utilidades Tailwind automáticas (`bg-accent`, `text-danger`, etc.) — no
  usar `neutral-*`/`emerald-*` sueltos de Tailwind en vistas nuevas.
- Tipografía: una sola familia (Instrument Sans, vía `@fonts` de Laravel
  en el layout), escala fija en rem.
- Componentes Blade reutilizables: `<x-button variant="primary|secondary|danger|link">`
  y `<x-badge variant="success|warning|danger|neutral">` — usarlos en vez
  de reinventar botones/badges por vista.
- Motion: un único momento animado por interacción (200ms, ease-out),
  nunca decorativo. `.animar-entrada` para el flash de éxito, `.acordeon`
  (grid-template-rows) para revelar campos condicionales como la
  observación de una corrección. Respeta `prefers-reduced-motion`.

`reportado_por` referencia `users(id)` (no `usuario(id)` como dice
`plan.md`, tabla que no existe en ningún lado del plan) — es el único
catálogo real de identidades que hay, vía el scaffold de auth de Laravel.
Queda nullable porque todavía no hay login (Fase 5).

Los tests corren contra PostgreSQL (`sistema_autos_km_test`), no SQLite —
`phpunit.xml` así lo tiene configurado, porque la migración usa SQL crudo
específico de Postgres (CHECK, índice DESC) que no es portable.

## Levantar el entorno de desarrollo

**No usar `php artisan serve`** — falla siempre en esta máquina porque el
proyecto vive bajo `C:\Users\Nicolás\...` (la tilde) y PHP en Windows
corrompe esa ruta al pasarla al subproceso interno de `artisan serve`
(error `Failed opening required '...NicolÃ¡s...server.php'`). En su lugar,
usar el router de reemplazo ya incluido en el repo:

```sh
php -S 127.0.0.1:8000 -t public dev-server-router.php
npm run dev   # Vite, en otra terminal — necesario para Tailwind/CSS
```

`dev-server-router.php` es equivalente al `server.php` de Laravel (sirve
estáticos de `public/` directo, todo lo demás va a `index.php`); no se
borra porque el bug de ruta con tilde afecta a cualquier sesión futura en
este equipo.

**Tampoco usar `php artisan test`** — mismo bug de ruta con tilde
(falla con "Could not read XML from file"). Correr phpunit directo:

```sh
php vendor/bin/phpunit -c phpunit.xml
```

PHP y Composer no están en el PATH del shell por defecto en esta máquina;
si un comando `php`/`composer` falla con "not found", usar la ruta
completa a `php.exe`/`composer.phar` bajo
`%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_...` o refrescar
`$env:Path` desde el registro (Machine + User) en esa misma sesión de
shell.

## Reglas que no deben romperse

1. **El odómetro se guarda como lectura absoluta**, nunca como incremento.
   La tabla `lectura_odometro` es la fuente de verdad; `vehiculo.km_actual`
   es una copia denormalizada que se actualiza junto con cada lectura.

2. **Los intervalos de mantención NO se hardcodean.** "Cada 10.000 km" es un
   registro en `plan_mantencion`. Nunca una constante en el código.

3. **El umbral de aviso tampoco se hardcodea.** Es `plan_mantencion.umbral_aviso`.

4. **La comparación de la ventana de aviso es `km_faltantes <= umbral_aviso`**,
   incluyendo valores negativos. Nunca un rango cerrado tipo
   `0 <= faltantes <= 500`: un salto de 9.800 a 10.400 se perdería.

5. **Los tipos de vehículo van en la tabla `tipo_vehiculo`**, no en un enum
   ni en constantes.

6. **La deduplicación de correos depende del índice
   `UNIQUE (patente, plan_id, km_objetivo)`** en `notificacion_enviada`.
   No quitar ese índice.

7. **Validación de patente: formato chileno.** `LLLL·NN` (desde 2007),
   `LL·NNNN` y `LLL·NNN` (antiguas). Normalizar a mayúsculas sin puntos ni
   guiones antes de persistir.

8. **El odómetro no retrocede.** Una lectura menor a `km_actual` se rechaza,
   salvo `origen='CORRECCION'` con observación obligatoria.

9. **En desarrollo, el correo se escribe a log, no se envía.** El driver debe
   ser configurable por variable de entorno.

10. **El envío de correo nunca ocurre dentro de la transacción** que inserta
    la lectura de odómetro. Se encola.

## Flujo de trabajo

- Una fase por sesión. No adelantarse a la siguiente.
- Empezar siempre por migraciones y modelos; esperar revisión antes de
  controladores y vistas.
- La lógica de detección de mantención va con tests. Los casos obligatorios
  están en la sección 9 de [plan.md](plan.md).

## Stack

- Framework: Laravel
- Base de datos: PostgreSQL
- Proveedor SMTP: por definir (recién se necesita en Fase 4)
