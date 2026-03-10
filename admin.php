<?php
// Default WAMP local database connection. 
// Note: In a production environment, credentials should be moved to a secure, hidden config file.
$conn = new mysqli('localhost','root','','mostre');
if($conn->connect_error){ 
    die("Connessione fallita: ".$conn->connect_error); 
}

// Inizializza variabili
$modifica_id = null;
$modifica_tab = null;
$messaggio = '';
$tipo_messaggio = '';

// Gestione form
if(isset($_POST['azione'])){
    $azione = $_POST['azione'];

    try {
        // AGGIUNGI
        if($azione=='aggiungi_mostra'){
            $titolo = $conn->real_escape_string($_POST['titolo']);
            $curatore = $conn->real_escape_string($_POST['curatore']);
            $sede = intval($_POST['sede']);
            $data_inizio = $_POST['data_inizio'];
            $data_fine = $_POST['data_fine'];
            
            $result = $conn->query("INSERT INTO mostra(titolo,curatore,sede,data_inizio,data_fine) VALUES('$titolo','$curatore',$sede,'$data_inizio','$data_fine')");
            if($result) {
                $messaggio = "Mostra '$titolo' aggiunta con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='aggiungi_autore'){
            $nome=$conn->real_escape_string($_POST['nome']);
            $cognome=$conn->real_escape_string($_POST['cognome']);
            $movimento=$conn->real_escape_string($_POST['movimento']);
            $nazionalita=$conn->real_escape_string($_POST['nazionalita']);
            
            $result = $conn->query("INSERT INTO autore(nome,cognome,movimento,nazionalita) VALUES('$nome','$cognome','$movimento','$nazionalita')");
            if($result) {
                $messaggio = "Autore '$nome $cognome' aggiunto con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='aggiungi_opera'){
            $titolo=$conn->real_escape_string($_POST['titolo']);
            $datazione=intval($_POST['datazione']);
            $id_collocazione=intval($_POST['id_collocazione']);
            $id_autore=intval($_POST['id_autore']);
            $id_tipologia=intval($_POST['id_tipologia']);
            $id_tecnica=intval($_POST['id_tecnica']) ?: 'NULL';
            
            // Inserisci opera
            $result = $conn->query("INSERT INTO opera(titolo,datazione,id_collocazione,id_autore,id_tipologia,id_tecnica) VALUES('$titolo',$datazione,$id_collocazione,$id_autore,$id_tipologia,$id_tecnica)");
            
            if($result) {
                $opera_id = $conn->insert_id;
                
                // Gestisci temi se selezionati
                if(isset($_POST['temi']) && is_array($_POST['temi'])){
                    foreach($_POST['temi'] as $tema_id){
                        $tema_id = intval($tema_id);
                        $conn->query("INSERT INTO opera_tema(id_opera,id_tema) VALUES($opera_id,$tema_id)");
                    }
                }
                
                // Gestisci mostra se selezionata
                if(!empty($_POST['id_mostra'])){
                    $id_mostra = intval($_POST['id_mostra']);
                    $conn->query("INSERT INTO esposizione(id_opera,id_mostra) VALUES($opera_id,$id_mostra)");
                }
                
                // Gestisci prestito se specificato
                if(!empty($_POST['inizio_prestito']) && !empty($_POST['fine_prestito'])){
                    $inizio = $_POST['inizio_prestito'];
                    $fine = $_POST['fine_prestito'];
                    $organizzatore = intval($_POST['organizzatore']);
                    $conn->query("INSERT INTO prestito(id_opera,organizzatore,inizio,fine) VALUES($opera_id, $organizzatore, '$inizio', '$fine')");
                }
                
                $messaggio = "Opera '$titolo' aggiunta con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // AGGIUNGI TIPOLOGIE E TECNICHE
        if($azione=='aggiungi_tipologia'){
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("INSERT INTO tipologia(categoria) VALUES('$nome')");
            if($result) {
                $messaggio = "Tipologia '$nome' aggiunta con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='aggiungi_tecnica'){
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("INSERT INTO tecnica(nome) VALUES('$nome')");
            if($result) {
                $messaggio = "Tecnica '$nome' aggiunta con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // AGGIUNGI COLLOCAZIONI, TEMI E PRESTATORI
        if($azione=='aggiungi_collocazione'){
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("INSERT INTO collocazione(nome) VALUES('$nome')");
            if($result) {
                $messaggio = "Collocazione '$nome' aggiunta con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='aggiungi_tema'){
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("INSERT INTO tema(nome) VALUES('$nome')");
            if($result) {
                $messaggio = "Tema '$nome' aggiunto con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        // Azioni per prestatori
        if($azione=='aggiungi_prestatore'){
            $nome_ente = trim($_POST['nome_ente']);
            $paese = trim($_POST['paese']);
            
            if($nome_ente && $paese){
                // Prima inserisci/trova l'ente
                $check_ente = $conn->query("SELECT id_ente FROM enti WHERE nome_ente = '".mysqli_real_escape_string($conn, $nome_ente)."'");
                
                if($check_ente->num_rows > 0){
                    // L'ente esiste già
                    $id_ente = $check_ente->fetch_assoc()['id_ente'];
                } else {
                    // Crea nuovo ente
                    $conn->query("INSERT INTO enti (nome_ente) VALUES ('".mysqli_real_escape_string($conn, $nome_ente)."')");
                    $id_ente = $conn->insert_id;
                }
                
                // Poi inserisci il prestatore
                $result = $conn->query("INSERT INTO prestatore (id_ente, paese) VALUES ($id_ente, '".mysqli_real_escape_string($conn, $paese)."')");
                
                if($result){
                    $messaggio = "Prestatore '$nome_ente' aggiunto con successo!";
                    $tipo_messaggio = 'success';
                } else {
                    $messaggio = "Errore nell'aggiunta del prestatore: " . $conn->error;
                    $tipo_messaggio = 'error';
                }
            } else {
                $messaggio = "Tutti i campi sono obbligatori!";
                $tipo_messaggio = 'error';
            }
        }

        // MODIFICA
        if($azione=='modifica_mostra'){
            $id=intval($_POST['id']);
            $titolo=$conn->real_escape_string($_POST['titolo']);
            $curatore=$conn->real_escape_string($_POST['curatore']);
            $sede=intval($_POST['sede']);
            $data_inizio=$_POST['data_inizio'];
            $data_fine=$_POST['data_fine'];
            
            $result = $conn->query("UPDATE mostra SET titolo='$titolo',curatore='$curatore',sede=$sede,data_inizio='$data_inizio',data_fine='$data_fine' WHERE id=$id");
            if($result) {
                $messaggio = "Mostra aggiornata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='modifica_autore'){
            $id=intval($_POST['id']);
            $nome=$conn->real_escape_string($_POST['nome']);
            $cognome=$conn->real_escape_string($_POST['cognome']);
            $movimento=$conn->real_escape_string($_POST['movimento']);
            $nazionalita=$conn->real_escape_string($_POST['nazionalita']);
            
            $result = $conn->query("UPDATE autore SET nome='$nome',cognome='$cognome',movimento='$movimento',nazionalita='$nazionalita' WHERE id=$id");
            if($result) {
                $messaggio = "Autore aggiornato con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='modifica_opera'){
            $id=intval($_POST['id']);
            $titolo=$conn->real_escape_string($_POST['titolo']);
            $datazione=intval($_POST['datazione']);
            $id_collocazione=intval($_POST['id_collocazione']);
            $id_autore=intval($_POST['id_autore']);
            $id_tipologia=intval($_POST['id_tipologia']);
            $id_tecnica=intval($_POST['id_tecnica']) ?: 'NULL';
            
            // Aggiorna opera
            $result = $conn->query("UPDATE opera SET titolo='$titolo',datazione=$datazione,id_collocazione=$id_collocazione,id_autore=$id_autore,id_tipologia=$id_tipologia,id_tecnica=$id_tecnica WHERE id=$id");
            
            if($result) {
                // Aggiorna temi
                $conn->query("DELETE FROM opera_tema WHERE id_opera=$id");
                if(isset($_POST['temi']) && is_array($_POST['temi'])){
                    foreach($_POST['temi'] as $tema_id){
                        $tema_id = intval($tema_id);
                        $conn->query("INSERT INTO opera_tema(id_opera,id_tema) VALUES($id,$tema_id)");
                    }
                }
                
                // Aggiorna esposizione
                $conn->query("DELETE FROM esposizione WHERE id_opera=$id");
                if(!empty($_POST['id_mostra'])){
                    $id_mostra = intval($_POST['id_mostra']);
                    $conn->query("INSERT INTO esposizione(id_opera,id_mostra) VALUES($id,$id_mostra)");
                }
                
                // Aggiorna prestito
                $conn->query("DELETE FROM prestito WHERE id_opera=$id");
                if(!empty($_POST['inizio_prestito']) && !empty($_POST['fine_prestito'])){
                    $inizio = $_POST['inizio_prestito'];
                    $fine = $_POST['fine_prestito'];
					$organizzatore = intval($_POST['organizzatore']);
                    $conn->query("INSERT INTO prestito(id_opera,organizzatore,inizio,fine) VALUES($id, $organizzatore, '$inizio', '$fine')");
                }
                
                $messaggio = "Opera aggiornata con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // MODIFICA TIPOLOGIE E TECNICHE
        if($azione=='modifica_tipologia'){
            $id=intval($_POST['id']);
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("UPDATE tipologia SET categoria='$nome' WHERE id=$id");
            if($result) {
                $messaggio = "Tipologia aggiornata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='modifica_tecnica'){
            $id=intval($_POST['id']);
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("UPDATE tecnica SET nome='$nome' WHERE id=$id");
            if($result) {
                $messaggio = "Tecnica aggiornata con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // MODIFICA COLLOCAZIONI, TEMI E PRESTATORI
        if($azione=='modifica_collocazione'){
            $id=intval($_POST['id']);
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("UPDATE collocazione SET nome='$nome' WHERE id=$id");
            if($result) {
                $messaggio = "Collocazione aggiornata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='modifica_tema'){
            $id=intval($_POST['id']);
            $nome=$conn->real_escape_string($_POST['nome']);
            $result = $conn->query("UPDATE tema SET nome='$nome' WHERE id=$id");
            if($result) {
                $messaggio = "Tema aggiornato con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='modifica_prestatore'){
            $id = (int)$_POST['id'];
            $nome_ente = trim($_POST['nome_ente']);
            $paese = trim($_POST['paese']);
            
            if($id && $nome_ente && $paese){
                // Trova/crea l'ente
                $check_ente = $conn->query("SELECT id_ente FROM enti WHERE nome_ente = '".mysqli_real_escape_string($conn, $nome_ente)."'");
                
                if($check_ente->num_rows > 0){
                    $id_ente = $check_ente->fetch_assoc()['id_ente'];
                } else {
                    $conn->query("INSERT INTO enti (nome_ente) VALUES ('".mysqli_real_escape_string($conn, $nome_ente)."')");
                    $id_ente = $conn->insert_id;
                }
                
                // Aggiorna il prestatore
                $result = $conn->query("UPDATE prestatore SET id_ente = $id_ente, paese = '".mysqli_real_escape_string($conn, $paese)."' WHERE id = $id");
                
                if($result){
                    $messaggio = "Prestatore modificato con successo!";
                    $tipo_messaggio = 'success';
                } else {
                    $messaggio = "Errore nella modifica: " . $conn->error;
                    $tipo_messaggio = 'error';
                }
            } else {
                $messaggio = "Tutti i campi sono obbligatori!";
                $tipo_messaggio = 'error';
            }
        }

        // ELIMINA
        if($azione=='elimina_mostra'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM mostra WHERE id=$id");
            if($result) {
                $messaggio = "Mostra eliminata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='elimina_autore'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM autore WHERE id=$id");
            if($result) {
                $messaggio = "Autore eliminato con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='elimina_opera'){ 
            $id = intval($_POST['id']);
            // Prima elimina le relazioni
            $conn->query("DELETE FROM opera_tema WHERE id_opera=$id");
            $conn->query("DELETE FROM esposizione WHERE id_opera=$id");
            $conn->query("DELETE FROM prestito WHERE id_opera=$id");
            // Poi elimina l'opera
            $result = $conn->query("DELETE FROM opera WHERE id=$id");
            if($result) {
                $messaggio = "Opera eliminata con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // ELIMINA TIPOLOGIE E TECNICHE
        if($azione=='elimina_tipologia'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM tipologia WHERE id=$id");
            if($result) {
                $messaggio = "Tipologia eliminata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='elimina_tecnica'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM tecnica WHERE id=$id");
            if($result) {
                $messaggio = "Tecnica eliminata con successo!";
                $tipo_messaggio = 'success';
            }
        }

        // ELIMINA COLLOCAZIONI, TEMI E PRESTATORI
        if($azione=='elimina_collocazione'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM collocazione WHERE id=$id");
            if($result) {
                $messaggio = "Collocazione eliminata con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='elimina_tema'){ 
            $id = intval($_POST['id']);
            $result = $conn->query("DELETE FROM tema WHERE id=$id");
            if($result) {
                $messaggio = "Tema eliminato con successo!";
                $tipo_messaggio = 'success';
            }
        }
        
        if($azione=='elimina_prestatore'){
            $id = (int)$_POST['id'];
            if($id){
                // Controlla se ha prestiti attivi
                $check = $conn->query("SELECT COUNT(*) as count FROM prestito WHERE organizzatore = $id");
                $count = $check->fetch_assoc()['count'];
                
                if($count > 0){
                    $messaggio = "Impossibile eliminare: il prestatore ha $count prestiti attivi!";
                    $tipo_messaggio = 'error';
                } else {
                    $result = $conn->query("DELETE FROM prestatore WHERE id = $id");
                    if($result){
                        $messaggio = "Prestatore eliminato con successo!";
                        $tipo_messaggio = 'success';
                    } else {
                        $messaggio = "Errore nell'eliminazione: " . $conn->error;
                        $tipo_messaggio = 'error';
                    }
                }
            }
        }

        // Form di modifica
        if($azione=='mostra_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='mostra'; 
        }
        if($azione=='autore_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='autore'; 
        }
        if($azione=='opera_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='opera'; 
        }
        if($azione=='tipologia_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='tipologia'; 
        }
        if($azione=='tecnica_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='tecnica'; 
        }
        if($azione=='collocazione_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='collocazione'; 
        }
        if($azione=='tema_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='tema'; 
        }
        if($azione=='prestatore_modifica_form'){ 
            $modifica_id=intval($_POST['id']); 
            $modifica_tab='prestatore'; 
        }
        
    } catch (Exception $e) {
        $messaggio = "Errore: " . $e->getMessage();
        $tipo_messaggio = 'error';
    }
}

// Funzione per htmlspecialchars sicuro
function h($s){ 
    return htmlspecialchars($s ?? '', ENT_QUOTES); 
}

// Funzione per generare CSS inline
function getCSS() {
    return "
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #8b5e3c, #c75b3c); color: white; padding: 20px; text-align: center; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .header a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .message { padding: 15px; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #c75b3c; }
        .stat-label { color: #666; font-size: 0.9rem; text-transform: uppercase; }
        .selector { background: white; padding: 30px; border-radius: 10px; margin: 30px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .selector h2 { color: #8b5e3c; margin-bottom: 20px; }
        .selector-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .selector-btn { background: #f8f5f0; border: 2px solid #e0d6be; padding: 20px; text-align: center; border-radius: 10px; cursor: pointer; transition: all 0.3s; text-decoration: none; color: #8b5e3c; font-weight: bold; display: block; }
        .selector-btn:hover, .selector-btn.active { background: #c75b3c; color: white; transform: translateY(-3px); }
        .section { background: white; border-radius: 10px; margin: 30px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .section-header { background: #8b5e3c; color: white; padding: 20px; font-size: 1.3rem; font-weight: bold; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f5f0; font-weight: bold; color: #8b5e3c; }
        tr:hover { background: rgba(199, 91, 60, 0.05); }
        .actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 0.85rem; display: inline-block; }
        .btn-edit { background: #17a2b8; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-primary { background: #c75b3c; color: white; padding: 12px 25px; font-size: 1rem; }
        .btn-secondary { background: #6c757d; color: white; padding: 12px 25px; font-size: 1rem; margin-left: 10px; }
        .form-section { background: white; padding: 30px; border-radius: 10px; margin: 30px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-section.modify { border: 3px solid #17a2b8; }
        .form-title { color: #8b5e3c; font-size: 1.4rem; margin-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: bold; color: #8b5e3c; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem; }
        input:focus, select:focus { border-color: #c75b3c; outline: none; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-ended { color: #dc3545; font-weight: bold; }
        .count-badge { background: #e9ecef; padding: 3px 8px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; }
        .quick-manage { border: 2px solid #17a2b8; background: rgba(23, 162, 184, 0.05); margin: 20px 0; }
        .quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .quick-list { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: white; }
        .quick-item { padding: 5px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .quick-delete { background: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; font-size: 0.8rem; cursor: pointer; }
        @media (max-width: 768px) {
            .container { padding: 10px; }
            .form-grid { grid-template-columns: 1fr; }
            .selector-grid { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr; }
            .quick-grid { grid-template-columns: 1fr; }
            th, td { padding: 8px; font-size: 0.9rem; }
        }
    </style>";
}

// Ottieni statistiche
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM mostra) as mostre,
        (SELECT COUNT(*) FROM autore) as autori,
        (SELECT COUNT(*) FROM opera) as opere,
        (SELECT COUNT(*) FROM tipologia) as tipologie,
        (SELECT COUNT(*) FROM tecnica) as tecniche,
        (SELECT COUNT(*) FROM collocazione) as collocazioni,
        (SELECT COUNT(*) FROM tema) as temi,
        (SELECT COUNT(*) FROM prestatore) as prestatori,
        (SELECT COUNT(*) FROM mostra WHERE data_fine >= CURDATE()) as mostre_attive
")->fetch_assoc();

// Inizia output HTML
echo "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Panel ArtInsight</title>";

echo getCSS();

echo "</head>
<body>";

// Header
echo "<header class='header'>
        <h1>🏛️ ArtInsight Admin Panel</h1>
        <p>Pannello di Amministrazione Completo</p>
        <a href='http://localhost/prova/mostre/index.php'>🏠 Torna al Sito Pubblico</a>
      </header>";

echo "<div class='container'>";



// Ottieni statistiche
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM mostra) as mostre,
        (SELECT COUNT(*) FROM autore) as autori,
        (SELECT COUNT(*) FROM opera) as opere,
        (SELECT COUNT(*) FROM tipologia) as tipologie,
        (SELECT COUNT(*) FROM tecnica) as tecniche,
        (SELECT COUNT(*) FROM collocazione) as collocazioni,
        (SELECT COUNT(*) FROM tema) as temi,
        (SELECT COUNT(*) FROM prestatore) as prestatori,
        (SELECT COUNT(*) FROM mostra WHERE data_fine >= CURDATE()) as mostre_attive
")->fetch_assoc();

// Inizia output HTML
echo "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Panel - ArtInisght </title>";

echo getCSS();

echo "</head>
<body>";



// Messaggi
if($messaggio) {
    echo "<div class='message $tipo_messaggio'>";
    echo ($tipo_messaggio === 'success') ? '✅ ' : '❌ ';
    echo h($messaggio);
    echo "</div>";
}

// Statistiche
echo "<div class='stats'>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['mostre'] . "</div>
            <div class='stat-label'>🏛️ Mostre</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['mostre_attive'] . "</div>
            <div class='stat-label'>🔥 Attive</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['autori'] . "</div>
            <div class='stat-label'>👨‍🎨 Artisti</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['opere'] . "</div>
            <div class='stat-label'>🎨 Opere</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['tipologie'] . "</div>
            <div class='stat-label'>📂 Tipologie</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['tecniche'] . "</div>
            <div class='stat-label'>🎭 Tecniche</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['collocazioni'] . "</div>
            <div class='stat-label'>📍 Collocazioni</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['temi'] . "</div>
            <div class='stat-label'>🏷️ Temi</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>" . $stats['prestatori'] . "</div>
            <div class='stat-label'>🤝 Prestatori</div>
        </div>
      </div>";

// Selettore sezione
echo "<div class='selector'>
        <h2>Seleziona Sezione da Gestire</h2>
        <div class='selector-grid'>";

$gestione = $_POST['gestione'] ?? '';

echo "<form method='post'>
        <input type='hidden' name='gestione' value='mostre'>
        <button type='submit' class='selector-btn " . (($gestione=='mostre') ? 'active' : '') . "'>
            🏛️<br>Mostre
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='autori'>
        <button type='submit' class='selector-btn " . (($gestione=='autori') ? 'active' : '') . "'>
            👨‍🎨<br>Autori
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='opere'>
        <button type='submit' class='selector-btn " . (($gestione=='opere') ? 'active' : '') . "'>
            🎨<br>Opere
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='tipologie'>
        <button type='submit' class='selector-btn " . (($gestione=='tipologie') ? 'active' : '') . "'>
            📂<br>Tipologie
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='tecniche'>
        <button type='submit' class='selector-btn " . (($gestione=='tecniche') ? 'active' : '') . "'>
            🎭<br>Tecniche
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='collocazioni'>
        <button type='submit' class='selector-btn " . (($gestione=='collocazioni') ? 'active' : '') . "'>
            📍<br>Collocazioni
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='temi'>
        <button type='submit' class='selector-btn " . (($gestione=='temi') ? 'active' : '') . "'>
            🏷️<br>Temi
        </button>
      </form>";

echo "<form method='post'>
        <input type='hidden' name='gestione' value='prestatori'>
        <button type='submit' class='selector-btn " . (($gestione=='prestatori') ? 'active' : '') . "'>
            🤝<br>Prestatori
        </button>
      </form>";

echo "</div></div>";

// --- GESTIONE COLLOCAZIONI ---
if($gestione=='collocazioni'){
    echo "<div class='section'>
            <div class='section-header'>📍 Collocazioni Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Collocazione</th>
                            <th>Opere Associate</th>
                            <th>Mostre Ospitate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT c.*, 
               COUNT(DISTINCT o.id) as num_opere,
               COUNT(DISTINCT m.id) as num_mostre
        FROM collocazione c 
        LEFT JOIN opera o ON c.id = o.id_collocazione 
        LEFT JOIN mostra m ON c.id = m.sede
        GROUP BY c.id 
        ORDER BY c.nome
    ");
    
    while($r=$res->fetch_assoc()){
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['nome'])."</td>
                <td><span class='count-badge'>".$r['num_opere']." opere</span></td>
                <td><span class='count-badge'>".$r['num_mostre']." mostre</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='collocazione_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='collocazioni'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questa collocazione ha ".$r['num_opere']." opere e ".$r['num_mostre']." mostre associate!\")'>
                            <input type='hidden' name='azione' value='elimina_collocazione'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='collocazioni'>
							                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi collocazione
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuova Collocazione</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_collocazione'>
                <input type='hidden' name='gestione' value='collocazioni'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_coll'>Nome Collocazione *</label>
                        <input type='text' id='nome_coll' name='nome' required placeholder='Es: Museo del Louvre, Galleria degli Uffizi...'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Collocazione</button>
            </form>
          </div>";

    // Form modifica collocazione se richiesto
    if(isset($modifica_tab) && $modifica_tab=='collocazione' && $modifica_id){
        $mod=$conn->query("SELECT * FROM collocazione WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Collocazione ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_collocazione'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='collocazioni'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_coll_mod'>Nome Collocazione *</label>
                            <input type='text' id='nome_coll_mod' name='nome' value='".h($mod['nome'])."' required>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='collocazioni'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE TEMI ---
if($gestione=='temi'){
    echo "<div class='section'>
            <div class='section-header'>🏷️ Temi Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Tema</th>
                            <th>Opere Associate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT t.*, COUNT(ot.id_opera) as num_opere 
        FROM tema t 
        LEFT JOIN opera_tema ot ON t.id = ot.id_tema 
        GROUP BY t.id 
        ORDER BY t.nome
    ");
    
    while($r=$res->fetch_assoc()){
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['nome'])."</td>
                <td><span class='count-badge'>".$r['num_opere']." opere</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='tema_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='temi'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questo tema ha ".$r['num_opere']." opere associate!\")'>
                            <input type='hidden' name='azione' value='elimina_tema'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='temi'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi tema
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuovo Tema</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_tema'>
                <input type='hidden' name='gestione' value='temi'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_tema'>Nome Tema *</label>
                        <input type='text' id='nome_tema' name='nome' required placeholder='Es: Ritratti, Paesaggi, Natura morta...'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Tema</button>
            </form>
          </div>";

    // Form modifica tema se richiesto
    if(isset($modifica_tab) && $modifica_tab=='tema' && $modifica_id){
        $mod=$conn->query("SELECT * FROM tema WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Tema ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_tema'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='temi'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_tema_mod'>Nome Tema *</label>
                            <input type='text' id='nome_tema_mod' name='nome' value='".h($mod['nome'])."' required>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='temi'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE PRESTATORI ---
if($gestione=='prestatori'){
    echo "<div class='section'>
            <div class='section-header'>🤝 Prestatori Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ente</th>
                            <th>Paese</th>
                            <th>Prestiti Attivi</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT p.id, p.paese, p.id_ente,
               e.nome_ente, 
               COUNT(pr.id) as num_prestiti
        FROM prestatore p 
        LEFT JOIN enti e ON p.id_ente = e.id_ente
        LEFT JOIN prestito pr ON p.id = pr.organizzatore
        GROUP BY p.id, p.paese, p.id_ente, e.nome_ente
        ORDER BY e.nome_ente
    ");
    
    while($r=$res->fetch_assoc()){
        $nome_ente = $r['nome_ente'] ? h($r['nome_ente']) : '<em style="color:#999;">Ente non specificato</em>';
        
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>$nome_ente</td>
                <td>".h($r['paese'])."</td>
                <td><span class='count-badge'>".$r['num_prestiti']." prestiti</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='prestatore_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='prestatori'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questo prestatore ha ".$r['num_prestiti']." prestiti associati!\")'>
                            <input type='hidden' name='azione' value='elimina_prestatore'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='prestatori'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi prestatore
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuovo Prestatore</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_prestatore'>
                <input type='hidden' name='gestione' value='prestatori'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_ente_prestatore'>Nome Ente *</label>
                        <input type='text' id='nome_ente_prestatore' name='nome_ente' required placeholder='Es: Museo del Louvre'>
                    </div>
                    <div class='form-group'>
                        <label for='paese_prestatore'>Paese *</label>
                        <input type='text' id='paese_prestatore' name='paese' required placeholder='Es: Francia'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Prestatore</button>
            </form>
          </div>";

    // Form modifica prestatore se richiesto
    if(isset($modifica_tab) && $modifica_tab=='prestatore' && $modifica_id){
        $mod=$conn->query("
            SELECT p.*, e.nome_ente 
            FROM prestatore p 
            LEFT JOIN enti e ON p.id_ente = e.id_ente 
            WHERE p.id=$modifica_id
        ")->fetch_assoc();
        
        if($mod){
            echo "<div class='form-section modify'>
                    <h3 class='form-title'>✏️ Modifica Prestatore ID $modifica_id</h3>
                    <form method='post'>
                        <input type='hidden' name='azione' value='modifica_prestatore'>
                        <input type='hidden' name='id' value='$modifica_id'>
                        <input type='hidden' name='gestione' value='prestatori'>
                        <div class='form-grid'>
                            <div class='form-group'>
                                <label for='nome_ente_prestatore_mod'>Nome Ente *</label>
                                <input type='text' id='nome_ente_prestatore_mod' name='nome_ente' value='".h($mod['nome_ente'] ?? '')."' required>
                            </div>
                            <div class='form-group'>
                                <label for='paese_prestatore_mod'>Paese *</label>
                                <input type='text' id='paese_prestatore_mod' name='paese' value='".h($mod['paese'])."' required>
                            </div>
                        </div>
                        <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                        <form method='post' style='display: inline;'>
                            <input type='hidden' name='gestione' value='prestatori'>
                            <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                        </form>
                    </form>
                  </div>";
        } else {
            echo "<div class='alert alert-error'>❌ Prestatore non trovato!</div>";
        }
    }
} // Fine gestione prestatori




// --- GESTIONE MOSTRE ---
if($gestione=='mostre'){
    echo "<div class='section'>
            <div class='section-header'>🏛️ Mostre Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titolo</th>
                            <th>Curatore</th>
                            <th>Sede</th>
                            <th>Data Inizio</th>
                            <th>Data Fine</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("SELECT m.*,c.nome as sede_nome FROM mostra m LEFT JOIN collocazione c ON m.sede=c.id ORDER BY m.id DESC");
    while($r=$res->fetch_assoc()){
        $oggi = date('Y-m-d');
        $stato = ($r['data_fine'] >= $oggi) ? 
            "<span class='status-active'>🟢 Attiva</span>" : 
            "<span class='status-ended'>🔴 Terminata</span>";
        
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['titolo'])."</td>
                <td>".h($r['curatore'])."</td>
                <td>".h($r['sede_nome'])."</td>
                <td>".date('d/m/Y', strtotime($r['data_inizio']))."</td>
                <td>".date('d/m/Y', strtotime($r['data_fine']))."</td>
                <td>$stato</td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='mostra_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='mostre'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro?\")'>
                            <input type='hidden' name='azione' value='elimina_mostra'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='mostre'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi mostra
echo "<div class='form-section'>
        <h3 class='form-title'>➕ Aggiungi Nuova Mostra</h3>
        <form method='post'>
            <input type='hidden' name='azione' value='aggiungi_mostra'>
            <input type='hidden' name='gestione' value='mostre'>
            <div class='form-grid'>
                <div class='form-group'>
                    <label for='titolo'>Titolo Mostra *</label>
                    <input type='text' id='titolo' name='titolo' required placeholder='Inserisci il titolo'>
                </div>
                <div class='form-group'>
                    <label for='curatore'>Curatore</label>
                    <input type='text' id='curatore' name='curatore' placeholder='Nome del curatore'>
                </div>
                <div class='form-group'>
                    <label for='sede'>Collocazione/Sede *</label>
                    <select id='sede' name='sede' required>
                        <option value=''>-- Seleziona Collocazione --</option>";

$collocazioni=$conn->query("SELECT * FROM collocazione ORDER BY nome");
while($c=$collocazioni->fetch_assoc()){
    echo "<option value='".$c['id']."'>".h($c['nome'])."</option>";
}

echo "          </select>
                </div>
                <div class='form-group'>
                    <label for='data_inizio'>Data Inizio</label>
                    <input type='date' id='data_inizio' name='data_inizio'>
                </div>
                <div class='form-group'>
                    <label for='data_fine'>Data Fine</label>
                    <input type='date' id='data_fine' name='data_fine'>
                </div>
            </div>
            
            <!-- FORM RAPIDO PER AGGIUNGERE COLLOCAZIONE -->
            <div id='quick-collocazione-form' style='display: none; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;'>
                <h4 style='color: #17a2b8; margin-bottom: 10px;'>➕ Aggiungi Nuova Collocazione</h4>
                <div style='display: flex; gap: 10px; align-items: end;'>
                    <div style='flex: 1;'>
                        <label for='nuova_collocazione'>Nome Collocazione</label>
                        <input type='text' id='nuova_collocazione' name='nuova_collocazione' placeholder='Es: Palazzo delle Esposizioni'>
                    </div>
                    <button type='button' onclick='aggiungiCollocazione()' style='background: #17a2b8; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;'>Aggiungi</button>
                </div>
                <small style='color: #666;'>La nuova collocazione sarà aggiunta e selezionata automaticamente</small>
            </div>
            
            <button type='submit' class='btn btn-primary'>➕ Aggiungi Mostra</button>
        </form>
      </div>";


    // Form modifica se richiesto
    if(isset($modifica_tab) && $modifica_tab=='mostra' && $modifica_id){
        $mod=$conn->query("SELECT * FROM mostra WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Mostra ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_mostra'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='mostre'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='titolo_mod'>Titolo Mostra *</label>
                            <input type='text' id='titolo_mod' name='titolo' value='".h($mod['titolo'])."' required>
                        </div>
                        <div class='form-group'>
                            <label for='curatore_mod'>Curatore</label>
                            <input type='text' id='curatore_mod' name='curatore' value='".h($mod['curatore'])."'>
                        </div>
                        <div class='form-group'>
                            <label for='sede_mod'>Sede *</label>
                            <select id='sede_mod' name='sede' required>";
        
        $collocazioni=$conn->query("SELECT * FROM collocazione ORDER BY nome");
        while($c=$collocazioni->fetch_assoc()){
            $selected = ($c['id']==$mod['sede']) ? 'selected' : '';
            echo "<option value='".$c['id']."' $selected>".h($c['nome'])."</option>";
        }
        
        echo "          </select>
                        </div>
                        <div class='form-group'>
                            <label for='data_inizio_mod'>Data Inizio</label>
                            <input type='date' id='data_inizio_mod' name='data_inizio' value='".$mod['data_inizio']."'>
                        </div>
                        <div class='form-group'>
                            <label for='data_fine_mod'>Data Fine</label>
                            <input type='date' id='data_fine_mod' name='data_fine' value='".$mod['data_fine']."'>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='mostre'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE TIPOLOGIE ---
if($gestione=='tipologie'){
    echo "<div class='section'>
            <div class='section-header'>📂 Tipologie Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Tipologia</th>
                            <th>Opere Associate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT t.*, COUNT(o.id) as num_opere 
        FROM tipologia t 
        LEFT JOIN opera o ON t.id = o.id_tipologia 
        GROUP BY t.id 
        ORDER BY t.categoria
    ");
    
    while($r=$res->fetch_assoc()){
            echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['categoria'])."</td>
                <td><span class='count-badge'>".$r['num_opere']." opere</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='tipologia_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='tipologie'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questa tipologia ha ".$r['num_opere']." opere associate!\")'>
                            <input type='hidden' name='azione' value='elimina_tipologia'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='tipologie'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi tipologia
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuova Tipologia</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_tipologia'>
                <input type='hidden' name='gestione' value='tipologie'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_tip'>Nome Tipologia *</label>
                        <input type='text' id='nome_tip' name='nome' required placeholder='Es: Pittura, Scultura, Fotografia...'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Tipologia</button>
            </form>
          </div>";

    // Form modifica tipologia se richiesto
    if(isset($modifica_tab) && $modifica_tab=='tipologia' && $modifica_id){
        $mod=$conn->query("SELECT * FROM tipologia WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Tipologia ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_tipologia'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='tipologie'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_tip_mod'>Nome Tipologia *</label>
                            <input type='text' id='nome_tip_mod' name='nome' value='".h($mod['categoria'])."' required>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='tipologie'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE TECNICHE ---
if($gestione=='tecniche'){
    echo "<div class='section'>
            <div class='section-header'>🎭 Tecniche Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Tecnica</th>
                            <th>Opere Associate</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT t.*, COUNT(o.id) as num_opere 
        FROM tecnica t 
        LEFT JOIN opera o ON t.id = o.id_tecnica 
        GROUP BY t.id 
        ORDER BY t.nome
    ");
    
    while($r=$res->fetch_assoc()){
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['nome'])."</td>
                <td><span class='count-badge'>".$r['num_opere']." opere</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='tecnica_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='tecniche'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questa tecnica ha ".$r['num_opere']." opere associate!\")'>
                            <input type='hidden' name='azione' value='elimina_tecnica'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='tecniche'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi tecnica
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuova Tecnica</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_tecnica'>
                <input type='hidden' name='gestione' value='tecniche'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_tec'>Nome Tecnica *</label>
                        <input type='text' id='nome_tec' name='nome' required placeholder='Es: Olio su tela, Acquerello, Bronzo...'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Tecnica</button>
            </form>
          </div>";

    // Form modifica tecnica se richiesto
    if(isset($modifica_tab) && $modifica_tab=='tecnica' && $modifica_id){
        $mod=$conn->query("SELECT * FROM tecnica WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Tecnica ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_tecnica'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='tecniche'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_tec_mod'>Nome Tecnica *</label>
                            <input type='text' id='nome_tec_mod' name='nome' value='".h($mod['nome'])."' required>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='tecniche'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE OPERE ---
if($gestione=='opere'){
    echo "<div class='section'>
            <div class='section-header'>🎨 Opere Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titolo</th>
                            <th>Autore</th>
                            <th>Anno</th>
                            <th>Tipologia</th>
                            <th>Collocazione</th>
                            <th>Temi</th>
                            <th>Mostra</th>
                            <th>Prestito</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT o.*, 
               a.nome as autore_nome, a.cognome as autore_cognome, 
               t.categoria as tipologia_nome, 
               c.nome as collocazione_nome,
               m.titolo as mostra_titolo,
               pr.inizio as prestito_inizio, pr.fine as prestito_fine,
               e.nome_ente as organizzatore_nome
        FROM opera o 
        LEFT JOIN autore a ON o.id_autore = a.id
        LEFT JOIN tipologia t ON o.id_tipologia = t.id
        LEFT JOIN collocazione c ON o.id_collocazione = c.id
        LEFT JOIN esposizione esp ON o.id = esp.id_opera
        LEFT JOIN mostra m ON esp.id_mostra = m.id
        LEFT JOIN prestito pr ON o.id = pr.id_opera
        LEFT JOIN prestatore p ON pr.organizzatore = p.id
        LEFT JOIN enti e ON p.id_ente = e.id_ente
        ORDER BY o.id DESC
    ");
    
    while($r=$res->fetch_assoc()){
        $autore = trim($r['autore_nome'] . ' ' . $r['autore_cognome']);
        $mostra_info = $r['mostra_titolo'] ? h($r['mostra_titolo']) : '<span style="color:#999;">Nessuna</span>';
        
        // Recupera i temi dell'opera
        $temi_query = $conn->query("
            SELECT t.nome 
            FROM opera_tema ot 
            JOIN tema t ON ot.id_tema = t.id 
            WHERE ot.id_opera = ".$r['id']."
        ");
        $temi = [];
        while($tema = $temi_query->fetch_assoc()){
            $temi[] = $tema['nome'];
        }
        $temi_info = !empty($temi) ? implode(', ', $temi) : 'Nessun tema';
        
        $prestito_info = '';
        if($r['prestito_inizio'] && $r['prestito_fine']){
            $prestito_info = "<small>".date('d/m/Y', strtotime($r['prestito_inizio']))." - ".date('d/m/Y', strtotime($r['prestito_fine']))."</small><br>";
            $prestito_info .= "<strong>".h($r['organizzatore_nome'])."</strong>";
        } else {
            $prestito_info = '<span style="color:#999;">Nessun prestito</span>';
        }
        
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($r['titolo'])."</td>
                <td>".h($autore)."</td>
                <td>".$r['datazione']."</td>
                <td>".h($r['tipologia_nome'])."</td>
                <td>".h($r['collocazione_nome'])."</td>
                <td><small>".h($temi_info)."</small></td>
                <td>$mostra_info</td>
                <td>$prestito_info</td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='opera_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='opere'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro di voler eliminare questa opera?\")'>
                            <input type='hidden' name='azione' value='elimina_opera'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='opere'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi opera
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuova Opera</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_opera'>
                <input type='hidden' name='gestione' value='opere'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='titolo_opera'>Titolo *</label>
                        <input type='text' id='titolo_opera' name='titolo' required placeholder=\"Titolo dell'opera\">
                    </div>
                    <div class='form-group'>
                        <label for='anno_opera'>Anno</label>
                        <input type='text' id='anno_opera' name='datazione' placeholder='Anno di realizzazione'>
                    </div>
                    <div class='form-group'>
                        <label for='id_autore_opera'>Autore *</label>
                        <select id='id_autore_opera' name='id_autore' required>
                            <option value=''>-- Seleziona Autore --</option>";
    
    $autori=$conn->query("SELECT * FROM autore ORDER BY cognome, nome");
    while($a=$autori->fetch_assoc()){
        echo "<option value='".$a['id']."'>".h($a['nome'].' '.$a['cognome'])."</option>";
    }
    
    echo "          </select>
                    </div>
                    <div class='form-group'>
                        <label for='id_tipologia_opera'>Tipologia *</label>
                        <select id='id_tipologia_opera' name='id_tipologia' required>
                            <option value=''>-- Seleziona Tipologia --</option>";
    
    $tipologie=$conn->query("SELECT * FROM tipologia ORDER BY categoria");
    while($t=$tipologie->fetch_assoc()){
        echo "<option value='".$t['id']."'>".h($t['categoria'])."</option>";
    }
    
    echo "          </select>
                    </div>
                    <div class='form-group'>
                        <label for='id_tecnica_opera'>Tecnica</label>
                        <select id='id_tecnica_opera' name='id_tecnica'>
                            <option value=''>-- Seleziona Tecnica --</option>";
    
    $tecniche=$conn->query("SELECT * FROM tecnica ORDER BY nome");
    while($t=$tecniche->fetch_assoc()){
        echo "<option value='".$t['id']."'>".h($t['nome'])."</option>";
    }
    
    echo "          </select>
                    </div>
                    <div class='form-group'>
                        <label for='id_collocazione_opera'>Collocazione *</label>
                        <select id='id_collocazione_opera' name='id_collocazione' required>
                            <option value=''>-- Seleziona Collocazione --</option>";
    
    $collocazioni=$conn->query("SELECT * FROM collocazione ORDER BY nome");
    while($c=$collocazioni->fetch_assoc()){
        echo "<option value='".$c['id']."'>".h($c['nome'])."</option>";
    }
    
    echo "          </select>
                    </div>
                    <div class='form-group'>
                        <label for='id_mostra_opera'>Mostra 🏛️</label>
                        <select id='id_mostra_opera' name='id_mostra'>
                            <option value=''>-- Nessuna Mostra --</option>";
    
    $mostre=$conn->query("SELECT * FROM mostra ORDER BY titolo");
    while($m=$mostre->fetch_assoc()){
        $stato_mostra = (date('Y-m-d') <= $m['data_fine']) ? '🟢' : '🔴';
        echo "<option value='".$m['id']."'>$stato_mostra ".h($m['titolo'])."</option>";
    }
    
    echo "          </select>
                    </div>
                    <div class='form-group'>
                        <label for='id_prestatore_opera'>Prestatore 🤝</label>
                        <select id='id_prestatore_opera' name='id_prestatore'>
                            <option value=''>-- Nessun Prestatore --</option>";
    
    $prestatori=$conn->query("
        SELECT p.*, e.nome_ente 
        FROM prestatore p 
        LEFT JOIN enti e ON p.id_ente = e.id_ente 
        ORDER BY e.nome_ente
    ");
    while($p=$prestatori->fetch_assoc()){
        echo "<option value='".$p['id']."'>".h($p['nome_ente'])." (".h($p['paese']).")</option>";
    }
    
    echo "          </select>
                    </div>
                </div>
                
                <!-- SEZIONE TEMI -->
                <div style='margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;'>
                    <h4 style='margin: 0 0 15px 0; color: #856404;'>🎨 Temi dell Opera (Opzionale)</h4>
                    <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: white;'>";
    
    $temi=$conn->query("SELECT * FROM tema ORDER BY nome");
    while($tema=$temi->fetch_assoc()){
        echo "<label style='display: flex; align-items: center; cursor: pointer; padding: 5px;'>
                <input type='checkbox' name='temi[]' value='".$tema['id']."' style='margin-right: 8px;'>
                <span>".h($tema['nome'])."</span>
              </label>";
    }
    
    echo "      </div>
                    <small style='color: #856404;'>💡 Seleziona uno o più temi che caratterizzano quest opera</small>
                </div>
                
                <!-- SEZIONE PRESTITO -->
                <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;'>
                    <h4 style='margin: 0 0 15px 0; color: #007bff;'>📋 Informazioni Prestito (Opzionale)</h4>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='organizzatore_prestito'>Organizzatore Prestito 🏛️</label>
                            <select id='organizzatore_prestito' name='organizzatore'>
                                <option value=''>-- Nessun Organizzatore --</option>";
    
    $organizzatori=$conn->query("
        SELECT p.id as prestatore_id, e.nome_ente, p.paese 
        FROM prestatore p 
        JOIN enti e ON p.id_ente = e.id_ente 
        ORDER BY e.nome_ente
    ");
    while($org=$organizzatori->fetch_assoc()){
        echo "<option value='".$org['prestatore_id']."'>".h($org['nome_ente'])." (".h($org['paese']).")</option>";
    }
    
    echo "              </select>
                        </div>
                        <div class='form-group'>
                            <label for='inizio_prestito'>Data Inizio Prestito 📅</label>
                            <input type='date' id='inizio_prestito' name='inizio_prestito'>
                        </div>
                        <div class='form-group'>
                            <label for='fine_prestito'>Data Fine Prestito 📅</label>
                            <input type='date' id='fine_prestito' name='fine_prestito'>
                        </div>
                    </div>
                    <small style='color: #666;'>💡 Compila questi campi solo se l opera è oggetto di un prestito</small>
                </div>
                
                <button type='submit' class='btn btn-primary' style='margin-top: 20px;'>➕ Aggiungi Opera</button>
            </form>
          </div>";

    // Form modifica opera se richiesto
    if(isset($modifica_tab) && $modifica_tab=='opera' && $modifica_id){
        $mod=$conn->query("SELECT * FROM opera WHERE id=$modifica_id")->fetch_assoc();
        
        if($mod){
            // Recupera eventuale prestito esistente
            $prestito_esistente = $conn->query("SELECT * FROM prestito WHERE id_opera=$modifica_id")->fetch_assoc();
            
            // Recupera eventuale esposizione esistente
            $esposizione_esistente = $conn->query("SELECT id_mostra FROM esposizione WHERE id_opera=$modifica_id")->fetch_assoc();
            
            // Recupera temi esistenti
            $temi_esistenti = [];
            $temi_query = $conn->query("SELECT id_tema FROM opera_tema WHERE id_opera=$modifica_id");
            while($tema = $temi_query->fetch_assoc()){
                $temi_esistenti[] = $tema['id_tema'];
            }
            
            echo "<div class='form-section modify'>
                    <h3 class='form-title'>✏️ Modifica Opera ID $modifica_id</h3>
                    <form method='post'>
                        <input type='hidden' name='azione' value='modifica_opera'>
                        <input type='hidden' name='id' value='$modifica_id'>
                        <input type='hidden' name='gestione' value='opere'>
                        <div class='form-grid'>
                            <div class='form-group'>
                                <label for='titolo_opera_mod'>Titolo *</label>
                                <input type='text' id='titolo_opera_mod' name='titolo' value='".h($mod['titolo'])."' required>
                            </div>
                            <div class='form-group'>
                                <label for='anno_opera_mod'>Anno</label>
                                <input type='text' id='anno_opera_mod' name='datazione' value='".$mod['datazione'].">
                            </div>
                            <div class='form-group'>
                                <label for='id_autore_opera_mod'>Autore *</label>
                                <select id='id_autore_opera_mod' name='id_autore' required>";
            
            $autori=$conn->query("SELECT * FROM autore ORDER BY cognome, nome");
            while($a=$autori->fetch_assoc()){
                $selected = ($a['id']==$mod['id_autore']) ? 'selected' : '';
                echo "<option value='".$a['id']."' $selected>".h($a['nome'].' '.$a['cognome'])."</option>";
            }
            
            echo "          </select>
                            </div>
                            <div class='form-group'>
                                <label for='id_tipologia_opera_mod'>Tipologia *</label>
                                <select id='id_tipologia_opera_mod' name='id_tipologia' required>";
            
            $tipologie=$conn->query("SELECT * FROM tipologia ORDER BY categoria");
            while($t=$tipologie->fetch_assoc()){
                $selected = ($t['id']==$mod['id_tipologia']) ? 'selected' : '';
                echo "<option value='".$t['id']."' $selected>".h($t['categoria'])."</option>";
            }
            
            echo "          </select>
                            </div>
                            <div class='form-group'>
                                <label for='id_tecnica_opera_mod'>Tecnica</label>
                                <select id='id_tecnica_opera_mod' name='id_tecnica'>
                                    <option value=''>-- Nessuna --</option>";
            
            $tecniche=$conn->query("SELECT * FROM tecnica ORDER BY nome");
            while($t=$tecniche->fetch_assoc()){
                $selected = ($t['id']==$mod['id_tecnica']) ? 'selected' : '';
                echo "<option value='".$t['id']."' $selected>".h($t['nome'])."</option>";
            }
            
            echo "          </select>
                            </div>
                            <div class='form-group'>
                                <label for='id_collocazione_opera_mod'>Collocazione *</label>
                                <select id='id_collocazione_opera_mod' name='id_collocazione' required>";
            
            $collocazioni=$conn->query("SELECT * FROM collocazione ORDER BY nome");
            while($c=$collocazioni->fetch_assoc()){
                $selected = ($c['id']==$mod['id_collocazione']) ? 'selected' : '';
                echo "<option value='".$c['id']."' $selected>".h($c['nome'])."</option>";
            }
            
            echo "          </select>
                            </div>
                            <div class='form-group'>
                                <label for='id_mostra_opera_mod'>Mostra 🏛️</label>
                                <select id='id_mostra_opera_mod' name='id_mostra'>
                                    <option value=''>-- Nessuna Mostra --</option>";
            
            $mostre=$conn->query("SELECT * FROM mostra ORDER BY titolo");
            while($m=$mostre->fetch_assoc()){
                $selected = ($esposizione_esistente && $m['id']==$esposizione_esistente['id_mostra']) ? 'selected' : '';
                $stato_mostra = (date('Y-m-d') <= $m['data_fine']) ? '🟢' : '🔴';
                echo "<option value='".$m['id']."' $selected>$stato_mostra ".h($m['titolo'])."</option>";
            }
            
            echo "          </select>
                            </div>
                            <div class='form-group'>
                                <label for='id_prestatore_opera_mod'>Prestatore 🤝</label>
                                <select id='id_prestatore_opera_mod' name='id_prestatore'>
                                    <option value=''>-- Nessun Prestatore --</option>";
            
            $prestatori=$conn->query("
                SELECT p.*, e.nome_ente 
                FROM prestatore p 
                LEFT JOIN enti e ON p.id_ente = e.id_ente 
                ORDER BY e.nome_ente
            ");
            while($p=$prestatori->fetch_assoc()){
                $selected = ($p['id']==$mod['id_prestatore']) ? 'selected' : '';
                echo "<option value='".$p['id']."' $selected>".h($p['nome_ente'])." (".h($p['paese']).")</option>";
            }
            
            echo "          </select>
                            </div>
                        </div>
                        
                        <!-- SEZIONE TEMI MODIFICA -->
                        <div style='margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;'>
                            <h4 style='margin: 0 0 15px 0; color: #856404;'>🎨 Temi dell Opera</h4>
                            <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: white;'>";
            
            $temi=$conn->query("SELECT * FROM tema ORDER BY nome");
            while($tema=$temi->fetch_assoc()){
                $checked = in_array($tema['id'], $temi_esistenti) ? 'checked' : '';
                echo "<label style='display: flex; align-items: center; cursor: pointer; padding: 5px;'>
                        <input type='checkbox' name='temi[]' value='".$tema['id']."' $checked style='margin-right: 8px;'>
                        <span>".h($tema['nome'])."</span>
                      </label>";
            }
            
            echo "      </div>
                        </div>
                        
                        <!-- SEZIONE PRESTITO MODIFICA -->
                        <div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;'>
                            <h4 style='margin: 0 0 15px 0; color: #007bff;'>📋 Informazioni Prestito</h4>
                            <div class='form-grid'>
                                <div class='form-group'>
                                    <label for='organizzatore_prestito_mod'>Organizzatore Prestito 🏛️</label>
                                    <select id='organizzatore_prestito_mod' name='organizzatore'>
                                        <option value=''>-- Nessun Organizzatore --</option>";
            
            $organizzatori=$conn->query("
                SELECT p.id as prestatore_id, e.nome_ente, p.paese 
                FROM prestatore p 
                JOIN enti e ON p.id_ente = e.id_ente 
                ORDER BY e.nome_ente
            ");
            while($org=$organizzatori->fetch_assoc()){
                $selected = ($prestito_esistente && $org['prestatore_id']==$prestito_esistente['organizzatore']) ? 'selected' : '';
                echo "<option value='".$org['prestatore_id']."' $selected>".h($org['nome_ente'])." (".h($org['paese']).")</option>";
            }
            
            echo "              </select>
                                </div>
                                <div class='form-group'>
                                    <label for='inizio_prestito_mod'>Data Inizio Prestito 📅</label>
                                    <input type='date' id='inizio_prestito_mod' name='inizio_prestito' value='".($prestito_esistente['inizio'] ?? '')."'>
                                </div>
                                <div class='form-group'>
                                    <label for='fine_prestito_mod'>Data Fine Prestito 📅</label>
                                    <input type='date' id='fine_prestito_mod' name='fine_prestito' value='".($prestito_esistente['fine'] ?? '')."'>
                                </div>
                            </div>
                        </div>
                        
                        <button type='submit' class='btn btn-primary' style='margin-top: 20px;'>💾 Salva Modifiche</button>
                        <form method='post' style='display: inline;'>
                            <input type='hidden' name='gestione' value='opere'>
                            <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                        </form>
                    </form>
                  </div>";
        } else {
            echo "<div class='alert alert-error'>❌ Opera non trovata!</div>";
        }
    }
} // Fine gestione opere




// --- GESTIONE AUTORI ---
if($gestione=='autori'){
    echo "<div class='section'>
            <div class='section-header'>👨‍🎨 Autori Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Completo</th>
                            <th>Movimento</th>
                            <th>Nazionalità</th>
                            <th>Opere</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT a.*, COUNT(o.id) as num_opere 
        FROM autore a
		        LEFT JOIN opera o ON a.id = o.id_autore 
        GROUP BY a.id 
        ORDER BY a.cognome, a.nome
    ");
    
    while($r=$res->fetch_assoc()){
        $nome_completo = trim($r['nome'] . ' ' . $r['cognome']);
        echo "<tr>
                <td><strong>".$r['id']."</strong></td>
                <td>".h($nome_completo)."</td>
                <td>".h($r['movimento'])."</td>
                <td>".h($r['nazionalita'])."</td>
                <td><span class='count-badge'>".$r['num_opere']." opere</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='autore_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='autori'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questo autore ha ".$r['num_opere']." opere associate!\")'>
                            <input type='hidden' name='azione' value='elimina_autore'>
                            <input type='hidden' name='id' value='".$r['id']."'>
                            <input type='hidden' name='gestione' value='autori'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi autore
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuovo Autore</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_autore'>
                <input type='hidden' name='gestione' value='autori'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_aut'>Nome *</label>
                        <input type='text' id='nome_aut' name='nome' required placeholder='Inserisci Nome'>
                    </div>
                    <div class='form-group'>
                        <label for='cognome_aut'>Cognome *</label>
                        <input type='text' id='cognome_aut' name='cognome' required placeholder='Inserisci Cognome'>
                    </div>
                    <div class='form-group'>
                        <label for='movimento_aut'>Movimento Artistico</label>
                        <input type='text' id='movimento_aut' name='movimento' placeholder='Es: Rinascimento, Impressionismo...'>
                    </div>
                    <div class='form-group'>
                        <label for='nazionalita_aut'>Nazionalità</label>
                        <input type='text' id='nazionalita_aut' name='nazionalita' placeholder='Es: Italiana, Francese...'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Autore</button>
            </form>
          </div>";

    // Form modifica autore se richiesto
    if(isset($modifica_tab) && $modifica_tab=='autore' && $modifica_id){
        $mod=$conn->query("SELECT * FROM autore WHERE id=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Autore ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_autore'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='autori'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_aut_mod'>Nome *</label>
                            <input type='text' id='nome_aut_mod' name='nome' value='".h($mod['nome'])."' required>
                        </div>
                        <div class='form-group'>
                            <label for='cognome_aut_mod'>Cognome *</label>
                            <input type='text' id='cognome_aut_mod' name='cognome' value='".h($mod['cognome'])."' required>
                        </div>
                        <div class='form-group'>
                            <label for='movimento_aut_mod'>Movimento Artistico</label>
                            <input type='text' id='movimento_aut_mod' name='movimento' value='".h($mod['movimento'])."'>
                        </div>
                        <div class='form-group'>
                            <label for='nazionalita_aut_mod'>Nazionalità</label>
                            <input type='text' id='nazionalita_aut_mod' name='nazionalita' value='".h($mod['nazionalita'])."'>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='autori'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

// --- GESTIONE ENTI ---
if($gestione=='enti'){
    echo "<div class='section'>
            <div class='section-header'>🏢 Enti Esistenti</div>
            <div class='table-container'>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Ente</th>
                            <th>Tipo</th>
                            <th>Città</th>
                            <th>Prestatori</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $res=$conn->query("
        SELECT e.*, COUNT(p.id) as num_prestatori 
        FROM enti e 
        LEFT JOIN prestatore p ON e.id_ente = p.id_ente 
        GROUP BY e.id_ente 
        ORDER BY e.nome_ente
    ");
    
    while($r=$res->fetch_assoc()){
        echo "<tr>
                <td><strong>".$r['id_ente']."</strong></td>
                <td>".h($r['nome_ente'])."</td>
                <td>".h($r['tipo'])."</td>
                <td>".h($r['citta'])."</td>
                <td><span class='count-badge'>".$r['num_prestatori']." prestatori</span></td>
                <td>
                    <div class='actions'>
                        <form method='post' style='display:inline; margin:0;'>
                            <input type='hidden' name='azione' value='ente_modifica_form'>
                            <input type='hidden' name='id' value='".$r['id_ente']."'>
                            <input type='hidden' name='gestione' value='enti'>
                            <button type='submit' class='btn btn-edit'>✏️ Modifica</button>
                        </form>
                        <form method='post' style='display:inline; margin:0;' onsubmit='return confirm(\"Sei sicuro? Questo ente ha ".$r['num_prestatori']." prestatori associati!\")'>
                            <input type='hidden' name='azione' value='elimina_ente'>
                            <input type='hidden' name='id' value='".$r['id_ente']."'>
                            <input type='hidden' name='gestione' value='enti'>
                            <button type='submit' class='btn btn-delete'>🗑️ Elimina</button>
                        </form>
                    </div>
                </td>
              </tr>";
    }
    echo "</tbody></table></div></div>";

    // Form aggiungi ente
    echo "<div class='form-section'>
            <h3 class='form-title'>➕ Aggiungi Nuovo Ente</h3>
            <form method='post'>
                <input type='hidden' name='azione' value='aggiungi_ente'>
                <input type='hidden' name='gestione' value='enti'>
                <div class='form-grid'>
                    <div class='form-group'>
                        <label for='nome_ente'>Nome Ente *</label>
                        <input type='text' id='nome_ente' name='nome_ente' required placeholder='Es: Museo del Louvre, Galleria degli Uffizi...'>
                    </div>
                    <div class='form-group'>
                        <label for='tipo_ente'>Tipo Ente</label>
                        <select id='tipo_ente' name='tipo'>
                            <option value=''>-- Seleziona Tipo --</option>
                            <option value='Museo'>Museo</option>
                            <option value='Galleria'>Galleria</option>
                            <option value='Fondazione'>Fondazione</option>
                            <option value='Collezione Privata'>Collezione Privata</option>
                            <option value='Università'>Università</option>
                            <option value='Altro'>Altro</option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <label for='citta_ente'>Città</label>
                        <input type='text' id='citta_ente' name='citta' placeholder='Città dell\'ente'>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary'>➕ Aggiungi Ente</button>
            </form>
          </div>";

    // Form modifica ente se richiesto
    if(isset($modifica_tab) && $modifica_tab=='ente' && $modifica_id){
        $mod=$conn->query("SELECT * FROM enti WHERE id_ente=$modifica_id")->fetch_assoc();
        echo "<div class='form-section modify'>
                <h3 class='form-title'>✏️ Modifica Ente ID $modifica_id</h3>
                <form method='post'>
                    <input type='hidden' name='azione' value='modifica_ente'>
                    <input type='hidden' name='id' value='$modifica_id'>
                    <input type='hidden' name='gestione' value='enti'>
                    <div class='form-grid'>
                        <div class='form-group'>
                            <label for='nome_ente_mod'>Nome Ente *</label>
                            <input type='text' id='nome_ente_mod' name='nome_ente' value='".h($mod['nome_ente'])."' required>
                        </div>
                        <div class='form-group'>
                            <label for='tipo_ente_mod'>Tipo Ente</label>
                            <select id='tipo_ente_mod' name='tipo'>
                                <option value=''>-- Seleziona Tipo --</option>";
        
        $tipi = ['Museo', 'Galleria', 'Fondazione', 'Collezione Privata', 'Università', 'Altro'];
        foreach($tipi as $tipo){
            $selected = ($tipo == $mod['tipo']) ? 'selected' : '';
            echo "<option value='$tipo' $selected>$tipo</option>";
        }
        
        echo "          </select>
                        </div>
                        <div class='form-group'>
                            <label for='citta_ente_mod'>Città</label>
                            <input type='text' id='citta_ente_mod' name='citta' value='".h($mod['citta'])."'>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>💾 Salva Modifiche</button>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='gestione' value='enti'>
                        <button type='submit' class='btn btn-secondary'>❌ Annulla</button>
                    </form>
                </form>
              </div>";
    }
}

echo "</div>"; // Chiude container


?>


                
