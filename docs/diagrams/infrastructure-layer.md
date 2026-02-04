# Infrastructure Layer - Diagramas

> Diagramas del Infrastructure Layer de PictoSpeak AI

## Diagrama General de Componentes

```mermaid
graph TB
    subgraph Infrastructure["Infrastructure Layer"]
        subgraph Http["Http (Controllers)"]
            HC[HealthController]
            CC[CategoryController]
            PC[PictogramController]
            PhC[PhraseController]
        end

        subgraph Console["Console Commands"]
            LFC[LoadFixturesCommand]
            SAC[SyncArasaacCommand]
        end

        subgraph ExternalApi["External APIs"]
            AAC[ArasaacApiClient]
            OAI[OpenAIPhraseGenerator]
            FOAI[FakeOpenAIPhraseGenerator]
        end

        subgraph Persistence["Persistence (Cycle ORM)"]
            CCR[CycleCategoryRepository]
            CPR[CyclePictogramRepository]
            CPhR[CyclePhraseRepository]

            CE[CategoryEntity]
            PE[PictogramEntity]
            PhE[PhraseEntity]

            CM[CategoryMapper]
            PM[PictogramMapper]
            PhM[PhraseMapper]
        end

        subgraph Health["Health Checks"]
            DHC[CycleDatabaseHealthChecker]
        end

        subgraph Service["Services"]
            VL[YamlVocabularyLoader]
            ID[HttpImageDownloader]
        end

        subgraph DataFixtures["Data Fixtures"]
            CF[CategoryFixtures]
        end

        subgraph Shared["Shared"]
            UG[SymfonyUuidGenerator]
        end
    end

    subgraph Application["Application Layer"]
        UC1[GetAllCategories]
        UC2[GetAllPictograms]
        UC3[GetPictogramsByCategory]
        UC4[SearchPictogram]
        UC5[GenerateHumanizedPhrase]
    end

    subgraph Domain["Domain Layer"]
        CatRepo[CategoryRepository]
        PicRepo[PictogramRepository]
        PhrRepo[PhraseRepository]
        PPI[PictogramProviderInterface]
        PGI[PhraseGeneratorInterface]
        UGI[UuidGeneratorInterface]
    end

    subgraph External["External Services"]
        DB[(PostgreSQL)]
        ARASAAC[ARASAAC API]
        OpenAI[OpenAI API]
    end

    %% Controller -> Use Case
    CC --> UC1
    PC --> UC2
    PC --> UC3
    PC --> UC4
    PhC --> UC5

    %% Use Case -> Repository Interface
    UC1 --> CatRepo
    UC2 --> PicRepo
    UC3 --> PicRepo
    UC3 --> CatRepo
    UC4 --> PicRepo
    UC4 --> PPI
    UC5 --> PicRepo
    UC5 --> PhrRepo
    UC5 --> PGI

    %% Infrastructure implements Domain interfaces
    CCR -.-> CatRepo
    CPR -.-> PicRepo
    CPhR -.-> PhrRepo
    AAC -.-> PPI
    OAI -.-> PGI
    FOAI -.-> PGI
    UG -.-> UGI

    %% Persistence -> Database
    CCR --> DB
    CPR --> DB
    CPhR --> DB

    %% External API -> External Services
    AAC --> ARASAAC
    OAI --> OpenAI

    %% Health Check -> Database
    DHC --> DB
    HC --> DHC

    %% Console -> Services
    SAC --> AAC
    SAC --> VL
    SAC --> ID
    LFC --> CF

    style Infrastructure fill:#fff3e0
    style Application fill:#e3f2fd
    style Domain fill:#e8f5e9
    style External fill:#fce4ec
```

## Diagrama de Controllers HTTP

```mermaid
classDiagram
    direction TB

    class HealthController {
        -DatabaseHealthCheckerInterface dbHealthChecker
        +index() JsonResponse
        +live() JsonResponse
        +ready() JsonResponse
    }

    class CategoryController {
        -GetAllCategories getAllCategories
        -CategoryRepository categoryRepository
        +list() JsonResponse
        +show(string id) JsonResponse
    }

    class PictogramController {
        -GetAllPictograms getAllPictograms
        -GetPictogramsByCategory getPictogramsByCategory
        -PictogramRepository pictogramRepository
        -SearchPictogram searchPictogram
        +list(Request) JsonResponse
        +search(Request) JsonResponse
        +show(string id) JsonResponse
    }

    class PhraseController {
        -GenerateHumanizedPhrase generateHumanizedPhrase
        -RateLimiterFactory rateLimiter
        +generate(Request) JsonResponse
    }

    HealthController ..> DatabaseHealthCheckerInterface
    CategoryController ..> GetAllCategories
    CategoryController ..> CategoryRepository
    PictogramController ..> GetAllPictograms
    PictogramController ..> GetPictogramsByCategory
    PictogramController ..> SearchPictogram
    PictogramController ..> PictogramRepository
    PhraseController ..> GenerateHumanizedPhrase
```

## Diagrama de External API Clients

```mermaid
classDiagram
    direction TB

    class PictogramProviderInterface {
        <<interface>>
        +searchByKeyword(string, string) array~Pictogram~
        +fetchById(int) Pictogram?
        +sync(array~string~) int
    }

    class PhraseGeneratorInterface {
        <<interface>>
        +generate(PictogramSequence) array~string~
    }

    class ArasaacApiClient {
        -HttpClientInterface httpClient
        -UuidGeneratorInterface? uuidGenerator
        +searchByKeyword(string keyword, string language) array~Pictogram~
        +fetchById(int providerId) Pictogram?
        +sync(array~string~ keywords) int
        +getImageUrl(int arasaacId, int resolution) string
        -mapToPictogram(array data) Pictogram
        -extractLabel(array data) string
        -validateLanguage(string language) string
    }

    class RealOpenAIPhraseGenerator {
        -HttpClientInterface httpClient
        -string apiKey
        -string model
        -float temperature
        -int maxTokens
        -array~string~ labels
        +generate(PictogramSequence) array~string~
        -buildRequestBody() array
        -buildUserPrompt() string
        -sanitizeLabel(string label) string
        -handleResponse(mixed response) array~string~
        -parseResponse(array data) array~string~
        -parseVariations(string content) array~string~
    }

    class FakeOpenAIPhraseGenerator {
        +generate(PictogramSequence) array~string~
    }

    class OpenAIPhraseGeneratorFactory {
        +create(array labels) PhraseGeneratorInterface
    }

    PictogramProviderInterface <|.. ArasaacApiClient
    PhraseGeneratorInterface <|.. RealOpenAIPhraseGenerator
    PhraseGeneratorInterface <|.. FakeOpenAIPhraseGenerator
    OpenAIPhraseGeneratorFactory --> RealOpenAIPhraseGenerator
    OpenAIPhraseGeneratorFactory --> FakeOpenAIPhraseGenerator
```

## Diagrama de Persistence (Cycle ORM)

```mermaid
classDiagram
    direction TB

    %% Domain Repositories
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
        +findByArasaacId(int) Pictogram?
        +findByCategoryId(CategoryId) array~Pictogram~
        +findByLabelLike(string, int) array~Pictogram~
        +findAll() array~Pictogram~
        +save(Pictogram) void
    }

    class PhraseRepository {
        <<interface>>
        +findById(PhraseId) Phrase?
        +findBySequenceHash(string) Phrase?
        +save(Phrase) void
    }

    %% Cycle Implementations
    class CycleCategoryRepository {
        -ORM orm
        +findById(CategoryId) Category?
        +findByName(string) Category?
        +findAll() array~Category~
        +save(Category) void
        -toDomain(CategoryEntity) Category
    }

    class CyclePictogramRepository {
        -ORM orm
        +findById(PictogramId) Pictogram?
        +findByArasaacId(int) Pictogram?
        +findByCategoryId(CategoryId) array~Pictogram~
        +findByLabelLike(string, int) array~Pictogram~
        +findAll() array~Pictogram~
        +save(Pictogram) void
        -toDomain(PictogramEntity) Pictogram
    }

    class CyclePhraseRepository {
        -ORM orm
        +findById(PhraseId) Phrase?
        +findBySequenceHash(string) Phrase?
        +save(Phrase) void
        -toDomain(PhraseEntity) Phrase
    }

    %% Cycle Entities
    class CategoryEntity {
        +string id
        +string name
        +string? icon
    }

    class PictogramEntity {
        +string id
        +int arasaacId
        +string categoryId
        +string label
        +string imagePath
    }

    class PhraseEntity {
        +string id
        +string sequenceHash
        +array~string~ pictogramIds
        +array~string~ variations
        +DateTimeImmutable createdAt
    }

    %% Relationships
    CategoryRepository <|.. CycleCategoryRepository
    PictogramRepository <|.. CyclePictogramRepository
    PhraseRepository <|.. CyclePhraseRepository

    CycleCategoryRepository --> CategoryEntity
    CyclePictogramRepository --> PictogramEntity
    CyclePhraseRepository --> PhraseEntity
```

## Diagrama de Console Commands

```mermaid
classDiagram
    direction TB

    class LoadFixturesCommand {
        -CategoryFixturesInterface categoryFixtures
        +configure() void
        +execute(InputInterface, OutputInterface) int
        -executeDryRun(SymfonyStyle) void
    }

    class SyncArasaacCommand {
        -PictogramProviderInterface pictogramProvider
        -PictogramRepository pictogramRepository
        -CategoryRepository categoryRepository
        -UuidGeneratorInterface uuidGenerator
        -VocabularyLoaderInterface vocabularyLoader
        -ImageDownloaderInterface imageDownloader
        -string pictogramsDirectory
        +configure() void
        +execute(InputInterface, OutputInterface) int
        -syncKeywords(SymfonyStyle, array, string, bool, int) int
        -syncAllCategories(SymfonyStyle, bool, int) int
        -syncKeywordsForCategory(SymfonyStyle, array, Category, bool, int) int
        -processKeyword(SymfonyStyle, string, Category, bool, int) int
        -syncSinglePictogram(SymfonyStyle, Pictogram, CategoryId) bool
    }

    class CategoryFixturesInterface {
        <<interface>>
        +load() int
    }

    class CategoryFixtures {
        -CategoryRepository categoryRepository
        -UuidGeneratorInterface uuidGenerator
        +load() int
        +getCategories()$ array
    }

    class VocabularyLoaderInterface {
        <<interface>>
        +load() array
    }

    class YamlVocabularyLoader {
        -string vocabularyPath
        +load() array
    }

    class ImageDownloaderInterface {
        <<interface>>
        +download(string url, string targetPath) bool
        +exists(string path) bool
    }

    class HttpImageDownloader {
        -HttpClientInterface httpClient
        +download(string url, string targetPath) bool
        +exists(string path) bool
    }

    LoadFixturesCommand --> CategoryFixturesInterface
    CategoryFixturesInterface <|.. CategoryFixtures
    SyncArasaacCommand --> VocabularyLoaderInterface
    SyncArasaacCommand --> ImageDownloaderInterface
    VocabularyLoaderInterface <|.. YamlVocabularyLoader
    ImageDownloaderInterface <|.. HttpImageDownloader
```

## Diagrama de Health Checks

```mermaid
classDiagram
    direction LR

    class DatabaseHealthCheckerInterface {
        <<interface>>
        +check() array
    }

    class CycleDatabaseHealthChecker {
        -DatabaseManager databaseManager
        +check() array
    }

    class HealthController {
        -DatabaseHealthCheckerInterface databaseHealthChecker
        +index() JsonResponse
        +live() JsonResponse
        +ready() JsonResponse
    }

    DatabaseHealthCheckerInterface <|.. CycleDatabaseHealthChecker
    HealthController --> DatabaseHealthCheckerInterface
```

## Flujo de Request HTTP

```mermaid
sequenceDiagram
    participant Client
    participant Router as Symfony Router
    participant Controller
    participant UseCase as Use Case
    participant Repository as Repository Interface
    participant CycleRepo as Cycle Repository
    participant DB as PostgreSQL

    Client->>Router: HTTP Request
    Router->>Controller: Route to action

    alt Health Check
        Controller->>Controller: Check dependencies
        Controller-->>Client: 200 OK / 503 Service Unavailable
    else API Request
        Controller->>Controller: Validate input
        Controller->>UseCase: Execute
        UseCase->>Repository: Query/Command
        Repository->>CycleRepo: Delegate
        CycleRepo->>DB: SQL Query
        DB-->>CycleRepo: Result
        CycleRepo-->>Repository: Domain Entity
        Repository-->>UseCase: Domain Entity
        UseCase-->>Controller: DTO
        Controller-->>Client: JSON Response
    end
```

## Flujo de Sincronizacion ARASAAC

```mermaid
sequenceDiagram
    participant CLI as CLI Command
    participant VL as VocabularyLoader
    participant AAC as ArasaacApiClient
    participant ID as ImageDownloader
    participant Repo as PictogramRepository
    participant ARASAAC as ARASAAC API
    participant CDN as ARASAAC CDN
    participant DB as PostgreSQL
    participant FS as File System

    CLI->>VL: load()
    VL-->>CLI: {category: [keywords]}

    loop For each category/keyword
        CLI->>AAC: searchByKeyword(keyword)
        AAC->>ARASAAC: GET /pictograms/es/search/{keyword}
        ARASAAC-->>AAC: JSON results
        AAC-->>CLI: array<Pictogram>

        loop For each pictogram
            CLI->>ID: exists(localPath)
            ID-->>CLI: bool

            alt Image not exists
                CLI->>ID: download(cdnUrl, localPath)
                ID->>CDN: GET image
                CDN-->>ID: PNG binary
                ID->>FS: Write file
                FS-->>ID: OK
                ID-->>CLI: true
            end

            CLI->>Repo: save(pictogram)
            Repo->>DB: INSERT
            DB-->>Repo: OK
        end
    end

    CLI-->>CLI: Success message
```

## Resumen de Componentes

| Categoria | Componente | Responsabilidad |
|-----------|------------|-----------------|
| **Http** | `HealthController` | Health checks para orquestadores |
| **Http** | `CategoryController` | CRUD de categorias |
| **Http** | `PictogramController` | CRUD y busqueda de pictogramas |
| **Http** | `PhraseController` | Generacion de frases con rate limiting |
| **Console** | `LoadFixturesCommand` | Carga de categorias SAAC |
| **Console** | `SyncArasaacCommand` | Sincronizacion con ARASAAC |
| **ExternalApi** | `ArasaacApiClient` | Cliente ARASAAC API |
| **ExternalApi** | `RealOpenAIPhraseGenerator` | Generador con OpenAI |
| **ExternalApi** | `FakeOpenAIPhraseGenerator` | Fake para desarrollo |
| **Persistence** | `CycleCategoryRepository` | Persistencia de categorias |
| **Persistence** | `CyclePictogramRepository` | Persistencia de pictogramas |
| **Persistence** | `CyclePhraseRepository` | Persistencia de frases (cache) |
| **Health** | `CycleDatabaseHealthChecker` | Verificacion de conectividad DB |
| **Service** | `YamlVocabularyLoader` | Carga de vocabulario |
| **Service** | `HttpImageDownloader` | Descarga de imagenes |
| **Shared** | `SymfonyUuidGenerator` | Generacion de UUIDs |

## Principios Aplicados

| Principio | Aplicacion |
|-----------|------------|
| **Dependency Inversion** | Controllers dependen de Use Cases, no de Repositories |
| **Interface Segregation** | Interfaces especificas: `ImageDownloaderInterface`, `VocabularyLoaderInterface` |
| **Single Responsibility** | Cada servicio tiene una unica responsabilidad |
| **Open/Closed** | Nuevos proveedores (Claude, Gemini) sin modificar Use Cases |
| **Liskov Substitution** | `FakeOpenAIPhraseGenerator` intercambiable con `RealOpenAIPhraseGenerator` |
