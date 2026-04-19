# Patrones arquitectónicos del proyecto handel_api

## Stack

jQuery vanilla + Bootstrap 4 + PHP procedural con clases. Sin framework MVC.

## Arquitectura backend PHP

Cada entidad del dominio (Auditoria, Usuario, Area, Categoria, etc.) tiene
**3 archivos paralelos** que trabajan juntos:

### 1. `php/repositorios/{Entidad}.php` — ROUTER (dispatcher HTTP)

Punto de entrada HTTP. Recibe el POST/GET, hace `switch` sobre el parámetro
`action` y delega en el Repositorio. Ejemplo:

```php
switch ($action) {
    case 'consultar':
        $resultado = $repositorio->consultar($criterios);
        break;
    case 'consultarValoresSecciones':
        $resultado = $repositorio->consultarValoresSecciones($llaves);
        break;
}
```

### 2. `php/repositorios/{Entidad}Repositorio.php` — REPOSITORIO (SQL)

Contiene la implementación real con consultas SQL:

```php
class AuditoriasRepositorio implements IAuditoriasRepositorio {
    public function consultarValoresSecciones($llaves) {
        $sql = "SELECT ... FROM ...";
        return $resultado;
    }
}
```

### 3. `php/interfaces/I{Entidad}Repositorio.php` — CONTRATO

Declara la firma de cada método público del Repositorio:

```php
interface IAuditoriasRepositorio {
    public function consultarValoresSecciones($llaves);
}
```

## REGLA CRÍTICA: cadena Router → Repositorio → Interface

**Cuando agregues un nuevo `case` en el Router PHP, SIEMPRE debes agregar:**

1. El método correspondiente en `{Entidad}Repositorio.php` con su SQL
2. La declaración del método en `I{Entidad}Repositorio.php`

**Nunca agregues un `case` que llame a un método inexistente.** Eso rompe
el código en runtime.

### Ejemplo concreto

Si agregas este case:

```php
// En Auditorias.php (router)
case 'registrarPresencia':
    $resultado = $repositorio->registrarPresencia($usuario, $llaves);
    break;
```

OBLIGATORIAMENTE también debes:

```php
// En AuditoriasRepositorio.php
public function registrarPresencia($usuario, $llaves) {
    // INSERT ... ON DUPLICATE KEY UPDATE ...
    return $resultado;
}
```

```php
// En IAuditoriasRepositorio.php
public function registrarPresencia($usuario, $llaves);
```

Los **tres cambios son inseparables**: van siempre juntos.

## Arquitectura frontend JS (MVP)

Cada pantalla tiene 3 capas:

### 1. `js/repositorios/{entidad}_repositorio.js` — hace AJAX

```javascript
class AuditoriasRepositorio extends Repositorio {
    constructor() {
        super("/php/repositorios/Auditorias.php");
    }
    consultar(contexto, callback, criterios) {
        this.post({accion: 'consultar', criterios}, contexto, callback);
    }
}
```

### 2. `js/presentadores/{entidad}_presentador.js` — lógica

Orquesta repositorio y vista. Nunca toca DOM directamente.

### 3. `js/vistas/{entidad}_vista.js` — DOM puro

Solo manipula el DOM. Delega eventos al presentador. Nunca hace llamadas
HTTP directas.

## REGLA CRÍTICA: cadena Vista → Presentador → Repositorio JS

Al agregar una nueva acción del usuario (click, cambio de valor, evento):

1. Handler en la Vista que llama al Presentador
2. Método en el Presentador que orquesta + llama al Repositorio JS
3. Método en el Repositorio JS que hace el AJAX al backend

Si una nueva acción del usuario golpea un endpoint backend, **los 3 archivos
cambian juntos**.

## Checklist para un nuevo endpoint end-to-end

Cuando planees una feature que necesite un nuevo endpoint, estos archivos
cambian **siempre juntos**. No omitas ninguno en el plan:

**Backend (3 archivos):**
- [ ] `php/repositorios/{Entidad}.php` — nuevo `case`
- [ ] `php/repositorios/{Entidad}Repositorio.php` — nuevo método con SQL
- [ ] `php/interfaces/I{Entidad}Repositorio.php` — declaración del método

**Frontend (3 archivos):**
- [ ] `js/repositorios/{entidad}_repositorio.js` — función AJAX
- [ ] `js/presentadores/{entidad}_presentador.js` — lógica orquestadora
- [ ] `js/vistas/{entidad}_vista.js` — handler de evento / render del resultado

**Opcional según la feature:**
- [ ] `php/modelos/{Entidad}.php` — si hay nuevas propiedades
- [ ] Migración SQL si hay cambios de schema (tablas, columnas)
- [ ] CSS si hay cambios visuales

## Anti-patterns

- NO mezclar UI con lógica de negocio (la Vista NO decide, solo muestra)
- NO duplicar consultas SQL (siempre en el Repositorio PHP)
- NO llamar `fetch` o `$.ajax` desde la Vista (va siempre por Repositorio JS)
- NO agregar `case` en Router PHP sin el método correspondiente en Repositorio
- NO olvidar actualizar la Interface cuando cambian los métodos públicos
- NO poner lógica de negocio en el Repositorio PHP (solo SQL + mapeo)
