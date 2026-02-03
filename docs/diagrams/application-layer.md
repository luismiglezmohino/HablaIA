# Application Layer - Diagramas

> Diagramas del Application Layer de HablaIA

## Diagrama de Use Cases

```mermaid
classDiagram
    direction TB

    %% Use Cases - Category
    class GetAllCategories {
        <<use case>>
        -CategoryRepository repository
        +__invoke() array~CategoryDTO~
    }

    %% Use Cases - Pictogram
    class GetAllPictograms {
        <<use case>>
        -PictogramRepository repository
        +__invoke() array~PictogramDTO~
    }

    class GetPictogramsByCategory {
        <<use case>>
        -PictogramRepository pictogramRepository
        -CategoryRepository categoryRepository
        +__invoke(string categoryId) array~PictogramDTO~
    }

    %% Use Cases - Phrase (Core MVP)
    class GenerateHumanizedPhrase {
        <<use case>>
        -PictogramRepository pictogramRepository
        -PhraseRepository phraseRepository
        -PhraseGeneratorInterface phraseGenerator
        -UuidGeneratorInterface uuidGenerator
        +__invoke(array pictogramIds) PhraseResponseDTO
        -validateAndGetPictograms(array) array~Pictogram~
        -buildFallbackPhrase(array) string
    }

    %% Repositories (from Domain)
    class CategoryRepository {
        <<interface>>
    }
    class PictogramRepository {
        <<interface>>
    }
    class PhraseRepository {
        <<interface>>
    }
    class PhraseGeneratorInterface {
        <<interface>>
    }

    %% Dependencies
    GetAllCategories --> CategoryRepository
    GetAllPictograms --> PictogramRepository
    GetPictogramsByCategory --> PictogramRepository
    GetPictogramsByCategory --> CategoryRepository
    GenerateHumanizedPhrase --> PictogramRepository
    GenerateHumanizedPhrase --> PhraseRepository
    GenerateHumanizedPhrase --> PhraseGeneratorInterface
```

## Diagrama de DTOs

```mermaid
classDiagram
    direction LR

    class CategoryDTO {
        <<readonly>>
        +string id
        +string name
        +string? icon
        +fromEntity(Category)$ CategoryDTO
    }

    class PictogramDTO {
        <<readonly>>
        +string id
        +int arasaacId
        +string categoryId
        +string label
        +string imagePath
        +fromEntity(Pictogram)$ PictogramDTO
    }

    class PhraseResponseDTO {
        <<readonly>>
        +array~string~ variations
        +string source
        +string sequenceHash
        +array~string~ pictogramIds
        +SOURCE_CACHE$ string
        +SOURCE_GENERATED$ string
        +SOURCE_FALLBACK$ string
    }

    %% Relationships with Domain Entities
    CategoryDTO ..> Category : transforms from
    PictogramDTO ..> Pictogram : transforms from
```

## Diagrama de Excepciones

```mermaid
classDiagram
    direction TB

    class ApplicationException {
        <<abstract>>
    }

    class CategoryNotFoundException {
        +withId(string)$ self
    }

    class PictogramNotFoundException {
        -array~string~ missingIds
        +withIds(array)$ self
        +getMissingIds() array~string~
    }

    ApplicationException <|-- CategoryNotFoundException
    ApplicationException <|-- PictogramNotFoundException
```

## Flujo de GenerateHumanizedPhrase

```mermaid
flowchart TB
    Start([Usuario selecciona pictogramas]) --> Validate

    subgraph Validate["1. Validación"]
        V1[Recibir array de IDs]
        V2{UUID válido?}
        V3{Pictograma existe?}
        V1 --> V2
        V2 -->|No| ErrUUID[InvalidArgumentException]
        V2 -->|Sí| V3
        V3 -->|No| ErrNotFound[PictogramNotFoundException]
        V3 -->|Sí| ValidOK[Pictogramas validados]
    end

    Validate --> BuildSeq

    subgraph BuildSeq["2. Construir Secuencia"]
        B1[Crear PictogramSequence]
        B2{1-10 pictogramas?}
        B1 --> B2
        B2 -->|No| ErrSeq[InvalidPictogramSequenceException]
        B2 -->|Sí| Hash[Calcular hash SHA256]
    end

    BuildSeq --> Cache

    subgraph Cache["3. Buscar en Caché"]
        C1[findBySequenceHash]
        C2{Cache hit?}
        C1 --> C2
    end

    C2 -->|Sí| ReturnCache[Retornar source=cache]
    C2 -->|No| Generate

    subgraph Generate["4. Generar con LLM"]
        G1[Llamar PhraseGeneratorInterface]
        G2{LLM OK?}
        G1 --> G2
        G2 -->|Sí| GenOK[variations + source=generated]
        G2 -->|No| Fallback
    end

    subgraph Fallback["5. Fallback"]
        F1[Concatenar labels]
        F2[source=fallback]
        F1 --> F2
    end

    GenOK --> Save
    Fallback --> Save

    subgraph Save["6. Guardar en Caché"]
        S1[Crear Phrase entity]
        S2[phraseRepository.save]
        S1 --> S2
    end

    Save --> Return([Retornar PhraseResponseDTO])
    ReturnCache --> Return

    style Cache fill:#e8f5e9
    style Generate fill:#fff3e0
    style Fallback fill:#ffebee
```

## Diagrama de Secuencia - GenerateHumanizedPhrase

```mermaid
sequenceDiagram
    participant C as Controller
    participant UC as GenerateHumanizedPhrase
    participant PR as PictogramRepository
    participant PhR as PhraseRepository
    participant LLM as PhraseGenerator

    C->>UC: __invoke(["id1", "id2", "id3"])

    loop Para cada ID
        UC->>PR: findById(PictogramId)
        PR-->>UC: Pictogram | null
    end

    Note over UC: Construir PictogramSequence<br/>Calcular hash SHA256

    UC->>PhR: findBySequenceHash(hash)

    alt Cache hit
        PhR-->>UC: Phrase existente
        UC-->>C: PhraseResponseDTO(source=cache)
    else Cache miss
        PhR-->>UC: null

        UC->>LLM: generate(sequence)

        alt LLM success
            LLM-->>UC: ["Frase 1", "Frase 2", "Frase 3"]
            Note over UC: source = generated
        else LLM failure
            LLM-->>UC: Exception
            Note over UC: buildFallbackPhrase()<br/>source = fallback
        end

        UC->>PhR: save(newPhrase)
        UC-->>C: PhraseResponseDTO(source=generated|fallback)
    end
```

## Arquitectura de Capas

```mermaid
graph TB
    subgraph Infrastructure["Infrastructure Layer (futuro)"]
        Ctrl[Controllers]
        DoctrineRepo[Doctrine Repositories]
        OpenAI[OpenAI Adapter]
        RamseyUuid[Ramsey UUID]
    end

    subgraph Application["Application Layer"]
        subgraph UseCases["Use Cases"]
            UC1[GetAllCategories]
            UC2[GetAllPictograms]
            UC3[GetPictogramsByCategory]
            UC4[GenerateHumanizedPhrase]
        end

        subgraph DTOs["DTOs"]
            DTO1[CategoryDTO]
            DTO2[PictogramDTO]
            DTO3[PhraseResponseDTO]
        end

        subgraph AppExc["Exceptions"]
            AE1[ApplicationException]
            AE2[CategoryNotFoundException]
            AE3[PictogramNotFoundException]
        end
    end

    subgraph Domain["Domain Layer"]
        Entities[Entities]
        ValueObjects[Value Objects]
        Repositories[Repository Interfaces]
        Services[Service Interfaces]
        DomainExc[Domain Exceptions]
    end

    Ctrl --> UseCases
    UseCases --> DTOs
    UseCases --> Repositories
    UseCases --> Services
    DoctrineRepo -.-> Repositories
    OpenAI -.-> Services
    RamseyUuid -.-> Services

    style Application fill:#e3f2fd
    style Domain fill:#e8f5e9
    style Infrastructure fill:#fff3e0
```

## Resumen de Componentes

| Módulo | Use Cases | DTOs | Excepciones |
|--------|-----------|------|-------------|
| **Category** | `GetAllCategories` | `CategoryDTO` | `CategoryNotFoundException` |
| **Pictogram** | `GetAllPictograms`, `GetPictogramsByCategory` | `PictogramDTO` | `PictogramNotFoundException` |
| **Phrase** | `GenerateHumanizedPhrase` | `PhraseResponseDTO` | - |

## Principios SOLID Aplicados

| Principio | Aplicación |
|-----------|------------|
| **S** - Single Responsibility | Cada Use Case hace una sola cosa |
| **O** - Open/Closed | Nuevos generadores sin modificar Use Cases |
| **L** - Liskov Substitution | FakePhraseGenerator intercambiable con OpenAI |
| **I** - Interface Segregation | Interfaces pequeñas y específicas |
| **D** - Dependency Inversion | Use Cases dependen de interfaces, no implementaciones |

## Estrategia de Caché

```mermaid
graph LR
    subgraph Input
        P1[🍎 comer]
        P2[👦 yo]
        P3[⏰ ahora]
    end

    subgraph Hash
        H1["SHA256('id1|id2|id3')"]
        H2["= a1b2c3d4..."]
    end

    subgraph Cache
        DB[(PostgreSQL)]
        Hit{Hit?}
    end

    subgraph Output
        O1["✅ Cache: 0ms"]
        O2["🤖 LLM: ~1.5s"]
        O3["⚠️ Fallback: 0ms"]
    end

    P1 & P2 & P3 --> H1
    H1 --> H2
    H2 --> DB
    DB --> Hit
    Hit -->|Sí| O1
    Hit -->|No| LLM
    LLM -->|OK| O2
    LLM -->|Error| O3

    style O1 fill:#c8e6c9
    style O2 fill:#fff9c4
    style O3 fill:#ffcdd2
```

> **Nota:** El hash SHA256 garantiza que secuencias idénticas siempre produzcan el mismo hash, permitiendo cacheo eficiente sin duplicados.

## Tests Coverage

| Use Case | Tests | Assertions |
|----------|-------|------------|
| `GetAllCategories` | 2 | 6 |
| `GetAllPictograms` | 7 | 35 |
| `GetPictogramsByCategory` | 8 | 40 |
| `GenerateHumanizedPhrase` | 14 | 27 |
| **Total** | **31** | **108+** |

> **Cobertura objetivo:** 80% Application Layer (cumplido con 31 tests)
