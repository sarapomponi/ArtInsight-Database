<?php
$conn = new mysqli('localhost', 'root', '', 'mostre');

$mostra_id = (int)($_POST['mostra_id'] ?? 0);

// Conta gli autori per nazionalità nella mostra selezionata
$sql = "
    SELECT a.nazionalita, COUNT(*) as totale
    FROM partecipazioni p
    JOIN autore a ON a.id = p.id_autore
    WHERE p.id_mostra = ?
    GROUP BY a.nazionalita
    ORDER BY totale DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mostra_id);
$stmt->execute();
$result = $stmt->get_result();

// Totale artisti
$total_artist_query = "
    SELECT COUNT(*) AS totale
    FROM partecipazioni
    WHERE id_mostra = ?
";
$stmt_total = $conn->prepare($total_artist_query);
$stmt_total->bind_param("i", $mostra_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result()->fetch_assoc();
$totale_artisti = $total_result['totale'];

echo "<h2>Distribuzione per nazionalità</h2>";

$found_majority = false;

while ($row = $result->fetch_assoc()) {
    $naz = htmlspecialchars($row['nazionalita']);
    $count = $row['totale'];
    $percent = round(($count / $totale_artisti) * 100);

    echo "<p>$naz: $count artisti ($percent%)</p>";

    if ($percent > 50) {
        echo "<p><strong>→ Maggioranza di artisti provenienti da $naz</strong></p>";
        $found_majority = true;
    }
}

if (!$found_majority) {
    echo "<p><em>Nessuna nazionalità prevalente (nessuna oltre il 50%).</em></p>";
}

if (!$found_majority && $result->num_rows > 0) {
    $result->data_seek(0); // Torna all'inizio del risultato
    $prima = $result->fetch_assoc();
    echo "<p><strong>→ Nazionalità più rappresentata:</strong> " . htmlspecialchars($prima['nazionalita']) . " con " . $prima['totale'] . " artisti (" . round(($prima['totale'] / $totale_artisti) * 100) . "%)</p>";
}


$stmt->close();
$stmt_total->close();
$conn->close();
?>
