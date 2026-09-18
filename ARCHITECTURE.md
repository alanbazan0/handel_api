# Arquitectura — SIVAH Backend/API (handel_api)

> Repo hermano: `dys` (frontend, ver su `ARCHITECTURE.md`). Este documento describe cómo está construido **este** repo (`handel_api`, servido como `api.apps-handel.com`) y su rol real frente a `dys`.

## Resumen

Pese al nombre, este repo **no es consumido por `dys` como una API REST de datos de negocio** para las pantallas CRUD estándar (áreas, usuarios, empresas, etc.) — esas viven duplicadas en `dys/php/{interfaces,repositorios,modelos}` con su propia conexión MySQL.

El rol real de `handel_api` hacia el frontend es:
1. **Servir assets/librerías estáticas compartidas** (`lib/`, `html/formularios`, `html/modales`) que `dys` referencia vía `getHandelAPI()` (`php/configuracion.php` en `dys`) en `<link>`/`<script>`.
2. **Alojar su propia app** con el mismo patrón arquitectónico (interfaces → repositorios → modelos), usada para módulos propios de este repo (auditorías, reportes, cronjobs, generación de PDF/Excel, plantillas de correo, etc. — ver `php/`).

Tiene conexión MySQL propia (`php/clases/AdministradorConexion.php`), con credenciales distintas a las de `dys` pero apuntando aparentemente a la misma base de datos lógica del sistema.

## Estructura de carpetas relevante

```
php/
  clases/                  utilidades transversales (conexión, correo, sesión, archivos, etc.)
  interfaces/              I{Entidad}Repositorio.php — contratos
  repositorios/            {Entidad}Repositorio.php — implementación con SQL crudo (mysqli)
  modelos/                 {Entidad}.php — entidades planas
  queries/                 consultas SQL sueltas
  generadores/             generación de documentos/reportes
  reportes*, reportes_pdf/, reportes_kci/, reportes_mensuales/, reportes_evidencia/, ...
  cronjobs/                tareas programadas
  plantillas_correo/, plantillas_html/, plantillas_texto/
  test/                    (existente, pero no confirmado como suite automatizada real — revisar antes de asumir cobertura)
vendor/                    dependencias Composer reales (PhpOffice, Symfony, myclabs, etc.)
lib/                       librerías front-end compartidas servidas a dys (jQuery, DataTables, Bootstrap, SweetAlert, FontAwesome, amCharts, etc.)
html/
  formularios/, modales/   fragmentos HTML compartidos que dys incluye vía getHandelAPI()
```

A diferencia de `dys`, aquí **sí hay `vendor/` de Composer real** (PhpOffice para Excel/Word, Symfony components, myclabs/enum, etc.) usado por los generadores de reportes.

## Patrón de capas (igual al de `dys`, ver su ARCHITECTURE.md para el detalle completo)

- **Interface** `I{Entidad}Repositorio` (`php\interfaces`).
- **Repositorio** `{Entidad}Repositorio extends RepositorioBase implements I{Entidad}Repositorio` (`php\repositorios`): SQL crudo con `mysqli` prepared statements, mismos helpers genéricos (`calcularId`, `where`, `bind_param`, `get_result`, `select/insert/update`).
- **Modelo** `{Entidad}` (`php\modelos`): clase plana de propiedades públicas.
- **Endpoint HTTP**: mismo patrón de script standalone por entidad con `switch` sobre `accion` y respuesta `Resultado { valor, mensajeError }` — confirmar ubicación exacta por módulo antes de asumir dónde vive el endpoint de una entidad específica, ya que aquí conviven módulos de negocio propios (auditorías, procesos, evidencias, capacitaciones) además de las entidades catálogo compartidas por nombre con `dys`.

## Puntos a tener en cuenta al implementar un nuevo requerimiento

- **Antes de tocar una entidad, verificar si también existe en `dys`** (mismo nombre de interface/repositorio/modelo) — si es así, es candidata a duplicación y hay que decidir explícitamente dónde vive la lógica nueva.
- **No asumir que este repo expone REST real** hacia `dys` para catálogos estándar; si el nuevo requerimiento necesita que `dys` consuma algo de aquí por HTTP, eso sería un cambio de patrón, no algo ya establecido — vale la pena confirmarlo con el usuario antes de construir sobre ese supuesto.
- **Sin tests automatizados confirmados** pese a existir `php/test/` — revisar su contenido real antes de asumir que hay cobertura o un framework ya integrado (PHPUnit no aparece en `vendor/`).
- **Credenciales de BD hardcodeadas** en `php/clases/AdministradorConexion.php` — deuda técnica ya señalada, no forma parte del patrón a replicar.
- **CORS abierto (`*`)** y ausencia de autoload PSR-4 propio (todo vía `include`/`require_once` relativos) — mantener consistencia con el patrón existente salvo pedido explícito de cambiarlo.
- Este repo tiene lógica de generación de documentos (PDF/Excel/Word vía PhpOffice) y cronjobs — si el requerimiento nuevo involucra reportes o tareas programadas, es probable que corresponda implementarlo aquí en vez de en `dys`.
