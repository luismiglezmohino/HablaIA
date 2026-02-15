# Domain Layer - Diagramas

> Diagramas del Domain Layer de HablaIA

## Diagrama de Clases

```mermaid
classDiagram
    direction TB

    %% Shared Module
    class Uuid {
        <<utility>>
        +isValid(string)$ bool
        +normalize(string)$ string
    }

    class UuidGeneratorInterface {
        <<interface>>
        +generate() string
    }

    %% Category Module
    class Category {
        -CategoryId id
        -string name
        -string? icon
        -string colorHex
        -int displayOrder
        +id() CategoryId
        +name() string
        +icon() string?
        +colorHex() string
        +displayOrder() int
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
    CategoryId ..> Uuid : uses
    PictogramId ..> Uuid : uses
    PhraseId ..> Uuid : uses
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
        +findByIds(array~PictogramId~) array~Pictogram~
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

    class PictogramProviderInterface {
        <<interface>>
        +searchByKeyword(string, string) array~Pictogram~
        +fetchById(int) Pictogram?
        +sync(array~string~) int
    }

    CategoryRepository ..> Category
    PictogramRepository ..> Pictogram
    PictogramRepository ..> CategoryId
    PhraseRepository ..> Phrase
    PhraseGeneratorInterface ..> PictogramSequence
    PictogramProviderInterface ..> Pictogram
```

> **Nota:** Las interfaces de servicio permiten cambiar proveedores sin modificar el dominio:
> - `PhraseGeneratorInterface` → OpenAI, Claude, Gemini, etc.
> - `PictogramProviderInterface` → ARASAAC, Mulberry Symbols, etc.

## Diagrama de Módulos

```mermaid
graph TB
    subgraph Domain["Domain Layer"]
        subgraph SharedModule["Shared"]
            S_Exception[DomainException]
            S_Uuid[Uuid]
            S_UuidGen[UuidGeneratorInterface]
        end

        subgraph PictogramModule["Pictogram"]
            P_Entity[Pictogram]
            P_VO1[PictogramId]
            P_VO2[ArasaacId]
            P_Exc1[InvalidPictogramLabelException]
            P_Exc2[InvalidImagePathException]
            P_Repo[PictogramRepository]
            P_Service[PictogramProviderInterface]
        end

        subgraph CategoryModule["Category"]
            C_Entity[Category]
            C_VO[CategoryId]
            C_Exc1[InvalidCategoryNameException]
            C_Exc2[InvalidCategoryColorException]
            C_Exc3[InvalidCategoryDisplayOrderException]
            C_Repo[CategoryRepository]
        end

        subgraph PhraseModule["Phrase"]
            Ph_Entity[Phrase]
            Ph_VO1[PhraseId]
            Ph_VO2[PictogramSequence]
            Ph_Exc1[InvalidPhraseVariationsException]
            Ph_Exc2[InvalidPictogramSequenceException]
            Ph_Repo[PhraseRepository]
            Ph_Service[PhraseGeneratorInterface]
        end
    end

    P_Entity --> C_VO
    Ph_VO2 --> P_VO1
    Ph_Service --> Ph_VO2

    P_Exc1 --> S_Exception
    P_Exc2 --> S_Exception
    C_Exc1 --> S_Exception
    C_Exc2 --> S_Exception
    C_Exc3 --> S_Exception
    Ph_Exc1 --> S_Exception
    Ph_Exc2 --> S_Exception

    P_VO1 -.-> S_Uuid
    C_VO -.-> S_Uuid
    Ph_VO1 -.-> S_Uuid

    style Domain fill:#e1f5fe
    style SharedModule fill:#f3e5f5
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
    participant LLM as LLM Provider

    U->>App: Selecciona pictogramas [🍎,👦,⏰]
    App->>App: Genera PictogramSequence
    App->>App: Calcula hash SHA256
    App->>PR: findBySequenceHash(hash)

    alt Frase en caché
        PR-->>App: Phrase existente
        App-->>U: Variaciones cacheadas
    else No está en caché
        App->>LLM: Genera 3 variaciones
        LLM-->>App: ["Quiero comer", "Me gustaría...", "Tengo ganas..."]
        App->>PR: save(nuevaPhrase)
        App-->>U: Variaciones nuevas
    end
```

> **Nota:** LLM Provider implementa `PhraseGeneratorInterface`. Inicialmente OpenAI GPT-4o-mini.

## Diagrama de Excepciones

```mermaid
classDiagram
    direction TB

    class DomainException {
        <<abstract>>
    }

    class InvalidPictogramLabelException {
        -int? actualLength
        -int? maxLength
        +tooLong(int, int)$ self
        +empty()$ self
        +getActualLength() int?
        +getMaxLength() int?
    }

    class InvalidImagePathException {
        -string? path
        +pathTraversalDetected(string)$ self
        +empty()$ self
        +getPath() string?
    }

    class InvalidCategoryNameException {
        -int? actualLength
        -int? maxLength
        +tooLong(int, int)$ self
        +empty()$ self
        +getActualLength() int?
        +getMaxLength() int?
    }

    class InvalidCategoryColorException {
        +invalidFormat(string)$ self
        +empty()$ self
    }

    class InvalidCategoryDisplayOrderException {
        +negative(int)$ self
    }

    class InvalidPhraseVariationsException {
        -int? actualCount
        -int? maxCount
        -int? index
        -int? actualLength
        -int? maxLength
        +tooMany(int, int)$ self
        +empty()$ self
        +variationTooLong(int, int, int)$ self
    }

    class InvalidPictogramSequenceException {
        -int? actualCount
        -int? maxCount
        +tooMany(int, int)$ self
        +empty()$ self
    }

    DomainException <|-- InvalidPictogramLabelException
    DomainException <|-- InvalidImagePathException
    DomainException <|-- InvalidCategoryNameException
    DomainException <|-- InvalidCategoryColorException
    DomainException <|-- InvalidCategoryDisplayOrderException
    DomainException <|-- InvalidPhraseVariationsException
    DomainException <|-- InvalidPictogramSequenceException
```

> **Nota:** Todas las excepciones de dominio extienden `DomainException`, permitiendo captura semántica en Application/Infrastructure.

## Resumen

| Módulo | Entidad | Value Objects | Excepciones | Repositorio | Servicio |
|--------|---------|---------------|-------------|-------------|----------|
| **Shared** | - | `Uuid` | `DomainException` | - | `UuidGeneratorInterface` |
| **Pictogram** | `Pictogram` | `PictogramId`, `ArasaacId` | `InvalidPictogramLabelException`, `InvalidImagePathException` | `PictogramRepository` | `PictogramProviderInterface` |
| **Category** | `Category` | `CategoryId` | `InvalidCategoryNameException`, `InvalidCategoryColorException`, `InvalidCategoryDisplayOrderException` | `CategoryRepository` | - |
| **Phrase** | `Phrase` | `PhraseId`, `PictogramSequence` | `InvalidPhraseVariationsException`, `InvalidPictogramSequenceException` | `PhraseRepository` | `PhraseGeneratorInterface` |

> **Nota sobre Uuid:** `Uuid` solo valida formato UUID v4 (RFC 4122). La generación se delega a `UuidGeneratorInterface`, cuya implementación vive en Infrastructure (inyección de dependencias).

## Validaciones de Seguridad

| Entidad | Validación | Motivo |
|---------|------------|--------|
| `Pictogram` | `imagePath` no contiene `..` | Prevenir path traversal |
| `Pictogram` | `label` máx 100 chars | Prevenir DoS |
| `Category` | `name` máx 50 chars | Prevenir DoS |
| `Phrase` | `variations` máx 3, 500 chars c/u | Límite LLM + DoS |
| `PictogramSequence` | Máx 10 pictogramas | Límite razonable |
