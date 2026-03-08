<?php

namespace App\Enums;

enum RepositoryStack: string
{
    case Laravel = 'laravel';
    case Symfony = 'symfony';
    case React = 'react';
    case Vue = 'vue';
    case Angular = 'angular';
    case Node = 'node';
    case Python = 'python';
    case Django = 'django';
    case Flask = 'flask';
    case FastApi = 'fastapi';
    case Spring = 'spring';
    case Express = 'express';
    case Next = 'next';
    case Nuxt = 'nuxt';
    case Nest = 'nestjs';
    case DotNet = 'dotnet';
    case AspNet = 'aspnet';
    case Html = 'html';
    case Css = 'css';

    public function label(): string
    {
        return match ($this) {
            self::Laravel => 'Laravel',
            self::Symfony => 'Symfony',
            self::React => 'React',
            self::Vue => 'Vue',
            self::Angular => 'Angular',
            self::Node => 'Node.js',
            self::Python => 'Python',
            self::Django => 'Django',
            self::Flask => 'Flask',
            self::FastApi => 'FastAPI',
            self::Spring => 'Spring',
            self::Express => 'Express',
            self::Next => 'Next.js',
            self::Nuxt => 'Nuxt',
            self::Nest => 'NestJS',
            self::DotNet => '.NET',
            self::AspNet => 'ASP.NET',
            self::Html => 'HTML',
            self::Css => 'CSS',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Laravel => 'fa-brands fa-laravel',
            self::Symfony => 'fa-brands fa-symfony',
            self::React => 'fa-brands fa-react',
            self::Vue => 'fa-brands fa-vuejs',
            self::Angular => 'fa-brands fa-angular',
            self::Node => 'fa-brands fa-node-js',
            self::Python => 'fa-brands fa-python',
            self::Django => 'fa-solid fa-leaf',
            self::Flask => 'fa-solid fa-flask',
            self::FastApi => 'fa-solid fa-bolt',
            self::Spring => 'fa-solid fa-seedling',
            self::Express => 'fa-solid fa-server',
            self::Next => 'fa-solid fa-n',
            self::Nuxt => 'fa-solid fa-layer-group',
            self::Nest => 'fa-solid fa-cubes',
            self::DotNet => 'fa-solid fa-code',
            self::AspNet => 'fa-solid fa-globe',
            self::Html => 'fa-solid fa-code',
            self::Css => 'fa-solid fa-paint-brush',
        };
    }

    public function extensions(): array
    {
        return match ($this) {
            self::Laravel,
            self::Symfony => ['php', 'html', 'css', 'scss'],

            self::React,
            self::Node,
            self::Express,
            self::Next => ['js', 'jsx', 'ts', 'tsx', 'css', 'scss'],

            self::Vue,
            self::Nuxt => ['js', 'ts', 'vue', 'css', 'scss'],

            self::Angular,
            self::Nest => ['ts', 'html', 'css', 'scss'],

            self::Python,
            self::Django,
            self::Flask,
            self::FastApi => ['py', 'html', 'css'],

            self::Spring => ['java', 'kt', 'html', 'css'],

            self::DotNet,
            self::AspNet => ['cs', 'html', 'css'],
            self::Html => ['html'],
            self::Css => ['css', 'scss'],
        };
    }

    public function detectorMethod(): string
    {
        return match ($this) {
            self::Laravel => 'detectLaravel',
            self::Symfony => 'detectSymfony',
            self::React => 'detectReact',
            self::Vue => 'detectVue',
            self::Angular => 'detectAngular',
            self::Node => 'detectNode',
            self::Python => 'detectPython',
            self::Django => 'detectDjango',
            self::Flask => 'detectFlask',
            self::FastApi => 'detectFastApi',
            self::Spring => 'detectSpring',
            self::Express => 'detectExpress',
            self::Next => 'detectNext',
            self::Nuxt => 'detectNuxt',
            self::Nest => 'detectNest',
            self::DotNet => 'detectDotNet',
            self::AspNet => 'detectAspNet',
            self::Html => 'detectHtml',
            self::Css => 'detectCss',
        };
    }
}
