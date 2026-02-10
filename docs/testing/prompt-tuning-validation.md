# Validación del Prompt Tuning LLM

**Fecha:** 2026-02-10
**Modelo:** Gemini 2.5 Flash (free tier, 20 RPD)
**Temperatura:** 0.5
**maxOutputTokens:** 2048

## Contexto

El sistema genera frases humanizadas a partir de secuencias de pictogramas ARASAAC.
El prompt incluye 20 ejemplos few-shot (1-10 pictogramas), reglas de coherencia semántica
y autovalidación con criterio de logopeda profesional.

## Configuración técnica

| Parámetro | Valor |
|---|---|
| Modelo | `gemini-2.5-flash` |
| API field | `system_instruction` (separado de `contents`) |
| `responseMimeType` | `application/json` |
| Temperatura | 0.5 |
| maxOutputTokens | 2048 (necesario para thinking models) |

### Por qué maxOutputTokens = 2048

Gemini 2.5 Flash es un "thinking model" que consume tokens internos para razonamiento.
Con `maxOutputTokens: 256`, el modelo usaba ~243 tokens en razonamiento y solo quedaban
~13 para la respuesta, produciendo JSON truncado (`finishReason: "MAX_TOKENS"`).
Con 2048 hay margen suficiente para razonamiento + respuesta.

### Decisiones de diseño del prompt

**Por qué el prompt está en PHP (`PhrasePrompt::SYSTEM`) y no externalizado (YAML/.env):**

1. **Consistencia para el usuario SAAC.** El usuario final de este sistema es una persona
   con diversidad funcional que depende de respuestas predecibles y consistentes para
   comunicarse. Externalizar el prompt facilita modificarlo sin pasar por el ciclo de
   desarrollo (test, review, deploy), lo que puede generar regresiones silenciosas en la
   calidad de las frases. Un cambio mal calibrado en el prompt puede producir frases
   incoherentes, literales o confusas — causando más daño que beneficio al usuario final.
   El prompt validado debe tratarse como código estable, no como configuración ajustable.

2. **Es lógica de negocio, no configuración de entorno.** El prompt define *cómo* el sistema
   genera frases SAAC — forma parte del comportamiento de la aplicación, no es un parámetro
   de infraestructura como una URL o API key. En Clean Architecture, la lógica de negocio
   pertenece al código, no a ficheros de configuración.

3. **Testeable directamente.** Los tests unitarios acceden a `PhrasePrompt::SYSTEM` sin
   necesidad de cargar configuración externa ni inyectar dependencias adicionales.

4. **Los parámetros que SÍ cambian entre entornos están externalizados.** Temperatura, modelo,
   API key y maxTokens están en `.env` porque son configuración de infraestructura. El prompt
   no cambia entre dev/staging/prod — es el mismo comportamiento en todos los entornos.

**Cuándo tendría sentido externalizar:** Si el sistema necesitara prompts diferentes por
idioma, por perfil de usuario o A/B testing, se movería a base de datos o fichero de
configuración. En la fase actual (MVP monolingüe) no aplica.

## Metodología

Las pruebas se realizaron mediante llamadas `curl` al endpoint `POST /api/phrases/generate`
del backend desplegado en Docker (entorno dev local). Cada petición envía los UUIDs reales
de los pictogramas almacenados en PostgreSQL y recibe la respuesta del modelo LLM en tiempo
real (no mocks). Las respuestas se registran tal cual las devuelve la API.

## Resultados: 14/14 (100%)

| # | Pictogramas | Cant. | Variación 1 | Variación 2 | Variación 3 |
|---|---|---|---|---|---|
| 1 | hola | 1 | Hola! Que tal? | Buenos dias! | Hola, como estas? |
| 2 | ir, parque | 2 | Quiero ir al parque | Vamos al parque! | Podemos ir al parque, por favor? |
| 3 | yo, querer, agua | 3 | Quiero agua | Tengo mucha sed | Me das agua, por favor? |
| 4 | yo, estar, triste | 3 | Estoy triste | Me siento un poco triste | No me encuentro bien |
| 5 | yo, no, gustar, colegio | 4 | No me gusta el colegio | El colegio no me apetece nada | No quiero ir al colegio |
| 6 | mama, yo, querer, comer, agua | 5 | Mama, quiero **beber** agua | Mama, tengo sed, quiero agua | Mama, me das agua, por favor? |
| 7 | mama, yo, querer, comer, galleta | 5 | Mama, quiero una galleta | Mama, puedo comer una galleta? | Mama, me apetece una galleta |
| 8 | papa, yo, querer, ir, tienda | 5 | Papa, quiero ir a la tienda | Papa, vamos a la tienda! | Papa, podemos ir a la tienda? |
| 9 | yo, querer, jugar, pelota, parque, despues, amigo | 7 | Quiero jugar a la pelota en el parque despues con mi amigo | Que ganas de jugar a la pelota con mi amigo en el parque luego! | Podemos ir a jugar a la pelota con mi amigo al parque mas tarde? |
| 10 | papa, yo, querer, ir, playa, manana, coche | 7 | Papa, quiero ir a la playa en coche manana | Papa, que ilusion ir manana a la playa en coche! | Papa, podemos ir manana a la playa en coche? |
| 11 | mama, hoy, yo, estar, contento, ir, playa, coche | 8 | Mama, hoy estoy contento porque vamos a la playa en coche | Mama, que alegria! Hoy nos vamos a la playa en coche | Mama, me hace mucha ilusion ir hoy a la playa en coche |
| 12 | yo, estar, cansado, querer, dormir, casa, hoy, mama | 8 | Mama, estoy cansado y quiero dormir en casa hoy | Que cansado estoy, mama! Hoy quiero descansar en casa | Mama, me siento muy cansado. Podemos ir a casa a dormir hoy? |
| 13 | yo, querer, ir, casa, abuela, manana, mama, papa, coche | 9 | Manana quiero ir a casa de la abuela con mama y papa en coche | Que ganas de ir manana en coche a casa de la abuela con mis padres! | Mama y papa, podemos ir manana en coche a casa de la abuela? |
| 14 | hoy, yo, estar, cansado, no, gustar, colegio, querer, dormir, casa | 10 | Hoy estoy cansado, no me gusta el colegio y quiero dormir en casa | Estoy agotado hoy! No quiero estar en el colegio, prefiero dormir en casa | Hoy estoy cansado y no me siento bien. Puedo ir a casa a dormir? |

## Comparativa con Flash Lite (sesión anterior)

| Aspecto | Flash Lite | Flash |
|---|---|---|
| Coherencia semántica | "comer agua" (incorrecto) | "beber agua" (corregido) |
| Formato JSON | 2/10 double-encoded | 14/14 JSON limpio |
| Calidad frases | Buena | Excelente (más naturales) |
| Thinking tokens | No (no es thinking model) | Sí (~200-300 tokens internos) |
| maxOutputTokens necesarios | 256 suficiente | 2048 necesario |

## Caso destacado: corrección semántica

**Entrada:** mamá + yo + querer + comer + agua

- **Flash Lite:** "Mamá, quiero comer agua" (incoherencia semántica)
- **Flash:** "Mamá, quiero **beber** agua" (corrección automática)

El prompt incluye la regla: *"Corrige incoherencias semánticas: usa el verbo adecuado
al contexto (comer + agua → beber agua)"* y la autovalidación: *"Valida como lo haría
un logopeda profesional: ¿coherencia semántica?"*

## Problemas encontrados y solucionados

| Problema | Causa | Solución |
|---|---|---|
| JSON truncado (`{"`) | `maxOutputTokens: 256` insuficiente para thinking model | Aumentado a 2048 |
| Flash devuelve markdown | System prompt concatenado en `contents` | Separado a campo `system_instruction` de la API |
| "comer agua" | Sin regla de coherencia semántica | Regla en prompt + autovalidación logopeda |
| JSON double-encoded (Flash Lite) | Modelo wrappea JSON en JSON | Resuelto al cambiar a Flash con `system_instruction` |

## Observaciones

- Las 3 variaciones por frase siguen el patrón: directa, expresiva, cortés
- El modelo interpreta la intención comunicativa, no traduce literalmente
- Conjuga verbos correctamente según el sujeto (yo querer → quiero)
- Añade artículos, preposiciones y pronombres de forma natural
- Con 9-10 pictogramas genera frases complejas coherentes de hasta 15 palabras
- El fallback (concatenación literal) solo se activa al agotar el rate limit (429)
- Se implementó fallback automático entre modelos: Flash (primario) → Flash Lite (secundario),
  duplicando la capacidad del free tier a 40 RPD antes de recurrir a la concatenación literal

## Limitaciones y trabajo futuro

Esta validación cubre el comportamiento técnico del prompt con 14 combinaciones representativas
(1-10 pictogramas). Para un entorno de producción más allá del MVP, sería necesario:

- **Testing con usuarios reales:** Observar cómo interactúan personas con diversidad funcional
  (TEA, afasia, parálisis cerebral, ELA) y si las frases generadas se ajustan a sus necesidades
  comunicativas reales. La validación técnica no sustituye la validación con el usuario final.
- **Volumen de pruebas mayor:** Ampliar a cientos de combinaciones cubriendo vocabulario
  específico por contexto (escolar, doméstico, sanitario) y detectar casos extremos.
- **Analítica de uso:** PostHog (planificado) permitirá medir qué variaciones eligen los usuarios,
  qué secuencias producen frases poco naturales, y dónde el modelo falla con datos reales de uso.
- **Feedback loop:** Iterar el prompt basándose en datos reales, no en suposiciones. Los cambios
  en el prompt deben pasar por el ciclo completo de desarrollo (test, review, deploy) para
  proteger la consistencia de la experiencia del usuario SAAC.
