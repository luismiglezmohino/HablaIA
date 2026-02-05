# ADR-009: Soporte Multi-proveedor LLM (OpenAI + Gemini)

**Estado:** Aceptado
**Fecha:** 2026-02-05
**Contexto:** HablaIA - Validación práctica de Clean Architecture

## Contexto

En ADR-001 se adoptó Clean Architecture con el objetivo explícito de "permitir cambiar infraestructura sin afectar lógica de negocio". En ADR-002 se eligió OpenAI como proveedor inicial de IA, pero se valoraron alternativas (Claude, modelos locales) anticipando la posibilidad de cambio futuro.

La interfaz `PhraseGeneratorInterface` se definió en la capa Domain desde el inicio, desacoplada de cualquier proveedor concreto:

```php
// Domain — sin dependencia de infraestructura
interface PhraseGeneratorInterface
{
    public function generate(PictogramSequence $sequence, array $labels): array;
}
```

Ahora se materializa esa decisión añadiendo **Google Gemini** como segundo proveedor, con dos objetivos:

1. **Validar Clean Architecture:** Demostrar que añadir un proveedor LLM alternativo solo requiere cambios en Infrastructure — cero modificaciones en Domain y Application.
2. **Reducir coste:** Gemini 2.5 Flash Lite ofrece un free tier generoso frente a OpenAI GPT-4o-mini ($0.15/1M tokens).

## Decisión

Implementar soporte multi-proveedor configurable por variable de entorno:

```
PHRASE_PROVIDER=gemini|openai|fake
```

### Proveedores

| Proveedor | Modelo | Coste | Uso recomendado |
|-----------|--------|-------|-----------------|
| `gemini` | Gemini 2.5 Flash Lite | Gratis (10 RPM, 20 RPD) | Default en dev y prod |
| `openai` | GPT-4o-mini | $0.15/1M tokens | Alternativa configurable |
| `fake` | Templates locales | Gratis | Fallback si los proveedores LLM fallan |

### Cadena de fallback

```
1. Cache (BD)                    ← Hit: < 100ms
2. Proveedor (Gemini/OpenAI)     ← Miss: ~1-2s
3. Fake (templates)              ← Si LLM falla: frases básicas pero funcionales
```

Si el proveedor LLM falla (timeout, API caída, key inválida), el sistema cae al Fake. El Fake no genera frases inteligentes — usa templates fijos como "Quiero %s" — pero garantiza que el usuario siempre recibe una respuesta funcional en lugar de un error.

### Prompt compartido

El prompt del sistema es compartido entre todos los proveedores como constante en Infrastructure. Cada proveedor lo adapta a su formato HTTP pero el contenido es idéntico.

Esta decisión es deliberada: el público objetivo de HablaIA son personas con necesidades comunicativas específicas (TEA, afasia, parálisis cerebral, ELA). La calidad y consistencia de las frases generadas es crítica para estos usuarios. No tiene sentido aplicar test A/B de prompts ni variar el comportamiento entre proveedores — la experiencia del usuario debe ser predecible e igual independientemente del proveedor LLM que haya por debajo.

### Arquitectura

```
PhraseGeneratorInterface (Domain)
    ├── GeminiPhraseGenerator (Infrastructure)      ← NUEVO
    ├── RealOpenAIPhraseGenerator (Infrastructure)   ← existente
    └── FakeOpenAIPhraseGenerator (Infrastructure)   ← existente (fallback)

PhrasePrompt (Infrastructure)                        ← NUEVO
    → Constantes compartidas: SYSTEM_PROMPT, USER_PROMPT_TEMPLATE

PhraseGeneratorFactory (Infrastructure)
    → Lee PHRASE_PROVIDER del entorno
    → Instancia la implementación correspondiente
```

### Impacto por capa

| Capa | Ficheros modificados | Ficheros nuevos |
|------|---------------------|-----------------|
| **Domain** | 0 | 0 |
| **Application** | 0 | 0 |
| **Infrastructure** | Factory renombrada, config | GeminiPhraseGenerator, GeminiException, PhrasePrompt, tests |

Esto valida empíricamente la premisa de ADR-001: **el dominio permanece puro ante cambios de infraestructura.**

## Consecuencias

### Positivas

- **Validación arquitectónica:** Evidencia medible de que Clean Architecture cumple su promesa
- **Coste cero:** Gemini 2.5 Flash Lite gratis para MVP y demos
- **Resiliencia:** Si el proveedor LLM falla, fallback a templates garantiza respuesta al usuario
- **Extensibilidad:** Añadir futuros proveedores (Claude, Mistral, modelos locales) sigue el mismo patrón
- **Cambio por configuración:** `PHRASE_PROVIDER` en `.env` cambia el proveedor sin tocar código

### Negativas

- **Complejidad:** Factory con switch de proveedores
- **Mantenimiento:** Cada proveedor tiene su formato de API HTTP
- **Testing:** Cada implementación necesita sus propios tests unitarios

### Mitigaciones

- Factory simple con 3 opciones, complejidad contenida
- Prompt compartido como constante: un solo punto de cambio para el contenido
- Tests unitarios con mocks HTTP para cada proveedor

## Alternativas Consideradas
### 1. Solo OpenAI sin alternativa

**Pros:** Ya implementado, calidad probada
**Contras:** Coste en cada demo y test. Sin free tier, cada prueba del profesor o del tribunal cuesta dinero.
**Rechazo:** Para un MVP que se va a demostrar repetidamente, depender de un servicio de pago no es práctico.

## Referencias

- ADR-001: Adopción de Clean Architecture
- ADR-002: OpenAI API para Humanización de Frases
- [Google Gemini API Free Tier](https://ai.google.dev/pricing)
- [Clean Architecture (Uncle Bob)](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
