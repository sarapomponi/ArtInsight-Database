<?php
// Connessione DB
$conn = new mysqli('localhost', 'root', '', 'mostre');
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$opera_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$opera = null;

if ($opera_id > 0) {
    // Query con JOIN solo per collocazione
    $sql = "
        SELECT 
            o.*,
            a.nome as autore_nome, 
            a.cognome as autore_cognome,
            a.movimento as autore_movimento,
            a.nazionalita as autore_nazionalita,
            tip.categoria as tipologia_nome,
            tec.nome as tecnica_nome,
            col.nome as collocazione_nome
        FROM opera o
        LEFT JOIN autore a ON o.id_autore = a.id
        LEFT JOIN tipologia tip ON o.id_tipologia = tip.id
        LEFT JOIN tecnica tec ON o.id_tecnica = tec.id
        LEFT JOIN collocazione col ON o.id_collocazione = col.id
        WHERE o.id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $opera_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $opera = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$opera) {
    header("Location: index.php");
    exit();
}

// Query per i temi dell'opera (usando opera_tema)
$temi_sql = "
    SELECT t.nome 
    FROM tema t 
    JOIN opera_tema ot ON t.id = ot.id_tema 
    WHERE ot.id_opera = ?
";
$stmt_temi = $conn->prepare($temi_sql);
$stmt_temi->bind_param("i", $opera_id);
$stmt_temi->execute();
$result_temi = $stmt_temi->get_result();
$temi = [];
while ($tema = $result_temi->fetch_assoc()) {
    $temi[] = $tema;
}
$stmt_temi->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($opera['titolo']); ?> - MOSTRATISTICA</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0; 
            background-color: #fdf6f0; 
            font-family: 'Playfair Display', serif; 
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .back-button {
            display: inline-flex;
			align-items: center;
			gap: 0.5rem;
            background: linear-gradient(45deg, #8b5e3c, #d4b185);
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
			position: relative;
			overflow: hidden;
            border-radius: 20px;
            font-weight: 600;
            margin right: 10px;
            transition: all 0.3s ease;
            font-family: 'Playfair Display', serif;
			box-shadow: 0 4px 10px rgba(139, 94, 60, 0.2);
        }
        
        .back-button:hover {
            background:linear-gradient(45deg, #c75b3c, #f1c27d);
            transform: translateY(-3px) scale(1.05);
			color: white;
			box-shadow: 0 8px 25px rgba(139, 94, 60, 0.4);
        }
        
		.nav-btn::after {
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

.nav-btn:hover::after {
    transform: rotate(45deg) translateX(100%);
}

.nav-btn.home::before {
    content: "🏛️";
    font-size: 1.2rem;
}

.nav-btn.archive::before {
    content: "📚";
    font-size: 1.2rem;
}
        .opera-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .opera-title {
            font-size: 3rem;
            color: #8b5e3c;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .opera-author {
            font-size: 1.5rem;
            color: #666;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        
        .author-details {
            font-size: 1rem;
            color: #888;
            font-family: 'Inter', sans-serif;
            font-style: italic;
        }
        
        .opera-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .opera-image {
            text-align: center;
        }
        
        .opera-image img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .opera-image img:hover {
            transform: scale(1.02);
        }
        
        .no-image {
            background: linear-gradient(135deg, #f8f5f0, #f0f0f0);
            padding: 3rem 2rem;
            border-radius: 15px;
            color: #999;
            text-align: center;
            font-family: 'Inter', sans-serif;
            border: 2px dashed #ddd;
        }
        
        .no-image::before {
            content: "🖼️";
            font-size: 4rem;
            display: block;
            margin-bottom: 1rem;
            opacity: 0.6;
        }
        
        .opera-details {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .detail-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f0e6d2;
        }
        
        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .detail-title {
            font-size: 1.3rem;
            color: #8b5e3c;
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-family: 'Inter', sans-serif;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            color: #2c2c2c;
            text-align: right;
            max-width: 60%;
        }
        
        .author-highlight {
            background: linear-gradient(135deg, #fff7ef, #f8f5f0);
            border-left: 4px solid #e4a567;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .author-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #8b5e3c;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .author-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-family: 'Inter', sans-serif;
        }
        
        .author-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        
        .author-info-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .author-info-value {
            color: #2c2c2c;
            font-weight: 500;
        }
        
        .location-highlight {
            background: linear-gradient(135deg, #f8f5f0, #fff7ef);
            border-left: 4px solid #c75b3c;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .location-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #8b5e3c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .no-location {
            background: linear-gradient(135deg, #f9f9f9, #f0f0f0);
            color: #999;
            font-style: italic;
            text-align: center;
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }
        
        .no-location::before {
            content: "📍";
            font-size: 2rem;
            display: block;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }
        
        .dimensions-display {
            background: rgba(199, 91, 60, 0.1);
            padding: 0.8rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: #8b5e3c;
            margin-top: 0.5rem;
        }

        /* Stili per i temi */
        .temi-section {
            margin: 30px 0;
            padding: 20px;
            background: #fafafa; /* Sfondo leggermente diverso per staccare */
            border-radius: 4px;
        }

        .tags-container {
           display: flex;
           flex-wrap: wrap;
           gap: 12px;
           margin-top: 15px;
         }
		 
		 
		 .tema-tag {
    display: inline-block;
    padding: 8px 20px;
    border: 1px solid #d4b185; /* Bordo color oro/bronzo */
    background-color: #fff;
    color: #8b5e3c; /* Testo marrone elegante */
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.05em; /* Spaziatura tra lettere */
    text-transform: uppercase; /* Tutto maiuscolo per un look istituzionale */
    border-radius: 2px; /* Angoli quasi retti, molto più eleganti di quelli tondi */
    transition: all 0.3s ease;
    box-shadow: 2px 2px 0px rgba(212, 177, 133, 0.2); /* Ombra rigida stile design moderno */
}

.tema-tag:hover {
    background-color: #d4b185;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 4px 4px 0px rgba(139, 94, 60, 0.3);
}

.detail-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    color: #6b4e31;
    border-bottom: 2px solid #d4b185;
    display: inline-block;
    padding-bottom: 5px;
    margin-bottom: 10px;
}

        .chart-container-css {
            background: #f8f5f0;
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid #e0d5c7;
        }

        .chart-title {
            text-align: center;
            color: #8b5e3c;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .bars-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .bar-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .bar-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
        }

        .bar-container {
            background: #f0e6d2;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.8s ease;
            position: relative;
        }

        .bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .temi-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tema-item {
            background: linear-gradient(135deg, #fff7ef, #f8f5f0);
            border-left-width: 4px;
            border-left-style: solid;
            padding: 1rem;
            border-radius: 8px;
        }

        .tema-nome {
            font-weight: 600;
            color: #8b5e3c;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tema-color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .no-temi {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 2rem;
            background: linear-gradient(135deg, #f9f9f9, #f0f0f0);
            border-radius: 8px;
            border: 2px dashed #ddd;
        }

        .no-temi::before {
            content: "🎨";
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Stili per i link */
        .author-link {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s ease;
        }

        .author-link:hover {
            transform: translateY(-2px);
        }

        .author-link:hover .author-highlight {
            background: linear-gradient(135deg, #fff2e6, #f5f0eb);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .mostra-link {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .mostra-link:hover {
            background: linear-gradient(135deg, #fff7ef, #f8f5f0);
            transform: translateX(5px);
        }

        .mostra-item {
            padding: 0.8rem;
            border-radius: 8px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .mostra-link:hover .mostra-item {
            border-left-color: #c75b3c;
        }

        .link-arrow {
            opacity: 0;
            transition: all 0.3s ease;
            font-weight: bold;
            color: #c75b3c;
            margin-left: 0.5rem;
        }

        .author-link:hover .link-arrow,
        .mostra-link:hover .link-arrow {
            opacity: 1;
            transform: translateX(3px);
        }

        /* Effetto hover per il cursore */
        .author-link,
        .mostra-link {
            cursor: pointer;
        }

        .author-link:hover .author-name,
        .mostra-link:hover .detail-label {
            color: #c75b3c;
        }
        
        @media (max-width: 768px) {
            .opera-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .opera-title {
                font-size: 2rem;
            }
            
            .detail-item {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .detail-value {
                text-align: left;
                max-width: 100%;
            }
            
            .author-info {
                grid-template-columns: 1fr;
            }

            .temi-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .bars-container {
                gap: 0.8rem;
            }
            
            .bar-label {
                font-size: 0.8rem;
            }
            
            .bar-container {
                height: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="javascript:history.back()" class="back-button">← Torna Indietro</a>
    
    <div class="opera-header">
        <h1 class="opera-title"><?php echo htmlspecialchars($opera['titolo']); ?></h1>
        <?php if ($opera['autore_nome'] && $opera['autore_cognome']): ?>
            <p class="opera-author">
                di <?php echo htmlspecialchars($opera['autore_nome'] . ' ' . $opera['autore_cognome']); ?>
            </p>
            <div class="author-details">
                <?php 
                $author_details = [];
                if ($opera['autore_nazionalita']) $author_details[] = $opera['autore_nazionalita'];
                if ($opera['autore_movimento']) $author_details[] = $opera['autore_movimento'];
                if (!empty($author_details)) {
                    echo implode(' • ', $author_details);
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="opera-content">
        <div class="opera-image">
            <?php
            $image_path = "immagini_opere/opera_" . $opera['id'];
            $image_found = false;
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($extensions as $ext) {
                $full_path = $image_path . '.' . $ext;
                if (file_exists($full_path)) {
                    echo '<img src="' . $full_path . '" alt="' . htmlspecialchars($opera['titolo']) . '">';
                    $image_found = true;
                    break;
                }
            }
            
            if (!$image_found) {
                echo '<div class="no-image">';
                echo '<div style="font-weight:600; margin-bottom:0.5rem;">Immagine non disponibile</div>';
                echo '<div style="font-size:0.9rem; opacity:0.7;">Cercando: opera_' . $opera['id'] . '.jpg</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="opera-details">
            <!-- Informazioni sull'Autore -->
            <?php if ($opera['autore_nome'] && $opera['autore_cognome']): ?>
            <div class="detail-section">
                <h3 class="detail-title">👨‍🎨 Autore</h3>
                
                <a href="autore.php?id=<?php echo $opera['id_autore']; ?>" class="author-link">
                    <div class="author-highlight">
                        <div class="author-name">
                            🎨 <?php echo htmlspecialchars($opera['autore_nome'] . ' ' . $opera['autore_cognome']); ?>
                            <span class="link-arrow">→</span>
                        </div>
                        
                        <div class="author-info">
                            <?php if ($opera['autore_nazionalita']): ?>
                            <div class="author-info-item">
                                <span class="author-info-label">Nazionalità</span>
                                <span class="author-info-value">🌍 <?php echo htmlspecialchars($opera['autore_nazionalita']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($opera['autore_movimento']): ?>
                            <div class="author-info-item">
                                <span class="author-info-label">Movimento</span>
                                <span class="author-info-value">🎭 <?php echo htmlspecialchars($opera['autore_movimento']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Informazioni Generali -->
            <div class="detail-section">
                <h3 class="detail-title">📋 Informazioni Generali</h3>
                
                <?php if (isset($opera['datazione']) && !empty(trim($opera['datazione']))): ?>
                <div class="detail-item">
                    <span class="detail-label">Datazione:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($opera['datazione']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($opera['tecnica_nome']) && $opera['tecnica_nome']): ?>
                <div class="detail-item">
                    <span class="detail-label">Tecnica:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($opera['tecnica_nome']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($opera['tipologia_nome']) && $opera['tipologia_nome']): ?>
                <div class="detail-item">
                    <span class="detail-label">Tipologia:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($opera['tipologia_nome']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ((isset($opera['altezza']) && $opera['altezza'] > 0) || (isset($opera['larghezza']) && $opera['larghezza'] > 0)): ?>
                <div class="detail-item">
                    <span class="detail-label">Dimensioni:</span>
                    <div class="detail-value">
                        <div class="dimensions-display">
                            📏 
                            <?php 
                            $dimensions = [];
                            if ($opera['altezza'] > 0) $dimensions[] = "H: " . $opera['altezza'] . " cm";
                            if ($opera['larghezza'] > 0) $dimensions[] = "L: " . $opera['larghezza'] . " cm";
                            echo implode(" × ", $dimensions);
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Collocazione -->
            <div class="detail-section">
                <h3 class="detail-title">🏛️ Collocazione</h3>
                
                <?php if (isset($opera['collocazione_nome']) && !empty(trim($opera['collocazione_nome']))): ?>
                    <div class="location-highlight">
                        <div class="location-name">
                            📍 <?php echo htmlspecialchars($opera['collocazione_nome']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-location">
                        <div>Informazioni sulla collocazione non disponibili</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mostre dove è esposta -->
            <?php
            $mostre_sql = "
                SELECT m.id, m.titolo, m.data_inizio, m.data_fine 
                FROM mostra m 
                JOIN esposizione e ON m.id = e.id_mostra 
                WHERE e.id_opera = ? 
                ORDER BY m.data_inizio DESC
            ";
            $stmt_mostre = $conn->prepare($mostre_sql);
            $stmt_mostre->bind_param("i", $opera_id);
            $stmt_mostre->execute();
            $result_mostre = $stmt_mostre->get_result();
            
            if ($result_mostre->num_rows > 0): ?>
            <div class="detail-section">
                <h3 class="detail-title">🎭 Mostre</h3>
                <?php while ($mostra = $result_mostre->fetch_assoc()): ?>
                    <a href="mostra.php?id=<?php echo $mostra['id']; ?>" class="mostra-link">
                        <div class="detail-item mostra-item">
                            <span class="detail-label">
                                <?php echo htmlspecialchars($mostra['titolo']); ?>
                                <span class="link-arrow">→</span>
                            </span>
                            <span class="detail-value">
                                <?php echo date('d/m/Y', strtotime($mostra['data_inizio'])); ?> - 
                                <?php echo date('d/m/Y', strtotime($mostra['data_fine'])); ?>
                            </span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
            <?php endif; 
            $stmt_mostre->close();
            ?>
        </div>
    </div>

    <!-- Sezione Temi -->
    

<div class="temi-section">
    <h2 class="detail-title">🎨 Temi dell'Opera</h2>
    
    <?php if (!empty($temi)): ?>
        <div class="tags-container">
            <?php foreach ($temi as $tema): ?>
                <span class="tema-tag">
                    <?php echo htmlspecialchars($tema['nome']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-temi">
            Nessun tema associato a questa opera
        </div>
    <?php endif; ?>
</div>
        
</div>

</body>
</html>

<?php
$conn->close();
?>
