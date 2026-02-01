# ADR-003: ARASAAC como Fuente de Pictogramas

**Estado:** Aceptado
**Fecha:** 2026-01-31
**Contexto:** HablaIA - Pictogramas para SAAC

## Contexto

Necesitamos pictogramas para el comunicador. Opciones:
1. Crear propios (diseño custom)
2. Comprar licencia (Symbolstix, SymbolWorld)
3. Usar recursos libres (ARASAAC, Mulberry Symbols)

**Requisitos:**
- Alto contraste y claridad
- Multilingüe (español prioridad)
- Cobertura amplia (>10,000 conceptos)
- Licencia compatible con MVP educativo/terapéutico

## Decisión

Usar **ARASAAC** (ARAgonese System of Augmentative and Alternative Communication).

### API ARASAAC

**Endpoint de búsqueda:**
```http
GET https://api.arasaac.org/api/pictograms/es/search/comer
```

**Respuesta:**
```json
[
  {
    "_id": 4887,
    "keywords": [
      {"keyword": "comer", "type": 1}
    ],
    "synsets": ["eat-01"],
    "categories": ["alimentos"]
  }
]
```

**Descarga de imagen:**
```http
GET https://api.arasaac.org/api/pictograms/4887?download=false
```

### Arquitectura de Sincronización

```php
// Backend: Comando Symfony
php bin/console app:sync-arasaac

// UseCase: SyncPictogramsFromArasaacUseCase
1. Define keywords (150 básicos para MVP)
2. Por cada keyword → GET /api/pictograms/es/search/{keyword}
3. Descarga imagen → /public/pictograms/{id}.png
4. Inserta en DB:
   - pictogram (id, arasaac_id, label, image_path, category_id)
```

### Licencia: Creative Commons BY-NC-SA 4.0

**Permite:**
- Uso educativo
- Uso terapéutico sin ánimo de lucro
- Modificación (adaptación visual)
- Redistribución (con misma licencia)

**Prohibe:**
- Uso comercial (venta del software)
- Eliminar atribución

**Implicación:** HablaIA debe ser **NO comercial** en Fase 1-4.

## Consecuencias

### Positivas

- **Calidad:** Pictogramas profesionales diseñados por terapeutas
- **Cobertura:** >30,000 pictogramas en español
- **Coste:** Gratuito
- **Comunidad:** Estándar de facto en España (terapeutas lo conocen)
- **API estable:** Mantenida por Gobierno de Aragón

### Negativas

- **Restricción comercial:** No podemos vender HablaIA con ARASAAC
- **Dependencia API:** Si cae, debemos tener fallback local
- **Estilo visual único:** No personalizable (todos usan mismo estilo)

### Mitigaciones

- **Restricción comercial:**
  - Fase 1-4: Free, educativo (compatible con ARASAAC)
  - Fase 5-6 (comercial): Migrar a Mulberry Symbols (GPL) o pedir permiso a ARASAAC
- **Dependencia API:**
  - Caché local de imágenes descargadas
  - Sincronización inicial + updates periódicos (no en tiempo real)
- **Estilo visual:**
  - Fase 6: Permitir upload de pictogramas custom del usuario

## Alternativas Consideradas

### 1. Mulberry Symbols (GPL)

**Pros:** Licencia GPL (comercial OK), 3,500+ símbolos
**Contras:** Menor cobertura que ARASAAC, menos conocido en España
**Rechazo:** Para MVP, ARASAAC es mejor (más conocido por terapeutas)

### 2. Symbolstix (Commercial)

**Pros:** 12,000+ símbolos, muy completo
**Contras:** $49/año licencia, comercial desde día 1
**Rechazo:** Coste prohibitivo para MVP educativo

### 3. Pictogramas Propios (Diseño Custom)

**Pros:** Control total, branding
**Contras:** $1,000+ diseño, 6+ meses producción
**Rechazo:** Inviable para MVP

## Plan de Migración Futura

Si en Fase 5 decidimos comercializar:

**Opción A:** Solicitar permiso a ARASAAC (arasaac@aragon.es)
**Opción B:** Migrar a Mulberry Symbols (GPL, libre comercial)
**Opción C:** Híbrido - ARASAAC por defecto, Mulberry para usuarios premium

**Esfuerzo estimado:** 1-2 días (código ya abstrae `PictogramProviderInterface`)

## Referencias

- [ARASAAC](https://arasaac.org)
- [API Docs](https://arasaac.org/developers/api)
- [Licencia CC BY-NC-SA](https://creativecommons.org/licenses/by-nc-sa/4.0/)
- [Mulberry Symbols](https://mulberrysymbols.org/)
