# ADR-003: ARASAAC como Fuente de Pictogramas

**Estado:** Aceptado<br>
**Fecha:** 2026-01-31<br>
**Contexto:** HablaIA - Comunicador SAAC con IA<br>

## Contexto

HablaIA necesita pictogramas para su comunicador aumentativo. Las opciones principales son: crear pictogramas propios, comprar licencia comercial (Symbolstix, SymbolWorld) o usar recursos libres (ARASAAC, Mulberry Symbols).

**Requisitos:**

- Alto contraste y claridad visual
- Multilingüe (español como prioridad)
- Cobertura amplia (>10.000 conceptos)
- Licencia compatible con proyecto educativo/terapéutico
- Disponibilidad crítica: una aplicación SAAC no puede depender de un servicio externo en tiempo real

Los usuarios SAAC (personas con TEA, afasia, parálisis cerebral, ELA) pueden no escribir acentos correctamente, por lo que la búsqueda debe ser tolerante a variaciones ortográficas.

## Decisión

Usar **ARASAAC** (ARAgonese System of Augmentative and Alternative Communication) como fuente de pictogramas, con una estrategia local-first que garantiza disponibilidad independiente de la API externa.

### Estrategia Local-first con Fallback ARASAAC

El sistema mantiene 194 pictogramas pre-cargados en 11 categorías (10 curadas + "Sin categoría") que cubren el vocabulario base SAAC (core vocabulary). Para pictogramas fuera de ese vocabulario, el caso de uso `SearchPictogram` implementa un flujo en dos pasos:

1. **Búsqueda local:** Consultar la base de datos PostgreSQL con `LIKE` + `unaccent()` (insensible a acentos)
2. **Fallback ARASAAC:** Solo si la búsqueda local devuelve cero resultados, consultar la API ARASAAC

Los pictogramas obtenidos de ARASAAC se descargan (imagen a disco) y se persisten en base de datos, funcionando como caché permanente. Búsquedas futuras con el mismo término los encontrarán localmente sin volver a consultar ARASAAC. Esto permite que el vocabulario crezca orgánicamente según el uso real.

### Constantes del caso de uso SearchPictogram

| Constante | Valor | Propósito |
|-----------|-------|-----------|
| `MAX_RESULTS` | 10 | Límite de resultados por búsqueda |
| `MIN_QUERY_LENGTH` | 2 | Mínimo caracteres para buscar |
| `MAX_QUERY_LENGTH` | 100 | Máximo caracteres permitidos |
| `DEFAULT_CATEGORY_ID` | `00000000-0000-4000-8000-000000000000` | UUID fijo para "Sin categoría" |
| `DEFAULT_LANGUAGE` | `es` | Idioma de búsqueda en ARASAAC |

### Categoría "Sin categoría"

Los pictogramas importados desde ARASAAC no encajan en ninguna de las 10 categorías curadas. Se asignan a una categoría especial con UUID fijo:

| Propiedad | Valor |
|-----------|-------|
| UUID | `00000000-0000-4000-8000-000000000000` (UUID v4 válido con bytes fijos) |
| Icono | `help-circle` |
| Color | `#9CA3AF` (gris neutro) |
| display_order | 99 (siempre última en CategoryBar) |
| Migración | V005 |

El UUID fijo y conocido permite distinguir programáticamente los pictogramas importados de los curados. En el frontend, los pictogramas de esta categoría muestran borde gris, diferenciándolos visualmente de las categorías con codificación Fitzgerald.

### Búsqueda insensible a acentos

La búsqueda tolera ausencia o presencia de acentos en ambas direcciones:

- **PostgreSQL:** Extensión `unaccent` habilitada en `docker/postgres/init.sql` y migración V006. Query: `WHERE unaccent(LOWER(label)) LIKE unaccent(?)`
- **Tests (InMemoryPictogramRepository):** PHP `Normalizer::FORM_D` + eliminación de marcas diacríticas con regex `\p{Mn}`

### Filtro de pictogramas AAC

ARASAAC contiene pictogramas genéricos además de los diseñados específicamente para comunicación aumentativa. El cliente de la API filtra por el campo `aac: true` de la respuesta. Si ningún resultado tiene `aac: true`, se devuelven todos los resultados como fallback.

### Licencia: Creative Commons BY-NC-SA 4.0

**Permite:**

- Uso educativo y terapéutico sin ánimo de lucro
- Modificación (adaptación visual)
- Redistribución (con misma licencia)

**Prohibe:**

- Uso comercial (venta del software)
- Eliminar atribución

**Implicación:** HablaIA debe ser NO comercial mientras use pictogramas ARASAAC.

## Consecuencias

### Positivas

- Pictogramas profesionales diseñados por terapeutas
- Cobertura de más de 30.000 pictogramas en español
- Coste gratuito
- Estándar de facto en España (terapeutas lo conocen)
- API estable mantenida por Gobierno de Aragón
- Búsqueda local con latencia mínima (< 50ms para 194 pictogramas)
- Caché permanente: cada pictograma de ARASAAC se descarga una sola vez
- Búsqueda accent-insensitive mejora usabilidad para usuarios SAAC
- "Sin categoría" no interfiere visualmente con las categorías curadas

### Negativas

- Restricción comercial: no se puede vender HablaIA con pictogramas ARASAAC
- Primera búsqueda de un término nuevo tiene latencia mayor (llamada a ARASAAC + descarga de imágenes)
- Los pictogramas importados carecen de categorización semántica (todos en "Sin categoría")
- Estilo visual único no personalizable
- El almacenamiento en disco crece con cada búsqueda nueva
- La query `unaccent(LOWER(...))` impide el uso de índices B-tree convencionales

### Mitigaciones

- **Restricción comercial:** Mientras el proyecto sea educativo/terapéutico es compatible. Si se comercializa: migrar a Mulberry Symbols (GPL) o solicitar permiso a ARASAAC
- **Latencia primera búsqueda:** Amortiguada con debounce de 300ms en frontend y límite de 10 resultados
- **Categorización:** En el futuro se podría implementar clasificación automática de pictogramas importados
- **Estilo visual:** En el futuro se podría permitir upload de pictogramas custom del usuario
- **Almacenamiento:** Con 194 pictogramas base, el volumen adicional es marginal (imágenes de 500px)
- **Índices:** Con el tamaño actual de la tabla (~200 filas), sequential scan con `unaccent()` es eficiente. Si crece significativamente, se puede añadir un índice funcional GIN con `pg_trgm`

## Alternativas Consideradas

### 1. Mulberry Symbols (GPL)

**Pros:** Licencia GPL (uso comercial permitido), 3.500+ símbolos
**Contras:** Menor cobertura que ARASAAC, menos conocido en España
**Rechazo:** Para MVP, ARASAAC es mejor (más conocido por terapeutas)

### 2. Symbolstix (Commercial)

**Pros:** 12.000+ símbolos, muy completo
**Contras:** $49/año licencia, comercial desde día 1
**Rechazo:** Coste prohibitivo para MVP educativo

### 3. Pictogramas propios (diseño custom)

**Pros:** Control total, branding
**Contras:** Requiere validación por terapeutas, los pictogramas ARASAAC ya están validados clínicamente
**Rechazo:** Inviable para MVP, aunque con IA generativa se reduciría el tiempo de diseño

### 4. Solo API ARASAAC (sin base de datos local)

**Pros:** Siempre actualizado, sin almacenamiento local, implementación simple
**Contras:** Dependencia total de ARASAAC (si cae, la app es inutilizable), latencia en cada búsqueda (100-500ms), sin soporte offline
**Rechazo:** Inaceptable para una aplicación SAAC donde la disponibilidad es crítica

### 5. Solo base de datos local (sin fallback ARASAAC)

**Pros:** Máxima velocidad, funciona offline, control total del contenido
**Contras:** Limitado a 194 pictogramas pre-cargados, no escala sin intervención manual
**Rechazo:** 194 pictogramas cubren el 80% del uso diario pero no los términos específicos que cada usuario necesita

### 6. Motor de búsqueda dedicado (Elasticsearch / Meilisearch)

**Pros:** Búsqueda full-text avanzada, fuzzy matching, relevancia por scoring
**Contras:** Infraestructura adicional, complejidad operacional, over-engineering para ~200 pictogramas
**Rechazo:** El volumen actual no justifica un motor de búsqueda dedicado

## Plan de Migración Futura

Si se decide comercializar el proyecto:

- **Opción A:** Solicitar permiso a ARASAAC (arasaac@aragon.es)
- **Opción B:** Migrar a Mulberry Symbols (GPL, libre comercial)
- **Opción C:** Híbrido (ARASAAC por defecto, Mulberry para usuarios premium)

Esfuerzo estimado: 1-2 días (el código ya abstrae `PictogramProviderInterface`).

## Referencias

- [ARASAAC](https://arasaac.org)
- [ARASAAC API Docs](https://arasaac.org/developers/api)
- [Licencia CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
- [Mulberry Symbols](https://mulberrysymbols.org/)
- [PostgreSQL unaccent](https://www.postgresql.org/docs/16/unaccent.html)
- ADR-008: Codificación de Color Fitzgerald Key
