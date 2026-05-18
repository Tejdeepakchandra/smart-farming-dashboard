# Smart Farming Dashboard 🌾

A comprehensive IoT-integrated farming dashboard built with **Laravel 12**, **MongoDB Atlas**, **Tailwind CSS**, **Chart.js**, and **Google Gemini AI**. This project simulates IoT sensor data and provides AI-powered farming insights.

## Tech Stack

| Technology | Purpose |
|---|---|
| **PHP Laravel 12** | Backend MVC framework |
| **MongoDB Atlas** | Cloud NoSQL database |
| **Blade + Tailwind CSS** | Frontend templating & styling |
| **Chart.js** | Data visualization |
| **Google Gemini AI** | AI-powered farming recommendations |
| **Laravel Breeze** | Authentication (email/password) |
| **Laravel Scheduler** | IoT sensor data simulation |

## Features

- **📊 Dashboard** — Real-time sensor cards (Temperature, Soil Moisture, Humidity, Light, Rainfall) with 30-second auto-refresh
- **🌱 Crop Management** — Full CRUD for crops with status tracking (active/harvested)
- **📡 Sensor Simulation** — Artisan command generating realistic IoT sensor data every minute
- **🤖 AI Insights** — Gemini AI-powered farming recommendations based on live sensor data
- **💬 AI Chat** — WhatsApp-style chat interface for farming Q&A with Gemini
- **🚨 Smart Alerts** — Automatic alerts for drought, heat stress, humidity, and rainfall anomalies
- **📈 Sensor History** — Filterable history with Chart.js visualization and data tables
- **🔐 Authentication** — Secure login/register with Laravel Breeze

## Setup Instructions

### Prerequisites
- PHP 8.2+ with MongoDB extension (`ext-mongodb`)
- Composer
- Node.js & npm
- MongoDB Atlas account (free tier)
- Google Gemini API key (free tier)

### Installation

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd LaravelProject

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Configure .env file
# Set your MONGODB_URI, MONGODB_DATABASE, and GEMINI_API_KEY

# 7. Build frontend assets
npm run build

# 8. Start the development server
php artisan serve
```

### Environment Variables

```env
DB_CONNECTION=mongodb
MONGODB_URI=mongodb+srv://username:password@cluster.mongodb.net/smart_farming?retryWrites=true&w=majority
MONGODB_DATABASE=smart_farming
GEMINI_API_KEY=your_gemini_api_key_here
```

### Running Sensor Simulation

```bash
# Run once manually
php artisan sensor:simulate

# Run scheduler (every minute)
php artisan schedule:work
```

## Project Structure

```
app/
├── Console/Commands/
│   └── SimulateSensorData.php      # IoT sensor data generator
├── Http/Controllers/
│   ├── Auth/                        # Laravel Breeze auth controllers
│   ├── DashboardController.php      # Main dashboard
│   ├── CropController.php           # Crop CRUD
│   ├── SensorController.php         # Sensor data API
│   ├── AlertController.php          # Alert management
│   ├── AiInsightController.php      # Gemini AI insights
│   └── ChatController.php           # AI chat
├── Models/
│   ├── User.php                     # MongoDB User model
│   ├── Crop.php                     # Crop model
│   ├── SensorReading.php            # Sensor data model
│   ├── Alert.php                    # Alert model
│   ├── AiInsight.php                # AI insight model
│   └── ChatMessage.php              # Chat message model
└── Services/
    └── GeminiService.php            # Gemini API wrapper

resources/views/
├── layouts/app.blade.php            # Main layout with sidebar
├── dashboard/index.blade.php        # Dashboard view
├── crops/                           # Crop views (index, create, edit, show)
├── alerts/index.blade.php           # Alerts view
├── chat/index.blade.php             # AI chat view
├── sensors/history.blade.php        # Sensor history view
└── welcome.blade.php                # Landing page
```

## MongoDB Collections

| Collection | Description |
|---|---|
| `users` | Farmer accounts with farm details |
| `crops` | Crop records with planting dates |
| `sensor_readings` | Simulated IoT sensor data |
| `alerts` | System-generated farming alerts |
| `ai_insights` | Gemini AI recommendations |
| `chat_messages` | AI chat conversation history |

## Alert Thresholds

| Condition | Alert Type | Message |
|---|---|---|
| Soil Moisture < 25% | 🔴 Danger | Irrigation needed urgently |
| Temperature > 40°C | 🟡 Warning | Heat stress warning |
| Humidity > 85% | 🟡 Warning | Fungal disease risk |
| Rainfall > 80mm | 🔵 Info | Check drainage |

## License

This project is created for educational purposes as a semester project.
