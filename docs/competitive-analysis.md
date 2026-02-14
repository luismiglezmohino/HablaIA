# Análisis Comparativo — HablaIA vs Comunicadores SAAC Existentes

**Última actualización:** 14 de febrero de 2026

---

## Tesis

Todos los comunicadores SAAC existentes delegan la gramática al usuario o la resuelven parcialmente con reglas. **Ninguno usa un LLM para transformar pictogramas en frases naturales completas.** HablaIA es el primero en hacerlo.

---

## Cómo se Construyen Frases Hoy

Dado el input `[yo] [querer] [comer]` un lunes a las 09:30:

| Comunicador | Output | ¿Quién gestiona la gramática? |
|-------------|--------|-------------------------------|
| **LetMeTalk** (gratuito) | "yo querer comer" | Nadie — concatena etiquetas tal cual |
| **Proloquo2Go** (~250 EUR, iPad) | "Yo quiero comer" | El usuario elige "quiero" de un popup de conjugaciones |
| **Grid 3** (~600 EUR + PC) | "Yo quiero comer" | Reglas automáticas conjugan el verbo, pero no añaden artículos ni preposiciones |
| **TD Snap** (~15.000 EUR, prescripción médica) | "Yo quiero comer" | Navegación estructurada; la IA solo funciona en modo texto, no en pictogramas |
| **HablaIA** (web, cualquier dispositivo) | 1. "Buenos días, tengo hambre" — 2. "Me apetece desayunar algo" — 3. "Necesito comer, ¿qué hay?" | **La IA — el usuario solo selecciona pictogramas** |

La diferencia fundamental: en los 4 primeros, el resultado es siempre la misma frase literal. En HablaIA, el LLM genera **3 variaciones naturales adaptadas al contexto** (hora, día de la semana).

---

## Comparativa Técnica

| Capacidad | LetMeTalk | Proloquo2Go | Grid 3 | TD Snap | **HablaIA** |
|-----------|:---------:|:-----------:|:------:|:-------:|:-----------:|
| Inserción automática de artículos/preposiciones | No | Manual | No | No | **Sí** |
| Reestructuración de frase | No | No | No | No | **Sí** |
| Variaciones naturales | No | No | No | No | **3 por petición** |
| Adaptación al contexto temporal | No | No | No | No | **Sí** |
| Multilingüe | Por etiquetas | 1 idioma/ingeniería | 1 idioma/ingeniería | Varios (texto) | Solo español (Fase 1). Arquitectura preparada, pero requiere traducir UI y validar pictogramas por país |
| Carga gramatical para el usuario | Total | Alta | Media | Media | **Mínima** |

---

## Coste y Acceso

| Solución | Coste | Acceso | Hardware |
|----------|-------|--------|----------|
| **Tobii Dynavox** | ~15.000 EUR | Prescripción médica, 6-12 meses | Propietario |
| **Irisbond** | 5.000-10.000 EUR | Prescripción médica, 6-12 meses | Eye-tracker propietario |
| **Proloquo2Go** | ~250 EUR | App Store (inmediato) | iPad |
| **Grid 3** | ~600 EUR + hardware | Distribución limitada | PC/tablet Windows |
| **LetMeTalk** | Gratuito | Google Play (inmediato) | Android |
| **HablaIA** | Proyecto académico | Web (inmediato) | Cualquier tablet/móvil con navegador |

Las soluciones de gama alta (Tobii, Irisbond) requieren **prescripción médica** y plazos de 6-12 meses. HablaIA es accesible desde cualquier navegador sin hardware especializado.

---

## Investigación Relacionada

### Precedente más cercano: Compansion (UAB Barcelona, 2017)

Transforma secuencias de pictogramas en frases naturales en español y catalán usando **reglas sintáctico-semánticas** (no IA). Resultados: 99.66% corrección gramatical, +41.59% tasa de comunicación, validado con 4 usuarios con parálisis cerebral en 40 sesiones.

**Diferencia con HablaIA:** Compansion usa reglas rígidas por idioma (requiere ingeniería manual para cada lengua nueva). HablaIA usa un LLM que genera variaciones naturales y se adapta al contexto temporal.

### IA aplicada a SAAC (pero no a pictogramas)

| Proyecto | Qué hace | Por qué no resuelve lo mismo |
|----------|----------|------------------------------|
| **SpeakFaster** (Google, 2024) | LLM expande abreviaturas escritas por eye-tracking. +29-60% velocidad para usuarios con ELA | El usuario necesita saber escribir. Los usuarios de pictogramas no escriben |
| **PrAACT** (2023) | Predice el siguiente pictograma en una secuencia | Ayuda a elegir pictogramas más rápido, pero no genera frases naturales |

### Evidencia de demanda

El estudio **"The less I type, the better"** (Google, CHI 2023) probó sugerencias de texto con IA en 12 usuarios SAAC. Conclusión: los usuarios valoran el ahorro de tiempo y esfuerzo, pero piden que las sugerencias reflejen su estilo personal. Esto valida que hay demanda real de IA en comunicación aumentativa.

**Dato clave:** La tasa de comunicación en SAAC es de 8-15 palabras/minuto frente a 130-250 del habla típica. Cada paso cognitivo adicional (elegir conjugaciones, gestionar gramática) amplía esa brecha.

---

## Referencias

- [AssistiveWare — Grammar Support](https://www.assistiveware.com/blog/grammar-support)
- [Proloquo2Go — Grammar Popups](https://www.assistiveware.com/support/proloquo2go/vocabulary-grammar/grammar-support-popups)
- [Grid 3 — Grammar Features](https://hub.thinksmartbox.com/knowledgebase/how-do-i-use-grids-grammar-features/)
- [TD Snap — Tobii Dynavox](https://us.tobiidynavox.com/pages/td-snap)
- [TD Talk — Phrase Prediction](https://www.tobiidynavox.com/blogs/product-discovery/td-talk-talk-faster-with-word-and-phrase-prediction)
- [SpeakFaster — Nature Communications 2024](https://www.nature.com/articles/s41467-024-53873-3)
- [PrAACT — Expert Systems with Applications 2023](https://www.sciencedirect.com/science/article/abs/pii/S0957417423029196)
- [Compansion System — PubMed](https://pubmed.ncbi.nlm.nih.gov/29045194/)
- [Google CHI 2023 — "The less I type, the better"](https://research.google/pubs/the-less-i-type-the-better-how-ai-language-models-can-enhance-or-impede-communication-for-aac-users/)
