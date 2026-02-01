# Domain Layer - Diagramas

> Diagramas del Domain Layer de HablaIA

## Diagrama de Clases

```mermaid
classDiagram
    direction TB

    %% Category Module
    class Category {
        -CategoryId id
        -string name
        -string? icon
        +id() CategoryId
        +name() string
        +icon() string?
    }

    class CategoryId {
        -string value
        +generate()$ CategoryId
        +fromString(string)$ CategoryId
        +value() string
        +equals(CategoryId) bool
    }

    %% Pictogram Module
    class Pictogram {
        -PictogramId id
        -ArasaacId arasaacId
        -CategoryId categoryId
        -string label
        -string imagePath
        +id() PictogramId
        +arasaacId() ArasaacId
        +categoryId() CategoryId
        +label() string
        +imagePath() string
    }

    class PictogramId {
        -string value
        +generate()$ PictogramId
        +fromString(string)$ PictogramId
        +value() string
        +equals(PictogramId) bool
    }

    class ArasaacId {
        -int value
        +value() int
        +equals(ArasaacId) bool
    }

    %% Phrase Module
    class Phrase {
        -PhraseId id
        -PictogramSequence pictogramSequence
        -array~string~ variations
        -DateTimeImmutable createdAt
        +id() PhraseId
        +pictogramSequence() PictogramSequence
        +variations() array
        +createdAt() DateTimeImmutable
        +sequenceHash() string
    }

    class PhraseId {
        -string value
        +generate()$ PhraseId
        +fromString(string)$ PhraseId
        +value() string
        +equals(PhraseId) bool
    }

    class PictogramSequence {
        -array~PictogramId~ pictogramIds
        +pictogramIds() array
        +count() int
        +hash() string
    }

    %% Relationships
    Category --> CategoryId
    Pictogram --> PictogramId
    Pictogram --> ArasaacId
    Pictogram --> CategoryId
    Phrase --> PhraseId
    Phrase --> PictogramSequence
    PictogramSequence --> PictogramId
```

## Diagrama de Repositorios y Servicios

```mermaid
classDiagram
    direction LR

    class CategoryRepository {
        <<interface>>
        +findById(CategoryId) Category?
        +findByName(string) Category?
        +findAll() array~Category~
        +save(Category) void
    }

    class PictogramRepository {
        <<interface>>
        +findById(PictogramId) Pictogram?
        +findByCategoryId(CategoryId) array~Pictogram~
        +findAll() array~Pictogram~
        +save(Pictogram) void
    }

    class PhraseRepository {
        <<interface>>
        +findById(PhraseId) Phrase?
        +findBySequenceHash(string) Phrase?
        +save(Phrase) void
    }

    class PhraseGeneratorInterface {
        <<interface>>
        +generate(PictogramSequence) array~string~
    }

    CategoryRepository ..> Category
    PictogramRepository ..> Pictogram
    PictogramRepository ..> CategoryId
    PhraseRepository ..> Phrase
    PhraseGeneratorInterface ..> PictogramSequence
```

> **Nota:** `PhraseGeneratorInterface` permite cambiar el proveedor de IA (OpenAI, Claude, Gemini, etc.) sin modificar el dominio.

## Diagrama de Módulos

```mermaid
graph TB
    subgraph Domain["Domain Layer"]
        subgraph PictogramModule["Pictogram"]
            P_Entity[Pictogram]
            P_VO1[PictogramId]
            P_VO2[ArasaacId]
            P_Repo[PictogramRepository]
        end

        subgraph CategoryModule["Category"]
            C_Entity[Category]
            C_VO[CategoryId]
            C_Repo[CategoryRepository]
        end

        subgraph PhraseModule["Phrase"]
            Ph_Entity[Phrase]
            Ph_VO1[PhraseId]
            Ph_VO2[PictogramSequence]
            Ph_Repo[PhraseRepository]
            Ph_Service[PhraseGeneratorInterface]
        end
    end

    P_Entity --> C_VO
    Ph_VO2 --> P_VO1
    Ph_Service --> Ph_VO2

    style Domain fill:#e1f5fe
    style PictogramModule fill:#fff3e0
    style CategoryModule fill:#e8f5e9
    style PhraseModule fill:#fce4ec
```

## Flujo de Caché de Frases

```mermaid
sequenceDiagram
    participant U as Usuario
    participant App as Application
    participant PR as PhraseRepository
    participant OpenAI as OpenAI API

    U->>App: Selecciona pictogramas [🍎,👦,⏰]
    App->>App: Genera PictogramSequence
    App->>App: Calcula hash SHA256
    App->>PR: findBySequenceHash(hash)

    alt Frase en caché
        PR-->>App: Phrase existente
        App-->>U: Variaciones cacheadas
    else No está en caché
        App->>OpenAI: Genera 3 variaciones
        OpenAI-->>App: ["Quiero comer", "Me gustaría...", "Tengo ganas..."]
        App->>PR: save(nuevaPhrase)
        App-->>U: Variaciones nuevas
    end
```

## Resumen

| Módulo | Entidad | Value Objects | Repositorio | Servicio |
|--------|---------|---------------|-------------|----------|
| **Pictogram** | `Pictogram` | `PictogramId`, `ArasaacId` | `PictogramRepository` | - |
| **Category** | `Category` | `CategoryId` | `CategoryRepository` | - |
| **Phrase** | `Phrase` | `PhraseId`, `PictogramSequence` | `PhraseRepository` | `PhraseGeneratorInterface` |

## Validaciones de Seguridad

| Entidad | Validación | Motivo |
|---------|------------|--------|
| `Pictogram` | `imagePath` no contiene `..` | Prevenir path traversal |
| `Pictogram` | `label` máx 100 chars | Prevenir DoS |
| `Category` | `name` máx 50 chars | Prevenir DoS |
| `Phrase` | `variations` máx 3, 500 chars c/u | Límite OpenAI + DoS |
| `PictogramSequence` | Máx 10 pictogramas | Límite razonable |
