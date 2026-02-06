# ADR-008: Modified Fitzgerald Key como Sistema de Colores para Categorias SAAC

**Estado:** Aceptado
**Fecha:** 2026-02-05
**Contexto:** HablaIA - SAAC con IA

## Contexto

El comunicador pictografico necesita un sistema de codificacion por colores para las categorias de pictogramas. El color es un elemento critico en SAAC porque:

1. **Orientacion visual rapida:** Usuarios con TEA, afasia o paralisis cerebral dependen del color para localizar categorias sin leer texto
2. **Consistencia terapeutica:** Los terapeutas ya trabajan con sistemas de colores estandarizados; usar colores arbitrarios genera confusion
3. **Accesibilidad cognitiva:** El color como canal redundante (junto al icono y el nombre) reduce la carga cognitiva

El **Modified Fitzgerald Key** es el sistema de codificacion por colores estandar en SAAC. Fue creado por Edith Fitzgerald en 1926 para la ensenanza del lenguaje a personas sordas y posteriormente adaptado para comunicacion aumentativa. ARASAAC y la mayoria de herramientas SAAC en el ambito hispanohablante lo utilizan como referencia.

### Asignacion Fitzgerald Key estandar

| Familia Fitzgerald | Tipo gramatical | Color base |
|--------------------|-----------------|------------|
| Amarillo | Pronombres, personas | Amarillo |
| Verde | Verbos, acciones | Verde |
| Azul | Adjetivos, descriptores | Azul |
| Naranja | Sustantivos | Naranja |
| Rosa | Social, interacciones | Rosa |
| Blanco | Miscelanea, articulos | Blanco |

### Problema especifico

HablaIA define 7 categorias SAAC (Personas, Acciones, Emociones, Lugares, Objetos, Comida, Transporte). De estas, **tres son sustantivos** (Lugares, Objetos, Comida) y todas corresponderian al naranja en Fitzgerald estricto, lo que las haria visualmente indistinguibles en el grid de pictogramas.

## Decision

Adoptar el **Modified Fitzgerald Key con variaciones tonales** para categorias que comparten la misma familia cromatica.

### Mapeo completo

| Categoria | Familia Fitzgerald | Color Hex | Tono Tailwind | Orden | Justificacion |
|-----------|-------------------|-----------|---------------|-------|---------------|
| Personas | Amarillo (pronombres/personas) | `#FBBF24` | Amber-400 | 1 | Amarillo calido, familia Fitzgerald para personas |
| Acciones | Verde (verbos) | `#22C55E` | Green-500 | 2 | Verde primario, estandar Fitzgerald para verbos |
| Emociones | Azul (adjetivos/descriptores) | `#3B82F6` | Blue-500 | 3 | Azul primario, estandar Fitzgerald para descriptores |
| Lugares | Naranja (sustantivos) | `#F97316` | Orange-500 | 4 | Naranja primario, tono base de la familia |
| Objetos | Naranja (sustantivos) | `#FB923C` | Orange-400 | 5 | Naranja claro, diferenciado de Lugares |
| Comida | Naranja (sustantivos) | `#EA580C` | Orange-600 | 6 | Naranja oscuro, diferenciado de Lugares y Objetos |
| Transporte | Amarillo (mixto) | `#F59E0B` | Amber-500 | 7 | Ambar dorado, diferenciado de Personas |

### Estrategia de variaciones tonales

Para categorias dentro de la misma familia Fitzgerald, se utilizan diferentes tonos de la escala Tailwind CSS:

```
Familia Naranja (sustantivos):
  Lugares  -> Orange-500 (#F97316) -- tono base
  Objetos  -> Orange-400 (#FB923C) -- tono claro
  Comida   -> Orange-600 (#EA580C) -- tono oscuro

Familia Amarillo (personas/mixto):
  Personas   -> Amber-400 (#FBBF24) -- tono calido
  Transporte -> Amber-500 (#F59E0B) -- tono dorado
```

Esta diferencia de al menos un paso en la escala Tailwind (100 puntos) garantiza distincion visual entre subcategorias.

### Los colores son constantes de dominio

Los colores se definen como constantes en `CategoryFixtures` y **no son configurables por el usuario**, porque representan un estandar terapeutico:

```php
// backend/src/Infrastructure/DataFixtures/CategoryFixtures.php
private const array SAAC_CATEGORIES = [
    ['name' => 'Personas',   'icon' => 'users',    'colorHex' => '#FBBF24', 'displayOrder' => 1],
    ['name' => 'Acciones',   'icon' => 'play',     'colorHex' => '#22C55E', 'displayOrder' => 2],
    ['name' => 'Emociones',  'icon' => 'heart',    'colorHex' => '#3B82F6', 'displayOrder' => 3],
    ['name' => 'Lugares',    'icon' => 'map-pin',  'colorHex' => '#F97316', 'displayOrder' => 4],
    ['name' => 'Objetos',    'icon' => 'box',      'colorHex' => '#FB923C', 'displayOrder' => 5],
    ['name' => 'Comida',     'icon' => 'utensils', 'colorHex' => '#EA580C', 'displayOrder' => 6],
    ['name' => 'Transporte', 'icon' => 'car',      'colorHex' => '#F59E0B', 'displayOrder' => 7],
];
```

### Aplicacion visual: color como borde, no como fondo

En los comunicadores SAAC profesionales (Proloquo2Go, Grid, TD Snap) y en las laminas impresas de ARASAAC, el color Fitzgerald se aplica como **borde o franja de la tarjeta**, no como fondo completo. HablaIA adopta este mismo patron:

```
┌─────────────────┐
│ ██████████████  │  <- Borde superior grueso (color Fitzgerald)
│                 │
│   [pictograma]  │  <- Fondo blanco (pictogramas ARASAAC son blancos)
│                 │
│     comer       │  <- Texto negro sobre blanco (contraste 21:1)
└─────────────────┘
```

**Razones:**

1. **Contraste:** Tonos claros como Amber-400 (#FBBF24) tienen ratio ~1.5:1 contra blanco como fondo completo (no pasa WCAG AA). Como borde sobre fondo blanco, el problema no existe
2. **Pictogramas ARASAAC:** Disenados sobre fondo blanco; un fondo de color altera su legibilidad
3. **Fondo blanco vs negro:** El fondo blanco es estandar en SAAC porque reduce la estimulacion visual (critico en TEA), es familiar para terapeutas (similar a laminas impresas), y mantiene coherencia con los pictogramas
4. **Texto legible:** Negro sobre blanco garantiza ratio 21:1 sin depender del color de categoria

> **Nota para frontend:** Esta decision afecta directamente a los componentes Vue del grid de pictogramas. El `colorHex` de la API debe usarse para bordes/acentos (`border-color`, `border-top`), nunca como `background-color` del area completa de la tarjeta.

### Validacion en la capa de dominio

El color se valida en la entidad `Category` con regex `^#[0-9A-Fa-f]{6}$`:

```php
// backend/src/Domain/Category/Entity/Category.php
private function validateColorHex(string $colorHex): void
{
    if ($colorHex === '') {
        throw InvalidCategoryColorException::empty();
    }

    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorHex)) {
        throw InvalidCategoryColorException::invalidFormat($colorHex);
    }
}
```

El `displayOrder` (1-7) se valida como entero no negativo y determina el orden de presentacion en el grid:

```php
// backend/src/Domain/Category/Entity/Category.php
private function validateDisplayOrder(int $displayOrder): void
{
    if ($displayOrder < 0) {
        throw InvalidCategoryDisplayOrderException::negative($displayOrder);
    }
}
```

## Consecuencias

### Positivas

- **Consistencia terapeutica:** Los terapeutas reconocen inmediatamente las familias de colores Fitzgerald
- **Distincion visual entre subcategorias:** Lugares, Objetos y Comida son diferenciables a pesar de pertenecer a la misma familia
- **Alineacion con ARASAAC:** El sistema de colores es coherente con la codificacion que ARASAAC recomienda
- **Accesibilidad cognitiva:** El color como canal redundante (color + icono + texto) reduce la carga cognitiva para usuarios SAAC
- **Escala Tailwind coherente:** Los tonos seleccionados (400/500/600) forman una escala visual progresiva y uniforme
- **Validacion en dominio:** Los Value Objects impiden colores invalidos en tiempo de construccion

### Negativas

- **Flexibilidad limitada:** Los colores no son configurables por el usuario
- **Contraste con fondo:** Algunos tonos (Amber-400 sobre fondo blanco) pueden tener ratio de contraste reducido para texto
- **Ampliacion futura:** Anadir nuevas categorias requerira seleccionar tonos dentro de las familias existentes o extender el mapeo

### Mitigaciones

- **Flexibilidad:** En fases futuras (Fase 5-6), se puede permitir personalizacion de colores manteniendo Fitzgerald como preset por defecto
- **Contraste:** Los colores se aplican como borde/acento de la tarjeta (no como fondo completo), con texto negro sobre fondo blanco (ratio 21:1). Ver seccion "Aplicacion visual" para detalles
- **Ampliacion:** Documentar el proceso de asignacion de tonos para nuevas categorias: identificar familia Fitzgerald, seleccionar tono libre en la escala Tailwind (300-700)

## Alternativas Consideradas

### 1. Colores arbitrarios de Tailwind

**Descripcion:** Asignar colores basandose unicamente en estetica (rojo para Comida, morado para Emociones, etc.)
**Pros:** Libertad total de diseno, maxima diferenciacion visual
**Contras:** Sin base terapeutica, confunde a terapeutas que usan Fitzgerald, inconsistente con ARASAAC
**Rechazo:** Un comunicador SAAC debe respetar los estandares terapeuticos para ser adoptado por profesionales

### 2. Fitzgerald Key estricto (mismo color para todos los sustantivos)

**Descripcion:** Usar exactamente un color por familia Fitzgerald: todos los sustantivos en naranja identico
**Pros:** Adherencia pura al estandar, simplicidad
**Contras:** Lugares, Objetos y Comida serian visualmente identicos en el grid, degradando la UX para usuarios que dependen del color para navegar
**Rechazo:** La UX para usuarios SAAC es prioritaria; el estandar Fitzgerald no preve aplicaciones con grids de categorias multiples del mismo tipo gramatical

### 3. Paleta terapeutica personalizada

**Descripcion:** Crear una paleta propia consultando con terapeutas SAAC
**Pros:** Optimizada para el caso de uso especifico, libertad creativa
**Contras:** Sin respaldo de un estandar reconocido, costoso en tiempo de validacion, los terapeutas ya conocen Fitzgerald
**Rechazo:** El Modified Fitzgerald Key es el estandar de facto; reinventar el sistema no aporta valor y retrasa el MVP

## Referencias

- [Modified Fitzgerald Key - ARASAAC](https://arasaac.org/aac/es) - Sistema de colores estandar en SAAC
- [ARASAAC - Categorias de pictogramas](https://arasaac.org/pictograms/search) - Clasificacion de pictogramas por tipo gramatical
- [WCAG 2.1 - Criterio 1.4.3 Contraste minimo (AA)](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html) - Ratio minimo 4.5:1 para texto sobre fondo de color
- [WCAG 2.1 - Criterio 1.4.1 Uso del color](https://www.w3.org/WAI/WCAG21/Understanding/use-of-color.html) - El color no debe ser el unico canal de informacion
- [Fitzgerald Key - Wikipedia](https://en.wikipedia.org/wiki/Fitzgerald_Key) - Historia y aplicacion del sistema original
- [Tailwind CSS Color Palette](https://tailwindcss.com/docs/customizing-colors) - Escala de tonos utilizada para las variaciones
