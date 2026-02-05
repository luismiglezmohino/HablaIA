# API Flow - Diagramas

> Diagramas de flujo de las APIs principales de HablaIA

## Flujo de Generacion de Frase (POST /api/phrases/generate)

Este es el **Core MVP feature** de HablaIA.

### Diagrama de Secuencia Completo

```mermaid
sequenceDiagram
    participant Client
    participant PC as PhraseController
    participant RL as RateLimiter
    participant UC as GenerateHumanizedPhrase
    participant PicRepo as PictogramRepository
    participant PhrRepo as PhraseRepository
    participant Gen as PhraseGenerator
    participant OpenAI as OpenAI API
    participant DB as PostgreSQL

    Client->>PC: POST /api/phrases/generate
    Note right of Client: {"pictogramIds": ["uuid1", "uuid2"]}

    %% Rate Limiting
    PC->>RL: consume(clientIP)
    alt Rate limit exceeded
        RL-->>PC: rejected
        PC-->>Client: 429 Too Many Requests
        Note right of Client: {"error": "Too many requests", "retryAfter": timestamp}
    else Rate limit OK
        RL-->>PC: accepted

        %% JSON Validation
        PC->>PC: Parse JSON body

        alt Invalid JSON
            PC-->>Client: 400 Bad Request
            Note right of Client: {"error": "Invalid JSON body"}
        else JSON OK

            alt Missing pictogramIds
                PC-->>Client: 400 Bad Request
                Note right of Client: {"error": "Missing required field: pictogramIds"}
            else pictogramIds present

                %% Execute Use Case
                PC->>UC: __invoke(pictogramIds)

                %% Validate Pictograms
                loop For each UUID
                    UC->>PicRepo: findById(PictogramId)
                    PicRepo->>DB: SELECT
                    DB-->>PicRepo: result
                    PicRepo-->>UC: Pictogram | null
                end

                alt Pictogram not found
                    UC-->>PC: PictogramNotFoundException
                    PC-->>Client: 404 Not Found
                    Note right of Client: {"error": "Pictograms not found: uuid"}
                else All pictograms exist

                    %% Build sequence and hash
                    UC->>UC: Create PictogramSequence
                    UC->>UC: Calculate SHA256 hash

                    alt Invalid sequence (empty or >10)
                        UC-->>PC: InvalidPictogramSequenceException
                        PC-->>Client: 400 Bad Request
                    else Sequence valid

                        %% Check cache
                        UC->>PhrRepo: findBySequenceHash(hash)
                        PhrRepo->>DB: SELECT WHERE sequence_hash = ?
                        DB-->>PhrRepo: result

                        alt Cache hit
                            PhrRepo-->>UC: Phrase
                            UC-->>PC: PhraseResponseDTO(source=cache)
                            PC-->>Client: 200 OK
                            Note right of Client: source: "cache"
                        else Cache miss
                            PhrRepo-->>UC: null

                            %% Generate with LLM
                            UC->>Gen: generate(sequence)
                            Gen->>OpenAI: POST /v1/chat/completions
                            Note right of Gen: model: gpt-4o-mini

                            alt OpenAI success
                                OpenAI-->>Gen: JSON response
                                Gen-->>UC: ["Frase 1", "Frase 2", "Frase 3"]
                                Note over UC: source = generated
                            else OpenAI failure
                                OpenAI-->>Gen: Error
                                UC->>UC: buildFallbackPhrase()
                                Note over UC: Concatenate labels<br/>source = fallback
                            end

                            %% Save to cache
                            UC->>PhrRepo: save(newPhrase)
                            PhrRepo->>DB: INSERT
                            DB-->>PhrRepo: OK

                            UC-->>PC: PhraseResponseDTO
                            PC-->>Client: 200 OK
                        end
                    end
                end
            end
        end
    end
```

### Diagrama de Flujo Simplificado

```mermaid
flowchart TB
    Start([POST /api/phrases/generate]) --> RateLimit{Rate Limit<br/>OK?}

    RateLimit -->|No| Error429[429 Too Many Requests]
    RateLimit -->|Yes| ParseJSON[Parse JSON Body]

    ParseJSON --> ValidJSON{Valid JSON?}
    ValidJSON -->|No| Error400A[400 Invalid JSON]
    ValidJSON -->|Yes| HasField{Has pictogramIds?}

    HasField -->|No| Error400B[400 Missing field]
    HasField -->|Yes| ValidateUUIDs[Validate UUIDs]

    ValidateUUIDs --> UUIDValid{All UUIDs<br/>valid?}
    UUIDValid -->|No| Error400C[400 Invalid UUID]
    UUIDValid -->|Yes| FindPictograms[Find Pictograms in DB]

    FindPictograms --> AllExist{All pictograms<br/>exist?}
    AllExist -->|No| Error404[404 Not Found]
    AllExist -->|Yes| BuildSequence[Build PictogramSequence]

    BuildSequence --> ValidSequence{1-10 pictograms?}
    ValidSequence -->|No| Error400D[400 Invalid sequence]
    ValidSequence -->|Yes| CalcHash[Calculate SHA256 Hash]

    CalcHash --> CheckCache[Check Cache by Hash]
    CheckCache --> CacheHit{Cache Hit?}

    CacheHit -->|Yes| ReturnCache[Return source=cache]
    CacheHit -->|No| CallLLM[Call OpenAI API]

    CallLLM --> LLMSuccess{LLM Success?}
    LLMSuccess -->|Yes| Generated[source=generated]
    LLMSuccess -->|No| Fallback[Concatenate labels<br/>source=fallback]

    Generated --> SaveCache[Save to Cache]
    Fallback --> SaveCache

    SaveCache --> Success([200 OK])
    ReturnCache --> Success

    style Success fill:#c8e6c9
    style Error429 fill:#ffcdd2
    style Error400A fill:#ffcdd2
    style Error400B fill:#ffcdd2
    style Error400C fill:#ffcdd2
    style Error400D fill:#ffcdd2
    style Error404 fill:#ffcdd2
```

### Posibles Respuestas

| Codigo | Escenario | Ejemplo Response |
|--------|-----------|------------------|
| **200** | Exito (cache/generated/fallback) | `{"variations": [...], "source": "generated", ...}` |
| **400** | JSON invalido | `{"error": "Invalid JSON body"}` |
| **400** | Campo faltante | `{"error": "Missing required field: pictogramIds"}` |
| **400** | UUID invalido | `{"error": "Invalid UUID format"}` |
| **400** | Secuencia invalida | `{"error": "Pictogram sequence cannot be empty"}` |
| **404** | Pictograma no existe | `{"error": "Pictograms not found: uuid"}` |
| **429** | Rate limit excedido | `{"error": "Too many requests", "retryAfter": 1234567890}` |

---

## Flujo de Busqueda de Pictograma (GET /api/pictograms/search?q=)

### Diagrama de Secuencia Completo

```mermaid
sequenceDiagram
    participant Client
    participant PC as PictogramController
    participant UC as SearchPictogram
    participant Repo as PictogramRepository
    participant AAC as ArasaacApiClient
    participant ID as ImageDownloader
    participant ARASAAC as ARASAAC API
    participant CDN as ARASAAC CDN
    participant DB as PostgreSQL
    participant FS as File System

    Client->>PC: GET /api/pictograms/search?q=comer

    %% Validate query
    PC->>PC: Get query param 'q'

    alt Missing query
        PC-->>Client: 400 Bad Request
        Note right of Client: {"error": "Query parameter q is required"}
    else Query present

        PC->>UC: __invoke(query)

        %% Sanitize and validate
        UC->>UC: sanitizeQuery(query)
        UC->>UC: Check min length (2 chars)

        alt Query too short
            UC-->>PC: InvalidArgumentException
            PC-->>Client: 400 Bad Request
            Note right of Client: {"error": "Search query must be at least 2 characters"}
        else Query valid

            %% Step 1: Search locally
            UC->>Repo: findByLabelLike(query, 10)
            Repo->>DB: SELECT WHERE label LIKE '%query%' LIMIT 10
            DB-->>Repo: results

            alt Local results found
                Repo-->>UC: array<Pictogram>
                UC-->>PC: array<PictogramDTO>
                PC-->>Client: 200 OK (local results)
            else No local results
                Repo-->>UC: []

                %% Step 2: Search in ARASAAC
                UC->>AAC: searchByKeyword(query, 'es')
                AAC->>ARASAAC: GET /pictograms/es/search/comer
                ARASAAC-->>AAC: JSON results
                AAC-->>UC: array<Pictogram>

                alt No ARASAAC results
                    UC-->>PC: []
                    PC-->>Client: 200 OK (empty array)
                else ARASAAC results found

                    %% Step 3: Process results (max 10)
                    loop For each ARASAAC pictogram (max 10)
                        UC->>Repo: findByArasaacId(id)
                        Repo->>DB: SELECT WHERE arasaac_id = ?
                        DB-->>Repo: result

                        alt Already exists locally
                            Repo-->>UC: Pictogram
                            Note over UC: Use existing
                        else Not in local DB
                            Repo-->>UC: null

                            %% Download image
                            UC->>ID: download(cdnUrl, localPath)
                            ID->>CDN: GET /pictograms/{id}/{id}_500.png
                            CDN-->>ID: PNG binary
                            ID->>FS: Write to public/pictograms/
                            FS-->>ID: OK
                            ID-->>UC: true

                            alt Download success
                                %% Save to DB
                                UC->>Repo: save(newPictogram)
                                Repo->>DB: INSERT
                                DB-->>Repo: OK
                            else Download failed
                                Note over UC: Skip this pictogram
                            end
                        end
                    end

                    UC-->>PC: array<PictogramDTO>
                    PC-->>Client: 200 OK
                end
            end
        end
    end
```

### Diagrama de Flujo Simplificado

```mermaid
flowchart TB
    Start([GET /api/pictograms/search?q=]) --> HasQuery{Has 'q'<br/>param?}

    HasQuery -->|No| Error400A[400 Missing query]
    HasQuery -->|Yes| Sanitize[Sanitize Query]

    Sanitize --> MinLength{Length >= 2?}
    MinLength -->|No| Error400B[400 Query too short]
    MinLength -->|Yes| SearchLocal[Search in Local DB<br/>LIKE query]

    SearchLocal --> LocalFound{Local results<br/>found?}

    LocalFound -->|Yes| ReturnLocal([200 OK - Local Results])

    LocalFound -->|No| SearchARASAAC[Search ARASAAC API]

    SearchARASAAC --> ARASAACFound{ARASAAC results<br/>found?}

    ARASAACFound -->|No| ReturnEmpty([200 OK - Empty Array])

    ARASAACFound -->|Yes| ProcessResults[Process max 10 results]

    subgraph ProcessLoop["For each ARASAAC pictogram"]
        CheckExists[Check if exists locally]
        CheckExists --> Exists{Exists?}
        Exists -->|Yes| UseExisting[Use existing]
        Exists -->|No| Download[Download image]
        Download --> SaveDB[Save to DB]
    end

    ProcessResults --> ProcessLoop
    ProcessLoop --> ReturnResults([200 OK - Mixed Results])

    style ReturnLocal fill:#c8e6c9
    style ReturnEmpty fill:#fff9c4
    style ReturnResults fill:#c8e6c9
    style Error400A fill:#ffcdd2
    style Error400B fill:#ffcdd2
```

### Posibles Respuestas

| Codigo | Escenario | Ejemplo Response |
|--------|-----------|------------------|
| **200** | Resultados encontrados (local o ARASAAC) | `[{"id": "...", "label": "comer", ...}]` |
| **200** | Sin resultados | `[]` |
| **400** | Query faltante | `{"error": "Query parameter q is required"}` |
| **400** | Query muy corta | `{"error": "Search query must be at least 2 characters"}` |

---

## Flujo de Health Check (GET /api/health)

### Diagrama de Secuencia

```mermaid
sequenceDiagram
    participant Client
    participant HC as HealthController
    participant DHC as DatabaseHealthChecker
    participant DB as PostgreSQL

    Client->>HC: GET /api/health

    HC->>DHC: check()
    DHC->>DB: SELECT 1
    Note right of DHC: Measure latency

    alt Database OK
        DB-->>DHC: result
        DHC-->>HC: {status: "up", latency_ms: 2.45}
        HC-->>Client: 200 OK
        Note right of Client: status: "healthy"
    else Database Error
        DB-->>DHC: connection error
        DHC-->>HC: {status: "down", error: "..."}
        HC-->>Client: 503 Service Unavailable
        Note right of Client: status: "unhealthy"
    end
```

### Diagrama de Flujo

```mermaid
flowchart TB
    subgraph HealthCheck["/api/health"]
        Start1([GET /api/health]) --> CheckDB1[Check Database]
        CheckDB1 --> DBOK1{DB OK?}
        DBOK1 -->|Yes| Healthy([200 healthy])
        DBOK1 -->|No| Unhealthy([503 unhealthy])
    end

    subgraph LivenessProbe["/api/health/live"]
        Start2([GET /api/health/live]) --> Alive([200 alive])
        Note1[No dependency checks<br/>Just confirms PHP is running]
    end

    subgraph ReadinessProbe["/api/health/ready"]
        Start3([GET /api/health/ready]) --> CheckDB3[Check Database]
        CheckDB3 --> DBOK3{DB OK?}
        DBOK3 -->|Yes| Ready([200 ready])
        DBOK3 -->|No| NotReady([503 not_ready])
    end

    style Healthy fill:#c8e6c9
    style Alive fill:#c8e6c9
    style Ready fill:#c8e6c9
    style Unhealthy fill:#ffcdd2
    style NotReady fill:#ffcdd2
```

### Kubernetes Configuration

```yaml
livenessProbe:
  httpGet:
    path: /api/health/live
    port: 8080
  initialDelaySeconds: 5
  periodSeconds: 10
  failureThreshold: 3

readinessProbe:
  httpGet:
    path: /api/health/ready
    port: 8080
  initialDelaySeconds: 10
  periodSeconds: 5
  failureThreshold: 3

startupProbe:
  httpGet:
    path: /api/health/ready
    port: 8080
  initialDelaySeconds: 0
  periodSeconds: 5
  failureThreshold: 30
```

---

## Resumen de APIs

| Endpoint | Metodo | Proposito | Latencia Esperada |
|----------|--------|-----------|-------------------|
| `/api/health` | GET | Monitoring general | <10ms |
| `/api/health/live` | GET | Kubernetes liveness | <1ms |
| `/api/health/ready` | GET | Kubernetes readiness | <10ms |
| `/api/categories` | GET | Listar categorias | <50ms |
| `/api/categories/{id}` | GET | Detalle categoria | <20ms |
| `/api/pictograms` | GET | Listar pictogramas | <100ms |
| `/api/pictograms/search` | GET | Buscar pictogramas | <2s (con ARASAAC) |
| `/api/pictograms/{id}` | GET | Detalle pictograma | <20ms |
| `/api/phrases/generate` | POST | Generar frase | <200ms (cache) / <3s (LLM) |

> **Nota Performance:** El objetivo es <200ms en p95 para operaciones criticas de UX.
> La generacion de frases con LLM puede tomar hasta 3s la primera vez, pero respuestas
> cacheadas se sirven en <50ms.
