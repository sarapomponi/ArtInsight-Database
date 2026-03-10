# 🏛️ ArtInsight: Exhibition Trends Analytics Database

**ArtInsight** is a custom relational database designed and developed to overcome the fragmentation of traditional physical art archives. It transforms passive historical data into an active research tool, allowing curators and Digital Humanities scholars to interactively explore exhibition trends and international loan networks.

## 🎯 Context & Objectives
This project was developed as my Bachelor's thesis in Art History at Roma Tre University. The main goal is to aggregate heterogeneous art-historical information into a single, remotely accessible platform, solving the issue of institutional isolation and the physical limits of traditional paper catalogs.

## 💻 Tech Stack & Architecture
The environment was built locally using the **WAMP** stack:
* **Backend:** MySQL for relational data structuring (eliminating data redundancy).
* **Frontend:** Dynamic web pages generated in real-time using PHP.
* **UI/UX:** Custom CSS layout designed to reduce cognitive load and highlight artworks.
* **Data Visualization:** Interactive bar and pie charts rendered via Chart.js.

## 📁 Repository Structure
* `index.php` / `archive.php` / `artwork.php` / `author.php` / `exhibition.php`: Core frontend pages for dynamic navigation and hypertextual exploration simulating a physical museum layout.
* `admin.php`: An independent admin panel with summary metrics and guided insertion forms (dropdowns, multi-select checkboxes) to prevent data entry errors.
* `analisi_nazionalita.php` / `prestiti_per_paese.php`: Specific modules for historiographical and geopolitical data extraction.
* `mostre.sql`: The database dump containing the Entity-Relationship structure and sample data.

## ⚙️ Key Technical Features
* **Complex Relational Modeling:** Built on an Entity-Relationship conceptual model centered around a Many-to-Many (N:M) relationship between Exhibition and Artwork.
* **Dynamic Search Engine:** Queries multiple tables simultaneously using SQL `UNION` and `LIKE` operators with wildcards to guarantee relevant results even from partial keywords.
* **Data Aggregation:** Uses `GROUP BY` queries to calculate thematic frequencies beyond simple chronological reading.
* **Geopolitical Mapping:** Utilizes `INNER JOIN` clauses to identify lending institutions and map the geopolitics of international loans.

## 🚀 How to Run Locally
1. Install a local server environment like XAMPP or WAMP.
2. Clone this repository into your `htdocs` (XAMPP) or `www` (WAMP) folder.
3. Open phpMyAdmin and create a new database named `mostre`.
4. Import the `mostre.sql` file into the newly created database.
5. Access the project via your browser at `http://localhost/ArtInsight-Database` (adjust the folder name as needed).

---
*Created by Sara Pomponi - Digital Humanities Master's Student @ Tor Vergata | BA Art History @ Roma Tre.*
