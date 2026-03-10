<?php
$conn = new mysqli('localhost', 'root', '', 'mostre');
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id_autore = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_mostra = isset($_GET['id_mostra']) ? intval($_GET['id_mostra']) : 0;

$autore = null;
$opere = [];
$img_path = null;

if ($id_autore > 0) {
    $sql = "SELECT * FROM autore WHERE id = $id_autore";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $autore = $result->fetch_assoc();

        // Gestione intelligente delle immagini autore
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Cerca l'immagine con diverse estensioni
        foreach ($extensions as $ext) {
            $test_path = "immagini_autori/autore_" . $autore['id'] . "." . $ext;
            if (file_exists($test_path)) {
                $img_path = $test_path;
                break;
            }
        }
        
        // Se non trova l'immagine, usa un'immagine SVG generata
        if (!$img_path) {
            $img_path = "data:image/svg+xml;base64," . base64_encode('
                <svg width="150" height="180" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100%" height="100%" fill="#f0e6d2" stroke="#d4b185" stroke-width="3"/>
                    <circle cx="75" cy="60" r="25" fill="#e4a567"/>
                    <rect x="50" y="90" width="50" height="60" fill="#c75b3c"/>
                    <text x="75" y="165" text-anchor="middle" font-family="serif" font-size="10" fill="#8b5e3c">
                        ' . htmlspecialchars($autore['nome']) . '
                    </text>
                </svg>
            ');
        }

        $sql_opere = "SELECT * FROM opera WHERE id_autore = $id_autore";
        $res_opere = $conn->query($sql_opere);
        if ($res_opere) {
            while ($row = $res_opere->fetch_assoc()) {
                // Aggiungi il percorso dell'immagine per ogni opera
                $opera_img_path = null;
                foreach ($extensions as $ext) {
                    $test_opera_path = "immagini_opere/opera_" . $row['id'] . "." . $ext;
                    if (file_exists($test_opera_path)) {
                        $opera_img_path = $test_opera_path;
                        break;
                    }
                }
                
                // Se non trova l'immagine, crea placeholder
                if (!$opera_img_path) {
                    $opera_img_path = "data:image/svg+xml;base64," . base64_encode('
                        <svg width="120" height="90" xmlns="http://www.w3.org/2000/svg">
                            <rect width="100%" height="100%" fill="#f0e6d2" stroke="#d4b185" stroke-width="2"/>
                            <rect x="20" y="15" width="80" height="50" fill="#fff" stroke="#d4b185" stroke-width="1"/>
                            <circle cx="60" cy="30" r="8" fill="#e4a567"/>
                            <rect x="40" y="40" width="40" height="15" fill="#e4a567"/>
                            <text x="60" y="80" text-anchor="middle" font-family="serif" font-size="8" fill="#8b5e3c">
                                Opera
                            </text>
                        </svg>
                    ');
                }
                
                $row['img_path'] = $opera_img_path;
                $opere[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Dettagli Autore</title>
  <style>
    /* Font elegante */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap');

    body {
      font-family: 'Playfair Display', serif;
      background: #fffdf9;
      padding: 2rem;
      color: #333;
    }
    
    h1 { 
      color: #444; 
      margin-top: 0; 
      font-weight: 700;
      font-size: 2rem;
    }
    
    h2 {
      color: #444;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .autore-container {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 2rem;
      margin-bottom: 2rem;
      max-width: 900px;
    }
    
    .autore-info {
      flex: 1;
    }
    
    .autore-img {
      max-width: 150px;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border: 3px solid #d4b185;
    }
    
    .opera-list {
      margin-top: 2rem;
      max-width: 900px;
    }
    
    /* GRIGLIA DELLE OPERE */
    .opere-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    
    .opera-item {
      position: relative;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      padding-bottom: 10px;
    }
    
    .opera-item:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }
    
    .opera-img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-bottom: 3px solid #d4b185;
      transition: all 0.3s ease;
    }
    
    .opera-item:hover .opera-img {
      transform: scale(1.05);
      filter: brightness(1.1);
    }
    
    /* TITOLO SOTTO L'IMMAGINE */
    .opera-title {
      padding: 8px 12px;
      font-size: 0.9rem;
      font-weight: 600;
      color: #444;
      text-align: center;
      line-height: 1.3;
      min-height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .opera-item:hover .opera-title {
      color: #e76f51;
      transform: scale(1.05);
    }
    
    /* TOOLTIP ELEGANTE (opzionale, per info extra) */
    .opera-item::after {
      content: "Clicca per vedere i dettagli";
      position: absolute;
      bottom: -35px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0,0,0,0.9);
      color: white;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      white-space: nowrap;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 1000;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .opera-item::before {
      content: "";
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 6px solid transparent;
      border-right: 6px solid transparent;
      border-bottom: 6px solid rgba(0,0,0,0.9);
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 1001;
    }
    
    .opera-item:hover::after,
    .opera-item:hover::before {
      opacity: 1;
      visibility: visible;
    }
    
    /* EFFETTO CORNICE AL HOVER */
    .opera-item:hover {
      border: 3px solid #e76f51;
    }
    
    .buttons {
      margin-bottom: 1.5rem;
    }

    /* TASTO MOSTRA CON ANIMAZIONE ELEGANTE */
    .mostra-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.8rem;
      background: linear-gradient(45deg, #e76f51, #f4a261);
      color: white !important;
      padding: 0.8rem 1.5rem;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      position: relative;
      overflow: hidden;
      margin-right: 10px;
      font-family: 'Playfair Display', serif;
      box-shadow: 0 4px 15px rgba(231, 111, 81, 0.3);
      border: none;
    }

    .mostra-btn::before {
      content: "←";
      font-size: 1.4rem;
      font-weight: bold;
      display: inline-block;
      transition: all 0.4s ease;
      animation: arrow-pulse 2s ease-in-out infinite;
    }

    .mostra-btn:hover {
      transform: translateY(-4px) scale(1.08);
      box-shadow: 0 12px 30px rgba(231, 111, 81, 0.5);
      background: linear-gradient(45deg, #c75b3c, #e76f51);
      color: white !important;
      animation: gallery-back 0.8s ease;
    }

    .mostra-btn:hover::before {
      animation: arrow-dance 0.8s ease-in-out;
      transform: translateX(-8px) scale(1.2);
    }

    .mostra-btn::after {
      content: "";
      position: absolute;
      top: 50%;
      left: -100%;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
      transform: translateY(-50%);
      transition: all 0.6s ease;
    }

    .mostra-btn:hover::after {
      left: 100%;
      animation: light-trail 0.8s ease-in-out;
    }

    /* TASTO HOME CON MUSEO FISSO */
    .home-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: linear-gradient(45deg, #8b5e3c, #d4b185);
      color: white !important;
      padding: 0.8rem 1.5rem;
      text-decoration: none;
      border-radius: 20px;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      font-family: 'Playfair Display', serif;
      box-shadow: 0 4px 10px rgba(139, 94, 60, 0.2);
      border: none;
    }

    .home-btn::before {
      content: "🏛️";
      font-size: 1.2rem;
      display: inline-block;
    }

    .home-btn:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(139, 94, 60, 0.4);
      background: linear-gradient(45deg, #c75b3c, #f1c27d);
      color: white !important;
    }

    .home-btn::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
      transform: rotate(45deg) translateX(-100%);
      transition: all 0.6s ease;
    }

    .home-btn:hover::after {
      transform: rotate(45deg) translateX(100%);
    }

    /* ANIMAZIONI */
    @keyframes arrow-pulse {
      0%, 100% { 
        transform: translateX(0px) scale(1); 
        opacity: 1; 
      }
      50% { 
        transform: translateX(-3px) scale(1.1); 
        opacity: 0.8; 
      }
    }

    @keyframes arrow-dance {
      0% { 
        transform: translateX(-8px) scale(1.2) rotate(0deg); 
      }
      25% { 
        transform: translateX(-12px) scale(1.3) rotate(-10deg); 
      }
      50% { 
        transform: translateX(-10px) scale(1.4) rotate(5deg); 
      }
      75% { 
        transform: translateX(-14px) scale(1.2) rotate(-5deg); 
      }
      100% { 
        transform: translateX(-8px) scale(1.2) rotate(0deg); 
      }
    }

    @keyframes gallery-back {
      0% { 
        transform: translateY(-4px) scale(1.08); 
      }
      50% { 
        transform: translateY(-6px) scale(1.12) rotateZ(-2deg); 
      }
      100% { 
        transform: translateY(-4px) scale(1.08); 
      }
    }

    @keyframes light-trail {
      0% { 
        left: -100%; 
        opacity: 0; 
      }
      50% { 
        opacity: 1; 
      }
      100% { 
        left: 100%; 
        opacity: 0; 
      }
    }

    p {
      margin-bottom: 0.8rem;
      line-height: 1.6;
    }

    strong {
      color: #8b5e3c;
    }
  </style>
</head>
<body>

<div class="buttons">
  <?php if ($id_mostra > 0): ?>
    <a href="mostra.php?id=<?= $id_mostra ?>" class="mostra-btn">Torna alla mostra</a>
  <?php endif; ?>
  <a href="index.php" class="home-btn">Home</a>
</div>

<?php if ($autore): ?>
  <div class="autore-container">
    <div class="autore-info">
      <h1><?= htmlspecialchars($autore['nome'] . ' ' . $autore['cognome']) ?></h1>
      <p><strong>Nazionalità:</strong> <?= htmlspecialchars($autore['nazionalita']) ?></p>
      <p><strong>Movimento artistico:</strong> <?= htmlspecialchars($autore['movimento']) ?></p>
    </div>

    <?php if ($img_path): ?>
      <img src="<?= htmlspecialchars($img_path) ?>" 
           alt="Immagine di <?= htmlspecialchars($autore['nome']) ?>" 
           class="autore-img">
    <?php endif; ?>
  </div>

  <div class="opera-list">
    <h2>Opere realizzate:</h2>
    <?php if (count($opere) > 0): ?>
      <div class="opere-grid">
        <?php foreach ($opere as $opera): ?>
          <div class="opera-item" onclick="location.href='opera.php?id=<?= $opera['id'] ?>&id_mostra=<?= $id_mostra ?>'">
            <img src="<?= htmlspecialchars($opera['img_path']) ?>" 
                 alt="<?= htmlspecialchars($opera['titolo']) ?>" 
                 class="opera-img">
            <div class="opera-title"><?= htmlspecialchars($opera['titolo']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>Nessuna opera registrata per questo autore.</p>
    <?php endif; ?>
  </div>
<?php else: ?>
  <p>Autore non trovato.</p>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>


