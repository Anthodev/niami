# Project

**ns2-ubcr** — Video game compatibility reporting platform.

## Objective

Users search for games via the IGDB API, submit compatibility reports (`GREAT / OK / BAD / BUGGED`), add comments, and upvote community reports. Aggregate compatibility data helps gamers make informed decisions.

## Stack

- **Language**: PHP 8.4+ (strict typing, native attributes)
- **Framework**: Symfony 7.4.5
- **Runtime**: FrankenPHP (`runtime/frankenphp-symfony`)
- **Database**: PostgreSQL 16
- **ORM**: Doctrine ORM 3.6.2 (XML mapping)
- **Frontend**: Twig + Tailwind CSS (via `symfonycasts/tailwind-bundle`) + DaisyUI + Symfony UX Turbo + Stimulus + AssetMapper
- **Auth**: Lexik JWT Authentication Bundle 3.2.0
- **Real-time**: Mercure (embedded in FrankenPHP/Caddy)
- **Message bus**: Symfony Messenger (CQRS dispatch)
- **Cache**: Symfony Cache (IGDB API response caching)

## Dependencies

From `composer.lock`:

| Package | Version |
|---|---|
| symfony/framework-bundle | 7.4.5 |
| doctrine/orm | 3.6.2 |
| lexik/jwt-authentication-bundle | 3.2.0 |
| symfony/messenger | 7.4.* |
| symfony/mercure-bundle | ^0.3.9 |
| symfonycasts/tailwind-bundle | >=0.12.0 |
| pestphp/pest | 4.3.2 |
| phpstan/phpstan | 2.1.39 |
| friendsofphp/php-cs-fixer | ^3.94.0 |
| symplify/easy-coding-standard | ^12.6.2 |
| dama/doctrine-test-bundle | ^8.6.0 |
| fakerphp/faker | ^1.24.1 |

Dev dependencies: `dama/doctrine-test-bundle`, `doctrine/doctrine-fixtures-bundle`, `fakerphp/faker`, `pestphp/pest`, `phpstan/phpstan`, `symfony/maker-bundle`, `symplify/easy-coding-standard`.

## Architecture

**Clean Architecture + CQRS** via Symfony Messenger.

Layers:
- `Domain/` — entities, repository interfaces, domain service interfaces, factories, traits, XML validation constraints
- `Application/` — CQRS commands, queries, handlers, fetchers, event listeners, exceptions, security authenticator, serializers, cache helpers
- `Infrastructure/` — Doctrine repositories, migrations, IGDB API client, custom Doctrine types
- `Presentation/` — controllers (web + API), Symfony forms, DTOs, Twig templates
- `Shared/` — cross-layer DTOs and enums
- `DataFixtures/` — Doctrine fixtures (dev/test only)

CQRS dispatch goes through `MessageBusHelper` (`src/Application/Helper/MessageBusHelper.php`). Repository interfaces defined in `Domain/`, implemented in `Infrastructure/Persistence/Doctrine/`. Service bindings in `config/services.yaml`.

ORM mapping: XML files in `src/Infrastructure/Persistence/Doctrine/config/`.

## Project structure

```
src/
├── Application/
│   ├── Command/         # CQRS write commands (Game, Report)
│   ├── CommandHandler/  # Handlers for commands
│   ├── Query/           # CQRS read queries (Game, Report, Search)
│   ├── QueryHandler/    # Handlers for queries
│   ├── Fetcher/         # Application-level fetchers
│   ├── Helper/          # MessageBusHelper, CacheKeyBuilderHelper, SlugHelper
│   ├── EventListener/   # EntityListener, JsonExceptionListener, UserHasherPassword
│   ├── Security/        # JwtAuthenticator, PasswordChanger
│   ├── Serializer/      # IGDB response normalizers
│   └── Service/Game/    # ApiGameSearchService
├── Domain/
│   ├── Model/           # Game, Developer, Publisher, ApiGame, Report, ReportComment, User, Role
│   ├── Repository/      # Repository interfaces (per aggregate)
│   ├── Factory/         # Domain factories
│   ├── Service/         # GameApiServiceInterface
│   ├── Trait/           # IdTrait, TimestampableTrait
│   └── Resources/config/validator/  # XML validation constraints
├── Infrastructure/
│   ├── Client/          # IgdbClient (OAuth2 + IGDB REST)
│   ├── Persistence/Doctrine/
│   │   ├── config/      # XML ORM mappings
│   │   ├── Migrations/  # Doctrine migrations
│   │   └── */Repository/ # DoctrineXxxRepository implementations
│   ├── Repository/Game/ # IgdbApiRepository (implements ApiGameRepositoryInterface)
│   └── Enum/            # IgdbGamePlatformEnum, IgdbGameTypeEnum, ReportGameStatusEnum, etc.
├── Presentation/
│   ├── Controller/      # Web controllers + Api/ subdirectory
│   ├── Form/            # Symfony Form types
│   ├── Dto/             # Form input DTOs
│   └── Template/        # Twig templates (partials, reports, turbo frames)
├── Shared/
│   ├── Dto/Game/        # IgdbSearchResponseDto, SearchResultsOutputDto, etc.
│   └── Enum/            # SearchParameterEnum
└── DataFixtures/        # AppFixtures, GameFixtures, UserFixtures, etc.

config/
├── packages/            # Per-bundle YAML config
├── routes.yaml          # Route loader (attribute-based) + /api/login_check
└── services.yaml        # DI bindings and interface-to-implementation wiring

tests/
├── Unit/                # 20 unit test files (handlers, services, serializers, validators)
├── Functional/          # 3 functional test files (controllers)
├── BaseTestCase.php
├── BaseWebTestCase.php
└── Pest.php
```

## Main features

| Feature | Entry point |
|---|---|
| Home / game search | `src/Presentation/Controller/DefaultController.php`, `SearchController.php` |
| Compatibility reports list | `src/Presentation/Controller/ReportsController.php` |
| Create report (web) | `src/Presentation/Controller/ReportCreateController.php` |
| Create report (dev CLI shortcut) | `src/Presentation/Controller/TestController.php` — dev env only, bypasses front |
| Report comments | `src/Presentation/Controller/CreateReportCommentController.php` |
| Upvote report | `src/Presentation/Controller/ReportIncreaseUpvoteCountController.php` |
| JWT login | `POST /api/login_check` (Lexik JWT) |
| API ping | `src/Presentation/Controller/Api/PingController.php` |
| IGDB game search | `src/Infrastructure/Client/IgdbClient.php` + `IgdbApiRepository.php` |
| Game auto-create/update from IGDB | `CreateGameWithCacheCheckCommandHandler.php`, `UpdateGameFromApiCommandHandler.php` |
| Cache layer (IGDB responses) | `CacheKeyBuilderHelper.php`, `CacheDurationEnum.php` |
| Mercure real-time | Embedded in FrankenPHP, configured in `config/packages/mercure.yaml` |

Report statuses (enum `ReportGameStatusEnum`): `GREAT`, `OK`, `BAD`, `BUGGED`.

`ApiGame` model (`src/Domain/Model/Game/ApiGame.php`) represents raw IGDB search results before they are persisted as `Game` entities.

## Tests

- **Runner**: Pest 4.3.2 (PHPUnit-compatible)
- **Config**: `phpunit.xml.dist`, `tests/bootstrap.php`
- **Extension**: `DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension` (DB transaction rollback per test)
- **Unit tests**: 20 files — command handlers, query handlers, services, serializers, validators, IGDB client/repo
- **Functional tests**: 3 files — `PingControllerTest`, `SearchControllerTest`, `ReportIncreaseUpvoteCountControllerTest`
- **Coverage**: no numeric percentage available (no coverage report file present); README claims 95%+ but this is unverified without running the suite
- **Fixtures**: `dama/doctrine-test-bundle` wraps tests in transactions; dedicated fixture loader for test env via `just ft`

Run tests:
```bash
just tests
# or inside container:
vendor/bin/pest --display-warnings --display-errors
```

## Tooling

| Tool | Config file | Purpose |
|---|---|---|
| PHPStan (level 9) | `phpstan.dist.neon` | Static analysis (paths: `bin/`, `config/`, `public/`, `src/`) |
| EasyCodingStandard | `ecs.php` | Code style check + autofix (`src/` only) |
| PHP-CS-Fixer | `.php-cs-fixer.dist.php` | Rulesets: `@PER-CS`, `@Symfony` |
| Pest | `phpunit.xml.dist` | Test runner |
| Just | `justfile` | Task runner (Docker-aware, falls back to local PHP) |
| Docker Compose | `compose.yaml`, `compose.override.yaml`, `compose.prod.yaml` | Dev/prod environments |
| FrankenPHP + Caddy | `Dockerfile`, `frankenphp/Caddyfile` | App server (HTTP/3, TLS, Mercure, worker mode) |
| Symfony AssetMapper | `importmap.php`, `config/packages/asset_mapper.yaml` | JS/CSS asset pipeline |
| GitHub Actions CI | `.github/workflows/ci.yml` | PR gate: ECS, PHPStan, Pest, schema validation, security check |
| Symfony Maker | `symfony/maker-bundle` | Code generation (dev only) |
| Xdebug | `compose.override.yaml` (`XDEBUG_MODE` env var) | Debug (off by default) |
| Supervisor | available in container | Messenger workers (start: `just ss`, stop: `just sp`) |

Common `just` commands:
```bash
just start          # Full setup: build + up + DB + fixtures
just tests          # Run Pest suite
just stan           # PHPStan analysis
just ecs            # ECS check + autofix
just ecs-check      # ECS check only (no fix)
just migrate        # Run Doctrine migrations
just fixtures       # Load fixtures (dev)
just setup-database-test  # Drop/create/migrate/fixtures for test DB
just bash           # Shell into PHP container
```

## Project Management

- **Linear workspace verification**: Before creating or modifying Linear issues, projects, or initiatives, verify the active workspace and confirm it matches the user's intent.
- **Full preview required**: Always present a complete preview (title, description, state, labels, assignees, dates, linked items) before any create or update action.
- **Confirmation gate**: Never execute create, modify, or delete operations on Linear entities without explicit user confirmation.

## Conventions / Hotspots

- **Interface binding**: all repository and service interfaces wired explicitly in `config/services.yaml`
- **ORM mapping**: XML only — no annotations/attributes on entities; mapping files in `src/Infrastructure/Persistence/Doctrine/config/`
- **CQRS dispatch**: always via `MessageBusHelper::dispatchCommand()` / `dispatchQuery()` — never dispatch directly on the bus from controllers
- **Cache keys**: always built via `CacheKeyBuilderHelper` — duration controlled by `CacheDurationEnum`
- **JWT**: keys committed at `config/jwt/private.pem` + `config/jwt/public.pem`; CI regenerates them before tests
- **`TestController`**: dev-only shortcut to create reports without going through the frontend; must not be exposed in production
- **`ApiGame`**: transient IGDB result model — never persisted directly; converted to `Game` via `ApiGameFactory`
- **Validation**: XML constraint files in `src/Domain/Resources/config/validator/` — not PHP attributes
- **Ask Claude**: Use Ask Claude mode by default for all user requests for now.
