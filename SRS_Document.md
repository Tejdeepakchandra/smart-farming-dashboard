# Smart Farming Dashboard with IoT Integration

## Software Requirements Specification

**Course Code:** [Your Course Code]
**Course Name:** [Your Course Name]

**Student Names:** [Your Names]
**Student Registration Numbers:** [Your Reg Numbers]

**Prepared for:** Continuous Assessment 3 — Spring 2025

---

## REVISION HISTORY

| Date | Version | Description | Author |
|------|---------|-------------|--------|
| 18-05-2025 | 1.0 | Initial SRS Document | [Student Names] |

---

## Table of Contents

1. Introduction
2. General Description
3. Specific Requirements
4. Analysis Models
5. GitHub Link
6. Deployed Link
7–11. Proof Documents
A. Appendices

---

## 1. INTRODUCTION

### 1.1 Purpose

This Software Requirements Specification (SRS) document provides a complete and detailed description of the **Smart Farming Dashboard with IoT Integration** web application. The purpose of this document is to define the functional and non-functional requirements, system architecture, data flow, and user interface specifications for the application.

The intended audience includes:
- **Project Evaluator / Faculty:** To assess the completeness and technical soundness of the project
- **Development Team:** To serve as the reference guide during implementation and testing
- **Stakeholders:** To understand the system capabilities and constraints

### 1.2 Scope

**Product Name:** Smart Farming Dashboard with IoT Integration

**What the software will do:**
- Provide a web-based dashboard for farmers to monitor crop health using simulated IoT sensor data (temperature, soil moisture, humidity, light intensity, rainfall)
- Support crop-specific sensor profiles — each crop type (Rice, Wheat, Tomato, Corn, Potato, Sugarcane, Cotton, Soybean) has unique ideal sensor value ranges
- Simulate IoT sensor data generation through the web interface (no physical hardware or terminal commands required)
- Automatically compare sensor readings against crop-specific ideal ranges and display health status in farmer-friendly language (e.g., "Too Hot", "Needs Water", "Perfect")
- Provide AI-powered farming recommendations using Google Gemini API based on real-time sensor snapshots
- Offer an AI chatbot for farmers to ask farming-related questions in natural language
- Generate automatic alerts when sensor readings exceed safe thresholds (drought, heat stress, fungal disease risk, heavy rainfall)
- Visualize sensor trends using interactive Chart.js line graphs

**What the software will NOT do:**
- Connect to real physical IoT sensors or hardware devices (this is a simulation-based project)
- Provide weather forecast integration from external APIs
- Support multi-language localization (English only in current version)

**Benefits and Objectives:**
- Enable farmers to make data-driven decisions by providing an intuitive, visual dashboard
- Reduce crop loss by alerting farmers when environmental conditions go outside ideal ranges
- Provide free, accessible AI-powered farming advice using the Gemini free tier
- Demonstrate a full-stack MVC web application with NoSQL database integration

### 1.3 Definitions, Acronyms, and Abbreviations

| Term | Definition |
|------|-----------|
| IoT | Internet of Things — network of physical devices with sensors |
| SRS | Software Requirements Specification |
| MVC | Model-View-Controller — software architectural pattern |
| CRUD | Create, Read, Update, Delete — basic data operations |
| API | Application Programming Interface |
| AI | Artificial Intelligence |
| MongoDB | A NoSQL document-oriented database |
| Blade | Laravel's templating engine for rendering HTML views |
| Tailwind CSS | A utility-first CSS framework |
| Chart.js | JavaScript library for creating interactive charts |
| Gemini API | Google's generative AI API (gemini-2.0-flash model) |
| Sensor Reading | A single data point from IoT sensors (simulated) |
| Ideal Range | The optimal min-max sensor values for a specific crop |
| Alert | An automated notification triggered when sensor values exceed safe thresholds |
| CSRF | Cross-Site Request Forgery — a web security vulnerability |

### 1.4 References

1. IEEE Std 830-1998 — IEEE Recommended Practice for Software Requirements Specifications
2. Laravel 12 Official Documentation — https://laravel.com/docs/12.x
3. MongoDB Laravel Package — https://github.com/mongodb/laravel-mongodb
4. Google Gemini API Documentation — https://ai.google.dev/docs
5. Chart.js Documentation — https://www.chartjs.org/docs/latest/
6. Tailwind CSS Documentation — https://tailwindcss.com/docs
7. Laravel Breeze Authentication — https://laravel.com/docs/12.x/starter-kits

### 1.5 Overview

This SRS document is organized as follows:
- **Section 2 (General Description):** Provides product context, user characteristics, constraints, and assumptions
- **Section 3 (Specific Requirements):** Details all functional requirements (features), non-functional requirements, interface requirements, and design constraints
- **Section 4 (Analysis Models):** Includes Data Flow Diagrams (DFDs) showing system data movement
- **Sections 5–11:** Project links and proof documents
- **Appendix A:** MongoDB collection schemas and supplementary information

---

## 2. GENERAL DESCRIPTION

### 2.1 Product Perspective

The Smart Farming Dashboard is a **standalone web application** designed as a semester project to demonstrate modern full-stack development concepts. It operates independently and does not integrate with existing farm management systems.

The system simulates an IoT-based farming environment where:
1. **Sensors** (simulated) generate environmental data for each crop
2. **Dashboard** displays real-time sensor health with farmer-friendly indicators
3. **AI Engine** (Google Gemini) analyzes sensor data and provides crop-specific recommendations
4. **Alert System** automatically monitors for dangerous conditions

**System Architecture Overview:**

```
┌─────────────────────────────────────────────────┐
│                   CLIENT (Browser)               │
│  Blade Templates + Tailwind CSS + Chart.js       │
└──────────────────────┬──────────────────────────┘
                       │ HTTP Requests
┌──────────────────────▼──────────────────────────┐
│              LARAVEL 12 (MVC Backend)            │
│  ┌──────────┐ ┌────────────┐ ┌───────────────┐  │
│  │ Routes   │→│Controllers │→│  Blade Views  │  │
│  └──────────┘ └──────┬─────┘ └───────────────┘  │
│                      │                           │
│  ┌──────────┐ ┌──────▼─────┐ ┌───────────────┐  │
│  │ Services │ │   Models   │ │ Auth (Breeze)  │  │
│  │(Gemini,  │ │(MongoDB    │ │               │  │
│  │Simulator)│ │ Eloquent)  │ │               │  │
│  └──────────┘ └──────┬─────┘ └───────────────┘  │
└──────────────────────┼──────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│           MONGODB ATLAS (Cloud Database)         │
│  Collections: users, crops, sensor_readings,     │
│  alerts, ai_insights, chat_messages              │
└─────────────────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────┐
│          GOOGLE GEMINI API (External)            │
│  Model: gemini-2.0-flash (Free Tier)             │
└─────────────────────────────────────────────────┘
```

### 2.2 Product Functions

The system provides the following major functions:

| # | Function | Description |
|---|----------|-------------|
| F1 | User Authentication | Register/Login with farm name and location |
| F2 | Crop Management | Full CRUD for crops with 8 preset crop types |
| F3 | IoT Sensor Simulation | Web-based sensor data generation with crop-specific profiles |
| F4 | Real-time Dashboard | Sensor health cards with farmer-friendly status labels |
| F5 | Sensor Trend Charts | Interactive Chart.js graphs showing 24-hour sensor trends |
| F6 | Smart Alerts | Automatic alerts for dangerous conditions (drought, heat, etc.) |
| F7 | AI Insights | Gemini-powered crop-specific recommendations |
| F8 | AI Chat | Natural language chatbot for farming questions |
| F9 | Sensor History | Filterable data table and chart of past readings |
| F10 | Auto Data Generation | 20 initial readings generated when creating a new crop |

### 2.3 User Characteristics

**Primary User: Farmer**
- May have limited technical knowledge
- Needs simple, visual indicators instead of raw numerical data
- Uses mobile phones or basic laptops to access the dashboard
- Understands farming terminology (soil moisture, humidity, planting dates)
- Expects information in plain language (e.g., "Needs Water!" instead of "Below ideal moisture threshold")

**Secondary User: Agricultural Advisor / Teacher**
- Has technical understanding of farming parameters
- May use the system to demonstrate IoT and AI concepts
- Evaluates the project for academic assessment

### 2.4 General Constraints

1. **No Physical Hardware:** The project uses simulated sensor data; no actual IoT devices are connected
2. **API Rate Limits:** Google Gemini free tier allows approximately 15 requests/minute; excessive usage triggers HTTP 429 errors
3. **Internet Required:** MongoDB Atlas and Gemini API require active internet connectivity
4. **Browser Dependency:** The application requires a modern browser supporting JavaScript ES6+ and CSS3
5. **Single User per Session:** The system does not support concurrent multi-device sessions for the same user
6. **English Only:** All UI text and AI responses are in English

### 2.5 Assumptions and Dependencies

**Assumptions:**
1. The user has a modern web browser (Chrome, Firefox, Edge, Safari)
2. The deployment server has PHP 8.2+ with the MongoDB PHP extension installed
3. The MongoDB Atlas cluster is accessible from the deployment server (IP whitelisting configured)
4. The Gemini API key is valid and has not exceeded its daily quota
5. Users have basic internet connectivity

**Dependencies:**
1. **MongoDB Atlas** — Cloud database service (free M0 tier)
2. **Google Gemini API** — AI model service (free tier, gemini-2.0-flash)
3. **Laravel 12 Framework** — PHP backend framework
4. **Composer** — PHP dependency manager
5. **Node.js & npm** — Frontend asset compilation
6. **Tailwind CSS CDN** — Styling framework (loaded via CDN in production)
7. **Chart.js CDN** — Charting library (loaded via CDN)

---

## 3. SPECIFIC REQUIREMENTS

### 3.1 External Interface Requirements

#### 3.1.1 User Interfaces

The application provides the following user interfaces:

| Screen | Description |
|--------|-------------|
| **Landing Page** | Public page with hero section, feature cards, crop type showcase, tech stack badges, and login/register CTAs |
| **Registration** | Form with Name, Email, Farm Name, Location, Password fields |
| **Login** | Email + Password authentication form |
| **Dashboard** | Main screen with welcome banner, sensor health cards (5 cards with gauge bars), Chart.js trend graph, AI insight panel, and recent alerts |
| **Crops List** | Grid of crop cards showing name, emoji, field, status, live sensor snapshot, and CRUD actions |
| **Add Crop** | Visual crop type picker (8 preset emojis), form fields for name, field, dates, with auto-data generation |
| **Crop Detail** | Crop info, ideal vs actual comparison grid, 24h sensor chart |
| **Edit Crop** | Pre-filled form for updating crop details and status |
| **Sensor History** | Date/crop filters, Chart.js graph, and color-coded data table |
| **Alerts** | List of alerts with emoji severity (🚨/⚠️/ℹ️), mark-as-read functionality |
| **AI Chat** | WhatsApp-style interface with quick-ask suggestion buttons and typing indicator |
| **Profile** | User profile management (inherited from Laravel Breeze) |

**UI Design Principles:**
- Dark theme with gradient backgrounds for reduced eye strain
- Nunito font family for readability
- Large emojis as visual identifiers (🌾🌿🍅🌽🥔🎋☁️🫘)
- Color-coded health status: Green = Good, Amber = Warning, Red = Danger
- Farmer-friendly labels: "Too Hot", "Needs Water", "Perfect" instead of technical terms

#### 3.1.2 Hardware Interfaces

The system does not interface with any physical hardware. IoT sensor data is simulated through the `SensorSimulatorService` class, which generates randomized values based on crop-specific profiles. The simulation is triggered via web buttons on the dashboard — no terminal access required.

#### 3.1.3 Software Interfaces

| Software | Interface Type | Purpose |
|----------|---------------|---------|
| MongoDB Atlas | Database Driver (mongodb/laravel-mongodb) | Store all application data in 6 collections |
| Google Gemini API | REST API (HTTP POST) | Generate AI farming recommendations and chat responses |
| Laravel Breeze | Authentication Package | Provide email/password login, registration, and session management |
| Chart.js | JavaScript Library (CDN) | Render interactive line charts for sensor trends |
| Tailwind CSS | CSS Framework (CDN) | Style all UI components |

#### 3.1.4 Communications Interfaces

- **HTTP/HTTPS:** All client-server communication uses HTTP (development) or HTTPS (production)
- **AJAX/Fetch API:** Dashboard sensor simulation, AI insight generation, and chat messages use asynchronous JavaScript fetch() calls
- **MongoDB Wire Protocol:** Laravel communicates with MongoDB Atlas over TLS-encrypted connections
- **Gemini REST API:** HTTPS POST requests to `generativelanguage.googleapis.com` with JSON payload

### 3.2 Functional Requirements

#### 3.2.1 User Registration and Authentication (FR-01)

**Introduction:** Users must register with farm-specific details and authenticate before accessing the dashboard.

**Inputs:**
- Registration: Name, Email, Password, Confirm Password, Farm Name, Location
- Login: Email, Password

**Processing:**
- Validate all input fields (server-side Laravel validation)
- Hash password using bcrypt (12 rounds)
- Create user document in MongoDB `users` collection
- Create authenticated session using Laravel Breeze

**Outputs:**
- Successful registration → Redirect to Dashboard
- Successful login → Redirect to Dashboard
- Validation error → Display inline error messages

**Error Handling:**
- Duplicate email → "The email has already been taken"
- Weak password → Password strength requirements displayed
- Invalid credentials → "These credentials do not match our records"

#### 3.2.2 Crop Management — CRUD (FR-02)

**Introduction:** Farmers can add, view, edit, and delete crops. Each crop has a type that determines its ideal sensor ranges.

**Inputs:**
- Crop Name (from 8 presets or custom), Field Name, Planting Date, Expected Harvest Date, Status

**Processing:**
- On CREATE: Store crop in MongoDB, auto-generate 20 initial sensor readings using crop-specific profile
- On READ: Fetch crops for authenticated user with latest sensor reading attached
- On UPDATE: Validate and update crop document
- On DELETE: Remove crop and all associated sensor readings

**Outputs:**
- Crop list page with live sensor mini-dashboards per crop
- Crop detail page with ideal vs actual comparison
- Success/error flash messages

**Error Handling:**
- Ownership check: Users can only access their own crops (user_id filter)
- Validation errors displayed inline

#### 3.2.3 IoT Sensor Data Simulation (FR-03)

**Introduction:** Since no physical IoT hardware is available, the system simulates sensor data through web-based buttons. Each crop type has unique ideal ranges.

**Inputs:**
- "New Reading" button → generates 1 reading for all active crops
- "Fill 24h Data" button → generates 20 readings spread over past hours
- Crop-specific request with crop_id parameter

**Processing:**
- `SensorSimulatorService` looks up the crop's ideal profile
- 70% of generated values fall within ideal range, 30% drift to extremes
- Values stored in `sensor_readings` collection with timestamp
- Alert thresholds checked after each reading

**Crop-Specific Sensor Profiles:**

| Crop | Temp (°C) | Moisture (%) | Humidity (%) | Light (lux) | Rain (mm) |
|------|-----------|-------------|-------------|-------------|-----------|
| Rice | 25–30 | 70–85 | 70–80 | 20K–40K | 15–40 |
| Wheat | 18–24 | 40–55 | 45–60 | 25K–45K | 5–20 |
| Tomato | 21–27 | 50–65 | 50–65 | 30K–55K | 5–15 |
| Corn | 24–32 | 50–70 | 55–70 | 30K–50K | 10–25 |
| Potato | 15–22 | 60–75 | 65–80 | 18K–35K | 8–20 |
| Sugarcane | 27–35 | 65–80 | 65–80 | 35K–60K | 15–35 |
| Cotton | 25–35 | 40–55 | 40–55 | 40K–65K | 5–20 |
| Soybean | 22–30 | 50–65 | 55–70 | 25K–40K | 8–25 |

**Outputs:**
- JSON response with success status, message, and generated data count
- Dashboard auto-refreshes to show updated sensor cards and chart

**Error Handling:**
- No active crops → "No active crops found. Add a crop first!"
- Crop not found → "Crop not found or not active"

#### 3.2.4 Real-time Dashboard Display (FR-04)

**Introduction:** The dashboard shows a farmer-friendly overview of crop health with visual indicators.

**Inputs:** Authenticated user session

**Processing:**
- Fetch latest sensor reading for the user's active crop
- Compare each sensor value against the crop's ideal range
- Determine status: Good (within ideal), Low (below ideal_min), High (above ideal_max)
- Calculate gauge bar percentage based on value position in min–max range

**Outputs:**
- 5 sensor health cards with: emoji icon, large value, farmer-friendly status label, gauge bar, ideal range text
- Status labels: "Perfect"/"Too Cold"/"Too Hot" (temperature), "Good"/"Needs Water"/"Too Wet" (moisture), etc.
- Color coding: Green = within range, Amber/Blue = below range, Red = above range

#### 3.2.5 Smart Alert System (FR-05)

**Introduction:** Automatic alerts are triggered when sensor values exceed safe thresholds.

**Inputs:** Sensor readings generated by simulation

**Processing:**
- Low moisture alert: triggers when soil_moisture < 60% of crop's ideal_min
- High temperature alert: triggers when temperature > 115% of crop's ideal_max
- High humidity alert: triggers when humidity > 110% of crop's ideal_max
- Heavy rainfall alert: triggers when rainfall > 200% of crop's ideal_max
- Cooldown: Same alert type won't re-trigger for 2 hours

**Outputs:**
- Alert stored in `alerts` collection with type (danger/warning/info), message, and timestamp
- Unread count badge on navigation bell icon
- Alert list page with mark-as-read and mark-all-read actions

#### 3.2.6 AI-Powered Insights (FR-06)

**Introduction:** Google Gemini AI analyzes current sensor data and provides crop-specific recommendations.

**Inputs:** "Ask AI" button click on dashboard

**Processing:**
- Fetch latest sensor reading for active crop
- Build detailed prompt including: current sensor values, crop name, and ideal ranges
- Send POST request to Gemini API (gemini-2.0-flash model)
- Parse response and store in `ai_insights` collection

**Outputs:**
- 3 short, actionable recommendations displayed in the AI panel
- Stored insight linked to crop_id with sensor snapshot for historical reference

**Error Handling:**
- No sensor data → "No sensor data available. Click New Reading first!"
- API rate limit (HTTP 429) → "AI service returned error. Please try again."
- No internet → "Failed to connect to AI service"

#### 3.2.7 AI Farming Chatbot (FR-07)

**Introduction:** Interactive chat interface where farmers can ask any farming-related question.

**Inputs:** Text message typed by user (max 1000 characters)

**Processing:**
- Build prompt with farming-assistant persona and rules
- Send to Gemini API, receive response
- Store both message and response in `chat_messages` collection
- Display in real-time with typing indicator animation

**Outputs:**
- AI response displayed in WhatsApp-style chat bubble
- Quick-ask suggestion buttons for common questions
- Chat history persisted across sessions

**Error Handling:**
- Non-farming questions → AI redirects: "I specialize in farming topics..."
- API errors → Error message displayed in chat
# SRS Part 2 — Smart Farming Dashboard (Sections 3.5 onwards)

---

## 3.5 Non-Functional Requirements

### 3.5.1 Performance

| Requirement | Metric |
|-------------|--------|
| NFR-P1 | Dashboard page shall load within 3 seconds on a stable internet connection |
| NFR-P2 | Sensor simulation (single reading) shall complete within 1 second |
| NFR-P3 | Batch simulation (20 readings) shall complete within 5 seconds |
| NFR-P4 | Chart.js graph shall render within 2 seconds for up to 500 data points |
| NFR-P5 | AI insight generation shall complete within 30 seconds (dependent on Gemini API latency) |
| NFR-P6 | Alert badge count shall update within 30 seconds via polling |

### 3.5.2 Reliability

| Requirement | Metric |
|-------------|--------|
| NFR-R1 | The system shall handle Gemini API failures gracefully with user-friendly error messages |
| NFR-R2 | MongoDB connection failures shall not crash the application; fallback error pages shall be shown |
| NFR-R3 | Alert cooldown mechanism prevents duplicate alerts within 2-hour windows |
| NFR-R4 | All form submissions include CSRF token validation to prevent forged requests |

### 3.5.3 Availability

| Requirement | Metric |
|-------------|--------|
| NFR-A1 | The application shall be available 99% uptime when deployed on a hosting platform |
| NFR-A2 | MongoDB Atlas free tier provides 99.95% uptime SLA |
| NFR-A3 | Gemini API free tier has rate limits of ~15 requests/minute; system handles 429 errors gracefully |

### 3.5.4 Security

| Requirement | Metric |
|-------------|--------|
| NFR-S1 | All passwords shall be hashed using bcrypt with 12 rounds |
| NFR-S2 | All authenticated routes shall be protected by Laravel's `auth` middleware |
| NFR-S3 | Each user can only access their own data (user_id ownership checks on all queries) |
| NFR-S4 | All forms include CSRF token protection |
| NFR-S5 | MongoDB Atlas connection uses TLS encryption |
| NFR-S6 | Gemini API key stored in environment variables, never exposed to frontend |
| NFR-S7 | Session-based authentication with configurable timeout (120 minutes default) |

### 3.5.5 Maintainability

| Requirement | Metric |
|-------------|--------|
| NFR-M1 | Code follows Laravel MVC architecture — Models, Views, and Controllers are separated |
| NFR-M2 | Business logic for sensor simulation is encapsulated in `SensorSimulatorService` |
| NFR-M3 | AI communication is encapsulated in `GeminiService` — switching AI providers requires changing only one file |
| NFR-M4 | Crop-specific sensor profiles are defined as arrays in the service class — adding a new crop requires only adding a new array entry |
| NFR-M5 | All views use Blade template inheritance (`@extends('layouts.app')`) for consistent layout |

### 3.5.6 Portability

| Requirement | Metric |
|-------------|--------|
| NFR-PO1 | The application runs on any server with PHP 8.2+ and the MongoDB extension |
| NFR-PO2 | Frontend uses CDN-loaded Tailwind CSS and Chart.js — no build step required for deployment |
| NFR-PO3 | The application is browser-agnostic — works on Chrome, Firefox, Edge, Safari |
| NFR-PO4 | Responsive design supports mobile, tablet, and desktop viewports |

---

## 3.7 Design Constraints

1. **Framework:** Must use Laravel 12 with MVC architecture (as per project requirements)
2. **Database:** Must use MongoDB (via mongodb/laravel-mongodb package) — not SQL databases
3. **Authentication:** Must use Laravel Breeze for authentication (simple email/password)
4. **AI Model:** Must use Gemini API free tier (gemini-2.0-flash) — no paid AI services
5. **No Physical IoT:** Sensor data must be simulated (no Arduino, Raspberry Pi, etc.)
6. **Frontend:** Must use Blade templating — no SPA frameworks (React, Vue)
7. **Styling:** Tailwind CSS for responsive design

---

## 3.9 Other Requirements

1. The application must work without running any terminal commands after deployment — all sensor simulation is web-based
2. The system should auto-generate 20 initial sensor readings when a new crop is created
3. Chart.js graphs should be interactive (hover tooltips, clickable legend)
4. The landing page must be publicly accessible without authentication
5. The sidebar navigation must highlight the currently active page

---

## 4. ANALYSIS MODELS

### 4.1 Data Flow Diagrams (DFD)

#### Level 0 — Context Diagram

```
                    ┌──────────────┐
    Registration    │              │    Dashboard Data
    Login Info  ───►│              │───► Sensor Cards
    Crop Data   ───►│   SMART      │───► Charts
    Simulate Req───►│   FARMING    │───► Alerts
    Chat Message───►│   DASHBOARD  │───► AI Insights
    AI Request  ───►│              │───► Chat Response
                    │              │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
        ┌──────────┐ ┌──────────┐ ┌──────────┐
        │ MongoDB  │ │ Gemini   │ │ Browser  │
        │ Atlas    │ │ API      │ │ (User)   │
        └──────────┘ └──────────┘ └──────────┘
```

#### Level 1 — System DFD

```
┌─────────────────────────────────────────────────────────────────┐
│                        SMART FARMING SYSTEM                      │
│                                                                  │
│  ┌─────────┐    ┌──────────────┐    ┌─────────────────┐         │
│  │  USER   │───►│ 1.0 AUTH     │───►│  users           │         │
│  │(Farmer) │    │ (Register/   │    │  collection      │         │
│  │         │    │  Login)      │    │  (MongoDB)       │         │
│  └────┬────┘    └──────────────┘    └─────────────────┘         │
│       │                                                          │
│       │         ┌──────────────┐    ┌─────────────────┐         │
│       ├────────►│ 2.0 CROP     │◄──►│  crops           │         │
│       │         │ MANAGEMENT   │    │  collection      │         │
│       │         │ (CRUD)       │    │                  │         │
│       │         └──────┬───────┘    └─────────────────┘         │
│       │                │                                         │
│       │         ┌──────▼───────┐    ┌─────────────────┐         │
│       ├────────►│ 3.0 SENSOR   │───►│  sensor_readings │         │
│       │         │ SIMULATION   │    │  collection      │         │
│       │         │ (Generate)   │    │                  │         │
│       │         └──────┬───────┘    └─────────────────┘         │
│       │                │                                         │
│       │         ┌──────▼───────┐    ┌─────────────────┐         │
│       │         │ 4.0 ALERT    │───►│  alerts          │         │
│       │         │ ENGINE       │    │  collection      │         │
│       │         │ (Check       │    │                  │         │
│       │         │  Thresholds) │    └─────────────────┘         │
│       │         └──────────────┘                                 │
│       │                                                          │
│       │         ┌──────────────┐    ┌─────────────────┐         │
│       ├────────►│ 5.0 AI       │───►│  ai_insights     │         │
│       │         │ INSIGHTS     │    │  collection      │         │
│       │         │ (Gemini API) │    │                  │         │
│       │         └──────────────┘    └─────────────────┘         │
│       │                                                          │
│       │         ┌──────────────┐    ┌─────────────────┐         │
│       └────────►│ 6.0 AI CHAT  │───►│  chat_messages   │         │
│                 │ (Gemini API) │    │  collection      │         │
│                 └──────────────┘    └─────────────────┘         │
│                                                                  │
│       ┌──────────────┐                                           │
│       │ 7.0 DASHBOARD│◄── Reads from all collections above      │
│       │ (Aggregate   │───► Renders sensor cards, charts,         │
│       │  & Display)  │     alerts, AI panel to user              │
│       └──────────────┘                                           │
└─────────────────────────────────────────────────────────────────┘
```

#### Level 2 — Sensor Simulation Process (Process 3.0)

```
┌────────┐   Simulate Request    ┌───────────────────┐
│  User  │──────────────────────►│ 3.1 Get Crop      │
│(Button)│                       │ Profile            │
└────────┘                       └────────┬──────────┘
                                          │ Crop Name
                                 ┌────────▼──────────┐
                                 │ 3.2 Lookup Ideal   │
                                 │ Ranges             │
                                 │ (SensorSimulator   │
                                 │  Service)          │
                                 └────────┬──────────┘
                                          │ Profile Data
                                 ┌────────▼──────────┐
                                 │ 3.3 Generate       │
                                 │ Random Values      │
                                 │ (70% ideal,        │
                                 │  30% extreme)      │
                                 └────────┬──────────┘
                                          │ Sensor Values
                              ┌───────────┼───────────┐
                              ▼                       ▼
                    ┌─────────────────┐    ┌──────────────────┐
                    │ 3.4 Store in    │    │ 3.5 Check Alert  │
                    │ sensor_readings │    │ Thresholds       │
                    │ (MongoDB)       │    │ (Create alert    │
                    └─────────────────┘    │  if exceeded)    │
                                          └──────────────────┘
```

---

## 5. GITHUB LINK

[Insert your GitHub repository URL here]

Example: `https://github.com/yourusername/smart-farming-dashboard`

---

## 6. DEPLOYED LINK

[Insert your deployed application URL here]

Example: `https://smart-farming.your-domain.com`

---

## 7–11. PROOF DOCUMENTS

[Insert screenshots/scans of Client Approval, Location Proof, Transaction ID, Email Acknowledgement, and GST No. as required by your institution]

---

## A. APPENDICES

### A.1 MongoDB Collection Schemas

#### users Collection
```json
{
  "_id": "ObjectId",
  "name": "string",
  "email": "string (unique)",
  "password": "string (bcrypt hashed)",
  "farm_name": "string",
  "location": "string",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

#### crops Collection
```json
{
  "_id": "ObjectId",
  "user_id": "string (ref: users._id)",
  "name": "string (e.g., Rice, Wheat, Tomato)",
  "field_name": "string",
  "planting_date": "datetime",
  "expected_harvest_date": "datetime",
  "status": "string (active | harvested)",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

#### sensor_readings Collection
```json
{
  "_id": "ObjectId",
  "user_id": "string (ref: users._id)",
  "crop_id": "string (ref: crops._id)",
  "temperature": "float (°C)",
  "soil_moisture": "float (%)",
  "humidity": "float (%)",
  "light_intensity": "integer (lux)",
  "rainfall": "float (mm)",
  "recorded_at": "datetime"
}
```

#### alerts Collection
```json
{
  "_id": "ObjectId",
  "user_id": "string (ref: users._id)",
  "type": "string (danger | warning | info)",
  "message": "string",
  "is_read": "boolean (default: false)",
  "triggered_at": "datetime",
  "created_at": "datetime"
}
```

#### ai_insights Collection
```json
{
  "_id": "ObjectId",
  "user_id": "string (ref: users._id)",
  "crop_id": "string (ref: crops._id)",
  "sensor_snapshot": "object (temperature, soil_moisture, etc.)",
  "prompt_sent": "string",
  "ai_response": "string",
  "created_at": "datetime"
}
```

#### chat_messages Collection
```json
{
  "_id": "ObjectId",
  "user_id": "string (ref: users._id)",
  "message": "string (user's question)",
  "response": "string (AI's answer)",
  "created_at": "datetime"
}
```

### A.2 Project File Structure

```
app/
├── Console/Commands/SimulateSensorData.php    # CLI simulator (backup)
├── Http/Controllers/
│   ├── DashboardController.php                # Main dashboard
│   ├── CropController.php                     # Crop CRUD
│   ├── SensorController.php                   # Sensor API + web simulation
│   ├── AlertController.php                    # Alert management
│   ├── AiInsightController.php                # Gemini AI insights
│   └── ChatController.php                     # AI chat
├── Models/
│   ├── User.php, Crop.php, SensorReading.php
│   ├── Alert.php, AiInsight.php, ChatMessage.php
└── Services/
    ├── GeminiService.php                      # Gemini API wrapper
    └── SensorSimulatorService.php             # Crop-specific simulation

resources/views/
├── layouts/app.blade.php                      # Sidebar layout
├── welcome.blade.php                          # Landing page
├── dashboard/index.blade.php                  # Dashboard
├── crops/ (index, create, edit, show)         # Crop views
├── sensors/history.blade.php                  # Sensor history
├── alerts/index.blade.php                     # Alerts
└── chat/index.blade.php                       # AI chat
```

### A.3 Technology Stack Summary

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend Framework | Laravel | 12 |
| Language | PHP | 8.2+ |
| Database | MongoDB Atlas | 7.x (M0 Free Tier) |
| MongoDB Driver | mongodb/laravel-mongodb | Latest |
| Authentication | Laravel Breeze | Latest |
| AI Service | Google Gemini API | gemini-2.0-flash |
| Frontend Templating | Laravel Blade | Built-in |
| CSS Framework | Tailwind CSS | 3.x (CDN) |
| Charts | Chart.js | 4.x (CDN) |
| Font | Google Fonts — Nunito | — |

---

*End of SRS Document*
