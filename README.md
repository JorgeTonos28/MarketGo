# MarketGo

MarketGo is a shopping list management application built with Laravel and Ionic.

## Architecture

- **Backend:** Laravel 11 (Monolith + API)
- **Frontend (Web):** Blade + Tailwind CSS
- **Frontend (Mobile):** Ionic + Vue 3 (located in `mobile-app/`)

## Getting Started

### Backend Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd marketgo
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup Database:**
   - Configure your database in `.env`.
   - Run migrations:
     ```bash
     php artisan migrate
     ```

5. **Serve the application:**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000/api`.

### Mobile App Setup

1. **Navigate to the mobile app directory:**
   ```bash
   cd mobile-app
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Configure environment:**
   - Copy the example environment file:
     ```bash
     cp .env.example .env
     ```
   - Edit `.env` and set `VITE_API_URL` to your backend URL.
     - **For Android Emulator:** Use `http://10.0.2.2:8000/api`
     - **For iOS Simulator:** Use `http://localhost:8000/api`
     - **For Physical Device:** Use your computer's local IP (e.g., `http://192.168.1.X:8000/api`). Ensure your backend is running with `php artisan serve --host 0.0.0.0`.

4. **Run the app:**
   ```bash
   ionic serve
   ```
   This will start the development server and open the app in your browser.

## Testing

- **Backend Tests:**
  ```bash
  php artisan test
  ```

## Mobile App Details

The mobile app is built with Ionic and Vue 3. It uses `vite` for building and bundling.
The configuration for Ionic is in `ionic.config.json` (configured as `vue-vite`).

### Key Directories
- `src/views`: Application pages (Login, Lists, Products).
- `src/services`: API service configuration.
- `src/router`: Navigation routing.
