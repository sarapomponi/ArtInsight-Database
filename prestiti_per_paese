<?php
$conn = new mysqli('localhost', 'root', '', 'mostre');
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Recupera il numero di prestiti per paese
$sql = "
    SELECT p.paese, COUNT(*) AS numero_prestiti
    FROM prestito pr
    JOIN prestatore p ON pr.organizzatore = p.id
    GROUP BY p.paese
    ORDER BY numero_prestiti DESC
";

$result = $conn->query($sql);

// Prepara array per JavaScript
$paesi = [];
$conteggi = [];

while ($row = $result->fetch_assoc()) {
    $paesi[] = $row['paese'];
    $conteggi[] = $row['numero_prestiti'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Paesi che Prestano di Più</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body { font-family: sans-serif; padding: 20px; }
    h2 { margin-bottom: 20px; text-align: center; }
    canvas { display: block; margin: auto; }
    </style>

</head>
<body>
    <h2>Paesi che Prestano di Più</h2>
    <div style="max-width: 500px; margin: auto;">
    <canvas id="graficoPrestiti" width="500" height="300"></canvas>
</div>


    <script>
        const ctx = document.getElementById('graficoPrestiti').getContext('2d');
        const grafico = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($paesi) ?>,
                datasets: [{
                    label: 'Numero Prestiti',
                    data: <?= json_encode($conteggi) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Numero Prestiti'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Paesi'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
