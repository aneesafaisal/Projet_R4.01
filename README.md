# R4.01 — Sports Team Management App

A PHP web application for managing a sports team: players, matches, participations, and player evaluations.

## Features

- Login / logout with JWT authentication and role-based access (`admin`, `coach`)
- Manage players (add, edit, delete, comments)
- Manage matches (add, edit, delete, record results)
- Manage match sheets (assign players, set positions)
- Evaluate player performance per match

## Architecture

The project uses a 3-layer architecture with strict frontend/backend separation:

- **Frontend** (`r401-front.alwaysdata.net`) — PHP router, session management, reusable components
- **Backend** (`equipe.alwaysdata.net`) — REST API endpoints, business logic, database access via PDO
- **Auth** (`auth.alwaysdata.net`) — JWT token generation and verification

The frontend never accesses the database directly. All data goes through the backend REST API, with JWT tokens sent in the `Authorization: Bearer` header for protected routes.

## Tech Stack

- PHP 8.1+
- MySQL
- JWT (HS256)
- Apache with mod_rewrite
- PSR-4 autoloading
