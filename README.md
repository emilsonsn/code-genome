<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12.x">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

<h1 align="center">🧬 Code Genome</h1>

<p align="center">
  <strong>Extract the DNA of any codebase</strong><br>
  A powerful Laravel-based tool that analyzes the internal structure of software repositories,<br>
  detecting technologies, measuring complexity, and generating maintainability insights.
</p>

<p align="center">
  <a href="#-features">Features</a> •
  <a href="#-quick-start">Quick Start</a> •
  <a href="#-installation">Installation</a> •
  <a href="#-usage">Usage</a> •
  <a href="#-architecture">Architecture</a> •
  <a href="#-contributing">Contributing</a>
</p>

---

<p align="center">
  <img height="783" alt="image" src="https://github.com/user-attachments/assets/721e1693-711f-4c48-88b9-caa6e5a17953" width="49%" />
    <img height="782" alt="image" src="https://github.com/user-attachments/assets/e907b668-baa5-4ef0-a0c2-2ad39cb70126" width="49%" />
</p>

<p align="center">
  <img width="49%" height="776" alt="image" src="https://github.com/user-attachments/assets/37c0b970-d936-4648-b552-50791576ecb4" />

<img width="49%" height="777" alt="image" src="https://github.com/user-attachments/assets/a58f6800-13bc-4451-a3ae-c6ac64c5dfb4" />
</p>

---

## 📖 Overview

Modern software repositories contain valuable signals about code quality, architecture, and maintainability. However, extracting these signals manually is time-consuming and error-prone.

**Code Genome** automates this process by:

1. **Cloning** any public Git repository using optimized shallow cloning
2. **Analyzing** the filesystem structure and file contents
3. **Detecting** programming languages, frameworks, and tech stacks
4. **Measuring** project complexity through multiple metrics
5. **Scoring** documentation, testing, and maintainability
6. **Visualizing** results in an interactive dashboard

> **Note:** Code Genome focuses on **structural analysis**, not static code analysis. It examines the shape and organization of your codebase rather than the code itself.

---

## ✨ Features

### 🔍 Repository Analysis
| Feature | Description |
|---------|-------------|
| **URL-based Analysis** | Simply paste a Git repository URL to start analysis |
| **Shallow Cloning** | Uses `--depth=1` for fast and efficient cloning |
| **Automatic Cleanup** | Cloned repositories are deleted after analysis |
| **Result Caching** | Previously analyzed repositories are cached |

### 🛠️ Stack Detection
Automatically identifies 17+ technologies and frameworks:

| Category | Technologies |
|----------|-------------|
| **PHP** | Laravel, Symfony |
| **JavaScript** | Node.js, React, Vue, Angular, Express, Next.js, Nuxt, NestJS |
| **Python** | Python, Django, Flask, FastAPI |
| **Java** | Spring |
| **.NET** | .NET, ASP.NET |

### 📊 Metrics Collected

<details>
<summary><strong>File & Directory Metrics</strong></summary>

- Total files count
- Total directories count
- Repository size (bytes & human-readable)
- Maximum directory depth
- Average files per directory
- Largest files (top 10)
- Largest directories (top 10)
- Visual directory tree structure

</details>

<details>
<summary><strong>Language Analysis</strong></summary>

- Language detection by file extension
- Language distribution percentages
- Extension frequency mapping

</details>

<details>
<summary><strong>Quality Indicators</strong></summary>

- README presence detection
- Documentation folder (`/docs`) detection
- Test files detection (supports PHP, JavaScript, TypeScript, Python conventions)
- Test-to-code ratio calculation

</details>

<details>
<summary><strong>Dependency Ecosystem</strong></summary>

Detects presence of:
- `composer.json` (PHP/Composer)
- `package.json` (Node.js/npm)
- `requirements.txt` (Python/pip)
- `pyproject.toml` (Python/Poetry)
- `Gemfile` (Ruby/Bundler)
- `go.mod` (Go modules)

</details>

### 📈 Scoring System

Code Genome generates three key scores (0-100):

| Score | How It's Calculated |
|-------|---------------------|
| **Documentation** | +70 for README, +30 for `/docs` folder |
| **Tests** | Based on test file ratio (test files / total files) |
| **Maintainability** | Composite score: README (+30), tests presence (+40), project size (+30) |

---

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/emilsonsn/code-genome.git
cd code-genome

# Run the setup script (installs all dependencies)
composer setup

# Start the development server
composer dev
```

Then open http://localhost:8000 in your browser.

---

## 📋 Prerequisites

Before installing Code Genome, ensure you have the following:

| Requirement | Version | Notes |
|-------------|---------|-------|
| **PHP** | >= 8.2 | With required extensions |
| **Composer** | >= 2.0 | PHP dependency manager |
| **Node.js** | >= 18.x | For frontend assets |
| **npm** | >= 9.x | Node package manager |
| **Git** | >= 2.x | Required for cloning repositories |
| **SQLite** | >= 3.x | Default database (or MySQL/PostgreSQL) |

### Required PHP Extensions

```
BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
```

---

## 📥 Installation

### Option 1: Automated Setup (Recommended)

The project includes a convenient setup script that handles everything:

```bash
# 1. Clone the repository
git clone https://github.com/emilsonsn/code-genome.git
cd code-genome

# 2. Run the automated setup
composer setup
```

This command will:
- Install PHP dependencies via Composer
- Copy `.env.example` to `.env` (if not exists)
- Generate application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

### Option 2: Manual Installation

```bash
# 1. Clone the repository
git clone https://github.com/emilsonsn/code-genome.git
cd code-genome

# 2. Install PHP dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Create SQLite database
touch database/database.sqlite

# 6. Run migrations
php artisan migrate

# 7. Install Node.js dependencies
npm install

# 8. Build frontend assets
npm run build
```

---

## ⚙️ Configuration

### Environment Variables

Copy the example environment file and configure as needed:

```bash
cp .env.example .env
```

Key configuration options:

```env
# Application
APP_NAME="Code Genome"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (SQLite is default, no extra config needed)
DB_CONNECTION=sqlite

# For MySQL/PostgreSQL, uncomment and configure:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=code_genome
# DB_USERNAME=root
# DB_PASSWORD=

# Queue (for async processing)
QUEUE_CONNECTION=database

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

### Database Options

**SQLite (Default)**
```bash
# Create the SQLite database file
touch database/database.sqlite
php artisan migrate
```

**MySQL**
```bash
# Update .env with MySQL credentials, then:
php artisan migrate
```

---

## 🎯 Usage

### Starting the Development Server

Code Genome includes a powerful development script that runs all services concurrently:

```bash
composer dev
```

This starts:
- 🌐 **Laravel Server** at http://localhost:8000
- 📋 **Queue Worker** for background jobs
- 📝 **Laravel Pail** for real-time log tailing
- ⚡ **Vite** for hot-reload development

### Analyzing a Repository

1. Open http://localhost:8000 in your browser
2. Paste a public Git repository URL (e.g., `https://github.com/laravel/laravel`)
3. Click **"Analyze Repository"**
4. View the comprehensive analysis dashboard

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/` | Home page with analysis form |
| `POST` | `/analyses` | Submit a repository for analysis |
| `GET` | `/analyses/{slug}` | View analysis results |

---

## 🏗️ Architecture

Code Genome follows a clean, layered architecture inspired by Domain-Driven Design principles:

```
app/
├── Enums/                    # Application enumerations
│   └── RepositoryStack.php   # Supported tech stacks enum
├── Http/
│   └── Controllers/          # HTTP request handlers
├── Infrastructure/           # External concerns
│   ├── Git/                  # Git operations
│   │   └── GitRepositoryCloner.php
│   ├── Metrics/              # Metrics collection
│   │   └── RepositoryMetricsCollector.php
│   ├── Score/                # Score calculations
│   │   └── RepositoryScoreCalculator.php
│   └── Stack/                # Stack detection
│       └── StackDetector.php
├── Models/                   # Eloquent models
│   └── RepositoryAnalysis.php
├── Repositories/             # Data access layer
│   └── RepositoryAnalysisRepository.php
└── Services/                 # Application services
    └── RepositoryAnalyzerService.php
```

### Key Components

| Component | Responsibility |
|-----------|----------------|
| `RepositoryAnalyzerService` | Orchestrates the analysis pipeline |
| `GitRepositoryCloner` | Handles shallow cloning of repositories |
| `RepositoryMetricsCollector` | Collects all structural metrics |
| `StackDetector` | Identifies technologies and frameworks |
| `RepositoryScoreCalculator` | Computes quality scores |

### Analysis Flow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   User Input    │────▶│  Clone Repo      │────▶│ Collect Metrics │
│   (Git URL)     │     │  (shallow)       │     │                 │
└─────────────────┘     └──────────────────┘     └────────┬────────┘
                                                          │
┌─────────────────┐     ┌──────────────────┐     ┌────────▼────────┐
│   Dashboard     │◀────│  Store Results   │◀────│ Calculate Scores│
│   (Results)     │     │  (Database)      │     │                 │
└─────────────────┘     └──────────────────┘     └─────────────────┘
```

---

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
composer test

# Or using PHPUnit directly
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

---

## 📁 Project Structure

```
code-genome/
├── app/                      # Application code
│   ├── Enums/                # PHP 8.1+ enumerations
│   ├── Http/Controllers/     # Request handlers
│   ├── Infrastructure/       # External services
│   ├── Models/               # Eloquent ORM models
│   ├── Providers/            # Service providers
│   ├── Repositories/         # Data access layer
│   └── Services/             # Business logic
├── bootstrap/                # Framework bootstrap
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── storage/                  # Application storage
│   └── app/repos/            # Temporary cloned repos
├── tests/                    # Test suites
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── package.json              # Node dependencies
├── phpunit.xml               # PHPUnit configuration
└── vite.config.js            # Vite configuration
```

---

## 🔮 Future Improvements

- [ ] **GitHub Integration** - OAuth authentication and private repo support
- [ ] **Comparison Mode** - Compare multiple repositories side-by-side
- [ ] **Historical Tracking** - Track repository evolution over time
- [ ] **API Endpoints** - RESTful API for programmatic access
- [ ] **Export Reports** - PDF/JSON export functionality
- [ ] **Code Complexity** - Cyclomatic complexity analysis
- [ ] **Dependency Analysis** - Security vulnerability scanning
- [ ] **AI Insights** - Machine learning-powered recommendations

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

### Getting Started

1. **Fork** the repository
2. **Clone** your fork locally
3. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
4. **Commit** your changes (`git commit -m 'Add amazing feature'`)
5. **Push** to your branch (`git push origin feature/amazing-feature`)
6. **Open** a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use conventional commits format

### Running Code Style Fixes

```bash
# Run Laravel Pint (PHP code style fixer)
./vendor/bin/pint

# Run only on dirty files
./vendor/bin/pint --dirty
```

---

## 📄 License

This project is open-sourced software licensed under the **MIT License**.

```
MIT License

Copyright (c) 2024 Emilson S.N.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/emilsonsn">Emilson S.N.</a>
</p>

<p align="center">
  <a href="https://github.com/emilsonsn/code-genome/issues">Report Bug</a> •
  <a href="https://github.com/emilsonsn/code-genome/issues">Request Feature</a>
</p>
