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

#### Estrategia de Almacenamiento: Híbrida (Local + CDN)

**Problema:** Si ARASAAC cae, la app sería inutilizable. Para una aplicación SAAC esto es inaceptable.

**Solución:**
- **Vocabulario base (80% uso diario):** ~150 pictogramas descargados localmente
- **Búsquedas nuevas (20%):** CDN de ARASAAC + descarga local tras primer uso

```
backend/
├── config/
│   └── vocabulary/
│       └── core_vocabulary.yaml    # 150 palabras del core vocabulary SAAC
└── public/
    └── pictograms/
        └── {arasaac_id}.png        # Imágenes descargadas (~50MB total)
```

#### Core Vocabulary SAAC

El **core vocabulary** en SAAC representa las ~200 palabras que cubren el 80% de la comunicación diaria:

| Categoría | Ejemplos | Cantidad |
|-----------|----------|----------|
| Personas | yo, tú, mamá, papá, familia, amigo | ~20 |
| Acciones | querer, ir, comer, beber, jugar, ayudar | ~35 |
| Emociones | feliz, triste, enfadado, cansado, miedo | ~15 |
| Lugares | casa, colegio, parque, baño, hospital | ~15 |
| Objetos | agua, comida, juguete, libro, teléfono | ~25 |
| Comida | pan, leche, fruta, galleta, zumo | ~20 |
| Transporte | coche, autobús, tren, avión | ~10 |
| Social | hola, adiós, gracias, por favor, sí, no | ~10 |

**Fuente:** Basado en estudios de core vocabulary de ARASAAC y literatura SAAC.

#### Comando de Sincronización

```bash
# Sincronizar vocabulario base completo (150 pictogramas)
php bin/console app:arasaac:sync --all

# Sincronizar keywords específicos
php bin/console app:arasaac:sync comer beber dormir --category=Acciones

# Ver qué se sincronizaría (sin ejecutar)
php bin/console app:arasaac:sync --all --dry-run
```

#### Flujo de Sincronización

```php
// 1. Leer vocabulario desde config/vocabulary/core_vocabulary.yaml
// 2. Por cada keyword:
//    a. GET https://api.arasaac.org/v1/pictograms/es/search/{keyword}
//    b. Descargar imagen: GET https://static.arasaac.org/pictograms/{id}/{id}_500.png
//    c. Guardar en: public/pictograms/{arasaac_id}.png
//    d. Insertar en DB con path local: /pictograms/{arasaac_id}.png
```

#### Flujo Principal: Grid de Pictogramas (80% vocabulario base)

El frontend muestra el vocabulario base directamente en el grid por categorías:

```
INICIO (Carga de app)
        │
        ▼
Frontend: GET /api/categories
        │
        ▼
Frontend: GET /api/pictograms?categoryId={id}
        │
        ▼
Backend retorna pictogramas desde DB local
(path: /pictograms/{arasaac_id}.png)
        │
        ▼
Frontend muestra grid con vocabulario base (80%)
```

#### Flujo de Búsqueda: Pictogramas adicionales (20% restante)

Cuando el usuario necesita un pictograma fuera del vocabulario base:

```
Usuario busca "dinosaurio"
        │
        ▼
Frontend: GET /api/pictograms/search?q=dinosaurio
        │
        ▼
¿Existe en DB local? ───Sí──► Retornar (path local)
        │
        No
        ▼
Backend busca en API ARASAAC
        │
        ▼
¿Encontrado? ───No──► Retornar vacío
        │
        Sí
        ▼
Descargar imagen a /public/pictograms/
        │
        ▼
Guardar en DB (cachear para futuro)
        │
        ▼
Retornar pictograma
```

> **Nota:** La búsqueda permite que el vocabulario crezca orgánicamente según el uso real. Los pictogramas buscados se guardan localmente para futuras consultas.

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
