# CaptioMirror

**CaptioMirror** is an IoT-based smart mirror system integrated with an AI assistant. Designed to be user-centric, modular, and accessible, CaptioMirror enhances daily routines by providing a seamless voice-activated interface for information, reminders, and tasks.

---

##  Features

- **Voice-Activated AI Assistant**  
  Powered by a smart AI model (e.g., Mistral AI), CaptioMirror responds to voice commands using the Web Speech API for recognition and speech synthesis.

- **Modular Component Dashboard**  
  Easily add, remove, or update modules such as Weather, DateTime, Stock Prices, To-Do List, Quote of the Day, and Scheduler. Architecture supports future third-party extensions.

- **Real-time Updates & Personalized UX**  
  Offers dynamic data updates. User preferences (e.g., voice settings, displayed modules) are stored and adaptable based on user behavior.

- **Built with Accessible Design in Mind**  
  Focuses on inclusivity—especially for visually impaired and elderly users—with readable layouts, voice-based navigation, and clear UI components.

---

##  Architecture Overview

| Layer              | Details                                                                 |
|-------------------|-------------------------------------------------------------------------|
| **Hardware**       | Raspberry Pi 4 Model B (8GB), omnidirectional USB microphone, 32″ two-way glass monitor |
| **OS & Backend**   | Raspbian OS, MariaDB for data persistence, Apache2 (or Node.js) for hosting |
| **Frontend**       | Dashboard (PHP/Electron) with modules (e.g., `weather.script.js`, `todo.script.js`) |
| **AI Stack**       | Web Speech API, AI model (e.g., Mistral), voice synthesis with speechSynthesis |
| **Usability**      | Prototyped via Figma, supported by functional, use case, swimlane, ER diagrams|

---

##  Getting Started

###  Prerequisites
- Raspberry Pi 4 Model B (8GB)
- Raspbian OS installed
- 32″ two-way glass mirror monitor
- USB microphone and keyboard/mouse
- Installed software: MariaDB, Apache2 (or Node.js), Electron (optional)

###  Installation
1. **Clone the repository:**
    ```bash
    git clone https://github.com/yenzukai/CaptioMirror.git
    cd CaptioMirror
    ```

2. **Install dependencies:**
   - Backend: Set up MariaDB and Apache2 (or Node.js) following included `setup/` or `scripts/`.
   - Frontend: If using Electron, install modules like Web Speech API, Figma components, etc.

3. **Launch the dashboard:**
   - Start your web server or execute the Electron app.
   - Navigate to `http://localhost/views/index.php` (or launch your app).

4. **Sign in and configure:**
   - Use the built-in sign-in interface or create a new account.
   - Enable or disable modules via the mobile interface or dashboard settings.

---

##  Usage

- **Voice Commands**: Speak phrases like “What’s the weather?” or “Add a to-do item.”
- **Module Management**: Activate or deactivate modules (e.g., stock prices, quotes) through your dashboard preferences.
- **AI Assistant**: Provides spoken or displayed responses using text-to-speech and stored preferences.
- **Scheduled Tasks**: Set reminders through the To-Do or Scheduler modules, triggering notifications.

---

##  Roadmap

-  **Voice Improvements**: Enhance accent detection, reduce background noise interference.
-  **Predictive Insights**: Implement AI-based forecasts, habit prediction, and personalized suggestions.
-  **Expanded Modules**: Add integrations like News, Calendar, Health Tracking.
-  **Performance Enhancements**: Optimize backend data flows and caching for faster response and lower latency.
-  **Community Extensions**: Document API and UI guidelines for third-party module development.

---

##  Contributing

We welcome your contributions! Whether you’d like to improve accessibility, add features, or optimize performance, please:

1. Fork the repository  
2. Create a feature branch (`git checkout -b feature/name`)  
3. Commit your changes (`git commit -m "Add a new feature"`)  
4. Push to your branch (`git push origin feature/name`)  
5. Open a Pull Request to discuss improvements  

Ensure your contributions align with existing architecture patterns and update documentation as needed.

---

##  License

This project is released under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

##  Acknowledgments & References

Thanks to user feedback, iterative prototyping, and comprehensive evaluations aligned with ISO/IEC 25010 standards, CaptioMirror has evolved into a functional, intuitive, and reliable system. Special thanks to the open-source community, IoT forums, and smart mirror developers for inspiration and guidance.

---

Feel free to modify any parts or let me know if you’d like specific logos, screenshots, or additional details included!
::contentReference[oaicite:0]{index=0}
