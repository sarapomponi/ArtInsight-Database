<?php
$conn = new mysqli('localhost', 'root', '', 'mostre');
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
$oggi = date('Y-m-d');

// Query per le mostre
$sql = "SELECT id, titolo, data_inizio, data_fine FROM mostra ORDER BY data_inizio DESC";
$res = $conn->query($sql);

// Query migliorata per il grafico dei movimenti artistici - ULTIMI 10 ANNI
$sql_movimenti = "
    SELECT 
    a.movimento,
    COUNT(DISTINCT m.id) AS numero_mostre
FROM mostra m
JOIN esposizione e ON m.id = e.id_mostra
JOIN opera o ON e.id_opera = o.id
JOIN autore a ON o.id_autore = a.id
WHERE m.data_inizio >= DATE_SUB(CURDATE(), INTERVAL 10 YEAR)
  AND a.movimento IS NOT NULL 
  AND TRIM(a.movimento) != ''
GROUP BY a.movimento
ORDER BY numero_mostre DESC
LIMIT 8

";

$res_movimenti = $conn->query($sql_movimenti);
$movimenti_data = [];

if ($res_movimenti) {
    while ($row = $res_movimenti->fetch_assoc()) {
        $movimento = trim($row['movimento']);
        $anno = isset($row['anno']) ? $row['anno'] : 'Sconosciuto'; 
        $numero = isset($row['numero_mostre']) ? (int)$row['numero_mostre'] : 0;
        
        if (!isset($movimenti_data[$movimento])) {
            $movimenti_data[$movimento] = 0;
        }
        $movimenti_data[$movimento] += $numero;
    }
}

// Ordina per numero di mostre e prendi solo i top 8
arsort($movimenti_data);
$movimenti_data = array_slice($movimenti_data, 0, 8, true);

$movimenti_labels = json_encode(array_keys($movimenti_data));
$movimenti_values = json_encode(array_values($movimenti_data));
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Archivio Mostre</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap');
    
    body {
       font-family: 'Playfair Display', serif;
      margin: 2rem;
      background-color: #f8f5f0; /* avorio */
      color: #2a2a2a; /* antracite */
    }

    /* TASTO HOME IN ALTO A SINISTRA */
    .home-button {
      position: absolute;
      top: 2rem;
      left: 2rem;
      z-index: 100;
    }

    .back-link {
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

    .back-link::before {
      content: "🏛️";
      font-size: 1.2rem;
      display: inline-block;
    }

    .back-link:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(139, 94, 60, 0.4);
      background: linear-gradient(45deg, #c75b3c, #f1c27d);
      color: white !important;
    }

    .back-link::after {
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

    .back-link:hover::after {
      transform: rotate(45deg) translateX(100%);
    }

    h1 {
      text-align: center;
      color: #8b5e3c;
      margin-bottom: 2rem;
      margin-top: 1rem; /* Spazio per il tasto home */
    }

    .mostra-lista {
      max-width: 900px;
      margin: 2rem auto;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 2rem;
    }

    .mostra {
      background-color: #c5a46d; /* oro antico cornice */
      padding: 10px;
      border: 6px solid #6b4e31; /* legno scuro */
      border-radius: 6px;
      aspect-ratio: 1 / 1; /* quadrato */
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); /* ombra principale */
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .mostra:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
    }

    /* texture tela */
    .mostra-inner {
      background: #faf7f0 url('https://www.transparenttextures.com/patterns/canvas.png');
      background-size: 200px 200px;
      border: 1px solid #ccc;
      padding: 1rem;
      text-align: center;
      width: 90%;
      height: 90%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      box-shadow: inset 0 0 8px rgba(0,0,0,0.1); /* ombra interna leggera */
    }

    .mostra a {
      font-size: 1.2rem;
      color: #8b5e3c;
      font-weight: bold;
      text-decoration: none;
    }

    .mostra small {
      display: block;
      color: #6b4e31;
      margin-top: 0.3rem;
      font-size: 0.9rem;
    }

    /* gancio e chiodino */
    .mostra::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      transform: translateX(-50%);
      width: 2px;
      height: 20px;
      background: #6b4e31;
    }
    .mostra::after {
      content: "";
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      width: 8px;
      height: 8px;
      background: #6b4e31;
      border-radius: 50%;
    }

    /* SEZIONE GRAFICO PICCOLA SOTTO LE MOSTRE */
    .grafico-section {
      max-width: 500px;
      margin: 3rem auto 2rem auto;
      background: #faf7f0;
      border: 4px solid #c5a46d;
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }

    .grafico-section h3 {
      text-align: center;
      color: #6b4e31;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }

    .chart-container {
      height: 250px;
      position: relative;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .home-button {
        position: relative;
        top: 0;
        left: 0;
        margin-bottom: 1rem;
        text-align: left;
      }

      body {
        margin: 1rem;
      }

      h1 {
        margin-top: 0;
      }
      
      .grafico-section {
        margin: 2rem 1rem;
        padding: 1rem;
        max-width: 90%;
      }
      
      .chart-container {
        height: 200px;
      }
	  
	  /* Migliora l'aspetto del grafico */
.grafico-section {
    max-width: 500px;
    margin: 3rem auto 2rem auto;
    background: #faf7f0;
    border: 4px solid #c5a46d;
    border-radius: 12px; /* Angoli più arrotondati */
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15); /* Ombra più pronunciata */
    position: relative;
}

/* Effetto cornice interna */
.grafico-section::before {
    content: "";
    position: absolute;
    top: 8px;
    left: 8px;
    right: 8px;
    bottom: 8px;
    border: 1px solid #d4b185;
    border-radius: 8px;
    pointer-events: none;
}

.grafico-section h3 {
    text-align: center;
    color: #6b4e31;
    margin-bottom: 1.2rem;
    font-size: 1.1rem;
    position: relative;
    z-index: 2;
}

.chart-container {
    height: 250px;
    position: relative;
    z-index: 2;
}

      
      .mostra-lista {
        margin: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
      }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- TASTO HOME IN ALTO A SINISTRA -->
  <div class="home-button">
    <a href="index.php" class="back-link">Torna alla Home</a>
  </div>

  <h1>Archivio Mostre</h1>

  <!-- LISTA MOSTRE -->
  <div class="mostra-lista">
    <?php if ($res && $res->num_rows > 0): ?>
      <?php while ($row = $res->fetch_assoc()):
        $in_corso = $row['data_fine'] >= $oggi;
      ?>
        <div class="mostra">
          <div class="mostra-inner">
            <a href="mostra.php?id=<?= $row['id']; ?>"><?= htmlspecialchars($row['titolo']); ?></a>
            <small>
              dal <?= date('d/m/Y', strtotime($row['data_inizio'])); ?> al <?= date('d/m/Y', strtotime($row['data_fine'])); ?>
              (<?= $in_corso ? 'In corso' : 'Conclusa'; ?>)
            </small>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Nessuna mostra trovata.</p>
    <?php endif; ?>
  </div>

  <!-- SEZIONE GRAFICO PICCOLA SOTTO LE MOSTRE -->
<?php if (!empty($movimenti_data)): ?>
<div class="grafico-section">
  <h3>📊 Movimenti Artistici più Rappresentati (ultimi 10 anni)</h3>
  
  <!-- ✅ SPIEGAZIONE CON STILE COORDINATO -->
  <div style="text-align: center; margin-bottom: 1.2rem;">
    <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1rem; font-family: 'Inter', sans-serif; line-height: 1.4;">
  Numero di mostre che includono opere di ciascun movimento artistico (ultimi 10 anni)
</p>
    <small style="color: #666; font-size: 0.85rem; font-family: 'Inter', sans-serif; font-style: italic;">
      Ogni movimento può essere presente in più mostre
    </small>
  </div>
  
  <div class="chart-container">
    <canvas id="movimentiChart"></canvas>
  </div>
</div>
<?php endif; ?>

  


  <script>
<?php if (!empty($movimenti_data)): ?>
const ctx = document.getElementById('movimentiChart').getContext('2d');

// NUOVA PALETTE: Colori più contrastati e vivaci
const colors = [
    '#8b5e3c',  // Marrone originale
    '#d2691e',  // Arancione cioccolato
    '#b22222',  // Rosso mattone
    '#daa520',  // Oro scuro
    '#8fbc8f',  // Verde salvia
    '#cd853f',  // Marrone sabbia
    '#4682b4',  // Blu acciaio
    '#9370db'   // Viola medio
];

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= $movimenti_labels ?>,
        datasets: [{
            data: <?= $movimenti_values ?>,
            backgroundColor: colors,
            borderColor: '#faf7f0',
            borderWidth: 3, // Bordo più spesso per maggiore definizione
            hoverBorderWidth: 4,
            hoverBorderColor: '#6b4e31',
            // NUOVO: Effetto hover più evidente
            hoverBackgroundColor: colors.map(color => color + 'dd'), // Aggiunge trasparenza
            hoverOffset: 8 // Sposta il segmento quando hover
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
                        size: 11,
                        weight: '600' // Font più bold per leggibilità
                    },
                    color: '#6b4e31',
                    padding: 12, // Più spazio tra le etichette
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 12, // Indicatori più grandi
                    boxHeight: 12
                }
            },
            tooltip: {
                backgroundColor: 'rgba(107, 78, 49, 0.95)', // Più opaco
                titleColor: '#faf7f0',
                bodyColor: '#faf7f0',
                borderColor: '#c5a46d',
                borderWidth: 2,
                cornerRadius: 8, // Angoli più arrotondati
                titleFont: {
                    family: 'Playfair Display',
                    size: 13, // Font più grande
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Playfair Display',
                    size: 12 // Font più grande
                },
                padding: 12, // Più padding interno
                callbacks: {
    label: function(context) {
        const total = context.dataset.data.reduce((a, b) => a + b, 0);
        const percentage = Math.round((context.parsed / total) * 100);
        const numero = context.parsed;
        
        // Versione compatta con operatore ternario
        const testo = numero === 1 ? numero + ' mostra' : numero + ' mostre';
        
        return context.label + ': ' + testo + ' (' + percentage + '%)';
    }
}

            }
        },
        animation: {
            animateRotate: true,
            animateScale: true,
            duration: 1800, // Animazione più lunga
            easing: 'easeOutQuart'
        },
        // NUOVO: Configurazione per maggiore interattività
        interaction: {
            intersect: false,
            mode: 'point'
        }
    }
});
<?php endif; ?>
</script>


</body>
</html>

<?php $conn->close(); ?>
