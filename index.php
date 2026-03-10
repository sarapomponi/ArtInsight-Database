<?php
// Connessione DB
$conn = new mysqli('localhost', 'root', '', 'mostre');
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$query = '';
$results = [];
$searching = false;

// Controllo parametro per mostrare "Modifica Mostre"
$showModifica = isset($_GET['modifica']) && $_GET['modifica'] == '1';

// 📊 STATISTICHE
$stats = [];
$sql_stats = "
    SELECT 
        (SELECT COUNT(*) FROM mostra) as totale_mostre,
        (SELECT COUNT(*) FROM mostra WHERE data_fine >= CURDATE()) as mostre_attive,
        (SELECT COUNT(DISTINCT id_autore) FROM opera WHERE id_autore IS NOT NULL) as totale_artisti,
        (SELECT COUNT(*) FROM opera) as totale_opere
";
$result_stats = $conn->query($sql_stats);
if ($result_stats) {
    $stats = $result_stats->fetch_assoc();
}

// 🏛️ MOSTRE IN EVIDENZA (ultime 3 attive)
$mostre_evidenza = [];
$sql_evidenza = "
    SELECT m.*, COUNT(e.id_opera) as num_opere
    FROM mostra m
    LEFT JOIN esposizione e ON m.id = e.id_mostra
    WHERE m.data_fine >= CURDATE()
    GROUP BY m.id
    ORDER BY m.data_inizio DESC
    LIMIT 3
";
$result_evidenza = $conn->query($sql_evidenza);
if ($result_evidenza) {
    while ($row = $result_evidenza->fetch_assoc()) {
        $mostre_evidenza[] = $row;
    }
}

// Gestione ricerca
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $searching = true;
    $query = $conn->real_escape_string(trim($_GET['q']));

    // ✅ QUERY ESTESA CON RICERCA OPERE
    $sql = "
    SELECT 'mostra' AS tipo, id, titolo AS nome, NULL as id_autore, NULL as nome_autore
    FROM mostra
    WHERE titolo LIKE '%$query%'
    
    UNION
    
    SELECT 'autore' AS tipo, id, CONCAT(nome, ' ', cognome) AS nome, NULL as id_autore, NULL as nome_autore
    FROM autore
    WHERE nome LIKE '%$query%' OR cognome LIKE '%$query%'
    
    UNION
    
    SELECT 'opera' AS tipo, o.id, o.titolo AS nome, o.id_autore, CONCAT(a.nome, ' ', a.cognome) as nome_autore
    FROM opera o
    LEFT JOIN autore a ON o.id_autore = a.id
    WHERE o.titolo LIKE '%$query%'
    
    ORDER BY nome ASC
    ";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
}


// Funzione per ottenere le immagini dello slideshow
function getSlideShowImages() {
    $dir = "immagini_opere/";
    $allowed_ext = ['jpg','jpeg','png','gif'];
    $images = [];
    
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_ext)) {
                $images[] = $dir . $file;
            }
        }
    }
    return $images;
}

$slideshow_images = getSlideShowImages();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ArtInsight - Galleria d'Arte</title>
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    body { 
        margin:0; 
        background-color:#fdf6f0; 
        font-family:'Playfair Display', serif; 
        line-height: 1.6;
    }
    
    header { text-align:center; padding:2rem 1rem 1rem; }
    .logo { font-family:'Dancing Script', cursive; font-size:3rem; color:#2c2c2c; margin:0; }
    .subtitle { 
    font-size: 1.1rem; 
    color: #8b5e3c; 
    font-weight: 500; 
    margin: 1rem 0 0 0; 
    font-family: 'Inter', sans-serif; 
    letter-spacing: 1.5px; 
    text-transform: uppercase;
    position: relative;
    padding: 1rem 2rem;
    background: rgba(240, 230, 210, 0.3);
    border-radius: 25px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(139, 94, 60, 0.1);
    border: 1px solid rgba(139, 94, 60, 0.1);
}

.subtitle::before {
    content: '✦';
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: #c75b3c;
    font-size: 0.8rem;
}

.subtitle::after {
    content: '✦';
    position: absolute;
    right: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: #c75b3c;
    font-size: 0.8rem;
}


    
    nav { background-color:#f0e6d2; border-top:1px solid #e0d6be; border-bottom:1px solid #e0d6be; }
    .nav-container { display:flex; justify-content:space-between; align-items:center; max-width:1000px; margin:0 auto; padding:0 1rem; }
    nav ul { list-style:none; margin:0; padding:0; display:flex; }
    nav li { margin:0 1rem; }
    nav a { display:block; padding:1rem 0; text-decoration:none; color:#8b5e3c; font-weight:500; transition:color 0.3s; }
    nav a:hover { color:#a84b2f; }
    
    .search-bar input[type="text"] { 
        padding:0.5rem 0.8rem; 
        border:1px solid #d8c9b5; 
        border-radius:20px; 
        font-size:0.9rem; 
        outline:none; 
        background-color:#fffaf0; 
    }
    
    /* 🎠 SLIDESHOW */
    .slideshow-container { 
        max-width:1000px; 
        overflow:hidden; 
        margin:2rem auto 0 auto; 
        border-radius:12px; 
        box-shadow:0 0 20px rgba(0,0,0,0.12); 
        position:relative; 
        height:250px; 
    }
    
    .slides-wrapper { 
        display:flex; 
        white-space:nowrap; 
        animation:scroll-left 40s linear infinite; 
    }
    
    .slides-wrapper img { 
        height:250px; 
        width:auto; 
        margin-right:10px; 
        object-fit:cover; 
        border-radius:8px; 
        box-shadow:0 3px 8px rgba(0,0,0,0.15); 
    }
    
    @keyframes scroll-left { 
        0% { transform:translateX(0); } 
        100% { transform:translateX(-50%); } 
    }

    /* 📊 SEZIONE STATISTICHE */
    .stats-section {
        max-width: 1000px;
        margin: 3rem auto;
        padding: 0 1rem;
    }

    .stats-title {
        text-align: center;
        font-size: 2.5rem;
        color: #8b5e3c;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .stats-subtitle {
        text-align: center;
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 3rem;
        font-family: 'Inter', sans-serif;
        font-weight: 400;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #fff7ef 0%, #f8f5f0 100%);
        border: 2px solid #f0e6d2;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        box-shadow: 0 8px 25px rgba(199, 91, 60, 0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #c75b3c, #e4a567, #f1c27d);
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 40px rgba(199, 91, 60, 0.25);
        border-color: #c75b3c;
    }

    .stat-icon {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        display: block;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }

    .stat-number {
        font-size: 3.5rem;
        font-weight: 700;
        color: #c75b3c;
        margin-bottom: 0.5rem;
        display: block;
        font-family: 'Playfair Display', serif;
    }

    .stat-label {
        color: #8b5e3c;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        font-family: 'Inter', sans-serif;
    }

    .stat-description {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.4;
        font-family: 'Inter', sans-serif;
    }

    /* 🏛️ MOSTRE IN EVIDENZA */
    .featured-section {
        max-width: 1000px;
        margin: 4rem auto;
        padding: 0 1rem;
    }

    .section-title {
        text-align: center;
        font-size: 2.2rem;
        color: #8b5e3c;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .section-subtitle {
        text-align: center;
        color: #666;
        font-size: 1rem;
        margin-bottom: 3rem;
        font-family: 'Inter', sans-serif;
    }

    .featured-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .featured-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        border: 1px solid #f0e6d2;
    }

    .featured-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .card-header {
        background: linear-gradient(135deg, #c75b3c, #e4a567);
        color: white;
        padding: 1.5rem;
        position: relative;
    }

    .card-title {
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .card-curator {
        opacity: 0.9;
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
    }

    .card-works {
        color: #c75b3c;
        font-weight: 600;
        background: rgba(199, 91, 60, 0.1);
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
    }

    .card-status {
        color: #2d5016;
        font-weight: 600;
        background: rgba(45, 80, 22, 0.1);
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
    }

    .card-button {
        width: 100%;
        background: linear-gradient(135deg, #c75b3c, #a84b2f);
        color: white;
        border: none;
        padding: 0.8rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
        font-family: 'Inter', sans-serif;
    }

    .card-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(199, 91, 60, 0.3);
    }

    /* Messaggio nessuna mostra */
    .no-exhibitions {
        text-align: center;
        padding: 3rem 2rem;
        background: rgba(199, 91, 60, 0.1);
        border-radius: 15px;
        border: 2px dashed #c75b3c;
    }

    .no-exhibitions h3 {
        color: #8b5e3c;
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .no-exhibitions p {
        color: #666;
        margin-bottom: 1.5rem;
        font-family: 'Inter', sans-serif;
    }

    .no-exhibitions .cta-button {
        background: #c75b3c;
        color: white;
        padding: 1rem 2rem;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        display: inline-block;
    }

    .no-exhibitions .cta-button:hover {
        background: #a84b2f;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(199, 91, 60, 0.3);
    }

    /* 🔍 STILI MIGLIORATI PER I RISULTATI DI RICERCA */
    .results-section {
        max-width: 1000px;
        margin: 3rem auto;
        padding: 0 1rem;
    }

    .results-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(199, 91, 60, 0.1), rgba(228, 165, 103, 0.1));
        border-radius: 20px;
        border: 2px solid rgba(199, 91, 60, 0.2);
    }

    .results-title {
        font-size: 2.2rem;
        color: #8b5e3c;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .results-query {
        font-size: 1.3rem;
        color: #c75b3c;
        font-weight: 600;
        font-style: italic;
        margin-bottom: 0.5rem;
    }

    .results-count {
        color: #666;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
    }

    .results-grid {
        display: grid;
        gap: 1.5rem;
    }

    .result-card {
        background: linear-gradient(135deg, #fff 0%, #fefcf8 100%);
        border: 2px solid #f0e6d2;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(199, 91, 60, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .result-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #c75b3c, #e4a567);
    }

    .result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(199, 91, 60, 0.2);
        border-color: #c75b3c;
    }

    .result-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .result-type-badge {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
    }

    .result-type-badge.mostra {
        background: linear-gradient(135deg, #c75b3c, #a84b2f);
        color: white;
    }

    .result-type-badge.autore {
        background: linear-gradient(135deg, #e4a567, #d18c4f);
        color: white;
    }

    .result-type-badge.opera {
        background: linear-gradient(135deg, #f1c27d, #e4a567);
        color: #8b5e3c;
    }

    .result-icon {
        font-size: 1.5rem;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }

    .result-content {
        flex: 1;
    }

    .result-name {
        font-size: 1.3rem;
        color: #2c2c2c;
        margin-bottom: 0.5rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .result-author {
        color: #8b5e3c;
        font-style: italic;
        font-size: 1rem;
        margin-bottom: 1rem;
        font-family: 'Inter', sans-serif;
    }

    .result-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    .result-button {
        background: linear-gradient(135deg, #c75b3c, #a84b2f);
        color: white;
        padding: 0.8rem 1.5rem;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(199, 91, 60, 0.3);
    }

    .result-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199, 91, 60, 0.4);
        background: linear-gradient(135deg, #a84b2f, #8b3e2a);
    }

    .no-results {
        text-align: center;
        padding: 4rem 2rem;
        background: linear-gradient(135deg, rgba(199, 91, 60, 0.05), rgba(228, 165, 103, 0.05));
        border-radius: 20px;
        border: 2px dashed rgba(199, 91, 60, 0.3);
    }

    .no-results-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.6;
    }

    .no-results h3 {
        color: #8b5e3c;
        margin-bottom: 1rem;
        font-size: 1.8rem;
        font-weight: 600;
    }

    .no-results p {
        color: #666;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .search-suggestions {
        background: rgba(255, 255, 255, 0.8);
        padding: 1.5rem;
        border-radius: 15px;
        margin-top: 1.5rem;
    }

    .search-suggestions h4 {
        color: #8b5e3c;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .search-suggestions ul {
        list-style: none;
        padding: 0;
        margin: 0;
        font-family: 'Inter', sans-serif;
    }

    .search-suggestions li {
        color: #666;
        margin-bottom: 0.5rem;
        padding-left: 1.5rem;
        position: relative;
    }

    .search-suggestions li::before {
        content: '💡';
        position: absolute;
        left: 0;
        top: 0;
    }

    /* 📱 RESPONSIVE */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .featured-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-title {
            font-size: 2rem;
        }
        
        .section-title {
            font-size: 1.8rem;
        }

        .nav-container {
            flex-direction: column;
            gap: 1rem;
        }

        nav ul {
            flex-wrap: wrap;
            justify-content: center;
        }

        .results-title {
            font-size: 1.8rem;
        }

        .results-query {
            font-size: 1.1rem;
        }

        .result-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.8rem;
        }

        .result-actions {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-card {
            padding: 2rem 1rem;
        }

        .logo {
            font-size: 2.5rem;
        }

        nav li {
            margin: 0 0.5rem;
        }
        
        .subtitle {
            font-size: 0.9rem;
            letter-spacing: 1px;
            padding: 0.8rem 1.5rem;
        }

        .result-card {
            padding: 1rem;
        }

        .results-header {
            padding: 1.5rem;
        }
    }
    
	
	

    /* Animazioni barre originali */
    .bar:nth-child(3) { animation:bar-move-1 1.5s infinite ease-in-out; }
    .bar:nth-child(4) { animation:bar-move-2 1.7s infinite ease-in-out; }
    .bar:nth-child(5) { animation:bar-move-3 1.6s infinite ease-in-out; }
    .bar:nth-child(6) { animation:bar-move-4 1.8s infinite ease-in-out; }
    @keyframes bar-move-1 { 0%,100%{height:15px;y:40;}50%{height:25px;y:30;} }
    @keyframes bar-move-2 { 0%,100%{height:20px;y:35;}50%{height:28px;y:27;} }
    @keyframes bar-move-3 { 0%,100%{height:25px;y:30;}50%{height:32px;y:23;} }
    @keyframes bar-move-4 { 0%,100%{height:30px;y:25;}50%{height:35px;y:20;} }
  </style>
</head>
<body>

<header style="display:flex; align-items:center; justify-content:center; gap:0.5rem; flex-direction:column;">
  <div style="display:flex; align-items:center; gap:0.5rem;">
    <svg width="40" height="40" viewBox="0 0 64 64">
      <circle cx="32" cy="2" r="2" fill="#555"/>
      <rect x="2" y="5" width="60" height="57" fill="#f0e6d2" stroke="#c97c50" stroke-width="3"/>
      <rect class="bar" x="10" y="40" width="6" height="15" fill="#c97c50"/>
      <rect class="bar" x="20" y="35" width="6" height="20" fill="#e4a567"/>
      <rect class="bar" x="30" y="30" width="6" height="25" fill="#f1c27d"/>
      <rect class="bar" x="40" y="25" width="6" height="30" fill="#d18c4f"/>
    </svg>
    <h1 class="logo" style="margin:0; color:#8b5e3c;">ARTINSIGHT</h1>
  </div>
  <p class="subtitle">Analytics Avanzate per il Patrimonio Artistico</p>
</header>

<nav>
  <div class="nav-container">
    <ul>
      <li><a href="archivio.php">Archivio Mostre</a></li>
	  <li><a href="index.php">Home</a></li>
      <?php if($showModifica): ?>
        <li><a href="backoffice.php">Modifica Mostre</a></li>
      <?php endif; ?>
    </ul>
    <div class="search-bar">
      <form method="GET" action="index.php">
        <input type="text" name="q" placeholder="Cerca..." value="<?php echo htmlspecialchars($query); ?>">
      </form>
    </div>
  </div>
</nav>

<?php if (!$searching): ?>
  <!-- 🎠 SLIDESHOW -->
  <div class="slideshow-container">
    <div class="slides-wrapper">
      <?php
      if (count($slideshow_images) > 0) {
          // Duplica le immagini per l'effetto continuo
		  shuffle($slideshow_images);
          $all_images = array_merge($slideshow_images, $slideshow_images);
          foreach ($all_images as $image) {

              echo "<img src='" . htmlspecialchars($image) . "' alt='Opera d\'arte'>";
          }
      } else {
          echo "<p style='color:red; text-align:center; padding:2rem;'>Cartella immagini non trovata o vuota.</p>";
      }
      ?>
    </div>
  </div>

  <!-- 📊 SEZIONE STATISTICHE -->
  <?php if ($stats): ?>
  <section class="stats-section">
    <h2 class="stats-title">Database Statistico</h2>
    <p class="stats-subtitle">Panoramica completa delle mostre catalogate e delle opere d'arte registrate</p>
    
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-icon">🏛️</span>
        <span class="stat-number"><?php echo $stats['totale_mostre']; ?></span>
        <span class="stat-label">Mostre Catalogate</span>
        <p class="stat-description">Esposizioni registrate nel database</p>
      </div>
      
      <div class="stat-card">
        <span class="stat-icon">🔥</span>
        <span class="stat-number"><?php echo $stats['mostre_attive']; ?></span>
        <span class="stat-label">Mostre Attive</span>
        <p class="stat-description">Esposizioni attualmente visitabili</p>
      </div>
      
      <div class="stat-card">
        <span class="stat-icon">👨‍🎨</span>
        <span class="stat-number"><?php echo $stats['totale_artisti']; ?></span>
        <span class="stat-label">Artisti</span>
        <p class="stat-description">Creativi rappresentati nelle collezioni</p>
      </div>
      
      <div class="stat-card">
        <span class="stat-icon">🎨</span>
        <span class="stat-number"><?php echo $stats['totale_opere']; ?></span>
                <span class="stat-label">Opere Registrate</span>
        <p class="stat-description">Opere d'arte catalogate nel sistema</p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- 🏛️ MOSTRE IN EVIDENZA -->
  <?php if (count($mostre_evidenza) > 0): ?>
  <section class="featured-section">
    <h2 class="section-title">Mostre in Evidenza</h2>
    <p class="section-subtitle">Le esposizioni più interessanti attualmente visitabili</p>
    
    <div class="featured-grid">
      <?php foreach ($mostre_evidenza as $mostra): ?>
      <div class="featured-card">
        <div class="card-header">
          <h3 class="card-title"><?php echo htmlspecialchars($mostra['titolo']); ?></h3>
          <p class="card-curator">Curatore: <?php echo htmlspecialchars($mostra['curatore']); ?></p>
        </div>
        <div class="card-body">
          <div class="card-info">
            <span class="card-works">🎨 <?php echo $mostra['num_opere']; ?> opere</span>
            <span class="card-status">✅ Attiva</span>
          </div>
          <a href="mostra.php?id=<?php echo $mostra['id']; ?>" class="card-button">
            Visita la Mostra
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php else: ?>
  <section class="featured-section">
    <h2 class="section-title">Mostre in Evidenza</h2>
    <div class="no-exhibitions">
      <h3>🎭 Nessuna mostra attiva al momento</h3>
      <p>Torna presto per scoprire le prossime esposizioni straordinarie!</p>
      <a href="archivio.php" class="cta-button">📚 Esplora l'Archivio</a>
    </div>
  </section>
  <?php endif; ?>

<?php else: ?>
  <!-- 🔍 RISULTATI RICERCA MIGLIORATI -->
  <section class="results-section">
    <div class="results-header">
      <h2 class="results-title">🔍 Risultati di Ricerca</h2>
      <div class="results-query">"<?php echo htmlspecialchars($query); ?>"</div>
      <p class="results-count">
        <?php 
        $count = count($results);
        if ($count == 0) {
          echo "Nessun risultato trovato";
        } elseif ($count == 1) {
          echo "1 risultato trovato";
        } else {
          echo "$count risultati trovati";
        }
        ?>
      </p>
    </div>

    <?php if (count($results) > 0): ?>
      <div class="results-grid">
        <?php foreach ($results as $item): ?>
          <div class="result-card">
            <div class="result-header">
              <div class="result-type-badge <?php echo $item['tipo']; ?>">
                <?php 
                switch($item['tipo']) {
                  case 'mostra':
                    echo '🏛️ Mostra';
                    break;
                  case 'autore':
                    echo '👨‍🎨 Artista';
                    break;
                  case 'opera':
                    echo '🎨 Opera';
                    break;
                }
                ?>
              </div>
            </div>
            
            <div class="result-content">
              <h3 class="result-name"><?php echo htmlspecialchars($item['nome']); ?></h3>
              
              <?php if ($item['tipo'] === 'opera' && $item['nome_autore']): ?>
                <p class="result-author">
                  <span style="color: #8b5e3c;">👨‍🎨</span> 
                  di <?php echo htmlspecialchars($item['nome_autore']); ?>
                </p>
              <?php endif; ?>
            </div>
            
            <div class="result-actions">
              <a class="result-button" href="<?php
                if ($item['tipo'] === 'mostra') {
                    echo "mostra.php?id=" . urlencode($item['id']) . "&q=" . urlencode($query);
                } elseif ($item['tipo'] === 'autore') {
                    echo "autore.php?id=" . urlencode($item['id']);
                } elseif ($item['tipo'] === 'opera') {
                    echo "opera.php?id=" . urlencode($item['id']);
				
                }
              ?>">
                <span>Vedi dettagli</span>
                <span>→</span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="no-results">
        <div class="no-results-icon">🔍</div>
        <h3>Nessun risultato trovato</h3>
        <p>
          Non abbiamo trovato nessun elemento che corrisponda alla tua ricerca 
          "<strong><?php echo htmlspecialchars($query); ?></strong>".
        </p>
        
        <div class="search-suggestions">
          <h4>💡 Suggerimenti per migliorare la ricerca:</h4>
          <ul>
            <li>Controlla l'ortografia delle parole chiave</li>
            <li>Prova con termini più generali o sinonimi</li>
            <li>Usa parole chiave diverse o più specifiche</li>
            <li>Cerca solo il nome o cognome dell'artista</li>
            <li>Prova con parte del titolo dell'opera o mostra</li>
          </ul>
        </div>
        
        <div style="margin-top: 2rem;">
          <a href="index.php" class="cta-button">🏠 Torna alla Home</a>
          <a href="archivio.php" class="cta-button" style="margin-left: 1rem;">📚 Esplora l'Archivio</a>
        </div>
      </div>
    <?php endif; ?>
  </section>

<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
