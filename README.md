# SIVAH — Backend/API (handel_api)

Backend PHP del sistema de inspecciones **SIVAH** (10y7 / Handel), servido en `api.apps-handel.com`. Repo hermano del frontend [`dys`](https://github.com/alanbazan0/dys), al que provee assets/librerías compartidas y algunos endpoints propios.

Ver [ARCHITECTURE.md](ARCHITECTURE.md) para el detalle completo de patrones, convenciones y rol real frente a `dys`.

## Stack

- PHP 7.3 puro, sin framework (patrón repositorio propio: interface → repositorio → modelo).
- MySQL vía `mysqli`.
- Composer para dependencias de generación de documentos (PhpSpreadsheet, etc.) — ver `composer.json`.
- `js/` comparte el mismo patrón MVP (Vista/Presentador/Repositorio) usado por `dys`; buena parte del JS activo del sistema se sirve físicamente desde aquí, aunque las pantallas vivan en `dys`.

## Qué es este repo

Pese al nombre, **no expone una API REST de datos de negocio** consumida por `dys` para los catálogos estándar (esos están duplicados en `dys`). Su rol real:

1. Sirve librerías/assets estáticos compartidos (`lib/`, `html/formularios`, `html/modales`, `js/vistas`, `js/presentadores`, `js/repositorios`) que `dys` referencia vía `getHandelAPI()`.
2. Aloja módulos de negocio propios: auditorías, reportes, generación de PDF/Excel/Word, cronjobs, plantillas de correo.
3. Backend real de la app móvil Android (`10y7`), que sube inspecciones vía `php/repositorios/Inspecciones.php`.

## Desarrollo local

1. Servidor PHP embebido apuntando a un directorio que contenga tanto este repo como `dys` como subcarpetas del mismo host:
   ```bash
   php -S 127.0.0.1:7500 -t /ruta/a/htdocs
   ```
2. Instalar dependencias PHP:
   ```bash
   composer install
   ```
3. Base de datos: configurada en `php/clases/AdministradorConexion.php`.

## Estructura

```
php/interfaces/          contratos I{Entidad}Repositorio
php/repositorios/        lógica de acceso a datos + endpoints HTTP
php/modelos/             entidades planas
php/clases/              utilidades transversales
php/reportes*/           generación de PDF/Excel/Word
php/cronjobs/            tareas programadas
js/vistas|presentadores|repositorios/   capa MVP activa consumida por dys
lib/                      librerías front-end de terceros compartidas
html/formularios|modales/ fragmentos HTML compartidos con dys
```

## Deploy

Manual por ahora — ver [ARCHITECTURE.md](ARCHITECTURE.md) para deuda técnica conocida (credenciales hardcodeadas, sin tests, sin CI). CI/CD hacia HostGator vía GitHub Actions en curso.
