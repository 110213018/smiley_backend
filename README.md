# Smiley Backend

An AI-powered diary application backend that supports emotion analysis, chatbot interaction, and multi-sensory healing features.

## Prerequisites

- Python 3.10+  
- MySQL (with XAMPP or standalone installation)  
- Flutter SDK  
- Ollama (for local LLM serving)  

## Installation & Setup

1. **Clone the repository**  
   ```bash
   git clone https://github.com/110213018/smiley_backend
   ```
   > Place the project in your C:/xampp/htdocs/{directory}.
2. **Set up the database**
   Import smiley.sql into MySQL.
   ```bash
   mysql -u root -p your_database_name < smiley.sql
3. **Start Ollama local server**
   ```bash
   ollama serve
4. **Run the chatbot script (inside Chatbot_screen folder)**
   ```bash
   python TaiwanLLM_Chatbot_.py
5. **Run the backend**
   ```bash
   flask run
6. **Run the frontend**
   ```bash
   flutter run

## Project Structure
   ```bash
  smiley_backend/
  ├── Chatbot_screen/        # Chatbot scripts
  ├── backend/               # Flask backend
  ├── frontend/              # Flutter frontend
  ├── smiley.sql             # Database schema
  └── README.md              # Project documentation
