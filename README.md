# Game Compatibility Reporting Platform

A modern web application built with Symfony that allows users to search for video games and submit compatibility reports. Users can report how well games perform with different statuses and vote on existing reports to help the gaming community make informed decisions.

## 🎮 What This Project Does

This platform provides:

### Game Management
- **Game Search**: Search for video games using integration with the IGDB (Internet Game Database) API
- **Game Details**: View comprehensive game information including developers, publishers, and metadata
- **Automatic Data Sync**: Games are automatically created and updated from IGDB API data

### Compatibility Reporting System
- **Status Reports**: Users can submit reports with four different compatibility statuses:
    - `GREAT` - Game works perfectly
    - `OK` - Game works with minor issues
    - `BAD` - Game has significant problems
    - `BUGGED` - Game is broken or unplayable

### Community Features
- **Voting System**: Users can upvote helpful reports to increase their visibility
- **Report Ranking**: Reports are automatically sorted by upvote count and creation date
- **User-Generated Content**: Community-driven compatibility database

## 🛠 Technical Architecture

This application follows **Clean Architecture** principles with **CQRS** (Command Query Responsibility Segregation) pattern:

### Backend Stack
- **Framework**: Symfony 7.3 with PHP 8.4+
- **Database**: Doctrine ORM with PostgreSQL/MySQL support
- **Architecture**: Domain-Driven Design (DDD) with separated layers:
    - `Domain/` - Business logic and entities
    - `Application/` - Use cases, commands, and handlers
    - `Infrastructure/` - External services and persistence
    - `Presentation/` - Controllers, forms, and templates

### Frontend Stack
- **UI Framework**: Twig templates with Tailwind CSS and Daisy UI
- **Interactivity**: Symfony UX with Turbo and Stimulus
- **Real-time Updates**: Turbo frames for dynamic content loading

### External Integrations
- **IGDB API**: For comprehensive game database access
- **Caching**: Symfony Cache for API response optimization
- **Authentication**: Lexik JWT Authentication Bundle

## 🚀 Key Features

### For Users
- Search and discover games from the IGDB database
- Submit detailed compatibility reports
- Vote on community reports
- View aggregated compatibility data per game

### For Developers
- Clean, testable codebase with 95%+ test coverage
- Docker-ready development environment
- Modern PHP 8.4 features with strict typing
- Comprehensive test suite with Pest PHP

## 🔧 Development Setup

This project uses a **Docker-based development environment** with FrankenPHP and Caddy:

1. **Prerequisites**: Docker Compose v2.10+
2. **Build**: `docker compose build --no-cache`
3. **Start**: `docker compose up --pull always -d --wait`
4. **Access**: Visit `https://localhost` (accept the self-signed certificate)
5. **Stop**: `docker compose down --remove-orphans`

## 🎯 Use Cases

This platform is ideal for:
- **Gaming Communities** wanting to track game compatibility
- **Platform-Specific Gaming** (e.g., Steam Deck, gaming handhelds)
- **Beta Testing Communities** reporting game issues
- **Gaming Forums** needing structured compatibility data

## 📊 Project Statistics

- **Architecture**: Clean Architecture + CQRS
- **Test Coverage**: Comprehensive unit and integration tests
- **Code Quality**: Enforced with PHPStan (level 9) and PHP-CS-Fixer
- **Performance**: Optimized with FrankenPHP worker mode and caching
- **Security**: JWT authentication with proper validation

## 🚦 Production Features

- **Security**: HTTPS by default with automatic TLS certificates
- **Scalability**: HTTP/3 and Early Hints support
- **Real-time**: Mercure hub integration for live updates

---

This project transforms game compatibility tracking from scattered forum posts into a structured, community-driven database that helps gamers make informed decisions about their gaming experience.
