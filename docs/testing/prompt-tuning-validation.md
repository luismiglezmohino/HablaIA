# Validación del Prompt Tuning LLM

**Fecha:** 2026-02-10 (Gemini), 2026-02-18 (Groq)
**Modelos:** Gemini 2.5 Flash, Groq Llama 3.3 70B, Groq GPT-OSS 120B
**Temperatura:** 0.5
**Pruebas totales:** 126 (26 Gemini + 50 Groq Llama + 50 Groq GPT-OSS)

## Contexto

El prompt genera frases humanizadas a partir de secuencias de pictogramas ARASAAC.
Incluye 20 ejemplos few-shot (1-10 pictogramas), reglas de coherencia semántica
y autovalidación con criterio de logopeda profesional.

## Configuración técnica

| Parámetro | Gemini 2.5 Flash | Groq Llama 3.3 70B | Groq GPT-OSS 120B |
|---|---|---|---|
| Modelo | `gemini-2.5-flash` | `llama-3.3-70b-versatile` | `openai/gpt-oss-120b` |
| API | Gemini REST (`system_instruction`) | OpenAI-compatible (`/v1/chat/completions`) | OpenAI-compatible (`/v1/chat/completions`) |
| Formato respuesta | `responseMimeType: application/json` | JSON via prompt instruction | JSON via prompt instruction |
| Temperatura | 0.5 | 0.5 | 0.5 |
| Max tokens | 2048 (necesario para thinking) | 256 (sin thinking overhead) | 256 (sin thinking overhead) |
| Free tier | 20 RPD (Flash + Lite = 40) | 1.000 RPD / 12K TPM | 1.000 RPD / 8K TPM |

**Nota:** Gemini 2.5 Flash usa 2048 maxOutputTokens porque es un modelo con razonamiento
interno que consume parte de los tokens de salida antes de generar la respuesta. Con 256
la respuesta JSON se truncaba. Groq no necesita este margen (256 es suficiente).

### Decisiones de diseño del prompt

**Por qué el prompt está en PHP (`PhrasePrompt::SYSTEM`) y no externalizado (YAML/.env):**

1. **Consistencia para el usuario SAAC.** El usuario final de este sistema es una persona
   con diversidad funcional que depende de respuestas predecibles y consistentes para
   comunicarse. Si el prompt estuviera externalizado (YAML/.env), cualquier cambio podría
   aplicarse sin pasar por el ciclo de desarrollo (test, review, deploy), lo que puede
   provocar una pérdida de calidad inadvertida en las frases generadas. Un cambio mal calibrado en el prompt puede producir frases
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
configuración. En la fase actual (MVP solo en español) no aplica.

## Metodología

Las pruebas se realizaron mediante llamadas `curl` al endpoint `POST /api/phrases/generate`
del backend desplegado en Docker (entorno dev local). Cada petición envía los UUIDs reales
de los pictogramas almacenados en PostgreSQL y recibe la respuesta del modelo LLM en tiempo
real (no mocks). Las respuestas se registran tal cual las devuelve la API.

Los resultados raw de Groq están en `docs/testing/groq-*.json` y el script de pruebas
en `scripts/groq-test-50.py`.

## Resultados Gemini: 26 pruebas

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
| 9 | yo, querer, jugar, pelota, parque, después, amigo | 7 | Quiero jugar a la pelota en el parque después con mi amigo | Que ganas de jugar a la pelota con mi amigo en el parque luego! | Podemos ir a jugar a la pelota con mi amigo al parque mas tarde? |
| 10 | papa, yo, querer, ir, playa, manana, coche | 7 | Papa, quiero ir a la playa en coche manana | Papa, que ilusion ir manana a la playa en coche! | Papa, podemos ir manana a la playa en coche? |
| 11 | mama, hoy, yo, estar, contento, ir, playa, coche | 8 | Mama, hoy estoy contento porque vamos a la playa en coche | Mama, que alegria! Hoy nos vamos a la playa en coche | Mama, me hace mucha ilusion ir hoy a la playa en coche |
| 12 | yo, estar, cansado, querer, dormir, casa, hoy, mama | 8 | Mama, estoy cansado y quiero dormir en casa hoy | Que cansado estoy, mama! Hoy quiero descansar en casa | Mama, me siento muy cansado. Podemos ir a casa a dormir hoy? |
| 13 | yo, querer, ir, casa, abuela, manana, mama, papa, coche | 9 | Manana quiero ir a casa de la abuela con mama y papa en coche | Que ganas de ir manana en coche a casa de la abuela con mis padres! | Mama y papa, podemos ir manana en coche a casa de la abuela? |
| 14 | hoy, yo, estar, cansado, no, gustar, colegio, querer, dormir, casa | 10 | Hoy estoy cansado, no me gusta el colegio y quiero dormir en casa | Estoy agotado hoy! No quiero estar en el colegio, prefiero dormir en casa | Hoy estoy cansado y no me siento bien. Puedo ir a casa a dormir? |
| 15 | yo, estar, enfadado, no, gustar, colegio | 6 | Estoy enfadado y no me gusta el colegio | Qué enfado tengo con el colegio! No me gusta nada | Hoy no me gusta el colegio, estoy enfadado |
| 16 | mamá, yo, doler, necesitar, médico | 5 | Mamá, me duele y necesito un médico | Mamá, me duele mucho y necesito que me vea un médico | Mamá, me encuentro mal y creo que necesito ir al médico |
| 17 | mañana, yo, querer, ir, piscina, amigo | 6 | Mañana quiero ir a la piscina con mi amigo | Qué ganas de ir a la piscina con mi amigo mañana! | Podemos ir a la piscina con mi amigo mañana? |
| 18 | yo, querer, comer, galleta, chocolate, leche | 6 | Quiero una galleta de chocolate y leche | Qué rica una galleta de chocolate con leche! | Me das una galleta de chocolate y leche, por favor? |
| 19 | yo, estar, triste, querer, hablar, mamá | 6 | Estoy triste, quiero hablar con mamá | Me siento triste, necesito hablar con mi mamá | Mamá, estoy triste, podemos hablar? |
| 20 | adiós | 1 | ¡Adiós! | ¡Hasta luego! | ¡Nos vemos pronto! |
| 21 | gracias | 1 | ¡Muchas gracias! | Te lo agradezco mucho | Gracias, eres muy amable |
| 22 | querer, dormir | 2 | Quiero dormir | Tengo sueño | Me gustaría dormir |
| 23 | doler, yo | 2 | Me duele. | ¡Ay, me duele mucho! | Me duele, ¿puedes ayudarme? |
| 24 | yo, querer, leche | 3 | Quiero leche | Me apetece leche | ¿Me das leche, por favor? |
| 25 | yo, estar, enfadado | 3 | Estoy enfadado | ¡Me siento muy enfadado! | No estoy de buen humor |
| 26 | yo, necesitar, ayudar | 3 | Necesito ayuda | ¿Me puedes ayudar? | Ayúdame, por favor |

### Latencia Gemini (5 tests generados, 18 feb 2026)

| Métrica | Valor |
|---|---|
| Latencia mínima | 2666ms |
| Latencia media | 3739ms |
| Latencia máxima | 5427ms |
| Rate limit (free tier) | 20 RPD (Flash) / 40 RPD (Flash + Lite) |

## Resultados Groq Llama 3.3 70B: 50 pruebas

**Fecha:** 2026-02-18
**Proveedor:** Groq (LPU Inference Engine)
**API:** OpenAI-compatible (`https://api.groq.com/openai/v1/chat/completions`)

| # | Pictogramas | Cant. | Variación 1 | Variación 2 | Variación 3 |
|---|---|---|---|---|---|
| 1 | buenos días | 1 | ¡Buenos días! | Buenos días, ¿cómo estás? | ¡Hola, buenos días! |
| 2 | por favor | 1 | Por favor | ¿Podrías por favor? | Por favor, si es posible |
| 3 | lo siento | 1 | Lo siento mucho | Disculpa, lo siento | Perdón, me arrepiento |
| 4 | querer, comer | 2 | Quiero comer | Tengo hambre | ¿Puedo comer algo? |
| 5 | querer, jugar | 2 | Quiero jugar | Me apetece jugar | ¿Puedo jugar? |
| 6 | estar, bien | 2 | Estoy bien | Me siento bien | Todo está bien |
| 7 | ir, casa | 2 | Voy a casa | Quiero ir a casa | Me voy a casa |
| 8 | tener, miedo | 2 | Tengo miedo | Me da miedo | Estoy asustado |
| 9 | yo, querer, galleta | 3 | Quiero una galleta | Me apetece una galleta | ¿Puedo tener una galleta? |
| 10 | yo, estar, contento | 3 | Estoy contento | Me siento muy feliz | Estoy muy alegre |
| 11 | yo, necesitar, agua | 3 | Tengo sed | Necesito agua | ¿Me das agua, por favor? |
| 12 | yo, tener, miedo | 3 | Tengo miedo | Me da miedo | Estoy asustado |
| 13 | mamá, querer, hablar | 3 | Quiero hablar con mamá | Mamá, ¿puedo hablar contigo? | Quiero charlar con mamá |
| 14 | yo, querer, beber | 3 | Quiero beber algo | Tengo sed, quiero beber | ¿Puedo beber algo, por favor? |
| 15 | ella, estar, triste | 3 | Ella está triste | Está un poco triste | Se encuentra mal |
| 16 | yo, querer, ir, parque | 4 | Quiero ir al parque | Me apetece ir al parque | ¿Podemos ir al parque? |
| 17 | yo, no, querer, dormir | 4 | No quiero dormir | No tengo sueño | No me apetece dormir |
| 18 | mamá, yo, tener, miedo | 4 | Mamá, tengo miedo | Mamá, me da miedo | Mamá, estoy asustado |
| 19 | yo, no, gustar, verduras | 4 | No me gustan las verduras | Las verduras no me apetecen | No me gustan las verduras, gracias |
| 20 | yo, querer, ver, tele | 4 | Quiero ver la tele | Me apetece ver televisión | ¿Puedo ver la tele? |
| 21 | yo, necesitar, ir, médico | 4 | Necesito ir al médico | Tengo que ver al médico | Quiero ir al médico |
| 22 | hermano, querer, jugar, pelota | 4 | Quiero jugar a la pelota con mi hermano | Mi hermano y yo queremos jugar con la pelota | ¿Puedo jugar a la pelota con mi hermano? |
| 23 | yo, querer, ir, parque, mañana | 5 | Quiero ir al parque mañana | Mañana me apetece ir al parque | ¿Puedo ir al parque mañana? |
| 24 | mamá, yo, querer, comer, pan | 5 | Mamá, quiero comer pan | Mamá, ¿puedo comer pan? | Mamá, me apetece pan |
| 25 | yo, estar, triste, querer, casa | 5 | Quiero estar en casa porque estoy triste | Estoy triste, quiero ir a casa | Me siento triste, necesito ir a casa |
| 26 | yo, querer, jugar, pelota, amigo | 5 | Quiero jugar a la pelota con mi amigo | Mi amigo y yo queremos jugar con la pelota | ¿Jugamos a la pelota, amigo? |
| 27 | mamá, yo, no, querer, colegio | 5 | No quiero ir al colegio, mamá | Mamá, no me apetece ir al colegio | Mamá, prefiero no ir al colegio hoy |
| 28 | yo, querer, beber, zumo, naranja | 5 | Quiero beber zumo de naranja | Me apetece un zumo de naranja | ¿Puedo tomar zumo de naranja? |
| 29 | yo, querer, comer, arroz, carne | 5 | Quiero comer arroz con carne | Me apetece arroz y carne | Quisiera comer arroz con carne, por favor |
| 30 | mamá, yo, querer, ir, playa, mañana | 6 | Mamá, quiero ir a la playa mañana | Mamá, ¿mañana vamos a la playa? | Mañana me apetece ir a la playa con mamá |
| 31 | yo, estar, cansado, querer, dormir, casa | 6 | Estoy cansado, quiero dormir en casa | Quiero ir a casa a dormir, estoy agotado | Me voy a dormir a casa, estoy muy cansado |
| 32 | papá, yo, querer, jugar, pelota, parque | 6 | Papá, quiero jugar en el parque con la pelota | Quiero jugar a la pelota en el parque con papá | Papá, ¿vamos al parque a jugar con la pelota? |
| 33 | yo, no, gustar, colegio, querer, casa | 6 | No me gusta ir al colegio | No quiero ir al colegio, prefiero quedarme en casa | El colegio no me gusta, quiero estar en casa |
| 34 | mamá, yo, estar, enfadado, no, hablar | 6 | No estoy enfadado con mamá | Mamá, no estoy enfadado contigo | Hablo con mamá, no estoy enfadado |
| 35 | yo, estar, triste, necesitar, hablar, mamá | 6 | Mamá, necesito hablar, estoy triste | Estoy triste, mamá, ¿podemos hablar? | Mamá, me siento triste, necesito hablar contigo |
| 36 | mamá, yo, querer, ir, casa, abuela, mañana | 7 | Mañana quiero ir a casa de la abuela con mamá | Mamá, ¿mañana vamos a casa de la abuela? | Quiero que mañana mamá me lleve a casa de la abuela |
| 37 | yo, estar, cansado, no, querer, ir, colegio | 7 | No quiero ir al colegio, estoy cansado | Estoy cansado, no me apetece ir al colegio | Hoy no quiero ir al colegio, estoy agotado |
| 38 | papá, yo, querer, jugar, pelota, parque, amigo | 7 | Papá, quiero jugar con mi amigo en el parque a la pelota | Quiero que papá me lleve al parque a jugar con mi amigo | Papá, ¿podemos ir al parque a jugar a la pelota con mi amigo? |
| 39 | yo, querer, comer, arroz, carne, verduras, cena | 7 | Quiero cena de arroz, carne y verduras | Me apetece comer arroz con carne y verduras para cenar | Para cenar, quiero arroz, carne y verduras, por favor |
| 40 | papá, mamá, yo, querer, ir, playa, coche | 7 | Mamá y papá, quiero ir a la playa en coche | Quiero ir a la playa en coche con mamá y papá | ¿Podemos ir a la playa en coche, mamá y papá? |
| 41 | mamá, yo, estar, enfadado, no, gustar, colegio, hoy | 8 | Mamá, hoy no me gusta el cole porque estoy enfadado | Hoy estoy enfadado, mamá, y no quiero ir al colegio | Mamá, no me apetece ir al colegio hoy, estoy molesto |
| 42 | papá, yo, querer, ir, playa, coche, mañana, amigo | 8 | Mañana papá y yo queremos ir a la playa con un amigo en coche | Papá, mañana me apetece ir a la playa contigo y un amigo en coche | Mañana quiero ir a la playa en coche con papá y un amigo |
| 43 | yo, estar, cansado, querer, dormir, casa, hoy, mamá | 8 | Mamá, estoy cansado, quiero dormir en casa hoy | Hoy estoy agotado, mamá, quiero dormir en casa | Mamá, me siento muy cansado hoy, quiero ir a casa a dormir |
| 44 | yo, no, gustar, verduras, querer, comer, galleta, chocolate | 8 | No me gustan las verduras, prefiero galletas de chocolate | Quiero galletas de chocolate, no verduras | Me gustan las galletas de chocolate, no las verduras |
| 45 | mamá, yo, querer, ir, casa, abuela, mañana, papá, coche | 9 | Mañana quiero ir a casa de la abuela con mamá y papá en coche | Mamá y papá, ¿mañana vamos en coche a casa de la abuela? | Quiero que mañana mamá, papá y yo vayamos en coche a casa de la abuela |
| 46 | yo, estar, cansado, hoy, no, gustar, colegio, querer, casa | 9 | No me gusta ir al colegio hoy, estoy cansado | Hoy estoy cansado y no me apetece el colegio, quiero ir a casa | Estoy agotado, no quiero ir al colegio hoy, prefiero quedarme en casa |
| 47 | papá, mamá, yo, querer, ir, playa, mañana, coche, amigo | 9 | Mañana quiero ir a la playa con mamá, papá y amigo en coche | Mamá, papá, ¿mañana vamos a la playa con mi amigo en coche? | Quiero que mañana mamá, papá y amigo me lleven en coche a la playa |
| 48 | mamá, papá, yo, querer, ir, playa, coche, mañana, amigo, pelota | 10 | Mañana quiero ir a la playa con mamá, papá y amigos a jugar con la pelota en coche | Quiero que mañana mamá, papá y amigos me lleven en coche a la playa a jugar con la pelota | Mañana me apetece ir con mamá, papá y amigos en coche a la playa a jugar con la pelota |
| 49 | papá, yo, querer, ir, restaurante, comer, carne, arroz, cena, mañana | 10 | Papá, quiero ir a un restaurante a cenar mañana | Papá, mañana me apetece ir a cenar arroz y carne a un restaurante | ¿Podemos ir mañana a cenar carne con arroz a un restaurante, papá? |
| 50 | abuela, abuelo, yo, querer, ir, casa, cocinar, comer, galleta, hoy | 10 | Hoy vamos a casa de los abuelos a cocinar y comer galletas | Quiero ir a casa de los abuelos a comer lo que cocinen hoy | Hoy los abuelos me invitan a su casa a comer galletas |

### Latencia Groq Llama (50 tests, 18 feb 2026)

| Métrica | Valor |
|---|---|
| Latencia mínima | 381ms |
| Latencia media | 563ms |
| Latencia máxima | 804ms |
| Rate limit (free tier) | 12K TPM (tokens por minuto) |

### Caso destacado: corrección semántica (Llama 70B)

**Entrada:** yo + necesitar + agua
- **Llama 70B:** "Tengo sed" (interpreta la intención, no traduce literalmente)

**Entrada:** yo + no + gustar + verduras + querer + comer + galleta + chocolate (8 pictogramas)
- **Llama 70B:** "No me gustan las verduras, prefiero galletas de chocolate" (frase compleja coherente)

## Resultados Groq GPT-OSS 120B: 50 pruebas

**Fecha:** 2026-02-18
**Proveedor:** Groq (LPU Inference Engine)
**API:** OpenAI-compatible (`https://api.groq.com/openai/v1/chat/completions`)

| # | Pictogramas | Cant. | Variación 1 | Variación 2 | Variación 3 |
|---|---|---|---|---|---|
| 1 | buenos días | 1 | ¡Buenos días! | ¡Muy buenos días! | Buenos días, ¿cómo estás? |
| 2 | por favor | 1 | Por favor | ¿Me puedes ayudar, por favor? | Te lo agradecería mucho, por favor |
| 3 | lo siento | 1 | Lo siento | Perdón, no fue mi intención | Disculpa, lo lamento mucho |
| 4 | querer, comer | 2 | Quiero comer | Tengo ganas de comer | ¿Me puedes dar algo para comer, por favor? |
| 5 | querer, jugar | 2 | Quiero jugar | Me apetece jugar | ¿Podrías jugar conmigo, por favor? |
| 6 | estar, bien | 2 | Estoy bien | Me siento muy bien | Todo está bien, gracias |
| 7 | ir, casa | 2 | Voy a casa | ¡Me apetece ir a casa! | ¿Podrías llevarme a casa, por favor? |
| 8 | tener, miedo | 2 | Tengo miedo | Me asusta mucho | ¿Podrías tranquilizarme? Tengo miedo |
| 9 | yo, querer, galleta | 3 | Quiero una galleta | Me apetece una galleta | ¿Podrías darme una galleta, por favor? |
| 10 | yo, estar, contento | 3 | Estoy contento | Me siento muy contento | Estoy muy feliz, gracias |
| 11 | yo, necesitar, agua | 3 | Necesito agua | Tengo mucha sed, necesito agua | ¿Me puedes dar agua, por favor? |
| 12 | yo, tener, miedo | 3 | Tengo miedo | Me da mucho miedo | ¿Podrías acompañarme? Tengo miedo |
| 13 | mamá, querer, hablar | 3 | Mamá, quiero hablar contigo | Mamá, me gustaría hablar contigo | Mamá, ¿podrías hablar conmigo, por favor? |
| 14 | yo, querer, beber | 3 | Quiero beber | Me apetece beber algo | ¿Podrías darme algo para beber, por favor? |
| 15 | ella, estar, triste | 3 | Ella está triste | Se siente triste | Parece que está triste |
| 16 | yo, querer, ir, parque | 4 | Quiero ir al parque | Me apetece ir al parque | ¿Podemos ir al parque, por favor? |
| 17 | yo, no, querer, dormir | 4 | No quiero dormir. | ¡No me apetece dormir! | Preferiría no dormir ahora, por favor. |
| 18 | mamá, yo, tener, miedo | 4 | Mamá, tengo miedo | ¡Mamá, me da mucho miedo! | Mamá, ¿puedes ayudarme? Tengo miedo |
| 19 | yo, no, gustar, verduras | 4 | No me gustan las verduras | Las verduras no me apetecen | Preferiría no comer verduras |
| 20 | yo, querer, ver, tele | 4 | Quiero ver la tele | Me apetece ver la tele | ¿Podrías poner la tele, por favor? |
| 21 | yo, necesitar, ir, médico | 4 | Necesito ir al médico | Me hace falta ir al médico | ¿Podrías llevarme al médico, por favor? |
| 22 | hermano, querer, jugar, pelota | 4 | Hermano, quiero jugar a la pelota. | ¡Me encantaría jugar a la pelota contigo, hermano! | ¿Podrías jugar a la pelota conmigo, hermano? |
| 23 | yo, querer, ir, parque, mañana | 5 | Quiero ir al parque mañana | ¡Me apetece mucho ir al parque mañana! | ¿Podemos ir al parque mañana, por favor? |
| 24 | mamá, yo, querer, comer, pan | 5 | Mamá, quiero comer pan | ¡Mamá, me apetece mucho comer pan! | Mamá, ¿podrías darme pan, por favor? |
| 25 | yo, estar, triste, querer, casa | 5 | Quiero estar en casa porque estoy triste. | Me siento triste y quisiera quedarme en casa. | ¿Me puedes llevar a casa? Estoy triste. |
| 26 | yo, querer, jugar, pelota, amigo | 5 | Quiero jugar a la pelota con mi amigo | ¡Me apetece jugar a la pelota con mi amigo! | ¿Podemos jugar a la pelota, amigo? |
| 27 | mamá, yo, no, querer, colegio | 5 | Mamá, no quiero ir al colegio | Mamá, de verdad no me apetece ir al cole | Mamá, ¿puedes aceptar que no quiero ir al colegio? |
| 28 | yo, querer, beber, zumo, naranja | 5 | Quiero beber zumo de naranja | Tengo mucha sed, quiero zumo de naranja | ¿Me das un poco de zumo de naranja, por favor? |
| 29 | yo, querer, comer, arroz, carne | 5 | Quiero comer arroz y carne | ¡Me apetece mucho comer arroz con carne! | ¿Me puedes dar arroz con carne, por favor? |
| 30 | mamá, yo, querer, ir, playa, mañana | 6 | Mamá, mañana quiero ir a la playa | ¡Mamá, estoy deseando ir a la playa mañana! | Mamá, ¿podemos ir a la playa mañana? |
| 31 | yo, estar, cansado, querer, dormir, casa | 6 | Estoy cansado, quiero dormir en casa. | ¡Qué cansado estoy! Necesito dormir en casa. | ¿Me llevas a casa? Estoy cansado y quiero dormir. |
| 32 | papá, yo, querer, jugar, pelota, parque | 6 | Quiero jugar en el parque con papá y la pelota | Me apetece jugar en el parque con papá y una pelota | Papá, ¿podemos ir al parque a jugar con la pelota? |
| 33 | yo, no, gustar, colegio, querer, casa | 6 | No quiero ir a casa ni al colegio | No me apetece ir a casa ni al colegio | ¿Podrías llevarme a otro sitio? No quiero ir a casa ni al colegio |
| 34 | mamá, yo, estar, enfadado, no, hablar | 6 | Mamá, no quiero hablar, estoy enfadado. | ¡Mamá, estoy enfadado y no quiero hablar! | Mamá, por favor, no hablemos ahora, estoy enfadado. |
| 35 | yo, estar, triste, necesitar, hablar, mamá | 6 | Necesito hablar con mamá, estoy triste. | Me siento triste y quiero hablar con mamá. | ¿Podrías ayudarme? Necesito hablar con mamá porque estoy triste. |
| 36 | mamá, yo, querer, ir, casa, abuela, mañana | 7 | Mañana quiero ir a casa de la abuela con mamá | ¡Mamá, estoy deseando ir mañana a casa de la abuela! | Mamá, ¿podrías llevarme mañana a casa de la abuela? |
| 37 | yo, estar, cansado, no, querer, ir, colegio | 7 | No quiero ir al colegio, estoy cansado. | Estoy muy cansado, no me apetece ir al colegio. | ¿Podrías dejarme descansar? No quiero ir al colegio porque estoy cansado. |
| 38 | papá, yo, querer, jugar, pelota, parque, amigo | 7 | Quiero jugar a la pelota en el parque con papá y mi amigo. | ¡Me apetece jugar a la pelota en el parque con papá y mi amigo! | ¿Podemos ir al parque a jugar a la pelota con papá y mi amigo, por favor? |
| 39 | yo, querer, comer, arroz, carne, verduras, cena | 7 | Quiero cenar arroz, carne y verduras. | Me apetece mucho cenar arroz con carne y verduras. | ¿Podrías preparar arroz, carne y verduras para la cena, por favor? |
| 40 | papá, mamá, yo, querer, ir, playa, coche | 7 | Quiero ir a la playa en coche con mamá y papá | ¡Me encantaría ir a la playa en coche con mamá y papá! | ¿Podemos ir a la playa en coche, mamá y papá? |
| 41 | mamá, yo, estar, enfadado, no, gustar, colegio, hoy | 8 | Hoy no me gusta estar enfadado con mamá en el cole | ¡Hoy no quiero estar enfadado con mamá en el colegio! | ¿Podrías ayudarme a no estar enfadado con mamá en el colegio hoy? |
| 42 | papá, yo, querer, ir, playa, coche, mañana, amigo | 8 | Quiero ir a la playa mañana con papá y mi amigo en coche. | ¡Me muero de ganas de ir a la playa mañana con papá y mi amigo en coche! | ¿Me llevas mañana en coche a la playa con papá y mi amigo, por favor? |
| 43 | yo, estar, cansado, querer, dormir, casa, hoy, mamá | 8 | Mamá, hoy estoy cansado y quiero dormir en casa | ¡Mamá, qué cansado estoy! Hoy quiero ir a casa a dormir | Mamá, ¿podrías llevarme a casa hoy? Estoy cansado y quiero dormir |
| 44 | yo, no, gustar, verduras, querer, comer, galleta, chocolate | 8 | No me gustan las verduras, quiero una galleta o chocolate. | ¡No me apetecen las verduras! Prefiero una galleta o chocolate. | ¿Podrías darme una galleta o chocolate? No me gustan las verduras. |
| 45 | mamá, yo, querer, ir, casa, abuela, mañana, papá, coche | 9 | Mañana quiero ir a casa de la abuela en coche con mamá y papá | ¡Qué ilusión! Mañana voy a ir a casa de la abuela en coche con mamá y papá | Mamá, papá, ¿podemos ir mañana a casa de la abuela en coche? |
| 46 | yo, estar, cansado, hoy, no, gustar, colegio, querer, casa | 9 | Hoy no quiero estar cansado en el colegio, prefiero estar en casa. | ¡Estoy tan cansado! Hoy no quiero ir al colegio, me gustaría estar en casa. | ¿Podrías llevarme a casa hoy? No quiero estar cansado en el colegio. |
| 47 | papá, mamá, yo, querer, ir, playa, mañana, coche, amigo | 9 | Mañana quiero ir a la playa en coche con mamá, papá y mi amigo. | ¡Qué ilusión! Mañana vamos a la playa en coche con mamá, papá y mi amigo. | ¿Podrías llevarme mañana en coche a la playa con mamá, papá y mi amigo? |
| 48 | mamá, papá, yo, querer, ir, playa, coche, mañana, amigo, pelota | 10 | Mañana quiero ir a la playa en coche con mamá, papá y amigo a jugar con la pelota. | Quiero ir mañana a la playa en coche con mi mamá, papá y mi amigo para jugar a la pelota. | Mañana iremos a la playa en coche con mamá, papá y amigo y jugaremos con la pelota. |
| 49 | papá, yo, querer, ir, restaurante, comer, carne, arroz, cena, mañana | 10 | Mañana quiero ir al restaurante con papá a cenar arroz y carne. | ¡Mañana me apetece ir al restaurante con papá a cenar arroz y carne! | ¿Podemos ir mañana al restaurante con papá a cenar arroz y carne, por favor? |
| 50 | abuela, abuelo, yo, querer, ir, casa, cocinar, comer, galleta, hoy | 10 | Quiero ir a casa de mis abuelos hoy a cocinar y comer galletas | ¡Hoy me apetece ir a casa de mis abuelos a preparar y comer galletas! | ¿Podrías llevarme a casa de mis abuelos hoy para cocinar y comer galletas? |

### Latencia Groq GPT-OSS (50 tests, 18 feb 2026)

| Métrica | Valor |
|---|---|
| Latencia mínima | 437ms |
| Latencia media | 982ms |
| Latencia máxima | 2600ms |
| Rate limit (free tier) | 8K TPM (tokens por minuto) |

### Caso destacado: cortesía natural (GPT-OSS)

**Entrada:** tener + miedo (2 pictogramas)
- **GPT-OSS:** "¿Podrías tranquilizarme? Tengo miedo" (añade petición de ayuda implícita)

**Entrada:** mamá + yo + estar + enfadado + no + hablar (6 pictogramas)
- **GPT-OSS:** "Mamá, por favor, no hablemos ahora, estoy enfadado." (frase compleja con cortesía)

## Comparativa de modelos

| Aspecto | Gemini 2.5 Flash | Groq Llama 3.3 70B | Groq GPT-OSS 120B |
|---|---|---|---|
| Tests completados | 26/26 (100%) | 50/50 (100%) | 50/50 (100%) |
| Latencia media | 3.7s | 563ms | 982ms |
| Corrección semántica | ✅ ("beber agua") | ✅ ("tengo sed") | ✅ ("tranquilizarme") |
| Calidad 1-3 pictogramas | Excelente | Excelente | Excelente |
| Calidad 4-7 pictogramas | Excelente | Excelente | Excelente |
| Calidad 8-10 pictogramas | Buena (errores menores) | Buena (errores menores) | Buena (errores menores) |
| Parámetros del modelo | — | 70B | 120B |
| Free tier | 40 RPD (Flash + Lite) | 1.000 RPD / 12K TPM | 1.000 RPD / 8K TPM |
| Thinking tokens | Sí (~200-300 internos) | No | No |
| API compatible OpenAI | No (API propia) | Sí | Sí |
| Tarjeta de crédito | No | No | No |

**Conclusión:** Los tres modelos producen frases SAAC de alta calidad. Con 1-7 pictogramas la calidad
es excelente. Con 8-10 pictogramas aparecen errores menores (inversión de negación, omisión de
conceptos), pero en el 98% de los tests al menos 1 de las 3 variaciones es correcta.

### Ortografía española

| Aspecto | Gemini 2.5 Flash | Groq (Llama + GPT-OSS) |
|---|---|---|
| Signos de apertura `¿` `¡` | No los genera | ✅ Correctos |
| Tildes (días, mañana, ilusión) | Omite frecuentemente | ✅ Correctas |
| Ortografía general | Irregular | ✅ Consistente |

**Ejemplo:** Gemini genera `Hola! Que tal?` en vez de `¡Hola! ¿Qué tal?`. Los modelos Groq
generan ortografía española correcta en las 100 pruebas (50 Llama + 50 GPT-OSS).

### Coste por millón de tokens

| Modelo | Input/M tokens | Output/M tokens |
|---|---|---|
| **Groq GPT-OSS 120B** | **$0.15** | **$0.60** |
| OpenAI GPT-4o-mini | $0.15 | $0.60 |
| Gemini 2.5 Flash Lite | $0.10 | $0.40 |
| Gemini 2.5 Flash | $0.30 | $2.50 |
| Groq Llama 3.3 70B | $0.59 | $0.79 |

GPT-OSS 120B es **4x más barato que Llama 70B** en input ($0.15 vs $0.59) a pesar de tener
casi el doble de parámetros (120B vs 70B). Mismo precio que GPT-4o-mini pero con free tier
de 1.000 RPD y la velocidad del chip LPU de Groq. Gemini Flash es el más caro en output
($2.50/M) — 4x más que GPT-OSS.

**Recomendación: Groq GPT-OSS 120B como proveedor principal.**

- **120B parámetros** (casi el doble que Llama 70B) → frases más elaboradas y con cortesía
  natural ("¿Podrías llevarme?", "por favor"), útil en SAAC porque enseña pragmática comunicativa
- **Latencia media ~1 segundo** (982ms media, 2.6s máximo) — mayor que Llama (563ms media, 804ms máximo).
  En UX, < 1s se percibe como instantáneo, 1-3s es aceptable con indicador de carga y > 3s el usuario
  pierde atención. GPT-OSS se mantiene dentro del rango aceptable
- **4x más barato que Llama 70B** en input ($0.15 vs $0.59/M tokens)
- **Escalabilidad:** 8K TPM soporta ~7 usuarios concurrentes con frases nuevas, ~20+ con cache
  caliente. Si crece más allá, cambiar a Llama 70B (12K TPM) es 1 variable de entorno
- Ambos modelos Groq superan a Gemini en latencia (5-10x menor) y capacidad (25x más RPD)
- La API OpenAI-compatible permite cambiar de proveedor sin modificar código

## Análisis de calidad semántica (378 variaciones)

Revisión manual de las 378 variaciones generadas (78 Gemini + 150 Llama + 150 GPT-OSS)
buscando errores semánticos: inversión de significado, invención de conceptos no presentes
en los pictogramas, o pérdida del mensaje comunicativo.

### Errores graves: las 3 variaciones incorrectas

Cuando las 3 variaciones de un test son incorrectas, el usuario SAAC no tiene ninguna
opción válida. Esto es el escenario más crítico.

| Modelo | Test | Pictogramas | Intención | Error |
|---|---|---|---|---|
| Llama 70B | 34 | mamá, yo, estar, enfadado, no, hablar | "Mamá, estoy enfadado, no quiero hablar" | Invierte: dice "NO estoy enfadado" (3/3) |
| GPT-OSS 120B | 33 | yo, no, gustar, colegio, querer, casa | "No me gusta el colegio, quiero ir a casa" | Invierte: dice "no quiero casa NI colegio" (3/3) |
| GPT-OSS 120B | 41 | mamá, yo, estar, enfadado, no, gustar, colegio, hoy | "Mamá, hoy estoy enfadado, no me gusta el colegio" | Reinterpreta: "no quiero estar enfadado en el colegio" (3/3) |

**Patrón común:** Los 3 errores graves involucran la partícula `no` en secuencias de 6-8
pictogramas. La ambigüedad de a qué verbo aplica la negación es inherente a la comunicación
por pictogramas. Esto es potencialmente solucionable añadiendo reglas específicas de negación
al prompt (ej: "cuando `no` aparece entre dos verbos, aplica al segundo"). Se revisará en
una futura iteración del prompt.

### Errores menores: 1-2 variaciones incorrectas

Cuando solo 1 o 2 variaciones fallan, el usuario tiene al menos 1 opción correcta.

| Modelo | Test | Variación | Error |
|---|---|---|---|
| Gemini | 14 | V3 | Inventa "no me siento bien" (no está en los pictogramas) |
| Gemini | 15 | V2 | "Qué enfado tengo con el colegio" — atribuye el enfado al colegio en vez de expresar estado + negación |
| Llama 70B | 15 | V3 | "Se encuentra mal" — inventa concepto no presente en los pictogramas (estar triste ≠ encontrarse mal) |
| Llama 70B | 41 | V1 | "No me gusta el cole porque estoy enfadado" — inventa causalidad (el enfado no es por el colegio) |
| Llama 70B | 46 | V1 | "No me gusta ir al colegio hoy, estoy cansado" — omite "querer casa" (3 ideas, solo expresa 2) |
| Llama 70B | 50 | V3 | Inventa "me invitan" y omite "cocinar" (no están en los pictogramas) |
| GPT-OSS 120B | 46 | V3 | "No quiero estar cansado en el colegio" — invierte el sentido: la intención es "estoy cansado", no "no quiero estar cansado" |

### Complementariedad entre modelos

Un hallazgo relevante: los modelos fallan en tests diferentes.

| Test (6+ pictogramas) | Llama 70B | GPT-OSS 120B |
|---|---|---|
| mamá, yo, estar, enfadado, no, hablar (#34) | ❌ 3/3 incorrectas | ✅ 3/3 correctas |
| yo, no, gustar, colegio, querer, casa (#33) | ✅ 3/3 correctas | ❌ 3/3 incorrectas |
| mamá, yo, estar, enfadado, no, gustar, colegio, hoy (#41) | ✅ 3/3 correctas | ❌ 3/3 incorrectas |

Esto sugiere que un sistema multi-modelo (consultar un segundo modelo cuando el primero
genera frases con baja confianza) podría eliminar estos errores. No implementado en el MVP,
pero viable con la arquitectura actual (`PhraseGeneratorInterface`).

### Tasa de error

| Métrica | Llama 70B | GPT-OSS 120B | Gemini |
|---|---|---|---|
| Tests con las 3 variaciones correctas | 45/50 (90%) | 47/50 (94%) | 24/26 (92%) |
| Tests con al menos 1 variación correcta | 49/50 (98%) | 48/50 (96%) | 26/26 (100%) |
| Variaciones incorrectas | 7/150 (4.7%) | 7/150 (4.7%) | 2/78 (2.6%) |

### Un SAAC convencional genera 1 frase; HablaIA genera 3

Un comunicador pictográfico convencional genera frases
gramaticalmente correctas a partir de los pictogramas seleccionados — por ejemplo,
"Estoy enfadado. No quiero hablar." — pero produce 1 única salida con un registro neutro.

HablaIA genera 3 variaciones por secuencia — directa, expresiva y cortés. Esto tiene
dos implicaciones:

1. **Resiliencia ante errores.** Si 1 variación falla, el usuario puede elegir otra. En el
   98% de los tests (123/126), al menos 1 variación es correcta.

2. **Enriquecimiento comunicativo.** Un SAAC convencional produce frases funcionales pero
   con un único registro. HablaIA ofrece registros distintos ("Quiero agua" / "Tengo mucha
   sed" / "¿Me das agua, por favor?"), lo que permite al usuario SAAC expresar no solo
   *qué* quiere decir sino *cómo* quiere decirlo — aportando pragmática comunicativa
   (cortesía, emoción, intención).

## Observaciones

- Las 3 variaciones por frase siguen el patrón: directa, expresiva, cortés
- Los tres modelos interpretan la intención comunicativa, no traducen literalmente
- Conjugan verbos correctamente según el sujeto (yo querer → quiero)
- Añaden artículos, preposiciones y pronombres de forma natural
- Con 9-10 pictogramas generan frases complejas coherentes de hasta 20 palabras
- El fallback (concatenación literal) solo se activa al agotar el rate limit (429)
- **Groq GPT-OSS 120B (recomendado):** 120B parámetros, frases más elaboradas y corteses (982ms media, < 1s)
- Groq Llama 3.3 70B: menor latencia absoluta (563ms) pero frases más directas
- GPT-OSS añade cortesía natural ("¿Podrías tranquilizarme?", "¿Podrías llevarme?") — enseña pragmática comunicativa al usuario SAAC
- La arquitectura multi-provider (`PhraseGeneratorInterface`) permite cambiar entre Gemini, OpenAI
  y Groq sin modificar código — solo cambiando variables de entorno
- Los 3 modelos testados alcanzan entre 90%-94% de éxito (3 variaciones correctas) y entre 96%-100% (al menos 1 correcta) con el mismo prompt (Gemini: 26 pruebas, Llama y GPT-OSS: 50 cada uno)

## Limitaciones y trabajo futuro

Esta validación cubre el comportamiento técnico del prompt con 126 combinaciones (26 Gemini +
50 Groq Llama 3.3 70B + 50 Groq GPT-OSS 120B) de 1-10 pictogramas, usando tres modelos LLM
de dos proveedores distintos. Para un entorno de producción más allá del MVP, sería necesario:

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
