# Aktivitetskalender Plugin

A modular WordPress plugin for managing and displaying activities with calendar and list views.

## Features

- 📅 Interactive calendar view with month navigation
- 📋 List view of all activities
- 🔀 Toggle between calendar and list views
- 🎨 Responsive design
- 🏷️ Activity status indicators (Ledig, Fullt, Lukket)
- 🔗 Direct links to activities
- 📱 Mobile-friendly

## Project Structure

```
ms-aktivitetskalender/
├── ms-aktivitetskalender.php     # Main plugin file
├── includes/
│   ├── cpt.php                   # Custom Post Type
│   ├── acf.php                   # ACF Field Groups
│   ├── helpers.php               # Utility functions
│   ├── calendar.php              # Calendar rendering logic
│   └── shortcodes.php            # Shortcode definitions
├── assets/
│   ├── css/
│   │   └── style.css             # Plugin styles
│   └── js/
│       └── calendar.js           # Plugin JavaScript
└── README.md
```

## Installation

1. Upload the `ms-aktivitetskalender` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Ensure ACF Pro is installed for field groups

## Usage

### Shortcodes

**Calendar View:**
```
[ms_calendar]
```

**List View:**
```
[ms_activities]
```

**Toggle View (List + Calendar with switcher):**
```
[ms_activities_toggle]
```

## Dependencies

- WordPress 5.0+
- Advanced Custom Fields (ACF) Pro

## Version

5.0 - Modular structure with separate assets and CSS

## Author

Creato Design AS