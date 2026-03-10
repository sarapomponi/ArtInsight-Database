<?php

$conn = new mysqli('localhost', 'root', '', 'mostre');

if ($conn->connect_error) {

    die("Connessione fallita: " . $conn->connect_error);

}



$id_mostra = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

$id_opera_evidenziata = isset($_GET['id_opera']) && is_numeric($_GET['id_opera']) ? (int)$_GET['id_opera'] : 0;



if (!$id_mostra) {

    die("ID mostra non valido.");

}



$mostra = null;

$opere = [];



$sql_mostra = "

    SELECT 

        m.*, 

        c.nome AS nome_collocazione  -- Stai recuperando il nome e lo stai chiamando 'nome_collocazione'

    FROM mostra m

    JOIN collocazione c ON m.sede = c.id

    WHERE m.id = $id_mostra

";

$result = $conn->query($sql_mostra);

if ($result && $result->num_rows > 0) {

    $mostra = $result->fetch_assoc();

    

    $sql_opere = "

        SELECT o.*, a.nome, a.cognome, t.categoria as tipologia_nome, te.nome as tecnica_nome

        FROM opera o

        JOIN esposizione e ON o.id = e.id_opera

        LEFT JOIN autore a ON o.id_autore = a.id

        LEFT JOIN tipologia t ON o.id_tipologia = t.id

        LEFT JOIN tecnica te ON o.id_tecnica = te.id

        WHERE e.id_mostra = $id_mostra

        ORDER BY o.titolo

    ";

    $res_opere = $conn->query($sql_opere);

    if ($res_opere) {

        while ($row = $res_opere->fetch_assoc()) {

            $opere[] = $row;

        }

    }

}



// Funzione per trovare l'immagine dell'opera con diversi formati

function trovaImmagineOpera($id_opera) {

    $formati = ['jpg', 'jpeg', 'png', 'gif'];

    $base_path = "immagini_opere/opera_" . $id_opera;

    

    foreach ($formati as $formato) {

        $percorso = $base_path . "." . $formato;

        if (file_exists($percorso)) {

            return $percorso;

        }

    }

    return null; // Nessuna immagine trovata

}



// Dati per il grafico delle tipologie

$sql_tipologie = "

    SELECT t.categoria, COUNT(*) as totale

    FROM opera o

    JOIN esposizione e ON o.id = e.id_opera

    JOIN tipologia t ON o.id_tipologia = t.id

    WHERE e.id_mostra = $id_mostra

    GROUP BY t.id

    ORDER BY totale DESC

";

$res_tipologie = $conn->query($sql_tipologie);

$tipologie_data = [];

if ($res_tipologie) {

    while ($row = $res_tipologie->fetch_assoc()) {

        $tipologie_data[] = $row;

    }

}



// Dati per il grafico delle tecniche

$sql_tecniche = "

    SELECT te.nome, COUNT(*) as totale

    FROM opera o

    JOIN esposizione e ON o.id = e.id_opera

    JOIN tecnica te ON o.id_tecnica = te.id

    WHERE e.id_mostra = $id_mostra

    GROUP BY te.id

    ORDER BY totale DESC

";

$res_tecniche = $conn->query($sql_tecniche);

$tecniche_data = [];

if ($res_tecniche) {

    while ($row = $res_tecniche->fetch_assoc()) {

        $tecniche_data[] = $row;

    }

}



// NUOVO: Dati per il grafico delle nazionalità degli artisti

$sql_paesi = "
    SELECT 
        a.nazionalita AS paese,  -- usiamo 'paese' per non cambiare la logica del JS
        COUNT(o.id) AS totale,   -- numero di opere
        COUNT(DISTINCT a.id) AS artisti
    FROM esposizione e
    JOIN opera o ON e.id_opera = o.id
    JOIN autore a ON o.id_autore = a.id
    WHERE e.id_mostra = $id_mostra
    GROUP BY a.nazionalita
    ORDER BY totale DESC
";

$res_nazionalita = $conn->query($sql_paesi);

$nazionalita_data = [];

if ($res_nazionalita) {
    while ($row = $res_nazionalita->fetch_assoc()) {
        $naz = $row['paese'] ?: 'Sconosciuto';
        $nazionalita_data[] = [
            'nazionalita' => $naz,
            'numero_opere' => (int)$row['totale'],
            'numero_artisti' => (int)$row['artisti']
        ];
    }
}




// DATI PER IL GRAFICO DEI PAESI PRESTATORI (Filtrato per questa mostra)

$sql_paesi_query = "

    SELECT 

        pr.paese, 

        COUNT(p.id) AS totale

    FROM prestito p

    INNER JOIN prestatore pr ON p.organizzatore = pr.id

    INNER JOIN esposizione e ON p.id_opera = e.id_opera

    WHERE e.id_mostra = $id_mostra

    GROUP BY pr.paese

";



$res_paesi = $conn->query($sql_paesi_query);

$paesi_stats = []; // Usiamo un nome univoco per non confonderci

if ($res_paesi) {

    while ($row = $res_paesi->fetch_assoc()) {

        $paesi_stats[] = $row;

    }

}

$res_paesi = $conn->query($sql_paesi);

$paesi_data = [];

if ($res_paesi) {

    while ($row = $res_paesi->fetch_assoc()) {

        $paesi_data[] = $row;

    }

}



function formatDateItalian($date) {

    $formatter = new IntlDateFormatter(

        'it_IT',

        IntlDateFormatter::LONG,

        IntlDateFormatter::NONE

    );

    $timestamp = strtotime($date);

    return $formatter->format($timestamp);

}







$tipologie_json = json_encode(array_column($tipologie_data, 'categoria'));

$tipologie_totali_json = json_encode(array_column($tipologie_data, 'totale'));

$tecniche_json = json_encode(array_column($tecniche_data, 'nome'));

$tecniche_totali_json = json_encode(array_column($tecniche_data, 'totale'));



// NUOVO: JSON per il grafico dei paesi

$paesi_json = json_encode(array_column($paesi_stats, 'paese'));

$paesi_totali_json = json_encode(array_column($paesi_stats, 'totale'));



// NUOVO: JSON per il grafico delle nazionalità

$nazionalita_json = json_encode(array_column($nazionalita_data, 'nazionalita'));
$nazionalita_opere_json = json_encode(array_column($nazionalita_data, 'numero_opere'));
$nazionalita_artisti_json = json_encode(array_column($nazionalita_data, 'numero_artisti'));





$conn->close();

?>


<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Dettaglio Mostra</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap');

    body {
        font-family: 'Playfair Display', serif;
        padding: 2rem;
        background: #f8f5f0; /* Sfondo avorio come il sito */
        color: #2a2a2a;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* TASTI NAVIGAZIONE */
    .navigation-buttons {
        margin-bottom: 2rem;
        text-align: left;
    }

    .nav-btn {
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
        margin-right: 10px;
        font-family: 'Playfair Display', serif;
        box-shadow: 0 4px 10px rgba(139, 94, 60, 0.2);
        border: none;
    }

    .nav-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(139, 94, 60, 0.4);
        background: linear-gradient(45deg, #c75b3c, #f1c27d);
        color: white !important;
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

    /* TITOLO MOSTRA */
    .mostra-header {
        text-align: center;
        margin-bottom: 3rem;
        background: #faf7f0; /* Sfondo tela */
        border: 6px solid #c5a46d; /* Cornice oro */
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .mostra-title {
        color: #8b5e3c;
        margin: 0 0 1rem 0;
        font-weight: 700;
        font-size: 2.5rem;
    }

    .mostra-details {
        color: #6b4e31;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    /* SEZIONE GRAFICI - LAYOUT 2x2 OTTIMIZZATO */
.grafici-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.2rem;
    margin: 2.5rem 0;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.grafico-box {
    background: #faf7f0;
    border: 4px solid #c5a46d; /* Bordo più sottile */
    border-radius: 8px;
    padding: 1rem; /* Padding ridotto */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
    position: relative;
    height: 280px; /* Altezza fissa più compatta */
}

/* Effetto cornice interna più sottile */
.grafico-box::before {
    content: "";
    position: absolute;
    top: 6px;
    left: 6px;
    right: 6px;
    bottom: 6px;
    border: 1px solid #d4b185;
    border-radius: 4px;
    pointer-events: none;
}

.grafico-box h3 {
    text-align: center;
    color: #6b4e31;
    margin: 0 0 0.8rem 0; /* Margine ridotto */
    font-size: 1rem; /* Font più piccolo */
    position: relative;
    z-index: 2;
}

.chart-container {
    height: 200px; /* Altezza grafico ridotta */
    position: relative;
    z-index: 2;
}

/* GRIGLIA OPERE - DIMENSIONI ORIGINALI RIPRISTINATE */
.opere-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

/* QUADRI OPERE CON IMMAGINI - DIMENSIONI ORIGINALI */
.opera-frame {
    background-color: #c5a46d; /* Cornice oro */
    padding: 12px;
    border: 6px solid #6b4e31; /* Legno scuro */
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.opera-frame:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
}

.opera-frame.evidenziata {
    border-color: #e76f51;
    box-shadow: 0 0 20px rgba(231, 111, 81, 0.5);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(231, 111, 81, 0.5); 
    }
    50% { 
        box-shadow: 0 0 30px rgba(231, 111, 81, 0.8); 
    }
}

/* Gancio e chiodino per i quadri */
.opera-frame::before {
    content: "";
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 20px;
    background: #6b4e31;
    z-index: 10;
}

.opera-frame::after {
    content: "";
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: #6b4e31;
    border-radius: 50%;
    z-index: 10;
}

.opera-inner {
    background: #faf7f0;
    border: 1px solid #ccc;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

/* TITOLO SOPRA L'IMMAGINE */
.opera-title-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(180deg, rgba(107, 78, 49, 0.9) 0%, rgba(107, 78, 49, 0.7) 70%, transparent 100%);
    color: #faf7f0;
    padding: 1rem 1rem 2rem 1rem;
    font-size: 1.1rem;
    font-weight: 700;
    text-align: center;
    z-index: 5;
    line-height: 1.2;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

/* IMMAGINE DELL'OPERA - DIMENSIONI ORIGINALI */
.opera-image {
    width: 100%;
    height: 300px; /* Altezza originale ripristinata */
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.opera-frame:hover .opera-image {
    transform: scale(1.05);
}

/* IMMAGINE PLACEHOLDER SE NON DISPONIBILE - DIMENSIONI ORIGINALI */
.opera-placeholder {
    width: 100%;
    height: 300px; /* Altezza originale ripristinata */
    background: linear-gradient(135deg, #faf7f0, #e8e0d0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #c5a46d;
    border: 2px dashed #d4b185;
    position: relative;
}

.opera-placeholder::before {
    content: "🎨";
    font-size: 4rem;
    opacity: 0.6;
}

/* DETTAGLI OPERA SOTTO L'IMMAGINE */
.opera-details {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(0deg, rgba(107, 78, 49, 0.95) 0%, rgba(107, 78, 49, 0.8) 70%, transparent 100%);
    color: #faf7f0;
    padding: 2rem 1rem 1rem 1rem;
    font-size: 0.9rem;
    line-height: 1.4;
    z-index: 5;
}

.opera-author {
    font-weight: 600;
    color: #f4a261;
    margin-bottom: 0.3rem;
    font-size: 1rem;
}

.opera-info {
    margin-bottom: 0.2rem;
    opacity: 0.9;
}

/* RESPONSIVE - GRAFICI COMPATTI, OPERE NORMALI */
@media (max-width: 900px) {
    /* Grafici responsive */
    .grafici-container {
        grid-template-columns: 1fr;
        max-width: 400px;
        gap: 1rem;
    }
    
    .grafico-box {
        height: 300px;
    }
    
    .chart-container {
        height: 220px;
    }
    
    /* Opere mantengono dimensioni normali */
    .opere-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    /* Grafici */
    .grafico-box {
        height: 280px;
        padding: 0.8rem;
    }
    
    .chart-container {
        height: 200px;
    }
    
    .grafico-box h3 {
        font-size: 0.9rem;
        margin-bottom: 0.6rem;
    }
    
    /* Opere - dimensioni mobile originali */
    .opera-image, .opera-placeholder {
        height: 250px; /* Come nell'originale */
    }
    
    .mostra-title {
        font-size: 2rem;
    }
}

@media (max-width: 600px) {
    .grafici-container {
        max-width: 350px;
    }
}

</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if ($mostra): ?>

<!-- TASTI NAVIGAZIONE -->
<div class="navigation-buttons">
    <a href="index.php" class="nav-btn home">Home</a>
    <a href="archivio.php" class="nav-btn archive">Archivio Mostre</a>
</div>

<!-- HEADER MOSTRA -->
<div class="mostra-header">
    <h1 class="mostra-title"><?= htmlspecialchars($mostra['titolo']) ?></h1>
    <div class="mostra-details">
        <strong>📍 Sede:</strong> <?= htmlspecialchars($mostra['nome_collocazione']) ?><br>
        <strong>👤 Curatore:</strong> <?= htmlspecialchars($mostra['curatore']) ?><br>
        <strong>📅 Periodo:</strong> 
        dal <?= formatDateItalian($mostra['data_inizio']) ?> 
        al <?= formatDateItalian($mostra['data_fine']) ?>
    </div>
</div>

<!-- GRAFICI - AGGIORNATO CON IL GRAFICO DELLE NAZIONALITÀ -->
<?php if (count($tipologie_data) > 0 || count($tecniche_data) > 0 || count($paesi_data) > 0 || count($nazionalita_data) > 0): ?>
<div class="grafici-container">
    <?php if (count($tipologie_data) > 0): ?>
        <div class="grafico-box">
            <h3>📊 Tipologie di Opere</h3>
            <div class="chart-container">
                <canvas id="tipologieChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($tecniche_data) > 0): ?>
        <div class="grafico-box">
            <h3>🎨 Tecniche Utilizzate</h3>
            <div class="chart-container">
                <canvas id="tecnicheChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($paesi_data) > 0): ?>
        <div class="grafico-box">
            <h3>🌍 Paesi Prestatori</h3>
            <div class="chart-container">
                <canvas id="paesiChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($nazionalita_data) > 0): ?>
        <div class="grafico-box">
            <h3>🏛️ Nazionalità Artisti</h3>
            <div class="chart-container">
                <canvas id="nazionalitaChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>


<!-- OPERE CON IMMAGINI -->
<?php if (count($opere) > 0): ?>
    <div class="opere-grid">
        <?php foreach ($opere as $opera): ?>
            <a href="opera.php?id=<?= $opera['id'] ?>">
                <div class="opera-frame <?= ($opera['id'] == $id_opera_evidenziata) ? 'evidenziata' : '' ?>">
                    <div class="opera-inner">
                        <!-- TITOLO SOPRA L'IMMAGINE -->
                        <div class="opera-title-overlay">
                            <?= htmlspecialchars($opera['titolo']) ?>
                        </div>

                        <!-- IMMAGINE DELL'OPERA CON SUPPORTO MULTI-FORMATO -->
                        <?php 
                        $image_path = trovaImmagineOpera($opera['id']);
                        ?>
                        
                        <?php if ($image_path): ?>
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($opera['titolo']) ?>" class="opera-image">
                        <?php else: ?>
                            <div class="opera-placeholder"></div>
                        <?php endif; ?>

                        <!-- DETTAGLI SOTTO L'IMMAGINE -->
                        <div class="opera-details">
                            <?php if ($opera['nome'] && $opera['cognome']): ?>
                                <div class="opera-author">
                                    👨‍🎨 <?= htmlspecialchars($opera['nome'] . ' ' . $opera['cognome']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($opera['datazione']): ?>
                                <div class="opera-info">📅 <?= htmlspecialchars($opera['datazione']) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($opera['tipologia_nome']): ?>
                                <div class="opera-info">🎭 <?= htmlspecialchars($opera['tipologia_nome']) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($opera['tecnica_nome']): ?>
                                <div class="opera-info">🎨 <?= htmlspecialchars($opera['tecnica_nome']) ?></div>
                         
                            
                           
                           <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="text-align: center; color: #6b4e31; font-size: 1.2rem;">
        Nessuna opera trovata per questa mostra.
    </p>
<?php endif; ?>

<script>
// Colori coordinati con il sito (palette oro/marrone)
const siteColors = [
    '#8b5e3c', '#c5a46d', '#6b4e31', '#d4b185', 
    '#e4a567', '#c75b3c', '#f4a261', '#b85450',
    '#daa520', '#cd853f', '#a0522d', '#b8860b'
];

<?php if (count($tipologie_data) > 0): ?>
// Grafico Tipologie
const ctxTipologie = document.getElementById('tipologieChart').getContext('2d');
new Chart(ctxTipologie, {
    type: 'pie',
    data: {
        labels: <?= $tipologie_json ?>,
        datasets: [{
            data: <?= $tipologie_totali_json ?>,
            backgroundColor: siteColors.slice(0, <?= count($tipologie_data) ?>),
            borderColor: '#faf7f0',
            borderWidth: 2,
            hoverBorderWidth: 3,
            hoverBorderColor: '#6b4e31'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        family: 'Playfair Display',
                        size: 11
                    },
                    color: '#6b4e31',
                    padding: 10,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(107, 78, 49, 0.9)',
                titleColor: '#faf7f0',
                bodyColor: '#faf7f0',
                borderColor: '#c5a46d',
                borderWidth: 2,
                cornerRadius: 6,
                titleFont: {
                    family: 'Playfair Display',
                    size: 12,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Playfair Display',
                    size: 11
                }
            }
        },
        animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});
<?php endif; ?>

<?php if (count($tecniche_data) > 0): ?>
// Grafico Tecniche
const ctxTecniche = document.getElementById('tecnicheChart').getContext('2d');
new Chart(ctxTecniche, {
    type: 'doughnut',
    data: {
        labels: <?= $tecniche_json ?>,
        datasets: [{
            data: <?= $tecniche_totali_json ?>,
            backgroundColor: siteColors.slice(0, <?= count($tecniche_data) ?>),
            borderColor: '#faf7f0',
            borderWidth: 2,
            hoverBorderWidth: 3,
            hoverBorderColor: '#6b4e31'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        family: 'Playfair Display',
                        size: 11
                    },
                    color: '#6b4e31',
                    padding: 10,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(107, 78, 49, 0.9)',
                titleColor: '#faf7f0',
                bodyColor: '#faf7f0',
                borderColor: '#c5a46d',
                borderWidth: 2,
                cornerRadius: 6,
                titleFont: {
                    family: 'Playfair Display',
                    size: 12,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Playfair Display',
                    size: 11
                }
            }
        },
        animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});
<?php endif; ?>

<?php if (count($paesi_data) > 0): ?>
// NUOVO: Grafico Paesi Prestatori
const ctxPaesi = document.getElementById('paesiChart').getContext('2d');
new Chart(ctxPaesi, {
    type: 'bar',
    data: {
        labels: <?= $paesi_json ?>,
        datasets: [{
            label: 'Numero di Prestiti',
            data: <?= $paesi_totali_json ?>,
            backgroundColor: siteColors.slice(0, <?= count($paesi_data) ?>),
            borderColor: '#6b4e31',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(107, 78, 49, 0.9)',
                titleColor: '#faf7f0',
                bodyColor: '#faf7f0',
                borderColor: '#c5a46d',
                borderWidth: 2,
                cornerRadius: 6,
                titleFont: {
                    family: 'Playfair Display',
                    size: 12,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Playfair Display',
                    size: 11
                },
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' prestit' + (context.parsed.y === 1 ? 'o' : 'i');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    color: '#6b4e31',
                    font: {
                        family: 'Playfair Display',
                        size: 10
                    }
                },
                grid: {
                    color: 'rgba(107, 78, 49, 0.1)'
                }
            },
            x: {
                ticks: {
                    color: '#6b4e31',
                    font: {
                        family: 'Playfair Display',
                        size: 10
                    },
                    maxRotation: 45,
                    minRotation: 0
                },
                grid: {
                    display: false
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});
<?php endif; ?>

<?php if (count($nazionalita_data) > 0): ?>
// ALTERNATIVA: Grafico a Barre Orizzontali per Nazionalità
const ctxNazionalita = document.getElementById('nazionalitaChart').getContext('2d');
new Chart(ctxNazionalita, {
    type: 'bar',
    data: {
        labels: <?= $nazionalita_json ?>,
        datasets: [{
            label: 'Numero di Opere',
            data: <?= $nazionalita_opere_json ?>,
            backgroundColor: siteColors.slice(0, <?= count($nazionalita_data) ?>),
            borderColor: '#6b4e31',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y', // Barre orizzontali
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(107, 78, 49, 0.9)',
                titleColor: '#faf7f0',
                bodyColor: '#faf7f0',
                borderColor: '#c5a46d',
                borderWidth: 2,
                cornerRadius: 6,
                titleFont: {
                    family: 'Playfair Display',
                    size: 12,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Playfair Display',
                    size: 11
                },
                callbacks: {
                    label: function(context) {
                        const opere = context.parsed.x;
                        const artisti = <?= $nazionalita_artisti_json ?>[context.dataIndex];
                        return [
                            opere + ' oper' + (opere === 1 ? 'a' : 'e'),
                            artisti + ' artist' + (artisti === 1 ? 'a' : 'i')
                        ];
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    color: '#6b4e31',
                    font: {
                        family: 'Playfair Display',
                        size: 10
                    }
                },
                grid: {
                    color: 'rgba(107, 78, 49, 0.1)'
                }
            },
            y: {
                ticks: {
                    color: '#6b4e31',
                    font: {
                        family: 'Playfair Display',
                        size: 10
                    }
                },
                grid: {
                    display: false
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeOutQuart'
        }
    }
});
<?php endif; ?>



</script>

<?php else: ?>
    <p style="text-align: center; color: #6b4e31; font-size: 1.2rem;">Mostra non trovata.</p>
<?php endif; ?>

</body>
</html>
