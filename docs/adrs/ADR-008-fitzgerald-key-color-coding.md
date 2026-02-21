# ADR-008: Modified Fitzgerald Key como Sistema de Colores para Categorías SAAC

**Estado:** Aceptado<br>
**Fecha:** 2026-02-05<br>
**Contexto:** HablaIA - SAAC con IA<br>

## Contexto

El comunicador pictográfico necesita un sistema de codificación por colores para las categorías de pictogramas. El color es un elemento crítico en SAAC porque:

1. **Orientación visual rápida:** Usuarios con TEA, afasia o parálisis cerebral dependen del color para localizar categorías sin leer texto
2. **Consistencia terapéutica:** Los terapeutas ya trabajan con sistemas de colores estandarizados; usar colores arbitrarios genera confusión
3. **Accesibilidad cognitiva:** El color como canal redundante (junto al icono y el nombre) reduce la carga cognitiva

El **Modified Fitzgerald Key** es el sistema de codificación por colores estándar en SAAC. Fue creado por Edith Fitzgerald en 1926 para la enseñanza del lenguaje a personas sordas y posteriormente adaptado para comunicación aumentativa. ARASAAC y la mayoría de herramientas SAAC en el ámbito hispanohablante lo utilizan como referencia.

### Asignación Fitzgerald Key estándar

| Familia Fitzgerald | Tipo gramatical | Color base |
|--------------------|-----------------|------------|
| Amarillo | Pronombres, personas | Amarillo |
| Verde | Verbos, acciones | Verde |
| Azul | Adjetivos, descriptores | Azul |
| Naranja | Sustantivos | Naranja |
| Rosa | Social, interacciones | Rosa |
| Blanco | Miscelánea, artículos | Blanco |

### Problema específico

HablaIA define 10 categorías SAAC (Personas, Acciones, Emociones, Lugares, Objetos, Comida, Transporte, Social, Tiempo, Descriptivos). De estas, **tres son sustantivos** (Lugares, Objetos, Comida) y todas corresponderían al naranja en Fitzgerald estricto, lo que las haría visualmente indistinguibles en el grid de pictogramas.

## Decisión

Adoptar el **Modified Fitzgerald Key con variaciones tonales** para categorías que comparten la misma familia cromática.

### Mapeo completo

| Categoría | Familia Fitzgerald | Color Hex | Tono Tailwind | Orden | Justificación |
|-----------|-------------------|-----------|---------------|-------|---------------|
| Personas | Amarillo (pronombres/personas) | `#FBBF24` | Amber-400 | 1 | Amarillo cálido, familia Fitzgerald para personas |
| Acciones | Verde (verbos) | `#22C55E` | Green-500 | 2 | Verde primario, estándar Fitzgerald para verbos |
| Emociones | Azul (adjetivos/descriptores) | `#3B82F6` | Blue-500 | 3 | Azul primario, estándar Fitzgerald para descriptores |
| Lugares | Naranja (sustantivos) | `#F97316` | Orange-500 | 4 | Naranja primario, tono base de la familia |
| Objetos | Naranja (sustantivos) | `#FB923C` | Orange-400 | 5 | Naranja claro, diferenciado de Lugares |
| Comida | Naranja (sustantivos) | `#EA580C` | Orange-600 | 6 | Naranja oscuro, diferenciado de Lugares y Objetos |
| Transporte | Amarillo (mixto) | `#F59E0B` | Amber-500 | 7 | Ámbar dorado, diferenciado de Personas |

### Estrategia de variaciones tonales

Para categorías dentro de la misma familia Fitzgerald, se utilizan diferentes tonos de la escala Tailwind CSS:

```
Familia Naranja (sustantivos):
  Lugares  -> Orange-500 (#F97316) -- tono base
  Objetos  -> Orange-400 (#FB923C) -- tono claro
  Comida   -> Orange-600 (#EA580C) -- tono oscuro

Familia Amarillo (personas/mixto):
  Personas   -> Amber-400 (#FBBF24) -- tono cálido
  Transporte -> Amber-500 (#F59E0B) -- tono dorado
```

Esta diferencia de al menos un paso en la escala Tailwind (100 puntos) garantiza distinción visual entre subcategorías.

### Los colores son constantes de dominio

Los colores se definen como constantes en `CategoryFixtures` y **no son configurables por el usuario**, porque representan un estándar terapéutico:

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

### Aplicación visual: color como borde, no como fondo

En los comunicadores SAAC profesionales (Proloquo2Go, Grid, TD Snap) y en las láminas impresas de ARASAAC, el color Fitzgerald se aplica como **borde o franja de la tarjeta**, no como fondo completo. HablaIA adopta este mismo patrón:

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
2. **Pictogramas ARASAAC:** Diseñados sobre fondo blanco; un fondo de color altera su legibilidad
3. **Fondo blanco vs negro:** El fondo blanco es estándar en SAAC porque reduce la estimulación visual (crítico en TEA), es familiar para terapeutas (similar a láminas impresas), y mantiene coherencia con los pictogramas
4. **Texto legible:** Negro sobre blanco garantiza ratio 21:1 sin depender del color de categoría

> **Nota para frontend:** Esta decisión afecta directamente a los componentes Vue del grid de pictogramas. El `colorHex` de la API debe usarse para bordes/acentos (`border-color`, `border-top`), nunca como `background-color` del área completa de la tarjeta.

### Validación en la capa de dominio

El color se válida en la entidad `Category` con regex `^#[0-9A-Fa-f]{6}$`:

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

El `displayOrder` (1-7) se válida como entero no negativo y determina el orden de presentación en el grid:

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

- **Consistencia terapéutica:** Los terapeutas reconocen inmediatamente las familias de colores Fitzgerald
- **Distinción visual entre subcategorías:** Lugares, Objetos y Comida son diferenciables a pesar de pertenecer a la misma familia
- **Alineación con ARASAAC:** El sistema de colores es coherente con la codificación que ARASAAC recomienda
- **Accesibilidad cognitiva:** El color como canal redundante (color + icono + texto) reduce la carga cognitiva para usuarios SAAC
- **Escala Tailwind coherente:** Los tonos seleccionados (400/500/600) forman una escala visual progresiva y uniforme
- **Validación en dominio:** Los Value Objects impiden colores inválidos en tiempo de construcción

### Negativas

- **Flexibilidad limitada:** Los colores no son configurables por el usuario
- **Contraste con fondo:** Algunos tonos (Amber-400 sobre fondo blanco) pueden tener ratio de contraste reducido para texto
- **Ampliación futura:** Añadir nuevas categorías requerirá seleccionar tonos dentro de las familias existentes o extender el mapeo

### Mitigaciones

- **Flexibilidad:** En el futuro se puede permitir personalización de colores manteniendo Fitzgerald como preset por defecto
- **Contraste:** Los colores se aplican como borde/acento de la tarjeta (no como fondo completo), con texto negro sobre fondo blanco (ratio 21:1). Ver sección "Aplicación visual" para detalles
- **Ampliación:** Documentar el proceso de asignación de tonos para nuevas categorías: identificar familia Fitzgerald, seleccionar tono libre en la escala Tailwind (300-700)

## Alternativas Consideradas

### 1. Colores arbitrarios de Tailwind

**Descripción:** Asignar colores basándose únicamente en estética (rojo para Comida, morado para Emociones, etc.)
**Pros:** Libertad total de diseño, máxima diferenciación visual
**Contras:** Sin base terapéutica, confunde a terapeutas que usan Fitzgerald, inconsistente con ARASAAC
**Rechazo:** Un comunicador SAAC debe respetar los estándares terapéuticos para ser adoptado por profesionales

### 2. Fitzgerald Key estricto (mismo color para todos los sustantivos)

**Descripción:** Usar exactamente un color por familia Fitzgerald: todos los sustantivos en naranja idéntico
**Pros:** Adherencia pura al estándar, simplicidad
**Contras:** Lugares, Objetos y Comida serían visualmente idénticos en el grid, degradando la UX para usuarios que dependen del color para navegar
**Rechazo:** La UX para usuarios SAAC es prioritaria; el estándar Fitzgerald no prevé aplicaciones con grids de categorías múltiples del mismo tipo gramatical

### 3. Paleta terapéutica personalizada

**Descripción:** Crear una paleta propia consultando con terapeutas SAAC
**Pros:** Optimizada para el caso de uso específico, libertad creativa
**Contras:** Sin respaldo de un estándar reconocido, costoso en tiempo de validación, los terapeutas ya conocen Fitzgerald
**Rechazo:** El Modified Fitzgerald Key es el estándar de facto; reinventar el sistema no aporta valor y retrasa el MVP

## Referencias

- [Modified Fitzgerald Key - ARASAAC](https://arasaac.org/aac/es) - Sistema de colores estándar en SAAC
- [ARASAAC - Categorías de pictogramas](https://arasaac.org/pictograms/search) - Clasificación de pictogramas por tipo gramatical
- [WCAG 2.2 - Criterio 1.4.3 Contraste mínimo (AA)](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html) - Ratio mínimo 4.5:1 para texto sobre fondo de color
- [WCAG 2.2 - Criterio 1.4.1 Uso del color](https://www.w3.org/WAI/WCAG22/Understanding/use-of-color.html) - El color no debe ser el único canal de información
- [Fitzgerald Key for AAC - Communication Community](https://www.communicationcommunity.com/fitzgerald-key-for-aac/) - Historia y aplicación del sistema original
- [Tailwind CSS Color Palette](https://tailwindcss.com/docs/customizing-colors) - Escala de tonos utilizada para las variaciones
